<?php

header("Content-type:text/html; charset=utf-8");

require_once "lib/WxPay.Api.php";
require_once "unit/WxPay.NativePay.php";
require_once 'unit/log.php';
//模式一
$notify = new NativePay();
//$url1 = $notify->GetPrePayUrl("123456789");

//模式二
$input = new WxPayUnifiedOrder();



$input->SetBody("测试商品1");
$input->SetAttach("test2");
$input->SetOut_trade_no(2015031931590);
$input->SetTotal_fee("1");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 86400));
$input->SetGoods_tag("test3");
//$input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
$input->SetNotify_url("http://www.easeeyes.com/wxpay/native_notify.php");
$input->SetTrade_type("NATIVE");
$input->SetProduct_id("889");
$result = $notify->GetPayUrl($input);
$url2 = $result["code_url"];

var_dump($result);die;

$PNG_TEMP_DIR = dirname(__FILE__).'/wxpic/';

$PNG_WEB_DIR = 'wxpic/';
//var_dump(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR);die;
include "phpqrcode/qrlib.php";

$data = $url2; // data
$ecc = 'M';	// L-smallest, M, Q, H-best
$size = 4; // 1-50

$filename = $PNG_TEMP_DIR.'qrcode_'.time().'.png';
QRcode::png($data, $filename, $ecc, $size, 2);
chmod($filename, 0777);
echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" />';

?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>微信支付样例-退款</title>
</head>
<body>
	<div style="margin-left: 10px;color:#556B2F;font-size:30px;font-weight: bolder;">扫描支付模式一</div><br/>
	<img alt="模式一扫码支付" src="http://paysdk.weixin.qq.com/qrcode.php?data=<?php echo urlencode($url2);?>" style="width:150px;height:150px;"/>
	<br/><br/><br/>
	<div style="margin-left: 10px;color:#556B2F;font-size:30px;font-weight: bolder;">扫描支付模式二</div><br/>
	<img alt="模式二扫码支付" src="http://paysdk.weixin.qq.com/qrcode.php?data=<?php echo urlencode($url2);?>" style="width:150px;height:150px;"/>
	
</body>
</html>