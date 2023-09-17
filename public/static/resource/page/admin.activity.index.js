layui.use(["jquery", "layer", "table", "form"],
function() {
    var d = layui.jquery,
    a = layui.layer,
    c = layui.table,
    b = layui.form;
    layLoad("数据加载中...");
    c.render({
        elem: "#table-block",
        url: data_url,
        loading: true,
        cols: [[{
            title: "活动标题",
            align: "center",
            minWidth: 160,
            templet: function(g) {
                var f = g.name + "&nbsp;";
                f += '<a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="copy" title="点击复制链接"><i class="layui-icon layui-icon-link"></i></a>';
                return f
            }
        },
        {
            field: "content",
            title: "活动内容",
            align: "center",
            minWidth: 200
        },
        {
            field: "between_time",
            title: "活动时间",
            align: "center",
            minWidth: 300
        },
        {
            field: "charge_total",
            title: "累计充值",
            align: "center",
            minWidth: 100
        },
        {
            field: "charge_nums",
            title: "充值笔数",
            align: "center",
            minWidth: 100
        },
        {
            field: "first_str",
            title: "充值限制",
            align: "center",
            minWidth: 100
        },
        {
            title: "状态",
            align: "center",
            minWidth: 160,
            templet: function(h) {
                var f = h.status_name;
                var g = parseInt(h.status);
                switch (g) {
                case 1:
                    f += '&nbsp;<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="off">禁用</a>';
                    break;
                case 2:
                    f += '&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="on">启用</a>';
                    break
                }
                return f
            }
        },
        {
            title: "操作",
            align: "center",
            minWidth: 300,
            templet: function(g) {
                var f = "";
                if (g.customer_url) {
                    f += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + g.customer_url + '">客服消息</a>'
                }
                f += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + g.do_url + '">编辑</a>';
                f += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';
                return f
            }
        }]],
        page: true,
        height: "full-220",
        response: {
            statusCode: 1
        },
        id: "table-block",
        done: function(f) {
            a.closeAll()
        }
    });
    c.on("tool(table-block)",
    function(g) {
        var f = g.data;
        switch (g.event) {
        case "on":
            e({
                id:
                f.id,
                event: g.event
            },
            "确定要启用该活动吗？");
            break;
        case "off":
            e({
                id:
                f.id,
                event: g.event
            },
            "确定要禁用该活动吗？");
            break;
        case "delete":
            e({
                id:
                f.id,
                event: g.event
            },
            "确定要将该删除该活动吗？");
            break;
        case "copy":
            layPage("复制活动链接【" + f.name + "】", f.copy_url, "800px", "500px");
            break
        }
    });
    function e(f, g) {
        ajaxPost(do_url, f, g,
        function() {
            layOk("操作成功");
            setTimeout(function() {
                layLoad("数据加载中...");
                c.reload("table-block")
            },
            1400)
        })
    }
    b.on("submit(table-search)",
    function(g) {
        var f = g.field;
        layLoad("数据加载中...");
        c.reload("table-block", {
            where: f,
            page: {
                curr: 1
            }
        })
    })
});