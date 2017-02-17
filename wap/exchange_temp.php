<?php
/* =================================================================================================
 * 商城页面 积分兑换列表页面 具体兑换页面【2012/4/6】【TIME:2012/10/11】
 * =================================================================================================
 * 散光片不进行积分兑换
 * 积分兑换商品，一次只能兑换一个, 如果一副眼镜，则要兑换两次就可以了。
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_clips.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
if((DEBUG_MODE & 2) != 2){ $smarty->caching = true;}

$id = $_GET['id'];

if($id == 1){
    $coupon_sn = '354637';
    $end_date  = '2016年11月01日';
    $name      = '隐形眼镜30元红包（满199可用）'; 
    $desc      =  '';
}elseif($id == 2){
    $coupon_sn = '940458';
    $end_date  = '2016年11月01日';
    $name      = '注册即送澜柏护理液2*10ml（新加坡进口）'; 
    $desc      = '';   
}


//------------------------------------页头 页尾 数据---------------------------------------//
$smarty->assign('img_site',             IMG_PATH); //CDN图片路径
$position = assign_ur_here();
$smarty->assign('column',               get_column() ); //栏目导航
$smarty->assign('page_title',          $position['title']);    
$smarty->assign('ur_here',             $position['ur_here']);  
$smarty->assign('topbanner',           ad_info_by_time(31,1));            //头部横幅广告
$smarty->assign('helps',               get_shop_help());          //网店帮助文章
$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
$cat_tree = get_category_tree();                     			  //分类列表
$smarty->assign('cat_1',        		$cat_tree[1]);
$smarty->assign('cat_6',				$cat_tree[6]);
$smarty->assign('cat_64',				$cat_tree[64]);
$smarty->assign('cat_76',				$cat_tree[76]);	
$smarty->assign('cat_159',				$cat_tree[159]);
$smarty->assign('cat_190',				$cat_tree[190]);
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
//------------------------------------页头 页尾 数据end------------------------------------//


		$smarty->assign('end_date',   $end_date);
		$smarty->assign('coupon_sn',  $coupon_sn);
        $smarty->assign('name',       $name);
        $smarty->assign('desc',       $desc);
		$smarty->assign('ur_here',    '<a href="./">首页</a> <code>></code> <a href="exchange.html">会员专区</a> <code>></code> 积分换券'); 
		$smarty->display('coupon_res_temp.dwt');
	//==============================自动发放优惠券end===========================//	

//yi:返回一个随机数字--作为图片路径
function rand_adv()
{	
	return rand(1,3);
}
?>