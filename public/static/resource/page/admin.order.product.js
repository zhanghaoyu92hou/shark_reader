layui.use(["jquery","layer","table","form","laydate"],function(){var e=layui.jquery,b=layui.layer,d=layui.table,c=layui.form,a=layui.laydate;layLoad("数据加载中...");d.render({elem:"#table-block",url:data_url,loading:true,cols:[[{field:"order_no",title:"订单号",align:"center",minWidth:200},{field:"pname",title:"商品名称",align:"center",minWidth:160},{field:"channel_name",title:"所属代理",align:"center",minWidth:100},{field:"nickname",title:"用户昵称",align:"center",minWidth:100},{title:"收货信息",align:"center",minWidth:200,templet:function(f){return f.username+"/"+f.phone+"<br />"+f.address}},{field:"count",title:"商品数量",align:"center",minWidth:100},{field:"money",title:"订单金额",align:"center",minWidth:100},{field:"status_name",title:"状态",align:"center",minWidth:100},{field:"create_time",title:"下单时间",align:"center",minWidth:100},{title:"操作",align:"center",minWidth:100,templet:function(h){var f=parseInt(h.status);var g="";if(f===2){g+='<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="send">发货</a>'}else{g+="--"}return g}}]],page:true,height:"full-220",response:{statusCode:1},id:"table-block",done:function(f){b.closeAll()}});a.render({elem:"#between_time",type:"datetime",range:"~"});d.on("tool(table-block)",function(g){var f=g.data;if(g.event==="send"){ajaxPost(do_url,{id:f.id},"确定要发货吗？",function(){layOk("操作成功");setTimeout(function(){layLoad("数据加载中...");d.reload("table-block")},1400)})}});c.on("submit(table-search)",function(g){var f=g.field;layLoad("数据加载中...");d.reload("table-block",{where:f,page:{curr:1}})})});