/* =======================================================================================================================
 * 具体商品页面 脚本【2012/9/27】【Author:yijiangwen】
 * =======================================================================================================================
 * pre-loaded yijq.js
 */

$(document).ready(function(){

//yi:赠品提示块
	$("#g_gift_tip, #g_gift_text").hover(
		function(){
			$("#g_gift_head").hide();
			var twid = $("#g_gift_text").show().width();
			twid += 10;
			$("#g_gift_tip").parent().css({"position":"absolute", "left":twid});
			$("#g_gift_tt").css("z-index", "1");
		},
		function(){
			$("#g_gift_head").show();
			$("#g_gift_text").hide();
			$("#g_gift_tt").css("z-index", "0");
		}
	);

//yi:重构页面脚本
	showpan1();

	//选度数面板背景变色
	$(".pro_top_bem").hover(function(){
		$(this).css({"background-color":"#f9f9f9"});
	},function(){
		$(this).css({ "background-color": "" });
	});

	//未知
	$(".hd ul li").click(function(){
		$(this).addClass("pro_top_link pro_top_bb");  
		$(this).siblings().removeClass("pro_top_link pro_top_bb");
	});

	//登陆注册切换
	$("#hide, #hide1").click(function(){
		$(".aa").hide();
		$(".bb").show();
	});
	$("#show, #show1").click(function(){
		$(".aa").show();
		$(".bb").hide();
	});

	//vip,度数换算表等弹出窗口
	$("#trigger1, #trigger2").powerFloat();

	//关联商品弹出窗口
	$("#triggera1, #triggera2, #triggera3, #triggera4, #triggera5, #triggera6, #triggera7, #triggera8").powerFloat();

	//未知
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

	//产品放大效果
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
		//var src=$(this).attr("src");

		//yi:判断商品原始图片。显示300px原始图片
		var src = $(this).attr("_tab");
		var jsrc = $(this).attr("_tab2");

		//=>如果商品原图加载异常处理

		$("#spec-n1 img").eq(0).attr({
			src:src.replace("\/n5\/","\/n1\/"),
			jqimg:jsrc.replace("\/n5\/","\/n0\/")
		});
		$(this).css({
			"border":"2px solid #b5e8cd",
			"padding":"1px"
		});
	}).bind("mouseout",function(){
		$(this).css({
			"border":"1px solid #ccc",
			"padding":"2px"
		});
	});	

//yi:重构页面脚本end

	
	//mjx模块
	
	//mjx投票
	$("#add_mjx_vote").click(function(){

		var mjx_id = $("#get_sh_mjx_id").attr("value");
		if(mjx_id>0)
		{
			$.ajax({
				type:'post',
				url: 'ajax_step.php?act=add_mjx_vote',	
				data:'&mjx_id='+mjx_id+'&m='+Math.random(),		
				cache:true,
				success:
				function(da)
				{
					//var da = eval('('+da+')');				
					if(da == 'ok')
					{
						var vote = $("#mjx_user_vote").val()*1+1;
						$("#mjx_user_vote").val(vote);						
					}
					else if(da == 'limit')
					{
						alert('很抱歉，每人每天限投5票！');
					}
					else
					{
						//no
					}
				}					
			});
		}
	});
	
	//first mjx_thumb border is blue
	var blue = "#0ca7bd";
	$(".mjx_thumb dd:visible").first().css("border-color", blue);
	
	var shw_mjx   = $("#sh_mjx_img");
	var one_width = shw_mjx.attr('width');	
	if(680<one_width)
	{
		var one_height = shw_mjx.attr('height');
		var one_bi     = one_height/one_width;
		var one2_height= Math.ceil(one_bi*680);
		shw_mjx.css('width', '680').css('height', one2_height);
	}

	/*
	//img fadein
	var is_blue = 0;
	$(".mjx_thumb dd:visible").live("mouseover", function(){
		if($(this).css("border-color")==blue)
		{
			is_blue = 1;
		}
		$(this).css("border-color", "#dcdcdc");
	});
	$(".mjx_thumb dd:visible").live("mouseout",function(){
		if(is_blue)
		{
			$(this).css("border-color", blue);
			is_blue = 0;
		}
		else
		{
			$(this).css("border-color", "#f2f2f2");
		}			
	});*/
		
	
	//click mjx_thumb
	if($.browser.msie)
	{
		$(".mjx_thumb dd:visible > img").live("click", function(){
	
			var click_dd = $(this).parent("dd");		
			if($(this).css("border-top-color")!=blue)
			{
				var t_mjx_id = $(this).attr("name");
				update_mjx_info(t_mjx_id);
				click_dd.css("border-color", blue).siblings().css("border-color", "#f2f2f2");
			}				
		});
	}
	else
	{
		$(".mjx_thumb dd:visible > img").bind("click", function(){
			var click_dd = $(this).parent("dd");		
			if($(this).css("border-top-color")!=blue)
			{
				var t_mjx_id = $(this).attr("name");
				update_mjx_info(t_mjx_id);							
				click_dd.css("border-color", blue).siblings().css("border-color", "#f2f2f2");
			}		
		});
	}	
	
	$(".next_mjx").click(function(){
	
		//next show, renew load next
		var mjx_dd   = $(".mjx_thumb dd:visible");
		var goods_id = $("#get_goods_id").attr('value');
		var mjx_id   = mjx_dd.last('dd').children("img").attr("name");

		$.ajax({
			type:'post',
			url: 'ajax_step.php?act=reload_next_mjx',	
			data:'&mjx_id='+mjx_id+'&goods_id='+goods_id+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				var da = eval('('+da+')');
				
				//have next mjx loaded
				if(da.id !='undefind' && da.id>0)
				{
					//get next mjx success
					mjx_dd.first().hide();
					$(".mjx_thumb").append('<dd><img src="'+da.thumb_img+'" width="120px" height="150px" alt="" name="'+da.id+'"/></dd>');
					
					//change next mjx info
					var sh_img = $(".mjx_thumb dd:visible").first().css("border-color", blue);	
					sh_img.siblings().css("border-color", "#f2f2f2");			
					var get_mjx_id = sh_img.children('img').attr("name");
					update_mjx_info(get_mjx_id);				
					//change next mjx info end					
				}
				else
				{
					$(".mjx_thumb dd:visible").each(function(i){
							
						if($.browser.msie)
						{
							if($(this).css("border-top-color")==blue && i<4)
							{
								var n_mjx_id = $(this).css("border-color", "#f2f2f2").next('dd').css("border-color", blue).children("img").attr("name");
								update_mjx_info(n_mjx_id);							
								return false;
							}
						}
						else
						{							
							if(rgb2hex($(this).css("border-top-color"))==blue && i<4)
							{
								var n_mjx_id = $(this).css("border-color", "#f2f2f2").next('dd').css("border-color", blue).children("img").attr("name");
								update_mjx_info(n_mjx_id);							
								return false;
							}
						}
					});
				}
			}				
		});		
	});
	
	//prev_mjx
	$(".prev_mjx").click(function(){
		
		var mjx_dd   = $(".mjx_thumb dd:visible");
		var goods_id = $("#get_goods_id").attr('value');
		var mjx_id   = mjx_dd.first().children("img").attr("name");		
		
		$.ajax({
			type:'post',
			url: 'ajax_step.php?act=reload_prev_mjx',	
			data:'&mjx_id='+mjx_id+'&goods_id='+goods_id+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				var da = eval('('+da+')');
				
				//have next mjx loaded
				if(da.id>0)
				{
					//get next mjx success
					mjx_dd.last().hide();					
					var ind = mjx_dd.first().index()-1;
					$(".mjx_thumb dd").eq(ind).show();

					//$(".mjx_thumb").append('<dd><img src="'+da.thumb_img+'" width="120px" height="150px" alt="" name="'+da.id+'"/></dd>');					
					/*
					//change next mjx info
					var sh_img = $(".mjx_thumb dd:visible").first().css("border-color", blue);	
					sh_img.siblings().css("border-color", "#f2f2f2");			
					var get_mjx_id = sh_img.children('img').attr("name");
					update_mjx_info(get_mjx_id);				
					//change next mjx info end	
					*/				
				}
				else
				{				
					$(".mjx_thumb dd:visible").each(function(i){							
						if($.browser.msie)
						{
							if($(this).css("border-top-color")==blue && i>0)
							{
								var n_mjx_id = $(this).css("border-color", "#f2f2f2").prev('dd').css("border-color", blue).children("img").attr("name");
								update_mjx_info(n_mjx_id);							
								return false;
							}
						}
						else
						{							
							if(rgb2hex($(this).css("border-top-color"))==blue && i>0)
							{
								var n_mjx_id = $(this).css("border-color", "#f2f2f2").prev('dd').css("border-color", blue).children("img").attr("name");
								update_mjx_info(n_mjx_id);							
								return false;
							}
						}
					});					
				}
			}				
		});			
	});	
	//mjx模块END
	
	
	//yi:商品数量的加减
	$(".bt_minus").click(function(){
		var obj = $(this).parent().find("input[name*=count]");
		if(parseInt(obj.val())>0){
			obj.val(parseInt(obj.val())-1);
		}else{
			obj.val(0);
		} 
	});
	$(".bt_add").click(function(){
		var obj = $(this).parent().find("input[name*=count]");
		if(obj.val()==""||parseInt(obj.val())<0){
			obj.val(1);
		}else{
			obj.val(parseInt(obj.val())+1);
		} 
	});
	$(".input_count").blur(function(){
		if(parseInt($(this).val())<0){
			$(this).val(0);
		}
	});
	//yi:护理液的加减
	$(".bt_minus_h").click(function(){
		var obj = $(this).parent().find("input[name=number]");
		if(parseInt(obj.val())>0){
			obj.val(parseInt(obj.val())-1);
		}else{
			obj.val(0);
		} 
	});
	$(".bt_add_h").click(function(){
		var obj = $(this).parent().find("input[name=number]");
		if(obj.val()==""||parseInt(obj.val())<0){
			obj.val(1);
		}else{
			obj.val(parseInt(obj.val())+1);
		} 
	});	
	
	//yi:用户选择了验光单

	$("#receipt_select_back_8888").change(function(){
		//var rec_id = $(this).val();
		//alert('999');
		
		
		if(rec_id>0)
		{		
			//找到这个商品的眼镜的数据
			$.ajax({
				type:'post',
				url: 'ajax_step.php?act=get_user_receipt',	
				data:'&rec_id='+rec_id+'&m='+Math.random(),		
				cache:false,
				success:
				function(da)
				{
					var da = eval('('+da+')');
					
					//匹配度数
					if(da.zeye_qiujin !='' && da.yeye_qiujin != '')
					{
						var err_msg = "";							
						$("#zselect_ds").val(da.zeye_qiujin);
						$("#yselect_ds").val(da.yeye_qiujin);
						
						if($("#zselect_ds").val()==null || $("#zselect_ds").val()=='')
						{
							$("#zselect_ds").val('');
							err_msg += "左";
						}
						
						if($("#yselect_ds").val()==null || $("#yselect_ds").val()=='')
						{
							$("#yselect_ds").val('');
							err_msg += "，右";
						}
						if(err_msg != '')
						{
							err_msg += "眼度数不匹配，请手动选择！";
							alert(err_msg);
						}					
					}
				}		
			});
		}//end if
	});		
	
	
	//yi:用户表单登录
	$("#yi_login_bt").click(function(){
		
		var username = $("#yi_login_form input[name='username']").val();
		var password = $("#yi_login_form input[name='password']").val();
		var autologin= $("#yi_login_form input[name='autologin']").attr('checked');
		
		//数据提交之前进行数据验证
		if(username == '')
		{
			//$("#yi_login_form input[name='username']").blur(true);
			//$("#tip_un_tr").show().children("td:eq(1)").text("用户名不能为空");
		}
		if(password == '')
		{   
			//$("#yi_login_form input[name='password']").blur();
			//$("#tip_un_tr2").show().children("td:eq(1)").text("登录密码不能为空");
		}		
		//$("#yi_login_form").submit(false);	
		
		$.ajax({
				type:'post',
				url: 'ajax_step.php?act=ajax_user_login',	
				data:'&username='+username+'&password='+password+'&autologin='+autologin+'&m='+Math.random(),		
				cache:false,
				success:
				function(da)
				{
					var da = eval('('+da+')');
										
					if(da.error == 1)
					{
						//登录成功
						$("#ECS_MEMBERZONE").html(da.content);	
						$("#TB_window").hide();		
						$("#TB_overlay").hide();
						
						//查找是否有我的验光单。有则把验光单加载进来。
						$.ajax({
							type:'post',
							url: 'ajax_step.php?act=get_user_receipt_list',	
							data:'&m='+Math.random(),		
							cache:false,
							success:
							function(dt)
							{
								var dt = eval('('+dt+')');
								var len = dt.length;
								
								if(len == 0)
								{
									//用户没有验光单
									$("#receipt_sh1").html('管理我的验光单：<a href="user_receipt.html" target="_blank" title="去管理我的验光单">[去管理验光单]</a>');
								}
								else
								{
									/*
									var op_str = '';
									for(var i=0; i<len; i++)
									{
										op_str += '<option value="'+dt[i]['rec_id']+'">'+dt[i]['receipt_name']+'</option>';
									}
								
									var html_se = '<span class="receipt_h_text">选择已有验光单：</span><select id="receipt_select" class="pro_top_link_selse fl" style="width:auto; min-width:130px; height:22px;"><option value="">请选择</option>';
									html_se += op_str+'</select>';
									$("#receipt_sh1").addClass("ml10 color9").html(html_se);
									*/
									document.location.reload();
								}
							}							
						});						
					}
					else
					{
						//登录失败
						alert(da.content);
						//$("#TB_window").hide();		
						//$("#TB_overlay").hide();
					}																							
				}					
		});
		
	});//end

	//选中商品度数，自动选择下边组合商品度数
});


//=====================================================【具体商品页面函数】=====================================================//
//【函数】

//yi:弹窗控制函数
function showpan1()
{
	$("#show_vip_tip, #vip_pr_p").hover(function(){
		$("#vip_pr_p").show();
	},
	function(){
		$("#vip_pr_p").hide();
	});
}




//yi:同时购买框架和镜片
function kjjp_add_cart(gid)
{
	var glasses = document.getElementById("get_glasses_type").value;
	var zselect = document.getElementById("zselect_ds").value;
	var yselect = document.getElementById("yselect_ds").value;
	var kj_tongju = document.getElementById("kj_tongju").value;
	
	if(zselect == '' || yselect == '' || kj_tongju == '')
	{
		alert('请先选择好眼镜的度数和瞳距！');
		return false;
	}
	else
	{
		if(glasses == '' || glasses<1)
		{
			glasses = 1;
		}	
		
		kj_add_cart(gid, glasses);
	}		
}

//yi:框架加入购物车    //glasses_type:镜片类型
function kj_add_cart(goods_id, glasses_type)
{
	//==================================眼镜数据==================================//
	var zselect =(document.ECS_FORMBUY.zselect.value);
    var yselect =(document.ECS_FORMBUY.yselect.value);
    var goods_number =(document.ECS_FORMBUY.goods_number.value);
    var kj_tongju    = (document.ECS_FORMBUY.kj_tongju.value);
    var zsg   = (document.ECS_FORMBUY.zsg.value);
    var ysg   = (document.ECS_FORMBUY.ysg.value);
    var zzhou = (document.ECS_FORMBUY.zzhou.value);
    var yzhou = (document.ECS_FORMBUY.yzhou.value);

	$.ajax({
			type:'post',
			url: 'ajax_step.php?act=kuangjia_buy',	
			data:'&goods_id='+goods_id+'&glasses_type='+glasses_type+'&goods_number='+goods_number+'&zselect='+zselect+'&yselect='+yselect+'&kj_tongju='+kj_tongju+'&zsg='+zsg+'&ysg='+ysg+'&zzhou='+zzhou+'&yzhou='+yzhou+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{	
				var dr = Array();
				dr = da.split('_');
				if(dr[0] == 'ok')
				{					
					//---------------------------------弹出div提示----------------------------------//	
					$("#mydiv").fadeIn(300);
					$("#div_cart_info_num").text(dr[1]);
						
					//判断浏览器版本,解决ie6bug.
					if($.browser.msie && $.browser.version == '6.0'){
						$("#framediv").css("display","block");
					}	
					//---------------------------------弹出div提示END-------------------------------//
				}
				else if(da == 'fail')
				{
					alert('很抱歉，加入购物车失败，请联系客服！');
				}	
				else{}																				
			}			
	});	
}

function kj_jia()
{
	var goods_number = $(".kj_n_input").val();
	if(goods_number>0)
	{
		goods_number = goods_number*1+1;
		$(".kj_n_input").val(goods_number);
	}
}
function kj_jian()
{
	var goods_number = $(".kj_n_input").val();
	if(goods_number>1)
	{
		goods_number = goods_number*1-1;
		$(".kj_n_input").val(goods_number);
	}	
}

function chg_receipt(tval)
{
	rec_id = tval;
	if(rec_id>0)
	{		
		//找到这个商品的眼镜的数据
		$.ajax({
			type:'post',
			url: 'ajax_step.php?act=get_user_receipt',	
			data:'&rec_id='+rec_id+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				
				var da = eval('('+da+')');
				
				//匹配度数
				if(da.zeye_qiujin !='' && da.yeye_qiujin != '')
				{
					var err_msg = "";							
					$("#zselect_ds").val(da.zeye_qiujin);
					$("#yselect_ds").val(da.yeye_qiujin);
					
					if($("#zselect_ds").val()==null || $("#zselect_ds").val()=='')
					{
						$("#zselect_ds").val('');
						err_msg += "左";
					}
					
					if($("#yselect_ds").val()==null || $("#yselect_ds").val()=='')
					{
						$("#yselect_ds").val('');
						err_msg += "，右";
					}
					if(err_msg != '')
					{
						err_msg += "眼度数不匹配，请手动选择！";
						alert(err_msg);
					}					
				}
			}		
		});
	}//end if
}

function rgb2hex(rgb){
 rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
 function hex(x) {
  return ("0" + parseInt(x).toString(16)).slice(-2);
 }
 return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

//get this mjx info	
function update_mjx_info(get_mjx_id)
{
	if(get_mjx_id>0)
	{
		var image2 = new Image();
		$.ajax({
			type:'post',
			url: 'ajax_step.php?act=get_mjx_info',	
			data:'&mjx_id='+get_mjx_id+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				var da = eval('('+da+')');
				$("#mjx_user_name").text(da.user_name);				
				$("#mjx_detail").text(da.detail);
				$("#mjx_user_vote").val(da.vote);
				$("#get_sh_mjx_id").val(da.id);	
				image2.src = da.img;
			}				
		});
		
		if(image2.complete)
		{
			image2.onload = function()
			{
				var mwidth  = image2.width;
				var mheight = image2.height;
				if(mwidth>680)
				{
					var bi = mheight/mwidth;
					mwidth = 680;
					mheight= Math.ceil(bi*680);
				}
				var sh_mjx = document.getElementById("sh_mjx_img");
				sh_mjx.src = image2.src;
				sh_mjx.width  = mwidth;
				sh_mjx.height = mheight;
				$(".mjx_img, #sh_mjx_img").css("width", mwidth).css("height", mheight);				
			}			
		}		
		else
		{
			image2.onload = function()
			{
				var mwidth  = image2.width;
				var mheight = image2.height;
				if(mwidth>680)
				{
					var bi = mheight/mwidth;
					mwidth = 680;
					mheight= Math.ceil(bi*680);
				}
				var sh_mjx = document.getElementById("sh_mjx_img");
				sh_mjx.src = image2.src;
				sh_mjx.width  = mwidth;
				sh_mjx.height = mheight;
				$(".mjx_img, #sh_mjx_img").css("width", mwidth).css("height", mheight);				
			}
		}
	}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:组合购买功能
 * ----------------------------------------------------------------------------------------------------------------------
 */
function yi_group_buy(goods_id)
{
	var buy_id = $("#get_group_buy_id").val();//选择的全部商品id号字符串。
	if(buy_id == '')
	{
		alert("系统繁忙，请稍后再试！");
		return ;
	}
	else
	{
		var have_ds   = $("#goods_have_ds").val();
		var g_zselect = $("#g_zselect").val();
		var g_yselect = $("#g_yselect").val();
		var pei_ds    = '';//配件度数字符串
		var buy_bz    = 1; //购买步骤
		if(have_ds)
		{
			if(g_zselect == '' || g_yselect == '')
			{
				alert("请选择第一个组合商品的度数！");
				buy_bz = 0;
			}
		}
		
		//验证配件的度数
		if(buy_bz)
		{
			var pei = buy_id.split(',');
			for(var i=1; i<pei.length; i++)
			{
				var ds_id = 'pei_ds_'+pei[i];		
				if($('#'+ds_id).length)
				{
					if($('#'+ds_id).val()=='')
					{
						alert('请选择组合购买商品的度数！');
						buy_bz = 0;
						break;
					}
					else
					{
						//添加配件商品度数						
						if(pei_ds == '')
						{
							pei_ds = $('#'+ds_id).val()+'_'+pei[i];
						}
						else
						{
							pei_ds = pei_ds+'|_|'+$('#'+ds_id).val()+'_'+pei[i];
						}
					}
				}
				else
				{
					pei_ds = pei_ds+'|_|'+'_'+pei[i];//没有度数的配件
				}
			}
		}
	
		//全部配件商品的度数发送到处理程序。
		if(buy_bz)
		{
			$.ajax({
					type:'post',
					url: 'ajax_step.php?act=add_group_buy',	
					data:'&buy_id='+buy_id+'&goods_id='+goods_id+'&pei_ds='+pei_ds+'&g_zselect='+g_zselect+'&g_yselect='+g_yselect+'&m='+Math.random(),		
					cache:false,
					success:
					function(da)
					{				
						if(da == 'ok')
						{
							window.location.href = 'flow.html';
						}																					
					}			
			});
		}
	}	
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:变更组合购买商品
 * ----------------------------------------------------------------------------------------------------------------------
 * id:选中的那个checkbox对象
 */
function ck_group_buy(id)
{
	var gid             = $(id).attr("value");	                    //配件商品的id
	var ch_price        = $("#get_price_"+gid).attr("value");       //配件商品的组合购买价
	var ch_market_price = $("#get_market_price_"+gid).attr("value");//配件商品的市场价

	var ym_p   = $("#yi_market_price");//组合购买总市场价
	var ys_p   = $("#yi_shop_price");  //组合购买总价
	var save_p = $("#yi_save_price");  //组合购买总节省
	
	var ym_pv  = ym_p.attr("title");
	var ys_pv  = ys_p.attr("title");
	var save_pv= save_p.attr("title");
	
	var buy_id = $("#get_group_buy_id").attr("value");
	
	
	if($(id).attr("checked"))//选中该商品
	{
		buy_id = buy_id + "," + gid;	
		$("#get_group_buy_id").attr("value", buy_id);	
		
		var t_m_p    = Number(ym_pv)   + Number(ch_market_price);
		var t_s_p    = Number(ys_pv)   + Number(ch_price);
		var t_save_p = Number(save_pv) + Number(ch_market_price - ch_price);
	}
	else
	{
		buy_id = buy_id.replace(","+gid , "");		
		$("#get_group_buy_id").attr("value", buy_id);		
		var t_m_p    = Number(ym_pv)   - Number(ch_market_price);
		var t_s_p    = Number(ys_pv)   - Number(ch_price);
		var t_save_p = Number(save_pv) - Number(ch_market_price - ch_price);
	}
	ym_p.attr("title",   t_m_p).text(t_m_p);
	ys_p.attr("title",   t_s_p).text(t_s_p);		
	save_p.attr("title", t_save_p).text(t_save_p);
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:验证 用户登录信息
 * ----------------------------------------------------------------------------------------------------------------------
 */
function ck_user_login_info()
{
	
}


function userLogin()
{
  var frm      = document.forms['formLogin'];
  var username = frm.elements['username'].value;
  var password = frm.elements['password'].value;
  var msg = '';

  if(username.length == 0)
  {
    msg += username_empty + '\n';
  }

  if(password.length == 0)
  {
    msg += password_empty + '\n';
  }

  if(msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}



/* ----------------------------------------------------------------------------------------------------------------------
 * yi:产品页面——用户提问
 * ----------------------------------------------------------------------------------------------------------------------
 */
function aa(){	
	var form   = document.forms['formMsg'];
	var email  = document.getElementById('email2').value;	
	var context= document.getElementById('context2').value;
	var msg_type = null;
	
	//用户提问提交前， 验证用户是否登录！
	var logins = iflogin();
	if( !logins ){
		return false;
	}
	//问答类型
	var len = form.elements['msg_type'].length;
	for(var i=0; i<len; i++){
		if( form.elements['msg_type'][i].checked == true){
			msg_type = i;
		}
	}
	if(msg_type == null){
		alert("^_^ 请您选择问答类型！");
		return false;
	}
	//邮箱或电话
	if(email == ''){
		alert("^_^ 请填写邮箱或联系电话！");		
		return false;
	}else{
		//只有数字/[0-9]/.test(email); /^[1][358]\d{9}$/; (/[^\d]/;
		if( /^[0-9\-]+$/.test(email) ){
			if( !(/^[1][358]\d{9}$/.test(email) || /^[0][1-9]\d{9}$/.test(email) || /^[0][7][3][1]\d{8}$/.test(email) || /(^[0][1-2][0-9]\-\d{8}$)|(^[0][1-9]{3}\-\d{7}$)|(^[0][7][3][1]\-\d{8}$)/.test(email) )){
				alert("联系电话错误！");
				return false;
			}		
		}else{
			//邮箱
			if(!(Utils.isEmail(email))){
				alert("邮箱格式错误！");
				return false;
			}
		}		
	}
	//问题内容	
	if(context == ''){
		alert("^_^ 提问内容不能为空！");
		return false;
	}else{
		//验证用户提交的内容中间是否包含非法字符	
		var ifstr1 = context.match("</a>");
		var ifstr2 = context.match("<a"); 
		var ifstr3 = context.match("href="); 
		if( ifstr1 != null || ifstr2 != null || ifstr3 != null ){
			alert("你提交的内容含有非法链接，请修正!");
			return false;
		}		
	}
	//数据正确
	return true;
}

//提问的邮箱检查
function yi_chack(){
	var email = document.getElementById("aq_email");
	var context = document.getElementById("aq_context");
	var href = window.location.href;
		
	if(email.value == ""){
		alert("邮箱不能为空！返回请填写");
		window.location.href = href;
		all.email.blur();	
	}
	if(context.value == ""){
		alert("提交内容不能为空！请返回填写！");
		window.location.href = href;
		context.blur();
	}
}

/*--------------------用户提问前是否登录验证-------------------*/
function iflogin(){
	//用户名
	var userinfo = document.getElementById("user_info").value;
	if( userinfo == '' ){
		alert("请先登录，才可提问噢 ^_^");
		return false;
	}else{
		return true;
	}	
}
/*--------------------------------------------------------------------------------------------------------------------------------*/

//==========================================================y_polls.js==========================================================||
/*-ajax--客户端-*/
var xmlHttp;
function showUser(str1,str2,str3,str4)
{
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		 alert ("Browser does not support HTTP Request");
		 return;
	} 
	var url="yi_ajax.php";
	url=url+"?vote="+str1+"&gid="+str2+"&uid="+str3+"&mjxid="+str4;
	url=url+"&sid="+Math.random();

	xmlHttp.open("GET",url,true);
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.send(null);
}

function stateChanged()
{
	if (xmlHttp.readyState==4) {
		 if(xmlHttp.status == 200 || xmlHttp.readyState=="complete"){
		
			var a = document.getElementById("vote");			
			//如何实现重新刷新页面
			window.location.reload();		
		 }
	 }
}

function GetXmlHttpObject()
{
	var xmlHttp=null;
	try{
		 // Firefox, Opera 8.0+, Safari
		 xmlHttp=new XMLHttpRequest();
	 }catch(e){
		 try{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		  } catch (e){
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
	 }
	return xmlHttp;
}
//==========================================================y_polls.js end=======================================================||

//==========================================================mjx.js===============================================================||
/*----------------------------------------------------------*/
function ckpic(){
	//获取div的id
	for(var i=1; i<=8; i++){
		pici = document.getElementById("pic'+i+'");
	}

	for(var i=1; i<=8; i++){
		if(i == n){
			pici.style.background="url(themes/default/images/yi_index/pic'+i+'1.gif)";
		}		
	}
	pic1.style.background="url(themes/default/images/yi_index/pic11.gif)";
}
/*-------------------------------------------------------上传买家秀图片------------------------------------------*/
//是否登录
function login(){	
	var username = null;
	username = document.getElementById("h_username").value;
	if( username == ''){
		//阻止文件上传处--打开窗口-----------
		alert("请您先登录，才可以上传噢^_^");
		return false;
	}else{
		return true;
	}
}
/*------------------------------------------------买家秀--投票顶一下--验证------------------------------------------*/
/*----------------ajax--mjx_vote投票实现----------------------------*/
var http_request1=false;
function send_request_vote(url){
	http_request1=false;
	if(window.XMLHttpRequest){     //not IE
		http_request1=new XMLHttpRequest();
		if(http_request1.overrideMimeType){
			http_request1.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){//IE
		try{
			http_request1=new ActiveXObject("Msxml2.XMLHttp");
		}catch(e){
			try{
				http_request1=new ActiveXobject("Microsoft.XMLHttp");
			}catch(e){}
		}
	}	
	if(!http_request1){
		window.alert("创建XMLHttp失败！");
		return false;
	}
	http_request1.onreadystatechange=voterequest;
	//发送数据
	http_request1.open("GET",url,true);
	http_request1.send(null);
}
//mjx_vote回调函数
function voterequest(){
	if(http_request1.readyState==4){
		if(http_request1.status==200){
			var res = http_request1.responseText;
			//alert(res);	
			//分割数据‘，’
			var vote=null, id=null ;
			var aa = Array();
			aa = res.split(',');
			vote = aa[0];    //投票后的票数
			id   = aa[1];	 //投票的图片的id
			//befor= aa[2];		
			if(vote != ''){
				alert("谢谢您的投票！");
				document.getElementById(id).innerHTML = vote;		
			}else{
				alert("您今天已经投过票了，一天只能投五票哦！");
			}		
		}else{
			alert("您的请求失败！");
		}
	}
}
//mjx_vote---调用函数----
function showUser(str1,str2,str3,str4)
{
	var url="mjx_vote.php"; 
	url= url+"?vote="+str1+"&gid="+str2+"&uid="+str3+"&mjxid="+str4;
	url= url+"&sid="+Math.random();
	send_request_vote(url);
}
/*------------------------------------------------mjx页面的ajax(-)--函数---------------------------------------------------*/
var http_request=false;
function send_request(url){
	http_request=false;
	if(window.XMLHttpRequest){     //not IE
		http_request=new XMLHttpRequest();
		if(http_request.overrideMimeType){
			http_request.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){//IE
		try{
			http_request=new ActiveXObject("Msxml2.XMLHttp");
		}catch(e){
			try{
				http_request=new ActiveXobject("Microsoft.XMLHttp");
			}catch(e){}
		}
	}
	
	if(!http_request){
		window.alert("创建XMLHttp失败！");
		return false;
	}
	http_request.onreadystatechange=processrequest;
	//发送数据
	http_request.open("GET",url,true);
	http_request.send(null);
}

function processrequest(){
	if(http_request.readyState==4){
		if(http_request.status==200){
			//document.getElementById('goodslist').innerHTML="";			
			document.getElementById('goodslist').innerHTML=http_request.responseText;
		}else{
			alert("您所请求的页面不正常！");
		}
	}
}
//--------------ajax刷新动态加载数据库中品牌---onchange="keycheck(this.value)"--------------||
function keycheck(n){
	send_request('ajaxgoods.php?keyword='+n+'&rand='+Math.random());
}
//==========================================================mjx.js end===============================================================||





//未知函数
function op(unit)
{
	var txt = document.getElementById('txt'), v = txt.value;
	txt.value = parseInt(v, 10) + unit;
}

/*---------提示信息----------*/
function wsug(e, str)
{
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


//==========================================================【插件板块】===============================================================//
/* ----------------------------------------------------------------------------------------------------------------------
 * 小插件 yi:回到顶部（兼容ie6）【2011-6-9】
 * ----------------------------------------------------------------------------------------------------------------------
 */
$(function(){
	var imgUrl = "http://www.easeeyes.com/themes/default/images/to-top.gif";
	$('head').append('<style>#go_top{position:fixed; width:19px; height:63px; background:url(' + imgUrl + ') no-repeat; cursor:pointer; display:none;}</style>');
	$('body').append('<div id="go_top"></div>');

	var obj = $("#go_top");
	var flag = false;
	var onlyOne = true;
	var clearTime = null;
	var layoutWidth = 990;//页面宽度
	obj.css("left", Math.floor(($(window).width() - layoutWidth) / 2) + layoutWidth + 5 + "px");
	if ($.browser.msie && $.browser.version == '6.0') {
		obj.css("position", "absolute");
	} else {
		obj.css("top", $(window).height() - 260 + "px");
	}
	obj.click(function() {
		$(window).scrollTop(0);
	});
	$(window).scroll(function() {
		if ($(window).scrollTop() == 0) {
			obj.fadeOut();
			flag = true;
		} else if (flag == true) {
			flag = false;
			obj.fadeIn();
		} else if (onlyOne == true) {
			obj.fadeIn();
			onlyOne = false;
		}
		if ($.browser.msie && $.browser.version == '6.0') {
			obj.css('top', $(window).height() + $(window).scrollTop() - 260 + 'px');
			if (clearTime != null) {
				clearTimeout(clearTime);
				obj.css("display", "none");
			}
			if ($(window).scrollTop() > 0) clearTime = setTimeout("$('#go_top').fadeIn('10');", 20);
		}
	});
	$(window).resize(function() {
		if ($.browser.msie && $.browser.version == '6.0') {
			obj.css('top', $(window).height() + $(window).scrollTop() - 260 + 'px');
		} else {
			obj.css("top", $(window).height() - 260 + "px");
		}
		var HalfWidth = Math.floor(($(window).width() - layoutWidth) / 2);
		if (HalfWidth > 10) obj.css("left", HalfWidth + layoutWidth + 5 + "px");
	});
});

//==========================================================【小插件end】===============================================================//
