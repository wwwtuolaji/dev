<?php

/* 商品数据模型 */

class Recharge_logModel extends BaseModel
{
    var $table = 'recharge_log';
    var $prikey = 'recharge_id';
    var $_name = 'recharge_log';
    var $temp; // 临时变量
    var $db;
    
	function get_dialog_html($user_id){
		//获取手机号码
		$user_info=$this->get_member_info($user_id);
		$phone_mob = substr_replace($user_info['phone_mob'],"*****",3,5);
		$email     = substr_replace($user_info['email'],"****",3,5);
		return $str='<div id="dialog_object_deposit_captcha" class="dialog_wrapper ui-draggable simple-blue" style="z-index: 9999; position: absolute; width: 400px; left: 566.5px; top: 50%;"> <div class="dialog_body" style="position: relative;"> <h3 class="dialog_head" style="cursor: move;"> <div class="dialog_ornament1"></div> <div class="dialog_ornament2"></div> <span class="dialog_title"> <span class="dialog_title_icon">验证码</span> </span> <span class="dialog_close_button" style="position: absolute; text-indent: -9999px; cursor: pointer; overflow: hidden;">close</span> </h3> <div class="dialog_content" style="margin: 0px; padding: 0px;"> <div class="captcha-form"> <ul class="tab J_Tab clearfix"> <li class="active" codetype="phone">手机短信验证</li> <li codetype="email">邮箱验证</li> </ul> <div class="eject_con"> <div class="add"> <div id="warning"></div> <div class="captcha-fields J_TabForm"> <form method="post" action="" id="captcah_form" target="iframe_post"> <ul class="each"> <li class="clearfix"> <p class="first">您的手机号</p> <p> <input name="'. $phone_mob .'" value="" type="hidden">'. $phone_mob .'</p> </li> <li class="clearfix"> <p class="first">手机验证码</p> <p> <input name="phone_code" class="text width_short" type="text"> <input value="免费获取验证码" id="send_phonecode" type="button"></p> </li> </ul> <ul class="each hidden"> <li class="clearfix"> <p class="first">您的邮箱</p> <p> <input name="email" value="'. $email .'" type="hidden">'.$email.'</p> </li> <li class="clearfix"> <p class="first">邮箱验证码</p> <p> <input name="email_code" class="text width_short" type="text"> <input value="免费获取验证码" id="send_emailcode" type="button"></p> </li> </ul> <div class="mt10 clearfix"> <input value="phone" class="J_CodeType" type="hidden"> <input value="提交" id="gs_submit" class="btn-submit" type="button"></div> </form> </div> </div> </div> </div> </div> </div> <div style="clear:both;display:block;"></div> </div> <div id="mengban"style="position: absolute; top: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 999; width: 100%; height: 100%;"></div> <!--end-->'; 
	}
	    
	/**
	 * [get_member_info 员工信息]
	 * @param  [type] $user_id [description]
	 * @return [type]          [description]
	 */
	function get_member_info($user_id){
		$member_mod = m('member');
		$data = array("conditions" =>"user_id =$user_id","fields"=>"phone_mob,email");
		$member_info = $member_mod->get($data);
		return $member_info;
	}
	/**
	 * [find_drawlist 提现分页]
	 * @param  [type] $where [description]
	 * @param  [type] $limit [description]
	 * @return [type]        [description]
	 */
	function find_drawlist($where,$limit){
		$sql = "select * from ecm_recharge_log where $where limit $limit";
		return $this->db->getall($sql);
	}
	/**
	 * [get_recharge_count 获取分页的数目]
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	function get_recharge_count($where){
		$sql="select count(*) from ecm_recharge_log where $where";
		return $this->db->getone($sql);
	}
	/*获取当前可用余额*/
	function get_use_money($user_id){
		$sql="select use_money from ecm_member where user_id='$user_id'";
		$use_money = $this->getone($sql);
		if (empty($use_money)) {
			return 0;
		}
		$not_use_money = $this->get_not_use_money($user_id);
		
		$temp = (int)(string)($use_money*100) - (int)(string)($not_use_money*100);
		return $temp/100;
	}
	/**
	 * [get_not_use_money 获取不可用余额]
	 * @param  [type] $user_id [description]
	 * @return [type]          [description]
	 */
	function get_not_use_money($user_id){
		$sql="select pay_money from ecm_recharge_log where user_id = '$user_id' AND pay_status = 80 AND recharge_status = 1";
		$use_money_arr = $this->db->getcol($sql);
		$use_money_temp =0;
		foreach ($use_money_arr as $use_money) {
			$use_money_temp = $use_money_temp +(int)(string)($use_money*100);
		}
		return $use_money_temp/100;

	}

	function find_recharge_all($where,$limit){
		/*$sql = "select * from ecm_recharge_log where $where limit $limit";*/
		$sql = "SELECT DISTINCT member.user_name, log.* FROM ecm_money_history AS history RIGHT JOIN ecm_recharge_log AS log ON history.transaction_sn = log.recharge_id INNER JOIN ecm_member AS member ON log.user_id = member.user_id WHERE (history.platform_from = 3 OR (log.recharge_status = 1 AND log.pay_status in (80,81))) $where limit $limit"; 
		return $this->db->getall($sql);
	}
	function get_wehre_count($where){
		$sql = "SELECT DISTINCT count(log.recharge_id) FROM ecm_money_history AS history RIGHT JOIN ecm_recharge_log AS log ON history.transaction_sn = log.recharge_id INNER JOIN ecm_member AS member ON log.user_id = member.user_id WHERE (history.platform_from = 3 OR (log.recharge_status = 1 AND log.pay_status in (80,81))) $where"; 
		return $this->db->getone($sql);

	}

	
	function getToken($len = 32, $md5 = true) {
	 	 # Seed random number generator
	 	 # Only needed for PHP versions prior to 4.2
	  	mt_srand((double) microtime() * 1000000);
	  	# Array of characters, adjust as desired
	  	$chars = array (
	  	'Q', '@', '8', 'y', '%', '^', '5', 'Z', '(', 'G', '_', 'O', '`', 'S', '-', 'N', '<', 'D', '{', '}', '[', ']', 'h', ';', 'W', '.', '/', '|', ':', '1', 'E', 'L', '4', '&', '6', '7', '#', '9', 'a', 'A', 'b', 'B', '~', 'C', 'd', '>', 'e', '2', 'f', 'P', 'g', ')', '?', 'H', 'i', 'X', 'U', 'J', 'k', 'r', 'l', '3', 't', 'M', 'n', '=', 'o', '+', 'p', 'F', 'q', '!', 'K', 'R', 's', 'c', 'm', 'T', 'v', 'j', 'u', 'V', 'w', ',', 'x', 'I', '$', 'Y', 'z', '*'); # Array indice friendly number of chars;
	  	$numChars = count($chars) - 1;
	  	$token = '';
	  	# Create random token at the specified length
	  	for ($i = 0; $i < $len; $i++)
	    	$token .= $chars[mt_rand(0, $numChars)];
	  	# Should token be run through md5?
	  	if ($md5) {
		   	 # Number of 32 char chunks
		    	$chunks = ceil(strlen($token) / 32);
		    	$md5token = '';
		    	# Run each chunk through md5
		    	for ($i = 1; $i <= $chunks; $i++)
		      $md5token .= md5(substr($token, $i * 32 - 32, 32));
		    	# Trim the token
		    	$token = substr($md5token, 0, $len);
	  	}
	  	return $token;
	}
      function set_token() {
      	$_SESSION['token'] = $this->getToken();
      }	
	function valid_token() {
	  $return = $_REQUEST['token'] === $_SESSION['token'] ? true : false;
	  $this->set_token();
	  return $return;
	}

	function _check_code($code,$user_id){
      
        if (empty($_SESSION['email_code_'.$user_id])) {
            return false;
        }
        /*超出验证时间*/  
        if(time() - $_SESSION['email_code_'.$user_id]['time']>60*5){
            return false;
        }
        if($_SESSION['email_code_'.$user_id]['code'] == $code){
            return true;
        }

    	}
      function start(){
        $this->db->query("SET AUTOCOMMIT=0");
    	}
    	function cancel(){
        $this->db->query('ROLLBACK');
        $this->db->query("SET AUTOCOMMIT=1");
    	}
    	function commit(){
        $this->db->query('COMMIT');
        $this->db->query("SET AUTOCOMMIT=1");
    	}
}  