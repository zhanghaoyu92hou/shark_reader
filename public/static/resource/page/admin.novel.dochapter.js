layui.use(["layedit", "form", "layer", "jquery"],
function() {
    var e = layui.jquery,
    b = layui.layer,
    d = layui.form,
    c = layui.layedit;
    c.set({
        tool: ["html", "strong", "italic", "underline", "del", "addhr", "|", "link", "unlink", "|", "left", "center", "right"],
        height: "300px"
    });
    var a = c.build("content");
    d.on("submit(dosubmit)",
    function(h) {
        var g = h.field;
        var f = c.getContent(a);
        g["content"] = f;
        ajaxPost("", g, "确定要保存吗？",
        function(i) {
            layOk("保存成功");
            setTimeout(function() {
                window.history.go( - 1)
            },
            1000)
        })
    })
});