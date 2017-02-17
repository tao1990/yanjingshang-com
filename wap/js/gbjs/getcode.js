var tel_box=document.getElementById("tel_num"),
	msg_box=document.getElementById("msg"),
	sendBtn=document.getElementById("getcodes"),
	text_btn=sendBtn.innerHTML,
	reg=/^^[1][3578][0-9]{9}$/;
// 清空消息栏	
tel_box.addEventListener('input',function(){
	msg_box.innerHTML="";
},false);
// 获取验证码
function getCode(time){
	var tel_num=tel_box.value;
	if(tel_num.trim()===""){
		msg.innerHTML="手机号码不能为空";
	}else if(!reg.test(tel_num)){
		msg.innerHTML="手机号码格式错误";
	}else{
		msg.innerHTML="验证码已发送到你的手机";
		function countdown(){
			if(time>0){
				sendBtn.disabled=true;
				time--;
				sendBtn.innerHTML=time+"秒后可再次发送";
				setTimeout(countdown,1000);
			}else{
				sendBtn.disabled=false;
				sendBtn.innerHTML=text_btn;
			}
		}
		countdown();
		/*$.ajax({
			url:url,
			type:"",
			dataType:"",
			success:function(d){
				
			}

		})*/
	}
}