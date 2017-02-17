<?php
/**
 * 团购首页
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

$smarty->assign('helps',               get_shop_help());          //网店帮助文章
$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
$smarty->assign('ur_here',             '团购列表');

$ctime = time();

//当前团购礼包信息
$current_tuan = $GLOBALS['db']->GetRow("SELECT * FROM ecs_tuan WHERE is_promotion=1 AND promotion_start_time <= $ctime AND promotion_end_time >= $ctime 
				AND start_time <= $ctime AND end_time >= $ctime ORDER BY rec_id DESC LIMIT 1");

//下期团购信息
$next_tuan = $GLOBALS['db']->GetRow("SELECT * FROM ecs_tuan WHERE is_promotion=1 AND start_time <= $ctime AND end_time >= $ctime 
				AND promotion_start_time > $ctime ORDER BY promotion_start_time LIMIT 1");
/*$next_tuan = $GLOBALS['db']->GetRow("SELECT * FROM ecs_tuan WHERE is_promotion=1 AND start_time <= $ctime AND end_time >= $ctime 
				AND promotion_start_time > $ctime ORDER BY rec_id DESC LIMIT 1");*/

$format_ctime = array();
if ($current_tuan)
{
	//格式化价格
	if ($current_tuan['promotion_price'])
	{
		$format_cprice = explode('.', $current_tuan['promotion_price']);
		$current_tuan['prom_price_int'] = $format_cprice[0];		//整数部分
		$current_tuan['prom_price_decimal'] = $format_cprice[1];	//小数部分
	}
	
	//礼包市场价
	$current_tuan['package_market_price'] = get_package_market_price($current_tuan['rec_id']);
	
	//节省的金额
	$current_tuan['saving'] = sprintf("%01.2f", $current_tuan['package_market_price'] - $current_tuan['promotion_price']);
	
	//折扣
	$current_tuan['zhekou'] = sprintf("%01.1f", ($current_tuan['promotion_price'] / $current_tuan['package_market_price']) * 10);
	
	//格式化当前团购的开始或截止时间
	if ($ctime >= $current_tuan['promotion_start_time']) {
		//团购已开始,格式化截止时间
		$format_ctime['time_type'] = '结束';
		$format_ctime['Y'] = date('Y', $current_tuan['promotion_end_time']);
		$format_ctime['n'] = date('n', $current_tuan['promotion_end_time']);
		$format_ctime['j'] = date('j', $current_tuan['promotion_end_time']);
		$format_ctime['G'] = date('G', $current_tuan['promotion_end_time']);
		$format_ctime['i'] = date('i', $current_tuan['promotion_end_time']);
	} else {
		//团购未开始,格式化开始时间
		$format_ctime['time_type'] = '开始';
		$format_ctime['Y'] = date('Y', $current_tuan['promotion_start_time']);
		$format_ctime['n'] = date('n', $current_tuan['promotion_start_time']);
		$format_ctime['j'] = date('j', $current_tuan['promotion_start_time']);
		$format_ctime['G'] = date('G', $current_tuan['promotion_start_time']);
		$format_ctime['i'] = date('i', $current_tuan['promotion_start_time']);
	}
	
}
$format_ntime = array();
if ($next_tuan)
{
	if ($next_tuan['promotion_price'])
	{
		$format_nprice = explode('.', $next_tuan['promotion_price']);
		$next_tuan['prom_price_int'] = $format_nprice[0];
		$next_tuan['prom_price_decimal'] = $format_nprice[1];
	}
	
	$next_tuan['package_market_price'] = get_package_market_price($next_tuan['rec_id']);
	$next_tuan['saving'] = sprintf("%01.2f", $next_tuan['package_market_price'] - $next_tuan['promotion_price']);
	$next_tuan['zhekou'] = sprintf("%01.1f", ($next_tuan['promotion_price'] / $next_tuan['package_market_price']) * 10);
	
    
	//格式化下期团购时间
	if ($ctime < $next_tuan['promotion_start_time'])
	{
		$format_ntime['time_type'] = '开始';
		$format_ntime['Y'] = date('Y', $next_tuan['promotion_start_time']);
		$format_ntime['n'] = date('n', $next_tuan['promotion_start_time']);
		$format_ntime['j'] = date('j', $next_tuan['promotion_start_time']);
		$format_ntime['G'] = date('G', $next_tuan['promotion_start_time']);
		$format_ntime['i'] = date('i', $next_tuan['promotion_start_time']);
	}
	
}
//print_r($current_tuan);
//print_r($next_tuan);

$smarty->assign('format_ctime',		$format_ctime);
$smarty->assign('format_ntime',		$format_ntime);
$smarty->assign('current_tuan',		$current_tuan);
$smarty->assign('next_tuan',		$next_tuan);

//取得团购列表信息
$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('tuan')." WHERE start_time<".$ctime." AND end_time>".$ctime." AND is_show_wap=1 ORDER BY `is_promotion_wap` desc,`start_time` asc,`end_time` asc ";
$res = $db->query($sql);
$tuan_list = array();
while($row = $db->fetchRow($res))
{
    //还原序列化信息,读取字段中的礼包价和市场价
    @$ext_arr = unserialize($row['ext_info']);
    unset($row['ext_info']);
    if($ext_arr){
        foreach ($ext_arr as $key=>$val){$row[$key] = $val; }
    }
    $row['market_price']	= 	sprintf("%01.2f", get_package_market_price($row['rec_id']));			//市场价
    $row['tuan_price']		= 	sprintf("%01.2f", $row['tuan_price']); 									//团购价
    if ($row['market_price'] && $row['market_price'] > 0.00)
    {
    	$row['zhekou']		= 	sprintf("%01.1f", ($row['tuan_price'] / $row['market_price']) * 10);	//折扣
    }
    // 主推期间价格处理
    if($row['is_promotion'] == 1 && $row['promotion_start_time'] < $ctime && $row['promotion_end_time'] > $ctime){
        $row['zhekou']		= sprintf("%01.1f", ($row['promotion_price'] / $row['market_price']) * 10);	//折扣
        $row['saving']      = sprintf("%01.2f", $row['market_price'] - $row['promotion_price']); // 节省的价格
        $row['tuan_price']	= sprintf("%01.2f", $row['promotion_price']); 						//团购价
    }
    
    //处理团购价,用于前台显示
    $temp_tuan_price = explode('.', $row['tuan_price']);
    $row['tuan_price_1'] = $temp_tuan_price[0];	//整数部分
    $row['tuan_price_2'] = $temp_tuan_price[1];	//小数部分
    
    $tuan_list[] = $row;
}
$smarty->assign('tuan_list', $tuan_list);

$smarty->display('tuan.dwt');


//计算礼包的市场价(各商品市场价累加)
function get_package_market_price($tuan_id=0) {
	if (intval($tuan_id) > 0) {
		$tuan_market_price = $GLOBALS['db']->GetOne("SELECT SUM(a.market_price*b.goods_number) AS package_market_price FROM ecs_goods a 
									LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id WHERE b.tuan_id = $tuan_id");
		if ($tuan_market_price)
		{
			return $tuan_market_price;
		}
		else
		{
			return $GLOBALS['db']->GetOne("SELECT SUM(a.shop_price*b.goods_number) AS package_market_price FROM ecs_goods a 
									LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id WHERE b.tuan_id = $tuan_id");
		}
	}
}

?>