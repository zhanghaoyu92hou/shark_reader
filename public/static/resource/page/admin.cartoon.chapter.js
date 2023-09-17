layui.use(["jquery", "layer", "table", "form", "upload"],
function() {
    var $ = layui.jquery,
    layer = layui.layer,
    table = layui.table,
    form = layui.form,
    upload = layui.upload;
    var uplayer;
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
            field: "name",
            title: "章节名称",
            align: "center",
            minWidth: 120
        },
        {
            field: "number",
            title: "章节数",
            align: "center",
            minWidth: 120
        },
        {
            field: "read_num",
            title: "阅读量",
            align: "center",
            minWidth: 100
        },
        {
            title: "操作",
            align: "center",
            minWidth: 400,
            templet: function(d) {
                var $html = "";
                $html += '<a class="layui-btn layui-btn-normal layui-btn-xs" onclick="javascript:layPage(\'' + d.name + "','" + d.show_url + "','600px','80%');\">查看</a>";
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
        done: function(res) {
            layer.closeAll()
        }
    });
    $("#checkChapter").click(function() {
        ajaxPost(check_url, {
            book_id: book_id
        },
        "",
        function(data) {
            if (data !== 0) {
                var str = '<div class="layui-fluid"><ul>';
                $.each(data,
                function(i, item) {
                    str += '<li class="err-li">' + item + "</li>"
                });
                str += "</ul></div>";
                layer.open({
                    type: 1,
                    shade: false,
                    title: "章节异常检测",
                    shade: [0],
                    offset: "200px",
                    area: ["330px", "300px"],
                    content: str
                })
            } else {
                layOk("章节数据正常")
            }
        })
    });
    $("#delAll").click(function() {
        ajaxPost(del_all_url, {
            book_id: book_id
        },
        "确定要清空所有章节吗？",
        function(data) {
            layOk("清除成功");
            table.reload("table-block", {
                page: {
                    curr: 1
                }
            })
        })
    });
    $("#doUploadZip").click(function() {
        $(".upload-loading").hide();
        $(".upload-remark").show();
        uplayer = layer.open({
            type: 1,
            shade: false,
            title: "上传漫画分集压缩包",
            shade: [0],
            offset: "100px",
            area: "630px",
            content: $(".zipLayer")
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
            FilesAdded: function(up, files) {
                var str = '<div class="upload-state-div"><span></span><font>文件上传中...</font></div>';
                $(".upload-loading").html(str);
                $(".upload-loading").show();
                $(".upload-remark").hide();
                uploader.start()
            },
            FileUploaded: function(up, file, info) {
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
                        beforeSend: function() {
                            var str = '<div class="upload-state-div"><span></span><font>文件解压处理中...</font></div>';
                            $(".upload-loading").html(str)
                        },
                        success: function(res) {
                            if (res.code === 1) {
                                layOk("处理成功");
                                table.reload("table-block")
                            } else {
                                var str = '<div class="upload-state-div"><font class="text-red">' + res.msg + "</font></div>";
                                $(".upload-loading").html(str)
                            }
                        },
                        error: function() {
                            var str = '<div class="upload-state-div"><font class="text-red">网络错误，请重试</font></div>';
                            $(".upload-loading").html(str)
                        }
                    })
                } else {
                    var str = '<div class="upload-state-div"><font class="text-red">' + res.msg + "</font></div>";
                    $(".upload-loading").html(str)
                }
            },
            Error: function(up, err) {
                layError("上传失败,请重试");
                $(".upload-loading").hide();
                $(".upload-remark").show()
            }
        }
    });
    uploader.init();
    table.on("tool(table-block)",
    function(obj) {
        var field = obj.data;
        switch (obj.event) {
        case "delete":
            var data = {
                id: field.id,
                book_id: book_id
            };
            ajaxPost(del_url, data, "删除章节后不可恢复，确认继续吗？",
            function() {
                layOk("操作成功");
                setTimeout(function() {
                    layLoad();
                    table.reload("table-block")
                },
                1400)
            });
            break
        }
    });
    form.on("submit(table-search)",
    function(obj) {
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