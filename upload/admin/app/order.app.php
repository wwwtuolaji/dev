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
    public $db;
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

    /**
     *    查看
     *
     *    @author    JJc
     *    @param    none
     *    @return    void
     */
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
     * @author JJc
     * @return [type] [description]
     */
    function drawlist(){
        $get_where ="";
        if(!empty($_GET['user_name'])){
          $get_where .="AND member.user_name like '%{$_GET['user_name']}%'";  
        }
        if (!empty($_GET['record_type'])) {
            if ($_GET['record_type']=="recharge") {
              $get_where .="AND log.recharge_status ='0'";  
            }else{
              $get_where .="AND log.recharge_status ='1'";  
            }
        }
        if (!empty($_GET['status'])) {
            if ($_GET['status']=='success') {
                $get_where .="AND log.pay_status ='40'";
            }else{
                $get_where .="AND log.pay_status <>'40'";
            }
        }
        if (!empty($_GET['order_amount_from'])) {
            $get_where .="AND log.pay_money > '{$_GET['order_amount_from']}'";
        }
         if (!empty($_GET['order_amount_to'])) {
            $get_where .="AND log.pay_money <'{$_GET['order_amount_to']}'";
        }
        if (!empty($get_where)) {
           $this->assign('init_btn',true); 
        }
        else{
           $this->assign('init_btn',false);   
        }
        $add_time_from = empty($_GET['add_time_from'])?strtotime("1970-01-01"):strtotime($_GET['add_time_from']);
        $add_time_to   = empty($_GET['add_time_to'])?time():strtotime($_GET['add_time_to']);
        $get_where .= "AND log.first_time > '$add_time_from' AND log.first_time < '$add_time_to'";
        $recharge_mod = m('recharge_log');
        $user_id = $this->visitor->get('user_id');
        /* $where = "user_id = '$user_id' AND recharge_status ='0'".$where_time;*/  
        $page = $this->_get_page(10);
        $recharge_arr = $recharge_mod ->find_recharge_all($get_where,$page['limit']);
         /* $page['item_count'] = $model_order->getCount();  */ //获取统计的数据
        $page['item_count'] = $recharge_mod->get_wehre_count($get_where);   //获取统计的数据
        $this->_format_page($page);
        foreach ($recharge_arr as $key => $recharge) {
            $recharge_arr[$key]['first_time_des'] = date('Y-m-d H:i:s',$recharge['first_time']);
            $recharge_arr[$key]['pay_status_des'] = Lang::get("pay_status_".$recharge['pay_status']);
            /*$recharge_arr[$key]['bank_info'] = $bank_mod->get($recharge['pay_account']);*/
             if ($recharge['pay_method'] == 1) {
               $recharge_arr[$key]['money_from_des']='支付宝';
            }elseif($recharge['pay_method'] == 2){
               $recharge_arr[$key]['money_from_des']='微信'; 
            }elseif($recharge['pay_method'] == 3){
               $recharge_arr[$key]['money_from_des']='银行卡';  
            }elseif($recharge['pay_method'] == 0){
               $recharge_arr[$key]['money_from_des']='预存款'; 
            }
   
        }
        /*dump($page);*/
        $this->assign('page_info', $page);   
        $this->assign('recharge_arr',$recharge_arr);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,layer/layer.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('drawlist.index.html');
    }
    /**
     * [view_withdraw description]
     * @author JJc
     * @return [type] [description]
     */
    function view_withdraw(){
        $recharge_id = $_GET['id'];
        $recharge_mod = m('recharge_log');
        /*获取当前的订单状态*/
        $recharge_info = $recharge_mod ->get($recharge_id);
        $recharge_info['first_time_des'] =date('Y-m-d H:i:s',$recharge_info['first_time']);
        $member = m('member');
        $user_info = $member ->get($recharge_info['user_id']);
        if ($recharge_info['pay_method'] == 3) {
            $bank_mod = m('bank');
            $bank_info = $bank_mod->get($recharge_info['pay_account']);
            $bank_info['num_des'] = number_format($bank_info['num']);
            $recharge_info['payment_name']='bank_method';
        } 
        $money_mod = m('money_history');
        $data = array('conditions'=>"user_id ='{$recharge_info['user_id']}'");
        $money_array = $money_mod->find($data);
        /*dump($money_arr);*/
        //虚拟货币金额明细
        foreach ($money_array as $key => $money) {
            //0预存款，1支付宝, 2微信
            if ($money['money_from'] == 1) {
               $money_array[$key]['money_from_des']='支付宝';
            }elseif($money['money_from'] == 2){
               $money_array[$key]['money_from_des']='微信'; 
            }elseif($money['money_from'] == 3){   
                $money_array[$key]['money_from_des']='银行卡'; 
            }elseif($money['money_from'] == 0){
               $money_array[$key]['money_from_des']='预存款'; 
            }
            //0,茶通历史表，1，商城 2，transactionc茶通金额取消使用此项3,个人充值
            if ($money['platform_from']==1) {
                //交易对象是商城待定
                $money_array[$key]['platform_from_des']='商城：产品购买';
            }elseif($money['platform_from'] == 0){
                $money_array[$key]['platform_from_des']='茶通：产品卖出盈利';
            }elseif($money['platform_from'] == 2){
                //判断当前是取消还是，买入
                $sql = "select * from ecm_transaction where transaction_sn='{$money['transaction_sn']}'";
                $db = db();
                $transaction_info = $db->getrow($sql);
                //lll
                if ($transaction_info['transaction_status']==-1) {
                    $money_array[$key]['platform_from_des']='茶通：产品取消退款';
                }else{
                    $money_array[$key]['platform_from_des']='茶通：申请购买产品扣款'; 
                }
            }elseif($money['platform_from'] == 3){
                $money_array[$key]['platform_from_des']='个人充值'; 
            }
            $money_array[$key]['pay_time_des'] = date('Y-m-d H:i:s',$money['pay_time']);
            $money_array[$key]['money_history_id_des'] =100000 + $money['money_history_id'];
            
        }
        /*dump($money_array);*/
        $this->assign('money_array',$money_array);
        $this->assign('bank_info',$bank_info);
        $this->assign('user_info',$user_info);
        $this->assign('recharge',$recharge_info);
        $this->display('withdraw.view.html');
    }
    /**
     * [money_info 单笔详细信息]
     * @return [type] [description]
     */
    function money_info(){
        $money_history_id = $_GET['money_history_id'];
        $platform_from =$_GET['platform_from'];
        $sn =$_GET['sn'];
        $user_id = $_GET['user_id'];
        if (empty($money_history_id) || empty($platform_from) || empty($sn) || empty($user_id)) {
            $this->show_warning('参数异常');
            return;
        }   
        $money_history_mod = m('money_history');
        $transaction_mod = m('transaction');
        $transaction_history_mod = m('transaction_history');
        $member_mod = m('member');
        $member_info = $member_mod ->get($user_id);
        $recharge_mod = m('recharge_log');
        //0,茶通历史表，1，商城 2，transactionc茶通金额取消使用此项3,个人充值
        $money_info = $money_history_mod->get($money_history_id);
        if ($money_info['money_from'] == 1) {
            $money_info['money_from_des']='支付宝';
        }elseif($money_info['money_from'] == 2){
            $money_info['money_from_des']='微信'; 
        }elseif($money_info['money_from'] == 3){   
            $money_info['money_from_des']='银行卡'; 
        }elseif($money_info['money_from'] == 0){
            $money_info['money_from_des']='预存款'; 
        }   
        //支付时间   
         
        if ($platform_from==1) {
            //交易对象是商城待定
            $money_info['platform_from_des']='商城：产品购买';
            $money_info['status_des'] ='已完成';//暂时定已经完成
        }elseif($platform_from == 0){
            $money_info['platform_from_des']='茶通：产品卖出盈利';
            $money_info['status_des'] ='已完成';
            //查询当前的历史交易获取当前的交易的用户
            $history_info = $transaction_history_mod->get_history_info($sn);
            $money_info['buyer_name'] = $history_info['buyer'];
            $money_info['seller_name'] = $history_info['seller']; 
            $money_info['transaction_time'] = date('Y-m-d H:i:s',$history_info['transaction_time']);//起始时间
            $money_info['transaction_des'] = "用户“{$money_info['buyer_name']}” 于  {$money_info['transaction_time']}   买入用户“{$money_info['seller_name']}”的产品“{$history_info['goods_name']}” 单价是（{$history_info['transaction_price']}）元,数量（{$history_info['transaction_history_count']}},用户“{$money_info['seller_name']}”盈利（{$money_info['receive_money']}）元";
        }elseif($platform_from == 2){
            //判断当前是取消还是，买入
            $sql = "select * from ecm_transaction where transaction_sn='{$money['transaction_sn']}'";
            $db = db();
            $transaction_info = $db->getrow($sql);
            $money_info['status_des'] ='已完成';//暂时定已经完成
            $money_info['transaction_time'] = date('Y-m-d H:i:s',$transaction_info['transaction_time']);
            if ($transaction_info['transaction_status']==-1) {
                $money_info['platform_from_des']='茶通：产品取消退款';
                $money_info['transaction_des'] = "用户“{$member_info['user_name']}”于{$money_info['transaction_time']} 取消产品“{$transaction_info['goods_name']}” 单价是（{$transaction_info['transaction_price']}）元，数量（{$transaction_info['transaction_count']}）,用户“{$member_info['user_name']}”增加退款（{$money_info['receive_money']}）元。";
            }else{
                $money_info['platform_from_des']='茶通：申请购买产品扣款';
                $money_info['transaction_des'] = "用户“{$member_info['user_name']}”于{$money_info['transaction_time']} 申请买入产品“{$transaction_info['goods_name']}” 单价是（{$transaction_info['transaction_price']}）元，数量（{$transaction_info['transaction_count']}）,用户“{$member_info['user_name']}”减少（{$money_info['receive_money']}）元。";
            }
            $money_info['buyer_name'] = $member_info['user_name'];
            $money_info['seller_name'] = '福禄仓投资集团';
        }elseif($platform_from  == 3){
            $money_info['status_des'] ='已完成';//
            //判断是充值还是提现？
            $recharge_info = $recharge_mod ->get($sn);
            $money_info['transaction_time'] = date('Y-m-d H:i:s',$recharge_info['first_time']);
            //1提现
            if ($recharge_info ['recharge_status']==1) {
                $money_info['platform_from_des']='个人充值：提现'; 

                $money_info['transaction_des'] = "用户“{$member_info['user_name']}”于{$money_info['transaction_time']} 提现（{$recharge_info['pay_money']}）元,用户“{$member_info['user_name']}”减少（{$money_info['receive_money']}）元。";

            }elseif ($recharge_info ['recharge_status']==0) {
                $money_info['platform_from_des']='个人充值：充值'; 

                $money_info['transaction_des'] = "用户“{$member_info['user_name']}”于{$money_info['transaction_time']} 充值（{$recharge_info['pay_money']}）元,用户“{$member_info['user_name']}”增加（{$money_info['receive_money']}）元。";

            }
            $money_info['buyer_name'] = $member_info['user_name'];
            $money_info['seller_name'] = '福禄仓投资集团';
        
        }
        $money_info['pay_time'] = date('Y-m-d H:i:s',$money_info['pay_time']);
        $this->assign('money_info',$money_info);
        $this->assign('user_info',$member_info);
        $this->display('money_info.index.html');
    }
    /**
     * [edit_denied_reason 查阅的时候，编辑拒绝提现的理由]
     * @return [type] [description]
     */
    function edit_denied_reason(){
        $recharge_id =$_POST['recharge_id'];
        $recharge_mod = m('recharge_log');
        /*获取当前的订单状态*/
        $recharge_info = $recharge_mod ->get($recharge_id);
        $comments = trim($_POST['comments']);
        if ($recharge_info['pay_status'] == 81 && $recharge_info['recharge_status'] == 1) {
            $edit = array('comment_des'=>$comments);
            $result = $recharge_mod->edit($recharge_id,$edit); 
            if ($result) {
                $out_data = array(
                            'message'=>Lang::get('alter_success'),
                            'code'=>0,
                            'data'=>'');    
            }else{
                 $out_data = array(
                            'message'=>Lang::get('alter_fail'),
                            'code'=>1,
                            'data'=>'');  
            }
        }else{
            $out_data = array(
                            'message'=>Lang::get('alter_warring'),
                            'code'=>100,
                            'data'=>'');
        }
        echo json_encode($out_data);    
    }
    /**
     * 响应ajax请求，允许提现x
     * @return [type] [description]
     */
    function allow_withdraw(){
        $this->db = db();
        $recharge_id = $_POST['recharge_id'];
        if (empty($recharge_id)) {
            $out_data = array(
                            'message'=>Lang::get('param_null_warring'),
                            'code'=>1,
                            'data'=>'');  
        }else{
            $recharge_mod = m('recharge_log');
            $recharge_info = $recharge_mod ->get($recharge_id);    
            if ($recharge_info['pay_status'] != 80) {
                $out_data = array(
                            'message'=>Lang::get('alter_warring'),
                            'code'=>2,
                            'data'=>''); 
            }else{
                $member = m('member');
                $use_money = $member ->get_money($recharge_info['user_id']);
                $update_money = (intval($use_money*100)-intval($recharge_info['pay_money'])*100)/100;
                $money_history_mod = m('money_history');
                $recharge_log_mod = m('recharge_log');
                if ($update_money>0) {
                    //1.减少预存款余额
                    $this->start();  
                    $edit = array('use_money'=>$update_money);
                    $time = time();
                    $member_result = $member ->edit($recharge_info['user_id'],$edit);
                     //2.增加支出记录
                    $money_history_add = array(
                                                'transaction_sn'=>$recharge_id,
                                                'money_from'=>0,
                                                'transaction_type'=>1,
                                                'receive_money' => $recharge_info['pay_money'],
                                                'pay_time' => $time,
                                                'platform_from' =>3,
                                                'use_money_history'=>$use_money,
                                                'user_id'=>$recharge_info['user_id'],
                                                'comments'=>'管理员通过了您的提现审核',
                                                );
                    $money_result = $money_history_mod ->add($money_history_add);
                    //3.修改当前的订单的状态
                    $recharge_edit =array('finished_time'=>$time,'comment_des'=>'提现申请被通过','pay_status'=>40);
                    $recharge_result = $recharge_log_mod ->edit($recharge_id,$recharge_edit);
                    if ($member_result && $money_result &&  $recharge_result) {
                        $this->commit();
                        $out_data = array(
                            'message'=>Lang::get('success'),
                            'code'=>0,
                            'data'=>''); 
                    }else{
                        $this->cancel();
                        $out_data = array(
                            'message'=>Lang::get('fail'),
                            'code'=>4,
                            'data'=>''); 
                    }

                }else{
                    $out_data = array(
                            'message'=>Lang::get('money_warring'),
                            'code'=>3,
                            'data'=>''); 
                }           
                //4.两次都成功事务提交              
            }
        }
        echo json_encode($out_data);

    }

    function start(){
        $this->db->query("SET AUTOCOMMIT=0");
    }
    function cancel(){
        $this->db->query('ROLLBACK');
        $this->db->query("SET AUTOCOMMIT=1");
    }
    function commit(){
        $this->db->query('COMMIT');
        $this->db->query("SET AUTOCOMMIT=1");
    }
    /**
     * [denied_withdraw 拒绝提现]
     * @return [type] [description]
     */
    function denied_withdraw(){
        $recharge_id =$_POST['recharge_id'];
        $recharge_mod = m('recharge_log');
        /*获取当前的订单状态*/
        $recharge_info = $recharge_mod ->get($recharge_id);
        $comments = trim($_POST['comments']);
        if ($recharge_info['pay_status'] == 80 && $recharge_info['recharge_status'] == 1) {
            $edit = array('comment_des'=>$comments,'pay_status'=>81,'finished_time'=>time());
            $result = $recharge_mod->edit($recharge_id,$edit); 
            if ($result) {
                $out_data = array(
                            'message'=>Lang::get('alter_success'),
                            'code'=>0,
                            'data'=>'');    
            }else{
                 $out_data = array(
                            'message'=>Lang::get('alter_fail'),
                            'code'=>1,
                            'data'=>'');  
            }
        }else{
            $out_data = array(
                            'message'=>Lang::get('alter_warring'),
                            'code'=>100,
                            'data'=>'');
        }
        echo json_encode($out_data);    
    }


}
?>
