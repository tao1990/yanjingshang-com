<?php
/**
 * 每周活动之秒杀
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',          $position['title']);    
$smarty->assign('ur_here',             $position['ur_here']);  
$smarty->assign('helps',               get_shop_help());          //网店帮助文章
$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
$cat_tree = get_category_tree();                     			  //分类列表
/*   $cat_tree   未定义
$smarty->assign('cat_1',        		$cat_tree[1]);
$smarty->assign('cat_6',				$cat_tree[6]);
$smarty->assign('cat_64',				$cat_tree[64]);
$smarty->assign('cat_76',				$cat_tree[76]);
$smarty->assign('cat_159',				$cat_tree[159]);
$smarty->assign('cat_190',				$cat_tree[190]);*/
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$ctime = time();

//当前秒杀商品信息
//$ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE status=0 AND start_time <= $ctime AND end_time >= $ctime ORDER BY rec_id DESC LIMIT 1");
$ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE status=0 AND is_show_wap = 1 AND start_time<".$ctime." AND end_time >".$ctime." ORDER BY rec_id DESC LIMIT 1");
if ($ms)
{
	//格式化价格
	if ($ms['price']) {
		$format_cprice = explode('.', $ms['price']);
		$ms['price_int'] = $format_cprice[0];		//整数部分
		$ms['price_decimal'] = $format_cprice[1];	//小数部分
	}
	
	//市场价
	$ms['market_price'] = $GLOBALS['db']->GetOne("SELECT market_price FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
	if ( ! $ms['market_price'] OR $ms['market_price'] <= 0.00) {
		$ms['market_price'] = $GLOBALS['db']->GetOne("SELECT shop_price FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
	}
	$ms['market_price'] = sprintf("%01.2f", $ms['market_price'] * $ms['ms_number']);
	
	//节省的金额
	$ms['saving'] = sprintf("%01.2f", $ms['market_price'] - $ms['price']);
	
	//折扣
	$ms['zhekou'] = sprintf("%01.1f", ($ms['price'] / $ms['market_price']) * 10);
	
	//格式化秒杀商品的开始或截止时间
	if ($ctime >= $ms['start_time']) {
		//秒杀已开始,格式化截止时间
		$format_ctime['time_type'] = '结束';
		$format_ctime['Y'] = date('Y', $ms['end_time']);
		$format_ctime['n'] = date('n', $ms['end_time']);
		$format_ctime['j'] = date('j', $ms['end_time']);
		$format_ctime['G'] = date('G', $ms['end_time']);
		$format_ctime['i'] = date('i', $ms['end_time']);
	} else {
		//秒杀未开始,格式化开始时间
		$format_ctime['time_type'] = '开始';
		$format_ctime['Y'] = date('Y', $ms['start_time']);
		$format_ctime['n'] = date('n', $ms['start_time']);
		$format_ctime['j'] = date('j', $ms['start_time']);
		$format_ctime['G'] = date('G', $ms['start_time']);
		$format_ctime['i'] = date('i', $ms['start_time']);
	}
	
	//秒杀状态标识：0:未开始  1:进行中	2:已结束
	if ($ms['start_time'] > $ctime) {
		$ms['ms_status'] = 0;
	} elseif ($ms['start_time'] <= $ctime && $ms['end_time'] >= $ctime) {
		$ms['ms_status'] = 1;
	} else {
		$ms['ms_status'] = 2;
	}
	
}

$goods_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (835, 2572, 118, 105)");
$goods_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (2608, 1177, 890, 241)");
$goods_kj = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (1389, 1317, 2619, 1374)");
$goods_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (580, 2280, 924, 2786)");
$goods_hlgj = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (1195, 2277, 642, 643)");

$smarty->assign('ms', $ms);
$smarty->assign('format_ctime', $format_ctime);
$smarty->assign('goods_tmp', $goods_tmp);
$smarty->assign('goods_cp', $goods_cp);
$smarty->assign('goods_kj', $goods_kj);
$smarty->assign('goods_hly', $goods_hly);
$smarty->assign('goods_hlgj', $goods_hlgj);

$smarty->display('miaosha.dwt');

