<?php
	//主动通知页面
	define('IN_ECS', true);
	require('../includes/init.php');
	require(ROOT_PATH . 'includes/lib_payment.php');

	require_once ("config.inc");  //参数文件
	$tranCode  = "cb2200_verify";

	$notifyMsg = $_REQUEST["notifyMsg"];  	

	$lastIndex = strripos($notifyMsg,"|");
	$signMsg   = substr($notifyMsg,$lastIndex+1);  //签名信息
	$srcMsg    = substr($notifyMsg,0,$lastIndex+1);//原文

	$socketUrl = "tcp://".$socket_ip.":".$socket_port;
	$fp = stream_socket_client($socketUrl, $errno, $errstr, 30);
	$retMsg="";

	if (!$fp) {
		echo "$errstr ($errno)<br/> \n";
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
	   //echo "交易返回码：".$retCode_value."<br>";
	   //echo "交易错误信息：" .$errMsg_value."<br>";
	}
	else
	{
		$arr     = preg_split("/\|{1,}/",$srcMsg);
		$pay_res = intval($arr[9]);//交易的支付结果 1:支付成功
		$log_id  = intval(base64_decode($arr[16]));//商户备注
	
		if($pay_res == 1)
		{
			if(!empty($log_id))
			{
				order_paid($log_id);
			}
		}
	}
?> 