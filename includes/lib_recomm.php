<?php
/* ============================================================================
 * 广告/推荐公用函数 Tao【2015/06/24】
 * ============================================================================
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 后台添加页面图片
 * @param $pid position_id
 * @param $size 图片张数 
 */
function ad_info($pid=0, $size=1)
{
    $sql =  'SELECT ad_id,ad_code,ad_link,ad_name FROM ' . $GLOBALS['ecs']->table('ad') . ' '.
			' WHERE enabled=1 and position_id='.$pid.' ' .
			' ORDER BY sort_order ASC, ad_id DESC LIMIT '.$size.' ' ;
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach($res AS $idx => $row)
    {
        $arr[$idx]['id']         = $row['ad_id'];   
		$arr[$idx]['ad_name']    = $row['ad_name'];         
        $arr[$idx]['ad_code']    = $row['ad_code'];
		$arr[$idx]['ad_link']    = $row['ad_link'];       
    }
    return $arr; 
}

/**
 * 后台添加页面图片
 * @param $pid position_id
 * @param $size 图片张数 
 */
function ad_info_by_time($pid=0, $size=1)
{
    $time = time();
    $sql =  'SELECT * FROM ' . $GLOBALS['ecs']->table('ad') . ' '.
			' WHERE enabled=1 and position_id='.$pid.' ' .
    		' AND start_time <'.$time.' AND end_time >'.$time.' '.
			' ORDER BY sort_order ASC LIMIT '.$size.' ' ;
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach($res AS $idx => $row)
    {
    		$arr[$idx]['id']         = $row['ad_id'];   
			$arr[$idx]['ad_name']    = $row['ad_name'];         
	        $arr[$idx]['ad_code']    = $row['ad_code'];
			$arr[$idx]['ad_link']    = $row['ad_link']; 
    }
    return $arr; 
}

/**
 * 获取品牌列表数据信息
 * @param $num 数量 
 */

function get_brand_info_list($num=20)
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('brand').' where is_show=1 ORDER BY sort_order limit 0,'.$num;
    
    return $GLOBALS['db']->getAll($sql);
}
?>