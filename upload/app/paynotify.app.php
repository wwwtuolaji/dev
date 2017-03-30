<?php

/**
 *    支付网关通知接口
 *
 *    @author    Garbin
 *    @usage    none
 */
class PaynotifyApp extends MallbaseApp
{
    /**
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //哪个订单
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* 没有该订单 */
            $this->show_warning('forbidden');

            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id={$order_info['seller_id']}");
        /*var_dump($payment_info);
        die;*/
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');

            return;
        }

        /* 调用相应的支付方式 */
        //获得一个对应的支付方式的对象
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $this->show_warning($payment->get_error());

            return;
        }
        //jjc 增加用户是否支付成功的字段，用于判断用户是否支付过
        $db=& db();
        $sql="update ecm_order set pay_have='1',status='11' where order_id =".$order_info['order_id'];

        $db->query($sql);
        
        #TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
        //$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        /* 只有支付时会使用到return_url，所以这里显示的信息是支付成功的提示信息 */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }
      /**
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function index_new()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //哪个订单
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }

        /* 获取订单信息 */
        $recharge_mod =& m('recharge_log');
        $recharge_arr  = $recharge_mod->get($order_id);
        if (empty($recharge_arr))
        {
            /* 没有该订单 */
            $this->show_warning('forbidden');

            return;
        }

        $db = db();
        $sql = "select user_id,user_name,email from ecm_member where user_id = '{$recharge_arr['user_id']}' ";
        $user_info = $db->getrow($sql);

        $order_info['order_id'] = $order_id;
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
        /*var_dump($payment_info);
        die;*/
        $payment_info['config']='a:5:{s:14:"alipay_account";s:15:"ppb_kf1@126.com";s:10:"alipay_key";s:32:"7j0f1lc72we44pwwr9wf6087wzis0h9l";s:14:"alipay_partner";s:16:"2088521075622716";s:14:"alipay_service";s:25:"create_direct_pay_by_user";s:5:"pcode";s:0:"";}';
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');

            return;
        }

        /* 调用相应的支付方式 */
        //获得一个对应的支付方式的对象
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $this->show_warning($payment->get_error());

            return;
        }
        //jjc 增加用户是否支付成功的字段，用于判断用户是否支付过
       /* $db=& db();
        $sql="update ecm_order set pay_have='1',status='11' where order_id =".$order_info['order_id'];

        $db->query($sql);*/
        if (empty($user_info)) {
            dump('用户已经信息过期请重新登陆！');
        }
        $db=& db();
        $money_mod = m('money_history');
         //检查该订单是否已经插入过,非法操作
        $have_add = $money_mod ->get(array('fields'=>'money_history_id','conditions'=>"transaction_sn ='$order_id'AND money_from ='1' AND platform_from ='3'"));
        if (!$have_add) {
            $get_sql = "select use_money from ecm_member where user_id = '{$user_info['user_id']}'";
            $use_money = $db->getone($get_sql);
            $use_money = intval($use_money*100)+ intval($recharge_arr['pay_money']*100);
            $use_money = $use_money/100;
            $sql="update ecm_member set use_money = '$use_money' where user_id = '{$user_info['user_id']}'";
            $db->query($sql);

            $add = array(
                        'transaction_sn'     => $order_id,//充值id
                        'money_from'         => 1,//0预存款，1支付宝, 2微信
                        'transaction_type'   => 0,//0,收入 1支出
                        'receive_money'      => $recharge_arr['pay_money'],//收入或减去的金额
                        'pay_time'           => time(),//支付时间
                        'platform_from'      => 3,//0,茶通历史表，1，商城 2，transaction3,个人充值
                        'use_money_history'  => $use_money,//当前的金额
                        'user_id'            => $user_info['user_id'],//user_id
                        'comments'           => '个人充值',//备注
                        );
            $money_mod ->add($add);

            //修改充值记录 
            #TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
            
        }
            $this->_change_order_status_new($order_id, $order_info['extension'], $notify_result);
          //生成历史记录
    
       
        /* 只有支付时会使用到return_url，所以这里显示的信息是支付成功的提示信息 */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }

    /**
     *    支付完成后，外部网关的通知地址，在此会进行订单状态的改变，这里严格验证，改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function notify()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else
        {
            $order_id = intval($_GET['order_id']);
        }
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('no_such_order');
            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* 没有该订单 */
            $this->show_warning('no_such_order');
            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id={$order_info['seller_id']}");
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');
            return;
        }

        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $payment->verify_result(false);
            return;
        }

        //改变订单状态
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);

        if ($notify_result['target'] == ORDER_ACCEPTED)
        {
            /* 发送邮件给卖家，提醒付款成功 */
            $model_member =& m('member');
            $seller_info  = $model_member->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* 同步发送 */
            $this->_sendmail(true);
        }
    }
    /**
     * @author jjc
     * @des 支付充值完成后，外部网关的通知地址，在此会进行订单状态的改变，这里严格验证，改变订单状态
     * @return [type] [description]
     */
    function notify_new()
    {

        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else
        {
            $order_id = intval($_GET['order_id']);
        }
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('no_such_order');
            return;
        }

        /* 获取订单信息 */
        $recharge_log =& m('recharge_log');
        $recharge_arr  = $recharge_log->get($order_id);

        if (empty($recharge_arr))
        {
            /* 没有该订单 */
            $this->show_warning('no_such_order111');
            return;
        }

        $db = db();
        $sql = "select user_id,user_name,email from ecm_member where user_id = '{$recharge_arr['user_id']}' ";
        $user_info = $db->getrow($sql);

        $order_info['order_id'] = $order_id;
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
        //$model_payment =& m('payment');
        $payment_info['config']='a:5:{s:14:"alipay_account";s:15:"ppb_kf1@126.com";s:10:"alipay_key";s:32:"7j0f1lc72we44pwwr9wf6087wzis0h9l";s:14:"alipay_partner";s:16:"2088521075622716";s:14:"alipay_service";s:25:"create_direct_pay_by_user";s:5:"pcode";s:0:"";}';
        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            echo "支付失败";
            /* 支付失败 */
            $payment->verify_result(false);
            return;
        }

        //改变订单状态
        $this->_change_order_status_new($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);

        if ($notify_result['target'] == ORDER_ACCEPTED)
        {
            /* 发送邮件给卖家，提醒付款成功 */
            $model_member =& m('member');
            $seller_info  = $model_member->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* 同步发送 */
            $this->_sendmail(true);
        }   
    }
      /**
     *    改变订单状态
     *
     *    @author    jjc
     *    @param     int $order_id 充值记录的id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status_new($order_id, $order_type, $notify_result)
    {
        /* 将验证结果传递给订单类型处理 */
       //没用注释 
        $order_type  =& ot($order_type);
        $order_type->respond_notify_new($order_id, $notify_result);    //响应通知
    }

    /**
     *    改变订单状态
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result)
    {
        /* 将验证结果传递给订单类型处理 */
        $order_type  =& ot($order_type);
        $order_type->respond_notify($order_id, $notify_result);    //响应通知
    }

  
}

?>
