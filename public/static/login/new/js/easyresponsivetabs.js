(function(a){a.fn.extend({easyResponsiveTabs:function(j){var c={type:"default",width:"auto",fit:true,closed:false,activate:function(){}};var j=a.extend(c,j);var i=j,g=i.type,f=i.fit,h=i.width,k="vertical",b="accordion";var d=window.location.hash;var e=!!(window.history&&history.replaceState);a(this).bind("tabactivate",function(m,l){if(typeof j.activate==="function"){j.activate.call(l,m)}});this.each(function(){var l=a(this);var m=l.find("ul.resp-tabs-list");var t=l.attr("id");l.find("ul.resp-tabs-list li").addClass("resp-tab-item");l.css({display:"block",width:h});l.find(".resp-tabs-container > div").addClass("resp-tab-content");r();function r(){if(g==k){l.addClass("resp-vtabs")}if(f==true){l.css({width:"100%",margin:"0px"})}if(g==b){l.addClass("resp-easy-accordion");l.find(".resp-tabs-list").css("display","none")}}var o;l.find(".resp-tab-content").before("<h2 class='resp-accordion' role='tab'><span class='resp-arrow'></span></h2>");var q=0;l.find(".resp-accordion").each(function(){o=a(this);var w=l.find(".resp-tab-item:eq("+q+")");var v=l.find(".resp-accordion:eq("+q+")");v.append(w.html());v.data(w.data());o.attr("aria-controls","tab_item-"+(q));q++});var p=0,n;l.find(".resp-tab-item").each(function(){$tabItem=a(this);$tabItem.attr("aria-controls","tab_item-"+(p));$tabItem.attr("role","tab");var v=0;l.find(".resp-tab-content").each(function(){n=a(this);n.attr("aria-labelledby","tab_item-"+(v));v++});p++});var u=0;if(d!=""){var s=d.match(new RegExp(t+"([0-9]+)"));if(s!==null&&s.length===2){u=parseInt(s[1],10)-1;if(u>p){u=0}}}a(l.find(".resp-tab-item")[u]).addClass("resp-tab-active");if(j.closed!==true&&!(j.closed==="accordion"&&!m.is(":visible"))&&!(j.closed==="tabs"&&m.is(":visible"))){a(l.find(".resp-accordion")[u]).addClass("resp-tab-active");a(l.find(".resp-tab-content")[u]).addClass("resp-tab-content-active").attr("style","display:block")}else{a(l.find(".resp-tab-content")[u]).addClass("resp-tab-content-active resp-accordion-closed")}l.find("[role=tab]").each(function(){var v=a(this);v.click(function(){var w=a(this);var x=w.attr("aria-controls");if(w.hasClass("resp-accordion")&&w.hasClass("resp-tab-active")){l.find(".resp-tab-content-active").slideUp("",function(){a(this).addClass("resp-accordion-closed")});w.removeClass("resp-tab-active");return false}if(!w.hasClass("resp-tab-active")&&w.hasClass("resp-accordion")){l.find(".resp-tab-active").removeClass("resp-tab-active");l.find(".resp-tab-content-active").slideUp().removeClass("resp-tab-content-active resp-accordion-closed");l.find("[aria-controls="+x+"]").addClass("resp-tab-active");l.find(".resp-tab-content[aria-labelledby = "+x+"]").slideDown().addClass("resp-tab-content-active")}else{l.find(".resp-tab-active").removeClass("resp-tab-active");l.find(".resp-tab-content-active").removeAttr("style").removeClass("resp-tab-content-active").removeClass("resp-accordion-closed");l.find("[aria-controls="+x+"]").addClass("resp-tab-active");l.find(".resp-tab-content[aria-labelledby = "+x+"]").addClass("resp-tab-content-active").attr("style","display:block")}w.trigger("tabactivate",w);if(e){var y=window.location.hash;var z=t+(parseInt(x.substring(9),10)+1).toString();if(y!=""){var A=new RegExp(t+"[0-9]+");if(y.match(A)!=null){z=y.replace(A,z)}else{z=y+"|"+z}}else{z="#"+z}history.replaceState(null,null,z)}})});a(window).resize(function(){l.find(".resp-accordion-closed").removeAttr("style")})})}})})(jQuery);