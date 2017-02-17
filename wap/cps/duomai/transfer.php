<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php
//http://m.easeeyes.com/cps/duomai/transfer.php?union_id=duomai&euid=10026_235_0_dGVzdA%3D%3D_0&mid=10026&to=http%3A%2F%2Fm.easeeyes.com%2F
//http://192.168.1.52:3001/wap/cps/duomai/transfer.php?union_id=duomai&euid=10026_235_0_dGVzdA%3D%3D_0&mid=10026&to=http%3A%2F%2Fm.easeeyes.com%2F
$unionid = isset($_GET['union_id']) ? $_GET['union_id'] : '';	//联盟标识
$siteid    = isset($_GET['euid']) ? $_GET['euid'] : '';	         //来源网站ID
$mid     = isset($_GET['mid']) ? $_GET['mid'] : '';
$to      = isset($_GET['to']) ? $_GET['to'] : '';				//跳转目标地址


if ($siteid == '148716_274_0__1'){
    header('HTTP/1.1 404 Not Found');
	exit;
}
//非本站则跳出404
if($to!=''){
    $expUrl = explode('.',$to);
    if(!strstr($expUrl[0],'easeeyes') && !strstr($expUrl[1],'easeeyes')){
        header("HTTP/1.1 404 Not Found");exit;  
    }
}

//清除其他cps合作cookie
if (isset($_COOKIE['LTINFO'])) setcookie('LTINFO', '', time()-3600, '/');
if (isset($_COOKIE['fanli_uid'])) setcookie('fanli_uid', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_360'])) setcookie('cpsinfo_360', '', time()-3600, '/');
//if (isset($_COOKIE['cpsinfo_duomai'])) setcookie('cpsinfo_duomai', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_fanhuan_channel_id'])) setcookie('cpsinfo_fanhuan_channel_id', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_yiqifa_src'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_51fanli_channel_id'])) setcookie('cpsinfo_51fanli_channel_id', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_51fanli_u_id'])) setcookie('cpsinfo_51fanli_u_id', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_51fanli_tracking_code'])) setcookie('cpsinfo_51fanli_tracking_code', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_51fanli_uname'])) setcookie('cpsinfo_51fanli_uname', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_yiqifa_src_roi'])) setcookie('cpsinfo_yiqifa_src_roi', '', time()-3600, '/');

if ($unionid == 'duomai' && $siteid && $mid && $to) {
	//记入cookie,跳转
	setcookie('cpsinfo_duomai', 'duomai', time()+(3600*12*24), '/', '');
	setcookie('cpsinfo_duomai_siteid', $siteid, time()+(3600*12*24), '/', '');
	//echo $_COOKIE['cpsinfo_duomai'].' - '.$_COOKIE['cpsinfo_duomai_siteid'];
	Header("Location:$to");
} else {
?>

<p>请求参数错误!</p>
<p>将在<span id="mes">5</span> 秒钟后为您跳转到易视网首页！</p>

<script language="javascript" type="text/javascript">
var i = 5;
var intervalid;
intervalid = setInterval("fun()", 1000);
function fun() {
	if (i == 0) {
		clearInterval(intervalid);
		window.location.href = "http://m.easeeyes.com/";
	}
	document.getElementById("mes").innerHTML = i;
	i--;
}
</script>

<?php
}
?>
