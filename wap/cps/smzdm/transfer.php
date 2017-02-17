<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php
// http://192.168.1.52:3001/cps/smzdm/transfer.php?feedback=26_0_183_&to=http%3A%2F%2F192.168.1.53%3A3001%2F
// http://www.linkstars.com/click.php?feedback=26_0_183_&to=http%3A%2F%2F192.168.1.52%3A3001%2Fcps%2Fsmzdm%2Ftransfer.php%3Ffeedback%3D26_0_183_%26to%3Dhttp%3A%2F%2F192.168.1.52%3A3001%2F
$feedback = isset($_GET['feedback']) ? $_GET['feedback'] : '';	//广告计划以及网站主渠道的标识
$to = isset($_GET['to']) ? $_GET['to'] : '';				//跳转目标地址

//非本站则跳出404
if($to!=''){
    $expUrl = explode('.',$to);
    if(!strstr($expUrl[0],'easeeyes') && !strstr($expUrl[1],'easeeyes')){
        header("HTTP/1.1 404 Not Found");exit;  
    }
}

//清除其他cps合作cookie
if (isset($_COOKIE['LTINFO'])) setcookie('LTINFO', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_duomai'])) setcookie('cpsinfo_duomai', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_yiqifa_src'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_zhongmin_uid'])) setcookie('cpsinfo_zhongmin_uid', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_51fanli_channel_id'])) setcookie('cpsinfo_51fanli_channel_id', '', time()-3600, '/');

if ($feedback && $to) {
	//记入cookie,跳转
	setcookie('cpsinfo_smzdm', 'smzdm', time()+(3600*12*24), '/', '');
	setcookie('cpsinfo_smzdm_feedback', $feedback, time()+(3600*12*24), '/', '');
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
