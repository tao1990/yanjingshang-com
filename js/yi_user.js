/* ======================================================================================================================
 * js：公共js文件，取代旧common.js【author:yijiangwen】【TIME:2012/9/24 15:24】
 * ======================================================================================================================
 * pre loaded yijq.js
 */
 
//document loading. 

	 
//document loaded.
$(document).ready(function(){
	/*----------------------------------------------------------------【页头脚本】----------------------------------------------------------------*/
	/*----------------------------------------------------------------【页头脚本end】-------------------------------------------------------------*/

	$(".rp_btn_1").click(function(){
		$(".pop_div").fadeIn();	
		$("#add_receipt").find('[name="act_type"]').val('add');
	});

	$(".pop_div_close, .p_d_bt_2").click(function(){
		$(".pop_div").fadeOut();
	});

	//check 验光单提交数据
	$(".p_d_bt_1").click(function(){
		var frm = $("#add_receipt");
		var receipt_type = frm.find('[name="receipt_type"]').val();
		var act_type     = frm.find('[name="act_type"]').val();
		var get_rec_id   = frm.find('[name="get_rec_id"]').val();

		if(receipt_type == 1)
		{
			frm = $("#ying_pop");
		}
		else if(receipt_type == 2)
		{
			frm = $("#kuang_pop");
		}
		
		var y_qiujin = frm.find('[name="yeye_qiujin"]').val(); 
		var z_qiujin = frm.find('[name="zeye_qiujin"]').val();

		var y_zhujin = frm.find('[name="yeye_zhujin"]').val(); 
		var z_zhujin = frm.find('[name="zeye_zhujin"]').val(); 
		var y_zhouwei = frm.find('[name="yeye_zhouwei"]').val(); 
		var z_zhouwei = frm.find('[name="zeye_zhouwei"]').val(); 
		var eye_tongju = frm.find('[name="eye_tongju"]').val(); 
		var user_id   = $("#get_user_id").val();
		
		err_msg = '';
		if(y_qiujin == '')
		{
			err_msg += "请选择右眼度数！\n";
		}
		if(z_qiujin == '')
		{
			err_msg += "请选择左眼度数！\n";
		}

		if(receipt_type == 2)
		{
			if(eye_tongju == '')
			{
				err_msg += "请选择双眼瞳距！\n";
			}
		}

		if(err_msg == '')
		{
			if('add' == act_type)
			{			
				//ajax submit form data, send data array to php.	
				$.ajax({
					type:'post',
					url: 'user.php?act=ajax_add_receipt',	
					data:'&user_id='+user_id+'&receipt_type='+receipt_type+'&yeye_qiujin='+y_qiujin+'&zeye_qiujin='+z_qiujin+
						 '&yeye_zhujin='+y_zhujin+'&zeye_zhujin='+z_zhujin+'&yeye_zhouwei='+y_zhouwei+'&zeye_zhouwei='+z_zhouwei+'&eye_tongju='+eye_tongju+'&m='+Math.random(),		
					cache:true,
					success:
					function(da)
					{
						da = eval('('+da+')');
						//alert(da.res);
						if(da.res == 'ok')
						{
							//alert('^_^ 您的验光单添加成功！');
							$(".pop_div").fadeOut();

							var dd = $(".r_tbody").last().html();
							dd = '<dl class="r_tbody">'+dd+'</dl>';
							$(".rp_table").append(dd);

							var lasttr = $(".r_tbody").last();
							lasttr.find(".r_td2:eq(0)").text(da.receipt_type);
							lasttr.find(".r_td3 .td_t1").text(da.yeye_qiujin);
							lasttr.find(".r_td3 .td_t2").text(da.zeye_qiujin);
							lasttr.find(".r_td4").text(da.eye_tongju);
							lasttr.find(".r_td5:eq(0) .td_t1").text(da.yeye_zhujin);
							lasttr.find(".r_td5:eq(0) .td_t2").text(da.zeye_zhujin);
							lasttr.find(".r_td5:eq(1) .td_t1").text(da.yeye_zhouwei);
							lasttr.find(".r_td5:eq(1) .td_t2").text(da.zeye_zhouwei);
							lasttr.find(".r_td7 dd a:eq(0)").attr('href','javascript:show_receipt('+da.list_id+')');
							lasttr.find(".r_td7 dd a:eq(1)").attr('href','javascript:edit_receipt('+da.list_id+')');
							lasttr.find(".r_td7 dd a:eq(2)").attr("href","javascript:if(confirm('您确实要删除该验光单吗？'))location.href='user.php?act=remove_receipt&rec_id="+da.list_id+"'");
						}
						else if(da.res == 'fail')
						{
							alert('很抱歉，验光单添加失败，请检查后重新提交！');
						}
						else
						{
							//todo
						}
					}					
				});//ajax end

			}
			else if('edit' == act_type)
			{
				//ajax submit form data, send data array to php.	
				$.ajax({
					type:'post',
					url: 'user.php?act=ajax_edit_receipt',	
					data:'&rec_id='+get_rec_id+'&receipt_type='+receipt_type+'&yeye_qiujin='+y_qiujin+'&zeye_qiujin='+z_qiujin+
						 '&yeye_zhujin='+y_zhujin+'&zeye_zhujin='+z_zhujin+'&yeye_zhouwei='+y_zhouwei+'&zeye_zhouwei='+z_zhouwei+'&eye_tongju='+eye_tongju+'&m='+Math.random(),		
					cache:true,
					success:
					function(da)
					{
						da = eval('('+da+')');
						//alert(da.res);
						if(da.res == 'ok')
						{
							//alert('^_^ 您的验光单添加成功！');
							$(".pop_div").fadeOut();
							location.reload();
							/*
							var dd = $(".r_tbody").last().html();
							dd = '<dl class="r_tbody">'+dd+'</dl>';
							$(".rp_table").append(dd);

							var lasttr = $(".r_tbody").last();
							lasttr.find(".r_td2:eq(0)").text(da.receipt_type);
							lasttr.find(".r_td3 .td_t1").text(da.yeye_qiujin);
							lasttr.find(".r_td3 .td_t2").text(da.zeye_qiujin);
							lasttr.find(".r_td4").text(da.eye_tongju);
							lasttr.find(".r_td5:eq(0) .td_t1").text(da.yeye_zhujin);
							lasttr.find(".r_td5:eq(0) .td_t2").text(da.zeye_zhujin);
							lasttr.find(".r_td5:eq(1) .td_t1").text(da.yeye_zhouwei);
							lasttr.find(".r_td5:eq(1) .td_t2").text(da.zeye_zhouwei);
							lasttr.find(".r_td7 dd a:eq(0)").attr('href','javascript:show_receipt('+da.list_id+')');
							lasttr.find(".r_td7 dd a:eq(1)").attr('href','javascript:edit_receipt('+da.list_id+')');
							lasttr.find(".r_td7 dd a:eq(2)").attr("href","javascript:if(confirm('您确实要删除该验光单吗？'))location.href='user.php?act=remove_receipt&rec_id="+da.list_id+"'");
							*/
						}
						else if(da.res == 'fail')
						{
							alert('很抱歉，验光单添加失败，请检查后重新提交！');
						}
						else
						{
							//todo
						}
					}					
				});//ajax end				
			}
		}
		else
		{
			alert(err_msg);
			return false;			
		}
	});

});



/* ----------------------------------------------------------------------------------------------------------------------
 * yi:用户未登录提示
 * ----------------------------------------------------------------------------------------------------------------------
 */
function no_login_msg(msg)
{
	if(msg == '')
	{
		msg = '^_^ 您还未登录，请先登录！';
	}
	alert(msg);
    location.href = 'user.html';
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:用户切换验光单类型
 * ----------------------------------------------------------------------------------------------------------------------
 */
function sel_cp_type(val)
{
	if(val == '')
	{
		return false;
	}

	if(val == 1)
	{		
		document.getElementById("ying_text").style.display = '';
		document.getElementById("kuang_text").style.display = 'none';
		document.getElementById("ying_pop").style.display = '';
		document.getElementById("kuang_pop").style.display = 'none';
	}
	else if(val == 2)
	{		
		document.getElementById("ying_text").style.display = 'none';
		document.getElementById("kuang_text").style.display = '';
		document.getElementById("ying_pop").style.display = 'none';
		document.getElementById("kuang_pop").style.display = '';
	}
	else
	{
	}
}