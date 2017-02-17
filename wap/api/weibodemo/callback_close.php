<?php
//授权成功后不登录，关闭页面
session_start();
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

//session_destroy();
//exit;

/*if(isset($_REQUEST['code'])){
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = 'http://www.easeeyes.com/api/sina/weibodemo/callback_close.php';
	try
	{
		$token = $o->getAccessToken( 'code', $keys ) ;
	}
	catch (OAuthException $e)
	{
	}
}*/

if(!isset($_SESSION['token'])) {
	//$_SESSION['token']['access_token'] = '2.00SpoBHC4mjoyD59fade001ebCOmjC';
	//$_SESSION['token']['remind_in'] = '654124';
	//$_SESSION['token']['expires_in'] = '654124';
	//$_SESSION['token']['uid'] = '1936133724';
	$_SESSION['token'] = array('access_token'=>'2.00SpoBHC4mjoyD59fade001ebCOmjC', 'remind_in' => '654124', 'expires_in' => '654124', 'uid' => '1936133724');
}

print_r($_SESSION);
echo '<br><br>';

$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
$ms  = $c->home_timeline(); // done
$uid_get = $c->get_uid();
$uid = $uid_get['uid'];
$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博接口</title>
</head>
<body style="background:#e6f2fb; margin:0; padding:0;">


<?=$user_message['screen_name']?>,您好！ 
<h2 align="left">发送新微博</h2>
<form action="" >
	<input type="text" name="text" style="width:300px" />
	<input type="submit" />
</form>

<?php
if( isset($_REQUEST['text']) ) {
$ret = $c->update( $_REQUEST['text'] );	//发送微博
if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
	echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
} else {
	echo "<p>发送成功</p>";
}
}
?>

<?php if( is_array( $ms['statuses'] ) ): ?>
<?php foreach( $ms['statuses'] as $item ): ?>
<div style="padding:10px;margin:5px;border:1px solid #ccc">
	<?=$item['text'];?>
</div>
<?php endforeach; ?>
<?php endif; ?>


<?php
/*if($token)
{
	//授权完成
	$_SESSION['token'] = $token;
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token));

	echo '<input type="button" value="授权成功,请关闭页面"  style="margin:50px;" />';
}

else
{
	echo '<input type="button" value="授权失败,请重新登录" onclick="window.location=\'http://www.easeeyes.com/api/sina/weibodemo/shouquan.php\'" style="margin:50px;" />';
	//header("Location: http://www.easeeyes.com/api/sina/weibodemo/shouquan.php \n");
}*/
?>

</body>
</html>
