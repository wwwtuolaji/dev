<?php

/**
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_paymentApp extends StoreadminbaseApp
{
    function index()
    {
        /* 取得列表数据 */
        $model_payment =& m('payment');

        /* 获取白名单 */
        $white_list    = $model_payment->get_white_list();

        /* 获取白名单过滤后的内置支付方式列表 */
        $payments      = $model_payment->get_builtin($white_list);

        $installed     = $model_payment->get_installed($this->visitor->get('manage_store'));
        $site_url=site_url();
        $this->assign('site_url',$site_url);
        foreach ($payments as $key => $value)
        {
            foreach ($installed as $installed_payment)
            {
                if ($installed_payment['payment_code'] == $key)
                {
                    $payments[$key]['payment_desc']     =   $installed_payment['payment_desc'];
                    $payments[$key]['enabled']          =   $installed_payment['enabled'];
                    $payments[$key]['installed']        =   1;
                    $payments[$key]['payment_id']       =   $installed_payment['payment_id'];
                }
            }
        }

        $this->assign('payments', $payments);
        $this->import_resource(array(
          'script' => array(
                   array(
                      'path' => 'dialog/dialog.js',
                      'attr' => 'id="dialog_js"',
                   ),
                   array(
                      'path' => 'jquery.ui/jquery.ui.js',
                      'attr' => '',
                   ),
          ),
          'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css,res:jqtreetable.css',
        ));

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('my_payment'), 'index.php?app=my_payment',
                         LANG::get('payment_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_payment');

        /* 当前所处子菜单 */
        $this->_curmenu('payment_list');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_payment'));
        header("Content-Type:text/html;charset=" . CHARSET);
        $this->display('my_payment.index.html');

    }

    /**
     *    安装支付方式
     *
     *    @author    Garbin
     *    @return    void
     */
    function install()
    {
        $code = isset($_GET['code']) ? trim($_GET['code']) : 0;
        $code=str_replace(array("/","\\"), '', $code); 
        if (!$code)
        {
            echo Lang::get('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        $payment       = $model_payment->get_builtin_info($code);
        if (!$payment)
        {
            echo Lang::get('no_such_payment');

            return;
        }
        $payment_info = $model_payment->get("store_id=" . $this->visitor->get('manage_store') . " AND payment_code='{$code}'");
        if (!empty($payment_info))
        {
            echo Lang::get('already_installed');

            return;
        }
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_payment'), 'index.php?app=my_payment',
                             LANG::get('payment_list'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_payment');

            /* 当前所处子菜单 */
            $this->_curmenu('install_payment');

            /* 默认启用 */
            $payment['enabled'] = 1;

            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->assign('payment', $payment);
            $this->_config_seo('title', Lang::get('member_center') . Lang::get('my_payment'));
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_payment.form.html');
        }
        else
        {
            $data = array(
                'store_id'      => $this->visitor->get('manage_store'),
                'payment_name'  => $payment['name'],
                'payment_code'  => $code,
                'payment_desc'  => $_POST['payment_desc'],
                'config'        => $_POST['config'],
                'is_online'     => $payment['is_online'],
                'enabled'       => $_POST['enabled'],
                'sort_order'    => $_POST['sort_order'],
            );
            if (!($payment_id = $model_payment->install($data)))
            {
                //$this->show_warning($model_payment->get_error());
                $msg = $model_payment->get_error();
                $this->pop_warning($msg['msg']);
                return;
            }
            $this->pop_warning('ok', 'my_payment_install');
        }
    }

    function config()
    {
        if (!$this->visitor->has_login)
        {
                //未登录
            echo "登录信息已经过期请重新登录！";
            return;
        }
       
 
        $payment_id =   isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
        if (!$payment_id)
        {
            echo Lang::get('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("store_id = " . $this->visitor->get('manage_store') . " AND payment_id={$payment_id}");
        if (!$payment_info)
        {
            echo Lang::get('no_such_payment');

            return;
        }
        $payment = $model_payment->get_builtin_info($payment_info['payment_code']);
        if (!$payment)
        {
            echo Lang::get('no_such_payment');

            return;
        }

        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_payment'), 'index.php?app=my_payment',
                             LANG::get('payment_list'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_payment');

            /* 当前所处子菜单 */
            $this->_curmenu('install_payment');
            $payment['payment_id']  =   $payment_info['payment_id'];
            $payment['payment_desc']=   $payment_info['payment_desc'];
            $payment['enabled']     =   $payment_info['enabled'];
            $payment['sort_order']  =   $payment_info['sort_order'];
           /* var_dump($payment);
            die;*/
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->assign('config', unserialize($payment_info['config']));
            $this->assign('payment', $payment);
            $this->_config_seo('title', Lang::get('member_center') . Lang::get('my_payment'));
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_payment.form.html');
        }
        else
        {
            $data = array(
                'payment_desc'  =>  $_POST['payment_desc'],
                'config'        =>  serialize($_POST['config']),
                'enabled'       =>  $_POST['enabled'],
                'sort_order'    =>  $_POST['sort_order'],
            );
            
            /*$data['config']['alipay_partner']='1111';
            $data['config']['sms_code']='1111';*/
            
            $model_payment->edit("store_id =" . $this->visitor->get('manage_store') . " AND payment_id={$payment_id}", $data);

            if ($model_payment->has_error())
            {
                //$this->show_warning($model_payment->get_error());
                $msg = $model_payment->get_error();
                $this->pop_warning($msg['msg']);
                return;
            }
            $this->pop_warning('ok', 'my_payment_config');
            //$this->show_message('config_payment_successed');
        }
    }

    function uninstall()
    {
        $payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        $model_payment =& m('payment');
        $model_payment->uninstall($this->visitor->get('manage_store'), $payment_id);
        if ($model_payment->has_error())
        {
            $this->show_warning($model_payment->get_error());

            return;
        }

        $this->show_message('uninstall_payment_successed');
    }


    /**
     *    三级菜单
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_member_submenu()
    {
        $arr = array(
            array(
                'name'  => 'payment_list',
                'url'   => 'index.php?app=my_payment',
            ),
            array(
                'name'  => 'install_payment',
                'url'   => 'javascript:;',
            ),
        );
        if (ACT == 'index')
        {
            unset($arr[1]);
        }

        return $arr;
    }
    /**
     * [send_alipay_sms 发送阿里短信息]
     * @return [type] [code]
     */
    function send_alipay_sms(){
         //已经登陆
        $user_id=$this->visitor->get('user_id');
        $db=&db();
        $sql="select user_id,phone_mob from ecm_member where user_id=$user_id";
        $user_info=$db->getrow($sql);
        $this->assign('user_info', $user_info);
        $mt_rand=mt_rand(100000,999999);
        $_SESSION[$user_info['user_id'].'_'.$user_info['phone_mob']]=$mt_rand;
        include(ROOT_PATH . '/includes/sms_send/sms_function.php');
        $content = '您的验证码为：111111，请及时完成注册，如非本人操作请忽略。【福禄仓投资集团】';
        $send_result=send_sms($content,$phone,$type='post');
        $send_result_=explode("&", $send_result);
        foreach ($send_result_ as $key => $value) {
            $temp=explode("=",$value);
            $result[$temp[0]]=$temp[1];
        }
         echo json_encode($result);
    }
    function check_code(){
        $user_id=$this->visitor->get('user_id');
        $db=&db();
        $sql="select user_id,phone_mob from ecm_member where user_id=$user_id";
        $user_info=$db->getrow($sql);
        $mt_rand=$_SESSION[$user_info['user_id'].'_'.$user_info['phone_mob']];
        if ($_GET['code']==$mt_rand) {
            $output=array(
                'code'=>0,
                'message'=>'验证成功',
                'data'=>$mt_rand
                );
        }else{
             $output=array(
                'code'=>1,
                'message'=>'验证码输入错误',
                'data'=>''
                ); 
        }
        echo json_encode($output);

    }
}
