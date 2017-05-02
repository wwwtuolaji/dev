<?php

/**
 * 公告栏挂件
 *
 * @param   string  $ad_image_url   广告图片地址
 * @param   string  $ad_link_url    广告链接地址
 * @return  array
 */
class Article_new_messageWidget extends BaseWidget
{
    var $_name = 'article_new_message';
    var $_num  = 6;
    var $articles_type  =  array(1=>'直击报导', 2=>'最新资讯', 3=>'名人专栏', 4=>'专家分析', 5=>'专家解疑',6=>'每天行情',7=>'一周走势');
     function _get_data()
    {
       $cache_server =& cache_server();

        $data = $this->_get_article_data($this->options);
    /*      dump($data);*/

        return array(
            'report'       => $data,
            'ad_image_url'  => $this->options['ad_image_url'],
            'ad_link_url'   => $this->options['ad_link_url'],
        );
    }

    function parse_config($input)
    {

        return $input;
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
            $limit =5;
        }
        if (!(empty($cate_id))) {
            $getcol="select * from ecm_article where cate_id ='$cate_id' order by sort_order limit $limit";
            $articles=$db->getall($getcol);
            foreach ($articles as $key => $article) {
                $articles[$key]['add_time_des'] = date("Y-m-d",$article['add_time']);
            }

            return array('cate_id'=>$cate_id,'articles'=>$articles,'article_type_des'=>$serch_content);
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