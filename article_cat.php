<?php
/* =======================================================================================================================
 * 商城页面 文章分类页面 【Author:yijiangwen】【TIME:2012/10/22】
 * =======================================================================================================================
 * article_cat-88.html
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
//获得指定的分类ID
if(!empty($_GET['id']))
{
    $cat_id = intval($_GET['id']);
}
elseif(!empty($_GET['category']))
{
    $cat_id = intval($_GET['category']);
}
else
{
    ecs_header("Location: ./\n");//参数错误，跳回首页。
    exit;
}

$page     = !empty($_REQUEST['page'])? intval($_REQUEST['page']): 1;//当前页码
$cache_id = sprintf('%X', crc32($cat_id . '-' . $page . '-' . $_CFG['lang']));//获得页面的缓存ID

if(!$smarty->is_cached('article_cat.dwt', $cache_id))
{
	/*------------------------------------页头 页尾 数据---------------------------------------*/
	$position = assign_ur_here();
	//$smarty->assign('page_title',        $position['title']);    
	$smarty->assign('ur_here',             $position['ur_here']); 
	$smarty->assign('topbanner',           ad_info(31,1));            //头部横幅广告
	//页尾
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

    assign_template('a', array($cat_id));    

	$tit = get_article_cat_cat_name($cat_id);
	$smarty->assign('ur_here',              '<a href="\.">首页</a> <code>></code> '.$tit); //当前位置
	$smarty->assign('page_title',           $tit.'_易视网'); 
    $smarty->assign('article_cat',          get_shop_help2());//右边5类文章列表
	$smarty->assign('latest_promote',       get_latest_promote());//限时抢购
	$smarty->assign('link_goods',		    get_article_link_goods(4));//关联商品(随机)

    //SEO Meta
    $meta = $db->getRow("SELECT keywords, cat_desc FROM " . $ecs->table('article_cat') . " WHERE cat_id = '$cat_id'");
    if($meta === false || empty($meta))
    {
        ecs_header("Location: ./\n");
        exit;
    }
    $smarty->assign('keywords',    htmlspecialchars($meta['keywords']));
    $smarty->assign('description', htmlspecialchars($meta['cat_desc']));


	//文章列表分页操作
    //$size   = isset($_CFG['article_page_size']) && intval($_CFG['article_page_size']) > 0 ? intval($_CFG['article_page_size']) : 20;
	$size = 6;
    $count  = get_article_count($cat_id);//文章总数
    $pages  = ($count > 0) ? ceil($count / $size) : 1;
    if($page > $pages)
    {
        $page = $pages;
    }
    $pager['search']['id'] = $cat_id;
    $keywords = '';

    //获得文章列表
    if(isset($_GET['keywords']))
    {
        $keywords = addslashes(urldecode(trim($_GET['keywords'])));
        $pager['search']['keywords'] = $keywords;
        $search_url = $_SERVER['REQUEST_URI'];

        $smarty->assign('search_value',     $keywords);
        $smarty->assign('search_url',       $search_url);
        $count  = get_article_count($cat_id, $keywords);
        $pages  = ($count > 0) ? ceil($count / $size) : 1;
        if($page > $pages)
        {
            $page = $pages;
        }
    }	
    $pager = get_pager('article_cat.php', $pager['search'], $count, $page, $size);  //分页数据
    $smarty->assign('pager', $pager);

    $smarty->assign('artciles_list', get_cat_articles($cat_id, $page, $size ,$keywords));
    $smarty->assign('cat_id',    $cat_id);
    $smarty->assign('sales_charts1',    get_sales_charts_all(0,3));  //热销排行榜1
    $smarty->assign('sales_charts2',    get_sales_charts_all(3,3));  //热销排行榜2
    $smarty->assign('sales_charts3',    get_sales_charts_all(6,3));  //热销排行榜3

    assign_dynamic('article_cat');
}

$smarty->display('article_cat.dwt', $cache_id);



//=============================================================================【函数】=============================================================================//
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:获得最新的限时抢购商品
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_latest_promote()
{
	$now = $_SERVER['REQUEST_TIME'];
    $sql = "SELECT goods_id, goods_name, promote_start_date as start_time, promote_end_date as end_time, goods_thumb, goods_img, promote_img ".
            " FROM " . $GLOBALS['ecs']->table('goods') ." WHERE is_promote=1 AND is_on_sale=1 AND is_delete=0 " .
            " AND promote_start_date <= '$now' AND promote_end_date >= '$now' ORDER BY start_time DESC LIMIT 1";
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:关联商品（随机四个）
 * ----------------------------------------------------------------------------------------------------------------------
 * 随机推荐商品
 */
function get_article_link_goods($num=4)
{	
	$goods = array();
	for($i=0; $i<$num; $i++)
	{
		$rad = mt_rand(90,1000);		
		$sql = "select g.*, b.brand_name, b.site_url from ecs_goods as g left join ecs_category as c on g.cat_id=c.cat_id left join ecs_brand as b on g.brand_id=b.brand_id ".
			   " where g.goods_id=".$rad." and g.goods_number>0 and g.is_on_sale=1 and g.is_alone_sale=1 and g.is_delete=0 limit 1";
		$res = $GLOBALS['db']->getRow($sql);

		if(empty($res))
		{
			$i = $i-1;
		}
		else
		{
			$goods[$i] = $res;
		}
	}
	return $goods;
}




/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:获得文章分类的标题
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_article_cat_cat_name($cat_id = 0)
{	
	$res = $GLOBALS['db']->GetOne("select cat_name from ecs_article_cat where cat_id=".$cat_id);
	return $res;
}
?>