<?php

/**
 *    Desc
 *
 * @author    Garbin
 * @usage    none
 */
class DepositApp extends MemberbaseApp
{
    private $recharege_mod;
    private $user_id;
    function __construct(){
        parent::__construct();
        $this->user_id = $this->visitor->get('user_id');
        $this->recharge_mod = m('recharge_log');
    }
    /**
     *    预存款
     *
     * @author    jjc
     * @return    void
     */
    function deposit()
    {
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            LANG::get('deposit')
        );

        /* 当前所处子菜单 */
        $this->_curmenu('deposit');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        /*获取当前用户的金额*/
        $member_mod = m('member');
        $user_id = $this->visitor->get('user_id');
        $data = array(
            'conditions' => 'user_id=' . $user_id,
            'fields' => 'money_is_open,user_name'
        );
        //获取银行卡相关信息
        $bank_mod = m("bank");
     
        $bank_count=$bank_mod ->get_count($user_id);
        if ($bank_count) {
            $bank_arr  =$bank_mod ->getall_list($user_id);
            $this->assign('bank_arr',$bank_arr);  
        }
        $this->assign('bank_count',$bank_count);    
        $member_info = $member_mod->find($data);
        if ($member_info) {
            $this->assign('member_info', $member_info[$user_id]);
        }
        $recharge_log = m('recharge_log');
        $use_money = $recharge_log->get_use_money($user_id);
        $not_use_money = $recharge_log->get_not_use_money($user_id);
        $use_money = number_format($use_money,2);
        $not_use_money = number_format($not_use_money,2);
        $this->assign('use_money',$use_money);
        $this->assign('not_use_money',$not_use_money);
        /* 当前所处子菜单 */
        $this->_curmenu('deposit');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->display('deposit.index.html');
    }

    function recharge()
    {   
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('recharge')
        );
        /* 当前所处子菜单 */
        $this->_curmenu('recharge');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->display('recharge.index.html');

    }

    /**
     *    三级菜单
     *
     * @author    jjc
     * @return    void
     */
    function _get_member_submenu()
    {
        $submenus = array(
            array(
                'name' => 'deposit',
                'url' => 'index.php?app=deposit&amp;act=deposit',
            ),
            array(
                'name' => 'account_setting',
                'url' => 'index.php?app=deposit&amp;act=account_setting',
            ),
          /*  array(
                'name' => 'transaction_history',
                'url' => 'index.php?app=deposit&amp;act=transaction_history',
            ),*/
            array(
                'name' => 'financial_details',
                'url' => 'index.php?app=deposit&amp;act=financial_details',
            ),
            array(
                'name' => 'drawlist',
                'url' => 'index.php?app=deposit&amp;act=drawlist',
            ),

        );
        if (!empty($_GET['act']) && $_GET['act'] == 'recharge') {
            $submenus[] = array(
                'name' => 'recharge',
                'url' => 'index.php?app=deposit&amp;act=recharge',
            );
        }
        
        if (!empty($_GET['act']) && $_GET['act'] == 'withdraw') {
            $submenus[] = array(
                'name' => 'withdraw',
                'url' => 'index.php?app=deposit&amp;act=withdraw',
            );
        }
         if (!empty($_GET['act']) && $_GET['act'] == 'withdraw_confirm') {
            $submenus[] = array(
                'name' => 'withdraw_confirm',
                'url' => 'index.php?app=deposit&amp;act=withdraw_confirm',
            );
        }
        if (!empty($_GET['act']) && $_GET['act'] == 'record') {
            $submenus[] = array(
                'name' => 'record',
                'url' => 'index.php?app=deposit&amp;act=record',
            );
        }
        if (!empty($_GET['act']) && $_GET['act'] == 'record_drawlist_aply') {
            $submenus[] = array(
                'name' => 'record',
                'url' => 'index.php?app=deposit&amp;act=record_drawlist_aply',
            );
        }
        
        if ($this->_feed_enabled) {
            $submenus[] = array(
                'name' => 'feed_settings',
                'url' => 'index.php?app=member&amp;act=feed_settings',
            );
        }

        return $submenus;
    }

    /*处理阿里支付*/
    function alipay_deposit()
    {
        if ($_POST) {
            $user_info = $this->visitor->get();
            if (empty($user_info )) {
              $this->show_warning('请优先登录');
              return;
            }
            if ($_POST['payment_code'] == 'alipay') {
                if (!is_numeric($_POST['money'])) {
                    $this->show_warning('输入金额不合法');
                    return;
                }
                $db = db();
                $get_count_money = "select count(*) from ecm_recharge_log";
                $get_count_money = $db->getone($get_count_money); 
                $order_rand = $get_count_money + time(); 
                $recharge_log = m('recharge_log');
                //生成临时充值订单
                $recharge_arr['recharge_sn']    = $order_rand;
                $recharge_arr['pay_money']      = $_POST['money'];
                $recharge_arr['pay_account']    = '暂无';//充值账户
                $recharge_arr['pay_method']     = 1;//支付方式1，支付宝，2微信
                $recharge_arr['pay_status']     = 11;//11:等待买家付款;20:买家已付款，等待卖家发货;40:交易成功;0:交易取消；
                $recharge_arr['comment_des']    = $_POST['remark'];
                $recharge_arr['pay_status']     = 0;//-1充值失败;0:充值当中；1充值成功；
                $recharge_arr['first_time']     = time();
                $recharge_arr['finished_time']  = 0;
                $recharge_arr['user_id']        = $user_info['user_id'];
                $pay_id = $recharge_log->add($recharge_arr);
           
                if ($pay_id!==false) {
                    $order_info['order_id'] = $pay_id;
                    $order_info['order_sn'] = $recharge_arr['recharge_sn'];
                    $order_info['type'] = 'material';
                    $order_info['extension'] = 'normal';
                    $order_info['seller_id'] = -1;
                    $order_info['seller_name'] = '保定市福禄仓货币充值中心';
                    $order_info['buyer_id'] = $user_info['user_id'];
                    $order_info['buyer_name'] = $user_info['user_name'];
                    $order_info['buyer_email'] = $user_info['email'];
                    $order_info['status'] = $recharge_arr['pay_status'];
                    $order_info['add_time'] =  $recharge_arr['first_time'];
                    $order_info['payment_id'] = -1;//这个payment_id 没用不知道什么情况待定！
                    $order_info['payment_name'] = '支付宝';
                    $order_info['payment_code'] = 'alipay';
                    $order_info['out_trade_sn'] = $recharge_arr['recharge_sn'];
                    $order_info['pay_time'] = time();
                    $order_info['pay_message'] = '';
                    $order_info['ship_time'] = '';
                    $order_info['invoice_no'] = '';
                    $order_info['finished_time'] = 0;
                    $order_info['goods_amount'] = $recharge_arr['pay_money'];
                    $order_info['discount'] = 0.00;//折扣价
                    $order_info['order_amount'] = $recharge_arr['pay_money'];//
                    $order_info['evaluation_status'] = 0;
                    $order_info['evaluation_time'] = 0;
                    $order_info['anonymous'] = 0;
                    $order_info['postscript'] = '';
                    $this->goto_pay($order_info);   
                }
                             
                //组装支付的数据
        }
      }
    }
    function goto_pay($order_info){
      /*$url= ROOT_PATH . '/includes/payments/alipay_new/AopSdk.php';
      echo "$url";
      require  include($url);*/
        $payment_info['config']='a:5:{s:14:"alipay_account";s:15:"ppb_kf1@126.com";s:10:"alipay_key";s:32:"7j0f1lc72we44pwwr9wf6087wzis0h9l";s:14:"alipay_partner";s:16:"2088521075622716";s:14:"alipay_service";s:25:"create_direct_pay_by_user";s:5:"pcode";s:0:"";}';
        $payment = $this->_get_payment('alipay', $payment_info);
             
        //生成当前充值的order_id 来源充值记录表
        $payment_form = $payment->get_payform_new($order_info);
         /* 线下付款的 */
        if (!$payment_info['online'])
        {
          $this->_curlocal(
          Lang::get('post_pay_message')
          );
        }
        /*dump($payment_form);*/
        /* 跳转到真实收银台 */
        $this->_config_seo('title', Lang::get('cashier'));
         Lang::get('post_pay_message');
       
        $this->assign('payform', $payment_form);
        $this->assign('payment', $payment_info);
        $this->assign('order', $order_info);
        header('Content-Type:text/html;charset=' . CHARSET);
        $this->display('cashier.payform.html');
     
    }
    function wechat_deposit(){
        dump('此功能正在开发中，即将上线。。。');
    }
    /**
     * 提现
     * @return [type] [description]
     */
    function withdraw(){
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('withdraw')
        );
          /* 当前所处子菜单 */
        $this->_curmenu('withdraw');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        //获取当前用户的银行卡
        $bank_mod = m('bank');
        $user_id = $this ->visitor ->get('user_id');
        $recharge_log = m('recharge_log');
        $use_money = $recharge_log->get_use_money($user_id);
        $this->assign('money',$use_money);
        $bank_arr = $bank_mod ->find(array('conditions'=>"user_id ='$user_id' and bank_status>-1"));
        $this -> assign('bank_arr',$bank_arr);
       
        $this->display('withdraw.index.html');
    }
    /**
     * [pay_status 修改支付状态]
     * @return [type] [description]
     */
    function pay_status(){

        $user_id=$this->visitor->get('user_id');
        if($_GET['status']=="on"||$_GET['status']=="off"){
            $db = db($_GET['status']=="on");
            if ($_GET['status']=="on") {
                $member_mod = m('member');
                $data = array('money_is_open'=>1);
                $member_mod->edit($user_id,$data);
            }else{
                 $member_mod = m('member');
                $data = array('money_is_open'=>0);
                $member_mod->edit($user_id,$data);
            }
            $this->show_message('修改成功',
                   'back_list',    'index.php?app=deposit&act=deposit'
                  );
             

        }else{
            $this->show_warning("请求异常");
        }

    }

    function account_setting()
    {  
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('account_setting')
        );
        if ($_POST) {
            $code = addslashes($_POST['code']);
            $result = $this->_check_code($code);
            if (!$result) {
                $this->show_warning("验证码输入超时请检查");
                return;
            }

            /*过滤数据*/
            $real_name = addslashes($_POST['real_name']);
            $real_name = trim($real_name);
            $password  = addslashes($_POST['password']);
            $password_confirm  = addslashes($_POST['password_confirm']);
            if (!empty($password) && $password!=$password_confirm) {
                $this->show_warning("密码设置异常");
                return;
            }

            if ($_POST['pay_status']=="on") {
                $money_is_open = 1;
            }else{
                 $money_is_open = 0;
            }
            //0是email 修改密码，1是通过手机
            if ($_POST['codeType'] =='email') {
                $validate_method = 0;
            }else{
                $validate_method = 1;
            }
            $user_id = $this->visitor->get('user_id');
            if (!empty($user_id)) {
                if(empty($password)){
                    $edit = array(  'real_name' => $real_name,
                                   
                                    'money_is_open' => $money_is_open 
                                );
                }else{
                     $edit = array(  'real_name' => $real_name,
                                      'pay_pwd'   => md5($password),
                                     'money_is_open' => $money_is_open 
                                );  
                }
                $member_mod = m('member');
                $member_mod->edit($user_id,$edit);
                /*增加修改记录*/
                $alter_log = m('alterpwd_log');
                $add = array('alter_method' => $validate_method );
                $alter_id = $alter_log->add($add);
                if ($alter_id) {
                    $this->show_message('修改成功',
                   'back_list',    'index.php?app=deposit&act=account_setting'
                    );                
                }else{
                     $this->show_message('修改失败',
                   'back_list',    'index.php?app=deposit&act=account_setting'
                    );    
                }
                return;

            }
            $this->show_warning('请重新登录');
            return;
        }
        $recarge_mod = m('recharge_log');
        /* 当前所处子菜单 */
        $this->_curmenu('account_setting');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $user_id     =  $this->visitor->get('user_id');
        $user_info=$recarge_mod->get_member_info($user_id);
        $data['phone_mob'] = substr_replace($user_info['phone_mob'],"*****",3,5);
        $data['email']     = substr_replace($user_info['email'],"****",3,5);
        $this->assign('contact_way',$data);
        $member_mod = m("member");
        $user_info = $member_mod ->get(array('conditions'=>"user_id=$user_id",'fields'=>"real_name,user_name,email,phone_mob,money_is_open"));
        $this->assign('user_info',$user_info);

        $this->display('account_setting.index.html');
    }
    /*响应ajax请求*/
    function dialog_set_account(){
        $recarge_mod = m('recharge_log');
        $user_id = $this->visitor->get("user_id");
        $user_info=$recarge_mod->get_member_info($user_id);
        $data['phone_mob'] = substr_replace($user_info['phone_mob'],"*****",3,5);
        $data['email']     = substr_replace($user_info['email'],"****",3,5);
        $arr = array('code'=>0,
                    'message'=>'请求成功',
                    'data'=>$data);
        echo json_encode($arr);

    }
    /*获取手机验证码请求暂时未做,做的时候验证记得改写！*/
    function get_phonecode(){
        echo "string";
    }

    function send_email(){
        $recarge_mod = m('recharge_log');
        $user_id = $this ->visitor ->get("user_id");
        if (empty($user_id)) {
            $array = array('code' => 2,
                            'message' => "用户过期",
                            'data'  => $mailer->errors);
            echo json_encode($array);
            return;
        }
        $member_info = $recarge_mod ->get_member_info($user_id);
        $rand = mt_rand(100000,999999);
        $_SESSION['email_code_'.$user_id]= array('code'=>$rand,'time'=>time());
        $email = $member_info['email'];
        /* 使用mailer类 */
        import('mailer.lib');
        $email_from = Conf::get('site_name');
        $email_type = Conf::get('email_type');
        $email_host = Conf::get('email_host');
        $email_port = Conf::get('email_port');
        $email_addr = Conf::get('email_addr');
        $email_id   = Conf::get('email_id');
        $email_pass = Conf::get('email_pass');
        $email_to   = $email;
        /*获取支付的验证码*/
        if ($_POST['type']=='get_pay_code') {
            $email_subject = '支付验证码'; 
        }else{
            $email_subject = '修改支付密码';  
        }
        $email_content = "【福禄仓投资集团】您的验证码是 $rand ,不要告诉别人哦!有效时间5分钟！";  
        $mailer = new Mailer($email_from, $email_addr, $email_type, $email_host, $email_port, $email_id, $email_pass);
        $mail_result = $mailer->send($email_to, $email_subject, $email_content, CHARSET, 1);
        if ($mail_result)
        {
            $array = array('code' => "0",
                            'message' => "请求成功",
                            'data'  => '');
        }
        else
        { 
            $array = array('code' => "1",
                            'message' => "请检查邮箱账号",
                            'data'  => $mailer->errors);
            
        }
        echo json_encode($array);

    }


    function check_code(){
        if (!empty($_POST['email_code'])) {
            $_POST['email_code'] = addslashes($_POST['email_code']);
            $email_result = $this->_check_code($_POST['email_code']);
            if ($email_result ) {
                $out_data = array(  'code' =>0,
                                    'message'=>'邮箱验证成功',
                                    'data' =>array('code'=>$_POST['email_code'],'code_type'=>'email')
                                    );
                echo json_encode($out_data);
                return;
            }
        }
        if (!empty($_POST['phone_code'])) {
             $_POST['phone_code'] = addslashes($_POST['phone_code']);
             $phone_result = $this->_check_code($_POST['phone_code']);
             if ($phone_result ) {
                $out_data = array(  'code' =>0,
                                    'message'=>'手机验证成功',
                                    'data' =>array('code'=>$_POST['phone_code'],'code_type'=>'phone')
                                    );
                echo json_encode($out_data);
                return;
            }

        }
     
        $out_data = array(  'code' => 1,
                            'message'=>'验证码输入错误请检查',
                            'data' =>'');
        echo json_encode($out_data);

    }

    function _check_code($code){
        $user_id = $this->visitor->get('user_id');
        if (empty($_SESSION['email_code_'.$user_id])) {
            return false;
        }
        /*超出验证时间*/  
        if(time() - $_SESSION['email_code_'.$user_id]['time']>60*5){
            return false;
        }
        if($_SESSION['email_code_'.$user_id]['code'] == $code){
            return true;
        }

    }
    /**
     * [financial_details 交易记录]
     * @return [type] [description]
     */
    function financial_details(){
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('financial_details')
        );
         /* 当前所处子菜单 */
        $this->_curmenu('financial_details');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');  
        //数据获取
        $this->import_resource(array(
            'script' => 'My97DatePicker/WdatePicker.js,jquery-form.js,echart/build/dist/echarts.js',
        ));
        $page = $this->_get_page(9);
        //获取财务明细
        $user_id  = $this->user_id;
        $money_mod = m('money_history');
        $money_array = $money_mod ->get_all($user_id,$page['limit']);
        $page['item_count'] = $money_mod->getCount_money($user_id);
        $details['income'] = $money_mod -> get_income($user_id);
        $details['expenditure'] = $money_mod ->get_expenditure($user_id);
        $this ->assign('details',$details);
        $db = db();
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
               if($money_info['platform_from']==0){
                $sql = "select a.transaction_status from ecm_transaction as a where a.transaction_sn = '{$money_info['transaction_sn']}'";
                $history_info = $db ->getone($sql);
                $money_array[$key]['is_cancel'] = $history_info;
               }
            }
            //0,收入 1支出
            $money_array[$key]['pay_time_des'] = date('Y-m-d H:i:s',$money['pay_time']);
            $money_array[$key]['money_history_id_des'] =100000 + $money['money_history_id'];
            //0,茶通历史表，1，商城 2，transactionc茶通金额取消使用此项3,个人充值
        }
        $this->_format_page($page);
        /*dump($page);*/
        $this->assign('page_info',$page);
        //获取总收入
       /* dump($money_array);*/
        $this ->assign('money_array',$money_array);
        $this->display('financial_details.html');

    }
    /**
     * [record 财务明细详情]
     * @return [type] [description]
     */
    function record(){
      
        if (empty($_GET['tradeNo'])) {
           $this->show_warning('该订单不存在');
           return;
        }
          $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('record')
        );
        /* 当前所处子菜单 */
        $this->_curmenu('record');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');  
        $trade_no = $_GET['tradeNo'];
        $money_mod = m('money_history');
        $user_id = $this->user_id;
        $get = array('conditions' => "user_id ='$user_id' and money_history_id = '$trade_no'");
        $money_info = $money_mod -> get($get);
        if (empty($money_info)) {
            $this->show_warning('该订单不存在');
            return;
        }
        $member_mod = m("member");
        $transaction_mod = m('transaction');
        $db = db();
        //0,茶通历史表，1，商城 2，transactionc茶通金额取消使用此项3,个人充值
        if ($money_info['platform_from']==3) {
            $recharge_mod = m('recharge_log');
            $history_info = $recharge_mod ->get($money_info['transaction_sn']); 
            $history_info['first_time_des'] = date('Y-m-d H:i:s',$history_info['first_time']); 
            $history_info['finished_time_des'] = date('Y-m-d H:i:s',$history_info['finished_time']);               
        }elseif($money_info['platform_from']==2){
            $sql = "select a.*,b.user_name from ecm_transaction as a inner join ecm_member as b on a.user_id = b.user_id where a.transaction_sn = '{$money_info['transaction_sn']}'";
            $history_info = $db ->getrow($sql);
            if ($history_info['transaction_status']==-1) {
               $history_info['user_name'] ='';
            }
            $history_info['transaction_time_des'] = date('Y-m-d H:i:s',$history_info['transaction_time']);
        }elseif($money_info['platform_from']==1){
            //商城待定
            $history_info = array();
        }elseif($money_info['platform_from']==0){
            //判断是收入还是支出，如果是支出就获取收款人的姓名，
            if($money_info['transaction_type']==1){
                $sql = "select a.*,c.user_name from ecm_transaction_history as a inner join ecm_transaction as b on a.sell_sn_id = b.transaction_sn inner join ecm_member as c on b.user_id = c.user_id where a.history_id = '{$money_info['transaction_sn']}'";
                /* dump($sql);*/
                $history_info = $db->getrow($sql);
            }else{
                $sql = "select a.*,c.user_name from ecm_transaction_history as a inner join ecm_transaction as b on a.buy_sn_id = b.transaction_sn inner join ecm_member as c on b.user_id = c.user_id where a.history_id = '{$money_info['transaction_sn']}'";
                $history_info = $db->getrow($sql);
            }
           
            $history_info = $db ->getrow($sql);
            $history_info['transaction_time_des'] = date('Y-m-d H:i:s',$history_info['transaction_time']);
        }
         //0预存款，1支付宝, 2微信
        if ($money_info['money_from'] == 1) {
            $money_info['money_from_des']='支付宝';
        }elseif($money_info['money_from'] == 2){
            $money_info['money_from_des']='微信'; 
        }elseif($money_info['money_from'] == 0){
            $money_info['money_from_des']='预存款'; 
        }
        $money_info['pay_time_des'] = date('Y-m-d H:i:s',$money_info['pay_time']);
        $this ->assign('money_info',$money_info);
       /* dump($history_info);*/
        $this ->assign('history_info',$history_info);
        $this->display('record.index.html');
    }
    function record_drawlist_aply(){
        if (empty($_GET['tradeNo'])) {
           $this->show_warning('该订单不存在');
           return;
        }
          $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('record')
        );
        /* 当前所处子菜单 */
        $this->_curmenu('record');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');  
        $trade_no = $_GET['tradeNo'];
        $recharge_mod = m('recharge_log');
        $recharge_info = $recharge_mod->get($trade_no);
        $recharge_info['pay_status_des'] = Lang::get("pay_status_".$recharge_info['pay_status']);
        if (empty($recharge_info)) {
           $this->show_warning("该订单不存在");
           return;
        }
        $money_info = array('platform_from'     =>1,
                            'money_from_des'    =>'银行卡',
                            'transaction_type'  =>1,
                            'receive_money'     =>$recharge_info['pay_money'],
                            'platform_from'     =>'3',
                            'comments'          =>$recharge_info['comment_des']
                            );
        $history_info['first_time_des']=date('Y-m-d H:i:s',$recharge_info['first_time']);
       
        $this->assign('recharge_arr',$recharge_info);
        $this->assign('money_info',$money_info);
        $this->assign('history_info',$history_info);
        $this->display('record.index.html');

    }
    /*按日期查询*/
    function recordlist(){
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('financial_details')
        );
         /* 当前所处子菜单 */
        $this->_curmenu('financial_details');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');  
        //数据获取
        $this->import_resource(array(
            'script' => 'My97DatePicker/WdatePicker.js,jquery-form.js,echart/build/dist/echarts.js',
        ));
        $page = $this->_get_page(9);
        //获取财务明细
        $user_id  = $this->user_id;
        $money_mod = m('money_history');
        $add_time_from = empty($_GET['add_time_from'])?strtotime("1970-01-01"):strtotime($_GET['add_time_from']);
        $add_time_to   = empty($_GET['add_time_to'])?time():strtotime($_GET['add_time_to']);
        $where = "AND pay_time > '$add_time_from' AND pay_time < '$add_time_to'";
        $money_array = $money_mod ->get_all($user_id,$page['limit'],$where);

        $page['item_count'] = $money_mod->getCount_money($user_id,$where);

        $details['income'] = $money_mod -> get_income($user_id);
        $details['expenditure'] = $money_mod ->get_expenditure($user_id);
        $this ->assign('details',$details);
        $db = db();
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
               if($money_info['platform_from']==0){
                $sql = "select a.transaction_status from ecm_transaction as a where a.transaction_sn = '{$money_info['transaction_sn']}'";
                $history_info = $db ->getone($sql);
                $money_array[$key]['is_cancel'] = $history_info;
               }
            }
            //0,收入 1支出
            $money_array[$key]['pay_time_des'] = date('Y-m-d H:i:s',$money['pay_time']);
            $money_array[$key]['money_history_id_des'] =100000 + $money['money_history_id'];
            //0,茶通历史表，1，商城 2，transactionc茶通金额取消使用此项3,个人充值
        }
        $this->_format_page($page);
        /*dump($page);*/
        $this->assign('page_info',$page);
        //获取总收入
        $this ->assign('money_array',$money_array);
        $this->display('financial_details.html'); 
    }
    function withdraw_confirm(){
        $user_id = $this->visitor->get('user_id');
           $bank_mod = m('bank');
        if (empty($_GET['bid'])) {
            $bid = $_POST['bid'];
        }else{
             $bid = $_GET['bid'];
        }
        if(is_numeric($bid)){
            $get_bank = array('conditions'=>"user_id ='$user_id' AND bank_id = '$bid' AND bank_status>-1");
            $bank_info = $bank_mod ->get($get_bank);   
            if (empty($bank_info)) {
                 $this->show_warning('卡号异常请重新输入');
                 return;
            }
        }else{
            $this->show_warning('卡号异常请重新编辑');
            return;
        }
        $recharge_log = m('recharge_log');
        if (empty($_SESSION['token'])) {
           $recharge_log ->set_token();
        }
        $this->assign('token',$_SESSION['token']);
        if ($_POST) {
            //校验touken
            if(!$recharge_log -> valid_token()){
                $this->show_warning('不能重复提交');
                return;
            }
            /*校验支付密码*/
            $member_mod = m('member');
            $member_info = $member_mod->get($user_id);
            $pwd = md5($_POST['password']);
            if ($pwd != $member_info['pay_pwd']) {
                $this->show_warning('密码验证失败');
                return;
            }
            //请求成功加入recharge表做申请记录
            $db = db();
            $rechage_mod = m('recharge_log');
            $get_count_money = "select count(*) from ecm_recharge_log";
            $get_count_money = $db->getone($get_count_money); 
            $order_rand = $get_count_money + time(); 
            
            $bank_mod = m('bank');
            //再次检测传入的银行卡是否属于该用户
            $bid = intval($_POST['bid']);
            $where =array('conditions'=>"bank_id = '$bid' AND user_id = '$user_id'");
            $bank_info = $bank_mod ->get($where);
            if (empty($bank_info)) {
                $this-> $this->show_message('卡号异常',
                   'back_list',    'index.php?app=deposit&act=deposit'
                    );
                 return;
            }
            //检测当前用户的可用余额是否足够提取
            $pay_money = $_POST['user_money'];
            $use_money = $recharge_log->get_use_money($user_id);

            if ((int)(string)($pay_money*100)>(int)(string)($use_money*100)) {
                $this-> $this->show_message('余额不足，无法进行提现！',
                   'back_list',    'index.php?app=deposit&act=deposit'
                    );
                return;
            }
            $add = array('recharge_sn'  => $order_rand,
                         'pay_money'    => $pay_money,
                         'pay_account'  => intval($_POST['bid']),//提现账户id
                         'pay_method'   => 3,
                         'pay_status'   => 80,
                         'first_time'   => time(),
                         'finished_time'=> 0,
                         'comment_des'  => '请求提现',
                         'user_id'      => $user_id,
                         'recharge_status' => 1);
            $recharge_id = $rechage_mod->add($add);
            if ($recharge_id) {
                 $this->show_message('申请成功',
                   'back_list',    'index.php?app=deposit&act=drawlist'
                    );     
                 return;
            }else{
                 $this-> $this->show_message('申请失败',
                   'back_list',    'index.php?app=deposit&act=deposit'
                    );
                 return;
            }
        }
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('withdraw_confirm')
        );
          /* 当前所处子菜单 */
        $this->_curmenu('withdraw_confirm');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->assign('bank_info',$bank_info);
        $this->display('withdraw_confirm.html'); 
    }

    function drawlist(){
        if (empty($_GET['status'])) {
            $status='';
        }else{
            if ($_GET['status']=='verifing') {
                $status="AND pay_status='80'";
            }elseif($_GET['status']=='success'){
                $status="AND pay_status='40'";
            }
            
        }
        $add_time_from = empty($_GET['add_time_from'])?strtotime("1970-01-01"):strtotime($_GET['add_time_from']);
        $add_time_to   = empty($_GET['add_time_to'])?time():strtotime($_GET['add_time_to']);
        $where_time = "AND first_time > '$add_time_from' AND first_time < '$add_time_to'".$status;

        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('drawlist')
        );
         /* 当前所处子菜单 */
        $this->_curmenu('drawlist');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');  
        //数据获取
        $recharge_mod = m('recharge_log');
        $user_id = $this->visitor->get('user_id');
        $where = "user_id = '$user_id' AND recharge_status ='1'".$where_time;
        $page = $this->_get_page(9);
        $recharge_arr = $recharge_mod ->find_drawlist($where,$page['limit']);
        $page['item_count'] = $recharge_mod->get_recharge_count($where);
        $this->import_resource(array(
            'script' => 'My97DatePicker/WdatePicker.js,jquery-form.js,echart/build/dist/echarts.js',
        )); 
        /*dump($recharge_arr); */ 
        $bank_mod = m('bank');
        foreach ($recharge_arr as $key => $recharge) {
            $recharge_arr[$key]['first_time'] = date('Y-m-d H:i:s',$recharge['first_time']);
            $recharge_arr[$key]['pay_status_des'] = Lang::get("pay_status_".$recharge['pay_status']);
            $recharge_arr[$key]['bank_info'] = $bank_mod->get($recharge['pay_account']);
        }
        $this->assign('recharge_arr',$recharge_arr);
        $this->_format_page($page);
        /*dump($page);*/
        $this->assign('page_info',$page);
        $this->display('drawlist.index.html');
    }
    /**
     * [rechargelist 存款列表]
     * @return [type] [description]
     */
    function rechargelist(){
        
        $add_time_from = empty($_GET['add_time_from'])?strtotime("1970-01-01"):strtotime($_GET['add_time_from']);
        $add_time_to   = empty($_GET['add_time_to'])?time():strtotime($_GET['add_time_to']);
        $where_time = "AND first_time > '$add_time_from' AND first_time < '$add_time_to'".$status;

        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
            Lang::get('drawlist')
        );
         /* 当前所处子菜单 */
        $this->_curmenu('drawlist');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');  
        //数据获取
        $recharge_mod = m('recharge_log');
        $user_id = $this->visitor->get('user_id');
        $where = "user_id = '$user_id' AND recharge_status ='0'".$where_time;
        $page = $this->_get_page(9);
        $recharge_arr = $recharge_mod ->find_drawlist($where,$page['limit']);
        $page['item_count'] = $recharge_mod->get_recharge_count($where);
        $this->import_resource(array(
            'script' => 'My97DatePicker/WdatePicker.js,jquery-form.js,echart/build/dist/echarts.js',
        )); 
        /*dump($recharge_arr); */ 
        $bank_mod = m('bank');
        $money_history_mod =m('money_history');
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

            $money_info = $money_history_mod->get(array('conditions'=>"transaction_sn = '{$recharge['recharge_id']}' AND platform_from ='3'"));
            if(empty($money_info)){
                $this->show_warning('用户存款信息存在异常！请联系管理员！');
                return;
            }
            $recharge_arr[$key]['money_history_id'] = $money_info['money_history_id'];
        }
        $this->assign('recharge_arr',$recharge_arr);
        $this->_format_page($page);
        /*dump($page);*/
        $this->assign('page_info',$page);
        $this->display('rechargelist.index.html');
    }
    /**
     * [check_pay_pwd 检查支付密码和手机验证码]
     * @return [type] [description]
     */
    function check_pay_code_pwd(){
        $user_id = $this->visitor->get('user_id');
        $member_mod = m('member');
        $member_info = $member_mod->get($user_id);
        $pwd = md5($_POST['pay_pwd']);
        $validate_code = $_POST['validate_code'];
        $result = $this->_check_code( $validate_code);
        if ($pwd != $member_info['pay_pwd']) {
            $out_data = array('code' => 1,
                            'message'=>'支付密码验证失败',
                            'data'   =>'');

        }elseif(!$result){
             $user_id = $this->visitor->get('user_id');
            $out_data = array('code' => 2,
                            'message'=>'手机或邮箱验证码，验证失败',
                            'data'   =>'email_code_' . $user_id);
        }else{
            $out_data = array('code'=> 0,
                            'message' =>'验证成功',
                            'data'=>'');
        } 
        echo json_encode($out_data);
    }


}