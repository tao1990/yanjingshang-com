<?php
/* =================================================================================================
 * 商城页面 商品配件加入购物车【2012/3/13】
 * =================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');

$goods_id = isset($_GET['goods_id'])? intval($_GET['goods_id']): 0;
$res_str  = "hao";  //ajax调用，返回的字符串


/*-----------------------------------配件是否已经在购物车中：if在：返回一个标志-------------------------------------------------*/
$sql3 = "select * from ".$GLOBALS['ecs']->table('cart')." where goods_id=".$goods_id." AND session_id='".SESS_ID."' limit 1;";
$row  = $GLOBALS['db']->getRow($sql3);
$hava = !empty($row)? 1: 0; //已经在购物车中，ajax更新那个商品的数量和价格。

//配件商品加入购物车 $suc==true 表示添加成功
$aa  = array();
$suc = addto_cart($goods_id, 1, $aa, 0, '', '', '', '');


/*-----------------------------------返回已加入购物车中商品的信息----------------------------------------------------------------*/
$res = mysql_query("select goods_name,goods_img from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$goods_id." limit 1;");
while($row = mysql_fetch_array($res))
{
	$aa = $row['goods_name'];
	$a1 = $row['goods_img'];
}

//购物车表
$sql2 = "select * from ".$GLOBALS['ecs']->table('cart')." where goods_id=".$goods_id." AND session_id='".SESS_ID."' limit 1;";
$res2 = mysql_query($sql2);
while($row = mysql_fetch_array($res2))
{
	$a2			= $row['goods_price'];
	$rec_id		= $row['rec_id'];
	$cart_num	= $row['goods_number'];
}

/*----------------------------------重新计算商品数量，积分，价格总计传到前端----------------------------------------------------*/
$tnum       = cart_goods_total_num(); 
$cart_goods = get_cart_goods();
$total_sum  = $cart_goods['total']['goods_price']; //购物车总金额
$points     = $cart_goods['total']['goods_amount'];//购物车总积分
$cart_weight= cart_goods_total_weight();           //商品总重
$base_line  = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 150;
$freepx     = ($cart_goods['total']['goods_pricex'] > $base_line)? 0: ($base_line - $cart_goods['total']['goods_pricex']); //免运费句子
//yi:订单是否包邮功能，包邮提示语句【唯一】flow,add_to_cart 2个页面中引用。
if(include_free_ship_goods())
{
	$freepx = -1;
}
/*----------------------------------------------------------[end]-----------------------------------------------------------------*/

$res_str = $aa.','.$a1.','.$a2.','.$tnum.','.$total_sum.','.$points.','.$freepx.','.$goods_id.','.$suc.','.$rec_id.','.$have.','.$cart_num.','.$cart_weight.','.$base_line;
echo $res_str;

?>