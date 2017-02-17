<?php
/* -------------------------------------------------------------------------------------------------
 * 商城页面：老参数分类页面+搜索页面 【2012/4/25】【yijiangwen】
 * -------------------------------------------------------------------------------------------------
 */

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(dirname(__FILE__) . '/includes/pf_public.php');


//未注册用户是否跳回展示页(index_unck.dwt)
if(!index_unck_display($_SESSION['user_id'])){
    header("Location: user.html \n");
}
if((DEBUG_MODE & 2) != 2){ $smarty->caching = false;}

$keyword = $_GET['keyword'];

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$tit = '';
if(!empty($_GET['attr0']))
{
	$tit = strip_tags($_GET['attr0']);
}
elseif(!empty($_GET['attr1']))
{
	$tit = strip_tags($_GET['attr1']);
}
elseif(!empty($keyword))
{
	$tit = strip_tags($keyword);
}

/*------------------------------------页头 页尾 数据end------------------------------------*/

/*===========================================yi:左侧导航======================================*/
//所有品牌列表(左侧导航)
$smarty->assign('cat_all',     get_categories_treep()); 
$smarty->assign('cat3', 6); $smarty->assign('sale_order', sale_sort_list(6));
/*===========================================左侧导航end=======================================*/



//----------------------------------------获得url参数-------------------------------------//
$cat_id = isset($_GET['cat_id'])? intval($_GET['cat_id']): 0;
$brand  = isset($_GET['brand'])? intval($_GET['brand']): 0;
$attr0  = strip_tags($_GET['attr0']);
$attr1  = strip_tags($_GET['attr1']);
$attr2  = strip_tags($_GET['attr2']);
$attr3  = strip_tags($_GET['attr3']);
$attr4  = strip_tags($_GET['attr4']);
$attr5  = strip_tags($_GET['attr5']);
$sort	= ck_para_sort($_GET['sort']);
$order  = ($_GET['order'] == 'asc')? 'asc': 'desc';
if(!$sort){$sort="goods_id";}
if($keyword=='请输入关键字'){ $keyword=''; }

$smarty->assign('page_title',      $keyword.'_眼镜行业全方位服务提供商');
$smarty->assign('keywords',        $keyword.'_眼镜行业全方位服务提供商');
$smarty->assign('description',     $keyword.'_眼镜行业全方位服务提供商');

$smarty->assign('keyword', $keyword);
$smarty->assign('color_array',	get_color_attr());	//获取颜色属性

if(!$fattrnamestr){$fattrnamestr="眼镜搜索";}
$smarty->assign('fattrnamestr',    $fattrnamestr);

    $smarty->assign('zj_array',	array('13.40mm', '13.50mm', '13.80mm', '13.90mm', '14.00mm', '14.10mm', '14.20mm', '14.30mm', '14.40mm', '14.50mm'));
    $smarty->assign('jh_array',	array('8.30mm', '8.40mm', '8.50mm', '8.60mm', '8.70mm', '8.80mm', '8.90mm', '弹性基弧'));

  

	$smarty->assign('categoryjg',       get_jg()); // 价格参数树
	$smarty->assign('categories',       get_categories_tree($cat_id)); // 分类树
	$smarty->assign('top_goods',        get_top10());                  // 销售排行
	$smarty->assign('categoriesp',      get_categories_treep()); // 分类品牌树
	$smarty->assign('categoriescs',     get_categories_treecs()); // 分类参数树	
	$smarty->assign('categoriescsz',    get_categories_treecsz()); // 分类参数树	
	$smarty->assign('brand_listn',      get_brandsbot());

	//热门商品推荐
	$smarty->assign('hot_goods_rand',   get_hot_goods(4) );	
	$smarty->assign('hot_goods',        get_category_recommend_goodss('hot', '0', $brand, $price_min, $price_max, $ext));
	$smarty->assign('brand_id',         $brand);
	$smarty->assign('price_max',        $price_max);
	$smarty->assign('price_min',        $price_min);
	$smarty->assign('filter_attr',      $filter_attr_str);
    
    $smarty->assign('keyword',          $_GET['keyword']);
    $smarty->assign('sales_charts1',    get_sales_charts_all(0,3));  //热销排行榜1
    $smarty->assign('sales_charts2',    get_sales_charts_all(3,3));  //热销排行榜2
    $smarty->assign('sales_charts3',    get_sales_charts_all(6,3));  //热销排行榜3
    

	$smarty->display('categorysea.dwt');


//yi:商城所有商品的 最近一周(7天) 分类销售排行榜   参数：fcat_id：父分类id， num：排行商品个数。
function sale_sort_list( $fcat_id, $num = 5){
	
	//根据商品fcat_id 找出所有该父分类的分类id
	$sqlc   = "select cat_id from ".$GLOBALS['ecs']->table('category')." where parent_id = ".$fcat_id." and is_show = 1;";
	$cat_id = $GLOBALS['db']->GetAll($sqlc);
	
	$in = "(0";
	foreach($cat_id as $k => $v){
		if(!empty($cat_id[$k]['cat_id'])){
			$in .= ",".$cat_id[$k]['cat_id'];
		}
	}
	$in .= ")";
	
	//获取商品销售排行列表 查询耗时：0.0007秒
	$sql  = "select * from ecs_sales_charts where cat_id in".$in." limit 0,".$num.";";
	$sale = $GLOBALS['db']->GetAll($sql);
	return $sale;
}

//yi:随机获取4个热销商品 参数：size:随机获取热销商品的数量||
function get_hot_goods($size = 1){
	$rand_num = 0;
	if($size < 8){ $rand_num = rand(0,7);}else{$rand_num = rand(0,12-$size);}
	
	$hot_goods = array();
	$sql = "select goods_id,goods_name,shop_price,market_price,goods_thumb,goods_img,original_img from ".$GLOBALS['ecs']->table('goods')
		 ." where is_hot=1 and is_alone_sale=1 and is_on_sale=1 and is_delete=0 limit ".$rand_num.",".$size." ;";
	$res = $GLOBALS['db']->query($sql);
	while($row = $GLOBALS['db']->fetchRow($res)){
		$hot_goods[] = $row;
	}
	return $hot_goods;
}

function get_cagtegory_goods_count($id, $brand, $price_min, $price_max, $ext,$sql){
	return $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ('.$sql.') a ');
}

function category_get_goods($id, $brand, $price_min, $price_max, $ext, $size, $page, $sort, $order, $sql, $keyword='', $package_num=0)
{
	$display = $GLOBALS['display']; 

	//yi:添加礼包商品之后的分页控制
	$start = ($page-1)*$size;
	if($package_num>0)
	{
		$psize = ceil($package_num/$size);//礼包页数
		if($page==$psize)
		{
			$size  = $size - $package_num;			
		}
		else if($page<$psize)
		{
			$size = 0; $start = 0;
		}
		else
		{
			$start = $start - $package_num;
		}
	}
    $res = $GLOBALS['db']->selectLimit($sql, $size, $start);
    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0)
        {
            $watermark_img = "watermark_promote_small";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new_small";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best_small";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']         = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name']       = $row['goods_name'];
        }
        $arr[$row['goods_id']]['name']             = $row['goods_name'];
        $arr[$row['goods_id']]['goods_brief']      = $row['goods_brief'];
		$arr[$row['goods_id']]['goods_name_desc']  = $row['goods_name_desc'];
		$arr[$row['goods_id']]['click_count']      = $row['click_count'];

        $arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);
        $arr[$row['goods_id']]['market_price']     = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']       = ($promote_price > 0)? price_format($promote_price): price_format($row['shop_price']);
        $arr[$row['goods_id']]['type']             = $row['goods_type'];
        $arr[$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
		$arr[$row['goods_id']]['hv_gift']          = goods_hv_gift($row['goods_id']);//商品是否有赠品

		//yi获得商品：特价，新品，热销，促销。页面显示tip。
		if($row['is_promote']>0 && $row['promote_end_date']>time() && $row['promote_start_date']<time()){
			$arr[$row['goods_id']]['show_tip'] = 1;
		}else if($row['is_cx']>0){
			$arr[$row['goods_id']]['show_tip'] = 2;
		}else if($row['is_tj']>0 || $row['is_hot']>0){
			$arr[$row['goods_id']]['show_tip'] = 3;
		}else if($row['is_new']>0){
			$arr[$row['goods_id']]['show_tip'] = 4;
		}else{
			$arr[$row['goods_id']]['show_tip'] = 0;
		}
		//--------------------------------------------------------------------
    }
	if(!empty($keyword) && $package_num>0 && $page==$psize)
	{
		$package = get_packages_by_key($keyword);
		$arr     = array_merge($package, $arr);
	}
    return $arr;
}

function sqlcondtion($varstr){
	$strsqld=" select gd.goods_id from ". $GLOBALS['ecs']->table('goods') ." as gd,". $GLOBALS['ecs']->table('goods_attr') ." as ad where ad.goods_id=gd.goods_id and ad.attr_value = '".trim($varstr)."'  group by ad.goods_id ";
	$resp = $GLOBALS['db']->selectLimit($strsqld, 600000, 0);
	$k=0;
	while ($rowp = $GLOBALS['db']->fetchRow($resp))
	{
		if($k==0){
			$strsql=$rowp['goods_id'];
		}else{
			$strsql=$strsql.','.$rowp['goods_id'];
		}
		$k++;
	}	
	if($strsql){$strsql="and g.goods_id in(".$strsql.")";}else{$strsql="and g.goods_id in(0)";}
	return $strsql;
}

function fattrname($varstr){
	$attr_name="";
	$strsqld=" select attr_name from ". $GLOBALS['ecs']->table('attribute') ." where attr_values like '%".trim($varstr)."%'  ";
	$resp = $GLOBALS['db']->selectLimit($strsqld, 3, 0);
	$k=0;
	while ($rowp = $GLOBALS['db']->fetchRow($resp))
	{
		$attr_name=$rowp['attr_name'];
		$k++;
	}
	return $attr_name;
}

//yi:专门过滤关键字的方法
function sql_filter2($str = "")
{
	if(!empty($str))
	{
		$str = str_replace("</title>",   "", $str);
		$str = str_replace("alert(",     "", $str);

		$str = str_replace("'",     "", $str);
		$str = str_replace('\"',    "", $str);
		$str = str_replace("\\",    "", $str);	
		$str = str_replace("</",	"", $str);
		$str = str_replace("<",		"", $str);
		$str = str_replace(">",		"", $str);
		$str = str_replace("%20",   "", $str);
		$str = str_replace("script","", $str);
	}
	return strip_tags($str);
}

//yi:专门过滤参数的方法
function sql_para_filter($str = "")
{
	if(!empty($str))
	{
		$str = str_replace("</title>",   "", $str);
		$str = str_replace("alert(",     "", $str);
		$str = str_replace(")",     "", $str);
		$str = str_replace("'",     "", $str);
		$str = str_replace('\"',    "", $str);
		$str = str_replace("\\",    "", $str);	
		$str = str_replace("</",	"", $str);
		$str = str_replace("<",		"", $str);
		$str = str_replace(">",		"", $str);
		$str = str_replace("%20",   "", $str);
		$str = str_replace("%",		"", $str);
		$str = str_replace("script","", $str);
	}
	return strip_tags($str);
}

//yi:专门检测sort参数
function ck_para_sort($str='')
{
	if(!empty($str))
	{
		$sort= array('goods_id', 'shop_price', 'last_update', 'click_count', 'is_new');
		if(!in_array($str, $sort))
		{
			$str = "last_update";
		}
	}
	else
	{
		$str = "last_update";
	}
	return $str;
}
?>