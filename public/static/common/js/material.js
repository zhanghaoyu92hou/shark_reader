/**
 * 
 */
layui.use(['jquery','layer'],function(){
	var $ = layui.jquery,
	layer = layui.layer;
	var len = $('.message-item').length;
	len = len ? (len-1) : 0;
	$('#addChild').click(function(){
		len ++;
		var title = getRandomTitle();
		var cover = getRandomCover();
		var html = '';
		html += '<div class="message-child message-item">';
		html += '<a class="layui-btn layui-btn-sm layui-btn-danger img-delete layui-hide" href="javascript:void(0);"><i class="layui-icon layui-icon-delete"></i>删除</a>';
		html += '<div class="child-main message-title">'+title+'</div>';
		html += '<div class="child-image">';
		html += '<div class="pimg">';
		html += '<img class="message-cover" src="'+cover+'" />';
		html += '</div>';
		html += '</div>';
		html += '<div class="message-edit-box child-edit-box layui-hide">';
		html += '<span class="triangle"></span>';
		html += '<div class="layui-fluid">';
		html += '<div class="layui-card">';
		html += '<div class="layui-card-body">';
		html += '<div class="layui-form-item">';
		html += '<label class="layui-form-label">封面：</label>';
		html += '<div class="layui-input-block mg80">';
		html += '<a href="javascript:void(0);" class="layui-btn layui-btn-sm layui-btn-primary chooseCover">选择素材</a>';
		html += '<a href="javascript:void(0);" class="layui-btn layui-btn-sm layui-btn-primary doUpload" data-size="200x200">重新上传</a>';
		html += '<input type="hidden" name="message_cover['+len+']" value="'+cover+'" />';
		html += '</div>';
		html += '</div>';
		html += '<div class="layui-form-item">';
		html += '<label class="layui-form-label">标题：</label>';
		html += '<div class="layui-input-block mg80 message-title-block">';
		html += '<input type="text" name="message_title['+len+']" value="'+title+'" class="layui-input input-title"/>';
		html += '<a class="layui-btn layui-btn-sm layui-btn-primary chooseTitle">选择</a>';
		html += '</div>';
		html += '</div>';
		html += '<div class="layui-form-item">';
		html += '<label class="layui-form-label">链接：</label>';
		html += '<div class="layui-input-block mg80">';
		html += '<input type="text" name="message_link['+len+']" class="layui-input" />';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		$('.message-child-box').append(html);
	});
	
	$('body').on('keyup','.input-title',function(){
		var val = $(this).val();
		$(this).parents('.message-item').find('.message-title').text(val);
	});
	
	$('body').click(function(){
		$('.message-edit-box').addClass('layui-hide');
	});
	
	$('body').on('click','.message-item',function(e){
		e.stopPropagation();
		$('.message-edit-box').addClass('layui-hide');
		$(this).find('.message-edit-box').removeClass('layui-hide');
	});
	
	$('body').on('click','.img-delete',function(e){
		e.stopPropagation();
		$(this).parents('.message-child').remove();
	});
	
	$('body').on('mouseover','.message-child',function(){
		$(this).find('.img-delete').removeClass('layui-hide');
	});
	
	$('body').on('mouseout','.message-child',function(){
		$(this).find('.img-delete').addClass('layui-hide');
	});
	
	$('body').on('click','.chooseCover',function(e){
		e.stopPropagation();
		$('.message-cover').removeClass('current-cover');
		$(this).parents('.message-item').find('.message-cover').addClass('current-cover');
		showCover();
	});
	
	$('body').on('click','.doUpload',function(e){
		e.stopPropagation();
		$('.message-cover').removeClass('current-cover');
		$(this).parents('.message-item').find('.message-cover').addClass('current-cover');
		var corp_size = $(this).attr('data-size');
		cropImageCallback('上传客服消息封面图片',corp_size,function(cover){
			$('.current-cover').attr('src',cover);
			$('.current-cover').parents('.message-item').find('.hide-cover').val(cover);
		});
	});

	$('body').on('click','.chooseTitle',function(e){
		e.stopPropagation();
		$('.message-title').removeClass('current-title');
		$(this).parents('.message-item').find('.message-title').addClass('current-title');
		showTitle();
	});
	
	$('body').on('click','.layCover',function(e){
		e.stopPropagation();
		var cover = $(this).attr('src');
		$('.current-cover').attr('src',cover);
		$('.current-cover').parents('.message-item').find('.hide-cover').val(cover);
		layer.closeAll();
	});
	
	$('body').on('click','.layTitle',function(e){
		e.stopPropagation();
		var title = $(this).text();
		$('.current-title').text(title);
		$('.current-title').parents('.message-item').find('.message-title-block>input').val(title);
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