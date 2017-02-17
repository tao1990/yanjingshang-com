/*-------------买家秀页面所有的验证----------------------------*/
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
/*-------------买家秀验证----------------------------*/
//验证用户是否登录 未登录提示用户登录 
function login(){	
	var username = document.getElementById("h_username").value;
	if( username == ''){
		alert("请先登录，才可以上传噢^_^");
	}
}


//submit_click--事件 ---检查上传数据不为空 ---
function check_nulls(){
	var username = document.getElementById("h_username").value;
	if( username != ''){
		
		//文件路径不能为空！
		var filename = document.getElementById("upfile");
		if( filename.value == ''){
			alert("文件路径不能为空！");
			return(false);
		}
		
		//alert("check");
	
	}
}



//用户没有登录 阻止提交表单
function unsubmit(){	
	
	var username = document.getElementById("h_username");
		
	if(username.value == ''){
		return false;
	}
}










/*-------------买家秀--投票顶一下--验证----------------------------*/
/*-ajax--客户端--没实现-*/
var xmlHttp;
function showUser(str1,str2,str3,str4)
{
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		 alert ("Browser does not support HTTP Request");
		 return;
	 }
	 
	 
	var url="yi_ajax.php";    //yi_ajax--处理投票结果的php页面---4个参数--未果--
	url=url+"?vote="+str1+"&gid="+str2+"&uid="+str3+"&mjxid="+str4;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);	
	xmlHttp.send(null);
}

//回调函数
function stateChanged()
{
	if (xmlHttp.readyState==4) {
		 if(xmlHttp.status == 200 || xmlHttp.readyState=="complete"){
		
			//var a = document.getElementById("vote");
			var result=xmlHttp.responseText;
			if(result=="no"){		
				alert("您今天已经投过票了，一天只能投一票哦！");				
			}else{				
				//var coo = document.cookie;				
				//alert(coo);
				//实现重新刷新买家秀页面
				if(result == "yes"){
					//var coo = document.cookie;				
					//alert(coo);
					window.location.href="buyersshow.php";
				}
			}
			
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
		 //Internet Explorer
		 try{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		  } catch (e){
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
	 }
	return xmlHttp;
}

