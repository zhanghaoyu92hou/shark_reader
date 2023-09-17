layui.use(["jquery", "layer", "table", "form", "upload"],
        function () {
            var $ = layui.jquery,
                    layer = layui.layer,
                    table = layui.table,
                    form = layui.form,
                    upload = layui.upload;

            layLoad();
            table.render({
                elem: "#table-block",
                url: data_url,
                loading: true,
                cols: [[{
                            title: "序号",
                            type: "numbers",
                        },
                        {
                            title: "广告图",
                            align: "center",
                            minWidth: 100,
                            templet: function (d) {
                                var g = '<font class="text-red">暂无封面</font>';
                                if (d.img) {
                                    g = '<img src="' + d.img + '" style="width:50px;" />'
                                }
                                return g
                            }
                        },
                        {
                            field: "url",
                            title: "广告链接",
                            align: "center",
                            minWidth: 120,
                            templet: function (d) {
                                return d.url
                            }
                        },
                        {
                            field: "status",
                            title: "状态",
                            align: "center",
                            minWidth: 100,
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
                            templet: function (d) {
                                var $html = "";
                                $html += '<a class="layui-btn layui-btn-normal layui-btn-xs" href="/admin/advertisement/doedit.html?id=' + d.id + '">编辑</a>';
                                $html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>';
                                return $html
                            }
                        }]],
                page: true,
                limit: 20,
                height: "full-220",
                response: {
                    statusCode: 1
                },
                id: "table-block",
                done: function (res) {
                    layer.closeAll()
                }
            });
            table.on("tool(table-block)",
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
            function f(g, h) {
//                alert(do_url); return false;
                ajaxPost(do_url, g, h,
                        function () {
                            layOk("操作成功");
                            setTimeout(function () {
                                layLoad("数据加载中...");
                                table.reload("table-block")
                            },
                                    1400)
                        })
            }
            $("#delAll").click(function () {
                ajaxPost(del_all_url, {
                    cid: cid
                },
                        "确定要清空所有广告吗？",
                        function (data) {
                            layOk("清除成功");
                            table.reload("table-block", {
                                page: {
                                    curr: 1
                                }
                            })
                        })
            });
            var uploader = new plupload.Uploader({
                runtimes: "html5,flash,silverlight,html4",
                browse_button: "doneUploadZip",
                url: file_url,
                chunk_size: "500kb",
                save_key: true,
                multi_selection: false,
                flash_swf_url: "/static/plugins/plupload/Moxie.swf",
                silverlight_xap_url: "/static/plugins/plupload/Moxie.xap",
                filters: {
                    max_file_size: "500mb",
                    mime_types: [{
                            title: "Zip files",
                            extensions: "zip"
                        }],
                    prevent_duplicates: false
                },
                init: {
                    FilesAdded: function (up, files) {
                        var str = '<div class="upload-state-div"><span></span><font>文件上传中...</font></div>';
                        $(".upload-loading").html(str);
                        $(".upload-loading").show();
                        $(".upload-remark").hide();
                        uploader.start()
                    },
                    FileUploaded: function (up, file, info) {
                        var res = eval("(" + info.response + ")");
                        if (res.code === 1) {
                            var data = {
                                book_id: book_id,
                                filename: res.data.filename
                            };
                            $.ajax({
                                type: "post",
                                url: decode_url,
                                data: data,
                                dataType: "json",
                                beforeSend: function () {
                                    var str = '<div class="upload-state-div"><span></span><font>文件解压处理中...</font></div>';
                                    $(".upload-loading").html(str)
                                },
                                success: function (res) {
                                    if (res.code === 1) {
                                        layOk("处理成功");
                                        table.reload("table-block")
                                    } else {
                                        var str = '<div class="upload-state-div"><font class="text-red">' + res.msg + "</font></div>";
                                        $(".upload-loading").html(str)
                                    }
                                },
                                error: function () {
                                    var str = '<div class="upload-state-div"><font class="text-red">网络错误，请重试</font></div>';
                                    $(".upload-loading").html(str)
                                }
                            })
                        } else {
                            var str = '<div class="upload-state-div"><font class="text-red">' + res.msg + "</font></div>";
                            $(".upload-loading").html(str)
                        }
                    },
                    Error: function (up, err) {
                        layError("上传失败,请重试");
                        $(".upload-loading").hide();
                        $(".upload-remark").show()
                    }
                }
            });
            uploader.init();
            form.on("submit(table-search)",
                    function (obj) {
                        var where = obj.field;
                        layLoad();
                        table.reload("table-block", {
                            where: where,
                            page: {
                                curr: 1
                            }
                        })
                    })
        });
function layerShow(b, a) {
    layui.use(["table", "layer"],
            function () {
                layui.layer.open({
                    type: 2,
                    title: b,
                    closeBtn: 1,
                    shade: [0],
                    area: ["600px", "80%"],
                    offset: "50px",
                    time: 0,
                    anim: 2,
                    content: [a, "yes"]
                })
            })
}
;