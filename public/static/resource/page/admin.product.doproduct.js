layui.use(["form","laydate","layedit"],function(){var e=layui.form,b=layui.laydate,d=layui.layedit;var c={uploadImage:{url:$upUrl,accept:"image",acceptMime:"image/*",exts:"jpg|png|gif|bmp|jpeg",size:"1024"},tool:["html","strong","italic","underline","del","addhr","|","link","unlink","image_alt","|","left","center","right"],height:"300px"};d.set(c);var a=d.build("content");b.render({elem:"#start_time",type:"datetime"});b.render({elem:"#end_time",type:"datetime"});e.on("submit(dosubmit)",function(h){var g=h.field;var f=d.getContent(a);g["content"]=f;ajaxPost("",g,"确定要保存吗？",function(i){layOk("保存成功");setTimeout(function(){location.href=$backUrl},1000)})})});