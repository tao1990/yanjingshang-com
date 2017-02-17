<?php
/* ============================================================================
 * 交通银行支付网关【2012/12/27】【author:yijiangwen】
 * ============================================================================
 */
require_once("config.inc");

	//获得表单传过来的数据
	$interfaceVersion = "1.0.0.0";//版本号//
	$merID          = $merchID;   //商户号//	
	$orderid		= $_REQUEST["orderid"];   //
	$orderDate		= $_REQUEST["orderDate"]; //
	$orderTime		= $_REQUEST["orderTime"]; //

	$tranType		= 0; //交易类别：0=>B2C//

	$amount	        = $_REQUEST["amount"];//订单金额（元为单位）//
	//$amount       = 0.01;
	$curType        = "CNY";              //交易币种//

	$orderContent	= $_REQUEST["orderContent"];//订单内容//这个会显示出来的。
	$orderMono		= $_REQUEST["orderMono"];   //商家备注

	$phdFlag		= $_REQUEST["phdFlag"];//物流配送标志
	$notifyType		= $_REQUEST["notifyType"];//通知方式 0 不通知 1 通知 2 抓取页面//

	$merURL			= $_REQUEST["merURL"]; //主动通知URL
	$goodsURL		= $_REQUEST["goodsURL"];//取货URL


	$jumpSeconds	= $_REQUEST["jumpSeconds"];//自动跳转时间
	$payBatchNo		= $_REQUEST["payBatchNo"];//商户批次号 商家对账使用
	$proxyMerName	= $_REQUEST["proxyMerName"];//代理商家名称
	$proxyMerType	= $_REQUEST["proxyMerType"];//代理商家类型
	$proxyMerCredentials = $_REQUEST["proxyMerCredentials"];//代理商家证件号码
	//$netType		= $_REQUEST["netType"]; //渠道编号 0:html渠道  //
	$netType        = 0;
	$issBankNo		= $_REQUEST["issBankNo"];//发卡行行号 不输默认为交行

	$tranCode		= "cb2200_sign";//交易签名
	$source			= "";
	
	//yi:字符串编码
	//htmlentities($orderMono, "ENT_QUOTES", "GB2312");
	//$orderContent = iconv('UTF-8', 'GB2312', $orderContent);

	//连接字符串
	$source = $interfaceVersion."|".$merID."|".$orderid."|".$orderDate."|".$orderTime."|".$tranType."|"
	.$amount."|".$curType."|".$orderContent."|".$orderMono."|".$phdFlag."|".$notifyType."|".$merURL."|"
	.$goodsURL."|".$jumpSeconds."|".$payBatchNo."|".$proxyMerName."|".$proxyMerType."|".$proxyMerCredentials."|".$netType;

	//连接地址
	$socketUrl = "tcp://".$socket_ip.":".$socket_port;
	$fp        = stream_socket_client($socketUrl, $errno, $errstr, 30);
	$retMsg   = "";

	//socket通信
	if(!$fp)
	{
		echo "$errstr ($errno)<br />\n";
	}
	else 
	{
		$in  = "<?xml version='1.0' encoding='UTF-8'?>";
		$in .= "<Message>";
		$in .= "<TranCode>".$tranCode."</TranCode>";
		$in .= "<MsgContent>".$source."</MsgContent>";
		$in .= "</Message>";
		fwrite($fp, $in);
		while(!feof($fp)){
			$retMsg =$retMsg.fgets($fp, 1024);			
		}
		fclose($fp);
	}
	//echo "retMsg=".$retMsg."***************";

	//解析返回xml
	if(get_class_methods(DOMDocument))
	{
		$dom = new DOMDocument;
		$dom->loadXML($retMsg);

		$retCode = $dom->getElementsByTagName('retCode');
		$retCode_value = $retCode->item(0)->nodeValue;
		
		$errMsg = $dom->getElementsByTagName('errMsg');
		$errMsg_value = $errMsg->item(0)->nodeValue;

		$signMsg = $dom->getElementsByTagName('signMsg');
		$signMsg_value = $signMsg->item(0)->nodeValue;

		$orderUrl = $dom->getElementsByTagName('orderUrl');
		$orderUrl_value = $orderUrl->item(0)->nodeValue;
	}
	else
	{
		echo 'no dom class exists!';
	}
	//echo "retMsg=".$retMsg;
	//echo $retCode_value." ".$errMsg_value." ".$signMsg_value." ".$orderUrl_value;

	if($retCode_value != "0")
	{
		echo "交易返回码：".$retCode_value."<br>";
		echo "交易错误信息：" .$errMsg_value."<br>";
	}
	else
	{

		//交易信息正确，提交到交行
?> 
<html>
    <head>
        <title>商户订单提交</title>
        <meta http-equiv = "Content-Type" content = "text/html;charset=GBK">
    </head>
	<body bgcolor = "#FFFFFF" text = "#000000" onload="form1.submit()">
        <form name = "form1" method = "post" action = "<?php echo($orderUrl_value); ?>">
            <input type = "hidden" name = "interfaceVersion" value = "<?php echo($interfaceVersion); ?>">
            <input type = "hidden" name = "merID" value = "<?php echo($merchID); ?>">
            <input type = "hidden" name = "orderid" value = "<?php echo($orderid); ?>">
            <input type = "hidden" name = "orderDate" value = "<?php echo($orderDate); ?>">
            <input type = "hidden" name = "orderTime" value = "<?php echo($orderTime); ?>">
            <input type = "hidden" name = "tranType" value = "<?php echo($tranType); ?>">
            <input type = "hidden" name = "amount" value = "<?php echo($amount); ?>">
            <input type = "hidden" name = "curType" value = "<?php echo($curType); ?>">
            <input type = "hidden" name = "orderContent" value = "<?php echo($orderContent); ?>">
            <input type = "hidden" name = "orderMono" value = "<?php echo($orderMono); ?>">
            <input type = "hidden" name = "phdFlag" value = "<?php echo($phdFlag); ?>">
            <input type = "hidden" name = "notifyType" value = "<?php echo($notifyType); ?>">
            <input type = "hidden" name = "merURL" value = "<?php echo($merURL); ?>">
            <input type = "hidden" name = "goodsURL" value = "<?php echo($goodsURL); ?>">
            <input type = "hidden" name = "jumpSeconds" value = "<?php echo($jumpSeconds); ?>">
            <input type = "hidden" name = "payBatchNo" value = "<?php echo($payBatchNo); ?>">
            <input type = "hidden" name = "proxyMerName" value = "<?php echo($proxyMerName); ?>">
            <input type = "hidden" name = "proxyMerType" value = "<?php echo($proxyMerType); ?>">
            <input type = "hidden" name = "proxyMerCredentials" value = "<?php echo($proxyMerCredentials); ?>">
            <input type = "hidden" name = "netType" value = "<?php echo($netType); ?>">
            <input type = "hidden" name = "merSignMsg" value = "<?php echo($signMsg_value); ?>">
            <input type = "hidden" name = "issBankNo" value = "<?php echo($issBankNo); ?>">
        </form>
    </body> 
</html>
<?php
	}
?>