<?php
session_start();
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );

//添加指定的登录后跳转地址2014-03-31
$defined_url = isset($_REQUEST['defined_url']) ? $_REQUEST['defined_url'] : 'http://m.easeeyes.com/';
if ( ! empty($defined_url))
{
	$_SESSION['defined_url'] = $defined_url;
}

header("Location: ".$code_url."\n");
?>