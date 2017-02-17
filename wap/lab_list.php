<?php
/* =======================================================================================================================
 * 商城 商品标签页面 【2013/2/17】【Author:yijiangwen】
 * =======================================================================================================================
 */
  header('Location: http://www.easeeyes.com/');
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
require(ROOT_PATH.'includes/lib_order.php');

$action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$lab_id = isset($_REQUEST['lab_id']) ? intval($_REQUEST['lab_id']) : 0;
if($lab_id == 0)
{
	ecs_header("Location: ./\n"); exit;
}

$lab_info = get_lab_list_info($lab_id);
if($lab_info === false)
{
	ecs_header("Location: ./\n"); exit;
}

$tit = '';
switch($lab_info['cat_belong'])
{
	case 1:
		$tit = '透明隐形眼镜'; $cat_f_id = 1;
		break;
	case 6:
		$tit = '彩色隐形眼镜'; $cat_f_id = 6;
		break;
	case 64:
		$tit = '护理液润眼液'; $cat_f_id = 64;
		break;
	case 76:
		$tit = '功能隐形眼镜'; $cat_f_id = 76;
		break;
	case 159:
		$tit = '框架眼镜';     $cat_f_id = 159;
		break;
	case 190:
		$tit = '太阳眼镜';     $cat_f_id = 190;
		break;
	default:
		$tit = '隐形眼镜';     $cat_f_id = 6;
}
$cat_f_id = empty($cat_f_id)? 6: $cat_f_id;

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('column',               get_column() ); //栏目导航
$smarty->assign('page_title',          $lab_info['lab_name']."_".$tit."_易视网");     
$smarty->assign('ur_here',             '<a href="./">首页</a> <code>></code> <a href="category_'.$cat_f_id.'.html">'.$tit."</a> <code>></code> ".$lab_info['lab_name']); 
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
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/
$smarty->assign('ad_hot_list',          ad_info(51,1));//焦点图片广告
$smarty->assign('show_marketprice',     1);
$smarty->assign('cat_all',			    get_categories_treep());  //左侧导航分类

//标签归属商品分类，和热销排行商品
$fcat = get_lab_list_cat($lab_id);
switch($fcat)
{
	case 1:
		$smarty->assign('cat2', $fcat);
		$smarty->assign('sale_order',  sale_sort_list(1));
		break;
	case 6:
		$smarty->assign('cat3', $fcat);
		$smarty->assign('sale_order',  sale_sort_list(6));
		break;
	case 64:
		$smarty->assign('cat4', $fcat); 
		$smarty->assign('sale_order',  sale_sort_list(64));
		break;
	case 76:
		$smarty->assign('cat5', $fcat);
		$smarty->assign('sale_order',  sale_sort_list(76));
		break;
	case 159:
		$smarty->assign('cat6', $fcat);
		$smarty->assign('sale_order',  sale_sort_list(159));
		break;
	case 190:
		$smarty->assign('cat7', $fcat);	$smarty->assign('sale_order',  sale_sort_list(190)); break;
	default:
		$lab_cat_arr = array(62,63,64,65,66);
		if(in_array($lab_id, $lab_cat_arr))
		{
			$smarty->assign('cat6', 159);
			$smarty->assign('sale_order',  sale_sort_list(159));
			$smarty->assign('gn_cat', true);
		}
		else
		{
			$smarty->assign('cat3', 6);
			$smarty->assign('sale_order',  sale_sort_list(6));
		}
		break;		
}

if(in_array($lab_id, array(30, 31, 32, 34, 35, 43)))
{
	$smarty->assign('lab_img', true);
	$smarty->assign('lab_id',  $lab_id);
}

if('default' == $action)
{
	/*=======================================商品列表 分页================================================*/
	$sort  = (isset($_REQUEST['sort'])&& in_array( trim(strtolower($_REQUEST['sort'])), array('goods_id','shop_price','last_update','click_count')) )? trim($_REQUEST['sort']): '';
	$order = (isset($_REQUEST['order'])&& in_array(trim(strtoupper($_REQUEST['order'])),array('ASC','DESC'))) ? trim($_REQUEST['order']) : '';
	$page  = (isset($_REQUEST['page']) && !empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))? intval($_REQUEST['page']) : 1;

	$pager = array();
	$size  = 20;
	$con   = get_lab_list_num($lab_id);
	$page_count            = ceil($con/$size);
	$pager['size']         = $size;
	$pager['record_count'] = $con;
	$pager['page_count']   = $page_count;
	$pager['page']         = $page; 
	$pager['page_prev']    = ($pager['page']>1)? $pager['page']-1 : ''; 
	$pager['page_next']    = ($pager['page']<$page_count)? $pager['page']+1: '';
	$pager['page_number']  = array();
	for($i=1; $i<=$page_count; ++$i)
	{
		$pager['page_number'][$i] = $i;
	}

	//页面参数（ 分页 排序）。
	$url_arr = explode("&", $_SERVER['argv'][0]);
	if(!empty($sort) && !empty($order) && count($url_arr)>2){
		$page_url = $_SERVER['PHP_SELF']."?lab_id=".$lab_id."&sort=".$sort."&order=".$order;
	}else{
		$page_url = $_SERVER['PHP_SELF']."?lab_id=".$lab_id;
	}
	//------------------------------//
	$smarty->assign('pager',				$pager);

	$smarty->assign('record_count',			$con);
	$smarty->assign('page_url',             $page_url);
	$smarty->assign('page_url_sort',        $_SERVER['PHP_SELF']."?lab_id=".$lab_id);//lab_list.php?lab_id=14&sort=goods_id&order=desc
	$smarty->assign('lab_goods_order',      $order);
	/*=======================================商品列表 分页end=============================================*/

	$lab_goods = get_lab_goods_list($lab_id, $page, $size, $sort, $order);

	if(!empty($lab_goods)){
		$smarty->assign('goods_list', $lab_goods);
	}else{
		ecs_header("Location: ./\n"); exit;
	}


	$lab_arr = array(31,34,56, 57, 58, 59, 60, 67, 68, 70, 85, 86, 87,88,89,90,91,93,94,95,96,97,99,100,101,102,103,104,105,106,109,107,108,110,111,113,112,114,115,116,155,157,141);//有大广告活动图片的标签页
	if(in_array($lab_id, $lab_arr))
	{
		$smarty->assign('lab_at_id',     $lab_id);
		$smarty->display('lab_active.dwt');
	}
	else
	{
		$smarty->display('hot_list.dwt'); //普通标签，与热门系列共用模板。
	}

}
elseif('pages'== $action)
{
	$page     = isset($_REQUEST['page'])? intval($_REQUEST['page']): 1;
	$size     = isset($_REQUEST['size'])? intval($_REQUEST['size']): 20;
	$count    = isset($_REQUEST['count'])? intval($_REQUEST['count']): 1;

	$pager    = get_pager('user.php', array('act' => $action), $count, $page, $size);    
	$smarty->assign('pager',        $pager);
	$smarty->assign('list',			get_user_msg_list($user_id,  $page, $size));  //全部消息
	$smarty->display('pap_user_msg_list.dwt');
}
else
{}


/*====================================================================================【函数】=========================================================================*/
//yi:根据标签id找出它属于商品板块
function get_lab_list_cat($lab_id){
	$sql = "select f.cat_belong from ".$GLOBALS['ecs']->table('lab')." as l,".$GLOBALS['ecs']->table('lab_cat')." as f where l.lab_cat=f.cat_id AND lab_id=".$lab_id.";";
	return $GLOBALS['db']->GetOne($sql);
}

//yi:标签详细信息
function get_lab_list_info($lab_id){
	$sql = "select f.cat_belong,l.* from ".$GLOBALS['ecs']->table('lab')." as l,".$GLOBALS['ecs']->table('lab_cat')." as f where l.is_show=1 AND l.lab_cat=f.cat_id AND lab_id=".$lab_id.";";
	$row = $GLOBALS['db']->GetRow($sql);
	if($row !== false){
		return $row;
	}else{
		return false;
	}
}

//yi:商城所有商品的分类销售排行列表（可以根据分类 算出分类的销售排行） 最近一周(7天)销售排行榜
//fcat_id：父分类id。 num：排行商品个数。
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
	
	//获取商城销售排行列表 耗时：0.0007秒
	$sql  = "select * from ecs_sales_charts where cat_id in".$in." limit 0,".$num.";";
	$sale = $GLOBALS['db']->GetAll($sql);
	return $sale;
}

//YI:标签商品  $lab_id：id $page:当前页码 $sort排序字段 $order排序方式
function get_lab_goods_list($lab_id, $page=1, $size=20, $sort='', $order='')
{
	$lab_goods = $GLOBALS['db']->GetOne("select lab_goods from ".$GLOBALS['ecs']->table('lab')." where is_show=1 AND lab_id=".$lab_id." limit 1;");
	$lab_goods = empty($lab_goods)? '0': trim($lab_goods);
	$sql_in = "(".$lab_goods.")";

	if(!empty($sort) && !empty($order))
	{
		$sql_order = " ORDER BY ".$sort." ".$order." ";
	}
	else
	{ 
		$sql_order = " ORDER BY sort_order asc,goods_id desc "; 
	}

	$arr = array();
	$sql =  "SELECT g.is_on_sale,g.cat_id,g.goods_id, g.goods_name,g.goods_name_desc, g.click_count, g.market_price, g.is_new, g.is_best, g.is_hot, g.is_promote, g.is_tj, g.is_cx, g.shop_price AS org_price, ".
            "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, " .
            "g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img " .
            "FROM ".$GLOBALS['ecs']->table('goods')." AS g LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.goods_id in".$sql_in.$sql_order;
	$star = ($page-1)*$size;
	$res  = $GLOBALS['db']->selectLimit($sql, $size, $star);

    while($row = $GLOBALS['db']->fetchRow($res))
    {
        if($row['is_on_sale'] == 1 && $row['cat_id'] != 138){
            if($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            }
            else
            {
                $promote_price = 0;
            }
    
            $arr[$row['goods_id']]['goods_id']       = $row['goods_id'];
            if($display == 'grid')
            {
                $arr[$row['goods_id']]['goods_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            }
            else
            {
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            }
            $arr[$row['goods_id']]['name']             = $row['goods_name'];
    		$arr[$row['goods_id']]['goods_name_desc']  = $row['goods_name_desc'];
            $arr[$row['goods_id']]['goods_brief']      = $row['goods_brief'];
    		$arr[$row['goods_id']]['click_count']      = $row['click_count'];
            $arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);
            $arr[$row['goods_id']]['market_price']     = price_format($row['market_price']);
            $arr[$row['goods_id']]['shop_price']       = price_format($row['shop_price']);
            $arr[$row['goods_id']]['type']             = $row['goods_type'];
            $arr[$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';
            $arr[$row['goods_id']]['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $arr[$row['goods_id']]['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
            //$arr[$row['goods_id']]['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    		$arr[$row['goods_id']]['url']              = 'goods'.$row['goods_id'].'.html';
    		$arr[$row['goods_id']]['hv_gift']          = goods_hv_gift($row['goods_id']);//商品是否有赠品
    
    		//yi:商品小标：1,特价，2,促销，3,推荐&热销 4,新品，0,没有小标。
    		if($row['is_promote']>0 && $row['promote_end_date']>$_SERVER['REQUEST_TIME'] && $_SERVER['REQUEST_TIME']>$row['promote_start_date']){
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
    		//--------------------------------商品贴标记结束------------------------------------//
        }
    }
    return $arr;
}

//yi: 总lab记录数
function get_lab_list_num($lab_id=0)
{
	$lab_goods = $GLOBALS['db']->GetOne("select lab_goods from ".$GLOBALS['ecs']->table('lab')." where is_show=1 AND lab_id=".$lab_id." limit 1;");
	$lab_goods = empty($lab_goods)? '0': trim($lab_goods);
	$sql_in    = "(".$lab_goods.")";
	$sql       = "SELECT count(goods_id) FROM ".$GLOBALS['ecs']->table('goods')." WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 AND goods_id in".$sql_in.";";
	return $GLOBALS['db']->GetOne($sql);	
}
?>