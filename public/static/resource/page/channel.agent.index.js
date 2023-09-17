layui.use(["jquery","layer","table","form"],
    function(){
    var d=layui.jquery,
        a=layui.layer,
        c=layui.table,
        b=layui.form;
    layLoad("数据加载中...");
    c.render(
        {
            elem:"#table-block",url:data_url,loading:true,
            cols:[
                [
                    {title:"序号",type:"numbers"},
                    {field:"name",title:"代理名称",align:"center",minWidth:150},
                    {field:"login_name",title:"代理账号",align:"center",minWidth:100},
                    {field:"money",title:"账户余额",align:"center",minWidth:120},
                    {field:"ratio",title:"佣金比例",align:"center",minWidth:200},
                    {field:"total_charge",title:"累计充值",align:"center",minWidth:200},
                    {title:"代理状态",align:"center",minWidth:260,
                        templet:function(g){
                            var e=g.status_name+"&nbsp;";
                            var f=parseInt(g.status);
                            switch(f){
                                case 1:e+='<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="off">禁用</a>';break;
                                case 2:e+='<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="on">启用</a>';break
                            }return e
                        }
                    },
                    {title:"操作",align:"center",minWidth:320,
                        templet:function(f){
                            var e="";
                            var g=parseInt(f.id);
                            e+='<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="location">进入后台</a>';
                            e+='<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="resetpwd">重置登录密码</a>';
                            e+='<a class="layui-btn layui-btn-normal layui-btn-xs" href="'+f.do_url+'">编辑</a>';
                            e+='<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';
                            return e
                        }
                    }
                ]
            ],page:true,height:"full-220",
            response:{statusCode:1},id:"table-block",
            done:function(e){a.closeAll()}
        });
        c.on("tool(table-block)",
            function(g){
                var f=g.data;
                var e={id:f.id,event:g.event};
                var h="";
                if(e.event==="location"){
                    w(g.data.id)
                }else {
                    switch (g.event) {
                        case"on":
                            h = "确定要启用该代理吗？";
                            break;
                        case"off":
                            h = "确定要禁用该代理吗？";
                            break;
                        case"delete":
                            h = "确定要将该删除该代理吗？";
                            break;
                        case"resetpwd":
                            h = "确定要将该代理登录密码重置为123456吗？";
                            break
                    }
                    if (h) {
                        ajaxPost(do_url, e, h,
                            function () {
                                layOk("操作成功");
                                if (g.event !== "resetpwd") {
                                    setTimeout(function () {
                                        layLoad("数据加载中...");
                                        c.reload("table-block")
                                    }, 1400)
                                }
                            })
                    } else {
                        layError("该按钮未绑定事件")
                    }
                }
        });
        function w(g){
            var f={id:g};
            ajaxPost(
                $location_url,f,"",
                function(h){
                    window.open(h.url,"_blank")
                })
        }
        b.on("submit(table-search)",
            function(f){
                var e=f.field;layLoad("数据加载中...");
                c.reload("table-block",{where:e,page:{curr:1}})
        })
});