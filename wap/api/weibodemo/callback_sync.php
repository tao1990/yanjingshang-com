<?php
//授权成功后不登录，关闭页面
session_start();

define('IN_ECS', true);
require(dirname(__FILE__) . '/../../../includes/init.php');

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

//session_destroy();
//exit;

if(isset($_REQUEST['code'])){
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = 'http://www.easeeyes.com/api/sina/weibodemo/callback_sync.php';
	try
	{
		$token = $o->getAccessToken( 'code', $keys ) ;
	}
	catch (OAuthException $e)
	{
	}
}

/*if(!isset($_SESSION['token'])) {
	$_SESSION['token'] = array(
		'access_token'	=>	'2.00SpoBHC4mjoyD59fade001ebCOmjC', 
		'remind_in'		=>	'654124', 
		'expires_in'	=>	'654124',
		'uid'			=>	'1936133724'
	);
}*/
//print_r($_SESSION);
//echo '<br><br>';



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博接口</title>
</head>
<body style="background:#e6f2fb; margin:0; padding:0;">

<?php
if($token)
{
	//授权完成
	$_SESSION['token'] = $token;
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token));

	/*$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
	$ms  = $c->home_timeline(); // done
	$uid_get = $c->get_uid();
	$uid = $uid_get['uid'];
	$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息*/

	//授权成功,记录到用户绑定同步表
	//print_r($_SESSION);
	//echo '<br><br>';
	if ($_SESSION['token']) {
		//echo $_SESSION['user_id'].'<br/>';
		
		if ($_SESSION['user_id']) {
			$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
			$rs = $GLOBALS['db']->getAll($sql);

			$time = time();
			$session_data = serialize($_SESSION['token']);
			if ($rs) {
				$sina_exist = false;
				foreach ($rs as $k => $v) {
					if ($v['app_name'] == 'sina') {
						$sina_exist = true;
						break;
					}
				}

				if ($sina_exist) {
					$GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('user_app_sync') . " SET add_time = $time, session_data = '$session_data', sync_status = 1 WHERE user_id = " . $_SESSION['user_id'] . " AND app_name = 'sina'");
				} else {
					$GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('user_app_sync') . " (user_id, app_name, add_time, session_data, sync_option, sync_status) VALUES ('".$_SESSION['user_id']."', 'sina', $time, '$session_data', '', 1)");
				}

			} else {
				$GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('user_app_sync') . " (user_id, app_name, add_time, session_data, sync_option, sync_status) VALUES ('".$_SESSION['user_id']."', 'sina', $time, '$session_data', '', 1)");
			}
		}
	}

	//echo '<input type="button" value="授权成功,请关闭页面"  style="margin:50px;" onclick="window.close();" />';
	//echo '<a href="javascript:;" onclick="window.opener=null;window.open(\'\',\'_self\');window.close();">授权成功,请关闭页面</a>';
	/*echo '<script>parentDialog.close();</script>';*/
	echo '<div style="width:800px; height:660px;" onmouseover="parentDialog.close();">&nbsp;</div>';
}

else
{
	echo '<input type="button" value="授权失败,请重新登录" onclick="window.location=\'http://www.easeeyes.com/api/sina/weibodemo/sync.php\'" style="margin:50px;" />';
}
?>

</body>
</html>
