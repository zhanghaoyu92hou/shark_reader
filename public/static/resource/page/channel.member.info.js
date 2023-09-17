layui.use(["table","element"],function(){var b=layui.table,a=layui.element;b.render({elem:"#charge-table",url:chargeUrl,loading:true,cols:[[{field:"order_no",title:"订单号",align:"center",minWidth:200},{field:"money",title:"充值金额",align:"center",minWidth:100},{field:"from_name",title:"订单来源",align:"center",minWidth:160},{field:"status_name",title:"状态",align:"center",minWidth:100},{field:"create_time",title:"下单时间",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#activity-table",url:activityUrl,loading:true,cols:[[{field:"order_no",title:"订单号",align:"center",minWidth:200},{field:"money",title:"充值金额",align:"center",minWidth:100},{field:"relation_name",title:"来源活动",align:"center",minWidth:160},{field:"status_name",title:"状态",align:"center",minWidth:100},{field:"create_time",title:"下单时间",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#reward-table",url:rewardUrl,loading:true,cols:[[{field:"order_no",title:"订单号",align:"center",minWidth:200},{field:"money",title:"充值金额",align:"center",minWidth:100},{field:"from_name",title:"订单来源",align:"center",minWidth:160},{field:"status_name",title:"状态",align:"center",minWidth:100},{field:"create_time",title:"下单时间",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#sign-table",url:signUrl,loading:true,cols:[[{title:"序号",type:"numbers"},{field:"create_time",title:"签到时间",align:"center",minWidth:200},{field:"money",title:"获得书币",align:"center",minWidth:160}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#consume-table",url:consumeUrl,loading:true,cols:[[{title:"序号",type:"numbers"},{field:"money",title:"消费书币",align:"center",minWidth:100},{field:"summary",title:"描述",align:"center",minWidth:160},{field:"create_time",title:"消费时间",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}})});