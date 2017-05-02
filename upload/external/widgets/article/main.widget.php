<?php

/**
 * 公告栏挂件
 *
 * @param   string  $ad_image_url   广告图片地址
 * @param   string  $ad_link_url    广告链接地址
 * @return  array
 */
class ArticleWidget extends BaseWidget
{
    var $_name = 'article';
    var $_ttl  = 86400;
    var $_num  = 5;

    function _get_data()
    {
        $cache_server =& cache_server();
        $data=$this->_get_zhiji_data();
         //  var_dump($data);

        return array(
            'report'       => $data,
            'ad_image_url'  => $this->options['ad_image_url'],
            'ad_link_url'   => $this->options['ad_link_url'],
        );
    }

    function parse_config($input)
    {
        $image = $this->_upload_image();
        if ($image)
        {
            $input['ad_image_url'] = $image;
        }

        return $input;
    }

    function _upload_image()
    {
        import('uploader.lib');
        $file = $_FILES['ad_image_file'];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            return $uploader->save('data/files/mall/template', $uploader->random_filename());
        }

        return '';
    }
    /**
     * [_get_zhiji_data 获取直击报导的文章信息]
     * @return [type] [description]
     */
    function _get_zhiji_data(){
        $db=db();
        $sql="select cate_id from ecm_acategory where cate_name='直击报导'";
        $cate_id=$db->getone($sql);
        if (!(empty($cate_id))) {
            $getcol="select * from ecm_article where cate_id ='$cate_id' order by sort_order limit 7";
            $articles=$db->getall($getcol);
            foreach ($articles as $key => $article) {
                $articles[$key]['add_time_des'] = date("Y-m-d",$article['add_time']);
            }

            return array('cate_id'=>$cate_id    ,'articles'=>$articles);
        }

        return array();
    }
}

?>