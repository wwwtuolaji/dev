<?php

/**
 *    收银台控制器，其扮演的是收银员的角色，你只需要将你的订单交给收银员，收银员按订单来收银，她专注于这个过程
 *
 *    @author    Garbin
 */
class CashierApp extends ShoppingbaseApp
{
    /**
     *    根据提供的订单信息进行支付
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        /* 外部提供订单号 */
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
        /* 内部根据订单号收银,获取收多少钱，使用哪个支付接口 */
        $order_model =& m('order');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }
        /* 订单有效性判断 */
        if ($order_info['payment_code'] != 'cod' && $order_info['status'] != ORDER_PENDING)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $payment_model =& m('payment');
        if (!$order_info['payment_id'])
        {
            /* 若还没有选择支付方式，则让其选择支付方式 */
            $payments = $payment_model->get_enabled($order_info['seller_id']);
            if (empty($payments))
            {
                $this->show_warning('store_no_payment');

                return;
            }
            /* 找出配送方式，判断是否可以使用货到付款 */
            $model_extm =& m('orderextm');
            $consignee_info = $model_extm->get($order_id);
            if (!empty($consignee_info))
            {
                /* 需要配送方式 */
                $model_shipping =& m('shipping');
                $shipping_info = $model_shipping->get($consignee_info['shipping_id']);
                $cod_regions   = unserialize($shipping_info['cod_regions']);
                $cod_usable = true;//默认可用
                if (is_array($cod_regions) && !empty($cod_regions))
                {
                    /* 取得支持货到付款地区的所有下级地区 */
                    $all_regions = array();
                    $model_region =& m('region');
                    foreach ($cod_regions as $region_id => $region_name)
                    {
                        $all_regions = array_merge($all_regions, $model_region->get_descendant($region_id));
                    }

                    /* 查看订单中指定的地区是否在可货到付款的地区列表中，如果不在，则不显示货到付款的付款方式 */
                    if (!in_array($consignee_info['region_id'], $all_regions))
                    {
                        $cod_usable = false;
                    }
                }
                else
                {
                    $cod_usable = false;
                }
                if (!$cod_usable)
                {
                    /* 从列表中去除货到付款的方式 */
                    foreach ($payments as $_id => $_info)
                    {
                        if ($_info['payment_code'] == 'cod')
                        {
                            /* 如果安装并启用了货到付款，则将其从可选列表中去除 */
                            unset($payments[$_id]);
                        }
                    }
                }
            }
            $all_payments = array('online' => array(), 'offline' => array());
            foreach ($payments as $key => $payment)
            {
                if ($payment['is_online'])
                {
                    $all_payments['online'][] = $payment;
                }
                else
                {
                    $all_payments['offline'][] = $payment;
                }
            }
            $this->assign('order', $order_info);
            $this->assign('payments', $all_payments);
            $this->_curlocal(
                LANG::get('cashier')
            );

            $this->_config_seo('title', Lang::get('confirm_payment') . ' - ' . Conf::get('site_title'));
            $this->display('cashier.payment.html');
        }
        else
        {
            /* 否则直接到网关支付 */
            /* 验证支付方式是否可用，若不在白名单中，则不允许使用 */
            if (!$payment_model->in_white_list($order_info['payment_code']))
            {
                $this->show_warning('payment_disabled_by_system');

                return;
            }

            $payment_info  = $payment_model->get("payment_code = '{$order_info['payment_code']}' AND store_id={$order_info['seller_id']}");
            /* 若卖家没有启用，则不允许使用 */
            if (!$payment_info['enabled'])
            {
                $this->show_warning('payment_disabled');

                return;
            }
            /*dump($payment_info);*/
            //替换config配置数据a:5:{s:14:"alipay_account";s:6:"111112";s:10:"alipay_key";s:0:"";s:14:"alipay_partner";s:0:"";s:14:"alipay_service";s:21:"trade_create_by_buyer";s:5:"pcode";s:0:"";}
            //替换支付宝配置信息
            $payment_info['config']='a:5:{s:14:"alipay_account";s:15:"ppb_kf1@126.com";s:10:"alipay_key";s:32:"7j0f1lc72we44pwwr9wf6087wzis0h9l";s:14:"alipay_partner";s:16:"2088521075622716";s:14:"alipay_service";s:25:"create_direct_pay_by_user";s:5:"pcode";s:0:"";}';
          /*  $a=json_decode($payment_info['config']);
            var_dump($a);*/
             /*dump($payment_info);*/
            $payment    = $this->_get_payment($order_info['payment_code'], $payment_info);
         
            $payment_form = $payment->get_payform($order_info);
           
            /* 货到付款，则显示提示页面 */
            if ($payment_info['payment_code'] == 'cod')
            {
                $this->show_message('cod_order_notice',
                    'view_order',   'index.php?app=buyer_order',
                    'close_window', 'javascript:window.close();'
                );

                return;
            }
               /*dump($order_info);  */
            /* 线下付款的 */
            if (!$payment_info['online'])
            {
                $this->_curlocal(
                    Lang::get('post_pay_message')
                );
            }
            // dump($payment_form);
            /* 跳转到真实收银台 */
            $this->_config_seo('title', Lang::get('cashier'));

            $this->assign('payform', $payment_form);
            $this->assign('payment', $payment_info);
            $this->assign('order', $order_info);
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('cashier.payform.html');
        }
    }

    /**
     *    确认支付
     *
     *    @author    Garbin
     *    @return    void
     */
    function goto_pay()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $order_model =& m('order');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }

        #可能不合适
        if ($order_info['payment_id'])
        {
            $this->_goto_pay($order_id);
            return;
        }

        /* 验证支付方式 */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        if (!$payment_info)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* 保存支付方式 */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );

        /* 如果是货到付款，则改变订单状态 */
        if ($payment_info['payment_code'] == 'cod')
        {
            $edit_data['status']    =   ORDER_SUBMITTED;
        }

        $order_model->edit($order_id, $edit_data);

        /* 开始支付 */
        $this->_goto_pay($order_id);
    }

    /**
     *    线下支付消息
     *
     *    @author    Garbin
     *    @return    void
     */
    function offline_pay()
    {
        if (!IS_POST)
        {
            return;
        }
        $order_id       = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $pay_message    = isset($_POST['pay_message']) ? trim($_POST['pay_message']) : '';
        if (!$order_id)
        {
            $this->show_warning('no_such_order');
            return;
        }
        if (!$pay_message)
        {
            $this->show_warning('no_pay_message');

            return;
        }
        $order_model =& m('order');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }
        $edit_data = array(
            'pay_message' => $pay_message
        );

        $order_model->edit($order_id, $edit_data);

        /* 线下支付完成并留下pay_message,发送给卖家付款完成提示邮件 */
        $model_member =& m('member');
        $seller_info   = $model_member->get($order_info['seller_id']);
        $mail = get_mail('toseller_offline_pay_notify', array('order' => $order_info, 'pay_message' => $pay_message));
        $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

        $this->show_message('pay_message_successed',
            'view_order',   'index.php?app=buyer_order',
            'close_window', 'javascript:window.close();');
    }

    function _goto_pay($order_id)
    {
        header('Location:index.php?app=cashier&order_id=' . $order_id);
    }
    /**
     * [fulucang_money 福禄仓货币支付页面]
     * @return [type] [description]
     */
    function  fulucang_money(){
        if (empty($_POST)) {
             $this->show_message('请返回到用户订单重新支付',
                'back_list',    'index.php?app=member',
                'edit_again', 'index.php?app=buyer_order'
            );
            return;
        }
        
        //校验订单状态
        $order_mod = m('order');
        $conditions =array('conditions'=>'order_sn ='.$_POST['out_trade_no']);
        $order_info = $order_mod ->get($conditions);
        //严格校验当前的支付状态
        //11:等待买家付款;20:买家已付款，等待卖家发货;40:交易成功;0:交易取消
        if ($order_info['pay_have']==1) {
            $this->show_warning('订单已经交易！或取消请勿重复提交！','用户中心','index.php?app=member',
                '订单列表', 'index.php?app=buyer_order');
            return;
        }
        //1.获取当前用户有没有开启预存款支付
        $member_mod =m('member');
        $user_id = $this->visitor->get('user_id');
        $member_info = $member_mod->get($user_id);
        if (!$member_info['money_is_open']) {
            $this->show_warning('请优先开启预存款支付','用户中心','index.php?app=member',
                '订单列表', 'index.php?app=buyer_order');
            return;
        }
        //获取当前可用余额
        $recharge_mod = m('recharge_log');
        $use_money = $recharge_mod ->get_use_money($user_id);
        $this->assign('cur_use_money',$use_money);
        //处理要分配给前台的数据，避免数据泄露
        $out_data['out_trade_no'] = $_POST['out_trade_no'];
        //如果token为空则生成一个token
        if(!isset($_SESSION['token']) || $_SESSION['token']=='') {
          $recharge_mod->set_token();
        }
        $this->assign('token',$_SESSION['token']);
        $out_data['price']  = $_POST['price'];
        $this->assign('order_info',$out_data);
        $this->_curlocal(LANG::get('cashier'));
        $this->display('fulucang_pay.index.html');
        /***/
    }
    function transaction(){
        $user_id = $this->visitor->get('user_id');
        $recharge_mod = m('recharge_log');
        if (!$recharge_mod->valid_token()) {
            $this->show_warning('token验证失败',
                '用户中心',    'index.php?app=member',
                '订单列表', 'index.php?app=buyer_order'
            );
            return;
        }
        //验证支付密码
        $member_mod = m('member');
        $member_info = $member_mod->get($user_id);
        $pwd = md5($_POST['password']);
        if ($pwd != $member_info['pay_pwd']) {
             $this->show_warning('支付密码异常',
                '订单列表',    'index.php?app=member',
                '用户中心', 'index.php?app=buyer_order'
            );
            return;
        }

        $code = $_POST['validate_code'];
        $result_recharge = $recharge_mod ->_check_code($code,$user_id);  
        if (!$result_recharge) {
            $this->show_warning('接收的验证码过期',
                '订单列表',    'index.php?app=member',
                '用户中心', 'index.php?app=buyer_order'
            );
            return;
        }
        //检查可用余额和支付金额
        if (empty($_POST['out_trade_no'])) {
             $this->show_warning('订单业务异常',
                '订单列表',    'index.php?app=member',
                '用户中心', 'index.php?app=buyer_order'
            );
            return;
        }

        //交易信息
        $order_mod = m('order');
        $order_info = $order_mod ->getorder_info($_POST['out_trade_no']);

        //个人存款
        $recarge_mod = m('recharge_log');
        $use_money = $recharge_mod ->get_use_money($user_id);

        //需要支付的金额计算
        $price = ((int)(string)($order_info['price']*100)+(int)(string)($order_info['shipping_fee']*100))/100;
        if ((int)(string)($use_money*100)<(int)(string)($price*100)) {
             $this->show_warning('金额不足',
                '订单列表',    'index.php?app=member',
                '用户中心', 'index.php?app=buyer_order'
            );
            return;
        }
       
        //以上校验通过进行交易
        $recarge_mod ->start();//开启事务

        //1.预存款金额减少
        $use_money = $member_mod ->get_money($user_id);
        $set_money = ((int)(string)($use_money*100)-(int)(string)($price*100))/100;
        $edit_array = array('use_money'=>$set_money);
        $result_member = $member_mod ->edit($user_id,$edit_array);
        
        //2.生成交易记录
        $add_array = array(
                        'transaction_sn'     => $order_info['order_id'],//充值id
                        'money_from'         => 0,//0预存款，1支付宝, 2微信
                        'transaction_type'   => 1,//0,收入 1支出
                        'receive_money'      => $price,//收入或减去的金额
                        'pay_time'           => time(),//支付时间
                        'platform_from'      => 1,//0,茶通历史表，1，商城 2，transaction3,个人充值
                        'use_money_history'  => $set_money,//当前的金额
                        'user_id'            => $user_id,//user_id
                        'comments'           => "购买商品({$order_info['goods_name']}),支付价格是({$price}元)",//备注
                        );  
        $money_history_mod = m('money_history');
        $result_add_history = $money_history_mod ->add($add_array);   

        //3.订单状态改变
        $update_order = array('pay_have'=>1,'status'=>11);
        $result_order_status = $order_mod->edit($order_info['order_id'], $update_order);

        //4.将支付订单金额增加到admin账户
        
        //4.1 获取到admin账户的金额
        $admin_money =  $member_mod ->get_money(1);
        //4.2 计算增加后的金额
        $set_admin_money = ((int)(string)($price*100)+(int)(string)(100*$admin_money))/100;
        //4.3 编辑金额
        $edit_admin_array =  array('use_money'=>$set_admin_money);
        $result_member_admin = $member_mod ->edit(1,$edit_admin_array);
        //4.4 增加admin的金额日志记录
        $add_admin_array = array(
                        'transaction_sn'     => $order_info['order_id'],//充值id
                        'money_from'         => 0,//0预存款，1支付宝, 2微信
                        'transaction_type'   => 0,//0,收入 1支出
                        'receive_money'      => $price,//收入或减去的金额
                        'pay_time'           => time(),//支付时间
                        'platform_from'      => 1,//0,茶通历史表，1，商城 2，transaction3,个人充值
                        'use_money_history'  => $set_admin_money,//当前的金额
                        'user_id'            => 1,//user_id
                        'comments'           => "用户({$member_info['user_name']})购买商品({$order_info['goods_name']}),到账金额是({$price}元)",//备注
                        ); 
         $result_add_admin = $money_history_mod ->add($add_admin_array); 
        //5.以上成功事务提交
        if ($result_member && $result_add_history && $result_order_status && $result_member_admin && $result_add_admin) {
            $recarge_mod ->commit();
            /* 发送邮件给卖家，提醒付款成功 */
            $seller_info  = $member_mod->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* 同步发送 */
            $this->_sendmail(true);
        }else{
            $recarge_mod ->cancel();
           
            $seller_info  = $member_mod->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], '商品支付异常', '管理员及时检查订单id'.$order_info['order_id']);
            $this->show_warning("商品支付异常");
            return;

        }
        //请求成功跳转到
        $site_url =site_url();
        header("Location: $site_url"."index.php?app=paynotify&order_id={$order_info['order_id']}");
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
        //获取订单的信息
        $out_trade_no = $_POST['sn'];
        $order_mod = m('order');
        
        $order_info = $order_mod ->getorder_info($out_trade_no);

        $member_info = $recarge_mod ->get_member_info($user_id);
        //计算加上物流费用以后的价
        $price = ((int)(string)($order_info['price']*100)+(int)(string)($order_info['shipping_fee']*100))/100;
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
        $email_content = "【福禄仓投资集团】您将购买商品({$order_info['goods_name']}),支付价格是({$price}元),您的验证码是 $rand ,不要告诉别人哦!有效时间5分钟！";  
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

}

?>
