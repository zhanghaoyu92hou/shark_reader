layui.use(["jquery","layer","table","form"],function(){var d=layui.jquery,a=layui.layer,c=layui.table,b=layui.form;layLoad("数据加载中...");c.render({elem:"#table-block",url:data_url,loading:true,cols:[[{title:"序号",type:"numbers"},{field:"id",title:"粉丝ID",align:"center",minWidth:120},{field:"agent_name",title:"所属代理",align:"center",minWidth:120},{field:"nickname",title:"粉丝昵称",align:"center",minWidth:120},{field:"phone",title:"绑定手机号",align:"center",minWidth:160},{field:"money",title:"书币余额",align:"center",minWidth:130},{field:"is_subscribe",title:"是否关注",align:"center",minWidth:100},{field:"vip_str",title:"VIP",align:"center",minWidth:260},{field:"create_time",title:"注册时间",align:"center",minWidth:200},{field:"status_name",title:"用户状态",align:"center",minWidth:100},{title:"更多",align:"center",minWidth:120,templet:function(f){var e="";e+='<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info">查看详情</a>';return e}}]],page:true,height:"full-220",response:{statusCode:1},id:"table-block",done:function(e){a.closeAll()}});c.on("tool(table-block)",function(f){if(f.event==="info"){var e=f.data;layPage("用户详情",e.info_url,"90%","80%")}});b.on("submit(table-search)",function(f){var e=f.field;layLoad("数据加载中...");c.reload("table-block",{where:e,page:{curr:1}})})});