<?php
/* =======================================================================================================================
 * 商城页面 具体文章页面（包括3类文章）【Author:yijiangwen】【TIME:2012/10/22】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$article_id     = $_REQUEST['id'];
if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] < 0)
{
    $article_id = $db->getOne("SELECT article_id FROM " . $ecs->table('article') . " WHERE cat_id = '".intval($_REQUEST['cat_id'])."' ");
}
$cache_id = sprintf('%X', crc32($_REQUEST['id'] . '-' . $_CFG['lang']));


/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',          $position['title']);    
$smarty->assign('ur_here',             $position['ur_here']);  
$smarty->assign('topbanner',           ad_info_by_time(31,1));            //头部横幅广告
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
//	$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
//	$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
//	$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
//	$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
//	$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
//	$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('article_categories',     article_categories_treezb(12));//文章分类树

if(!$smarty->is_cached('article.dwt', $cache_id))
{    
    $article = get_article_info($article_id);//文章详情（全部信息）
    if(empty($article))
    {
        ecs_header("Location: ./\n");
        exit;
    }
    if(!empty($article['link']) && $article['link'] != 'http://' && $article['link'] != 'https://')
    {
        ecs_header("location:$article[link]\n");
        exit;
    }

/*
    $smarty->assign('top_goods',        get_top10());    // 销售排行
    $smarty->assign('best_goods',       get_recommend_goods('best'));       // 推荐商品
    $smarty->assign('new_goods',        get_recommend_goods('new'));        // 最新商品
    $smarty->assign('hot_goods',        get_recommend_goods('hot'));        // 热点文章
    $smarty->assign('promotion_goods',  get_promote_goods());    // 特价商品
    $smarty->assign('related_goods',    article_related_goods($_REQUEST['id']));  // 特价商品
	$smarty->assign('categoriesp',     get_categories_treep()); // 分类品牌树
    $smarty->assign('username',         $_SESSION['user_name']);
    $smarty->assign('email',            $_SESSION['email']);
    $smarty->assign('promotion_info', get_promotion_info());
	$smarty->assign('brand_listn',      get_brandsbot());


    //验证码相关设置
    if((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }*/


/*
    $catlist = array();
    foreach(get_article_parent_cats($article['cat_id']) as $k=>$v)
    {
        $catlist[] = $v['cat_id'];
    }
    assign_template('a', $catlist);//未知
*/

    //文章相关商品
	/*
    $sql = "SELECT a.goods_id, g.goods_name " .
            "FROM " . $ecs->table('goods_article') . " AS a, " . $ecs->table('goods') . " AS g " .
            "WHERE a.goods_id = g.goods_id " .
            "AND a.article_id = '$_REQUEST[id]' ";
    $smarty->assign('goods_list', $db->getAll($sql));
	*/

    //上一篇下一篇文章
    $next_article = $db->getRow("SELECT article_id, title FROM " .$ecs->table('article'). " WHERE article_id > $article_id AND cat_id=$article[cat_id] AND is_open=1 LIMIT 1");
    if(!empty($next_article))
    {
        $next_article['url'] = build_uri('article', array('aid'=>$next_article['article_id']), $next_article['title']);
        $smarty->assign('next_article', $next_article);
    }

    $prev_aid = $db->getOne("SELECT max(article_id) FROM " . $ecs->table('article') . " WHERE article_id < $article_id AND cat_id=$article[cat_id] AND is_open=1");
    if(!empty($prev_aid))
    {
        $prev_article = $db->getRow("SELECT article_id, title FROM " .$ecs->table('article'). " WHERE article_id = $prev_aid");
        $prev_article['url'] = build_uri('article', array('aid'=>$prev_article['article_id']), $prev_article['title']);
        $smarty->assign('prev_article', $prev_article);
    }

	$smarty->assign('attitle',			$article['title']);     //yi:页头中页面标题
    $smarty->assign('id',               $article_id);
    $smarty->assign('type',             '1');//未知
    $smarty->assign('comment_type',     1);  //评论类型：文章

	$art_cat_name = get_article_fcat_name($article['cat_id']); //文章父类名字

    $position     = assign_ur_here($article['cat_id'], $article['title']);
	$page_title   = $article['title'].'-'.$art_cat_name.'-'.'易视网';

    $smarty->assign('page_title',   $page_title);          //页面标题
    $smarty->assign('ur_here',      $position['ur_here']); //当前位置

    $smarty->assign('article',      $article);
    $smarty->assign('keywords',     htmlspecialchars($article['keywords']));
    $smarty->assign('description',  htmlspecialchars($article['description']));

	//这个文章的关键词
	$at_key_lab = array();
	if(!empty($article['key_lab']))
	{
		$sql = "select * from ".$ecs->table('key_lab')." where rec_id in(".$article['key_lab'].") ";
		$at_key_lab = $db->GetAll($sql);
	}	
	$smarty->assign('at_key_lab',  $at_key_lab);

    //assign_dynamic('article');
}

//yi:具体文章页面分类加载模板
if(isset($article) && $article['cat_id'] >= 2 && $article['cat_id'] < 12)
{
	if(isset($article) && $article['cat_id']>2)
	{
		//帮助中心，购物指南页面【cat_id>2 cat_id<12】
		$smarty->display('article_help.dwt', $cache_id);
	}
	else
	{
		//网站信息页面【cat_id==2】
		$smarty->display('article_site_info.dwt', $cache_id);
	}
}
else
{
	//(具体的文章页面)【cat_id >= 12】
	$smarty->assign('ad_line1_r1',     ad_info(52,1));            //首页右上
	$smarty->assign('qianggou',        get_qianggou_img());       //限时抢购图片
	$smarty->assign('latest_promote',  get_latest_promote());     //限时抢购

	$smarty->assign('link_goods',      get_article_link_goods(4));//关联商品(随机)
	$smarty->assign('sales_charts1',    get_sales_charts_all(0,3));  //热销排行榜1
    $smarty->assign('sales_charts2',    get_sales_charts_all(3,3));  //热销排行榜2
    $smarty->assign('sales_charts3',    get_sales_charts_all(6,3));  //热销排行榜3
	$smarty->assign('article_top',     yi_get_article_list(10));  //文章排行榜

	$smarty->display('article.dwt', $cache_id);
}



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
 * 函数 yi:热门文章排行榜（资讯文章）
 * ----------------------------------------------------------------------------------------------------------------------
 */
function yi_get_article_list($num = 5)
{
	$sql = "select * from ecs_article where cat_id=14 order by add_time desc limit 0,".$num;
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}



//yi:限时抢购内容
function get_qianggou_img()
{
    $now = gmtime();
    $sql = "SELECT goods_id, promote_img FROM " . $GLOBALS['ecs']->table('goods') . " WHERE  1=is_promote" .
            " AND is_on_sale = 1" .
            " AND promote_start_date <= '$now'" .
            " AND promote_end_date >= '$now'" .
            " AND is_delete = 0" .
            " ORDER BY promote_start_date DESC LIMIT 1";
    $res = $GLOBALS['db']->getRow($sql);

/*
    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res)){          
		$arr['thumb']       = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$arr['goods_img']   = get_image_path($row['goods_id'], $row['goods_img'], true);
		$arr['promote_img'] = get_image_path($row['goods_id'], $row['promote_img'], true);
		$arr['formated_market_price']  = price_format($row['market_price']);
		$arr['shop_price']  = price_format($row['shop_price']);		
		$arr['save_price']    = $row['shop_price']-$row['promote_price'];//节省
		$arr['url']           = "goods". $row['goods_id'].".html";
		$arr['promote_price'] = $row['promote_price'];
		$arr['short_name']    = $row['goods_name'];
		$arr['start_time']    = $row['start_time'];
		$arr['end_time']      = $row['end_time'];
        $list[] = $arr;
    }

*/
    return $res;
}

//获得文章的父类名字
function get_article_fcat_name($cat_id){
	$sql = "select cat_name from ".$GLOBALS['ecs']->table('article_cat')." where cat_id = ".$cat_id.";";
	$cat_name = $GLOBALS['db']->GetOne($sql);
	return $cat_name;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数: 获得指定文章的详细信息
 * ----------------------------------------------------------------------------------------------------------------------
 * return array;
 */
function get_article_info($article_id = 0)
{
    $sql = "SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ".
            "FROM " .$GLOBALS['ecs']->table('article'). " AS a ".
            "LEFT JOIN " .$GLOBALS['ecs']->table('comment'). " AS r ON r.id_value = a.article_id AND comment_type = 1 ".
            "WHERE a.is_open = 1 AND a.article_id = '$article_id' GROUP BY a.article_id";
    $row = $GLOBALS['db']->getRow($sql);

    if($row !== false)
    {
        $row['comment_rank'] = ceil($row['comment_rank']);                                    //用户评论级别
        $row['add_time']     = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']); //修正添加时间显示

        //作者信息如果为空，则用网站名称替换
        if(empty($row['author']) || $row['author'] == '_SHOPHELP')
        {
            $row['author'] = $GLOBALS['_CFG']['shop_name'];
        }
    }
    return $row;
}

/**
 * 获得文章关联的商品
 *
 * @access  public
 * @param   integer $id
 * @return  array
 */
function article_related_goods($id)
{
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
                'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' .
            'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' ga ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            "WHERE ga.article_id = '$id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
        $arr[$row['goods_id']]['goods_name']    = $row['goods_name'];
        $arr[$row['goods_id']]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
            sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
        $arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

        if ($row['promote_price'] > 0)
        {
            $arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
        }
        else
        {
            $arr[$row['goods_id']]['promote_price'] = 0;
        }
    }
    return $arr;
}
?>