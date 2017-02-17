<?php
//buyersshow_user.php下面：我的晒单的列表 分享
session_start();
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$code_url = $o->getAuthorizeURL( 'http://www.easeeyes.com/api/sina/weibodemo/callback_sync_share_buyersshow.php' );
header("Location: ".$code_url."\n");
?>