<?php

class IndexApp extends IndexbaseApp
{
    function __construct()
    {
        parent::__construct();
        //parent::_config_view();
    }

    function index()
    {
        $visitor = $this->visitor->get();
        if (!$this->visitor->has_login) {
            //未登录
            $member_info = false;
        } else {
            //已经登陆
            $user_info = $this->visitor->get();
            //过滤前台分配数据
            $member_info['user_id'] = $user_info['user_id'];
            $member_info['level'] = $user_info['member_level'];
            $member_info['card'] = $user_info['member_card_num'];
            $member_info['name'] = $user_info['user_name'];
        }
        $index_model = m('index_show');
        $index_arr = $index_model->get(1);
        $index_arr = $index_model->return_arr_val($index_arr);
        $this->assign('index_arr',$index_arr);
        //获取企业信息
        $day = (time()-$index_arr['current_time_date'])/(60*60*24);
        $day = intval(strval($day));
        $to = $day * 6;
        $limit = "$to,6";
        $store_arr = $this->_get_data_priv_store_limit($limit);
        //dump($store_arr);
        $this->assign('site_url',site_url());
        if (count($store_arr)<6) {
            $store_arr = $this->_get_data_priv_store_limit('0,6');
            $edit['current_time_date'] = time();
            $index_model->edit('1',$edit);
        }
        $notice = $this->_get_article_notice();
        $drogue_arr = $this->_get_data();
        $goods_new = $this->_get_data_new();

        $out_data['notice'] = $notice;
        $out_data['member_info'] = $member_info;
        $out_data['goods_new'] = $goods_new;
        $out_data['drogue_arr'] = $drogue_arr;
        $out_data['store_arr'] = $store_arr;

     
        //qq
        $this->assign('qq_arr',$index_arr ['qq_num_des']);
        $this->assign("out_data", $out_data);
        $this->display("index.html");
    }

    /**
     * [loan 贷款页面]
     * @return [type] [description]
     */
    function loan()
    {
         $site_url = site_url();
      
        $recharge_mod = m('recharge_log');
          //如果token为空则生成一个token
        if(!isset($_SESSION['token']) || $_SESSION['token']=='') {
          $recharge_mod->set_token();
        }
        if (isset($_GET['back_token'])) {
            $conditions = array('conditions'=>"back_token='{$_GET['back_token']}'");
            $model_loan = m('loan');
            $loan = $model_loan->get($conditions);
            $this->assign('loan',$loan);

        }
       
        $this->assign('token',$_SESSION['token']);
        if (empty($_POST)) {
            $this->display("loan.html");
          /*  $this->display("loan.html");*/
        }else{
          
        }

    }
    /**
     * [loan_i 表单校验第二部]
     * @return [type] [description]
     */
    function loan_i(){
            $site_url = site_url();
            $this->assign('site_url',$site_url);
            if (empty($_POST)) {

                header("Location:".$site_url."/index.php?app=index&act=loan");
            }
            $recharge_mod = m('recharge_log');
             //1.表单唯一校验
             //用于表单返回参数
            $leisure_mod = m('leisure');
          /*  $province_arr = $leisure_mod->get_province();
            $this->assign('province_arr',$province_arr);*/
            if(!$recharge_mod->valid_token()) {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('表单无法重复提交，请刷新页面重试');
                return;
            }
           //2.手机验证码校验
            if($_SESSION['email_code']['code']!=$_POST['smsCode']){
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('邮箱验证码验证失败');
                return;
            }
           
            $this->assign('token',$_SESSION['token']);
            //3.数据处理
            $add['money'] = sprintf("%.3f", $_POST['money']); 
            $add['first_name'] = htmlspecialchars($_POST['first_name']);
            $add['sex'] = $_POST['sex'];
            $add['phone'] = $_POST['phone'];
            $add['email'] = htmlspecialchars($_POST['email']);
            $add['back_token'] = $_POST['token'];
            $add['apply_time'] = time();
            $model_loan = m('loan');
            //分配唯一并且不能修改的校验码
            $this->assign('back_token',$_POST['token']);
            if (empty($_POST['back_token'])) {
                
                $loan_id = $model_loan ->add($add);
            }else{
                //每次都更新token 结果就不会为false
                $loan_result = $model_loan ->edit("loan_id = '{$_POST['loan_id']}' AND back_token ='{$_POST['back_token']}'",$add); 
                if (!$loan_result) {
                     echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                    $this->show_warning('非法操作异常');
                    return;
                } 
                $loan_id = $_POST['loan_id'];
            }
           
            if (!$loan_id) {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('数据异常');
                return;
            }

            $this->assign('back_token',$_POST['token']);
            $this->assign('loan_id',$loan_id);
            $this->display("loan_i.html");
    }
    function loan_success(){
        $site_url = site_url();
        //校验是否被更改过
        $conditions = array("loan_id = '{$_POST['loan_id']}' AND back_token ='{$_POST['back_token']}'");
        $model_loan = m('loan');
        $loan = $model_loan->get($conditions);
        $this->assign('site_url',$site_url);
        //获取当前订单是否存在不存在直接返回
        if (empty($_POST)||empty($loan)) {
            echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('数据异常');
            header("Location:".$site_url."/index.php?app=index&act=loan");
            return;
        }
        $edit['address'] = htmlspecialchars($_POST['address']);
        $edit['enterprise_name'] = htmlspecialchars($_POST['enterprise_name']);
        $edit['enterprise_code'] = htmlspecialchars($_POST['enterprise_code']);
        $edit['relation_name'] = htmlspecialchars($_POST['relation_name']);
        $edit['member_card'] = htmlspecialchars($_POST['member_card']);
        $edit['duration'] = htmlspecialchars($_POST['duration']);
        $edit['apply_time'] = time();
        $result = $model_loan->edit($_POST['loan_id'],$edit);
        if ($result) {
            $this->display("loan_success.html");
        }else{
            echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('数据异常');
                header("Location:".$site_url."/index.php?app=index&act=loan");
                return;
        }


        //1.
    }


    /**
     * [leisure 茶休闲会所]
     * @return [type] [description]
     */
    function leisure()
    {
        $leisure_mod = m('leisure');
        $province_arr = $leisure_mod->get_province();
        $leisure_info = $leisure_mod->find();
        if (empty($_GET['leishure_id'])) {
            //随机取出一个茶会所
            
            $leisure = mt_rand(1,count($leisure_info));
            $count = 0;
            $arr   = array();
            foreach ($leisure_info as $key => $value) {
                $count++;
                if ($count==$leisure) {
                    $arr = $value;
                }
            }
        }else{
            $arr = $leisure_mod->get($_GET['leishure_id']); 
        }
     
        //通过aera_id 获取当前所在的城市
        $selected =$leisure_mod-> get_area($arr['area_id']);
        //获取所在城市的店铺
        $shop_arr = $leisure_mod->get_shop_arr($arr['area_id']);
        $this->assign('shop_arr',$shop_arr);
        $arr['images'] = explode("||", $arr['images']);
        $arr['slide_link'] = explode("||", $arr['slide_link']);
        /* dump($arr); */  
        //获取所有地址，和详细地址名称   
        foreach ($leisure_info as $key => $leisure) {
            $temp = explode(",",$leisure['coordinate']);
           /*  $address_arr[]=array($temp[0],$temp[1],"地址:".$leisure['address_info']."\<br\>\<a href='index.php?app=index&act=leisure&leishure_id=$key'>查看详情\<\/a>");*/
            $address_arr[]=array($temp[0],$temp[1],"地址:".$leisure['address_info'],$key);

        }
        $address_arr = json_encode($address_arr);
        $coordinate = explode(",", $arr['coordinate']);
        $this->assign('coordinate',$coordinate);
        $this->assign('address_arr',$address_arr);
        $this->assign('leisure_info_all',$leisure_info);
        $this->assign('leisure_info',$arr);
        //获取初始化的城市
        $this->assign('selected',$selected);
        $this->assign('province_arr',$province_arr);
        $this->display("leisure_clubs.html");
    }
    function search_leisure(){
        if ($_POST['search']){
            $search = $_POST['search'];
            $leisure_mod =m('leisure');
            $conditions = array('conditions'=>"leisure_name like '%$search%'");
            $data = $leisure_mod ->find($conditions);
            if (empty($data)) {
                $out_data = array(
                            'code'=>2,
                            'message'=>'没有找到信息',
                            'data'=>$data);
            }else{
                $out_data = array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>$data);  
            }
        }else{
            $out_data = array(
                            'code'=>2,
                            'message'=>'数据为空',
                            'data'=>''); 
        }
        echo json_encode($out_data);
    }
    /**
     * [get_enterprise 响应ajax请求获取企业信息]
     * @return [type] [description]
     */
   
    function get_city_enterprise(){
        if (empty($_POST['area_id'])) {
            $array  = array(
                            'code' =>1,
                            'message'=>'参数异常',
                            'data' =>'');
        }else{
            $leisure_mod = m('leisure');
            $citys = $leisure_mod ->get_city_enterprise($_POST['area_id']);
            $array = array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>$citys);

        }
        
        echo json_encode($array);

    }
    function get_city()
    {
        if (empty($_POST['area_id'])) {
            $array  = array(
                            'code' =>1,
                            'message'=>'参数异常',
                            'data' =>'');
        }else{
            $leisure_mod = m('leisure');
            $citys = $leisure_mod ->get_city($_POST['area_id']);
            $array = array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>$citys);

        }
        
        echo json_encode($array);
    }
    function get_shop()
    {
        if (empty($_POST['area_id'])) {
            $array  = array(
                            'code' =>1,
                            'message'=>'参数异常',
                            'data' =>'');
        }else{
            $leisure_mod = m('leisure');
            $citys = $leisure_mod ->get_shop_arr($_POST['area_id']);
            $array = array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>$citys);

        }
        
        echo json_encode($array);

    }
    function enterprise()
    {   
        $provinceID = $_GET['provinceID'];
        $province = $_GET['province'];
        $area = $_GET['area'];
        $area_id = $_GET['area_id'];
        $leisure_mod = m('leisure');
        $province_arr = $leisure_mod->get_province();
        if (empty($_GET['page'])) {
            $limit = '8';
        } 
        
         //带条件查询
        if ($provinceID && $province && $area && $area_id) {
             $citys = $leisure_mod ->get_city_enterprise($provinceID);
             $this->assign('citys',$citys);
             $city_strs=$leisure_mod ->get_enterprise($area);
             if (empty($city_strs)) {
                 $city_strs='is_none';             
             }else{
                $where ="store.region_id in(".$city_strs.")";
             }
             
            /* dump( $where);*/
        }
      
        if ($city_strs == 'is_none') {
            $store_arr=array();
        }else{
        //获取要查询的分页的参数数目
    
            $goods_count = $this->_get_data_priv_store_count($where);
            $page = $this->_get_page(8);
            $page['item_count'] = $goods_count;
            $this->_format_page($page);
            $store_arr = $this->_get_data_priv_store_limit($page['limit'],$where);  

        }
        $this->assign('page_info', $page);
        /*分类加载*/
        $gcategorys = $this->_list_gcategory();
     //dump($gcategorys);
        $this->assign('gcategorys', $gcategorys);
        /*dump($province_arr);*/
        $out_data['store_arr'] = $store_arr;
        $this->assign("out_data", $out_data);
        $this->assign("province_arr",$province_arr);
        $this->display("enterprise.html");
    }
    /**
     * [get_shop_by_str 模糊查找企业名称]
     * @return [type] [description]
     */
    function get_shop_by_str(){
        if (empty($_POST['out_btn_con'])) {
            $array  = array(
                            'code' =>1,
                            'message'=>'参数异常',
                            'data' =>'');
        }else{
            $store_mod = m('store');
            $conditions = "store_name like '%{$_POST['out_btn_con']}%'";
            $store_arr = $store_mod ->find(array('conditions'=>$conditions,
                                                'fields' => 'store_name,store_id'));
            $array = array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>$store_arr);

        }
        echo json_encode($array);
    }

    /**
     * [_get_article_notice 获取当前动态文章信息]
     * @return [type] [description]
     */
    function _get_article_notice()
    {
        $acategory_mod =& m('acategory');
        $article_mod =& m('article');
        $data = $article_mod->find(array(
            'conditions' => 'cate_id=' . $acategory_mod->get_ACC(ACC_NOTICE) . ' AND if_show = 1',
            'order' => 'sort_order ASC, add_time DESC',
            'fields' => 'article_id, title, add_time',
            'limit' => $this->_num,
        ));
        return $data;
    }


    /**
     * [_get_data 获取茶叶风向标数据]
     * @return [type] [description]
     */
    function _get_data()
    {
        $db = &db();
        $drogue = $db->getall("select id, type, real_price,goods_id,show_date from (select * from ecm_drogue as b order by show_date desc) as d group by d.goods_id");
        if (!empty($drogue)) {
            foreach ($drogue as $key => $value) {
                //升跌=实际价格-参考价格
                $drogue_row = $db->getrow("select goods_id,goods_name from ecm_goods where goods_id=" . $value['goods_id']);
                //获取前一天的价格当做参考价
                $date = $value['show_date'] - (3600 * 24);
                $pre_sql = "select real_price from ecm_drogue where show_date ='" . $date . "' and goods_id='" . $value['goods_id'] . "'order by show_date desc";
                $value['ref_price'] = $db->getone($pre_sql);
                $i = 2;
                //如果为空进入循环直到有数据的那一天
                while (empty($value['ref_price'])) {
                    $date = $value['show_date'] - (3600 * 24 * $i);
                    $pre_sql = "select real_price from ecm_drogue where show_date ='" . $date . "' and goods_id='" . $value['goods_id'] . "'order by show_date desc";
                    $value['ref_price'] = $db->getone($pre_sql);
                    $i++;
                    if ($i > 50) {
                        break;
                    }
                }
                $value['tea_name'] = $drogue_row['goods_name'];
                $value['goods_id'] = $drogue_row['goods_id'];
                $value['fluctuate_price'] = $value['real_price'] - $value['ref_price'];
                if ($value['type'] == 0) {
                    if (empty($value['ref_price']) || $value['ref_price'] == 0.00) {
                        continue;
                    }
                    $value['percentage'] = $value['fluctuate_price'] / $value['ref_price'];
                    $value['percentage'] = $value['percentage'] * 100;
                    $value['percentage'] = number_format($value['percentage'], 1);
                    $value['status'] = $value['fluctuate_price'] >= 0 ? 1 : 0;
                    $value['fluctuate_price'] = abs($value['fluctuate_price']);
                    $value['percentage'] = abs($value['percentage']);
                    $drogue_a[] = $value;
                } elseif ($value['type'] == 1) {
                    //升跌=实际价格-参考价格
                    if (empty($value['ref_price']) || $value['ref_price'] == 0.00) {
                        continue;
                    }
                    $value['percentage'] = $value['fluctuate_price'] / $value['ref_price'];
                    $value['percentage'] = $value['percentage'] * 100;
                    $value['percentage'] = number_format($value['percentage'], 1);
                    $value['status'] = $value['fluctuate_price'] >= 0 ? 1 : 0;
                    $value['fluctuate_price'] = abs($value['fluctuate_price']);
                    $value['percentage'] = abs($value['percentage']);
                    $drogue_b[] = $value;

                } elseif ($value['type'] == 2) {
                    //升跌=实际价格-参考价格
                    if (empty($value['ref_price']) || $value['ref_price'] == 0.00) {
                        continue;
                    }
                    $value['percentage'] = $value['fluctuate_price'] / $value['ref_price'];
                    $value['percentage'] = $value['percentage'] * 100;
                    $value['percentage'] = number_format($value['percentage'], 1);
                    $value['status'] = $value['fluctuate_price'] >= 0 ? 1 : 0;
                    $value['fluctuate_price'] = abs($value['fluctuate_price']);
                    $value['percentage'] = abs($value['percentage']);
                    $drogue_c[] = $value;
                }

                if ($value['status'] === 1) {
                    $drogue_upmount[] = $value;
                }

            }
        } else {
            $options['drogue_a'] = array();
            $options['drogue_b'] = array();
            $options['drogue_c'] = array();
        }

        $options['drogue_a'] = $drogue_a;
        $options['drogue_b'] = $drogue_b;
        $options['drogue_c'] = $drogue_c;
        $options['drogue_upmount'] = $this->_get_upmount($drogue_upmount);
        /*dump($options['drogue_upmount']);*/
        return $options;
    }

    /*涨幅排序*/
    function _get_upmount($data_upmount)
    {
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'percentage',       //排序字段
        );
        $arrSort = array();
        foreach ($data_upmount as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }

        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $data_upmount);
        }

        return array_slice($data_upmount, 0, 10);

    }

    /*获取推荐的图片信息*/
    function _get_data_new()
    {

        $recom_mod =& m('recommend');

        $img_goods_list = $recom_mod->get_recommended_goods(7, 3, true, 1);

        return $img_goods_list;
    }

    /*获取非自营店铺信息*/
    function _get_data_priv_store($limit = 6)
    {
        $db =& db();
        $inner_join = "SELECT
			store.store_id,
			store.store_name,
            store.store_logo
		FROM
			ecm_store AS store
		INNER JOIN ecm_category_store AS cs ON store.store_id = cs.store_id
		INNER JOIN ecm_scategory AS es ON cs.cate_id = es.cate_id
		WHERE
			es.cate_name NOT LIKE 'VIP%'
		AND es.parent_id = 0 order by store.sort_order ASC limit $limit";
        $store_arr = $db->getall($inner_join);
        return $store_arr;
    }

    /*设置会员录入*/
    function set_member()
    {
        /*权限检查*/
        $result = $this->rbac_check();
        if (!$result) {
            $this->show_warning('没有权限！请联系管理员！');
        }

        if (IS_POST) {
            $current_user_id = $this->visitor->get('user_id');
            $user_id = $_POST['user_id'];
            $arr = array(
                'member_card_num' => $_POST['member_card_num'],
                'inner_card_num' => $_POST['inner_card_num'],
                'member_level' => $_POST['member_level'],
                'card_user_id' => $current_user_id,
                'real_name' => $_POST['real_name'],
                'im_qq' => $_POST['phone_mob'],
                'card_store_name' => $_POST['card_store_name'],
                'phone_mob' => $_POST['phone_mob'],
                'card_time' => time()
            );
            $member_model =& m("member");
            $result = $member_model->edit($user_id, $arr);
            if (!$result) {
                $this->show_warning('编辑异常');
                return;
            }
            $this->show_message('编辑成功');
            return;

        } else {
            $this->display("set_member.html");
        }


    }

    /**
     * [rbac_check 权限检查]
     * @return [bool] []
     */
    function rbac_check()
    {
        $visitor = $this->visitor->get();
        if ($visitor['member_level'] >= 100) {
            return ture;
        }
        return false;
    }

    /*响应ajax请求*/
    function serch_username()
    {

        $_GET['user_name'];
        $db =& db();
        $sql = "select * from ecm_member where user_name= '" . $_GET['user_name'] . "'";
        $result = $db->getrow($sql);
        if ($result) {
            $out_data = array("code" => 0,
                "message" => "请求成功",
                "data" => $result);
        } else {
            $out_data = array("code" => 1,
                "message" => "该用户名不存在",
                "data" => "");
        }

        echo json_encode($out_data);
    }

    /**
     * [check_outer_number 检查外部卡号]n
     * @return [type] [description]
     */
    function check_outer_number()
    {
        $member_card_num = $_GET['outer_number'];
        $user_id = $_GET['user_id'];
        if (empty($member_card_num) || empty($user_id)) {
            $out_data = array(
                "code" => 1,
                "message" => "卡号数据异常",
                "data" => array("user_id" => $user_id, "outer_number" => $member_card_num)
            );
        } else {
            $db = &db();
            $have_card = $db->getone("select user_id from ecm_member where member_card_num='$member_card_num' and user_id<>'$user_id'");
            if ($have_card) {
                $out_data = array(
                    "code" => 1,
                    "message" => "外部卡号重复",
                    "data" => $have_card
                );

            } else {
                $out_data = array(
                    "code" => 0,
                    "message" => "请求成功",
                    "data" => $have_card
                );
            }
        }
        echo json_encode($out_data);
    }

    function check_inner_number()
    {
        $member_card_num = $_GET['inner_number'];
        $user_id = $_GET['user_id'];
        if (empty($member_card_num) || empty($user_id)) {
            $out_data = array(
                "code" => 1,
                "message" => "卡号数据异常",
                "data" => array("user_id" => $user_id, "inner_number" => $member_card_num)
            );
        } else {
            $db = &db();
            $have_card = $db->getone("select user_id from ecm_member where inner_card_num='$member_card_num' and user_id<>'$user_id'");
            if ($have_card) {
                $out_data = array(
                    "code" => 1,
                    "message" => "内部卡号重复",
                    "data" => $have_card
                );

            } else {
                $out_data = array(
                    "code" => 0,
                    "message" => "请求成功",
                    "data" => $have_card
                );
            }
        }
        echo json_encode($out_data);
    }

    function tea()
    {
        //获取登录信息
        $user_name = $this->visitor->get('user_name');
        $out_data['user_name'] = $user_name;
        $drogue_arr = $this->_get_data();
        $out_data['drogue_arr'] = $drogue_arr;
        $this->assign("out_data", $out_data);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea  ', '茶商首页');
        /* $this->_curitem('my_store');
            $this->_curmenu('my_store');*/
        $index_model = m('index_show');
        $index_arr = $index_model->get(1);
        $index_arr = $index_model->return_arr_val($index_arr);
        $this->assign('qq_arr',$index_arr ['qq_num_des']);
        $this->display("tea.html");

    }

    function market()
    {
        $user_name = $this->visitor->get('user_name');
        $out_data['user_name'] = $user_name;
        $this->assign("out_data", $out_data);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea', '行情参考');
        $cate_id = $_GET['cate_id'];
        if (empty($cate_id)) {
            $cate_id = 1;
        }
        if (empty($_GET['page'])) {
            $limit = '25';
        } else {
            $limit = '25';
        }
        $gcategory_mod =& bm('gcategory');
        $layer = $gcategory_mod->get_layer($cate_id, true);
        /* 商品展示方式 */
        $display_mode = ecm_getcookie('goodsDisplayMode');
        if (empty($display_mode) || !in_array($display_mode, array('list', 'squares'))) {
            $display_mode = 'squares'; // 默认格子方式
        }
        $this->assign('display_mode', $display_mode);
        $goods = array();
        //获取要查询的分页的参数数目
        $goods_count = $this->_get_goods_list_count($cate_id, $_GET['brand'], $layer);
        //配置分页信息
        $page = $this->_get_page(15);
        $page['item_count'] = $goods_count;
        $this->_format_page($page);
        $this->assign('page_info', $page);
        /*获取相关的产品*/
        $goods_list = $this->_get_goods_list($cate_id, $_GET['brand'], $layer, $page['limit']);
        $this->assign('goods_list', $goods_list);
        //1.先获取茶叶的顶级分类
        $db = db();
        $choose = array();
        if ($layer == 1) {
            //茶叶分类
            $category = $db->getall("select cate_id, cate_name from ecm_gcategory where parent_id=1 and cate_name ='普洱熟茶' or cate_name ='普洱生茶'");
            // 取出茶叶品牌
            $brands = $this->_get_brands($cate_id);
            $this->assign('categories', $category);

        } else {
            $cate_name = $db->getone("select cate_name from ecm_gcategory where cate_id=$cate_id");

            //茶页就3个等级，直接取出下个等级的东西
            if ($layer == 2) {
                $category = $db->getall("select cate_id, cate_name from ecm_gcategory where parent_id=$cate_id");
                $this->assign('categories', $category);
                $brands = $this->_get_brands($cate_id);
                $choose['category'][] = array("cate_id" => $cate_id, "cate_name" => $cate_name);
                //设置该分类被选中
            } else {
                //第三级需要获取第二级选中的名字和id
                $cate_parent = "select cate_name,cate_id from ecm_gcategory where cate_id=(select parent_id from ecm_gcategory where cate_id=$cate_id)";
                $parent_cate = $db->getrow($cate_parent);
                $choose['category'][] = $parent_cate;
                $choose['category'][] = array("cate_id" => $cate_id, "cate_name" => $cate_name);
                /*随便取一个分类的内容*/
                $category = $db->getall("select cate_id, cate_name from ecm_gcategory where parent_id=42");
                $this->assign('categories', $category);
                $this->assign('category_limit', true);
            }

        }


        //dump($choose);
        $this->assign("layer", $layer);
        $this->assign("choose", $choose);
        // 查询参数
        $param = $this->_get_query_param();
        if (empty($param)) {
            header('Location: index.php?app=tea');
            exit;
        }
        if (isset($param['cate_id']) && $param['layer'] === false) {
            $this->show_warning('no_such_category');
            return;
        }
        $this->assign("brands", $brands);

        /*$stats = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);
        var_dump($stats['by_brand']);
        //获取品牌相关分类
        $this->assign('brands', $stats['by_brand']);   
        $this->assign('price_intervals', $stats['by_price']);
        $this->assign('filters', $this->_get_filter($param));
        $this->assign('categories', $stats['by_category']);*/
        $this->display("market.html");
    }

    /**
     * [_get_goods_list 获取商品列表]
     * @param  [type] $cate_id [上级和当前的id]
     * @param  [type] $brand   [品牌，为空为所有]
     * @return [type]          [description]
     */
    function _get_goods_list_count($cate_id, $brand, $layer)
    {
        //dump($layer);
        if ($layer == 1) {
            $db = db();
            $sql = "select * from ecm_gcategory";
            $categorys = $db->getall($sql);
            $tea_id_arr = $this->_tree($categorys, $cate_id);
            $tea_id_str = implode($tea_id_arr, ",");
            /*dump($tea_id_str);*/
            if (empty($tea_id_str)) {
                return array();
            }
            if (empty($_GET['brand'])) {
                $goods = "select count(*) from ecm_goods where  brand <> '' and cate_id in ($tea_id_str)";
            } else {
                $goods = "select count(*) from ecm_goods where cate_id in ($tea_id_str) and brand='" . $_GET['brand'] . "'";
            }

            $result = $db->getone($goods);

        } elseif ($layer == 2) {
            $db = db();
            $sql = "select * from ecm_gcategory";
            $categorys = $db->getall($sql);
            $tea_id_arr = $this->_tree($categorys, $cate_id);
            $tea_id_str = implode($tea_id_arr, ",");
            /*dump($tea_id_str);*/
            if (empty($tea_id_str)) {
                return array();
            }
            if (empty($_GET['brand'])) {
                $goods = "select count(*) from ecm_goods where  brand <> '' and cate_id in ($tea_id_str)";
            } else {
                $goods = "select count(*) from ecm_goods where cate_id in ($tea_id_str) and brand='" . $_GET['brand'] . "'";
            }
            $result = $db->getone($goods);

        } else {
            $db = db();
            $sql = "select * from ecm_gcategory";
            $categorys = $db->getall($sql);
            if (empty($_GET['brand'])) {
                $goods = "select count(*) from ecm_goods where  brand <> '' and cate_id ='" . $_GET['cate_id'] . "'";
            } else {
                $goods = "select count(*) from ecm_goods where cate_id =" . $_GET['cate_id'] . " and brand='" . $_GET['brand'] . "'";
            }

            $result = $db->getone($goods);

        }
        return $result;
    }

    /**
     * [_get_goods_list 获取商品列表]
     * @param  [type] $cate_id [上级和当前的id]
     * @param  [type] $brand   [品牌，为空为所有]
     * @return [type]          [description]
     */
    function _get_goods_list($cate_id, $brand, $layer, $limit)
    {
        //dump($layer);
        if ($layer == 1) {
            $db = db();
            $sql = "select * from ecm_gcategory";
            $categorys = $db->getall($sql);
            $tea_id_arr = $this->_tree($categorys, $cate_id);
            $tea_id_str = implode($tea_id_arr, ",");
            /*dump($tea_id_str);*/
            if (empty($tea_id_str)) {
                return array();
            }
            if (empty($_GET['brand'])) {
                $goods = "select * from ecm_goods where  brand <> '' and cate_id in ($tea_id_str) limit " . $limit;
            } else {
                $goods = "select * from ecm_goods where cate_id in ($tea_id_str) and brand='" . $_GET['brand'] . "'limit " . $limit;
            }

            $result = $db->getall($goods);

        } elseif ($layer == 2) {
            $db = db();
            $sql = "select * from ecm_gcategory";
            $categorys = $db->getall($sql);
            $tea_id_arr = $this->_tree($categorys, $cate_id);
            $tea_id_str = implode($tea_id_arr, ",");
            /*dump($tea_id_str);*/
            if (empty($tea_id_str)) {
                return array();
            }
            if (empty($_GET['brand'])) {
                $goods = "select * from ecm_goods where  brand <> '' and cate_id in ($tea_id_str)limit " . $limit;
            } else {
                $goods = "select * from ecm_goods where cate_id in ($tea_id_str) and brand='" . $_GET['brand'] . "'limit " . $limit;
            }
            $result = $db->getall($goods);

        } else {
            $db = db();
            $sql = "select * from ecm_gcategory";
            $categorys = $db->getall($sql);
            if (empty($_GET['brand'])) {
                $goods = "select * from ecm_goods where  brand <> '' and cate_id ='" . $_GET['cate_id'] . "'limit " . $limit;
            } else {
                $goods = "select * from ecm_goods where cate_id =" . $_GET['cate_id'] . " and brand='" . $_GET['brand'] . "'limit " . $limit;
            }

            $result = $db->getall($goods);

        }
        return $result;

    }

    /**
     * 根据查询条件取得分组统计信息
     *
     * @param   array $param 查询参数（参加函数_get_query_param的返回值说明）
     * @param   bool $cached 是否缓存
     * @return  array(
     *              'total_count' => 10,
     *              'by_category' => array(id => array('cate_id' => 1, 'cate_name' => 'haha', 'count' => 10))
     *              'by_brand'    => array(array('brand' => brand, 'count' => count))
     *              'by_region'   => array(array('region_id' => region_id, 'region_name' => region_name, 'count' => count))
     *              'by_price'    => array(array('min' => 10, 'max' => 50, 'count' => 10))
     *          )
     */
    function _get_group_by_info($param, $cached)
    {
        $data = false;

        if ($cached) {
            $cache_server =& cache_server();
            $key = 'group_by_info_' . var_export($param, true);
            $data = $cache_server->get($key);
        }

        if ($data === false) {
            $data = array(
                'total_count' => 0,
                'by_category' => array(),
                'by_brand' => array(),
                'by_region' => array(),
                'by_price' => array()
            );

            $goods_mod =& m('goods');
            $store_mod =& m('store');
            $table = " {$goods_mod->table} g LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id";
            $conditions = $this->_get_goods_conditions($param);
            $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions;
            $total_count = $goods_mod->getOne($sql);
            if ($total_count > 0) {
                $data['total_count'] = $total_count;
                /* 按分类统计 */
                $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
                $sql = "";
                if ($cate_id > 0) {
                    $layer = $param['layer'];
                    if ($layer < 4) {
                        $sql = "SELECT g.cate_id_" . ($layer + 1) . " AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_" . ($layer + 1) . " > 0 GROUP BY g.cate_id_" . ($layer + 1) . " ORDER BY count DESC";
                    }
                } else {
                    $sql = "SELECT g.cate_id_1 AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_1 > 0 GROUP BY g.cate_id_1 ORDER BY count DESC";
                }

                if ($sql) {
                    $category_mod =& bm('gcategory');
                    $children = $category_mod->get_children($cate_id, true);
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res)) {
                        $data['by_category'][$row['id']] = array(
                            'cate_id' => $row['id'],
                            'cate_name' => $children[$row['id']]['cate_name'],
                            'count' => $row['count']
                        );
                    }
                }

                /* 按品牌统计 */
                $sql = "SELECT g.brand, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.brand > '' GROUP BY g.brand ORDER BY count DESC";
                $by_brands = $goods_mod->db->getAllWithIndex($sql, 'brand');

                /* 滤去未通过商城审核的品牌 */
                if ($by_brands) {
                    $m_brand = &m('brand');
                    $brand_conditions = db_create_in(addslashes_deep(array_keys($by_brands)), 'brand_name');
                    $brands_verified = $m_brand->getCol("SELECT brand_name FROM {$m_brand->table} WHERE " . $brand_conditions . ' AND if_show=1');
                    foreach ($by_brands as $k => $v) {
                        if (!in_array($k, $brands_verified)) {
                            unset($by_brands[$k]);
                        }
                    }
                }
                $data['by_brand'] = $by_brands;


                /* 按地区统计 */
                $sql = "SELECT s.region_id, s.region_name, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND s.region_id > 0 GROUP BY s.region_id ORDER BY count DESC";
                $data['by_region'] = $goods_mod->getAll($sql);

                /* 按价格统计 */
                if ($total_count > NUM_PER_PAGE) {
                    $sql = "SELECT MIN(g.price) AS min, MAX(g.price) AS max FROM {$table} WHERE" . $conditions;
                    $row = $goods_mod->getRow($sql);
                    $min = $row['min'];
                    $max = min($row['max'], MAX_STAT_PRICE);
                    $step = max(ceil(($max - $min) / PRICE_INTERVAL_NUM), MIN_STAT_STEP);
                    $sql = "SELECT FLOOR((g.price - '$min') / '$step') AS i, count(*) AS count FROM {$table} WHERE " . $conditions . " GROUP BY i ORDER BY i";
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res)) {
                        $data['by_price'][] = array(
                            'count' => $row['count'],
                            'min' => $min + $row['i'] * $step,
                            'max' => $min + ($row['i'] + 1) * $step,
                        );
                    }
                }
            }

            if ($cached) {
                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
            }
        }

        return $data;
    }

    /**
     * 取得查询条件语句
     *
     * @param   array $param 查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_goods_conditions($param)
    {
        /* 组成查询条件 */
        $conditions = " g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态,
        if (isset($param['keyword'])) {
            $conditions .= $this->_get_conditions_by_keyword($param['keyword'], ENABLE_SEARCH_CACHE);
        }
        if (isset($param['cate_id'])) {
            $conditions .= " AND g.cate_id_{$param['layer']} = '" . $param['cate_id'] . "'";
        }
        if (isset($param['brand'])) {
            $conditions .= " AND g.brand = '" . $param['brand'] . "'";
        }
        if (isset($param['region_id'])) {
            $conditions .= " AND s.region_id = '" . $param['region_id'] . "'";
        }
        if (isset($param['price'])) {
            $min = $param['price']['min'];
            $max = $param['price']['max'];
            $min > 0 && $conditions .= " AND g.price >= '$min'";
            $max > 0 && $conditions .= " AND g.price <= '$max'";
        }

        return $conditions;
    }

    /**
     * 根据关键词取得查询条件（可能是like，也可能是in）
     *
     * @param   array $keyword 关键词
     * @param   bool $cached 是否缓存
     * @return  string      " AND (0)"
     *                      " AND (goods_name LIKE '%a%' AND goods_name LIKE '%b%')"
     *                      " AND (goods_id IN (1,2,3))"
     */
    function _get_conditions_by_keyword($keyword, $cached)
    {
        $conditions = false;

        if ($cached) {
            $cache_server =& cache_server();
            $key1 = 'query_conditions_of_keyword_' . join("\t", $keyword);
            $conditions = $cache_server->get($key1);
        }

        if ($conditions === false) {
            /* 组成查询条件 */
            $conditions = array();
            foreach ($keyword as $word) {
                $conditions[] = "g.goods_name LIKE '%{$word}%'";
            }
            $conditions = join(' AND ', $conditions);

            /* 取得满足条件的商品数 */
            $goods_mod =& m('goods');
            $sql = "SELECT COUNT(*) FROM {$goods_mod->table} g WHERE " . $conditions;
            $current_count = $goods_mod->getOne($sql);
            if ($current_count > 0) {
                if ($current_count < MAX_ID_NUM_OF_IN) {
                    /* 取得商品表记录总数 */
                    $cache_server =& cache_server();
                    $key2 = 'record_count_of_goods';
                    $total_count = $cache_server->get($key2);
                    if ($total_count === false) {
                        $sql = "SELECT COUNT(*) FROM {$goods_mod->table}";
                        $total_count = $goods_mod->getOne($sql);
                        $cache_server->set($key2, $total_count, SEARCH_CACHE_TTL);
                    }

                    /* 不满足条件，返回like */
                    if (($current_count / $total_count) < MAX_HIT_RATE) {
                        /* 取得满足条件的商品id */
                        $sql = "SELECT goods_id FROM {$goods_mod->table} g WHERE " . $conditions;
                        $ids = $goods_mod->getCol($sql);
                        $conditions = 'g.goods_id' . db_create_in($ids);
                    }
                }
            } else {
                /* 没有满足条件的记录，返回0 */
                $conditions = "0";
            }

            if ($cached) {
                $cache_server->set($key1, $conditions, SEARCH_CACHE_TTL);
            }
        }

        return ' AND (' . $conditions . ')';
    }


    /**
     * 取得查询参数（有值才返回）
     *
     * @return  array(
     *              'keyword'   => array('aa', 'bb'),
     *              'cate_id'   => 2,
     *              'layer'     => 2, // 分类层级
     *              'brand'     => 'ibm',
     *              'region_id' => 23,
     *              'price'     => array('min' => 10, 'max' => 100),
     *          )
     */
    function _get_query_param()
    {
        static $res = null;
        if ($res === null) {
            $res = array();

            // keyword
            $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
            if ($keyword != '') {
                //$keyword = preg_split("/[\s," . Lang::get('comma') . Lang::get('whitespace') . "]+/", $keyword);
                $tmp = str_replace(array(Lang::get('comma'), Lang::get('whitespace'), ' '), ',', $keyword);
                $keyword = explode(',', $tmp);
                sort($keyword);
                $res['keyword'] = $keyword;
            }
            //@jjc  cate_id为空就为1，只能显示茶叶因为茶叶就是1
            if (empty($_GET['cate_id'])) {
                $_GET['cate_id'] = 1;
            }
            // cate_id
            if (isset($_GET['cate_id']) && intval($_GET['cate_id']) > 0) {
                $res['cate_id'] = $cate_id = intval($_GET['cate_id']);
                $gcategory_mod =& bm('gcategory');
                $res['layer'] = $gcategory_mod->get_layer($cate_id, true);
            }

            // brand
            if (isset($_GET['brand'])) {
                $brand = trim($_GET['brand']);
                $res['brand'] = $brand;
            }

            // region_id
            if (isset($_GET['region_id']) && intval($_GET['region_id']) > 0) {
                $res['region_id'] = intval($_GET['region_id']);
            }

            // price
            if (isset($_GET['price'])) {
                $arr = explode('-', $_GET['price']);
                $min = abs(floatval($arr[0]));
                $max = abs(floatval($arr[1]));
                if ($min * $max > 0 && $min > $max) {
                    list($min, $max) = array($max, $min);
                }

                $res['price'] = array(
                    'min' => $min,
                    'max' => $max
                );
            }
        }

        return $res;
    }

    /**
     * 取得过滤条件
     */
    function _get_filter($param)
    {
        static $filters = null;
        if ($filters === null) {
            $filters = array();
            if (isset($param['keyword'])) {
                $keyword = join(' ', $param['keyword']);
                $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
            }
            isset($param['brand']) && $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $param['brand']);
            if (isset($param['region_id'])) {
                // todo 从地区缓存中取
                $region_mod =& m('region');
                $row = $region_mod->get(array(
                    'conditions' => $param['region_id'],
                    'fields' => 'region_name'
                ));
                $filters['region_id'] = array('key' => 'region_id', 'name' => LANG::get('region'), 'value' => $row['region_name']);
            }
            if (isset($param['price'])) {
                $min = $param['price']['min'];
                $max = $param['price']['max'];
                if ($min <= 0) {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('le') . ' ' . price_format($max));
                } elseif ($max <= 0) {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                } else {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
            }
        }


        return $filters;
    }

    /*获取品牌信息*/
    function _get_brands($cate_id)
    {
        //1.首先获取1级分类下的所有品牌
        $db =& db();
        $sql = "select * from ecm_gcategory";
        $categorys = $db->getall($sql);
        $tea_id_arr = $this->_tree($categorys, $cate_id);
        $tea_id_str = implode($tea_id_arr, ",");

        $brand = "select cate_id,brand from ecm_goods where  brand <> '' and cate_id in ($tea_id_str) group by brand ";
        $brands = $db->getall($brand);
        $brands = array_filter($brands);
        /*$new_brand=array_unique($brands);*/
        return $brands;

    }

    //取出顶级为茶的所有商品类
    function _tree($goods_categorys, $parent_id)
    {
        static $tea_id = array();
        foreach ($goods_categorys as $goods_category) {
            if ($goods_category['parent_id'] == $parent_id) {
                $tea_id[] = $goods_category['cate_id'];
                $this->_tree($goods_categorys, $goods_category['cate_id']);
            }
        }
        return $tea_id;
    }

    function agent()
    {
        $user_name = $this->visitor->get('user_name');
        $out_data['user_name'] = $user_name;
        $this->assign("out_data", $out_data);
        $db = db();
        $sql = "select * from ecm_agent";
        $arr = $db->getall($sql);
        $this->assign('agent_info', $arr);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea', '经纪人');
        $this->display("agent.html");
    }

    function news()
    {
        $user_name = $this->visitor->get('user_name');
        $out_data['user_name'] = $user_name;
        $this->assign("out_data", $out_data);
        $drogue_arr = $this->_get_data();
        $out_data['drogue_arr'] = $drogue_arr;
        $this->assign("out_data", $out_data);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea', '茶叶资讯');
        $this->display('news.html');
    }

    function transaction()
    {
        $transaction_mod =& m('transaction');
        $recharge_mod = m('recharge_log');
        $member_mod = m('member');
        $db=db();
        $user_info = $this->visitor->get();
        $app_mod = m('transaction_apply');
         //如果token为空则生成一个token
        if(!isset($_SESSION['token']) || $_SESSION['token']=='') {
          $recharge_mod->set_token();
        }
        $this->assign('token',$_SESSION['token']);
        //获取交易的数据信息
        $transaction_arr= $transaction_mod->find(array('conditions'=>'transaction_status>-1 and transaction_count>0'));
        
        foreach ($transaction_arr as $key => $value) {
            $transaction_arr[$key]['date_des']=date('Y-m-d H:i:s',$value['transaction_time']);
             if ($value['goods_type'] == 2) {
                    $transaction_arr[$key]['type_des'] = '中期茶';
                } else if ($value['goods_type'] == 1) {
                    $transaction_arr[$key]['type_des'] = '新茶';
                } else {
                    $transaction_arr[$key]['type_des'] = '当年茶';
                }
            $transaction_arr[$key]['temp_sn']=sprintf("%06d", $value['transaction_sn']);

        }
        $this->assign('transaction_arr',$transaction_arr);
       
        if ($_POST) {
            //校验token
              if (!$recharge_mod->valid_token()) {
                     echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('表单无法重复提交，请刷新页面重试');
                return;
            }

            if (empty($_POST['goods_id'])) {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('参数异常');
                return;
            }
            $_POST['tea_type']=$this->_get_tea_des($_POST['goods_id']);
            if ($arr = $this->_check_is_empty($_POST)) {
                //数据正常
                extract($arr);
                if (strchr($tea_type, '新')) {
                    $tea_type = 1;
                } elseif (strchr($tea_type, '当')) {
                    $tea_type = 0;
                } elseif (strchr($tea_type, '中')) {
                    $tea_type = 2;
                }

                if ($fruit == 1) {
                    //买
                    $transaction_status = 0;
                    //校验支付密码
                    $result = $this->check_pwd($confirm_pwd);
                    if (!$result) {
                        echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                        $this->show_warning('支付密码错误，请重新提交表单');
                        return;
                    }

                } else {
                    $transaction_status = 1;
                }

                $user_id = $this->visitor->get('user_id');
                $member_info = $member_mod ->get($user_id);
                $waiting_price = (int)(string)($transaction_price*100)*$transaction_count/100; 
                $arr = array('goods_id' => $goods_id,
                    'goods_type' => $tea_type,
                    'transaction_status' => $transaction_status,
                    'goods_name' => $goods_name,
                    'transaction_price' => $transaction_price,
                    'transaction_count' => $transaction_count,
                    'user_id' => $user_id,
                    'transaction_time' => time(),
                    'transaction_from_sn' => -1,
                    'date_format' => date('Y-m-d', time()),
                    'have_transaction' => 0,
                    'waiting_pay_price'=>$waiting_price
                );
                
                $use_money = $this->get_user_count();
                $use_money_temp=(int)(string)($use_money*100);
                $waiting_price_temp =(int)(string)($waiting_price*100);
                $able_use_money = $recharge_mod ->get_use_money($user_id);//可以用的钱
                if ($waiting_price_temp > (int)(string)($able_use_money*100)) {
                     $this->show_warning('余额不足');
                     return;
                }
                $db->query('SET AUTOCOMMIT=0');
                $update_money=($use_money_temp-$waiting_price_temp)/100;
                 //减少金额
                $abstract="update ecm_member set use_money= '$update_money' where user_id=".$user_id;
                $ab_result=$db->query($abstract);
                //增加记录
                $transaction = $transaction_mod->add($arr);
                if ($transaction) {
                     $money_hitory_mod =& m('money_history');
                     $have_add = $money_hitory_mod ->get(array('fields'=>'money_history_id','conditions'=>"transaction_sn ='$transaction'AND money_from ='0' AND platform_from ='2' AND transaction_type ='1'"));
                     if (!$have_add) {
                          //金额日志记录
                        $add_money_arr = array(
                        'transaction_sn' => $transaction,//历史id
                        'money_from' => 0,
                        'transaction_type' => 1,//0,收入 1支出
                        'receive_money' => $waiting_price,
                        'pay_time' => time(),
                        'platform_from' => 2,//0,茶通历史表，1，商城 2，transaction
                        'use_money_history' => $update_money,
                        'user_id' => $user_id,
                        'comments' =>"申请买入$goods_name 扣款",
                        );
                        $money_log = $money_hitory_mod->add($add_money_arr);

                        //将要买入的金额临时转入到admin账户
                        //4.1 获取到admin账户的金额
                        $admin_money =  $member_mod ->get_money(1);
                        //4.2 计算增加后的金额
                        $set_admin_money = ((int)(string)($waiting_price*100)+(int)(string)(100*$admin_money))/100;
                        //4.3 编辑金额
                        $edit_admin_array =  array('use_money'=>$set_admin_money);
                        $result_member_admin = $member_mod ->edit(1,$edit_admin_array);
                        //4.4 增加admin的金额日志记录
                         $add_admin_array = array(
                            'transaction_sn'     => $transaction,//充值id
                            'money_from'         => 0,//0预存款，1支付宝, 2微信
                            'transaction_type'   => 0,//0,收入 1支出
                            'receive_money'      => $waiting_price,//收入或减去的金额
                            'pay_time'           => time(),//支付时间
                            'platform_from'      => 2,//0,茶通历史表，1，商城 2，transaction3,个人充值
                            'use_money_history'  => $set_admin_money,//当前的金额
                            'user_id'            => 1,//user_id
                            'comments'           => "用户({$member_info['user_name']})申请买入商品(id={$goods_id}),单价是({$transaction_price})元，数量$transaction_count"//备注
                        ); 
                        $result_add_admin = $money_hitory_mod ->add($add_admin_array); 

                    } else{
                        /*设置为false让以上修改都不成功*/
                        $money_log = false;
                    } 
                    
                }
                if ($money_log && $transaction &&  $ab_result && $result_add_admin && $result_member_admin) {  
                    $this->transaction_produce();
                    $db->query('COMMIT');
                    $db->query("SET AUTOCOMMIT=1");
                    echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                    $this->show_message('添加成功',
                        'edit_again', 'index.php?app=index&act=transaction',
                        'back_list', 'index.php?app=tea'    
                    );
                    return;
                } else {
                     
                     $db->query('ROLLBACK');
                     $db->query("SET AUTOCOMMIT=1");
                     echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                     $this->show_warning('添加失败');
                } 
            } else {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('数据异常,请重新提交');
            }
        }
         //获取可以申请炒茶的产品名称
        $get_sql="select goods_id,goods_name from ecm_share_tea";
        $transaction_apply = $db->getall($get_sql);
        $this->assign('transaction_apply',$transaction_apply);
        //获取已经申请的信息
        $apply_arr = $app_mod->get_apply_transaction($user_info['user_id']);
        $this->assign('apply_count',count($apply_arr));
        $apply_arr = json_encode($apply_arr);
        $this->assign('apply_arr',$apply_arr);
        //持仓明细相关信息
        $particulars=$transaction_mod ->get_warehouse($user_info['user_id']);
        $this->assign('particulars',$particulars);
        if ($user_info['member_level'] != 1) {
            $this->flush_html('没有权限', 0);
            die;
        }
        $agent_transaction = $this->_get_agent_transaction($user_info['user_id']);
        /*dump($agent_transaction);*/
        if (!empty($agent_transaction)) {
            foreach ($agent_transaction as $key => $agent) {
                # code...
                if ($agent['goods_type'] == 2) {
                    $agent_transaction[$key]['type_des'] = '中期茶';
                } else if ($agent['goods_type'] == 1) {
                    $agent_transaction[$key]['type_des'] = '新茶';
                } else {
                    $agent_transaction[$key]['type_des'] = '当年茶';
                }
                $agent_transaction[$key]['total_count'] = $agent['transaction_price'] * $agent['transaction_count'];
                if ($agent['transaction_status'] == 1) {
                    $agent_transaction[$key]['status_des'] = '卖';
                } else {
                    $agent_transaction[$key]['status_des'] = '买';
                }
                $agent_transaction[$key]['format_time'] = date('Y-m-d H:i', $agent['transaction_time']);
            }
        }
        $sql="SELECT * FROM ecm_transaction_history AS h inner JOIN ecm_transaction AS t on  h.sell_sn_id = t.transaction_sn OR h.buy_sn_id = t.transaction_sn WHERE user_id ={$user_info['user_id']}"; 
        $history_transaction= $db->getall($sql);
         if (!empty($history_transaction)) {
            foreach ($history_transaction as $key => $agent) {
                # code...
                if ($agent['goods_type'] == 2) {
                    $history_transaction[$key]['type_des'] = '中期茶';
                } else if ($agent['goods_type'] == 1) {
                    $history_transaction[$key]['type_des'] = '新茶';
                } else {
                    $history_transaction[$key]['type_des'] = '当年茶';
                }
                $history_transaction[$key]['total_count'] = $agent['transaction_price'] * $agent['transaction_history_count'];
                if ($agent['transaction_status'] == 1) {
                    $history_transaction[$key]['status_des'] = '卖';
                } else {
                    $history_transaction[$key]['status_des'] = '买';
                }
                $history_transaction[$key]['format_time'] = date('Y-m-d H:i', $agent['transaction_time']);
            }
        }
        /*dump($history_transaction);*/

        $this->assign('history_transaction',$history_transaction);
        $this->assign('agent_transaction', $agent_transaction);
        $user_name = $this->visitor->get('user_name');
        $out_data['user_name'] = $user_name;
        $drogue_arr = $this->_get_data();
        $this->assign('user_id',$user_info['user_id']);
        $out_data['drogue_arr'] = $drogue_arr;
        $this->assign("out_data", $out_data);
        $this->import_resource(array(
            'script' => 'My97DatePicker/WdatePicker.js,jquery-form.js,echart/build/dist/echarts.js,v531_valid.js',
        ));
        $this->get_drogue_info_echarts();
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea', '茶叶交易');
        $this->display('transaction.html');
    }
    function transaction_apply(){

       if (empty($_POST)) {
            echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('非法操作');
                return;
            
        } 
        $recharge_mod = m('recharge_log');
        if (!$recharge_mod->valid_token()) {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('表单无法重复提交，请刷新页面重试');
                return;
        }
        $user_info = $this->visitor->get();
        if (empty($user_info)) {
            echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('非法操作');
                return;
            
        } 
        $add = array(
                     'produce_count'=>intval($_POST['produce_count']),
                     'produce_price'=> number_format($_POST['produce_price'],2),
                     'goods_id' =>intval($_POST['goods_id']),
                     'apply_time'=>time(),
                     'user_id' => $user_info['user_id'],
                     'user_name'=>$user_info['user_name'],
                    );
        $app_mod = m('transaction_apply');
        $add_id = $app_mod->add($add);
        if ($add_id) {
            $this->show_message('申请成功');
        }else{
          echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('添加失败');
                return;  
        }
    }

    /**获取用户的买卖交易记录*/
    function _get_agent_transaction($user_id)
    {
        $transaction_mod =& m('transaction');
        $transaction = $transaction_mod->find(array('conditions' => "user_id='$user_id'and transaction_count>0 and  transaction_status>-1"));
        return $transaction;

    }

    /**检查数组是否为空*/
    function _check_is_empty($arr)
    {
        foreach ($arr as $key => $value) {
            if (empty($value)) {
                return false;
            }
            $arr[$key] = trim($value);

            $arr[$key] = stripslashes($value);

            $arr[$key] = htmlspecialchars($value);
        }
        return $arr;
    }

    /**
     * [get_tea_goods 响应ajax请求获取]
     * @return [array] [茶叶id，茶叶name]
     */
    function get_tea_goods()
    {
        if (empty($_POST['tea_name']) && $_POST['tea_name'] == '') {
            $out_data = array('code' => 1,
                'message' => '搜索内容为空',
                'data' => $goods_data);
            echo json_encode($out_data);
            return;
        }
        $tea_name = $_POST['tea_name'];
        $db = db();
        //设置缓存避免频繁请求
        if (empty($_SESSION['temp_arr'])) {
            $sql = "select * from ecm_gcategory where cate_name<>'茶珍' and cate_name<>'袋泡茶'";
            $categorys = $db->getall($sql);
            $cate_id = 1;
            $tea_id_arr = $this->_tree($categorys, $cate_id);

            $tea_id_arr = implode($tea_id_arr, ",");
            $sql = "select goods_id,goods_name,ecm_goods.cate_id from ecm_goods inner join ecm_gcategory on ecm_goods.cate_id=ecm_gcategory.cate_id where  ecm_goods.cate_id in ($tea_id_arr)";
            $tea_arr = $db->getall($sql);
            $_SESSION['temp_arr'] = $tea_arr;
        }
        foreach ($_SESSION['temp_arr'] as $key => $goods) {
            if (strchr($goods['goods_name'], $tea_name) !== false) {
                if (in_array($goods['cate_id'], array(84, 86, 87, 88, 89, 90, 91, 92, 93, 94, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111))) {
                    //中期茶中 期茶2010年之前        
                    $goods['type'] = 2;
                    $goods['type_des'] = '中期茶';
                } else if (in_array($goods['cate_id'], array(95, 96, 97, 98, 99, 112, 113, 114, 115, 116))) {
                    //新茶  新茶包含2010-2014
                    $goods['type'] = 1;
                    $goods['type_des'] = '新茶';
                } else {
                    # code...
                    $goods['type'] = 0;
                    $goods['type_des'] = '当年茶';
                }
                $goods_data[] = $goods;
            }

        }
        $out_data = array('code' => 0,
            'message' => '请求成功',
            'data' => $goods_data);
        echo json_encode($out_data);

    }

    /**
     * [get_drogue_by_id 响应ajax请求]
     * @return [Array] [price]
     */
    function get_drogue_by_id()
    {
        if (empty($_GET['goods_id'])) {
            $out_data = array('code' => 1,
                'message' => '参数异常',
                'data' => '');
        } else {
            $transaction_mod=& m('transaction');
            $goods_id = $_GET['goods_id'];
            $db = db();
            $sql = "select real_price from ecm_drogue where goods_id='$goods_id' order by show_date desc limit 1";
            //市场价格
            $temp['real_price'] = $db->getone($sql);
            //获取开盘价格：开盘价：第一笔成交价为开盘价，如果没有成交记录就取默认的记录
            $transaction = $this->_get_kaipan_price($goods_id);
            $temp['kai_pan_price'] = $transaction['transaction_price'];
            if ($transaction['have_transaction'] > 0) {
                //获取卖的需要交易的数量
                $count_sql = "select sum(transaction_count) as transaction_count from ecm_transaction where goods_id='$goods_id' and transaction_status=1 and have_transaction =0";
                $temp['transaction_count'] = $db->getone($count_sql);
                $temp['new_price'] = $transaction['new_price'];
                $get_max_price = "select max(transaction_price) as transaction_price from ecm_transaction where goods_id='$goods_id'and have_transaction >0";
                $temp['max_price'] = $db->getone($get_max_price);
                $get_min_price = "select min(transaction_price) as transaction_price from ecm_transaction where goods_id='$goods_id'and have_transaction >0";
                $temp['min_price'] = $db->getone($get_min_price);
                //涨幅： （现价-开盘价）/开盘价的百分比
                $temp['zhangfu_price'] = ($transaction['new_price'] - $transaction['transaction_price']) / $transaction['transaction_price'];
                $temp['zhangfu_price'] = round($temp['zhangfu_price'], 2);
                $temp['zhangdie_price'] = $transaction['new_price'] - $transaction['transaction_price'];
                //涨停价，跌停价以昨日收盘价上下浮动10%
                $zhangting_arr = $transaction_mod->_get_zhang_ting($goods_id, $transaction['transaction_time']);
                $temp['zhangting_price'] = $zhangting_arr['zhangting'];
                $temp['dieting_price'] = $zhangting_arr['dieting'];
                //昨日收盘价：
                $temp['shoupan_price'] = $zhangting_arr['shoupan'];
            } else {
                $temp['transaction_count'] = $transaction['transaction_count'];
                $temp['new_price'] = '--';
                $temp['max_price'] = '--';
                $temp['min_price'] = '--';
                $temp['zhangfu_price'] = '--';
                $temp['zhangdie_price'] = '--';
                $temp['zhangting_price'] = $transaction['transaction_price'] * 1.1;
                $temp['dieting_price'] = $transaction['transaction_price'] * 0.9;
                $temp['shoupan_price'] = $transaction['transaction_price'];
            }
            $limit_arr = $this->_situation_for_trasztion($goods_id);
            $temp['sell_pan'] = $limit_arr['sell'];
            $temp['buy_pan'] = $limit_arr['buy'];
            if ($temp['real_price'] === false) {
                $out_data == array('code' => 2,
                    'message' => '该产品已下架',
                    'data' => '');
            } else {
                $out_data = array('code' => 0,
                    'message' => '请求成功',
                    'data' => $temp);
            }
        }
        echo json_encode($out_data);
    }

    /**
     * [_situation_for_trasztion 获取交易情况]
     * @param  [type] $goods_id [description]
     * @return [type]           [description]
     */
    function _situation_for_trasztion($goods_id)
    {
        //1.没有交易过的，获取买的价格最高的前5名
        $db = db();
        $get_mai = "select transaction_price,sum(transaction_count) as transaction_count from ecm_transaction where goods_id='$goods_id' and transaction_status=0 and transaction_count<>0 GROUP BY transaction_price order by transaction_price desc limit 5";
        $out_data['buy'] = $db->getall($get_mai);
        $buy_count = count($out_data['buy']);
        $while = 5 - $buy_count;
        for ($i = 0; $i < $while; $i++) {
            $out_data['buy'][] = array('transaction_price' => '--', 'transaction_count' => '--');
        }

        //2.没有交易过的，获取卖的价格最低的前5名
        $get_sell_mai = "select transaction_price,sum(transaction_count) as transaction_count from ecm_transaction where goods_id='$goods_id' and transaction_status=1 and transaction_count<>0 GROUP BY transaction_price order by transaction_price asc limit 5";
        $out_data['sell'] = $db->getall($get_sell_mai);
        $sell_count = count($out_data['sell']);
        $while = 5 - $sell_count;
        for ($i = 0; $i < $while; $i++) {
            array_unshift($out_data['sell'], array('transaction_price' => '--', 'transaction_count' => '--'));
        }
        return $out_data;
    }

   

    /**
     * [_get_kaipan_price description]
     * @return [type] [description]
     */
    function _get_kaipan_price($goods_id)
    {
        //获取开盘价格：开盘价：第一笔成交价为开盘价，如果没有成交记录就取默认的记录
        if (empty($goods_id)) {
            return false;
        }
        $db = db();
        $get_kaipan = "select * from ecm_transaction where goods_id='$goods_id' and have_transaction >0 order by transaction_sn limit 1";
        $transaction = $db->getrow($get_kaipan);
        //是否有交易记录
        if (empty($transaction['transaction_time'])) {
            $get_from_init = "select price as transaction_price,stock as transaction_count from ecm_goods_spec where goods_id='$goods_id' limit 1";
            $transaction = $db->getrow($get_from_init);
            $transaction['have_transaction'] = 0;
        } else {
            //如果有交易记录就取出当前天的最开始的价格，即是开盘价格
            $cur_day = date('Y-m-d', $transaction['transaction_time']);
            $begin_time = strtotime($cur_day);
            $end_time = $begin_time + (3600 * 24);
            //最新价格
            $new_price = $transaction['transaction_price'];
            $get_kaipan_to = "select * from ecm_transaction where goods_id='$goods_id'and transaction_time > '$begin_time' and transaction_time < '$end_time'and have_transaction >0 order by transaction_sn limit 1";
            //重新定义交易信息获取当天的开盘价格
            $transaction = $db->getrow($get_kaipan_to);
            $transaction['new_price'] = $new_price;
        }
        return $transaction;
    }

    function get_drogue_info_echarts()
    {
        $_GET['id'] = 480;
        if (!empty($_GET['id'])) {
            $goods_id = $_GET['id'];
            $db =& db();
            //默认显示七天的时间
            $now_date = date("Y-m-d");
            $firstday = strtotime("$now_date -6 day");
            $lastday = strtotime("$now_date");
            $temp_start_time = date("Y-m-d", $firstday);
            $temp_end_time = date("Y-m-d", $lastday);
            $this->assign("temp_start_time", $temp_start_time);
            $this->assign("temp_end_time", $temp_end_time);

            $sql = "select * from ecm_drogue where goods_id=$goods_id AND show_date>='" . $firstday . "' AND show_date<='" . $lastday . "'order by show_date asc";
            $drogue = $db->getAll("$sql");
            if (empty($drogue)) {
                $drogue = "";
            } else {
                $drogue = json_encode($drogue);

            }
            $this->assign('drogue', $drogue);

        }
    }

    /**
     * [produce_share_tea 初始化炒茶的数据]
     * @return [type] [description]
     */
    function produce_share_tea()
    {dump('禁止访问！');$this->insert_system_tea();
         $transaction_init_mod = m('transaction_init');
        //1.获取需要超差的id
        $sql = 'select goods_id from ecm_drogue group by goods_id';
        $db = db();
        $data_col = $db->getcol($sql);
        //2.获取数据插入到需要用于炒茶的表中
        foreach ($data_col as $key => $goods) {
            $get_cur_id = "select goods_id from ecm_share_tea where goods_id='$goods'";
            $get_cur_row = $db->getrow($get_cur_id);
            if (empty($get_cur_row)) {
                $insert = "insert into ecm_share_tea (goods_id,store_id,type,goods_name,description,cate_id,cate_name,brand,spec_qty,spec_name_1,spec_name_2,if_show,closed,close_reason,add_time,last_update,default_spec,default_image,recommended,cate_id_1,cate_id_2,cate_id_3,cate_id_4,price,tags,member_price_1,member_price_2,member_price_3,member_price_4)select * from ecm_goods where goods_id='$goods'";
                $db->query($insert);
                $insert_spec = "insert into ecm_share_spec (spec_id,goods_id,spec_1,spec_2,color_rgb,price,stock,sku,member_price_1,member_price_2,member_price_3,member_price_4) select * from ecm_goods_spec where goods_id='$goods'";
                $db->query($insert_spec);

            }
        }
        //插入到茶叶交易表
        $get_share_tea = 'select * from ecm_share_tea inner join ecm_share_spec on ecm_share_tea.goods_id=ecm_share_spec.goods_id';
        $share_teas = $db->getall($get_share_tea);
        foreach ($share_teas as $key => $share_tea) {
            $time = time();
            $date_format = date('Y-m-d', $time);
            if ($share_tea['have_create_tra'] < 1) {
                if (in_array($share_tea['cate_id'], array(84, 86, 87, 88, 89, 90, 91, 92, 93, 94, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111))) {
                    //中期茶中 期茶2010年之前        
                    $share_tea['type'] = 2;
                    $share_tea['type_des'] = '中期茶';
                } else if (in_array($share_tea['cate_id'], array(95, 96, 97, 98, 99, 112, 113, 114, 115, 116))) {
                    //新茶  新茶包含2010-2014
                    $share_tea['type'] = 1;
                    $share_tea['type_des'] = '新茶';
                } else {
                    # code...
                    $share_tea['type'] = 0;
                    $share_tea['type_des'] = '当年茶';
                }
                $insert_tra = "insert into ecm_transaction(transaction_sn,goods_id,goods_name,transaction_price,transaction_count,transaction_time,transaction_from_sn,user_id,transaction_status,goods_type,sell_persentage,date_format,have_transaction) values(null,{$share_tea['goods_id']},'{$share_tea['goods_name']}',{$share_tea['price']},{$share_tea['stock']},$time,-1,1,1,{$share_tea['type']},0.9,'$date_format',0)";
                $db->query($insert_tra);
                //1.添加到最初交易的记录表
                $add_init = array('goods_id' =>$goods_id,
                                'produce_count' => $share_tea['stock'],
                                'produce_price'=> $share_tea['price'],
                                'apply_time' => time(),
                                'user_id'    => 1,//user_id 主用户id为1
                                'user_name'  => 'admin_fulucang',
                                'comments'   => 'admin用户初始化项目'
                                );

                
                 $transaction_init_mod ->add($add_init);
            }
        }
    }

    function insert_system_tea()
    {
        dump('禁止访问！');
        $db=db();
        $sql='select goods_id,stock,price from ecm_share_spec';
        $goods_arr=$db->getall($sql);
        foreach ($goods_arr as $key => $goods) {
            $goods['price'] = (int)(string)($goods['price']*100);
            $price = $goods['stock']*$goods['price'];
            $price = $price/100;
            $insert="insert into ecm_own_warehouse values (null,1,{$goods['goods_id']},{$goods['stock']},$price)";
            echo "$insert<br/>";
            $db->query($insert);
        }

    }

    /**
     * [get_goods_name 获取商品名称]
     * @return [type] [description]
     */
    function get_goods_name()
    {
        if (empty($_POST['goods_id'])) {
            $out_data = array('code' => 1,
                'message' => '参数异常,刷新页面重试',
                'data' => '');
        } else {
            $db = db();
            $sql = 'select goods_name from ecm_goods where goods_id=' . $_POST['goods_id'];
            $goods_name = $db->getone($sql);
            $out_data = array('code' => 0,
                'message' => '请求成功',
                'data' => $goods_name);
        }
        echo json_encode($out_data);
    }

    /**
     * [check_pwd 校验输入的验证码]
     * @return [boolean] [description]
     */
    function check_pwd($pwd)
    {
        $db = db();
        $user_id = $this->visitor->get('user_id');
        $check = "select pay_pwd from ecm_member where user_id='$user_id'";
        $pay_pwd = $db->getone($check);
        $pwd = md5($pwd);
        if ($pay_pwd === $pwd) {
            return true;
        }
        return false;
    }

    /**
     * [for_ajax_pwd 检查密码]
     * @return [type] [description]
     */
    function for_ajax_pwd()
    {
        if (empty($_POST['pay_pwd'])) {
            $out_data = array('code' => 1,
                'message' => '支付密码不能为空',
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }
        $user_id = $this->visitor->get('user_id');
        if (empty($user_id)) {
            $out_data = array('code' => 2,
                'message' => '当前用户信息没有获取到，请重新登陆',
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }
        if (empty($_SESSION["user_pwd_count_$user_id"])) {
            $_SESSION["user_pwd_count_$user_id"] = 0;
        }
        if ($_SESSION["user_pwd_count_$user_id"] >= 4) {
            $out_data = array('code' => 3,
                'message' => '您已经超出密码验证次数！',
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }
        $result = $this->check_pwd($_POST['pay_pwd']);
        if ($result) {
            //验证user_money
            $_POST['user_money']=$_POST['user_money']*100;
            $recharge_mod = m('recharge_log');
            $able_use_money = $recharge_mod ->get_use_money($user_id);//可以用
            if ($_POST['user_money'] > (int)(string)($able_use_money*100)) {
                $out_data = array('code' => 6,
                    'message' => "余额不足,当前余额为$able_use_money",
                    'data' => ''
                );

            } else {
                $out_data = array('code' => 0,
                    'message' => '请求成功',
                    'data' => $able_use_money
                );
            }
            echo json_encode($out_data);
            return;
        } else {
            $_SESSION["user_pwd_count_$user_id"]++;
            $cha = 3 - $_SESSION["user_pwd_count_$user_id"];
            $out_data = array('code' => 5,
                'message' => "密码错误,剩余($cha)次!",
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }

    }
    /**
     * [for_sell_pwd 检查出售的密码]
     * @return [type] [description]
     */
    function for_sell_pwd()
    {
        
        if (empty($_POST['pay_pwd'])) {
            $out_data = array('code' => 1,
                'message' => '支付密码不能为空',
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }
        $user_id = $this->visitor->get('user_id');
        if (empty($user_id)) {
            $out_data = array('code' => 2,
                'message' => '当前用户信息没有获取到，请重新登陆',
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }
        if (empty($_SESSION["user_pwd_count_$user_id"])) {
            $_SESSION["user_pwd_count_$user_id"] = 0;
        }
        if ($_SESSION["user_pwd_count_$user_id"] >= 3) {
            $out_data = array('code' => 3,
                'message' => '您已经超出密码验证次数！',
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }
        $result = $this->check_pwd($_POST['pay_pwd']);
        if ($result) {
            $db=db();
            $goods_id=$_POST['goods_id'];
            $goods_count=$_POST['goods_count'];
            //验证当前用户库存
            $sql="select goods_count from ecm_own_warehouse where goods_id='$goods_id' and user_id='$user_id'";
            $get_count=$db->getone($sql);
            if ($goods_count>$get_count) {
                $out_data = array('code' => 7,
                        'message' => '库存不足',
                        'data' => '',
                );
            }else{
                    $out_data = array('code' => 0,
                        'message' => '请求成功',
                        'data' => '',
                );
            }
            
            echo json_encode($out_data);
            return;
        } else {
            $_SESSION["user_pwd_count_$user_id"]++;
            $cha = 3 - $_SESSION["user_pwd_count_$user_id"];
            $out_data = array('code' => 5,
                'message' => "密码错误,剩余($cha)次!",
                'data' => ''
            );
            echo json_encode($out_data);
            return;
        }

    }

    /**获取当前用户的余额*/
    function get_user_count()
    {
        $user_id = $this->visitor->get('user_id');
         $member_mod = m('member');
        $use_money = $member_mod->get($user_id);
        return $use_money['use_money'];
    }

    /**
     * [flush_html 刷新当前的html]
     * @return [type] [description]
     */
    function flush_html($message, $status = 1)
    {
        if ($status === 1) {
            $img = 'dui.jpg';
        } else {
            $img = 'cuo.jpg';
        }
        if (empty($message)) {
            $message = '请求成功';
        }
        $this->assign('img', $img);
        $this->assign('message', $message);
        $this->display('flush.html');
    }

    /**取消代理*/
    function cancel_agent()
    {

        //校验token
        $recharge_mod =m("recharge_log");
        $result_token = $recharge_mod ->valid_token();
        if (!$result_token) {
            $out_data = array('code' => 3,
                'message' => '请勿重复提交，刷新页面重试',
                'data' => '');
            return;
        }
        $transaction_sn = $_POST['transaction_sn'];
        $goods_count = $_POST['goods_count'];
        /*  dump($transaction_sn);*/
        /*$transaction_sn=67;*/
       
        if (empty($transaction_sn)) {
            $out_data = array('code' => 1,
                'message' => '参数异常',
                'data' => '');
        } else {
            $transaction_sn = intval($transaction_sn);
            $transaction_mod =& m('transaction');
            $user_id=$this->visitor->get('user_id');
            if (empty($user_id)) {
                $out_data = array('code' => 2,
                    'message' => '当前用户信息没有获取到，请重新登陆',
                    'data' => $transaction_sn);
                 echo json_encode($out_data);
                 return;
            }
            $tran_result = $transaction_mod->cancel_agent_mod($transaction_sn, $goods_count,$user_id);
            if ($tran_result) {
                $out_data = array('code' => 0,
                    'message' => '请求成功',
                    'data' => '');
            } else {
                $out_data = array('code' => 2,
                    'message' => '该交易已经成交无法撤回',
                    'data' => $transaction_sn);
            }

        }
        echo json_encode($out_data);
    }

    /**刷新当前当前用户交易状态*/
    function transaction_produce()
    {   
        if (empty($user_id)) {
            $user_id=$this->visitor->get('user_id');

        }
        $transaction_mod =& m('transaction');
        $transaction = $this->_get_agent_transaction($user_id);
        /*dump($transaction);*/
        $transaction_mod->transaction_produce_mod($transaction);
    }
    /*检查当前用户的库存*/
    function check_current_stock(){
        $db=db();
        $own_warehouse_mod =& m('own_warehouse');
        $user_id=$this->visitor->get('user_id');
        $goods_id=$_POST['goods_id'] ;
        $sql = 'select goods_name from ecm_goods where goods_id=' . $_POST['goods_id'];
        $goods_name = $db->getone($sql);
        if (empty($goods_id)) {
            $out_data=array(
                'code'   =>'2',
                'message'=>'参数异常',
                'data'=>'');    
        }else{
             $arr=  $own_warehouse_mod ->find(array(
                'conditions'=>"goods_id='$goods_id' and user_id='$user_id'"
                ));
          
             if (!empty($arr)) {
                 foreach ($arr as $key => $value) {
                    $goods_counts=$value;
                 }
             }
             if(empty($arr)){
                $out_data=array(
                'code'   =>'3',
                'message'=>'库存不足',
                'data'=>''); 
                
             }elseif (empty($goods_counts['goods_count'])) {
                $out_data=array(
                'code'   =>'3',
                'message'=>'库存不足',
                'data'=>''); 
             }else{
                $goods_counts['goods_name']=$goods_name;
                $out_data=array(
                'code'   =>'0',
                'message'=>'请求成功',
                'data'=>$goods_counts); 

             }   
        }
        echo json_encode($out_data);
    }
    /*处理出售的茶叶信息*/
    function produce_sell(){
        if ($_POST) {
            $db=db();
            $transaction_mod =& m('transaction');
            if (empty($_POST['goods_id'])) {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
            $this->show_warning('参数异常');
            return;
            }
            $_POST['tea_type']=$this->_get_tea_des($_POST['goods_id']);
            if ($arr = $this->_check_is_empty($_POST)) {
                //数据正常
                extract($arr);
                if (strchr($tea_type, '新')) {
                    $tea_type = 1;
                } elseif (strchr($tea_type, '当')) {
                    $tea_type = 0;
                } elseif (strchr($tea_type, '中')) {
                    $tea_type = 2;
                }

                if ($fruit == 1) {
                    //买
                    $transaction_status = 0;
                    //校验支付密码
                    $result = $this->check_pwd($confirm_pwd);
                    if (!$result) {
                        echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                        $this->show_warning('支付密码错误，请重新提交表单');
                        return;
                    }

                } else {
                    $transaction_status = 1;
                    //校验支付密码
                    $result = $this->check_pwd($confirm_pwd);
                    if (!$result) {
                        echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                        $this->show_warning('支付密码错误，请重新提交表单');
                        return;
                    }

                }
                $user_id = $this->visitor->get('user_id');

                $arr = array('goods_id' => $goods_id,
                    'goods_type' => $tea_type,
                    'transaction_status' => $transaction_status,
                    'goods_name' => $goods_name,
                    'transaction_price' => $transaction_price,
                    'transaction_count' => $transaction_count,
                    'user_id' => $user_id,
                    'transaction_time' => time(),
                    'transaction_from_sn' => -1,
                    'date_format' => date('Y-m-d', time()),
                    'have_transaction' => 0,
                );
                
                //获取当前库存信息，减少库存
                $get_stock="select * from ecm_own_warehouse where goods_id='$goods_id' and user_id=$user_id";
                $goods_warehouse=$db->getrow($get_stock);
                if (empty($goods_warehouse['goods_count'])|| $goods_warehouse['goods_count']<$transaction_count) {
                    echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                        $this->show_warning('库存不足');
                        return;
                }
                //减少成本计算 ： 当前库存除以当前数量，求出平均价格 乘以要卖出的数量
               /* $transaction_price=intval($transaction_price*100);
                $abstract_price=$transaction_count*$transaction_price;*/
                $transaction_price_total = (int)(string)($goods_warehouse['transaction_price']*100);
                $transaction_prev_total  = $transaction_price_total/$goods_warehouse['goods_count'] ;
                $transaction_prev_total = ceil($transaction_prev_total);
                $transaction_price= $transaction_price_total- $transaction_prev_total*$transaction_count;
                if ($transaction_price<0) {
                   $transaction_price=0; 
                }else{
                   $transaction_price= $transaction_price/100; 
                } 
                //减少库存
                $db->query("SET AUTOCOMMIT=0");
                $abstract_stock="update ecm_own_warehouse set goods_count=goods_count-'$transaction_count',transaction_price='$transaction_price'where user_id='$user_id'and goods_id='$goods_id'";
                $ab_result= $db->query($abstract_stock);
                $transaction = $transaction_mod->add($arr);
                if ($transaction && $ab_result) {
                    $db->query('COMMIT');
                    $db->query("SET AUTOCOMMIT=1");
                    $this->transaction_produce();
                    echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                    $this->show_message('添加成功');
                    return;
                } else {
                     $db->query('ROLLBACK');
                    $db->query("SET AUTOCOMMIT=1");
                    echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                   $this->show_warning('添加失败');
                   return;
                }
            } else {
                echo '<style type="text/css"> body #header {width:1230px}body #header .search{  bottom: 57px; margin: -73px 0; position: absolute; right: 0; width: 1230px; } div.content{width:1230px;margin:50px auto;}</style>';
                $this->show_warning('数据异常,请重新提交');
            }
        }else{
         $this->show_warning('请求异常');   
        }
        
    }

    function _get_tea_des($goods_id)
    {
        $db=&db();
        $get_tea_des="select cate_id from ecm_goods where goods_id=$goods_id ";
        $goods_cate_id = $db->getone($get_tea_des);
        if (in_array($goods_cate_id, array(84, 86, 87, 88, 89, 90, 91, 92, 93, 94, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111))) {
            //中期茶中 期茶2010年之前        
            $goods['type'] = 2;
            $goods['type_des'] = '中期茶';
        } else if (in_array($goods_cate_id, array(95, 96, 97, 98, 99, 112, 113, 114, 115, 116))) {
            //新茶  新茶包含2010-2014
            $goods['type'] = 1;
            $goods['type_des'] = '新茶';
        } else {
            # code...
            $goods['type'] = 0;
            $goods['type_des'] = '当年茶';
        }
                
        return   $goods['type_des'];
    }
    
    /**
     * [_get_data_priv_store_limit 根据当前时间显示不同的店铺]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    function _get_data_priv_store_limit($limit,$where=''){
        if (empty($where)) {
            $where ='1=1';
        }

        $db =& db();
        $inner_join = "SELECT
            store.store_id,
            store.store_name,
            store.store_logo
        FROM
            ecm_store AS store
        INNER JOIN ecm_category_store AS cs ON store.store_id = cs.store_id
        INNER JOIN ecm_scategory AS es ON cs.cate_id = es.cate_id
        WHERE
            es.cate_name NOT LIKE 'VIP%'
        AND es.parent_id = 0 AND ". $where ." order by store.sort_order ASC limit $limit";
        $store_arr = $db->getall($inner_join);
        return $store_arr;
    }
   function _get_data_priv_store_count($where='1=1'){
    if (empty($where)) {
        $where='1=1';
    }
            $db =& db();
        $inner_join = "SELECT
           count(store.store_id) 
        FROM
            ecm_store AS store
        INNER JOIN ecm_category_store AS cs ON store.store_id = cs.store_id
        INNER JOIN ecm_scategory AS es ON cs.cate_id = es.cate_id
        WHERE
            es.cate_name NOT LIKE 'VIP%'
        AND es.parent_id = 0 AND ". $where;
        $store_count = $db->getone($inner_join);
        return $store_count;
   }
     /* 取得商品分类 */
    function _list_gcategory()
    {
        $cache_server =& cache_server();
        $key = 'page_goods_category';
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
            $gcategories = $gcategory_mod->get_list(-1,true);
    
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
            $data = $tree->getArrayList(0);

            $cache_server->set($key, $data, 3600);
        }

        return $data;
    }

    function send_code(){
   
        if (empty($_POST['email_str'])) {
            $array = array('code' => "1",
                            'message' => "请检查邮箱账号",
                            'data'  => $mailer->errors);
             echo json_encode($array);
             return;

        }elseif (!preg_match('/^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/',$_POST['email_str'])) {
            $array = array('code' => "2",
                            'message' => "邮箱账号格式错误",
                            'data'  => $_POST['email_str']);
            echo json_encode($array);
            return;

        }
        $rand = mt_rand(100000,999999);
        $_SESSION['email_code']= array('code'=>$rand,'time'=>time());
        /* 使用mailer类 */
        import('mailer.lib');
        $email_from = Conf::get('site_name');
        $email_type = Conf::get('email_type');
        $email_host = Conf::get('email_host');
        $email_port = Conf::get('email_port');
        $email_addr = Conf::get('email_addr');
        $email_id   = Conf::get('email_id');
        $email_pass = Conf::get('email_pass');
        $email_to   = $_POST['email_str'];
        /*获取支付的验证码*/
        /*if ($_POST['type']=='get_pay_code') {
            $email_subject = '福禄仓验证码'; 
        }else{
            $email_subject = '福禄仓验证码';  
        }*/
        $email_subject = '福禄仓验证码';  
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
        if(empty($_POST['input_code'])){
           $array = array('code' => "1",
                            'message' => "参数异常",
                            'data'  => ''); 
        }elseif ($_SESSION['email_code']['code']==$_POST['input_code']) {
            if (time()-$_SESSION['email_code']['time']>600) {
               $array = array('code' => "3",
                            'message' => "超出时间",
                            'data'  => ''); 
            }else{
              $array = array('code' => "0",
                            'message' => "请求成功",
                            'data'  => '');   
            }
            
            
        }else{
            $array = array('code' => "2",
                            'message' => "状态码输入错误",
                            'data'  => ''); 
        }
        echo json_encode($array);
    }

    function return_captch(){
        
    }

}