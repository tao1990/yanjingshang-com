<?php
/**
 * ajax:工具类相关
 * @version 2014
 * @author xuyizhi
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

//获取地区列表的option
if ($_REQUEST['act'] == 'get_district_option')
{
	//require(dirname(__FILE__) . '/includes/pf_util.php');
	
	$region_type = isset($_REQUEST['region_type'])? intval($_REQUEST['region_type']) : 0;
	$parent_id = isset($_REQUEST['parent_id'])? intval($_REQUEST['parent_id']) : 0;
	
	$str_option = get_district_option($region_type, $parent_id);
    
	echo $str_option;
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
