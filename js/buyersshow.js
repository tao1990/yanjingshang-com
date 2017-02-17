//买家秀

//显示、隐藏投票、分享标签
function show_a(action, id) {
	if (action == 'show') {
		$("#temp_x_" + id).slideDown(0);
	} else {
		$("#temp_x_" + id).slideUp(0);
	}
}
function show_b(action, id) {
	if (action == 'show') {
		$("#temp_xs_" + id).slideDown(0);
	} else {
		$("#temp_xs_" + id).slideUp(0);
	}
}

/*-----------------------买家秀 投票 ------------------*/
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
				
				if (document.getElementById('vote_num')) {
					var n = parseInt(document.getElementById('vote_num').innerHTML);
					n++;
					document.getElementById('vote_num').innerHTML = n;
				}
				
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
/*-----------------------买家秀 投票 ------------------*/

/*--------------------------根据cat_id读取栏目下商品列表 start -----------------*/
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
function keycheck(n){
	send_request('ajaxgoods.php?keyword='+n+'&rand='+Math.random());
}
/*--------------------------根据cat_id读取栏目下商品列表 end -----------------*/


//验证表单
function checkForm(theForm) {
    
	if (theForm.upload_type.value == "" || theForm.upload_type.value == "0") { 
	      alert("请选择晒单类型！"); 
		  theForm.upload_type.focus(); 
		  return (false); 
	}
	if (theForm.cat_id.value == "" || theForm.cat_id.value == "0") { 
	      alert("请选择商品分类！"); 
		  theForm.cat_id.focus(); 
		  return (false); 
	}
	if (theForm.goods_id.value == "" || theForm.goods_id.value == "0") { 
	      alert("请选择产品！"); 
		  theForm.goods_id.focus(); 
		  return (false); 
	}
	if (theForm.detail.value == "") { 
	      alert("说两句吧！"); 
		  theForm.detail.focus(); 
		  return (false); 
	}
	if (theForm.select_img.value == "") { 
	      alert("请选择图片上传！"); 
		  return (false); 
	}
	if (theForm.thumb_img.value == "") { 
	      alert("您未保存图片！"); 
		  return (false); 
	}
	
	document.getElementById('upload_buyersshow').submit();
	document.getElementById('reset_from').click();
}