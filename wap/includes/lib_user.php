<?php
/**
 * 用户相关函数
 * @version 2014
 * @author xuyizhi
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

//yi:站内信函数
function get_user_msg_list($user_id=0, $page=1, $size=10)
{
	$page  = ($page<1)? 1: intval($page);
	$start = ($page-1)*$size;
	$sql = "select m.*, FROM_UNIXTIME(m.add_time) AS f_add_time, IF(m.is_bat=1, b.is_read, m.is_read) as is_read from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.is_show=1 and (m.user_id='$user_id') or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id') order by m.rec_id desc limit ".$start.",".$size;
	$list = $GLOBALS['db']->GetAll($sql);
	return $list;
}

function get_user_msg_unread($user_id=0, $page=1, $size=10)
{
	$page  = ($page<1)? 1: intval($page);
	$start = ($page-1)*$size;
	$sql = "select m.*, FROM_UNIXTIME(m.add_time) AS f_add_time from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.is_show=1 and (m.user_id='$user_id' and m.is_read=0) or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id' and b.is_read=0) order by m.rec_id desc limit ".$start.",".$size;
	$unread = $GLOBALS['db']->GetAll($sql);	
	return $unread;
}

function get_user_msg_number($user_id=0)
{
	$sql = "select count(m.rec_id) from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.is_show=1 and (m.user_id='$user_id') or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id') ";
	return $GLOBALS['db']->GetOne($sql);
}


//yi:会员中心随机推荐10个商品
/*size:随机获取热销商品的数量. 商品范围包括所有热销和新品的商品*/
function user_hot_goods($size = 10){
	//随机的 开始数字
	$sqlrand  = "select count(*) from ".$GLOBALS['ecs']->table('goods')." where (is_alone_sale=1 and is_delete=0 and is_on_sale=1 and is_hot=1) or 
				(is_alone_sale=1 and is_delete=0 and is_on_sale=1 and is_tj=1);";
	$count    = $GLOBALS['db']->GetOne($sqlrand);
	$rand_num = rand(0,$count-$size);
	
	$hot_goods = array();
	$sql = "select goods_id, goods_name, shop_price, market_price, goods_thumb, goods_img, original_img from ".$GLOBALS['ecs']->table('goods')
		  ." where (is_alone_sale=1 and is_delete=0 and is_on_sale=1 and is_hot=1) or 
			(is_alone_sale=1 and is_delete=0 and is_on_sale=1 and is_tj=1) limit ".$rand_num.",".$size.";";
	$res = $GLOBALS['db']->query($sql);
	while($row = $GLOBALS['db']->fetchRow($res)){
		$hot_goods[] = $row;
	}
	return $hot_goods;
}

/**
 * 取得帐户积分详情
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_accountlist($user_id, $account_type = '')
{
    /* 检查参数 */
    $where = " WHERE user_id = '$user_id' ";
    if (in_array($account_type, array('user_money', 'frozen_money', 'rank_points', 'pay_points')))
    {
        $where .= " AND $account_type <> 0 ";
    }

    /* 初始化分页参数 */
    $filter = array(
        'user_id'       => $user_id,
        'account_type'  => $account_type
    );

    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('account_log') . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);



    /* 查询记录 */
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('account_log') . $where .
            " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
        $arr[] = $row;
    }

    return array('account' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/**
 * 分页的信息加入条件的数组
 *
 * @access  public
 * @return  array
 */
function page_and_size($filter)
{
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
    {
        $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
    }
    else
    {
        $filter['page_size'] = 15;
    }

    /* 每页显示 */
    $filter['page'] = (empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    /* page 总数 */
    $filter['page_count'] = (!empty($filter['record_count']) && $filter['record_count'] > 0) ? ceil($filter['record_count'] / $filter['page_size']) : 1;

    /* 边界处理 */
    if ($filter['page'] > $filter['page_count'])
    {
        $filter['page'] = $filter['page_count'];
    }

    $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];

    return $filter;
}

/**
 * 获得指定分类下的推荐商品 
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
    $res = $GLOBALS['db']->selectLimit($sql, 6);

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


function get_district_lsit($type=0, $parent=0)
{
	return $GLOBALS['db']->GetAll("SELECT region_id, region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_type = ".intval($type)." AND parent_id = ".intval($parent));
}

/**
 * 取得收货人地址详细信息
 * @param   int     $user_id    用户编号
 * @param   int     $address_id    用户编号
 * @return  array
 */
function get_consignee_row($user_id,$address_id)
{
    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE user_id = '$user_id' and address_id = '$address_id'";
    return $GLOBALS['db']->getRow($sql);
}