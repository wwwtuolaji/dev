<?php

/**
 *    Desc
 *
 * @author    Garbin
 * @usage    none
 */
class DepositApp extends MemberbaseApp
{
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
            'fields' => 'use_money,money_is_open,user_name'
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
        /* 当前所处子菜单 */
        $this->_curmenu('deposit');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->display('deposit.index.html');
    }

    function recharge()
    {
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
            array(
                'name' => 'transaction_history',
                'url' => 'index.php?app=deposit&amp;act=transaction_history',
            ),
            array(
                'name' => 'financial_details',
                'url' => 'index.php?app=deposit&amp;act=financial_details',
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
                
                $recharge_log = m('recharge_log');
                $rand_math = mt_rand(0,10000);
                //生成临时充值订单
                $recharge_arr['recharge_sn']    = time()+$rand_math;
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
          /* 当前所处子菜单 */
        $this->_curmenu('withdraw');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
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
        /* 当前所处子菜单 */
        $this->_curmenu('account_setting');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $user_id     =  $this->visitor->get('user_id');
        $member_mod = m("member");
        $user_info = $member_mod ->get(array('conditions'=>"user_id=$user_id",'fields'=>"real_name,user_name,email,phone_mob,money_is_open"));
        $this->assign('user_info',$user_info);

        $this->display('account_setting.index.html');
    }
    /*响应ajax请求*/
    function dialog_set_account(){
        $recarge_mod = m('recharge_log');
        $user_id = $this->visitor->get("user_id");
        $html=$recarge_mod ->get_dialog_html( $user_id );
        echo json_encode($html);

    }
    /*获取手机验证码请求暂时未做*/
    function get_phonecode(){
        echo "string";
    }

    function send_email(){
        $recarge_mod = m('recharge_log');
        $user_id = $this ->visitor ->get("user_id");
        $member_info = $recarge_mod ->get_member_info($user_id);
        $email = $member_info['email'];


    }
}