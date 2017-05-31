<?php
/* 会员控制器 */
class TransactionApp extends BackendApp
{

    function __construct()
    {
        $this->TransactionApp();
    }

    function TransactionApp()
    {
        
        parent::__construct();
    }
    function index()
    {
    	$app_mod = m('transaction_apply');
        $where = "";
        if (!empty($_GET['user_name'])) {
            $where .= "AND user_name like '{$_GET['user_name']}%'";
        }
        if (!empty($_GET['produce_name'])) {
            $where .= "AND goods_name like '{$_GET['produce_name']}%'";
        }
        if (!empty($_GET['start_time'])) {
            $strt_time = strtotime($_GET['start_time']);
            $end_time = $_GET['end_time']?strtotime($_GET['end_time']):time();
            $where .= "AND apply_time >'$strt_time' AND apply_time<'$end_time'";
        }
        $recharge_mod = m("recharge_log");
         if(!isset($_SESSION['token']) || $_SESSION['token']=='') {
          $recharge_mod->set_token();
        }
         $page = $this->_get_page(15);
        $this->assign('token',$_SESSION['token']);
        $apply_arr = $app_mod ->get_all($where,$page['limit']);
        $page['item_count'] = $app_mod->getCount($where);
        $this->_format_page($page);
        $this->assign('page_info',$page);
        foreach ($apply_arr as $key => $value) {
            $apply_arr[$key]['apply_time'] = date("Y-m-d H:i:s",$value['apply_time']);
        }
        $this->assign('apply_arr',$apply_arr);

        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,layer/layer.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('transaction.index.html');
    }
    function edit_comments(){
        $content = $_POST['approval_comments'];
        $app_mod = m('transaction_apply');
        $array = array('approval_comments' => $content);
        
        $result = $app_mod ->edit($_POST['approval_id'],$array);
        if ($result!==false) {
            $out_arr =  array(   'code' =>0,
                                'message' =>'请求成功',
                                'data' =>'');

        }else{
            $out_arr = array(  'code' =>1,
                                'message' =>'修改失败',
                                'data' =>'');
        }
        echo json_encode($out_arr);
    }

    function edit_approval(){
        if (empty($_GET['approval_id'])) {
            $this->show_warring('请求失败');
            return;
        }
         $recharge_mod = m("recharge_log");
        //校验token
        if(!$recharge_mod->valid_token()) {
            $this->show_warning('表单无法重复提交，请返回上一页面重试');
            return;
        }
        //检查当前的id是否已经操作过
         $app_mod = m('transaction_apply');
         $apply_info = $app_mod ->get($_GET['approval_id']);
         if ($apply_info['approval_status']!=-1) {
             $this->show_warning('已经审核过');
            return;
         }
         //是拒绝不做任何操作。通过进行个人库存，交易初始化 两个表内容增加，交易表不做操作，卖不卖自己决定。
         if($_GET['approval_status']==1){
            //1.增加库存
            //1）先找到是否有对应的商品，有则在原来基础上添加，没有再重新添加
            $warehouse_mod = m('own_warehouse');
            $warehouse_info = $warehouse_mod ->get_warehouse($apply_info['goods_id'],$apply_info['user_id']);
            $transaction_price = (int)(string)($apply_info['produce_price']*100)*$apply_info['produce_count'];
            if (empty($warehouse_info)) {
                //没有生成新的库存
                $warehouse_add = array('user_id'=> $apply_info['user_id'],
                                        'goods_id'=> $apply_info['goods_id'],
                                        'goods_count'=> $apply_info['produce_count'],
                                        'transaction_price' => $transaction_price/100,
                                );
                $warehouse_mod->add($warehouse_add);
            }else{
                //存在在原来基础上增加
                $temp_price = intval($warehouse_info['transaction_price'] *100) + $transaction_price;
                //库存数量增加
                $transaction_count = $warehouse_info['transaction_count'] + $apply_info['produce_count'];
                $warehouse_edit = array(
                                        'transaction_price' => $temp_price/100,
                                        'transaction_count' => $transaction_count
                                );
                $warehouse_mod->edit($warehouse_edit);

            } 
            //2.交易初始化功能。
            $add_init = array(  'goods_id' =>$apply_info['goods_id'],
                                'produce_count' => $apply_info['produce_count'],
                                'produce_price'=> $apply_info['produce_price'],
                                'apply_time' => time(),
                                'user_id'    => 1,//user_id 主用户id为1
                                'user_name'  => $apply_info['user_name'],
                                'comments'   => "用户{$apply_info['user_name']} 的申请通过记录",
                                );

            $transaction_init_mod = m('transaction_init');   
            $transaction_init_mod ->add($add_init);
         }
       
        $array = array('approval_status'=>$_GET['approval_status']);
        $app_mod->edit($_GET['approval_id'],$array);
        $this->show_message('设置成功');
    }
    /**
     * [add_produce 添加产品供用户选择]
     */
    function add_produce()
    {
        $recharge_mod = m("recharge_log");
        if ($_POST) {

            //校验token
            if(!$recharge_mod->valid_token()) {
                $this->show_warning('表单无法重复提交，请返回上一页面重试');
                return;
            }
            $arr = array('goods_name'=>$_POST['goods_name'],
                        'description'=>$_POST['description'],
                        'price'=>$_POST['price']);
            $share_tea_mod = m('share_tea');
            $share_id = $share_tea_mod ->add($arr);
            if($share_id){  
            $this->show_message('添加成功',
                       '返回列表',    'index.php?app=transaction',
                      '继续添加',   'index.php?app=transaction&amp;act=add_produce');
            return;
            
            }else{
                $this->show_warning('添加失败');
                return;
            }
        }
       
        if(!isset($_SESSION['token']) || $_SESSION['token']=='') {
          $recharge_mod->set_token();
        }
        $this->assign('token',$_SESSION['token']);
        $this->display("transaction.add.html");
        
    }
    /**
     * [init_produce 初始库存信息]
     * @return [type] [description]
     */
    function init_produce(){
        $page = $this->_get_page(15);
        $transaction_init_mod = m('transaction_init');  
        $transaction_arr = $transaction_init_mod ->get_all($page['limit']);
        $page['item_count'] = $transaction_init_mod->get_count();
        $this->_format_page($page);
        $this->assign('page_info',$page);
        foreach ($transaction_arr as $key => $value) {
            $transaction_arr[$key]['apply_time'] =date('Y-m-d H:i:s', $value['apply_time']);
        }
        $this->assign('transaction_arr',$transaction_arr);
        $this->display("init_produce.html");
    }
}