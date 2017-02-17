<?php
//v2.0接口
session_start();
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

if(isset($_REQUEST['code'])){
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try
	{
		$token = $o->getAccessToken( 'code', $keys ) ;
	}
	catch (OAuthException $e)
	{
	}
}

if($token)
{
	//授权完成
	$_SESSION['token'] = $token;
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token));

	//yi:授权成功后 取得数据操作
	$c   = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
	$ms  = $c->home_timeline(); //done
	$uid_get = $c->get_uid();
	$uid     = $uid_get['uid'];
	$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

	$user_name = $user_message['screen_name']; //用户名
	$open_id   = $uid;                         //open_id 唯一值
	$email     = $uid.'@sina.weibo.com';
	
	//yi到我网站进行的操作	
	header("Location: http://m.easeeyes.com/api/login_sina.php?user_name=".urlencode($user_name)."&email=".$email."&open_id=".$open_id."\n");
}
else
{
	//授权失败 回到登录页面。
	header("Location: http://m.easeeyes.com/user.php \n");
}
?>