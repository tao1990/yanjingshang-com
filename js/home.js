//==========================================【首页2011-6-22】【Author:yijiangwen】【TIME:20120828】===============================================//
//预装jquery.1.4.4-min.js


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:多行文本自动滚动效果,友情链接自动滚动【20110810】
 * ----------------------------------------------------------------------------------------------------------------------
 */
(function($){
	$.fn.extend({
		Scroll:function(opt,callback){
			if(!opt) var opt={};
			var _this=this.eq(0).find("ul:first");
			var lineH=_this.find("li:first").height(),
			line=opt.line?parseInt(opt.line,15):parseInt(this.height()/lineH,10),
			speed=opt.speed?parseInt(opt.speed,10):3000, //卷动速度，数值越大，速度越慢（毫秒）
			timer=opt.timer?parseInt(opt.timer,13):5000; //滚动的时间间隔（毫秒）

			if(line==0) line=1;
			var upHeight=0-line*lineH;
			scrollUp=function(){
				_this.animate({
						marginTop:upHeight
					},speed,function(){
						for(i=1;i<=line;i++){
							_this.find("li:first").appendTo(_this);
						}
						_this.css({marginTop:0});
					}
				);
			}
			_this.hover(function(){
				clearInterval(timerIDs);
			},function(){
				timerIDs=setInterval("scrollUp()",timer);
			}).mouseout();
		}
	})
})(jQuery);


$(document).ready(function(){
	
	/*
	
	//首页商品分类推荐展示块
	$("#sh_h > li").hover(
		function(){
			var idx = $(this).index();
			var h_x = "-"+(187*idx)+"px";
			//var h_y = "-"+(56*idx)+"px";
			$(this).css("background-position", h_x+" -56px");
			$(".sh_p").hide().eq($(this).index()).show();
		},
		function(){
			var idx = $(this).index();
			var h_x = "-"+(187*idx)+"px";
			//var h_y = "-"+(56*idx)+"px";
			if(idx == 0)
			{
				$(this).css("background-position", h_x+" 0px");
			}
			else
			{
				$(this).css("background-position", h_x+" -28px");
			}			
		}
	);
	
	//==================================推荐商品模块展示==================================//
	var a = 1;
	var b = 0;
	var c = $("#sh_h a").length;
	
	$("#sh_h a").click(function(){
		var d = $(this).attr("_tab");//当前第几个 index()+1;
		var e = $("#sh_h").siblings(".sh_p");
		$("#sh_h a").removeClass();
		$(this).addClass("cur");//添加样式表
		e.css("display", "none");
		e.eq(d-1).css("display", "block");//标记面板显示
		a = d;
		if(b == 1) {
			clearInterval(slideTime);
			slideTime = setInterval(function(){
				autoSwitch(a);
				a++;
				if(a>c)
				{
					a = 1;//返回第一个元素
				}
			}, 4000)
		}
	});
	
	slideTime = setInterval(function() {
		autoSwitch(a);
		a++;
		if (a > c) {
			a = 1
		}
	}, 4000);
	
	$("#sh_h a>span").mouseover(function(d) {
		overTime = setTimeout(function() {
			var e = $(d.target);
			if (e.tagName != "a") {
				e = e.parent()
			}
			a = e.attr("_tab");
			autoSwitch(a);
			clearInterval(slideTime);
			b = 1
		}, 400)
	}).mouseout(function() {
		clearTimeout(overTime);
		if (b == 1) {
			a++;
			clearInterval(slideTime);
			if (a > c) {
				a = 1
			}
			slideTime = setInterval(function() {
				autoSwitch(a);
				a++;
				if (a > c) {
					a = 1
				}
			}, 4000)
		}
	});
	
	$(".noMargin .goodsList").hover(function() {
		clearInterval(slideTime);
		b = 1
	}, function() {
		slideTime = setInterval(function() {
			autoSwitch(a);
			a++;
			if (a > c) {
				a = 1
			}
		}, 4000)
	});	*/
	//==================================推荐商品模块展示END==================================//	
	
	
		
	//买家秀图片切换效果
	var pnum = $(".mjx_img").size();	
	$(".mjx_prev").click(function(){
		
		var midx = $(".mjx_img:visible").index();
		if(midx==1)
		{
			$(".mjx_img:visible").hide();//到第一张
			$(".mjx_img:last").fadeIn("slow");
			$(".mjx_txt_pan").eq(midx-1).hide();
			$(".mjx_txt_pan").eq(pnum-1).show();	
		}
		else
		{
			$(".mjx_img:visible").hide().prev().fadeIn();
			$(".mjx_txt_pan").eq(midx-1).hide().prev().show();
		}
	});
	
	$(".mjx_next").click(function(){

		var midx = $(".mjx_img:visible").index();
		if(midx==pnum)
		{
			$(".mjx_img:visible").hide();//最后一张
			$(".mjx_img:first").fadeIn("slow");
			$(".mjx_txt_pan").eq(midx-1).hide();
			$(".mjx_txt_pan").eq(0).show();			
		}
		else
		{
			$(".mjx_img:visible").hide().next().fadeIn();
			$(".mjx_txt_pan").eq(midx-1).hide().next().show();
		}
	});	

	$("#scrollDiv").Scroll({line:1,speed:800,timer:2000});//实例化友情链接效果	
	
	//首页商品展示效果
	$(".show_head li").mouseover(function(){
		
		var idx = $(this).index();		
		var h_x = "-"+(187*idx)+"px";//x轴
		
		$(this).siblings().each(function(){			
			var sibling_x = "-"+(187*$(this).index())+"px";//x轴
			$(this).css("background-position", sibling_x+" -56px");
		});
		
		$(this).css("background-position", h_x+" 0px");//（中间）
	
		overTime = setTimeout(function(){
			$(".show_head li").eq(idx).css("background-position", h_x+" -28px").fadeIn('slow');//蓝色
		}, 400);
		$(".sh_p").hide().eq($(this).index()).show();		
	});
	
	
	$(".show_head li").mouseout(function(){		
		var idx = $(this).index();		
		var h_x = "-"+(187*idx)+"px";//x轴		
		clearTimeout(overTime);		
		//$(this).css("background-position", h_x+" -56px");//灰色		
	});
	
	setInterval('AutoScroll("#public_notice")', 3000);
	

});

//每周活动弹开/关闭面板
function show_panel(id) {
	for (var i=1; i<=5; i++) {
		$("#da_panel"+i).slideUp();
		$("#da_flip"+i).removeClass('da_flip_on'+i);//移除选中状态
	}
	$("#da_panel"+id).slideDown();
	$("#da_flip"+id).addClass('da_flip_on'+id);
}

//公告翻滚
function AutoScroll(obj) {
	$(obj).find("ul:first").animate({
		marginTop: "-15px"
	}, 500, function () {
		$(this).css({ marginTop: "0px" }).find("li:first").appendTo(this);
	});
}

//外站广告
function website_ad(id) {
	var bg_position = parseInt(id) * 92;
	$('.website_link ul').css("background-position", "0px -" + bg_position + "px");
	for (var j=1; j<=4; j++) {
		if (j==id) {
			$('#website'+j).css("display", "block");
		} else {
			$('#website'+j).css("display", "none");
		}
	}
}
