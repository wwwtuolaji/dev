<?php
/* 店铺 store */
class RecordModel extends BaseModel
{
    var $table  = 'record';
    var $prikey = 'record_id';
    var $alias  = 're';
    var $_name  = 'record';
    var $_relation = array(
       
     );
    //获取最新记录
    function getrecord_by_goodsid($goods_id,$user_id){
    	$sql="select * from ecm_record where goods_id='$goods_id' and user_id='$user_id' order by visit_date desc limit 1";
    	return $this->db->getrow($sql);
    }
    //添加记录
       /* 新增 */
    function add($data, $compatible = false)
    {
        $id = parent::add($data, $compatible);
        if ($res === false)
        {
            return false;
        }

        return $id ;
    }


    function edit($conditions, $edit_data)
    {  
      return parent::edit($conditions, $edit_data);
    }
    /**
     * [get_record_by_store_id 获取浏览信息]
     * @param  [type] $store_id [店铺id]
     * @return [array]
     */
    function get_record_by_store_id($store_id,$page=10)
    {
        $sql="SELECT us.user_name, gs.goods_name, us.member_level, us.phone_tel, rd.visit_date, priv.store_id, store.store_name FROM ecm_record AS rd INNER JOIN ecm_member AS us ON rd.user_id = us.user_id INNER JOIN ecm_goods AS gs ON rd.goods_id = gs.goods_id LEFT JOIN ecm_user_priv AS priv ON rd.user_id = priv.user_id LEFT JOIN ecm_store AS store ON store.store_id = priv.store_id WHERE rd.goods_id IN (SELECT gs.goods_id FROM ecm_goods AS gs WHERE gs.store_id = '$store_id') AND rd.user_id<>(select user_id from ecm_user_priv where store_id='$store_id') order by visit_date desc limit $page";
       //1.获取当前店铺的用户信息
       
        return $record_arr= $this->db->getall($sql);

    }
    /**
     * [get_record_count_by_store_id 获取当前商店的浏览记录总数]
     * @param  [type] $store_id [商铺id]
     * @return [type]           [description]
     */
    function get_record_count_by_store_id($store_id)
    {
        $sql="select count(*) from ecm_record as rd WHERE rd.goods_id IN (SELECT gs.goods_id FROM ecm_goods AS gs WHERE gs.store_id = '$store_id') AND rd.user_id<>(select user_id from ecm_user_priv where store_id='$store_id')";
        return $this->db->getone($sql);
    }

}