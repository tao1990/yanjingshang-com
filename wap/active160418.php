<?php
/* =======================================================================================================================
 * 商城 总的活动页面 今后的活动页面通通带上参数进行：active.php?id=120802 =>然后我通过url处理变成静态页面。
 * =======================================================================================================================
 * active.php?id=120802 url说明：id=120802是六位的日期，是站内的正常的活动。
 * 如果是qq活动的页面。统一采用99开头的8位日期。 如：id=99120802。
 * 如果其它活动的页面。统一采用599开头的9位日期。如：id=599120802。
 * 如果要控制广告横幅，可以采用参数的方式进行。或者我再写一个active2.php就行了。
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

$end_status = 0;//活动结束状态

$smarty->assign('week',  date('w'));

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',          '活动详情 - 易视网手机版');
$smarty->assign('ur_here',             '活动详情');
$smarty->assign('helps',               get_shop_help());          //网店帮助文章
$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
$cat_tree = get_category_tree();                     			  //分类列表
/*$smarty->assign('cat_1',        		$cat_tree[1]);
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

$pid = isset($_REQUEST['id'])? intval($_REQUEST['id']): 0;

//会员是否登录
$user_id = (isset($_SESSION['user_id']) && $_SESSION['user_id']>0)? intval($_SESSION['user_id']): 0;
//$smarty->assign('column',               get_column() ); //栏目导航
$smarty->assign('user_id', $user_id);
$smarty->assign('back_act', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//判断活动页面来源，显示相应的抬头背景文件。
$refer = "";
if(!empty($pid))
{
	switch($pid)
	{
		case $pid>99000000: //来自qq
			$refer = "qq";
			$pid   = intval($pid - 99000000);
			break;
		default:            //来自本站
			$refer = "";  
	}
}

if($pid == 120711)
{
	$smarty->assign('topbanner',           ad_info(31,1));            //头部横幅广告
}
elseif($pid == 130701)
{
	if($user_id > 0)
	{
		$sql = "SELECT * FROM ecs_user_bonus WHERE user_id ='$user_id' AND bonus_type_id = 637 limit 1;";
		$res = $GLOBALS['db']->getAll($sql);
		if($res) $smarty->assign('have_bonus',	1);
		else $smarty->assign('have_bonus',	0);
	}
}
elseif($pid == 130624)
{
	if($_SESSION['user_id'] > 0) {
		$smarty->assign('user_id',	$_SESSION['user_id']);
		
		//判断是否已领取红包
		$sql = "SELECT * FROM ecs_user_bonus WHERE user_id = " .$_SESSION['user_id']. " AND bonus_type_id = 596";
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) $smarty->assign('have_bonus',	1);
		else $smarty->assign('have_bonus',	0);
	}
}
elseif($pid == 130618)
{
	if($_SESSION['user_id'] > 0) {
		$smarty->assign('user_id',	$_SESSION['user_id']);
		
		//判断是否已领取红包
		//全场满300减50
		$sql = "SELECT * FROM ecs_user_bonus WHERE user_id = " .$_SESSION['user_id']. " AND bonus_type_id = 593";
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) $smarty->assign('have_bonus_50',	1);
		else $smarty->assign('have_bonus_50',	0);
		
		//全场满199减30
		$sql = "SELECT * FROM ecs_user_bonus WHERE user_id = " .$_SESSION['user_id']. " AND bonus_type_id = 595";
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) $smarty->assign('have_bonus_30',	1);
		else $smarty->assign('have_bonus_30',	0);
		
		//全场满200减15
		$sql = "SELECT * FROM ecs_user_bonus WHERE user_id = " .$_SESSION['user_id']. " AND bonus_type_id = 594";
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) $smarty->assign('have_bonus_15',	1);
		else $smarty->assign('have_bonus_15',	0);
	}
}
elseif($pid == 130604)
{
	//yi:ck活动，商品会随时调整
	$sql = "select g.goods_id, g.goods_name, g.market_price,g.goods_img, s.add_time, s.exclusive_price from ecs_goods as g left join ecs_source as s on g.goods_id=s.goods_id where g.cat_id=191 and g.goods_type=16 and g.is_delete=0 and g.is_on_sale=1 and g.is_alone_sale=1 and s.source='easeeyes' and s.is_by=1 and g.goods_id<>2151 and g.goods_id<>2164 and g.goods_id<>2165 ";
	$s_goods = $GLOBALS['db']->GetAll($sql);
	
	foreach($s_goods as $k => $v)
	{
		$s_goods[$k]['url']      = "goods".$v['goods_id'].".html?from=easeeyes-_-active-_-".$v['add_time'];
		$tm						 = explode('.', $v['goods_img']);
		$s_goods[$k]['img_type'] = (!empty($tm[1]))? '.'.$tm[1]: '.jpg';
		$s_goods[$k]['exclusive_price'] = intval($v['exclusive_price']);
	}
	$smarty->assign('s_goods',    $s_goods); 
}
elseif($pid == 130603)
{
	date_default_timezone_set('PRC');
	if($_SESSION['user_id'] > 0) {
		$smarty->assign('user_id',	$_SESSION['user_id']);
		
		//判断是否已领取红包
		$sql = "SELECT * FROM ecs_user_bonus WHERE user_id = " .$_SESSION['user_id']. " AND bonus_type_id = 552";
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) $smarty->assign('have_bonus',	1);
		else $smarty->assign('have_bonus',	0);
	}
	
	//秒杀时间区间：2013-06-03 2013-06-09
	$time = time();
	$t = date('md', time());
	$ms_str = '';
	$img_path = '';
	if ($t < '0603') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'ago';
		$img_path_0604 = 'ago';
		$img_path_0605 = 'ago';
		$img_path_0606 = 'ago';
		$img_path_0607 = 'ago';
		$img_path_0608 = 'ago';
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0603') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		if ($time < strtotime('2013-06-03 11:00:00')) {
			$img_path_0603 = 'ago';
		} elseif ($time >= strtotime('2013-06-03 11:00:00') && $time <= strtotime('2013-06-03 15:00:00')) {
			$img_path_0603 = 'now';
		} else {
			$img_path_0603 = 'after';
		}
		$img_path_0604 = 'ago';
		$img_path_0605 = 'ago';
		$img_path_0606 = 'ago';
		$img_path_0607 = 'ago';
		$img_path_0608 = 'ago';
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0604') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		if ($time < strtotime('2013-06-04 11:00:00')) {
			$img_path_0604 = 'ago';
		} elseif ($time >= strtotime('2013-06-04 11:00:00') && $time <= strtotime('2013-06-04 15:00:00')) {
			$img_path_0604 = 'now';
		} else {
			$img_path_0604 = 'after';
		}
		$img_path_0605 = 'ago';
		$img_path_0606 = 'ago';
		$img_path_0607 = 'ago';
		$img_path_0608 = 'ago';
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0605') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		$img_path_0604 = 'after';
		if ($time < strtotime('2013-06-05 11:00:00')) {
			$img_path_0605 = 'ago';
		} elseif ($time >= strtotime('2013-06-05 11:00:00') && $time <= strtotime('2013-06-05 15:00:00')) {
			$img_path_0605 = 'now';
		} else {
			$img_path_0605 = 'after';
		}
		$img_path_0606 = 'ago';
		$img_path_0607 = 'ago';
		$img_path_0608 = 'ago';
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0606') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		$img_path_0604 = 'after';
		$img_path_0605 = 'after';
		if ($time < strtotime('2013-06-06 11:00:00')) {
			$img_path_0606 = 'ago';
		} elseif ($time >= strtotime('2013-06-06 11:00:00') && $time <= strtotime('2013-06-06 15:00:00')) {
			$img_path_0606 = 'now';
		} else {
			$img_path_0606 = 'after';
		}
		$img_path_0607 = 'ago';
		$img_path_0608 = 'ago';
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0607') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		$img_path_0604 = 'after';
		$img_path_0605 = 'after';
		$img_path_0606 = 'after';
		if ($time < strtotime('2013-06-07 11:00:00')) {
			$img_path_0607 = 'ago';
		} elseif ($time >= strtotime('2013-06-07 11:00:00') && $time <= strtotime('2013-06-07 15:00:00')) {
			$img_path_0607 = 'now';
		} else {
			$img_path_0607 = 'after';
		}
		$img_path_0608 = 'ago';
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0608') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_ago/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		$img_path_0604 = 'after';
		$img_path_0605 = 'after';
		$img_path_0606 = 'after';
		$img_path_0607 = 'after';
		if ($time < strtotime('2013-06-08 11:00:00')) {
			$img_path_0608 = 'ago';
		} elseif ($time >= strtotime('2013-06-08 11:00:00') && $time <= strtotime('2013-06-08 15:00:00')) {
			$img_path_0608 = 'now';
		} else {
			$img_path_0608 = 'after';
		}
		$img_path_0609 = 'ago';
		
	} elseif ($t == '0609') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_now/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		$img_path_0604 = 'after';
		$img_path_0605 = 'after';
		$img_path_0606 = 'after';
		$img_path_0607 = 'after';
		$img_path_0608 = 'after';
		if ($time < strtotime('2013-06-09 11:00:00')) {
			$img_path_0609 = 'ago';
		} elseif ($time >= strtotime('2013-06-09 11:00:00') && $time <= strtotime('2013-06-09 15:00:00')) {
			$img_path_0609 = 'now';
		} else {
			$img_path_0609 = 'after';
		}
		
	} elseif ($t > '0609') {
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0603.jpg" width="141" height="86" title="点击查看6月3日秒杀" class="hand" id="time1" onclick="setTab(1, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0604.jpg" width="142" height="86" title="点击查看6月4日秒杀" class="hand" id="time2" onclick="setTab(2, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0605.jpg" width="141" height="86" title="点击查看6月5日秒杀" class="hand" id="time3" onclick="setTab(3, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0606.jpg" width="142" height="86" title="点击查看6月6日秒杀" class="hand" id="time4" onclick="setTab(4, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0607.jpg" width="141" height="86" title="点击查看6月7日秒杀" class="hand" id="time5" onclick="setTab(5, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0608.jpg" width="142" height="86" title="点击查看6月8日秒杀" class="hand" id="time6" onclick="setTab(6, this, this.src)" /></li>';
		$ms_str .= '<li><img src="themes/default/images/active/20130603/time_after/0609.jpg" width="141" height="86" title="点击查看6月9日秒杀" class="hand" id="time7" onclick="setTab(7, this, this.src)" /></li>';
		
		$img_path_0603 = 'after';
		$img_path_0604 = 'after';
	
		$img_path_0605 = 'after';
		$img_path_0606 = 'after';
		$img_path_0607 = 'after';
		$img_path_0608 = 'after';
		$img_path_0609 = 'after';
		
	}
	$smarty->assign('ms_str',	$ms_str);
	$smarty->assign('t',	$t);
	$smarty->assign('img_path_0603',	$img_path_0603);
	$smarty->assign('img_path_0604',	$img_path_0604);
	$smarty->assign('img_path_0605',	$img_path_0605);
	$smarty->assign('img_path_0606',	$img_path_0606);
	$smarty->assign('img_path_0607',	$img_path_0607);
	$smarty->assign('img_path_0608',	$img_path_0608);
	$smarty->assign('img_path_0609',	$img_path_0609);
}
elseif($pid == 130528)
{
	//yi:六一儿童节活动
	$mb_ck    = 0;
	$email_ck = 0;
	if($user_id > 0)
	{
		$user_info = $GLOBALS['db']->GetRow("select * from ecs_users where user_id=".$user_id." limit 1;");
		$mb_ck     = intval($user_info['mobile_ck']);
		$email_ck  = intval($user_info['email_ck']);
		$smarty->assign('user_info',    $user_info);

		//邮箱跳转
		$email = strtolower(trim($user_info['email']));
		if(!empty($email))
		{
			$emarr = explode("@", $email);
			$to_url= "http://mail.".$emarr[1];
			//print_r($to_url);
			$smarty->assign('to_url',    $to_url);
		}
		
	}
	$smarty->assign('mb_ck',    $mb_ck);
	$smarty->assign('email_ck', $email_ck);
}
elseif($pid == 121212)
{
	//xyz:双12活动页面商品库存控制=========================================================================================//
	$goods_array = array(1302,1308,1307,1309,1346,1310);
	$goods_number_array = array();
	if (date('G') < 15 ) {
		$b_time = mktime(10, 0, 0, date("m"), date("d"), date("Y"));
		$e_time = mktime(15, 0, 0, date("m"), date("d"), date("Y"));
	} else {
		$b_time = mktime(15, 0, 0, date("m"), date("d"), date("Y"));
		$e_time = mktime(18, 0, 0, date("m"), date("d"), date("Y"));
	}

	//时间段内的订单ID
	$u_order = $GLOBALS['db']->GetAll("SELECT order_id FROM ecs_order_info WHERE order_status <> 2 AND add_time > " .$b_time. " AND add_time < " .$e_time);
	foreach ($goods_array as $v) {
		//商品表中的库存数量
		$all_num = $GLOBALS['db']->GetOne("SELECT goods_number FROM ecs_goods WHERE goods_id=".$v);
		
		//购物车商品数量
		$cart_number = 0;
		$c_num = $GLOBALS['db']->GetOne("select SUM(goods_number) from ecs_cart where goods_id=".$v);
		if ($c_num) $cart_number = $c_num;
		
		//订单表中的数量
		$goods_number = 0;
		if($u_order)
		{
			foreach($u_order as $k => $value)
			{
				$sql = "SELECT SUM(goods_number) FROM ecs_order_goods WHERE order_id=".$value['order_id']." AND goods_id=".$v;
				$g_num = $GLOBALS['db']->GetOne($sql);
				if ($g_num) $goods_number += $g_num;
			}
		}	
		$goods_number_array[] = $all_num - $goods_number - $cart_number;
	}
	$smarty->assign('goods_number_array',	$goods_number_array);
	//=======================================================================================================================//
}
elseif(130307 == $pid)
{
	$pz_arr = array('【一等奖】免单大奖', '【二等奖】伊厶康美瞳一副','【三等奖】10元彩色隐形眼镜现金红包','【四等奖】5元全场通用现金红包','【五等奖】易视网1000消费积分','【特别奖】易视网100消费积分');
	$sql = "select user_name, prize_rank, extension_id from ecs_prize where refer='bocomm_chou' and extension_id<>'' order by rec_id desc;";
	$prize = $GLOBALS['db']->GetAll($sql);
	foreach($prize as $k => $v)
	{
		$prize[$k]['prize_name'] = trim($pz_arr[$v['prize_rank']]);
	}
	$smarty->assign('prize_list',	$prize);
}
//yi:公布交行抽奖活动名单
elseif(130418 == $pid)
{
	$pz3 = array(
		array('ceshi45646545',	'20130321***17'), 
		array('家有贝贝小猪',	'20130326***28'),
		array('weiyu821106',	'20130326***43'), 
		array('ChristineWen',	'20130326***42'), 
		array('lanlan1007',		'20130328***33'), 
		array('jkday2',			'20130328***76') 
	);
	$pz4 = array(
		array('lixin0714',		'20130326***47'), 
		array('jieling8366',	'20130327***29'),
		array('caisenyang',		'20130411***29')
	);
	$pz5 = array(
		array('ceshi45646545',	'20130321***75'), 
		array('jefftan',		'20130326***47'),
		array('地顿时主',		'20130402***78'), 
		array('skyingyoung',	'20130403***64') 
	);
	$pz6 = array(
		array('zhangyanghust',	'20130326***79'), 
		array('ducy7',			'20130326***96'),
		array('四吖子',			'20130327***53'), 
		array('skysand123',		'20130327***53'), 
		array('kikitannn',		'20130403***85'), 
		array('michelle_xsc',	'20130405***55'),
		array('面包12号',		'20130410***55'), 
		array('buxiejing',		'20130417***77')
	);
	$prize3 = array_merge($pz3, get_prize_info(600, 4, 2));
	$prize4 = array_merge($pz4, get_prize_info(610, 7, 2));
	$prize5 = array_merge($pz5, get_prize_info(620, 6, 2));
	$prize6 = array_merge($pz6, get_prize_info(630, 2, 2));
	$smarty->assign('prize1',	get_prize_info(590, 3, 2));
	$smarty->assign('prize2',	get_prize_info(19));
	$smarty->assign('prize3',	$prize3);
	$smarty->assign('prize32',	get_prize_info(20));

	$smarty->assign('prize4',	$prize4);
	$smarty->assign('prize42',	get_prize_info(2));
	$smarty->assign('prize43',	get_prize_info(3));
	$smarty->assign('prize44',	get_prize_info(4));
	$smarty->assign('prize45',	get_prize_info(5));

	$smarty->assign('prize5',	$prize5);
	$smarty->assign('prize52',	get_prize_info(7));
	$smarty->assign('prize53',	get_prize_info(8));
	$smarty->assign('prize54',	get_prize_info(9));
	$smarty->assign('prize55',	get_prize_info(10));
	$smarty->assign('prize56',	get_prize_info(11));
	$smarty->assign('prize57',	get_prize_info(12));
	$smarty->assign('prize58',	get_prize_info(13));

	$smarty->assign('prize6',	$prize6);
	$smarty->assign('prize62',	get_prize_info(15));
	$smarty->assign('prize63',	get_prize_info(16));
	$smarty->assign('prize64',	get_prize_info(17));
	$smarty->assign('prize65',	get_prize_info(18));
}

//3周年庆活动页面
elseif(130729 == $pid)
{
	date_default_timezone_set('PRC');
	$now = time();

	//判断当前时间是秒杀第几波
	$ms_wave = 1;
	$str_ms1 = ''; //秒杀第一波在不同时间段显示的内容
	if ($now < strtotime('2013-08-04 00:00:00')) {
		$ms_wave = 1;
	} elseif ($now >= strtotime('2013-08-04 00:00:00') && $now <= strtotime('2013-08-06 23:59:59')) {
		$ms_wave = 2;
	} elseif ($now >= strtotime('2013-08-07 00:00:00') && $now <= strtotime('2013-08-09 23:59:59')) {
		$ms_wave = 3;
	} else {
		$ms_wave = 3;
	}
	
	//秒杀第1波在不同时间段显示的内容
	$str_ms1 = '';
	if ($now < strtotime('2013-08-04 00:00:00')) 
	{
		if (date('G') < 11)
		{
			$str_ms1 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_future_10.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_future_11.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_future_12.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_future_14.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_future_15.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_future_16.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
		elseif (date('G') >= 11 && date('G') < 13)
		{
			$str_ms1 = '<span class="float_span"><a href="goods2636.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/ms_now_10.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2637.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/ms_now_11.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2638.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/ms_now_12.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2639.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/ms_now_14.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2640.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/ms_now_15.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2641.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/ms_now_16.jpg" title="" alt="" /></a></span>';
		}
		else
		{
			$str_ms1 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_next_10.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_next_11.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_next_12.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_next_14.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_next_15.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_next_16.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
	}
	else 
	{
		$str_ms1 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_over_10.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_over_11.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_over_12.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_over_14.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_over_15.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/ms_over_16.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	$smarty->assign('str_ms1',	$str_ms1);
	
	//秒杀第2波在不同时间段显示的内容
	$str_ms2 = '';
	if ($now >= strtotime('2013-08-04 00:00:00') && $now <= strtotime('2013-08-06 23:59:59')) 
	{
		//if (date('G') < 11)
		if (time() < strtotime('2013-08-'.date('d').' 10:54:00'))
		{
			$str_ms2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_10.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_11.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_12.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_14.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_15.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_16.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
		//elseif (date('G') >= 11 && date('G') < 13)
		elseif (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
		{
			$str_ms2 = '<span class="float_span"><a href="goods2643.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/ms_now_10.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2644.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/ms_now_11.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2648.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/ms_now_12.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2645.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/ms_now_14.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2647.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/ms_now_15.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2665.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/ms_now_16.jpg" title="" alt="" /></a></span>';
		}
		else
		{
			$str_ms2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_next_10.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_next_11.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_next_12.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_next_14.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_next_15.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_next_16.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
	}
	elseif ($now < strtotime('2013-08-04 00:00:00'))
	{
		$str_ms2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_10.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_11.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_12.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_14.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_15.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_future_16.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	else 
	{
		$str_ms2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_over_10.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_over_11.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_over_12.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_over_14.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_over_15.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/ms_over_16.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	$smarty->assign('str_ms2',	$str_ms2);
	
	//秒杀第3波在不同时间段显示的内容
	$str_ms3 = '';
	if ($now >= strtotime('2013-08-07 00:00:00') && $now <= strtotime('2013-08-09 13:59:59')) 
	{
		//if (date('G') < 11)
		if (time() < strtotime('2013-08-'.date('d').' 10:54:00'))
		{
			$str_ms3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_10.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_11.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_12.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_14.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_15.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_16.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
		//elseif (date('G') >= 11 && date('G') < 13)
		elseif (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
		{
			$str_ms3 = '<span class="float_span"><a href="goods2650.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/ms_now_10.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2651.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/ms_now_11.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2652.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/ms_now_12.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2653.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/ms_now_14.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2662.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/ms_now_15.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="goods2664.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/ms_now_16.jpg" title="" alt="" /></a></span>';
		}
		else
		{
			$str_ms3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_next_10.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_next_11.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_next_12.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_next_14.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_next_15.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_next_16.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
	}
	elseif ($now < strtotime('2013-08-07 00:00:00'))
	{
		$str_ms3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_10.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_11.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_12.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_14.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_15.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_future_16.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	else 
	{
		$str_ms3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_over_10.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_over_11.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_over_12.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_over_14.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_over_15.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/ms_over_16.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	$smarty->assign('str_ms3',	$str_ms3);
	
	
	//判断当前时间是积分换购第几波
	$jf_wave = 1;
	if ($now < strtotime('2013-08-13 00:00:00')) {
		$jf_wave = 1;
	} elseif ($now >= strtotime('2013-08-13 00:00:00') && $now <= strtotime('2013-08-16 23:59:59')) {
		$jf_wave = 2;
	} elseif ($now >= strtotime('2013-08-17 00:00:00') && $now <= strtotime('2013-08-19 23:59:59')) {
		$jf_wave = 3;
	} else {
		$jf_wave = 3;
	}
	
	//积分换购第1波在不同时间段显示的内容
	$str_jf1 = '';
	if ($now < strtotime('2013-08-13 00:00:00')) 
	{
		if (time() < strtotime('2013-08-'.date('d').' 10:54:00'))
		{
			$str_jf1 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_future_23.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_future_24.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_future_25.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
		elseif (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
		{
			$str_jf1 = '<span class="float_span"><a href="exchange2655.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/jf_now_23.jpg" class="not_lazyload" title="" alt="" /></a></span>
						<span class="float_span"><a href="exchange2368.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/jf_now_24.jpg" class="not_lazyload" title="" alt="" /></a></span>
						<span class="float_span"><a href="exchange2656.html" target="_blank"><img src="themes/default/images/active/20130729/wave1/jf_now_25.jpg" class="not_lazyload" title="" alt="" /></a></span>';
		}
		else
		{
			$str_jf1 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_next_23.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_next_24.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_next_25.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
	}
	else 
	{
		$str_jf1 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_over_23.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_over_24.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave1/jf_over_25.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	$smarty->assign('str_jf1',	$str_jf1);
	
	//积分换购第2波在不同时间段显示的内容
	$str_jf2 = '';
	if ($now >= strtotime('2013-08-13 00:00:00') && $now <= strtotime('2013-08-16 23:59:59')) 
	{
		if (time() < strtotime('2013-08-'.date('d').' 10:54:00'))
		{
			$str_jf2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_future_23.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_future_24.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_future_25.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
		elseif (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
		{
			$str_jf2 = '<span class="float_span"><a href="exchange2657.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/jf_now_23.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="exchange2658.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/jf_now_24.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="exchange2311.html" target="_blank"><img src="themes/default/images/active/20130729/wave2/jf_now_25.jpg" title="" alt="" /></a></span>';
		}
		else
		{
			$str_jf2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_next_23.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_next_24.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_next_25.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
	}
	elseif ($now < strtotime('2013-08-13 00:00:00'))
	{
		$str_jf2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_future_23.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_future_24.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_future_25.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	else 
	{
		$str_jf2 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_over_23.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_over_24.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave2/jf_over_25.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	$smarty->assign('str_jf2',	$str_jf2);
	
	//积分换购第3波在不同时间段显示的内容
	$str_jf3 = '';
	if ($now >= strtotime('2013-08-17 00:00:00') && $now <= strtotime('2013-08-19 13:00:00')) 
	{
		if (time() < strtotime('2013-08-'.date('d').' 10:54:00'))
		{
			$str_jf3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_future_23.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_future_24.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_future_25.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
		elseif (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
		{
			$str_jf3 = '<span class="float_span"><a href="exchange2659.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/jf_now_23.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="exchange2660.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/jf_now_24.jpg" title="" alt="" /></a></span>
						<span class="float_span"><a href="exchange2661.html" target="_blank"><img src="themes/default/images/active/20130729/wave3/jf_now_25.jpg" title="" alt="" /></a></span>';
		}
		else
		{
			$str_jf3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_next_23.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_next_24.jpg" class="not_lazyload" title="" alt="" /></span>
						<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_next_25.jpg" class="not_lazyload" title="" alt="" /></span>';
		}
	}
	elseif ($now < strtotime('2013-08-17 00:00:00'))
	{
		$str_jf3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_future_23.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_future_24.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_future_25.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	else 
	{
		$str_jf3 = '<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_over_23.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_over_24.jpg" class="not_lazyload" title="" alt="" /></span>
					<span class="float_span"><img src="themes/default/images/active/20130729/wave3/jf_over_25.jpg" class="not_lazyload" title="" alt="" /></span>';
	}
	$smarty->assign('str_jf3',	$str_jf3);
	
	
	if($_SESSION['user_id'] > 0) {
		$smarty->assign('user_id',	$_SESSION['user_id']);
	}
	$smarty->assign('ms_wave', $ms_wave); 
	$smarty->assign('jf_wave', $jf_wave);
	
	//获取用户绑定的应用
	/*$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
	$sync = $GLOBALS['db']->getAll($sql);
	$user_sync = array();
	if ($sync) {
		foreach ($sync as $k => $v) {
			$user_sync[$v['app_name']]['sid'] = $v['sid'];
			$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
			$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
			$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
			$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
			$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
			$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
		}
	}
	$qq_sync = $user_sync['qq'];
	$sina_sync = $user_sync['sina'];
	//$smarty->assign('user_sync',	$user_sync);
	$smarty->assign('qq_sync',		$qq_sync);
	$smarty->assign('sina_sync',	$sina_sync);*/
}

//双旦主页面
elseif(131220 == $pid)
{
	if (date('G') == 10)
	{
		$ms_bg = "themes/default/images/active/20131220/ms_a2.jpg";
		$ms_step = 1;// 上午开始 b1
	}
	elseif (date('G') == 14)
	{
		$ms_bg = "themes/default/images/active/20131220/ms_b2.jpg";
		$ms_step = 3;//下午开始 b7
	}
	elseif (date('G') < 10)
	{
		$ms_bg = "themes/default/images/active/20131220/ms_a1.jpg";
		$ms_step = 0;//上午未开始 a1
	}
	elseif (date('G') > 10 && date('G') < 14)
	{
		$ms_bg = "themes/default/images/active/20131220/ms_b1.jpg";
		$ms_step = 2;//下午未开始 a7
	}
	else
	{
		$ms_bg = "themes/default/images/active/20131220/ms_b1.jpg";
		$ms_step = 4; //结束 c7
	}
	
	$today = date('Ymd', time());
	if ($today < '20131220') $today = '20131220';
	if ($today > '20140102') $today = '20140102';
	
	$smarty->assign('today',	$today);
	$smarty->assign('ms_bg',	$ms_bg);
	$smarty->assign('ms_step',	$ms_step);
}

//140520随心配
elseif(140520 == $pid)
{
	/*$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';
	
	if ($act == 'add_to_cart') {
		if($num == 149){
			$cart149_number = isset($_REQUEST['cart149_number'])? $_REQUEST['cart149_number']: '0';
		
			$cart149_goods1 = isset($_REQUEST['cart149_goods1'])? $_REQUEST['cart149_goods1']: '0';
			$cart149_goods2 = isset($_REQUEST['cart149_goods2'])? $_REQUEST['cart149_goods2']: '0';
			$cart149_goods1_zselect = isset($_REQUEST['cart149_goods1_zselect'])? $_REQUEST['cart149_goods1_zselect']: '';
			$cart149_goods1_yselect = isset($_REQUEST['cart149_goods1_yselect'])? $_REQUEST['cart149_goods1_yselect']: '';
			$cart149_goods2_zselect = isset($_REQUEST['cart149_goods2_zselect'])? $_REQUEST['cart149_goods2_zselect']: '';
			$cart149_goods2_yselect = isset($_REQUEST['cart149_goods2_yselect'])? $_REQUEST['cart149_goods2_yselect']: '';
			
			
			$total_price_149 = 149.00;	//随心配的总价 是固定的
			$package_id_149 = 160;		//礼包ID 是固定的
			
			if ($cart149_number) 
			{
				if ($cart149_goods1 && $cart149_goods2) 
				{
					$g_1 = get_goods_info($cart149_goods1);
					$g_2 = get_goods_info($cart149_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '1', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '".$cart149_goods1_zselect.','.$cart149_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart149_goods2_zselect.','.$cart149_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '彩色片加入购物车!';
				}
				
				
			}
			
			exit;
		}
		
		if($num == 199){
			$cart199_number = isset($_REQUEST['cart199_number'])? $_REQUEST['cart199_number']: '0';
		
			$cart199_goods1 = isset($_REQUEST['cart199_goods1'])? $_REQUEST['cart199_goods1']: '0';
			$cart199_goods2 = isset($_REQUEST['cart199_goods2'])? $_REQUEST['cart199_goods2']: '0';
			$cart199_goods1_zselect = isset($_REQUEST['cart199_goods1_zselect'])? $_REQUEST['cart199_goods1_zselect']: '';
			$cart199_goods1_yselect = isset($_REQUEST['cart199_goods1_yselect'])? $_REQUEST['cart199_goods1_yselect']: '';
			$cart199_goods2_zselect = isset($_REQUEST['cart199_goods2_zselect'])? $_REQUEST['cart199_goods2_zselect']: '';
			$cart199_goods2_yselect = isset($_REQUEST['cart199_goods2_yselect'])? $_REQUEST['cart199_goods2_yselect']: '';
			
			
			$total_price_199 = 149.00;	//随心配的总价 是固定的
			$package_id_199 = 161;		//礼包ID 是固定的
			
			if ($cart199_number) 
			{
				if ($cart199_goods1 && $cart199_goods2) 
				{
					$g_1 = get_goods_info($cart199_goods1);
					$g_2 = get_goods_info($cart199_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods1."', '1', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_199."', '1', '".$cart199_goods1_zselect.','.$cart199_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods2."', '', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart199_goods2_zselect.','.$cart199_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '透明片加入购物车!';
				}
				
				
			}
			
			exit;
		}
	}*/
	
	$goods_1081   = get_goods_info(1081);
	$goods_1079   = get_goods_info(1079);
	$goods_1080    = get_goods_info(1080);
	$goods_896   = get_goods_info(896);
	$goods_898    = get_goods_info(898);
	$goods_3641   = get_goods_info(3641);
	$goods_3640   = get_goods_info(3640);
	$goods_3636    = get_goods_info(3636);
	$goods_2928   = get_goods_info(2928);
	$goods_2929   = get_goods_info(2929);
	$goods_2859   = get_goods_info(2859);
	$goods_2862   = get_goods_info(2862);
	$goods_2410   = get_goods_info(2410);
	$goods_2409   = get_goods_info(2409);
	$goods_2408   = get_goods_info(2408);
	$goods_2411   = get_goods_info(2411);
	$goods_3288   = get_goods_info(3288);
	$goods_3287   = get_goods_info(3287);
	$goods_986   = get_goods_info(986);
	$goods_984   = get_goods_info(984);
	$goods_157   = get_goods_info(157);
	$goods_1144   = get_goods_info(1144);
	$goods_1476    = get_goods_info(1476);
	$goods_978   = get_goods_info(978);
	$goods_1150    = get_goods_info(1150);
	$goods_952   = get_goods_info(952);
	$goods_3035    = get_goods_info(3035 );
	$goods_2593    = get_goods_info(2593);
	
	$goodsds_1081   = get_goods_ds(1081);
	$goodsds_1079   = get_goods_ds(1079);
	$goodsds_1080    = get_goods_ds(1080);
	$goodsds_896   = get_goods_ds(896);
	$goodsds_898    = get_goods_ds(898);
	$goodsds_3641   = get_goods_ds(3641);
	$goodsds_3640   = get_goods_ds(3640);
	$goodsds_3636    = get_goods_ds(3636);
	$goodsds_2928   = get_goods_ds(2928);
	$goodsds_2929   = get_goods_ds(2929);
	$goodsds_2859   = get_goods_ds(2859);
	$goodsds_2862   = get_goods_ds(2862);
	$goodsds_2410   = get_goods_ds(2410);
	$goodsds_2409   = get_goods_ds(2409);
	$goodsds_2408   = get_goods_ds(2408);
	$goodsds_2411   = get_goods_ds(2411);
	$goodsds_3288   = get_goods_ds(3288);
	$goodsds_3287   = get_goods_ds(3287);
	$goodsds_986   = get_goods_ds(986);
	$goodsds_984   = get_goods_ds(984);
	$goodsds_157   = get_goods_ds(157);
	$goodsds_1144   = get_goods_ds(1144);
	$goodsds_1476    = get_goods_ds(1476);
	$goodsds_978   = get_goods_ds(978);
	$goodsds_1150    = get_goods_ds(1150);
	$goodsds_952   = get_goods_ds(952);
	$goodsds_3035    = get_goods_ds(3035 );
	$goodsds_2593    = get_goods_ds(2593);
	
	$smarty->assign('goods_1081', $goods_1081);
	$smarty->assign('goods_1079', $goods_1079);
	$smarty->assign('goods_1080', $goods_1080);
	$smarty->assign('goods_896', $goods_896);
	$smarty->assign('goods_898', $goods_898);
	$smarty->assign('goods_3641', $goods_3641);
	$smarty->assign('goods_3640', $goods_3640);
	$smarty->assign('goods_3636', $goods_3636);
	$smarty->assign('goods_2928', $goods_2928);
	$smarty->assign('goods_2929', $goods_2929);
	$smarty->assign('goods_2859', $goods_2859);
	$smarty->assign('goods_2862', $goods_2862);
	$smarty->assign('goods_2410', $goods_2410);
	$smarty->assign('goods_2409', $goods_2409);
	$smarty->assign('goods_2408', $goods_2408);
	$smarty->assign('goods_2411', $goods_2411);
	$smarty->assign('goods_3288', $goods_3288);
	$smarty->assign('goods_3287', $goods_3287);
	$smarty->assign('goods_986', $goods_986);
	$smarty->assign('goods_984', $goods_984);
	$smarty->assign('goods_157', $goods_157);
	$smarty->assign('goods_1144', $goods_1144);
	$smarty->assign('goods_1476', $goods_1476);
	$smarty->assign('goods_978', $goods_978);
	$smarty->assign('goods_1150', $goods_1150);
	$smarty->assign('goods_952', $goods_952);
	$smarty->assign('goods_3035', $goods_3035);
	$smarty->assign('goods_2593', $goods_2593);
	
	$smarty->assign('goodsds_1081', $goodsds_1081);
	$smarty->assign('goodsds_1079', $goodsds_1079);
	$smarty->assign('goodsds_1080', $goodsds_1080);
	$smarty->assign('goodsds_896', $goodsds_896);
	$smarty->assign('goodsds_898', $goodsds_898);
	$smarty->assign('goodsds_3641', $goodsds_3641);
	$smarty->assign('goodsds_3640', $goodsds_3640);
	$smarty->assign('goodsds_3636', $goodsds_3636);
	$smarty->assign('goodsds_2928', $goodsds_2928);
	$smarty->assign('goodsds_2929', $goodsds_2929);
	$smarty->assign('goodsds_2859', $goodsds_2859);
	$smarty->assign('goodsds_2862', $goodsds_2862);
	$smarty->assign('goodsds_2410', $goodsds_2410);
	$smarty->assign('goodsds_2409', $goodsds_2409);
	$smarty->assign('goodsds_2408', $goodsds_2408);
	$smarty->assign('goodsds_2411', $goodsds_2411);
	$smarty->assign('goodsds_3288', $goodsds_3288);
	$smarty->assign('goodsds_3287', $goodsds_3287);
	$smarty->assign('goodsds_986', $goodsds_986);
	$smarty->assign('goodsds_984', $goodsds_984);
	$smarty->assign('goodsds_157', $goodsds_157);
	$smarty->assign('goodsds_1144', $goodsds_1144);
	$smarty->assign('goodsds_1476', $goodsds_1476);
	$smarty->assign('goodsds_978', $goodsds_978);
	$smarty->assign('goodsds_1150', $goodsds_1150);
	$smarty->assign('goodsds_952', $goodsds_952);
	$smarty->assign('goodsds_3035', $goodsds_3035);
	$smarty->assign('goodsds_2593', $goodsds_2593);
}

//0728随心配
elseif(140728 == $pid)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';
	
	if ($act == 'add_to_cart') {
	if($num == 149){
			$cart149_number = isset($_REQUEST['cart149_number'])? $_REQUEST['cart149_number']: '0';
		
			$cart149_goods1 = isset($_REQUEST['cart149_goods1'])? $_REQUEST['cart149_goods1']: '0';
			$cart149_goods2 = isset($_REQUEST['cart149_goods2'])? $_REQUEST['cart149_goods2']: '0';
			$cart149_goods1_zselect = isset($_REQUEST['cart149_goods1_zselect'])? $_REQUEST['cart149_goods1_zselect']: '';
			$cart149_goods1_yselect = isset($_REQUEST['cart149_goods1_yselect'])? $_REQUEST['cart149_goods1_yselect']: '';
			$cart149_goods2_zselect = isset($_REQUEST['cart149_goods2_zselect'])? $_REQUEST['cart149_goods2_zselect']: '';
			$cart149_goods2_yselect = isset($_REQUEST['cart149_goods2_yselect'])? $_REQUEST['cart149_goods2_yselect']: '';
			
			
			$total_price_149 = 77.00;	//随心配的总价 是固定的
			
			if ($cart149_number) 
			{
				if ($cart149_goods1 && $cart149_goods2) 
				{
					$g_1 = get_goods_info($cart149_goods1);
					$g_2 = get_goods_info($cart149_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '1', '[77元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '".$cart149_goods1_zselect.','.$cart149_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '', '[77元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart149_goods2_zselect.','.$cart149_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '透明片加入购物车!';
				}
				
				
			}
			
			exit;
		}
		
		if($num == 199){
			$cart199_number = isset($_REQUEST['cart199_number'])? $_REQUEST['cart199_number']: '0';
		
			$cart199_goods1 = isset($_REQUEST['cart199_goods1'])? $_REQUEST['cart199_goods1']: '0';
			$cart199_goods2 = isset($_REQUEST['cart199_goods2'])? $_REQUEST['cart199_goods2']: '0';
			$cart199_goods1_zselect = isset($_REQUEST['cart199_goods1_zselect'])? $_REQUEST['cart199_goods1_zselect']: '';
			$cart199_goods1_yselect = isset($_REQUEST['cart199_goods1_yselect'])? $_REQUEST['cart199_goods1_yselect']: '';
			$cart199_goods2_zselect = isset($_REQUEST['cart199_goods2_zselect'])? $_REQUEST['cart199_goods2_zselect']: '';
			$cart199_goods2_yselect = isset($_REQUEST['cart199_goods2_yselect'])? $_REQUEST['cart199_goods2_yselect']: '';
			
			
			$total_price_199 = 77.00;	//随心配的总价 是固定的
			
			if ($cart199_number) 
			{
				if ($cart199_goods1 && $cart199_goods2) 
				{
					$g_1 = get_goods_info($cart199_goods1);
					$g_2 = get_goods_info($cart199_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods1."', '1', '[77元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_199."', '1', '".$cart199_goods1_zselect.','.$cart199_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods2."', '', '[77元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart199_goods2_zselect.','.$cart199_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '彩片加入购物车!';
				}
				
				
			}
			
			exit;
		}
	}
	
	$goods_3036   = get_goods_info(3036);
	$goods_3034   = get_goods_info(3034);
	$goods_158   = get_goods_info(158);
	$goods_152   = get_goods_info(152);
	$goods_150   = get_goods_info(150);
	$goods_951   = get_goods_info(951);
	$goods_126   = get_goods_info(126);
	$goods_175   = get_goods_info(175);
	$goods_1460   = get_goods_info(1460);
	$goods_1459   = get_goods_info(1459);
	$goods_1458   = get_goods_info(1458);
	$goods_1457   = get_goods_info(1457);
	$goods_1461   = get_goods_info(1461);
	$goods_2925   = get_goods_info(2925);
	$goods_2927   = get_goods_info(2927);
	$goods_3641   = get_goods_info(3641);
	$goods_3640   = get_goods_info(3640);
	$goods_3638   = get_goods_info(3638);
	$goods_3637   = get_goods_info(3637);
	$goods_3634   = get_goods_info(3634);
	$goods_3636   = get_goods_info(3636);
	$goods_3639   = get_goods_info(3639);
	$goods_3635   = get_goods_info(3635);
	$goods_990   = get_goods_info(990);
	$goods_992   = get_goods_info(992);
	$goods_991   = get_goods_info(991);
	$goods_989   = get_goods_info(989);
	$goods_988   = get_goods_info(988);
	
	$goodsds_3036   = get_goods_ds(3036);
	$goodsds_3034   = get_goods_ds(3034);
	$goodsds_158   = get_goods_ds(158);
	$goodsds_152   = get_goods_ds(152);
	$goodsds_150   = get_goods_ds(150);
	$goodsds_951   = get_goods_ds(951);
	$goodsds_126   = get_goods_ds(126);
	$goodsds_175   = get_goods_ds(175);
	$goodsds_1460   = get_goods_ds(1460);
	$goodsds_1459   = get_goods_ds(1459);
	$goodsds_1458   = get_goods_ds(1458);
	$goodsds_1457   = get_goods_ds(1457);
	$goodsds_1461   = get_goods_ds(1461);
	$goodsds_2925   = get_goods_ds(2925);
	$goodsds_2927   = get_goods_ds(2927);
	$goodsds_3641   = get_goods_ds(3641);
	$goodsds_3640   = get_goods_ds(3640);
	$goodsds_3638   = get_goods_ds(3638);
	$goodsds_3637   = get_goods_ds(3637);
	$goodsds_3634   = get_goods_ds(3634);
	$goodsds_3636   = get_goods_ds(3636);
	$goodsds_3639   = get_goods_ds(3639);
	$goodsds_3635   = get_goods_ds(3635);
	$goodsds_990   = get_goods_ds(990);
	$goodsds_992   = get_goods_ds(992);
	$goodsds_991   = get_goods_ds(991);
	$goodsds_989   = get_goods_ds(989);
	$goodsds_988   = get_goods_ds(988);
	
	$smarty->assign('goods_3036', $goods_3036);
	$smarty->assign('goods_3034', $goods_3034);
	$smarty->assign('goods_158', $goods_158);
	$smarty->assign('goods_152', $goods_152);
	$smarty->assign('goods_150', $goods_150);
	$smarty->assign('goods_951', $goods_951);
	$smarty->assign('goods_126', $goods_126);
	$smarty->assign('goods_175', $goods_175);
	$smarty->assign('goods_1460', $goods_1460);
	$smarty->assign('goods_1459', $goods_1459);
	$smarty->assign('goods_1458', $goods_1458);
	$smarty->assign('goods_1457', $goods_1457);
	$smarty->assign('goods_1461', $goods_1461);
	$smarty->assign('goods_2925', $goods_2925);
	$smarty->assign('goods_2927', $goods_2927);
	$smarty->assign('goods_3641', $goods_3641);
	$smarty->assign('goods_3640', $goods_3640);
	$smarty->assign('goods_3638', $goods_3638);
	$smarty->assign('goods_3637', $goods_3637);
	$smarty->assign('goods_3634', $goods_3634);
	$smarty->assign('goods_3636', $goods_3636);
	$smarty->assign('goods_3639', $goods_3639);
	$smarty->assign('goods_3635', $goods_3635);
	$smarty->assign('goods_990', $goods_990);
	$smarty->assign('goods_992', $goods_992);
	$smarty->assign('goods_991', $goods_991);
	$smarty->assign('goods_989', $goods_989);
	$smarty->assign('goods_988', $goods_988);
	
	$smarty->assign('goodsds_3036', $goodsds_3036);
	$smarty->assign('goodsds_3034', $goodsds_3034);
	$smarty->assign('goodsds_158', $goodsds_158);
	$smarty->assign('goodsds_152', $goodsds_152);
	$smarty->assign('goodsds_150', $goodsds_150);
	$smarty->assign('goodsds_951', $goodsds_951);
	$smarty->assign('goodsds_126', $goodsds_126);
	$smarty->assign('goodsds_175', $goodsds_175);
	$smarty->assign('goodsds_1460', $goodsds_1460);
	$smarty->assign('goodsds_1459', $goodsds_1459);
	$smarty->assign('goodsds_1458', $goodsds_1458);
	$smarty->assign('goodsds_1457', $goodsds_1457);
	$smarty->assign('goodsds_1461', $goodsds_1461);
	$smarty->assign('goodsds_2925', $goodsds_2925);
	$smarty->assign('goodsds_2927', $goodsds_2927);
	$smarty->assign('goodsds_3641', $goodsds_3641);
	$smarty->assign('goodsds_3640', $goodsds_3640);
	$smarty->assign('goodsds_3638', $goodsds_3638);
	$smarty->assign('goodsds_3637', $goodsds_3637);
	$smarty->assign('goodsds_3634', $goodsds_3634);
	$smarty->assign('goodsds_3636', $goodsds_3636);
	$smarty->assign('goodsds_3639', $goodsds_3639);
	$smarty->assign('goodsds_3635', $goodsds_3635);
	$smarty->assign('goodsds_990', $goodsds_990);
	$smarty->assign('goodsds_992', $goodsds_992);
	$smarty->assign('goodsds_991', $goodsds_991);
	$smarty->assign('goodsds_989', $goodsds_989);
	$smarty->assign('goodsds_988', $goodsds_988);
}

//市场活动通用页面，复制140520功能
elseif(140601 == $pid)
{
	$goods_3036   = get_goods_info(3036);
	$goods_3034   = get_goods_info(3034);
	$goods_158   = get_goods_info(158);
	$goods_152   = get_goods_info(152);
	$goods_150   = get_goods_info(150);
	$goods_951   = get_goods_info(951);
	$goods_126   = get_goods_info(126);
	$goods_175   = get_goods_info(175);
	$goods_1460   = get_goods_info(1460);
	$goods_1459   = get_goods_info(1459);
	$goods_1458   = get_goods_info(1458);
	$goods_1457   = get_goods_info(1457);
	$goods_1461   = get_goods_info(1461);
	$goods_2925   = get_goods_info(2925);
	$goods_2927   = get_goods_info(2927);
	$goods_3641   = get_goods_info(3641);
	$goods_3640   = get_goods_info(3640);
	$goods_3638   = get_goods_info(3638);
	$goods_3637   = get_goods_info(3637);
	$goods_3634   = get_goods_info(3634);
	$goods_3636   = get_goods_info(3636);
	$goods_3639   = get_goods_info(3639);
	$goods_3635   = get_goods_info(3635);
	$goods_990   = get_goods_info(990);
	$goods_992   = get_goods_info(992);
	$goods_991   = get_goods_info(991);
	$goods_989   = get_goods_info(989);
	$goods_988   = get_goods_info(988);
	
	$goodsds_3036   = get_goods_ds(3036);
	$goodsds_3034   = get_goods_ds(3034);
	$goodsds_158   = get_goods_ds(158);
	$goodsds_152   = get_goods_ds(152);
	$goodsds_150   = get_goods_ds(150);
	$goodsds_951   = get_goods_ds(951);
	$goodsds_126   = get_goods_ds(126);
	$goodsds_175   = get_goods_ds(175);
	$goodsds_1460   = get_goods_ds(1460);
	$goodsds_1459   = get_goods_ds(1459);
	$goodsds_1458   = get_goods_ds(1458);
	$goodsds_1457   = get_goods_ds(1457);
	$goodsds_1461   = get_goods_ds(1461);
	$goodsds_2925   = get_goods_ds(2925);
	$goodsds_2927   = get_goods_ds(2927);
	$goodsds_3641   = get_goods_ds(3641);
	$goodsds_3640   = get_goods_ds(3640);
	$goodsds_3638   = get_goods_ds(3638);
	$goodsds_3637   = get_goods_ds(3637);
	$goodsds_3634   = get_goods_ds(3634);
	$goodsds_3636   = get_goods_ds(3636);
	$goodsds_3639   = get_goods_ds(3639);
	$goodsds_3635   = get_goods_ds(3635);
	$goodsds_990   = get_goods_ds(990);
	$goodsds_992   = get_goods_ds(992);
	$goodsds_991   = get_goods_ds(991);
	$goodsds_989   = get_goods_ds(989);
	$goodsds_988   = get_goods_ds(988);
	
	$smarty->assign('goods_3036', $goods_3036);
	$smarty->assign('goods_3034', $goods_3034);
	$smarty->assign('goods_158', $goods_158);
	$smarty->assign('goods_152', $goods_152);
	$smarty->assign('goods_150', $goods_150);
	$smarty->assign('goods_951', $goods_951);
	$smarty->assign('goods_126', $goods_126);
	$smarty->assign('goods_175', $goods_175);
	$smarty->assign('goods_1460', $goods_1460);
	$smarty->assign('goods_1459', $goods_1459);
	$smarty->assign('goods_1458', $goods_1458);
	$smarty->assign('goods_1457', $goods_1457);
	$smarty->assign('goods_1461', $goods_1461);
	$smarty->assign('goods_2925', $goods_2925);
	$smarty->assign('goods_2927', $goods_2927);
	$smarty->assign('goods_3641', $goods_3641);
	$smarty->assign('goods_3640', $goods_3640);
	$smarty->assign('goods_3638', $goods_3638);
	$smarty->assign('goods_3637', $goods_3637);
	$smarty->assign('goods_3634', $goods_3634);
	$smarty->assign('goods_3636', $goods_3636);
	$smarty->assign('goods_3639', $goods_3639);
	$smarty->assign('goods_3635', $goods_3635);
	$smarty->assign('goods_990', $goods_990);
	$smarty->assign('goods_992', $goods_992);
	$smarty->assign('goods_991', $goods_991);
	$smarty->assign('goods_989', $goods_989);
	$smarty->assign('goods_988', $goods_988);
	
	$smarty->assign('goodsds_3036', $goodsds_3036);
	$smarty->assign('goodsds_3034', $goodsds_3034);
	$smarty->assign('goodsds_158', $goodsds_158);
	$smarty->assign('goodsds_152', $goodsds_152);
	$smarty->assign('goodsds_150', $goodsds_150);
	$smarty->assign('goodsds_951', $goodsds_951);
	$smarty->assign('goodsds_126', $goodsds_126);
	$smarty->assign('goodsds_175', $goodsds_175);
	$smarty->assign('goodsds_1460', $goodsds_1460);
	$smarty->assign('goodsds_1459', $goodsds_1459);
	$smarty->assign('goodsds_1458', $goodsds_1458);
	$smarty->assign('goodsds_1457', $goodsds_1457);
	$smarty->assign('goodsds_1461', $goodsds_1461);
	$smarty->assign('goodsds_2925', $goodsds_2925);
	$smarty->assign('goodsds_2927', $goodsds_2927);
	$smarty->assign('goodsds_3641', $goodsds_3641);
	$smarty->assign('goodsds_3640', $goodsds_3640);
	$smarty->assign('goodsds_3638', $goodsds_3638);
	$smarty->assign('goodsds_3637', $goodsds_3637);
	$smarty->assign('goodsds_3634', $goodsds_3634);
	$smarty->assign('goodsds_3636', $goodsds_3636);
	$smarty->assign('goodsds_3639', $goodsds_3639);
	$smarty->assign('goodsds_3635', $goodsds_3635);
	$smarty->assign('goodsds_990', $goodsds_990);
	$smarty->assign('goodsds_992', $goodsds_992);
	$smarty->assign('goodsds_991', $goodsds_991);
	$smarty->assign('goodsds_989', $goodsds_989);
	$smarty->assign('goodsds_988', $goodsds_988);
}

//元宵撞上情人节 EYE上约惠
elseif(140128 == $pid){
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';

	if ($act == 'add_to_cart') {
		if($num == 149){
			$cart149_number = isset($_REQUEST['cart149_number'])? $_REQUEST['cart149_number']: '0';
		
			$cart149_goods1 = isset($_REQUEST['cart149_goods1'])? $_REQUEST['cart149_goods1']: '0';
			$cart149_goods2 = isset($_REQUEST['cart149_goods2'])? $_REQUEST['cart149_goods2']: '0';
			$cart149_goods1_zselect = isset($_REQUEST['cart149_goods1_zselect'])? $_REQUEST['cart149_goods1_zselect']: '';
			$cart149_goods1_yselect = isset($_REQUEST['cart149_goods1_yselect'])? $_REQUEST['cart149_goods1_yselect']: '';
			$cart149_goods2_zselect = isset($_REQUEST['cart149_goods2_zselect'])? $_REQUEST['cart149_goods2_zselect']: '';
			$cart149_goods2_yselect = isset($_REQUEST['cart149_goods2_yselect'])? $_REQUEST['cart149_goods2_yselect']: '';
			
			
			$total_price_149 = 149.00;	//随心配的总价 是固定的
			$package_id_149 = 113;		//礼包ID 是固定的
			
			if ($cart149_number) 
			{
				if ($cart149_goods1 && $cart149_goods2) 
				{
					$g_1 = get_goods_info($cart149_goods1);
					$g_2 = get_goods_info($cart149_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '1', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '".$cart149_goods1_zselect.','.$cart149_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart149_goods2_zselect.','.$cart149_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '149元区加入购物车!';
				}
				
				
			}
			
			exit;
		}
	}
	
	$goods_896   = get_goods_info(896);
	$goods_898   = get_goods_info(898);
	$goods_895    = get_goods_info(895);
	$goods_897   = get_goods_info(897);
	$goods_1271    = get_goods_info(1271);
	$goods_1270   = get_goods_info(1270);
	$goods_1228   = get_goods_info(1228);
	$goods_1229    = get_goods_info(1229);
	$goods_946   = get_goods_info(946);
	$goods_945   = get_goods_info(945);
	
	$goodsds_896   = get_goods_ds(896);
	$goodsds_898   = get_goods_ds(898);
	$goodsds_895    = get_goods_ds(895);
	$goodsds_897   = get_goods_ds(897);
	$goodsds_1271    = get_goods_ds(1271);
	$goodsds_1270   = get_goods_ds(1270);
	$goodsds_1228    = get_goods_ds(1228);
	$goodsds_1229    = get_goods_ds(1229);
	$goodsds_946   = get_goods_ds(946);
	$goodsds_945   = get_goods_ds(945);
	
	$smarty->assign('goods_896', $goods_896);
	$smarty->assign('goods_898', $goods_898);
	$smarty->assign('goods_895',  $goods_895);
	$smarty->assign('goods_897',  $goods_897);
	$smarty->assign('goods_1271', $goods_1271);
	$smarty->assign('goods_1270',  $goods_1270);
	$smarty->assign('goods_1228', $goods_1228);
	$smarty->assign('goods_1229', $goods_1229);
	$smarty->assign('goods_946',  $goods_946);
	$smarty->assign('goods_945', $goods_945);
	
	$smarty->assign('goodsds_896', $goodsds_896);
	$smarty->assign('goodsds_898', $goodsds_898);
	$smarty->assign('goodsds_895',  $goodsds_895);
	$smarty->assign('goodsds_897', $goodsds_897);
	$smarty->assign('goodsds_1271',  $goodsds_1271);
	$smarty->assign('goodsds_1270', $goodsds_1270);
	$smarty->assign('goodsds_1228', $goodsds_1228);
	$smarty->assign('goodsds_1229',  $goodsds_1229);
	$smarty->assign('goodsds_946', $goodsds_946);
	$smarty->assign('goodsds_945', $goodsds_945);
}
//59元双旦随心配
elseif(13122001 == $pid)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';
	if ($act == 'add_to_cart') {
		if($num == 59){
			$cart59_number = isset($_REQUEST['cart59_number'])? $_REQUEST['cart59_number']: '0';
		
			$cart59_goods1 = isset($_REQUEST['cart59_goods1'])? $_REQUEST['cart59_goods1']: '0';
			$cart59_goods2 = isset($_REQUEST['cart59_goods2'])? $_REQUEST['cart59_goods2']: '0';
			$cart59_goods1_zselect = isset($_REQUEST['cart59_goods1_zselect'])? $_REQUEST['cart59_goods1_zselect']: '';
			$cart59_goods1_yselect = isset($_REQUEST['cart59_goods1_yselect'])? $_REQUEST['cart59_goods1_yselect']: '';
			$cart59_goods2_zselect = isset($_REQUEST['cart59_goods2_zselect'])? $_REQUEST['cart59_goods2_zselect']: '';
			$cart59_goods2_yselect = isset($_REQUEST['cart59_goods2_yselect'])? $_REQUEST['cart59_goods2_yselect']: '';
			
			
			$total_price_59 = 59.00;	//随心配的总价 是固定的
			$package_id_59 = 113;		//礼包ID 是固定的
			
			if ($cart59_number ) 
			{
				if ($cart59_goods1 && $cart59_goods2) 
				{
					$g_1 = get_goods_info($cart59_goods1);
					$g_2 = get_goods_info($cart59_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart59_goods1."', '1', '[59元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_59."', '1', '".$cart59_goods1_zselect.','.$cart59_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart59_goods2."', '', '[59元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart59_goods2_zselect.','.$cart59_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '59元区加入购物车!';
				}
				
				
			}
			
			exit;
		}elseif($num == 99){
			$cart99_number = isset($_REQUEST['cart99_number'])? $_REQUEST['cart99_number']: '0';
			
			$cart99_goods1 = isset($_REQUEST['cart99_goods1'])? $_REQUEST['cart99_goods1']: '0';
			$cart99_goods2 = isset($_REQUEST['cart99_goods2'])? $_REQUEST['cart99_goods2']: '0';
			$cart99_goods1_zselect = isset($_REQUEST['cart99_goods1_zselect'])? $_REQUEST['cart99_goods1_zselect']: '';
			$cart99_goods1_yselect = isset($_REQUEST['cart99_goods1_yselect'])? $_REQUEST['cart99_goods1_yselect']: '';
			$cart99_goods2_zselect = isset($_REQUEST['cart99_goods2_zselect'])? $_REQUEST['cart99_goods2_zselect']: '';
			$cart99_goods2_yselect = isset($_REQUEST['cart99_goods2_yselect'])? $_REQUEST['cart99_goods2_yselect']: '';
			
			
			$total_price_99 = 99.00;	//随心配的总价 是固定的
			$package_id_99 = 113;		//礼包ID 是固定的
			
			if ($cart99_number ) 
			{
				if ($cart99_goods1 && $cart99_goods2) 
				{
					$g_1 = get_goods_info($cart99_goods1);
					$g_2 = get_goods_info($cart99_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart99_goods1."', '1', '[99元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_99."', '1', '".$cart99_goods1_zselect.','.$cart99_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart99_goods2."', '', '[99元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart99_goods2_zselect.','.$cart99_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '99元区加入购物车!';
				}
				
				
			}
			
			exit;
		}elseif($num == 199){
			$cart199_number = isset($_REQUEST['cart199_number'])? $_REQUEST['cart199_number']: '0';
		
			$cart199_goods1 = isset($_REQUEST['cart199_goods1'])? $_REQUEST['cart199_goods1']: '0';
			$cart199_goods2 = isset($_REQUEST['cart199_goods2'])? $_REQUEST['cart199_goods2']: '0';
			$cart199_goods1_zselect = isset($_REQUEST['cart199_goods1_zselect'])? $_REQUEST['cart199_goods1_zselect']: '';
			$cart199_goods1_yselect = isset($_REQUEST['cart199_goods1_yselect'])? $_REQUEST['cart199_goods1_yselect']: '';
			$cart199_goods2_zselect = isset($_REQUEST['cart199_goods2_zselect'])? $_REQUEST['cart199_goods2_zselect']: '';
			$cart199_goods2_yselect = isset($_REQUEST['cart199_goods2_yselect'])? $_REQUEST['cart199_goods2_yselect']: '';
			
			
			$total_price_199 = 199.00;	//随心配的总价 是固定的
			$package_id_199 = 113;		//礼包ID 是固定的
			
			if ($cart199_number ) 
			{
				if ($cart199_goods1 && $cart199_goods2) 
				{
					$g_1 = get_goods_info($cart199_goods1);
					$g_2 = get_goods_info($cart199_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods1."', '1', '[199元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_199."', '1', '".$cart199_goods1_zselect.','.$cart199_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods2."', '', '[199元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart199_goods2_zselect.','.$cart199_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '199元区加入购物车!';
				}
				
				
			}
			
			exit;
		}elseif($num == 299){
			$cart299_number = isset($_REQUEST['cart299_number'])? $_REQUEST['cart299_number']: '0';
		
			$cart299_goods1 = isset($_REQUEST['cart299_goods1'])? $_REQUEST['cart299_goods1']: '0';
			$cart299_goods2 = isset($_REQUEST['cart299_goods2'])? $_REQUEST['cart299_goods2']: '0';
			$cart299_goods1_zselect = isset($_REQUEST['cart299_goods1_zselect'])? $_REQUEST['cart299_goods1_zselect']: '';
			$cart299_goods1_yselect = isset($_REQUEST['cart299_goods1_yselect'])? $_REQUEST['cart299_goods1_yselect']: '';
			$cart299_goods2_zselect = isset($_REQUEST['cart299_goods2_zselect'])? $_REQUEST['cart299_goods2_zselect']: '';
			$cart299_goods2_yselect = isset($_REQUEST['cart299_goods2_yselect'])? $_REQUEST['cart299_goods2_yselect']: '';
			
			
			$total_price_299 = 299.00;	//随心配的总价 是固定的
			$package_id_299 = 113;		//礼包ID 是固定的
			
			if ($cart299_number ) 
			{
				if ($cart299_goods1 && $cart299_goods2) 
				{
					$g_1 = get_goods_info($cart299_goods1);
					$g_2 = get_goods_info($cart299_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart299_goods1."', '1', '[299元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_299."', '1', '".$cart299_goods1_zselect.','.$cart299_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart299_goods2."', '', '[299元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart299_goods2_zselect.','.$cart299_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '299元区加入购物车!';
				}
				
				
			}
			
			exit;
		}
	}
	$goods_1065   = get_goods_info(1065);
	$goods_1068   = get_goods_info(1068);
	$goods_225    = get_goods_info(225);
	$goods_2880   = get_goods_info(2880);
	$goods_629    = get_goods_info(629);
	$goods_2280   = get_goods_info(2280);
	$goods_2404   = get_goods_info(2404);
	$goods_628    = get_goods_info(628);
	$goods_2573   = get_goods_info(2573);
	$goods_2571   = get_goods_info(2571);
	$goods_599    = get_goods_info(599);
	$goods_2407   = get_goods_info(2407);
	$goods_788    = get_goods_info(788);
	$goods_968    = get_goods_info(968);
	
	$goodsds_1065   = get_goods_ds(1065);
	$goodsds_1068   = get_goods_ds(1068);
	$goodsds_225    = get_goods_ds(225);
	$goodsds_2880   = get_goods_ds(2880);
	$goodsds_629    = get_goods_ds(629);
	$goodsds_2280   = get_goods_ds(2280);
	$goodsds_2404    = get_goods_ds(2404);
	$goodsds_920    = get_goods_ds(628);
	$goodsds_2573   = get_goods_ds(2573);
	$goodsds_2571   = get_goods_ds(2571);
	$goodsds_599    = get_goods_ds(599);
	$goodsds_2407   = get_goods_ds(2407);
	$goodsds_788    = get_goods_ds(788);
	$goodsds_968    = get_goods_ds(968);
	
	$smarty->assign('goods_1065', $goods_1065);
	$smarty->assign('goods_1068', $goods_1068);
	$smarty->assign('goods_225',  $goods_225);
	$smarty->assign('goods_2880', $goods_2880);
	$smarty->assign('goods_629',  $goods_629);
	$smarty->assign('goods_2280', $goods_2280);
	$smarty->assign('goods_2404', $goods_2404);
	$smarty->assign('goods_628',  $goods_628);
	$smarty->assign('goods_2573', $goods_2573);
	$smarty->assign('goods_2571', $goods_2571);
	$smarty->assign('goods_599',  $goods_599);
	$smarty->assign('goods_2407', $goods_2407);
	$smarty->assign('goods_788',  $goods_788);
	$smarty->assign('goods_968',  $goods_968);
	
	$smarty->assign('goodsds_1065', $goodsds_1065);
	$smarty->assign('goodsds_1068', $goodsds_1068);
	$smarty->assign('goodsds_225',  $goodsds_225);
	$smarty->assign('goodsds_2880', $goodsds_2880);
	$smarty->assign('goodsds_629',  $goodsds_629);
	$smarty->assign('goodsds_2280', $goodsds_2280);
	$smarty->assign('goodsds_2404', $goodsds_2404);
	$smarty->assign('goodsds_628',  $goodsds_628);
	$smarty->assign('goodsds_2573', $goodsds_2573);
	$smarty->assign('goodsds_2571', $goodsds_2571);
	$smarty->assign('goodsds_599',  $goodsds_599);
	$smarty->assign('goodsds_2407', $goodsds_2407);
	$smarty->assign('goodsds_788',  $goodsds_788);
	$smarty->assign('goodsds_968',  $goodsds_968);
}
//99元七夕随心配
elseif(13122002 == $pid)
{
	$goods_2757   = get_goods_info(2757);
	$goods_2753   = get_goods_info(2753);
	$goods_2606   = get_goods_info(2606);
	$goods_141    = get_goods_info(141);
	$goods_2574   = get_goods_info(2574);
	$goods_2929   = get_goods_info(2929);
	$goods_2928   = get_goods_info(2928);
	$goods_825    = get_goods_info(825);
	$goods_1023   = get_goods_info(1023);
	$goods_916    = get_goods_info(916);
	$goods_1472   = get_goods_info(1472);
	$goods_1471   = get_goods_info(1471);
	$goods_1459   = get_goods_info(1459);
	$goods_1457   = get_goods_info(1457);
	$goods_2593   = get_goods_info(2593);
	$goods_596    = get_goods_info(596);
	$goods_955    = get_goods_info(955);
	$goods_956    = get_goods_info(956);
	$goods_951    = get_goods_info(951);
	$goods_953    = get_goods_info(953);
	

	
	$goodsds_2757   = get_goods_ds(2757);
	$goodsds_2753   = get_goods_ds(2753);
	$goodsds_2606   = get_goods_ds(2606);
	$goodsds_141    = get_goods_ds(141);
	$goodsds_2574   = get_goods_ds(2574);
	$goodsds_2929   = get_goods_ds(2929);
	$goodsds_2928   = get_goods_ds(2928);
	$goodsds_825    = get_goods_ds(825);
	$goodsds_1023   = get_goods_ds(1023);
	$goodsds_916    = get_goods_ds(916);
	$goodsds_1472   = get_goods_ds(1472);
	$goodsds_1471   = get_goods_ds(1471);
	$goodsds_1459   = get_goods_ds(1459);
	$goodsds_1457   = get_goods_ds(1457);
	$goodsds_2593   = get_goods_ds(2593);
	$goodsds_596    = get_goods_ds(596);
	$goodsds_955    = get_goods_ds(955);
	$goodsds_956    = get_goods_ds(956);
	$goodsds_951    = get_goods_ds(951);
	$goodsds_953    = get_goods_ds(953);

	
	$smarty->assign('goods_2757', $goods_2757);
	$smarty->assign('goods_2753', $goods_2753);
	$smarty->assign('goods_2606', $goods_2606);
	$smarty->assign('goods_141',  $goods_141);
	$smarty->assign('goods_2574', $goods_2574);
	$smarty->assign('goods_2929', $goods_2929);
	$smarty->assign('goods_2928', $goods_2928);
	$smarty->assign('goods_825',  $goods_825);
	$smarty->assign('goods_1023', $goods_1023);
	$smarty->assign('goods_916',  $goods_916);
	$smarty->assign('goods_1472', $goods_1472);
	$smarty->assign('goods_1471', $goods_1471);
	$smarty->assign('goods_1459', $goods_1459);
	$smarty->assign('goods_1457', $goods_1457);
	$smarty->assign('goods_2593', $goods_2593);
	$smarty->assign('goods_596',  $goods_596);
	$smarty->assign('goods_955',  $goods_955);
	$smarty->assign('goods_956',  $goods_956);
	$smarty->assign('goods_951',  $goods_951);
	$smarty->assign('goods_953',  $goods_953);
	
	$smarty->assign('goodsds_2757', $goodsds_2757);
	$smarty->assign('goodsds_2753', $goodsds_2753);
	$smarty->assign('goodsds_2606', $goodsds_2606);
	$smarty->assign('goodsds_141',  $goodsds_141);
	$smarty->assign('goodsds_2574', $goodsds_2574);
	$smarty->assign('goodsds_2929', $goodsds_2929);
	$smarty->assign('goodsds_2928', $goodsds_2928);
	$smarty->assign('goodsds_825',  $goodsds_825);
	$smarty->assign('goodsds_1023', $goodsds_1023);
	$smarty->assign('goodsds_916',  $goodsds_916);
	$smarty->assign('goodsds_1472', $goodsds_1472);
	$smarty->assign('goodsds_1471', $goodsds_1471);
	$smarty->assign('goodsds_1459', $goodsds_1459);
	$smarty->assign('goodsds_1457', $goodsds_1457);
	$smarty->assign('goodsds_2593', $goodsds_2593);
	$smarty->assign('goodsds_596',  $goodsds_596);
	$smarty->assign('goodsds_955',  $goodsds_955);
	$smarty->assign('goodsds_956',  $goodsds_956);
	$smarty->assign('goodsds_951',  $goodsds_951);
	$smarty->assign('goodsds_953',  $goodsds_953);
}
//199元七夕随心配
elseif(13122003 == $pid)
{
	
	$goods_1271  = get_goods_info(1271);
	$goods_901   = get_goods_info(901);
	$goods_185   = get_goods_info(185);
	$goods_227   = get_goods_info(227);
	$goods_359   = get_goods_info(359);
	$goods_358   = get_goods_info(358);
	$goods_104   = get_goods_info(104);
	$goods_835   = get_goods_info(835);
	$goods_896   = get_goods_info(896);
	$goods_898   = get_goods_info(898);
	$goods_94    = get_goods_info(94);
	$goods_118   = get_goods_info(118);
	$goods_2862  = get_goods_info(2862);
	$goods_2412  = get_goods_info(2412);
	$goods_1220  = get_goods_info(1220);

	
	$goodsds_1271  = get_goods_ds(1271);
	$goodsds_901   = get_goods_ds(901);
	$goodsds_185   = get_goods_ds(185);
	$goodsds_227   = get_goods_ds(227);
	$goodsds_359   = get_goods_ds(359);
	$goodsds_358   = get_goods_ds(358);
	$goodsds_104   = get_goods_ds(104);
	$goodsds_835   = get_goods_ds(835);
	$goodsds_896   = get_goods_ds(896);
	$goodsds_898   = get_goods_ds(898);
	$goodsds_94    = get_goods_ds(94);
	$goodsds_118   = get_goods_ds(118);
	$goodsds_2862  = get_goods_ds(2862);
	$goodsds_2412  = get_goods_ds(2412);
	$goodsds_1220  = get_goods_ds(1220);

	
	$smarty->assign('goods_1271', $goods_1271);
	$smarty->assign('goods_901',  $goods_901);
	$smarty->assign('goods_185',  $goods_185);
	$smarty->assign('goods_227',  $goods_227);
	$smarty->assign('goods_359',  $goods_359);
	$smarty->assign('goods_358',  $goods_358);
	$smarty->assign('goods_104',  $goods_104);
	$smarty->assign('goods_835',  $goods_835);
	$smarty->assign('goods_896',  $goods_896);
	$smarty->assign('goods_898',  $goods_898);
	$smarty->assign('goods_94',   $goods_94);
	$smarty->assign('goods_118',  $goods_118);
	$smarty->assign('goods_2862', $goods_2862);
	$smarty->assign('goods_2412', $goods_2412);
	$smarty->assign('goods_1220', $goods_1220);
	
	$smarty->assign('goodsds_1271', $goodsds_1271);
	$smarty->assign('goodsds_901',  $goodsds_901);
	$smarty->assign('goodsds_185',  $goodsds_185);
	$smarty->assign('goodsds_227',  $goodsds_227);
	$smarty->assign('goodsds_359',  $goodsds_359);
	$smarty->assign('goodsds_358',  $goodsds_358);
	$smarty->assign('goodsds_104',  $goodsds_104);
	$smarty->assign('goodsds_835',  $goodsds_835);
	$smarty->assign('goodsds_896',  $goodsds_896);
	$smarty->assign('goodsds_898',  $goodsds_898);
	$smarty->assign('goodsds_94',   $goodsds_94);
	$smarty->assign('goodsds_118',  $goodsds_118);
	$smarty->assign('goodsds_2862', $goodsds_2862);
	$smarty->assign('goodsds_2412', $goodsds_2412);
	$smarty->assign('goodsds_1220', $goodsds_1220);
}
//299元七夕随心配
elseif(13122004 == $pid)
{

	
	$goods_1269   = get_goods_info(1269);
	$goods_1262   = get_goods_info(1262);
	$goods_1266   = get_goods_info(1266);
	$goods_1265   = get_goods_info(1265);
	$goods_2105   = get_goods_info(2105);
	$goods_2111   = get_goods_info(2111);
	$goods_2575   = get_goods_info(2575);
	$goods_2762   = get_goods_info(2762);
	$goods_119    = get_goods_info(119);
	$goods_1097   = get_goods_info(1097);
	$goods_2913   = get_goods_info(2913);
	$goods_2915   = get_goods_info(2915);
	$goods_948    = get_goods_info(948);
	$goods_1149   = get_goods_info(1149);
	$goods_1152   = get_goods_info(1152);
	$goods_1147   = get_goods_info(1147);

	
	$goodsds_1269   = get_goods_ds(1269);
	$goodsds_1262   = get_goods_ds(1262);
	$goodsds_1266   = get_goods_ds(1266);
	$goodsds_1265   = get_goods_ds(1265);
	$goodsds_2105   = get_goods_ds(2105);
	$goodsds_2111   = get_goods_ds(2111);
	$goodsds_2575   = get_goods_ds(2575);
	$goodsds_2762   = get_goods_ds(2762);
	$goodsds_119    = get_goods_ds(119);
	$goodsds_1097   = get_goods_ds(1097);
	$goodsds_2913   = get_goods_ds(2913);
	$goodsds_2915   = get_goods_ds(2915);
	$goodsds_948    = get_goods_ds(948);
	$goodsds_1149   = get_goods_ds(1149);
	$goodsds_1152   = get_goods_ds(1152);
	$goodsds_1147   = get_goods_ds(1147);

	
	$smarty->assign('goods_1269', $goods_1269);
	$smarty->assign('goods_1262', $goods_1262);
	$smarty->assign('goods_1266', $goods_1266);
	$smarty->assign('goods_1265', $goods_1265);
	$smarty->assign('goods_2105', $goods_2105);
	$smarty->assign('goods_2111', $goods_2111);
	$smarty->assign('goods_2575', $goods_2575);
	$smarty->assign('goods_2762', $goods_2762);
	$smarty->assign('goods_119',  $goods_119);
	$smarty->assign('goods_1097', $goods_1097);
	$smarty->assign('goods_2913', $goods_2913);
	$smarty->assign('goods_2915', $goods_2915);
	$smarty->assign('goods_948',  $goods_948);
	$smarty->assign('goods_1149', $goods_1149);
	$smarty->assign('goods_1152', $goods_1152);
	$smarty->assign('goods_1147', $goods_1147);
	
	$smarty->assign('goodsds_1269', $goodsds_1269);
	$smarty->assign('goodsds_1262', $goodsds_1262);
	$smarty->assign('goodsds_1266', $goodsds_1266);
	$smarty->assign('goodsds_1265', $goodsds_1265);
	$smarty->assign('goodsds_2105', $goodsds_2105);
	$smarty->assign('goodsds_2111', $goodsds_2111);
	$smarty->assign('goodsds_2575', $goodsds_2575);
	$smarty->assign('goodsds_2762', $goodsds_2762);
	$smarty->assign('goodsds_119',  $goodsds_119);
	$smarty->assign('goodsds_1097', $goodsds_1097);
	$smarty->assign('goodsds_2913', $goodsds_2913);
	$smarty->assign('goodsds_2915', $goodsds_2915);
	$smarty->assign('goodsds_948',  $goodsds_948);
	$smarty->assign('goodsds_1149', $goodsds_1149);
	$smarty->assign('goodsds_1152', $goodsds_1152);
	$smarty->assign('goodsds_1147', $goodsds_1147);
}
elseif(130812 == $pid)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	if ($act == 'add_to_cart') {
		/* * <form id="myform_99">
				<input type="hidden" autocomplete="off" name="cart99_number" id="cart99_number" value="0" />
				<input type="hidden" autocomplete="off" name="cart99_goods1" id="cart99_goods1" value="0" />
				<input type="hidden" autocomplete="off" name="cart99_goods2" id="cart99_goods2" value="0" />
				<input type="hidden" autocomplete="off" name="cart99_goods1_zselect" id="cart99_goods1_zselect" value="" />
				<input type="hidden" autocomplete="off" name="cart99_goods1_yselect" id="cart99_goods1_yselect" value="" />
				<input type="hidden" autocomplete="off" name="cart99_goods2_zselect" id="cart99_goods2_zselect" value="" />
				<input type="hidden" autocomplete="off" name="cart99_goods2_yselect" id="cart99_goods2_yselect" value="" />
				
				<input type="hidden" autocomplete="off" name="cart199_number" id="cart199_number" value="0" />
				<input type="hidden" autocomplete="off" name="cart199_goods1" id="cart199_goods1" value="0" />
				<input type="hidden" autocomplete="off" name="cart199_goods2" id="cart199_goods2" value="0" />
				<input type="hidden" autocomplete="off" name="cart199_goods1_zselect" id="cart199_goods1_zselect" value="" />
				<input type="hidden" autocomplete="off" name="cart199_goods1_yselect" id="cart199_goods1_yselect" value="" />
				<input type="hidden" autocomplete="off" name="cart199_goods2_zselect" id="cart199_goods2_zselect" value="" />
				<input type="hidden" autocomplete="off" name="cart199_goods2_yselect" id="cart199_goods2_yselect" value="" />
				</form>
		 * */
		$cart99_number = isset($_REQUEST['cart99_number'])? $_REQUEST['cart99_number']: '0';
		$cart199_number = isset($_REQUEST['cart199_number'])? $_REQUEST['cart199_number']: '0';
		
		$cart99_goods1 = isset($_REQUEST['cart99_goods1'])? $_REQUEST['cart99_goods1']: '0';
		$cart99_goods2 = isset($_REQUEST['cart99_goods2'])? $_REQUEST['cart99_goods2']: '0';
		$cart99_goods1_zselect = isset($_REQUEST['cart99_goods1_zselect'])? $_REQUEST['cart99_goods1_zselect']: '';
		$cart99_goods1_yselect = isset($_REQUEST['cart99_goods1_yselect'])? $_REQUEST['cart99_goods1_yselect']: '';
		$cart99_goods2_zselect = isset($_REQUEST['cart99_goods2_zselect'])? $_REQUEST['cart99_goods2_zselect']: '';
		$cart99_goods2_yselect = isset($_REQUEST['cart99_goods2_yselect'])? $_REQUEST['cart99_goods2_yselect']: '';
		
		$cart199_goods1 = isset($_REQUEST['cart199_goods1'])? $_REQUEST['cart199_goods1']: '';
		$cart199_goods2 = isset($_REQUEST['cart199_goods2'])? $_REQUEST['cart199_goods2']: '';
		$cart199_goods1_zselect = isset($_REQUEST['cart199_goods1_zselect'])? $_REQUEST['cart199_goods1_zselect']: '';
		$cart199_goods1_yselect = isset($_REQUEST['cart199_goods1_yselect'])? $_REQUEST['cart199_goods1_yselect']: '';
		$cart199_goods2_zselect = isset($_REQUEST['cart199_goods2_zselect'])? $_REQUEST['cart199_goods2_zselect']: '';
		$cart199_goods2_yselect = isset($_REQUEST['cart199_goods2_yselect'])? $_REQUEST['cart199_goods2_yselect']: '';
		
		$total_price_99 = 99.00;	//随心配的总价 是固定的
		$package_id_99 = 113;		//礼包ID 是固定的
		
		$total_price_199 = 199.00;
		$package_id_199 = 114;
		
		if ($cart99_number || $cart199_number) 
		{
			if ($cart99_goods1 && $cart99_goods2) 
			{
				$g_1 = get_goods_info($cart99_goods1);
				$g_2 = get_goods_info($cart99_goods2);
				
				$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart99_goods1."', '1', '[99元随心配]".$g_1['goods_name']."', '".$package_id_99."', '".$total_price_99."', '1', '".$cart99_goods1_zselect.','.$cart99_goods1_yselect."', '1', 'package_buy', '$package_id_99', '1', '".$g_1['goods_img']."')";
				$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart99_goods2."', '', '[99元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart99_goods2_zselect.','.$cart99_goods2_yselect."', '1', 'package_buy', '$package_id_99', '1', '".$g_2['goods_img']."')";
				
				$res1 = $GLOBALS['db']->query($sql1);
				$res2 = $GLOBALS['db']->query($sql2);
				
				if ($res1 && $res2) echo '99元区加入购物车!';
			}
			
			if ($cart199_goods1 && $cart199_goods2) 
			{
				$g_1 = get_goods_info($cart199_goods1);
				$g_2 = get_goods_info($cart199_goods2);
				
				$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods1."', '1', '[199元随心配]".$g_1['goods_name']."', '".$package_id_199."', '".$total_price_199."', '1', '".$cart199_goods1_zselect.','.$cart199_goods1_yselect."', '1', 'package_buy', '$package_id_199', '1', '".$g_1['goods_img']."')";
				$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart199_goods2."', '', '[199元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart199_goods2_zselect.','.$cart199_goods2_yselect."', '1', 'package_buy', '$package_id_199', '1', '".$g_2['goods_img']."')";
				
				$res1 = $GLOBALS['db']->query($sql1);
				$res2 = $GLOBALS['db']->query($sql2);
				
				if ($res1 && $res2) echo '199元区加入购物车!';
			}
		}
		
		exit;
	}
	
	$goods_891   = get_goods_info(891);
	$goods_890   = get_goods_info(890);
	$goods_1461   = get_goods_info(1461);
	$goods_1457   = get_goods_info(1457);
	$goods_916   = get_goods_info(916);
	$goods_920   = get_goods_info(920);
	$goods_2571   = get_goods_info(2571);
	$goods_2573   = get_goods_info(2573);
	$goods_1269   = get_goods_info(1269);
	$goods_1262   = get_goods_info(1262);
	$goods_901   = get_goods_info(901);
	$goods_2110   = get_goods_info(2110);
	$goods_2115   = get_goods_info(2115);
	$goods_1177   = get_goods_info(1177);
	$goods_1179   = get_goods_info(1179);
	$goods_359   = get_goods_info(359);
	$goods_1218   = get_goods_info(1218);
	$goods_118   = get_goods_info(118);
	$goods_757   = get_goods_info(757);
	$goods_835   = get_goods_info(835);
	
	$goodsds_891   = get_goods_ds(891);
	$goodsds_890   = get_goods_ds(890);
	$goodsds_1461   = get_goods_ds(1461);
	$goodsds_1457   = get_goods_ds(1457);
	$goodsds_916   = get_goods_ds(916);
	$goodsds_920   = get_goods_ds(920);
	$goodsds_2571   = get_goods_ds(2571);
	$goodsds_2573   = get_goods_ds(2573);
	$goodsds_1269   = get_goods_ds(1269);
	$goodsds_1262   = get_goods_ds(1262);
	$goodsds_901   = get_goods_ds(901);
	$goodsds_2110   = get_goods_ds(2110);
	$goodsds_2115   = get_goods_ds(2115);
	$goodsds_1177   = get_goods_ds(1177);
	$goodsds_1179   = get_goods_ds(1179);
	$goodsds_359   = get_goods_ds(359);
	$goodsds_1218   = get_goods_ds(1218);
	$goodsds_118   = get_goods_ds(118);
	$goodsds_757   = get_goods_ds(757);
	$goodsds_835   = get_goods_ds(835);
	
	$smarty->assign('goods_891', $goods_891);
	$smarty->assign('goods_890', $goods_890);
	$smarty->assign('goods_1461', $goods_1461);
	$smarty->assign('goods_1457', $goods_1457);
	$smarty->assign('goods_916', $goods_916);
	$smarty->assign('goods_920', $goods_920);
	$smarty->assign('goods_2571', $goods_2571);
	$smarty->assign('goods_2573', $goods_2573);
	$smarty->assign('goods_1269', $goods_1269);
	$smarty->assign('goods_1262', $goods_1262);
	$smarty->assign('goods_901', $goods_901);
	$smarty->assign('goods_2110', $goods_2110);
	$smarty->assign('goods_2115', $goods_2115);
	$smarty->assign('goods_1177', $goods_1177);
	$smarty->assign('goods_1179', $goods_1179);
	$smarty->assign('goods_359', $goods_359);
	$smarty->assign('goods_1218', $goods_1218);
	$smarty->assign('goods_118', $goods_118);
	$smarty->assign('goods_757', $goods_757);
	$smarty->assign('goods_835', $goods_835);
	
	$smarty->assign('goodsds_891', $goodsds_891);
	$smarty->assign('goodsds_890', $goodsds_890);
	$smarty->assign('goodsds_1461', $goodsds_1461);
	$smarty->assign('goodsds_1457', $goodsds_1457);
	$smarty->assign('goodsds_916', $goodsds_916);
	$smarty->assign('goodsds_920', $goodsds_920);
	$smarty->assign('goodsds_2571', $goodsds_2571);
	$smarty->assign('goodsds_2573', $goodsds_2573);
	$smarty->assign('goodsds_1269', $goodsds_1269);
	$smarty->assign('goodsds_1262', $goodsds_1262);
	$smarty->assign('goodsds_901', $goodsds_901);
	$smarty->assign('goodsds_2110', $goodsds_2110);
	$smarty->assign('goodsds_2115', $goodsds_2115);
	$smarty->assign('goodsds_1177', $goodsds_1177);
	$smarty->assign('goodsds_1179', $goodsds_1179);
	$smarty->assign('goodsds_359', $goodsds_359);
	$smarty->assign('goodsds_1218', $goodsds_1218);
	$smarty->assign('goodsds_118', $goodsds_118);
	$smarty->assign('goodsds_757', $goodsds_757);
	$smarty->assign('goodsds_835', $goodsds_835);
}
//十一秒杀
elseif(130924 == $pid)
{
	date_default_timezone_set('PRC');
	$now = time();
	
	if ($now < strtotime('2013-09-'.date('d').' 10:55:00'))
	{
		//未开始
		$ms_str = '<span><a href="#two"><img src="themes/default/images/active/20130924/future_1.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/future_2.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/future_3.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/future_4.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/future_5.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/future_6.jpg" title="" alt="" /></a></span>';
	}
	elseif ($now > strtotime('2013-09-'.date('d').' 10:55:00') && $now < strtotime('2013-09-'.date('d').' 11:30:00'))
	{
		//进行中
		$ms_str = '<span><a href="goods2816.html" target="_blank"><img src="themes/default/images/active/20130924/now_1.jpg" title="" alt="" /></a></span>
				   <span><a href="goods2815.html" target="_blank"><img src="themes/default/images/active/20130924/now_2.jpg" title="" alt="" /></a></span>
				   <span><a href="goods2817.html" target="_blank"><img src="themes/default/images/active/20130924/now_3.jpg" title="" alt="" /></a></span>
				   <span><a href="goods2819.html" target="_blank"><img src="themes/default/images/active/20130924/now_4.jpg" title="" alt="" /></a></span>
				   <span><a href="goods2813.html" target="_blank"><img src="themes/default/images/active/20130924/now_5.jpg" title="" alt="" /></a></span>
				   <span><a href="goods2818.html" target="_blank"><img src="themes/default/images/active/20130924/now_6.jpg" title="" alt="" /></a></span>';
	}
	else
	{
		//结束
		$ms_str = '<span><a href="#two"><img src="themes/default/images/active/20130924/next_1.jpg" title="" alt="" /></a></span>
		           <span><a href="#two"><img src="themes/default/images/active/20130924/next_2.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/next_3.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/next_4.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/next_5.jpg" title="" alt="" /></a></span>
				   <span><a href="#two"><img src="themes/default/images/active/20130924/next_6.jpg" title="" alt="" /></a></span>';
	}
	
	$smarty->assign('ms_str',	$ms_str);
}

//堆糖专享活动注册
elseif ($pid == 131028)
{
	/*$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND add_time > 1384312501 ORDER BY rec_id DESC LIMIT 50");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['order_sn'] = $v['extension_id'];
		$prize_arr[$key]['prize_rank'] = get_prize_content($v['prize_rank']);
	}*/
	//user_id = 0 是手动添加的中奖信息
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND (add_time > 1385308800 OR user_id = 0) ORDER BY rec_id DESC LIMIT 70");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['prize_rank'] = get_prize_content($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}

//双12领克特注册
elseif ($pid == 131023)
{
	//$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='领克特专享注册抽奖' AND (add_time > 1385913600 OR user_id = 0) ORDER BY rec_id DESC LIMIT 70");
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='领克特专享注册抽奖' AND add_time > 1385913600 ORDER BY rec_id DESC LIMIT 70");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['prize_rank'] = get_prize_content($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}

//双12多麦注册
elseif ($pid == 131202)
{
	//$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='多麦专享注册抽奖' AND (add_time > 1385913600 OR user_id = 0) ORDER BY rec_id DESC LIMIT 70");
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='多麦专享注册抽奖' AND add_time > 1385913600 ORDER BY rec_id DESC LIMIT 70");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['prize_rank'] = get_prize_content($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}

//双11商家联盟抽奖
elseif ($pid == 131108)
{
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='2013双11联合抽奖' ORDER BY rec_id DESC LIMIT 50");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['order_sn'] = $v['extension_id'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_1108($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}

//131119感恩节抽奖信息
elseif ($pid == 131119)
{
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='2013感恩节抽奖' ORDER BY rec_id DESC LIMIT 50");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['order_sn'] = $v['extension_id'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_1119($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}

//双11
elseif ($pid == 131111)
{
	date_default_timezone_set('PRC');
	$now = time();
	if ($now >= strtotime('2013-11-11 10:00:00') && $now <= strtotime('2013-11-11 23:59:59'))
	{
		$smarty->assign('can_go', 1);
	}
}

/* 原周二专场
elseif ($pid == 2)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (835,2572,118,105)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (2608,1177,890,241)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (580,2280,924,2786)");

	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}
*/
// 周二和16年五一大促活动分会场   $pid == 2 ||
elseif($pid == 16041801){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr1 = array(
        array(4793,3), array(4138,3), array(4135,3), array(4852,3), array(5198,3), array(5110,3)
    );
    $goodsArr2 = array(
        array(313,3), array(916,3), array(238,3), array(1475,3), array(5185,3), array(955,3)
    );
    $goodsArr3 = array(
        array(351,3), array(355,3), array(2869,3), array(5157,3), array(4559,3), array(4615,3)
    );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],1);
            } else {
                $res['promote_price'] = round($res['shop_price'],1);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['promote_price'];
                $res['zk'] = "买一送一";
                if($v[0] == 4793){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon%E6%A2%A6%E5%B9%BB%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                    $res['goods_name'] = 'Bescon梦幻半年抛彩色隐形眼镜1片装';
                }elseif($v[0] == 4138){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Circle%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                    $res['goods_name'] = 'Bescon Tutti Circle大直径系列半年抛彩色隐形眼镜1片装';
                }elseif($v[0] == 4135){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+premium%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                    $res['goods_name'] = 'Bescon Tutti premium大直径系列半年抛彩色隐形眼镜1片装';
                }elseif($v[0] == 4852){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7%E7%84%95%E5%BD%A9%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                    $res['goods_name'] = '菲士康焕彩日抛型彩色隐形眼镜10片装';
                }elseif($v[0] == 5198){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%B0%B4%E7%81%B5%E7%82%AB%E5%BD%A9%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                    $res['goods_name'] = '博士伦水灵炫彩半年抛彩色隐形眼镜1片装';
                }elseif($v[0] == 5110){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E7%94%9C%E5%BF%83%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B1%E7%89%87%E8%A3%85';
                    $res['goods_name'] = '伊厶康甜心彩色隐形眼镜年抛1片装';
                }
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],1);
            } else {
                $res['promote_price'] = round($res['shop_price'],1);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['promote_price'];
                $res['zk'] = "买一送一";
                if($v[0] == 313){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=G%EF%BC%86G%E8%A5%BF%E6%AD%A6%E5%A4%A7%E7%9C%BC%E7%9D%9B%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                    $res['goods_name'] = 'G＆G西武大眼睛系列彩色隐形眼镜';
                }elseif($v[0] == 916){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=Cool%E8%8F%A0%E8%90%9D%E4%B8%89%E8%89%B2%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                    $res['goods_name'] = 'Bescon Tutti Cool菠萝三色系列年抛型彩色隐形眼镜';
                }elseif($v[0] == 238){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%A7%91%E8%8E%B1%E5%8D%9Aclear%E5%9B%9B%E5%8F%B6%E8%8D%89';
                    $res['goods_name'] = 'Bescon Tutti 科莱博clear四叶草年抛型彩色隐形眼镜';
                }elseif($v[0] == 1475){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E6%80%A1%E7%BE%8E%E6%80%9D%E7%B2%89%E9%92%BB%E7%B3%BB%E5%88%97';
                    $res['goods_name'] = '怡美思粉钻系列年抛型彩色隐形眼镜';
                }elseif($v[0] == 5185){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%A7%91%E8%8E%B1%E5%8D%9A%E7%AE%80%E5%8D%95%E7%88%B1%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B%E4%B8%80%E7%89%87%E8%A3%85';
                    $res['goods_name'] = '科莱博简单爱系列彩色隐形眼镜年抛一片装';
                }elseif($v[0] == 955){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%82%B2%E8%A7%86%E7%BB%9A%E4%B8%BD%E6%98%9F%E5%AD%A3%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                    $res['goods_name'] = '傲视绚丽星季抛彩色隐形眼镜2片装';
                }
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],1);
            } else {
                $res['promote_price'] = round($res['shop_price'],1);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['promote_price'];
                $res['zk'] = "买一送一";
                if($v[0] == 351){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E4%B8%89%E8%89%B2';
                    $res['goods_name'] = 'NEO公主系列三色系列年抛彩色隐形眼镜';
                }elseif($v[0] == 355){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                    $res['goods_name'] = 'NEO巨目系列年抛型彩色隐形眼镜';
                }elseif($v[0] == 2869){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%89%BE%E7%88%B5%E5%B7%A7%E5%85%8B%E5%8A%9B%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                    $res['goods_name'] = '艾爵巧克力公主系列彩色隐形眼镜 ';
                }elseif($v[0] == 5157){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%88%B1%E6%BC%BE%E5%94%90%E7%BA%B3%E6%BB%8B%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B%E5%9E%8B';
                    $res['goods_name'] = '爱漾唐纳滋系列彩色隐形眼镜年抛型';
                }elseif($v[0] == 4559){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8F%AF%E4%B8%BD%E5%8D%9A%E9%9B%AF%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                    $res['goods_name'] = '可丽博雯彩系列年抛型彩色隐形眼镜';
                }elseif($v[0] == 4615){
                    $res['href']       = 'http://m.easeeyes.com/category.php?search=1&keyword=%E9%AD%85%E7%9E%B3%E6%98%93%E5%BD%A9%E9%AD%94%E5%B9%BB%E6%98%9F%E7%A9%BA%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                    $res['goods_name'] = '魅瞳易彩魔幻星空系列年抛型彩色隐形眼镜';
                }
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '/盒</del>';
            }
            $resArr3[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
// 周四和16年五一大促活动分会场    $pid == 4 ||
elseif($pid == 16041802){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr4 = array();
    $goodsArr1 = array(
        array(1144,1), array(1,1), array(2,1), array(5205,1)
    , array(3,1), array(633,1), array(4796,1), array(5089,1)
    );
    $goodsArr2 = array(
        array(4938,1), array(1,1), array(2,1), array(3,1)
    , array(4,1), array(5,1), array(6,1), array(860,1)
    );
    $goodsArr3 = array(
        array(5090,1), array(3035,1), array(5164,1), array(1,1)
    , array(2,1), array(997,1), array(2748,1), array(634,1)
    );
    $goodsArr4 = array(
        array(1151,1), array(105,1), array(4298,1), array(4802,1)
    , array(1,1), array(2,1), array(3,1), array(2556,1)
    );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 5136");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%A7%91%E5%B0%94%E8%A7%86%E6%A0%BC%E8%A8%80%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = '科尔视格言系列日抛彩色隐形眼镜2片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 5158");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%88%B1%E6%BC%BE%E5%A4%A9%E4%BD%BF';
                $res['goods_name'] = '爱漾天使三色系列彩色隐形眼镜年抛型';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4142");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Classic%E7%BB%8F%E5%85%B8%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Classic经典大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 633){// 团购商品
                $res = $GLOBALS['db']->GetRow("SELECT SUM(a.market_price*b.goods_number) AS market_price, SUM(a.shop_price*b.goods_number) AS shop_price, c.tuan_img, c.tuan_price FROM ecs_goods a LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id LEFT JOIN ecs_tuan c ON c.rec_id=b.tuan_id WHERE b.tuan_id = 633");
                $res['promote_price'] = number_format($res['tuan_price'],1);
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://www.easeeyes.com/tuan_buy_633.html';
                $res['goods_name'] = '卫康金装清凉型隐形眼镜护理液125m*2';
                $res['goods_thumb'] = $res['tuan_img'];
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = number_format($res['promote_price'],1);
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } elseif ($v[1] == 3) {
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 982");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝明眸日抛5片';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 879");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO%E7%BA%AA%E4%BE%9D%E6%BE%B3+%E5%98%89%E4%B8%BD%E7%A7%80%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'GEO纪依澳 嘉丽秀系列年抛隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4333");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%88%B1%E6%BC%BE+%E7%BC%AA%E6%96%AF';
                $res['goods_name'] = '爱漾缪斯女神系列大直径彩色隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1204");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%AB%E5%BA%B7%E7%BB%AE%E9%9D%93%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '卫康绮靓系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2998");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%89%E7%9E%B3%E7%BE%8E%E6%84%9F%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85';
                $res['goods_name'] = '安瞳美感系列日抛型彩色隐形眼镜5片装';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 988");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E6%B5%B7%E6%98%8C%E6%B5%B7%E4%BF%AA%E6%81%A9%E9%9D%93%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '海昌海俪恩靓彩系列年抛型彩色隐形眼镜';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = number_format($res['promote_price'],1);
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } elseif ($v[1] == 3) {
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 5102");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8KEESMO%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'NEO可视眸KEESMO彩色隐形眼镜日抛10片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1817");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%26G%E8%A5%BF%E6%AD%A6%E9%97%AA%E7%9D%9B%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'G&G西武闪睛系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 634){// 团购商品
                $res = $GLOBALS['db']->GetRow("SELECT SUM(a.market_price*b.goods_number) AS market_price, SUM(a.shop_price*b.goods_number) AS shop_price, c.tuan_img, c.tuan_price FROM ecs_goods a LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id LEFT JOIN ecs_tuan c ON c.rec_id=b.tuan_id WHERE b.tuan_id = 634");
                $res['promote_price'] = number_format($res['tuan_price'],1);
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://www.easeeyes.com/tuan_buy_634.html';
                $res['goods_name'] = '卫康金装清凉型隐形眼镜护理液125m*2';
                $res['goods_thumb'] = $res['tuan_img'];
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = number_format($res['promote_price'],1);
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } elseif ($v[1] == 3) {
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2581");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E7%82%AB%E7%9C%B8%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝炫眸彩色隐形眼镜日抛10片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4527");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=SHO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87DECORATIVE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85+';
                $res['goods_name'] = 'SHO-BI美妆彩片DECORATIVE月抛型彩色隐形眼镜2片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 243");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%A7%91%E8%8E%B1%E5%8D%9Aclearcolor';
                $res['goods_name'] = '科莱博clearcolor梦幻黑年抛型彩色隐形眼镜';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = number_format($res['promote_price'],1);
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } elseif ($v[1] == 3) {
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = number_format($res['shop_price'],1);
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr4[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif($pid == 2 || $pid == 160119){
    date_default_timezone_set('PRC');
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(
        array(1,1), array(2,1), array(3,1), array(4,1)
    , array(5,1), array(6,1), array(7,1), array(8,1)
    );
    $goodsArr2 = array(
        array(5057,1), array(1145,1), array(1,1), array(4298,3,"第二盒半价")
    , array(2,1), array(3,3,"四盒减228"), array(3631,1), array(3630,1)
    );
    $goodsArr3 = array(
        array(4523,3,"买一送一"), array(1,3,"送假睫毛+胶水"), array(2,3,"送唇蜜"), array(3,1,"送睫毛膏")
    );
    $goodsArr4 = array(
        array(1,1,"买一送一"), array(2,1,"买一送一"), array(3,1,"买一送一"), array(4,1)
    , array(5,1), array(6,1), array(7,1,"买一送一"), array(8,2)
    );
    $goodsArr5 = array(
        array(359,3,"买一送一"), array(1,3,"买一送一"), array(2,3,"买一送一"), array(3,3,"二片减18元")
    , array(4,3,"二片减18元"), array(5,3,"买一送一"), array(6,3,"二盒送面膜"), array(7,3,"二盒送甲油")
    );
    $goodsArr6 = array(
        array(1,3,"二盒送护理液"), array(2,3,"二盒送护理液"), array(3,3,"二盒送护理液"), array(4,3,"二盒送护理液")
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4283");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%26G%E8%A5%BF%E6%AD%A6Secret+CandyEyes%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'G&G西武Secret CandyEyes系列年抛型彩色隐形眼镜1片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3928");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%26G%E8%A5%BF%E6%AD%A6CandyEyes%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'G&G西武CandyEyes系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 876");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%EF%BC%86G%E8%A5%BF%E6%AD%A6%E8%87%AA%E7%84%B6%E5%8F%8C%E8%89%B2%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'G&G西武自然双色系列彩色隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1819");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%26G%E8%A5%BF%E6%AD%A6%E9%97%AA%E7%9D%9B%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'G&G西武闪睛系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 335");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%EF%BC%86G%E8%A5%BF%E6%AD%A6%E6%A2%A6%E5%B9%BB180%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'G&G西武梦幻180半年抛彩色隐形眼镜';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 324");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%EF%BC%86G%E8%A5%BF%E6%AD%A6%E5%B9%BB%E5%BD%A9%E4%BA%AE%E5%A6%86%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'G&G西武幻彩亮妆彩色隐形眼镜';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 319");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%EF%BC%86G%E8%A5%BF%E6%AD%A6%E5%BD%A9%E5%A6%86%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'G&G西武彩妆系列彩色隐形眼镜';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 868");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%EF%BC%86G%E8%A5%BF%E6%AD%A6%E9%92%BB%E6%99%B6';
                $res['goods_name'] = 'G&G西武钻晶系列彩色隐形眼镜';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1146");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%9E%E7%9E%B3%E5%B9%BB%E6%A8%B1%E6%81%8B%E5%BF%85%E9%A1%BA%E5%8F%8C%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85';
                $res['goods_name'] = '实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1185");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜10片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1187");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜30片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4529");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=SHO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87DECORATIVE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'SHO-BI美妆彩片DECORATIVE月抛型彩色隐形眼镜2片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4530");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=SHO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87Brigitte%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'SHO-BI美妆彩片Brigitte日抛型彩色隐形眼镜10片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4551");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=SHO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87PienAge%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C12%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'SHO-BI美妆彩片PienAge日抛型彩色隐形眼镜12片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4134");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+premium%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti premium大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4139");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Circle%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Circle大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4146");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Classic%E7%BB%8F%E5%85%B8%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Classic经典大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4794");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon%E6%A2%A6%E5%B9%BB%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon梦幻半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 891");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=BESCON%E5%8F%8C%E8%89%B2%E6%B6%A6%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON双色润彩系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 905");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=BESCON%E5%8F%8C%E8%89%B2%E9%92%BB%E7%9F%B3%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON双色钻石系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2580");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Natural%E5%8D%95%E8%89%B2%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Natural单色系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 5034");
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=BESCON%E4%B8%89%E8%89%B2%E6%B6%A6%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON三色润彩系列半年抛彩色隐形眼镜1片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr4[] = $res;
        }
        $resArr5 = array();
        foreach($goodsArr5 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 355");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO巨目系列彩色隐形眼镜';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 351");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E4%B8%89%E8%89%B2';
                $res['goods_name'] = 'NEO公主系列三色隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3641");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E5%A5%B3%E7%9A%87%E5%9B%9B%E8%89%B2';
                $res['goods_name'] = 'NEO可视眸女皇四色系列隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3634");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E8%87%AA%E7%84%B6';
                $res['goods_name'] = 'NEO可视眸自然系列隐形眼镜';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3635");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO可视眸巨目系列隐形眼镜';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3062");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%9E%B3%E7%91%B6+NEO+COSMO%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = '瞳瑶 NEO COSMO系列半年抛彩色隐形眼镜2片装';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 5104");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8KEESMO%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'NEO可视眸KEESMO彩色隐形眼镜日抛10片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr5[] = $res;
        }
        $resArr6 = array();
        foreach($goodsArr6 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3949");
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO%E6%83%91%E5%8A%9B%E7%8C%ABHoli+Cat%E7%B3%BB%E5%88%97';
                $res['goods_name'] = 'GEO惑力猫Holi Cat系列彩色隐形眼镜';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3964");
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+Eyes+cream%E7%B3%BB%E5%88%97';
                $res['goods_name'] = 'GEO Eyes cream系列彩色隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3971");
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+Grang+Grang%E7%B3%BB%E5%88%97';
                $res['goods_name'] = 'GEO Grang Grang系列彩色隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 884");
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO%E7%BA%AA%E4%BE%9D%E6%BE%B3+%E5%98%89%E4%B8%BD%E7%A7%80';
                $res['goods_name'] = 'GEO纪依澳 嘉丽秀系列彩色隐形眼镜';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr6[] = $res;
        }

        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
        $smarty->assign('goodsArr5',	$resArr5);
        $smarty->assign('goodsArr6',	$resArr6);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 4 || $pid == 160324)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    // 博士伦
    $goodsArr1 = array(
        array(592,1), array(1035,2), array(4925,1)
    , array(3420,2), array(2296,1), array(595,1)
    );
    // 爱尔康
    $goodsArr2 = array(
        array(585,1), array(4070,2), array(924,1)
    , array(4757,2), array(5163,1), array(632,1)
    );
    // 海昌
    $goodsArr3 = array(
        array(599,2), array(596,1), array(600,2)
    , array(2614,2), array(2824,2), array(3642,2)
    );
    // 卫康
    $goodsArr4 = array(
        array(609,1), array(786,2), array(2867,2)
    , array(4833,1), array(4884,2), array(4203,2)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['shop_price'] . '</del>';
            } else{
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['shop_price'] . '</del>';
            } else{
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['shop_price'] . '</del>';
            } else{
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['shop_price'] . '</del>';
            } else{
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr4[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 160225)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(
        array(757,1), array(107,2), array(112,1), array(101,3,"四盒减88元")
    , array(971,1), array(110,2), array(113,1), array(106,2)
    , array(109,2), array(111,1), array(2118,1), array(969,2)
    , array(4751,1), array(4311,2), array(5090,3,"两盒送润明60ml"), array(5077,3,"两盒送润明60ml")
    );
    $goodsArr2 = array(
        array(1,1), array(2,1), array(3,1), array(4,1)
    , array(5,1), array(6,1), array(7,3,"第二盒半价"), array(8,3,"第二盒半价")
    );
    $goodsArr3 = array(
        array(595,1), array(2191,1), array(1035,2), array(594,2)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = ".$v[0]);
            if($v[1] == 1){
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
            }elseif ($v[1] == 2) {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 950");
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝明眸日抛型彩色隐形眼镜30片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4321");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%98%9F%E6%82%A6%E9%80%B8%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦星悦逸彩系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3155");
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%98%9F%E6%82%A6%E9%80%B8%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦星悦逸彩系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3077");
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%8E%B9%E7%BF%A0%E4%BA%AE%E7%9C%B8%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦莹翠亮眸系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3900");
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%96%B0%E9%94%90%E6%99%B6%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦新锐晶彩系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4004");
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%AC%A3%E8%8E%B9%E7%82%AB%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦欣莹炫彩系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4752");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E7%9D%9B%E7%92%A8%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦睛璨明眸日抛型彩色隐形眼镜10片装';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4977");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E7%9D%9B%E7%92%A8%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦睛璨明眸日抛型彩色隐形眼镜30片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                } elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($v[1]==1){
                if($res['is_promote'] == 1 & $res['promote_end_date'] > $now & $res['promote_start_date'] < $now){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
            }elseif ($v[1] == 2) {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr3[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 160121)
{
	date_default_timezone_set('PRC');
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(
        array(1,1), array(92,1), array(223,1), array(1026,2)
    , array(1645,2), array(227,2), array(1251,2), array(2,1)
    , array(224,1), array(222,1), array(226,1)
    );
    $goodsArr2 = array(
        array(105,1), array(757,1), array(4751,1), array(5090,2)
    , array(104,1), array(101,3,"四盒减88元"), array(103,1), array(970,3,"四盒减16元")
    , array(2118,1), array(1,1), array(2,3,"一付减20"), array(3,1)
    , array(4,3,"一付减20"), array(5,3,"第二盒半价"), array(6,1), array(7,3,"一付减20元")
    , array(3338,1), array(4925,1), array(592,1), array(1035,2)
    );
    $goodsArr3 = array(
        array(767,3,"买三送一"), array(1151,2), array(1045,3,"送护理液"), array(662,3,"买三送一")
    , array(185,3,"送植物精灵"), array(1153,2), array(761,3,"送植物精灵"), array(1152,2)
    );
    $goodsArr4 = array(
        array(119,2), array(117,3,"四盒减24元"), array(1097,3,"四盒减80元"), array(1010,1)
    , array(1,3,"二盒减6元"), array(2686,1), array(589,1), array(5061,1)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id)) {
        $resArr1 = array();
        foreach ($goodsArr1 as $v) {
            if ($v[0] == 1) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 4477");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生安视优define美瞳日抛彩色隐形眼镜30片装';
            } elseif ($v[0] == 2) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 4782");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生安视优define美瞳日抛彩色隐形眼镜5片装';
            } else {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach ($goodsArr2 as $v) {
            if ($v[0] == 1) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 811");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E4%B8%A4%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝明眸两周抛彩色隐形眼镜6片装';
            } elseif ($v[0] == 2) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 972");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%B0%B4%E7%81%B5%E7%84%95%E5%BD%A9%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦水灵焕彩年抛型彩色隐形眼镜';
            } elseif ($v[0] == 3) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 2583");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E7%82%AB%E7%9C%B8%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝炫眸彩色隐形眼镜日抛10片装';
            } elseif ($v[0] == 4) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 4004");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%AC%A3%E8%8E%B9%E7%82%AB%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦欣莹炫彩系列年抛型彩色隐形眼镜';
            } elseif ($v[0] == 5) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 4752");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E7%9D%9B%E7%92%A8%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦睛璨明眸日抛型彩色隐形眼镜10片装';
            } elseif ($v[0] == 6) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 4321");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%98%9F%E6%82%A6%E9%80%B8%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦星悦逸彩系列半年抛彩色隐形眼镜1片装';
            } elseif ($v[0] == 7) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 3155");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%98%9F%E6%82%A6%E9%80%B8%E5%BD%A9%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '博士伦星悦逸彩系列年抛型彩色隐形眼镜';
            } else {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                } elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach ($goodsArr3 as $v) {
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
            }elseif ($v[1] == 2) {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach ($goodsArr4 as $v) {
            if ($v[0] == 1) {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = 1180");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%A7%86%E5%BA%B7%E7%9D%9B%E5%BD%A9%E5%A4%A9%E5%A4%A9%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '视康睛彩天天抛彩色隐形眼镜10片装';
            } else {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr4[] = $res;
        }

        $smarty->assign('goodsArr1', $resArr1);
        $smarty->assign('goodsArr2', $resArr2);
        $smarty->assign('goodsArr3', $resArr3);
        $smarty->assign('goodsArr4', $resArr4);
    }
    $smarty->display('active' . $pid . '.dwt', $cache_id);
    exit;
}
elseif($pid == 151222){
    date_default_timezone_set('PRC');
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr1 = array(
        array(4801,'直降10元') ,array(3039,'直降13元') ,array(773,'直降23元'),array(2403,'直降22元')
    ,array(138,'直降<br/>2元'),array(4807,'直降<br/>3元') ,array(1,'直降10元'),array(2,'直降<br/>8元')
    ,array(4884,'直降32元'),array(609,'买一送一'),array(4973,'直降<br/>2元'),array(4214,'直降13元')
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){

            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,goods_thumb,is_promote,promote_price,market_price,shop_price,original_img FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1204");
                if($res['is_promote'] == 1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降<br />'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['goods_name'] = '卫康绮靓系列年抛型彩色隐形眼镜（5色）';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%AB%E5%BA%B7%E7%BB%AE%E9%9D%93%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,goods_thumb,is_promote,promote_price,market_price,shop_price,original_img FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1209");
                if($res['is_promote'] == 1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降<br />'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['goods_name'] = '卫康清丽系列年抛型彩色隐形眼镜';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%AB%E5%BA%B7%E6%B8%85%E4%B8%BD%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,goods_thumb,is_promote,promote_price,market_price,shop_price,original_img FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
                if($res['is_promote'] == 1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降<br />'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }else{
                    $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'].'折';
                    $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
                }
                $res['href'] = 'goods'.$res['goods_id'].'.html';
            }
            $resArr1[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 151225)
{
    date_default_timezone_set('PRC');
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr1 = array(
        array(1645,1) ,array(227,1) ,array(93,1)
    ,array(1251,2),array(4782,1),array(224,1)
    ,array(222,1),array(226,1)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1 && $v[1]==1){
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
            }else{
                $res['zk'] = number_format($res['shop_price']/$res['market_price'],2)*10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'].'折';
                $res['fomart_price'] = '市场价<del>￥'.$res['market_price'].'</del>';
            }
            $res['href'] = 'goods'.$res['goods_id'].'.html';

            $resArr1[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 131129)
{
	date_default_timezone_set('PRC');
	if (time() < 1385654400) {
		$smarty->assign('could_huan', 1);
	}
}elseif ($pid == 131221){
	//活动截止时间
	$active_time_begin = strtotime('2013-12-20 11:00:00');
	$active_time_end = strtotime('2013-12-31 23:59:59');

	//当天活动开始和结束时间
	$curactive_time_begin = strtotime(date('Y-m-d 11:00:00'));
	$curactive_time_end = strtotime(date('Y-m-d 23:59:59'));
	//当前时间
	$curtime = strtotime(date('Y-m-d H:i:s'));
	//当天起始时间
	$curstartday = strtotime(date('Y-m-d 00:00:00'));
	
	$curtime =  $_SERVER['REQUEST_TIME'];
	$robinfo  = array('rob_code'=>0, 'rob_msg'=>''); //领取结果 0表示失败, 1表示领取成功。

	if($curactive_time_begin >= $active_time_begin && $curactive_time_end <= $active_time_end ){//判断当天活动是否在活动始终时间内
		
		if($curtime >= $curactive_time_begin && $curtime <= $curactive_time_end){//判断当前时间是否在当前活动时间内
			$rob_go = 1;//立即抢购
			//判断是否抢完
			$robsql = "select * from ecs_wdcoupon where coupon_status = 0  ";
			$robres = $GLOBALS['db']->GetAll($robsql);
			if(empty($robres)){
				$rob_go = 3;//明天再抢！
			}else{
				$robnum = count($robres);	
			}

		}else if($curtime < $curactive_time_begin && $curtime > $curstartday){
			$rob_go = 0;//等待开始！	
		}
	}else{
		$rob_go = 0;//活动尚未开始！！！
	}
	
	$smarty->assign('rob_go',$rob_go);
	$smarty->assign('robnum',$robnum);
	
	$mb_ck    = 0;
	$email_ck = 0;
	if($user_id > 0)
	{
		$user_info = $GLOBALS['db']->GetRow("select * from ecs_users where user_id=".$user_id." limit 1;");
		$mb_ck     = intval($user_info['mobile_ck']);
		$email_ck  = intval($user_info['email_ck']);
		$smarty->assign('user_info',    $user_info);

		//邮箱跳转
		$email = strtolower(trim($user_info['email']));
		if(!empty($email))
		{
			$emarr = explode("@", $email);
			$to_url= "http://mail.".$emarr[1];
			//print_r($to_url);
			$smarty->assign('to_url',    $to_url);
		}
		
	}
	$smarty->assign('mb_ck',    $mb_ck);
	$smarty->assign('email_ck', $email_ck);

}

elseif ($pid == 131227)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (2570,835,2573,855,2405,141,2571,153,148,2572,166,2575)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (987,2104,814,2897,2895,2896,2336,2338,2702,2700,214,1006)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (991,990,989,988)");
	
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}
elseif ($pid == 140310)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (221,3001,2583,223,1180,981,662,225,2584,2104,2059,982,834,2999,2927,2757,2406,857,1251,1185)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (987,2104,814,2897,2895,2896,2336,2338,2702,2700,214,1006)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (991,990,989,988)");
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}
elseif ($pid == 140401)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (221,3001,2583,223,1180,981,662,225,2584,2104,2059,982,834,2999,2927,2757,2406,857,1251,1185)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (987,2104,814,2897,2895,2896,2336,2338,2702,2700,214,1006)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (991,990,989,988)");
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}
elseif ($pid == 140211)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (981,812,811,948,949,950,2581,2582,2583,2584,982,813,947,2879,2880,2881,2878,983)");
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
}
//财付通活动
elseif ($pid == 131228)
{
	//判断是否用户已领取红包
	$sql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$user_id' and bonus_type_id IN (1098,1099,1100,1101,1102)";
	$quan = $GLOBALS['db']->getAll($sql3);
	if (count($quan) > 0)
	{
		$smarty->assign('have_bonus',  1);
	}
}
elseif ($pid == 140317)
{	
}
elseif ($pid == 140311)
{
		
		//user_id = 0 是手动添加的中奖信息
		$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='女人节抽奖奖品' AND (add_time > 0 OR user_id = 0) ORDER BY rec_id DESC LIMIT 50");
		$prize_arr = array();
		foreach ($prize_list as $key => $v)
		{
			$prize_arr[$key]['user_name'] = $v['user_name'];
			$prize_arr[$key]['prize_rank'] = get_prize_content_140311($v['prize_rank']);
		}
		$smarty->assign('prize_arr',	$prize_arr);
	
}
elseif($pid == 140314){
	$has_act = $GLOBALS['db']->getAll("SELECT rec_id FROM ecs_tenpay_active WHERE active_status in (2998,2999,3000,3001)"); //此用户是否已参与
	//echo count($has_act);

	$goods_2998   = get_goods_info(2998);
	$goods_2999   = get_goods_info(2999);
	$goods_3000    = get_goods_info(3000);
	$goods_3001   = get_goods_info(3001);
	
	$goodsds_2998   = get_goods_ds(2998);
	$goodsds_2999   = get_goods_ds(2999);
	$goodsds_3000    = get_goods_ds(3000);
	$goodsds_3001   = get_goods_ds(3001);
	
	$smarty->assign('goods_2998', $goods_2998);
	$smarty->assign('goods_2999', $goods_2999);
	$smarty->assign('goods_3000',  $goods_3000);
	$smarty->assign('goods_3001', $goods_3001);
	
	$smarty->assign('goodsds_2998', $goodsds_2998);
	$smarty->assign('goodsds_2999', $goodsds_2999);
	$smarty->assign('goodsds_3000',  $goodsds_3000);
	$smarty->assign('goodsds_3001', $goodsds_3001);
}
elseif($pid == 14031401){
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$status = isset($_REQUEST['status'])? $_REQUEST['status']: '';
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE active_status in (2998,2999,3000,3001) and user_id=$user_id"); //此用户是否已参与
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['	city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				$time = date("Y-m-d H:i:s");
				if ($status == 2998){
					$active_status = 9;//娃娃黑日抛型
				}else if($status == 2999){
					$active_status = 8;//混血灰日抛型
				}else if($status == 3000){
					$active_status = 7;//蜜糖棕日抛型
				}else if($status == 3001){
					$active_status = 6;//巧克力色日抛型
				}

				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds,add_time,active_status) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds','$time','$status')";
					$ret = mysql_query($sql_tenpay);
					
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
	$smarty->assign('goodid',  $goodid);
}
elseif($pid == 14031402){
	
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$status = isset($_REQUEST['status'])? $_REQUEST['status']: '';
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE active_status in (2998,2999,3000,3001) and user_id=$user_id"); //此用户是否已参与
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				$time = date("Y-m-d H:i:s");
				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds,add_time,active_status) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds','$time','$status')";
					$ret = mysql_query($sql_tenpay);
					
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
	$smarty->assign('goodid',  $goodid);
}
elseif($pid == 14031403){
	
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$status = isset($_REQUEST['status'])? $_REQUEST['status']: '';
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE active_status in (2998,2999,3000,3001) and user_id=$user_id"); //此用户是否已参与
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				$time = date("Y-m-d H:i:s");

				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds,add_time,active_status) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds','$time','$status')";
					$ret = mysql_query($sql_tenpay);
					
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
	$smarty->assign('goodid',  $goodid);
}
elseif($pid == 14031404){
	
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$status = isset($_REQUEST['status'])? $_REQUEST['status']: '';
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE active_status in (2998,2999,3000,3001) and user_id=$user_id"); //此用户是否已参与
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				$time = date("Y-m-d H:i:s");

				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds,add_time,active_status) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds','$time','$status')";
					$ret = mysql_query($sql_tenpay);
					
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
	$smarty->assign('goodid',  $goodid);
}
elseif ($pid == 14031101)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_prize WHERE refer='女人节抽奖奖品' and  user_id=$user_id"); //此用户是否已参与
	
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				
				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds')";
					$ret = mysql_query($sql_tenpay);
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
}
elseif ($pid == 13122801)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE user_id=$user_id"); //此用户是否已参与
	
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				
				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds')";
					$ret = mysql_query($sql_tenpay);
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
}

elseif ($pid == 14090201)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE user_id=$user_id AND add_time > '2014-09-02 00:00:00' "); //此用户是否已参与
	//$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE user_id=$user_id");
	
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$qq = isset($_REQUEST['qq'])? addslashes($_REQUEST['qq']): '';
				$yselect_ds = isset($_REQUEST['yselect'])? addslashes($_REQUEST['yselect']): '';
				$zselect_ds = isset($_REQUEST['zselect'])? addslashes($_REQUEST['yselect']): '';
				$ds = $yselect_ds . ',' . $zselect_ds;
				
				if (!empty($email) && !empty($tel) && !empty($username) && !empty($yselect_ds) && !empty($zselect_ds))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, qq, ds) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$qq', '$ds')";
					$ret = mysql_query($sql_tenpay);
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
}

//140108财付通活动
elseif ($pid == 140108)
{
	
}
elseif ($pid == 14010801)
{
	$act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_tenpay_active WHERE user_id=$user_id and active_status = 1"); //此用户是否已参与
	
	if ( ! empty($act))
	{
		if ($user_id > 0)
		{
			if (empty($has_act))
			{
				//未参加抽奖
				$smarty->assign('submit_personal_info',  1); //已提交个人信息
				$province = isset($_REQUEST['province'])? intval($_REQUEST['province']): '0';
				$city = isset($_REQUEST['city'])? intval($_REQUEST['city']): '0';
				$district = isset($_REQUEST['district'])? intval($_REQUEST['district']): '0';
				$address = isset($_REQUEST['address'])? addslashes($_REQUEST['address']): '';
				$postcode = isset($_REQUEST['postcode'])? intval($_REQUEST['postcode']): '0';
				$username = isset($_REQUEST['username'])? addslashes($_REQUEST['username']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
				$ds =  '平光,平光' ;
				if (!empty($email) && !empty($tel) && !empty($username))
				{
					$sql_tenpay = "insert into ecs_tenpay_active (user_id, province, city, district, address, postcode, username, email, tel, ds , active_status) values 
							('$user_id', '$province', '$city', '$district', '$address', '$postcode', '$username', '$email', '$tel', '$ds' , '1')";
					$ret = mysql_query($sql_tenpay);
				}
			}
		}
	}
	else
	{
		$smarty->assign('country_list',  get_regions());
		$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
		$smarty->assign('city_list',     $city_list);
		$smarty->assign('district_list', $district_list);
	}
	
	$smarty->assign('has_act',  $has_act);
}elseif($pid == 131226){

	//结束时间
	$end_time = strtotime(date('2014-02-01 00:00:00'));
	//当前时间
	$curtime =  $_SERVER['REQUEST_TIME'];
	if($curtime > $end_time){
		$changepic = 1;
	}
	$smarty->assign('changepic',$changepic);//12元商品 到期
	//是否下单
	$orderid = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE user_id = ".$user_id." AND  (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5)))");
	
	if(!empty($orderid)){
		$ordertype = 1 ;//下单成功
	}else{
		$ordertype = 2 ;//没有下单
	}
	
	//是否领取过彩票
	$cpres = $GLOBALS['db']->GetRow("select * from ecs_cpcoupon where cp_status = 1 and user_id = '".$user_id."'");
	if(!empty($cpres)){
		$cptype = 2;//领取过
	}else{
		$cptype = 1;//没有领取过
	}
	$smarty->assign('cptype',$cptype);//是否领取彩票
	$smarty->assign('ordertype',$ordertype);//是否下单
	
	
	//是否已授权微博
	if($user_id > 0)
	{
		date_default_timezone_set('PRC'); 
		//获取用户绑定的应用
		$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
		$sync =  $GLOBALS['db']->getAll($sql_sync);
		$user_sync = array();
		if ($sync) {
			foreach ($sync as $k => $v) {
				$user_sync[$v['app_name']]['sid'] = $v['sid'];
				$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
				$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
				$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
				$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
				$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
				$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
				$user_sync[$v['app_name']]['sign_date'] = intval((time()-$v['add_time'])/86400); //上次签名至今的天数(判断签名是否还有效)
			}
		}
		//print_r($user_sync);
		$qq_sync = $user_sync['qq'];
		$sina_sync = $user_sync['sina'];
		
		$smarty->assign('qq_sync',		$qq_sync);
		$smarty->assign('sina_sync',	$sina_sync);
		$smarty->assign('user_id',		$user_id);
	}
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='招商20131226专享抽奖' and prize_rank in(1,2,3,5) ORDER BY rec_id DESC LIMIT 50");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['order_sn'] = $v['extension_id'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_0107($v['prize_rank']);
	}
	$smarty->assign('prize_arr',	$prize_arr);

}
elseif($pid == 140110){
	$s_time = strtotime('2014-01-10 00:00:00');
	$e_time = strtotime('2014-02-07 23:59:59');

	//是否下单
	$orderid = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE user_id = ".$user_id." AND add_time > " .$s_time. " AND add_time < " .$e_time ." AND  (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5)))");
	
	if(!empty($orderid)){
		$ordertype = 1 ;//下单成功
	}else{
		$ordertype = 2 ;//没有下单
	}
	$countcp = $GLOBALS['db']->GetRow("select * from ecs_cpcoupon where cp_status = 1 ");
	$countnumber = count($countcp);
	echo $countnumber;
	//是否领取过彩票
	$cpres = $GLOBALS['db']->GetRow("select * from ecs_cpcoupon where cp_status = 1 and user_id = '".$user_id."'");
	if(!empty($cpres)){
		$cptype = 2;//领取过
	}else{
		$cptype = 1;//没有领取过
	}
	$smarty->assign('cptype',$cptype);//是否领取彩票
	$smarty->assign('ordertype',$ordertype);//是否下单
	
	
	//是否已授权微博
	if($user_id > 0)
	{
		date_default_timezone_set('PRC'); 
		//获取用户绑定的应用
		$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
		$sync =  $GLOBALS['db']->getAll($sql_sync);
		$user_sync = array();
		if ($sync) {
			foreach ($sync as $k => $v) {
				$user_sync[$v['app_name']]['sid'] = $v['sid'];
				$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
				$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
				$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
				$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
				$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
				$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
				$user_sync[$v['app_name']]['sign_date'] = intval((time()-$v['add_time'])/86400); //上次签名至今的天数(判断签名是否还有效)
			}
		}
		//print_r($user_sync);
		$qq_sync = $user_sync['qq'];
		$sina_sync = $user_sync['sina'];
		
		$smarty->assign('qq_sync',		$qq_sync);
		$smarty->assign('sina_sync',	$sina_sync);
		$smarty->assign('user_id',		$user_id);
	}
	

}elseif($pid == 140113){
	$s_time = strtotime('2014-01-10 00:00:00');
	$e_time = strtotime('2014-02-07 23:59:59');
	//是否下单
	$orderid = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE user_id = ".$user_id." AND add_time > " .$s_time. " AND add_time < " .$e_time ." AND  (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5)))");
	if(!empty($orderid)){
		$ordertype = 1 ;//下单成功
	}else{
		$ordertype = 2 ;//没有下单
	}
	//是否领取过彩票
	$cpres = $GLOBALS['db']->GetRow("select * from ecs_cpcoupon where cp_status = 1 and user_id = '".$user_id."'");
	if(!empty($cpres)){
		$cptype = 2;//领取过
	}else{
		$cptype = 1;//没有领取过
	}
	
	$smarty->assign('cptype',$cptype);//是否领取彩票
	$smarty->assign('ordertype',$ordertype);//是否下单
	
	
	//是否已授权微博
	if($user_id > 0)
	{
		date_default_timezone_set('PRC'); 
		//获取用户绑定的应用
		$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
		$sync =  $GLOBALS['db']->getAll($sql_sync);
		$user_sync = array();
		if ($sync) {
			foreach ($sync as $k => $v) {
				$user_sync[$v['app_name']]['sid'] = $v['sid'];
				$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
				$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
				$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
				$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
				$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
				$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
				$user_sync[$v['app_name']]['sign_date'] = intval((time()-$v['add_time'])/86400); //上次签名至今的天数(判断签名是否还有效)
			}
		}
		$qq_sync = $user_sync['qq'];
		$sina_sync = $user_sync['sina'];
		
		$smarty->assign('qq_sync',		$qq_sync);
		$smarty->assign('sina_sync',	$sina_sync);
		$smarty->assign('user_id',		$user_id);
	}
	

}elseif($pid == 140326){
	$mb_ck    = 0;
	$email_ck = 0;
	if($user_id > 0)
	{
		$user_info = $GLOBALS['db']->GetRow("select * from ecs_users where user_id=".$user_id."  limit 1;");
		$mb_ck     = intval($user_info['mobile_ck']);
		$email_ck  = intval($user_info['email_ck']);
		$smarty->assign('user_info',    $user_info);

		//邮箱跳转
		$email = strtolower(trim($user_info['email']));
		if(!empty($email))
		{
			$emarr = explode("@", $email);
			$to_url= "http://mail.".$emarr[1];
			//print_r($to_url);
			$smarty->assign('to_url',    $to_url);
		}

		include_once(ROOT_PATH .'includes/lib_clips.php');
		include_once(ROOT_PATH .'includes/lib_transaction.php');

		if($rank = get_rank_info())
		{
			$smarty->assign('rank_name', $rank['rank_name']);//用户等级
			if(!empty($rank['next_rank_name']))
			{
				$smarty->assign('next_rank_point', $rank['next_rank']);
				$smarty->assign('next_rank_name',  $rank['next_rank_name']);
			}
		}
		$smarty->assign('info',        get_user_default($user_id));
		$smarty->assign('user_notice', $_CFG['user_notice']);
		$smarty->assign('prompt',      get_user_prompt($user_id));
		
	}
	$smarty->assign('mb_ck',    $mb_ck);
	$smarty->assign('email_ck', $email_ck);
}
elseif($pid == 140402){
	
	$mb_ck    = 0;
	$email_ck = 0;
	if($user_id > 0)
	{
		$user_info = $GLOBALS['db']->GetRow("select * from ecs_users where user_id=".$user_id."  limit 1;");
		$mb_ck     = intval($user_info['mobile_ck']);
		$email_ck  = intval($user_info['email_ck']);
		$smarty->assign('user_info',    $user_info);

		//邮箱跳转
		$email = strtolower(trim($user_info['email']));
		if(!empty($email))
		{
			$emarr = explode("@", $email);
			$to_url= "http://mail.".$emarr[1];
			//print_r($to_url);
			$smarty->assign('to_url',    $to_url);
		}

		include_once(ROOT_PATH .'includes/lib_clips.php');
		include_once(ROOT_PATH .'includes/lib_transaction.php');

		if($rank = get_rank_info())
		{
			$smarty->assign('rank_name', $rank['rank_name']);//用户等级
			if(!empty($rank['next_rank_name']))
			{
				$smarty->assign('next_rank_point', $rank['next_rank']);
				$smarty->assign('next_rank_name',  $rank['next_rank_name']);
			}
		}
		$smarty->assign('info',        get_user_default($user_id));
		$smarty->assign('user_notice', $_CFG['user_notice']);
		$smarty->assign('prompt',      get_user_prompt($user_id));
		
	}
	$smarty->assign('mb_ck',    $mb_ck);
	$smarty->assign('email_ck', $email_ck);
}
elseif($pid == 140115){
	$s_time = strtotime('2014-01-10 00:00:00');
	$e_time = strtotime('2014-02-07 23:59:59');
	//是否下单
	$orderid = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE user_id = ".$user_id." AND add_time > " .$s_time. " AND add_time < " .$e_time ." AND  (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5)))");
	if(!empty($orderid)){
		$ordertype = 1 ;//下单成功
	}else{
		$ordertype = 2 ;//没有下单
	}
	//是否领取过彩票
	$cpres = $GLOBALS['db']->GetRow("select * from ecs_cpcoupon where cp_status = 1 and user_id = '".$user_id."'");
	if(!empty($cpres)){
		$cptype = 2;//领取过
	}else{
		$cptype = 1;//没有领取过
	}
	
	$smarty->assign('cptype',$cptype);//是否领取彩票
	$smarty->assign('ordertype',$ordertype);//是否下单
	
	
	//是否已授权微博
	if($user_id > 0)
	{
		date_default_timezone_set('PRC'); 
		//获取用户绑定的应用
		$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
		$sync =  $GLOBALS['db']->getAll($sql_sync);
		$user_sync = array();
		if ($sync) {
			foreach ($sync as $k => $v) {
				$user_sync[$v['app_name']]['sid'] = $v['sid'];
				$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
				$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
				$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
				$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
				$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
				$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
				$user_sync[$v['app_name']]['sign_date'] = intval((time()-$v['add_time'])/86400); //上次签名至今的天数(判断签名是否还有效)
			}
		}
		$qq_sync = $user_sync['qq'];
		$sina_sync = $user_sync['sina'];
		
		$smarty->assign('qq_sync',		$qq_sync);
		$smarty->assign('sina_sync',	$sina_sync);
		$smarty->assign('user_id',		$user_id);
	}
	

}elseif($pid == 140107){
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='财付通20140107专享抽奖' and prize_rank in(1,2,3,5) ORDER BY rec_id DESC LIMIT 50");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['order_sn'] = $v['extension_id'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_0107($v['prize_rank']);
	}
	$smarty->assign('prize_arr',	$prize_arr);
}
//周年庆活动子页面：
/*elseif(13072905 == $pid OR 13072908 == $pid)
{
	if($_SESSION['user_id'] > 0) 
	{
		$smarty->assign('user_id',	$_SESSION['user_id']);
		
		//查询用户的订单是否满足投票条件
		date_default_timezone_set('PRC');
		$b_time = strtotime('2013-08-01 00:00:00');
		$e_time = strtotime('2013-08-15 23:59:59');
		
		$vote_sql = "SELECT b.goods_id, b.goods_number, b.goods_price FROM ecs_order_info a LEFT JOIN ecs_order_goods b ON a.order_id = b.order_id 
					WHERE a.user_id = ".$_SESSION['user_id']." AND a.add_time >= ".$b_time." AND a.add_time <= ".$e_time." 
					AND (a.pay_status=2 OR (a.pay_id=3 AND (a.order_status=1 OR a.order_status=5)))";
		$vote_rs = $GLOBALS['db']->getAll($vote_sql);
		
		$can_vote = 0;
		$active_goods = array(1156, 1155, 1154, 1153, 1152, 1151, 1150, 1149, 1045, 1145, 1144, 2608, 2607, 2606, 1189, 1188, 1187, 1186, 1185, 1184, 1147, 1146);
		$sum_goods_amount = 0;
		foreach ($vote_rs as $item)
		{
			if (in_array($item['goods_id'], $active_goods)) 
			{
				$can_vote = 1;
				break;
			}
			$sum_goods_amount += $item['goods_number'] * $item['goods_price'];
		}
		if (intval($sum_goods_amount) >= 150) {
			$can_vote = 1;
		}
		
		$smarty->assign('can_vote',	$can_vote);
	}
}*/
elseif($pid == 140507)
{
	$user_rank = (isset($_SESSION['user_rank']) && $_SESSION['user_rank']>0)? intval($_SESSION['user_rank']): 0;
	$smarty->assign('user_rank', $user_rank);
	
	$now = time();
	//$d = date('d', $now);
	
	if ($now >= strtotime('2014-05-14 10:00:00') && $now < strtotime('2014-05-14 11:00:00')) {
		$smarty->assign('ms_a', 0); //8日上午秒杀进行中
	} elseif ($now < strtotime('2014-05-14 10:00:00')) {
		$smarty->assign('ms_a', -1); //8日上午秒杀未开始
	} else {
		$smarty->assign('ms_a', 1); //8日上午秒杀已结束
	}
	
	if ($now >= strtotime('2014-05-14 14:00:00') && $now < strtotime('2014-05-14 15:00:00')) {
		$smarty->assign('ms_b', 0); //8日下午秒杀进行中
	} elseif ($now < strtotime('2014-05-14 14:00:00')) {
		$smarty->assign('ms_b', -1); //8日下午秒杀未开始
	} else {
		$smarty->assign('ms_b', 1); //8日下午秒杀已结束
	}
}

elseif($pid == 140508)
{
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (1045,97,118,140)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (225,2879,896,946)");
	$goods_list_kj = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (1389,1317,2289,2948)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (1068,2958,924,3338)");
	
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_kj', $goods_list_kj);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}

elseif($pid == 140612)
{
	$now = time();
	
	if ($now >= strtotime('2014-06-17 10:00:00') && $now < strtotime('2014-06-17 11:00:00')) {
		$smarty->assign('ms_617', 0); //秒杀进行中
	} elseif ($now < strtotime('2014-06-17 10:00:00')) {
		$smarty->assign('ms_617', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_617', 1); //秒杀已结束
	}
	
	if ($now >= strtotime('2014-06-18 10:00:00') && $now < strtotime('2014-06-18 11:00:00')) {
		$smarty->assign('ms_618', 0);
	} elseif ($now < strtotime('2014-06-18 10:00:00')) {
		$smarty->assign('ms_618', -1);
	} else {
		$smarty->assign('ms_618', 1);
	}
	
	//6.19
	if ($now >= strtotime('2014-06-19 10:00:00') && $now < strtotime('2014-06-19 12:00:00')) {
		$smarty->assign('ms_619_1', 0);
	} elseif ($now < strtotime('2014-06-19 10:00:00')) {
		$smarty->assign('ms_619_1', -1);
	} else {
		$smarty->assign('ms_619_1', 1);
	}
	if ($now >= strtotime('2014-06-19 14:00:00') && $now < strtotime('2014-06-19 16:00:00')) {
		$smarty->assign('ms_619_2', 0);
	} elseif ($now < strtotime('2014-06-19 14:00:00')) {
		$smarty->assign('ms_619_2', -1);
	} else {
		$smarty->assign('ms_619_2', 1);
	}
	if ($now >= strtotime('2014-06-19 20:00:00') && $now < strtotime('2014-06-19 22:00:00')) {
		$smarty->assign('ms_619_3', 0);
	} elseif ($now < strtotime('2014-06-19 20:00:00')) {
		$smarty->assign('ms_619_3', -1);
	} else {
		$smarty->assign('ms_619_3', 1);
	}
	
	//6.20
	if ($now >= strtotime('2014-06-20 10:00:00') && $now < strtotime('2014-06-20 12:00:00')) {
		$smarty->assign('ms_620_1', 0);
	} elseif ($now < strtotime('2014-06-20 10:00:00')) {
		$smarty->assign('ms_620_1', -1);
	} else {
		$smarty->assign('ms_620_1', 1);
	}
	if ($now >= strtotime('2014-06-20 14:00:00') && $now < strtotime('2014-06-20 16:00:00')) {
		$smarty->assign('ms_620_2', 0);
	} elseif ($now < strtotime('2014-06-20 14:00:00')) {
		$smarty->assign('ms_620_2', -1);
	} else {
		$smarty->assign('ms_620_2', 1);
	}
	if ($now >= strtotime('2014-06-20 20:00:00') && $now < strtotime('2014-06-20 22:00:00')) {
		$smarty->assign('ms_620_3', 0);
	} elseif ($now < strtotime('2014-06-20 20:00:00')) {
		$smarty->assign('ms_620_3', -1);
	} else {
		$smarty->assign('ms_620_3', 1);
	}
}

elseif($pid == 140624)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (835,2572,118,105)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (2608,1177,890,241)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (580,2280,924,2786)");
	
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}

elseif($pid == 140627)
{
	date_default_timezone_set('PRC');
	$goods_list_tmp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (105,118,835,1045)");
	$goods_list_cp = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (2700,2608,896,3634)");
	$goods_list_hly = $GLOBALS['db']->GetAll("SELECT goods_id, goods_name, goods_name_desc, goods_brief, click_count, market_price, shop_price, original_img FROM ecs_goods WHERE goods_id IN (580,924,2280,2786)");
	
	$smarty->assign('goods_list_tmp', $goods_list_tmp);
	$smarty->assign('goods_list_cp', $goods_list_cp);
	$smarty->assign('goods_list_hly', $goods_list_hly);
}

elseif($pid == 140731)
{
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='易视网4周年大抽奖' ORDER BY rec_id DESC LIMIT 10");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_140731($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}

elseif($pid == 140801)
{
	$now = time();
	
	if ($now >= strtotime('2014-08-20 11:00:00') && $now <= strtotime('2014-08-20 11:04:00'))
	{
		$smarty->assign('ms1_now', 1);
	}
	if ($now >= strtotime('2014-08-20 16:00:00') && $now <= strtotime('2014-08-20 16:04:00'))
	{
		$smarty->assign('ms2_now', 1);
	}
}

elseif($pid == 140930)
{
	$now = time();
	$t1_1 = 1;
	$t1_2 = 1;
	$t1_3 = 1;
	$t2_1 = 1;
	$t2_2 = 1;
	$t2_3 = 1;
	$t3_1 = 1;
	$t3_2 = 1;
	$t3_3 = 1;
	$t4_1 = 1;
	$t4_2 = 1;
	$t4_3 = 1;
	$t5_1 = 1;
	$t5_2 = 1;
	$t5_3 = 1;
	$t6_1 = 1;
	$t6_2 = 1;
	$t6_3 = 1;
	$t7_1 = 1;
	$t7_2 = 1;
	$t7_3 = 1;
	$t8_1 = 1;
	$t8_2 = 1;
	$t8_3 = 1;
	
	if ($now >= strtotime('2014-10-01 11:00:00') && $now <= strtotime('2014-10-01 11:02:00')) {
		$t1_1 = 0;
	} elseif ($now <= strtotime('2014-10-01 11:00:00')) {
		$t1_1 = -1;
	}
	if ($now >= strtotime('2014-10-01 16:00:00') && $now <= strtotime('2014-10-01 16:02:00')) {
		$t1_2 = 0;
	} elseif ($now <= strtotime('2014-10-01 16:00:00')) {
		$t1_2 = -1;
	}
	if ($now >= strtotime('2014-10-01 20:00:00') && $now <= strtotime('2014-10-01 20:02:00')) {
		$t1_3 = 0;
	} elseif ($now <= strtotime('2014-10-01 20:00:00')) {
		$t1_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-02 11:00:00') && $now <= strtotime('2014-10-02 11:02:00')) {
		$t2_1 = 0;
	} elseif ($now <= strtotime('2014-10-02 11:00:00')) {
		$t2_1 = -1;
	}
	if ($now >= strtotime('2014-10-02 16:00:00') && $now <= strtotime('2014-10-02 16:02:00')) {
		$t2_2 = 0;
	} elseif ($now <= strtotime('2014-10-02 16:00:00')) {
		$t2_2 = -1;
	}
	if ($now >= strtotime('2014-10-02 20:00:00') && $now <= strtotime('2014-10-02 20:02:00')) {
		$t2_3 = 0;
	} elseif ($now <= strtotime('2014-10-02 20:00:00')) {
		$t2_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-03 11:00:00') && $now <= strtotime('2014-10-03 11:02:00')) {
		$t3_1 = 0;
	} elseif ($now <= strtotime('2014-10-03 11:00:00')) {
		$t3_1 = -1;
	}
	if ($now >= strtotime('2014-10-03 16:00:00') && $now <= strtotime('2014-10-03 16:02:00')) {
		$t3_2 = 0;
	} elseif ($now <= strtotime('2014-10-03 16:00:00')) {
		$t3_2 = -1;
	}
	if ($now >= strtotime('2014-10-03 20:00:00') && $now <= strtotime('2014-10-03 20:02:00')) {
		$t3_3 = 0;
	} elseif ($now <= strtotime('2014-10-03 20:00:00')) {
		$t3_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-04 11:00:00') && $now <= strtotime('2014-10-04 11:02:00')) {
		$t4_1 = 0;
	} elseif ($now <= strtotime('2014-10-04 11:00:00')) {
		$t4_1 = -1;
	}
	if ($now >= strtotime('2014-10-04 16:00:00') && $now <= strtotime('2014-10-04 16:02:00')) {
		$t4_2 = 0;
	} elseif ($now <= strtotime('2014-10-04 16:00:00')) {
		$t4_2 = -1;
	}
	if ($now >= strtotime('2014-10-04 20:00:00') && $now <= strtotime('2014-10-04 20:02:00')) {
		$t4_3 = 0;
	} elseif ($now <= strtotime('2014-10-04 20:00:00')) {
		$t4_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-05 11:00:00') && $now <= strtotime('2014-10-05 11:02:00')) {
		$t5_1 = 0;
	} elseif ($now <= strtotime('2014-10-05 11:00:00')) {
		$t5_1 = -1;
	}
	if ($now >= strtotime('2014-10-05 16:00:00') && $now <= strtotime('2014-10-05 16:02:00')) {
		$t5_2 = 0;
	} elseif ($now <= strtotime('2014-10-05 16:00:00')) {
		$t5_2 = -1;
	}
	if ($now >= strtotime('2014-10-05 20:00:00') && $now <= strtotime('2014-10-05 20:02:00')) {
		$t5_3 = 0;
	} elseif ($now <= strtotime('2014-10-05 20:00:00')) {
		$t5_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-06 11:00:00') && $now <= strtotime('2014-10-06 11:02:00')) {
		$t6_1 = 0;
	} elseif ($now <= strtotime('2014-10-06 11:00:00')) {
		$t6_1 = -1;
	}
	if ($now >= strtotime('2014-10-06 16:00:00') && $now <= strtotime('2014-10-06 16:02:00')) {
		$t6_2 = 0;
	} elseif ($now <= strtotime('2014-10-06 16:00:00')) {
		$t6_2 = -1;
	}
	if ($now >= strtotime('2014-10-06 20:00:00') && $now <= strtotime('2014-10-06 20:02:00')) {
		$t6_3 = 0;
	} elseif ($now <= strtotime('2014-10-06 20:00:00')) {
		$t6_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-07 11:00:00') && $now <= strtotime('2014-10-07 11:02:00')) {
		$t7_1 = 0;
	} elseif ($now <= strtotime('2014-10-07 11:00:00')) {
		$t7_1 = -1;
	}
	if ($now >= strtotime('2014-10-07 16:00:00') && $now <= strtotime('2014-10-07 16:02:00')) {
		$t7_2 = 0;
	} elseif ($now <= strtotime('2014-10-07 16:00:00')) {
		$t7_2 = -1;
	}
	if ($now >= strtotime('2014-10-07 20:00:00') && $now <= strtotime('2014-10-07 20:02:00')) {
		$t7_3 = 0;
	} elseif ($now <= strtotime('2014-10-07 20:00:00')) {
		$t7_3 = -1;
	}
	
	if ($now >= strtotime('2014-10-08 11:00:00') && $now <= strtotime('2014-10-08 11:02:00')) {
		$t8_1 = 0;
	} elseif ($now <= strtotime('2014-10-08 11:00:00')) {
		$t8_1 = -1;
	}
	if ($now >= strtotime('2014-10-08 16:00:00') && $now <= strtotime('2014-10-08 16:02:00')) {
		$t8_2 = 0;
	} elseif ($now <= strtotime('2014-10-08 16:00:00')) {
		$t8_2 = -1;
	}
	if ($now >= strtotime('2014-10-08 20:00:00') && $now <= strtotime('2014-10-08 20:02:00')) {
		$t8_3 = 0;
	} elseif ($now <= strtotime('2014-10-08 20:00:00')) {
		$t8_3 = -1;
	}
	
	$smarty->assign('t1_1', $t1_1);
	$smarty->assign('t1_2', $t1_2);
	$smarty->assign('t1_3', $t1_3);
	$smarty->assign('t2_1', $t2_1);
	$smarty->assign('t2_2', $t2_2);
	$smarty->assign('t2_3', $t2_3);
	$smarty->assign('t3_1', $t3_1);
	$smarty->assign('t3_2', $t3_2);
	$smarty->assign('t3_3', $t3_3);
	$smarty->assign('t4_1', $t4_1);
	$smarty->assign('t4_2', $t4_2);
	$smarty->assign('t4_3', $t4_3);
	$smarty->assign('t5_1', $t5_1);
	$smarty->assign('t5_2', $t5_2);
	$smarty->assign('t5_3', $t5_3);
	$smarty->assign('t6_1', $t6_1);
	$smarty->assign('t6_2', $t6_2);
	$smarty->assign('t6_3', $t6_3);
	$smarty->assign('t7_1', $t7_1);
	$smarty->assign('t7_2', $t7_2);
	$smarty->assign('t7_3', $t7_3);
	$smarty->assign('t8_1', $t8_1);
	$smarty->assign('t8_2', $t8_2);
	$smarty->assign('t8_3', $t8_3);
}

elseif ($pid == 141103)
{
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='2014双11预热抽奖' ORDER BY rec_id DESC LIMIT 80");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_141103($v['prize_rank']);
	}
	
	$smarty->assign('prize_arr',	$prize_arr);
}
//感恩节
elseif ($pid == 141118)
{
	$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_prize WHERE refer='2014感恩节抽奖' and extension ='order_id' ORDER BY rec_id DESC LIMIT 80");
	$prize_arr = array();
	foreach ($prize_list as $key => $v)
	{
		$prize_arr[$key]['user_name'] = $v['user_name'];
		$prize_arr[$key]['prize_rank'] = get_prize_content_141118($v['prize_rank']);
		$prize_arr[$key]['order_id'] = $v['extension_id'];
	}
	$smarty->assign('prize_arr',	$prize_arr);
}
//2014双11
elseif ($pid == 141111)
{
	$now = time();
	//if ($now > strtotime('2014-11-14 00:00:00')) $end_status = 1;
	
	//2014-11-11(每天三场,每场一个商品)
	if ($now >= strtotime('2014-11-11 11:00:00') && $now < strtotime('2014-11-11 11:05:00')) {
		$smarty->assign('ms_1101', 0); //秒杀进行中
	} elseif ($now < strtotime('2014-11-11 11:00:00')) {
		$smarty->assign('ms_1101', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1101', 1); //秒杀已结束
	}
	
	if ($now >= strtotime('2014-11-11 16:00:00') && $now < strtotime('2014-11-11 16:05:00')) {
		$smarty->assign('ms_1102', 0);
	} elseif ($now < strtotime('2014-11-11 16:00:00')) {
		$smarty->assign('ms_1102', -1);
	} else {
		$smarty->assign('ms_1102', 1);
	}
	
	if ($now >= strtotime('2014-11-11 20:00:00') && $now < strtotime('2014-11-11 20:30:00')) {
		$smarty->assign('ms_1103', 0);
	} elseif ($now < strtotime('2014-11-11 20:00:00')) {
		$smarty->assign('ms_1103', -1);
	} else {
		$smarty->assign('ms_1103', 1);
	}
	
	//2014-11-12
	if ($now >= strtotime('2014-11-12 11:00:00') && $now < strtotime('2014-11-12 11:05:00')) {
		$smarty->assign('ms_1201', 0); //秒杀进行中
	} elseif ($now < strtotime('2014-11-12 11:00:00')) {
		$smarty->assign('ms_1201', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1201', 1); //秒杀已结束
	}
	
	if ($now >= strtotime('2014-11-12 16:00:00') && $now < strtotime('2014-11-12 16:05:00')) {
		$smarty->assign('ms_1202', 0);
	} elseif ($now < strtotime('2014-11-12 16:00:00')) {
		$smarty->assign('ms_1202', -1);
	} else {
		$smarty->assign('ms_1202', 1);
	}
	
	if ($now >= strtotime('2014-11-12 20:00:00') && $now < strtotime('2014-11-12 20:05:00')) {
		$smarty->assign('ms_1203', 0);
	} elseif ($now < strtotime('2014-11-12 20:00:00')) {
		$smarty->assign('ms_1203', -1);
	} else {
		$smarty->assign('ms_1203', 1);
	}
	
	//2014-11-13
	if ($now >= strtotime('2014-11-13 11:00:00') && $now < strtotime('2014-11-13 11:05:00')) {
		$smarty->assign('ms_1301', 0); //秒杀进行中
	} elseif ($now < strtotime('2014-11-13 11:00:00')) {
		$smarty->assign('ms_1301', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1301', 1); //秒杀已结束
	}
	
	if ($now >= strtotime('2014-11-13 16:00:00') && $now < strtotime('2014-11-13 16:05:00')) {
		$smarty->assign('ms_1302', 0);
	} elseif ($now < strtotime('2014-11-13 16:00:00')) {
		$smarty->assign('ms_1302', -1);
	} else {
		$smarty->assign('ms_1302', 1);
	}
	
	if ($now >= strtotime('2014-11-13 20:00:00') && $now < strtotime('2014-11-13 20:05:00')) {
		$smarty->assign('ms_1303', 0);
	} elseif ($now < strtotime('2014-11-13 20:00:00')) {
		$smarty->assign('ms_1303', -1);
	} else {
		$smarty->assign('ms_1303', 1);
	}
	
}

elseif ($pid == 141114)
{
	$now = time();
	if ($now > strtotime('2014-11-19 00:00:00')) $end_status = 1;
}
elseif ($pid == 141128)
{
	$now = time();
	
	
	if ($now < strtotime('2014-11-28 23:59:59')){
		$end_status = 0;
	}elseif ($now > strtotime('2014-11-28 23:59:59') && $now < strtotime('2014-12-01 23:59:59')){
		$end_status = 1;
	}else{
		$end_status = 2;
	}
	$smarty->assign('end_status', $end_status);
}
elseif ($pid == 141208)
{
	$now = time();
	if ($now > strtotime('2014-12-31 23:59:59')) $end_status = 1;
	
}
elseif ($pid == 141215)
{
	$now = time();
	if ($now > strtotime('2014-12-26 00:00:00')) $end_status = 1;
	
	if ($now < strtotime('2014-12-15 23:59:59')){
		$status = 15;
	}elseif ($now > strtotime('2014-12-16 00:00:00') && $now < strtotime('2014-12-16 23:59:59')){
		$status = 16;
	}elseif ($now > strtotime('2014-12-17 00:00:00') && $now < strtotime('2014-12-17 23:59:59')){
		$status = 17;
	}elseif ($now > strtotime('2014-12-18 00:00:00') && $now < strtotime('2014-12-18 23:59:59')){
		$status = 18;
	}elseif ($now > strtotime('2014-12-19 00:00:00') && $now < strtotime('2014-12-19 23:59:59')){
		$status = 19;
	}elseif ($now > strtotime('2014-12-20 00:00:00') && $now < strtotime('2014-12-20 23:59:59')){
		$status = 20;
	}elseif ($now > strtotime('2014-12-21 00:00:00') && $now < strtotime('2014-12-21 23:59:59')){
		$status = 21;
	}elseif ($now > strtotime('2014-12-22 00:00:00') && $now < strtotime('2014-12-22 23:59:59')){
		$status = 22;
	}elseif ($now > strtotime('2014-12-23 00:00:00') && $now < strtotime('2014-12-23 23:59:59')){
		$status = 23;
	}elseif ($now > strtotime('2014-12-24 00:00:00') && $now < strtotime('2014-12-24 23:59:59')){
		$status = 24;
	}elseif ($now > strtotime('2014-12-25 00:00:00') && $now < strtotime('2014-12-25 23:59:59')){
		$status = 25;
	}
	$smarty->assign('status', $status);
}
elseif ($pid == 141201)
{
	$now = time();
	if ($now > strtotime('2014-12-22 00:00:00')) $end_status = 1;
	
	$smarty->assign('status', $status);
}
elseif ($pid == 150113)
{
	$now = time();
	if ($now > strtotime('2015-01-30 16:01:00')) $end_status = 1;
	
	if ($now < strtotime('2015-01-14 16:01:00')){
		//1.13~1.14
		$status = 1;
		$item_status = get_item_status_150113(13,14,$now);
	}elseif ($now > strtotime('2015-01-14 16:01:00') && $now < strtotime('2015-01-16 16:01:00')){
		//1.15~1.16
		$status = 2;
		$item_status = get_item_status_150113(15,16,$now);
	}elseif ($now > strtotime('2015-01-16 16:01:00') && $now < strtotime('2015-01-18 16:01:00')){
		//1.17~1.18
		$status = 3;
		$item_status = get_item_status_150113(17,18,$now);
	}elseif ($now > strtotime('2015-01-18 16:01:00') && $now < strtotime('2015-01-20 16:01:00')){
		//1.19~1.20
		$status = 4;
		$item_status = get_item_status_150113(19,20,$now);
	}elseif ($now > strtotime('2015-01-20 16:01:00') && $now < strtotime('2015-01-22 16:01:00')){
		//1.21~1.22
		$status = 5;
		$item_status = get_item_status_150113(21,22,$now);
	}elseif ($now > strtotime('2015-01-22 16:01:00') && $now < strtotime('2015-01-24 16:01:00')){
		//1.23~1.24
		$status = 6;
		$item_status = get_item_status_150113(23,24,$now);
	}elseif ($now > strtotime('2015-01-24 16:01:00') && $now < strtotime('2015-01-26 16:01:00')){
		//1.25~1.26
		$status = 7;
		$item_status = get_item_status_150113(25,26,$now);
	}elseif ($now > strtotime('2015-01-26 16:01:00') && $now < strtotime('2015-01-28 16:01:00')){
		//1.27~1.28
		$status = 8;
		$item_status = get_item_status_150113(27,28,$now);
	}elseif ($now > strtotime('2015-01-28 16:01:00') && $now < strtotime('2015-01-30 16:01:00')){
		//1.29~1.30
		$status = 9;
		$item_status = get_item_status_150113(29,30,$now);
	}
	$smarty->assign('status', $status);
	$smarty->assign('item_status', $item_status);
}
elseif ($pid == 150202)
{
    $now = time();
	//抽红包功能
	date_default_timezone_set('PRC');
	//if ($now > strtotime('2015-02-09 23:59:00')) $end_status = 1;
	
	$can_lottery = 0;
	if($_SESSION['user_id'] > 0){
		//查询是否已有1861,1862,1863,1864		
		$lottery  = $GLOBALS['db']->GetAll("SELECT * from ecs_user_bonus 
		WHERE bonus_type_id in(1861,1862,1863,1864) AND user_id = ".$_SESSION['user_id']);
		if(empty($lottery)){
			$can_lottery = 1;
		}
	}else{
		$can_lottery = 2;
	}
	$smarty->assign('can_lottery', $can_lottery);
    //秒杀功能
    
	//2015-02-03(每天三场,每场一个商品)
	if ($now >= strtotime('2015-02-03 11:00:00') && $now < strtotime('2015-02-03 11:01:00')) {
		$smarty->assign('ms_0301', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-03 11:00:00')) {
		$smarty->assign('ms_0301', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0301', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-03 16:00:00') && $now < strtotime('2015-02-03 16:01:00')) {
		$smarty->assign('ms_0302', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-03 16:00:00')) {
		$smarty->assign('ms_0302', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0302', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-03 20:00:00') && $now < strtotime('2015-02-03 20:01:00')) {
		$smarty->assign('ms_0303', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-03 20:00:00')) {
		$smarty->assign('ms_0303', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0303', 1); //秒杀已结束
	}
    
    //2015-02-05(每天三场,每场一个商品)
	if ($now >= strtotime('2015-02-05 11:00:00') && $now < strtotime('2015-02-05 11:01:00')) {
		$smarty->assign('ms_0501', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-05 11:00:00')) {
		$smarty->assign('ms_0501', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0501', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-05 16:00:00') && $now < strtotime('2015-02-05 16:01:00')) {
		$smarty->assign('ms_0502', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-05 16:00:00')) {
		$smarty->assign('ms_0502', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0502', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-05 20:00:00') && $now < strtotime('2015-02-05 20:01:00')) {
		$smarty->assign('ms_0503', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-05 20:00:00')) {
		$smarty->assign('ms_0503', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0503', 1); //秒杀已结束
	}
    
    //2015-02-09(每天三场,每场一个商品)
	if ($now >= strtotime('2015-02-09 11:00:00') && $now < strtotime('2015-02-09 11:01:00')) {
		$smarty->assign('ms_0901', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-09 11:00:00')) {
		$smarty->assign('ms_0901', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0901', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-09 16:00:00') && $now < strtotime('2015-02-09 16:01:00')) {
		$smarty->assign('ms_0902', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-09 16:00:00')) {
		$smarty->assign('ms_0902', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0902', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-09 20:00:00') && $now < strtotime('2015-02-09 20:01:00')) {
		$smarty->assign('ms_0903', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-09 20:00:00')) {
		$smarty->assign('ms_0903', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_0903', 1); //秒杀已结束
	}
    
    //2015-02-13(每天三场,每场一个商品)
	if ($now >= strtotime('2015-02-13 11:00:00') && $now < strtotime('2015-02-13 11:01:00')) {
		$smarty->assign('ms_1301', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-13 11:00:00')) {
		$smarty->assign('ms_1301', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1301', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-13 16:00:00') && $now < strtotime('2015-02-13 16:01:00')) {
		$smarty->assign('ms_1302', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-13 16:00:00')) {
		$smarty->assign('ms_1302', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1302', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-13 20:00:00') && $now < strtotime('2015-02-13 20:01:00')) {
		$smarty->assign('ms_1303', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-13 20:00:00')) {
		$smarty->assign('ms_1303', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1303', 1); //秒杀已结束
	}
    
    //2015-02-16(每天三场,每场一个商品)
	if ($now >= strtotime('2015-02-16 11:00:00') && $now < strtotime('2015-02-16 11:01:00')) {
		$smarty->assign('ms_1601', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-16 11:00:00')) {
		$smarty->assign('ms_1601', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1601', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-16 16:00:00') && $now < strtotime('2015-02-16 16:01:00')) {
		$smarty->assign('ms_1602', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-16 16:00:00')) {
		$smarty->assign('ms_1602', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1602', 1); //秒杀已结束
	}
    if ($now >= strtotime('2015-02-16 20:00:00') && $now < strtotime('2015-02-16 20:01:00')) {
		$smarty->assign('ms_1603', 0); //秒杀进行中
	} elseif ($now < strtotime('2015-02-16 20:00:00')) {
		$smarty->assign('ms_1603', -1); //秒杀未开始
	} else {
		$smarty->assign('ms_1603', 1); //秒杀已结束
	}
    $smarty->assign('now',$now);
    
    
}
elseif ($pid == 150204)
{
    $act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';

	if ($act == 'add_to_cart') {
		if($num == 149){
			$cart149_number = isset($_REQUEST['cart149_number'])? $_REQUEST['cart149_number']: '0';
		
			$cart149_goods1 = isset($_REQUEST['cart149_goods1'])? $_REQUEST['cart149_goods1']: '0';
			$cart149_goods2 = isset($_REQUEST['cart149_goods2'])? $_REQUEST['cart149_goods2']: '0';
			$cart149_goods1_zselect = isset($_REQUEST['cart149_goods1_zselect'])? $_REQUEST['cart149_goods1_zselect']: '';
			$cart149_goods1_yselect = isset($_REQUEST['cart149_goods1_yselect'])? $_REQUEST['cart149_goods1_yselect']: '';
			$cart149_goods2_zselect = isset($_REQUEST['cart149_goods2_zselect'])? $_REQUEST['cart149_goods2_zselect']: '';
			$cart149_goods2_yselect = isset($_REQUEST['cart149_goods2_yselect'])? $_REQUEST['cart149_goods2_yselect']: '';
			
			
			$total_price_149 = 149.00;	//随心配的总价 是固定的
			$package_id_149 = 113;		//礼包ID 是固定的
			
			if ($cart149_number) 
			{
				if ($cart149_goods1 && $cart149_goods2) 
				{
					$g_1 = get_goods_info($cart149_goods1);
					$g_2 = get_goods_info($cart149_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', 'MT888', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '".$cart149_goods1_zselect.','.$cart149_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', 'MT888', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart149_goods2_zselect.','.$cart149_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '149元区加入购物车!';
				}
				
				
			}
			
			exit;
		}
	}
	
	
	$smarty->assign('goods_2608', get_goods_info(2608));
	$smarty->assign('goods_2607', get_goods_info(2607));
	$smarty->assign('goods_2606', get_goods_info(2606));
	$smarty->assign('goods_975',  get_goods_info(975));
	$smarty->assign('goods_815',  get_goods_info(815));
	$smarty->assign('goods_816',  get_goods_info(816));
	$smarty->assign('goods_4518', get_goods_info(4518));
	$smarty->assign('goods_4519', get_goods_info(4519));
	$smarty->assign('goods_4520', get_goods_info(4520));
	$smarty->assign('goods_1459', get_goods_info(1459));
    $smarty->assign('goods_3043', get_goods_info(3043));
    $smarty->assign('goods_3288', get_goods_info(3288));
    $smarty->assign('goods_974', get_goods_info(974));
    $smarty->assign('goods_916', get_goods_info(916));
    $smarty->assign('goods_2294', get_goods_info(2294));
    $smarty->assign('goods_2115', get_goods_info(2115));
    $smarty->assign('goods_1218', get_goods_info(1218));
    $smarty->assign('goods_358', get_goods_info(358));
    
	$smarty->assign('goodsds_2608', get_goods_ds(2608));
    $smarty->assign('goodsds_2607', get_goods_ds(2607));
    $smarty->assign('goodsds_2606', get_goods_ds(2606));
    $smarty->assign('goodsds_975', get_goods_ds(975));
    $smarty->assign('goodsds_815', get_goods_ds(815));
    $smarty->assign('goodsds_816', get_goods_ds(816));
    $smarty->assign('goodsds_4518', get_goods_ds(4518));
    $smarty->assign('goodsds_4519', get_goods_ds(4519));
    $smarty->assign('goodsds_4520', get_goods_ds(4520));
    $smarty->assign('goodsds_1459', get_goods_ds(1459));
    $smarty->assign('goodsds_3043', get_goods_ds(3043));
    $smarty->assign('goodsds_3288', get_goods_ds(3288));
    $smarty->assign('goodsds_974', get_goods_ds(974));
    $smarty->assign('goodsds_916', get_goods_ds(916));
    $smarty->assign('goodsds_2294', get_goods_ds(2294));
    $smarty->assign('goodsds_2115', get_goods_ds(2115));
    $smarty->assign('goodsds_1218', get_goods_ds(1218));
    $smarty->assign('goodsds_358', get_goods_ds(358));
    
}
elseif ($pid == 150212)
{
    $now = time();
	//抽红包功能
	date_default_timezone_set('PRC');
	//if ($now > strtotime('2015-02-09 23:59:00')) $end_status = 1;
	
	$can_lottery = 0;
	if($_SESSION['user_id'] > 0){
		//查询是否已有1861,1862,1863,1864		
		$lottery  = $GLOBALS['db']->GetAll("SELECT * from ecs_user_bonus 
		WHERE bonus_type_id in(1861,1862,1863,1864) AND user_id = ".$_SESSION['user_id']);
		if(empty($lottery)){
			$can_lottery = 1;
		}
	}else{
		$can_lottery = 2;
	}
	$smarty->assign('can_lottery', $can_lottery);
    //秒杀功能
    
	if ( $now < strtotime('2015-02-16 00:00:00')) {
		$smarty->assign('ms_0215', 1); 
	} 
    if ($now >= strtotime('2015-02-16 00:00:00') && $now < strtotime('2015-02-16 23:59:59')) {
		$smarty->assign('ms_0216', 1);
	}
    if ($now >= strtotime('2015-02-17 00:00:00') && $now < strtotime('2015-02-17 23:59:59')) {
		$smarty->assign('ms_0217', 1);
	}
    if ($now >= strtotime('2015-02-18 00:00:00') && $now < strtotime('2015-02-18 23:59:59')) {
		$smarty->assign('ms_0218', 1);
	}
    if ($now >= strtotime('2015-02-19 00:00:00') && $now < strtotime('2015-02-19 23:59:59')) {
		$smarty->assign('ms_0219', 1);
	}
    if ($now >= strtotime('2015-02-19 00:00:00') && $now < strtotime('2015-02-19 23:59:59')) {
		$smarty->assign('ms_0219', 1);
	}
    if ($now >= strtotime('2015-02-20 00:00:00') && $now < strtotime('2015-02-20 23:59:59')) {
		$smarty->assign('ms_0220', 1);
	}
    if ($now >= strtotime('2015-02-21 00:00:00') && $now < strtotime('2015-02-21 23:59:59')) {
		$smarty->assign('ms_0221', 1);
	}
    if ($now >= strtotime('2015-02-22 00:00:00') && $now < strtotime('2015-02-22 23:59:59')) {
		$smarty->assign('ms_0222', 1);
	}
    if ($now >= strtotime('2015-02-23 00:00:00') && $now < strtotime('2015-02-23 23:59:59')) {
		$smarty->assign('ms_0223', 1);
	}
    if ($now >= strtotime('2015-02-24 00:00:00') && $now < strtotime('2015-02-24 23:59:59')) {
		$smarty->assign('ms_0224', 1);
	}
    
    $smarty->assign('now',$now);
    
    
}
elseif ($pid == 15050101)
{
    $now = time();
	//抽红包功能
	date_default_timezone_set('PRC');
	//if ($now > strtotime('2015-02-09 23:59:00')) $end_status = 1;
	

    //秒杀功能
    if ( $now < strtotime('2015-04-18 10:15:00')) {
		$ms['ten_18'] = 'red_10.jpg';
        $ms['two_18'] = 'black_14.jpg';
        $ms['pic_18_1'] = '25.jpg';
        $ms['pic_18_2'] = '26.jpg';
        
        $ms['ten_19'] = 'black_10.jpg';
        $ms['two_19'] = 'black_14.jpg';
        $ms['pic_19_1'] = '30.jpg';
        $ms['pic_19_2'] = '31.jpg';
        
	} 
	if ( $now > strtotime('2015-04-18 14:15:00') && $now < strtotime('2015-04-19 10:15:00')) {
	    $ms['ten_18'] = 'black_10.jpg';
        $ms['two_18'] = 'black_14.jpg';
        $ms['pic_18_1'] = '25w.jpg';
        $ms['pic_18_2'] = '26w.jpg';
        
        $ms['ten_19'] = 'red_10.jpg';
        $ms['two_19'] = 'black_14.jpg';
        $ms['pic_19_1'] = '30.jpg';
        $ms['pic_19_2'] = '31.jpg';
       
		
	} 
    if ($now > strtotime('2015-04-19 10:15:00') && $now < strtotime('2015-04-19 14:15:00')) {
		$ms['ten_18'] = 'black_10.jpg';
        $ms['two_18'] = 'black_14.jpg';
        $ms['pic_18_1'] = '25w.jpg';
        $ms['pic_18_2'] = '26w.jpg';
        
        $ms['ten_19'] = 'black_10.jpg';
        $ms['two_19'] = 'red_14.jpg';
        $ms['pic_19_1'] = '30.jpg';
        $ms['pic_19_2'] = '31.jpg';
	}
    
    if ($now >= strtotime('2015-04-19 14:15:01')) {
		$ms['ten_18'] = 'black_10.jpg';
        $ms['two_18'] = 'black_14.jpg';
        $ms['pic_18_1'] = '25w.jpg';
        $ms['pic_18_2'] = '26w.jpg';
        
        $ms['ten_19'] = 'black_10.jpg';
        $ms['two_19'] = 'red_14.jpg';
        $ms['pic_19_1'] = '30w.jpg';
        $ms['pic_19_2'] = '31w.jpg';
	}
    
    $smarty->assign('now',$now);
    $smarty->assign('ms',$ms);
    
    
}
elseif ($pid == 15050104)
{
    $now = time();

	date_default_timezone_set('PRC');
    //秒杀功能
    
	if ( $now < strtotime('2015-05-01 00:00:00')) {
		$smarty->assign('qg_0215', 0); 
	} 
    if ($now >= strtotime('2015-05-01 00:00:00') && $now < strtotime('2015-05-01 23:59:59')) {
		$smarty->assign('qg_0215', 1);
	}
    if ($now >= strtotime('2015-05-02 00:00:00') && $now < strtotime('2015-05-02 23:59:59')) {
		$smarty->assign('qg_0215', 2);
	}
    if ($now >= strtotime('2015-05-03 00:00:00') && $now < strtotime('2015-05-03 23:59:59')) {
		$smarty->assign('qg_0215', 3);
	}
    if ($now >= strtotime('2015-05-04 00:00:00') && $now < strtotime('2015-05-04 23:59:59')) {
		$smarty->assign('qg_0215', 4);
	}
    
    
}
elseif ($pid == 150515)
{
    $now = time();

	date_default_timezone_set('PRC');
    
    $smarty->assign('user_id', $user_id);
    
    if($_REQUEST['act'] == 'get_hly'){
        if($user_id>0){
            
            $is_over_1 = $GLOBALS['db']->getOne("SELECT count(*) FROM ". $GLOBALS['ecs']->table('cart') . " where goods_id = 4755 ");
            $is_over_2 = $GLOBALS['db']->getOne("SELECT count(*) FROM ". $GLOBALS['ecs']->table('order_goods') . " where goods_id = 4755 ");
            $is_over =$is_over_1+$is_over_2;
            if($is_over>=1000){
                    $res = 2;
                
            }else{
                $sel_1 = $GLOBALS['db']->getOne("SELECT * FROM ". $GLOBALS['ecs']->table('cart') . " where goods_id = 4755 and user_id = '".$user_id."'");
           
                $sel_2 = $GLOBALS['db']->getAll("SELECT a.order_id,b.goods_id FROM ". $GLOBALS['ecs']->table('order_info') . " 
                as a left join ".$GLOBALS['ecs']->table('order_goods')." as b 
                ON a.order_id = b.order_id WHERE a.user_id = '".$user_id."' AND b.goods_id =4755");
                
                if(empty($sel_1) && empty($sel_2)){
                    $into_cart = $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('cart') . 
                    "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) 
                    VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4755', '004755', '[招行支付专享]LB澜柏多功能隐形眼镜护理液2*10ml', '12.80', '1.00', '1', '', '1', 'unchange', '', '1', '')");
                    
                    $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('2081', 0, '".$user_id."', 0, 0, 0)");     
                    
                    if($into_cart && $into_bonus){
                        $res = 1;
                    }
                }else{
                    $res = 0;
                }
            }
            
        }
        echo $res;die;
    }
    
   
    
}
elseif ($pid == 150514)
{
    $act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';

	if ($act == 'add_to_cart') {
		if($num == 149){
			$cart149_number = isset($_REQUEST['cart149_number'])? $_REQUEST['cart149_number']: '0';
		
			$cart149_goods1 = isset($_REQUEST['cart149_goods1'])? $_REQUEST['cart149_goods1']: '0';
			$cart149_goods2 = isset($_REQUEST['cart149_goods2'])? $_REQUEST['cart149_goods2']: '0';
			$cart149_goods1_zselect = isset($_REQUEST['cart149_goods1_zselect'])? $_REQUEST['cart149_goods1_zselect']: '';
			$cart149_goods1_yselect = isset($_REQUEST['cart149_goods1_yselect'])? $_REQUEST['cart149_goods1_yselect']: '';
			$cart149_goods2_zselect = isset($_REQUEST['cart149_goods2_zselect'])? $_REQUEST['cart149_goods2_zselect']: '';
			$cart149_goods2_yselect = isset($_REQUEST['cart149_goods2_yselect'])? $_REQUEST['cart149_goods2_yselect']: '';
			
			
			$total_price_149 = 149.00;	//随心配的总价 是固定的
			$package_id_149 = 113;		//礼包ID 是固定的
			
			if ($cart149_number) 
			{
				if ($cart149_goods1 && $cart149_goods2) 
				{
					$g_1 = get_goods_info($cart149_goods1);
					$g_2 = get_goods_info($cart149_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', 'MT888', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '".$cart149_goods1_zselect.','.$cart149_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', 'MT888', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart149_goods2_zselect.','.$cart149_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '149元区加入购物车!';
				}
				
			}
			
			exit;
		}
	}elseif($act == 'get_tickets'){
	   
	   $order_sn = trim($_REQUEST['order_sn']);
	   $order_status = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_sn='$order_sn' AND pay_status = 2 
       AND user_id = ".$user_id);
       if($order_status){
            include_once('./includes/lib_main.php');
            
            if($order_status['goods_amount']-$order_status['discount']>=149){
                $if_used = $GLOBALS['db']->GetRow("SELECT ticket_password FROM lele_gwl WHERE ticket_type=7 AND status=1 AND order_sn='$order_sn' LIMIT 1");
                
                if($if_used){
                    echo '2';//此订单已经参与过此活动，请前往个人中心-站内信查看^_^
                }else{
                    $quan = $GLOBALS['db']->GetRow("SELECT ticket_password FROM lele_gwl WHERE ticket_type=7 AND status=0 AND order_sn='' LIMIT 1");
    	       
                    $GLOBALS['db']->query("UPDATE lele_gwl SET status=1,order_sn='".$order_sn."' WHERE ticket_type=7 
        										AND ticket_password = '".$quan['ticket_password']."'"); //标记已使用
        			
                    $msg189 = '恭喜您获得一张79元现金券<a style="color:red;">'.$quan['ticket_password'].'</a>,<br/>
        								扫描下方的”好厨师“二维码，在APP底部菜单“我的”中选“我的现金券”→ 输入兑换码确认/提交订单,即可使用免费体验四菜一汤服务，截止到6月3日<br />
                              <img src="http://img.easeeyes.com/promotion/haochushi.jpg" width="180px" height="180px"/>
                              ';
        		    $sql_prize189 = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) 
        								values (".$user_id.", '".$_SESSION['user_name']."', ".$_SERVER['REQUEST_TIME'].", '满149送好厨师优惠券', '".$msg189."', 'prize')";		
        			$res_prize189 = $GLOBALS['db']->query($sql_prize189);
                 
        			if($res_prize189){ unread_user_msg($user_id); }
                    
                    echo '1';//恭喜您获得一张79元现金券，前往个人中心-站内信查看^_^
                }
                
            }else{
                echo '0';//此订单不满足活动条件！或请登录后再试^_^
            }
            
       }else{
            echo '0';//此订单不满足活动条件！或请登录后再试^_^
       }
       die;
	}
	
	
	$smarty->assign('goods_2608', get_goods_info(2608));
	$smarty->assign('goods_2607', get_goods_info(2607));
	$smarty->assign('goods_2606', get_goods_info(2606));
	$smarty->assign('goods_975',  get_goods_info(975));
    $smarty->assign('goods_974', get_goods_info(974));
	$smarty->assign('goods_815',  get_goods_info(815));
	$smarty->assign('goods_816',  get_goods_info(816));
	$smarty->assign('goods_1459', get_goods_info(1459));
    $smarty->assign('goods_1458', get_goods_info(1458));
    $smarty->assign('goods_1461', get_goods_info(1461));
    $smarty->assign('goods_3043', get_goods_info(3043));
    $smarty->assign('goods_3045', get_goods_info(3045));
    $smarty->assign('goods_2294', get_goods_info(2294));
    $smarty->assign('goods_2295', get_goods_info(2295));
    
    $smarty->assign('goods_157', get_goods_info(157));
    $smarty->assign('goods_1144', get_goods_info(1144));
    $smarty->assign('goods_1476', get_goods_info(1476));
    $smarty->assign('goods_978', get_goods_info(978));
    $smarty->assign('goods_1150', get_goods_info(1150));
    $smarty->assign('goods_952', get_goods_info(952));
    $smarty->assign('goods_3035', get_goods_info(3035));
    
    
    $smarty->assign('goodsds_2608', get_goods_ds(2608));
	$smarty->assign('goodsds_2607', get_goods_ds(2607));
	$smarty->assign('goodsds_2606', get_goods_ds(2606));
	$smarty->assign('goodsds_975',  get_goods_ds(975));
    $smarty->assign('goodsds_974', get_goods_ds(974));
	$smarty->assign('goodsds_815',  get_goods_ds(815));
	$smarty->assign('goodsds_816',  get_goods_ds(816));
	$smarty->assign('goodsds_1459', get_goods_ds(1459));
    $smarty->assign('goodsds_1458', get_goods_ds(1458));
    $smarty->assign('goodsds_1461', get_goods_ds(1461));
    $smarty->assign('goodsds_3043', get_goods_ds(3043));
    $smarty->assign('goodsds_3045', get_goods_ds(3045));
    $smarty->assign('goodsds_2294', get_goods_ds(2294));
    $smarty->assign('goodsds_2295', get_goods_ds(2295));
    
    $smarty->assign('goodsds_157', get_goods_ds(157));
    $smarty->assign('goodsds_1144', get_goods_ds(1144));
    $smarty->assign('goodsds_1476', get_goods_ds(1476));
    $smarty->assign('goodsds_978', get_goods_ds(978));
    $smarty->assign('goodsds_1150', get_goods_ds(1150));
    $smarty->assign('goodsds_952', get_goods_ds(952));
    $smarty->assign('goodsds_3035', get_goods_ds(3035));
}
/*================================== 周末专场 ========================================*/
elseif ($pid == 6)
{
    if(@$_REQUEST['act'] == 'get_bonus'){

        if($user_id>0){
            $now = time();
            if($now < 1455292800){
                $bonus_id = 2727;
            }else{
                $bonus_id = 2728;
            }

            //$quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')."
//            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            $quan = $GLOBALS['db']->getOne("select count(*) as num from ".$GLOBALS['ecs']->table('user_bonus')."
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);

            if($quan>=5){
                echo '3';//已经领取过
            }else{

                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
                echo '1';//领取成功
            }

        }else{
            echo '2';//未登录
        }

        die;
    }


    $now = time();
    $first=6; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
    $w=date('w');  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
    $wk_start = strtotime(date('Y-m-d')." -".($w? $w - $first : 1).' days'); //获取本周开始日期，如果$w是0，则表示周日，减去 1天
    $wk_start_fomart = date('Y-m-d H:i:s',$wk_start);
    
    if($now >strtotime('2016-02-13 00:00:00')){
        $temp_img_05 = '01-auto.jpg';
        $temp_img_banner = 'banner-auto.jpg';
        $smarty->assign('temp_img_05', $temp_img_05);
        $smarty->assign('temp_img_banner', $temp_img_banner);
    }
    if($now < $wk_start){//未开始

        $djs_6="0";//未开始
        $djs_7="0";//未开始

    }elseif($now >= $wk_start && $now < ($wk_start+86400)){//进行中6

        $djs_6="1";
        $djs_7="0";//未开始
        $smarty->assign('wk_end_6', date('Y-m-d H:i:s',$wk_start+86400));//离6结束时间

    }elseif($now >= ($wk_start+86400) && $now < ($wk_start+172800)){//进行中7
        $djs_6="2";//已结束
        $djs_7="1";
        $smarty->assign('wk_end_7', date('Y-m-d H:i:s',$wk_start+172800));//离7结束时间

    }else{//已结束
        $djs_6="2";//已结束
        $djs_7="2";//已结束
    }

    $goods_id_6 = $GLOBALS['db']->getAll("SELECT goods_id FROM ecs_weekly_buy WHERE type = 6 ORDER BY wid ASC");
    $goods_id_7 = $GLOBALS['db']->getAll("SELECT goods_id FROM ecs_weekly_buy WHERE type = 7 ORDER BY wid ASC");


    $smarty->assign('wk_start_fomart', $wk_start_fomart);//离开始时间

    $smarty->assign('djs_6', $djs_6);
    $smarty->assign('djs_7', $djs_7);

    $smarty->assign('goods_6', $goods_id_6);
    $smarty->assign('goods_7', $goods_id_7);

    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        // 20160130  周末专场活动副推产品
        $goodsArr1 = array(//副推产品
            array(103,1),array(105,1),array(767,3,'买三送一'),array(757,1),
            array(1010,1),array(2686,1),array(1,1),array(2,1),
            array(359,3,'买一送一'),array(4523,3,'买一送一'),array(3630,1),array(3,1),
            array(589,1),array(5061,1),array(3338,1),array(592,1)
        );
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 811");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E4%B8%A4%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝明眸两周抛彩色隐形眼镜6片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4477");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生安视优define美瞳日抛彩色隐形眼镜30片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4281");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=G%26G%E8%A5%BF%E6%AD%A6Secret+CandyEyes%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'G&G西武Secret CandyEyes系列年抛型彩色隐形眼镜1片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        /*$goodsArr1 = array(//副推产品
            array(131,'直降<br />6元'),array(118,'直降<br />6元'),array(221,'直降<br />1元'),
            array(104,'直降<br />11元'),array(140,'4.8折<br />抢'),array(359,'5折<br />抢'),
            array(4500,'直降<br />26'),array(4283,'6.8 折<br />抢'),array(4553,'直降<br />10元'),
            array(884,'5.5折<br />抢'),array(5013,'2.9折<br />抢'),array(3631,'7.6折<br />抢'),
            array(4786,'直降<br />34'),array(4070,'6.6折<br />抢'),array(589,'直降<br />31'),
            array(861,'直降<br />2元')
        );
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            $res['shop_price'] = floor($res['shop_price']);
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            $resArr1[] = $res;
        }*/
        $smarty->assign('goodsArr1',	$resArr1);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 150603)
{
    
    if($_REQUEST['act'] == 'get_bonus'){
        
        if($user_id>0){
            if($_POST['bonus_id'] == 1){
                $bonus_id = 2160;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 2161;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 2162;
            }elseif($_POST['bonus_id'] == 4){
                $bonus_id = 2147;
            }elseif($_POST['bonus_id'] == 5){
                $bonus_id = 2148;
            }elseif($_POST['bonus_id'] == 6){
                $bonus_id = 2149;
            }elseif($_POST['bonus_id'] == 7){
                $bonus_id = 2150;
            }elseif($_POST['bonus_id'] == 8){
                $bonus_id = 2151;
            }elseif($_POST['bonus_id'] == 9){
                $bonus_id = 2152;
            }elseif($_POST['bonus_id'] == 10){
                $bonus_id = 2151;
            }elseif($_POST['bonus_id'] == 11){
                $bonus_id = 2152;
            }elseif($_POST['bonus_id'] == 12){
                $bonus_id = 2153;
            }elseif($_POST['bonus_id'] == 13){
                $bonus_id = 2154;
            }elseif($_POST['bonus_id'] == 14){
                $bonus_id = 2158;
            }elseif($_POST['bonus_id'] == 15){
                $bonus_id = 2159;
            }else{
                $bonus_id = 2160;
            }
            echo 3;die;
            $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if(!empty($quan)){
                echo '3';//已经领取过
            }else{
                
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;
    }
    
    
}elseif($pid == 150608){
    
    $now = time();
    if ($now >= strtotime('2015-06-08 00:00:00') && $now < strtotime('2015-06-08 23:59:59')) {
		$smarty->assign('qg_img','5.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/goods4795.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/goods1461.html');
        $smarty->assign('wx_img','6.jpg');
	}
    if ($now >= strtotime('2015-06-09 00:00:00') && $now < strtotime('2015-06-09 23:59:59')) {
		$smarty->assign('qg_img','5-2.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/goods4633.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/goods1064.html');
        $smarty->assign('wx_img','6.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150609.html');
	}
    if ($now >= strtotime('2015-06-10 00:00:00') && $now < strtotime('2015-06-10 23:59:59')) {
		$smarty->assign('qg_img','5-3.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_4781.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_2577.html');
        $smarty->assign('wx_img','6-3.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150610.html');
	}
    if ($now >= strtotime('2015-06-11 00:00:00') && $now < strtotime('2015-06-11 23:59:59')) {
		$smarty->assign('qg_img','5-4.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_4486.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_790.html');
        $smarty->assign('wx_img','6-4.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150611.html');
	}
    if ($now >= strtotime('2015-06-12 00:00:00') && $now < strtotime('2015-06-12 23:59:59')) {
		$smarty->assign('qg_img','5-5.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_4825.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_1064.html');
        $smarty->assign('wx_img','6-5.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150612.html');
	}
    if ($now >= strtotime('2015-06-13 00:00:00') && $now < strtotime('2015-06-13 23:59:59')) {
		$smarty->assign('qg_img','5-6.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_790.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_4486.html');
        $smarty->assign('wx_img','6-6.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150613.html');
	}
    if ($now >= strtotime('2015-06-14 00:00:00') && $now < strtotime('2015-06-14 23:59:59')) {
		$smarty->assign('qg_img','5-7.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_4826.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_4632.html');
        $smarty->assign('wx_img','6-7.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150614.html');
	}
    if ($now >= strtotime('2015-06-15 00:00:00') && $now < strtotime('2015-06-15 23:59:59')) {
		$smarty->assign('qg_img','5-8.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_1461.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_4825.html');
        $smarty->assign('wx_img','6-8.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150615.html');
	}
    if ($now >= strtotime('2015-06-16 00:00:00') && $now < strtotime('2015-06-16 23:59:59')) {
		$smarty->assign('qg_img','5-9.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_790.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_4633.html');
        $smarty->assign('wx_img','6-9.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150616.html');
	}
    if ($now >= strtotime('2015-06-17 00:00:00') && $now < strtotime('2015-06-17 23:59:59')) {
		$smarty->assign('qg_img','5-10.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_4825.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_1064.html');
        $smarty->assign('wx_img','6-10.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150617.html');
	}
    if ($now >= strtotime('2015-06-18 00:00:00') && $now < strtotime('2015-06-18 23:59:59')) {
		$smarty->assign('qg_img','5-11.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_2577.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_2577.html');
        $smarty->assign('wx_img','6-11.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150618.html');
	}
    if ($now >= strtotime('2015-06-19 00:00:00') && $now < strtotime('2015-06-19 23:59:59')) {
		$smarty->assign('qg_img','5-11.jpg');
        $smarty->assign('qg_link_1','http://www.easeeyes.com/miaosha_buy_2577.html');
        $smarty->assign('qg_link_2','http://www.easeeyes.com/miaosha_buy_2577.html');
        $smarty->assign('wx_img','6-11.jpg');
        $smarty->assign('wx_url','http://www.easeeyes.com/active150619.html');
	}
    if($_REQUEST['act'] == 'get_tickets'){
	   
	   $order_sn = trim($_REQUEST['order_sn']);
	   $order_status = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_sn='$order_sn' AND (shipping_status = 1  OR shipping_status = 2) 
       AND user_id = ".$user_id);
       if($order_status){
            include_once('./includes/lib_main.php');
            
                $if_used = $GLOBALS['db']->GetRow("SELECT ticket_password FROM lele_gwl WHERE ticket_type=7 AND status=1 AND order_sn='$order_sn' LIMIT 1");
                
                if($if_used){
                    echo '2';//此订单已经参与过此活动，请前往个人中心-站内信查看^_^
                }else{
                    $m_s  = mt_rand(1, 100);
          
                    if($m_s<=40){//100积分
                        $insert_gwl = $GLOBALS['db']->query("insert into lele_gwl (ticket_type, ticket_password, status, order_sn) 
            								values (7, 0, 1, '$order_sn')");	
                        $add_points = $GLOBALS['db']->query("UPDATE ecs_users SET pay_points=pay_points+100 WHERE user_id=".$user_id);		
                        echo '4';//恭喜您获得100易视积分 可用于易视积分商城^_^
                    }elseif($m_s<=80 && $m_s>40){//500积分
                        $insert_gwl = $GLOBALS['db']->query("insert into lele_gwl (ticket_type, ticket_password, status, order_sn) 
            								values (7, 0, 1, '$order_sn')");	
                        $add_points = $GLOBALS['db']->query("UPDATE ecs_users SET pay_points=pay_points+500 WHERE user_id=".$user_id);	
            			echo '5';//恭喜您获得500易视积分 可用于易视积分商城^_^
                        
                    }else{//好厨师
                        $quan = $GLOBALS['db']->GetRow("SELECT ticket_password FROM lele_gwl WHERE ticket_type=7 AND status=0 AND order_sn='' LIMIT 1");
    	       
                        $GLOBALS['db']->query("UPDATE lele_gwl SET status=1,order_sn='".$order_sn."' WHERE ticket_type=7 
            										AND ticket_password = '".$quan['ticket_password']."'"); //标记已使用
            			
                        $msg189 = '恭喜您获得一张79元现金券<a style="color:red;">'.$quan['ticket_password'].'</a>,<br/>
            								扫描下方的”好厨师“二维码，在APP底部菜单“我的”中选“我的现金券”→ 输入兑换码确认/提交订单,即可使用免费体验四菜一汤服务，截止到6月30日<br />
                                  <img src="http://img.easeeyes.com/promotion/haochushi.jpg" width="180px" height="180px"/>
                                  ';
            		    $sql_prize189 = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) 
            								values (".$user_id.", '".$_SESSION['user_name']."', ".$_SERVER['REQUEST_TIME'].", '618大促活动抽奖', '".$msg189."', 'prize')";		
            			$res_prize189 = $GLOBALS['db']->query($sql_prize189);
                     
            			if($res_prize189){ unread_user_msg($user_id); }
                        
                        echo '1';//恭喜您获得一张79元现金券，前往个人中心-站内信查看^_^
                    }       
                        
                }
                
       }else{
            echo '0';//此订单不满足活动条件！或请登录后再试^_^
       }
       die;
	}
}elseif($pid == 150620){
        
        
    if ($now < strtotime('2015-06-21 00:00:00')) {
        //$smarty->assign('bsl2','<img src="http://img.easeeyes.com/promotion/20150620/5-1.jpg" />');
        //$smarty->assign('bsl','<img src="http://img.easeeyes.com/promotion/20150620new/6.jpg"  border="0" usemap="#Map" id="wxdt"/>');
        //$smarty->assign('bsl3','<img src="http://img.easeeyes.com/promotion/20150620new/6-1.jpg" />');
        $smarty->assign('bg','bg_new.jpg');
	}else{
	   $smarty->assign('bsl','');
       $smarty->assign('bsl2','');
       $smarty->assign('bsl3','');
       $smarty->assign('bg','bg_new2.jpg');
	}
    
    
    
}elseif($pid == 150621){
    if($_REQUEST['act'] == 'get_tickets'){
        
            
        if($user_id>0){
            
            $bonus_sn = empty($_REQUEST['order_sn'])? 0 : trim($_REQUEST['order_sn']);
            $have_used = $GLOBALS['db']->getOne("SELECT bonus_id FROM ecs_user_bonus WHERE bonus_type_id = 2177 AND user_id = ".$user_id);
            
            if($have_used){
                echo '2';//您已经兑换过，不能多次兑换^_^
            }else{
                
                $bonus_id = $GLOBALS['db']->getOne("SELECT bonus_id FROM ecs_user_bonus 
                WHERE bonus_sn = ".$bonus_sn." AND bonus_type_id = 2177");
      
                if($bonus_id){
                    
                    $sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . 
                    "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) 
                    VALUES ('".$user_id."', '".SESS_ID."', 3947, '', '[微信抽奖]蓝睛灵蕾丝魅影系列双周抛彩色隐形眼镜', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '')";
                    
                    $res1 = $GLOBALS['db']->query($sql1);
                    
                    $sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . 
                    "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) 
                    VALUES ('".$user_id."', '".SESS_ID."', 827, '', '[微信抽奖]蓝睛灵去蛋白免揉搓隐形眼镜护理液120ML', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '')";
                    
                    $res2 = $GLOBALS['db']->query($sql2);
                    
                    $GLOBALS['db']->query("UPDATE ecs_user_bonus SET user_id = ".$user_id.", bonus_sn = ".$bonus_sn.",used_time = ".time()." WHERE bonus_id =".$bonus_id);
                    
                    echo '1';//礼包已加入您的购物车^_^
                    
                }else{
                    echo '3';//很抱歉，您输入的券号有误^_^
                }
            }
            
        }else{
            echo '0';//请登录后再试^_^
        }
        die;
        
    }
}elseif($pid == 150625){
        
    $now = time();
    if ($now < strtotime('2015-06-25 00:00:00')) {//未开始
       $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg1.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_4486.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4839.html');
	}elseif($now >= strtotime('2015-06-25 00:00:00') && $now < strtotime('2015-06-25 23:59:59')){//第一天
       $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg1.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_4486.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4839.html');
	}elseif($now >= strtotime('2015-06-26 00:00:00') && $now < strtotime('2015-06-26 23:59:59')){
	   $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg2.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_4826.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4839.html');
	}elseif($now >= strtotime('2015-06-27 00:00:00') && $now < strtotime('2015-06-27 23:59:59')){
	   $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg3.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_4841.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4486.html');
	}elseif($now >= strtotime('2015-06-28 00:00:00') && $now < strtotime('2015-06-28 23:59:59')){
	   $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg4.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_933.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4841.html');
	}elseif($now >= strtotime('2015-06-29 00:00:00') && $now < strtotime('2015-06-29 23:59:59')){
	   $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg5.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_4826.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4839.html');
	}elseif($now >= strtotime('2015-06-30 00:00:00') && $now < strtotime('2015-06-30 23:59:59')){
	   $smarty->assign('qg_img','http://img.easeeyes.com/promotion/20150625/qg6.jpg');
	   $smarty->assign('qg_1','http://www.easeeyes.com/miaosha_buy_4486.html');
       $smarty->assign('qg_2','http://www.easeeyes.com/miaosha_buy_4841.html');
	}
    
    
    
}elseif($pid == 150801){
    
    $now = time();
    
    
    if($_REQUEST['act'] == 'get_bonus'){
        
        if($user_id>0){
            
            if($_REQUEST['bonus_id'] == 1){
                $bonus_id = 2297;
            }elseif($_REQUEST['bonus_id'] == 2){
                $bonus_id = 2296;
            }elseif($_REQUEST['bonus_id'] == 3){
                $bonus_id = 2298;
            }
           
            $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if(!empty($quan)){
                echo '3';//已经领取过
            }else{
                
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;
        
    }
    
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active150801'));
    
    if(!$smarty->is_cached('active150801.dwt', $cache_id))
    {
        
        if($now >= strtotime('2015-8-03 00:00:00') && $now <= strtotime('2015-8-10 00:00:00')){//第一周
            $start = strtotime('2015-8-03 00:00:00');
            $end   = strtotime('2015-8-10 00:00:00');
        
            $list_123 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 0,3");
            
            foreach($list_123 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_123_val[] = $v;
            }
            
            $list = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 3,17");
            
            foreach($list as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_val[] = $v;
            }
            
            $smarty->assign('list_123',$list_123_val);
            $smarty->assign('list',$list_val);
            $smarty->assign('weekly',1);
            
        }elseif($now >= strtotime('2015-8-10 00:00:00') && $now <= strtotime('2015-8-17 00:00:00')){//第二周
        
            $start = strtotime('2015-8-10 00:00:00');
            $end   = strtotime('2015-8-17 00:00:00');
        
            $list_12 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 0,2");
            
            foreach($list_12 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_12_val[] = $v;
            }
            
            $list_3 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 2,2");
            
            foreach($list_3 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_3_val[] = $v;
            }
            
            $list = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 4,26");

            foreach($list as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_val[] = $v;
            }
            
            $smarty->assign('list_12',$list_12_val);
            $smarty->assign('list_3',$list_3_val);
            $smarty->assign('list',$list_val);
            $smarty->assign('weekly',2);
            
        }elseif($now >= strtotime('2015-8-17 00:00:00') && $now <= strtotime('2015-8-24 00:00:00')){//第三周
            $start = strtotime('2015-8-17 00:00:00');
            $end   = strtotime('2015-8-24 00:00:00');
        
            $list_1 = $GLOBALS['db']->getRow("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 0,1");
            
            $list_1['pay_total'] = floor($list_1['pay_total']);
        
            $list_2 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 1,2");
            
            foreach($list_2 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_2_val[] = $v;
            }
            
            $list_3 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 3,4");
            
            foreach($list_3 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_3_val[] = $v;
            }
            
            
            $list = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 7,23");
            
            foreach($list as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_val[] = $v;
            }
            
            $smarty->assign('list_1',$list_1);
            $smarty->assign('list_2',$list_2_val);
            $smarty->assign('list_3',$list_3_val);
            $smarty->assign('list',$list_val);
            $smarty->assign('weekly',3);
            
        }elseif($now >= strtotime('2015-8-24 00:00:00') && $now <= strtotime('2015-8-31 00:00:00')){//第四周
            $start = strtotime('2015-8-24 00:00:00');
            $end   = strtotime('2015-8-31 00:00:00');

        
            $list_12 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 0,2");
            
            foreach($list_12 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_12_val[] = $v;
            }
            
            $list_3 = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 2,4");
            
            foreach($list_3 as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_3_val[] = $v;
            }
            
            
            $list = $GLOBALS['db']->getAll("SELECT sum(a.money_paid) as pay_total,a.user_id,b.user_name  
            FROM ". $GLOBALS['ecs']->table('order_info') . " a  LEFT JOIN ". $GLOBALS['ecs']->table('users') ." b ON a.user_id = b.user_id 
            WHERE a.user_id != 0 AND a.pay_status = 2 AND a.pay_time >=".$start." 
            AND a.pay_time <=".$end." group by user_id desc order by pay_total desc limit 6,14");
            
            foreach($list as $v){
                $v['pay_total'] = floor($v['pay_total']);
                $list_val[] = $v;
            }
            
            $smarty->assign('list_12',$list_12_val);
            $smarty->assign('list_3',$list_3_val);
            $smarty->assign('list',$list_val);
            $smarty->assign('weekly',4);
            
        }
        
    }
    $smarty->display('active150801.dwt',$cache_id);
    exit;
}
elseif($pid == 150812)
{
    $act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';

	if ($act == 'add_to_cart') {
		if($num == 77){
			$cart77_number = isset($_REQUEST['cart77_number'])? $_REQUEST['cart77_number']: '0';
		
			$cart77_goods1 = isset($_REQUEST['cart77_goods1'])? $_REQUEST['cart77_goods1']: '0';
			$cart77_goods2 = isset($_REQUEST['cart77_goods2'])? $_REQUEST['cart77_goods2']: '0';
			$cart77_goods1_zselect = isset($_REQUEST['cart77_goods1_zselect'])? $_REQUEST['cart77_goods1_zselect']: '';
			$cart77_goods1_yselect = isset($_REQUEST['cart77_goods1_yselect'])? $_REQUEST['cart77_goods1_yselect']: '';
			$cart77_goods2_zselect = isset($_REQUEST['cart77_goods2_zselect'])? $_REQUEST['cart77_goods2_zselect']: '';
			$cart77_goods2_yselect = isset($_REQUEST['cart77_goods2_yselect'])? $_REQUEST['cart77_goods2_yselect']: '';
			
			
			$total_price_77 = 77.00;	//随心配的总价 是固定的
			$package_id_77 = 113;		//礼包ID 是固定的
			
			if ($cart77_number) 
			{
				if ($cart77_goods1 && $cart77_goods2) 
				{
					$g_1 = get_goods_info($cart77_goods1);
					$g_2 = get_goods_info($cart77_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart77_goods1."', 'MT888', '[77元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_77."', '1', '".$cart77_goods1_zselect.','.$cart77_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart77_goods2."', 'MT888', '[77元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart77_goods2_zselect.','.$cart77_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '77元区加入购物车!';
				}
				
			}
			
			exit;
		}
	}

	//美瞳
	$smarty->assign('goods_983', get_goods_info(983));
	$smarty->assign('goods_4822', get_goods_info(4822));
	$smarty->assign('goods_883', get_goods_info(883));
	$smarty->assign('goods_879',  get_goods_info(879));
    $smarty->assign('goods_3967', get_goods_info(3967));
	$smarty->assign('goods_4000',  get_goods_info(4000));
	$smarty->assign('goods_3963',  get_goods_info(3963));
	$smarty->assign('goods_3952', get_goods_info(3952));
    $smarty->assign('goods_4560', get_goods_info(4560));
    $smarty->assign('goods_3158', get_goods_info(3158));
    $smarty->assign('goods_4777', get_goods_info(4777));
    $smarty->assign('goods_3892', get_goods_info(3892));
    $smarty->assign('goods_956', get_goods_info(956));
    $smarty->assign('goods_1470', get_goods_info(1470));
    $smarty->assign('goods_3928', get_goods_info(3928));
    $smarty->assign('goods_2113', get_goods_info(2113));
    $smarty->assign('goods_2110', get_goods_info(2110));
    $smarty->assign('goods_4482', get_goods_info(4482));
    //透明片
    $smarty->assign('goods_4883', get_goods_info(4883));
    $smarty->assign('goods_1144', get_goods_info(1144));
    $smarty->assign('goods_3037', get_goods_info(3037));
    $smarty->assign('goods_4804', get_goods_info(4804));
    $smarty->assign('goods_970', get_goods_info(970));
    $smarty->assign('goods_141', get_goods_info(141));
    $smarty->assign('goods_2406', get_goods_info(2406));
    $smarty->assign('goods_4434', get_goods_info(4434));
    $smarty->assign('goods_731', get_goods_info(731));
    $smarty->assign('goods_951', get_goods_info(951));
    $smarty->assign('goods_1145', get_goods_info(1145));
    $smarty->assign('goods_4800', get_goods_info(4800));
    //var_dump(get_goods_info(1144));
    //美瞳ds
    $smarty->assign('goodsds_983', get_goods_ds(983));
	$smarty->assign('goodsds_4822', get_goods_ds(4822));
	$smarty->assign('goodsds_883', get_goods_ds(883));
	$smarty->assign('goodsds_879',  get_goods_ds(879));
    $smarty->assign('goodsds_3967', get_goods_ds(3967));
	$smarty->assign('goodsds_4000',  get_goods_ds(4000));
	$smarty->assign('goodsds_3963',  get_goods_ds(3963));
	$smarty->assign('goodsds_3952', get_goods_ds(3952));
    $smarty->assign('goodsds_4560', get_goods_ds(4560));
    $smarty->assign('goodsds_3158', get_goods_ds(3158));
    $smarty->assign('goodsds_4777', get_goods_ds(4777));
    $smarty->assign('goodsds_3892', get_goods_ds(3892));
    $smarty->assign('goodsds_956', get_goods_ds(956));
    $smarty->assign('goodsds_1470', get_goods_ds(1470));
    $smarty->assign('goodsds_3928', get_goods_ds(3928));
    $smarty->assign('goodsds_2113', get_goods_ds(2113));
    $smarty->assign('goodsds_2110', get_goods_ds(2110));
    $smarty->assign('goodsds_4482', get_goods_ds(4482));
    //透明片ds
    $smarty->assign('goodsds_4883', get_goods_ds(4883));
    $smarty->assign('goodsds_1144', get_goods_ds(1144));
    $smarty->assign('goodsds_3037', get_goods_ds(3037));
    $smarty->assign('goodsds_4804', get_goods_ds(4804));
    $smarty->assign('goodsds_970', get_goods_ds(970));
    $smarty->assign('goodsds_141', get_goods_ds(141));
    $smarty->assign('goodsds_2406', get_goods_ds(2406));
    $smarty->assign('goodsds_4434', get_goods_ds(4434));
    $smarty->assign('goodsds_731', get_goods_ds(731));
    $smarty->assign('goodsds_951', get_goods_ds(951));
    $smarty->assign('goodsds_1145', get_goods_ds(1145));
    $smarty->assign('goodsds_4800', get_goods_ds(4800));
}
/*=======================================================================================*/
elseif($pid == 151027){

    $count = $GLOBALS['db']->getOne("SELECT count(id) FROM temp_active WHERE act_id =20151027");
    $smarty->assign('count',            $count+400);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
}
elseif($pid == 151118){

    $count = $GLOBALS['db']->getOne("SELECT count(id) FROM temp_active WHERE act_id =20151118");
    $smarty->assign('count',            $count+458);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
}
/*=================================2015双十一活动=============================================*/
elseif($pid == 151101 || $pid == 151112)
{

    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active151101'));

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        //A[0]:商品id，A[1]：团购id1，A[2]：团购id2，A[2]：团购id2，A[3]：折扣
        $goodsArr1 = array(
            //透明片
            array(101,279,280,'3.9'),array(4849,281,282,'4.1'),array(4934,291,292,'3.5'),array(92,297,298,'4.7'),array(767,301,302,'3.6'),array(1097,285,286,'3.7')
        );
        $goodsArr2 = array(
            //彩片
            array(4475,322,323,'4.7'),array(4851,326,327,'3.5'),array(4527,332,333,'4.9'),array(811,336,337,'3.7'),array(5036,338,339,'3.8'),array(1177,340,341,'4.1')
        );
        $goodsArr3 = array(
            //护理液
            array(589,303,304,'2.5'),array(924,305,'','4.4'),array(3338,307,'','4.4'),array(596,308,'','4.4'),array(861,311,312,'3'),array(4214,318,'','4.6')
        );
        $resArr = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['shop_price'] = $res['promote_price'];
            }
            if($v[1]){
                $res['tp1'] = $GLOBALS['db']->getOne("SELECT tuan_price FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id =".$v[1]);
                $res['tp1'] = sprintf("%.2f", floor($res['tp1']/2));
            }
            if($v[2]){
                $res['tp2'] = $GLOBALS['db']->getOne("SELECT tuan_price FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id =".$v[2]);
                $res['tp2'] = sprintf("%.2f", floor($res['tp2']/4));
            }
            $res['t1'] = $v[1];
            $res['t2'] = $v[2];
            $res['zk'] = $v[3];
            $resArr1[] = $res;
        }
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['shop_price'] = $res['promote_price'];
            }
            if($v[1]){
                $res['tp1'] = $GLOBALS['db']->getOne("SELECT tuan_price FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id =".$v[1]);
                $res['tp1'] = sprintf("%.2f", floor($res['tp1']/2));
            }
            if($v[2]){
                $res['tp2'] = $GLOBALS['db']->getOne("SELECT tuan_price FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id =".$v[2]);
                $res['tp2'] = sprintf("%.2f", floor($res['tp2']/4));
            }
            $res['t1'] = $v[1];
            $res['t2'] = $v[2];
            $res['zk'] = $v[3];
            $resArr2[] = $res;
        }
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['shop_price'] = $res['promote_price'];
            }
            if($v[1]){
                $res['tp1'] = $GLOBALS['db']->getOne("SELECT tuan_price FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id =".$v[1]);
                $res['tp1'] = sprintf("%.2f", floor($res['tp1']/2));
            }
            if($v[2]){
                $res['tp2'] = $GLOBALS['db']->getOne("SELECT tuan_price FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id =".$v[2]);
                $res['tp2'] = sprintf("%.2f", floor($res['tp2']/4));
            }
            $res['t1'] = $v[1];
            $res['t2'] = $v[2];
            $res['zk'] = $v[3];
            $resArr3[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
    }
    if($pid == 151101){
        $smarty->assign('ur_here', '双十一主会场');
    }else{
        $smarty->assign('ur_here', '招商银行活动详情');
    }

    $smarty->assign('page_title', '11.11好货1折提前享 - 易视网手机版');
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;

}
/*===============================2015双十一抽奖活动===============================================*/
elseif($pid == 15110102)
{
    if(@$_REQUEST['act'] == 'lottery'){
        if(time()<strtotime('2015-11-13 00:00:00')){
            //是否登录
            if($user_id > 0){
                //是否已抽过（当天）
                $getTimes = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE act_id = 15110102  AND order_sn = '".date('Ymd')."'   AND user_id = '".$user_id."';");//该用户当天抽取次数
                if(!$getTimes){//没有--》实物
                    $getPrice = get_prize_2015110102_sw_wap();
                    //var_dump($getPrice);die;

                    if($getPrice == 1){
                        $goods_id = 4152;
                        $goods_name = 'LB澜柏多功能隐形眼镜护理液2*10ml';
                        $res = array('award_id'=>10,'award_name'=>$goods_name);

                    }elseif($getPrice == 2){
                        $goods_id = 3948;
                        $goods_name = '蓝睛灵蕾丝魅影系列双周抛彩色隐形眼镜2片装（双色）【颜色度数写在订单备注】';
                        $res = array('award_id'=>2,'award_name'=>$goods_name);

                    }elseif($getPrice == 3){
                        $goods_id = 3712;
                        $goods_name = '海昌星眸长效保湿型多功能隐形眼镜护理液360ml';
                        $res = array('award_id'=>4,'award_name'=>$goods_name);

                    }elseif($getPrice == 4){
                        $goods_id = 655;
                        $goods_name = '科莱博化妆镜';
                        $res = array('award_id'=>7,'award_name'=>$goods_name);

                    }elseif($getPrice == 5){
                        $res = array('award_id'=>9,'award_name'=>'再玩一次,再接再厉！');
                    }else{
                        $res = array('err'=>'系统错误，请稍后重试');
                    }
                    $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`order_sn`,`remarks`)  VALUES (15110102,'$user_id','".date('Ymd')."',1);");//插入此用户当天抽奖记录
                    //实物插入购物车
                    if($getPrice != 5){
                        $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('cart') .
                            "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`)
                            VALUES ('".$user_id."', '".SESS_ID."', '$goods_id', '', '[双11抽奖]$goods_name', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '')");
                    }

                }elseif($getTimes<3){//抽过--》优惠券
                    $getPrice = get_prize_2015110102_xn_wap();
                    $GLOBALS['db']->query("UPDATE temp_active SET remarks=remarks+1 WHERE act_id = 15110102  AND order_sn = '".date('Ymd')."'   AND user_id = '".$user_id."';");//增加此数量

                    if($getPrice == 1){
                        $bonus_id = 2501;
                        $bonus_name = '5元优惠券';
                        $res = array('award_id'=>1,'award_name'=>$bonus_name);
                    }elseif($getPrice == 2){
                        $bonus_id = 2502;
                        $bonus_name = '10元优惠券';
                        $res = array('award_id'=>8,'award_name'=>$bonus_name);
                    }elseif($getPrice == 3){
                        $bonus_id = 2503;
                        $bonus_name = '50元优惠券';
                        $res = array('award_id'=>6,'award_name'=>$bonus_name);
                    }else{
                        $bonus_id = 2501;
                        $bonus_name = '5元优惠券';
                        $res = array('award_id'=>1,'award_name'=>$bonus_name);
                    }

                    $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."
                         (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");

                }else{//超过3次
                    $res = array('err'=>'您已达到当日抽奖次数上限，请明日再来');
                }
            }else{
                $res = array('err'=>'请登录后再试');//未登录
            }
        }else{
            $res = array('err'=>'活动已过期');//活动过期
        }
//var_dump($res);
        echo json_encode($res);die;
    }
    $smarty->assign('ur_here', '双十一抽奖');
    $smarty->assign('page_title', '美瞳包邮专场 - 易视网手机版');
}
// 黑五活动
elseif ($pid == 151127 || $pid == 151201)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr4 = array();
    $goodsArr5 = array();

    $goodsArr1 = array(//买二付一
        array(4751,'6.6折'),array(4938,'5.8折'),array(4142,'直降<br />25.1')
    ,array(4135,'直降<br />20.1'),array(352,'5折'),array(4851,'7.5折')
    ,array(236,'6.8折'),array(1818,'5.6折'),array(4175,'6.5折')
    ,array(2863,'4.7折'),array(240,'5.4折'),array(946,'5.7折')
    );

    $goodsArr2 = array(//透明片
        array(767,'直降<br />10元'),array(1045,'直降<br />13元'),array(119,'直降<br />41元'),array(1010,'直降<br />43元')
    ,array(105,'直降<br />13元'),array(92,'直降<br />8元'),array(4934,'直降<br />8元'),array(4801,'直降<br />3元')
    );
    $goodsArr3 = array(//美瞳
        array(1188,'直降<br />86元'),array(3630,'直降<br />100元'),array(891,'直降<br />45元'),array(227,'直降<br />8元')
    ,array(1457,'直降<br />2元'),array(1180,'直降<br />13元'),array(1475,'直降<br />32元'),array(811,'直降<br />10元')
    );
    $goodsArr4 = array(//护理液
        array(4786,'直降<br />28元'),array(924,'直降<br />16元'),array(1067,'直降<br />9元'),array(4925,'直降<br />12元')
    ,array(596,'直降<br />3.2元'),array(2279,'直降<br />12.2元'),array(627,'直降<br />13.2元'),array(912,'直降<br />15.2元')
    );
    $goodsArr5 = array(//框架
        array(1282,'直降<br />128元'),array(2708,'直降<br />128元'),array(4196,'直降<br />128元'),array(1748,'下单立<br />减30元')
    ,array(2001,'下单立<br />减30元'),array(1919,'下单立<br />减30元'),array(3189,'直降立<br />减600元'),array(3196,'直降立<br />减300元')
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,goods_thumb,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);

            $res['promote_price'] = $res['shop_price']."/2盒";
            $res['shop_price'] = $res['shop_price']."/盒";
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            $resArr1[] = $res;
        }

        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,goods_thumb,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);

            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            $resArr2[] = $res;
        }

        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,goods_thumb,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,goods_thumb,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            $resArr4[] = $res;
        }
        $resArr5 = array();
        foreach($goodsArr5 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,goods_thumb,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
			if($res['goods_id'] == 1748 || $res['goods_id'] == 2001 || $res['goods_id'] == 1919 ){
				$res['promote_price'] = $res['promote_price'] - 30;
			}
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            $resArr5[] = $res;
        }

        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
        $smarty->assign('goodsArr5',	$resArr5);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;

}
//===================================================双十二活动===========================================================//

elseif($pid == 151212 || $pid == 15121208 || $pid == 15121206 || $pid == 15121205 || $pid == 15121204 || $pid == 15121207 || $pid == 15121203 || $pid == 151211 || $pid == 15121202 || $pid == 15121201 || $pid == 151214)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));
    // 自动修改返回主会场链接
    if($now > 1450022400){ // 14号0点自动切换
        $main_mp = 151214;
    }else{
        $main_mp = 151211;
    }
    $smarty->assign('main_mp', $main_mp);

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr4 = array();
    switch($pid){
        case '151212'://主会场
        case '151211':
        case '151214':
            //A[0]:商品id，A[1]: 1:直降 2:折扣
            $goodsArr1 = array(//199-20
                array(3888,2),array(5009,2),array(902,2),array(896,2) ,array(4804,2) ,array(971,2) ,array(731,2)
            ,array(4820,2) ,array(981,2) ,array(4985,2) ,array(2786,2) ,array(580,2)
            );
            $goodsArr2 = array(//299-30
                array(3995,2),array(3994,2),array(1223,2),array(1217,2) ,array(2403,2) ,array(4937,2) ,array(5033,2) ,array(1067,2)
            ,array(4527,2),array(2048,2) ,array(5013,2) ,array(1184,2)
            );
            $goodsArr3 = array(//399-40
                array(4802,2),array(166,2),array(4849,2),array(138,2) ,array(4299,2) ,array(2556,2)  ,array(3903,2)  ,array(4976,2)
            ,array(4062,2)  ,array(320,2)  ,array(2855,2) ,array(4494,2)
            );
            $goodsArr4 = array(//太阳镜
                array(1282,1),array(2708,1),array(4196,1),array(1748,2) ,array(2001,2) ,array(1919,2) ,array(3189,1) ,array(3196,1)
            ,array(3403,2),array(2595,2),array(2159,2),array(2217,2)
            );
            break;

        case '15121208'://强生
            //A[0]:商品id，A[1]: 1:直降 2:折扣
            $goodsArr1 = array(
                array(93,1),array(1251,2),array(4782,1),array(224,1) ,array(222,1) ,array(226,1)
            );
            break;

        case '15121206'://卫康
            $goodsArr1 = array(
                array(3039,2),array(4801,1),array(773,2),array(4806,1) ,array(2403,2) ,array(138,1) ,array(1205,2) ,array(1210,2) ,array(4884,1),
                array(609,2),array(4973,2),array(4214,1),array(4557,2),array(788,2)
            );
            break;

        case '15121205'://视康
            $goodsArr1 = array(
                array(117,1),array(118,1),array(589,1),array(2686,1) ,array(5061,1) ,array(2556,1)  ,array(2911,1)  ,array(4757,1)
            );
            break;

        case '15121204'://库博
            $goodsArr1 = array(
                array(761,1),array(185,1),array(1152,1),array(1153,1) ,array(1149,2) ,array(2406,2)
            );
            break;

        case '15121207'://科莱博
            $goodsArr1 = array(
                array(175,2),array(1476,2),array(241,1),array(245,1) ,array(1475,1) ,array(946,1) ,array(1457,1) ,array(2925,2) ,array(860,2) ,array(861,1) ,array(176,2)
            );
            break;

        case '15121203'://博士伦
            $goodsArr1 = array(
                array(757,1),array(103,1),array(107,1),array(104,1) ,array(971,2) ,array(811,1) ,array(3902,1) ,array(4002,1) ,array(948,1)
            ,array(975,1),array(4976,1),array(3420,1),array(4925,1),array(789,2)
            );
        break;

    }
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".
                $GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$res['zk'];
                $res['fomart_price'] = '易视价￥'.$res['shop_price'];
            }elseif($v[1] == 2){//折扣
                $res['zk'] = number_format($res['promote_price']/$res['market_price'],2)*10;
                $res['zk'] = $res['zk'].'折';
                $res['fomart_price'] = '市场价￥'.$res['market_price'];
            }
            $resArr1[] = $res;
        }

        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".
                $GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$res['zk'];
                $res['fomart_price'] = '易视价￥'.$res['shop_price'];
            }elseif($v[1] == 2){//折扣
                $res['zk'] = number_format($res['promote_price']/$res['market_price'],2)*10;
                $res['zk'] = $res['zk'].'折';
                $res['fomart_price'] = '市场价￥'.$res['market_price'];
            }
            $resArr2[] = $res;
        }

        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".
                $GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$res['zk'];
                $res['fomart_price'] = '易视价￥'.$res['shop_price'];
            }elseif($v[1] == 2){//折扣
                $res['zk'] = number_format($res['promote_price']/$res['market_price'],2)*10;
                $res['zk'] = $res['zk'].'折';
                $res['fomart_price'] = '市场价￥'.$res['market_price'];
            }
            $resArr3[] = $res;
        }

        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".
                $GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$res['zk'];
                $res['fomart_price'] = '易视价￥'.$res['shop_price'];
            }elseif($v[1] == 2){//折扣
                $res['zk'] = number_format($res['promote_price']/$res['market_price'],2)*10;
                $res['zk'] = $res['zk'].'折';
                $res['fomart_price'] = '市场价￥'.$res['market_price'];
            }
            $resArr4[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;

}
// 圣诞活动
elseif ($pid == 151224)
{
    $now = time();
    $smarty->caching = false;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();   // 第二件半价
    $goodsArr2 = array();   // 买一送二
    $goodsArr3 = array();   // 更多优惠

    $goodsArr1 = array(//第二件半价
        array(4434,'第二件半价'),array(2403,'第二件半价'),
        array(1045,'第二件半价'),array(4802,'第二件半价'),array(731,'第二件半价'),
        array(4751,'第二件半价'),array(4804,'第二件半价'),
        array(101,'第二件半价'),array(4752,'第二件半价'),array(5065,'第二件半价')
    ,array(3631,'第二件半价'),array(662,'第二件半价'),
    );

    $goodsArr2 = array(//买一送二
        array(4807,'买一送二'),array(103,'买一送二'),array(1,'买一送二'),array(2,'买一送二'),array(3,'买一送二'),array(4,'买一送二')
    ,array(5,'买一送二'),array(6,'买一送二'),array(7,'买一送二')
    ,array(8,'买一送二'),array(9,'买一送二'),array(313,'买一送二')
    );
    $goodsArr3 = array(//更多优惠
        array(4849,'直降86元'),array(92,'直降100元'),array(767,'直降45元'),array(3959,'直降8元')
    ,array(4298,'直降2元'),array(2404,'直降13元'),array(4475,'直降32元'),array(1,'直降10元')
    ,array(2,'直降2元'),array(3,'直降13元'),array(4,'直降32元'),array(3000,'直降10元')
    ,array(589,'直降10元'),array(2958,'直降2元'),array(861,'直降13元'),array(615,'直降32元')
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1){
                $res['promote_price'] = $res['promote_price'];
            }else{
                $res['promote_price'] = $res['shop_price'];
            }
            $res['shop_price'] = floor($res['shop_price']);
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['zk'] = $v[1];
            
            //Tao：获得商品是否设置包邮
            if($res['goods_id']){
              $now = time();
                $is_by = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_free_ship WHERE goods_id = ".$res['goods_id'].
                " AND `start_time`<='$now' AND `end_time`>='$now'".
                " AND free_num = 1 AND ext_code = 0 AND kind = 0");
                if($is_by){
                    $res['is_by']  = 1;
                }  
            }
            $resArr1[] = $res;
        }

        $resArr2 = array();
        foreach($goodsArr2 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =4527");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=HO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87DECORATIVE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'SHO-BI美妆彩片DECORATIVE月抛型彩色隐形眼镜2片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3635");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO可视眸巨目';
                $res['goods_name'] = 'NEO可视眸巨目系列（8色）';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3640");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO可视眸女皇';
                $res['goods_name'] = 'NEO可视眸女皇系列（2色）';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3634");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO可视眸自然';
                $res['goods_name'] = 'NEO可视眸自然系列（2色）';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3994");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+mimi%E5%85%AC%E4%B8%BB';
                $res['goods_name'] = 'GEO mimi公主系列';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3950");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+%E6%83%91%E5%8A%9B%E7%8C%AB';
                $res['goods_name'] = 'GEO惑力猫系列';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3995");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+MIMI%E5%92%96%E5%95%A1';
                $res['goods_name'] = 'GEO MIMI咖啡系列';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3964");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+eyes';
                $res['goods_name'] = 'GEO Eyes cream系列';
            }elseif($v[0] == 9){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =3967");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['zk'] = $v[1];
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO+Grang+Grang%E7%B3%BB%E5%88%97';
                $res['goods_name'] = 'GEO Grang Grang系列';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['href'] = 'goods'.$res['goods_id'].'.html';
                $res['zk'] = $v[1];
            }
            //Tao：获得商品是否设置包邮
            if($res['goods_id']){
              $now = time();
                $is_by = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_free_ship WHERE goods_id = ".$res['goods_id'].
                " AND `start_time`<='$now' AND `end_time`>='$now'".
                " AND free_num = 1 AND ext_code = 0 AND kind = 0");
                if($is_by){
                    $res['is_by']  = 1;
                }  
            }
            $resArr2[] = $res;
        }

        $resArr3 = array();
        foreach($goodsArr3 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =2581");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E7%82%AB%E7%9C%B8%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '博士伦蕾丝炫眸彩色隐形眼镜日抛10片装（4色）';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =1189");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜30片装（4色）';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =5036");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%A7%91%E8%8E%B1%E5%8D%9A%E7%BE%8E%E5%A6%86%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85';
                $res['goods_name'] = '科莱博美妆日抛彩色隐形眼镜5片装（2色）';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =4851");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7%E7%84%95%E5%BD%A9%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '菲士康焕彩日抛型彩色隐形眼镜10片装（3色）';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =2999");
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%89%E7%9E%B3%E7%BE%8E%E6%84%9F%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85';
                $res['goods_name'] = '安瞳美感系列日抛型彩色隐形眼镜5片装（4色）';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
                if($res['is_promote'] == 1){
                    $res['promote_price'] = $res['promote_price'];
                }else{
                    $res['promote_price'] = $res['shop_price'];
                }
                $res['href'] = 'goods'.$res['goods_id'].'.html';
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                if($v[0]==767){
                    $res['zk'] = number_format($res['promote_price']/$res['market_price'],2)*10;
                    $res['zk'] = $res['zk'].'折';
                }
            }
            //Tao：获得商品是否设置包邮
            if($res['goods_id']){
              $now = time();
                $is_by = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_free_ship WHERE goods_id = ".$res['goods_id'].
                " AND `start_time`<='$now' AND `end_time`>='$now'".
                " AND free_num = 1 AND ext_code = 0 AND kind = 0");
                if($is_by){
                    $res['is_by']  = 1;
                }  
            }
            $resArr3[] = $res;
        }

        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;

}
// 16新年活动主会场   20160120更新
elseif ($pid == 160118 || $pid == 160201 || $pid == 160208)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();  // 框架
    $goodsArr2 = array();  // 透明片
    $goodsArr3 = array();  // 彩片
    $goodsArr4 = array();  // 护理液
    $goodsArr1 = array(1317,1361,1542,1328,1312,1304,1276,1283,2595,2159,2355,2208,2199,3249,3257,3443);
    $goodsArr2 = array(92,1645,101,4751,767,662,1097,1010);
    $goodsArr3 = array(1,2,359,3,4,3630,5,6);
    $goodsArr4 = array(589,5061,3338,4925,1035,592,924,609);

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v);
            $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
            $res['promote_price'] = floor($res['shop_price']);
            $res['zk'] = $res['zk'] . '折';
            $res['href'] = 'goods' . $res['goods_id'] . '.html';
            $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';

            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            if($v == 101 || $v == 662 || $v == 767){
                $res['promote_price'] = $res['shop_price'];
                if($v == 101){
                    $res['zk'] = "四盒减88元";
                }else{
                    $res['zk'] = "买三送一";
                }
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v == 1645){
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = floor($res['shop_price']);
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods'.$res['goods_id'].'.html';
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
            }

            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            if($v == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 899");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=BESCON%E5%8F%8C%E8%89%B2%E9%92%BB%E7%9F%B3%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON双色钻石系列半年抛彩色隐形眼镜1片装';
            }elseif($v == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 891");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=BESCON%E5%8F%8C%E8%89%B2%E6%B6%A6%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON双色润彩系列半年抛彩色隐形眼镜1片装';
            }elseif($v == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 355");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = "买一送一";
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO巨目系列彩色隐形眼镜';
            }elseif($v == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 884");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=GEO%E7%BA%AA%E4%BE%9D%E6%BE%B3+%E5%98%89%E4%B8%BD%E7%A7%80';
                $res['goods_name'] = 'GEO纪依澳 嘉丽秀';
            }elseif($v == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 232");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7%E7%84%95%E5%BD%A9%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = '菲士康焕彩月抛型彩色隐形眼镜2片装';
            }elseif($v == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 228");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7%E5%A4%A7%E7%BE%8E%E7%9B%AE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = '菲士康大美目月抛型彩色隐形眼镜2片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
                if($v == 359){
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = "买一送一";
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }else{
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v);
            $zk = $res['shop_price'] - $res['promote_price'];
            $res['zk'] = '直降'.$zk.'元';
            $res['promote_price'] = $res['promote_price'];
            $res['href'] = 'goods'.$res['goods_id'].'.html';
            $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';

            $resArr4[] = $res;
        }

        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;

}
// 微信抽奖活动20160205
elseif($pid == 20160205){
    if($_POST){
        $mobile = $_POST['mobile'];
        //$mobile = "13756334432";  // 测试手机号
        if(preg_match("/^(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/",$mobile)){
            //验证通过
            $res = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = 20160205 AND phone = ".$mobile);  // 判断手机号是否参加过活动
            if($res > 0){
                echo "<script>alert('您已经抽过奖啦，不要贪心哦！');</script>";
            }else{
                $smarty->assign('mobile',$mobile);
                $smarty->display('active20162501.dwt');
                exit;
            }
        }else{
            //手机号码格式不对
            echo "手机号码格式不正确！";
        }
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif($pid == 20162501){
    $mobile   = $_GET['m'];                                             // 手机号
    $result = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = 20160205 AND phone = ".$mobile);  // 判断手机号是否参加过活动
    if($result > 0){
        $yhq_arr  = array('1'=>'1','5'=>'1');   // 优惠券id数组
        $id = array_rand($yhq_arr,1);
        $res = '{"id":"'.$id.'"}';
        echo $res;die;
    }
    $prize_id = get_prize_wx_wap();                                     // 获取抽奖信息
    $yhq_arr  = array('2'=>'1','4'=>'1','6'=>'1','7'=>'1','8'=>'1');   // 优惠券id数组
    $order_sn = strtotime(date("Y-m-d")) - 28800;                       // 每一天的标记（北京时间当日零时的时间戳）
    $now      = time();
    if($now < 1454688000 || ($now < 1456156800 && $now > 1455379200)){
        // 2.5、2.14 - 2.22有笔记本大礼包
        if($prize_id == 1){
            $id = array_rand($yhq_arr,1);
            $bonus_sn = get_bonus_wx_30();
            $res = '{"id":"'.$id.'", "name":"彩片优惠券 30元","bonus_sn":"'.$bonus_sn.'"}';
        }else{
            $id = 3;
            $bonus_sn = "";
            $res = '{"id":"'.$id.'", "name":"易视精美礼品一份"}';
        }
    }else{
        // 过年期间没有笔记本大礼包
        $id = array_rand($yhq_arr,1);
        $bonus_sn = get_bonus_wx_30();
        $res = '{"id":"'.$id.'", "name":"彩片优惠券 30元","bonus_sn":"'.$bonus_sn.'"}';
    }
    // 记录抽奖手机号和对应的信息
    $GLOBALS['db']->query("INSERT INTO `temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`,`phone`) VALUES (NULL , '20160205', '0', '".$order_sn."',  '".$id."', '".$mobile."')");
    echo $res;
    exit;
}
elseif($pid == 20162502){
    $bonus_sn = $_GET['bs'];
    $smarty->assign('bonus_sn', $bonus_sn);
    $smarty->display('active'.$pid.'.dwt');
    die;
}
elseif($pid == 20162504){
    if($_POST){
        $res = $GLOBALS['db']->getOne("SELECT remarks FROM `temp_active` WHERE act_id = 20160205 AND phone = ".$_POST['mobile']);  // 手机号查询中奖信息
        if($res == 3){
            echo "<script>alert('恭喜您中了三等奖呦！');</script>";
        }elseif($res == 2 || $res == 4 || $res == 6 || $res == 7 || $res == 8){
            echo "<script>alert('恭喜您中了参与奖呦！');</script>";
        }else{
            echo "<script>alert('您还没参加过活动，请返回首页参加活动吧！');</script>";
        }
    }else{
        $res = $GLOBALS['db']->getAll("SELECT phone,remarks FROM `temp_active` WHERE act_id = 20160205 ORDER BY id DESC");  // 查询中奖信息
        foreach($res as $k=>$v){
            if($v['remarks'] == 3){
                $remark = "三等奖";
            }else{
                $remark = "参与奖";
            }
            $phone = substr_replace($v['phone'],'****',3,4);
            $mess[] = $phone . " " . $remark;
        }
        $smarty->assign('mess', $mess);
    }
}
elseif ($pid == 160214)
{
    $act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';

	if ($act == 'add_to_cart') {
		if($num == 149){
		    $ds_str_1 = str_replace(array("\r\n", "\r", "\n"), "", $_REQUEST['goodsData'][0][1]);
            $ds_str_2 = str_replace(array("\r\n", "\r", "\n"), "", $_REQUEST['goodsData'][1][1]);
            $ds_arr_1 = explode(',',$ds_str_1);
            $ds_arr_2 = explode(',',$ds_str_2);
          
			$cart149_goods1 = isset($_REQUEST['goodsData'][0][0])? $_REQUEST['goodsData'][0][0]: '0';
			$cart149_goods2 = isset($_REQUEST['goodsData'][1][0])? $_REQUEST['goodsData'][1][0]: '0';
			$cart149_goods1_zselect = isset($ds_arr_1[0])? $ds_arr_1[0]: '';
			$cart149_goods1_yselect = isset($ds_arr_1[1])? $ds_arr_1[1]: '';
			$cart149_goods2_zselect = isset($ds_arr_2[0])? $ds_arr_2[0]: '';
			$cart149_goods2_yselect = isset($ds_arr_2[1])? $ds_arr_2[1]: '';
			
			
			$total_price_149 = 149.00;	//随心配的总价 是固定的
			$package_id_149 = 113;		//礼包ID 是固定的
			
				if ($cart149_goods1 && $cart149_goods2) 
				{
					$g_1 = get_goods_info($cart149_goods1);
					$g_2 = get_goods_info($cart149_goods2);
					
					$sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '".$cart149_goods1_zselect.','.$cart149_goods1_yselect."', '1', 'unchange', '1212', '1', '')";
					$res1 = $GLOBALS['db']->query($sql1);
					$parent_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set goods_attr_id='$parent_rec_id' where rec_id=".$parent_rec_id);
					
					$sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '".$g_1['goods_sn']."', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '".$cart149_goods2_zselect.','.$cart149_goods2_yselect."', '1', 'unchange', '1212', '1', '')";
					$res2 = $GLOBALS['db']->query($sql2);
					$new_rec_id = $db->insert_id();
					$GLOBALS['db']->query("update ecs_cart set parent_id='$parent_rec_id', goods_attr_id='$new_rec_id' where rec_id=".$new_rec_id);
					
					if ($res1 && $res2) echo '149元区加入购物车!';
				}
			exit;
		}
	}
	
	$smarty->assign('goods_978', get_goods_info(978));
    
	$smarty->assign('goods_816', get_goods_info(816));
	$smarty->assign('goods_815', get_goods_info(815));
	$smarty->assign('goods_965', get_goods_info(965));
	$smarty->assign('goods_964',  get_goods_info(964));
    $smarty->assign('goods_2608', get_goods_info(2608));
	$smarty->assign('goods_2607',  get_goods_info(2607));
	$smarty->assign('goods_2606',  get_goods_info(2606));
	$smarty->assign('goods_916', get_goods_info(916));
    $smarty->assign('goods_920', get_goods_info(920));
    $smarty->assign('goods_862', get_goods_info(862));
    $smarty->assign('goods_1459', get_goods_info(1459));
    $smarty->assign('goods_241', get_goods_info(241));
    $smarty->assign('goods_242', get_goods_info(242));
    $smarty->assign('goods_243', get_goods_info(243));
    $smarty->assign('goods_1218', get_goods_info(1218));
    $smarty->assign('goods_1216', get_goods_info(1216));
    
    $smarty->assign('goodsds_978', get_goods_ds(978));
    
    $smarty->assign('goodsds_816', get_goods_ds(816));
	$smarty->assign('goodsds_815', get_goods_ds(815));
	$smarty->assign('goodsds_965', get_goods_ds(965));
	$smarty->assign('goodsds_964',  get_goods_ds(964));
    $smarty->assign('goodsds_2608', get_goods_ds(2608));
	$smarty->assign('goodsds_2607',  get_goods_ds(2607));
	$smarty->assign('goodsds_2606',  get_goods_ds(2606));
	$smarty->assign('goodsds_916', get_goods_ds(916));
    $smarty->assign('goodsds_920', get_goods_ds(920));
    $smarty->assign('goodsds_862', get_goods_ds(862));
    $smarty->assign('goodsds_1459', get_goods_ds(1459));
    $smarty->assign('goodsds_241', get_goods_ds(241));
    $smarty->assign('goodsds_242', get_goods_ds(242));
    $smarty->assign('goodsds_243', get_goods_ds(243));
    $smarty->assign('goodsds_1218', get_goods_ds(1218));
    $smarty->assign('goodsds_1216', get_goods_ds(1216));

}
// 16年元宵活动
elseif($pid == 160218){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr4 = array();
    $goodsArr1 = array(
        array(92,1), array(1,1), array(4751,3,'四盒减80元'), array(105,1)
    , array(757,1), array(103,1), array(767,3,'买三送一'), array(662,3,'买三送一')
    , array(1045,1), array(117,3,'四盒减24元'), array(1097,3,'四盒减80元'), array(1010,1)
    );
    $goodsArr2 = array(
        array(4523,3,'买一送一'), array(1,3,'买一送一'), array(2,3,'买一送一'), array(3,3,'买一送一')
    , array(4,3,'买一送一'), array(359,3,'买一送一'), array(5,3,'买一送一'), array(6,3,'买一送一')
    , array(7,3,'买一送一') , array(8,3,'买一送一'), array(9,3,'买一送一'), array(10,3,'买一送一')
    );
    $goodsArr3 = array(
        array(589,1), array(5061,1), array(585,1), array(580,1)
    , array(581,1), array(3338,1), array(4925,1), array(592,1)
    , array(1035,2), array(609,1), array(4884,1), array(4786,1)
    );
    $goodsArr4 = array(
        array(1317,2), array(1361,2), array(1328,2), array(1312,2)
    , array(1276,2), array(1283,2), array(2595,2), array(2355,2)
    , array(2208,2), array(3249,2), array(3257,2), array(3443,2)
    );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4477");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生安视优define美瞳日抛彩色隐形眼镜30片装';
            }elseif($v[0] == 117){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                $res['promote_price'] = '65.00';
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else {
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                    $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1) {
                    $res['promote_price'] = $res['promote_price'];
                } else {
                    $res['promote_price'] = $res['shop_price'];
                }
                if ($v[1] == 1) {//直降
                    $res['zk'] = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $res['zk'] . '元';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价￥' . $res['shop_price'];
                } elseif ($v[1] == 2) {//折扣
                    $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价￥' . $res['market_price'];
                } else {// 自带标签
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4135");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+premium%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti premium大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4138");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Circle%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Circle大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4143");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.phpch=1&keyword=Bescon+Tutti+Classic%E7%BB%8F%E5%85%B8%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Classic经典大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2580");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Natural%E5%8D%95%E8%89%B2%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Natural单色系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 355");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO巨目系列彩色隐形眼镜';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 351");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E4%B8%89%E8%89%B2';
                $res['goods_name'] = 'NEO公主系列三色隐形眼镜';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3635");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO可视眸巨目系列隐形眼镜';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2858");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E5%86%B0%E6%B7%87%E6%B7%8B%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '伊厶康冰淇淋半年抛彩色隐形眼镜';
            }elseif($v[0] == 9){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 920");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Cool%E8%8F%A0%E8%90%9D%E4%B8%89%E8%89%B2%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'Cool菠萝三色系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 10){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 955");
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%82%B2%E8%A7%86%E7%BB%9A%E4%B8%BD%E6%98%9F%E5%AD%A3%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = '傲视绚丽星季抛彩色隐形眼镜2片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
                if($res['is_promote'] == 1 && $v[1]==1){
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降'.$zk.'元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods'.$res['goods_id'].'.html';
                    $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
                }elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1 && $v[1]==1){
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods'.$res['goods_id'].'.html';
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
            }elseif ($v[1] == 2) {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id =".$v[0]);
            if($res['is_promote'] == 1 && $v[1]==1){
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降'.$zk.'元';
                $res['promote_price'] = $res['promote_price'];
                $res['href'] = 'goods'.$res['goods_id'].'.html';
                $res['fomart_price'] = '易视价<del>￥'.$res['shop_price'].'</del>';
            }elseif ($v[1] == 2) {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }

            $resArr4[] = $res;
        }

        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
// 16年女人节活动
elseif($pid == 160301){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(
        array(92,1), array(3035,1), array(767,3,"买三送一"), array(662,3,"买三送一")
		, array(1045,1), array(117,1), array(1097,3,"四盒减80元"), array(1010,1)
		, array(359,3,"买一送一"), array(1,3,"买一送一"), array(2,3,"买一送一"), array(3,3,"买一送一")
		, array(4,3,"买一送一"), array(5,1), array(6,1), array(7,1)
		, array(589,1), array(5061,1), array(585,1), array(580,1)
		, array(581,1), array(5122,2), array(609,1), array(4786,1)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 356");
				$res['promote_price'] = $res['shop_price'];
				$res['zk'] = $v[2];
				$res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO巨目系列彩色隐形眼镜';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 351");
				$res['promote_price'] = $res['shop_price'];
				$res['zk'] = $v[2];
				$res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E4%B8%89%E8%89%B2';
                $res['goods_name'] = 'NEO公主系列三色隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3635");
				$res['promote_price'] = $res['shop_price'];
				$res['zk'] = $v[2];
				$res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO可视眸巨目系列隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2860");
				$res['promote_price'] = $res['shop_price'];
				$res['zk'] = $v[2];
				$res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E5%86%B0%E6%B7%87%E6%B7%8B%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '伊厶康冰淇淋半年抛彩色隐形眼镜';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1184");
                $zk = $res['shop_price'] - $res['promote_price'];
				$res['zk'] = '直降' . $zk . '元';
				$res['promote_price'] = $res['promote_price'];
				$res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜10片装';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 1177");
                $zk = $res['shop_price'] - $res['promote_price'];
				$res['zk'] = '直降' . $zk . '元';
				$res['promote_price'] = $res['promote_price'];
				$res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E8%A7%86%E5%BA%B7%E7%9D%9B%E5%BD%A9%E5%A4%A9%E5%A4%A9%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85';
                $res['goods_name'] = '视康睛彩天天抛彩色隐形眼镜10片装';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4477");
                $zk = $res['shop_price'] - $res['promote_price'];
				$res['zk'] = '直降' . $zk . '元';
				$res['promote_price'] = $res['promote_price'];
				$res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生安视优define美瞳日抛彩色隐形眼镜30片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = $res['promote_price'];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                } elseif ($v[1] == 2) {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['promote_price'] = $res['shop_price'];
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 160309)
{
    $act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
	$num = isset($_REQUEST['num'])? $_REQUEST['num']: '';

	if ($act == 'add_to_cart') {
		if($num == 149){
		    $ds_str_1 = str_replace(array("\r\n", "\r", "\n"), "", $_REQUEST['goodsData'][0][1]);
            $ds_str_2 = str_replace(array("\r\n", "\r", "\n"), "", $_REQUEST['goodsData'][1][1]);
            $ds_arr_1 = explode(',',$ds_str_1);
            $ds_arr_2 = explode(',',$ds_str_2);
          
			$cart149_goods1 = isset($_REQUEST['goodsData'][0][0])? $_REQUEST['goodsData'][0][0]: '0';
			$cart149_goods2 = isset($_REQUEST['goodsData'][1][0])? $_REQUEST['goodsData'][1][0]: '0';
			$cart149_goods1_zselect = isset($ds_arr_1[0])? $ds_arr_1[0]: '';
			$cart149_goods1_yselect = isset($ds_arr_1[1])? $ds_arr_1[1]: '';
			$cart149_goods2_zselect = isset($ds_arr_2[0])? $ds_arr_2[0]: '';
			$cart149_goods2_yselect = isset($ds_arr_2[1])? $ds_arr_2[1]: '';
			
			
			$total_price_149 = 149.00;	//随心配的总价 是固定的
			$package_id_149 = 113;		//礼包ID 是固定的

            if ($cart149_goods1 && $cart149_goods2) 
            {
                $g_1 = get_goods_info($cart149_goods1);
				$g_2 = get_goods_info($cart149_goods2);
    				
                $sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[149元随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods1_zselect."')";
   				$res1 = $GLOBALS['db']->query($sql1);
                $parent_rec_id = $db->insert_id();
                        
                $sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[149元随心配]".$g_1['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods1_yselect."',$parent_rec_id)";
				$res2 = $GLOBALS['db']->query($sql2);
                        
                $sql3 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '".$g_2['goods_sn']."', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods2_zselect."',$parent_rec_id)";
   				$res3 = $GLOBALS['db']->query($sql3);
                        
                $sql4 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '".$g_2['goods_sn']."', '[149元随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods2_yselect."',$parent_rec_id)";
   				$res4 = $GLOBALS['db']->query($sql4);
                        
    			if ($res1 && $res2 && $res3 && $res4) echo '149元区加入购物车!';
            }
			exit;
		}
	}
	
	$smarty->assign('goods_816', get_goods_info(816));
	$smarty->assign('goods_815', get_goods_info(815));
	$smarty->assign('goods_5066', get_goods_info(5066));//5066
	$smarty->assign('goods_5065',  get_goods_info(5065));//5065
    $smarty->assign('goods_2608', get_goods_info(2608));
	$smarty->assign('goods_2607',  get_goods_info(2607));
	$smarty->assign('goods_2606',  get_goods_info(2606));
	$smarty->assign('goods_916', get_goods_info(916));
    $smarty->assign('goods_920', get_goods_info(920));
    $smarty->assign('goods_879', get_goods_info(879));//879
    $smarty->assign('goods_878', get_goods_info(878));//878
    $smarty->assign('goods_882', get_goods_info(882));//882
    $smarty->assign('goods_946', get_goods_info(946));//946
    $smarty->assign('goods_945', get_goods_info(945));//945
    $smarty->assign('goods_1218', get_goods_info(1218));
    $smarty->assign('goods_1216', get_goods_info(1216));
    
    
    $smarty->assign('goodsds_816', get_goods_ds(816));
	$smarty->assign('goodsds_815', get_goods_ds(815));
	$smarty->assign('goodsds_5066', get_goods_ds(5066));
	$smarty->assign('goodsds_5065',  get_goods_ds(5065));
    $smarty->assign('goodsds_2608', get_goods_ds(2608));
	$smarty->assign('goodsds_2607',  get_goods_ds(2607));
	$smarty->assign('goodsds_2606',  get_goods_ds(2606));
	$smarty->assign('goodsds_916', get_goods_ds(916));
    $smarty->assign('goodsds_920', get_goods_ds(920));
    $smarty->assign('goodsds_879', get_goods_ds(879));
    $smarty->assign('goodsds_878', get_goods_ds(878));
    $smarty->assign('goodsds_882', get_goods_ds(882));
    $smarty->assign('goodsds_946', get_goods_ds(946));
    $smarty->assign('goodsds_945', get_goods_ds(945));
    $smarty->assign('goodsds_1218', get_goods_ds(1218));
    $smarty->assign('goodsds_1216', get_goods_ds(1216));

}elseif($pid == 160317){
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(
        array(359,3,"买一送一"), array(1,3,"买一送一"), array(2,3,"买一送一"), array(3,3,"买一送一")
    ,array(4,3,"买一送一"), array(4523,3,"买一送一"), array(5,3,"买一送一"), array(6,3,"买一送一")
    ,array(7,3,"买一送一"), array(8,3,"买一送一"), array(9,3,"买一送一"), array(10,3,"买三送一")
    ,array(11,3,"买三送一"), array(12,3,"买三送一"), array(13,3,"买一送一"), array(14,3,"买一送一")
    );
    $goodsArr2 = array(
        array(105,1), array(103,1), array(104,1), array(757,1)
    ,array(92,1), array(222,1), array(1097,3,"四盒减80元"), array(117,3,"四盒减24元")
    ,array(1010,1), array(1045,1), array(185,1), array(1145,1)
    ,array(4937,1), array(139,1), array(4803,1), array(3903,1)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 356");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO巨目系列彩色隐形眼镜';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 351");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E4%B8%89%E8%89%B2';
                $res['goods_name'] = 'NEO公主系列三色隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3635");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO可视眸巨目系列隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2860");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://wwwhttp://m.easeeyes.com/category.php?eyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E5%86%B0%E6%B7%87%E6%B7%8B%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '伊厶康冰淇淋半年抛彩色隐形眼镜';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4135");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+premium%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti premium大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4139");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Circle%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Circle大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4146");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=Bescon+Tutti+Classic%E7%BB%8F%E5%85%B8%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Classic经典大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4789");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=Bescon%E6%A2%A6%E5%B9%BB%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon梦幻半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 9){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2577");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=Bescon+Tutti+Natural%E5%8D%95%E8%89%B2%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Natural单色系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 10){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 899");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=BESCON%E5%8F%8C%E8%89%B2%E9%92%BB%E7%9F%B3%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON双色钻石系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 11){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 891");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=BESCON%E5%8F%8C%E8%89%B2%E6%B6%A6%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON双色润彩系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 12){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 896");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=BESCON%E4%B8%89%E8%89%B2%E6%B6%A6%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'BESCON三色润彩系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 13){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 920");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Cool%E8%8F%A0%E8%90%9D%E4%B8%89%E8%89%B2%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'Cool菠萝三色系列年抛型彩色隐形眼镜';
            }elseif($v[0] == 14){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 955");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%82%B2%E8%A7%86%E7%BB%9A%E4%B8%BD%E6%98%9F%E5%AD%A3%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85';
                $res['goods_name'] = '傲视绚丽星季抛彩色隐形眼镜2片装';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = number_format($res['promote_price'],2);
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                } elseif ($v[1] == 3) {
                    $res['promote_price'] = number_format($res['shop_price'],2);
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = number_format($res['shop_price'],2);
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
            } elseif ($v[1] == 3) {
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr2[] = $res;
        }
        $smarty->assign('goodsArr1',$resArr1);
        $smarty->assign('goodsArr2',$resArr2);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
// 2016清明踏青活动
elseif($pid == 160329){
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(
        array(105,1), array(104,1), array(757,1), array(2405,1)
        ,array(4803,1), array(4807,1), array(4802,1), array(4937,1)
        ,array(767,3,"买三送一"), array(1045,1), array(2686,1), array(1097,1,"四盒减80元")
    );
    $goodsArr2 = array(
        array(359,3,"买一送一"), array(1,3,"买一送一"), array(2,3,"买一送一"), array(3,3,"买一送一")
        ,array(4,3,"买一送一"), array(4523,3,"买一送一"), array(5,3,"买一送一"), array(6,3,"买一送一")
        ,array(7,3,"买一送一"), array(8,3,"买一送一"), array(9,3,"买一送一"), array(10,3,"买一送一")
    );
    $goodsArr3 = array(
        array(2151,1), array(2595,1), array(2159,1), array(2355,1)
        ,array(2351,1), array(2047,2), array(2185,1), array(2208,1)
        ,array(2217,1), array(2227,1), array(3881,1), array(3333,1)
    );

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
            } elseif ($v[1] == 3) {
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 356");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO巨目系列彩色隐形眼镜';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 351");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%85%AC%E4%B8%BB%E7%B3%BB%E5%88%97%E4%B8%89%E8%89%B2';
                $res['goods_name'] = 'NEO公主系列三色隐形眼镜';
            }elseif($v[0] == 3){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 3635");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=NEO%E5%8F%AF%E8%A7%86%E7%9C%B8%E5%B7%A8%E7%9B%AE';
                $res['goods_name'] = 'NEO可视眸巨目系列隐形眼镜';
            }elseif($v[0] == 4){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2860");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E5%86%B0%E6%B7%87%E6%B7%8B%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '伊厶康冰淇淋半年抛彩色隐形眼镜';
            }elseif($v[0] == 5){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4135");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=Bescon+Tutti+premium%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti premium大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 6){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4139");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http:http://m.easeeyes.com/category.php?h=1&keyword=Bescon+Tutti+Circle%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Circle大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 7){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4146");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Classic%E7%BB%8F%E5%85%B8%E5%A4%A7%E7%9B%B4%E5%BE%84%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Classic经典大直径系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 8){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4789");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon%E6%A2%A6%E5%B9%BB%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon梦幻半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 9){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2577");
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Bescon+Tutti+Natural%E5%8D%95%E8%89%B2%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85';
                $res['goods_name'] = 'Bescon Tutti Natural单色系列半年抛彩色隐形眼镜1片装';
            }elseif($v[0] == 10){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 920");
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $v[2];
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=Cool%E8%8F%A0%E8%90%9D%E4%B8%89%E8%89%B2%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = 'Cool菠萝三色系列年抛型彩色隐形眼镜';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if ($res['is_promote'] == 1 && $v[1] == 1) {
                    $zk = $res['shop_price'] - $res['promote_price'];
                    $res['zk'] = '直降' . $zk . '元';
                    $res['promote_price'] = number_format($res['promote_price'],2);
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
                } elseif ($v[1] == 3) {
                    $res['promote_price'] = number_format($res['shop_price'],2);
                    $res['zk'] = $v[2];
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                } else {
                    $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                    $res['promote_price'] = number_format($res['shop_price'],2);
                    $res['zk'] = $res['zk'] . '折';
                    $res['href'] = 'goods' . $res['goods_id'] . '.html';
                    $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
                }
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1 && $v[1] == 1) {
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '易视价<del>￥' . $res['shop_price'] . '</del>';
            } elseif ($v[1] == 3) {
                $res['promote_price'] = number_format($res['promote_price'],2);
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            } else {
                $res['zk'] = number_format($res['shop_price'] / $res['market_price'], 2) * 10;
                $res['promote_price'] = number_format($res['shop_price'],2);
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr3[] = $res;
        }
        $smarty->assign('goodsArr1',$resArr1);
        $smarty->assign('goodsArr2',$resArr2);
        $smarty->assign('goodsArr3',$resArr3);
    }
    $now = time();
    if($now < 1459353600){
        // 3.30
        $show_tag = 1;
    }elseif($now > 1459353600 && $now < 1459440000){
        // 3.31
        $show_tag = 2;
    }else{
        // 4.1
        $show_tag = 3;
    }
    if(($now > 1459340100 && $now < 1459353600) || ($now > 1459426500 && $now < 1459440000) || $now > 1459512900){
        $is_null = 1;
    }
    $smarty->assign('show_tag',$show_tag);
    $smarty->assign('is_null',$is_null);
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
// 疯狂眼镜城活动 16年
elseif($pid == 160411){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr4 = array();
    $goodsArr5 = array();
    $goodsArr6 = array();
    $goodsArr7 = array();
    $goodsArr8 = array();
    $goodsArr9 = array();
    $goodsArr1 = array(// Levis
        array(1328,2), array(1333,2), array(1330,2)
    , array(1337,2), array(1344,2), array(1341,2)
    );
    $goodsArr2 = array(// Coach
        array(1283,2), array(2708,2), array(2710,2)
    , array(2681,2), array(4187,2), array(4198,2)
    );
    $goodsArr3 = array(// Polo
        array(1354,2), array(1357,2), array(1358,2)
    , array(1356,2), array(1355,2), array(1351,2)
    );
    $goodsArr4 = array(// Basto
        array(2047,2), array(2045,2), array(2046,2)
    , array(2052,2), array(2054,2), array(2053,2)
    );
    $goodsArr5 = array(// Helen Keller
        array(3843,2), array(3786,2), array(3912,2)
    , array(3909,2), array(3910,2), array(3845,2)
    );
    $goodsArr6 = array(// FENDI
        array(3189,2), array(3193,2), array(3188,2)
    , array(3442,2), array(3191,2), array(3190,2)
    );
    $goodsArr7 = array(// CK
        array(2595,2), array(2159,2), array(2160,2)
    , array(2163,2), array(3526,2), array(3180,2)
    );
    $goodsArr8 = array(// Sisley
        array(2198,2), array(2217,2), array(2208,2)
    , array(2199,2), array(2224,2), array(2197,2)
    );
    $goodsArr9 = array(// Hello Kitty
        array(3754,2), array(3747,2), array(3666,2)
    , array(3740,2), array(3756,2), array(3670,2)
    );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr4[] = $res;
        }
        $resArr5 = array();
        foreach($goodsArr5 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr5[] = $res;
        }
        $resArr6 = array();
        foreach($goodsArr6 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr6[] = $res;
        }
        $resArr7 = array();
        foreach($goodsArr7 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr7[] = $res;
        }
        $resArr8 = array();
        foreach($goodsArr8 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr8[] = $res;
        }
        $resArr9 = array();
        foreach($goodsArr9 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = round($res['promote_price'],0);
            } else {
                $res['promote_price'] = round($res['shop_price'],0);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['promote_price'] = $res['shop_price'];
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '市场价<del>￥' . $res['market_price'] . '</del>';
            }
            $resArr9[] = $res;
        }

        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
        $smarty->assign('goodsArr5',	$resArr5);
        $smarty->assign('goodsArr6',	$resArr6);
        $smarty->assign('goodsArr7',	$resArr7);
        $smarty->assign('goodsArr8',	$resArr8);
        $smarty->assign('goodsArr9',	$resArr9);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}// 16年劳动节活动
elseif($pid == 160501){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));
    
    

    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        
        //array[0](商品id/自定义序号) array[1](两盒单价) array[2](四盒单价) array[3](产品id) array[4](产品名)
        //彩片
        $goodsArr1 = array(
            array(1,65,60,2581,'博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'), 
            array(1,92,88,811,'博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'), 
            array(1,58,48,228,'菲士康大美目月抛型彩色隐形眼镜2片装'), 
            array(1,110,90,987,'海昌星眸日抛型彩色隐形眼镜30片装'), 
            array(1,75,65,1184,'实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜10片装'), 
            array(1,129,109,2759,'Bescon tutti系列one-day color日抛型彩色隐形眼镜30片装'), 
            array(1,115,105,4551,'SHO-BI美妆彩片PienAge日抛型彩色隐形眼镜12片装'), 
            array(1,135,99,5080,'安瞳美感系列日抛型彩色隐形眼镜20片装'), 
            array(1,188,188,4475,'强生安视优define美瞳日抛彩色隐形眼镜30片装'), 
            array(1,23,21,4636,'科莱博小黑裙系列日抛型彩色隐形眼镜5片装'), 
            array(1,150,140,950,'博士伦蕾丝明眸日抛型彩色隐形眼镜30片装'), 
            array(1,39,33,2928,'科莱博 霓彩Käthe系列双周抛彩色隐形眼镜2片装')
        );
        $resArr1 = array();
        foreach($goodsArr1 as $v){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = ".$v[3]);
                $res['price_2'] = number_format($v[1],1);
                $res['price_4'] = number_format($v[2],1);
				$res['zk_2']    = number_format($res['shop_price']-$res['price_2'],1);
                $res['zk_4']    = number_format($res['shop_price']-$res['price_4'],1);
                $res['href'] = 'category.php?keyword='.$v[4];
                $res['goods_name'] = $v[4];
                if(time>$res['promote_start_date'] && time() <$res['promote_end_date'] && !empty($res['promote_price'])){
                    $res['shop_price'] = $res['promote_price'];
                }
            $resArr1[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        
        //透明片
        $goodsArr2 = array(
            array(1,22,20,4934,'科莱博水润目清日抛型隐形眼镜10片装'), 
            array(1,36,33,970,'博士伦清朗舒适月抛隐形眼镜2片装'), 
            array(1,26,24,4801,'卫康水盈月抛隐形眼镜2片装'), 
            array(1,78,70,140,'海昌EASY DAY睛亮无感日抛隐形眼镜30片装'), 
            array(1,93,89,4849,'菲士康EveryDay日抛型隐形眼镜32片装'), 
            array(1,136,116,101,'博士伦清朗一日水润高清日抛隐形眼镜30片装'), 
            array(1,79,69,4767,'舒透氧KKR日抛型隐形眼镜30片装'), 
            array(1,89,79,767,'库博欧柯莱视防紫外线日抛型隐形眼镜30片装'), 
            array(1,158,156,92,'强生舒日日抛型隐形眼镜30片装'), 
            array(1,149,129,4751,'博士伦纯视2代硅水凝胶月抛型隐形眼镜3片装'), 
            array(1,58,56,117,'视康水润天天抛隐形眼镜30片装'), 
            array(1,59,50,2405,'海昌HAPPY GO月抛型隐形眼镜6片装')
        );
        
        $resArr2 = array();
        foreach($goodsArr2 as $v){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = ".$v[3]);
                $res['price_2'] = number_format($v[1],1);
                $res['price_4'] = number_format($v[2],1);
				$res['zk_2']    = number_format($res['shop_price']-$res['price_2'],1);
                $res['zk_4']    = number_format($res['shop_price']-$res['price_4'],1);
                $res['href'] = 'category.php?keyword='.$v[4];
                if(time>$res['promote_start_date'] && time() <$res['promote_end_date'] && !empty($res['promote_price'])){
                    $res['shop_price'] = $res['promote_price'];
                }
                $res['goods_name'] = $v[4];
            $resArr2[] = $res;
        }
        $smarty->assign('goodsArr2',	$resArr2);
        
        //护理液 array[0]产品id/团购标识 array[1]角标提示 array[2]团购id array[3]产品id
        $goodsArr3 = array(
            array(3338,''), 
            array(580,''), 
            array(596,''), 
            array(2556,''), 
            array(5163,''), 
            array(786,''), 
            array(1,'',638,1121), 
            array(4925,''), 
            array(585,''), 
            array(4884,''), 
            array(1065,''), 
            array(1,'',636,5149)
        );
        
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = ".$v[3]);
                if(time>$res['promote_start_date'] && time() <$res['promote_end_date'] && !empty($res['promote_price'])){
                    $qgtip = '折后再降'.($res['shop_price']-$res['promote_price'])."元";
                    $res['shop_price'] = $res['promote_price'];
                }
                $res['tip'] = $qgtip? $qgtip : '享'.number_format(($res['shop_price']/$res['market_price'])*10,1)."折";
                $res['href'] = 'tuan_buy_'.$v[2].'.html';
            }else{
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if(time>$res['promote_start_date'] && time() <$res['promote_end_date'] && !empty($res['promote_price'])){
                    $qgtip = '折后再降'.($res['shop_price']-$res['promote_price'])."元";
                    $res['shop_price'] = $res['promote_price'];
                }
                $res['tip'] = $qgtip? $qgtip : '享'.number_format(($res['shop_price']/$res['market_price'])*10,1)."折";
                $res['href'] = 'goods'.$v[0].'.html';
            }
            $resArr3[] = $res;
        }
        $smarty->assign('goodsArr3',	$resArr3);
        
        //框架
        $goodsArr4 = array(
            array(4873,''), 
            array(1317,''), 
            array(3816,''), 
            array(1351,''), 
            array(3883,''), 
            array(3884,''), 
            array(3887,''), 
            array(3628,''), 
            array(3333,''), 
            array(2172,''), 
            array(2173,''), 
            array(2168,'')
        );
        
        $resArr4 = array();
        foreach($goodsArr4 as $v){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
                if(time>$res['promote_start_date'] && time() <$res['promote_end_date'] && !empty($res['promote_price'])){
                    $qgtip = '折后再降'.($res['shop_price']-$res['promote_price'])."元";
                    $res['shop_price'] = $res['promote_price'];
                }
                $res['tip'] = $qgtip? $qgtip : '享'.number_format(($res['shop_price']/$res['market_price'])*10,1)."折";
                $res['href'] = 'goods'.$v[0].'.html';
                $resArr4[] = $res;
        }
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
$smarty->assign('end_status', $end_status);

if(empty($pid))
{
	ecs_header("Location: ./\n");//返回首页
}
else
{
	$smarty->assign('active_from', $refer);
	$smarty->display('active'.$pid.'.dwt');//显示具体活动页面
}
/*===================================================== 函数 ===========================================================*/
/**
 * 2015双11抽奖
实物：
LB澜柏多功能隐形眼镜护理液2*10ml			100瓶/天
蓝睛灵蕾丝魅影系列双周抛彩色隐形眼镜2片装（双色）			20瓶/天
海昌星眸长效保湿型多功能隐形眼镜护理液360ml          1瓶/天
科莱博伴侣盒（每天设置抽中数量为2盒）		2盒/天

INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '15110102',  '0',  '20151030_1',  '100');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '15110102',  '0',  '20151030_2',  '20');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '15110102',  '0',  '20151030_3',  '1');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '15110102',  '0',  '20151030_4',  '2');
 *11
 * @return 奖品id

 */
function get_prize_2015110102_sw_wap()
{
    $marr = array(50,45,2,3);//设定4个商品概率

    $m_s  = mt_rand(1, 100);

    switch($m_s)
    {
        case($m_s<=50):
            $m = 1;//LB澜柏
            break;
        case($m_s<=95 && $m_s>50):
            $m = 2;//蓝睛灵
            break;
        case($m_s<=98 && $m_s>95):
            $m = 3;//海昌星眸
            break;
        case($m_s<=100 && $m_s>98):
            $m = 4;//科莱博伴侣盒
            break;
        default:
            $m = 5;
            break;
    }

    $str = date('Ymd').'_'.$m;//拼接查询条件

    $beLeft = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE order_sn = '".$str."';");//剩余数量
    if($beLeft >0){
        $GLOBALS['db']->query("UPDATE temp_active SET remarks=remarks-1 WHERE order_sn = '".$str."';");//减去此数量
        return $m;
    }else{
        return 5;
    }
}

/**
 *  2015双11抽奖
虚拟：
5元现金券
10元现金券
50元现金券
@return 红包id
 */
function get_prize_2015110102_xn_wap()
{
    $marr = array(50,35,15);
    $m_s  = mt_rand(1, 100);
    switch($m_s)
    {
        case($m_s<=50):
            return 1;//2501
            break;
        case($m_s<=85 && $m_s>50):
            return 2;//2502
            break;
        case($m_s<=100 && $m_s>85):
            return 3;//2503
            break;
        default:
            return 1;
            break;
    }
}
//yi:获得中奖信息
function get_prize_info($page=1, $size=10, $kind=1)
{
	$arr = array();
	$start = ($page-1)*$size;
	if($kind == 2)
	{
		$start = $page;
	}
	$sql   = "select user_name from ecs_users where email like '%@144.com' and user_name<>'' limit ".$start.",".$size.";";
	$puser = $GLOBALS['db']->GetAll($sql);
	foreach($puser as $k=>$v)
	{
		$temp[0] = trim($v['user_name']);
		$dt      = (mt_rand(3,4)==3)? "3".mt_rand(11,31): ($k%2==0)?"40".mt_rand(1,9):"4".mt_rand(10,18);	
		$temp[1] = '20130'.$dt.'***'.mt_rand(11,88);
		$arr[] = $temp;
	}
	return $arr;
}

function get_prize_content($rank = 1)
{
	/*if ($rank == 1)
		return '易视眼镜网满50减5优惠券';
	elseif ($rank == 2)
		return 'Visine优能眼部清洗液30ml';
	elseif ($rank == 3)
		return '卫康视季清凉型护理液125ml';
	elseif ($rank == 4)
		return '酷视迪士尼史迪奇隐形眼镜伴侣盒';*/
	if ($rank == 1)
		return 'Visine优能眼部清洗液30ml';
	elseif ($rank == 2)
		return '卫康视季清凉型护理液125ml';
	elseif ($rank == 3)
		return '酷视迪士尼史迪奇隐形眼镜伴侣盒';
	elseif ($rank == 4)
		return '易视网满99减5优惠券';
	elseif ($rank == 5)
		return '易视网满199减30彩片优惠券';
	elseif ($rank == 6)
		return '易视网海昌品牌满99减15优惠券';
}

function get_prize_content_1108($rank = 3)
{
	if ($rank == 1)
		return '格瓦拉电影票';
	elseif ($rank == 2)
		return '格瓦拉电影5元抵扣券';
	elseif ($rank == 3)
		return '格瓦拉电影10元抵扣券1张';
	elseif ($rank == 4)
		return '满499减50优惠券';
	elseif ($rank == 5)
		return 'Visine优能眼部清洗液30ml';
	elseif ($rank == 6)
		return '卫康视季清凉型护理液125ml';
	elseif ($rank == 7)
		return '酷视迪士尼史迪奇隐形眼镜伴侣盒';
	elseif ($rank == 8)
		return '免单大奖';
}

function get_prize_content_1119($rank = 3)
{
	if ($rank == 1)
		return '格瓦拉电影票';
	elseif ($rank == 2)
		return '易视网499-50元红包';
	elseif ($rank == 3)
		return '格瓦拉10元电影抵扣券';
	elseif ($rank == 4)
		return '易视网99-5元红包';
	elseif ($rank == 5)
		return '隐形眼镜史迪奇伴侣盒';
	elseif ($rank == 6)
		return '隐形眼镜卫康护理液';
	elseif ($rank == 7)
		return '隐形眼镜优能洗眼液';
	elseif ($rank == 8)
		return '雅漾三件套';
}
/*1 5元 30  1130
	2 30元  25  1131 
	3 45元  20  1132
	4 谢谢参与  20
	5 伴侣盒  5   1133*/
function get_prize_content_0107($rank = 3)
{
	if ($rank == 1)
		return '5元抵扣券';
	elseif ($rank == 2)
		return '150减30元彩片券';
	elseif ($rank == 3)
		return '45元框架太阳镜抵扣券';
	elseif ($rank == 4)
		return '谢谢参与';
	elseif ($rank == 5)
		return '凯达趣伴侣盒';
}

function get_prize_content_140311($rank = 3)
{
	if ($rank == 1)
		return 'Visine优能眼部清洗液30ml';
	elseif ($rank == 2)
		return '凯达伴侣盒';
	elseif ($rank == 3)
		return '易视网100积分';
}

function get_prize_content_140731($rank = 3)
{
	if ($rank == 3)
		return '易视网100消费积分';
	elseif ($rank == 4)
		return '5元全场通用现金券';
	elseif ($rank == 1)
		return '谢谢参与';
}

//5元现金券：1, 10元现金券：2, 50元现金券:3, 日本和风手绢： 4,  3M口罩：5,  暖宝宝：6,  高级运动随身杯： 7
function get_prize_content_141103($rank = 3)
{
	if ($rank == 1)
		return '5元全场通用现金券';
	elseif ($rank == 2)
		return '10元全场通用现金券';
	elseif ($rank == 3)
		return '50元全场通用现金券';
	elseif ($rank == 4)
		return '日本和风手绢';
	elseif ($rank == 5)
		return '3M口罩';
	elseif ($rank == 6)
		return '暖宝宝';
	elseif ($rank == 7)
		return '高级运动随身杯';
}

//么么哒	1 护理液2 暖宝宝3 电影票2张4 3M口罩5
function get_prize_content_141118($rank = 1)
{
	if ($rank == 1)
		return '感谢一路有你，么么哒~~';
	elseif ($rank == 2)
		return '海俪恩水涟隐形眼镜护理液500ml';
	elseif ($rank == 3)
		return '暖宝宝发热贴x5';
	elseif ($rank == 4)
		return '蜘蛛网黄飞鸿电影票2张';
	elseif ($rank == 5)
		return '3M口罩';
}

/**
 *  2016微信抽奖
 *  199 - 30元美瞳券
 *  笔记本套装（过年期间每天一本）
@return 红包id
 */
function get_prize_wx_wap()
{
    $m_s  = mt_rand(1, 100);                         // 随机范围
    $order_sn = strtotime(date("Y-m-d")) - 28800;    // 获取当天时间作为订单号
    switch($m_s)
    {
        case($m_s<=95):
            $m = 1;
            break;
        case($m_s<=100 && $m_s>95):
            $m = 2;
            break;
        default:
            $m = 1;
            break;
    }
    if($m == 2){
        $res = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = 20160205 AND order_sn = ".$order_sn." AND remarks = 3");  // 判断当天是否中过笔记本
        $m = $res > 0 ? 1 : 2;
    }
    return $m;
}
/**
 * 2016微信抽奖活动 - 生产优惠券号
 */
function get_bonus_wx_30(){
    //生成红包序列号
    $num = $GLOBALS['db']->getOne("SELECT MAX(bonus_sn) FROM `ecs_user_bonus`");
    $num = $num ? floor($num / 10000) : 100000;
    $bonus_sn = $num.str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $GLOBALS['db']->query("INSERT INTO `ecs_user_bonus` (bonus_type_id, bonus_sn, unlimit) VALUES('2720', '$bonus_sn', 1)");
    return $bonus_sn;
}


function get_item_status_150113($fst_day,$sec_day,$now){

		if($now < strtotime('2015-01-'.$fst_day.' 11:00:00')){
			$item_status_1 = '00';
			$item_status_2 = '00';
		}elseif($now >= strtotime('2015-01-'.$fst_day.' 11:00:00') && $now <= strtotime('2015-01-'.$fst_day.' 11:01:00')){
			$item_status_1 = '01';
			$item_status_2 = '00';
		}elseif($now > strtotime('2015-01-'.$fst_day.' 11:01:00') && $now < strtotime('2015-01-'.$fst_day.' 16:00:00')){
			$item_status_1 = '02';
			$item_status_2 = '00';
		}elseif($now >= strtotime('2015-01-'.$fst_day.' 16:00:00') && $now <= strtotime('2015-01-'.$fst_day.' 16:01:00')){
			$item_status_1 = '02';
			$item_status_2 = '01';
		}elseif($now > strtotime('2015-01-'.$fst_day.' 16:01:00') && $now < strtotime('2015-01-'.$sec_day.' 00:00:00')){
			$item_status_1 = '02';
			$item_status_2 = '02';
		}elseif($now >= strtotime('2015-01-'.$sec_day.' 00:00:00') && $now < strtotime('2015-01-'.$sec_day.' 11:00:00')){
			$item_status_1 = '00';
			$item_status_2 = '00';
		}elseif($now >= strtotime('2015-01-'.$sec_day.' 11:00:00') && $now <= strtotime('2015-01-'.$sec_day.' 11:01:00')){
			$item_status_1 = '01';
			$item_status_2 = '00';
		}elseif($now >= strtotime('2015-01-'.$sec_day.' 11:01:00') && $now < strtotime('2015-01-'.$sec_day.' 16:00:00')){
			$item_status_1 = '02';
			$item_status_2 = '00';
		}elseif($now >= strtotime('2015-01-'.$sec_day.' 16:00:00') && $now <= strtotime('2015-01-'.$sec_day.' 16:01:00')){
			$item_status_1 = '00';
			$item_status_2 = '01';
		}elseif($now > strtotime('2015-01-'.$sec_day.' 16:01:00')){
			$item_status_1 = '02';
			$item_status_2 = '02';
			
		}
		

		return array(
			'item_status_1' => $item_status_1,
			'item_status_2' => $item_status_2
		);
}
?>