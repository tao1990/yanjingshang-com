/*---------------------------------------------------购物车流程  2011-5-3 author:yijiangwen----------------------------------------------------------------*/

$(document).ready(function(){	
	
	//单击银行图标自动选中银行
	$('.pay_online_ul img').click(function(){
		$(this).parent().prev('.online_tr_li').find('input').attr('checked','checked');
	});

	//------------------------------------------------初始进入购物车页面【初始化处理】----------------------------------||
	//如果用户初次进入到购物车页面   余额使用金额数字初始为0
	if($('#ECS_SURPLUS').val()>0 && $('#tb_now_pay tr').eq(0).find('td').eq(1).text()!="余额支付"){
		$('#ECS_SURPLUS').val(0);
		changeSurplus(0);//余额使用清零函数
	}
	//----------------------------------------------------------------------------------------------------------------||
	
	
   	/*================================================================================================================*/
	//单击使用红包 ||使用余额按钮后的样式切换
	/*----------------------------------------------------------------------------------------------------------------*/
	$('#use_redbag').click(function(){
		$(this).toggleClass("redbg2").next('div').toggle(200).next().toggle(200);
	});	
	$('#use_yue').click(function(){
		$(this).toggleClass("redbg2").next('div').toggle(200);
	});	
	/*============================================end================================================================*/
	
			 
   	/*============================================【填写】收货人信息板块===============================================*/
   	
	//添加到常用地址的【自动填充地址功能】	
	$('#tb_usually input[name="usual_addres"]').bind("click",function(){
		//=============================获得选中的收获人地址行=======================//
		var consignee = $(this).parent().next().text();
		var pro       = $(this).nextAll('input[name="hide_pro"]').val();
		var city      = $(this).nextAll('input[name="hide_city"]').val();
		var dist      = $(this).nextAll('input[name="hide_dist"]').val();
		var address   = $(this).nextAll('input[name="hide_address"]').val();				
		var email     = $(this).nextAll('input[name="hide_email"]').val();
		var tel       = $(this).nextAll('input[name="hide_tel"]').val();
		//=============================数据获得结束================================//
				
		//----------------【单击地址事件】 更新收货人信息中地址数据------------------//
		$('#tb_addres input[id="consignee"]').val(consignee);
		$('#tb_addres input[id="address"]').val(address);		
		$('#tb_addres input[id="email"]').val(email);
		$('#tb_addres input[id="tel"]').val(tel);		
		//更新省
		$('#selProvinces').val(pro);
		
		//更新市
		$.ajax({
			url:'flow_ajax.php?action=get_city',
			data:'&pro='+pro+'&city='+city+'&m='+Math.random(),
			type:'POST',
			cache:false,
			success:
			function(option){
				if(option !=''){ $('#selCities').html(option);}
			}
		});
		
		//更新区
		$.ajax({
			url:'flow_ajax.php?action=get_dist',
			data:'&city='+city+'&dist='+dist+'&m='+Math.random(),
			type:'POST',
			cache:false,
			success:
			function(option){
				if(option !=''){ $('#selDistricts').html(option);}
			}
		});	
		//----------------【单击地址事件】 更新收货人信息中地址数据【end】---------------//
	});	

	//添加到常用地址的【删除常用地址项功能】
	$('#tb_usually td[class="delete_address"]').live("click",function(){
		var address_id = $(this).prev().prev().prev().find('input').eq(1).val();
		var address_tr = $(this).parent();		
		$.ajax({
			url:'flow_ajax.php?action=delete_address',
			data:'&address_id='+address_id+'&m='+Math.random(),
			type:'POST',
			cache:false,
			success:
			function(res){
				if(res == 'success'){
					//在前端删除选中的该地址行
					address_tr.remove();
					
					//如果没有地址行了，则隐藏整个【常用地址列表】
					var size = $('#tb_usually input[name="usual_addres"]').size();
					if(size == 0){ $('#div_usually_address').css("display","none"); }															
				}
			}
		});
	});  

	/*==========================================【提交表单】保存 收货人地址信息==================================*/
	$("#addres_form").ajaxForm({
		beforeSubmit: valid_add,
		success:
		function(res){			
		    if($("#div_addres").eq(0).css("display") == 'none' ){		    			
				$("#now_addres").hide();
				$("#div_addres").show();
			}else{
				//收货人地址从灰色面板切换到白色面板	
				$("#div_addres").hide();	
				$("#now_addres").show();
			}
			
			//【记录】用户选择的收货人城市
			var s_city = $("#selCities option[selected='true']").text(); 			
			
			//整理好详细地址
			var add = $("#selProvinces option:selected").text()+' '+s_city+' '+$("#selDistricts option:selected").text()+' '+$("#address").val();			
			
			//白色结果面板【显示收货人地址信息】
			var s_add = $("#tb_now_addres td[align='left']");
			s_add.eq(0).text(add);
			s_add.eq(1).text($("#consignee").val());
			s_add.eq(2).text($("#email").val());
			s_add.eq(3).text($("#tel").val());
			
			//【如果用户没有选择支付方式和配送方式】自动展示支付和配送方式面板	
			if($("#tb_now_pay td[align='left']").eq(0).text() == ''){				
				$("#now_pay").hide();
				$("#div_pay").show();
			}
			
			//==========================================【根据收货人地址的变更而变更运费，动态加载配送方式】==========================================||
			var pro  = $("#selProvinces").val();
			var city = $("#selCities").val();
			var dist = $("#selDistricts").val();
			var type = $("#flow_type").val();
			//=====================【清空以前配送方式，加载最新的配送方式到前端】==============================||
			$.ajax({
				url:'flow_ajax.php?action=shipping',
				data:'&pro='+pro+'&city='+city+'&dist='+dist+'&flow_type='+type+'&m='+Math.random(),							
				cache:false,					
				success:
				function(dd){
					
					//【dd】服务器端返回新的配送方式数据
					dd = eval('('+dd+')');
					var len = dd.length;
					
					//清空以前的配送方式
					$("#tb_shipping_list tr").detach();	
					
					//根据返回结果创建新的配送方式
					$.each(dd,function(i){						
						var aa = '<tr><td><input type="radio" name="shipping" value="'+dd[i].shipping_id+'" {if $order.shipping_id eq '+dd[i].shipping_id+'}checked="true"{/if} supportCod="'+dd[i].support_cod+'"';
						var bb = 'insure="'+dd[i].insure+'" onclick="selectShipping(this)" /></td><td align="left">'+dd[i].shipping_name+'</td><td class="td_shipping_desc">'+dd[i].shipping_desc+'</td>';
						var cc = '<td class="td_shipping_fee">'+dd[i].format_shipping_fee+'</td></tr>';
						var tr = aa+bb+cc;
						$("#tb_shipping_list").append(tr);												
					});					
					
					//====================【支付方式中选择了货到付款，配送方式自动选择货到付款】====================||
					var iscod  = $("#pay_form input[isCod='1']").attr("checked");
					var shp_li = $('#pay_form input[name="shipping"][supportCod="1"]');
					if(iscod){										
						//配送方式中只显示活到付款且选中【隐藏非货到付款配送方式】
						shp_li.attr("checked","true").parent().parent().siblings().hide();											
					}else{
						//否则隐藏货到付款配送方式【自动选中普通快递的配送方式】
						shp_li.parent().parent().hide();
						$('#pay_form input[name="shipping"][supportCod="0"]').first().click();
					}
					//===========================================================================================||
				}
			});
			//==========================================【根据收货人地址的变更而变更运费，动态加载配送方式】end==========================================||
			
			
			//===============================================保存地址时如果是新的地址 【更新前端常用地址列表】====================================||		
			if(res > 0){
				//获取客户填写的收货人信息【res:服务器端返回刚刚更新地址的id】
				var con     = $('#addres_form input[name="consignee"]').val();
				var pro     = $('#addres_form select[name="province"]').val();
				var city    = $('#addres_form select[name="city"]').val();
				var dict    = $('#addres_form select[name="district"]').val();
				var address = $('#addres_form input[name="address"]').val();
				var email   = $('#addres_form input[name="email"]').val();
				var tel     = $('#addres_form input[name="tel"]').val();
				var user_id = $('#hide_user_id').val();	
				
				var pro_name  = $('#selProvinces option[value="'+pro+'"]').text();
				var city_name = $('#selCities option[value="'+  city+'"]').text();
				var dict_name = $('#selDistricts option[value="'+dict+'"]').text();					
				
				//常用地址列表没有显示出来【则清空常用地址列表中原先空白行】
				if( $('#div_usually_address').css("display")=="none"){
					if( $('#tb_usually tr').size()>0){ $('#tb_usually tr').remove();}
				}
				
				//取消其它常用地址行的选中状态
				$('#tb_usually input[name="usual_addres"][checked="true"]').removeAttr('checked');	
				
				//常用地址列表中增加用户新填写的一条数据且选中该数据
				var address_tr = '<tr><td><input type="radio" checked="checked" name="usual_addres"/>';								
				address_tr += '<input type="hidden" name="hide_address_id" value="'+res+'"/>';
				address_tr += '<input type="hidden" name="hide_pro"        value="'+pro+'"/>';
				address_tr += '<input type="hidden" name="hide_city"       value="'+city+'"/>';
				address_tr += '<input type="hidden" name="hide_dist"       value="'+dict+'"/>';
				address_tr += '<input type="hidden" name="hide_address"    value="'+address+'"/>';
				address_tr += '<input type="hidden" name="hide_email"      value="'+email+'" />';
				address_tr += '<input type="hidden" name="hide_tel"        value="'+tel+'" />';
				address_tr += '</td><td class="ta_l ti_12">'+con+'</td>';
				address_tr += '<td class="ta_l">'+pro_name+' '+city_name+' '+dict_name+' '+address+'</td><td class="delete_address">[删除]</td></tr>';
				$('#tb_usually').append(address_tr);	
			}
			//===============================================保存地址时如果是地址 【更新前端常用地址列表】end=================================||
		}	
	});	
	/*====================================================【填写】收货人信息板块end==============================================================*/
 
 
	/*====================================================【选择】支付方式和配送方式=============================================================*/	
	//----------------------------------------------------选中或更改【支付方式】---------------------------------------||
	$('#pay_form input[name="payment"]').click(function(){		
		//选中【在线支付】 
		if($(this).val()== 0){
			$('.online_tb_tr').show();
		}else{
			$('.online_tb_tr').hide();
		}

		//选中【银行汇款 】
		if($(this).val()== 2){
			$('.bank_li_tr').show();
		}else{
			$('.bank_li_tr').hide();
		}	
		
		var iscod = $(this).attr('isCod');		
		if(iscod == 1){	
			//支付方式选中【货到付款】 配送方式中选中【货到付款】
			var shp_cod = $('#pay_form input[name="shipping"][supportCod="1"]');
			shp_cod.parent().parent().show().siblings().hide();			
			shp_cod.attr("checked","checked").trigger("click");
		}else{
			//支付式选中【非货到付款】隐藏货到付款配送方式显示非货到付款方式   自动选中【快递】
			$('#pay_form input[name="shipping"][supportCod="1"]').parent().parent().hide().siblings().show();
			var shp_cn = $('#pay_form input[name="shipping"][supportCod="0"]');
			if(shp_cn.size()>0){ shp_cn.first().attr("checked","checked").trigger("click");}			
		}		
	});	
	//---------------------------------------------------------------------------------------------------------------||
	
	
	//----------------------------------保存支付方式和配送方式信息【提交表单】------------------------------------------||
	$("#pay_form").ajaxForm({		
		beforeSubmit: function(){
			//---------------提交表单前 对支付方式 和 配送方式选择的验证-------------------------||			
						
			//初始化支付方式和配送方式都没选择
			var ipayment  = false;
			var ishipping = false;	
			var ck_pay = $('#pay_form input[name="payment"][type="radio"]:checked');
			var ck_shp = $('#pay_form input[name="shipping"][type="radio"]:checked');
			var cv_pay = ck_pay.val();	
			var cv_shp = ck_shp.val();		
			
			if(typeof cv_pay != "undefined"){ ipayment = true; }
			if(typeof cv_shp != "undefined"){ ishipping = true;}		
		
			if(ipayment == false||ishipping == false){
				alert('请选择好支付方式 和 配送方式!');
				return false;				
			}else{							
				//选择的支付方式是非货到付款 【验证选择的配送方式】
				if(ck_pay.parent().next().text() != '货到付款'){
					
					//【以前选择的货到付款】
					if( cv_shp == 8){
						alert('请您选择配送方式!'); return false;						
					}else{
						//【配送方式也是非货到付款（包括余额支付）】
					
						//------------------------------------【如果用户支付方式是余额支付】-----------------------------------||				
						if(ck_pay.parent().next().text() == '余额支付'){													
							var surplus_ok = false;//标记是否可用余额支付
							var surplus    = 0;
							/*=====================================控制 能否使用余额支付================================*/
							$('#pay_form input[name="shipping"][type="radio"]').each(function(){								
								//遍历所有的配送方式 【找到已经选择的配送方式判断是否可用余额支付】															
								if($(this).attr('checked') == true){
									var payables = $('#ECS_ORDERTOTAL span[id="payables"]').text();
									payables = payables.substring(1);
									surplus  = $('#pay_form input[type="hidden"][name="user_surplus"]').val();
									if( Number(surplus) >= Number(payables)){
										surplus_ok = true;
									}									
								}
							});
							/*======================================================================================*/
							if(surplus_ok){
								return true;
							}else{
								alert('您的余额为:'+surplus+',不足以支付该订单,请您选择其它支付方式.');
								return false;
							}							
						}else{
							//非余额支付
							return true;
						}
					}		
				}else{
					//选中支付方式是货到付款 【配送方式自动会选择为货到付款】
					return true;
				}								
			}								
		},
		success:
		function(){
			//-----------------------【支付方式和配送方式表单提交成功】在白色面板显示选择数据-----------------------------------||
			//切换面板
			add_pay();
			
			//用户选择的支付方式名配送方式名 显示在白色面板中
			var v_pay    = $('#pay_form input[name="payment"]:checked');
			var v_shp    = $('#pay_form input[name="shipping"]:checked');
			var show_pay = $('#tb_now_pay td[align="left"]');
			
			var pay_id   = v_pay.val();
			var shp_id   = v_shp.val();			
			var payment  = v_pay.parent().next().text();	
			var shipping = v_shp.parent().next().text();		

						
			//==============================【选中支付方式为余额支付】=============================//
			if(payment == '余额支付'){
				var payables = $('#ECS_ORDERTOTAL span[id="payables"]').text().substring(1);				
								
				//只有在以前没有选余额支付 时才进行此步骤
				if($('#ECS_SURPLUS').attr("disabled") != "disabled" || payables==0){
					//使用余额支付金额数目||更改余额input设置为不可用	
					$('#ECS_SURPLUS').val(payables).attr('disabled','disabled');				
					
					//余额面板自动展开
					if($('#use_yue').next().css("display")=='none'){$('#use_yue').click();}					
					changeSurplus(payables);//使用余额支付功能
				}
			}else{
				//支付方式为非余额支付||判断total是否已经计算了余额
				if($('#ECS_SURPLUS').attr("disabled")){					
					$('#ECS_SURPLUS').val(0);
					//余额input可用
					$('#ECS_SURPLUS').removeAttr('disabled');
					changeSurplus(0);//余额使用清零
				}
			}
			//==============================【选中支付方式为余额支付】end==========================//
			
			/*====================================================================================*/
			//记录用户的支付方式和配送方式 -下次进入即使用上一次的支付方式配送方式			
			setCookie("payment", pay_id);
			setCookie("shipping",shp_id);
			/*====================================================================================*/				
			
			//==============================【选中支付方式为在线支付】=============================//
			if(pay_id == 0){				
				//获取选中的银行id
				var bank = $('#pay_form input[name="bank"]:checked');
				var banv = bank.val();
				pay_id   = banv;			
				payment  = bank.parent().next().find('img').attr('alt');	
				
				//【记录银行名称】并添加到最后的购物车表单中
				var b_name = $('#cart_submit_form input[name="bank_name"]');
				if(b_name.size()>0){ b_name.remove();}				
				$('<input type="hidden" name="bank_name" value="'+payment+'" />').appendTo('#cart_submit_form');			
			}
			//==============================【选中支付方式为在线支付】end==========================//					
							
			show_pay.eq(0).text(payment);
			show_pay.eq(1).text(shipping);
			
			//============================================(最终提供给购物车的支付方式和配送方式)==============================================||	
			//如果原先存在这行则清空
			var in_pay = $('#cart_submit_form input[name="payment"]');
			var in_shp = $('#cart_submit_form input[name="shipping"]');			
			if(in_pay.size()>0){ in_pay.remove(); }
			if(in_shp.size()>0){ in_shp.remove(); }					

			if(parseInt(pay_id)>0 && parseInt(shp_id)>0){
				$('<input type="hidden" name="payment"  value="'+pay_id+'" />').appendTo('#cart_submit_form');	
				$('<input type="hidden" name="shipping" value="'+shp_id+'" />').appendTo('#cart_submit_form');	
				$('#get_pay_id').val(pay_id);
			}else{
				//表单数据不正确则使用默认值 [支付宝和快递]
				$('<input type="hidden" name="payment"  value="4" />').appendTo('#cart_submit_form');	
				$('<input type="hidden" name="shipping" value="9" />').appendTo('#cart_submit_form');					
			}
			//=============================================================================================================================||
	
			//如果窗顶不在可视范围  =>返回到窗顶部位置:198, 回到当前面板顶部位置是:385.
			$('html').scrollTop(198); 			
		}
	});
	/*======================================================【选择】支付方式和配送方式end===========================================================*/
	
		
	//====================================================【选择填写用户留言和发票抬头等其它信息】===================================================||
	$("#info_form").ajaxForm({
		beforeSubmit:valid_other_info,
		success:
		function(){			
			//切换其它信息的面板
			add_info();	
					
			/*----------------------------获取表单数据   显示在前端-------------------------------*/
			//发票
			var list     = '购物清单';//发票显示语句
			var listv    = $('#info_form select[name="list_kind"]').val();//是否开发票
			var list_tou = '';
			if(listv == 1){
				list_tou = $('#info_form input[name="inv_payee"]').val();
				list     = '购物清单+发票. 发票抬头:'+list_tou;
			}
			
			//订单附言
			var postscript = $('#postscript').val();
			postscript     = postscript ? postscript : '无';

			//白色面板表格显示语句
			var tr_info = $('#tb_now_otherinfo td[align="left"]');
			tr_info.eq(0).text(list);
			tr_info.eq(1).text(postscript);
			
			//如果原先存在这行则清空
			var in_inv = $('#cart_submit_form input[name="inv_payee"]');
			var in_pos = $('#cart_submit_form input[name="postscript"]');
			if(in_inv.size()>0){ in_inv.remove(); }
			if(in_pos.size()>0){ in_pos.remove(); }	
			
			//==============================表单隐藏域(提供给购物车中 发票抬头, 订单附言)=============================//				
			$('<input type="hidden" name="inv_payee"  value="'+list_tou+'" />').appendTo('#cart_submit_form');
			$('<input type="hidden" name="postscript" value="'+$('#postscript').val()+'" />').appendTo('#cart_submit_form');		
		}
	});	
	//====================================================【选择填写用户留言和发票抬头等其它信息】end===============================================||


	//===========================================购物车最后一步.确认购物信息.提交数据后去结算.【验证结算之前的数据】==================================|||	
	
	//-------------------------单击去结算按钮提交表单【防止重复提交】---------------------------------||
	$('#form_cart').submit(function(){
		
		if($('#now_addres').css('display') == 'none'){	
			alert('请保存您的收货人信息!');return false;	
							
		}else if( $('#now_pay').css('display') == 'none'){
			alert('请保存您的支付方式和配送方式!');return false;
						
		}else if( $('#now_info').css('display') == 'none'){
			alert('您还未保存其它信息 ！请保存！');return false;
								
		}else{	
			var td_pay = $('#tb_now_pay td[align="left"]');				
			if(td_pay.eq(0).text()== '' || td_pay.eq(1).text()== ''){
				alert('^_^ 您还未选择 支付方式 和 配送方式!');return false;					
			}		
		}
		//更换提交按钮图片，并设置按钮不可用
		$('#cart_submit').attr({src:"themes/default/images/cart/submit_wait.gif",disabled:"true"});
		
	});
});
/*=============================================================【购物车最后一步】end===============================================================*/



//【函数】
//yi:删除会员地址
function delete_adr(ths)
{
	var address_id = $(ths).prev().prev().prev().find('input').eq(1).val();
	var address_tr = $(ths).parent();		
	$.ajax({
		url:'flow_ajax.php?action=delete_address',
		data:'&address_id='+address_id+'&m='+Math.random(),
		type:'POST',
		cache:false,
		success:
		function(res){
			if(res == 'success'){				
				address_tr.remove();//在前端删除选中的该地址行				
				//如果没有地址行了，则隐藏整个【常用地址列表】
				var size = $('#tb_usually input[name="usual_addres"]').size();
				if(size == 0){ $('#div_usually_address').css("display","none"); }															
			}
		}
	});
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

function to_checkout()
{
	//var turl = window.location.href;
	//alert(turl);
	
	//先判断购物车中是否有商品
	var cart_num = $("#cart_num").text();//购物车商品数量总计
	if(cart_num > 0)
	{
		var user_id = $("#get_user_id").val();
		if(user_id>0)
		{
			window.location.href='flow.php?step=checkout';
		}
		else
		{
			change_lg_pan(2);
			$("#lg_pan").fadeIn();
			$(".pop_shadow").fadeTo("slow", 0.5);	
		}
	}
	else
	{
		alert('购物车商品为0，请选择您的商品后去结算！');
	}
}
function change_lg_pan(type)
{
	if(type == 1)
	{
		$(".lg_content").eq(0).hide().next().show();
		$(".lg_head_left").text('注册易视会员');
	}
	else
	{
		$(".lg_content").eq(1).hide().prev().show();
		$(".lg_head_left").text('登录易视网');		
	}
}

function lg_pan_close()
{
	$("#lg_pan").fadeOut();
	$(".pop_shadow").fadeOut();
}
/*=================================================================【表单验证函数】======================================================*/

//验证收货人地址信息
function valid_add(formData,jqform,opt)
{
	if(formData[0]['value'] == ''){
		msg = '^_^ 收货人姓名不能为空哦!';
		alert(msg);return false;
	}
	if(formData[1]['value'] == 0 || formData[2]['value'] == 0|| formData[3]['value'] == 0|| formData[4]['value'] == 0){
		msg = '^_^ 配送区域填写不全!';
		alert(msg);return false;
	}
	if(formData[5]['value'] == ''){
		msg = '^_^ 详细地址不能为空哦!';
		alert(msg);return false;
	}
	if(formData[6]['value'] == ''){	
		msg = '^_^ 邮箱不能为空哦!';
		alert(msg);return false;
	}else{
		var reg = /^(.*)+@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
		if(!reg.test(formData[6]['value'])){
			msg = '^_^ 您的邮箱格式错误！';
			alert(msg);return false;
		}
	}	
	if(formData[7]['value'] == ''){		
		msg = '^_^ 电话或手机不能为空哦!';
		alert(msg);return false;
	}else{
		var tel = formData[7]['value'];
		if( !( /^[1][3-9]\d{9}$/.test(tel) || /^[0][1-9]\d{9,10}$/.test(tel)|| /^[0][1-9]{2,3}\-\d{7,8}$/.test(tel) )){			
			msg = '^_^ 您的电话或手机格式错误！';
			alert(msg);return false;
		}
	}	
}

//验证订单的其它信息
function valid_other_info(){
	//无验证	
}

/*=================================================================【表单验证函数】end======================================================*/

//================================================================【记住用户的购物车信息功能】===============================================||
function setCookie(name,value)//两个参数，一个是cookie的名，一个是值
{
    var Days = 30; //此 cookie 将被保存 30 天
    var exp  = new Date();
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name+"="+escape(value)+ ";expires="+exp.toGMTString();
}

function getCookie(name)//取cookies函数        
{
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
    if(arr != null) return unescape(arr[2]); return null;
}
function delCookie(name)//删除cookie
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}
//================================================================【记住用户的购物车信息功能】end===========================================||


//=====================收货人地址白灰面板之间相互切换【如果原先是白的则换成灰的】=====================||
function add_addres(){	
	if( $("#div_addres").eq(0).css("display") == 'none' ){
		//-----------------------------【弹出灰色面板】-----------------------------||
		$("#now_addres").hide();
		$("#div_addres").show();
		//弹出灰色面板之后 如果支付方式灰色面板已经打开 则切换到白色面板
		if($("#div_pay").css("display")!="none"){
			$("#div_pay").hide();	
			$("#now_pay").show();
		}
	}else{
		//如果用户第一次进入且没有保存收货人信息  阻止关闭按钮提交.
		if( $('#tb_now_addres td[align="left"]').eq(0).text() == ''||$('#tb_now_addres td[align="left"]').eq(1).text() == '' ){
			alert('请先保存收货人信息!');
			return false;			
		}
		//-----------------------------【弹出白色面板】-----------------------------||	
		$("#div_addres").hide();	
		$("#now_addres").show();				
	}
}

//=====================支付方式和配送方式白灰面板之间相互切换【如果原先是白的则换成灰的】=====================||
function add_pay(){
	
	if( $("#div_pay").eq(0).css("display") == 'none' ){
		//--------------------------------------【弹出灰色面板】--------------------------------------||
		
		//-----------------修改支付和配送方式之前必填好收货人信息--------------------||
		if($("#now_addres").css("display")=='none'){
			alert("请先保存收货人地址!");
			return false;
		}
		//-------------------------------------------------------------------------||
				
		//----------------隐藏白色 弹出灰色选择面板----------------||
		$("#now_pay").hide();
		$("#div_pay").show();
		//-------------------------------------------------------||
		
		//----------------灰色面板中没有配送方式 则根据地址动态加载配送方式--------------||
		if($('#tb_shipping_list tr').size()==0){

			var pro = $("#selProvinces").val();
			var city= $("#selCities").val();
			var dist= $("#selDistricts").val();
			var type= $("#flow_type").val();
			
			//======================根据客户选择的收货人地址动态加载配送方式===========================||
			$.ajax({
				url:'flow_ajax.php?action=shipping',
				data:'&pro='+pro+'&city='+city+'&dist='+dist+'&flow_type='+type+'&m='+Math.random(),							
				cache:false,					
				success:
				function(dd){
					dd = eval('('+dd+')');
					var len = dd.length;
					
					//清空以前的配送方式
					$("#tb_shipping_list tr").detach();	
					
					//创建新的配送方式 行
					$.each(dd,function(i){						
						var aa = '<tr><td><input type="radio" name="shipping" value="'+dd[i].shipping_id+'" {if $order.shipping_id eq '+dd[i].shipping_id+'}checked="true"{/if} supportCod="'+dd[i].support_cod+'"';
						var bb ='insure="'+dd[i].insure+'" onclick="selectShipping(this)" /></td><td align="left">'+dd[i].shipping_name+'</td><td class="td_shipping_desc">'+dd[i].shipping_desc+'</td>';
						var cc ='<td class="td_shipping_fee">'+dd[i].format_shipping_fee+'</td></tr>';
						var tr = aa+bb+cc;
						$("#tb_shipping_list").append(tr);												
					});					
					
					//----------------------------------处理货到付款行-----------------------------------------||				
					var iscod = $("#pay_form input[isCod='1']").attr("checked");
					if(iscod){										
						//隐藏所有的兄弟行  只显示货到付款这行
						$('#pay_form input[name="shipping"][supportCod="1"]').attr("checked","true").parent().parent().siblings().hide();											
					}else{
						//隐藏货到付款这一行
						$('#pay_form input[name="shipping"][supportCod="1"]').parent().parent().hide();
					}
					//----------------------------------------------------------------------------------------||
				}
			});
		}else{
			//----------------------------------处理货到付款行-----------------------------------------||
			var iscod = $("#pay_form input[isCod='1']").attr("checked");
			if(iscod){										
				//隐藏所有的兄弟行     只显示货到付款这行
				$('#pay_form input[name="shipping"][supportCod="1"]').attr("checked","true").parent().parent().siblings().hide();											
			}else{
				//隐藏货到付款这一行
				$('#pay_form input[name="shipping"][supportCod="1"]').parent().parent().hide();
			}
			//----------------------------------------------------------------------------------------||
		}
	}else{
		//--------------------------------------【弹出白色面板】--------------------------------------||
		$("#div_pay").hide();	
		$("#now_pay").show();
	}
}

//其它信息的显示和隐藏
function add_info(){	
	if( $("#div_info").eq(0).css("display") == 'none' ){
		$("#now_info").hide();
		$("#div_info").show();		
	}else{	
		$("#div_info").hide();	
		$("#now_info").show();				
	}
}


//---------------------------------------------------------------更新购物车(-)【以前程序】------------------------------------------------------------------||
var xhr_cart = false;
/* -------------------------------------------------------------------------------------------------
 * 函数 发送ajax请求服务器处理url数据：更新购物车
 * -------------------------------------------------------------------------------------------------
 */
function send_request_cart(url){
	xhr_cart=false;
	if(window.XMLHttpRequest){
		xhr_cart=new XMLHttpRequest();
		if(xhr_cart.overrideMimeType){
			xhr_cart.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){
		try{
			xhr_cart=new ActiveXObject("Msxml2.XMLHttp");
		}catch(e){
			try{
				xhr_cart=new ActiveXobject("Microsoft.XMLHttp");
			}catch(e){}
		}
	}	
	if(!xhr_cart){
		window.alert("创建XMLHttp失败！");
		return false;
	}
	xhr_cart.onreadystatechange=cartrequest;
	xhr_cart.open("GET",url,true);
	xhr_cart.send(null);
}
/* -------------------------------------------------------------------------------------------------
 * send_request_cart(url) 回调函数
 * -------------------------------------------------------------------------------------------------
 */
function cartrequest(){
	if(xhr_cart.readyState==4){
		if(xhr_cart.status==200){
			var res = xhr_cart.responseText;
			var aa = Array();
			aa = res.split(',');
			var sid       = aa[0]; //rec_id
			var price     = aa[1]; //商品价格
			var tnum      = aa[2]; //商品总数
			var total_sum = aa[3]; //总金额
			var points    = aa[4]; //获得积分
			var freepx    = parseFloat(aa[5]); //免运费还差多少
			var num       = parseFloat(aa[6]); //rec_id商品数量
			var cart_weight = parseFloat(aa[7]);//商品总重
	
			//-----------------------购物车数据ajax更新:rec_id小计金额--------------------------------
			var id  = "sum_"+sid;
			var sum = parseFloat(price)*num;
			sum = sum.toFixed(2);
			sum = "￥"+sum;	
			if(!price.indexOf('.'))
			{
				sum = sum+".00";
			}		
			document.getElementById(id).innerHTML = sum;

			//-----------------------购物车数据ajax更新:购物车底部一行--------------------------------
			document.getElementById("cart_num").innerHTML   = tnum;			
			document.getElementById("cart_sum").innerHTML   = total_sum;			
			document.getElementById("cart_point").innerHTML = points;
			document.getElementById('cart_weight').innerHTML= cart_weight;
			if(freepx == 0){
				var base_line = aa[aa.length-1]*1;
				document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">购物已超过'+base_line+'元，您可以享受免费快递。</b>';				
			}else{
				document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">您还差<font style="color:#c30000; font-size:14px; font-weight:bold;">'+freepx+'元</font>就可以得到免费配送</b>';				
			}
			
		}else{
			alert("您的请求失败！");
		}
	}
}


//----------------------------(护理液单个商品)（左右眼商品）单击数量加减号（前端函数）---------------------------------------------------------------------//
function reduce(n)
{
	var tid = 'goods_number_'+n;
	var num = document.getElementById(tid).value;
	if(num > 1)
	{
		document.getElementById(tid).value = num -1;
		change(n);
	}
}

function add(n)
{
	var tid = 'goods_number_'+n;
	var num = document.getElementById(tid).value;
	if(num > 0)
	{
		document.getElementById(tid).value = num*1+1;			
		change(n);
	}
}

function reducez(n)
{
	var tid = 'goods_number_'+n;
	var num = document.getElementById(tid).value;
	if(num > 1)
	{
		document.getElementById(tid).value = num - 1;
		changez(n);
	}
}

function addz(n)
{
	var tid = 'goods_number_'+n;
	var num = document.getElementById(tid).value;
	if(num > 0)
	{
		document.getElementById(tid).value = num*1+1;
		changez(n);
	}
}

function reducey(n)
{
	var tid = 'goods_number2_'+n;
	var num = document.getElementById(tid).value;
	if(num > 1)
	{
		document.getElementById(tid).value = num - 1;
		changey(n);
	}
}

function addy(n)
{
	var tid = 'goods_number2_'+n;
	var num = document.getElementById(tid).value;
	if(num > 0)
	{
		document.getElementById(tid).value = num*1+1;
		changey(n);
	}
}

//-----------------------------数量框中的值改变(后台改变)------------------------------------------------------------------------//	
//n:购物车中商品rec_id(此值唯一存在购物车中)
function change(n)
{
	var tid = 'goods_number_'+n;
	var num = document.getElementById(tid).value;	
	if(num<1 || isNaN(num) ||num>9999)
	{
		num = 1;
		document.getElementById(tid).value = 1;
	}
	var url = "flow.php?step=update_cart"+"&key="+n+"&number="+num+"&sid="+Math.random();
	send_request_cart(url);
}

//左右眼数量改变
function changez(n)
{
	var tid = 'goods_number_'+n;
	var num = document.getElementById(tid).value;
	if(num<1 || isNaN(num) ||num>9999)
	{
		num = 1;
		document.getElementById(tid).value = 1;
	}
	var zb = num;	
	var yb = document.getElementById('goods_number2_'+n).value;
	if(yb != '')
	{
		num = parseFloat(zb)+parseFloat(yb);//num为左右眼的总数量
	}
	else
	{
		yb = null;		
	}
	var url = "flow.php?step=update_cart"+"&key="+n+"&number="+num+"&zb="+zb+"&yb="+yb+"&sid="+Math.random();
	send_request_cart(url);
}

function changey(n)
{
	var tid = 'goods_number2_'+n;
	var num = document.getElementById(tid).value;
	if(num<1 || isNaN(num) ||num>9999)
	{
		num = 1;
		document.getElementById(tid).value = 1;
	}
	var zb = document.getElementById('goods_number_'+n).value;	
	var yb = num;
	if(zb != '')
	{
		num = parseFloat(zb)+parseFloat(yb);//num为左右眼的总数量
	}
	else
	{
		zb = null;		
	}
	var url = "flow.php?step=update_cart"+"&key="+n+"&number="+num+"&zb="+zb+"&yb="+yb+"&sid="+Math.random();
	send_request_cart(url);
}


//-------------------------------------------------------更新礼包数量------------------------------------------------------------------------------------------//
var xhr_cartp=false;
function send_request_cartp(url){
	xhr_cartp=false;
	if(window.XMLHttpRequest){
		xhr_cartp=new XMLHttpRequest();
		if(xhr_cartp.overrideMimeType){
			xhr_cartp.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){
		try{
			xhr_cartp=new ActiveXObject("Msxml2.XMLHttp");
		}catch(e){
			try{
				xhr_cartp=new ActiveXobject("Microsoft.XMLHttp");
			}catch(e){}
		}
	}	
	if(!xhr_cartp){
		window.alert("创建XMLHttp失败！");
		return false;
	}
	xhr_cartp.onreadystatechange = cartrequestp;
	xhr_cartp.open("GET",url,true);
	xhr_cartp.send(null);
}

//更新礼包数量 回调函数(因为回调函数的不同，所以重写函数)
function cartrequestp(){
	if(xhr_cartp.readyState==4){
		if(xhr_cartp.status==200){
			//document.getElementById('goodslist').innerHTML=http_request.responseText;
			var res = xhr_cartp.responseText;
			var aa = Array();
			aa = res.split(',');
			var rec_id = aa[0];
			var num    = parseFloat(aa[1]);//礼包数量
			var g_num  = aa[2];            //礼包中的产品数量
			var restr  = parseFloat(aa[3]);//礼包单价
			var tnum   = aa[4];            //数量
			var total_sum = aa[5];         //金额总计
			var points = aa[6];            //积分
			var freepx = parseFloat(aa[7]);//运费
			
			var sum = restr*num;
			sum = "￥"+sum+".00";
			var id  = "sum_"+rec_id;
			document.getElementById(id).innerHTML = sum;
			//--------------------------------同时更新礼包下面的商品的数量--------------------------------
			if(g_num>0){
				for(var i=0; i<g_num; i++){
					var next_id = parseFloat(rec_id)+i;
					var dd = "package_"+next_id;
					document.getElementById(dd).value = num;
				}
			}

			//这些数据要靠后台传过来----靠前台无法解决---------------------------------------------
			document.getElementById("cart_num").innerHTML   = tnum;			
			document.getElementById("cart_sum").innerHTML   = total_sum;			
			document.getElementById("cart_point").innerHTML = points;
			if(freepx == 0){
				var base_line = aa[aa.length-1]*1;
				document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">购物已超过'+base_line+'元，您可以享受免费快递。</b>';				
			}else{
				document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">您还差<font style="color:#c30000; font-size:14px; font-weight:bold;">'+freepx+'元</font>就可以得到免费配送</b>';				
			}			
		}else{
			alert("您的请求失败！");
		}
	}
}
function reducep(n)
{
	var tid = 'package_'+n;
	var num = document.getElementById(tid).value;
	if(num > 1)
	{
		document.getElementById(tid).value = num - 1;
		changep(n);
	}	
}
function addp(n)
{
	var tid = 'package_'+n;
	var num = document.getElementById(tid).value;
	if(num > 0)
	{
		document.getElementById(tid).value = num*1+1;
		changep(n);
	}
}
function changep(n)
{	
	var tid = 'package_'+n;
	var num = document.getElementById(tid).value;//改变后这个记录数量值		
	if(num < 1 || isNaN(num) ||num>9999)
	{
		num = 1;
		document.getElementById(tid).value=1;
	}
	var url = "flow.php?step=update_package"+"&key="+n+"&number="+num+"&sid="+Math.random(); 
	send_request_cartp(url);
}


//-------------------------------------------------------------------------商品配件加入购物车（一）--------------------------------------------------------//
var xhr_addcart=false;
function send_request_add(url)
{
	xhr_addcart=false;
	if(window.XMLHttpRequest){
		xhr_addcart=new XMLHttpRequest();
		if(xhr_addcart.overrideMimeType){
			xhr_addcart.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){
		try{
			xhr_addcart=new ActiveXObject("Msxml2.XMLHttp");
		}catch(e){
			try{
				xhr_addcart=new ActiveXobject("Microsoft.XMLHttp");
			}catch(e){}
		}
	}	
	if(!xhr_addcart){
		window.alert("创建XMLHttp失败！");
		return false;
	}
	xhr_addcart.onreadystatechange = addrequest;
	xhr_addcart.open("GET", url, true);
	xhr_addcart.send(null);
}

//商品配件加入购物车 回调函数
function addrequest()
{
	if(xhr_addcart.readyState==4)
	{
		if(xhr_addcart.status==200)
		{
			var res = xhr_addcart.responseText;	
			var bb = Array();
			bb = res.split(',');
			var name = bb[0];
			var img  = bb[1];
			var price= "￥"+bb[2];
			var goods_id =  bb[7];	
			var suc      =  bb[8];//是否缺货 true:插入成功，false:插入失败
			var rec_id   =  bb[9];//购物车id
			var have     =  bb[10];//是否已在购物车中0:不在1:在
			var cart_num =  bb[11];
			if(suc)
			{
				//更新购物车第一步页面数据
				var tnum   = bb[3]; 
				var tsum   = bb[4];
				var points = bb[5];
				var freepx = bb[6];
				var weight = bb[12];
				document.getElementById("cart_weight").innerHTML= weight;	
				document.getElementById("cart_num").innerHTML   = tnum;			
				document.getElementById("cart_sum").innerHTML   = tsum;			
				document.getElementById("cart_point").innerHTML = points;
				if(freepx == 0){
					var base_line = bb[bb.length-1]*1;
					document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">购物已超过'+base_line+'元，您可以享受免费快递。</b>';
				}else if(freepx == -1){
					document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;"></b>';
				}else{
					document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">您还差<font style="color:#e43232; font-size:14px; font-weight:bold;">'+freepx+'元</font>就可以得到免费配送</b>';				
				}	
						
				if(have == 1)
				{
					//---------------------------------------------------累加购物车中配件的数量和小计----------------------------------------------------------------------
					//-----------------------更新数量------------------------
					var nid = "goods_number_"+rec_id;
					document.getElementById(nid).value = cart_num;
					//-----------------------更新小计-------------------------
					var did  = "sum_"+rec_id;
					var sum = parseFloat(bb[2])*cart_num;		
					sum = "￥"+sum+".00";
					document.getElementById(did).innerHTML = sum;
					
				}
				else
				{
				//-------------------------------------------------------配件加入购物车-------------------------------------------------------------------------------------------------------				
				var obj = document.getElementById("datatb");
				//要插入的位置：表格的行数-1 
				var len = obj.rows.length - 1;
				var newrow  = obj.insertRow(len);
				var newcell = newrow.insertCell(-1);
				newcell.innerHTML = '<a href="goods'+goods_id+'.html" target="_blank"><img src="'+img+'" border="0" title="'+name+'"  width="100" height="100"/></a>';
				newcell.setAttribute("align","center");
				
				var newcell1 = newrow.insertCell(-1);			
				newcell1.innerHTML = '<a href="goods'+goods_id+'.html" target="_blank"><font color="#333333">'+name+'</font></a>';
				newcell1.setAttribute("align","left");
				
				var newcell2 = newrow.insertCell(-1);
				newcell2.innerHTML = '<table width="270px" border="0"><tr><td width="135px"><span style="width:110px; display:inline-block; text-align:left; padding-left:20px;">眼镜度数：</span></td><td>数量： <img src="themes/default/images/jianhao.jpg" onclick="reduce('+rec_id+')" style="cursor:pointer;"/> <input type="text" name="goods_number['+rec_id+']" id="goods_number_'+rec_id+'" onchange="change('+rec_id+');" value="1" style="border:1px solid #666666;width:20px;text-align:center;"/> <img src="themes/default/images/jiahao.jpg" onclick="add('+rec_id+')" style="cursor:pointer;"/></td></tr></table>';
				newcell2.setAttribute("align","center");
				
				var newcell3 = newrow.insertCell(-1);
				newcell3.innerHTML = price;
				newcell3.setAttribute("align","center");
				//更新小计
				var newcell4 = newrow.insertCell(-1);
				newcell4.innerHTML = '<div id="sum_'+rec_id+'">'+price+'</div>';
				newcell4.setAttribute("align","center");
				
				var newcell5 = newrow.insertCell(-1);
				newcell5.innerHTML = '<a href="javascript:if(confirm(\'您确实要把该商品移出购物车吗？\'))location.href=\'flow.php?step=drop_goods&amp;id='+rec_id+'\';" style="color:#993300">删除</a>';
				newcell5.setAttribute("align","center");	
				//插入虚线
				var lens = obj.rows.length -1;
				var newrow2 = obj.insertRow(lens);
				var newcell21 = newrow2.insertCell(-1);
				newcell21.setAttribute("colSpan","6");
				newcell21.innerHTML = '<img src="themes/default/images/cartfgx.jpg" width="962px" />';
				//--------------------------------------------------------------------------------------------------------------------------------------------------------------------					
					
				}				
			}
			else
			{
				alert("对不起！该产品暂时缺货！我们会尽快补充库存的！");
			}									
		}
		else
		{
			alert("您的请求失败！");
		}
	}
}

//商品配件加入购物车
function addcart(goods_id)
{
	var url = "add_to_cart.php?goods_id="+goods_id+"&sid="+Math.random();
	send_request_add(url);
}

//----------------------------------------------------------------------------加钱赠品加入购物车（二）--------------------------------------------------------

/* -------------------------------------------------------------------------------------------------
 * 函数：yi：公共ajax函数  可复用
 * -------------------------------------------------------------------------------------------------
 * url: ajax请求url。 backfunction:回调函数。method:请求方式 GET/POST
 */
function yi_ajax(url, backfunction, method)
{
	xhr_fav = false;
	if(window.XMLHttpRequest){
		xhr_fav=new XMLHttpRequest();
		if(xhr_fav.overrideMimeType){
			xhr_fav.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){
		try{
			xhr_fav=new ActiveXObject("Msxml2.XMLHttp");
		}catch(e){
			try{
				xhr_fav=new ActiveXobject("Microsoft.XMLHttp");
			}catch(e){}
		}
	}	
	if(!xhr_fav){
		window.alert("创建XMLHttp失败！");
		return false;
	}
	xhr_fav.onreadystatechange = backfunction;
	xhr_fav.open(method, url, true);
	xhr_fav.send(null);	
}


/* -------------------------------------------------------------------------------------------------
 * 函数 加钱赠品加入购物车
 * -------------------------------------------------------------------------------------------------
 * act_id:优惠活动ID， goods_id:优惠活动加钱赠品ID。(act_id_goods_id 全局唯一)
 */
function add_fav(act_id, goods_id)
{	
	//赠品商品类型kind:0眼镜, 1护理液。
	var kind = document.getElementById('kind'+goods_id).value;
	
	//后边要使用的页面元素ID,保持ID命名不可随意更改				
	var ID_price  = "gift_price"+goods_id;
	var ID_number = "gift_number"+goods_id;
	var ID_z      = "zselect"+act_id+goods_id;
	var ID_y      = "yselect"+act_id+goods_id;	

	var fa_id     = 99;//未使用
	var can_add   = document.getElementById('fav_can_add').value;//能否加钱赠品标记

	if(kind == 0)
	{	
		var price       = document.getElementById(ID_price).value; //加钱赠品的价格
		var gift_number = document.getElementById(ID_number).value;//加钱赠品眼镜个数
		
		if(gift_number==1)
		{
			var ds = document.getElementById("ds"+act_id+goods_id).value;//一个眼镜的度数
			if(ds==''){alert('请选择度数！'); return;}	
			var url = "flow.php?step=yi_add_fav"+"&goods_id="+goods_id+"&price="+price+"&act_id="+act_id+"&ds="+encodeURIComponent(ds)+"&num="+gift_number+"&fa_id="+fa_id+"&sid="+Math.random();	
			yi_ajax(url, backf_add_fav, 'POST');	
		}
		else if(gift_number==2)
		{
			var zselect = encodeURIComponent(document.getElementById(ID_z).value);
			var yselect = encodeURIComponent(document.getElementById(ID_y).value);
			price = price/2;
			if(zselect=='' || yselect==''){alert('请选择眼镜度数！'); return;}			
			var url = "flow.php?step=yi_add_fav"+"&goods_id="+goods_id+"&price="+price+"&act_id="+act_id+"&zselect="+zselect+"&yselect="+yselect+"&num="+gift_number+"&fa_id="+fa_id;	
			url = url+"&sid="+Math.random();			
			yi_ajax(url, backf_add_fav, 'POST');
		}
		else
		{
			//其它
		}		
	}
	else
	{	
		//========================[护理液赠品加入购物车]=====================//	
		var price = document.getElementById(ID_price).value;         //价格
		var url   = "flow.php?step=yi_add_fav"+"&goods_id="+goods_id+"&price="+price+"&act_id="+act_id+"&num=1&fa_id="+fa_id+"&sid="+Math.random();
		yi_ajax(url, backf_add_fav, 'POST');
	}	
}

//加钱赠品加入购物车回调函数 ：今后回调函数采用统一命名：backf_函数名。
function backf_add_fav()
{
	if(xhr_fav.readyState==4)
	{
		if(xhr_fav.status==200)
		{			
			var res			= xhr_fav.responseText;//回调函数返回字符串
			var cart_weight = 0;
			var dd			= Array();
			dd = res.split(',');
			var goods_id = dd[0];
			var name     = dd[1];
			var img      = dd[2];
			var rec_id   = dd[4];
			var tnum     = dd[5];
			var total_sum= dd[6];
			var points   = dd[7];
			var freepx   = dd[8];	
			var act_id   = dd[9];
			var fa_id    = dd[10];			
			var num      = dd[11];
			if(num==1)
			{
				//加钱赠品为护理液或1个眼镜情况				
				var price  = "￥"+dd[3]+".00";
				var price1 = "￥"+dd[3]+".00";
				zselect    = dd[12];
				yselect    = '';				
				cart_weight= dd[13];
				document.getElementById("fav_can_add2").value = 0;//能否加赠品标记。
				var ds_text = (zselect !='')? '眼镜度数：'+zselect : '';
			}
			else
			{
				//加钱赠品为2个眼镜情况
				var price1 = "￥"+dd[3]+".00";
				var price  = "￥"+dd[3]*2+".00";
				zselect    = dd[12];
				yselect    = dd[13];				
				cart_weight= dd[14];
				document.getElementById("fav_can_add").value = 0;//能否加赠品标记。
			}
				
			//========================【ajax更新购物车页面数据】==============================================//
			document.getElementById("cart_weight").innerHTML= cart_weight;				
			document.getElementById("cart_num").innerHTML   = tnum;					
			document.getElementById("cart_sum").innerHTML   = total_sum;			
			document.getElementById("cart_point").innerHTML = points;		
			
			//yi:优惠品类型
			var gtext = (dd[3]>0)? '特惠商品': '赠品';			
			
			if(freepx==0)
			{
				var base_line = dd[dd.length-1]*1;
				document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">购物已超过'+base_line+'元，您可以享受免费快递。</b>';				
			}
			else
			{
				document.getElementById("freepx").innerHTML = '<b style="font-size:14px; color:#666;">您还差<font style="color:#c30000; font-size:14px; font-weight:bold;">'+freepx+'元</font>就可以得到免费配送</b>';				
			}		
			
			if(goods_id>0)
			{
			//-------------------------------------------------------【特惠商品ajax插入购物车】--------------------------------------------------------//				
			var obj = document.getElementById("datatb");			
			var len = obj.rows.length - 1; //要插入的表末索引位置：表格的行数-1 
			var newrow  = obj.insertRow(len);
			
			var newcell = newrow.insertCell(-1);
			newcell.innerHTML = '<a href="goods'+goods_id+'.html" target="_blank"><img src="'+img+'" title="'+name+'" width="100" height="100"/></a>';
			newcell.setAttribute("align", "center");
			
			var newcell1 = newrow.insertCell(-1);			
			newcell1.innerHTML = '<span class="redf">（'+gtext+'）</span><a href="goods'+goods_id+'.html" target="_blank" class="f6">'+name+'</a>';
			newcell1.setAttribute("align","left");
			
			var newcell2 = newrow.insertCell(-1);
			if(num==1)
			{
				newcell2.innerHTML = '<table width="270px" border="0"><tr><td width="135px"><span style="width:110px; display:inline-block; text-align:left;">'+ds_text+'</span></td><td>数量： <img src="themes/default/images/jianhao.jpg"/> <input type="text" name="goods_number['+rec_id+']" id="goods_number_'+rec_id+'" value="'+num+'" readonly="readonly" style="border:1px solid #999999;width:20px;text-align:center; color:#999;"/> <img src="themes/default/images/jiahao.jpg"/></td></tr></table>';
			}
			else
			{
				newcell2.innerHTML = '<table width="270px" border="0"><tr><td width="135px"><span style="width:110px; display:inline-block; text-align:left;">度数：'+zselect+','+yselect+'</span></td><td>数量： <img src="themes/default/images/jianhao.jpg"/> <input type="text" name="goods_number['+rec_id+']" id="goods_number_'+rec_id+'" value="'+num+'" readonly="readonly" style="border:1px solid #999999;width:20px;text-align:center; color:#999;"/> <img src="themes/default/images/jiahao.jpg"/></td></tr></table>';
			}
			newcell2.setAttribute("align","center");
			
			var newcell3 = newrow.insertCell(-1);
			newcell3.innerHTML = price1;
			newcell3.setAttribute("align","center");
			
			var newcell4 = newrow.insertCell(-1);
			newcell4.innerHTML = '<div id="sum_'+rec_id+'">'+price+'</div>';//更新小计
			newcell4.setAttribute("align","center");
			
			var newcell5 = newrow.insertCell(-1);
			newcell5.innerHTML = '<a href="javascript:if(confirm(\'您确实要把该商品移出购物车吗？\'))location.href=\'flow.php?step=drop_goods&amp;id='+rec_id+'\';" style="color:#993300">删除</a>';
			newcell5.setAttribute("align","center");	
			
			var lens = obj.rows.length -1;
			var newrow2 = obj.insertRow(lens);//插入虚线
			var newcell21 = newrow2.insertCell(-1);
			newcell21.setAttribute("colSpan","6");
			newcell21.innerHTML = '<img src="themes/default/images/cartfgx.jpg" width="962px" />';
			//---------------------------------------------------------------------------------------------------------------------------------//	

			}
			else
			{
				alert('^_^ 您已添加过该优惠活动商品了！');
			}
			
			act_id = "div"+act_id+fa_id;
			document.getElementById(act_id).style.display="none";//如果加钱赠品加入购物车后，删除选择框中的这个赠品				
		}
		else
		{
			alert("对不起，您请求失败！");
		}
	}
}
//--------------------------------------------------------------------------------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	