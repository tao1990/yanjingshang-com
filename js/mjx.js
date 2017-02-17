/*----------------------------------------------------------------------------买家秀页面所有的验证-----------------------------------------------------------------------------*/
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
	//alert("aa");	
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
/*-------------类别--图片--标题--描述--验证码-都验证----------------------------*/
function submit_mjx(){
	var form   = document.forms['upform'];
	var mbrand = form.elements['mbrand'].value;
	var mcolor = form.elements['mcolor'].value;
	var upfile = form.elements['upfile'].value;
	var title  = form.elements['title'].value;
	var detail = form.elements['detail'].value;	
	if(mbrand == ''){
		alert("请您选择所属类别！");
		return false;
	}
	if(mcolor == ''){
		alert("请选择颜色！");
		return false;
	}
	if(form.upfile.value == ''){
		alert("请您选择上传图片！");
		form.upfile.focus();
		return false;
	}
	if(title == ''){
		alert("请您填写标题！");
		form.title.focus();
		return false;
	}
	if(detail == ''){
		alert("请您简单描述一下吧！");
		form.detail.focus();
		return false;
	}
	return true;
}
/*------------------------------------------------买家秀--投票顶一下--验证------------------------------------------*/
/*----------------ajax--mjx_vote投票实现----------------------------*/
var http_request1=false;
function send_request_vote(url)
{
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

//send_request_vote回调函数
function voterequest()
{
	if(http_request1.readyState==4)
	{
		if(http_request1.status==200)
		{
			var res = http_request1.responseText;
			var vote= null, id=null ;
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
//--------------使用了ajax----动态加载数据库中品牌---onchange="keycheck(this.value)"--------------
function keycheck(n){
	send_request('ajaxgoods.php?keyword='+n+'&rand='+Math.random());
}
/*--------------------------------------------mjx-明星美瞳的切换---函数---------------------------------------------------------------------*/
/*---图片切换的函数--*/
function pplast(){
				
	if(document.getElementById("pp5").style.display=="none"){								
		document.getElementById("pp5").style.display = "block";		
		//其它的元素不显示
		document.getElementById("pp0").style.display = "none";				
		document.getElementById("pp1").style.display = "none";
		document.getElementById("pp2").style.display = "none";
		document.getElementById("pp3").style.display = "none";				
		document.getElementById("pp4").style.display = "none";			
	}		
}
function pp0(){
				
	if(document.getElementById("pp0").style.display=="none"){								
		document.getElementById("pp0").style.display = "block";		
		document.getElementById("pp5").style.display = "none";				
		document.getElementById("pp1").style.display = "none";
		document.getElementById("pp2").style.display = "none";
		document.getElementById("pp3").style.display = "none";				
		document.getElementById("pp4").style.display = "none";			
	}		
}
	
function pp1(){
				
	if(document.getElementById("pp1").style.display=="none"){								
		document.getElementById("pp1").style.display = "block";				
		document.getElementById("pp0").style.display = "none";				
		document.getElementById("pp5").style.display = "none";
		document.getElementById("pp2").style.display = "none";
		document.getElementById("pp3").style.display = "none";				
		document.getElementById("pp4").style.display = "none";			
	}		
}
function pp2(){
				
	if(document.getElementById("pp2").style.display=="none"){								
		document.getElementById("pp2").style.display = "block";
		document.getElementById("pp0").style.display = "none";				
		document.getElementById("pp1").style.display = "none";
		document.getElementById("pp5").style.display = "none";
		document.getElementById("pp3").style.display = "none";				
		document.getElementById("pp4").style.display = "none";			
	}		
}
function pp3(){
				
	if(document.getElementById("pp3").style.display=="none"){										
		document.getElementById("pp3").style.display = "block";				
		document.getElementById("pp0").style.display = "none";				
		document.getElementById("pp1").style.display = "none";
		document.getElementById("pp2").style.display = "none";
		document.getElementById("pp5").style.display = "none";				
		document.getElementById("pp4").style.display = "none";			
	}		
}				
function pp4(){
				
	if(document.getElementById("pp4").style.display=="none"){										
		document.getElementById("pp4").style.display = "block";				
		document.getElementById("pp0").style.display = "none";				
		document.getElementById("pp1").style.display = "none";
		document.getElementById("pp2").style.display = "none";
		document.getElementById("pp3").style.display = "none";				
		document.getElementById("pp5").style.display = "none";			
	}		
}	
/*----------------------------------------------------------------------------------------------------------------------------------------*/