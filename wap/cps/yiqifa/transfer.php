<?php
header("Content-type: text/html; charset=utf-8");

define('IN_ECS', true);
//require(dirname(__FILE__) . '/../../includes/init.php');

date_default_timezone_set('Asia/Shanghai');

//联合登录接口
//http%3A%2F%2Flocalhost	http%3A%2F%2Fwww.easeeyes.com
//http://localhost/cps/yiqifa/transfer.php?src=yiqifa&channel=cps&cid=101&wi=NDgwMDB8dGVzdA==&url=http%3A%2F%2Flocalhost
/*foreach ($_GET as $k => $v) {
	echo $k . '=' .  $v.'<br/>';
}
foreach ($_REQUEST as $k => $v) {
	echo $k . '=' .  $v.'<br/>';
}*/
$src = isset($_GET['src']) ? $_GET['src'] : 'yiqifa';			//来源标识
$channel = isset($_GET['channel']) ? $_GET['channel'] : '';		//渠道标识,我们的为cps
$cid = isset($_GET['cid']) ? $_GET['cid'] : '';					//广告主在亿起发推广的标识
$wi = isset($_GET['wi']) ? $_GET['wi'] : '';					//亿起发下级网站信息,base64编码
$url = isset($_GET['url']) ? $_GET['url'] : '';					//目标url

//非本站则跳出404
if($url!=''){
    $expUrl = explode('.',$url);
    if(!strstr($expUrl[0],'easeeyes') && !strstr($expUrl[1],'easeeyes')){
        header("HTTP/1.1 404 Not Found");exit;  
    }
}

if (empty($src)) $src = 'yiqifa';
if (empty($channel)) $channel = 'cps';
if (empty($url)) $url = 'http://www.easeeyes.com';

//清除其他cps合作cookie
if (isset($_COOKIE['LTINFO'])) setcookie('LTINFO', '', time()-3600, '/');
if (isset($_COOKIE['fanli_uid'])) setcookie('fanli_uid', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_360'])) setcookie('cpsinfo_360', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_duomai'])) setcookie('cpsinfo_duomai', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_fanhuan_channel_id'])) setcookie('cpsinfo_fanhuan_channel_id', '', time()-3600, '/');
//if (isset($_COOKIE['cpsinfo_yiqifa_src'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');

//记录cookie
if ($src && $channel && $cid && $wi) {
	setcookie('cpsinfo_yiqifa_src', $src, time()+3600*24*30, '/', '');
	setcookie('cpsinfo_yiqifa_channel', $channel, time()+3600*24*30, '/', '');	
	setcookie('cpsinfo_yiqifa_cid', $cid, time()+3600*24*30, '/', '');
	setcookie('cpsinfo_yiqifa_wi', $wi, time()+3600*24*30, '/', '');
	//echo $_COOKIE['cpsinfo_yiqifa_src'].' - '.$_COOKIE['cpsinfo_yiqifa_wi'];
	
	Header("Location:$url");
	exit;
	
} else {
	Header("Location:$url");
	exit;
}
?>
