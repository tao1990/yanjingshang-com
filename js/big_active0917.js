/* =======================================================================================================================
 * 商城大型活动页面脚本 【20120817】【Author:yijiangwen】【同步TIME:20120917】
 * =======================================================================================================================
 */
$(document).ready(function(){	
	
	//var user_id = $("#get_user_id").val();
	//if(user_id==0)
	//{
		//alert('很抱歉，您还未登录，请先登录！');
		//location.href = 'user.html';		
	//}
	
	//alert('5');
	//var dd = $("#sinaframe").attr("width");
	//alert(dd);
	/*
	$("#sinaframe").load(function(){
		var d = $('#sinaframe').contents().find("#sina").html();
		alert(d);    
	});*/
	
	
	//var tt = $("#sinaframe",document.frames('sina').document).html();
	//alert(tt);
	
	//var ds = $("iframe").contents()
	
	//$("iframe").contents().find("body").append("I'm in an iframe!");
	
	//ifr = document.getElementById("#sinaframe"); 
	//var dt = iframe.contentWindow.document.getElementById(id).style.color;
	//alert(ifr);
	
});


//提交问题表单
function sub_answer()
{
	//先验证用户登录。
	var user_id = $("#get_user_id").val();
	if(user_id==0)
	{
		alert('很抱歉，您还未登录，请先登录！');
		location.href = 'user.html';		
	}
	else
	{	
		var answer1 = 0;
		var answer2 = 0;
		$(':input:radio[name="answer1"]').each(function(i){
			if($(this).attr('checked'))
			{
				answer1 = i+1;
			}
		});
		
		$(':input:radio[name="answer2"]').each(function(i){
			if($(this).attr('checked'))
			{
				answer2 = i+1;
			}
		});

		var msg = "请回答";
		if(answer1==0)
		{
			msg += "问题一";
		}
		if(answer2==0)
		{
			if(answer1==0)
			{
				msg += ",";
			}
			msg += "问题二";
		}
		msg += "!";
		
		if(msg!="请回答!")
		{
			alert(msg);
			return;
		}
		else
		{
			var an_res1 = $("#an_res1").val();
			var an_res2 = $("#an_res2").val();
			
			//提交问题答案，判断答案
			if(answer1==an_res1 && answer2==an_res2)
			{
				//给顾客发放红包
				get_bonus(198);//72				
			}
			else
			{
				//回答错误
				alert("-_- 对不起，您的回答错误，请再接再励！");
				return;	
			}	
		}
	}
}


//点击免费获取红包
//bonus_type_id: 红包类型id。
function get_bonus(bonus_type_id)
{
	var user_id = $('#get_user_id').val();
	
	//验证用户是否登录
	if(user_id < 1)
	{
		alert('^_^ 对不起，您还未登录，请您先登录后才能领取红包！');
		return false;
	}
	
	//验证红包类型不能为空
	if(bonus_type_id < 1)
	{
		alert('^_^ 对不起，红包发放失败，请稍后再试！');
		return false;			
	}
	
	//alert(bonus_type_id);		
	//自动领取红包
	$.ajax({
		type:'POST',
		url:'ajax_step.php?act=send_bonus_no_limit',	
		data:'&user_id='+user_id+'&bonus_type_id='+bonus_type_id+'&m='+Math.random(),		
		cache:false,
		success:
		function(dd){
			var dd = eval('('+dd+')');
			var code = dd['info_code'];
			var msg  = dd['info_msg'];
	
			if(code==1){
								
				//红包领取成功，更新面板数据
				
				/*
				var day  = $('#sign_day_num').text();
				var t_day= parseInt(day)+1;
				$('#sign_day_num').text(t_day);	
				//积分
				var jifen   = $('#sign_integral').text();
				var t_jifen = parseInt(jifen)+1;
				$('#sign_integral').text(t_jifen);				
				//总签到人数
				var sign_sum  = $('#sign_sum').text();
				var tsign_sum = parseInt(sign_sum)+1;
				$('#sign_sum').text(tsign_sum);	
				
				//显示信息
				alert(dd['info_msg']);		
				*/	
				alert(msg);
				window.location.href = window.location.href;
			}else{
				//红包领取失败
				alert(msg);
			}
		}
	});	
}




/*
$(document).ready( function () {
 $("form").cssRadio();
});

//radio按钮效果重写
jQuery.fn.cssRadio = function(){
	var context = this;
               
	jQuery("input[@type='radio']", this)
   .each(function(){
	   if(jQuery(this).prev()[0].checked)
	   {
	   		jQuery(this).addClass("checked");
	   }
   })
  .hover(
   		function() { $(this).addClass("over"); },
   		function() { $(this).removeClass("over"); }
   )
  .click( function() {
   var contents = $(this).parent().parent();
   jQuery("input[@type='radio']", contents)
    .each( function() {
     jQuery(this)
      .removeClass()
      .prev()[0].checked = false;
    });
   jQuery(this)
    .addClass("checked")
    .prev()[0].checked = true;
   })
  .prev().hide();
}
*/
//=============================================================================【函数】=============================================================================//
