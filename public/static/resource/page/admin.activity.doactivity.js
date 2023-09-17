layui.use(["form", "laydate"],
function() {
    layui.laydate.render({
        elem: "#start_time",
        type: "datetime"
    });
    layui.laydate.render({
        elem: "#end_time",
        type: "datetime"
    });
    layui.form.on("submit(dosubmit)",
    function(b) {
        var a = b.field;
        ajaxPost("", a, "确定要保存吗？",
        function(c) {
            layOk("保存成功");
            setTimeout(function() {
                location.href = $backUrl
            },
            1000)
        })
    })
});