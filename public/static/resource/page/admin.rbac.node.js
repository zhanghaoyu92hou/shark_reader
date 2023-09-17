layui.config({base:"/static/layadmin/lay_extend/tree/"}).use(["jquery","layer","table","treetable"],function(){var e=layui.jquery,a=layui.layer,c=layui.table,b=layui.treetable;function d(){layLoad("数据加载中...");b.render({treeColIndex:0,treeSpid:"0",treeIdName:"id",treePidName:"pid",treeDefaultClose:true,treeLinkage:false,elem:"#table-block",url:window.location.href,page:false,height:"full-220",id:"table-block",cols:[[{field:"name",minWidth:300,title:"节点名称"},{field:"menu_name",minWidth:100,title:"节点类型",align:"center"},{field:"url",minWidth:100,title:"链接"},{title:"排序",minWidth:140,align:"center",templet:function(h){var f=parseInt(h.is_menu);var g="";if(f===1){g+='<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="sortUp">向上排序</a>';g+='<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="sortDown">向下排序</a>'}return g}},{title:"操作",minWidth:200,align:"center",templet:function(h){var g="";var f=parseInt(h.is_menu);if(f===1){g+='<a class="layui-btn layui-btn-normal layui-btn-xs" href="'+h.add_url+'">新增子节点</a>'}g+='<a class="layui-btn layui-btn-normal layui-btn-xs" href="'+h.do_url+'">编辑</a>';g+='<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';return g}}]],done:function(f){a.closeAll()}})}d();c.on("tool(table-block)",function(h){var g=h.data;var f={id:g.id,event:h.event};if(f.event==="delete"){ajaxPost($opt_url,f,"删除节点会将所有子节点一并删除，确认要继续吗？",function(){layOk("删除成功");setTimeout(function(){layLoad("数据加载中...");d()},1300)})}else{if(f.event==="sortUp"||f.event==="sortDown"){ajaxPost($opt_url,f,"",function(){layLoad("数据加载中...");d()})}else{layError("该按钮未绑定事件")}}});e("#openAll").click(function(){b.expandAll("#table-block")});e("#hideAll").click(function(){b.foldAll("#table-block")})});