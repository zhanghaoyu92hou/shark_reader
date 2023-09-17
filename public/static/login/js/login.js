layui.use(['jquery','layer','form'],function(){
	var $ = layui.jquery,
	layer = layui.layer,
	form = layui.form,
	is_submit = true;
	form.on('submit(dologin)',function(obj){
		if(is_submit){
			is_submit = false;
			var data = obj.field;
			$.ajax({
				type : 'post',
				data : data,
				dataType : 'json',
				beforeSend : function(){
					$('.lowin-btn').text('登录中...');
				},
				success : function(res){
					if(res.code){
						window.location.href = res.data.url;
					}else{
						$('#cur_img').click();
						layer.msg(res.msg,{icon:2});
					}
				},
				complete : function(){
					is_submit = true;
					$('.lowin-btn').text('登录');
				},
				error : function(){
					is_submit = true;
					$('#cur_img').click();
					$('.lowin-btn').text('登录');
					layer.msg('网络繁忙，请稍候再试',{icon:2});
				}
			});
		}
	});
	document.onkeydown = function (e) {
	    var theEvent = window.event || e;
	    var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
	    if (code == 13) {
	        $('.login-btn').click();
	    }
	}
});