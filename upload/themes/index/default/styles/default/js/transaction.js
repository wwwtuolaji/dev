$(function(){
	//解决360兼容问题
	$('#count_tran_in').removeAttr('readonly');
	//持仓明细卖出
	$('.mingxi_sell_btn button').on('click', function(event) {
		var goods_id=$(this).attr('goods_id');
		var goods_name=$(this).parent().parent().children().eq(0).html();
		var count=$(this).parent().parent().children().eq(2).html();
		$('#serch_content').val(goods_name);
		//组装触发的表格
		con_str_body='';
		var con_str_head='<table>';
		con_str_body +='<tr class="new_tr" goods_id="'+goods_id+'"><td>T00'+goods_id+'</td><td class="tea_name">'+goods_name+'</td></tr>';
		con_str=con_str_head+con_str_body+'</tr></table>';  
		 $("#produce_con").html(con_str);
		 $("#transaction_nav").css('display','none');
		 $('.new_tr[goods_id="'+goods_id+'"]').click();
		 $('#sell_out').click();
		 $('#count_tran_in').val(count);

	});	

})

//持仓部分js代码
$('#agent_transaction').on('click',function(){
	$('.transaction_click').css('display','none');
	$('#agent').css('display','');
	$('.chicang li').attr('class','');
	$('.chicang li').eq(1).attr('class','center-jy');
});
$('#mingxi_transaction').on('click',function(){
	$('.transaction_click').css('display','none');
	$('#mingxi').css('display','');
	$('.chicang li').attr('class','');
	$('.chicang li').eq(0).attr('class','center-jy');
});
$('#today_transaction').on('click',function(){
	$('.transaction_click').css('display','none');
	$('#agent').css('display','');
	$('.chicang li').attr('class','');
	$('.chicang li').eq(2).attr('class','center-jy');
});
//委托交易
//1.取消交易
$('.agent_cancel button').on('click',function(){
	cur_button=$(this);
	transaction_sn=$(this).attr('transaction_id');
	//要取消交易的产品的数量，防止部分成交
	goods_name=$(this).parent().parent().children().eq(0).html();
	
	goods_count=$(this).parent().parent().children().eq(2).html();

	layer.confirm("确定要取消 ‘"+goods_name+"’?", {
            btn: ['确定','点错了'] //按钮
        }, function(){
        	$.ajax({
        		url: 'index.php?app=index&act=cancel_agent',
        		type: 'POST',
        		dataType: 'json',
        		data: {transaction_sn: transaction_sn,goods_count:goods_count},
        		success:function(out_data){
        			if (out_data.code!=0) {
        				layer.msg(out_data.message, {icon: 1});
        			}
        			//请求成功更改
        			cur_button.parent().parent().css('display','none');
        			layer.msg('该委托已经取消', {icon: 1});
        		}
        	})
        	.fail(function() {
        		console.log("error");
        		 layer.msg('取消失败', {icon: 2});
        	})
            
        }, function(){
            /*layer.msg('也可以这样', {
                time: 2000, //2s后自动关闭
                btn: ['明白了', '知道了']
            });*/
        });
})