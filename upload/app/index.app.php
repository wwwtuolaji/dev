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
        //获取企业信息
        $store_arr = $this->_get_data_priv_store();
        $notice = $this->_get_article_notice();
        $drogue_arr = $this->_get_data();
        $goods_new = $this->_get_data_new();

        $out_data['notice'] = $notice;
        $out_data['member_info'] = $member_info;
        $out_data['goods_new'] = $goods_new;
        $out_data['drogue_arr'] = $drogue_arr;
        $out_data['store_arr'] = $store_arr;
        $this->assign("out_data", $out_data);
        $this->display("index.html");
    }

    /**
     * [loan 贷款页面]
     * @return [type] [description]
     */
    function loan()
    {
        if (empty($_POST)) {
            $this->display("loan.html");
        } else {

        }
    }

    /**
     * [leisure 茶休闲会所]
     * @return [type] [description]
     */
    function leisure()
    {

        $this->display("leisure_clubs.html");
    }

    function enterprise()
    {
        $store_arr = $this->_get_data_priv_store(8);
        $out_data['store_arr'] = $store_arr;
        $this->assign("out_data", $out_data);
        $this->display("enterprise.html");
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
			store.store_name
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
        $result=$this->rbac_check();
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
            $this->show_message('编辑成功',
                'edit_again', 'index.php?app=index&act=set_member',
                 'back_list', 'index.php?app=index'
            );

        } else {
            $this->display("set_member.html");
        }


    }
    /**
     * [rbac_check 权限检查]
     * @return [bool] []
     */
    function rbac_check(){
        $visitor=$this->visitor->get();
        if ($visitor['member_level']>=100) {
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
        $user_name=$this->visitor->get('user_name');
        $out_data['user_name']=$user_name;
        $drogue_arr = $this->_get_data();
        $out_data['drogue_arr'] = $drogue_arr;
        $this->assign("out_data", $out_data);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea  ','茶商首页');
        /* $this->_curitem('my_store');
            $this->_curmenu('my_store');*/
        $this->display("tea.html");

    }
    function market()
    {    
        $user_name=$this->visitor->get('user_name');
        $out_data['user_name']=$user_name;
        $this->assign("out_data", $out_data);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea','行情参考');
        $cate_id=$_GET['cate_id'];
        if(empty($cate_id)){
            $cate_id=1;
        }
        if (empty($_GET['page'])) {
          $limit='25';
        }else{
          $limit='25';  
        }
        $gcategory_mod  =& bm('gcategory');
        $layer   = $gcategory_mod->get_layer($cate_id, true); 
        /* 商品展示方式 */
        $display_mode = ecm_getcookie('goodsDisplayMode');
        if (empty($display_mode) || !in_array($display_mode, array('list', 'squares')))
        {
            $display_mode = 'squares'; // 默认格子方式
        }
        $this->assign('display_mode', $display_mode); 
        $goods=array();
        //获取要查询的分页的参数数目
        $goods_count=$this->_get_goods_list_count($cate_id,$_GET['brand'],$layer);
        //配置分页信息
        $page = $this->_get_page(15);
        $page['item_count'] = $goods_count;
        $this->_format_page($page);
        $this->assign('page_info', $page);
        /*获取相关的产品*/
        $goods_list=$this->_get_goods_list($cate_id,$_GET['brand'],$layer,$page['limit']);
        $this->assign('goods_list',$goods_list);
        //1.先获取茶叶的顶级分类
        $db = db();  
        $choose=array();
        if($layer==1){
        //茶叶分类
        $category=$db->getall("select cate_id, cate_name from ecm_gcategory where parent_id=1 and cate_name ='普洱熟茶' or cate_name ='普洱生茶'");
        // 取出茶叶品牌
         $brands=$this->_get_brands($cate_id);
        $this->assign('categories', $category);

        }else{
              $cate_name=$db->getone("select cate_name from ecm_gcategory where cate_id=$cate_id");
            
            //茶页就3个等级，直接取出下个等级的东西
            if ($layer==2) {
                $category=$db->getall("select cate_id, cate_name from ecm_gcategory where parent_id=$cate_id");
                $this->assign('categories', $category);
                $brands=$this->_get_brands($cate_id);
                $choose['category'][]=array("cate_id"=>$cate_id,"cate_name"=>$cate_name);
                //设置该分类被选中
            }else{
                //第三级需要获取第二级选中的名字和id
                $cate_parent="select cate_name,cate_id from ecm_gcategory where cate_id=(select parent_id from ecm_gcategory where cate_id=$cate_id)";
                $parent_cate=$db->getrow($cate_parent);
                $choose['category'][]=$parent_cate;
                $choose['category'][]=array("cate_id"=>$cate_id,"cate_name"=>$cate_name);
                /*随便取一个分类的内容*/
                $category=$db->getall("select cate_id, cate_name from ecm_gcategory where parent_id=42");
                $this->assign('categories', $category);
                $this->assign('category_limit',true);
            }
            
        }
       
        
        //dump($choose);
         $this->assign("layer",$layer);
        $this->assign("choose",$choose);
        // 查询参数
        $param = $this->_get_query_param();
        if (empty($param))
        {
            header('Location: index.php?app=tea');
            exit;
        }
        if (isset($param['cate_id']) && $param['layer'] === false)
        {
            $this->show_warning('no_such_category');
            return;
        }
        $this->assign("brands",$brands);
         
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
     function _get_goods_list_count($cate_id,$brand,$layer){
        //dump($layer);
            if ($layer==1) {
                 $db=db();
                $sql="select * from ecm_gcategory";
                $categorys= $db->getall($sql);
                $tea_id_arr=$this->_tree($categorys,$cate_id);
                $tea_id_str=implode($tea_id_arr, ",");
                /*dump($tea_id_str);*/
                if (empty($tea_id_str)) {
                    return array();
                }
                if (empty($_GET['brand'])) {
                    $goods="select count(*) from ecm_goods where  brand <> '' and cate_id in ($tea_id_str)";
                }else{
                    $goods="select count(*) from ecm_goods where cate_id in ($tea_id_str) and brand='".$_GET['brand']."'";
                }
                
                $result=$db->getone($goods);
               
            }elseif ($layer==2) {
                $db=db();
                $sql="select * from ecm_gcategory";
                $categorys= $db->getall($sql);
                $tea_id_arr=$this->_tree($categorys,$cate_id);
                $tea_id_str=implode($tea_id_arr, ",");
                /*dump($tea_id_str);*/
                if (empty($tea_id_str)) {
                    return array();
                }
                if (empty($_GET['brand'])) {
                    $goods="select count(*) from ecm_goods where  brand <> '' and cate_id in ($tea_id_str)";
                }else{
                    $goods="select count(*) from ecm_goods where cate_id in ($tea_id_str) and brand='".$_GET['brand']."'";
                }
                $result=$db->getone($goods);
               
            }else{
                $db=db();
                $sql="select * from ecm_gcategory";
                $categorys= $db->getall($sql);
                if (empty($_GET['brand'])) {
                    $goods="select count(*) from ecm_goods where  brand <> '' and cate_id ='".$_GET['cate_id']."'";
                }else{
                    $goods="select count(*) from ecm_goods where cate_id =".$_GET['cate_id']. " and brand='".$_GET['brand']."'";
                }
                
                $result=$db->getone($goods);
                
            }
            return $result;     

     }

    /**
     * [_get_goods_list 获取商品列表]
     * @param  [type] $cate_id [上级和当前的id]
     * @param  [type] $brand   [品牌，为空为所有]
     * @return [type]          [description]
     */
     function _get_goods_list($cate_id,$brand,$layer,$limit){
            //dump($layer);
            if ($layer==1) {
                 $db=db();
                $sql="select * from ecm_gcategory";
                $categorys= $db->getall($sql);
                $tea_id_arr=$this->_tree($categorys,$cate_id);
                $tea_id_str=implode($tea_id_arr, ",");
                /*dump($tea_id_str);*/
                if (empty($tea_id_str)) {
                    return array();
                }
                if (empty($_GET['brand'])) {
                    $goods="select * from ecm_goods where  brand <> '' and cate_id in ($tea_id_str) limit ".$limit;
                }else{
                    $goods="select * from ecm_goods where cate_id in ($tea_id_str) and brand='".$_GET['brand']."'limit ".$limit;
                }
                
                $result=$db->getall($goods);
               
            }elseif ($layer==2) {
                $db=db();
                $sql="select * from ecm_gcategory";
                $categorys= $db->getall($sql);
                $tea_id_arr=$this->_tree($categorys,$cate_id);
                $tea_id_str=implode($tea_id_arr, ",");
                /*dump($tea_id_str);*/
                if (empty($tea_id_str)) {
                    return array();
                }
                if (empty($_GET['brand'])) {
                    $goods="select * from ecm_goods where  brand <> '' and cate_id in ($tea_id_str)limit ".$limit;
                }else{
                    $goods="select * from ecm_goods where cate_id in ($tea_id_str) and brand='".$_GET['brand']."'limit ".$limit;
                }
                $result=$db->getall($goods);
               
            }else{
                $db=db();
                $sql="select * from ecm_gcategory";
                $categorys= $db->getall($sql);
                if (empty($_GET['brand'])) {
                    $goods="select * from ecm_goods where  brand <> '' and cate_id ='".$_GET['cate_id']."'limit ".$limit;
                }else{
                    $goods="select * from ecm_goods where cate_id =".$_GET['cate_id']. " and brand='".$_GET['brand']."'limit ".$limit;
                }
                
                $result=$db->getall($goods);
                
            }
            return $result;     

     }
     /**
     * 根据查询条件取得分组统计信息
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @param   bool    $cached 是否缓存
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

        if ($cached)
        {
            $cache_server =& cache_server();
            $key = 'group_by_info_' . var_export($param, true);
            $data = $cache_server->get($key);
        }

        if ($data === false)
        {
            $data = array(
                'total_count' => 0,
                'by_category' => array(),
                'by_brand'    => array(),
                'by_region'   => array(),
                'by_price'    => array()
            );

            $goods_mod =& m('goods');
            $store_mod =& m('store');
            $table = " {$goods_mod->table} g LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id";
            $conditions = $this->_get_goods_conditions($param);
            $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions;
            $total_count = $goods_mod->getOne($sql);
            if ($total_count > 0)
            {
                $data['total_count'] = $total_count;
                /* 按分类统计 */
                $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
                $sql = "";
                if ($cate_id > 0)
                {
                    $layer = $param['layer'];
                    if ($layer < 4)
                    {
                        $sql = "SELECT g.cate_id_" . ($layer + 1) . " AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_" . ($layer + 1) . " > 0 GROUP BY g.cate_id_" . ($layer + 1) . " ORDER BY count DESC";
                    }
                }
                else
                {
                    $sql = "SELECT g.cate_id_1 AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_1 > 0 GROUP BY g.cate_id_1 ORDER BY count DESC";
                }

                if ($sql)
                {
                    $category_mod =& bm('gcategory');
                    $children = $category_mod->get_children($cate_id, true);
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res))
                    {
                        $data['by_category'][$row['id']] = array(
                            'cate_id'   => $row['id'],
                            'cate_name' => $children[$row['id']]['cate_name'],
                            'count'     => $row['count']
                        );
                    }
                }

                /* 按品牌统计 */
                $sql = "SELECT g.brand, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.brand > '' GROUP BY g.brand ORDER BY count DESC";
                $by_brands = $goods_mod->db->getAllWithIndex($sql, 'brand');
                
                /* 滤去未通过商城审核的品牌 */
                if ($by_brands)
                {
                    $m_brand = &m('brand');
                    $brand_conditions = db_create_in(addslashes_deep(array_keys($by_brands)), 'brand_name');
                    $brands_verified = $m_brand->getCol("SELECT brand_name FROM {$m_brand->table} WHERE " . $brand_conditions . ' AND if_show=1');
                    foreach ($by_brands as $k => $v)
                    {
                        if (!in_array($k, $brands_verified))
                        {
                            unset($by_brands[$k]);
                        }
                    }
                }
                $data['by_brand'] = $by_brands;
                
                
                /* 按地区统计 */
                $sql = "SELECT s.region_id, s.region_name, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND s.region_id > 0 GROUP BY s.region_id ORDER BY count DESC";
                $data['by_region'] = $goods_mod->getAll($sql);

                /* 按价格统计 */
                if ($total_count > NUM_PER_PAGE)
                {
                    $sql = "SELECT MIN(g.price) AS min, MAX(g.price) AS max FROM {$table} WHERE" . $conditions;
                    $row = $goods_mod->getRow($sql);
                    $min = $row['min'];
                    $max = min($row['max'], MAX_STAT_PRICE);
                    $step = max(ceil(($max - $min) / PRICE_INTERVAL_NUM), MIN_STAT_STEP);
                    $sql = "SELECT FLOOR((g.price - '$min') / '$step') AS i, count(*) AS count FROM {$table} WHERE " . $conditions . " GROUP BY i ORDER BY i";
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res))
                    {
                        $data['by_price'][] = array(
                            'count' => $row['count'],
                            'min'   => $min + $row['i'] * $step,
                            'max'   => $min + ($row['i'] + 1) * $step,
                        );
                    }
                }
            }

            if ($cached)
            {
                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
            }
        }

        return $data;
    }

     /**
     * 取得查询条件语句
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_goods_conditions($param)
    {
        /* 组成查询条件 */
        $conditions = " g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态,
        if (isset($param['keyword']))
        {
            $conditions .= $this->_get_conditions_by_keyword($param['keyword'], ENABLE_SEARCH_CACHE);
        }
        if (isset($param['cate_id']))
        {
            $conditions .= " AND g.cate_id_{$param['layer']} = '" . $param['cate_id'] . "'";
        }
        if (isset($param['brand']))
        {
            $conditions .= " AND g.brand = '" . $param['brand'] . "'";
        }
        if (isset($param['region_id']))
        {
            $conditions .= " AND s.region_id = '" . $param['region_id'] . "'";
        }
        if (isset($param['price']))
        {
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
     * @param   array       $keyword    关键词
     * @param   bool        $cached     是否缓存
     * @return  string      " AND (0)"
     *                      " AND (goods_name LIKE '%a%' AND goods_name LIKE '%b%')"
     *                      " AND (goods_id IN (1,2,3))"
     */
    function _get_conditions_by_keyword($keyword, $cached)
    {
        $conditions = false;

        if ($cached)
        {
            $cache_server =& cache_server();
            $key1 = 'query_conditions_of_keyword_' . join("\t", $keyword);
            $conditions = $cache_server->get($key1);
        }

        if ($conditions === false)
        {
            /* 组成查询条件 */
            $conditions = array();
            foreach ($keyword as $word)
            {
                $conditions[] = "g.goods_name LIKE '%{$word}%'";
            }
            $conditions = join(' AND ', $conditions);

            /* 取得满足条件的商品数 */
            $goods_mod =& m('goods');
            $sql = "SELECT COUNT(*) FROM {$goods_mod->table} g WHERE " . $conditions;
            $current_count = $goods_mod->getOne($sql);
            if ($current_count > 0)
            {
                if ($current_count < MAX_ID_NUM_OF_IN)
                {
                    /* 取得商品表记录总数 */
                    $cache_server =& cache_server();
                    $key2 = 'record_count_of_goods';
                    $total_count = $cache_server->get($key2);
                    if ($total_count === false)
                    {
                        $sql = "SELECT COUNT(*) FROM {$goods_mod->table}";
                        $total_count = $goods_mod->getOne($sql);
                        $cache_server->set($key2, $total_count, SEARCH_CACHE_TTL);
                    }

                    /* 不满足条件，返回like */
                    if (($current_count / $total_count) < MAX_HIT_RATE)
                    {
                        /* 取得满足条件的商品id */
                        $sql = "SELECT goods_id FROM {$goods_mod->table} g WHERE " . $conditions;
                        $ids = $goods_mod->getCol($sql);
                        $conditions = 'g.goods_id' . db_create_in($ids);
                    }
                }
            }
            else
            {
                /* 没有满足条件的记录，返回0 */
                $conditions = "0";
            }

            if ($cached)
            {
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
        if ($res === null)
        {
            $res = array();
    
            // keyword
            $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
            if ($keyword != '')
            {
                //$keyword = preg_split("/[\s," . Lang::get('comma') . Lang::get('whitespace') . "]+/", $keyword);
                $tmp = str_replace(array(Lang::get('comma'),Lang::get('whitespace'),' '),',', $keyword);
                $keyword = explode(',',$tmp);
                sort($keyword);
                $res['keyword'] = $keyword;
            }
            //@jjc  cate_id为空就为1，只能显示茶叶因为茶叶就是1
            if (empty($_GET['cate_id'])) {
                $_GET['cate_id']=1;
            }
            // cate_id
            if (isset($_GET['cate_id']) && intval($_GET['cate_id']) > 0)
            {
                $res['cate_id'] = $cate_id = intval($_GET['cate_id']);
                $gcategory_mod  =& bm('gcategory');
                $res['layer']   = $gcategory_mod->get_layer($cate_id, true);
            }
    
            // brand
            if (isset($_GET['brand']))
            {
                $brand = trim($_GET['brand']);
                $res['brand'] = $brand;
            }
    
            // region_id
            if (isset($_GET['region_id']) && intval($_GET['region_id']) > 0)
            {
                $res['region_id'] = intval($_GET['region_id']);
            }
    
            // price
            if (isset($_GET['price']))
            {
                $arr = explode('-', $_GET['price']);
                $min = abs(floatval($arr[0]));
                $max = abs(floatval($arr[1]));
                if ($min * $max > 0 && $min > $max)
                {
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
        if ($filters === null)
        {
            $filters = array();
            if (isset($param['keyword']))
            {
                $keyword = join(' ', $param['keyword']);
                $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
            }
            isset($param['brand']) && $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $param['brand']);
            if (isset($param['region_id']))
            {
                // todo 从地区缓存中取
                $region_mod =& m('region');
                $row = $region_mod->get(array(
                    'conditions' => $param['region_id'],
                    'fields' => 'region_name'
                ));
                $filters['region_id'] = array('key' => 'region_id', 'name' => LANG::get('region'), 'value' => $row['region_name']);
            }
            if (isset($param['price']))
            {
                $min = $param['price']['min'];
                $max = $param['price']['max'];
                if ($min <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('le') . ' ' . price_format($max));
                }
                elseif ($max <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                }
                else
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
            }
        }
            

        return $filters;
    }

    /*获取品牌信息*/
    function _get_brands($cate_id){
        //1.首先获取1级分类下的所有品牌
        $db=&db();
        $sql="select * from ecm_gcategory";
        $categorys= $db->getall($sql);
        $tea_id_arr=$this->_tree($categorys,$cate_id);
        $tea_id_str=implode($tea_id_arr, ",");
       
        $brand="select cate_id,brand from ecm_goods where  brand <> '' and cate_id in ($tea_id_str) group by brand ";
        $brands=$db->getall($brand);
        $brands=array_filter($brands);
        /*$new_brand=array_unique($brands);*/
        return $brands;
      
    }
     //取出顶级为茶的所有商品类
    function _tree($goods_categorys,$parent_id){
        static $tea_id=array();
        foreach ($goods_categorys as $goods_category) {
            if ($goods_category['parent_id']==$parent_id) {
                $tea_id[]=$goods_category['cate_id'];
                $this->_tree($goods_categorys,$goods_category['cate_id']);
            }
        }
        return $tea_id;
    }

    function agent(){
        $user_name=$this->visitor->get('user_name');
        $out_data['user_name']=$user_name;
        $this->assign("out_data", $out_data);
        $db=db();
        $sql="select * from ecm_agent";
        $arr=$db->getall($sql);
        $this->assign('agent_info',$arr);
        $this->_curlocal('福禄仓茶叶', 'index.php?app=index&act=tea','经纪人');
        $this->display("agent.html");
    }
    function news(){
        $drogue_arr = $this->_get_data();
        $out_data['drogue_arr'] = $drogue_arr;
        $this->assign("out_data", $out_data);
        $this->display('news.html');
    }

}