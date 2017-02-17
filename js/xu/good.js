/*----------------返回顶部------------*/

$(function(){
$('head').append('<style>#go_top{position:fixed; width:19px;height:63px;cursor:pointer; display:none;}</style>');
$('body').append('<div id="go_top"></div>');
var obj=$("#go_top");
var flag=false;
var onlyOne=true;
var clearTime=null;
var layoutWidth=990;
obj.css("left",Math.floor(($(window).width()-layoutWidth)/2)+layoutWidth+5+"px");
if($.browser.msie && $.browser.version=='6.0'){
	obj.css("position","absolute");
}else{
	obj.css("top",$(window).height()-260+"px");
}
obj.click(function(){
	$(window).scrollTop(0);
});
$(window).scroll(function(){
	if($(window).scrollTop()==0){
	obj.fadeOut();
	flag=true;
}else if(flag==true){
	flag=false;
	obj.fadeIn();
}else if(onlyOne==true){
	obj.fadeIn();
	onlyOne=false;
}
if($.browser.msie && $.browser.version=='6.0'){
obj.css('top',$(window).height()+$(window).scrollTop()-260+'px');
if(clearTime!=null){
	clearTimeout(clearTime);
	obj.css("display","none");
}
if($(window).scrollTop()>0)
	clearTime=setTimeout("$('#go_top').fadeIn('10');",20);
}
});
$(window).resize(function(){
if($.browser.msie && $.browser.version=='6.0'){
	obj.css('top',$(window).height()+$(window).scrollTop()-260+'px');
}
else{
	obj.css("top",$(window).height()-260+"px");
}
var HalfWidth=Math.floor(($(window).width()-layoutWidth)/2);
if(HalfWidth>10)
	obj.css("left",HalfWidth+layoutWidth+5+"px");
});
});
/*----------------返回顶部end------------*/


/*------------产品图效果----------*/
$(function(){			
                       $(".jqzoom").jqueryzoom({
                            xzoom:300,
                            yzoom:300,
                            offset:10,
                            position:"right",
                            preload:1,
                            lens:1
                        });
                        $("#spec-list").jdMarquee({
                            deriction:"left",
                            width:350,
                            height:66,
                            step:2,
                            speed:4,
                            delay:10,
                            control:true,
                            _front:"#spec-right",
                            _back:"#spec-left"
                        });
                        $("#spec-list img").bind("mouseover",function(){
                            var src=$(this).attr("src");
                            $("#spec-n1 img").eq(0).attr({
                                src:src.replace("\/n5\/","\/n1\/"),
                                jqimg:src.replace("\/n5\/","\/n0\/")
                            });
                            $(this).css({
                                "border":"2px solid #ff6600",
                                "padding":"1px"
                            });
                        }).bind("mouseout",function(){
                            $(this).css({
                                "border":"1px solid #ccc",
                                "padding":"2px"
                            });
                        });				
                    })//产品放大
					
/*------------产品图效果end----------*/

/*------------背景颜色----------*/
$(document).ready(function() {//背景颜色
$(".pro_top_bem").hover(function() {
$(this).css({"background-color":"#f9f9f9"});

}, function() {
$(this).css({ "background-color": "" });

});
})
/*------------背景颜色end----------*/
$(document).ready(function(){                                    
                        $(".hd ul li").click(function()
                            {
                                $(this).addClass("pro_top_link pro_top_bb");  
                                $(this).siblings().removeClass("pro_top_link pro_top_bb");
                            });
                    });
/*------------登陆注册切换----------*/
$(document).ready(function(){
							  $("#hide").click(function(){
							  $(".aa").hide();
							  $(".bb").show();
							  });
							  $("#show").click(function(){
							  $(".aa").show();
							  $(".bb").hide();
							  });
							});
							$(document).ready(function(){
							  $("#hide1").click(function(){
							  $(".aa").hide();
							  $(".bb").show();
							  });
							  $("#show1").click(function(){
							  $(".aa").show();
							  $(".bb").hide();
							  });
							});
/*------------登陆注册切换end----------*/

/*---------提示信息----------*/
function wsug(e, str){
	var oThis = arguments.callee;
	if(!str) {
		oThis.sug.style.visibility = 'hidden';
		document.onmousemove = null;
		return;
	}		
	if(!oThis.sug){
		var div = document.createElement('div'), css = 'top:0; left:0; position:absolute; z-index:100; visibility:hidden';
			div.style.cssText = css;
			div.setAttribute('style',css);
		var sug = document.createElement('div'), css= 'font:normal 12px/16px "宋体"; white-space:nowrap; color:#666; padding:3px; position:absolute; left:0; top:0; z-index:10; background:#f9fdfd; border:1px solid #0aa';
			sug.style.cssText = css;
			sug.setAttribute('style',css);
		var dr = document.createElement('div'), css = 'position:absolute; top:3px; left:3px; background:#333; filter:alpha(opacity=50); opacity:0.5; z-index:9';
			dr.style.cssText = css;
			dr.setAttribute('style',css);
		var ifr = document.createElement('iframe'), css='position:absolute; left:0; top:0; z-index:8; filter:alpha(opacity=0); opacity:0';
			ifr.style.cssText = css;
			ifr.setAttribute('style',css);
		div.appendChild(ifr);
		div.appendChild(dr);
		div.appendChild(sug);
		div.sug = sug;
		document.body.appendChild(div);
		oThis.sug = div;
		oThis.dr = dr;
		oThis.ifr = ifr;
		div = dr = ifr = sug = null;
	}
	var e = e || window.event, obj = oThis.sug, dr = oThis.dr, ifr = oThis.ifr;
	obj.sug.innerHTML = str;
	var w = obj.sug.offsetWidth, h = obj.sug.offsetHeight, dw = document.documentElement.clientWidth||document.body.clientWidth; dh = document.documentElement.clientHeight || document.body.clientHeight;
	var st = document.documentElement.scrollTop || document.body.scrollTop, sl = document.documentElement.scrollLeft || document.body.scrollLeft;
	var left = e.clientX +sl +17 + w < dw + sl && e.clientX + sl + 15 || e.clientX +sl-8 - w, top = e.clientY + st + 17;
	obj.style.left = left+ 10 + 'px';
	obj.style.top = top + 10 + 'px';
	dr.style.width = w + 'px';
	dr.style.height = h + 'px';
	ifr.style.width = w + 3 + 'px';
	ifr.style.height = h + 3 + 'px';
	obj.style.visibility = 'visible';
	document.onmousemove = function(e){
		var e = e || window.event, st = document.documentElement.scrollTop || document.body.scrollTop, sl = document.documentElement.scrollLeft || document.body.scrollLeft;
		var left = e.clientX +sl +17 + w < dw + sl && e.clientX + sl + 15 || e.clientX +sl-8 - w, top = e.clientY + st +17 + h < dh + st && e.clientY + st + 17 || e.clientY + st - 5 - h;
		obj.style.left = left + 'px';
		obj.style.top = top + 'px';
	}
}
/*---------提示信息end----------*/

$(function(){
	
	$(".showbox_a").click(function(){
		$("#TB_overlayBG_a").css({
			display:"block",height:$(document).height()
		});
		$(".box_a").css({
			left:($("body").width()-$(".box_a").width())/2-20+"px",
			top:($(window).height()-$(".box_a").height())/2+$(window).scrollTop()+"px",
			display:"block"
		});
	});
	
	$(".close").click(function(){
		$("#TB_overlayBG_a").css("display","none");
		$(".box_a ").css("display","none");
	});
})

$(function(){
	
	$(".showbox_b").click(function(){
		$("#TB_overlayBG_b").css({
			display:"block",height:$(document).height()
		});
		$(".box_b").css({
			left:($("body").width()-$(".box_b").width())/2-20+"px",
			top:($(window).height()-$(".box_b").height())/2+$(window).scrollTop()+"px",
			display:"block"
		});
	});
	
	$(".close").click(function(){
		$("#TB_overlayBG_b").css("display","none");
		$(".box_b ").css("display","none");
	});
})

						
/*----------------弹出框------------*/					
$(function() {
	$("#trigger2").powerFloat();
});
$(function() {
	$("#trigger1").powerFloat();
});


$(function() {
	$("#triggera1").powerFloat();
});
$(function() {
	$("#triggera2").powerFloat();
});
$(function() {
	$("#triggera3").powerFloat();
});
$(function() {
	$("#triggera4").powerFloat();
});
$(function() {
	$("#triggera5").powerFloat();
});
$(function() {
	$("#triggera6").powerFloat();
});
$(function() {
	$("#triggera7").powerFloat();
});
$(function() {
	$("#triggera8").powerFloat();
});

/*----------------弹出框end------------*/
			

function op(unit)
{
	var txt = document.getElementById('txt'), v = txt.value;
	txt.value = parseInt(v, 10) + unit;
}				

var target = ["xixi-01","xixi-02","xixi-03","xixi-04","xixi-05","xixi-06"];

/* select遮罩层 */
/*
$(document).ready(function(){
	var newSelect = $("#aa");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});
$(document).ready(function(){
	var newSelect = $("#bb");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});
$(document).ready(function(){
	var newSelect = $("#cc");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});

$(document).ready(function(){
	var newSelect = $("#dd");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});

$(document).ready(function(){
	var newSelect = $("#ee");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});

$(document).ready(function(){
	var newSelect = $("#ff");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});

$(document).ready(function(){
	var newSelect = $("#gg");
	newSelect.click(function(e){
		if(this.className == ""){
			this.className = "open";
			$(this.nextSibling).slideDown("fast");
			e.stopPropagation();//阻止冒泡
		}
	});
	function closeSelect(obj){
		$(obj.nextSibling).slideUp("fast",function(){
			obj.className = "";
		});
	}
	$(document).bind("click", function() {
	  	closeSelect(newSelect[0]);
	});

	newSelect.next().click(function(e){
		var src = e.target;
		if(src.tagName == "A"){
			var PObj = src.parentNode;
			PObj.previousSibling.innerHTML = src.innerHTML;
			$(src).siblings().removeClass();
			src.className = "current";
			PObj.nextSibling.value = src.getAttribute("value");
		}
	});
});
*/
