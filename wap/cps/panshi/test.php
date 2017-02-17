<?php
//define('IN_ECS', true);
//header("Content-type:text/xml; charset=utf-8");

//setcookie('cpsinfo_ty_appid', $ty_appid, time()+2592000, '/', '');
//setcookie('cpsinfo_ty_uid', $ty_uid, time()+2592000, '/', '');	
//setcookie('cpsinfo_ty_trackingcode', $ty_trackingcode, time()+2592000, '/', '');

//echo $_COOKIE['xl_uid'];
//exit;

  
require_once(dirname(__FILE__) . '/post_order.class_test.php');

date_default_timezone_set('PRC');

$ps = new post_order();
$p = $ps->get_order_info(426129,$_COOKIE['cpsinfo_panshi_info']);//获取订单信息
print_r($p);die;
$urn = "http://open.adyun.com/order/push";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $urn);    // 设置你准备提交的URL 
curl_setopt($curl, CURLOPT_POST, true);  // 设置POST方式提交
curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
$data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容
curl_close($curl);        // 关闭curl请求

print_r($data);die;

