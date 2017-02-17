/*------------------------------------------问答---questionlist.dwt--------------------------------------------------------------*/

/*-------------用户提问--验证问答提交内容不能为空-------------*/
function qq(){	
	//用户提问
	var form   = document.forms['formMsg'];
	var email  = form.elements['msg_title'].value;	
	var context= form.elements['msg_content'].value;
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
			//邮箱验证
			if(!(Utils.isEmail(email))){
				alert("邮箱地址错误！");
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
/*--------------是否登录验证----------------------------*/
function iflogin(){
	//取得用户名
	var userinfo = document.getElementById("user_info").value;
	if( userinfo == '' ){
		alert("请先登录，才可提问噢 ^_^");
		return false;
	}else{
		return true;
	}	
}
/*-------------------------------------------------------*/