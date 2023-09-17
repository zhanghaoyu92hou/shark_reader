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
            title: "封面",
            minWidth: 100,
            align: "center",
            templet: function(g) {
                var f = '<font class="text-red">暂无封面</font>';
                if (g.cover) {
                    f = '<img src="' + g.cover + '" style="width:50px;" />'
                }
                return f
            }
        },
        {
            title: "视频标题",
            align: "center",
            minWidth: 200,
            templet: function(g) {
                var f = g.name + "&nbsp;";
                f += '<a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="copy" title="点击复制链接"><i class="layui-icon layui-icon-link"></i></a>';
                return f
            }
        },
        {
            field: "free_str",
            title: "是否收费",
            align: "center",
            minWidth: 120
        },
        {
            field: "read_num",
            title: "播放次数",
            align: "center",
            minWidth: 120
        },
        {
            title: "状态",
            align: "center",
            minWidth: 200,
            templet: function(h) {
                var f = h.status_name;
                var g = parseInt(h.status);
                switch (g) {
                case 1:
                    f += '&nbsp;<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="off">下架</a>';
                    break;
                case 2:
                    f += '&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="on">上架</a>';
                    break
                }
                return f
            }
        },
        {
            title: "操作",
            align: "center",
            minWidth: 400,
            templet: function(g) {
                var f = "";
                f += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="play">播放</a>';
                f += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + g.customer_url + '">客服消息</a>';
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
    d("body").on("click", "#refresh",
    function() {
        ajaxPost(refresh_url, {},
        "",
        function() {
            layOk("更新成功")
        })
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
            "确定要上架该视频吗？");
            break;
        case "off":
            e({
                id:
                f.id,
                event: g.event
            },
            "确定要下架该视频吗？");
            break;
        case "delete":
            e({
                id:
                f.id,
                event: g.event
            },
            "删除后不可恢复，确定要将该删除该视频吗？");
            break;
        case "copy":
            layPage("复制视频链接【" + f.name + "】", f.copy_url, "800px", "500px");
            break;
        case "play":
            layPage("播放【" + f.name + "】", f.play_url, "800px", "500px");
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