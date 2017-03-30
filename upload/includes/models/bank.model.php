<?php

/* 商品数据模型 */

class BankModel extends BaseModel
{
    var $table = 'bank';
    var $prikey = 'bank_id';
    var $_name = 'bank';
    var $temp; // 临时变量
    var $db;
    var $bank_name = array(
    			"ICBC"	=> "中国工商银行",
    			"CCB"  	=> "中国建设银行",
    			"ABC"  	=> "中国农业银行",
    			"POSTGC" 	=> "中国邮政储蓄银行",
    			"COMM" 	=> "交通银行",
    			"CMB" 	=> "招商银行",
    			"BOC" 	=> "中国银行",
    			"CEBBANK" 	=> "中国光大银行",
    			"CITIC" 	=> "中信银行",
    			"SPABANK" 	=> "深圳发展银行",
    			"SPDB" 	=> "上海浦东发展银行",
    			"CMBC" 	=> "中国民生银行",
    			"CIB" 	=> "兴业银行",
    			"GDB" 	=> "广东发展银行",
    			"SHRCB" 	=> "上海农村商业银行",
    			"SHBANK" 	=> "上海银行",
    			"NBBANK" 	=> "宁波银行",
    			"HZCB" 	=> "杭州银行",
    			"BJBANK" 	=> "北京银行",
    			"BJRCB"	=> "北京农村商业银行",
    			"FDB"		=> "富滇银行",
    			"WZCB"	=> "温州银行",
    			"CDCB"	=> "成都银行",
    			"HXBANK"	=> "华夏银行",
    		);
    	function get_bank_name(){
    		return $this->bank_name;
    	}
    	function getall_list($user_id){
    		$sql="select ecm_bank.* from ecm_bank where user_id='$user_id' and bank_status>-1";
        	$bank_arr = $this->db->getall($sql);
        	if ($bank_arr) {
        		foreach ($bank_arr as $key => $bank) {
        			//$bank_arr[$key]['bank_name'] = $bank[$bank['short_name']];
        			//1,借记卡2，信用卡
        			if ($bank['type']==1) {
        				$bank_arr[$key]['type_des'] = "debit";
        			}else{
        				$bank_arr[$key]['type_des'] = "credit";	
        			}	
        			
        		}
        	}else{
        		return array();
        	}
        	return $bank_arr;
    	}
    	/**
    	 * [get_count 获取当前的数量]
    	 * @return [int] []
    	 */
    	function get_count($user_id){
    		$count_sql="select count(user_id) from ecm_bank where user_id='$user_id' and bank_status>-1";
        	$bank_count=$this->db->getone($count_sql);
        	if (empty($bank_count)) {
        		return 0;
        	}
        	return $bank_count;
    	}
        /**
         * [get_short_name 获取银行卡简称]
         * @param  [type] $bank_id [description]
         * @return [type]          [description]
         */
        function get_short_name($bank_id){
            $sql = "select short_name from ecm_bank where bank_id =$bank_id";
            return $this->db->getone($sql);
        }   
}