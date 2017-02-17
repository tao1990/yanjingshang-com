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
$smarty->assign('user_id',              $_SESSION['user_id']);
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
// 周四专场
if ($pid == 160519 || $pid == 4){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr1 = array(
        array(4807,48,44), array(4803,126,116), array(4800,44,40)
    );
    $goodsArr2 = array(
        array(786,3), array(4884,3), array(609,3)
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
            $res['zk'] = ($res['shop_price'] - $v[2])*4;
            $res['href'] = 'goods' . $res['goods_id'] . '.html';
            $res['fomart_price'] = '易视价' . $res['shop_price'] . '元';
            $res['promote_price_2'] = $v[1];
            $res['promote_price_4'] = $v[2];
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
            $res['zk'] = $res['shop_price'] - $res['promote_price'];
            $res['href'] = 'goods' . $res['goods_id'] . '.html';
            $res['fomart_price'] = '易视价' . $res['shop_price'] . '元';

            $resArr2[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
// 周二和16年五一大促活动分会场   $pid == 2 ||
elseif($pid == 16041801 || $pid == 2){
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
// 16年五一大促活动分会场    $pid == 4 ||
elseif($pid == 16041802){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr3 = array();
    $goodsArr4 = array();
    $goodsArr1 = array(
        array(1144,1), array(1,1), array(2,1), array(5224,1)
    , array(3,1), array(633,1), array(2824,1), array(5089,1)
    );
    $goodsArr2 = array(
        array(4938,1), array(1,1), array(2,1), array(3,1)
    , array(4,1), array(5,1), array(6,1), array(860,1)
    );
    $goodsArr3 = array(
        array(977,1), array(3035,1), array(5164,1), array(1,1)
    , array(2,1), array(997,1), array(2748,1), array(634,1)
    );
    $goodsArr4 = array(
        array(1151,1), array(163,1), array(4298,1), array(1150,1)
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
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 5213");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://www.easeeyes.com/lab-151.html';
                $res['goods_name'] = '实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装';
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
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 4641");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E7%A7%91%E8%8E%B1%E5%8D%9A%E5%B0%8F%E9%BB%91%E8%A3%99%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '科莱博小黑裙系列半年抛彩色隐形眼镜';
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
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 2858");
                $zk = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '直降' . $zk . '元';
                $res['promote_price'] = number_format($res['promote_price'],1);
                $res['fomart_price'] = '<del>￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://www.easeeyes.com/categorysea.php?search=1&keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E5%86%B0%E6%B7%87%E6%B7%8B%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C';
                $res['goods_name'] = '伊厶康冰淇淋半年抛彩色隐形眼镜';
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
elseif ($pid == 160324)
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
elseif ($pid == 160903)//周六活动
{
    $w = date("w");
    
    $div1 = 'hide';
    $div2 = 'hide';
    if($w == 6){
        $div1 = "show";
    }elseif($w == 0){
        $div2 = "show";
    }else{
        $div1 = "show";
    }
    $smarty->assign('div1',	$div1);
    $smarty->assign('div2',	$div2);
    
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
elseif($pid == 160712){

    $count = $GLOBALS['db']->getOne("SELECT count(id) FROM temp_active WHERE act_id =20161122");
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
elseif($pid == 160501 || $pid == 160502){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));
    
    
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        if($now<strtotime('2016-04-28 13:00:00')){//1
            $smarty->assign('dayIndex',	    0);
            $smarty->assign('timeIndex',	0);
            $smarty->assign('ms_img',	'm1');
        }elseif($now>strtotime('2016-04-28 13:00:00') && $now<strtotime('2016-04-28 20:00:00')){//2
            $smarty->assign('dayIndex',	    0);
            $smarty->assign('timeIndex',	1);
            $smarty->assign('ms_img',	'm2');
        }elseif($now>strtotime('2016-04-28 20:00:00') && $now<strtotime('2016-04-29 10:00:00')){//3
            $smarty->assign('dayIndex',	    0);
            $smarty->assign('timeIndex',	2);
            $smarty->assign('ms_img',	'm3');
        }elseif($now>strtotime('2016-04-29 10:00:00') && $now<strtotime('2016-04-29 13:00:00')){//4
            $smarty->assign('dayIndex',	    1);
            $smarty->assign('timeIndex',	0);
            $smarty->assign('ms_img',	'm4');
        }elseif($now>strtotime('2016-04-29 13:00:00') && $now<strtotime('2016-04-29 20:00:00')){//5
            $smarty->assign('dayIndex',	    1);
            $smarty->assign('timeIndex',	1);
            $smarty->assign('ms_img',	'm5');
        }elseif($now>strtotime('2016-04-29 20:00:00') && $now<strtotime('2016-04-30 10:00:00')){//6
            $smarty->assign('dayIndex',	    1);
            $smarty->assign('timeIndex',	2);
            $smarty->assign('ms_img',	'm6');
        }elseif($now>strtotime('2016-04-30 10:00:00') && $now<strtotime('2016-04-30 13:00:00')){//7
            $smarty->assign('dayIndex',	    2);
            $smarty->assign('timeIndex',	0);
            $smarty->assign('ms_img',	'm7');
        }elseif($now>strtotime('2016-04-30 13:00:00') && $now<strtotime('2016-04-30 20:00:00')){//8
            $smarty->assign('dayIndex',	    2);
            $smarty->assign('timeIndex',	1);
            $smarty->assign('ms_img',	'm8');
        }elseif($now>strtotime('2016-04-30 20:00:00') && $now<strtotime('2016-05-01 10:00:00')){//9
            $smarty->assign('dayIndex',	    2);
            $smarty->assign('timeIndex',	2);
            $smarty->assign('ms_img',	'm9');
        }elseif($now>strtotime('2016-05-01 10:00:00') && $now<strtotime('2016-05-01 13:00:00')){//10
            $smarty->assign('dayIndex',	    3);
            $smarty->assign('timeIndex',	0);
            $smarty->assign('ms_img',	'm10');            
        }elseif($now>strtotime('2016-05-01 13:00:00') && $now<strtotime('2016-05-01 20:00:00')){//11
            $smarty->assign('dayIndex',	    3);
            $smarty->assign('timeIndex',	1);
            $smarty->assign('ms_img',	'm11');
        }elseif($now>strtotime('2016-05-01 20:00:00') && $now<strtotime('2016-05-02 10:00:00')){//12
            $smarty->assign('dayIndex',	    3);
            $smarty->assign('timeIndex',	2);
            $smarty->assign('ms_img',	'm12');
        }elseif($now>strtotime('2016-05-02 10:00:00') && $now<strtotime('2016-05-02 13:00:00')){//13
            $smarty->assign('dayIndex',	    4);
            $smarty->assign('timeIndex',	0);
            $smarty->assign('ms_img',	'm13');
        }elseif($now>strtotime('2016-05-02 13:00:00') && $now<strtotime('2016-05-02 20:00:00')){//14
            $smarty->assign('dayIndex',	    4);
            $smarty->assign('timeIndex',	1);
            $smarty->assign('ms_img',	'm14');
        }elseif($now>strtotime('2016-05-02 20:00:00')){//15
            $smarty->assign('dayIndex',	    4);
            $smarty->assign('timeIndex',	2);
            $smarty->assign('ms_img',	'm15');
        }
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
                $res['zk_4']    = number_format(($res['shop_price']-$res['price_4'])*4,1);
                //$res['href'] = 'category.php?keyword='.$v[4];
                $res['href'] = 'goods'.$v[3].'.html';
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
            array(1,93,89,4849,'菲士康EveryDay日抛型隐形眼镜32片装'), 
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
                $res['zk_4']    = number_format(($res['shop_price']-$res['price_4'])*4,1);
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
                if($v[3] == 5149){
                    $res['shop_price'] = 50;
                    $res['goods_name'] = '科莱博颂润隐形眼镜护理液500ml+120ml';
                }
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
// 16年防紫外线专场活动
elseif($pid == 160503){
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr2 = array();
    $goodsArr1 = array(
        array(1,2), array(93,3,"减3元送护理液"), array(2,2), array(185,3,"赠护理液")
		, array(1011,3,"赠护理液"), array(1149,3,"赠5片装日抛"), array(834,2), array(149,2)
		, array(5098,3,"减6元送护理液"), array(1145,1), array(1144,1), array(4299,1)
		, array(124,2), array(3037,2), array(4937,3,"减3元送护理液"), array(5164,1)
    );
    $goodsArr2 = array(
        array(3879,1), array(3883,1), array(3887,1), array(3884,1)
		, array(3886,1), array(3880,1), array(3881,1), array(3885,1)
    );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
			if($v[0] == 1){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 825");
                $res['promote_price'] = number_format($res['shop_price'],1);
				$res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
				$res['zk'] = $res['zk'] . '折';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E8%88%92%E6%BE%88%E6%9C%88%E6%8A%9B%E5%9E%8B%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生舒澈月抛型隐形眼镜6片装';
            }elseif($v[0] == 2){
                $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id = 95");
                $res['promote_price'] = number_format($res['shop_price'],1);
				$res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
				$res['zk'] = $res['zk'] . '折';
                $res['fomart_price'] = '<del>市场价￥' . $res['market_price'] . '</del>';
                $res['href'] = 'http://m.easeeyes.com/category.php?search=1&keyword=%E5%BC%BA%E7%94%9F%E8%88%92%E6%99%B0%E6%9C%88%E6%8A%9B%E5%9E%8B%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85';
                $res['goods_name'] = '强生舒晰月抛型隐形眼镜6片装';
            }else{
				$res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
					$GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
				if ($res['is_promote'] == 1) {
					$res['promote_price'] = number_format($res['promote_price'],1);
				} else {
					$res['promote_price'] = number_format($res['shop_price'],1);
				}
				if($v[1] == 1){//直降
					$res['zk'] = $res['shop_price'] - $res['promote_price'];
					$res['zk'] = '直降' . $res['zk'] . '元';
					$res['href'] = 'goods' . $res['goods_id'] . '.html';
					$res['fomart_price'] = '<del>市场价￥' . number_format($res['market_price']) . '</del>';
				}elseif($v[1] == 3){// 自带标签
					$res['zk'] = $v[2];
					$res['href'] = 'goods' . $res['goods_id'] . '.html';
					$res['fomart_price'] = '<del>市场价￥' . number_format($res['market_price']) . '</del>';
				}else{//折扣
					$res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
					$res['zk'] = $res['zk'] . '折';
					$res['href'] = 'goods' . $res['goods_id'] . '.html';
					$res['fomart_price'] = '<del>市场价￥' . number_format($res['market_price']) . '</del>';
				}
			}
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
            if ($res['is_promote'] == 1) {
                $res['promote_price'] = number_format($res['promote_price'],1);
            } else {
                $res['promote_price'] = number_format($res['shop_price'],1);
            }
            if($v[1] == 1){//直降
                $res['zk'] = $res['shop_price'] - $res['promote_price'];
                $res['zk'] = '立减' . $res['zk'] . '元';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . number_format($res['market_price']) . '</del>';
            }elseif($v[1] == 3){// 自带标签
                $res['zk'] = $v[2];
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . number_format($res['market_price']) . '</del>';
            }else{//折扣
                $res['zk'] = number_format($res['promote_price'] / $res['market_price'], 2) * 10;
                $res['zk'] = $res['zk'] . '折';
                $res['href'] = 'goods' . $res['goods_id'] . '.html';
                $res['fomart_price'] = '<del>市场价￥' . number_format($res['market_price']) . '</del>';
            }
            $resArr2[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
// 16年520活动
elseif ($pid == 160520)
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
    				
                $sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[爱眼日随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods1_zselect."')";
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

    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr1 = array(
        901,902,905,900
    ,890,891,892,894
    ,3640,3641,1459,1461
    ,1457,1460,5125,5128
    ,1091,1090,4865,4866
    ,4867,4868,5065,5066
    ,4523,4524,358,5246
    ,5247,5249,945,946
    ,4175,4178,1084,1087
    ,4996,5002,3947,3948
    );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            $res['goods_ds']     = get_goods_ds($v);
            $res['shop_price_t'] = number_format($res['shop_price'] * 2,2);
            $resArr1[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}// 16年招行活动
elseif ($pid == 160518)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array(93,101,91,4751,825,1010,118,4229,658,952,3039,834);
    $goodsArr2 = array(813,222,2581,4477,2189,5267,2760,987,352,359,1186,4553);
    $goodsArr3 = array(592,2296,3338,2192,580,2191,1066,2748,585,4884,860,3420);
    $goodsArr4 = array(2691,3880,3881,4198,1369,1400,4657,3442);
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            if($res['promote_price']>0 && $now>$res['promote_start_date'] && $now <$res['promote_end_date']){
                $res['shop_price'] = $res['promote_price'];
            }
            $resArr1[] = $res;
        }
        $resArr2 = array();
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            if($res['promote_price']>0 && $now>$res['promote_start_date'] && $now <$res['promote_end_date']){
                $res['shop_price'] = $res['promote_price'];
            }
            $resArr2[] = $res;
        }
        $resArr3 = array();
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            if($res['promote_price']>0 && $now>$res['promote_start_date'] && $now <$res['promote_end_date']){
                $res['shop_price'] = $res['promote_price'];
            }
            $resArr3[] = $res;
        }
        $resArr4 = array();
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            if($res['promote_price']>0 && $now>$res['promote_start_date'] && $now <$res['promote_end_date']){
                $res['shop_price'] = $res['promote_price'];
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
}// 16年爱眼日活动
elseif ($pid == 160606)
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
			
			
			$total_price_149 = 168.00;	//随心配的总价 是固定的
			$package_id_149 = 113;		//礼包ID 是固定的

          
            if ($cart149_goods1 && $cart149_goods2) 
            {
                $g_1 = get_goods_info($cart149_goods1);
				$g_2 = get_goods_info($cart149_goods2);
    				
                $sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[爱眼日随心配]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods1_zselect."')";
   				$res1 = $GLOBALS['db']->query($sql1);
                $parent_rec_id = $db->insert_id();
                        
                $sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[爱眼日随心配]".$g_1['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods1_yselect."',$parent_rec_id)";
				$res2 = $GLOBALS['db']->query($sql2);
                        
                $sql3 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '".$g_2['goods_sn']."', '[爱眼日随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods2_zselect."',$parent_rec_id)";
   				$res3 = $GLOBALS['db']->query($sql3);
                        
                $sql4 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '".$g_2['goods_sn']."', '[爱眼日随心配]".$g_2['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods2_yselect."',$parent_rec_id)";
   				$res4 = $GLOBALS['db']->query($sql4);
                        
    			if ($res1 && $res2 && $res3 && $res4) echo '168元区加入购物车!';
            }
			exit;
		}
	}

    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-active'.$pid));

    $goodsArr1 = array();
    $goodsArr1 = array(
                955,3929,951,4938,964,5142,5013,5015,1219,920,916,917,919,4176,996,5254,2959,580
            );
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $resArr1 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            $res['goods_ds']     = get_goods_ds($v);
            //$res['goods_dsy']     = get_goods_ds($v);
            $res['shop_price_t'] = number_format($res['shop_price'] * 2,2);
           
            if(in_array($res['goods_id'],array(996,5254,2959,580))){
                $res['is_hly'] = 1;
            }
            $resArr1[] = $res;
        }
        $smarty->assign('goodsArr1',	$resArr1);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}
elseif ($pid == 160618 || $pid == 160619 || $pid == 160620)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    
    if($now > strtotime('2016-06-18 00:00:00')){
        $smarty->assign('newBg',	1);
    }
    
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
       //透明片    
       $goodsArr1 = array(
                101,105,140,
                array(95,'category.php?keyword=%E5%BC%BA%E7%94%9F%E8%88%92%E6%99%B0%E6%9C%88%E6%8A%9B%E5%9E%8B%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
                185,731,1150,130,1010,4751,93,118
            );
       foreach($goodsArr1 as $k=> $v){
            $goods_id = $v;
            $goods_link = '';
            if(is_array($v)){
                $goods_id   = $v[0];
                $goods_link = $v[1];
                $goods_tips = $v[2];
            }
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $goods_id);
            
            $arr1[$k]['goods_id']        = $goods_id; 
            $arr1[$k]['goods_name']      = $res['goods_name']; 
            $arr1[$k]['shop_price']      = ($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price'];
            $arr1[$k]['market_price']    = $res['market_price'];
            $arr1[$k]['goods_img']       = $res['original_img'];
            $arr1[$k]['goods_link']      = empty($goods_link)? 'goods'.$goods_id.'.html':$goods_link; 
            $arr1[$k]['goods_tips']      = empty($goods_tips)? '直降'.($arr1[$k]['market_price']-$arr1[$k]['shop_price']).'元':$goods_tips;
            
       }
       
       //彩片     
       $goodsArr2 = array(
                array(2998,'category.php?keyword=%E5%AE%89%E7%9E%B3%E7%BE%8E%E6%84%9F%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85'),
                array(5016,'category.php?keyword=%E8%93%9D%E7%9D%9B%E7%81%B5%E8%95%BE%E4%B8%9D%E9%AD%85%E5%BD%B1%E7%B3%BB%E5%88%97%E5%8F%8C%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
                array(2189,'category_154.html'),
                array(1187,'category.php?keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85'),
                222,
                array(4281,'category.php?keyword=G%26G%E8%A5%BF%E6%AD%A6Secret+CandyEyes%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
                array(356,'goods356.html','买一送一'),
                array(987,'category.php?keyword=%E6%B5%B7%E6%98%8C%E6%98%9F%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85'),
                array(2581,'category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E7%82%AB%E7%9C%B8%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C'),
                array(811,'category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E4%B8%A4%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
                array(228,'category.php?keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7%E5%A4%A7%E7%BE%8E%E7%9B%AE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85'),
                array(5114,'categorysea.php?search=1&keyword=%20%E4%BC%8A%E5%8E%B6%E5%BA%B7%E7%94%9C%E5%BF%83%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B1%E7%89%87%E8%A3%85')
            );
       foreach($goodsArr2 as $k=> $v){
            $goods_id = $v;
            $goods_link = '';
            if(is_array($v)){
                $goods_id   = $v[0];
                $goods_link = $v[1];
                $goods_tips = $v[2];
            }
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $goods_id);
            
            $arr2[$k]['goods_id']        = $goods_id; 
            $arr2[$k]['goods_name']      = $res['goods_name']; 
            $arr2[$k]['shop_price']      = ($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price'];
            $arr2[$k]['market_price']    = $res['market_price'];
            $arr2[$k]['goods_img']       = $res['original_img'];
            $arr2[$k]['goods_link']      = empty($goods_link)? 'goods'.$goods_id.'.html':$goods_link; 
            $arr2[$k]['goods_tips']      = empty($goods_tips)? '直降'.($arr2[$k]['market_price']-$arr2[$k]['shop_price']).'元':$goods_tips;
       }
       
       //护理液    
       $goodsArr3 = array(
                585,592,580,596,2867,3338,788,860,599,924,631,2614
            );
       foreach($goodsArr3 as $k=>$v){
            $goods_id = $v;
            $goods_link = '';
            if(is_array($v)){
                $goods_id   = $v[0];
                $goods_link = $v[1];
                $goods_tips = $v[2];
            }
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $goods_id);
            
            $arr3[$k]['goods_id']        = $goods_id; 
            $arr3[$k]['goods_name']      = $res['goods_name']; 
            $arr3[$k]['shop_price']      = ($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price'];
            $arr3[$k]['market_price']    = $res['market_price'];
            $arr3[$k]['goods_img']       = $res['original_img'];
            $arr3[$k]['goods_link']      = empty($goods_link)? 'goods'.$goods_id.'.html':$goods_link; 
            $arr3[$k]['goods_tips']      = empty($goods_tips)? '直降'.($arr3[$k]['market_price']-$arr3[$k]['shop_price']).'元':$goods_tips;
       }
       
       //框架墨镜 
       $goodsArr4 = array(
                4655,4744,3883,2172,2595,2355,1791,1317,1319,1788,3816,1390
            );
       foreach($goodsArr4 as $k=>$v){
            $goods_id = $v;
            $goods_link = '';
            if(is_array($v)){
                $goods_id   = $v[0];
                $goods_link = $v[1];
                $goods_tips = $v[2];
            }
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $goods_id);
            
            $arr4[$k]['goods_id']        = $goods_id; 
            $arr4[$k]['goods_name']      = $res['goods_name']; 
            $arr4[$k]['shop_price']      = ($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price'];
            $arr4[$k]['market_price']    = $res['market_price'];
            $arr4[$k]['goods_img']       = $res['original_img'];
            $arr4[$k]['goods_link']      = empty($goods_link)? 'goods'.$goods_id.'.html':$goods_link; 
            $arr4[$k]['goods_tips']      = empty($goods_tips)? '直降'.($arr4[$k]['market_price']-$arr4[$k]['shop_price']).'元':$goods_tips;
       }
       
        $smarty->assign('goodsArr1',	$arr1);
        $smarty->assign('goodsArr2',	$arr2);
        $smarty->assign('goodsArr3',	$arr3);
        $smarty->assign('goodsArr4',	$arr4);
    }
}elseif ($pid == 16061810)
{
    header("Location:lab-156.html");
}
elseif ($pid == 16061807)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
       //透明片    
       $goodsArr1 = array(
                243,242,241,1482,1477,249,248,247,246
            );
       foreach($goodsArr1 as $k=> $v){
            $goods_id = $v;
            $goods_link = '';
            if(is_array($v)){
                $goods_id   = $v[0];
                $goods_link = $v[1];
                $goods_tips = $v[2];
            }
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $goods_id);
            
            $arr1[$k]['goods_id']        = $goods_id; 
            $arr1[$k]['goods_name']      = $res['goods_name']; 
            $arr1[$k]['shop_price']      = ($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price'];
            $arr1[$k]['market_price']    = $res['market_price'];
            $arr1[$k]['goods_img']       = $res['original_img'];
            $arr1[$k]['goods_link']      = empty($goods_link)? 'goods'.$goods_id.'.html':$goods_link; 
            $arr1[$k]['goods_tips']      = empty($goods_tips)? '直降'.($arr1[$k]['market_price']-$arr1[$k]['shop_price']).'元':$goods_tips;
            
       }
        $smarty->assign('goodsArr1',	$arr1);
    }
    
}elseif ($pid == 160617)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
       //透明片    
       $goodsArr1 = array(107,101,103,113,91,93,1251,825,2686,1097,117,1010);
       foreach($goodsArr1 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr1[$k]['goods_id']        = $v; 
            $arr1[$k]['goods_name']      = $res['goods_name']; 
            $arr1[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr1[$k]['market_price']    = $res['market_price'];
            $arr1[$k]['goods_img']       = $res['original_img'];
            $arr1[$k]['goods_link']      = 'goods'.$v.'.html'; 
            
       }
       //彩片 
       $goodsArr2 = array(4476,226,224,227,5145,5143,948,4325,1189,3631,4494,3888);
       foreach($goodsArr2 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr2[$k]['goods_id']        = $v; 
            $arr2[$k]['goods_name']      = $res['goods_name']; 
            $arr2[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr2[$k]['market_price']    = $res['market_price'];
            $arr2[$k]['goods_img']       = $res['original_img'];
            $arr2[$k]['goods_link']      = 'goods'.$v.'.html'; 
            
       }
       //护理液  
       $goodsArr3 = array(4925,3338,3132,592,580,581,1066,1065,2786,2614,788,4884);
       foreach($goodsArr3 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr3[$k]['goods_id']        = $v; 
            $arr3[$k]['goods_name']      = $res['goods_name']; 
            $arr3[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr3[$k]['market_price']    = $res['market_price'];
            $arr3[$k]['goods_img']       = $res['original_img'];
            $arr3[$k]['goods_link']      = 'goods'.$v.'.html'; 
            
       }
        $smarty->assign('goodsArr1',	$arr1);
        $smarty->assign('goodsArr2',	$arr2);
        $smarty->assign('goodsArr3',	$arr3);
    }
    
}elseif ($pid == 160703)
{
    $now = time();
    $smarty->caching = false;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
       //透明片    
       $goodsArr1 = array(4934,5164,117,2404,1645,148);
       foreach($goodsArr1 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr1[$k]['goods_id']        = $v; 
            $arr1[$k]['goods_name']      = $res['goods_name']; 
            $arr1[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr1[$k]['market_price']    = $res['market_price'];
            $arr1[$k]['goods_img']       = $res['original_img'];
            $arr1[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr1[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }
            
       }
       //彩片 
       $goodsArr2 = array(981,1274,1186,5241,4087,4868,4822,901,4845,4980,972,2336);
       foreach($goodsArr2 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr2[$k]['goods_id']        = $v; 
            $arr2[$k]['goods_name']      = $res['goods_name']; 
            $arr2[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr2[$k]['market_price']    = $res['market_price'];
            $arr2[$k]['goods_img']       = $res['original_img'];
            $arr2[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr2[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }
            
       }
       //护理液  
       $goodsArr3 = array(1065,599,596,4973,600,924,1035,581);
       foreach($goodsArr3 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr3[$k]['goods_id']        = $v; 
            $arr3[$k]['goods_name']      = $res['goods_name']; 
            $arr3[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr3[$k]['market_price']    = $res['market_price'];
            $arr3[$k]['goods_img']       = $res['original_img'];
            $arr3[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr3[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }
            
       }
       //太阳镜  
       $goodsArr4 = array(2045,4699,5308,2355);
       foreach($goodsArr4 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr4[$k]['goods_id']        = $v; 
            $arr4[$k]['goods_name']      = $res['goods_name']; 
            $arr4[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr4[$k]['market_price']    = $res['market_price'];
            $arr4[$k]['goods_img']       = $res['original_img'];
            $arr4[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr4[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }
            
            
       }
        $smarty->assign('goodsArr1',	$arr1);
        $smarty->assign('goodsArr2',	$arr2);
        $smarty->assign('goodsArr3',	$arr3);
        $smarty->assign('goodsArr4',	$arr4);
    }
    
}elseif ($pid == 160701)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
       //透明片    
       $goodsArr1 = array(767,101,117,2405,92,5164,93,119,140,1045,1010,4802);
       foreach($goodsArr1 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr1[$k]['goods_id']        = $v; 
            $arr1[$k]['goods_name']      = $res['goods_name']; 
            $arr1[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr1[$k]['market_price']    = $res['market_price'];
            $arr1[$k]['goods_img']       = $res['original_img'];
            $arr1[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr1[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }else{
                if($v == 101){$arr1[$k]['tips'] = '买三送一';}
                if($v == 2405){$arr1[$k]['tips'] = '赠护理液';}
                if($v == 92){$arr1[$k]['tips'] = '两盒立减';}
                if($v == 5164){$arr1[$k]['tips'] = '赠润眼液';}
                if($v == 119){$arr1[$k]['tips'] = '赠护理液';}
                if($v == 1045){$arr1[$k]['tips'] = '两盒立减';}
                if($v == 1010){$arr1[$k]['tips'] = '赠润眼液';}
                if($v == 4802){$arr1[$k]['tips'] = '赠护理液';}
            }
            
       }
       //彩片 
       $goodsArr2 = array(227,811,359,2581,4475,987,1177,818,4867,901,4636,4281);
       foreach($goodsArr2 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr2[$k]['goods_id']        = $v; 
            $arr2[$k]['goods_name']      = $res['goods_name']; 
            $arr2[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr2[$k]['market_price']    = $res['market_price'];
            $arr2[$k]['goods_img']       = $res['original_img'];
            $arr2[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr2[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }else{
                if($v == 359){$arr2[$k]['tips'] = '买一赠一';}
                if($v == 2581){$arr2[$k]['tips'] = '买三送一';}
                if($v == 4867){$arr2[$k]['tips'] = '买一赠一';}
                if($v == 4636){$arr2[$k]['tips'] = '买三送一';}
            }
            
       }
       //护理液  
       $goodsArr3 = array(1035,4925,924,599,3338,609,596,587,997,580,2865,860);
       foreach($goodsArr3 as $k=> $v){
        
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
            $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            
            $arr3[$k]['goods_id']        = $v; 
            $arr3[$k]['goods_name']      = $res['goods_name']; 
            $arr3[$k]['shop_price']      = floor(($res['is_promote'] == 1 && $now >$res['promote_start_date'] && $now < $res['promote_end_date'])? $res['promote_price']:$res['shop_price']);
            $arr3[$k]['market_price']    = $res['market_price'];
            $arr3[$k]['goods_img']       = $res['original_img'];
            $arr3[$k]['goods_link']      = 'goods'.$v.'.html'; 
            if($res['promote_price']>0 && $now >$res['promote_start_date'] && $now < $res['promote_end_date']){
                $arr3[$k]['zk']              = $res['shop_price'] - $res['promote_price'];
            }else{
                if($v == 359){$arr3[$k]['tips'] = '下单立减';}
            }
            
       }
      
        $smarty->assign('goodsArr1',	$arr1);
        $smarty->assign('goodsArr2',	$arr2);
        $smarty->assign('goodsArr3',	$arr3);
    }
    
}
elseif ($pid == 160801 || $pid == 160825 || $pid == 160829)//主会场
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        
        $goodsArr1 = array(
            array(4475,'','category.php?keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85'),
            array(4500,'','category.php?keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7+%E5%A4%A7%E7%BE%8E%E7%9B%AE+%E6%97%A5%E6%8A%9B'),
            array(5352,'下单立减','category.php?keyword=%E8%A7%86%E5%BA%B7%E7%9D%9B%E5%BD%A9%E5%A4%A9%E5%A4%A9%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85'),
            array(1186,'','category.php?keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85'),
            array(1146,'','category.php?keyword=%E5%AE%9E%E7%9E%B3%E5%B9%BB%E6%A8%B1%E6%81%8B%E5%BF%85%E9%A1%BA%E5%8F%8C%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
            array(5324,'送润眼液','category.php?keyword=%E7%A7%91%E8%8E%B1%E5%8D%9A%E5%B0%8F%E9%BB%91%E8%A3%99%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85
'),
            array(5066,'第二盒半价','category.php?keyword=%E5%8D%AB%E5%BA%B7%E6%A7%91%E7%9E%B3%E7%9C%BC%E5%86%92%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
            array(3000,'','category.php?keyword=%E5%AE%89%E7%9E%B3%E7%BE%8E%E6%84%9F%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85
'),
            array(4527,'','category.php?keyword=SHO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87DECORATIVE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85
'),
            array(4537,'送眼镜盒',''),
            array(5140,'买一送一','category.php?keyword=%E7%A7%91%E5%B0%94%E8%A7%86%E6%A0%BC%E8%A8%80%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85
'),
            array(5408,'下单减30','category.php?keyword=%E5%AE%89%E5%A8%9C%E8%8B%8F+%E5%8D%8A%E5%B9%B4'),
            );   
        $goodsArr2 = array(
            array(105,'立减满赠',''),
            array(101,'第二盒半价',''),
            array(92,'满赠',''),
            array(662,'立减满赠',''),
            array(117,'',''),
            array(4849,'赠护理液',''),
            array(5164,'赠润眼液',''),
            array(4807,'赠护理液    ',''),
            array(4938,'买一送一',''),
            array(1097,'',''),
            array(140,'赠润眼液',''),
            array(5057,'','')
        );   
        $goodsArr3 = array(
            array(2412,'',''),
            array(4925,'会员专享70元',''),
            array(600,'',''),
            array(2748,'',''),
            array(786,'',''),
            array(2959,'',''),
            array(5276,'赠同款100ml',''),
            array(5304,'赠眼镜盒',''),
            array(585,'',''),
            array(5163,'',''),
            array(581,'',''),
            array(997,'赠润眼液',''),
        );   
        $goodsArr4 = array(
            array(5308,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E5%A4%8D%E5%8F%A4%E9%BB%91%E8%BE%B9%E6%97%B6%E5%B0%9A%E5%A4%AA%E9%98%B3%E7%9C%BC%E9%95%9C1054'),
            array(5328,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E6%97%B6%E5%B0%9A%E9%85%B7%E7%82%AB%E7%BB%8F%E5%85%B8%E8%9B%A4%E8%9F%86%E9%95%9C1517'),
            array(3881,'',''),
            array(4655,'',''),
            array(4657,'',''),
            array(2045,'',''),
            array(1317,'',''),
            array(2077,'',''),
            array(3812,'',''),
            array(4585,'',''),
            array(1332,'',''),
            array(1390,'',''),
        );   
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
    }

}elseif($pid == 20160815 || $pid == 160822){//主会场

    $now = time();
     
        $goodsArr1 = array(
            array(4475,'','category.php?keyword=%E5%BC%BA%E7%94%9F%E5%AE%89%E8%A7%86%E4%BC%98define%E7%BE%8E%E7%9E%B3%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C30%E7%89%87%E8%A3%85'),
            array(4500,'','category.php?keyword=%E8%8F%B2%E5%A3%AB%E5%BA%B7+%E5%A4%A7%E7%BE%8E%E7%9B%AE+%E6%97%A5%E6%8A%9B'),
            array(5352,'下单立减','category.php?keyword=%E8%A7%86%E5%BA%B7%E7%9D%9B%E5%BD%A9%E5%A4%A9%E5%A4%A9%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85'),
            array(1186,'','category.php?keyword=%E5%AE%9E%E7%9E%B3Eye+coffret%E5%8F%AF%E8%8A%99%E8%95%BE%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85'),
            array(1146,'','category.php?keyword=%E5%AE%9E%E7%9E%B3%E5%B9%BB%E6%A8%B1%E6%81%8B%E5%BF%85%E9%A1%BA%E5%8F%8C%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
            array(5324,'送润眼液','category.php?keyword=%E7%A7%91%E8%8E%B1%E5%8D%9A%E5%B0%8F%E9%BB%91%E8%A3%99%E7%B3%BB%E5%88%97%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85
'),
            array(5066,'第二盒半价','category.php?keyword=%E5%8D%AB%E5%BA%B7%E6%A7%91%E7%9E%B3%E7%9C%BC%E5%86%92%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
            array(3000,'','category.php?keyword=%E5%AE%89%E7%9E%B3%E7%BE%8E%E6%84%9F%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85
'),
            array(4527,'','category.php?keyword=SHO-BI%E7%BE%8E%E5%A6%86%E5%BD%A9%E7%89%87DECORATIVE%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87%E8%A3%85
'),
            array(4537,'送眼镜盒',''),
            array(5140,'买一送一','category.php?keyword=%E7%A7%91%E5%B0%94%E8%A7%86%E6%A0%BC%E8%A8%80%E7%B3%BB%E5%88%97%E6%97%A5%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85
'),
            array(5408,'下单减30','category.php?keyword=%E5%AE%89%E5%A8%9C%E8%8B%8F+%E5%8D%8A%E5%B9%B4'),
            );   
        $goodsArr2 = array(
            array(105,'立减满赠',''),
            array(101,'第二盒半价',''),
            array(92,'满赠',''),
            array(662,'立减满赠',''),
            array(117,'',''),
            array(4849,'赠护理液',''),
            array(5164,'赠润眼液',''),
            array(4807,'赠护理液    ',''),
            array(4938,'买一送一',''),
            array(1097,'',''),
            array(140,'赠润眼液',''),
            array(5057,'','')
        );   
        $goodsArr3 = array(
            array(2412,'',''),
            array(4925,'会员专享70元',''),
            array(600,'',''),
            array(2748,'',''),
            array(786,'',''),
            array(2959,'',''),
            array(5276,'赠同款100ml',''),
            array(5304,'赠眼镜盒',''),
            array(585,'',''),
            array(5163,'',''),
            array(581,'',''),
            array(997,'赠润眼液',''),
        );   
        $goodsArr4 = array(
            array(5308,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E5%A4%8D%E5%8F%A4%E9%BB%91%E8%BE%B9%E6%97%B6%E5%B0%9A%E5%A4%AA%E9%98%B3%E7%9C%BC%E9%95%9C1054'),
            array(5328,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E6%97%B6%E5%B0%9A%E9%85%B7%E7%82%AB%E7%BB%8F%E5%85%B8%E8%9B%A4%E8%9F%86%E9%95%9C1517'),
            array(3881,'',''),
            array(4655,'',''),
            array(4657,'',''),
            array(2045,'',''),
            array(1317,'',''),
            array(2077,'',''),
            array(3812,'',''),
            array(4585,'',''),
            array(1332,'',''),
            array(1390,'',''),
        );   
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
  
}elseif($pid == 16080105){//库博
        $now = time();
        if($$now>strtotime("2016-08-08 00:00:00")){
            $smarty->assign('new_img',	1);
        }
}
elseif ($pid == 16080102)//博士伦
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        if($now > strtotime('2016-08-29 00:00:00')){
            $goodsArr1 = array(
                array(104,'赠护理液',''),
                array(971,'',''),
                array(102,'买2赠5',''),
                array(757,'直降35',''),
                array(100,'第二盒半价',''),
                array(107,'下单立减',''),
                array(106,'赠精美镜盒',''),
                array(113,'赠护理液',''),
                array(2118,'买三送一','')
            );   
            $goodsArr2 = array(
                array(811,'直降20','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E4%B8%A4%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
                array(2581,'直降23','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E7%82%AB%E7%9C%B8%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85'),
                array(981,'买三送一','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85'),
                array(972,'赠精美镜盒','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%B0%B4%E7%81%B5%E7%84%95%E5%BD%A9%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C'),
                array(4325,'不惧比价','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%98%9F%E6%82%A6%E9%80%B8%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
                array(3079,'不惧比价','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%8E%B9%E7%BF%A0%E4%BA%AE%E7%9C%B8%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C')
            );   
            $goodsArr3 = array(
                array(3338,'销量领先',''),
                array(4925,'直降20',''),
                array(1035,'下单立减3元',''),
                array(592,'下单立减3元',''),
                array(2280,'不惧比价',''),
                array(631,'直降17元',''),
            );   
        }else{
            $goodsArr1 = array(
                array(104,'赠护理液',''),
                array(971,'',''),
                array(102,'买2赠5',''),
                array(757,'下单减23',''),
                array(100,'第二盒半价',''),
                array(107,'下单立减',''),
                array(106,'赠精美镜盒',''),
                array(113,'赠护理液',''),
                array(2118,'买三送一','')
            );   
            $goodsArr2 = array(
                array(811,'下单立减20','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E4%B8%A4%E5%91%A8%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C6%E7%89%87%E8%A3%85'),
                array(2581,'买2赠5','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E7%82%AB%E7%9C%B8%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E6%97%A5%E6%8A%9B10%E7%89%87%E8%A3%85'),
                array(981,'买三送一','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%95%BE%E4%B8%9D%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C5%E7%89%87%E8%A3%85'),
                array(972,'赠精美镜盒','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%B0%B4%E7%81%B5%E7%84%95%E5%BD%A9%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C'),
                array(4325,'不惧比价','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E6%98%9F%E6%82%A6%E9%80%B8%E5%BD%A9%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
                array(3079,'不惧比价','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E8%8E%B9%E7%BF%A0%E4%BA%AE%E7%9C%B8%E7%B3%BB%E5%88%97%E5%B9%B4%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C')
            );   
            $goodsArr3 = array(
                array(3338,'销量领先',''),
                array(4925,'会员￥70',''),
                array(1035,'下单立减3元',''),
                array(592,'下单立减3元',''),
                array(2280,'不惧比价',''),
                array(631,'直降17元',''),
            ); 
        }
        
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
    
}
elseif ($pid == 16080104)//海昌
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(834,'',''),
            array(844,'',''),
            array(2405,'',''),
            array(5095,'',''),
            array(153,'',''),
            array(148,'',''),
            array(163,'',''),
            array(172,'',''),
            array(149,'','')
        );   
        $goodsArr2 = array(
            array(2048,'',''),
            array(4564,'',''),
            array(5146,'',''),
            array(3891,'',''),
            array(2699,'',''),
            array(2337,'',''),
            array(4075,'',''),
            array(1037,'',''),
            array(4081,'','')
        );   
        $goodsArr3 = array(
            array(2748,'',''),
            array(596,'',''),
            array(599,'',''),
            array(601,'',''),
            array(600,'',''),
            array(5121,'','')
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}
elseif ($pid == 16080106)//卫康
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(4807,'',''),
            array(4806,'',''),
            array(4805,'',''),
            array(4802,'',''),
            array(4801,'',''),
            array(4800,'',''),
            array(4804,'四盒立减10元',''),
            array(127,'',''),
            array(124,'',''),
            array(123,'',''),
            array(3081,'',''),
            array(3039,'',''),
            array(3038,'2件5折',''),
            array(3037,'',''),
            array(3036,'',''),
            array(3035,'',''),
            array(3034,'2件送护理液',''),
            array(139,'',''),
            array(2403,'',''),
            array(131,'','')
        ); 
          
        $goodsArr2 = array(
            array(1208,'',''),
            array(1207,'',''),
            array(1206,'',''),
            array(1205,'',''),
            array(1204,'',''),
            array(1213,'',''),
            array(1212,'',''),
            array(1211,'',''),
            array(1210,'',''),
            array(1209,'','')
        );   
        $goodsArr3 = array(
            array(5284,'',''),
            array(2867,'',''),
            array(2866,'',''),
            array(2865,'',''),
            array(2864,'',''),
            array(4834,'',''),
            array(4833,'',''),
            array(4213,'',''),
            array(4212,'',''),
            array(4930,'',''),
            array(4884,'',''),
            array(612,'买三送一',''),
            array(610,'第二件半价',''),
            array(609,'赠同款500ml',''),
            array(4973,'',''),
            array(921,'',''),
            array(786,'',''),
            array(617,'',''),
            array(616,'',''),
            array(4203,'',''),
            array(4201,'',''),
            array(620,'',''),
            array(618,'',''),
            array(5285,'',''),
            array(4634,'','')
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}
elseif ($pid == 16080109)
{//美瞳
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(4281,'','category.php?keyword=G西武Secret+CandyEyes系列年抛型彩色隐形眼镜1片装'),
            array(5111,'','category.php?keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E7%94%9C%E5%BF%83%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B1%E7%89%87%E8%A3%85'),
            array(4940,'','category.php?keyword=%E8%87%AA%E7%84%B6%E7%BE%8E%E9%AD%94%E7%BF%BC%E5%A4%A9%E4%BD%BF%E7%B3%BB%E5%88%97'),
            array(5125,'','category.php?keyword=%E4%B8%83%E5%A4%95%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B1%E7%89%87%E8%A3%85'),
            array(5002,'','category.php?keyword=%E5%8F%AF%E4%B8%BD%E5%8D%9Aclearcolor%E7%87%83%E5%BD%A9%E7%81%B0%E8%89%B2'),
            array(5151,'','category.php?keyword=%E7%A7%91%E5%B0%94%E8%A7%86%E6%A0%BC%E8%A8%80%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
            array(4138,'','category.php?keyword=Bescon Tutti Circle大直径系'),
            array(5331,'','category.php?keyword=%E7%A7%91%E8%8E%B1%E5%8D%9Aclbcolor%E4%B8%9D%E9%9F%B5%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B%E5%9E%8B1%E7%89%87%E8%A3%85'),
            array(5249,'','category.php?keyword=科莱博卡狮图系列年抛型彩色隐形眼镜1片装')
        );   
        $goodsArr2 = array(
            array(312,'','category.php?keyword=G＆G西武大眼睛系列彩色隐形眼镜'),
            array(5118,'','category.php?keyword=%E6%BE%9C%E6%9F%8F%E6%B2%81%E5%A6%8D%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87'),
            array(4752,'','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E7%9D%9B%E7%92%A8%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85'),
            array(5066,'','category.php?keyword=%E5%8D%AB%E5%BA%B7%E6%A7%91%E7%9E%B3'),
            array(2753,'','category.php?keyword=Bescon tutti系列one-day color日抛型彩色隐形眼镜5片装'),
            array(5243,'','category.php?keyword=科莱博可妮幸运星系列年抛型彩色隐形眼镜1片装（盒装）'),
            array(4636,'','category.php?keyword=科莱博小黑裙系列日抛型彩色隐形眼镜5片装'),
            array(4537,'','category.php?keyword=NEO可视眸巨目'),
            array(229,'','category.php?keyword=菲士康大美目半年抛彩色隐形眼镜2片装')
        );
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
    }
    if($$now>strtotime("2016-08-08 00:00:00")){
        $smarty->assign('new_img',	1);
    }
}
elseif ($pid == 16080110)//散光片
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(2851,'',''),
            array(2850,'',''),
            array(3081,'',''),
            array(993,'',''),
            array(203,'',''),
            array(204,'',''),
            array(2554,'',''),
            array(2555,'',''),
            array(5315,'',''),
            array(201,'',''),
            array(202,'',''),
            array(4858,'',''),
            array(4857,'',''),
            array(1011,'',''),
            array(171,'',''),
            array(170,'',''),
            array(169,'',''),
            array(168,'',''),
            array(181,'',''),
            array(4921,'',''),
            array(3373,'','')
        );   
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
    }
}
elseif ($pid == 160811)//招行
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(4820,'',''),
            array(757,'',''),
            array(1251,'',''),
            array(4751,'',''),
        );  
        $goodsArr2 = array(
            array(813,'',''),
            array(222,'',''),
            array(2581,'',''),
            array(4477,'',''),
        );  
        $goodsArr3 = array(
            array(592,'',''),
            array(122,'',''),
            array(3338,'',''),
            array(2191,'',''),
        );  
        $goodsArr4 = array(
            array(1304,'',''),
            array(3879,'',''),
            array(3881,'',''),
            array(2170,'',''),
        );   
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
    }
}
elseif ($pid == 16080108)//太阳镜
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(5376,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E6%97%B6%E5%B0%9A%E9%85%B7%E7%82%AB%E9%87%91%E8%89%B2%E6%A1%86%E7%BB%8F%E5%85%B8%E8%9B%A4%E8%9F%86%E9%95%9C+1517'),
            array(5364,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E5%A4%8D%E5%8F%A4%E9%87%91%E5%B1%9E%E5%8D%8A%E6%A1%86%E5%A4%AA%E9%98%B3%E9%95%9C%252B1055'),
            array(4744,'',''),
            array(2159,'',''),
            array(2595,'',''),
            array(2348,'',''),
            array(3884,'',''),
            array(2169,'',''),
            array(5328,'','category.php?keyword=Seven%E4%B8%83%E5%BA%A6%E6%97%B6%E5%B0%9A%E9%85%B7%E7%82%AB%E7%BB%8F%E5%85%B8%E8%9B%A4%E8%9F%86%E9%95%9C1517'),
        );  
        $goodsArr2 = array(
            array(1317,'',''),
            array(4417,'',''),
            array(4472,'',''),
            array(2609,'',''),
            array(2625,'',''),
            array(4198,'',''),
            array(3271,'',''),
            array(3812,'',''),
            array(2251,'',''),
        );  
        $goodsArr3 = array(
            array(3756,'',''),
            array(3747,'',''),
            array(3737,'',''),
            array(3704,'',''),
            array(2531,'',''),
            array(2320,'','')
        );   
        
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}
elseif ($pid == 16080112 || $pid == 160824)//奥运会
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(4281,'','category.php?keyword=Secret'),
            array(5111,'','category.php?keyword=%E4%BC%8A%E5%8E%B6%E5%BA%B7%E7%94%9C%E5%BF%83%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B1%E7%89%87%E8%A3%85'),
            array(4940,'','category.php?keyword=%E8%87%AA%E7%84%B6%E7%BE%8E%E9%AD%94%E7%BF%BC%E5%A4%A9%E4%BD%BF%E7%B3%BB%E5%88%97'),
            array(5125,'','category.php?keyword=%E4%B8%83%E5%A4%95%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B1%E7%89%87%E8%A3%85'),
            array(5002,'','category.php?keyword=%E5%8F%AF%E4%B8%BD%E5%8D%9Aclearcolor%E7%87%83%E5%BD%A9%E7%81%B0%E8%89%B2'),
            array(5151,'','category.php?keyword=%E7%A7%91%E5%B0%94%E8%A7%86%E6%A0%BC%E8%A8%80%E7%B3%BB%E5%88%97%E5%8D%8A%E5%B9%B4%E6%8A%9B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C1%E7%89%87%E8%A3%85'),
            array(4138,'','category.php?keyword=Tutti Circle'),
            array(5331,'','category.php?keyword=%E7%A7%91%E8%8E%B1%E5%8D%9Aclbcolor%E4%B8%9D%E9%9F%B5%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C%E5%B9%B4%E6%8A%9B%E5%9E%8B1%E7%89%87%E8%A3%85'),
            array(5249,'','category.php?keyword=科莱博卡狮')
        );   
        $goodsArr2 = array(
            array(312,'','category.php?keyword=G＆G西武大眼睛系列彩色隐形眼镜'),
            array(5118,'','category.php?keyword=%E6%BE%9C%E6%9F%8F%E6%B2%81%E5%A6%8D%E6%9C%88%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C2%E7%89%87'),
            array(4752,'','category.php?keyword=%E5%8D%9A%E5%A3%AB%E4%BC%A6%E7%9D%9B%E7%92%A8%E6%98%8E%E7%9C%B8%E6%97%A5%E6%8A%9B%E5%9E%8B%E5%BD%A9%E8%89%B2%E9%9A%90%E5%BD%A2%E7%9C%BC%E9%95%9C10%E7%89%87%E8%A3%85'),
            array(5066,'','category.php?keyword=%E5%8D%AB%E5%BA%B7%E6%A7%91%E7%9E%B3'),
            array(2753,'','category.php?keyword=Bescon tutti系列one-day'),
            array(5243,'','category.php?keyword=科莱博可妮幸运星系列年抛型彩色隐形眼镜1片装（盒装）'),
            array(4636,'','category.php?keyword=科莱博小黑裙系列日抛型彩色隐形眼镜5片装'),
            array(4537,'','category.php?keyword=NEO可视眸'),
            array(229,'','category.php?keyword=菲士康大美目')
        );
        $goodsArr3 = array(
            array(950,'第二盒半价','category.php?keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜30片装'),
            array(4475,'四盒立减20','category.php?keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
            array(987,'直降10元','category.php?keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
            array(1188,'','category.php?keyword=实瞳Eye+coffret可芙蕾日抛型彩色隐形眼镜30片装'),
            array(5117,'第二盒半价','category.php?keyword=澜柏沁妍月抛型彩色隐形眼镜2片'),
            array(1147,'直降46','category.php?keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装'),
            array(5408,'下单立减30','category.php?keyword=安娜苏半年'),
            array(4138,'第二盒0元','category.php?keyword=Bescon+Tutti+Circle大直径系列半年抛彩色隐形眼镜1片装'),
            array(359,'买一送一',''),
            array(2855,'买一送一','category.php?keyword=菲士康大美目半年抛彩色隐形眼镜2片装'),
            array(2925,'','category.php?keyword=科莱博+霓彩Käthe系列日抛型彩色隐形眼镜5片装'),
            array(811,'下单立减20','category.php?keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装')
        );
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}
elseif ($pid == 160912)//海昌专场
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
           
      
            $goodsArr1 = array(
                array(3891,'送镜盒','category.php?search=1&keyword=海昌心怡蕾丝公主系列年抛型彩色隐形眼镜'),
                array(2699,'送镜盒',''),
                array(2337,'送镜盒',''),
                array(1007,'送镜盒','category.php?search=1&keyword=海昌璀灿系列年抛型彩色隐形眼镜')
            );
            $goodsArr2 = array(
                array(2405,'',''),
                array(2404,'',''),
                array(834,'',''),
                array(5095,'','')
            );      
            $goodsArr3 = array(
                array(2748,'',''),
                array(596,'',''),
                array(601,'',''),
                array(2614,'','')
            );   
            $goodsArr4 = array(
                array(4821,'','category.php?search=1&keyword=海俪恩桃花秀幻境精灵半年抛彩色隐形眼镜1片'),
                array(4842,'','category.php?search=1&keyword=海俪恩潘朵拉宝盒混血自然半年抛彩色隐形眼镜1片装'),
                array(4083,'','category.php?search=1&keyword=海俪恩瞳话系列年抛型彩色隐形眼镜'),
                array(988,'','category.php?search=1&keyword=海昌海俪恩靓彩系列年抛型彩色隐形眼镜'),
                array(4163,'','category.php?search=1&keyword=海俪恩魔法魅眼系列年抛型彩色隐形眼镜'),
                array(146,'',''),
                array(152,'',''),
                array(5121,'',''),
                //array(5122,'','')
            );   
      
        
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
    }
    
}
elseif ($pid == 160915 )//招行
{
    $pid = 160916;
}
elseif ($pid == 160913)//中秋专场
{
    $pid = 160915;
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        
           // print_r(date('d'));die;
            $d = date('d');
            if($d <= 13){
                $msGoodsArr = array(array(4477,'',''),array(5408,'',''),array(101,'',''),array(5445,'',''),array(2412,'',''));
            }elseif($d == 14){
                $msGoodsArr = array(array(4476,'',''),array(1147,'',''),array(140,'',''),array(1097,'',''),array(5304,'',''));
            }elseif($d == 15){
                $msGoodsArr = array(array(103,'',''),array(731,'',''),array(987,'',''),array(5406,'',''),array(599,'',''));
            }elseif($d == 16){
                $msGoodsArr = array(array(950,'',''),array(4853,'',''),array(2405,'',''),array(4801,'',''),array(1065,'',''));
            }elseif($d == 17){
                $msGoodsArr = array(array(2104,'',''),array(948,'',''),array(3035,'',''),array(5097,'',''),array(4925,'',''));
            }elseif($d == 18){
                $msGoodsArr = array(array(1186,'',''),array(5115,'',''),array(4938,'',''),array(4937,'',''),array(5121,'',''));
            }elseif($d == 19){
                $msGoodsArr = array(array(4477,'',''),array(5408,'',''),array(101,'',''),array(5445,'',''),array(2412,'',''));
            }elseif($d == 20){
                $msGoodsArr = array(array(4476,'',''),array(1147,'',''),array(140,'',''),array(1097,'',''),array(5304,'',''));
            }elseif($d == 21){
                $msGoodsArr = array(array(103,'',''),array(731,'',''),array(987,'',''),array(5406,'',''),array(599,'',''));
            }
            
            $goodsArr1 = array(
                array(5065,'第二盒半价，送伴侣盒','category.php?search=1&keyword=卫康槑瞳眼冒系列半年抛彩色隐形眼镜1片装'),
                array(4283,'买一送一','category.php?search=1&keyword=CandyEyes系列年抛型彩色隐形眼镜1片装'),
                array(3927,'立省48元 送伴侣盒','category.php?search=1&keyword=CandyEyes系列年抛型彩色隐形眼镜'),
                array(3948,'立省21元 送伴侣盒','category.php?search=1&keyword=蓝睛灵蕾丝魅影系列双周抛彩色隐形眼镜2片装'),
                array(4987,'立省84元 送伴侣盒','category.php?search=1&keyword=KKR舒透氧纯然星动系列彩色隐形眼镜半年抛1片装'),
                array(950,'第二盒半价','category.php?search=1&keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜30片装'),
                array(811,'两盒减20，四盒减80','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'),
                array(274,'立省53元，送伴侣盒','category.php?search=1&keyword=自然美惹火水晶系列多弧保湿彩色隐形眼镜'),
                array(4803,'直降79元，赠润眼液10ml',''),
                array(4884,'立省25元',''),
                array(3035,'立省51元',''),
                array(130,'3.1折',''),
                array(5164,'直降15元，送润眼液10ml',''),
                array(105,'立省7元，+6元送小黑裙100ml',''),
                array(971,'立省50元',''),
                array(581,'立省24元',''),
            );
            
            $goodsArr2 = array(
                array(757,'赠博乐纯60ML+伴侣盒',''),
                array(93,'赠澜柏10ml*6瓶',''),
                array(92,'立省81元，送润眼液10ml',''),
                array(767,'满100减20，赠润眼液10ml',''),
                array(101,'买四送一，送润眼液',''),
                array(103,'送洗眼液',''),
                array(5445,'买三送一',''),
                array(1045,'领券立减20,折后仅119/盒',''),
                array(662,'领券立减20,折后仅139/盒',''),
                array(2686,'二盒立减22元，折后仅174',''),
                array(585,'立省24元',''),
                array(5255,'立省73元',''),
                array(5351,'直降33元',''),
                array(4752,'第二盒半价，送润眼液ml',''),
                array(4925,'下单立减15，仅75元',''),
                array(2412,'立省24元','')
            );
            
            $goodsArr3 = array(
                array(5243,'第二盒半价，赠伴侣盒','category.php?search=1&keyword=科莱博可妮幸运星系列年抛型彩色隐形眼镜1片装（盒装）'),
                array(1188,'立省103元，赠润眼液','category.php?search=1&keyword=实瞳Eye coffret可芙蕾日抛型彩色隐'),
                array(1146,'立省46元，赠伴侣盒','category.php?search=1&keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜'),
                array(359,'买一送一',''),
                array(5034,'立省43元，赠伴侣盒','category.php?search=1&keyword=BESCON三色润彩系列半年抛彩色隐形眼镜1片装'),
                array(880,'立省37元','category.php?search=1&keyword=GEO纪依澳'),
                array(1219,'第二件半价，赠伴侣盒','category.php?search=1&keyword=伊厶康糖果双色系列彩色隐形眼镜年抛1片装'),
                array(3640,'直降20元','category.php?search=1&keyword=NEO可视眸女皇四色棕年抛型彩色隐形眼镜 N414'),
                array(1461,'立省51元，赠伴侣盒','category.php?search=1&keyword=怡美思曼妙丝缎小玫瑰粉色 S033年抛型彩色隐形眼镜'),
                array(5151,'买一送一，赠伴侣盒','category.php?search=1&keyword=科尔视格言系列半年抛彩色隐形眼镜1片装'),
                array(4559,'立省50元，赠伴侣盒','category.php?search=1&keyword=可丽博雯彩系列年抛型彩色隐形眼镜'),
                array(1250,'立省45元，赠小黑裙100ml',''),
                array(5142,'直降52，送2片+润眼液10ml','category.php?search=1&keyword=科尔视格言系列日抛彩色隐形眼镜10片装'),
                array(312,'立省73元',''),
                array(4299,'第二盒半价',''),
                array(4937,'立省78元，送润眼液10ml',''),
            );
            
        $smarty->assign('d',            $d);
        $msGoods = get_goods_info_active($msGoodsArr);
        
        
        if($d == 19){
            $msGoods[0]['temp_price'] = 179;
            $msGoods[0]['temp_link'] = 'tuan_buy_831.html';
            $msGoods[1]['temp_price'] = 236;
            $msGoods[1]['temp_link'] = 'tuan_buy_832.html';
            $msGoods[2]['temp_price'] = 105;
            $msGoods[2]['temp_link'] = 'tuan_buy_833.html';
            $msGoods[3]['temp_price'] = 149;
            $msGoods[3]['temp_link'] = 'tuan_buy_834.html';
            $msGoods[4]['temp_price'] = 45;
            $msGoods[4]['temp_link'] = 'tuan_buy_835.html';
           
        }elseif($d == 20){
            $msGoods[0]['temp_price'] = 179;
            $msGoods[0]['temp_link'] = 'tuan_buy_836.html';
            $msGoods[1]['temp_price'] = 81;
            $msGoods[1]['temp_link'] = 'tuan_buy_837.html';
            $msGoods[2]['temp_price'] = 67;
            $msGoods[2]['temp_link'] = 'tuan_buy_838.html';
            $msGoods[3]['temp_price'] = 79;
            $msGoods[3]['temp_link'] = 'tuan_buy_839.html';
            $msGoods[4]['temp_price'] = 88;
            $msGoods[4]['temp_link'] = 'tuan_buy_840.html';
        }elseif($d == 21){
            $msGoods[0]['temp_price'] = 76;
            $msGoods[0]['temp_link'] = 'tuan_buy_842.html';
            $msGoods[1]['temp_price'] = 98;
            $msGoods[1]['temp_link'] = 'tuan_buy_843.html';
            $msGoods[2]['temp_price'] = 68;
            $msGoods[2]['temp_link'] = 'tuan_buy_844.html';
            $msGoods[3]['temp_price'] = 19;
            $msGoods[3]['temp_link'] = 'tuan_buy_845.html';
            $msGoods[4]['temp_price'] = 236;
            $msGoods[4]['temp_link'] = 'tuan_buy_846.html';
        }
        
        $smarty->assign('msGoodsArr',	$msGoods);
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        
        
    }
    
}elseif ($pid == 161001)//国庆专场
{
    $now = time();
    $d = date('d');
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
            
            if($d == 1){
                $goodsArrMs = array(array(945,'',''),array(5364,'',''),array(5277,'',''));
             }elseif($d == 2){
                $goodsArrMs = array(array(844,'',''),array(946,'',''),array(5462,'',''));
             }elseif($d == 3){
                $goodsArrMs = array(array(5139,'',''),array(5331,'',''),array(610,'',''));
             }elseif($d == 4){
                $goodsArrMs = array(array(989,'',''),array(5449,'',''),array(620,'',''));
             }elseif($d == 5){
                $goodsArrMs = array(array(610,'',''),array(4805,'',''),array(4142,'',''));
             }elseif($d == 6){
                $goodsArrMs = array(array(3948,'',''),array(4934,'',''),array(1121,'',''));
             }elseif($d == 7){
                $goodsArrMs = array(array(3892,'',''),array(5374,'',''),array(5335,'',''));
             }elseif($d == 30){
                $goodsArrMs = array(array(945,'',''),array(5364,'',''),array(5277,'',''));
             }
            $goodsArr1 = array(
                array(101,'买三送一',''),
                array(4938,'第二件半价',''),
                array(662,'赠250ML洗眼液',''),
                array(4299,'第二件半价',''),
                array(4803,'第二件半价',''),
                array(5164,'第二件半价',''),
                array(767,'赠250ML洗眼液',''),
                array(5449,'买一赠一',''),
                array(1010,'送洗眼液250ml',''),
                
            );
            $goodsArr2 = array(
                array(2581,'买三送一','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
                array(5139,'第二件半价','category.php?search=1&keyword=科尔视格言系列日抛彩色隐形眼镜2片装'),
                array(1188,'第二件半价','category.php?search=1&keyword=实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜30片装'),
                array(949,'买三送一','category.php?search=1&keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜30片装'),
                array(2761,'第二件半价','category.php?search=1&keyword=one-day color日抛型彩色隐形眼镜30片装'),
                array(5345,'买三送一','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜5片装'),
                array(4494,'第二件半价','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
                array(4851,'第二件半价','category.php?search=1&keyword=菲士康焕彩日抛型彩色隐形眼镜10片装'),
                array(4753,'第二件半价','category.php?search=1&keyword=博士伦睛璨明眸日抛型彩色隐形眼镜30片装'),
            );
            
            $goodsArr3 = array(
                array(103,'买三送一',''),
                array(2405,'第二件半价',''),
                array(185,'赠250ML洗眼液',''),
                array(731,'第二件半价',''),
                array(105,'超薄透氧',''),
                array(3036,'第二件半价',''),
                array(236,'第二件半价','category.php?search=1&keyword=菲士康焕彩月抛型彩色隐形眼镜2片装'),
                array(4283,'第二件半价','category.php?search=1&keyword=西武Secret CandyEyes系列年抛型彩色隐形眼镜1片装'),
                array(5335,'第二件半价','category.php?search=1&keyword=科莱博clbcolor丝韵彩色隐形眼镜年抛型1片装'),
                array(4865,'第二件半价','category.php?search=1&keyword=卫康槑瞳'),
                array(878,'第二件半价','category.php?search=1&keyword=GEO纪依澳'),
                array(5151,'第二件半价','category.php?search=1&keyword=科尔视格言系列半年抛彩色隐形眼镜1片装'),
            );
            
            $goodsArr4 = array(
                array(1097,'赠250ML洗眼液',''),
                array(5031,'第二件半价',''),
                array(1045,'赠优能250ml洗眼液',''),
                array(4751,'买三送一',''),
                array(5445,'第二件半价',''),
                array(5030,'第二件半价',''),
            );
            $goodsArr5 = array(
                array(2555,'第二件半价',''),
                array(2118,'第二件半价',''),
                array(201,'第二件半价',''),
                array(202,'第二件半价',''),
                array(169,'第二件半价','category.php?search=1&keyword=%E6%B5%B7%E6%98%8C+%E5%AE%9A%E5%88%B6'),
                array(4921,'第二件半价',''),
            );
            

            $goodsArr6 = array(
                array(3338,'',''),
                array(596,'',''),
                array(585,'',''),
                array(4925,'',''),
                array(581,'',''),
                array(5271,'',''),
                array(4884,'',''),
                array(924,'',''),
                array(592,'','')
            );
        $smarty->assign('goodsArrMs',	get_goods_info_active($goodsArrMs));
        $smarty->assign('d',	        $d);    
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
        $smarty->assign('goodsArr5',	get_goods_info_active($goodsArr5));
        $smarty->assign('goodsArr6',	get_goods_info_active($goodsArr6));
    }
    
}
elseif ($pid == 160926)//博士伦
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(4751,'2代硅水凝胶',''),
            array(757,'原装进口',''),
            array(5445,'日夜型硅水凝胶',''),
            array(4914,'散光速度定制',''),
            array(2118,'散光速度定制',''),
            array(5077,'散光速度定制',''),
           );   
        $goodsArr2 = array(
            array(2584,'绚丽水润','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
            array(947,'独特蕾丝花纹','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'),
            array(3079,'初阳般柔美莹亮','category.php?search=1&keyword=博士伦莹翠亮眸系列年抛型'),
            array(5357,'半年抛弃，健康舒适','category.php?search=1&keyword=博士伦莹翠亮眸半年抛'),
            array(5201,'打造水灵大眼睛','category.php?search=1&keyword=博士伦水灵炫彩半年抛彩色隐形眼镜1片装'),
            array(4325,'纹理逼真，清新迷人','category.php?search=1&keyword=博士伦星悦逸彩系列半年抛彩色隐形眼镜1片装'),
        );
        $goodsArr3 = array(
            array(3338,'',''),
            array(4925,'',''),
            array(3420,'',''),
            array(2191,'',''),
            array(4861,'',''),
            array(2296,'',''),
           ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}
elseif ($pid == 161017)//品牌日
{
    $now = time();
    $smarty->caching = false;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(2405,'第二件0元',''),
            array(5449,'第二件0元',''),
            array(4851,'第二件0元','category.php?search=1&keyword=菲士康焕彩日抛型彩色隐形眼镜10片装'),
            array(356,'第二件0元',''),
            array(4606,'第二件0元','category.php?search=1&keyword=魅瞳易彩维密蕾丝系列年抛型彩色隐形眼镜'),
            array(5481,'第二件0元',''),
           );   
        $goodsArr2 = array(
            array(5408,'直降20元','category.php?search=1&keyword=SUI安娜苏半年抛彩色隐形眼镜1片装'),
            array(4281,'直降54元','category.php?search=1&keyword=CandyEyes系列年抛型彩色隐形眼镜1片装'),
            array(4865,'直降12元','category.php?search=1&keyword=卫康槑瞳囧囧有神系列半年抛彩色隐形眼镜1片装'),
            array(4847,'直降21元','category.php?search=1&keyword=菲士康大美目半年抛彩色隐形眼镜1片装'),
            array(3630,'直降105',''),
            array(140,'直降14',''),
            array(5276,'直降11',''),
            array(2584,'限3天直降20元','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
            array(757,'限3天直降29元',''),
        );
        $goodsArr3 = array(
            array(101,'买三送一',''),
            array(948,'买三送一','category.php?search=1&keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜30片装'),
            array(103,'买三送一',''),
            array(4751,'买三送一',''),
            array(3338,'会员99',''),
            array(4752,'买三送一','category.php?search=1&keyword=博士伦睛璨明眸'),
            array(4925,'会员79',''),
           );
           
        $goodsArr4 = array(
            array(4475,'两盒立减20元','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
            array(93,'硅水凝胶',''),
            array(95,'超薄中心厚度',''),
            array(94,'超强氧传导',''),
            array(4782,'四盒立减20','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜5片装'),
            array(92,'赠润眼液',''),
            array(91,'长久保湿',''),
           ); 
           
        $goodsArr5 = array(
            array(119,'直降30元',''),
            array(2686,'折上2盒减30',''),
            array(1097,'直降32元',''),
            array(585,'立减3元',''),
            array(924,'直降15元',''),
            array(5352,'买三送一','category.php?search=1&keyword=爱尔康视康睛彩天天抛彩色隐形眼镜10片装'),
            array(2931,'直降23元',''),
           ); 
           
        $goodsArr6 = array(
            array(1045,'硅水凝胶',''),
            array(662,'送润眼液',''),
            array(767,'送洗眼液250ml',''),
            array(185,'送洗眼液250ml',''),
            array(761,'送优能洗眼液',''),
            array(1011,'极速散光定制',''),
            array(4820,'赠优能洗眼液',''),
           );
        
        $goodsArr7 = array(
            array(987,'第二盒半价','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
            array(834,'送润眼液',''),
            array(2405,'第二件0元',''),
            array(4878,'直降9元','category.php?search=1&keyword=海俪恩马卡龙之吻半年抛彩色隐形眼镜1片装'),
            array(601,'实惠套装',''),
            array(596,'直降3元',''),
            array(2614,'小包装便携',''),
           );
        
        $goodsArr8 = array(
            array(1187,'直降105','category.php?search=1&keyword=coffret可芙蕾日抛型彩色隐形眼镜30片装'),
            array(4299,'第二盒半价',''),
            array(3630,'直降104',''),
            array(1146,'直降44元','category.php?search=1&keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装'),
            array(5057,'高弹素材',''),
            array(1185,'送润眼液','category.php?search=1&keyword=coffret可芙蕾日抛型彩色隐形眼镜10片装'),
            array(3631,'两盒立省82元',''),
           ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
        $smarty->assign('goodsArr5',	get_goods_info_active($goodsArr5));
        $smarty->assign('goodsArr6',	get_goods_info_active($goodsArr6));
        $smarty->assign('goodsArr7',	get_goods_info_active($goodsArr7));
        $smarty->assign('goodsArr8',	get_goods_info_active($goodsArr8));
    }
}
elseif ($pid == 161101)
{
    $act = isset($_REQUEST['act'])? $_REQUEST['act']: '';
    $num = isset($_REQUEST['num'])? $_REQUEST['num']: '';
    
    if ($act == 'add_to_cart') {
        
        
        	$ds_str_1 = str_replace(array("\r\n", "\r", "\n"), "", $_REQUEST['goodsData'][0][1]);
            $ds_str_2 = str_replace(array("\r\n", "\r", "\n"), "", $_REQUEST['goodsData'][1][1]);
            $ds_arr_1 = explode(',',$ds_str_1);
            $ds_arr_2 = explode(',',$ds_str_2);
            
            
            $cart149_number = $num;
			$cart149_goods1 = isset($_REQUEST['goodsData'][0][0])? $_REQUEST['goodsData'][0][0]: '0';
			$cart149_goods2 = isset($_REQUEST['goodsData'][1][0])? $_REQUEST['goodsData'][1][0]: '0';
			$cart149_goods1_zselect = isset($ds_arr_1[0])? $ds_arr_1[0]: '';
			$cart149_goods2_zselect = isset($ds_arr_2[0])? $ds_arr_2[0]: '';
            
            if ($cart149_number == 2)
            {
                if ($cart149_goods1 && $cart149_goods2)
                {
                    $g_1 = get_goods_info($cart149_goods1);
                    $g_2 = get_goods_info($cart149_goods2);
                    
                    if($g_1['shop_price_nochar']>=$g_2['shop_price_nochar']){
                        $total_price_149 = $g_1['shop_price_nochar'];
                    }else{
                        $total_price_149 = $g_2['shop_price_nochar'];
                    }
                    $sql1 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`)
                     VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods1."', '".$g_1['goods_sn']."', '[双11二免一]".$g_1['goods_name']."', '0.00', '".$total_price_149."', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods1_zselect."')";
                    
                    $res1 = $GLOBALS['db']->query($sql1);
                    $parent_rec_id = $db->insert_id();

                    $sql2 = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`,`zcount`,`zselect`,`parent_id`)
                     VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$cart149_goods2."', '".$g_2['goods_sn']."', '[双11二免一]".$g_2['goods_name']."', '0.00', '0.00', '1', '', '1', 'unchange', '1212', '1', '',1,'".$cart149_goods2_zselect."',$parent_rec_id)";
                    $res2 = $GLOBALS['db']->query($sql2);

                    if ($res1 && $res2) echo '二免一商品成功加入购物车!';
                }
            }
            exit;
    }elseif($act == 'get_color'){
        
        $goods_id   = isset($_REQUEST['goods_id'])? $_REQUEST['goods_id']: '0'; 
        $html       = '';
        $error      = 0;
        $can_goods = array(2764,2763,2762,2761,2760,2759,353,352,351,354,5470,5471,4853,4852,4851,4282,4281,4283,
        5065,5066,4867,4868,4865,4866,356,355,358,359,5378,5386,5385,5384,5383,5382,5381,5380,5379,945,946,5467,3635,3636,
        4535,4537,5465,5466,2869,2870,2871,5345,5441,5439,4500,5448,5447,5446);//2073,2037ceshi
        
        if(!in_array($goods_id,$can_goods)){
            $error = 1;
        }else{
            if($goods_id>0){
                $goods_info   = $GLOBALS['db']->getRow("SELECT goods_name,goods_thumb FROM ecs_goods WHERE goods_id = ".$goods_id);
                $goods_ds     = get_goods_ds($goods_id);
                $html = '<select name="zselect" class="zselect" id="zselect_ds_'.$goods_id.'">';
                foreach($goods_ds as $v){
                    $val = $v['canbuy']==1? $v['val']:'nobuy';
                    $html.= '<option value="'.$val.'">'.$v['val'].$v['status'].'</option>';
                }
                $html.= '</select>';
            }
        }
        
        
        echo json_encode(array('goods_id'=>$goods_id,'html'=>$html,'goods_name'=>$goods_info['goods_name'],'goods_thumb'=>'http://img.easeeyes.com/'.$goods_info['goods_thumb'],'error'=>$error));
        exit;
    }

    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-wap--active'.$pid));
    $goodsArr1 = array(4751,4939,4849,5481,2405,5445,5449,5534,761);
    $goodsArr2 = array(5345,2869,4853,5378,945,2764,353,5470,4282,5065,356,5467);
    $goodsArr3 = array(580,5276,5427,2959,1121,609,5535,5304,1065); 
    $goodsArr4 = array(1319,1686,4592,4398,3812,4594,5310,5362,5327,4665,2595,2037);              
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        //榜单
        $st = strtotime("2016-11-1 00:00:00");
        $et = strtotime("2016-11-13 00:00:00");
        
        $sql = $GLOBALS['db']->getAll("SELECT a.user_id,b.user_name,SUM(goods_amount) AS amount FROM  ".$GLOBALS['ecs']->table("order_info")." a left join ".$GLOBALS['ecs']->table("users")." b 
        ON a.user_id = b.user_id WHERE a.user_id !=0 AND a.pay_status = 2 AND a.pay_time >$st AND a.pay_time <$et GROUP BY a.user_id ORDER BY amount DESC LIMIT 0,12");
        foreach($sql as $k=> $v){
            $arr['k'] = $k+1;
            $arr['user_name'] = $v['user_name'];
            $arr['amount']    = $v['amount'];
            $list[]   = $arr;  
        }
        $resArr1 = array();
        $resArr2 = array();
        $resArr3 = array();
        $resArr4 = array();
        foreach($goodsArr1 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            $res['goods_ds']     = get_goods_ds($v);
            $res['choose_color'] = 0;
            $res['shop_price_t'] = number_format($res['shop_price'],2);
            $resArr1[] = $res;
        }
        foreach($goodsArr2 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            $res['goods_ds']     = get_goods_ds($v);
            $res['choose_color'] = 1;
            $res['link_goods']   = get_link_goods_color($v);
            $res['shop_price_t'] = number_format($res['shop_price'],2);
            $resArr2[] = $res;
        }
        foreach($goodsArr3 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            $res['goods_ds']     = get_goods_ds($v);
            $res['choose_color'] = 0;
            $res['shop_price_t'] = number_format($res['shop_price'],2);
            $resArr3[] = $res;
        }
        foreach($goodsArr4 as $v){
            $res = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price FROM " .
                $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v);
            $res['goods_ds']     = get_goods_ds($v);
            if($v == 5364 || $v == 5326){
                $res['choose_color'] = 1;
            }else{
                $res['choose_color'] = 0;
            }
            $res['shop_price_t'] = number_format($res['shop_price'],2);
            $resArr4[] = $res;
        }
        
        $smarty->assign('list',	        $list);
        $smarty->assign('goodsArr1',	$resArr1);
        $smarty->assign('goodsArr2',	$resArr2);
        $smarty->assign('goodsArr3',	$resArr3);
        $smarty->assign('goodsArr4',	$resArr4);
    }
    $smarty->display('active'.$pid.'.dwt',$cache_id);
    exit;
}elseif ($pid == 16121201)//1212博士伦
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(5445,'',''),array(757,'',''),array(101,'',''),
            array(105,'',''),array(4751,'',''),array(5353,'',''),
            array(5077,'',''),array(970,'',''),array(971,'',''),
            array(113,'',''),array(111,'',''),array(5443,'',''),
        );   
        $goodsArr2 = array(
            array(2581,'','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
            array(811,'','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'),
            array(5359,'','category.php?search=1&keyword=博士伦莹翠亮眸半年抛彩色隐形眼镜1片装'),
            array(3078,'','category.php?search=1&keyword=博士伦莹翠亮眸系列年抛型彩色隐形眼镜'),
            array(4325,'','category.php?search=1&keyword=博士伦星悦逸彩系列半年抛彩色隐形眼镜1片装'),
            array(3159,'','category.php?search=1&keyword=博士伦星悦逸彩系列年抛型彩色隐形眼镜'),
            array(976,'','category.php?search=1&keyword=博士伦水灵焕彩年抛型彩色'),
            array(4976,'','category.php?search=1&keyword=博士伦睛璨明眸日抛型彩色隐形眼镜10片装'),
            array(4977,'','category.php?search=1&keyword=博士伦睛璨明眸日抛型彩色隐形眼镜30片装'),
        );
        $goodsArr3 = array(
            array(4925,'',''),array(2412,'',''),array(592,'',''),
            array(1035,'',''),array(2191,'',''),array(791,'',''),
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}elseif ($pid == 16121202)//1212海昌
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(5095,'',''),array(5097,'',''),array(5098,'',''),
            array(140,'',''),array(834,'',''),array(143,'',''),
            array(152,'',''),array(760,'',''),array(149,'',''),
        );   
        $goodsArr2 = array(
            array(5314,'','category.php?search=1&keyword=海昌清秀佳人月抛彩色隐形眼镜2片装'),
            array(4081,'','category.php?search=1&keyword=海昌之星半年抛型彩色隐形眼镜1片装'),
            array(1005,'','category.php?search=1&keyword=海昌璀灿系列年抛型彩色隐形眼镜'),
            array(5147,'','category.php?search=1&keyword=海昌印象之美日抛彩色隐形眼镜30片装'),
            array(2699,'','category.php?search=1&keyword=海昌甜心布朗尼半年抛彩色隐形眼镜1片装'),
            array(5278,'','category.php?search=1&keyword=海俪恩萌生宠爱半年抛彩色隐形眼镜1片装'),
            array(4166,'','category.php?search=1&keyword=海俪恩魔法魅眼系列年抛型彩色隐形眼镜'),
            array(4845,'','category.php?search=1&keyword=海俪恩潘朵拉宝盒混血自然半年抛彩色隐形眼镜1片装'),
            array(992,'','category.php?search=1&keyword=海昌海俪恩靓彩系列年抛型彩色隐形眼镜'),
        );
        $goodsArr3 = array(
            array(599,'',''),array(5122,'',''),array(2614,'','')
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}elseif ($pid == 16121205)//1212视康
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(1177,'','category.php?search=1&keyword=爱尔康视康睛彩天天抛彩色隐形眼镜10片装'),
            array(118,'',''),array(117,'',''),array(1010,'',''),array(1097,'',''),
            array(585,'',''),array(924,'',''),array(5344,'',''),array(4070,'',''),
        );   
        
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}elseif ($pid == 16121206)//1212卫康
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(4803,'',''),array(139,'',''),array(130,'',''),
            array(4805,'',''),array(4802,'',''),array(3036,'',''),
            array(131,'',''),array(3038,'',''),array(123,'',''),
            array(3081,'',''),array(4800,'',''),array(2403,'',''),
        );   
        $goodsArr2 = array(
            array(786,'',''),array(4884,'',''),array(610,'',''),
            array(2865,'',''),array(2867,'',''),array(4201,'',''),
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
    }
}elseif ($pid == 16121208 )//1212科莱博
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(5164,'',''),array(4937,'',''),array(175,'',''),
        );   
        $goodsArr2 = array(
            array(2927,'','category.php?search=1&keyword=霓彩Käthe系列日抛型彩色隐形眼镜5片装'),
            array(5245,'','category.php?search=1&keyword=科莱博可妮幸运星系列年抛型彩色隐形眼镜1片装'),
            array(4013,'','category.php?search=1&keyword=科莱博冰彩小黑裙系列'),
            array(5037,'','category.php?search=1&keyword=科莱博美妆日抛彩色隐形眼镜5片装'),
            array(5241,'','category.php?search=1&keyword=科莱博小黑裙系列月抛彩色隐形眼镜2片装'),
            array(4637,'','category.php?search=1&keyword=科莱博小黑裙系列日抛型彩色隐形眼镜5片装'),
        );
        $goodsArr3 = array(
            array(860,'',''),array(5277,'',''),array(5150,'',''),
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}elseif ($pid == 16121210)//1212美瞳
{
    $now = time();
    $smarty->caching = false;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
       
        $goodsArr1 = array(
            array(5034,'','category.php?search=1&keyword=BESCON三色润彩系列半年抛彩色隐形眼镜1片装'),
            array(5111,'','category.php?search=1&keyword=伊厶康甜心彩色隐形眼镜年抛1片装'),
            array(5140,'','category.php?search=1&keyword=科尔视格言系列日抛彩色隐形眼镜10片装'),
            array(5066,'','category.php?search=1&keyword=卫康槑瞳眼冒系列半年抛彩色隐形眼镜1片装'),
            array(3641,'','category.php?search=1&keyword=女皇四色'),
            array(1472,'','category.php?search=1&keyword=S513年抛型彩色隐形眼镜'),
            array(5441,'','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜5片装'),
            array(4853,'','category.php?search=1&keyword=菲士康焕彩日抛型彩色隐形眼镜10片装'),
            array(894,'','category.php?search=1&keyword=BESCON双色润彩系列半年抛彩色隐形眼镜1片装'),
            array(2856,'','category.php?search=1&keyword=伊厶康甜甜圈系列月抛型彩色隐形眼镜'),
            array(905,'','category.php?search=1&keyword=BESCON双色钻石系列半年抛彩色隐形眼镜1片装'),
            array(4989,'','category.php?search=1&keyword=KKR舒透氧纯然星动系列彩色隐形眼镜半年抛1片装'),
        );
        
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
    }
}elseif ($pid == 16110115)//1111护理液
{
    $now = time();
    $smarty->caching = false;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(1065,'',''),array(2395,'',''),array(997,'',''),
            array(1121,'',''),array(5003,'',''),array(5255,'',''),
            array(5427,'',''),array(5535,'',''),array(4147,'',''),
        );   
      
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
    }
}elseif ($pid == 161111)//1111主会场
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        //榜单
        $st = strtotime("2016-11-1 00:00:00");
        $et = strtotime("2016-11-15 00:00:00");
        
        $sql = $GLOBALS['db']->getAll("SELECT a.user_id,b.user_name,SUM(goods_amount) AS amount FROM  ".$GLOBALS['ecs']->table("order_info")." a left join ".$GLOBALS['ecs']->table("users")." b 
        ON a.user_id = b.user_id WHERE a.user_id !=0 AND a.pay_status = 2 AND a.pay_time >$st AND a.pay_time <$et GROUP BY a.user_id ORDER BY amount DESC LIMIT 0,12");
        
        foreach($sql as $k=> $v){
            $arr['k'] = $k+1;
            $arr['user_name'] = $v['user_name'];
            $arr['amount']    = $v['amount'];
            $list[]   = $arr;  
        }
        
        //产品
        $goodsArr1 = array(//强生
                array(92,'',''),
                array(91,'',''),
                array(1251,'',''),
                array(227,'',''),
                array(93,'',''),
                array(4783,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜5片装'),
                array(4477,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
        );
        $goodsArr2 = array(//博士伦
                array(103,'',''),array(757,'',''),array(101,'',''),
                array(2581,'','category.php?search=1&keyword=博士伦蕾丝炫眸'),
                array(812,'','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'),
                array(4925,'',''),array(3338,'','')
        );
        $goodsArr3 = array(//海昌
                array(140,'',''),array(2405,'',''),
                array(5095,'',''),
                array(2104,'','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
                array(4878,'','category.php?search=1&keyword=海俪恩马卡龙之吻半年抛彩色隐形眼镜1片装'),
                array(599,'',''),
                array(596,'',''),
        ); 
        $goodsArr4 = array(//视康
                array(119,'',''),array(1097,'',''),array(117,'',''),array(2686,'',''),
                array(118,'',''),array(585,'',''),array(924,'',''),
        );  
        $goodsArr5 = array(//卫康
                array(139,'',''),
                array(130,'',''),
                array(131,'',''),
                array(609,'',''),
                array(786,'',''),
                array(3035,'',''),
                array(5065,'','category.php?search=1&keyword=卫康槑瞳'),
        );  
        $goodsArr6 = array(//库博
                array(1045,'',''),
                array(761,'',''),
                array(185,'',''),
                array(767,'',''),
                array(662,'',''),
                array(1151,'',''),
                array(1153,'',''),
        );   
        
        $goodsArr7 = array(//实瞳
                array(1146,'','category.php?search=1&keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装'),
                array(4299,'',''),
                array(2959,'',''),
                array(1189,'','category.php?search=1&keyword=coffret可芙蕾日抛型彩色隐形眼镜30片装'),
                array(1186,'','category.php?search=1&keyword=实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜10片装'),
                array(5117,'','category.php?search=1&keyword=澜柏沁妍月抛型彩色隐形眼镜2片'),
                array(3630,'','')
        ); 
        
        $goodsArr8 = array(//科莱博
                array(5242,'',''),
                array(5403,'','category.php?search=1&keyword=SUI安娜苏月抛彩色隐形眼镜2片装'),
                array(5408,'','category.php?search=1&keyword=SUI安娜苏半年抛彩色隐形眼镜1片装'),
                array(1471,'','category.php?search=1&keyword=怡美思粉钻系列'),
                array(5036,'','category.php?search=1&keyword=科莱博美妆日抛'),
                array(4934,'',''),
                array(5276,'',''),
        );  
        $goodsArr9 = array(//硅水凝胶
                array(119,'',''),
                array(1097,'',''),
                array(2686,'',''),
                array(118,'',''),
                array(1045,'',''),
                array(93,'',''),
                array(5445,'',''),
        );
        $goodsArr10 = array(//美瞳
                array(4853,'',''),
                array(5142,'','category.php?search=1&keyword=科尔视格言系列日抛彩色隐形眼镜10片装'),
                array(5380,'','category.php?search=1&keyword=可丽博Clearcolor曦彩隐形眼镜半年抛2片装'),
                array(5441,'','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜5片装'),
                array(4885,'','category.php?search=1&keyword=艾爵巧克力公主彩色隐形眼镜月抛1片装'),
                array(5460,'','category.php?search=1&keyword=GEO星璇系列年抛彩色隐形眼镜1片装'),
                array(356,'',''),
        );  
        $goodsArr11 = array(//散光片
                array(1251,'',''),
                array(4914,'',''),
                array(4921,'',''),
                array(3081,'',''),
                array(168,'','category.php?search=1&keyword=海昌锐视定制近视散光隐形眼镜'),
                array(1011,'',''),
                array(2555,'',''),
        );
        $goodsArr12 = array(//护理液
                array(592,'',''),
                array(585,'',''),
                array(596,'',''),
                array(609,'',''),
                array(2412,'',''),
                array(599,'',''),
                array(924,'',''),
        );
        
        $smarty->assign('list',	        $list);
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
        $smarty->assign('goodsArr5',	get_goods_info_active($goodsArr5));
        $smarty->assign('goodsArr6',	get_goods_info_active($goodsArr6));
        $smarty->assign('goodsArr7',	get_goods_info_active($goodsArr7));
        $smarty->assign('goodsArr8',	get_goods_info_active($goodsArr8));
        $smarty->assign('goodsArr9',	get_goods_info_active($goodsArr9));
        $smarty->assign('goodsArr10',	get_goods_info_active($goodsArr10));
        $smarty->assign('goodsArr11',	get_goods_info_active($goodsArr11));
        $smarty->assign('goodsArr12',	get_goods_info_active($goodsArr12));
    }
    
    //秒杀
        $time = $now;
        $d = date('d');
        
        if($d <= 10){
            $ms_goods = array(
                array(5045,'',''),array(1121,'',''),array(3961,'','')
            ); 
        }elseif($d == 11){
            $ms_goods = array(
                array(5549,'',''),array(3000,'',''),array(5517,'','')
            ); 
        }elseif($d ==12){
            $ms_goods = array(
                array(5550,'',''),array(5523,'',''),array(5271,'','')
            ); 
        }elseif($d ==13){
            $ms_goods = array(
                array(1182,'',''),array(5481,'',''),array(5486,'','')
            ); 
        }elseif($d ==14){
            $ms_goods = array(
                array(2983,'',''),array(5428,'',''),array(2758,'','')
            ); 
        }
        
        $ms_goods = get_goods_info_active($ms_goods);
        
        if($time<=strtotime('2016-11-15')){
            if( $time <= strtotime('2016-11-'.$d.' 11:00:00') ){
                $status_1 = 0;  $status_2 = 0;  $status_3 = 0;
            }elseif( $time >= strtotime('2016-11-'.$d.' 11:00:00') && $time <= strtotime('2016-11-'.$d.' 11:30:00') ){//第一波
                $status_1 = 1;  $status_2 = 0;  $status_3 = 0;
            }elseif( $time >= strtotime('2016-11-'.$d.' 11:30:00') && $time <= strtotime('2016-11-'.$d.' 15:00:00') ){
                $status_1 = 2;  $status_2 = 0;  $status_3 = 0;
            }elseif( $time >= strtotime('2016-11-'.$d.' 15:00:00') && $time <= strtotime('2016-11-'.$d.' 15:30:00') ){////第二波
                $status_1 = 2;  $status_2 = 1;  $status_3 = 0;
            }elseif( $time >= strtotime('2016-11-'.$d.' 15:30:00') && $time <= strtotime('2016-11-'.$d.' 21:00:00') ){
                $status_1 = 2;  $status_2 = 2;  $status_3 = 0;
            }elseif( $time >= strtotime('2016-11-'.$d.' 21:00:00') && $time <= strtotime('2016-11-'.$d.' 21:30:00') ){////第三波
                $status_1 = 2;  $status_2 = 2;  $status_3 = 1;
            }else{
                $status_1 = 2;  $status_2 = 2;  $status_3 = 2;
            }
        }else{
            $status_1 = 2;
            $status_2 = 2;
            $status_3 = 2;
        }
        $ms_goods[0]['status'] = $status_1;
        $ms_goods[1]['status'] = $status_2;
        $ms_goods[2]['status'] = $status_3;
        $smarty->assign('msGoods',	$ms_goods);
    
        $smarty->display('active'.$pid.'.dwt',$cache_id);
        exit;
}elseif ($pid == 161115)//1111主会场
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        
        //产品
        $goodsArr1 = array(//强生
                array(92,'',''),
                array(91,'',''),
                array(1251,'',''),
                array(227,'',''),
                array(93,'',''),
                array(4783,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜5片装'),
                array(4477,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
        );
        $goodsArr2 = array(//博士伦
                array(103,'',''),array(757,'',''),array(101,'',''),
                array(2581,'','category.php?search=1&keyword=博士伦蕾丝炫眸'),
                array(812,'','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'),
                array(4925,'',''),array(3338,'','')
        );
        $goodsArr3 = array(//海昌
                array(140,'',''),array(2405,'',''),
                array(5095,'',''),
                array(2104,'','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
                array(4878,'','category.php?search=1&keyword=海俪恩马卡龙之吻半年抛彩色隐形眼镜1片装'),
                array(599,'',''),
                array(596,'',''),
        ); 
        $goodsArr4 = array(//视康
                array(119,'',''),array(1097,'',''),array(117,'',''),array(2686,'',''),
                array(118,'',''),array(585,'',''),array(924,'',''),
        );  
        $goodsArr5 = array(//卫康
                array(139,'',''),
                array(130,'',''),
                array(131,'',''),
                array(609,'',''),
                array(786,'',''),
                array(3035,'',''),
                array(5065,'','category.php?search=1&keyword=卫康槑瞳'),
        );  
        $goodsArr6 = array(//库博
                array(1045,'',''),
                array(761,'',''),
                array(185,'',''),
                array(767,'',''),
                array(662,'',''),
                array(1151,'',''),
                array(1153,'',''),
        );   
        
        $goodsArr7 = array(//实瞳
                array(1146,'','category.php?search=1&keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装'),
                array(4299,'',''),
                array(2959,'',''),
                array(1189,'','category.php?search=1&keyword=coffret可芙蕾日抛型彩色隐形眼镜30片装'),
                array(1186,'','category.php?search=1&keyword=实瞳Eye coffret可芙蕾日抛型彩色隐形眼镜10片装'),
                array(5117,'','category.php?search=1&keyword=澜柏沁妍月抛型彩色隐形眼镜2片'),
                array(3630,'','')
        ); 
        
        $goodsArr8 = array(//科莱博
                array(5242,'',''),
                array(5403,'','category.php?search=1&keyword=SUI安娜苏月抛彩色隐形眼镜2片装'),
                array(5408,'','category.php?search=1&keyword=SUI安娜苏半年抛彩色隐形眼镜1片装'),
                array(1471,'','category.php?search=1&keyword=怡美思粉钻系列'),
                array(5036,'','category.php?search=1&keyword=科莱博美妆日抛'),
                array(4934,'',''),
                array(5276,'',''),
        );  
        $goodsArr9 = array(//硅水凝胶
                array(119,'',''),
                array(1097,'',''),
                array(2686,'',''),
                array(118,'',''),
                array(1045,'',''),
                array(93,'',''),
                array(5445,'',''),
        );
        $goodsArr10 = array(//美瞳
                array(4853,'',''),
                array(5142,'','category.php?search=1&keyword=科尔视格言系列日抛彩色隐形眼镜10片装'),
                array(5380,'','category.php?search=1&keyword=可丽博Clearcolor曦彩隐形眼镜半年抛2片装'),
                array(5441,'','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜5片装'),
                array(4885,'','category.php?search=1&keyword=艾爵巧克力公主彩色隐形眼镜月抛1片装'),
                array(5460,'','category.php?search=1&keyword=GEO星璇系列年抛彩色隐形眼镜1片装'),
                array(356,'',''),
        );  
        $goodsArr11 = array(//散光片
                array(1251,'',''),
                array(4914,'',''),
                array(4921,'',''),
                array(3081,'',''),
                array(168,'','category.php?search=1&keyword=海昌锐视定制近视散光隐形眼镜'),
                array(1011,'',''),
                array(2555,'',''),
        );
        $goodsArr12 = array(//护理液
                array(592,'',''),
                array(585,'',''),
                array(596,'',''),
                array(609,'',''),
                array(2412,'',''),
                array(599,'',''),
                array(924,'',''),
        );
        
        $smarty->assign('list',	        $list);
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
        $smarty->assign('goodsArr5',	get_goods_info_active($goodsArr5));
        $smarty->assign('goodsArr6',	get_goods_info_active($goodsArr6));
        $smarty->assign('goodsArr7',	get_goods_info_active($goodsArr7));
        $smarty->assign('goodsArr8',	get_goods_info_active($goodsArr8));
        $smarty->assign('goodsArr9',	get_goods_info_active($goodsArr9));
        $smarty->assign('goodsArr10',	get_goods_info_active($goodsArr10));
        $smarty->assign('goodsArr11',	get_goods_info_active($goodsArr11));
        $smarty->assign('goodsArr12',	get_goods_info_active($goodsArr12));
    }
    

      
    
        $smarty->display('active'.$pid.'.dwt',$cache_id);
        exit;
}elseif ($pid == 161125)//16黑五
{
    
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(101,'',''),
            array(103,'',''),
            array(119,'',''),
            array(1010,'',''),
            array(1045,'送45元礼包',''),
            array(767,'送27元礼包',''),
            array(5095,'',''),
            array(2405,'',''),
            array(1149,'',''),
            array(4299,'',''),
            array(92,'',''),
            array(93,'两盒减20',''),
            array(3035,'',''),
            array(5164,'',''),
            array(139,'',''),
        );
        $goodsArr2 = array(//美瞳
                array(982,'买三送一','category.php?search=1&keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜5片装'),
                array(2582,'买三送一','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
                array(987,'','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
                array(5236,'','category.php?search=1&keyword=海俪恩萤之光半年抛彩色隐形眼镜1片装'),
                array(4882,'','category.php?search=1&keyword=海俪恩马卡龙之吻半年抛彩色隐形眼镜1片装'),
                array(1187,'','category.php?search=1&keyword=实瞳Eye+coffret可芙蕾日抛型彩色隐形眼镜30片装'),
                array(5408,'','category.php?search=1&keyword=ANNA+SUI安娜苏半年抛彩色隐形眼镜1片装'),
                array(4475,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
                array(5281,'','category.php?search=1&keyword=海俪恩萌生宠爱半年抛彩色隐形眼镜1片装'),
                array(5334,'买一送一','category.php?search=1&keyword=科莱博clbcolor丝韵彩色隐形眼镜年抛型1片装'),
                array(5486,'下单减30','category.php?search=1&keyword=可视眸NEO小棕环日抛型彩色隐形眼镜10片装'),
                array(4853,'买一送一','category.php?search=1&keyword=菲士康焕彩日抛型彩色隐形眼镜10片装'),
                array(351,'买一送一','category.php?search=1&keyword=NEO公主系列'),
                array(3641,'买一送一','category.php?search=1&keyword=NEO可视眸女皇四色'),
                array(5345,'买一送一','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜5片装'),
                
        );   
        $goodsArr3 = array(
            array(3338,'会员102',''),
            array(4925,'会员76',''),
            array(2412,'',''),
            array(609,'',''),
            array(580,'',''),
            array(5304,'',''),
            array(596,'',''),
            array(2959,'买一送一',''),
            array(5427,'','')
        );
        $goodsArr4 = array(
            array(5308,'','category.php?search=1&keyword=Seven七度复古黑边时尚太阳眼镜1054'),
            array(3879,'',''),
            array(5364,'','category.php?search=1&keyword=Seven七度复古金属半框太阳镜'),
            array(2046,'',''),
            array(1402,'',''),
            array(3271,'',''),
            array(4594,'',''),
            array(1319,'',''),
            array(4687,'',''),
                
        ); 
        $d = date('d');
        
        if($d == 25){
            $msArr = array(
                array(359,'','miaosha_buy_359.html'),
                array(4925,'',''),
            );
            $smarty->assign('jpg',	'02.jpg');
            $smarty->assign('msGoods',	get_goods_info_active($msArr));
        }elseif($d == 26 || $d == 27){
            $msArr = array(
                array(1187,'','category.php?search=1&keyword=实瞳Eye+coffret可芙蕾日抛型彩色隐形眼镜30片装'),
                array(2405,'',''),
            );
            $smarty->assign('jpg',	'02_2.jpg');
            $smarty->assign('msGoods',	get_goods_info_active($msArr));
        }elseif($d == 28){
            $msArr = array(
                array(118,'','miaosha_buy_118.html'),
                array(1151,'',''),
            );
            $smarty->assign('jpg',	'02_3.jpg');
            $smarty->assign('msGoods',	get_goods_info_active($msArr));
        }else{
            $msArr = array(
                array(359,'','miaosha_buy_359.html'),
                array(4925,'',''),
            );
            $smarty->assign('jpg',	'02_3.jpg');
            $smarty->assign('msGoods',	get_goods_info_active($msArr));
        }
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
    }
}elseif ($pid == 161201)
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(2405,'第二件半价',''),array(4299,'第二件半价',''),array(767,'第二件半价',''),
            array(103,'第二件半价',''),array(101,'第二件半价',''),array(1045,'第二件半价',''),
            array(662,'第二件半价',''),array(5095,'第二件半价',''),array(4751,'第二件半价',''),
            array(4803,'第二件半价',''),array(5164,'第二件半价',''),array(93,'第二件半价',''),
            array(844,'第二件半价',''),array(731,'第二件半价',''),array(3035,'第二件半价',''),
            array(168,'第二件半价','category.php?search=1&keyword=海昌锐视'),
        );   
        $goodsArr2 = array(
            array(982,'第二件半价','category.php?search=1&keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜5片装'),
            array(1187,'第二件半价','category.php?search=1&keyword=coffret可芙蕾日抛型彩色隐形眼镜30片装'),
            array(4494,'第二件半价','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
            array(3630,'第二件半价',''),
            array(5335,'第二件半价','category.php?search=1&keyword=科莱博clbcolor丝韵彩色隐形眼镜年抛型1片装'),
            array(4282,'第二件半价','category.php?search=1&keyword=西武Secret CandyEyes系列年抛型彩色隐形眼镜1片装'),
            array(4504,'第二件半价','category.php?search=1&keyword=菲士康大美目半年抛彩色隐形眼镜1片装'),
            array(2581,'第二件半价','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
            array(5446,'第二件半价','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜30片装'),
            array(1146,'第二件半价','category.php?search=1&keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装'),
            array(5281,'第二件半价','category.php?search=1&keyword=海俪恩萌生宠爱半年抛彩色隐形眼镜1片装'),
            array(5118,'第二件半价','category.php?search=1&keyword=澜柏沁妍月抛型彩色隐形眼镜2片'),
            array(5504,'第二件半价','category.php?search=1&keyword=GEO奶茶'),
            array(5066,'第二件半价','category.php?search=1&keyword=卫康槑瞳'),
            array(4824,'第二件半价','category.php?search=1&keyword=海俪恩桃花秀幻境精灵半年抛彩色隐形眼镜1片'),
            array(5402,'第二件半价','category.php?search=1&keyword=SUI安娜苏月抛彩色隐形眼镜2片装'),
        );
        $goodsArr3 = array(
            array(3338,'第二件半价',''),array(609,'第二件半价',''),array(4925,'第二件半价',''),
            array(580,'第二件半价',''),array(5427,'第二件半价',''),array(860,'第二件半价',''),
            array(921,'第二件半价',''),array(2959,'第二件半价',''),array(4973,'第二件半价',''),
            array(5304,'第二件半价',''),array(596,'第二件半价',''),array(1121,'第二件半价',''),
        ); 
        $goodsArr4 = array(
            array(4594,'限时半价',''),array(1319,'限时半价',''),array(4592,'限时半价',''),
            array(3812,'限时半价',''),array(1357,'限时半价',''),array(1686,'限时半价',''),
            array(3271,'限时半价',''),array(2046,'限时半价',''),
            array(5362,'限时半价','category.php?search=1&keyword=Seven七度复古金属半框太阳镜'),
            array(4657,'限时半价',''),array(4665,'限时半价',''),array(2595,'限时半价',''),
        ); 
        $smarty->assign('user_id',	    $_SESSION['user_id'] );
        $g1 = get_goods_info_active($goodsArr1);
        $g2 = get_goods_info_active($goodsArr2);
        $g3 = get_goods_info_active($goodsArr3);
        
        foreach($g1 as $k=>$v){
            $g1[$k]['now_price'] = $v['now_price']*0.75;
        }
        foreach($g2 as $k=>$v){
            $g2[$k]['now_price'] = $v['now_price']*0.75;
        }
        foreach($g3 as $k=>$v){
            $g3[$k]['now_price'] = $v['now_price']*0.75;
        }
        
        $smarty->assign('goodsArr1',	$g1);
        $smarty->assign('goodsArr2',	$g2);
        $smarty->assign('goodsArr3',	$g3);
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
    }
}elseif ($pid == 161212)//1212主会场
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    $smarty->assign('user_id',	    $_SESSION['user_id'] );
    
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
      
        //产品
        $goodsArr1 = array(//强生
                array(92,'',''),
                array(91,'',''),
                array(93,'',''),
                array(1251,'',''),
                array(4783,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜5片装'),
                array(4477,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
                array(227,'',''),
        );
        $goodsArr2 = array(//博士伦
                array(103,'',''),array(3338,'',''),array(101,'',''),
                array(950,'','category.php?search=1&keyword=博士伦蕾丝明眸日抛型彩色隐形眼镜30片装'),
                array(757,'',''),
                array(4925,'',''),
                array(947,'','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装')
        );
        
        $goodsArr3 = array(//海昌
                array(140,'',''),
                array(2104,'','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜30片装'),
                array(5095,'',''),
                array(2405,'',''),
                array(596,'',''),
                array(599,'',''),
                array(4878,'','category.php?search=1&keyword=海俪恩马卡龙之吻半年抛彩色隐形眼镜1片装'),
        ); 
        
        $goodsArr4 = array(//视康
                array(119,'',''),array(1097,'',''),array(117,'',''),array(2686,'',''),
                array(118,'',''),array(585,'',''),array(924,'',''),
        );  
        
        $goodsArr5 = array(//卫康
                array(139,'',''),
                array(130,'',''),
                array(131,'',''),
                array(609,'',''),
                array(786,'',''),
                array(3035,'',''),
                array(5065,'','category.php?search=1&keyword=卫康槑瞳'),
        ); 
        $goodsArr6 = array(//库博
                array(1045,'',''),
                array(761,'',''),
                array(185,'',''),
                array(767,'',''),
                array(662,'',''),
                array(1151,'',''),
                array(1153,'',''),
        );   
        
        $goodsArr7 = array(//实瞳
                array(1187,'','category.php?search=1&keyword=可芙蕾日抛型彩色隐形眼镜30片装'),
                array(4299,'',''),
                array(3630,'',''),
                array(1146,'','category.php?search=1&keyword=实瞳幻樱恋必顺双周抛彩色隐形眼镜6片装'),
                array(1184,'','category.php?search=1&keyword=可芙蕾日抛型彩色隐形眼镜10片装'),
                array(2960,'',''),
                array(5117,'','category.php?search=1&keyword=澜柏沁妍月抛型彩色隐形眼镜2片')
        ); 
        
        $goodsArr8 = array(//科莱博
                array(5242,'',''),
                array(5164,'',''),
                array(1471,'','category.php?search=1&keyword=怡美思粉钻系列'),
                array(4934,'',''),
                array(5276,'',''),
                array(5408,'','category.php?search=1&keyword=SUI安娜苏半年抛彩色隐形眼镜1片装'),
                array(5403,'','category.php?search=1&keyword=SUI安娜苏月抛彩色隐形眼镜2片装'),
        ); 
        
        $goodsArr9 = array(//菲士康
                array(229,'',''),
                array(4849,'',''),
                array(228,'',''),
                array(731,'',''),
                array(236,'','category.php?search=1&keyword=菲士康焕彩月抛型彩色隐形眼镜2片装'),
                array(4850,'','category.php?search=1&keyword=菲士康大美目日抛型彩色隐形眼镜10片装'),
                array(4853,'','category.php?search=1&keyword=菲士康焕彩日抛型彩色隐形眼镜10片装'),
        );
        
        $goodsArr10 = array(//硅水凝胶
                array(119,'',''),
                array(1097,'',''),
                array(2686,'',''),
                array(118,'',''),
                array(1045,'',''),
                array(93,'',''),
                array(5445,'',''),
        );
        
        $goodsArr11 = array(//美瞳
                array(4853,'',''),
                array(5142,'','category.php?search=1&keyword=科尔视格言系列日抛彩色隐形眼镜10片装'),
                array(356,'',''),
                array(5441,'','category.php?search=1&keyword=安瞳Mandol蔓朵系列日抛型彩色隐形眼镜5片装'),
                array(4885,'','category.php?search=1&keyword=艾爵巧克力公主彩色隐形眼镜月抛1片装'),
                array(5460,'','category.php?search=1&keyword=GEO星璇系列年抛彩色隐形眼镜1片装'),
                array(5380,'','category.php?search=1&keyword=可丽博Clearcolor曦彩隐形眼镜半年抛2片装'),
        );  
        
        
        $goodsArr12 = array(//散光片
                array(1251,'',''),
                array(4914,'',''),
                array(4921,'',''),
                array(3081,'',''),
                array(168,'','category.php?search=1&keyword=海昌锐视定制近视散光隐形眼镜'),
                array(1011,'',''),
                array(2555,'',''),
        );
        
        
        
        $time = $now;
        $d = date('d');
        
        if($d <= 9){
            $ms_goods = array(
                array(5136,'',''),array(4144,'',''),array(4886,'','')
            ); 
        }elseif($d == 10){
            $ms_goods = array(
                array(4139,'',''),array(1121,'',''),array(3097,'','')
            ); 
        }elseif($d ==11){
            $ms_goods = array(
                array(4141,'',''),array(5428,'',''),array(617,'','')
            ); 
        }
        
        $ms_goods = get_goods_info_active($ms_goods);
        
        foreach($ms_goods as $k=>$v){
            $ms_goods[$k]['status'] = 1;
            if($v['ms_limit']>0){
                $cartNum  = $GLOBALS['db']->getOne("SELECT SUM(goods_number) FROM ecs_cart WHERE goods_id = ".$v['goods_id']." AND extension_code = 'miaosha_buy'");
                $orderNum = $GLOBALS['db']->getOne("SELECT SUM(goods_number) FROM ecs_order_goods WHERE goods_id = ".$v['goods_id']." AND extension_code = 'miaosha_buy'");
                $totalNum = $cartNum+$orderNum;
                if($totalNum >= $v['ms_limit']){
                    $ms_goods[$k]['status'] = 2;
                }else{
                    $ms_goods[$k]['status'] = 1;
                }
            }
        }
        
        $smarty->assign('msGoods',	$ms_goods);
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
        $smarty->assign('goodsArr4',	get_goods_info_active($goodsArr4));
        $smarty->assign('goodsArr5',	get_goods_info_active($goodsArr5));
        $smarty->assign('goodsArr6',	get_goods_info_active($goodsArr6));
        $smarty->assign('goodsArr7',	get_goods_info_active($goodsArr7));
        $smarty->assign('goodsArr8',	get_goods_info_active($goodsArr8));
        $smarty->assign('goodsArr9',	get_goods_info_active($goodsArr9));
        $smarty->assign('goodsArr10',	get_goods_info_active($goodsArr10));
        $smarty->assign('goodsArr11',	get_goods_info_active($goodsArr11));
        $smarty->assign('goodsArr12',	get_goods_info_active($goodsArr12));
    }
        if($now >strtotime('2016-12-12 00:00:00')){
            $hide = 1;
            $smarty->assign('hide',	$hide);
        }
        $smarty->display('active'.$pid.'.dwt',$cache_id);
        exit;
}elseif ($pid == 161216)//1216
{
    $now = time();
    $smarty->caching = true;
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(1187,'',''),array(4299,'',''),array(4298,'',''),
            array(3630,'',''),array(1186,'',''),array(3631,'',''),
            array(1188,'',''),array(1189,'','')
        );  
        
        $goodsArr2 = array(
            array(1471,'','category.php?search=1&keyword=怡美思粉钻系列'),
            array(5330,'','category.php?search=1&keyword=科莱博clbcolor丝韵彩色隐形眼镜年抛型1片装'),
            array(2856,'','category.php?search=1&keyword=伊厶康甜甜圈系列月抛型彩色隐形眼镜'),
            array(351,'','category.php?search=1&keyword=NEO公主系列三色'),
            array(4146,'','category.php?search=1&keyword=Classic经典大直径系列半年抛彩色隐形眼镜1片装'),
            array(4138,'','category.php?search=1&keyword=Circle大直径系列半年抛彩色隐形眼镜1片装'),
            array(3636,'','category.php?search=1&keyword=NEO可视眸巨目'),
            array(5111,'','category.php?search=1&keyword=伊厶康甜心彩色隐形眼镜年抛1片装'),
        );
        
        $goodsArr3 = array(
            array(4853,'','category.php?search=1&keyword=菲士康焕彩日抛型彩色隐形眼镜10片装'),
            array(4500,'','category.php?search=1&keyword=菲士康大美目日抛型彩色隐形眼镜10片装'),
            array(231,'','category.php?search=1&keyword=菲士康大美目半年抛彩色隐形眼镜2片装'),
            array(5379,'','category.php?search=1&keyword=可丽博Clearcolor曦彩隐形眼镜半年抛2片装'),
            array(5002,'','category.php?search=1&keyword=可丽博clearcolor燃彩灰色年抛型彩色隐形眼镜'),
            array(4563,'','category.php?search=1&keyword=可丽博雯彩系列年抛型彩色隐形眼镜'),
            array(2959,'',''),
            array(1121,'','')
        );
      
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
}elseif ($pid == 161222)//12222
{
    $now = time();
    $smarty->caching = true;
    $smarty->assign('user_id',	$_SESSION['user_id']);
    $cache_id = sprintf('%X', crc32('active'.$pid));
    if(!$smarty->is_cached('active'.$pid.'.dwt', $cache_id))
    {
        $goodsArr1 = array(
            array(92,'',''),array(2405,'',''),array(834,'',''),
            array(101,'',''),array(103,'',''),array(4751,'',''),
            array(1010,'',''),array(662,'',''),array(757,'',''),
            array(5445,'',''),array(5095,'',''),array(1097,'',''),
        );   
        $goodsArr2 = array(
            array(2582,'','category.php?search=1&keyword=博士伦蕾丝炫眸彩色隐形眼镜日抛10片装'),
            array(4477,'','category.php?search=1&keyword=强生安视优define美瞳日抛彩色隐形眼镜30片装'),
            array(3155,'','category.php?search=1&keyword=博士伦星悦逸彩系列年抛型彩色隐形眼镜'),
            array(2699,'','category.php?search=1&keyword=海昌甜心布朗尼半年抛彩色隐形眼镜1片装'),
            array(2059,'','category.php?search=1&keyword=海昌星眸日抛型彩色隐形眼镜20片装'),
            array(947,'','category.php?search=1&keyword=博士伦蕾丝明眸两周抛彩色隐形眼镜6片装'),
            array(4081,'','category.php?search=1&keyword=海昌之星半年抛型彩色隐形眼镜1片装'),
            array(4753,'','category.php?search=1&keyword=博士伦睛璨明眸日抛型彩色隐形眼镜30片装'),
        );
        $goodsArr3 = array(
            array(4925,'',''),array(3338,'',''),array(5122,'',''),
            array(585,'',''),array(596,'',''),array(2412,'',''),
            array(4147,'',''),array(599,'','')
        ); 
        $smarty->assign('goodsArr1',	get_goods_info_active($goodsArr1));
        $smarty->assign('goodsArr2',	get_goods_info_active($goodsArr2));
        $smarty->assign('goodsArr3',	get_goods_info_active($goodsArr3));
    }
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
 * 获取
 */
function get_link_goods_color($goods_id){
    $resArr = array();
    $main_color =  $GLOBALS['db']->getOne("SELECT attr_value FROM ecs_goods_attr WHERE goods_id = ".$goods_id." AND attr_id = 212");

    $link_goods = $GLOBALS['db']->getAll("SELECT link_goods_id FROM ".$GLOBALS['ecs']->table('link_goods')." WHERE goods_id = ".$goods_id);
    foreach($link_goods as $v){
        $color = $GLOBALS['db']->getOne("SELECT attr_value FROM ecs_goods_attr WHERE goods_id = ".$v['link_goods_id']." AND attr_id = 212");
        $arr['goods_id']    =   $v['link_goods_id'];
        $arr['color']       =   $color;
        $resArr[] = $arr;
    }
    array_push($resArr,array('goods_id'=>$goods_id,'color'=>$main_color));//加入原来的主商品
    $resArr = array_reverse($resArr);//倒叙
    return $resArr;
}
/**
 *获取商品详情（用于活动页） 
 * @param $goodsArr[0]:商品id，$goodsArr[1]:角标文字，$goodsArr[2]:单独链接
 */
function get_goods_info_active($goodsArr){
    $now = time();
    foreach($goodsArr as $k=>$v){
        $goods_info = $GLOBALS['db']->getRow("SELECT goods_id,goods_thumb,goods_name,market_price,shop_price,original_img,is_promote,promote_price,promote_start_date,promote_end_date FROM " .
        $GLOBALS['ecs']->table('goods') . " WHERE goods_id =" . $v[0]);
        $arr[$k]['goods_id']        = $v[0]; 
        $arr[$k]['goods_name']      = $goods_info['goods_name']; 
        $arr[$k]['shop_price']      = $goods_info['shop_price'];
        //tao临时新增逻辑：如果不是抢购价则根据shop_price换算会员价格 95/97/98折
        if(($goods_info['is_promote'] != 1) || $now <$goods_info['promote_start_date'] || $now > $goods_info['promote_end_date']){
            $arr[$k]['vip_price_1']      = $goods_info['shop_price']*0.98;//vip
            $arr[$k]['vip_price_2']      = $goods_info['shop_price']*0.97;//白金
            $arr[$k]['vip_price_3']      = $goods_info['shop_price']*0.95;//钻石
        }
        $arr[$k]['now_price']      = floor(($goods_info['is_promote'] == 1 && $now >$goods_info['promote_start_date'] && $now < $goods_info['promote_end_date'])? $goods_info['promote_price']:$goods_info['shop_price']);
        $arr[$k]['market_price']    = $goods_info['market_price'];
        $arr[$k]['goods_img']       = $goods_info['original_img'];
        $arr[$k]['goods_link']      = 'goods'.$v[0].'.html'; 
        $arr[$k]['zk']              = '';
        $arr[$k]['ms_price']        = get_ms_price($v[0]);
        $arr[$k]['ms_limit']        = get_ms_total_limited($v[0]);
        if($goods_info['promote_price']>0 && $now >$goods_info['promote_start_date'] && $now < $goods_info['promote_end_date']){
                $arr[$k]['zk']              = $goods_info['shop_price'] - $goods_info['promote_price'];
        }
        if(!empty($v[1])){
            $arr[$k]['tips']        = $v[1];
        }
        if(!empty($v[2])){
            $arr[$k]['goods_link']  = $v[2];
        }
    }
    return $arr;
}
/**
 * 取得某商品的秒杀价格
 */
function get_ms_price($goods_id){
    $now = time();
    //return $GLOBALS['db']->getOne("SELECT price FROM ecs_miaosha WHERE goods_id = ".$goods_id." AND ".$now." > start_time AND ".$now." < end_time AND status = 0 LIMIT 1");
    return $GLOBALS['db']->getOne("SELECT price FROM ecs_miaosha WHERE goods_id = ".$goods_id."  AND status = 0  ORDER BY rec_id DESC LIMIT 1");
}
/**
 * 取得某商品的秒杀限购数量
 */
function get_ms_total_limited($goods_id){
    $now = time();
    //return $GLOBALS['db']->getOne("SELECT price FROM ecs_miaosha WHERE goods_id = ".$goods_id." AND ".$now." > start_time AND ".$now." < end_time AND status = 0 LIMIT 1");
    return $GLOBALS['db']->getOne("SELECT total_limited FROM ecs_miaosha WHERE goods_id = ".$goods_id."  AND status = 0 ORDER BY rec_id DESC LIMIT 1");
}
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