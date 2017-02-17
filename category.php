<?php
/* =======================================================================================================================
 * 商城页面 品牌分类 彩色片 透明片 护理液 护理工具栏目页面 2015-7-6[Author:zhuwentao]
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2){ $smarty->caching = true;}
//ini_set("display_errors", "On");
//error_reporting(E_ALL);

$user_id = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;


//未注册用户是否跳回展示页(index_unck.dwt)
if(!index_unck_display($user_id)){
    header("Location: user.html \n");
}

//print_r($_REQUEST['id']);die;

$cat_id = intval($_REQUEST['id']); //当前栏目ID
$parent_id = get_parent_category_id($cat_id); //父栏目ID

if(!$cat_id){
    header("Status: 404 Not Found"); header("Location: 404.html \n"); exit(0);
}


if($cat_id == 159 || $cat_id == 190 || $cat_id == 255 || $parent_id == 159 || $parent_id == 190 || $parent_id == 255){
    show_message('此类目暂未开启,我们将尽快开通谢谢！');
}


/*页面的缓存ID*/
$cache_id = sprintf('%X', crc32($cat_id . '-' . $display . '-' . $sort  .'-' . $order  .'-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' .$_CFG['lang'] .'-'. $brandx. '-' . $price_max . '-' .$price_min . '-'.$attr0 . '-'.$attr1 . '-'.$attr2 . '-'.$attr3 . '-'.$attr4 . '-' .$attr5 . '-' . $filter_attr_str));


if(!$smarty->is_cached('category.dwt', $cache_id)){
    
    
    //当前目录,父目录信息
	$current_category = get_category_info_by_id($cat_id);
	$parent_category = get_category_info_by_id($parent_id);
	$subdirectory = get_subdirectory($parent_id); //子目录id和名称数组

    //二级页面广告位1
    $smarty->assign('ad_B1',			ad_info_by_time(118,1));
    //二级页面广告位2
    $smarty->assign('ad_B2',			ad_info_by_time(119,1));
    
    $smarty->assign('page_title',      $current_category['cat_name'].'_眼镜行业全方位服务提供商');
    $smarty->assign('keywords',        $current_category['cat_name'].'_眼镜行业全方位服务提供商');
    $smarty->assign('description',     $current_category['cat_name'].'_眼镜行业全方位服务提供商');
    
	$smarty->assign('cat_id',			$cat_id);
	$smarty->assign('parent_id',		$parent_id);
	$smarty->assign('subdirectory',		$subdirectory);
	$smarty->assign('current_category',	$current_category);
	$smarty->assign('parent_category',	$parent_category);
	
	if ($parent_id == 6 OR $parent_id == 159 OR $parent_id == 190)
	{
		$smarty->assign('color_array',	get_color_attr($parent_id));	//获取颜色属性
		$smarty->assign('cz_array',		get_cz_attr($parent_id));		//获取材质属性
	}
	if ($parent_id == 1 OR $parent_id == 6)
	{
		$smarty->assign('zj_array',	array('13.40mm', '13.50mm', '13.80mm', '13.90mm', '14.00mm', '14.10mm', '14.20mm', '14.30mm', '14.40mm', '14.50mm'));
		$smarty->assign('jh_array',	array('8.30mm', '8.40mm', '8.50mm', '8.60mm', '8.70mm', '8.80mm', '8.90mm', '弹性基弧'));
	}
    
    $smarty->assign('sales_charts1',    get_sales_charts_all(0,3));  //热销排行榜1
    $smarty->assign('sales_charts2',    get_sales_charts_all(3,3));  //热销排行榜2
    $smarty->assign('sales_charts3',    get_sales_charts_all(6,3));  //热销排行榜3
    //商品精选
    $smarty->assign('spjx',  		b2b_sale_sort_list($parent_id,15) );
}

	$smarty->display('category.dwt', $cache_id);




/**
 * 获取父目录ID
 * @param int $cat_id
 */
function get_parent_category_id($cat_id=0)
{
	if( ! empty($cat_id))
	{
		$p_id = $GLOBALS['db']->GetOne("SELECT parent_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_id = ".intval($cat_id)." LIMIT 1");
		if (empty($p_id)) return $cat_id;
		else return $p_id;
	}
	else 
	{
		return 0;
	}
}


/**
 * 根据栏目ID获取栏目信息
 * @param int $cat_id
 */
function get_category_info_by_id($cat_id=0)
{
	if( ! empty($cat_id))
	{
		return $GLOBALS['db']->GetRow("SELECT * FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_id = ".intval($cat_id)." LIMIT 1");
	}
}

/**
 * 根据父目录ID返回子目录ID和名称数组
 * @param int $parent_id
 */
function get_subdirectory($parent_id=1)
{
	if ( ! empty($parent_id))
	{
		return $GLOBALS['db']->GetAll("SELECT cat_id, cat_name FROM " . $GLOBALS['ecs']->table('category') . " WHERE parent_id=" . $parent_id . " AND is_show=1");
	}
}


/**
 * 根据大类ID获取颜色属性值
 * @param int $parent_id
 */
function get_color_attr($parent_id=6)
{
	if ( ! empty($parent_id))
	{
		$sql = '';
		if ($parent_id == 6)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 212";
		}
		elseif ($parent_id == 159)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 248";
		}
		elseif ($parent_id == 190)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 262";
		}
		
		if ( ! empty($sql))
		{
			$str = $GLOBALS['db']->GetOne($sql);
			return explode("\n", $str);
		}
	}
}


/**
 * 根据大类ID获取材质属性值
 * @param int $parent_id
 */
function get_cz_attr($parent_id=159)
{
	if ( ! empty($parent_id))
	{
		$sql = '';
		if ($parent_id == 159)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 247";
		}
		elseif ($parent_id == 190)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 261";
		}
		
		if ( ! empty($sql))
		{
			$str = $GLOBALS['db']->GetOne($sql);
			return explode("\n", $str);
		}
	}
}

?>