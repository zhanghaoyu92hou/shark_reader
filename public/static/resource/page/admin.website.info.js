layui.use(["jquery","form"],function(){var d=layui.jquery,b=layui.form;a($is_location);c($is_sign);b.on("radio(is_location)",function(e){var f=parseInt(e.value);a(f)});b.on("radio(is_sign)",function(e){var f=parseInt(e.value);c(f)});function a(e){if(e===1){d("#locationUrlBox").removeClass("layui-hide")}else{d("#locationUrlBox").addClass("layui-hide")}}function c(e){if(e===1){d("#signBox").removeClass("layui-hide")}else{d("#signBox").addClass("layui-hide")}}b.on("submit(dosubmit)",function(f){var e=f.field;ajaxPost("",e,"确定要保存吗？",function(g){layOk("保存成功");d("#SiteName",window.parent.document).html(e.name)})})});