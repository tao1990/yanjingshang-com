<?php
/**
 * 活动的流量监测统计
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_crypto.php');
date_default_timezone_set('PRC');


ini_set("display_errors", "Off");
error_reporting(0);
$ad_url = isset($_GET['ad_url']) ? urldecode($_GET['ad_url']) : '';					//监测网址
$ad_source = isset($_GET['ad_source']) ? urldecode($_GET['ad_source']) : '';		//流量来源
$ad_medium = isset($_GET['ad_medium']) ? urldecode($_GET['ad_medium']) : '';		//来源类型(banner等)
$ad_keyword = isset($_GET['ad_keyword']) ? urldecode($_GET['ad_keyword']) : '';		//关键词
$ad_conent = isset($_GET['ad_conent']) ? urldecode($_GET['ad_conent']) : '';		//监测网址(广告)内容
$ad_name = isset($_GET['ad_name']) ? urldecode($_GET['ad_name']) : '';				//监测网址(广告)名称
$ad_director = isset($_GET['ad_director']) ? urldecode($_GET['ad_director']) : '';	//本活动(监测网页)负责人

/*foreach ($_GET as $k => $v) {
	echo $k . '=' .  $v.'<br/>';
}*/

$url = isset($_GET['url']) ? urldecode($_GET['url']) : '';							//email的监测网址(去掉'ad')
$source = isset($_GET['source']) ? urldecode($_GET['source']) : '';
if ( ! empty($url)) $ad_url = $url;
if ( ! empty($source)) $ad_source = $source;

if (empty($ad_url)) $ad_url = 'http://m.easeeyes.com';

$ad_url = decrypt($ad_url);

$click_time = time();

if (isset($_COOKIE['click_session_id']))
{
	$sql = "INSERT INTO ".$GLOBALS['ecs']->table('active_stat_new'). "
			(cookieid, access_time, ip, ad_url, ad_source, ad_medium, ad_keyword, ad_content, ad_name, ad_director) VALUES 
			('".$_COOKIE['click_session_id']."', ".$click_time.", ".get_ip().", '$ad_url', '$ad_source', '$ad_medium', '$ad_keyword', '$ad_conent', '$ad_name', '$ad_director')";
	
}
 
else 
{
	setcookie('click_session_id', SESS_ID, time()+3600*24*30, '/', '');
	
	$sql = "INSERT INTO ".$GLOBALS['ecs']->table('active_stat_new'). "
			(cookieid, access_time, ip, ad_url, ad_source, ad_medium, ad_keyword, ad_content, ad_name, ad_director) VALUES 
			('".SESS_ID."', ".$click_time.", ".get_ip()." , '$ad_url', '$ad_source', '$ad_medium', '$ad_keyword', '$ad_conent', '$ad_name', '$ad_director')";
}

if(json_encode($ad_url)!='null'){
    $GLOBALS['db']->query($sql);
    setcookie('click_time', $click_time, time()+3600*24*30, '/', '');
    
}

    Header("Location:$ad_url");


//获取访问者ip地址，并转化成int型
function get_ip() {  
	if(!empty($_SERVER["HTTP_CLIENT_IP"]))
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	else if(!empty($_SERVER["REMOTE_ADDR"]))
		$cip = $_SERVER["REMOTE_ADDR"];
	else
		$cip = "";
	return bindec(decbin(ip2long($cip)));
}
?>
