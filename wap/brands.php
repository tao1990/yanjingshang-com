<?php
//商城品牌列表汇总页 【20121119】【author：yijiangwen】
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

//------------------------------------页头 页尾 数据---------------------------------------//
$position = assign_ur_here();
//$smarty->assign('column',               get_column() ); //栏目导航
$smarty->assign('page_title',          "品牌汇 - 易视网手机版");
$smarty->assign('ur_here',             "品牌汇");
$smarty->assign('topbanner',           ad_info_by_time(31,1));            //头部横幅广告
$smarty->assign('helps',               get_shop_help());          //网店帮助文章
$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
/*$cat_tree = get_category_tree();                     			  //分类列表
$smarty->assign('cat_1',        		$cat_tree[1]);
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
//------------------------------------页头 页尾 数据end------------------------------------//

//产品页面数据。
$smarty->assign('ad_goods_pan',    ad_info(46,10));            // 首页广告
$smarty->assign('brand_1',         get_brand_by_cat(1));       // 透明隐形眼镜
$smarty->assign('brand_6',         get_brand_by_cat(6));       // 彩色隐形眼镜
$smarty->assign('brand_159',       get_brand_by_cat(159));     // 框架眼镜
$smarty->assign('brand_190',       get_brand_by_cat(190));     // 太阳眼镜
$smarty->assign('brand_64',        get_brand_by_cat(64));      // 护理液
$smarty->assign('brand_76',        get_brand_by_cat(76));      // 护理工具


$ch_arr = array();
for($i=65; $i<=90; $i++)
{
	$ch_arr[$i]['num']  = $i;
	$ch_arr[$i]['bnum'] = 0;
	$ch_arr[$i]['char'] = chr($i);	
}

$sql  = "select count(*) as bnum, first_letter from ecs_brand where is_show=1 and first_letter>0 group by first_letter order by first_letter asc";
$bnum = $GLOBALS['db']->getAll($sql);
foreach($bnum as $a => $b)
{
	$letter = $b['first_letter'];
	$ch_arr[$letter]['bnum'] = $b['bnum'];
}
$smarty->assign('ch_arr', $ch_arr);


//brand
$sql = "select * from ecs_brand where is_show=1 order by first_letter asc, sort_order asc;";
$brand = $GLOBALS['db']->getAll($sql);
foreach($brand as $k => $v)
{
	//$brand[$k]['url'] = ($v['site_url']=='' || $v['site_url']=='http://' || $v['site_url']=='http://#')? 'brand_'.$v['brand_id'].'-1-update_last-desc.html':$v['site_url'];
    //var_dump($v);
    // 处理品牌链接
    if(!empty($v['site_url'])){
        $arr1 = explode('_',$v['site_url']);
        if(isset($arr1[1])){
            $arr2 = explode('.',$arr1[1]);
            $brand[$k]['site_url'] = "category.php?cat_id=" . $arr2[0];
        }else{
            $brand[$k]['site_url'] = "";
        }
    }else{
        $brand[$k]['site_url'] = "";
    }
	$brand[$k]['url'] = 'brand_'.$v['brand_id'].'-1-update_last-desc.html';
	$brand[$k]['brand_logo'] = 'data/brandlogo/'.$v['brand_logo'];
}
$smarty->assign('brand', $brand);

$smarty->display('brands.dwt');


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:get brands by category
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_brand_by_cat($parent_id=1)
{
	$sql = "select distinct b.*, c.cat_id from ecs_goods as g left join ecs_category as c on g.cat_id=c.cat_id left join ecs_brand as b on g.brand_id=b.brand_id ".
		   " where c.parent_id=".$parent_id." and b.brand_id is not null and b.is_show=1 group by b.brand_id order by b.sort_order asc;";
	$arr = $GLOBALS['db']->getAll($sql);
	foreach($arr as $k => $v)
	{
		//$arr[$k]['url'] = ($v['site_url']=='' || $v['site_url']=='http://' || $v['site_url']=='http://#')? 'brand_'.$v['brand_id'].'-1-update_last-desc.html':$v['site_url']; 
		//$arr[$k]['url'] = 'brand2_'.$v['brand_id'].'-'.$parent_id.'-1-update_last-desc.html';
		$arr[$k]['url'] = 'category.php?cat_id='.$v['cat_id'];
		$arr[$k]['brand_logo'] = 'data/brandlogo/'.$v['brand_logo'];
	}
    //var_dump($arr); exit();
	return $arr;
}

//get品牌列表的详细信息
function get_brand_list_info()
{
	$sql = "select * from ".$GLOBALS['ecs']->table('brand')." where is_show=1 order by sort_order ASC;";
	$brands = $GLOBALS['db']->getAll($sql);
	
	foreach($brands as $k => $v)
	{
		if(!empty($v['brand_logo']))
		{
			$brands[$k]['img_url'] = 'data/brandlogo/'.$v['brand_logo'];
		}
		else
		{
			if(strlen($v['site_url'])<8)
			{
				$brands[$k]['site_url'] = "http://www.easeeyes.com";
			}
		}
		
		//该品牌下面商品的数量。
		$sql = "select count(goods_id) from ".$GLOBALS['ecs']->table('goods')." where brand_id=".$v['brand_id'];
		$brands[$k]['goods_num'] = $GLOBALS['db']->getOne($sql); 
	}
	
	return $brands;
}
?>