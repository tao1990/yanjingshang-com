<?php
// PHP version of merchant.jsp
//����B2CAPIͨ�ð��php�ͻ��˵��ò���
//��    �ߣ�bocomm
//����ʱ�䣺2012-4-10
?>

<?php
	require_once("config.inc");
	//��ñ�������������
	$interfaceVersion = $_REQUEST["interfaceVersion"];		
	$merID = $merchID; //�̻���Ϊ�̶�	
	$orderid = $_REQUEST["orderid"];
	$orderDate = $_REQUEST["orderDate"];
	$orderTime = $_REQUEST["orderTime"];
	$tranType = $_REQUEST["tranType"];
	$amount = $_REQUEST["amount"];
	$curType = $_REQUEST["curType"];
	$orderContent = $_REQUEST["orderContent"];
	$orderMono = $_REQUEST["orderMono"];
	$phdFlag = $_REQUEST["phdFlag"];
	$notifyType = $_REQUEST["notifyType"];
	$merURL = $_REQUEST["merURL"];
	$goodsURL = $_REQUEST["goodsURL"];
	$jumpSeconds = $_REQUEST["jumpSeconds"];
	$payBatchNo = $_REQUEST["payBatchNo"];
	$proxyMerName = $_REQUEST["proxyMerName"];
	$proxyMerType = $_REQUEST["proxyMerType"];
	$proxyMerCredentials = $_REQUEST["proxyMerCredentials"];
	$netType = $_REQUEST["netType"];
	$tranCode = "cb2200_sign";

	$source = "";
	
	//htmlentities($orderMono,"ENT_QUOTES","GB2312");
	//�����ַ���
	$source = $interfaceVersion."|".$merID."|".$orderid."|".$orderDate."|".$orderTime."|".$tranType."|"
	.$amount."|".$curType."|".$orderContent."|".$orderMono."|".$phdFlag."|".$notifyType."|".$merURL."|"
	.$goodsURL."|".$jumpSeconds."|".$payBatchNo."|".$proxyMerName."|".$proxyMerType."|".$proxyMerCredentials."|".$netType;


	//���ӵ�ַ
	$socketUrl = "tcp://".$socket_ip.":".$socket_port;
	$fp = stream_socket_client($socketUrl, $errno, $errstr, 30);
	$retMsg="";
	//
	if (!$fp) {
		echo "$errstr ($errno)<br />\n";
	} else 
	{
		$in  = "<?xml version='1.0' encoding='UTF-8'?>";
		$in .= "<Message>";
		$in .= "<TranCode>".$tranCode."</TranCode>";
		$in .= "<MsgContent>".$source."</MsgContent>";
		$in .= "</Message>";
		fwrite($fp, $in);
		while (!feof($fp)) {
			$retMsg =$retMsg.fgets($fp, 1024);
			
		}
		fclose($fp);
	}	
	echo "retMsg=".$retMsg."***************";
	//��������xml
	$dom = new DOMDocument;
	$dom->loadXML($retMsg);
	
	echo "dom=".$dom."1111111111111111111";

	$retCode = $dom->getElementsByTagName('retCode');
	$retCode_value = $retCode->item(0)->nodeValue;
	
	echo "retCode=".$retCode."22222222222222222222";
	echo "retCode_value=".$retCode_value."22222222222222222222";
	
	$errMsg = $dom->getElementsByTagName('errMsg');
	$errMsg_value = $errMsg->item(0)->nodeValue;

	echo "errMsg=".$errMsg."33333333333333333333";
	echo "errMsg_value=".$errMsg_value."33333333333333333333333";
	
	$signMsg = $dom->getElementsByTagName('signMsg');
	$signMsg_value = $signMsg->item(0)->nodeValue;

	echo "signMsg=".$signMsg."44444444444444444444";
	echo "signMsg_value=".$signMsg_value."444444444444444444444444";
	
	$orderUrl = $dom->getElementsByTagName('orderUrl');
	$orderUrl_value = $orderUrl->item(0)->nodeValue;
	
	echo "orderUrl=".$orderUrl."5555555555555555555555";
	echo "orderUrl_value=".$orderUrl_value."5555555555555555555555";
	
	echo "retMsg=".$retMsg;
	echo $retCode_value." ".$errMsg_value." ".$signMsg_value." ".$orderUrl_value;

	if($retCode_value != "0")
       {
            echo "���׷����룺".$retCode_value."<br>";
            echo "���״�����Ϣ��" .$errMsg_value."<br>";
       }
       else
       {

?> 

<html>
    <head>
        <title>�̻���������</title>
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
        </form>
    </body>
 
</html>
<?php
	}
?>