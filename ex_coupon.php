<?php
/*=====================================================================积分兑换奖券 yijiangwen ===========================================================*/
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_clips.php');
require(ROOT_PATH . 'includes/lib_order.php');

/*===============================================积分兑换公共数据=======================================*/

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$position['title'] = "积分换券_积分商城_易视网";
$smarty->assign('page_title',          $position['title']);    
$smarty->assign('ur_here',             '<a href="./">首页</a> <code>></code> <a href="exchange.html">会员专区</a> <code>></code> 积分换券'); 
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
	$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
	$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
	$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
	$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
	$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
	$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

/*====================================积分两页 广告图======================================*/
$smarty->assign('zbbannertop',  ad_info(17,1));
$smarty->assign('ad_left_up',   ad_info(20,1));    
$smarty->assign('ad_left_dn',   ad_info(21,1));
/*====================================积分两页 广告图end===================================*/

/*===============================================积分兑换公共数据end====================================*/

//id参数
$coupon_id = isset($_REQUEST['coupon_id'])?intval($_REQUEST['coupon_id']):0;
if(empty($coupon_id)){
	show_message("此优惠券无效，请选择其他优惠券。");
	exit;
}

$sql    = "select * from ".$GLOBALS['ecs']->table('coupon')." where coupon_id=".$coupon_id;
$coupon = $GLOBALS['db']->GetRow($sql);

//获取该优惠券未售券的数量
$coupon_num = get_coupon_hava_num($coupon_id);

//更改时间格式
$coupon['end_date'] = "自兑换之日起至".date('Y年m月d日',$coupon['end_date']);


$smarty->assign('coupon',      $coupon);
$smarty->assign('coupon_num',  $coupon_num);
$smarty->display('ex_coupon.dwt');

/*=================================================================================【函数】===============================================================================*/

//取得优惠券的剩余数量
function get_coupon_hava_num($coupon_id){
	if($coupon_id == 0){	
		return false;
	}	
	$sql = "select count(*) from ".$GLOBALS['ecs']->table('coupon_list')." where coupon_id=".$coupon_id." and status=0 and user_id=0";
	$num = $GLOBALS['db']->GetOne($sql);
	return $num;
}

//获得积分兑券列表
function get_ex_coupon_list(){
	$sql = "select * from ".$GLOBALS['ecs']->table('coupon')." where 1=1 and end_date>".gmtime()." order by sort_order asc limit 0,20";
	$res = $GLOBALS['db']->GetAll($sql);
	return $res;
}

//获得用户获得积分战报列表
function get_integral_list($size = 50){
	$sql = "select u.user_name,a.* from ".$GLOBALS['ecs']->table('account_log')." AS a,".$GLOBALS['ecs']->table('users')." AS u where a.user_id=u.user_id AND a.user_id>0 AND a.pay_points>0		   AND a.change_type=99 AND a.change_desc<>'' AND (a.change_time + 30*24*3600) > UNIX_TIMESTAMP(NOW()) order by a.change_time DESC limit 0,".$size." ";
	$res = $GLOBALS['db']->GetAll($sql);
	return $res;
}


//yi:获得购物车中用户已经扣掉的积分。
function get_cart_integeral(){	
}

/**
 * 获得分类的信息
 * @param   integer $cat_id
 * @return  void
 */
function get_cat_info($cat_id)
{
    return $GLOBALS['db']->getRow('SELECT keywords, cat_desc, style, grade, filter_attr, parent_id FROM ' . $GLOBALS['ecs']->table('category') .
        " WHERE cat_id = '$cat_id'");
}

//yi: 热兑排行榜
function get_hot_sort(){
	$arr = array();
	$sql = "select g.goods_id, g.goods_name, g.goods_name_style, eg.exchange_integral, g.goods_type, g.goods_brief, g.goods_thumb , g.goods_img, eg.is_hot from "
	       .$GLOBALS['ecs']->table('exchange_goods')." AS eg, ".$GLOBALS['ecs']->table('goods')." AS g ".
		   " where g.is_on_sale = 1 and g.is_alone_sale =1 and g.is_delete = 0 and eg.goods_id = g.goods_id order by click_count desc limit 0,5";
	$arr = $GLOBALS['db']->GetAll($sql);
	return $arr;
}

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function exchange_get_goods($children, $min, $max, $ext, $size, $page, $sort, $order)
{
    $display = $GLOBALS['display'];
    $where = "eg.is_exchange = 1 AND g.is_delete = 0 AND ".
             "($children OR " . get_extension_goods($children) . ')';

    if ($min > 0)
    {
        $where .= " AND eg.exchange_integral >= $min ";
    }

    if ($max > 0)
    {
        $where .= " AND eg.exchange_integral <= $max ";
    }

    /* 获得商品列表 */
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, eg.exchange_integral, ' .
                'g.goods_type, g.goods_brief, g.goods_thumb , g.goods_img, eg.is_hot ' .
            'FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg, ' .$GLOBALS['ecs']->table('goods') . ' AS g ' .
            "WHERE eg.goods_id = g.goods_id AND $where $ext ORDER BY $sort $order";
			
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        /* 处理商品水印图片 */
        $watermark_img = '';
        if ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']          = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']    = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name']    = $row['goods_name'];
        }
        $arr[$row['goods_id']]['name']              = $row['goods_name'];
        $arr[$row['goods_id']]['goods_brief']       = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_style_name']  = add_style($row['goods_name'],$row['goods_name_style']);
        $arr[$row['goods_id']]['exchange_integral'] = $row['exchange_integral'];
        $arr[$row['goods_id']]['type']              = $row['goods_type'];
        $arr[$row['goods_id']]['goods_thumb']       = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']         = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']               = build_uri('exchange_goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    }
    return $arr;
}

/**
 * 获得分类下的商品总数
 *
 * @access  public
 * @param   string     $cat_id
 * @return  integer
 */
function get_exchange_goods_count($children, $min = 0, $max = 0, $ext='')
{
    $where  = "eg.is_exchange = 1 AND g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';


    if ($min > 0)
    {
        $where .= " AND eg.exchange_integral >= $min ";
    }

    if ($max > 0)
    {
        $where .= " AND eg.exchange_integral <= $max ";
    }

    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg, ' .
           $GLOBALS['ecs']->table('goods') . " AS g WHERE eg.goods_id = g.goods_id AND $where $ext";

    /* 返回商品总数 */
    return $GLOBALS['db']->getOne($sql);
}


/***
热门商品
***/
function get_hots_goods()
{
	$gmtimex=gmtime();
	$sql = "SELECT goods_id, goods_name, goods_sn, shop_price,goods_brief, is_on_sale, is_best,market_price, is_new,goods_thumb,goods_img,goods_name_style, is_hot, sort_order, goods_number, integral, " .
			" (promote_price > 0 AND promote_start_date <= '$gmtimex' AND promote_end_date >= '$gmtimex') AS is_promote ".
			" FROM " . $GLOBALS['ecs']->table('goods') . " AS g WHERE is_delete='0' AND is_real='1'" .
			" ORDER BY goods_id DESC ".
			" LIMIT 0,8";
	$res = $GLOBALS['db']->query($sql);
    $idx = 0;
    $goods = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $goods[$idx]['id']     = $row['goods_id'];
        $goods[$idx]['name']         = $row['goods_name'];
        $goods[$idx]['brief']        = $row['goods_brief'];

        $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                       sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $goods[$idx]['market_price'] = price_format($row['market_price']);
        $goods[$idx]['shop_price']   = price_format($row['shop_price']);
        $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

        $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
        $idx++;
    }
    return $goods;
}




/**
 * 获得指定分类下的推荐商品
 *
 * @access  public
 * @param   string      $type       推荐类型，可以是 best, new, hot, promote
 * @param   string      $cats       分类的ID
 * @param   integer     $min        商品积分下限
 * @param   integer     $max        商品积分上限
 * @param   string      $ext        商品扩展查询
 * @return  array
 */
function get_exchange_recommend_goods($type = '', $cats = '', $min =0,  $max = 0, $ext='')
{
    $price_where = ($min > 0) ? " AND g.shop_price >= $min " : '';
    $price_where .= ($max > 0) ? " AND g.shop_price <= $max " : '';

    $sql =  'SELECT g.goods_id, g.goods_name, g.goods_name_style, eg.exchange_integral, ' .
                'g.goods_brief, g.goods_thumb, g.goods_img, b.brand_name ' .
            'FROM ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = eg.goods_id ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
            'WHERE eg.is_exchange = 1 AND g.is_delete = 0 ' . $price_where . $ext;
    $num = 0;
    $type2lib = array('best'=>'exchange_best', 'new'=>'exchange_new', 'hot'=>'exchange_hot');
    $num = get_library_number($type2lib[$type], 'exchange_list');

    switch ($type)
    {
        case 'best':
            $sql .= ' AND eg.is_best = 1';
            break;
        case 'new':
            $sql .= ' AND eg.is_new = 1';
            break;
        case 'hot':
            $sql .= ' AND eg.is_hot = 1';
            break;
    }

    if (!empty($cats))
    {
        $sql .= " AND (" . $cats . " OR " . get_extension_goods($cats) .")";
    }
    $order_type = $GLOBALS['_CFG']['recommend_order'];
    $sql .= ($order_type == 0) ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
    $res = $GLOBALS['db']->selectLimit($sql, 7);

    $idx = 0;
    $goods = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $goods[$idx]['id']                = $row['goods_id'];
        $goods[$idx]['name']              = $row['goods_name'];
        $goods[$idx]['brief']             = $row['goods_brief'];
        $goods[$idx]['brand_name']        = $row['brand_name'];
        $goods[$idx]['short_name']        = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $goods[$idx]['exchange_integral'] = $row['exchange_integral'];
        $goods[$idx]['thumb']             = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $goods[$idx]['goods_img']         = get_image_path($row['goods_id'], $row['goods_img']);
        $goods[$idx]['url']               = build_uri('exchange_goods', array('gid' => $row['goods_id']), $row['goods_name']);

        $goods[$idx]['short_style_name']  = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
        $idx++;
    }
    return $goods;
}
/**
 * 获得积分兑换商品的详细信息
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function get_exchange_goods_info($goods_id)
{
    $time = gmtime();
    $sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, eg.exchange_integral, eg.is_exchange ' .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg ON g.goods_id = eg.goods_id ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' .
            "WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " .
            'GROUP BY g.goods_id';

    $row = $GLOBALS['db']->getRow($sql);

    if ($row !== false)
    {
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot';
        }

        if ($watermark_img != '')
        {
            $row['watermark_img'] =  $watermark_img;
        }

        /* 修正重量显示 */
		$row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
		$row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
		($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

        /* 修正上架时间显示 */
        $row['add_time']      = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

        /* 修正商品图片 */
        $row['goods_img']     = get_image_path($goods_id, $row['goods_img']);
        $row['original_img']  = get_image_path($goods_id, $row['original_img']);
        $row['goods_thumb']   = get_image_path($goods_id, $row['goods_thumb'], true);
        return $row;
    }
    else
    {
        return false;
    }
}

function index_get_new_articles_aboutt($cat_id)
{
    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time,a.content, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM ' . $GLOBALS['ecs']->table('article') . ' AS a, ' .
                $GLOBALS['ecs']->table('article_cat') . ' AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id and ac.cat_id='.$cat_id.' AND ac.cat_type = 1 and a.open_type<>1' .
            ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT 3' ;
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
		$arr[$idx]['content']          = $row['content'];
        $arr[$idx]['title']       = $row['title'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                                        sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         =build_uri('article', array('aid' => $row['article_id']), $row['title']) ;
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }
    return $arr;
}

/*-------随机抽取3张广告图片显示---------*/
function rand_adv(){
	//返回一个随机数字--作为图片路径
	return rand(1,3);
}

//yi:如何付款文章
function index_get_new_articles_aboutt2($cat_id)
{
    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time,a.content, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM ' . $GLOBALS['ecs']->table('article') . ' AS a, ' .
                $GLOBALS['ecs']->table('article_cat') . ' AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id and ac.cat_id='.$cat_id.' AND ac.cat_type = 3 and a.open_type<>1' .
            ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT 3' ;
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
		$arr[$idx]['content']          = $row['content'];
        $arr[$idx]['title']       = $row['title'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                                        sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         =build_uri('article', array('aid' => $row['article_id']), $row['title']) ;
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }
    return $arr;
}

//yi：当天网站总签到人数
function get_all_sign(){
	$sql = "select count(*) from ".$GLOBALS['ecs']->table('users')." where sign_sum>0 and last_sign >= UNIX_TIMESTAMP(CURRENT_DATE) and last_sign < (UNIX_TIMESTAMP(CURRENT_DATE)+86400)";
	$num = $GLOBALS['db']->GetOne($sql);
	return $num;
}

?>