<?php
class Transaction_applyModel extends BaseModel
{
    var $table  = 'transaction_apply';
    var $prikey = 'id';
    var $_name  = 'transaction_apply';

    /**
     * [get_apply_transaction description]
     * @return [type] [description]
     */
    function get_apply_transaction($user_id){
        $sql ="select a.*,b.goods_name from ecm_transaction_apply as a inner join ecm_share_tea as b on a.goods_id = b.goods_id where a.user_id = $user_id order by apply_time desc";
        return $this->db->getall($sql);
    }
    function get_all($where = "",$limit=''){
        
        $sql ="select a.*,b.goods_name from ecm_transaction_apply as a inner join ecm_share_tea as b on a.goods_id = b.goods_id where 1=1 $where order by apply_time desc limit $limit";
        return $this->db->getall($sql);
    }
    function getCount($where=""){
        $sql ="select count(*) from ecm_transaction_apply as a inner join ecm_share_tea as b on a.goods_id = b.goods_id where 1=1 $where";
        return $this->db->getone($sql);
    }

    
}

?>