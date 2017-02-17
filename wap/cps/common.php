<?php
/* =======================================================================================================================
 * cps中间页：负责记录cps来源并跳转到目标页面 【2013/7/18】【Author:yijiangwen】
 * =======================================================================================================================
 * 已受理来源：
 * 1.智推网cps。
 * 2.乐告网cps。
 */
define('IN_ECS', true);
require('../includes/init.php');
date_default_timezone_set('PRC');



//*****************************************************************************//
//智推网cps
//http://www.adsite.com/track.page?a_id=xxx&source=zhitui&subid=xxx&url=http://www.adsite.com
//http:localhost/cps/common.php?a_id=&source=&subid=&url=http://www.easeeyes.com


/* 通用接口规范
 * http://www.easeeyes.com/cps/common.php?a_id=&source=&subid=&url=http://www.easeeyes.com
 * 参数说明
 * a_id  : 广告主id  （可选）
 * source: 广告主标识（必须的）
 * subid : cps平台下级网站信息(或合作站点) 
 * url   : 广告最终着陆地址 为广告主网站的任一URL，包括目录页、单品页、专题页
 * rd有效期：30天。
 */


/* 参数说明
 * a_id  : 广告主id
 * source: 广告主标识
 * subid : 智推下级网站信息(或合作站点)  此参数广告主须原样传回给智推联盟，作为智推网结算的依据。
 * url   : 广告最终着陆地址 为广告主网站的任一URL，包括目录页、单品页、专题页
 * rd有效期：30天。
 */

$source = isset($_REQUEST['source'])? trim($_REQUEST['source']): '';
//$merchant_domain = 'www.easeeyes.com';	
$merchant_domain = '';

if('zhitui' == $source)
{
	$a_id  = isset($_REQUEST['a_id'])? intval($_REQUEST['a_id']): 0;
	$subid = isset($_REQUEST['subid'])? trim($_REQUEST['subid']): '';
	$url   = isset($_REQUEST['url'])? trim($_REQUEST['url']): '';
	$rd    = 30*24*3600;//30天	

	//if(!empty($url) && strpos($url, 'easeeyes.com'))
	//{
		setcookie("zhitui_info", "zhitui|_|$a_id|_|$subid", time()+$rd, "/", $merchant_domain);
		
		if(isset($_COOKIE['zhitui_info']))
		{
			clear_cps_cookie('zhitui_info');
		}
		Header("Location: $url");
	//}
}
elseif('lergao' == $source)
{
	$a_id  = isset($_REQUEST['a_id'])? intval($_REQUEST['a_id']): 0;
	$subid = isset($_REQUEST['subid'])? trim($_REQUEST['subid']): '';
	$url   = isset($_REQUEST['url'])? trim($_REQUEST['url']): '';
	$rd    = 30*24*3600;//30天

	//if(!empty($url) && strpos($url, 'easeeyes.com'))
	//{
		setcookie("lergao_info", "lergao|_|$a_id|_|$subid", time()+$rd, "/", $merchant_domain);
		
		if(isset($_COOKIE['lergao_info']))
		{
			clear_cps_cookie('lergao_info');
		}
		Header("Location: $url");
	//}
}
elseif('woso' == $source)
{
	$a_id  = isset($_REQUEST['a_id'])? intval($_REQUEST['a_id']): 0;
	$subid = isset($_REQUEST['subid'])? trim($_REQUEST['subid']): '';
	$url   = isset($_REQUEST['url'])? trim($_REQUEST['url']): '';
	$rd    = 30*24*3600;//30天

	//if(!empty($url) && strpos($url, 'easeeyes.com'))
	//{
		setcookie("woso_info", "woso|_|$a_id|_|$subid", time()+$rd, "/", $merchant_domain);
		
		if(isset($_COOKIE['woso_info']))
		{
			clear_cps_cookie('woso_info');
		}
		//echo $_COOKIE['woso_info'];
		Header("Location: $url");
	//}
}
else
{
	Header("Location: http://www.easeeyes.com\n");
}

//*****************************************************************************//



//=============================================================================【函数】=============================================================================//


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:清除其它网盟cookie。系统中永远保持单一cookie。
 * ----------------------------------------------------------------------------------------------------------------------
 * $exception: 例外。
 */
function clear_cps_cookie($exception = '')
{
	single_clear_cookie('LTINFO', $exception);
	single_clear_cookie('fanli_uid', $exception);
	single_clear_cookie('cpsinfo_360', $exception);
	single_clear_cookie('cpsinfo_duomai', $exception);
	single_clear_cookie('cpsinfo_fanhuan_channel_id', $exception);
	single_clear_cookie('cpsinfo_yiqifa_src', $exception);
	single_clear_cookie('zhitui_info',        $exception);
	single_clear_cookie('lergao_info',        $exception);
	single_clear_cookie('woso_info',          $exception);
}
function single_clear_cookie($cookie_name='', $exception='')
{
	if(!empty($cookie_name))
	{
		if($cookie_name != $exception)
		{
			if(isset($_COOKIE[$cookie_name]))
			{
				setcookie($cookie_name, '', time()-36000, '/');
			}
			if(!empty($_COOKIE[$cookie_name]))
			{
				unset($_COOKIE[$cookie_name]);
			}
		}
	}
}
?>