<?php

/**
 *    Desc
 *
 * @author    jjc
 * @usage    none
 */
class BankApp extends MemberbaseApp
{
    public $bank_mod;
    function __construct(){
        parent::__construct();
        $this->bank_mod=&m('bank');
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

        if (!empty($_GET['act']) && $_GET['act'] == 'add_card') {
            $submenus[] = array(
                'name' => 'add_card',
                'url' => 'index.php?app=bank&amp;act=add_card',
            );
        }
        if (!empty($_GET['act']) && $_GET['act'] == 'edit') {
            $submenus[] = array(
                'name' => 'edit_card',
                'url' => 'index.php?app=bank&amp;act=edit_card',
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
    /*添加银行卡*/
    function add_card(){
        if ($_POST) {
           //校验验证码
           /*dump($_POST);*/
            $captcha=base64_decode($_SESSION['captcha']) ;
            if ($_POST['captcha'] !=  $captcha) {
               $this->show_warning('验证码错误');
               return;
            }
            //数据处理
            foreach ($_POST as $key => $value) {
                $temp = trim($value);
                $new_arr[$key]=addslashes($temp);
            }
            if ($new_arr['type']=='credit') {
                $new_arr['type'] = 2; 
            }else{
                $new_arr['type'] = 1; 
            }
            if(strlen($new_arr['num'])<16){
              $this->show_warning('检查输入的银行卡号');  
              return;
            }
            if(preg_match('/^[\x7f-\xff]+$/', $new_arr['account_name']))
             {} else 
             {
                show_warning('请输入中文真实姓名');                  
             } 
             unset($new_arr['captcha'] );
             //$bank_mod = m('bank');
             $new_arr['add_time'] = time();
             $new_arr['user_id'] = $this->visitor->get('user_id'); 
             $bank_id = $this->bank_mod->add($new_arr);
             if ($bank_id) {
                 $this->show_message('添加成功','go_back','index.php?app=deposit&act=deposit');
                 return;
             }
              $this->show_message('添加失败','go_back','index.php?app=deposit&act=deposit');
              return;
        }
            /* 当前所处子菜单 */
        $this->_curmenu('add_card');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->display('add_card.index.html');
    }
    /*编辑银行卡*/
    function edit(){
        if ($_POST) {
             //校验验证码
             /*dump($_POST);*/
             $captcha=base64_decode($_SESSION['captcha']) ;
             if ($_POST['captcha'] !=  $captcha) {
               $this->show_warning('验证码错误');
               return;
             }
             //数据处理
             foreach ($_POST as $key => $value) {
                $temp = trim($value);
                $new_arr[$key]=addslashes($temp);
             }
             if ($new_arr['type']=='credit') {
                $new_arr['type'] = 2; 
             }else{
                $new_arr['type'] = 1; 
             }
             if(strlen($new_arr['num'])<16){
              $this->show_warning('检查输入的银行卡号');  
              return;
             }
             if(preg_match('/^[\x7f-\xff]+$/', $new_arr['account_name']))
             {

             }else{
                show_warning('请输入中文真实姓名');                  
             } 
             unset($new_arr['captcha'] ); 
             $bank_id = $new_arr['bank_id'];
             unset($new_arr['bank_id']);
             $result = $this->bank_mod->edit($bank_id,$new_arr);
             if ($result) {
                
                //$this->show_message('编辑银行卡成功',"index.php?app=deposit&act=deposit");
                $this->show_message('编辑银行卡成功','go_back', 'index.php?app=deposit&amp;act=deposit', 'to_add_member', 'index.php?app=deposit&amp;act=deposit');
                return;
             }
              $this->show_message('编辑银行卡失败','go_back', 'index.php?app=deposit&amp;act=deposit', 'to_add_member', 'index.php?app=deposit&amp;act=deposit');
              return;
        }
        if(empty($_GET['bid'])){
            $this->show_warning('此卡不存在无法进行编辑！');
            return;
        } 
        /*检查权限*/
        $bank_id     =  $_GET['bid'];
        $user_id     =  $this->visitor->get('user_id');
        $bank_info   =  $this->bank_mod->get(array('conditions'=>"user_id='$user_id'and bank_id='$bank_id' and bank_status >-1"));
        if (empty($bank_info )) {
            $this->show_warning('您没有权限编辑此卡');
            return;
        }
        $this->assign('bank_info',$bank_info);
        /* 当前所处子菜单 */
        $this->_curmenu('edit_card');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->display('edit_card.index.html');
        /* dump($bank_info);*/
       
    }

    function drop()
    {
        $bank_id     =  $_GET['bid'];
        $user_id     =  $this->visitor->get('user_id');
        $bank_info   =  $this->bank_mod->get(array('conditions'=>"user_id='$user_id'and bank_id='$bank_id' and bank_status >-1"));
        if (empty($bank_info )) {
            $this->show_warning('您没有权限编辑此卡');
            return;
        }
        $edit = array('bank_status'=>-1);
        $result=$this->bank_mod->edit($bank_id,$edit);
        if ($result) {
            $this->show_message('删除成功','go_back','index.php?app=deposit&act=deposit');
            return;
        }
        $this->show_message('删除失败','go_back','index.php?app=deposit&act=deposit');
       
    }
   
   

    
 
}