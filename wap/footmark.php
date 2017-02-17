<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
$user_id = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']): 0;

$smarty->assign('footmark',               get_footmark() );
$smarty->assign('ur_here',               '浏览足迹' );

$smarty->display('footmark.dwt');






/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:获得已浏览的商品
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_footmark()
{
	@$where = db_create_in($_COOKIE['ECS']['history'], 'goods_id');
    
    @$sql   = 'SELECT goods_id, goods_name, goods_thumb, goods_img, shop_price, market_price FROM ' . $GLOBALS['ecs']->table('goods') .
                " WHERE $where AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 order by INSTR('".$_COOKIE['ECS']['history']."',goods_id) ASC";
     
        $res = $GLOBALS['db']->getAll($sql);
        
        return $res;           
       
}






?>