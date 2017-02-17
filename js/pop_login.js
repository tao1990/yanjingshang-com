
$(document).ready(function(){
    

	/* ------------------------------------------
	 * 登录过程中：验证会员名不能为空。
	 * ------------------------------------------
	 */		
	$("#aj_lg_name").blur(function(){
		if($("#aj_lg_name").val() == '')
		{
			$("#aj_lg_name").css("border", "1px red solid").parent().next().text("用户名不能为空");
		}
	}).focus(function(){
		$("#aj_lg_name").css("border", "1px solid gainsboro").parent().next().text("");
		if($(".aj_lg_error").text() != '')
		{
			$(".aj_lg_error").text("");
		}
	});	
	
	/* ------------------------------------------
	 * 验证用户注册信息
	 * ------------------------------------------
	 */	
	var obj_u   	= $("#reg_ul").find("input:eq(0)");
	var obj_pwd 	= $("#reg_ul").find("input:eq(1)");
	var obj_pwd2	= $("#reg_ul").find("input:eq(2)");
	var obj_email	= $("#reg_ul").find("input:eq(3)");
	var obj_mobile 	= $("#reg_ul").find("input:eq(4)");
		
	obj_u.blur(function(){
		p_ck_user_name($(this).val());
	}).focus(function(){
		p_ts_clear("#username_notice");		
	});	
	
	obj_pwd.blur(function(){
		p_ck_pwd($(this).val());
	}).focus(function(){	
		p_ts_clear("#password_notice");
	});	
	
	obj_pwd2.blur(function(){
		p_ck_pwd2($(this).val());
	}).focus(function(){
		p_ts_clear("#conform_password_notice");
	});	
	
	obj_email.blur(function(){
		p_ck_email($(this).val());
	}).focus(function(){	
		p_ts_clear("#email_notice");
	});	
	
	obj_mobile.blur(function(){
		p_ck_mobile($(this).val());	
	}).focus(function(){	
		p_ts_clear("#mobile_notice");	
	});	
	
	/* ------------------------------------------
	 * 注册新用户
	 * ------------------------------------------
	 */
	$("#regist_bt").click(function(){
		var user_name = obj_u.val();		
		var r_pwd     = obj_pwd.val();
		var r_pwd2    = obj_pwd2.val();
		var r_email   = obj_email.val();
		var r_mobile  = obj_mobile.val();
		
		//验证一遍用户提交的数据。
		p_ck_user_name(user_name);
		p_ck_pwd(r_pwd);
		p_ck_pwd2(r_pwd2);
		p_ck_email(r_email);
		p_ck_mobile(r_mobile);	

		if(user_name!='' && r_email!='' && r_pwd!='' && r_pwd2!='' && (r_pwd==r_pwd2) && ($("#u_can_reg").val()==1) && ($("#e_can_reg").val()==1))
		{
			$("#u_can_reg").val(0); $("#e_can_reg").val(0);
			var url = "user.html?act=ajax_register&username="+encodeURIComponent(user_name)+"&password="+encodeURIComponent(r_pwd)+"&email="+encodeURIComponent(r_email)+"&extend_field5="+encodeURIComponent(r_mobile);
			$.ajax({
				type:'post',
				url: url,	
				data:'&m='+Math.random(),		
				cache:true,
				success:
				function(res)
				{
					var da = res.split(",");
					if(da[0] == 'true')
					{	
						document.location.reload();				
					}
					else
					{
						//注册失败，给出提示
						alert('很抱歉！您刚刚的注册失败。');
					}					
				}
			});		
		}
		else
		{
			//alert('no, uname=');已经注册过了，或者注册信息不正确。防止重复注册。
		}		
	});
    
    
});

/* -------------------------------------
 * yi: 立即注册按钮
 * -------------------------------------
 */
function bt_to_reg()
{
	var user_id = $("#get_user_id").val();	
	if(user_id == 0)
	{
		pop_login();
		change_lg_pan(1);	
	}
	else
	{
		com_message('您已经登录，祝您玩得愉快！');
	}
}

/* -------------------------------------
 * yi: 公共消息提示窗口,取代alert().
 * -------------------------------------
 * txt:要提示的内容。内容控制在32个汉字内。作2行显示。
 */
function com_message(txt)
{
	pop_window("com_message_pan");
	$("#com_msg_pan").text(txt);
}

/* -------------------------------------
 * yi: 验证邮箱的功能
 * -------------------------------------
 */
function fn_ck_email()
{	
	//更换成一个邮件是否发送成功。
	$("#email_ck").fadeOut();
	pop_window("ask_email_ck");		
	$.ajax({
		type:'get',
		url: 'user.php?act=send_hash_mail',	
		data:'m='+Math.random(),		
		cache:true,
		success:
		function(da)
		{		
		}
	});
}

function re_ck_email()
{
	pop_pan_close("ask_email_ck");
	pop_window("email_ck");
}


/* -------------------------------------
 * yi: 登录模块
 * -------------------------------------
 */ 
function ajax_login()
{
	var user_name = $("#aj_lg_name").val();
	var password  = $("#aj_lg_pwd").val();
	var auto_login= $("#aj_lg_auto").attr("checked");
	if(user_name!='' && password !='')
	{
		var url = 'user.html?act=ajax_login&user_name='+encodeURIComponent(user_name)+'&password='+encodeURIComponent(password)+'&auto_login='+auto_login;			
		$.ajax({			
			type:'post',
			url: url,	
			data:'&m='+Math.random(),		
			cache:true,
			success:
			function(res)
			{
				var da = res.split(",");
				if('login_ok' == da[0])
				{	
					document.location.reload();			
				}
				else if('login_fail' == da[0])
				{
					$(".aj_lg_error").text("您输入的用户名或密码错误，请重新输入！");
				}
				else
				{
					$(".aj_lg_error").text("您输入的用户名不存在，请重新输入！");//用户信息不能为空。重新登录
				}
			}			
		});	
	}
	else
	{
		alert('登录用户名和密码不能为空，请输入正确登录信息！');
	}
}
/* -------------------------------------
 * yi: 登录和注册面板切换
 * -------------------------------------
 */ 
function change_lg_pan(type)
{
	if(type == 1)
	{
		$(".lg_content").eq(0).hide().next().show();
		$(".lg_rg_title").text('注册易视会员');
	}
	else
	{
		$(".lg_content").eq(1).hide().prev().show();
		$(".lg_rg_title").text('登录易视网');		
	}
}


/* -------------------------------------
 * yi: 弹出用户登录的窗口（淡出的效果）
 * -------------------------------------
 */ 
function pop_login()
{
	change_lg_pan(2);
	
	$("#lg_pan").fadeIn();
	pop_window_center("#lg_pan");	
	$(".pop_shadow").fadeTo("slow", 0.5);			
}
/* -------------------------------------
 * yi: 设置弹窗的位置为页面居中
 * -------------------------------------
 */
function pop_window_center(pan_id) 
{
	var f_left = ($(window).width()-$(pan_id).width())/2;
	var f_top  = ($(window).height()-$(pan_id).height())/2;	
	$(pan_id).css("left", f_left).css("top", f_top);	
}
/* -------------------------------------
 * yi: 通用弹出窗口（淡出的效果）
 * -------------------------------------
 */
function pop_window(ipan_id)
{
	var pan_id = ('#' == ipan_id.charAt(0))? ipan_id: "#"+ipan_id;	
	$(pan_id).fadeIn();
	pop_window_center(pan_id);	
	$(".pop_shadow").fadeTo("slow", 0.5);			
}
/* -------------------------------------
 * yi: 弹出手机验证窗口（拉伸的效果）
 * -------------------------------------
 */ 
function pop_mb_ck()
{
	var mb_width  = $("#mb_ck").width();
	var mb_height = $("#mb_ck").height();
	pop_window_center("#mb_ck");
	$("#mb_ck").delay(600).css({
			"display":"block",
			"borderWidth":"0",
			"width":"0",
			"height":"0"
		}).animate({
			"borderWidth":"4px",
			"width":mb_width,
			"height":mb_height			
		}, 500);	
		
	$(".pop_shadow").fadeTo("slow", 0.5);		
		
}

function pop_email_ck()
{
	
	/**/
	var mb_width  = $("#email_ck").width();
	var mb_height = $("#email_ck").height();
	pop_window_center("#email_ck");
	$("#email_ck").delay(600).css({
			"display":"block",
			"borderWidth":"0",
			"width":"0",
			"height":"0"
		}).animate({
			"borderWidth":"4px",
			"width":mb_width,
			"height":mb_height			
		}, 500);	
	/**/
	//window.location.href = window.location.href;
	//$("#email_ck").fadeIn();
	$(".pop_shadow").fadeTo("slow", 0.5);		
		
}
/* -------------------------------------
 * yi: 通用弹出窗口关闭（淡出的效果）
 * -------------------------------------
 */
function pop_pan_close(pan_id)
{
	$("#"+pan_id).fadeOut();
	$(".pop_shadow").fadeOut();
}
/* -------------------------------------
 * yi: 登录窗口关闭（淡出的效果）
 * -------------------------------------
 */
function lg_pan_close()
{
	$("#lg_pan").fadeOut();
	$(".pop_shadow").fadeOut();
}
/* -------------------------------------
 * yi: 登录窗口关闭（向右上角缩小的动画效果）
 * -------------------------------------
 */
function lg_pan_close_xg2()
{
	$("#lg_pan").animate({
			borderWidth:"0",
			width:"0",
			height:"0"
		}, 600);
	$(".pop_shadow").fadeOut();		
}

function user_rg()
{
	var lg_form   = document.getElementById('regist_form');	
	lg_form.submit(true);	
}
function user_lg()
{
	var lg_form   = document.getElementById('login_form');
	lg_form.submit(true);
}


/*
 * 验证用户信息情况
 */
function p_ck_user_name(vl)
{
	var rg_user_name = vl;
	var rg_msg       = '';
	var rg_un_len    = rg_user_name.replace(/[^\x00-\xff]/g, "**").length;

	if(rg_user_name == '')
	{
		rg_msg = "用户名不能为空";
	}		
	else if(rg_un_len < 5)
	{
		rg_msg = "不能少于5个字符";
	}
	else if(rg_un_len > 20)
	{
		rg_msg = "不能多于20字符";
	}
	else
	{
		var url = "user.html?act=ck_user_name&username="+encodeURIComponent(rg_user_name);
		$.ajax({
			type:'post',
			url: url,	
			data:'&m='+Math.random(),		
			cache:true,
			success:
			function(res)
			{
				if(res == 'false')
				{			
					$("#username_notice").removeClass("ts_ok").text("用户名已被注册").prev().children().css("background-color", "#fcc");
					$("#u_can_reg").val(0);
				}
				else
				{
					$("#username_notice").addClass("ts_ok").text("可以注册");
					$("#u_can_reg").val(1);
				}
			}
		});
	}	
	if(rg_msg != '')
	{
		$("#username_notice").text(rg_msg).prev().children().css("background-color", "#fcc");
	}	
}
/*
 * 验证用户邮箱
 */
function p_ck_email(vl)
{
	var rg_email = vl;
	var rg_msg   = '';
	if(rg_email == '')
	{
		rg_msg = "注册邮箱不能为空";
	}		
	else if(!(/([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/.test(rg_email)))
	{
		rg_msg = "邮箱格式错误！";
	}
	else
	{			
		var url = "user.html?act=check_email&email="+rg_email;
		$.ajax({
			type:'post',
			url: url,	
			data:'&m='+Math.random(),		
			cache:true,
			success:
			function(res)
			{
				if(res == 'false')
				{			
					$("#email_notice").removeClass("ts_ok").text("邮箱已被注册").prev().children().css("background-color", "#fcc");
					$("#e_can_reg").val(0);
				}
				else
				{
					$("#email_notice").addClass("ts_ok").text("可以注册");
					$("#e_can_reg").val(1);
				}
			}
		});			
	}				
	if(rg_msg != '')
	{
		$("#email_notice").text(rg_msg).prev().children().css("background-color", "#fcc");
	}	
}
/*
 * 验证用户密码
 */
function p_ck_pwd(vl)
{
	var rg_pwd       = vl;
	var rg_msg       = '';
	var rg_pwd_len   = rg_pwd.replace(/[^\x00-\xff]/g, "**").length;
	if(rg_pwd == '')
	{
		rg_msg = "密码不能为空";
	}		
	else if(rg_pwd_len < 6)
	{
		rg_msg = "密码不能少于6位";
	}
	else
	{
		$("#password_notice").addClass("ts_ok").text("可以注册");
	}				
	if(rg_msg != '')
	{
		$("#password_notice").text(rg_msg).prev().children().css("background-color", "#fcc");
	}	
}
function p_ck_pwd2(vl)
{
	var rg_pwd2 = vl;
	var rg_msg  = '';	
	var old_pwd = $("#password1").val();
	if(rg_pwd2 == '')
	{
		rg_msg = "请再次输入密码！";
	}		
	else if(rg_pwd2 != old_pwd)
	{
		rg_msg = "输入的密码不一致";
	}
	else
	{
		$("#conform_password_notice").addClass("ts_ok").text("可以注册");
	}			
	if(rg_msg != '')
	{
		$("#conform_password_notice").text(rg_msg).prev().children().css("background-color", "#fcc");
	}	
}
function p_ck_mobile(vl)
{
	var rg_mobile = vl;
	var rg_msg    = '';	
	if(rg_mobile == '')
	{
		rg_msg = "手机不能为空！";
	}
	else if(rg_mobile.length != 11)
	{
		rg_msg = "手机格式错误！";
	}
	else
	{
		$("#mobile_notice").addClass("ts_ok").text("可以注册");
	}	
	if(rg_msg != '')
	{
		$("#mobile_notice").text(rg_msg).prev().children().css("background-color", "#fcc");
	}	
}
//yi:提示清除
function p_ts_clear(pan_id)
{
	$(pan_id).text('').removeClass("ts_ok").prev().children().css("background-color", "#fff");	
}

