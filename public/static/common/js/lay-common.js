/**
 * 
 */
function ajaxPost(url,data,asked,callback){
	layui.use(['jquery','layer'],function(){
		var $ = layui.jquery,
		layer = layui.layer;
		var load;
		if(asked){
			layer.confirm(asked,{icon:3,offset:'200px'},function(index){
		      	layer.close(index);
		      	$.ajax({
		      		type : 'post',
		      		url : url,
		      		data : data,
		      		dataType : 'json',
		      		beforeSend : function(){
		      			load = layer.load(2,{offset:'300px'});
		      		},
		      		success : function(res){
		      			layer.close(load);
		      			if(res.msg === 'ok'){
		      				callback(res.data);
		      			}else{
		      				layError(res.msg);
		      			}
		      		},
		      		error : function(){
		      			layer.close(load);
		      			layError('网络繁忙，请稍候再试');
		      		}
		      	});
		    });
		}else{
			$.ajax({
	      		type : 'post',
	      		url : url,
	      		data : data,
	      		dataType : 'json',
	      		beforeSend : function(){
	      			load = layer.load(2,{offset:'300px'});
	      		},
	      		success : function(res){
	      			layer.close(load);
	      			if(res.msg === 'ok'){
	      				callback(res.data);
	      			}else{
	      				layError(res.msg);
	      			}
	      		},
	      		error : function(){
	      			layer.close(load);
	      			layError('网络繁忙，请稍候再试');
	      		}
	      	});
		}
	});
}



function layOk(msg){
	layui.use('layer',function(){
		layui.layer.msg(msg,{icon:1,offset:'200px',time:1000});
	});
}

function layError(msg){
	layui.use('layer',function(){
		layui.layer.msg(msg,{icon:2,offset:'200px',time:1000});
	});
}

function layLoad(text){
	layui.use('layer',function(){
		if(text){
			layer.load(2,{
		    	shade : [0.7,'#fff'],
		    	offset : '300px',
		    	content : text,
		    	success: function (layero) {
		            layero.find('.layui-layer-content').css({
		                'padding-left': '39px',
		                'width': '100px',
		                'line-height':'32px'
		            });
		    	}
		    });
		}else{
			layui.layer.load(2,{offset:'300px'});
		}
		
	});
}

function layPage(title,url,width,height){
	layui.use('layer',function(){
		layui.layer.open({
			  type: 2,
			  title: title,
			  closeBtn: 1,
			  shade: [0],
			  area: [width,height],
			  offset : '100px',
			  time: 0,
			  anim: 2,
			  scrollbar :false,
			  content: [url,'yes']
		});
	});
}

//上传裁剪图片
function cropImage(title,crop_size,elem){
	var url = createUpCropUrl(crop_size);
	if(url){
		layui.use('jquery',function(){
			var $ = layui.jquery;
			var obj = $(elem);
			window.top.layer.open({
				  type: 2,
				  title: title,
				  closeBtn: 1,
				  shade: [0],
				  area: ['800px','600px'],
				  offset : '100px',
				  time: 0,
				  anim: 2,
				  scrollbar :false,
				  content: [url,'yes'],
				  btn : ['确定','取消'],
				  yes : function(index,layero){
					  var childWindow = $(layero).find('iframe')[0].contentWindow;
					  childWindow.uploadImage(function(url){
						  window.top.layer.close(index);
						  obj.find('.showimg').attr('src',url);
						  obj.find('.hideval').val(url);
					  });
				  }
			});
		});
	}else{
		layError('构建裁剪上传失败');
	}
}
//上传裁剪图片并回调
function cropImageCallback(title,crop_size,callback){
	var url = createUpCropUrl(crop_size);
	if(url){
		layui.use('jquery',function(){
			var $ = layui.jquery;
			window.top.layer.open({
				type: 2,
				title: title,
				closeBtn: 1,
				shade: [0],
				area: ['800px','600px'],
				offset : '100px',
				time: 0,
				anim: 2,
				scrollbar :false,
				content: [url,'yes'],
				btn : ['确定','取消'],
				yes : function(index,layero){
					var childWindow = $(layero).find('iframe')[0].contentWindow;
					childWindow.uploadImage(function(url){
						window.top.layer.close(index);
						callback(url);
					});
				}
			});
		});
	}else{
		layError('构建裁剪上传失败');
	}
}

//获取随机数
function getRandomNum(minNum,maxNum){ 
    switch(arguments.length){ 
        case 1: 
            return parseInt(Math.random()*minNum+1,10); 
        break; 
        case 2: 
            return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10); 
        break; 
        default: 
            return 0; 
        break; 
    } 
}

//创建裁剪图片链接
function createUpCropUrl(crop_size){
	var selfUrl = window.location.href;
	var tmp = selfUrl.split('//');
	var urlPath = tmp[1];
	var tmps = urlPath.split('/');
	var module = tmps[1];
	if(module){
		return '/'+module+'/Upload/crop.html?crop_size='+crop_size;
	}else{
		return false;
	}
}
