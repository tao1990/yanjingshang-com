
//=====================================================产品页面脚本【yi_goods.js】=====================================================||
//要安装jquery包。
$(document).ready(function(){
	
	//商品数量的加减
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
	
	//弹出度数换算表，数量说明窗口
	$("#conversiontable").click(function(){
		if($.browser.msie){
			window.showModelessDialog("open.htm","","dialogWidth:670px;dialogHeight:600px;status:no;scroll:yes;dialogLeft:130px;dialogTop:160px;color:red;");
		}else{
			window.open("open.htm","","status:no;modal=yes;scroll:yes;width:670px;height:600px;left:130px;top:160px;color:red;");
		}
	});
	$("#ordernum").click(function(){
		if($.browser.msie){
			window.showModelessDialog("number.htm","","dialogWidth:540px;dialogHeight:340px;status:no;scroll:yes;dialogLeft:258px;dialogTop:280px;color:red;");
		}else{
			window.open("number.htm","","status:no;modal=yes;scroll:yes;width:540px;height:340px;left:258px;top:280px;color:red;");
		}
	});	
});

/*--------------------------------------------产品页面——用户提问----------------------------------------------------*/
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