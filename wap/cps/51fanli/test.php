<?php
//define('IN_ECS', true);
header("Content-type:text/xml; charset=utf-8");

//setcookie('cpsinfo_xunlei', '1', time()+3600*24*30, '/', '');
//setcookie('xunlei_cps_login_user', '64660', time()+3600*24*30, '/', '');
//setcookie('xl_uid', '30500663', time()+3600*24*30, '/', '');

//echo $_COOKIE['xl_uid'];
//exit;

require_once(dirname(__FILE__) . '/post_order.class.php');

date_default_timezone_set('PRC');

$t = new post_order();
$xml = $t->get_order_xml(178632); //交易失败
//echo $post_data;exit;


$urn = "http://union.51fanli.com/dingdan/push";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $urn);    // 设置你准备提交的URL 
$xmlStr = ''; // 订单内容，推送订单信息和查询API订单信息格式一样
$post_data = array(
	"content" => $xml
);
curl_setopt($curl, CURLOPT_POST, true);  // 设置POST方式提交
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//判断是否接收返回值，0：不接收，1：接收
$data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容
curl_close($curl);        // 关闭curl请求

