<?php
/* =======================================================================================================================
 * wap分类目录
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
//$a = category_list(0);
$menu_list = get_menu_list();
//echo '<pre/>';
//var_dump($menu_list);exit();

$smarty->assign('menu_list_bot',            $menu_list );
$smarty->assign('ur_here',              '商品类目' ); 

$smarty->display('menu.dwt');

/**
 * 获取产品分类
 */
/*function category_list($parent_id=0)
{
	$sql = "select cat_id,cat_name from ".$GLOBALS['ecs']->table('category')." where cat_id!=138  and parent_id= ".$parent_id;
	return $GLOBALS['db']->GetAll($sql);
}*/

/**
 * 获取分类目录
 */
function get_menu_list(){
   $top = category_list(0);
    foreach($top as $k => $v){
        $son = category_list($v['cat_id']);
        //$list[] = $v;
        $p_arr = array($v['cat_id'],$v['cat_name']);
        $p_arr['son'] = $son;
        $list[]=$p_arr;
    }
    return $list;
}

function gg(){
    return 'yes';
}