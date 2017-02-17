//======================================【会员中心】【2011-5-8 author:yijiangwen】【TIME:20120906】======================================//


/*=======================只放会员中心公共函数=======================*/
/* *
 * 修改会员信息
 */
function userEdit()
{
  var frm = document.forms['formEdit'];
  var email = frm.elements['email'].value;
  var msg = '';
  var reg = null;
  var passwd_answer = frm.elements['passwd_answer'] ? Utils.trim(frm.elements['passwd_answer'].value) : '';
  var sel_question =  frm.elements['sel_question'] ? Utils.trim(frm.elements['sel_question'].value) : '';

  if (email.length == 0)
  {
    msg += email_empty + '\n';
  }
  else
  {
    if ( ! (Utils.isEmail(email)))
    {
      msg += email_error + '\n';
    }
  }

  if (passwd_answer.length > 0 && sel_question == 0 || document.getElementById('passwd_quesetion') && passwd_answer.length == 0)
  {
    msg += no_select_question + '\n';
  }

  for (i = 7; i < frm.elements.length - 2; i++)	// 从第七项开始循环检查是否为必填项
  {
	needinput = document.getElementById(frm.elements[i].name + 'i') ? document.getElementById(frm.elements[i].name + 'i') : '';

	if (needinput != '' && frm.elements[i].value.length == 0)
	{
	  msg += '- ' + needinput.innerHTML + msg_blank + '\n';
	}
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* 会员修改密码 */
function editPassword()
{
  var frm              = document.forms['formPassword'];
  var old_password     = frm.elements['old_password'].value;
  var new_password     = frm.elements['new_password'].value;
  var confirm_password = frm.elements['comfirm_password'].value;

  var msg = '';
  var reg = null;

  if (old_password.length == 0)
  {
    msg += old_password_empty + '\n';
  }

  if (new_password.length == 0)
  {
    msg += new_password_empty + '\n';
  }

  if (confirm_password.length == 0)
  {
    msg += confirm_password_empty + '\n';
  }

  if (new_password.length > 0 && confirm_password.length > 0)
  {
    if (new_password != confirm_password)
    {
      msg += both_password_error + '\n';
    }
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 对会员的留言输入作处理
 */
function submitMsg()
{
  var frm         = document.forms['formMsg'];
  var msg_title   = frm.elements['msg_title'].value;
  var msg_content = frm.elements['msg_content'].value;
  var msg = '';

  if (msg_title.length == 0)
  {
    msg += msg_title_empty + '\n';
  }
  if (msg_content.length == 0)
  {
    msg += msg_content_empty + '\n'
  }

  if (msg_title.length > 200)
  {
    msg += msg_title_limit + '\n';
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 会员找回密码时，对输入作处理
 */
function submitPwdInfo()
{
  var frm = document.forms['getPassword'];
  //var user_name = frm.elements['user_name'].value;
  var email     = frm.elements['email'].value;

  var errorMsg = '';
  if(email.length == 0)
  {
    errorMsg += email_address_empty + '\n';
  }
  else
  {
    if( !(Utils.isEmail(email)))
    {
      errorMsg += email_address_error + '\n';
    }
  }

  if(errorMsg.length > 0)
  {
    alert(errorMsg);
    return false;
  }
  return true;
}

/* *
 * 会员找回密码时，对输入作处理
 */
function submitPwd()
{
  var frm = document.forms['getPassword2'];
  var password = frm.elements['new_password'].value;
  var confirm_password = frm.elements['confirm_password'].value;

  var errorMsg = '';
  if (password.length == 0)
  {
    errorMsg += new_password_empty + '\n';
  }

  if (confirm_password.length == 0)
  {
    errorMsg += confirm_password_empty + '\n';
  }

  if (confirm_password != password)
  {
    errorMsg += both_password_error + '\n';
  }

  if (errorMsg.length > 0)
  {
    alert(errorMsg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 处理会员提交的缺货登记
 */
function addBooking()
{
  var frm  = document.forms['formBooking'];
  var goods_id = frm.elements['id'].value;
  var rec_id  = frm.elements['rec_id'].value;
  var number  = frm.elements['number'].value;
  var desc  = frm.elements['desc'].value;
  var linkman  = frm.elements['linkman'].value;
  var email  = frm.elements['email'].value;
  var tel  = frm.elements['tel'].value;
  var msg = "";

  if (number.length == 0)
  {
    msg += booking_amount_empty + '\n';
  }
  else
  {
    var reg = /^[0-9]+/;
    if ( ! reg.test(number))
    {
      msg += booking_amount_error + '\n';
    }
  }

  if (desc.length == 0)
  {
    msg += describe_empty + '\n';
  }

  if (linkman.length == 0)
  {
    msg += contact_username_empty + '\n';
  }

  if (email.length == 0)
  {
    msg += email_empty + '\n';
  }
  else
  {
    if ( ! (Utils.isEmail(email)))
    {
      msg += email_error + '\n';
    }
  }

  if (tel.length == 0)
  {
    msg += contact_phone_empty + '\n';
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  return true;
}

//============验证会员登录信息============//
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

function chkstr(str)
{
  for (var i = 0; i < str.length; i++)
  {
    if (str.charCodeAt(i) < 127 && !str.substr(i,1).match(/^\w+$/ig))
    {
      return false;
    }
  }
  return true;
}

function check_password( password )
{
    if ( password.length < 6 )
    {
        document.getElementById('password_notice').innerHTML = password_shorter;
		document.getElementById('password_notice').style.color = "red";
    }
    else
    {
        document.getElementById('password_notice').innerHTML = msg_can_rg;
		document.getElementById('password_notice').style.color = "green";	
    }
}

function check_conform_password( conform_password )
{
    password = document.getElementById('password1').value;
    document.getElementById('conform_password_notice').style.color = "red";
    if ( conform_password.length < 6 )
    {
        document.getElementById('conform_password_notice').innerHTML = password_shorter;
        return false;
    }
    if ( conform_password != password )
    {
        document.getElementById('conform_password_notice').innerHTML = confirm_password_invalid;
    }
    else
    {
        document.getElementById('conform_password_notice').innerHTML = msg_can_rg;
		document.getElementById('conform_password_notice').style.color = "green";
    }
}
//检查电话号码
function check_phone(phone){
	if(phone.length == 0){
		document.getElementById('phoneid_notice').innerHTML = '电话或手机不能为空！';
	}else if(/^[0-9\-]+$/.test(phone) && ( /^[1][3578]\d{9}$/.test(phone) || /^[0][1-9][0-9\-]{9,10}$/.test(phone) )){		
			document.getElementById('phoneid_notice').style.color = "green";	
			document.getElementById('phoneid_notice').innerHTML = '可以注册';
	}else{	
		document.getElementById('phoneid_notice').style.color = "red";	
		document.getElementById('phoneid_notice').innerHTML = '电话或手机号码错误！';
	}	
}

function is_registered( username )
{
    var submit_disabled = false;
	var unlen = username.replace(/[^\x00-\xff]/g, "**").length;
	document.getElementById('username_notice').style.color = "red";	
	
    if(username == '')
    {
        document.getElementById('username_notice').innerHTML = '用户名不能为空！';//msg_un_blank;
        var submit_disabled = true;
    }

    if(!chkstr(username))
    {
        document.getElementById('username_notice').innerHTML = msg_un_format;
        var submit_disabled = true;
    }
    if( unlen < 5 && unlen >0)
    { 
        document.getElementById('username_notice').innerHTML = username_shorter;
        var submit_disabled = true;
    }
    if( unlen > 20 )
    {
        document.getElementById('username_notice').innerHTML = '(限5-20个字符以内)';//msg_un_length;
        var submit_disabled = true;
    }
    if( submit_disabled )
    {
        document.forms['formUser'].elements['Submit'].disabled = 'disabled';
        return false;
    }
    Ajax.call( 'user.php?act=is_registered', 'username=' + username, registed_callback , 'GET', 'TEXT', true, true );
}



function registed_callback(result)
{
  if ( result == "true" )
  {
	if(msg_can_rg == '可以注册'){
		document.getElementById('username_notice').style.color = "green";		
	}else{
		document.getElementById('username_notice').style.color = "red";	
	}	  
    document.getElementById('username_notice').innerHTML = msg_can_rg;
    document.forms['formUser'].elements['Submit'].disabled = '';
  }
  else
  {
    document.getElementById('username_notice').innerHTML = msg_un_registered;
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
  }
}

function checkEmail(email)
{
  var submit_disabled = false;
  
  if(email == '')
  {
    document.getElementById('email_notice').innerHTML = "邮箱不能为空！";//msg_email_blank;	
	document.getElementById('email_notice').style.color = "red";
    submit_disabled = true;
  }
  else if (!Utils.isEmail(email))
  {
    document.getElementById('email_notice').innerHTML = msg_email_format;
	document.getElementById('email_notice').style.color = "red";
    submit_disabled = true;
  }
 
  if( submit_disabled )
  {//不准提交 并中断
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
    return false;
  }
  Ajax.call( 'user.php?act=check_email', 'email=' + email, check_email_callback , 'GET', 'TEXT', true, true );
}

function check_email_callback(result)
{
  if( result == 'ok' )
  {
	if(msg_can_rg == '可以注册'){
		document.getElementById('email_notice').style.color = "green";		
	}else{
		document.getElementById('email_notice').style.color = "red";	
	}	
    document.getElementById('email_notice').innerHTML = msg_can_rg;
    document.forms['formUser'].elements['Submit'].disabled = '';
  }
  else
  {
    document.getElementById('email_notice').innerHTML = msg_email_registered;
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
  }
}

/* *
 * 处理注册用户
 */
function register()
{
  var frm       = document.forms['formUser'];
  var username  = Utils.trim(frm.elements['username'].value);
  var email     = frm.elements['email'].value;
  var password  = Utils.trim(frm.elements['password'].value);
  var confirm_password  = Utils.trim(frm.elements['confirm_password'].value);
  var checked_agreement = frm.elements['agreement'].checked;
  var msn = frm.elements['extend_field1'] ? Utils.trim(frm.elements['extend_field1'].value) : '';
  var qq  = frm.elements['extend_field2'] ? Utils.trim(frm.elements['extend_field2'].value) : '';
  var home_phone   = frm.elements['extend_field4'] ? Utils.trim(frm.elements['extend_field4'].value) : '';
  var office_phone = frm.elements['extend_field3'] ? Utils.trim(frm.elements['extend_field3'].value) : '';
  var mobile_phone = frm.elements['extend_field5'] ? Utils.trim(frm.elements['extend_field5'].value) : '';
  var passwd_answer = frm.elements['passwd_answer'] ? Utils.trim(frm.elements['passwd_answer'].value) : '';
  var sel_question =  frm.elements['sel_question'] ? Utils.trim(frm.elements['sel_question'].value) : '';


  var msg = "";
  // 检查输入
  var msg = '';
  if(username.length == 0)
  {
    msg += username_empty + '\n';
  }
  else if (username.match(/^\s*$|^c:\\con\\con$|[%,\'\*\"\s\t\<\>\&\\]/))
  {
    msg += username_invalid + '\n';
  }
  else if (username.length < 3)
  {
    //msg += username_shorter + '\n';
  }
  
  //邮箱
  if(email.length == 0)
  {
    msg += email_empty + '\n';
  }
  else
  {
    if ( !(Utils.isEmail(email)))
    {
      msg += email_invalid + '\n';
    }
  }
  
  //密码
  if(password.length == 0)
  {
    msg += password_empty + '\n';
  }
  else if (password.length < 6)
  {
    msg += password_shorter + '\n';
  }
  
  if (/ /.test(password) == true)
  {
	msg += passwd_balnk + '\n';
  }
  //确认密码
  if (confirm_password != password )
  {
    msg += confirm_password_invalid + '\n';
  }
  if(checked_agreement != true)
  {
    msg += agreement + '\n';
  }

  if (msn.length > 0 && (!Utils.isEmail(msn)))
  {
    msg += msn_invalid + '\n';
  }

  if (qq.length > 0 && (!Utils.isNumber(qq)))
  {
    msg += qq_invalid + '\n';
  }
//电话
  if(office_phone.length>0)
  {
    var reg = /^[\d|\-|\s]+$/;
    if (!reg.test(office_phone))
    {
      msg += office_phone_invalid + '\n';
    }
  }
  if (home_phone.length>0)
  {
    var reg = /^[\d|\-|\s]+$/;

    if (!reg.test(home_phone))
    {
      msg += home_phone_invalid + '\n';
    }
  }
  if (mobile_phone.length>0)
  {
    var reg = /^[\d|\-|\s]+$/;
    if (!reg.test(mobile_phone))
    {
      msg += mobile_phone_invalid + '\n';
    }
  }
  if (passwd_answer.length > 0 && sel_question == 0 || document.getElementById('passwd_quesetion') && passwd_answer.length == 0)
  {
    msg += no_select_question + '\n';
  }
//手机或电话
  for (i = 4; i < frm.elements.length - 4; i++)	//从第五项开始循环检查是否为必填项
  {
	needinput = document.getElementById(frm.elements[i].name + 'i') ? document.getElementById(frm.elements[i].name + 'i') : '';
	if(needinput != '' && frm.elements[i].value.length == 0)
	{
	  msg += '电话或手机不能为空！\n';  
	}
  }

  if(msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
	//yi--手机或电话验证之2  
	if(document.getElementById('phoneid_notice').innerHTML=='可以注册'){
		return true;
	}else{
		if(document.getElementById('phoneid_notice').innerHTML==''){
			alert('该号码已经注册，请重新输入');
			return false;
		}else{
			alert(document.getElementById('phoneid_notice').innerHTML);
			return false;			
		}
	}
  }
}

/* *
 * 用户中心订单保存地址信息
 */
function saveOrderAddress(id)
{
  var frm           = document.forms['formAddress'];
  var consignee     = frm.elements['consignee'].value;
  var email         = frm.elements['email'].value;
  var address       = frm.elements['address'].value;
  var zipcode       = frm.elements['zipcode'].value;
  var tel           = frm.elements['tel'].value;
  var mobile        = frm.elements['mobile'].value;
  var sign_building = frm.elements['sign_building'].value;
  var best_time     = frm.elements['best_time'].value;

  if (id == 0)
  {
    alert(current_ss_not_unshipped);
    return false;
  }
  var msg = '';
  if (address.length == 0)
  {
    msg += address_name_not_null + "\n";
  }
  if (consignee.length == 0)
  {
    msg += consignee_not_null + "\n";
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 会员余额申请
 */
function submitSurplus()
{
  var frm            = document.forms['formSurplus'];
  var surplus_type   = frm.elements['surplus_type'].value;
  var surplus_amount = frm.elements['amount'].value;
  var process_notic  = frm.elements['user_note'].value;
  var payment_id     = 0;
  var msg = '';

  if (surplus_amount.length == 0 )
  {
    msg += surplus_amount_empty + "\n";
  }
  else
  {
    var reg = /^[\.0-9]+/;
    if ( ! reg.test(surplus_amount))
    {
      msg += surplus_amount_error + '\n';
    }
  }

  if (process_notic.length == 0)
  {
    msg += process_desc + "\n";
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }

  if (surplus_type == 0)
  {
    for (i = 0; i < frm.elements.length ; i ++)
    {
      if (frm.elements[i].name=="payment_id" && frm.elements[i].checked)
      {
        payment_id = frm.elements[i].value;
        break;
      }
    }

    if (payment_id == 0)
    {
      alert(payment_empty);
      return false;
    }
  }

  return true;
}

/* *
 *  处理用户添加一个红包
 */
function addBonus()
{
  var frm      = document.forms['addBouns'];
  var bonus_sn = frm.elements['bonus_sn'].value;

  if (bonus_sn.length == 0)
  {
    alert(bonus_sn_empty);
    return false;
  }
  else
  {
    var reg = /^[0-9]{10}$/;
    if ( ! reg.test(bonus_sn))
    {
      alert(bonus_sn_error);
      return false;
    }
  }

  return true;
}

/* *
 *  合并订单检查
 */
function mergeOrder()
{
  if (!confirm(confirm_merge))
  {
    return false;
  }

  var frm        = document.forms['formOrder'];
  var from_order = frm.elements['from_order'].value;
  var to_order   = frm.elements['to_order'].value;
  var msg = '';

  if (from_order == 0)
  {
    msg += from_order_empty + '\n';
  }
  if (to_order == 0)
  {
    msg += to_order_empty + '\n';
  }
  else if (to_order == from_order)
  {
    msg += order_same + '\n';
  }
  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 订单中的商品返回购物车
 * @param       int     orderId     订单号
 */
function returnToCart(orderId)
{
  Ajax.call('user.php?act=return_to_cart', 'order_id=' + orderId, returnToCartResponse, 'POST', 'JSON');
}

function returnToCartResponse(result)
{
  alert(result.message);
}

/* *
 * 检测密码强度
 * @param       string     pwd     密码
 */
function checkIntensity(pwd)
{
  var Mcolor = "#FFF",Lcolor = "#FFF",Hcolor = "#FFF";
  var m=0;

  var Modes = 0;
  for (i=0; i<pwd.length; i++)
  {
    var charType = 0;
    var t = pwd.charCodeAt(i);
    if (t>=48 && t <=57)
    {
      charType = 1;
    }
    else if (t>=65 && t <=90)
    {
      charType = 2;
    }
    else if (t>=97 && t <=122)
      charType = 4;
    else
      charType = 4;
    Modes |= charType;
  }

  for (i=0;i<4;i++)
  {
    if (Modes & 1) m++;
      Modes>>>=1;
  }

  if (pwd.length<=4)
  {
    m = 1;
  }

  switch(m)
  {
    case 1 :
      Lcolor = "2px solid red";
      Mcolor = Hcolor = "2px solid #DADADA";
    break;
    case 2 :
      Mcolor = "2px solid #f90";
      Lcolor = Hcolor = "2px solid #DADADA";
    break;
    case 3 :
      Hcolor = "2px solid #3c0";
      Lcolor = Mcolor = "2px solid #DADADA";
    break;
    case 4 :
      Hcolor = "2px solid #3c0";
      Lcolor = Mcolor = "2px solid #DADADA";
    break;
    default :
      Hcolor = Mcolor = Lcolor = "";
    break;
  }
  if (document.getElementById("pwd_lower"))
  {
    document.getElementById("pwd_lower").style.borderBottom  = Lcolor;
    document.getElementById("pwd_middle").style.borderBottom = Mcolor;
    document.getElementById("pwd_high").style.borderBottom   = Hcolor;
  }


}

function changeType(obj)
{
  if (obj.getAttribute("min") && document.getElementById("ECS_AMOUNT"))
  {
    document.getElementById("ECS_AMOUNT").disabled = false;
    document.getElementById("ECS_AMOUNT").value = obj.getAttribute("min");
    if (document.getElementById("ECS_NOTICE") && obj.getAttribute("to") && obj.getAttribute('fee'))
    {
      var fee = parseInt(obj.getAttribute("fee"));
      var to = parseInt(obj.getAttribute("to"));
      if (fee < 0)
      {
        to = to + fee * 2;
      }
      document.getElementById("ECS_NOTICE").innerHTML = notice_result + to;
    }
  }
}

function calResult()
{
  var amount = document.getElementById("ECS_AMOUNT").value;
  var notice = document.getElementById("ECS_NOTICE");

  reg = /^\d+$/;
  if (!reg.test(amount))
  {
    notice.innerHTML = notice_not_int;
    return;
  }
  amount = parseInt(amount);
  var frm = document.forms['transform'];
  for(i=0; i < frm.elements['type'].length; i++)
  {
    if (frm.elements['type'][i].checked)
    {
      var min = parseInt(frm.elements['type'][i].getAttribute("min"));
      var to = parseInt(frm.elements['type'][i].getAttribute("to"));
      var fee = parseInt(frm.elements['type'][i].getAttribute("fee"));
      var result = 0;
      if (amount < min)
      {
        notice.innerHTML = notice_overflow + min;
        return;
      }

      if (fee > 0)
      {
        result = (amount - fee) * to / (min -fee);
      }
      else
      {
        //result = (amount + fee* min /(to+fee)) * (to + fee) / min ;
        result = amount * (to + fee) / min + fee;
      }

      notice.innerHTML = notice_result + parseInt(result + 0.5);
    }
  }
}

//yi:跟踪包裹功能
function fllow(oid)
{
	//获得订单信息数组
	$.ajax({
		url:'user.php?act=fllow_ajax',
		data:'&order_id='+oid+'&m='+Math.random(),
		cache:false,
		success:
		function(dd){
			dd = eval('('+dd+')');
			
			//显示面板信息dd[0]快递公司 dd[1]快递单号 dd[2]url 
			if(dd[2]!=''){
				//加载包裹信息
				var dw = $(document).width();
				var dh = $(document).height();					
				$(".pp_shadow").css({width:dw,height:dh}).show();
				//显示面板
				$("#fllow").show();	
				$("#fllow_id").text(dd[1]);
				$("#fllow_con").text(dd[0]);
				
				if(dd[0]!='' && dd[1]!=''){			
				//获取该订单的物流信息
					$.ajax({		
						url:'plugins/kuaidi100/express.php',
						data:'com='+dd[0]+'&nu='+dd[1],
						cache:false,
						success:
						function(dx){
							$("#fllow_div").html(dx);
						}
					});	
				}else{
					$("#fllow_div").text("<br/><br/><br/>物流信息查询失败，请登录快递官网查询！！");
				}								
			}else{
				//没有发货信息的情况
				$("#fllow_div").text("由于物流信息还未录入，暂无该物流信息，请稍后在试。");
			}	
		}
	});	
}

/*=============关闭模态窗口===============*/
function pclose(){
	$(".pp_shadow").hide();
	$("#fllow").hide();
}

//yi:
function show_receipt(rec_id)
{
	//
	edit_receipt(rec_id);
}

//yi:显示我的验光单
function show_receipt_back(rec_id)
{
	if(rec_id == '')
	{
		return false;
	}
	else
	{
		$("#s_add_receipt_pan").hide();			
		$("#s_edit_receipt_pan").hide();
		$("#s_show_receipt_pan").show();
		
		//获取该验光单的数据，最终显示出来。
		$.ajax({
				type:'post',
				url: 'ajax_step.php?act=get_user_receipt',	
				data:'&rec_id='+rec_id+'&m='+Math.random(),		
				cache:false,
				success:
				function(da)
				{
					var da = eval('('+da+')');
					$("#s_yeye_qiujin").text(da.yeye_qiujin);
					$("#s_zeye_qiujin").text(da.zeye_qiujin);
					$("#s_yeye_zhujin").text(da.yeye_zhujin);
					$("#s_zeye_zhujin").text(da.zeye_zhujin);
					$("#s_yeye_zhouwei").text(da.yeye_zhouwei);
					$("#s_zeye_zhouwei").text(da.zeye_zhouwei);
					$("#s_yeye_shili").text(da.yeye_shili);
					$("#s_zeye_shili").text(da.zeye_shili);
					$("#s_eye_tongju").text(da.eye_tongju);
										
					$("#h_receipt_time").val(da.receipt_time);
					$("#h_receipt_name").val(da.receipt_name);
					$("#h_receipt_unit").val(da.receipt_unit);					
					$("#h_receipt_author").val(da.receipt_author);
					$("#h_receipt_desc").val(da.receipt_desc);																	
				}			
		});	
		
		
		$("#TB_overlayBG").css({
			display:"block",height:$(document).height()
		});
		$(".boxxxxx").css({
			left:($("body").width()-$(".boxxxxx").width())/2-20+"px",
			top:($(window).height()-$(".boxxxxx").height())/2+$(window).scrollTop()+"px",
			display:"block"
		});			
	}
}

function edit_receipt(rec_id)
{
	if(rec_id == '')
	{
		return false;
	}
	else
	{
		$(".pop_div").fadeIn();

		//add data;
		//获取该验光单的数据，最终显示出来。
		$.ajax({
				type:'post',
				url: 'ajax_step.php?act=get_user_receipt',	
				data:'&rec_id='+rec_id+'&m='+Math.random(),		
				cache:false,
				success:
				function(da)
				{
					var da = eval('('+da+')');

					var frm = $("#add_receipt");
					frm.find('[name="receipt_type"]').val(da.receipt_type);
					frm.find('[name="act_type"]').val('edit');//编辑
					frm.find('[name="get_rec_id"]').val(rec_id);

					if(da.receipt_type == 1)
					{
						frm = $("#ying_pop");
					}
					else if(da.receipt_type == 2)
					{
						frm = $("#kuang_pop");
					}
					
					frm.find('[name="yeye_qiujin"]').val(da.yeye_qiujin); 
					frm.find('[name="zeye_qiujin"]').val(da.zeye_qiujin);

					frm.find('[name="yeye_zhujin"]').val(da.yeye_zhujin);
					frm.find('[name="zeye_zhujin"]').val(da.zeye_zhujin);

					frm.find('[name="yeye_zhouwei"]').val(da.yeye_zhouwei);
					frm.find('[name="zeye_zhouwei"]').val(da.zeye_zhouwei);
					frm.find('[name="eye_tongju"]').val(da.eye_tongju);												
				}			
		});	
				
	}
}

//yi:修改我的验光单
function edit_receipt_back(rec_id)
{
	if(rec_id == '')
	{
		return false;
	}
	else
	{
		$("#s_add_receipt_pan").hide();
		$("#s_show_receipt_pan").hide();	
		$("#s_edit_receipt_pan").show();
		$("#edit_receipt_form").val(rec_id);
		
		//获取该验光单的数据，最终显示出来。
		$.ajax({
				type:'post',
				url: 'ajax_step.php?act=get_user_receipt',	
				data:'&rec_id='+rec_id+'&m='+Math.random(),		
				cache:false,
				success:
				function(da)
				{
					var da = eval('('+da+')');
					$("#yeye_qiujin").val(da.yeye_qiujin);
					$("#zeye_qiujin").val(da.zeye_qiujin);
					$("#yeye_zhujin").val(da.yeye_zhujin);
					$("#zeye_zhujin").val(da.zeye_zhujin);
					$("#yeye_shili").val(da.yeye_shili);
					$("#zeye_shili").val(da.zeye_shili);
					$("#yeye_zhouwei").val(da.yeye_zhouwei);
					$("#zeye_zhouwei").val(da.zeye_zhouwei);
					$("#eye_tongju").val(da.eye_tongju);
										
					$("#s_receipt_time").val(da.receipt_time);
					$("#s_receipt_name").val(da.receipt_name);
					$("#s_receipt_unit").val(da.receipt_unit);					
					$("#s_receipt_author").val(da.receipt_author);
					$("#s_receipt_desc").val(da.receipt_desc);													
				}			
		});		
		
	
		
		$("#TB_overlayBG").css({
			display:"block",height:$(document).height()
		});
		$(".boxxxxx").css({
			left:($("body").width()-$(".boxxxxx").width())/2-20+"px",
			top:($(window).height()-$(".boxxxxx").height())/2+$(window).scrollTop()+"px",
			display:"block"
		});			
	}	
}


//xu:我的验光单功能
$(function(){
	
	$(".showbox_x").click(function(){
	
		$("#s_show_receipt_pan").hide();	
		$("#s_edit_receipt_pan").hide();	
		$("#s_add_receipt_pan").show();

				
		$("#TB_overlayBG").css({
			display:"block",height:$(document).height()
		});
		$(".boxxxxx").css({
			left:($("body").width()-$(".boxxxxx").width())/2-20+"px",
			top:($(window).height()-$(".boxxxxx").height())/2+$(window).scrollTop()+"px",
			display:"block"
		});
	});
	
	$(".close").click(function(){
		$("#TB_overlayBG").css("display","none");
		$(".boxxxxx ").css("display","none");
	});	
})

//==============================================【会员中心-资金管理-提现】========================================//
//局部更新提现 银行 所在城市	
function changed(ths)
{
	$("#tx_city").text('');
	$.ajax({
		url:"user.php",
		data:"act=ajax_city_list&p_id="+ths.value,
		cache:false,
		success:function(data){
			eval("var d ="+data);			
			var city_list = '';
			for(var i=0; i<d.length; i++){
				city_list += '<option value="'+d[i]['region_name']+'">'+d[i]['region_name']+'</option>';					
			}
			$("#tx_city").append(city_list);
			city_list = '';
		}
	});
}


//提现表单的验证
//tx_type => 提现方式（1，支付宝 2，银行卡）
function submit_tx(tx_type)
{
	if(tx_type == 'undefinded')
	{
		return false;
	}
	
	if(2 == tx_type)
	{
		//银行卡提现
		if($("#province_list").val() == 0)
		{
			alert("请选择银行所在省份!"); return false;
		}
		if($("#tx_city").val() == 0)
		{
			alert("请选择银行所在地区!"); return false;
		}			
		if($("#form_account_raply_bank input[name='bank_name_detail']").val() =='' || $("#form_account_raply_bank input[name='bank_name']").val()==0)
		{
			alert("请填写开户行具体名称（即***支行）!"); return false;
		}		
						
		if( $("#form_account_raply_bank input[name='pay_account']").val() =='' ){
			alert("请填写银行卡账号!"); return false;
		}
		if( $("#form_account_raply_bank input[name='bank_man']").val() =='' ){
			alert("请填写开户人姓名!"); return false;
		}	
		
		if($("#form_account_raply_bank input[name='amount']").val() ==''||$("#form_account_raply_bank input[name='amount']").val()<=0){
			alert("请填写提现金额!"); return false;
		}
		if( $("#form_account_raply_bank input[name='user_tel']").val() =='' ){
			alert("请填写联系电话!"); return false;
		}		
		$("#form_account_raply_bank").submit();//submit			
	}
	else if(1 == tx_type)
	{
		//支付宝提现
		//if( $("#form_account_raply input[name='pay_account']").val() =='' ){
		if($("#user_alipy_account").val() == ''){
			alert("请填写支付宝账号!"); return false;
		}
		if( $("#form_account_raply input[name='amount']").val() =='' ){
			alert("请填写提现金额!"); return false;
		}
		if( $("#form_account_raply input[name='user_tel']").val() =='' ){
			alert("请填写联系电话!"); return false;
		}
		$("#form_account_raply").submit();									
	}
	else
	{
	}		
}
//==============================================【会员中心-资金管理-提现end】========================================//
