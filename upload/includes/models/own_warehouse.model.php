<?php

/* 商品数据模型 */
class Own_warehouseModel extends BaseModel
{
    var $table  = 'own_warehouse';	
    var $prikey = 'warehouse_id';
    var $_name  = 'own_warehouse';
    var $temp; // 临时变量
   
   /**
    * [get_warehouse description]
    * @return [type] [description]
    */
   function get_warehouse($goods_id,$user_id){
   	//获取当前库存信息，减少库存
	$get_stock="select * from ecm_own_warehouse where goods_id='$goods_id' and user_id=$user_id";
	return $this->db->getrow($get_stock);
   }
}
