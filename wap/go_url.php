<?php
/* ============================================================================
 * 商城页面 客户留言（提问处理页面）
 * ============================================================================
 */
header("content-Type: text/html; charset=utf-8");
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$id = cleanInput($_REQUEST['id']);
$sid =explode('d',$id);
$sid =(int)$sid[1];

if($sid){
    $longUrl = $GLOBALS['db']->getOne("SELECT long_url FROM ecs_short_url WHERE sid = ".$sid);
    if(!$longUrl){
        $error = 1;
    }
}else{
    $error = 1;
}
if($error == 1 || !$longUrl){
    header("HTTP/1.1 404 Not Found");exit;  
}else{
    if(!strstr($longUrl,'http://')){
        header("Location:".$_SERVER['HTTP_HOST']."/$longUrl");
    }else{
        header("Location:$longUrl");
    }
}

die;
?>