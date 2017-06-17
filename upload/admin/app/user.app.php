<?php

/* 会员控制器 */
class UserApp extends BackendApp
{
    var $_user_mod;

    function __construct()
    {
        $this->UserApp();
    }

    function UserApp()
    {
        parent::__construct();
        $this->_user_mod =& m('member');
    }

    function index()
    {
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $_GET['field_name'],
                'name'  => 'field_value',
                'equal' => 'like',
            ),
        ));
        //更新排序
        if (isset($_GET['sort']) && !empty($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'user_id';
             $order = 'asc';
            }
        }
        else
        {
            if (isset($_GET['sort']) && empty($_GET['order']))
            {
                $sort  = strtolower(trim($_GET['sort']));
                $order = "";
            }
            else
            {
                $sort  = 'user_id';
                $order = 'asc';
            }
        }
        $page = $this->_get_page();
        $users = $this->_user_mod->find(array(
            'join' => 'has_store,manage_mall',
            'fields' => 'this.*,store.store_id,userpriv.store_id as priv_store_id,userpriv.privs',
            'conditions' => '1=1' . $conditions,
            'limit' => $page['limit'],
            'order' => "$sort $order",
            'count' => true,
        ));
        foreach ($users as $key => $val)
        {
            if ($val['priv_store_id'] == 0 && $val['privs'] != '')
            {
                $users[$key]['if_admin'] = true;
            }
        }
        $this->assign('users', $users);
        $page['item_count'] = $this->_user_mod->getCount();
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);
        /* 导入jQuery的表单验证插件 */
        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->assign('query_fields', array(
            'user_name' => LANG::get('user_name'),
            'email'     => LANG::get('email'),
            'real_name' => LANG::get('real_name'),
//            'phone_tel' => LANG::get('phone_tel'),
//            'phone_mob' => LANG::get('phone_mob'),
        ));
        $this->assign('sort_options', array(
            'reg_time DESC'   => LANG::get('reg_time'),
            'last_login DESC' => LANG::get('last_login'),
            'logins DESC'     => LANG::get('logins'),
        ));
        $this->display('user.index.html');
    }

    function add()
    {
        if (!IS_POST)
        {
            $this->assign('user', array(
                'gender' => 0,
            ));
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar());
            $this->display('user.form.html');
        }
        else
        {
            $user_name       = trim($_POST['user_name']);
            $password        = trim($_POST['password']);
            $email           = trim($_POST['email']);
            $real_name       = trim($_POST['real_name']);
            $gender          = trim($_POST['gender']);
            $im_qq           = trim($_POST['im_qq']);
            $im_msn          = trim($_POST['im_msn']);
            $member_card_num = trim($_POST['member_card_num']);
            $inner_card_num  = trim($_POST['inner_card_num']);
            $member_level    = trim($_POST['member_level']);
            if (strlen($user_name) < 3 || strlen($user_name) > 25)
            {
                $this->show_warning('user_name_length_error');

                return;
            }
            
            if (strlen($password) < 6 || strlen($password) > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            if (!is_email($email))
            {
                $this->show_warning('email_error');

                return;
            }

            /* 连接用户系统 */
            $ms =& ms();

            /* 检查名称是否已存在 */
            if (!$ms->user->check_username($user_name))
            {
                $this->show_warning($ms->user->get_error());

                return;
            }
            /*检查用户卡号是否存在*/
            if($this->check_inner_card($inner_card_num,$user_name)){
                $this->show_warning('[内部卡号]已经存在！请重新编辑');
                return;
            }

            /*检查用户卡号是否存在*/
            if($this->check_member_card($member_card_num,$user_name)){
                $this->show_warning('[外部卡号]已经存在！请重新编辑');
                return;
            }

            /* 保存本地资料 */
            $data = array(

                'real_name'         => $_POST['real_name'],
                'gender'            => $_POST['gender'],
                'member_card_num'   => $_POST['member_card_num'],
                'inner_card_num'    => $_POST['inner_card_num'],
                'member_level'      => $_POST['member_level'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
                'im_msn'    => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
                'reg_time'  => gmtime(),
            );

            /* 到用户系统中注册 */
            $user_id = $ms->user->register($user_name, $password, $email, $data);
            if (!$user_id)
            {
                $this->show_warning($ms->user->get_error());

                return;
            }

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false)
                {
                    return;
                }

                $portrait && $this->_user_mod->edit($user_id, array('portrait' => $portrait));
            }


            $this->show_message('add_ok',
                'back_list',    'index.php?app=user',
                'continue_add', 'index.php?app=user&amp;act=add'
            );
        }
    }

    /*检查用户卡号是否存在*/
    function check_member_card($member_card_num)
    {
        if (empty($member_card_num)) {
            return false;
        }
        $db = &db();    
        $have_card =$db->getrow("select * from ecm_member where member_card_num='$member_card_num' and user_name<>'$user_name'"); 
        if ($have_card) {
                
            return true;
        }
        return false;
    }
       /*检查用户卡号是否存在*/
       //存在返回true；
    function check_inner_card($member_card_num,$user_name)
    {
        if (empty($member_card_num)) {
            return false;
        }
        $db = &db();    
        $have_card =$db->getrow("select * from ecm_member where inner_card_num='$member_card_num' and user_name<>'$user_name'"); 
        if ($have_card) {
                
            return true;
        }
        return false;
    }

    /*检查会员名称的唯一性*/
    function  check_user()
    {
          $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
          if (!$user_name)
          {
              echo ecm_json_encode(false);
              return ;
          }

          /* 连接到用户系统 */
          $ms =& ms();
          echo ecm_json_encode($ms->user->check_username($user_name));
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $user = $this->_user_mod->get_info($id);
            if (!$user)
            {
                $this->show_warning('user_empty');
                return;
            }

            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar($id));
            $this->assign('user', $user);
            $this->assign('phone_tel', explode('-', $user['phone_tel']));
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->display('user.form.html');
        }
        else
        {
            //检查新修改的卡号是否和别的用户卡号冲突
            if (!empty($_POST['member_card_num'])) {

                $member_card_num=$_POST['member_card_num'];
                $db = &db();    
                $sql="select * from ecm_member where member_card_num='$member_card_num' and user_name<> '".$_POST['uname']."'";
                $have_card =$db->getrow($sql); 
            
                if ($have_card) {
                    $this->show_warning("修改的卡号,与其他用户的卡号冲突");
                    return;
                }
            }
            //检查新修改的卡号是否和别的用户卡号冲突
            if (!empty($_POST['inner_card_num'])) {

                $inner_card_num=$_POST['inner_card_num'];
                $db = &db();    
                $sql="select * from ecm_member where inner_card_num='$inner_card_num' and user_name<> '".$_POST['uname']."'";
                $have_card =$db->getrow($sql); 
            
                if ($have_card) {
                    $this->show_warning("修改的外部卡号,与其他用户的卡号冲突");
                    return;
                }
            }
            
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
                'im_msn'    => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
                'member_card_num' => trim($_POST['member_card_num']),
                'inner_card_num'  => trim($_POST['inner_card_num']),
                'member_level'    => trim($_POST['member_level']),
            );
            if (!empty($_POST['password']))
            {
                $password = trim($_POST['password']);
                if (strlen($password) < 6 || strlen($password) > 20)
                {
                    $this->show_warning('password_length_error');

                    return;
                }
            }
            if (!is_email(trim($_POST['email'])))
            {
                $this->show_warning('email_error');

                return;
            }

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($id);
                if ($portrait === false)
                {
                    return;
                }
                $data['portrait'] = $portrait;
            }

            /* 修改本地数据 */
            $this->_user_mod->edit($id, $data);

            /* 修改用户系统数据 */
            $user_data = array();
            !empty($_POST['password']) && $user_data['password'] = trim($_POST['password']);
            !empty($_POST['email'])    && $user_data['email']    = trim($_POST['email']);
            if (!empty($user_data))
            {
                $ms =& ms();
                $ms->user->edit($id, '', $user_data, true);
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=user',
                'edit_again',   'index.php?app=user&amp;act=edit&amp;id=' . $id
            );
        }
    }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_user_to_drop');
            return;
        }
        $admin_mod =& m('userpriv');
        if(!$admin_mod->check_admin($id))
        {
            $this->show_message('cannot_drop_admin',
                'drop_admin', 'index.php?app=admin');
            return;
        }

        $ids = explode(',', $id);

        /* 连接用户系统，从用户系统中删除会员 */
        $ms =& ms();
        if (!$ms->user->drop($ids))
        {
            $this->show_warning($ms->user->get_error());

            return;
        }

        $this->show_message('drop_ok');
    }

    /**
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }

        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
            $this->show_warning($uploader->get_error(), 'go_back', 'index.php?app=user&amp;act=edit&amp;id=' . $user_id);
            return false;
        }

        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }
    function member_recharge()
    {   
       if(empty($_REQUEST['user_id'])) {
        $this->show_warning('请返回页面重新编辑');
        return;
       }
        if ($_POST['user_id']) {
            $user_info = $this->_user_mod->get($_POST['user_id']);
            $recharge_mod = m('recharge_log');
            $recharge_sn = $recharge_mod ->get_rand();
            $cur_user_id = $this->visitor->get('user_id');
            //整理插入到充值数据
            $recharge_arr = array(
                                'recharge_sn'=>$recharge_sn,
                                'pay_money' =>$_POST['receive_money'],
                                'pay_account'=>"后台管理员$cur_user_id 转入",
                                'pay_method' =>"4",
                                'pay_status' =>"40",
                                'first_time' =>time(),
                                'finished_time'=>time(),
                                'comment_des'=>$_POST['comments'],
                                'user_id' =>$_POST['user_id'],
                                'recharge_status' =>$_POST['0'],
                                );
            $recharge_id = $recharge_mod ->add( $recharge_arr);
            if (empty($recharge_id)) {
                $this->show_warning('编辑失败');
                return;
            }

            //1.获取当前的金额
            $use_money_history = $user_info['use_money'];
            //2.修改金额
            $use_money = (int)(string)($user_info['use_money']*100) + (int)(string)($_POST['receive_money']*100);
            $use_money = array('use_money' => $use_money/100);
            /*dump($use_money_history);*/
            $result = $this->_user_mod->edit($_POST['user_id'],$use_money);
            $history_mod = m('money_history');
            if ($result) {
                $arr = array('transaction_sn'   => $recharge_id,//个人充值表
                        'money_from'        => 0,
                        'transaction_type'  => 0,
                        'receive_money'     => $_POST['receive_money'],
                        'pay_time'          => time(),
                        'platform_from'     => 3,
                        'use_money_history' => $use_money_history,
                        'user_id'           => $user_id,
                        'comments'          => $_POST['comments'],
                        );
                $history_mod = m('money_history');
                $history_mod ->add($arr);
                $this->show_message('充值成功',
                    'back_list',    'index.php?app=user',
                    'continue_add',  "index.php?app=user&amp;act=edit&amp;user_id ={$_POST['user_id']}"
                );
                return;
            }
            $this->show_warning('编辑失败');
            return;
        }
        //用户充值
        $this->display('account_recharge.html');
    }
}

?>
