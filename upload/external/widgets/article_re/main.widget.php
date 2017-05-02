<?php

/**
 * 公告栏挂件
 *
 * @param   string  $ad_image_url   广告图片地址
 * @param   string  $ad_link_url    广告链接地址
 * @return  array
 */
class Article_reWidget extends BaseWidget
{
    var $_name = 'article_re';
    var $_num  = 6;
    var $articles_type  =  array(1=>'直击报导', 2=>'最新资讯', 3=>'名人专栏', 4=>'专家分析', 5=>'专家解疑',6=>'每天行情',7=>'一周走势',8=>'新品速递',9=>'经典点评');
  

     function _get_data()
    {
        
        $image  = $this->options['image'];
        $arr['show_count']  = $this->options['show_count'];  
        $arr['article_type_id']  = $this->options['article_type_id'];  
        
        $data=$this->_get_article_data($arr);
        return array(
            'report'       => $data,
            'image'  => $image,
        );

    }

  
    function parse_config($input)
    {
        $result = array();
        $num    = isset($input['ad_link_url']) ? count($input['ad_link_url']) : 0;
        if ($num > 0)
        {
            $images = $this->_upload_image($num);
            for ($i = 0; $i < $num; $i++)
            {
                if (!empty($images[$i]))
                {
                    $input['ad_image_url'][$i] = $images[$i];
                }
    
                if (!empty($input['ad_image_url'][$i]))
                {
                    $result[] = array(
                        'ad_image_url' => $input['ad_image_url'][$i],
                        'ad_link_url'  => $input['ad_link_url'][$i]
                    );
                }
            }
        }
        $out_data ['image'] = $result;
        $out_data ['article_type_id'] = $input['article_type_id'];
        $out_data ['show_count'] = $input['show_count'];
        return $out_data;
    }

    function _upload_image($num)
    {
        import('uploader.lib');

        $images = array();
        for ($i = 0; $i < $num; $i++)
        {
            $file = array();
            foreach ($_FILES['ad_image_file'] as $key => $value)
            {
                $file[$key] = $value[$i];
            }

            if ($file['error'] == UPLOAD_ERR_OK)
            {
                $uploader = new Uploader();
                $uploader->allowed_type(IMAGE_FILE_TYPE);
                $uploader->addFile($file);
                $uploader->root_dir(ROOT_PATH);
                $images[$i] = $uploader->save('data/files/mall/template', $uploader->random_filename());
            }
        }

        return $images;
    }
    /**
     * [_get_zhiji_data 获取直击报导的文章信息]
     * @return [type] [description]
     */
    function _get_article_data($arr){
        $db=db();

        $serch_content = $this->articles_type[$arr['article_type_id']];
        $sql="select cate_id from ecm_acategory where cate_name='$serch_content'";
        $cate_id=$db->getone($sql);
        $limit = $arr['show_count'];
        if (empty($limit)) {
            $limit =10;
        }
        if (!(empty($cate_id))) {
            if ($limit>=5) {
                $getcol="select * from ecm_article where cate_id ='$cate_id' order by sort_order limit 0,5";
            }else{
                $getcol="select * from ecm_article where cate_id ='$cate_id' order by sort_order limit 0,$limit";
            }
            
            $articles=$db->getall($getcol);
            foreach ($articles as $key => $article) {
                $articles[$key]['add_time_des'] = date("Y-m-d",$article['add_time']);
            }
            $arr['arr1']=$articles;

            /*第二个数组*/
            if ($limit>5) {
                if(10 -$limit >=0){
                    $getcol_re="select * from ecm_article where cate_id ='$cate_id' order by sort_order limit 5,5";
                   
                }else{
                    $limit_re = 10 -$limit; 
                    $getcol_re="select * from ecm_article where cate_id ='$cate_id' order by sort_order limit 5,$limit_re";  
                }
                $articles_re =$db->getall($getcol_re);
                foreach ($articles_re as $key => $article) {
                    $articles_re[$key]['add_time_des'] = date("Y-m-d",$article['add_time']);
                }
                $arr['arr2']=$articles_re;
            }
            $arr['article_type_des']=$serch_content;
            $arr['cate_id'] = $cate_id;
            return $arr;
        }

        return array('article_type_des'=>$serch_content);
    }

     function get_config_datasrc()
    {
        // 取得推荐类型   ;
        $this->assign('articles_type', $this->articles_type);

    
    }
}

?>