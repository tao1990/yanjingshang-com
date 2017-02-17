<?php
//*==================================================领克特cps接口1 2011-9-7 yijiangwen==============================================*//
////set merchant server domain name as ".linktech.cn"

//2014-01-08 处理恶意流量
/* /cps/linktech/ltfront.php?a_id=A100170941&m_id=easeeyes&c_id=23100813860369^20130416152833-87676&l_id=99999&l_type1=01&rd=60&url=http%3A%2F%2Fwww.easeeyes.com */
// /cps/linktech/ltfront.php?a_id=A100170941&m_id=easeeyes&c_id=23100813860369%5E20130416152833-87676&l_id=99999&l_type1=01&rd=60&url=http%3A%2F%2Fwww.easeeyes.com 
///cps/linktech/ltfront.php?a_id=A100192194&m_id=easeeyes&c_id=23662310870099^20140813211359-86493&l_id=99999&l_type1=01&rd=30&url=http%3A%2F%2Fwww.easeeyes.com
///cps/linktech/ltfront.php?a_id=A100192194&m_id=easeeyes&c_id=23662310870099^20140813211359-86493&l_id=99999&l_type1=01&rd=30&url=http%3A%2F%2F192.168.1.52:3001/wap
if ($_REQUEST["a_id"] == 'A100170941' && $_REQUEST["m_id"] == 'easeeyes' && $_REQUEST["c_id"] == '23662310870099^20140813211359-86493' && $_REQUEST["l_id"] == '99999' && $_REQUEST["l_type1"] == '01' && $_REQUEST["rd"] == '60')
{
	header('HTTP/1.1 404 Not Found');
	exit;
}
if ($_REQUEST["a_id"] == 'A10016319519973432593180' || $_REQUEST["a_id"] == 'A100191970' || $_REQUEST["a_id"] == 'A100170941') 
{
	header('HTTP/1.1 404 Not Found');
	exit;
}
/* if ($_REQUEST['a_id'] == 'A100192194'){
    header('HTTP/1.1 404 Not Found');
	exit;
} */


$merchant_domain="www.easeeyes.com";
if(!get_cfg_var("register_globals"))
{
	$a_id  = $_REQUEST["a_id"];
	$m_id  = $_REQUEST["m_id"];
	$c_id  = $_REQUEST["c_id"];
	$l_id  = $_REQUEST["l_id"];
	$l_type1 = $_REQUEST["l_type1"];
	$rd    = $_REQUEST["rd"];
	$url   = $_REQUEST["url"];
}
//非本站则跳出404
if($url!=''){
    $expUrl = explode('.',$url);
    if(!strstr($expUrl[0],'easeeyes') && !strstr($expUrl[1],'easeeyes')){
        header("HTTP/1.1 404 Not Found");exit;  
    }
}
if($a_id=="" or $m_id=="" or $c_id=="" or $l_id=="" or $l_type1=="" or $rd=="" or $url=="")
{
	echo ("
		<html><head><script language=\"javascript\">
		<!--
				alert('LPMS:不能连接，请咨询网站负责人。');
				history.go(-1);
		//-->
		</script></head></html>
		 ");
	exit;
}
Header("P3P:CP=\"NOI DEVa TAIa OUR BUS UNI\"");

if($rd==0){
	SetCookie("LTINFO","$a_id|$c_id|$l_id|$l_type1|",0,"/", $merchant_domain);
	//SetCookie("LTINFO","$a_id|$c_id|$l_id|$l_type1|",0,"/", "");
}else{
	SetCookie("LTINFO","$a_id|$c_id|$l_id|$l_type1|",time()+(15*24*60*60),"/", $merchant_domain);
	//SetCookie("LTINFO","$a_id|$c_id|$l_id|$l_type1|",time()+(15*24*60*60),"/", "");
}

//*============================清除其它网盟的cookie防止重复返利=============================*//
if(isset($_COOKIE['LTINFO'])){
	//删除51fanli的cookie
	setcookie('channelid','51fanli',time()-3600,'/');
	//清除其他cps合作cookie
	if (isset($_COOKIE['fanli_uid'])) setcookie('fanli_uid', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_360'])) setcookie('cpsinfo_360', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_duomai'])) setcookie('cpsinfo_duomai', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_fanhuan_channel_id'])) setcookie('cpsinfo_fanhuan_channel_id', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_ty_appid'])) setcookie('cpsinfo_ty_appid', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_51fanli_channel_id'])) setcookie('cpsinfo_51fanli_channel_id', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_yiqifa_src'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');
	if (isset($_COOKIE['cpsinfo_yiqifa_src_roi'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');
	if(isset($_COOKIE['channelid']))
	{
		unset($_COOKIE['channelid']);
	}
}
//*============================清除其它网盟的cookie防止重复返利=============================*//

Header("Location: $url");
?>