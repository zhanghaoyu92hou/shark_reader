layui.use(["jquery","layer","table","form"],function(){var d=layui.jquery,a=layui.layer,c=layui.table,b=layui.form;layLoad("数据加载中...");c.render({elem:"#table-block",url:data_url,loading:true,cols:[[{field:"pname",title:"评论对象",align:"center",minWidth:160},{field:"content",title:"评论内容",align:"center",minWidth:300},{field:"nickname",title:"评论人",align:"center",minWidth:120},{title:"状态",align:"center",minWidth:160,templet:function(h){var f=h.status_name;var g=parseInt(h.status);switch(g){case 1:f+='&nbsp;<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="off">隐藏</a>';break;case 2:f+='&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="on">显示</a>';break}return f}},{title:"操作",align:"center",minWidth:120,templet:function(f){return'<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>'}}]],page:true,height:"full-220",response:{statusCode:1},id:"table-block",done:function(f){a.closeAll()}});c.on("tool(table-block)",function(g){var f=g.data;switch(g.event){case"on":e({id:f.id,event:g.event},"确定要显示该评论吗？");break;case"off":e({id:f.id,event:g.event},"确定要隐藏该评论吗？");break;case"delete":e({id:f.id,event:g.event},"确定要将该删除该评论吗？");break}});function e(f,g){ajaxPost(do_url,f,g,function(){layOk("操作成功");setTimeout(function(){layLoad("数据加载中...");c.reload("table-block")},1400)})}b.on("submit(table-search)",function(g){var f=g.field;layLoad("数据加载中...");c.reload("table-block",{where:f,page:{curr:1}})})});