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
     * [check_outer_number 检查外部卡号]
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

}