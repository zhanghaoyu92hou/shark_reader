/**
 * 
 */
layui.use(['jquery','layer'],function(){
	var $ = layui.jquery,
	layer = layui.layer;
	
	$('body').on('keyup','.input-title',function(){
		var val = $(this).val();
		$(this).parents('.custom-msg-box').find('.custom-msg-title').text(val);
	});
	
	$('body').on('keyup','.input-summary',function(){
		var val = $(this).val();
		$(this).parents('.custom-msg-box').find('.custom-msg-summary').text(val);
	});
	
	$('body').click(function(){
		$('.custom-msg-input-box').addClass('layui-hide');
	});
	
	$('body').on('click','.custom-msg-box',function(e){
		e.stopPropagation();
		$('.custom-msg-input-box').removeClass('layui-hide');
	});
	
	$('body').on('click','.doUpload',function(e){
		e.stopPropagation();
		var corp_size = $(this).attr('data-size');
		cropImageCallback('上传客服消息封面图片',corp_size,function(cover){
			$('.custom-msg-box .custom-msg-cover img').attr('src',cover);
			$('.custom-msg-box .hide-cover').val(cover);
		});
	});
	
	$('body').on('click','.chooseCover',function(e){
		e.stopPropagation();
		showCover();
	});

	$('body').on('click','.chooseTitle',function(e){
		e.stopPropagation();
		showTitle();
	});
	
	$('body').on('click','.layCover',function(e){
		e.stopPropagation();
		var cover = $(this).attr('src');
		$('.custom-msg-box .custom-msg-cover img').attr('src',cover);
		$('.custom-msg-box .hide-cover').val(cover);
		layer.closeAll();
	});
	
	$('body').on('click','.layTitle',function(e){
		e.stopPropagation();
		var title = $(this).text();
		$('.custom-msg-box .custom-msg-title').text(title);
		$('.custom-msg-box .input-title').val(title);
		layer.closeAll();
	});
	

	function getRandomCover(){
		var num = (getRandomNum(1,$material_num))-1;
		return $('.layCover:eq('+num+')').attr('src');
	}
	function getRandomTitle(){
		var num = (getRandomNum(1,$material_num))-1;
		return $('.layTitle:eq('+num+')').text();
		
	}
	
	function showTitle(){
		layer.open({
			  type: 1,
			  title: '选择消息标题',
			  closeBtn: 1,
			  shade: [0],
			  area: ['670px','600px'],
			  offset : '100px',
			  time: 0,
			  anim: 2,
			  scrollbar :false,
			  content: $('.titleLayer').html()
		});
	}
	
	function showCover(){
		layer.open({
			  type: 1,
			  title: '选择图片素材',
			  closeBtn: 1,
			  shade: [0],
			  area: ['670px','600px'],
			  offset : '100px',
			  time: 0,
			  anim: 2,
			  scrollbar :false,
			  content: $('.coverLayer').html()
		});
	}
});