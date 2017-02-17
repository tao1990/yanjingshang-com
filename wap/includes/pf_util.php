<?php
/**
 * 工具类函数
 * @version 2014
 * @author xuyizhi
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 获取地区列表,返回数组
 * @param int $type 1.省; 2.市; 3.区
 * @param int $parent 上级region_id
 */
function get_district_lsit($type=0, $parent=0)
{
	return $GLOBALS['db']->GetAll("SELECT region_id, region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_type = ".intval($type)." AND parent_id = ".intval($parent));
}

/**
 * 获取地区列表,返回字符串
 * @param int $type 1.省; 2.市; 3.区
 * @param int $parent 上级region_id
 */
function get_district_option($type=0, $parent=0)
{
    $list = $GLOBALS['db']->GetAll("SELECT region_id, region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_type = ".intval($type)." AND parent_id = ".intval($parent));
    $str = '';
    if ($list)
    {
    	foreach ($list as $v)
    	{
    		$str .= "<option value='".$v['region_id']."'>".$v['region_name']."</option>";
    	}
    }
    return $str;
}