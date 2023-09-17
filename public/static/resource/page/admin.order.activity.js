layui.use(["jquery", "layer", "table", "form", "laydate"],
function() {
    var e = layui.jquery,
    b = layui.layer,
    d = layui.table,
    c = layui.form,
    a = layui.laydate;
    layLoad("数据加载中...");
    d.render({
        elem: "#table-block",
        url: data_url,
        loading: true,
        cols: [[{
            field: "order_no",
            title: "订单号",
            align: "center",
            minWidth: 200
        },
        {
            title: "是否扣量",
            align: "center",
            minWidth: 80,
            templet: function(i) {
                var h = parseInt(i.is_count);
                var g = parseInt(i.channel_id);
                var f = "否";
                if (h == 2 && g > 0) {
                    f = '<a class="layui-btn layui-btn-danger layui-btn-xs">是</a>'
                }
                return f
            }
        },
        {
            field: "channel_name",
            title: "所属渠道",
            align: "center",
            minWidth: 100
        },
        {
            field: "agent_name",
            title: "所属代理",
            align: "center",
            minWidth: 100
        },
        {
            field: "user_info",
            title: "用户昵称",
            align: "center",
            minWidth: 100
        },
        {
            field: "money",
            title: "充值金额",
            align: "center",
            minWidth: 100
        },
        {
            field: "relation_name",
            title: "活动名称",
            align: "center",
            minWidth: 160
        },
        {
            field: "status_name",
            title: "状态",
            align: "center",
            minWidth: 100
        },
        {
            field: "create_time",
            title: "下单时间",
            align: "center",
            minWidth: 100
        }]],
        page: true,
        height: "full-220",
        response: {
            statusCode: 1
        },
        id: "table-block",
        done: function(f) {
            b.closeAll()
        }
    });
    a.render({
        elem: "#between_time",
        type: "datetime",
        range: "~"
    });
    c.on("submit(table-search)",
    function(g) {
        var f = g.field;
        layLoad("数据加载中...");
        d.reload("table-block", {
            where: f,
            page: {
                curr: 1
            }
        })
    })
});