layui.use(["table","element"],function(){var b=layui.table,a=layui.element;b.render({elem:"#cartoon-table",url:$dataUrl+"?type=cartoon",loading:true,cols:[[{type:"numbers",title:"序号"},{field:"name",title:"书名",align:"center",minWidth:100},{field:"spread_num",title:"推广次数",align:"center",minWidth:100},{field:"today",title:"今日充值",align:"center",minWidth:160},{field:"yesterday",title:"昨日充值",align:"center",minWidth:100},{field:"money",title:"总充值",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#novel-table",url:$dataUrl+"?type=novel",loading:true,cols:[[{type:"numbers",title:"序号"},{field:"name",title:"书名",align:"center",minWidth:100},{field:"spread_num",title:"推广次数",align:"center",minWidth:100},{field:"today",title:"今日充值",align:"center",minWidth:160},{field:"yesterday",title:"昨日充值",align:"center",minWidth:100},{field:"money",title:"总充值",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#music-table",url:$dataUrl+"?type=music",loading:true,cols:[[{type:"numbers",title:"序号"},{field:"name",title:"书名",align:"center",minWidth:100},{field:"today",title:"今日充值",align:"center",minWidth:160},{field:"yesterday",title:"昨日充值",align:"center",minWidth:100},{field:"money",title:"总充值",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}});b.render({elem:"#video-table",url:$dataUrl+"?type=video",loading:true,cols:[[{type:"numbers",title:"序号"},{field:"name",title:"书名",align:"center",minWidth:100},{field:"today",title:"今日充值",align:"center",minWidth:160},{field:"yesterday",title:"昨日充值",align:"center",minWidth:100},{field:"money",title:"总充值",align:"center",minWidth:200}]],page:true,height:"full",response:{statusCode:1}})});