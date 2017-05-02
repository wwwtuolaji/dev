<?php

/* 商品数据模型 */

class Money_historyModel extends BaseModel
{
    var $table = 'money_history';
    var $prikey = 'money_history_id';
    var $_name = 'money_history';
    var $temp; // 临时变量
    var $db;

      function get_all($user_id,$limit,$where=''){
	      $get_money_log = "select * from ecm_money_history where user_id = '$user_id' $where limit $limit";
	      $money_array = $this->db->getall($get_money_log);
	      return $money_array;
      }
      /*获取*/
      function get_income($user_id){
      	$get_money_log = "select receive_money from ecm_money_history where user_id = '$user_id' and transaction_type = 0";
      	$money_array = $this->db->getcol($get_money_log);
      	$money_count_temp =0;
      	foreach ($money_array as $money_price) {
      		$money_count_temp =$money_count_temp + (int)(string)($money_price*100);
      	}
      	$money_count = $money_count_temp/100;
      	return $money_count;

      }
      /**
       * [get_expenditure 支出]
       * @param  [type] $user_id [description]
       * @return [type]          [description]
       */
     	function get_expenditure($user_id){
     		$get_money_log = "select receive_money from ecm_money_history where user_id = '$user_id' and transaction_type = 1";
      	$money_array = $this->db->getcol($get_money_log);
      	$money_count_temp =0;
      	foreach ($money_array as $money_price) {
      		$money_count_temp =$money_count_temp + (int)(string)($money_price*100);
      	}
      	$money_count = $money_count_temp/100;
      	return $money_count;
     	}

     	function getCount_money($user_id,$where=''){
     		 $get_money_log = "select count(*) from ecm_money_history where user_id = '$user_id'$where";
	      $money_array = $this->db->getone($get_money_log);
	      return $money_array;
     	}
   
    
}