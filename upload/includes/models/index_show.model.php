<?php

/* 商品数据模型 */
class Index_showModel extends BaseModel
{
    var $table  = 'index_show';
    var $prikey = 'id';
    var $_name  = 'index_show';
    var $temp; // 临时变量
   
    /**
     * [return_arr_val 处理二维数组的val值]
     * @param  [type] $arr [description]
     * @return [type]      [返回]
     */
    function return_arr_val($arr){

    	$arr['slide_img_url'] =  explode('||', $arr['slide_img_url']);
    	$arr['slide_link'] =  explode('||', $arr['slide_link']);
    	$arr['qiye_banner_val'] =  explode('||', $arr['qiye_banner_val']);
	$arr['qiye_banner_name'] =  explode('||', $arr['qiye_banner_name']);
    	$arr['qiye_banner_link'] =  explode('||', $arr['qiye_banner_link']);
    	$arr['zizhi_url'] =  explode('||', $arr['zizhi_url']);
    	$arr['zizhi_link'] =  explode('||', $arr['zizhi_link']);
    	$arr['zizhi_name'] =  explode('||', $arr['zizhi_name']);
    	$arr['qq_num_des'] =  explode('||', $arr['qq_num']);
    	return $arr;
    }
  
}
