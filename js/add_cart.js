/*-------------------------------------------【加入购物车20120830】【Author:yijiangwen】【TIME:20120828】-------------------------------------------*/

//功能:加入购物车, 分页, 图片切换处理. 注意:加载此函数需先加载jquery.

$(document).ready(function(){

/*----------------------------------------------------------------【页头脚本】-------------------------------------------------------------------*/
	//yi:菜单条
	$("#nav > li:not(:first)").hover(
		function(){			
			$(this).addClass("nav_on nav_bg"+$(this).index()).children("div").show();
		},
		function(){			
			$(this).removeClass("nav_on nav_bg"+$(this).index()).children("div").hide();
		}
	);	
});


//导航删除购物车商品a:rec_id,b:字符串.
function dropHeadFlowNum(a,b){
	if( confirm(b) ){
		//要取得返回的值		
		$.ajax({
			type:'GET',
			url:'/flow.php?step=ajax_drop_goods',
			data:"id="+a,
			success:function(data){
				var retn   = data.split(",");
				var num    = retn[0];
				var rec_id = retn[1];
				var sum    = retn[2];
				
				$("#cart_info > .red_bold").text(num);
					
				//如果购物车空
				if(num == 0){
					$(".cart_pop").detach();
				}else{	
					//把面板内容从购物车中删除掉.
					$("dl[id="+rec_id+"]").detach();
					
					//更新购物车条
					$(".cart_count > font").eq(0).text(num);
					$(".cart_count > font").eq(1).text("￥"+sum+".00元");
				}
			}
		});
	}
}
//导航中删除礼包商品
function dropPackage(a,b){
	if( confirm(b) ){
		//要取得返回的值		
		$.ajax({
			type:'GET',
			url:'/flow.php?step=ajax_drop_package',
			data:"id="+a,
			success:function(data){			
				var retn   = data.split(",");
				var num    = retn[0];
				var sum    = retn[1];
								
				$("#cart_info > .red_bold").text(num);
				
				//如果购物车空
				if(num == 0){
					$(".cart_pop").detach();
				}else{	
					//把面板内容从购物车中删除掉.
					for( var i=2; i<retn.length; i++){ 
						$("dl[id="+retn[i]+"]").detach();
					}
					//在这里更新购物车中的数据.
					$(".cart_count > font").eq(0).text(num);
					$(".cart_count > font").eq(1).text("￥"+sum+".00元");
				}
			}
		});
	}	
}
/*----------------------------------------------------------------jquery代码结束--------------------------------------------------------------------*/

//------------------------------------------------------------添加商品到购物车（没有度数的） $参数：商品id号,有parentId：配件------------------------------------------------------------------ 
function addToCartz(goodsId, parentId)
{	
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var formBuy      = document.forms['ECS_FORMBUY'];
	var quick		 = 0;
	var bz           = 0;//步骤
	
	// 检查是否有商品规格 
	if(formBuy)
	{
		spec_arr = getSelectedAttributes(formBuy);
		if(formBuy.elements['number'])
		{
		  number = formBuy.elements['number'].value;
		}
		quick = 1;
	} 
	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;  
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
	Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
}

//------------------------------------------------------------添加商品到购物车（有度数）参数：商品id号,parentId：配件(可选)------------------------------------------- 
function addToCart(goodsId, parentId)
{	
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var formBuy      = document.forms['ECS_FORMBUY'];
	var quick		 = 0;
	var bz           = 0;
	
	var zselect="";
	var zcount =0;
	var yselect="";
	var ycount =0;
	//====================左右眼度数数量====================||
	zselect=(document.ECS_FORMBUY.zselect.value);
	yselect=(document.ECS_FORMBUY.yselect.value);
	zcount = parseInt(document.ECS_FORMBUY.zcount.value);
	ycount = parseInt(document.ECS_FORMBUY.ycount.value); 

	//检查是否有商品规格 
	if(formBuy){
		spec_arr = getSelectedAttributes(formBuy);
		if(formBuy.elements['number']){
		    number = formBuy.elements['number'].value;
		}
		quick = 1;
	}  
	//没有选择数量的可以过,但是到购物车这取消这一侧条记录
	if( zselect.length>0 && zcount==0){ zselect = "";}
	if( yselect.length>0 && ycount==0){ yselect = "";}
	if( yselect.length==0 && ycount==1){ ycount = 0;}
	if( zselect.length==0 && zcount==1){ zcount = 0;}		
	if((zselect.length>0&&zcount>0&&yselect.length==0&&ycount==0)||(yselect.length>0&&ycount>0&&zselect.length==0&&zcount==0)||(zselect.length>0&&zcount>0&&yselect.length>0&&ycount>0)){
		 bz=1;
		 number =zcount*1+ycount*1;
	}  

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;	
	goods.zselect  = zselect;
	goods.zcount   = zcount;
	goods.yselect  = yselect;
	goods.ycount   = ycount;
 
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
	if(bz){
    	Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
		return true;
	}else{
		if(zselect.length<1&&yselect.length<1&&(zcount+ycount)<1){alert('请选择眼镜度数和数量');return false;}
				
		if(zselect.length<1&&yselect.length<1&&(zcount*1+ycount*1)>0){alert('请选择眼镜度数');return false;}	
		if(zselect.length<1&&yselect.length>0&&ycount>0&&zcount>0){alert('请选择左眼度数');return false;}		
		if(yselect.length<1&&zselect.length>0&&zcount>0&&ycount>0){alert('请选择右眼度数');return false;}		
				
		if((zselect.length+yselect.length)>0&&(zcount*1+ycount*1)==0){alert('请选择眼镜数量');return false;}	
		if(zselect.length>0&&yselect.length>0&& zcount==0&&ycount>0){alert('请选择左眼数量');return false;}
		if(zselect.length>0&&yselect.length>0&& zcount>0&&ycount==0){alert('请选择右眼数量');return false;}			
	}
}


//带有属性商品加入购物车(左右眼度数,数量默认为1)
function addCart_attr(goodsId, zsclect, yselect){
	var goods  = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var zcount       = 0;
	var ycount       = 0;

	if(zsclect.length>0 && ysclect.length>0){number = 2;}
	if(zsclect.length>0){ zcount = 1;}
	if(ysclect.length>0){ ycount = 1;}
	
	goods.quick    = 0;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;
	
	goods.zselect   = zselect;
	goods.zcount    = zcount;
	goods.yselect   = yselect;
	goods.ycount    = ycount;

	Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addCart_attr_response, 'POST', 'JSON');	
}


//回调函数
function addCart_attr_response(){	
	alert("商品已经加入购物车!");
}


//------------------------------------------------添加商品到购物车---回调函数(购物车显示修改在这里)--------------------------------------------------------------------------
function addToCartResponse(result)
{
  if(result.error > 0)
  {
    //如果需要缺货登记，跳转
    if(result.error == 2)
    {/*
	  //return false;
	  if(confirm(result.message)){
        location.href = 'user.php?act=add_booking&id=' + result.goods_id;
      }
	  */
    }
    //没选规格，弹出属性选择框
    else if (result.error == 6)
    {
      openSpeDiv(result.message, result.goods_id, result.parent);
    }
    else
    {
      alert(result.message); 
    }
  }
  else
  {
	  //加入购物车成功
    var cartInfo = document.getElementById('ECS_CARTINFO');
    var cart_url = 'flow.php?step=cart';
    if (cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }

    if (result.one_step_buy == '1')
    {
      location.href = cart_url;
    }
    else
    {
      switch(result.confirm_type)
      {
        case '1' :
          if (confirm(result.message)) location.href = cart_url;
          break;
        case '2' :
          if (!confirm(result.message)) location.href = cart_url;
          break;
        case '3' :
		  //alert("在这里局部刷新购物车！");
          //location.href = cart_url;
		  document.getElementById('div_cart_info_num').innerHTML = result.content;
		  document.getElementById('cart_info_num').innerHTML     = result.content;	  
          break;
        default :
          break;
      }
    }
  }
  //return true;
}


/**
 * 获得选定的商品属性
 */
function getSelectedAttributes(formBuy)
{
  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < formBuy.elements.length; i ++ )
  {
    var prefix = formBuy.elements[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((formBuy.elements[i].type == 'radio' || formBuy.elements[i].type == 'checkbox') && formBuy.elements[i].checked) ||
      formBuy.elements[i].tagName == 'SELECT'))
    {
      spec_arr[j] = formBuy.elements[i].value;
      j++ ;
    }
  }

  return spec_arr;
}


/* *
 * 处理收藏商品的反馈信息:回调函数
 */
function collectResponse(result)
{
  alert(result.message);
}

/* *
 * 处理会员登录的反馈信息
 */
function signInResponse(result)
{
  toggleLoader(false);

  var done    = result.substr(0, 1);
  var content = result.substr(2);

  if (done == 1)
  {
    document.getElementById('member-zone').innerHTML = content;
  }
  else
  {
    alert(content);
  }
}

/* *
 * 评论的翻页函数
 */
function gotoPage(page, id, type)
{
  Ajax.call('comment.php?act=gotopage', 'page=' + page + '&id=' + id + '&type=' + type, gotoPageResponse, 'GET', 'JSON');
}

function gotoPageResponse(result)
{
  document.getElementById("ECS_COMMENT").innerHTML = result.content;
}

/* *
 * 取得格式化后的价格
 * @param : float price
 */
function getFormatedPrice(price)
{
  if (currencyFormat.indexOf("%s") > - 1)
  {
    return currencyFormat.replace('%s', advFormatNumber(price, 2));
  }
  else if (currencyFormat.indexOf("%d") > - 1)
  {
    return currencyFormat.replace('%d', advFormatNumber(price, 0));
  }
  else
  {
    return price;
  }
}

/* *
 *  返回属性列表
 */
function getAttr(cat_id)
{
  var tbodies = document.getElementsByTagName('tbody');
  for (i = 0; i < tbodies.length; i ++ )
  {
    if (tbodies[i].id.substr(0, 10) == 'goods_type')tbodies[i].style.display = 'none';
  }

  var type_body = 'goods_type_' + cat_id;
  try
  {
    document.getElementById(type_body).style.display = '';
  }
  catch (e)
  {
  }
}

/* *
 * 截取小数位数
 */
function advFormatNumber(value, num) // 四舍五入
{
  var a_str = formatNumber(value, num);
  var a_int = parseFloat(a_str);
  if (value.toString().length > a_str.length)
  {
    var b_str = value.toString().substring(a_str.length, a_str.length + 1);
    var b_int = parseFloat(b_str);
    if (b_int < 5)
    {
      return a_str;
    }
    else
    {
      var bonus_str, bonus_int;
      if (num == 0)
      {
        bonus_int = 1;
      }
      else
      {
        bonus_str = "0."
        for (var i = 1; i < num; i ++ )
        bonus_str += "0";
        bonus_str += "1";
        bonus_int = parseFloat(bonus_str);
      }
      a_str = formatNumber(a_int + bonus_int, num)
    }
  }
  return a_str;
}

function formatNumber(value, num) // 直接去尾
{
  var a, b, c, i;
  a = value.toString();
  b = a.indexOf('.');
  c = a.length;
  if (num == 0)
  {
    if (b != - 1)
    {
      a = a.substring(0, b);
    }
  }
  else
  {
    if (b == - 1)
    {
      a = a + ".";
      for (i = 1; i <= num; i ++ )
      {
        a = a + "0";
      }
    }
    else
    {
      a = a.substring(0, b + num + 1);
      for (i = c; i <= b + num; i ++ )
      {
        a = a + "0";
      }
    }
  }
  return a;
}

/* *
 * 根据当前shiping_id设置当前配送的的保价费用，如果保价费用为0，则隐藏保价费用
 *
 * return       void
 */
function set_insure_status()
{
  // 取得保价费用，取不到默认为0
  var shippingId = getRadioValue('shipping');
  var insure_fee = 0;
  if (shippingId > 0)
  {
    if (document.forms['theForm'].elements['insure_' + shippingId])
    {
      insure_fee = document.forms['theForm'].elements['insure_' + shippingId].value;
    }
    // 每次取消保价选择
    if (document.forms['theForm'].elements['need_insure'])
    {
      document.forms['theForm'].elements['need_insure'].checked = false;
    }

    // 设置配送保价，为0隐藏
    if (document.getElementById("ecs_insure_cell"))
    {
      if (insure_fee > 0)
      {
        document.getElementById("ecs_insure_cell").style.display = '';
        setValue(document.getElementById("ecs_insure_fee_cell"), getFormatedPrice(insure_fee));
      }
      else
      {
        document.getElementById("ecs_insure_cell").style.display = "none";
        setValue(document.getElementById("ecs_insure_fee_cell"), '');
      }
    }
  }
}

/* *
 * 当支付方式改变时出发该事件
 * @param       pay_id      支付方式的id
 * return       void
 */
function changePayment(pay_id)
{
  // 计算订单费用
  calculateOrderFee();
}

function getCoordinate(obj)
{
  var pos =
  {
    "x" : 0, "y" : 0
  }

  pos.x = document.body.offsetLeft;
  pos.y = document.body.offsetTop;

  do
  {
    pos.x += obj.offsetLeft;
    pos.y += obj.offsetTop;

    obj = obj.offsetParent;
  }
  while (obj.tagName.toUpperCase() != 'BODY')

  return pos;
}

function showCatalog(obj)
{
  var pos = getCoordinate(obj);
  var div = document.getElementById('ECS_CATALOG');

  if (div && div.style.display != 'block')
  {
    div.style.display = 'block';
    div.style.left = pos.x + "px";
    div.style.top = (pos.y + obj.offsetHeight - 1) + "px";
  }
}

function hideCatalog(obj)
{
  var div = document.getElementById('ECS_CATALOG');

  if (div && div.style.display != 'none') div.style.display = "none";
}

//发送邮箱验证
function sendHashMail()
{
  Ajax.call('user.php?act=send_hash_mail', '', sendHashMailResponse, 'GET', 'JSON');
}

function sendHashMailResponse(result)
{
  //alert(result.message);
  //提示用户去邮箱
  if(result.error ==0)
  {
	  alert("验证邮件发送成功!请到您的邮箱确认.");
  }else{
	  alert(result.message);
  }
}

/* 订单查询 */
function orderQuery()
{
  var order_sn = document.forms['ecsOrderQuery']['order_sn'].value;

  var reg = /^[\.0-9]+/;
  if (order_sn.length < 10 || ! reg.test(order_sn))
  {
    alert(invalid_order_sn);
    return;
  }
  Ajax.call('user.php?act=order_query&order_sn=s' + order_sn, '', orderQueryResponse, 'GET', 'JSON');
}

function orderQueryResponse(result)
{
  if (result.message.length > 0)
  {
    alert(result.message);
  }
  if (result.error == 0)
  {
    var div = document.getElementById('ECS_ORDER_QUERY');
    div.innerHTML = result.content;
  }
}

function display_mode(str)
{
    document.getElementById('display').value = str;
    setTimeout(doSubmit, 0);
    function doSubmit() {document.forms['listform'].submit();}
}

function display_mode_wholesale(str)
{
    document.getElementById('display').value = str;
    setTimeout(doSubmit, 0);
    function doSubmit() 
    {
        document.forms['wholesale_goods'].action = "wholesale.php";
        document.forms['wholesale_goods'].submit();
    }
}

//----------------------------------------------ecshop官方的解决ie6中png图片不透明问题---------------------------------------//
/* 修复IE6以下版本PNG图片Alpha */
function fixpng()
{
  var arVersion = navigator.appVersion.split("MSIE")
  var version = parseFloat(arVersion[1])

  if ((version >= 5.5) && (document.body.filters))
  {
     for(var i=0; i<document.images.length; i++)
     {
        var img = document.images[i]
        var imgName = img.src.toUpperCase()
        if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
        {
           var imgID = (img.id) ? "id='" + img.id + "' " : ""
           var imgClass = (img.className) ? "class='" + img.className + "' " : ""
           var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
           var imgStyle = "display:inline-block;" + img.style.cssText
           if (img.align == "left") imgStyle = "float:left;" + imgStyle
           if (img.align == "right") imgStyle = "float:right;" + imgStyle
           if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
           var strNewHTML = "<span " + imgID + imgClass + imgTitle
           + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
           + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
           + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>"
           img.outerHTML = strNewHTML
           i = i-1
        }
     }
  }
}

function hash(string, length)
{
  var length = length ? length : 32;
  var start = 0;
  var i = 0;
  var result = '';
  filllen = length - string.length % length;
  for(i = 0; i < filllen; i++)
  {
    string += "0";
  }
  while(start < string.length)
  {
    result = stringxor(result, string.substr(start, length));
    start += length;
  }
  return result;
}

function stringxor(s1, s2)
{
  var s = '';
  var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var max = Math.max(s1.length, s2.length);
  for(var i=0; i<max; i++)
  {
    var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
    s += hash.charAt(k % 52);
  }
  return s;
}

var evalscripts = new Array();
function evalscript(s)
{
  if(s.indexOf('<script') == -1) return s;
  var p = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/ig;
  var arr = new Array();
  while(arr = p.exec(s)) appendscript(arr[1], '', arr[2], arr[3]);
  return s;
}

function $$(id)
{
    return document.getElementById(id);
}

function appendscript(src, text, reload, charset)
{
  var id = hash(src + text);
  if(!reload && in_array(id, evalscripts)) return;
  if(reload && $$(id))
  {
    $$(id).parentNode.removeChild($$(id));
  }
  evalscripts.push(id);
  var scriptNode = document.createElement("script");
  scriptNode.type = "text/javascript";
  scriptNode.id = id;
  //scriptNode.charset = charset;
  try
  {
    if(src)
    {
      scriptNode.src = src;
    }
    else if(text)
    {
      scriptNode.text = text;
    }
    $$('append_parent').appendChild(scriptNode);
  }
  catch(e)
  {}
}

function in_array(needle, haystack)
{
  if(typeof needle == 'string' || typeof needle == 'number')
  {
    for(var i in haystack)
    {
      if(haystack[i] == needle)
      {
        return true;
      }
    }
  }
  return false;
}

var pmwinposition = new Array();

var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
function pmwin(action, param)
{
  var objs = document.getElementsByTagName("OBJECT");
  if(action == 'open')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].style.visibility != 'hidden')
      {
        objs[i].setAttribute("oldvisibility", objs[i].style.visibility);
        objs[i].style.visibility = 'hidden';
      }
    }
    var clientWidth = document.body.clientWidth;
    var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
    var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
    var pmwidth = 800;
    var pmheight = clientHeight * 0.9;
    if(!$$('pmlayer'))
    {
      div = document.createElement('div');div.id = 'pmlayer';
      div.style.width = pmwidth + 'px';
      div.style.height = pmheight + 'px';
      div.style.left = ((clientWidth - pmwidth) / 2) + 'px';
      div.style.position = 'absolute';
      div.style.zIndex = '999';
      $$('append_parent').appendChild(div);
      $$('pmlayer').innerHTML = '<div style="width: 800px; background: #666666; margin: 5px auto; text-align: left">' +
        '<div style="width: 800px; height: ' + pmheight + 'px; padding: 1px; background: #FFFFFF; border: 1px solid #7597B8; position: relative; left: -6px; top: -3px">' +
        '<div onmousedown="pmwindrag(event, 1)" onmousemove="pmwindrag(event, 2)" onmouseup="pmwindrag(event, 3)" style="cursor: move; position: relative; left: 0px; top: 0px; width: 800px; height: 30px; margin-bottom: -30px;"></div>' +
        '<a href="###" onclick="pmwin(\'close\')"><img style="position: absolute; right: 20px; top: 15px" src="images/close.gif" title="关闭" /></a>' +
        '<iframe id="pmframe" name="pmframe" style="width:' + pmwidth + 'px;height:100%" allowTransparency="true" frameborder="0"></iframe></div></div>';
    }
    $$('pmlayer').style.display = '';
    $$('pmlayer').style.top = ((clientHeight - pmheight) / 2 + scrollTop) + 'px';
    if(!param)
    {
        pmframe.location = 'pm.php';
    }
    else
    {
        pmframe.location = 'pm.php?' + param;
    }
  }
  else if(action == 'close')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].attributes['oldvisibility'])
      {
        objs[i].style.visibility = objs[i].attributes['oldvisibility'].nodeValue;
        objs[i].removeAttribute('oldvisibility');
      }
    }
    hiddenobj = new Array();
    $$('pmlayer').style.display = 'none';
  }
}

var pmwindragstart = new Array();
function pmwindrag(e, op)
{
  if(op == 1)
  {
    pmwindragstart = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    pmwindragstart[2] = parseInt($$('pmlayer').style.left);
    pmwindragstart[3] = parseInt($$('pmlayer').style.top);
    doane(e);
  }
  else if(op == 2 && pmwindragstart[0])
  {
    var pmwindragnow = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    $$('pmlayer').style.left = (pmwindragstart[2] + pmwindragnow[0] - pmwindragstart[0]) + 'px';
    $$('pmlayer').style.top = (pmwindragstart[3] + pmwindragnow[1] - pmwindragstart[1]) + 'px';
    doane(e);
  }
  else if(op == 3)
  {
    pmwindragstart = [];
    doane(e);
  }
}

function doane(event)
{
  e = event ? event : window.event;
  if(is_ie)
  {
    e.returnValue = false;
    e.cancelBubble = true;
  }
  else if(e)
  {
    e.stopPropagation();
    e.preventDefault();
  }
}


/* *
 * 处理添加礼包到购物车的反馈信息
 */
function addPackageToCartResponse(result)
{
  if(result.error > 0)
  {
    if (result.error == 2)
    {
      if (confirm(result.message))
      {
        location.href = 'user.php?act=add_booking&id=' + result.goods_id;
      }
    }
    else
    {
      alert(result.message);    
    }
  }
  else
  {
    var cartInfo = document.getElementById('ECS_CARTINFO');
    var cart_url = 'flow.php?step=cart';
    if (cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }

    if (result.one_step_buy == '1')
    {
      location.href = cart_url;
    }
    else
    {
      switch(result.confirm_type)
      {
        case '1' :
          if (confirm(result.message)) location.href = cart_url;
          break;
        case '2' :
          if (!confirm(result.message)) location.href = cart_url;
          break;
        case '3' :
          location.href = cart_url;
          break;
        default :
          break;
      }
    }
  }
}

function setSuitShow(suitId)
{
    var suit    = document.getElementById('suit_'+suitId);

    if(suit == null)
    {
        return;
    }
    if(suit.style.display=='none')
    {
        suit.style.display='';
    }
    else
    {
        suit.style.display='none';
    }
}


/* 以下四个函数为属性选择弹出框的功能函数部分 */
//检测层是否已经存在
function docEle() 
{
  return document.getElementById(arguments[0]) || false;
}

//生成属性选择层
function openSpeDiv(message, goods_id, parent) 
{
  var _id = "speDiv";
  var m = "mask";
  if (docEle(_id)) document.removeChild(docEle(_id));
  if (docEle(m)) document.removeChild(docEle(m));
  //计算上卷元素值
  var scrollPos; 
  if (typeof window.pageYOffset != 'undefined') 
  { 
    scrollPos = window.pageYOffset; 
  } 
  else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') 
  { 
    scrollPos = document.documentElement.scrollTop; 
  } 
  else if (typeof document.body != 'undefined') 
  { 
    scrollPos = document.body.scrollTop; 
  }

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "hidden";
    i++;
  }

  // 新激活图层
  var newDiv = document.createElement("div");
  newDiv.id = _id;
  newDiv.style.position = "absolute";
  newDiv.style.zIndex = "10000";
  newDiv.style.width = "300px";
  newDiv.style.height = "260px";
  newDiv.style.top = (parseInt(scrollPos + 200)) + "px";
  newDiv.style.left = (parseInt(document.body.offsetWidth) - 200) / 2 + "px"; // 屏幕居中
  newDiv.style.overflow = "auto"; 
  newDiv.style.background = "#FFF";
  newDiv.style.border = "3px solid #59B0FF";
  newDiv.style.padding = "5px";

  //生成层内内容
  newDiv.innerHTML = '<h4 style="font-size:14; margin:15 0 0 15;">' + select_spe + "</h4>";

  for (var spec = 0; spec < message.length; spec++)
  {
      newDiv.innerHTML += '<hr style="color: #EBEBED; height:1px;"><h6 style="text-align:left; background:#ffffff; margin-left:15px;">' +  message[spec]['name'] + '</h6>';

      if (message[spec]['attr_type'] == 1)
      {
        for (var val_arr = 0; val_arr < message[spec]['values'].length; val_arr++)
        {
          if (val_arr == 0)
          {
            newDiv.innerHTML += "<input style='margin-left:15px;' type='radio' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' checked /><font color=#555555>" + message[spec]['values'][val_arr]['label'] + '</font> [' + message[spec]['values'][val_arr]['format_price'] + ']</font><br />';      
          }
          else
          {
            newDiv.innerHTML += "<input style='margin-left:15px;' type='radio' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' /><font color=#555555>" + message[spec]['values'][val_arr]['label'] + '</font> [' + message[spec]['values'][val_arr]['format_price'] + ']</font><br />';      
          }
        } 
        newDiv.innerHTML += "<input type='hidden' name='spec_list' value='" + val_arr + "' />";
      }
      else
      {
        for (var val_arr = 0; val_arr < message[spec]['values'].length; val_arr++)
        {
          newDiv.innerHTML += "<input style='margin-left:15px;' type='checkbox' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' /><font color=#555555>" + message[spec]['values'][val_arr]['label'] + ' [' + message[spec]['values'][val_arr]['format_price'] + ']</font><br />';     
        }
        newDiv.innerHTML += "<input type='hidden' name='spec_list' value='" + val_arr + "' />";
      }
  }
  newDiv.innerHTML += "<br /><center>[<a href='javascript:submit_div(" + goods_id + "," + parent + ")' class='f6' >" + btn_buy + "</a>]&nbsp;&nbsp;[<a href='javascript:cancel_div()' class='f6' >" + is_cancel + "</a>]</center>";
  document.body.appendChild(newDiv);


  // mask图层
  var newMask = document.createElement("div");
  newMask.id = m;
  newMask.style.position = "absolute";
  newMask.style.zIndex = "9999";
  newMask.style.width = document.body.scrollWidth + "px";
  newMask.style.height = document.body.scrollHeight + "px";
  newMask.style.top = "0px";
  newMask.style.left = "0px";
  newMask.style.background = "#FFF";
  newMask.style.filter = "alpha(opacity=30)";
  newMask.style.opacity = "0.40";
  document.body.appendChild(newMask);
} 

//获取选择属性后，再次提交到购物车
function submit_div(goods_id, parentId) 
{
  var goods        = new Object();
  var spec_arr     = new Array();
  var fittings_arr = new Array();
  var number       = 1;
  var input_arr      = document.getElementsByTagName('input'); 
  var quick		   = 1;

  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < input_arr.length; i ++ )
  {
    var prefix = input_arr[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((input_arr[i].type == 'radio' || input_arr[i].type == 'checkbox') && input_arr[i].checked)))
    {
      spec_arr[j] = input_arr[i].value;
      j++ ;
    }
  }

  goods.quick    = quick;
  goods.spec     = spec_arr;
  goods.goods_id = goods_id;
  goods.number   = number;
  goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);

  Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');

  document.body.removeChild(docEle('speDiv'));
  document.body.removeChild(docEle('mask'));

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "";
    i++;
  }

}

// 关闭mask和新图层
function cancel_div() 
{
  document.body.removeChild(docEle('speDiv'));
  document.body.removeChild(docEle('mask'));

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "";
    i++;
  }
}


//--------------------------------------------------------------------产品页----选项卡切换------------------------------------------------------------
function detail(n){		
	//--采用一起换的方式进行----
	if(n){
		document.getElementById("detail1").style.display="none";
		document.getElementById("detail4").style.display="none";
		document.getElementById("detail5").style.display="none";	
		document.getElementById("detail"+n).style.display="";	
	}
}

function jiansh(n){
	if(document.formCart("goods_number["+n+"]").value>1)
	document.formCart("goods_number["+n+"]").value=document.formCart("goods_number["+n+"]").value-1;
	}
	
	function jiash(n){
	if(document.formCart("goods_number["+n+"]").value>0)
	document.formCart("goods_number["+n+"]").value=document.formCart("goods_number["+n+"]").value*1+1;
	}
//-----------------------------减号-----------------------------------	
function jianzbsh(){
	alert("-");
	if(document.formCart("sszb["+n+"]").value>1)
	document.formCart("sszb["+n+"]").value=document.formCart("sszb["+n+"]").value-1;
}
//-----------------------------加号-----------------------------------	
function jiazbsh(){
	if(document.formCart("sszb["+n+"]").value>0)
	document.formCart("sszb["+n+"]").value=document.formCart("sszb["+n+"]").value*1+1;
}
	
	
	function jianybsh(n){
	if(document.formCart("ssyb["+n+"]").value>1)
	document.formCart("ssyb["+n+"]").value=document.formCart("ssyb["+n+"]").value-1;
	}
	
	function jiaybsh(n){
	if(document.formCart("sszb["+n+"]").value>0)
	document.formCart("ssyb["+n+"]").value=document.formCart("ssyb["+n+"]").value*1+1;
	}
	
function showqg(n)
{
	document.getElementById("tjcontent").style.display="none";
	document.getElementById("xiancontent").style.display="none";
	if(n==1){document.getElementById("tjcontent").style.display="";}
	if(n==2){document.getElementById("xiancontent").style.display="";}
}
		
function showgood(n){
	document.getElementById("newgood1").style.display="none";
	document.getElementById("newgood2").style.display="none";
	document.getElementById("newgood3").style.display="none";
	document.getElementById("newgood4").style.display="none";
	document.getElementById("newgood5").style.display="none";
	document.getElementById("newgood6").style.display="none";	
	document.getElementById("newgood"+n).style.display="";
}
	

//---------------------------------------------------购买商品局部刷新---------------------------------------------
function showDiv(id, gid)
{
	var user_id = document.getElementById("get_user_id").value;//验证用户是否登录
	if(user_id<=0)
	{
		alert("您还未登陆，请先登陆后购买，一人限抢一件！");
		return;
	}
	
	//判断是否有足够的商品数量进行抢购
	$.ajax({
			type:'post',
			url: 'ajax_step.php?act=if_enough_goods',	
			data:'&goods_id='+gid+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				if(da == 'no')
				{
					alert('您的动作慢了一步，已被其他瞳学抢购一空了，下次加油哦！');
					return;
				}																			
			}
	});
	
	//验证用户是否已经抢购过
	$.ajax({
			type:'post',
			url: 'ajax_step.php?act=user_if_buy',	
			data:'&goods_id='+gid+'&user_id='+user_id+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				if(da == 'yes')
				{
					alert('您今天已经抢购过了，一人只能抢购一件！');
					//return;
				}	
				else
				{
					var ret = '';
					ret = addToCart(gid);//加入购物车
					if(ret == false)
					{
						return;
					}
					else
					{	
						//-------------------弹出div提示-------------------------//
						$("#mydiv").fadeIn(300);
						if($.browser.msie && $.browser.version == '6.0')
						{
							$("#framediv").css("display","block");//解决ie6bug frame_bug
						}
						var obj = document.getElementById(id);		
						obj.style.position = "relative";		
						obj.style.top = "-120px";
						obj.style.left= "-340px";
					}					
				}																				
			}
	});
}


function showClose(id){
	document.getElementById(id).style.display = "none";	
	//ie6bug.
	if($.browser.msie && $.browser.version == '6.0'){
		$("#framediv").css("display","none");
	}
}
//护理液加入购物车
function showDivz(id,gid)
{		
	var user_id = document.getElementById("get_user_id").value;//验证用户是否登录
	if(user_id<=0)
	{
		alert("您还未登陆，请先登陆后购买，一人限抢一件！");
		return;
	}
	
	//判断是否有足够的商品数量进行抢购
	$.ajax({
			type:'post',
			url: 'ajax_step.php?act=if_enough_goods',	
			data:'&goods_id='+gid+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				if(da == 'no')
				{
					alert('您的动作慢了一步，已被其他瞳学抢购一空了，下次加油哦！');
					return;
				}																			
			}
	});
	
	//验证用户是否已经抢购过
	$.ajax({
			type:'post',
			url: 'ajax_step.php?act=user_if_buy',	
			data:'&goods_id='+gid+'&user_id='+user_id+'&m='+Math.random(),		
			cache:false,
			success:
			function(da)
			{
				if(da == 'yes')
				{
					alert('您今天已经抢购过了，一人只能抢购一件！');
					//return;
				}	
				else
				{
					//---------------------------------真正加入购物车---------------------------------
					addToCartz(gid);
					//---------------------------------加入购物车之前验证：是否有货(此处未完善)-----------
					var obj = document.getElementById(id);	
					obj.style.display = "block";	
					obj.style.position = "relative";
					obj.style.top = "-112px";
					obj.style.left= "-61px";				
				}																				
			}
	});

}
//-------------------------------------------------------------------------------------------------------

//logo_tr切换
function pan_hide(id)
{
	document.getElementById(id).style.display='none';
}
function pan_show(id)
{
	document.getElementById(id).style.display='block';
}
/*====================================================会员中心===========================================================*/


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:第二件商品加入购物车
 * ----------------------------------------------------------------------------------------------------------------------
 */
function yi_buy2_add_cart(goods_id, parent_id)
{
	var fm = document.getElementById("buy2");	
	var zselect = fm.elements['zselect'].value;
	if(zselect == '')
	{
		alert("请选择您的眼镜度数！");
		return;
	}

	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var quick		 = 0;
	var bz           = 0;
	
	var zcount       = 1;
	var yselect      = "";
	var ycount       = 0;	
 
	//没有选择数量的可以过,但是到购物车这取消这一侧条记录
	if( zselect.length>0 && zcount==0){ zselect = "";}
	if( yselect.length>0 && ycount==0){ yselect = "";}
	if( yselect.length==0 && ycount==1){ ycount = 0;}
	if( zselect.length==0 && zcount==1){ zcount = 0;}		
	if((zselect.length>0&&zcount>0&&yselect.length==0&&ycount==0)||(yselect.length>0&&ycount>0&&zselect.length==0&&zcount==0)||(zselect.length>0&&zcount>0&&yselect.length>0&&ycount>0)){
		 bz=1;
		 number =zcount*1+ycount*1;
	}  

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goods_id;
	goods.number   = number;	
	goods.zselect  = zselect;
	goods.zcount   = zcount;
	goods.yselect  = yselect;
	goods.ycount   = ycount; 
	goods.parent   = 0;

	Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), yi_respond_buy2, 'POST', 'JSON');
	return true;	
}

//没有度数商品
function yi_buy2_add_cart_2(goodsId, parentId)
{
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var formBuy      = document.forms['buy2'];
	var quick		 = 0;
	var bz           = 0;//步骤
	
	// 检查是否有商品规格 
	if(formBuy)
	{
		spec_arr = getSelectedAttributes(formBuy);
		if(formBuy.elements['number'])
		{
		  number = formBuy.elements['number'].value;
		}
		quick = 1;
	} 
	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;  
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
	Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), yi_respond_buy2, 'POST', 'JSON');
}

//回调函数:yi_buy2_add_cart()
function yi_respond_buy2(result)
{
	if(result.error > 0)
	{
	    if(result.error == 2)
	    {	    	
	    	alert('对不起，该商品已售完！');//商品缺货
	    }
	    else
	    {
	    	alert(result.message); 
	    }		
	}
	else
	{
		if(confirm("您已成功加入购物车,是否去购物车结算。"))
		{
			location.href = "flow.html";
		}
	}	
}
