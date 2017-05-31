<?php
class Transaction_initModel extends BaseModel
{
    var $table  = 'transaction_init';
    var $prikey = 'id';
    var $_name  = 'transaction_init';

    /**
     * [get_all 获取所有关联信息]
     * @return [type] [description]
     */
    function get_all($limit = ''){
    		$sql = "select a.*,b.goods_name from ecm_transaction_init as a inner join ecm_share_tea as b on a.goods_id = b.goods_id order by a.apply_time desc limit $limit";
    		return $this->db->getall($sql);
    }
    function get_count(){
    		$sql = "select count(*) from ecm_transaction_init as a ";
    		return $this->db->getone($sql);
    }
    
}

?>