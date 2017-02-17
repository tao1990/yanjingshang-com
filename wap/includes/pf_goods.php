<?php
/**
 * 商品相关函数
 * @version 2014
 * @author xuyizhi
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 获取商品详情(ecs_goods)
 */
function get_goods_details($goods_id=0)
{
	$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = " . $goods_id . " LIMIT 1";
	$row = $GLOBALS['db']->getRow($sql);
	
	return $row;
}

/**
 * 获取商品相册
 */
function get_goods_album($goods_id=0)
{
	$rs = $GLOBALS['db']->GetAll("SELECT * FROM ecs_goods_gallery WHERE goods_id=".$goods_id." ORDER BY is_main DESC");
	return $rs;
}

/**
 * 获取眼镜度数
 */
function get_goods_degree($goods_id=0)
{
	$sell = (!$save)? " and sell=1 ": "";//是否保留取消度数
	$ds = $GLOBALS['db']->getAll("select *, IF(stock>-50, '', '(补货中)') as status,IF(stock>-50, 1, 0) as canbuy from ecs_ds where gid='$goods_id' ".$sell." order by pid");
	
	$arr = array();
	
	foreach ($ds as $k => $v)
	{
		$arr[$k]['pid'] = $v['pid'];
		$arr[$k]['gid'] = $v['gid'];
		$arr[$k]['val'] = str_replace("\r", "", $v['val']);
		$arr[$k]['sell'] = $v['sell'];
		$arr[$k]['stock'] = $v['stock'];
		$arr[$k]['status'] = $v['status'];
		$arr[$k]['canbuy'] = $v['canbuy'];
	}
	
	return $arr;
}

/**
 * 获取商品的分类、品牌的ID和名称
 */
function get_goods_category_and_brand($goods_id)
{
	$sql = "SELECT c.cat_name, b.brand_name FROM " . $GLOBALS['ecs']->table('goods') . " g " .
		   "LEFT JOIN " . $GLOBALS['ecs']->table('category') . " c ON g.cat_id = c.cat_id " . 
		   "LEFT JOIN " . $GLOBALS['ecs']->table('brand') . " b ON g.brand_id = b.brand_id " . 
		   "WHERE g.goods_id = " . $goods_id . " LIMIT 1";
	return $GLOBALS['db']->getRow($sql);
}

/**
 * 获取商品不同用户等级的价格
 * @param $goods_id
 * @param $shop_price
 */
function get_user_rank_prices($goods_id, $shop_price)
{
    $sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " .
            'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
            "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " .
            "WHERE r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[$row['rank_id']] = array(
                        'rank_name' => htmlspecialchars($row['rank_name']),
                        'price'     => sprintf("￥%s", number_format($row['price'], 2, '.', '')),
						'price_pure'=> number_format($row['price'], 2, '.', '')
			);
    }

    return $arr;
}

/**
 * 取得商品的关联商品
 */
function get_link_goods($goods_id=0)
{
	$sql = "SELECT l.*, g.goods_img, g.goods_thumb, g.original_img FROM ecs_link_goods as l 
			LEFT JOIN ecs_goods as g on l.link_goods_id=g.goods_id 
			WHERE l.goods_id=".$goods_id." and g.cat_id<>138 and g.goods_number>0 and g.is_delete=0 and g.is_on_sale=1 and g.is_alone_sale=1 
			LIMIT 0, 5";
	return $GLOBALS['db']->GetAll($sql);
}

/**
 * 是否是散光片
 */
function is_sg($goods_id=0)
{
	$rt = false;

	//散光片id在ecs_goods_cat表中的cat_id=15。
	$sql = "SELECT goods_id FROM ".$GLOBALS['ecs']->table('goods_cat')." WHERE cat_id = 15 AND goods_id = " . $goods_id . " LIMIT 1";
	$res = $GLOBALS['db']->getOne($sql);
	if( ! empty($res))
	{
		$rt = true;
	}
	
	return $rt;
}

/**
 * 获取散光度数
 */
function get_sg_degree($goods_id=0)
{
    $sql = 'SELECT b.attr_values FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ,' . $GLOBALS['ecs']->table('attribute_sg') . ' AS b ' .  
           "WHERE g.goods_id = '$goods_id' AND g.sgds_id = b.attr_id ";
    $row = $GLOBALS['db']->getRow($sql);
    if($row !== false)
    {      
        //$row['ds_values'] =  explode("\n", $row['attr_values']);
        $sg = explode("\n", $row['attr_values']);
        $arr = array();
        foreach ($sg as $v)
        {
        	if ( ! empty($v)) $arr[] = $v;
        }
        return $arr;
        //return $row;
    }
    else
    {
        return false;
    }
}

/**
 * 获取商品类型名称和商品大类ID
 */
function get_goods_type_name_and_parentid($goods_type=10)
{
	$arr = array();
	if ($goods_type == 10) 
	{
		$arr = array('goods_type_name' => '彩色隐形眼镜', 'parent_id' => 6);
	} 
	elseif ($goods_type == 12) 
	{
		$arr = array('goods_type_name' => '透明隐形眼镜', 'parent_id' => 1);
	}
	elseif ($goods_type == 13) 
	{
		$arr = array('goods_type_name' => '护理产品', 'parent_id' => 64);
	}
	elseif ($goods_type == 14) 
	{
		$arr = array('goods_type_name' => '护理产品', 'parent_id' => 64);
	}
	elseif ($goods_type == 15) 
	{
		$arr = array('goods_type_name' => '框架眼镜', 'parent_id' => 159);
	}
	elseif ($goods_type == 16) 
	{
		$arr = array('goods_type_name' => '太阳眼镜', 'parent_id' => 190);
	}
	
	return $arr;
}

/**
 * 获取框架的镜片信息
 */
function get_glass_type_info($glass_type=1)
{
	if ( ! empty($glass_type))
	{
		if ($glass_type == 1) {
			return get_goods_details(1393);
		} elseif ($glass_type == 2) {
			return get_goods_details(1394);
		} elseif ($glass_type == 3) {
			return get_goods_details(1395);
		} elseif ($glass_type == 4) {
			return get_goods_details(1396);
		} elseif ($glass_type == 5) {
			return get_goods_details(1397);
		} elseif ($glass_type == 6) {
			return get_goods_details(1398);
		}
	}
}

