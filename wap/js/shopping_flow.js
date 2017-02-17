/*----------------------------------------------------------------------------购物车中表单信息提交函数2011-4-21-------------------------------------------------------------------*/
var selectedShipping = null;
var selectedPayment  = null;
var selectedPack     = null;
var selectedCard     = null;
var selectedSurplus  = '';
var selectedBonus    = 0;
var selectedIntegral = 0;
var selectedOOS      = null;
var alertedSurplus   = false;
var groupBuyShipping = null;
var groupBuyPayment  = null;

//改变配送方式 -- ajax改变统计金额
function selectShipping(obj)
{
  if(selectedShipping == obj)
  {
    return;
  }
  else
  {
    selectedShipping = obj;
  }
  
  var supportCod = obj.attributes['supportCod'].value + 0;
  var now = new Date();
  Ajax.call('flow.php?step=select_shipping', 'shipping=' + obj.value, orderShippingSelectedResponse, 'GET', 'JSON');
}

//yi:更新统计结算面板
function yi_selectShipping(obj_value)
{
  var now = new Date();
  Ajax.call('flow.php?step=select_shipping', 'shipping=' + obj_value, orderShippingSelectedResponse, 'GET', 'JSON');
}

/**
 *
 */
function orderShippingSelectedResponse(result)
{
  if(result.need_insure)
  {
    try
    {
      document.getElementById('ECS_NEEDINSURE').checked = true;
    }
    catch (ex)
    {
      alert(ex.message);
    }
  }

  try
  {
    if (document.getElementById('ECS_CODFEE') != undefined)
    {
      document.getElementById('ECS_CODFEE').innerHTML = result.cod_fee;
    }
  }
  catch (ex)
  {
    alert(ex.message);
  }
 
  orderSelectedResponse(result);
}

/* *
 * 改变支付方式
 */
function selectPayment(obj)
{
	if(selectedPayment == obj)
	{
		return;
	}
	else
	{
		selectedPayment = obj;
	}
	Ajax.call('flow.php?step=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}
/* *
 * 团购购物流程 --> 改变配送方式
 */
function handleGroupBuyShipping(obj)
{
  if (groupBuyShipping == obj)
  {
    return;
  }
  else
  {
    groupBuyShipping = obj;
  }

  var supportCod = obj.attributes['supportCod'].value + 0;
  var theForm = obj.form;

  for (i = 0; i < theForm.elements.length; i ++ )
  {
    if (theForm.elements[i].name == 'payment' && theForm.elements[i].attributes['isCod'].value == '1')
    {
      if (supportCod == 0)
      {
        theForm.elements[i].checked = false;
        theForm.elements[i].disabled = true;
      }
      else
      {
        theForm.elements[i].disabled = false;
      }
    }
  }

  if (obj.attributes['insure'].value + 0 == 0)
  {
    document.getElementById('ECS_NEEDINSURE').checked = false;
    document.getElementById('ECS_NEEDINSURE').disabled = true;
  }
  else
  {
    document.getElementById('ECS_NEEDINSURE').checked = false;
    document.getElementById('ECS_NEEDINSURE').disabled = false;
  }

  Ajax.call('group_buy.php?act=select_shipping', 'shipping=' + obj.value, orderSelectedResponse, 'GET');
}

/* *
 * 团购购物流程 --> 改变支付方式
 */
function handleGroupBuyPayment(obj)
{
  if (groupBuyPayment == obj)
  {
    return;
  }
  else
  {
    groupBuyPayment = obj;
  }

  Ajax.call('group_buy.php?act=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET');
}

/* *
 * 改变商品包装
 */
function selectPack(obj)
{
  if (selectedPack == obj)
  {
    return;
  }
  else
  {
    selectedPack = obj;
  }

  Ajax.call('flow.php?step=select_pack', 'pack=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 改变祝福贺卡
 */
function selectCard(obj)
{
  if (selectedCard == obj)
  {
    return;
  }
  else
  {
    selectedCard = obj;
  }

  Ajax.call('flow.php?step=select_card', 'card=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 选定了配送保价
 */
function selectInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('flow.php?step=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 团购购物流程 --> 选定了配送保价
 */
function handleGroupBuyInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('group_buy.php?act=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 回调函数
 */
function orderSelectedResponse(result)
{
  if (result.error)
  {
    alert(result.error);
    location.href = './';
  }

  try
  {
    var layer = document.getElementById("ECS_ORDERTOTAL");
    
    layer.innerHTML = (typeof result == "object") ? result.content : result;

    if(result.payment != undefined)
    {
      var surplusObj = document.forms['theForm'].elements['surplus'];
      if(surplusObj != undefined)
      {
        surplusObj.disabled = result.pay_code == 'balance';
      }
    }
  }
  catch (ex) { }
}

//yi:点击使用余额按钮
function use_yue(yue){
	//能够使用的余额
	var able_yue = document.forms['cart_submit'].elements['able_use_surplus'].value;
	//if( yue > parseInt(able_yue)){
	if( yue > parseFloat(able_yue)){
		alert('您的余额不足，请输入正确余额值！');
		return false;
	}
	
	if(yue == ''){
		alert('请输入余额！');
		return false;		
	}else{
		changeSurplus(yue);
	}
}

/* *
 * 改变余额
 */
function changeSurplus(val)
{
  if(selectedSurplus == val){
    return false;
  }else{
    selectedSurplus = val;
  }
  Ajax.call('flow.php?step=change_surplus', 'surplus='+val, changeSurplusResponse, 'GET', 'JSON');
}

/* *
 * 改变余额回调函数
 */
function changeSurplusResponse(obj)
{
  if(obj.error)
  {
    try
    {
      //YI:document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = obj.error;
      alert(obj.error);
      document.getElementById('ECS_SURPLUS').value = '0';
      //document.getElementById('ECS_SURPLUS').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = '';
    }
    catch (ex) { }
    orderSelectedResponse(obj.content);
  }
}

/* *
 * 改变积分
 */
function changeIntegral(val)
{
  if (selectedIntegral == val)
  {
    return;
  }
  else
  {
    selectedIntegral = val;
  }

  Ajax.call('flow.php?step=change_integral', 'points=' + val, changeIntegralResponse, 'GET', 'JSON');
}

/* *
 * 改变积分回调函数
 */
function changeIntegralResponse(obj)
{
  if (obj.error)
  {
    try
    {
      document.getElementById('ECS_INTEGRAL_NOTICE').innerHTML = obj.error;
      document.getElementById('ECS_INTEGRAL').value = '0';
      document.getElementById('ECS_INTEGRAL').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById('ECS_INTEGRAL_NOTICE').innerHTML = '';
    }
    catch (ex) { }
    orderSelectedResponse(obj.content);
  }
}

/* *
 * 改变红包
 */
function changeBonus(val)
{
  if (document.getElementById('bonus_sn') && document.getElementById('bonus_sn').value != '')
  {
    alert('一个订单只允许使用一个优惠券');
	return false;
  }
  if(selectedBonus == val)
  {
    return false;
  }
  else
  {
    selectedBonus = val;
  }
  var pay_id = document.getElementById("get_pay_id").value;

  //Ajax.call('flow.php?step=change_bonus', 'bonus='+val+'&pay_id='+pay_id, changeBonusResponse, 'GET', 'JSON');
  $.ajax({
                type:'get',
                dateType:'JSON',
				url:'flow.php?step=change_bonus',
				data:'bonus='+val+'&pay_id='+pay_id,				
				success:function(dd){
			     changeBonusResponse(dd);
				}
			});
}

/* *
 * 改变红包的回调函数
 */
function changeBonusResponse(obj)
{
    obj = eval("("+obj+")");
  if(obj.error)
  {
    alert(obj.error);
    try
    {
      document.getElementById('ECS_BONUS').value = '0';
    }
    catch (ex) { }
  }
  else
  {
    orderSelectedResponse(obj.content);
  }
}

/**
 * 验证红包序列号 给出用户提示信息
 * @param string bonusSn 红包序列号
 */
function validateBonus(bonusSn)
{
	if (document.getElementById('ECS_BONUS') && document.getElementById('ECS_BONUS').value != '0')
	{
		alert('一个订单只允许使用一个优惠券');
		return;
	}
	if(bonusSn == ''){
		alert('请输入您的优惠券编号后点击使用!');
	}else{
		//Ajax.call('flow.php?step=validate_bonus', 'bonus_sn=' + bonusSn, validateBonusResponse, 'GET', 'JSON');

        $.ajax({
			type: "GET",
            dataType:'JSON',
			url: "flow.php?step=validate_bonus",
			data: {bonus_sn:bonusSn},
			success:function(d){
                var data = eval("(" + d + ")");
                //document.write(d);return false;
                //alert(data.error);return false;
				validateBonusResponse(data);
			}
		});
        
        
        
	}
}

function validateBonusResponse(obj)
{
	  if(obj.error)
	  {
			alert(obj.error);			
			//orderSelectedResponse(obj.content); return false;
			//清空选择框中的优惠券编号
			document.forms['cart_submit'].elements['bonus_sn'].value = '';
			try
			{
				  document.getElementById('ECS_BONUSN').value = '0';
			}
			catch(ex){				
			}
	  }
	  else
	  {
			//后台返回数据没有错误! 红包正常使用
			orderSelectedResponse(obj.content);
			alert('您已成功使用优惠券!');
	  }
}

/* -------------------------------------------------------------------------------------------------
 * yi:函数 提交订单之前检查红包是否合法
 * -------------------------------------------------------------------------------------------------
 */
function check_bonus_ok()
{
	var bonus_sn = document.forms['cart_submit'].elements['bonus_sn'].value;
	if(bonus_sn == '')
	{
		return true;
	}
	else
	{
		
	}
}


/*---------------改变发票的方式--------------------------*/
function changeNeedInv2()
{
  var obj        = document.getElementById('ECS_NEEDINV');
  //填写发票抬头框
  var objPayee   = document.getElementById('ECS_INVPAYEE');

  //alert(obj.checked);
  var needInv    = obj.checked ? 1 : 0; 
  //发票抬头的值
  var invPayee   = obj.checked ? objPayee.value : '';
  /*--yi--同步改变的地方----*/
  objPayee.disabled = !obj.checked;

//  Ajax.call('flow.php?step=change_needinv', 'need_inv=' + needInv + '&inv_payee=' + encodeURIComponent(invPayee) , orderSelectedResponse, 'GET');
   $.ajax({
			type: "GET",
            dataType:'JSON',
			url: "flow.php?step=change_needinv",
			data: {need_inv:needInv,inv_payee:encodeURIComponent(invPayee)},
			success:function(d){
				orderSelectedResponse(d);
			}
		});
}

/*----原先脚本函数----------*/
function changeNeedInv()
{
  var obj        = document.getElementById('ECS_NEEDINV'); 
  var objType    = document.getElementById('ECS_INVTYPE');
  var objPayee   = document.getElementById('ECS_INVPAYEE');
  var objContent = document.getElementById('ECS_INVCONTENT');
  var needInv    = obj.checked ? 1 : 0;
  var invType    = obj.checked ? (objType != undefined ? objType.value : '') : '';
  var invPayee   = obj.checked ? objPayee.value : '';
  var invContent = obj.checked ? objContent.value : '';
  objType.disabled = objPayee.disabled = objContent.disabled = ! obj.checked;  
  if(objType != null)
  {
    objType.disabled = ! obj.checked;
  }
  Ajax.call('flow.php?step=change_needinv', 'need_inv=' + needInv + '&inv_type=' + encodeURIComponent(invType) + '&inv_payee=' + encodeURIComponent(invPayee) + '&inv_content=' + encodeURIComponent(invContent), orderSelectedResponse, 'GET');
}

/* *
 * 改变发票的方式
 */
function groupBuyChangeNeedInv()
{
  var obj        = document.getElementById('ECS_NEEDINV');
  var objPayee   = document.getElementById('ECS_INVPAYEE');
  var objContent = document.getElementById('ECS_INVCONTENT');
  var needInv    = obj.checked ? 1 : 0;
  var invPayee   = obj.checked ? objPayee.value : '';
  var invContent = obj.checked ? objContent.value : '';
  objPayee.disabled = objContent.disabled = ! obj.checked;

  Ajax.call('group_buy.php?act=change_needinv', 'need_idv=' + needInv + '&amp;payee=' + invPayee + '&amp;content=' + invContent, null, 'GET');
}

/* *
 * 改变缺货处理时的处理方式
 */
function changeOOS(obj)
{
  if (selectedOOS == obj)
  {
    return;
  }
  else
  {
    selectedOOS = obj;
  }

  Ajax.call('flow.php?step=change_oos', 'oos=' + obj.value, null, 'GET');
}

/* *
 * 检查提交的订单表单
 */
function checkOrderForm(frm)
{
  var paymentSelected = false;
  var shippingSelected = false;

  // 检查是否选择了支付配送方式
  for (i = 0; i < frm.elements.length; i ++ )
  {
    if (frm.elements[i].name == 'shipping' && frm.elements[i].checked)
    {
      shippingSelected = true;
    }

    if (frm.elements[i].name == 'payment' && frm.elements[i].checked)
    {
      paymentSelected = true;
    }
  }

  if ( ! shippingSelected)
  {
    alert(flow_no_shipping);
    return false;
  }

  if ( ! paymentSelected)
  {
    alert(flow_no_payment);
    return false;
  }

  // 检查用户输入的余额
  if (document.getElementById("ECS_SURPLUS"))
  {
    var surplus = document.getElementById("ECS_SURPLUS").value;
    var error   = Utils.trim(Ajax.call('flow.php?step=check_surplus', 'surplus=' + surplus, null, 'GET', 'TEXT', false));

    if (error)
    {
      try
      {
        document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = error;
      }
      catch (ex)
      {
      }
      return false;
    }
  }

  // 检查用户输入的积分
  if (document.getElementById("ECS_INTEGRAL"))
  {
    var integral = document.getElementById("ECS_INTEGRAL").value;
    var error    = Utils.trim(Ajax.call('flow.php?step=check_integral', 'integral=' + integral, null, 'GET', 'TEXT', false));

    if (error)
    {
      return false;
      try
      {
        document.getElementById("ECS_INTEGRAL_NOTICE").innerHTML = error;
      }
      catch (ex)
      {
      }
    }
  }
  frm.action = frm.action + '?step=done';
  return true;
}

/* *
 * 检查收货地址信息表单中填写的内容
 */
function checkConsignee(frm)
{
  var msg = new Array();
  var err = false; //标志:是否有错.

  if (frm.elements['country'] && frm.elements['country'].value == 0)
  {
    msg.push(country_not_null);
    err = true;
  }

  if (frm.elements['province'] && frm.elements['province'].value == 0 && frm.elements['province'].length > 1)
  {
    err = true;
    msg.push(province_not_null);
  }

  if (frm.elements['city'] && frm.elements['city'].value == 0 && frm.elements['city'].length > 1)
  {
    err = true;
    msg.push(city_not_null);
  }

  if (frm.elements['district'] && frm.elements['district'].length > 1)
  {
    if (frm.elements['district'].value == 0)
    {
      err = true;
      msg.push(district_not_null);
    }
  }

  if (Utils.isEmpty(frm.elements['consignee'].value))
  {
    err = true;
    msg.push(consignee_not_null);
  }

  if ( ! Utils.isEmail(frm.elements['email'].value))
  {
    err = true;
    //msg.push(invalid_email);
    msg.push('邮箱格式错误!');
  }

  if (frm.elements['address'] && Utils.isEmpty(frm.elements['address'].value))
  {
    err = true;
    msg.push(address_not_null);
  }

  if (frm.elements['zipcode'] && frm.elements['zipcode'].value.length > 0 && (!Utils.isNumber(frm.elements['zipcode'].value)))
  {
    err = true;
    msg.push(zip_not_num);
  }
  
  // if (Utils.isEmpty(frm.elements['tel'].value))

  if (Utils.isEmpty(frm.elements['tel'].value)&&Utils.isEmpty(frm.elements['mobile'].value))
  {
    err = true;
    //msg.push(tele_not_null);
	//msg.push('电话和手机必须填写一个');
	msg.push('联系电话必须填写');
  }
  else
  {
    if (!Utils.isTel(frm.elements['tel'].value)&&frm.elements['tel'].value)
    {
      err = true;
      msg.push('电话号码填写错误,请您修正!');
    }
	
	// if (!Utils.isTel(frm.elements['mobile'].value)&&frm.elements['mobile'].value)
    // {
	// //  err = true;
	// //  msg.push(tele_invaild);
	// //  msg.push('手机号码不是合法号码');
    // }
  }
  
  // if (frm.elements['mobile'] && frm.elements['mobile'].value.length > 0 && (!Utils.isTel(frm.elements['mobile'].value)))
  // {
	   // err = true;
	   // msg.push(mobile_invaild);
  // }

  if(err)
  {
    message = msg.join("\n");
    alert(message);
  }
  return ! err;
}
//yi:开发票功能
function changelist(thi){
	if(thi.value == 1){
		document.getElementById('inv_head').style.display='';
	}
	if(thi.value == 0){
		document.getElementById('inv_head').style.display='none';
	}
}
