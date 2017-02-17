<?php
session_start();
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$code_url = $o->getAuthorizeURL( 'http://www.easeeyes.com/api/sina/weibodemo/callback_close.php' );
header("Location: ".$code_url."\n");
?>