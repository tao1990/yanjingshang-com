<?php
/* =======================================================================================================================
 * 商城页面 ysyj.com检查页面
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
//require_once(ROOT_PATH . 'includes/lib_order.php');
//include_once(ROOT_PATH . 'includes/lib_transaction.php');


$smarty->assign('at1', get_res(0, 12, 14));
$smarty->assign('at2', get_res(12,12));

$tt3 = "2727,2687,2678,2661,2637,2550,2497,2741,2739,2768,2764,2700,2666,2656,2622,2600,2590,2583";
$tt4 = "2747,2743,2709,2680,2664,2629,2612,2609,2758";
$tt5 = "2756,2754,2707,2673,2614,2572,2505,2402,2403,2379,2334";
$tt6 = "2714,2711,2493,2492,2468,2467,2434,2367,2278,2131,2009";
$smarty->assign('at3', get_res_str($tt3, 12));
$smarty->assign('at4', get_res_str($tt4, 9));
$smarty->assign('at5', get_res_str($tt5, 12));
$smarty->assign('at6', get_res_str($tt6, 12));


$sql	 = "select article_id, title from ".$GLOBALS['ecs']->table('article')." where cat_id=16 order by add_time desc limit 0,28;";
$article = $db->getAll($sql);
$smarty->assign('article', $article);

$smarty->display('ysyj.dwt');




//yi:函数
function get_res($st, $num=10, $cat_id=0)
{
	$tips= ($cat_id>0)? " and cat_id=".$cat_id : "";
	$sql = "select article_id, title from ecs_article where 1 ".$tips." order by add_time desc limit ".$st.",".$num.";";
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}

function get_res_str($str, $num=10)
{
	$sql = "select article_id, title from ecs_article where article_id in(".$str.") limit ".$num;
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}
?>