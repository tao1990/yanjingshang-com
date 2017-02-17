<?php
header("content-Type: text/html; charset=utf-8");
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
//-------------------------------------------------------------mjx投票逻辑--------------------------------------------------------------------------//

$vote  = $_GET["vote"]; //投票前的票数:只有页面刷新才是最新的数据
$gid   = $_GET["gid"];  //商品id
$uid   = $_GET["uid"];  //买家秀图片用户的id
$mjxid = $_GET["mjxid"];//买家秀图片id

if( intval($_COOKIE["yvote"]) >= 5) {
	$res = '';        
	//exit;
}else{

	//更新数据表中的vote;
	$sql="update ".$GLOBALS['ecs']->table('mjx')." set vote=vote+1 where user_id=".$uid." and goods_id=".$gid." and id=".$mjxid.";";
	mysql_query($sql);

	//---------------------------------站内信通知-------------------------------------------//
	$sql = "select * from ecs_user_msg where user_id=".$uid." and extension='mjx_vote' and current_date()=from_unixtime(add_time, '%Y-%m-%d')";
	$h_msg = $GLOBALS['db']->GetRow($sql);
	if(empty($h_msg))
	{
		$title = "亲！您的买家秀收到新的人气投票，快去看看吧！";
		$msg   = "亲爱的：<b>".$user_name."</b><br/>您好！您上传的买家秀图片受到了瞳学的热烈喜欢，并给您投了票。<a href=\'http://www.easeeyes.com/buyersshow_goods.php?mjxid=".$mjxid."\'>点击查看</a>";
		$user_name = $GLOBALS['db']->GetOne("select user_name from ecs_users where user_id=".$uid);
		$sql  = "insert into ecs_user_msg(user_id, user_name, add_time, title, msg, extension, extension_id) ".
				"values(".$uid.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'mjx_vote', ".$mjxid.")";
		$res  = mysql_query($sql);
		if($res){ unread_user_msg($uid); }
	}
	//--------------------------------------------------------------------------------------//

	$rr = false;//票是否增加了1

	//设置cookie--86400秒-----------
	if( intval($_COOKIE["yvote"])>0 && intval($_COOKIE["yvote"]) < 5){

		//加1
		$dd = intval($_COOKIE["yvote"])+1;
		$rr = setcookie("yvote",(string)$dd, time()+86400);
	}else{
		//创建cookie 返回true
		$rr = setcookie("yvote", "1", time()+86400);
	}

	if($rr){
		//返回当前图片的最新票数
		$sql="select vote from ".$GLOBALS['ecs']->table('mjx')." where user_id=".$uid." and goods_id=".$gid." and id=".$mjxid.";";
		$vote_new = $GLOBALS['db']->GetOne($sql);	
		$res = $vote_new; 
	}
}

$res = $res.',vote'.$mjxid;
echo $res;

?>