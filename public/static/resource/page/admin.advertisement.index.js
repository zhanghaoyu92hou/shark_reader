layui.use(["jquery", "layer", "table", "form"],
        function () {
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
                            title: "广告位名称",
                            minWidth: 260,
                            align: "center",
                            templet: function (h) {
                                var g = h.name
                                return g;
                            }
                        },

                        {
                            title: "广告数量",
                            align: "center",
                            minWidth: 120,
                            templet: function (h) {
                                var g = h.total_chapter;

                                g += '条&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" href=/admin/advertisement/details.html?id=' + h.id + '>查看</a>';
                                return g
                            }
                        },
                        {
                            title: "广告状态",
                            align: "center",
                            minWidth: 200,
                            templet: function (i) {
                                var g = i.status;
                                var h = parseInt(i.status);
                                switch (h) {
                                    case 1:
                                        g = '正常&nbsp;<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="off">禁用</a>';
                                        break;
                                    case 2:
                                        g = '禁用&nbsp;<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="on">启用</a>';
                                        break
                                }
                                return g
                            }
                        },
                        {
                            title: "操作",
                            align: "center",
                            minWidth: 400,
                            templet: function (i) {
                                var g = "";
                                g += '<a class="layui-btn layui-btn-normal layui-btn-xs" href=/admin/advertisement/adedit.html?id=' + i.id + '>编辑</a>';
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
                done: function (g) {
                    a.closeAll()
                }
            });
            c.on("tool(table-block)",
                    function (h) {
                    
                        var g = h.data;
                        switch (h.event) {
                            case "on":
                                f({
                                    id:
                                            g.id,
                                    event: h.event
                                },
                                        "确定要启用该广告吗？");
                                break;
                            case "off":
                                f({
                                    id:
                                            g.id,
                                    event: h.event
                                },
                                        "确定要禁用该广告吗？");
                                break;
                            case "delete":
                                f({
                                    id:
                                            g.id,
                                    event: h.event
                                },
                                        "删除后不可恢复，确定要将该删除该广告位吗？");
                                break;
                        }
                    });
            d("body").on("click", "#refresh",
                    function () {
                        ajaxPost(refresh_url, {},
                                "",
                                function () {
                                    layOk("更新成功")
                                })
                    });
            function f(g, h) {
//                alert(do_url); return false;
                ajaxPost(do_url, g, h,
                        function () {
                            layOk("操作成功");
                            setTimeout(function () {
                                layLoad("数据加载中...");
                                c.reload("table-block")
                            },
                                    1400)
                        })
            }
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
                    yes: function (k, i) {
                        var j = d(i).find("iframe")[0].contentWindow,
                                h = "pageSubmit",
                                l = i.find("iframe").contents().find("#" + h);
                        j.layui.form.on("submit(" + h + ")",
                                function (n) {
                                    var m = n.field;
                                    ajaxPost(g, m, "确定要生成推广链接吗？",
                                            function (o) {
                                                layOk("生成成功");
                                                a.close(k);
                                                setTimeout(function () {
                                                    window.location.href = spread_url
                                                },
                                                        1000)
                                            })
                                });
                        l.trigger("click")
                    }
                })
            }
            b.on("submit(table-search)",
                    function (h) {
                        var g = h.field;
                        layLoad("数据加载中...");
                        c.reload("table-block", {
                            where: g,
                            page: {
                                curr: 1
                            }
                        })
                    })
        });