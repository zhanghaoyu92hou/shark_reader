layui.use(["jquery", "layer"],
function() {
    var b = layui.jquery,
    a = layui.layer;
    b(".list-item").mouseover(function() {
        b(".list-item-content").addClass("layui-hide");
        b(this).find(".list-item-content").removeClass("layui-hide")
    });
    new Clipboard("#btn-copy-title").on("success",
    function(c) {
        c.clearSelection();
        layOk("标题复制成功")
    });
    new Clipboard("#btn-copy-content").on("success",
    function(c) {
        c.clearSelection();
        layOk("正文复制成功")
    });
    b(".list-item-option").click(function(g) {
        g.stopPropagation();
        var f = b(this);
        var d = f.attr("event");
        switch (d) {
        case "cover":
            var j = b(this).attr("src");
            b("#wx-article-cover").attr("src", j);
            b("body,html").animate({
                scrollTop: 0
            },
            200);
            break;
        case "title":
            var i = f.text();
            b("#wx-article-title").text(i);
            break;
        case "readpic":
            var j = b(this).attr("src");
            b("#wx-article-footer").attr("src", j);
            var h = b(document).height();
            b("body,html").animate({
                "scrollTop": h + "px"
            },
            500);
            break;
        case "chapter":
            var c = f.attr("data-number");
            ajaxPost("", {
                book_id: book_id,
                number: c
            },
            "",
            function(e) {
                b("#wx-article-content").html(e.info)
            });
            break
        }
    });
    b("body").click(function() {
        b(".list-item-content").addClass("layui-hide")
    })
});