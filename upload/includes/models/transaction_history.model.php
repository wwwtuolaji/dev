<?php

/* 商品数据模型 */
class Transaction_historyModel extends BaseModel
{
    var $table  = 'transaction_history';	
    var $prikey = 'history_id';
    var $_name  = 'transaction';
    var $temp; // 临时变量
   
   /**
    * [get_history_info 获取买卖双方用户的姓名]
    * @param  [type] $sn [description]
    * @return [type]     [description]
    */
   function get_history_info($sn){
   	$sql = "select h.*,g.goods_name from ecm_transaction_history as h inner join ecm_goods as g on h.goods_id = g.goods_id where h.history_id = $sn";
   	$history_info = $this->db->getrow($sql);
   	$member_mod = m('member');
   	$buy_arr = $member_mod->get($history_info['buy_sn_id']);
   	$sell_arr = $history_info['sell_sn_id']==-1?'福禄仓投资集团':$member_mod ->get($history_info['sell_sn_id']);
   	$name_arr['transaction_history_count'] = $history_info['transaction_history_count'];
   	$name_arr['transaction_price'] = $history_info['transaction_price'];
   	$name_arr['goods_name'] = $history_info['goods_name'];
   	$name_arr['transaction_time'] = $buy_arr['transaction_time'];
   	$name_arr['buyer'] = $buy_arr['user_name'];
   	$name_arr['seller'] = $sell_arr['user_name'];
   	return $name_arr;
   }

}
