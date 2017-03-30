<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class OrderApp extends BackendApp
{
    /**
     *    管理
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        $site=site_url();
        $this->assign('site_url',$site);
        $search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $model_order =& m('order');
        $page   =   $this->_get_page(10);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'add_time';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'add_time';
            $order = 'desc';
        }
        $orders = $model_order->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        $page['item_count'] = $model_order->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('order_pending'),
            ORDER_SUBMITTED => Lang::get('order_submitted'),
            ORDER_ACCEPTED => Lang::get('order_accepted'),
            ORDER_SHIPPED => Lang::get('order_shipped'),
            ORDER_FINISHED => Lang::get('order_finished'),
            ORDER_CANCELED => Lang::get('order_canceled'),
        ));
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,layer/layer.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('order.index.html');
    }

    /**
     *    查看
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info = $model_order->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_orderextm',
            'include'       => array(
                'has_ordergoods',   //取出订单商品
            ),
        ));

        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $order_info['group_id'] = 0;
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod =& m('groupbuy');
            $groupbuy = $groupbuy_mod->get(array(
                'fields' => 'groupbuy.group_id',
                'join' => 'be_join',
                'conditions' => "order_id = {$order_info['order_id']} ",
                )
            );
            $order_info['group_id'] = $groupbuy['group_id'];
        }
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            if (substr($goods['goods_image'], 0, 7) != 'http://')
            {
                $order_detail['data']['goods_list'][$key]['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
            }
        }
        $db=&db();
        $query="select * from ecm_order_tofac where order_id='$order_id' limit 1";
        $tofac=$db->getrow($query);
                //获取当前支付账号信息
            /*{s:14:"alipay_account";s:6:"111112";s:10:"alipay_key";s:0:"";s:14:"alipay_partner";s:0:"";s:14:"alipay_service";s:21:"trade_create_by_buyer";s:5:"pcode";s:0:"";s:8:"sms_code";s:0:"";}*/
        $payment=& m('payment');
        $payment_info= $payment->get(array("store_id = {$order_info['seller_id']}")
                    );
        preg_match('/s:14:"alipay_account";s:\d+:"([\w?@]+)"/i', $payment_info['config'], $matches);
        $alipay_account=$matches[1];
        $tofac['alipay_account']=$alipay_account;
        $tofac['log_time']=date("Y-m-d H:i:s",$tofac['log_time']);
        $this->assign('alipay_tofac',$tofac);
        
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('order.view.html');
    }
     /**
     *    收到货款
     *
     *    @author    jjc
     *    @param    none
     *    @return    void
     */
    function received_pay()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_PENDING);
        if (!$order_id)
        {
             $output=array("code"    =>1,
                              "message" =>"该订单不存在",
                              "data"    =>""
                            );
                echo json_encode($output);

            return;
        }
        if (!IS_POST)
        {
            /*header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.received_pay.html');*/
            return;
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->edit(intval($order_id), array('status' => ORDER_ACCEPTED, 'pay_time' => gmtime()));
            if ($model_order->has_error())
            {
                $output=array("code"    =>2,
                              "message" =>$model_order->get_error(),
                              "data"    =>""
                              );
                echo json_encode($output);
                return;
            }
            #TODO 发邮件通知
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件，提示等待安排发货 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_offline_pay_success_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_accepted'),
                'actions'   => array(
                    'cancel',
                    'shipped'
                ), //可以取消可以发货
            );

           $output=array("code"    =>0,
                         "message" =>"订单确认成功",
                         "data"    =>""
                        );
            echo json_encode($output);
            return;
            
        }

    }
     /**
     *    获取有效的订单信息
     *
     *    @author    jjc
     *    @param     array $status
     *    @param     string $ext
     *    @return    array
     */
    function _get_valid_order_info($status, $ext = '')
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {

            return array();
        }
        if (!is_array($status))
        {
            $status = array($status);
        }

        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* 只有已发货的货到付款订单可以收货 */
        $order_info     = $model_order->get(array(
            'conditions'    => "order_id={$order_id} AND status " . db_create_in($status) . $ext,
        ));

        if (empty($order_info))
        {

            return array();
        }

        return array($order_id, $order_info);
    }
    function confirm_pay_tofac(){
        //数据准备
        $order_id   =$_GET['order_id'];
        if (empty($order_id)) {
              $output=array(
                            'code'=>3,
                            'message'=>'参数异常',
                            'data'=>"",
                            );
               echo json_encode($output);
               return;
        }
        $operator   =$this->visitor->get('user_name');
        $remark     =$_POST['remark'];
        $log_time   =time();
        $db=&db();
        $insert="insert into ecm_order_tofac values(null,'$order_id','$operator','$remark','$log_time')";
        $result=$db->query($insert);
        //修改订单的状态
        $update="update ecm_order set pay_to_fac=1 where order_id=$order_id";
        $db->query($update);
        if ($result) {
            $output=array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>"",
                            );
        }else{
            $output=array(
                            'code'=>1,
                            'message'=>'请求失败',
                            'data'=>"",
                            );
        }
        echo json_encode($output);
    }
    /**
     * [drawlist 充提记录]
     * @return [type] [description]
     */
    function drawlist(){
        $recharge_mod = m('recharge_log');
        $user_id = $this->visitor->get('user_id');
        $where = "user_id = '$user_id' AND recharge_status ='0'".$where_time;
        $page = $this->_get_page(10);
        $recharge_arr = $recharge_mod ->find_recharge_all($where,$page['limit']);


        foreach ($recharge_arr as $key => $recharge) {
            $recharge_arr[$key]['first_time'] = date('Y-m-d H:i:s',$recharge['first_time']);
            $recharge_arr[$key]['pay_status_des'] = Lang::get("pay_status_".$recharge['pay_status']);
            /*$recharge_arr[$key]['bank_info'] = $bank_mod->get($recharge['pay_account']);*/
             if ($recharge['pay_method'] == 1) {
               $recharge_arr[$key]['money_from_des']='支付宝';
            }elseif($money['pay_method'] == 2){
               $recharge_arr[$key]['money_from_des']='微信'; 
            }elseif($money['pay_method'] == 0){
               $recharge_arr[$key]['money_from_des']='预存款'; 
            }
   
        }
        $this->assign('recharge_arr',$recharge_arr);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,layer/layer.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('drawlist.index.html');
    }
}
?>
