
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