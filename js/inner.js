/*===========================内页主要脚本.先装yijq.js. 2011-6-22 yi====================*/
/*公共脚本（jquery）和 内页脚本函数*/
$(document).ready(function(){
	//yi:新版 购物车条(全部页面)
	if($(".cart_pop").text()){		
		$(".cart_pan").hover(
			function(){$(".cart_pop").slideDown(100);
			}, 
			function(){$(".cart_pop").slideUp(100);
			}
		);		
	}else{
		$(".cart_pan").hover(
			function(){$(".cart_pop").slideDown(100);
				$(".cart_pop").text("您的购物车中暂时没有商品。").css("line-height","40px");				
			}, 
			function(){$(".cart_pop").slideUp(100);
				$(".cart_pop").text("").css("line-height","");
			}
		);
	}
	
	//yi:页头菜单切换（全部页面）	
	$("#nav > li:not(:first)").mouseover(function(){			
			$(this).addClass("nav_on nav_bg"+$(this).index()).children("div").show();
	}).mouseout(function(){			
			$(this).removeClass("nav_on nav_bg"+$(this).index()).children("div").hide();
	});			

	//yi:新版 左侧导航菜单功能(部分使用)
	if($(".cat_li_1"))
	{
		$(".cat_li_1").click( function(){$("#cat_content_1").toggle(300,toggle_cat_list_pan(1));});
		$(".cat_li_6").click( function(){$("#cat_content_6").toggle(300,toggle_cat_list_pan(6));});
		$(".cat_li_159").click( function(){$("#cat_content_159").toggle(300,toggle_cat_list_pan(159));});
		$(".cat_li_190").click( function(){$("#cat_content_190").toggle(300,toggle_cat_list_pan(190));});
		$(".cat_li_64").click( function(){$("#cat_content_64").toggle(300,toggle_cat_list_pan(64));});
		$(".cat_li_76").click( function(){$("#cat_content_76").toggle(300,toggle_cat_list_pan(76));});
	}
});

function toggle_cat_list_pan(cat_id)
{
	var this_pan = $("#cat_content_"+cat_id);
	if(this_pan.css("display") == "none"){
		this_pan.prev().css("background-image","url(http://www.easeeyes.com/themes/default/images/inner/tip_jian.gif)");									
	}else{
		this_pan.prev().css("background-image","url(http://www.easeeyes.com/themes/default/images/inner/tip_jia.gif)");
	}
}