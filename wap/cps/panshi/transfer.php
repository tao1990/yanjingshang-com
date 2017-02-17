<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php
// http://192.168.1.53:3001/cps/panshi/transfer.php?um_id=xxx&track_code=xxxxxx&target=http%3A%2F%2F192.168.1.53%3A3001%2F
$feedback = isset($_GET['feedback']) ? $_GET['feedback'] : '';	//广告计划以及网站主渠道的标识
$to = isset($_GET['to']) ? $_GET['to'] : '';				//跳转目标地址

$um_id =  isset($_GET['um_id']) ? $_GET['um_id'] : '';                  // 广告主标识数据的来源
$track_code =  isset($_GET['track_code']) ? $_GET['track_code'] : '';   // 盘石网盟跟踪标签
$target =  isset($_GET['target']) ? $_GET['target'] : '';               // 广告主网站的任意落地页面地址



//非本站则跳出404
if($target!=''){
    $expUrl = explode('.',$target);
    if(!strstr($expUrl[0],'easeeyes') && !strstr($expUrl[1],'easeeyes')&& !strstr($expUrl[0],'http://192')){
        header("HTTP/1.1 404 Not Found");exit;
    }
}

//清除其他cps合作cookie
if (isset($_COOKIE['LTINFO'])) setcookie('LTINFO', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_duomai'])) setcookie('cpsinfo_duomai', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_yiqifa_src'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_zhongmin_uid'])) setcookie('cpsinfo_zhongmin_uid', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_51fanli_channel_id'])) setcookie('cpsinfo_51fanli_channel_id', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_smzdm'])) setcookie('cpsinfo_smzdm_feedback', '', time()-3600, '/');

if ($track_code && $target) {
	//记入cookie,跳转
	setcookie('wap_cpsinfo_panshi', 'panshi', time()+(3600*12*24), '/', '');
	setcookie('wap_cpsinfo_panshi_info', $track_code, time()+(3600*12*24), '/', '');
	Header("Location:$target");
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
		window.location.href = "http://www.easeeyes.com/";
	}
	document.getElementById("mes").innerHTML = i;
	i--;
}
</script>

<?php
}
?>
