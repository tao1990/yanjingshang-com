<?php
//订单提交接口
ini_set('display_errors', true);
error_reporting(E_ALL);

require_once("Java.inc");

date_default_timezone_set('PRC');
$orderdate = date("YmdHis",time());
echo $orderdate;
//$password = "JKDCHKBA";

//商城代码：1001EC24075824
//企业 1001331619300000458

$password = "12345678";
/*$tranData = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"no\"?><B2CReq><interfaceName>ICBC_PERBANK_B2C</interfaceName><interfaceVersion>1.0.0.11</interfaceVersion><orderInfo><orderDate>".$orderdate."</orderDate><curType>001</curType><merID>1001EC24075824</merID><subOrderInfoList><subOrderInfo><orderid>201403081416290</orderid><amount>1</amount><installmentTimes>1</installmentTimes><merAcct>1001331619300000458</merAcct><goodsID></goodsID><goodsName>www.easeeyes.com</goodsName><goodsNum></goodsNum><carriageAmt>20</carriageAmt></subOrderInfo></subOrderInfoList></orderInfo><custom><verifyJoinFlag>0</verifyJoinFlag><Language>ZH_CN</Language></custom><message><creditType>2</creditType><notifyType>HS</notifyType><resultType>1</resultType><merReference>www.easeeyes.com</merReference><merCustomIp></merCustomIp><goodsType>1</goodsType><merCustomID></merCustomID><merCustomPhone></merCustomPhone><goodsAddress></goodsAddress><merOrderRemark></merOrderRemark><merHint></merHint><remark1>1</remark1><remark2>2</remark2><merURL>http://www.easeeyes.com/icbc/002.php</merURL><merVAR>test</merVAR></message></B2CReq>";*/

//-------------------------------------
$merID 		= '1001EC24075824';			//商城代码
$merAcct	= '1001331619300000458';	//企业帐号
$password	= "12345678";				//证书密匙
//$addTime	= date('YmdHis', $order['add_time']); //交易时间
$addTime	= date('YmdHis', time());
$amount		= 1; //订单金额(单位:分)

$strKey = 'sZrLozDnF260MF9e';			//商户自定义密匙
//$merVAR = '2014041793441'."||".md5($strKey.'2014041793441');		//商家自定义参数,内容是:订单号 + md5(商户密匙+订单号)
$merVAR = md5($strKey.'2014081393441');

$tranData = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"no\"?><B2CReq><interfaceName>ICBC_PERBANK_B2C</interfaceName><interfaceVersion>1.0.0.11</interfaceVersion><orderInfo><orderDate>".$addTime."</orderDate><curType>001</curType><merID>".$merID."</merID><subOrderInfoList><subOrderInfo><orderid>2014081393441</orderid><amount>".$amount."</amount><installmentTimes>1</installmentTimes><merAcct>".$merAcct."</merAcct><goodsID></goodsID><goodsName>www.easeeyes.com</goodsName><goodsNum></goodsNum><carriageAmt></carriageAmt></subOrderInfo></subOrderInfoList></orderInfo><custom><verifyJoinFlag>0</verifyJoinFlag><Language>ZH_CN</Language></custom><message><creditType>2</creditType><notifyType>HS</notifyType><resultType>1</resultType><merReference>*.easeeyes.com</merReference><merCustomIp></merCustomIp><goodsType>1</goodsType><merCustomID></merCustomID><merCustomPhone></merCustomPhone><goodsAddress></goodsAddress><merOrderRemark></merOrderRemark><merHint></merHint><remark1>1</remark1><remark2>2</remark2><merURL>http://www.easeeyes.com/respond_icbc.php</merURL><merVAR>".$merVAR."</merVAR></message></B2CReq>";
//----------------------------------------------

$sign = new java("com.icbc.b2c.Signature");

$tranData_base64 = $sign->tranDataBase64($tranData); //订单数据BASE64编码
$signMsgBase64 = $sign->signMsgBase64($tranData, "/data/www/icbc/Easeeyes.key", $password); //签名信息base64编码
$certBase64 = $sign->certBase64($tranData, "/data/www/icbc/Easeeyes.key", "/data/www/icbc/Easeeyes.crt", $password); //证书信息base编码
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>

<FORM id=FORM1 name=FORM1 action="https://B2C.icbc.com.cn/servlet/ICBCINBSEBusinessServlet" method=post">
	<font face='Arial' size='4' color='white'>商户订单数据签名页面</font>
	<table width="98%"  border="1">
		<tr>
			<td width="9%">接口名称</td>
			<td width="91%"><INPUT ID="interfaceName" NAME="interfaceName" TYPE="text" value="ICBC_PERBANK_B2C" size="120" ></td>
		</tr>
		<tr>
			<td width="9%">接口版本号</td>
			<td width="91%"><INPUT ID="interfaceVersion" NAME="interfaceVersion" TYPE="text" value="1.0.0.11" size="120"></td>
		</tr>
		<tr>
			<td width="9%">接口数据</td>
			<td width="91%"><textarea ID="tranData" name="tranData" cols="120" rows="5"><?php echo $tranData_base64; ?></textarea>
		</tr>
		<tr>
			<td width="9%">签名数据</td>
			<td width="91%"><INPUT ID="merSignMsg" NAME="merSignMsg" TYPE="text" size="120" value="<?php echo $signMsgBase64; ?>">
		</tr>
		<tr>
			<td width="9%">证书数据</td>
			<td width="91%"><INPUT ID="merCert" NAME="merCert" TYPE="text" size="120" value="<?php echo $certBase64; ?>">
		</tr>
		<tr>
			<td width="9%">订单明文数据：</td>
			<td width="91%"><textarea ID="tranData" name="tranData" cols="120" rows="5"><?php echo $tranData; ?></textarea>
		</tr>
	</table>
	<table>
		<tr>
			<td><INPUT TYPE="submit" value=" 提 交 订 单 "></td>
			<td><INPUT  type="button" value=" 返 回 修 改 " onClick="self.history.back();"></td>
		</tr>
	</table>
</FORM>

</body>
</html>