
$(document).ready(function() {


	var c = $("#favTab a").length;//a 的长度
	
	var a = 1;
	var b = 0;

	//样式效果在这个函数里面
	$("#favTab a").click(function() {
		var d = $(this).attr("_tab");//当前tab的编号 1234
		//var e = $("#favTab").siblings().find(".goodsList");//下面展示块元素数组对象

		alert(d);

		$("#favTab a").removeClass();//全部的a 删除cur类
		$(this).addClass("cur");//当前的a 加上cur

		//e.css("display", "none");
		//e.eq(d - 1).css("display", "block");//显示下面的那个元素。
		a = d;//当前tab的编号 1234

		if (b == 1) {//a从第一个tab开始
			clearInterval(slideTime);//清除老的定时器

			slideTime = setInterval(function() {
				autoSwitch(a);//重设定时器
				a++;//tab的编号+1
				if (a > c) {
					a = 1//a大于a 的长度，就加1.
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


	//鼠标放上去的效果
	$("#favTab li>a").mouseover(function(d) {

		overTime = setTimeout(function()
		{
			var e = $(d.target);
			if (e.tagName != "a") {
				e = e.parent()
			}
			a = e.attr("_tab");//当前tab的编号
			autoSwitch(a);//自动切换编号
			clearInterval(slideTime);//清除循环定时器
			b = 1//清除头尾
		}, 100)//设定延时触发器

	}).mouseout(function() {
		clearTimeout(overTime);//切除一次定时器

		if (b == 1) {
			a++;
			clearInterval(slideTime);
			if (a > c) {
				a = 1
			}
			slideTime = setInterval(function() {
				autoSwitch(a);//设定循环定时器
				a++;
				if (a > c) {
					a = 1
				}
			}, 4000)
		}
	});

/*
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
	});//简单的切除和设定循环定时器*/


	//focusImg("slideImg", 3000)//首页焦点图片的切换。
});


function autoSwitch(a) {
	$("#favTab a").eq(a - 1).trigger("click")//自动触发click时间。
}

