<?php
/**
 * ajax:商品交叉检索结果列表页 ：【Author:xuyizhi】【TIME:2012/11/27】
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
if ((DEBUG_MODE & 2) != 2){ $smarty->caching = true;}

$smarty->caching = false;
/*=======================================参数================================================*/
//品牌:$brandx 周期:$attr0 颜色:$attr1 含水量:$attr2 直径:$attr3 基弧$attr4 最小价:格$price_mi 最大价格$price_max
$url = "";
foreach ($_GET as $key => $value) {
	if ($key == 'page') continue;
	$url = $url.$key.'='.$value.'&';
}
//echo $url.$page.'<br>';

$cat_ids = 0;//分类id

if (isset($_REQUEST['id'])){
    $cat_ids = intval($_REQUEST['id']);
} else {
	ecs_header("Location: ./\n");
	exit;
}

//获取url中的属性值
$brandx = $_GET['brandx'];
$attr0 = $_GET['attr0'];
$attr1 = $_GET['attr1'];
$attr2 = $_GET['attr2'];
$attr3 = $_GET['attr3'];
$attr4 = $_GET['attr4'];
$attr5 = $_GET['attr5'];
$price_max = isset($_REQUEST['price_max']) && intval($_REQUEST['price_max']) > 0 ? intval($_REQUEST['price_max']) : 0;
$price_min = isset($_REQUEST['price_min']) && intval($_REQUEST['price_min']) > 0 ? intval($_REQUEST['price_min']) : 0;
$order_by = isset($_GET['order_by']) ? $_GET['order_by']  : '';
$keyword= isset($_GET['keyword'])? trim($_GET['keyword']):'';//搜索关键字
/*=======================================参数================================================*/

//---分页数组--记录url中的属性---
$pager['search']['attr0'] = $attr0;
$pager['search']['attr1'] = $attr1;
$pager['search']['attr2'] = $attr2;
$pager['search']['attr3'] = $attr3;
$pager['search']['attr4'] = $attr4;
$pager['search']['attr5'] = $attr5;

$smarty->assign('cat_ids',  $cat_ids);

/*初始化分页信息*/
$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
//$size = isset($_CFG['page_size'])  && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 20;
$size = 20;



/*-------------------------------------------------------------------------程序----------------------------------------------------------------------*/
//if(!$smarty->is_cached('goods_list_ajax.dwt', $cache_id)){
    /*如果页面没有被缓存则重新获取页面的内容 */
	
	//---商品筛选的sql条件语句----
	$where = '';
	if(!empty($keyword)) {
		$where = ' is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 ';
		$keyword = trim($keyword);
		$key_arr = explode(' ', $keyword);
		if(count($key_arr)>=1) {
			foreach($key_arr as $k => $v) {
				if(!empty($key_arr[$k])) {
					$where .= " AND goods_name like '%".$key_arr[$k]."%' ";
				}
			}
		}
	} else {
		$where = ' is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 ';
	}
	
	if ($price_min == '0' && $price_max == '0') {
		//$where = ' is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 ';
	} else {
		if ($price_max == '0') {
			$where = ' is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 AND shop_price>=' . $price_min . ' ';
		} else {
			$where = ' is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 AND shop_price>=' . $price_min. ' AND shop_price<=' . $price_max . ' ';
		}
	}
	
	$sql = '';
	
	//父目录ID
	$sql_f = "SELECT parent_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_id=" . $cat_ids . " AND is_show=1 LIMIT 1";
	$f_id = $GLOBALS['db']->getOne($sql_f);

	//yi:修改交叉检索
	$attr_sql = "";
	if ($attr0) $attr_sql .= get_goods_id_from_attr($attr0);
	if ($attr1) $attr_sql .= get_goods_id_from_attr($attr1);
	if ($attr2) {
		if ($f_id == 1 || $f_id == 6) {
			$attr_sql .= get_goods_id_from_attr($attr2, 'hsl');
		} elseif ($f_id == 0 && ($cat_ids == 1 || $cat_ids == 6)) {
			$attr_sql .= get_goods_id_from_attr($attr2, 'hsl');
		} else {
			$attr_sql .= get_goods_id_from_attr($attr2);
		}
	}
	if ($attr3) $attr_sql .= get_goods_id_from_attr($attr3);
	if ($attr4) $attr_sql .= get_goods_id_from_attr($attr4);
	
	//未选择最终品牌子目录，则读取该父类下的所有子目录
	if ($brandx == '0') {
		if ($cat_ids == 0) {
			//未读取到栏目id
			$sql .= ' cat_id != 138 AND ' . $where;
		} else {
			$children_cat_array = array();
			$children_cat_str = '';
			
			$sql_cats = "SELECT cat_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE parent_id=" . $cat_ids . " AND is_show=1";
			$res_cats = $GLOBALS['db']->query($sql_cats);
			while($row = $GLOBALS['db']->fetchRow($res_cats)){
				$children_cat_array[] = $row['cat_id'];
			}
			$children_cat_str = implode(',', $children_cat_array);
			if ($children_cat_str) {
				$sql .= ' cat_id IN (' . $children_cat_str .') AND ' . $where;
			} else {
				//如果该目录没有子目录，则读取该目录的父目录下的所有子目录
				$sql_f_cat = "SELECT parent_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_id=" . $cat_ids . " AND is_show=1 LIMIT 1";
				$f_cat_id = $GLOBALS['db']->getOne($sql_f_cat); //父目录id
				
				$sql_cats = "SELECT cat_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE parent_id=" . $f_cat_id . " AND is_show=1";
				$res_cats = $GLOBALS['db']->query($sql_cats);				
				while($row = $GLOBALS['db']->fetchRow($res_cats)){
					$children_cat_array[] = $row['cat_id'];
				}
				$children_cat_str = implode(',', $children_cat_array);
				if ($children_cat_str) {
					$sql .= ' cat_id IN (' . $children_cat_str .') AND ' . $where;
				} else {
					$sql .= ' cat_id != 138 AND ' . $where;
				}
				
			}
		}
		$sql .= $attr_sql;		
	}
	else 
	{
		//选择了品牌(栏目),$brandx!=0
		$sql .= ' cat_id=' . $cat_ids .' AND ' . $where.$attr_sql;
	}
	
	
	//扩展分类(既属于其他栏目,也属于本栏目的产品)
	//$sql .= get_goods_id_from_cat($cat_ids);

	//yi:修改扩展分类的商品没有控制属性筛选。
	$sql   .= " or goods_id in( select distinct goods_id from ecs_goods_cat where cat_id=$cat_ids ".$attr_sql.") ";
	
	$sql_query = 'SELECT goods_id,goods_name,goods_brief,goods_name_desc,market_price,shop_price, promote_price, goods_thumb, click_count, is_new, is_best, is_hot, is_cx, is_promote, is_tj, promote_start_date, promote_end_date FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE ' . $sql;
	$sql_count = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' WHERE  ' . $sql;
	//echo $sql_count.'<br><br>';
	
	//获取总记录数
	$total_rows  = $GLOBALS['db']->getOne($sql_count);
	$package_num = get_packages_by_cat_num($cat_ids);
	$total_rows += $package_num;
	
	//总页数
	$total_pages = 1;
	if ($total_rows/$size > 1) {
		if (is_int($total_rows/$size)) $total_pages = $total_rows/$size;
		else $total_pages = intval($total_rows/$size) + 1;
	}

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
	
	//当前页
	if ($page > $total_pages) $page = $total_pages;
	
	//排序
	$order_sql = ' ORDER BY last_update DESC ';
	if ($order_by) $order_sql = ' ORDER BY ' . $order_by;
	$sql_query .= $order_sql.' LIMIT '.$start.', '.$size;
	
	//返回商品列表
	$goods_list = array();
	$res_search = $GLOBALS['db']->query($sql_query);
	while($row = $GLOBALS['db']->fetchRow($res_search)){
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }
		//$goods_list[] = $row;
		$goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];
		$goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
		$goods_list[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
		$goods_list[$row['goods_id']]['goods_name_desc'] = $row['goods_name_desc'];
		$goods_list[$row['goods_id']]['market_price'] = $row['market_price'];
		//$goods_list[$row['goods_id']]['shop_price'] = $row['shop_price'];
		$goods_list[$row['goods_id']]['shop_price'] = ($promote_price > 0)? $promote_price: $row['shop_price'];
		$goods_list[$row['goods_id']]['goods_thumb'] = $row['goods_thumb'];
		$goods_list[$row['goods_id']]['click_count'] = $row['click_count'];
		$goods_list[$row['goods_id']]['is_new'] = $row['is_new'];
		$goods_list[$row['goods_id']]['is_best'] = $row['is_best'];
		$goods_list[$row['goods_id']]['is_hot'] = $row['is_hot'];
		$goods_list[$row['goods_id']]['is_cx'] = $row['is_cx'];
		$goods_list[$row['goods_id']]['is_promote'] = $row['is_promote'];
		$goods_list[$row['goods_id']]['is_tj'] = $row['is_tj'];
		$goods_list[$row['goods_id']]['promote_start_date'] = $row['promote_start_date'];
		$goods_list[$row['goods_id']]['promote_end_date'] = $row['promote_end_date'];
		$goods_list[$row['goods_id']]['url']			  = 'goods'.$row['goods_id'].'.html';
		$goods_list[$row['goods_id']]['hv_gift']          = goods_hv_gift($row['goods_id']);//商品是否有赠品
		//yi获得商品：特价，热销，推荐。页面显示tip。
		if($row['is_promote']>0 && $row['promote_end_date']>$_SERVER['REQUEST_TIME'] && $_SERVER['REQUEST_TIME']>$row['promote_start_date']){
			$goods_list[$row['goods_id']]['show_tip'] = 1;
		}else if($row['is_cx']>0){
			$goods_list[$row['goods_id']]['show_tip'] = 2;
		}else if($row['is_tj']>0 || $row['is_hot']>0){
			$goods_list[$row['goods_id']]['show_tip'] = 3;
		}else if($row['is_new']>0){
			$goods_list[$row['goods_id']]['show_tip'] = 4;
		}else{
			$goods_list[$row['goods_id']]['show_tip'] = 0;
		}
	}
	
	if($cat_ids>0 && $package_num>0 && $page==$psize)
	{
		$package     = get_packages_by_cat($cat_ids);
		$goods_list  = array_merge($package, $goods_list);
	}
	
	$smarty->assign('goods_list',   $goods_list);
	$smarty->assign('total_rows',   $total_rows);
	$smarty->assign('total_pages',   $total_pages);
	$smarty->assign('page',   $page);
	$smarty->assign('url',   $url);
	if ($order_by == 'shop_price ASC') $smarty->assign('order_by',   'ASC');
	else $smarty->assign('order_by',   'DESC');
	
	//页面快速跳转
	$page_array = array();
	if ($total_pages > 9) {
		
		if (($page+4) <= $total_pages && ($page-4) > 0) {
			$page_array[0] = $page-4;
			$page_array[1] = $page-3;
			$page_array[2] = $page-2;
			$page_array[3] = $page-1;
			$page_array[4] = $page;
			$page_array[5] = $page+1;
			$page_array[6] = $page+2;
			$page_array[7] = $page+3;
			$page_array[8] = $page+4;
		} else if (($page+4) > $total_pages) {
			$page_array[0] = $total_pages-8;
			$page_array[1] = $total_pages-7;
			$page_array[2] = $total_pages-6;
			$page_array[3] = $total_pages-5;
			$page_array[4] = $total_pages-4;
			$page_array[5] = $total_pages-3;
			$page_array[6] = $total_pages-2;
			$page_array[7] = $total_pages-1;
			$page_array[8] = $total_pages;
		} else if (($page-4) <= 0) {
			$page_array[0] = 1;
			$page_array[1] = 2;
			$page_array[2] = 3;
			$page_array[3] = 4;
			$page_array[4] = 5;
			$page_array[5] = 6;
			$page_array[6] = 7;
			$page_array[7] = 8;
			$page_array[8] = 9;
		}
		
	} else {
		for ($i=1; $i<=$total_pages; $i++) {
			$page_array[$i] = $i;
		}
	}
	$smarty->assign('page_array',   $page_array);

//}//if 缓存

	$smarty->display('goods_list_ajax.dwt', $cache_id);
	
	
	
//根据选择参数从ecs_goods_attr表中查询适应该概述的goods_id
function get_goods_id_from_attr($attr_value, $str='') {
	$goods_ids = '';
	$temp_array = array();
	
	$temp_sql = '';
	if ($str == 'hsl') {
		//含水量属性
		if ($attr_value == '1') {
			//37%以下
			$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='24%' OR attr_value='33%' OR attr_value='36%'";
		} elseif ($attr_value == '2') {
			$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='38%' OR attr_value='39%' OR attr_value='40%' OR attr_value='42%' OR attr_value='45%' OR attr_value='47%' OR attr_value='48%'";
		} elseif ($attr_value == '3') {
			$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='52%' OR attr_value='55%' OR attr_value='58%'";
		} elseif ($attr_value == '4') {
			$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='59%' OR attr_value='60%' OR attr_value='66%' OR attr_value='69%'";
		}
	} else {
		$temp_sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value="' . $attr_value . '"';
	}
	
	$res_temp = $GLOBALS['db']->query($temp_sql);
	while($row = $GLOBALS['db']->fetchRow($res_temp)){
		$temp_array[] = $row['goods_id'];
	}
	if ($temp_array) {
		$goods_ids = ' AND goods_id IN ('.implode(',', $temp_array) . ') ';
	} else {
		$goods_ids = ' AND goods_id IN (0) ';
	}
	
	return $goods_ids;
}

//获得所有扩展分类属于本栏目的所有商品ID
function get_goods_id_from_cat($cat_ids) {
    $goods_ids = '';
	$temp_cat_ids = $cat_ids;
	$temp_array = array();
	if ($cat_ids == 12) {
		//读取所有功能隐形眼镜
		$temp_cat_ids = implode(',', get_special_cat_ids());;
	}
    //$sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods_cat') . ' WHERE cat_id IN (' . $temp_cat_ids . ')';
    $sql = 'SELECT a.goods_id FROM ' . $GLOBALS['ecs']->table('goods_cat') . ' a LEFT JOIN ecs_goods b ON a.goods_id=b.goods_id WHERE a.cat_id IN (' . $temp_cat_ids . ') AND b.is_on_sale=1 AND b.is_alone_sale=1 AND b.is_delete=0';
    $res_temp = $GLOBALS['db']->query($sql);
	while($row = $GLOBALS['db']->fetchRow($res_temp)){
		$temp_array[] = $row['goods_id'];
	}
	if ($temp_array) $goods_ids = ' OR goods_id IN ('.implode(',', $temp_array) . ') ';
	
	return $goods_ids;
}

//获取功能性眼镜的分类ID
function get_special_cat_ids() {
	$children_cat_array = array();
	$sql_cats = "SELECT cat_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE parent_id=12 AND is_show=1";
	$res_cats = $GLOBALS['db']->query($sql_cats);
	
	while($row = $GLOBALS['db']->fetchRow($res_cats)){
		$children_cat_array[] = $row['cat_id'];
	}
	
	return $children_cat_array;
}
