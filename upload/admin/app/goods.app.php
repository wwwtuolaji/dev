<?php
/**
 *    商品管理控制器
 */
class GoodsApp extends BackendApp
{
    var $_goods_mod;

    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::BackendApp();

        $this->_goods_mod =& m('goods');
    }

    /* 商品列表 */
    function index()
    {
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'brand',
                'equal' => 'like',
            ),
            array(
                'field' => 'closed',
                'type'  => 'int',
            ),
        ));

        // 分类
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        if ($cate_id > 0)
        {
            $cate_mod =& bm('gcategory');
            $cate_ids = $cate_mod->get_descendant_ids($cate_id);
            $conditions .= " AND cate_id" . db_create_in($cate_ids);
        }

        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'goods_id';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'goods_id';
            $order = 'desc';
        }

        $page = $this->_get_page();
        $goods_list = $this->_goods_mod->get_list(array(
            'conditions' => "1 = 1" . $conditions,
            'count' => true,
            'order' => "$sort $order",
            'limit' => $page['limit'],
        ));
        foreach ($goods_list as $key => $goods)
        {
            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
        }
        $this->assign('goods_list', $goods_list);

        $page['item_count'] = $this->_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        // 第一级分类
        $cate_mod =& bm('gcategory', array('_store_id' => 0));
        $this->assign('gcategories', $cate_mod->get_options(0, true));
        $this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
        $this->assign('enable_radar', Conf::get('enable_radar'));
        $this->display('goods.index.html');
    }

    /* 推荐商品到 */
    function recommend()
    {
        if (!IS_POST)
        {
            /* 取得推荐类型 */
            $recommend_mod =& bm('recommend', array('_store_id' => 0));
            $recommends = $recommend_mod->get_options();
            if (!$recommends)
            {
                $this->show_warning('no_recommends', 'go_back', 'javascript:history.go(-1);', 'set_recommend', 'index.php?app=recommend');
                return;
            }
            $this->assign('recommends', $recommends);
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $recom_id = empty($_POST['recom_id']) ? 0 : intval($_POST['recom_id']);
            if (!$recom_id)
            {
                $this->show_warning('recommend_required');
                return;
            }

            $ids = explode(',', $id);
            $recom_mod =& bm('recommend', array('_store_id' => 0));
            $recom_mod->createRelation('recommend_goods', $recom_id, $ids);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('recommend_ok',
                'back_list', 'index.php?app=goods&page=' . $ret_page,
                'view_recommended_goods', 'index.php?app=recommend&amp;act=view_goods&amp;id=' . $recom_id);
        }
    }

    /* 编辑商品 */
    function edit()
    {
        if (!IS_POST)
        {
            // 第一级分类
            $cate_mod =& bm('gcategory', array('_store_id' => 0));
            $this->assign('gcategories', $cate_mod->get_options(0, true));

            $this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $ids = explode(',', $id);
            $data = array();
            if ($_POST['cate_id'] > 0)
            {
                $data['cate_id'] = $_POST['cate_id'];
                $data['cate_name'] = $_POST['cate_name'];
            }
            if (trim($_POST['brand']))
            {
                $data['brand'] = trim($_POST['brand']);
            }
            if ($_POST['closed'] >= 0)
            {
                $data['closed'] = $_POST['closed'] ? 1 : 0;
                $data['close_reason'] = $_POST['closed'] ? $_POST['close_reason'] : '';
            }

            if (empty($data))
            {
                $this->show_warning('no_change_set');
                return;
            }

            $this->_goods_mod->edit($ids, $data);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('edit_ok',
                'back_list', 'index.php?app=goods&page=' . $ret_page);
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('goods_name', 'brand', 'closed')))
       {
           $data[$column] = $value;
           $this->_goods_mod->edit($id, $data);
           if(!$this->_goods_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    /* 删除商品 */
    function drop()
    {
        if (!IS_POST)
        {
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }
            $ids = explode(',', $id);

            // notify store owner
            $ms =& ms();
            $goods_list = $this->_goods_mod->find(array(
                "conditions" => $ids,
                "fields" => "goods_name, store_id",
            ));
            foreach ($goods_list as $goods)
            {
                //$content = sprintf(LANG::get('toseller_goods_droped_notify'), );
                $content = get_msg('toseller_goods_droped_notify', array('reason' => trim($_POST['drop_reason']),
                    'goods_name' => addslashes($goods['goods_name'])));
                $ms->pm->send(MSG_SYSTEM, $goods['store_id'], '', $content);
            }

            // drop
            $this->_goods_mod->drop_data($ids);
            $this->_goods_mod->drop($ids);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('drop_ok',
                'back_list', 'index.php?app=goods&page=' . $ret_page);
        }
    }


        /*参考价格编辑*/
    function edit_price()
    {  
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('id 不存在请重新编辑');
            return;
        }
        $drogue_model=&m('drogue');
        $db=&db();
        if (!IS_POST)
        {    
            $page = $this->_get_page(20);
            //1.获取当前表单的内容
            $page_limit=$page['limit'];
            $goods_arr= $drogue_model->get_drogue_arr($id,$page_limit);
            $page['item_count'] = $drogue_model->get_drogue_count_by_store_id($id);
            $this->_format_page($page);
             $this->assign('page_info', $page);
            $first=true;
 
            if ($goods_arr) {
                foreach ($goods_arr as $key => $value) {
                    if ($first===true) {
                        $date=$value['show_date']-(3600*24);
                        $pre_sql="select real_price from ecm_drogue where goods_id='$id' and show_date ='" .$date ."'order by show_date asc";
                        $value['ref_price']=$db->getone($pre_sql);
                        $goods_arr[$key]['ref_price']=$value['ref_price'];
                    }else{
                        $value['ref_price']=0;
                    }
                        $first=true;  
                         $goods_arr[$key]['show_date']=date('Y/m/d',$value['show_date']);
                    if (empty($value['real_price'])||$value['real_price']=="0.00") {
                       $goods_arr[$key]['real_price']="0.00";
                       $goods_arr[$key]['show_date']="";
                    }elseif (empty($value['ref_price'])||$value['ref_price']=="0.00") {
                        $goods_arr[$key]['ref_price']="0.00";
                        $goods_arr[$key]['percent']="";
                    }else{
                      
                       $number=($value['real_price']-$value['ref_price'])/$value['ref_price']*100;
                       $goods_arr[$key]['percent']=number_format($number, 2, '.', '');  
                    }
                     
                }
            }
            $this->assign('goods_arr',$goods_arr);
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                        'script' => 'jquery.plugins/jquery.validate.js,mlselection.js,My97DatePicker/WdatePicker.js,jquery-form.js'
                    ));
            /*$this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');*/
            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
            $this->display('goods.edit_price.html');
        }else{ 

            foreach ($_POST['show_date'] as $key => $value) {
                $_POST['show_date'][$key]=strtotime($value);
            } 
            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                   foreach ($value as $kk => $vv) {
                        $arr[$kk][$key]=trim($vv);
                        $arr[$kk]['goods_id']=$id;
                    }
                }
            }
            /*var_dump($arr);
            die;*/
            //查询当前时间的id是否存在，否则就视为添加
            foreach($arr as $key =>$arrlist){
                if (empty($arrlist['show_date'])) {
                    continue;
                }
                $query_con="select id from ecm_drogue where goods_id='{$arrlist['goods_id']}' and show_date=". $arrlist['show_date'];
                $db=&db();
                $res=$db->getone($query_con);
                if ($res) {
                    # 存在
                    $drogue_model->edit($res,$arrlist);
                }else{
                    #不存在
                   $drogue_model->add($arrlist); 
                }

            }
            $this->show_message('插入成功',
                'back_list', 'index.php?app=goods&act=edit_price&id=' . $id);
                return;
           
           /* dump($drogue);*/
          /*  if ($res) {
                echo "chenggong";
            }else{
                echo "shibai";
            }
            var_dump($res);
            die;*/
             /*$con_str="";
            foreach ($arr as $key => $value) {
                $column_arrs=array_keys($value);
                $column_arr=implode(",",$column_arrs);
                $con_str .="('".implode("','",$value) ."'),";
            }
            $key_str=substr($column_arr, 0);
            $key_str .=") values";
            $sql="insert into ecm_drogue(" . $key_str . $con_str;
            $sql=substr($sql, 0, -1);
            $db = &db(); 
            //先删除当前id的内容
            $del="delete from ecm_drogue where goods_id='$id'";
            $result=$db->query($del);
            $result=$db->query($sql);
            if ($result) {
                $this->show_message('插入成功',
                'back_list', 'index.php?app=goods&act=edit_price&id=' . $id);
                return;
            }else{
                $this->show_warning('数据格式错误请检查!!');
                return;
            }*/

        }

    }

    /**
     * [delete_drogue 响应ajax请求]
     * @return [type] [description]
     */
    function delete_drogue()
    {
        $db=&db();
        if ($_POST['del_id']) {
          $del="delete from ecm_drogue where id=".$_POST['del_id'];
          $res=$db->query($del);
          if ($res) {
               $output=array("code"=>0,
                             "message"=>"删除成功",
                             "data"=>"");  
          }else{
               $output=array("code"=>3,
                             "message"=>"业务异常",
                             "data"=>"");    
          }
        }else{
          $output=array("code"=>1,
                             "message"=>"删除失败",
                             "data"=>"");  
        }
        
        echo json_encode($output);
    }

    function get_excel_example(){
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=drogue_data.xls");
        //输出内容如下： 
        echo   "当年1/新茶2/中期3"."\t"; 
        echo   "日期"."\t"; 
        echo   "价格"."\t"; 
        echo   "\n"; 
        echo   "1"."\t"; 
        echo   "2015/10/1"."\t"; 
        echo   "232"."\t"; 
        echo   "\n"; 
        echo   "1"."\t"; 
        echo   "2015/10/2"."\t"; 
        echo   "232"."\t"; 
        echo   "\n"; 
        echo   "1"."\t"; 
        echo   "2015/10/3"."\t"; 
        echo   "232"."\t"; 
        echo   "\n"; 
        echo   "1"."\t"; 
        echo   "2015/10/4"."\t"; 
        echo   "232"."\t"; 
    }
}

?>
