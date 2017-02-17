//==========================================【抢购页面20120830】【Author:yijiangwen】【TIME:20120830】===============================================//
//预装jquery.1.4.4-min.js

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:多行文本自动滚动效果,友情链接自动滚动【20110810】
 * ----------------------------------------------------------------------------------------------------------------------
 */
var sh;
$(document).ready(function(){
	
	//一进来就显示当天进行中的抢购活动。
	var do_idx = $("#is_do_index").val();
	var now_idx = $("#get_now_idx").val();	
	
	/*//首页商品分类推荐展示块
	$("#qg_pan > li:not('.q_line')").hover(
		function(){
			
			var idx = $(this).index();
			if(idx>2)
			{
				idx = idx - ((idx-1)/2);
			}			
			
			//面板效果
			if(idx<11)
			{
				$(".q_h1").removeClass().addClass("q_h2").after('<li class="q_line"></li>');
				$(this).removeClass().addClass("q_h1").next("li").detach();
			}
			else
			{
				$(".q_h1").removeClass().addClass("q_h2").after('<li class="q_line"></li>');
				$(this).removeClass().addClass("q_h1").next("li").detach();
			}
			
			if(idx!=do_idx)
			{
				//var dd = $(".q_h1").css("background-image","url(themes/default/images/active/snatchbuy/clock3.gif)");
				//alert(dd);
			}

			$(".q_pan").hide().eq(idx).show();		
		},
		function(){			
		}
	);	*/

	//$(".q_h_txt").eq(now_idx).parent().trigger("mouseover");//展示当日面板
	
	//倒计时模块
	fresh();
	//var sh;
	sh = setInterval(fresh,1000);	
		
});

//切换标签
function changeTab(num) {
	for (var id=0; id<=6; id++) {
		var select_tab = "q_h"+id;
		var select_block = "q_pan"+id;
		if (id==num) {
			document.getElementById(select_tab).className = "q_h1";
			document.getElementById(select_block).style.display="block";
		} else {
			document.getElementById(select_tab).className = "q_h2";
			document.getElementById(select_block).style.display="none";
		}
	}
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:到计时函数
 * ----------------------------------------------------------------------------------------------------------------------
 */
function fresh()
{
	var left_time_end = document.getElementById("get_left_time").value;
	var end_time = new Date(left_time_end);//结束时间
	var now_time = new Date();
	var leftsecond = parseInt((end_time.getTime()-now_time.getTime())/1000);
	var d = parseInt(leftsecond/3600/24);  //left day
	var h = parseInt((leftsecond/3600)%24);//left hour
	var m = parseInt((leftsecond/60)%60);
	var s = parseInt(leftsecond%60);
	
	if(h<10){h = '0'+h;}
	if(m<10){m = '0'+m;}
	if(s<10){s = '0'+s;}
	
	document.getElementById("time_h").innerHTML = h;
	document.getElementById("time_m").innerHTML = m;
	document.getElementById("time_s").innerHTML = s;
	
	if(leftsecond<=0){
		document.getElementById("time_h").innerHTML="00";
		document.getElementById("time_m").innerHTML="00";
		document.getElementById("time_s").innerHTML="00";
		$("#time_w").css("background-image","url(themes/default/images/active/snatchbuy/at_num.gif)");		
		clearInterval(sh);
	}	
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:抢购按钮检查并跳转
 * ----------------------------------------------------------------------------------------------------------------------
 */
function turn_url(goods_id)
{
	if(goods_id == '')
	{
		return;
	}	
	var user_id = document.getElementById("get_user_id").value;
	if(user_id<=0)
	{
		alert("您还未登陆，请先登陆后购买，一人限抢一件！");
	}
	else
	{
		document.location.href = "snatchs"+goods_id+".html";
	}	
}

