<?php

/* 商品数据模型 */
class DrogueModel extends BaseModel
{
    var $table  = 'drogue';
    var $prikey = 'id';
    var $_name  = 'drogue';
    var $temp; // 临时变量
   
    var $_relation = array(
       
     );

    function get_drogue_arr($id,$limit=20){

    	$sql="select * from ecm_drogue where goods_id='$id' order by show_date desc limit $limit";
    	return $this->db->getall($sql);

    }
    function get_drogue_count_by_store_id($id){
    		$sql="select count(*) from ecm_drogue where goods_id='$id'";
    	  return $this->db->getone($sql);

    }
  
}
