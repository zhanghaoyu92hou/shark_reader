layui.use(["jquery", "layer", "form"],
function() {
    var e = layui.jquery,
    b = layui.layer,
    d = layui.form;
    c($free_type);
    var a = 0;
    var f = new plupload.Uploader({
        runtimes: "html5,flash,silverlight,html4",
        browse_button: "doUploadZip",
        url: $up_url,
        flash_swf_url: "/static/plugins/plupload/Moxie.swf",
        silverlight_xap_url: "/static/plugins/plupload/Moxie.xap",
        multi_selection: true,
        chunk_size: "500kb",
        save_key: true,
        filters: {
            max_file_size: "20mb",
            mime_types: [{
                title: "Zip files",
                extensions: "zip"
            },
            ]
        },
        init: {
            FilesAdded: function(g, h) {
                plupload.each(h,
                function(j) {
                    var i = "";
                    i += '<div class="zip-item" id="file-' + j.id + '">';
                    i += '<div class="zip-item-img">';
                    i += '<img src="/static/common/images/zip.png"/>';
                    i += "</div>";
                    i += '<div class="zip-item-title">';
                    i += '<p style="">' + j.name + "</p>";
                    i += "</div>";
                    i += '<div class="zip-item-load">';
                    i += '<span class="progress-val">等待上传</span>';
                    i += "</div>";
                    i += '<input type="hidden" name="zip_title[' + a + ']" class="zip_title" value="' + j.name + '"/>';
                    i += '<input type="hidden" name="zip_filename[' + a + ']" class="zip_filename" />';
                    i += "</div>";
                    e("#zip-list-body").append(i);
                    a++
                });
                f.start()
            },
            UploadProgress: function(g, h) {
                var i = h.percent;
                var j = "#file-" + h.id;
                e(j).find(".progress-val").text(i + "%")
            },
            FileUploaded: function(g, i, h) {
                var j = JSON.parse(h.response);
                var k = "#file-" + i.id;
                if (j.msg === "ok") {
                    e(k).find(".progress-val").text("上传成功");
                    e(k).find(".zip_filename").val(j.data.filename)
                } else {
                    e(k).find(".progress-val").text(j.msg)
                }
            },
            Error: function(g, h) {
                layError(h.message)
            }
        }
    });
    f.init();
    d.on("radio(free_type)",
    function(h) {
        var g = parseInt(h.value);
        c(g)
    });
    function c(g) {
        if (g == 2) {
            e(".free-input-line").removeClass("layui-hide")
        } else {
            e(".free-input-line").addClass("layui-hide")
        }
    }
    d.on("submit(dosubmit)",
    function(h) {
        var g = h.field;
        ajaxPost("", g, "确定要提交吗？",
        function(i) {
            var j = '<div class="layui-fluid"><ul>';
            e.each(i,
            function(k, l) {
                j += '<li class="err-li">' + l + "</li>"
            });
            j += "</ul></div>";
            b.open({
                type: 1,
                shade: false,
                title: "批量上传日志",
                shade: [0],
                offset: "200px",
                area: ["400px", "300px"],
                content: j,
                btn: "返回小说列表",
                yes: function() {
                    window.location.href = $backUrl
                }
            })
        })
    })
});