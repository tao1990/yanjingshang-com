<?php
//通知接口
ini_set('display_errors', true);
error_reporting(E_ALL);

require_once("Java.inc");

$notifyData = trim($_REQUEST['notifyData']);	//交易数据base64编码
$signMsg = trim($_REQUEST['signMsg']);			//签名信息base64编码
$merVAR = trim($_REQUEST['merVAR']);			//商户自定义参数

//$bankCertPath = "/data/www/icbc/1.cer";			//银行证书文件路径
$bankCertPath = "/data/www/icbc/ebb2cpublic.crt";

$verifyNotify = new java("com.icbc.b2c.Signature");
$result = $verifyNotify->verifyNotify($notifyData, $signMsg, $bankCertPath);

define('IN_ECS', true);
if ($result == 0) {
	$message = 'verify success!';

	require(dirname(__FILE__) . '/../includes/init.php');
	$sql = "INSERT INTO ecs_icbc (result) VALUES ('".$message."')";
	$GLOBALS['db']->query($sql);

} else {
	$message = 'verify failed';

	require(dirname(__FILE__) . '/../includes/init.php');
	$sql = "INSERT INTO ecs_icbc (result) VALUES ('".$message."')";
	$GLOBALS['db']->query($sql);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>

<font face='Arial' size='4' color='green'>商户接收银行通知信息验签页面</font>
<table width="98%" border="1">
	<tr>
		<td width="9%">验签名结果</td>	
		<td width="91%"><input id="ret" name="ret" type="text" value="<?php echo $message ?>" size="120" /></td>
	</tr>
	<tr>
		<td width="9%">返回商户变量</td>
		<td width="91%"><input id="merVAR" name="merVAR" type="text" value="<?php echo $merVAR ?>" size="120" /></td>
	</tr>
	<tr>
		<td width="9%">通知结果数据</td>
		<td width="91%"><textarea id="notifyData" name="notifyData" cols="120" rows="5"><?php echo base64_decode($notifyData); ?></textarea></td>
	</tr>
</table>

</body>
</html>
