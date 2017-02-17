<?php
/**
 * 促销活动相关函数
 * @version 2014
 * @author xuyizhi
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 获取当前时间段的促销活动
 */
function get_current_promotion()
{
	$current_time = $_SERVER['REQUEST_TIME'];
	$user_rank = $_SESSION['user_rank']; //用户等级
	
	$sql = "SELECT * FROM " .$GLOBALS['ecs']->table('promotion'). " WHERE start_time <= $current_time AND end_time >= $current_time 
			AND FIND_IN_SET('".$user_rank."', user_rank)";
	$rs = $GLOBALS['db']->getAll($sql);
	
	return $rs;
}