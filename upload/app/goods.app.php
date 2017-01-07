<?php

/* 商品 */
class GoodsApp extends StorebaseApp
{
    var $_goods_mod;
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
    }

    function index()
    {
        /* 参数 id */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        /* 可缓存数据 */
        $data = $this->_get_common_info($id);    
       
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 更新浏览次数 */
        $this->_update_views($id);

        //是否开启验证码
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }
        $this->assign('guest_comment_enable', Conf::get('guest_comment'));
        $this->display('goods.index.html');
    }

    /* 商品评论 */
    function comments()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }


        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
     

        /* 赋值商品评论 */
        $data = $this->_get_goods_comment($id, 10);
        $this->_assign_goods_comment($data);

        $this->display('goods.comments.html');
    }

    /* 销售记录 */
    function saleslog()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
        
        /* 赋值销售记录 */
        $data = $this->_get_sales_log($id, 10);
        $this->_assign_sales_log($data);

        $this->display('goods.saleslog.html');
    }
    function qa()
    {
        $goods_qa =& m('goodsqa');
         $id = intval($_GET['id']);
         if (!$id)
         {
            $this->show_warning('Hacking Attempt');
            return;
         }
        if(!IS_POST)
        {
            $data = $this->_get_common_info($id);
            if ($data === false)
            {
                return;
            }
            else
            {
                $this->_assign_common_info($data);
            }
            $data = $this->_get_goods_qa($id, 10);
            $this->_assign_goods_qa($data);

            //是否开启验证码
            if (Conf::get('captcha_status.goodsqa'))
            {
                $this->assign('captcha', 1);
            }
          
            $this->assign('guest_comment_enable', Conf::get('guest_comment'));
            /*赋值产品咨询*/
            $this->display('goods.qa.html');
        }
        else
        {
            /* 不允许游客评论 */
            if (!Conf::get('guest_comment') && !$this->visitor->has_login)
            {
                $this->show_warning('guest_comment_disabled');

                return;
            }
            $content = (isset($_POST['content'])) ? trim($_POST['content']) : '';
            //$type = (isset($_POST['type'])) ? trim($_POST['type']) : '';
            $email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
            $hide_name = (isset($_POST['hide_name'])) ? trim($_POST['hide_name']) : '';
            if (empty($content))
            {
                $this->show_warning('content_not_null');
                return;
            }
            //对验证码和邮件进行判断

            if (Conf::get('captcha_status.goodsqa'))
            {
                if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
                {
                    $this->show_warning('captcha_failed');
                    return;
                }
            }
            if (!empty($email) && !is_email($email))
            {
                $this->show_warning('email_not_correct');
                return;
            }
            $user_id = empty($hide_name) ? $_SESSION['user_info']['user_id'] : 0;
            $conditions = 'g.goods_id ='.$id;
            $goods_mod = & m('goods');
            $ids = $goods_mod->get(array(
                'fields' => 'store_id,goods_name',
                'conditions' => $conditions
            ));
            extract($ids);
            $data = array(
                'question_content' => $content,
                'type' => 'goods',
                'item_id' => $id,
                'item_name' => addslashes($goods_name),
                'store_id' => $store_id,
                'email' => $email,
                'user_id' => $user_id,
                'time_post' => gmtime(),
            );
            if ($goods_qa->add($data))
            {
                header("Location: index.php?app=goods&act=qa&id={$id}#module\n");
                exit;
            }
            else
            {
                $this->show_warning('post_fail');
                exit;
            }
        }
    }

    /**
     * 取得公共信息
     *
     * @param   int     $id
     * @return  false   失败
     *          array   成功
     */
    function _get_common_info($id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_goods_' . $id;
        $data = $cache_server->get($key);
        $cached = true;

        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);

            /* 商品信息 */
            $goods = $this->_goods_mod->get_info($id);
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();

            $data['goods'] = $goods;

            /* 店铺信息 */
            if (!$goods['store_id'])
            {
                $this->show_warning('store of goods is empty');
                return false;
            }
            $this->set_store($goods['store_id']);
            $data['store_data'] = $this->get_store_data();

            /* 当前位置 */
            $data['cur_local'] = $this->_get_curlocal($goods['cate_id']);
            $data['goods']['related_info'] = $this->_get_related_objects($data['goods']['tags']);
            /* 分享链接 */
            $data['share'] = $this->_get_share($goods);

            $cache_server->set($key, $data, 1800);
        }
        if ($cached)
        {
            $this->set_store($data['goods']['store_id']);
        }
        //@jjc 在images数组中加入修改后的图片
         if (!empty($id)) {
               $sql_img="select * from ecm_goods_image_r where goods_id='$id'";
               $db=& db();
               $img_arr=$db->getall($sql_img);
            }
             foreach ($img_arr as $key => $img_value) {
                    //上面的数组没有，进行自定义的数组查询
                    $img_value['custom']=true;
                    if (empty($default_goods_images) && !empty($goods['default_image']) && ($img_value['thumbnail'] == $goods['default_image'])) {
                        $default_goods_images[] = $img_value;
                    }else{
                        //当前的key 要跟上面的key区分避免覆盖
                        $data['goods']['_images'][]=$img_value;  
                    }
                }

        return $data;
    }

    function _get_related_objects($tags)
    {
        if (empty($tags))
        {
            return array();
        }
        $tag = $tags[array_rand($tags)];
        $ms =& ms();

        return $ms->tag_get($tag);
    }

    /* 赋值公共信息 */
    function _assign_common_info($data)
    {

       
        /* 商品信息 */
        $goods = $data['goods'];
        
        //数据处理不同的会员显示不同的价格
        //判断用户是否登陆？

        if (!$this->visitor->has_login)
        {
            //未登录
            foreach ($goods['_specs'] as $key => $value) {
                $goods['_specs'][$key]['member_price_show']='false';
            }                         
        }else{
             //已经登陆判断会员级别
            $user_info=$this->visitor->get();
            foreach ($goods['_specs'] as $key => $value) {
                if (in_array($user_info['member_level'], array(1,2,3,4))) {
                    $str="member_price_".trim($user_info['member_level']);
                    $goods['_specs'][$key]['member_price_show']=$goods['_specs'][$key][$str];  
                }else{
                    $goods['_specs'][$key]['member_price_show']='false';  
                }             
            }
        }
        /*var_dump($goods);
        die;*/
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        /* 店铺信息 */
        $this->assign('store', $data['store_data']);

        /* 浏览历史 */
        $this->assign('goods_history', $this->_get_goods_history($data['id']));

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* 当前位置 */
        $this->_curlocal($data['cur_local']);

        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info($data['goods']));

        /* 商品分享 */
        $this->assign('share', $data['share']);
        //获取商品相关信息
        if (!empty($_GET['id'])) {
            $goods_id=$_GET['id'];
            $db=&db();
            //默认显示七天的时间
            $now_date=date("Y-m-d");
            $firstday = strtotime("$now_date -6 day");
            $lastday = strtotime("$now_date");
            $temp_start_time=date("Y-m-d",$firstday);
            $temp_end_time=date("Y-m-d",$lastday);
            $this->assign("temp_start_time",$temp_start_time);
            $this->assign("temp_end_time",$temp_end_time);

            $sql="select * from ecm_drogue where goods_id=$goods_id AND show_date>='".$firstday ."' AND show_date<='".$lastday . "'order by show_date asc";
            $drogue=$db->getAll("$sql");   
            if (empty($drogue)) {
               $drogue="";
            }else{
            $drogue=json_encode($drogue); 
              
            }
             $this->assign('drogue',$drogue);

        }
        
        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js,jquery.plugins/jquery.validate.js,mlselection.js,My97DatePicker/WdatePicker.js,jquery-form.js,echart/build/dist/echarts.js',
            'style' => 'res:jqzoom.css'
        )); 
    }

    /* 取得浏览历史 */
    function _get_goods_history($id, $num = 9)
    {
        $goods_list = array();
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows = $this->_goods_mod->find(array(
                'conditions' => $goods_ids,
                'fields'     => 'goods_name,default_image',
            ));
            foreach ($goods_ids as $goods_id)
            {
                if (isset($rows[$goods_id]))
                {
                    empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
                    $goods_list[] = $rows[$goods_id];
                }
            }
        }
        $goods_ids[] = $id;
        if (count($goods_ids) > $num)
        {
            unset($goods_ids[0]);
        }
        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));

        return $goods_list;
    }

    /* 取得销售记录 */
    function _get_sales_log($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $sales_list = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND status = '" . ORDER_FINISHED . "'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, add_time, anonymous, goods_id, specification, price, quantity, evaluation',
            'count' => true,
            'order' => 'add_time desc',
            'limit' => $page['limit'],
        ));
        $data['sales_list'] = $sales_list;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_sales'] = $page['item_count'] > $num_per_page;

        return $data;
    }

    /* 赋值销售记录 */
    function _assign_sales_log($data)
    {
        $this->assign('sales_list', $data['sales_list']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('more_sales', $data['more_sales']);
    }

    /* 取得商品评论 */
    function _get_goods_comment($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $comments = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND evaluation_status = '1'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
            'count' => true,
            'order' => 'evaluation_time desc',
            'limit' => $page['limit'],
        ));
        $data['comments'] = $comments;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_comments'] = $page['item_count'] > $num_per_page;

        return $data;
    }

    /* 赋值商品评论 */
    function _assign_goods_comment($data)
    {
        $this->assign('goods_comments', $data['comments']);
        $this->assign('page_info',      $data['page_info']);
        $this->assign('more_comments',  $data['more_comments']);
    }

    /* 取得商品咨询 */
    function _get_goods_qa($goods_id,$num_per_page)
    {
        $page = $this->_get_page($num_per_page);
        $goods_qa = & m('goodsqa');
        $qa_info = $goods_qa->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post,time_reply',
            'conditions' => '1 = 1 AND item_id = '.$goods_id . " AND type = 'goods'",
            'limit' => $page['limit'],
            'order' =>'time_post desc',
            'count' => true
        ));
        $page['item_count'] = $goods_qa->getCount();
        $this->_format_page($page);

        //如果登陆，则查出email
        if (!empty($_SESSION['user_info']))
        {
            $user_mod = & m('member');
            $user_info = $user_mod->get(array(
                'fields' => 'email',
                'conditions' => '1=1 AND user_id = '.$_SESSION['user_info']['user_id']
            ));
            extract($user_info);
        }

        return array(
            'email' => $email,
            'page_info' => $page,
            'qa_info' => $qa_info,
        );
    }

    /* 赋值商品咨询 */
    function _assign_goods_qa($data)
    {
        $this->assign('email',      $data['email']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('qa_info',    $data['qa_info']);
    }

    /* 更新浏览次数 */
    function _update_views($id)
    {
        $goodsstat_mod =& m('goodsstatistics');
        $goodsstat_mod->edit($id, "views = views + 1");
    }

    /**
     * 取得当前位置
     *
     * @param int $cate_id 分类id
     */
    function _get_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => url('app=category')),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => url('app=search&cate_id=' . $category['cate_id']));
        }
        $curlocal[] = array('text' => LANG::get('goods_detail'));

        return $curlocal;
    }

    function _get_share($goods)
    {
        $m_share = &af('share');
        $shares = $m_share->getAll();
        $shares = array_msort($shares, array('sort_order' => SORT_ASC));
        $goods_name = ecm_iconv(CHARSET, 'utf-8', $goods['goods_name']);
        $goods_url = urlencode(SITE_URL . '/' . str_replace('&amp;', '&', url('app=goods&id=' . $goods['goods_id'])));
        $site_title = ecm_iconv(CHARSET, 'utf-8', Conf::get('site_title'));
        $share_title = urlencode($goods_name . '-' . $site_title);
        foreach ($shares as $share_id => $share)
        {
            $shares[$share_id]['link'] = str_replace(
                array('{$link}', '{$title}'),
                array($goods_url, $share_title),
                $share['link']);
        }
        return $shares;
    }
    
    function _get_seo_info($data)
    {
        $seo_info = $keywords = array();
        $seo_info['title'] = $data['goods_name'] . ' - ' . Conf::get('site_title');        
        $keywords = array(
            $data['brand'],
            $data['goods_name'],
            $data['cate_name']
        );
        $seo_info['keywords'] = implode(',', array_merge($keywords, $data['tags']));        
        $seo_info['description'] = sub_str(strip_tags($data['description']), 10, true);
        return $seo_info;
    }
    /**
     * [get_goodsinfo 响应异步请求]
     * @return [type] [description]
     */
    function get_goodsinfo(){
        $id         =$_GET['id'];
        if (empty($id)) {
            $output=array(  "code"      =>1,
                            "message"   =>"商品id为空，请检查",
                            "data"      =>""
                            );
           echo  json_encode($output);
        return;
        }
        if (empty($_GET['begin_unix'])||empty($_GET['end_unix'])) {
            $output=array(  "code"      =>2,
                            "message"   =>"时间选择异常，请检查",
                            "data"      =>""
                            );
            echo  json_encode($output);
            return;
        } 
        if (empty($_GET['serch_method'])) {
          $_GET['serch_method']="day";  
        }
        if ($_GET['serch_method']=="day") {
            //判断是否大于12天
           /* if ($_GET['end_unix']-$_GET['begin_unix']>3600*24*12) {
                $output=array(  "code"      =>3,
                                "message"   =>"按天选择，不可超出12天!",
                                "data"      =>""
                                );
                echo  json_encode($output);
                return;            
            }*/
        }else{
             //判断是否大于12个月
                $end_unix_n=date('Y-m-d',$_GET['end_unix']);
                $begin_unix_n=date('Y-m-d',$_GET['begin_unix']);
                $date1 = explode("-",$end_unix_n);
                $date2 = explode("-",$begin_unix_n);
                $month = abs($date1[0]-$date2[0])*12+abs($date1[1]-$date2[1]);
                if ($_GET['end_unix']-$_GET['begin_unix']>367*3600*24) {
                    $output=array(  "code"      =>4,
                                    "message"   =>"按月选择，不可超出12个月!",
                                    "data"      =>""
                                    );
                   /* echo  json_encode($output);
                    return;   */         
                }
            
            }
        $db = &db(); 
        if ($_GET['serch_method']==="month") {
            //按月显示设置日期显示样式
            for ($i=0; $i <=$month;) { 
                //1.获取每个月的平均值
                $firstday_m = date('Y-m-01', $_GET['begin_unix']);
                $firstday=strtotime("$firstday_m +$i month");
                $firstday_m = date('Y-m-01', $firstday);
                $lastday = date('Y-m-d', strtotime("$firstday_m +1 month -1 day"));
                $lastday=strtotime("$lastday +$i month")+24*3600;
                $sql="select * from ecm_drogue where goods_id=$id AND show_date>='".$firstday ."' AND show_date<=".$lastday ."group by show_date asc";
                $goods_arr_s=$db->getall($sql);
             
                $ref_price_total=0;
                $real_price_total=0;
                $month_count=count($goods_arr_s);
                if (empty($month_count)) {
                    $month_count=1;
                }
                foreach ($goods_arr_s as $key_s => $value_s) {
                    $ref_price_total  +=$value_s['ref_price'];
                    $real_price_total +=$value_s['real_price'];
                    //$type[]=$value_s['type'];
                    $show_date = date('Y-m-01', $value_s['show_date']);
                    $show_date=strtotime($show_date);
                }
                if (empty($ref_price_total)) {
                    $ref_price_total=0;
                }
                if (empty($real_price_total)) {
                    $real_price_total=0;
                }
                $save[$i]['show_date']=$firstday;
                $save[$i]['ref_price']=floor($ref_price_total/$month_count);
                $save[$i]['real_price']=floor($real_price_total/$month_count);
                $i++;
            }
            $output=array( "code"      =>0,
                           "message"   =>"请求成功",
                           "data"      =>$save
                        );
            echo json_encode($output);
            return;

        }
        $goods_arr=$db->getall("select * from ecm_drogue where goods_id=$id AND show_date>='".$_GET['begin_unix'] ."' AND show_date<='".$_GET['end_unix']."'group by show_date asc");
        $output=array(  "code"      =>0,
                        "message"   =>"请求成功",
                        "data"      =>$goods_arr
                            );
        echo json_encode($output);
        return;
    }

      /**
     * [produce_day_price 生成当天的日期价格]
     * @param  [type] $date [日期 y-m-d]
     * @return [type]       [description]
     */
    function produce_day_price($date="",$goods_id=""){

        //1.获取到所有的商品的茶叶id
        $sql="select cate_id from ecm_gcategory where parent_id in (42,43,44)";
        $db=&db();
        $cate_arr=$db->getall($sql);

        foreach ($cate_arr as $key => $value) {
            $cate[]=$value['cate_id'];
        }
       $cate= implode(",",$cate);
       strchr($cate,-1);
       /* $sql="select a.goods_id,a.goods_name,a.price,a.cate_id from ecm_goods as a where a.cate_id in (42,43,44,92)";*/
       $sql="select a.goods_id,a.goods_name,a.price,a.cate_id from ecm_goods as a where a.cate_id in ($cate)";
        $cate_arr=$db->getall($sql);
        $days=31*6;
        $seria=date("Y-m-d");
        $time=strtotime("$seria");
        $time=$time-3600*24;
        $n=date("m");
        $n=$n-1;
        //判断有没有跨月
        $column="insert into ecm_drogue (goods_id,real_price,type,show_date) values";
         
        foreach ($cate_arr as $key => $value) {
            //组装sql
            $sql_value="";
       
                //$num++
               $ref_price_mt= mt_rand(-20,40);

               if (in_array($value['cate_id'], array(84,86,87,88,89,90,91,92,93,94,102,103,104,105,106,107,108,109,110,111))) {
                   //中期茶中 期茶2010年之前
                   $type=2;       
               }else if(in_array($value['cate_id'],array(95,96,97,98,99,112,113,114,115,116))) {
                   //新茶  新茶包含2010-2014
                   $type=1;                 
               }else{
                   $type=0;
                   # code...
               }
                echo "$type";
                $cha=$this->randomFloat($value['price']*(-0.015)+($value['price']*$n),$value['price']*0.025+($value['price']*$n)); 
                $cha=number_format($cha,2);
                $price=$value['price']+$cha;
                $sql_value .="('". $value['goods_id'] ."','" . $price ."','".$type."','". $time ."'),";               
            $sql_value2=$column . $sql_value;
            $sql_value2=substr($sql_value2, 0, -1);
            $sql_r[]=$sql_value2 ;
        }
        header("Content-Type:text/html;charset=utf-8");
        foreach ($sql_r as $key => $value) {
            $result=$db->query($value);
            if ($result) {
                echo "第$key 执行成功<br/>";
            }
        }
    }
    
    function randomFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}

?>
