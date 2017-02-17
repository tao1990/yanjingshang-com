<?php
//授权成功后不登录，关闭页面
//周年庆：分享活动网址,发放红包
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
	$keys['redirect_uri'] = 'http://www.easeeyes.com/api/sina/weibodemo/callback_anniversary.php';
	try
	{
		$token = $o->getAccessToken( 'code', $keys ) ;
	}
	catch (OAuthException $e)
	{
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>新浪微博接口</title>
<script type="text/javascript" src="/js/yijq.js"></script>
</head>
<body style="background:#f16a79; margin:0; padding:0;">

<?php
if($token)
{
	//授权完成
	$_SESSION['token'] = $token;
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token));

	//授权成功,记录到用户绑定同步表
	//print_r($_SESSION);
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

		//发微博
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );

		$weibo_text = '#易视3周年庆#210元现金券免费领！0元抢、品牌底价秒、美瞳99元2副、神秘日等尽请期待8.1！'." http://www.easeeyes.com/click.html?ad_url=http%253A%252F%252Fwww.easeeyes.com%252Factive130729.html&ad_source=sina3&ad_medium=&ad_keyword=&ad_conent=&ad_name=&ad_director=";
		$weibo_img = 'http://www.easeeyes.com/themes/default/images/easeeyes_3.jpg';
		$ret = $c->upload($weibo_text, $weibo_img);
		if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
			echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
		} else {
			//echo "<p>发送成功</p>";
			//echo '<input type="button" value="发布成功"  style="margin:50px;" onclick="parentDialog.close();" />';
			echo '<div style="margin:20px; text-align:center"><img src="/themes/default/images/active/20130729/bonus.jpg" title="" alt="" border="0" /></div>';
		}
	}

	//echo '<input type="button" value="授权成功,请关闭页面"  style="margin:50px;" onclick="window.close();" />';
	//echo '<a href="javascript:;" onclick="window.opener=null;window.open(\'\',\'_self\');window.close();">授权成功,请关闭页面</a>';
	/*echo '<script>parentDialog.close();</script>';*/
	//echo '<input type="button" value="发布成功"  style="margin:50px;" onclick="parentDialog.close();" />';
}

else
{
	echo '<input type="button" value="授权失败,请重新登录" onclick="window.location=\'http://www.easeeyes.com/api/sina/weibodemo/sync.php\'" style="margin:50px;" />';
}
?>

</body>
</html>

<script type="text/javascript">
$(document).ready(function(){
	var user_id = "<?php echo $_SESSION['user_id']; ?>";
	
	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=654&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});

	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=655&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});

	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=656&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});

	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=657&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});

	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=658&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});

	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=660&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});
	
	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=681&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});
	
	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=682&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});
	
	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=683&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});
	
	$.ajax({
		type:'POST',
		url:'http://www.easeeyes.com/ajax_step.php?act=send_bonus',	
		data:'&user_id='+user_id+'&bonus_type_id=684&m='+Math.random(),		
		cache:false,
		success:
			function(dd){
			}
	});

});
</script>