layui.use(["jquery", "form"],
function() {
    var c = layui.jquery,
    b = layui.form;
//    a($free_type);
//    b.on("radio(free_type)"
//    ,
//    function(e) {
//        var d = parseInt(e.value);
//        a(d)
//    });
    b.on("submit(dosubmit)",
    function(e) {
        var d = e.field;
        ajaxPost("", d, "确定要保存吗？",
        function(f) {
            layOk("保存成功");
            setTimeout(function() {
                location.href = $backUrl
            },
            1000)
        })
    });
    function a(d) {
        if (d == 2) {
            c(".free-input-line").removeClass("layui-hide")
        } else {
            c(".free-input-line").addClass("layui-hide")
        }
    }
});