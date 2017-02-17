<?php
	require_once ("config.inc");  //参数文件
	$tranCode = "cb2200_verify";

	$notifyMsg = $_REQUEST["notifyMsg"];  	
	if(is_utf8($string))
	{
		$string = mb_convert_encoding($string, "gbk");	
	}	
	

	$lastIndex = strripos($notifyMsg,"|");
	$signMsg   = substr($notifyMsg,$lastIndex+1); //签名信息
	$srcMsg    = substr($notifyMsg,0,$lastIndex+1);//原文

	//连接地址
	$socketUrl = "tcp://".$socket_ip.":".$socket_port;
	$fp = stream_socket_client($socketUrl, $errno, $errstr, 30);
	$retMsg="";

	if (!$fp) {
		echo "$errstr ($errno)<br />\n";
	}
	else 
	{
		$in  = "<?xml version='1.0' encoding='UTF-8'?>";
		$in .= "<Message>";
		$in .= "<TranCode>".$tranCode."</TranCode>";
		$in .= "<MsgContent>".$notifyMsg."</MsgContent>";
		$in .= "</Message>";
		fwrite($fp, $in);
		while (!feof($fp)) {
			$retMsg =$retMsg.fgets($fp, 1024);			
		}
		fclose($fp);
	}	
	
	//解析返回xml
	$dom = new DOMDocument;
	$dom->loadXML($retMsg);

	$retCode = $dom->getElementsByTagName('retCode');
	$retCode_value = $retCode->item(0)->nodeValue;
	
	$errMsg = $dom->getElementsByTagName('errMsg');
	$errMsg_value = $errMsg->item(0)->nodeValue;


	if($retCode_value != '0')
	{
	   echo "交易返回码：".$retCode_value."<br>";
	   echo "交易错误信息：" .$errMsg_value."<br>";
	}
	else
	{
		$arr = preg_split("/\|{1,}/",$srcMsg);

		$pay_res = intval($arr[9]);//这笔交易的支付结果

		$log_id = intval(base64_decode($arr[16]));//商户备注
		$log_id = urlencode($log_id);

		if($pay_res == 1)
		{
			//ok 支付成功， 返回验证页面。并且传递参数过去。
			header("Location: http://www.easeeyes.com/respond.php?code=bocomm&pay_log_id=".$log_id." \n");			
		}
		else
		{
			echo "支付失败，请联系客服！";
		}
	}

function is_utf8($string){       
	return preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $string);  
}
?> 