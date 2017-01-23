<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Betterslide_imageWidget extends BaseWidget
{
    var $_name = 'betterslide_image';
    var $_num  = 5;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $recom_mod =& m('recommend');
            $data = $recom_mod->get_recommended_goods($this->options['slide_outer']['img_recom_id'], $this->_num, true, $this->options['slide_outer']['img_cate_id']);
            $cache_server->set($key, $data, $this->_ttl);
        }
        $title_content=$this->options;
        $data['title_content']=$title_content['slide_outer']['title_content'];
        $result=$this->options;
        $result['slide_outer']=$data;
        //dump($result);
        return $result;
    }
    
    function get_config_datasrc()
    {
        // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());
        // 取得保存的title
        $title_content=$this->options;
        $this->assign('title_content',$title_content['slide_outer']['title_content']);
        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(1));
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
        $output['slide_src']=$result;

        if ($input['img_recom_id'] >= 0)
        {
            $input['img_cate_id'] = 0;
        }
        if (empty($input['title_content'])) {   
            $input['title_content']='中国好商品';
        }
        $output['slide_outer']=$input;
      

        return $output;
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
}

?>