layui.use(["jquery", "layer", "table", "form"],
function() {
    var d = layui.jquery,
    a = layui.layer,
    c = layui.table,
    b = layui.form;
    layLoad();
    c.render({
        elem: "#table-block",
        url: data_url,
        loading: true,
        cols: [[{
            title: "封面",
            minWidth: 100,
            align: "center",
            templet: function(h) {
                var g = '<font class="text-red">暂无封面</font>';
                if (h.cover) {
                    g = '<img src="' + h.cover + '" style="width:50px;" />'
                }
                return g
            }
        },
        {
            title: "漫画名称",
            align: "center",
            minWidth: 200,
            templet: function(h) {
                var g = h.name + "&nbsp;";
                g += '<a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="copy" title="点击复制链接"><i class="layui-icon layui-icon-link"></i></a>';
                return g
            }
        },
        {
            field: "author",
            title: "作者",
            align: "center",
            minWidth: 120
        },
        {
            title: "总章节",
            align: "center",
            minWidth: 120,
            templet: function(h) {
                var g = h.total_chapter;
                g += '&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + h.chapter_url + '">查看</a>';
                return g
            }
        },
        {
            field: "over_type",
            title: "进度",
            align: "center",
            minWidth: 100
        },
        {
            field: "free_type",
            title: "属性",
            align: "center",
            minWidth: 100
        },
/*        {
            field: "long_type",
            title: "篇幅",
            align: "center",
            minWidth: 100
        },*/
        {
            field: "is_hot",
            title: "推荐",
            align: "center",
            minWidth: 100
        },
        {
            title: "小说状态",
            align: "center",
            minWidth: 200,
            templet: function(i) {
                var g = i.status_name;
                var h = parseInt(i.status);
                switch (h) {
                case 1:
                    g += '&nbsp;<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="off">下架</a>';
                    break;
                case 2:
                    g += '&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="on">上架</a>';
                    break
                }
                return g
            }
        },
        {
            title: "操作",
            align: "center",
            minWidth: 400,
            templet: function(i) {
                var g = "";
                var h = parseInt(i.total_chapter);
                if (h > 0) {
                    g += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + i.guide_url + '" target="_blank">生成文案</a>';
                    g += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="spread">推广链接</a>';
                    g += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + i.customer_url + '">客服消息</a>'
                }
                g += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + i.share_url + '">分享话术</a>';
                g += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="' + i.do_url + '">编辑</a>';
                g += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';
                return g
            }
        }]],
        page: true,
        height: "full-220",
        response: {
            statusCode: 1
        },
        id: "table-block",
        done: function(g) {
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
    function(i) {
        var h = i.data;
        var g = {
            id: h.id,
            event: i.event
        };
        var j = "";
        switch (i.event) {
        case "on":
            f(g, "确定要上架该漫画吗？");
            break;
        case "off":
            f(g, "确定要下架该漫画吗？");
            break;
        case "delete":
            f(g, "删除后不可恢复，确定要将该删除该漫画吗？");
            break;
        case "spread":
            e(h.spread_url);
            break;
        case "copy":
            layPage("复制小说链接【" + h.name + "】", h.copy_url, "800px", "500px");
            break
        }
    });
    function e(g) {
        a.open({
            type: 2,
            title: "生成推广链接",
            closeBtn: 1,
            shade: [0],
            area: ["800px", "600px"],
            offset: "100px",
            time: 0,
            anim: 2,
            scrollbar: false,
            content: [g, "yes"],
            btn: ["生成推广链接"],
            yes: function(k, i) {
                var j = d(i).find("iframe")[0].contentWindow,
                h = "pageSubmit",
                l = i.find("iframe").contents().find("#" + h);
                j.layui.form.on("submit(" + h + ")",
                function(n) {
                    var m = n.field;
                    ajaxPost(g, m, "确定要生成推广链接吗？",
                    function(o) {
                        layOk("生成成功");
                        a.close(k);
                        setTimeout(function() {
                            window.location.href = spread_url
                        },
                        1000)
                    })
                });
                l.trigger("click")
            }
        })
    }
    function f(g, h) {
        ajaxPost(do_url, g, h,
        function() {
            layOk("操作成功");
            setTimeout(function() {
                layLoad();
                c.reload("table-block")
            },
            1400)
        })
    }
    b.on("submit(table-search)",
    function(h) {
        var g = h.field;
        layLoad();
        c.reload("table-block", {
            where: g,
            page: {
                curr: 1
            }
        })
    })
});