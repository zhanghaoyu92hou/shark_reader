layui.use(["jquery","layer","table","form"],function(){var d=layui.jquery,a=layui.layer,c=layui.table,b=layui.form;layLoad("数据加载中...");c.render({elem:"#table-block",url:data_url,loading:true,cols:[[{title:"封面",minWidth:100,align:"center",templet:function(f){var e='<font class="text-red">暂无封面</font>';if(f.cover){e='<img src="'+f.cover+'" style="width:50px;" />'}return e}},{title:"视频标题",align:"center",minWidth:200,templet:function(f){var e=f.name+"&nbsp;";e+='<a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="copy" title="点击复制链接"><i class="layui-icon layui-icon-link"></i></a>';return e}},{field:"free_str",title:"是否收费",align:"center",minWidth:120},{field:"read_num",title:"播放次数",align:"center",minWidth:120},{title:"操作",align:"center",minWidth:400,templet:function(f){var e="";e+='<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="play">播放</a>';e+='<a class="layui-btn layui-btn-normal layui-btn-xs" href="'+f.customer_url+'">客服消息</a>';return e}}]],page:true,height:"full-220",response:{statusCode:1},id:"table-block",done:function(e){a.closeAll()}});c.on("tool(table-block)",function(f){var e=f.data;switch(f.event){case"copy":layPage("复制视频链接【"+e.name+"】",e.copy_url,"800px","500px");break;case"play":layPage("播放【"+e.name+"】",e.play_url,"800px","500px");break}});b.on("submit(table-search)",function(f){var e=f.field;layLoad("数据加载中...");c.reload("table-block",{where:e,page:{curr:1}})})});