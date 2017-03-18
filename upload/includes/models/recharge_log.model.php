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
	return $str='<div id="dialog_object_deposit_captcha" class="dialog_wrapper ui-draggable simple-blue" style="z-index: 9999; position: absolute; width: 400px; left: 566.5px; top: 50%;"> <div class="dialog_body" style="position: relative;"> <h3 class="dialog_head" style="cursor: move;"> <div class="dialog_ornament1"></div> <div class="dialog_ornament2"></div> <span class="dialog_title"> <span class="dialog_title_icon">验证码</span> </span> <span class="dialog_close_button" style="position: absolute; text-indent: -9999px; cursor: pointer; overflow: hidden;">close</span> </h3> <div class="dialog_content" style="margin: 0px; padding: 0px;"> <div class="captcha-form"> <ul class="tab J_Tab clearfix"> <li class="active" codetype="phone">手机短信验证</li> <li codetype="email">邮箱验证</li> </ul> <div class="eject_con"> <div class="add"> <div id="warning"></div> <div class="captcha-fields J_TabForm"> <form method="post" action="" id="captcah_form" target="iframe_post"> <ul class="each"> <li class="clearfix"> <p class="first">您的手机号</p> <p> <input name="'. $phone_mob .'" value="" type="hidden">'. $phone_mob .'</p> </li> <li class="clearfix"> <p class="first">手机验证码</p> <p> <input name="phone_code" class="text width_short" type="text"> <input value="免费获取验证码" id="send_phonecode" type="button"></p> </li> </ul> <ul class="each hidden"> <li class="clearfix"> <p class="first">您的邮箱</p> <p> <input name="email" value="'. $email .'" type="hidden">'.$email.'</p> </li> <li class="clearfix"> <p class="first">邮箱验证码</p> <p> <input name="email_code" class="text width_short" type="text"> <input value="免费获取验证码" id="send_emailcode" type="button"></p> </li> </ul> <div class="mt10 clearfix"> <input value="phone" class="J_CodeType" type="hidden"> <input value="提交" id="gs_submit" class="btn-submit" type="button"></div> </form> </div> </div> </div> </div> </div> </div> <div style="clear:both;display:block;"></div> </div> <div id="mengban"style="position: absolute; top: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 999; width: 1519px; height: 1421px;"></div> <!--end-->'; 
}
    

function get_member_info($user_id){
	$member_mod = m('member');
	$data = array("conditions" =>"user_id =$user_id","fields"=>"phone_mob,email");
	$member_info = $member_mod->get($data);
	return $member_info;
}
}