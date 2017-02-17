<?php
/* =============================================================================================
 * 【购物车】购物流程函数库【Author:yijiangwen】【同步TIME:2012/8/27】
 * =============================================================================================
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 处理序列化的支付、配送的配置参数
 * 返回一个以name为索引的数组
 * @access  public
 * @param   string       $cfg
 * @return  void
 */
function unserialize_config($cfg)
{
    if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
    {
        $config = array();

        foreach ($arr AS $key => $val)
        {
            $config[$val['name']] = $val['value'];
        }

        return $config;
    }
    else
    {
        return false;
    }
}
/**
 * 取得已安装的配送方式
 * @return  array   已安装的配送方式
 */
function shipping_list()
{
    $sql = 'SELECT shipping_id, shipping_name ' .
            'FROM ' . $GLOBALS['ecs']->table('shipping') .
            ' WHERE enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得配送方式信息
 * @param   int     $shipping_id    配送方式id
 * @return  array   配送方式信息
 */
function shipping_info($shipping_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('shipping') .
            " WHERE shipping_id = '$shipping_id' " . 'AND enabled = 1';

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得可用的配送方式列表
 * @param   array   $region_id_list     收货人地区id数组（包括国家、省、市、区）
 * @return  array   配送方式数组
 */
function available_shipping_list($region_id_list)
{
    $sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, s.shipping_desc, s.insure, s.support_cod, a.configure ' .
		   'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' .
			$GLOBALS['ecs']->table('shipping_area') . ' AS a, ' .
			$GLOBALS['ecs']->table('area_region') . ' AS r ' .
			'WHERE r.region_id ' . db_create_in($region_id_list) .
			' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得某配送方式对应于某收货地址的区域信息
 * @param   int     $shipping_id          配送方式id
 * @param   array   $region_id_list       收货人地区id数组
 * @return  array   配送区域信息（config 对应着反序列化的 configure）
 */
function shipping_area_info($shipping_id, $region_id_list)
{
    $sql =  'SELECT s.shipping_code, s.shipping_name, s.shipping_desc, s.insure, s.support_cod, a.configure, a.shipping_area_id ' .
            'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' .$GLOBALS['ecs']->table('shipping_area') . ' AS a, ' .$GLOBALS['ecs']->table('area_region') . ' AS r ' .
            "WHERE s.shipping_id = '$shipping_id' " .'AND r.region_id ' . db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';
    $row = $GLOBALS['db']->getRow($sql);

    if (!empty($row))
    {
        $shipping_config = unserialize_config($row['configure']);
        if (isset($shipping_config['pay_fee']))
        {
            if (strpos($shipping_config['pay_fee'], '%') !== false)
            {
                $row['pay_fee'] = floatval($shipping_config['pay_fee']) . '%';
            }
            else
            {
                 $row['pay_fee'] = floatval($shipping_config['pay_fee']);
            }
        }
        else
        {
            $row['pay_fee'] = 0.00;
        }
    }

    return $row;
}

/* -------------------------------------------------------------------------------------------------
 * 函数:购物车中商品首重费用【yi】
 * -------------------------------------------------------------------------------------------------
 */
function shipping_base_fee($shipping_config)
{
	if(!is_array($shipping_config))
	{
		$shipping_config = unserialize($shipping_config);
	}

	$base_fee = 10; //运费配置表中首重费用 xu:原为0，改为10，防止有时未及时加载运费
	//$base_fee = 0;
	foreach($shipping_config as $k => $v)
	{
		if($shipping_config[$k]['name']=='base_fee')
		{
			$base_fee = intval($shipping_config[$k]['value']);
		}
	}
	return $base_fee;
}

/**
 * 计算【购物车中】订单运费
 * @param   string  $shipping_code      配送方式代码
 * @param   mix     $shipping_config    配送方式配置信息
 * @param   float   $goods_weight       商品重量
 * @param   float   $goods_amount       商品金额
 * @param   float   $goods_number       商品数量
 * @return  float   订单运费
 */
function shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number='', $area_id=0)
{
    if(!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }

    $filename = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
    if(file_exists($filename))
    {
        include_once($filename);

        $obj = new $shipping_code($shipping_config);
		if($area_id==0)
		{
			return $obj->calculate($goods_weight, $goods_amount, $goods_number);
		}
		else
		{
			return $obj->calculate($goods_weight, $goods_amount, $goods_number, $area_id);
		}
    }
    else
    {
        return 0;
    }
}

/* -------------------------------------------------------------------------------------------------
 * 函数 获得下一个免运费梯度重量，购物金额
 * -------------------------------------------------------------------------------------------------
 */
function get_next_shipping_level($goods_weight, $goods_amount, $goods_number='', $area_id=0)
{
	$level = array(
			'next_level_id'    =>0,
			'next_level_weight'=>0,//下梯度免掉的重量
			'next_level_sump'  =>0 //下梯度购物金额
		);

	//判断是否是普通快递
	if($area_id>0)
	{
		$shipping_id = $GLOBALS['db']->GetOne("select shipping_id from ".$GLOBALS['ecs']->table('shipping_area')." where shipping_area_id=".$area_id);

		if($shipping_id == 9)
		{
			//普通快递（只快递才有梯度）

			//该区域的所有梯度
			$levels   = $GLOBALS['db']->GetAll("select * from ".$GLOBALS['ecs']->table('shipping_ladder')." where shipping_area_id=".$area_id." order by rec_id asc");
			$l_weight = 1; //默认为首重

			foreach($levels as $k => $v)
			{
				//ex: 2.001kg  l:3.0kg， 4.0kg
				$l_weight = ($levels[$k]['weight']>$l_weight)? intval($levels[$k]['weight']): 1;

				if($goods_weight >= $l_weight)
				{
					continue;
				}
				else
				{
					//找到下一个梯度
					$level['next_level_id']     = $k;
					$level['next_level_weight'] = $levels[$k]['weight'];
					$level['next_level_sump']   = $levels[$k]['fee']-$goods_amount;
					break;
				}
			}
		}
		elseif($shipping_id == 9)
		{
			//货到付款
		}
		else
		{}
	}
	return $level;
}

//yi:【参考shipping_fee】计算购物车中原本应该支付的运费，一共省掉了多少运费
function ship_old($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number='', $area_id=0)
{
    if(!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }
    $filename = ROOT_PATH.'includes/modules/shipping/'.$shipping_code.'.php';
    if(file_exists($filename))
    {
        include_once($filename);
        $obj = new $shipping_code($shipping_config);
		return $obj->cal_old($goods_weight, $goods_amount, $goods_number, $area_id);
    }
    else
    {
        return 0;
    }
}

/**
 * 获取指定配送的保价费用
 *
 * @access  public
 * @param   string      $shipping_code  配送方式的code
 * @param   float       $goods_amount   保价金额
 * @param   mix         $insure         保价比例
 * @return  float
 */
function shipping_insure_fee($shipping_code, $goods_amount, $insure)
{
    if (strpos($insure, '%') === false)
    {
        /* 如果保价费用不是百分比则直接返回该数值 */
        return floatval($insure);
    }
    else
    {
        $path = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';

        if (file_exists($path))
        {
            include_once($path);

            $shipping = new $shipping_code;
            $insure   = floatval($insure) / 100;

            if (method_exists($shipping, 'calculate_insure'))
            {
                return $shipping->calculate_insure($goods_amount, $insure);
            }
            else
            {
                return ceil($goods_amount * $insure);
            }
        }
        else
        {
            return false;
        }
    }
}

/**
 * 取得已安装的支付方式列表
 * @return  array   已安装的配送方式列表
 */
function payment_list()
{
    $sql = 'SELECT pay_id, pay_name ' .
            'FROM ' . $GLOBALS['ecs']->table('payment') .
            ' WHERE enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/* ===========================================================================
 * 函数 yi:取得支付方式信息
 * ===========================================================================
 * $pay_id int    支付方式id
 * return  array  支付方式信息
 * @如果pay_id>100表示是银行直连支付，获取现在的pay_id=10快钱网关。
 */
function payment_info($pay_id)
{
	if(intval($pay_id)>100)
	{
		if (intval($pay_id) == 901)
		{
			$pay_id = 14; //得仕通支付
		}
		else
		{
			$pay_id = ($pay_id>100 && $pay_id<800)? 10: 13;
		}
	}
	
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment') ." WHERE pay_id = '$pay_id' AND enabled = 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 获得订单需要支付的支付费用
 *
 * @access  public
 * @param   integer $payment_id
 * @param   float   $order_amount
 * @param   mix     $cod_fee
 * @return  float
 */
function pay_fee($payment_id, $order_amount, $cod_fee=null)
{
    $pay_fee = 0;
    $payment = payment_info($payment_id);
    $rate    = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];

    if (strpos($rate, '%') !== false)
    {
        /* 支付费用是一个比例 */
        $val     = floatval($rate) / 100;
        $pay_fee = $val > 0 ? $order_amount * $val /(1- $val) : 0;
    }
    else
    {
        $pay_fee = floatval($rate);
    }

    return round($pay_fee, 2);
}

/**
 * 取得可用的支付方式列表
 * @param   bool    $support_cod        配送方式是否支持货到付款
 * @param   int     $cod_fee            货到付款手续费（当配送方式支持货到付款时才传此参数）
 * @param   int     $is_online          是否支持在线支付
 * @return  array   配送方式数组
 */
function available_payment_list($support_cod, $cod_fee = 0, $is_online = false)
{
    $sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc, pay_config, is_cod' .
            ' FROM ' . $GLOBALS['ecs']->table('payment') .
            ' WHERE enabled = 1 ';
    if (!$support_cod)
    {
        $sql .= 'AND is_cod = 0 '; // 如果不支持货到付款
    }
    if ($is_online)
    {
        $sql .= "AND is_online = '1' ";
    }
    $sql .= 'ORDER BY pay_order'; // 排序
    $res = $GLOBALS['db']->query($sql);

    $pay_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['is_cod'] == '1')
        {
            $row['pay_fee'] = $cod_fee;
        }
		
		$row['pay_desc'] =nl2br($row['pay_desc']);
		$row['format_pay_fee'] = strpos($row['pay_fee'], '%') !== false ? $row['pay_fee'] :
		price_format($row['pay_fee'], false);
		$modules[] = $row;
    }

	//include_once(ROOT_PATH.'includes/lib_compositor.php');排序

    if(isset($modules))
    {
        return $modules;
    }
}

/**
 * 取得包装列表
 * @return  array   包装列表
 */
function pack_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('pack');
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['format_pack_fee'] = price_format($row['pack_fee'], false);
        $row['format_free_money'] = price_format($row['free_money'], false);
        $list[] = $row;
    }

    return $list;
}

/**
 * 取得包装信息
 * @param   int     $pack_id    包装id
 * @return  array   包装信息
 */
function pack_info($pack_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pack') .
            " WHERE pack_id = '$pack_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 根据订单中的商品总额来获得包装的费用
 *
 * @access  public
 * @param   integer $pack_id
 * @param   float   $goods_amount
 * @return  float
 */
function pack_fee($pack_id, $goods_amount)
{
    $pack = pack_info($pack_id);

    $val = (floatval($pack['free_money']) <= $goods_amount && $pack['free_money'] > 0) ? 0 : floatval($pack['pack_fee']);

    return $val;
}

/**
 * 取得贺卡列表
 * @return  array   贺卡列表
 */
function card_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('card');
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['format_card_fee'] = price_format($row['card_fee'], false);
        $row['format_free_money'] = price_format($row['free_money'], false);
        $list[] = $row;
    }

    return $list;
}

/**
 * 取得贺卡信息
 * @param   int     $card_id    贺卡id
 * @return  array   贺卡信息
 */
function card_info($card_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('card') .
            " WHERE card_id = '$card_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 根据订单中商品总额获得需要支付的贺卡费用
 *
 * @access  public
 * @param   integer $card_id
 * @param   float   $goods_amount
 * @return  float
 */
function card_fee($card_id, $goods_amount)
{
    $card = card_info($card_id);

    return ($card['free_money'] <= $goods_amount && $card['free_money'] > 0) ? 0 : $card['card_fee'];
}

/**
 * 取得订单信息
 * @param   int     $order_id   订单id（如果order_id > 0 就按id查，否则按sn查）
 * @param   string  $order_sn   订单号
 * @return  array   订单信息（金额都有相应格式化的字段，前缀是formated_）
 */
function order_info($order_id, $order_sn = '')
{
    //计算订单各种费用之和
	//yi修改$total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";
    $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee -surplus -integral_money -bonus) AS total_fee ";
    $order_id = intval($order_id);
    if ($order_id > 0)
    {
        $sql = "SELECT *, " . $total_fee . " FROM " . $GLOBALS['ecs']->table('order_info') .
                " WHERE order_id = '$order_id'";
    }
    else
    {
        $sql = "SELECT *, " . $total_fee . "  FROM " . $GLOBALS['ecs']->table('order_info') .
                " WHERE order_sn = '$order_sn'";
    }
    $order = $GLOBALS['db']->getRow($sql);

    /* 格式化金额字段 */
    if ($order)
    {
        $order['formated_goods_amount']   = price_format($order['goods_amount'], false);
        $order['formated_discount']       = price_format($order['discount'], false);
        $order['formated_tax']            = price_format($order['tax'], false);
        $order['formated_shipping_fee']   = price_format($order['shipping_fee'], false);
        $order['formated_insure_fee']     = price_format($order['insure_fee'], false);
        $order['formated_pay_fee']        = price_format($order['pay_fee'], false);
        $order['formated_pack_fee']       = price_format($order['pack_fee'], false);
        $order['formated_card_fee']       = price_format($order['card_fee'], false);
        $order['formated_total_fee']      = price_format($order['total_fee'], false);
        $order['formated_money_paid']     = price_format($order['money_paid'], false);
        $order['formated_bonus']          = price_format($order['bonus'], false);
        $order['formated_integral_money'] = price_format($order['integral_money'], false);
        $order['formated_surplus']        = price_format($order['surplus'], false);
        $order['formated_order_amount']   = price_format(abs($order['order_amount']), false);
        $order['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
    }

    return $order;
}

/**
 * 判断订单是否已完成
 * @param   array   $order  订单信息
 * @return  bool
 */
function order_finished($order)
{
    return $order['order_status']  == OS_CONFIRMED &&
        ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) &&
        ($order['pay_status']      == PS_PAYED   || $order['pay_status'] == PS_PAYING);
}

/**
 * 取得订单商品
 * @param   int     $order_id   订单id
 * @return  array   订单商品数组
 */
function order_goods($order_id)
{
    $sql = "SELECT rec_id, goods_id, goods_name, goods_sn, market_price, goods_number, " .
            "goods_price, goods_attr, is_real, parent_id, is_gift,zselect,zcount,yselect,ycount, " .
            "goods_price * goods_number AS subtotal, extension_code, ds_extention " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') .
            " WHERE order_id = '$order_id'";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }
        $goods_list[] = $row;
    }

    //return $GLOBALS['db']->getAll($sql);
    return $goods_list;
}

/**
 * 取得订单总金额
 * @param   int     $order_id   订单id
 * @param   bool    $include_gift   是否包括赠品
 * @return  float   订单总金额
 */
function order_amount($order_id, $include_gift = true)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') .
            " WHERE order_id = '$order_id'";
    if (!$include_gift)
    {
        $sql .= " AND is_gift = 0";
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单商品总重量和总金额（对应 cart_weight_price）
 * @param   int     $order_id   订单id
 * @return  array   ('weight' => **, 'amount' => **, 'formated_weight' => **)
 */
function order_weight_price($order_id)
{
    $sql = "SELECT SUM(g.goods_weight * o.goods_number) AS weight, " .
                "SUM(o.goods_price * o.goods_number) AS amount ," .
                "SUM(o.goods_number) AS number " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS o, " .
                $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE o.order_id = '$order_id' " .
            "AND o.goods_id = g.goods_id";

    $row = $GLOBALS['db']->getRow($sql);
    $row['weight'] = floatval($row['weight']);
    $row['amount'] = floatval($row['amount']);
    $row['number'] = intval($row['number']);

    /* 格式化重量 */
    $row['formated_weight'] = formated_weight($row['weight']);

    return $row;
}

/* -------------------------------------------------------------------------------------------------
 * 函数 当前订单中（购物车中）积分兑换商品应扣除的会员消费积分数【yi】
 * -------------------------------------------------------------------------------------------------
 */
function order_exchange_goods_integral($user_id = 0)
{
	$integral = 0; //没有积分兑换商品，扣除积分0

	//购物车中是否有积分兑换的商品
	/*$sql = "select * from ".$GLOBALS['ecs']->table('cart').
		   " WHERE `session_id` = '".SESS_ID."' and extension_code='exchange' and goods_price=0 and goods_number=1 and user_id=".$user_id;*/
	$sql = "select * from ".$GLOBALS['ecs']->table('cart').
		   " WHERE `session_id` = '".SESS_ID."' and extension_code='exchange' and goods_price=0 and user_id=".$user_id; //xu:2013-12-12修改

	$exchange_goods = $GLOBALS['db']->getAll($sql);
	if(!empty($exchange_goods))
	{	
		//遍历购物车当中的积分兑换商品
		foreach($exchange_goods as $k => $v)
		{
			//积分兑换该产品应该扣除的积分
			$sqle = "select exchange_integral from ".$GLOBALS['ecs']->table('exchange_goods')." where is_exchange=1 and goods_id=".$exchange_goods[$k]['goods_id'] ;
			$exchange_integral = $GLOBALS['db']->getOne($sqle);
			$exchange_integral = $exchange_integral * $v['goods_number']; //xu:2013-12-12修改，当多个商品时，乘以数量

			if($exchange_integral>0)
			{
				$integral += intval($exchange_integral);
			}
		}
	}
	return $integral;
}


/* -------------------------------------------------------------------------------------------------
 * 函数 获得订单中的费用信息
 * -------------------------------------------------------------------------------------------------
 * @access  public
 * @param   array   $order     订单信息
 * @param   array   $goods     订单商品
 * @param   array   $consignee 收货人信息
 * @param   array   $free_shipping 是否包邮（yi新增）
 * @param   bool    $is_gb_deposit  是否团购保证金（如果是，应付款金额只计算商品总额和支付费用，可以获得的积分取 $gift_integral）
 * @return  array
 */
function order_fee($order, $goods, $consignee,$free_shipping=false)
{
    if(!isset($order['extension_code']))
    {
        $order['extension_code'] = '';
    }

    if($order['extension_code'] == 'group_buy')
    {
        $group_buy = group_buy_info($order['extension_id']);
    }

	//初始化订单统计数据
    $total  = array('real_goods_count' => 0,
                    'gift_amount'      => 0,
                    'goods_price'      => 0,
                    'market_price'     => 0,
                    'discount'         => 0,
                    'pack_fee'         => 0,
                    'card_fee'         => 0,
                    'shipping_fee'     => 0,
                    'shipping_insure'  => 0,
                    'integral_money'   => 0,
                    'bonus'            => 0,
                    'surplus'          => 0,
                    'cod_fee'          => 0,
                    'pay_fee'          => 0,
                    'tax'              => 0);
    $weight = 0;

    //计算订单中商品总价
    foreach($goods AS $val)
    {
        //统计实体商品的个数
        if($val['is_real'])
        {
            $total['real_goods_count']++;
        }
        $total['goods_price']  += $val['goods_price'] * $val['goods_number'];
        $total['market_price'] += $val['market_price'] * $val['goods_number'];
    }

    $total['saving']    = $total['market_price'] - $total['goods_price'];
    $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;

    $total['goods_price_formated']  = price_format($total['goods_price'], false);
    $total['market_price_formated'] = price_format($total['market_price'], false);
    $total['saving_formated']       = price_format($total['saving'], false);

    /* 折扣 */
    if($order['extension_code'] != 'group_buy')
    {
        $discount = compute_discount();
        $total['discount'] = $discount['discount'];
        $total['favourable_name'] = $discount['name'];//现金折扣优惠活动名称(数组)
        if($total['discount'] > $total['goods_price'])
        {
            $total['discount'] = $total['goods_price'];
        }
    }
    $total['discount_formated'] = price_format($total['discount'], false);

    /* 税额 */
    if (!empty($order['need_inv']) && $order['inv_type'] != '')
    {
        /* 查税率 */
        $rate = 0;
        foreach ($GLOBALS['_CFG']['invoice_type']['type'] as $key => $type)
        {
            if ($type == $order['inv_type'])
            {
                $rate = floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) / 100;
                break;
            }
        }
        if ($rate > 0)
        {
            $total['tax'] = $rate * $total['goods_price'];
        }
    }
    $total['tax_formated'] = price_format($total['tax'], false);

    /* 包装费用 */
    if (!empty($order['pack_id']))
    {
        $total['pack_fee']      = pack_fee($order['pack_id'], $total['goods_price']);
    }
    $total['pack_fee_formated'] = price_format($total['pack_fee'], false);

    /* 贺卡费用 */
    if (!empty($order['card_id']))
    {
        $total['card_fee']      = card_fee($order['card_id'], $total['goods_price']);
    }
    $total['card_fee_formated'] = price_format($total['card_fee'], false);

    /* 红包 */
    if(!empty($order['bonus_id']))
    {

		//yi:修改红包当中漏洞 当用户修改购物车之后又返回最后一步的情况 应当检查当前红包是否有效

		//当前购物车中金额是否达到最小购物金额  若没有达到 则取消当前红包	
		$sql = "select bt.min_goods_amount from ".$GLOBALS['ecs']->table('user_bonus')." as ub, ".$GLOBALS['ecs']->table('bonus_type').
			   " as bt where ub.bonus_type_id = bt.type_id and ub.bonus_id =".$order['bonus_id'].";";
		$min_goods_amount = $GLOBALS['db']->GetOne($sql);

		if($total['goods_price'] >= $min_goods_amount)
		{
			$bonus          = bonus_info($order['bonus_id']);
			$total['bonus'] = $bonus['type_money'];
		}
		else
		{
			//购物金额没有达到红包最小金额
			$total['bonus'] = 0;
		}
    }
    $total['bonus_formated'] = price_format($total['bonus'], false);

    /* 线下红包 */
    if(!empty($order['bonus_kill']))
    {
        $bonus          = bonus_info(0,$order['bonus_kill']);
        $total['bonus_kill'] = $order['bonus_kill'];
        $total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
    }

	//=========================================================yi:修改购物免邮(免首重)后超重的情况===========================================||
    /* 配送费用 */
    $shipping_cod_fee = NULL;
    if($order['shipping_id'] > 0 && $total['real_goods_count'] > 0)
    {
        $region['country']  = $consignee['country'];
        $region['province'] = $consignee['province'];
        $region['city']     = $consignee['city'];
        $region['district'] = $consignee['district'];
        $shipping_info = shipping_area_info($order['shipping_id'], $region); //配送信息
		$area_id       = $shipping_info['shipping_area_id'];                 //配送区域id,唯一值

		//yi:2013/6/17 2天包邮活动
		if(in_array($consignee['province'], array(5,20)) && $_SERVER['REQUEST_TIME']>1371484800 && $_SERVER['REQUEST_TIME']<=1371657600)
		{
			$area_id = 20;
		}

        if(!empty($shipping_info))
        {
            if($order['extension_code'] == 'group_buy')
            {
                $weight_price = cart_weight_price(CART_GROUP_BUY_GOODS);//团购商品
            }
            else
            {
                $weight_price = cart_weight_price();//购物车重量，价格，数量
            }

			//-----------------------------------------------------------修正礼包商品免了运费的情况-AND `extension_code` != 'package_buy' -----------------------------||
            //查看购物车中是否全为免运费商品，若是则把运费赋为零
            $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('cart') . " WHERE  `session_id` = '" . SESS_ID. "' AND `is_shipping` = 0";
            $shipping_count = $GLOBALS['db']->getOne($sql);	
			
			//yi:订单是否包邮功能【唯一】
			if((include_free_ship_goods() && !$shipping_info['support_cod']) || $free_shipping == true)
			{
				$shipping_count = 0;//包邮标志
			}

			//yi:购物车中运费计算
			
			//tao:包邮判断改为折扣前价格start
			$discount_price = compute_discount_amount();
			if(($total['goods_price']-$discount_price)>0){
				$goods_price_total = $total['goods_price'] - $discount_price;
			}else{
				$goods_price_total = $total['goods_price'];
			}
            //tao:包邮判断改为折扣前价格end
            
			//tao:钻石会员订单价小于1的情况
			if($_SESSION['base_line'] ==1 && $goods_price_total<1){
				$goods_price_total = 1;
			}
            $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ?0 :  shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $weight_price['weight'], $goods_price_total, $weight_price['number'],$area_id);
        	
        	
        	$area_id = $GLOBALS['db']->getOne('SELECT shipping_area_id FROM ecs_area_region WHERE region_id = '.$consignee['province']);
       		
            
			//yi:订单首重费用，超重费用(免首重，且没有超重，超重费为0)
			$total['shipping_base_fee']   = shipping_base_fee($shipping_info['configure']);
			$total['yi_cod'] = ($shipping_info['shipping_code']=='yto')? true: false;        //货到付款

			$total['area_id'] = $area_id;
			
			if($goods_price_total>=68 && !$total['yi_cod'] && $area_id!=22)
			{
				//超重费用：计算中已经免掉1kg的首重了。
				$total['shipping_over_fee']   = isset($total['shipping_fee'])? ($total['shipping_fee']): 0;
			}
			else
			{
				$total['shipping_over_fee']   = ($total['shipping_fee']>$total['shipping_base_fee'])? ($total['shipping_fee'] - $total['shipping_base_fee']): 0;
			}
			
			//yi:江文修改固定运费包邮的形式【唯一】
			$ship_gid_arr2 = array(1236, 1296);
			if(include_ship_fee_goods($ship_gid_arr2) && !$shipping_info['support_cod'] && $total['goods_price'] < 68)
			{
				$sql8 = 'SELECT SUM(goods_number) FROM '.$GLOBALS['ecs']->table('cart')." WHERE  `session_id` = '" . SESS_ID. "' AND goods_id=1236";
				$t_number = $GLOBALS['db']->getOne($sql8);
				$t_number = empty($t_number)? 1: intval($t_number);
				$total['shipping_fee']      = $t_number*10;
				$total['shipping_over_fee'] = 0;
				$weight_price['weight']     = 1;				
			}
			//yi:江文修改固定运费包邮的形式[end]

			$total['shipping_base_fee_f'] = price_format($total['shipping_base_fee'], false);
			$total['shipping_over_fee_f'] = price_format($total['shipping_over_fee'], false);
			
			
			$total['ship_cart_wei']= $weight_price['weight'];                                //商品总重
			$total['overload'] = ($total['ship_cart_wei']>1)? ($total['ship_cart_wei']-1): 0;//超重重量	

			//下一个阶梯的费用和重量。
			$level = get_next_shipping_level($weight_price['weight'], $total['goods_price'], $weight_price['number'], $area_id);
			$total['level'] = $level;

			//----------------实际应该支付的运费计算（原先方法,可删除）---------------------------------------------------------||
			$ship_temp = ship_old($shipping_info['shipping_code'], $shipping_info['configure'], $weight_price['weight'],
								  $total['goods_price'], $weight_price['number'], $area_id);
			if(!empty($ship_temp))
			{
				$total['ship_old']     = $ship_temp['fee']-$total['shipping_fee']; //【以免运费=实际应支付的全部运费-现在运费】
				$total['ship_old_fee'] = $ship_temp['fee_fee'];//在购物金额可免下个梯次运费
				$total['ship_old_id']  = $ship_temp['fee_id'];
				$total['ship_old_wei'] = $ship_temp['fee_wei'];
				//$total['ship_cart_wei']= $weight_price['weight'];//商品总重
			}
			//----------------实际应该支付的运费计算【end】---------------------------------------------------------------------||

            if(!empty($order['need_insure']) && $shipping_info['insure'] > 0)
            {
                $total['shipping_insure'] = shipping_insure_fee($shipping_info['shipping_code'], $total['goods_price'], $shipping_info['insure']);
            }
            else
            {
                $total['shipping_insure'] = 0;
            }

            if($shipping_info['support_cod'])
            {
                $shipping_cod_fee = $shipping_info['pay_fee'];
            }
        }
    }
	//===========================================================修改购物车商品超重费用end====================================================||

    $total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);
    $total['shipping_insure_formated'] = price_format($total['shipping_insure'], false);

    // 购物车中的商品能享受红包支付的总额
    $bonus_amount = compute_discount_amount();
    // 红包和积分最多能支付的金额为商品总额
    $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;

    /* 计算订单总额 */
    if ($order['extension_code'] == 'group_buy' && $group_buy['deposit'] > 0)
    {
        $total['amount'] = $total['goods_price'];
    }
    else
    {
		//yi:活动，交行支付购物满199立减10元。框架满300立减50。
		/*
		if(!$shipping_info['support_cod'] && $order['pay_id'] == 12 && $total['goods_price']>=199)
		{			
			$kj_amount = yi_cart_kj_amount(); $at_zhekou = 0;
			if($kj_amount>=300)
			{
				$at_zhekou = 50;
			}
			else
			{
				$at_amount = yi_cart_at_amount();
				if($at_amount>=199)
				{
					$at_zhekou = 10;
				}
			}
			$total['discount'] = ($total['discount']>$at_zhekou)? $total['discount']: $at_zhekou;
		}*/

        $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
						   $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];

        // 减去红包金额
        $use_bonus        = min($total['bonus'], $max_amount); //实际减去的红包金额
        if(isset($total['bonus_kill']))
        {
            $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
            $total['amount'] -=  $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
        }

        $total['bonus']   = $use_bonus;
        $total['bonus_formated'] = price_format($total['bonus'], false);

        $total['amount'] -= $use_bonus; // 还需要支付的订单金额
        $max_amount      -= $use_bonus; // 积分最多还能支付的金额

    }

    /* 余额 */
    $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
    if ($total['amount'] > 0)
    {
        if (isset($order['surplus']) && $order['surplus'] > $total['amount'])
        {
            $order['surplus'] = $total['amount'];
            $total['amount']  = 0;
        }
        else
        {
            $total['amount'] -= floatval($order['surplus']);
        }
    }
    else
    {
        $order['surplus'] = 0;
        $total['amount']  = 0;
    }
    $total['surplus'] = $order['surplus'];
    $total['surplus_formated'] = price_format($order['surplus'], false);

    /* 积分 */
    $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
    if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0)
    {
        $integral_money = value_of_integral($order['integral']);

        // 使用积分支付
        $use_integral            = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
        $total['amount']        -= $use_integral;
        $total['integral_money'] = $use_integral;
        $order['integral']       = integral_of_value($use_integral);
    }
    else
    {
        $total['integral_money'] = 0;
        $order['integral']       = 0;
    }
    $total['integral'] = $order['integral'];
    $total['integral_formated'] = price_format($total['integral_money'], false);

    /* 保存订单信息 */
    $_SESSION['flow_order'] = $order;

    /* 支付费用 */
    if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $_SESSION['flow_type'] != CART_EXCHANGE_GOODS))
    {
        $total['pay_fee']      = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
    }

    $total['pay_fee_formated'] = price_format($total['pay_fee'], false);

    $total['amount']           += $total['pay_fee']; //订单总额累加上支付费用
    $total['amount_formated']  = price_format($total['amount'], false);

    /* 取得可以得到的积分和红包 */
    if ($order['extension_code'] == 'group_buy')
    {
        $total['will_get_integral'] = $group_buy['gift_integral'];
    }
    elseif ($order['extension_code'] == 'exchange_goods')
    {
        $total['will_get_integral'] = 0;
    }
    else
    {
        $total['will_get_integral'] = get_give_integral($goods);
    }
    $total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(get_total_bonus(), false);
    $total['formated_goods_price']  = price_format($total['goods_price'], false);
    $total['formated_market_price'] = price_format($total['market_price'], false);
    $total['formated_saving']       = price_format($total['saving'], false);

    if ($order['extension_code'] == 'exchange_goods')
    {
        $sql = 'SELECT SUM(eg.exchange_integral) '.
               'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c,' . $GLOBALS['ecs']->table('exchange_goods') . 'AS eg '.
               "WHERE c.goods_id = eg.goods_id AND c.session_id= '" . SESS_ID . "' " .
               "  AND c.rec_type = '" . CART_EXCHANGE_GOODS . "' " .
               '  AND c.is_gift = 0 AND c.goods_id > 0 ' .
               'GROUP BY eg.goods_id';
        $exchange_integral = $GLOBALS['db']->getOne($sql);
        $total['exchange_integral'] = $exchange_integral;
    }
    return $total;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi: 购物车中是否包含免运费的商品
 * ----------------------------------------------------------------------------------------------------------------------
 * kind = 0:正常商品, 1:礼包商品, 2:多个数量包邮, 3.指定来源包邮。
 */
function include_free_ship_goods()
{
	$sql = "select f.* from ecs_cart as c left join ecs_free_ship as f on c.goods_id=f.goods_id where c.session_id='".SESS_ID."' and f.start_time < unix_timestamp() and f.end_time > unix_timestamp() and ((f.kind=0 and c.extension_code<>'package_buy' and c.extension_code<>'source_buy') or (f.kind=1 and c.extension_code='package_buy') or (f.kind=3 and c.extension_code='source_buy')) ;";
	$res = $GLOBALS['db']->getAll($sql);

	$rtv = empty($res)? false: true;
	if(!$rtv)
	{	
		$sql = "select f.* from ecs_cart as c left join ecs_free_ship as f on c.goods_id=f.goods_id where c.session_id='".SESS_ID."' and f.start_time < unix_timestamp() and f.end_time > unix_timestamp() and f.kind=2 and c.extension_code<>'package_buy' and f.free_num>1 limit 1;";
		$hv_duo = $GLOBALS['db']->getAll($sql);
		if(!empty($hv_duo))
		{
			$sql = "select sum(c.goods_number) as goods_number, f.free_num from ecs_cart as c left join ecs_free_ship as f on c.goods_id=f.goods_id where c.session_id='".SESS_ID."' and f.start_time < unix_timestamp() and f.end_time > unix_timestamp() and f.kind=2 and c.extension_code<>'package_buy' and f.free_num>1 group by c.goods_id;";
			$duo = $GLOBALS['db']->getAll($sql);
			if(!empty($duo))
			{
				foreach($duo as $k => $v)
				{
					if($v['goods_number']>=$v['free_num'])
					{
						$rtv = true; break;
					}
				}
			}
		}
	}

	$tmp = $GLOBALS['db']->getAll("select * from ecs_cart where session_id='".SESS_ID."' AND goods_id IN (4370,4388,4383,4378,4379,4387,4380,4381,4382)");
	if ($tmp)
	{ 
		$rtv = true; 
	}
	$tmp2 = $GLOBALS['db']->getAll("select * from ecs_cart where session_id='".SESS_ID."' AND goods_id = 767 AND extension_code = 'tuan_buy'");
	if ($tmp2)
	{ 
		$rtv = true; 
	}

	return $rtv;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:做免运费包邮的功能，购物车中商品是否包含该包邮商品。
 * ----------------------------------------------------------------------------------------------------------------------
 * ship_gid_arr:指定包邮产品数组。可以同时设定多个商品，只有有一个商品在购物车中，就包邮。单个商品包邮的情况也兼容。
 * 手动使用前，要构建一个包邮商品数组。
 */
function include_ship_fee_goods($ship_gid_arr)
{	
	$ship = false;
	if(!empty($ship_gid_arr))
	{
		$in  = implode(",", $ship_gid_arr);
		if(empty($in)){$in = '0';}
		$sql = 'SELECT goods_id FROM '.$GLOBALS['ecs']->table('cart')." WHERE  `session_id` = '" . SESS_ID. "' AND goods_id in(".$in.")";
		$ship= $GLOBALS['db']->getOne($sql);
	}	
	return ($ship)? true: false;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:活动中购物车合法商品金额
 * ----------------------------------------------------------------------------------------------------------------------
 */
function yi_cart_at_amount()
{
	$not_in = "4,5,29,65,134,154";
	$sql    = "SELECT IFNULL(sum(c.goods_price*c.goods_number),0) amount FROM ".$GLOBALS['ecs']->table('cart')." as c left join ecs_goods as g on c.goods_id=g.goods_id WHERE c.session_id='".SESS_ID."' and g.cat_id not in(".$not_in.") and c.is_cx=0;";
	return $GLOBALS['db']->getOne($sql);
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:活动中购物车框架眼镜金额
 * ----------------------------------------------------------------------------------------------------------------------
 */
function yi_cart_kj_amount()
{
	$sql = "SELECT IFNULL(sum(c.goods_price*c.goods_number),0) amount FROM ".$GLOBALS['ecs']->table('cart')." as c left join ecs_goods as g on c.goods_id=g.goods_id WHERE c.session_id='".SESS_ID."' and g.goods_type=15 and c.is_cx=0;";
	return $GLOBALS['db']->getOne($sql);
}



/**
 * 修改订单
 * @param   int     $order_id   订单id
 * @param   array   $order      key => value
 * @return  bool
 */
function update_order($order_id, $order)
{
    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'),
        $order, 'UPDATE', "order_id = '$order_id'");
}

/**
 * 得到新订单号
 * @return  string
 */
function get_order_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 取得购物车商品
 * @param   int     $type：商品类型（默认普通商品）
 * @return  array   购物车商品数组
 */
function cart_goods($type = CART_GENERAL_GOODS)
{
	//yi：修改购物车中商品排序
	//xyz:20130110 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql = "SELECT *, goods_price * goods_number AS subtotal FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type='$type' order by rec_id asc";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "SELECT *, goods_price * goods_number AS subtotal FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type='$type' order by rec_id asc";
		} else {
			$sql = "SELECT *, goods_price * goods_number AS subtotal FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type='$type' order by rec_id asc";
		}
	}
    $arr = $GLOBALS['db']->getAll($sql);	

    //格式化价格及礼包商品
    foreach ($arr as $key => $value)
    {
		$arr[$key]['eye_id'] = $GLOBALS['db']->getOne("select eye_id from ecs_goods where goods_id=".$value['goods_id']." limit 1;");
        $arr[$key]['formated_market_price'] = price_format($value['market_price'], false);
        $arr[$key]['formated_goods_price']  = price_format($value['goods_price'], false);      
 		$arr[$key]['formated_subtotal']     = price_format($value['subtotal'], false);
        if($value['extension_code'] == 'package_buy')
        {
            $arr[$key]['package_goods_list'] = get_package_goods($value['goods_id']);
        }
    }
    return $arr;
}

/* -------------------------------------------------------------------------------------------------
 * 函数yi 购物车中商品总数量
 * -------------------------------------------------------------------------------------------------
 * return num; 购物车中总商品数量
 */
function cart_goods_total_num($type = CART_GENERAL_GOODS)
{
	$num = 0;
	//xyz:20130110 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql =  "select goods_number FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND rec_type = '$type'";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql =  "select goods_number FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '$type'";
		} else {
			$sql =  "select goods_number FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '" . SESS_ID . "' AND rec_type = '$type'";
		}
	}
    $arr = $GLOBALS['db']->getAll($sql);	
    foreach($arr as $key => $value)
    {        
		$num = $num + $value['goods_number'];
    }
    return $num;
}

/* -------------------------------------------------------------------------------------------------
 * 函数yi 购物车中商品总重量统计
 * -------------------------------------------------------------------------------------------------
 * type:购物车中商品类型，暂时未用。
 * @return 购物车所有商品总重量。
 */
function cart_goods_total_weight($type = CART_GENERAL_GOODS)
{
	//xyz:20130110 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql = "select c.goods_number, g.goods_weight from ".$GLOBALS['ecs']->table('cart')." as c left join ".$GLOBALS['ecs']->table('goods')." as g on c.goods_id=g.goods_id WHERE user_id = '".$_SESSION['user_id']."' AND c.rec_type = '$type' ";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "select c.goods_number, g.goods_weight from ".$GLOBALS['ecs']->table('cart')." as c left join ".$GLOBALS['ecs']->table('goods')." as g on c.goods_id=g.goods_id WHERE c.user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND c.rec_type = '$type' ";
		} else {
			$sql = "select c.goods_number, g.goods_weight from ".$GLOBALS['ecs']->table('cart')." as c left join ".$GLOBALS['ecs']->table('goods')." as g on c.goods_id=g.goods_id WHERE session_id = '".SESS_ID."' AND c.rec_type = '$type' ";
		}
	}
    $arr = $GLOBALS['db']->getAll($sql);	
	$goods_weight = 0;
    foreach($arr as $key => $value)
    {        
		$goods_weight += $arr[$key]['goods_weight']*$arr[$key]['goods_number'];
    }
    return $goods_weight;
}

/**
 * 取得购物车总金额
 * @params  boolean $include_gift   是否包括赠品
 * @param   int     $type           类型：默认普通商品
 * @return  float   购物车总金额
 */
function cart_amount($include_gift = true, $type = CART_GENERAL_GOODS)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            " FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '$type' ";

    if (!$include_gift)
    {
        $sql .= ' AND is_gift = 0 AND goods_id > 0';
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 检查某商品是否已经存在于购物车
 *
 * @access  public
 * @param   integer     $id
 * @param   array       $spec
 * @param   int         $type   类型：默认普通商品
 * @return  boolean
 */
function cart_goods_exists($id, $spec, $type = CART_GENERAL_GOODS)
{
    /* 检查该商品是否已经存在在购物车中 */
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('cart').
            "WHERE session_id = '" .SESS_ID. "' AND goods_id = '$id' ".
            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
            "AND rec_type = '$type'
             AND shop_id = 2";

    return ($GLOBALS['db']->getOne($sql) > 0);
}

/**
 * 获得购物车中商品的总重量、总价格、总数量（并不是指礼包）
 * @access  public
 * @param   int     $type   类型：默认普通商品
 * @return  array
 */
function cart_weight_price($type = CART_GENERAL_GOODS)
{
    $package_row['weight'] = 0;
    $package_row['amount'] = 0;
    $package_row['number'] = 0;
    $packages_row['free_shipping'] = 1;

    /*------------------------------------------------计算超值礼包内商品的相关配送参数------------------------------------------------*/
    //xu:2013.09.13注释下面部分
    /*$sql = 'SELECT goods_id, goods_number, goods_price FROM ' . $GLOBALS['ecs']->table('cart') . " WHERE extension_code = 'package_buy' AND session_id ='".SESS_ID."'";
    $row = $GLOBALS['db']->getAll($sql);

    if(!empty($row))
    {
        $packages_row['free_shipping'] = 0;
        $free_shipping_count = 0;

        foreach($row as $val)
        {
            //如果商品全为免运费商品，设置一个标识变量
            $sql = 'SELECT count(*) FROM '.$GLOBALS['ecs']->table('package_goods') . ' AS pg, '.$GLOBALS['ecs']->table('goods') . ' AS g ' .
                   "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";
            $shipping_count = $GLOBALS['db']->getOne($sql);

            if($shipping_count > 0)
            {
                //循环计算每个超值礼包商品的重量和数量，注意一个礼包中可能包换若干个同一商品
                $sql = 'SELECT SUM(g.goods_weight * pg.goods_number) AS weight, ' .
                    'SUM(pg.goods_number) AS number FROM ' .
                    $GLOBALS['ecs']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['ecs']->table('goods') . ' AS g ' .
                    "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '". $val['goods_id'] . "'";

                $goods_row = $GLOBALS['db']->getRow($sql);
                $package_row['weight'] += floatval($goods_row['weight']) * $val['goods_number'];
                $package_row['amount'] += floatval($val['goods_price']) * $val['goods_number'];
                $package_row['number'] += intval($goods_row['number']) * $val['goods_number'];
            }
            else
            {
                $free_shipping_count++;
            }
        }
        $packages_row['free_shipping'] = $free_shipping_count == count($row) ? 1 : 0;
    }*/
	/*--------------------------------------------------------礼包商品数据处理end----------------------------------------------*/

    //===================获得购物车中商品的总重量 商品总金额 商品数量（非礼包）========================//
    $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, SUM(c.goods_price * c.goods_number) AS amount, SUM(c.goods_number) AS number '.
              'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c '.
              'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = c.goods_id '.
              "WHERE c.session_id = '" . SESS_ID . "' " .
              "AND c.rec_type = '$type' AND g.is_shipping = 0 ";
    $row = $GLOBALS['db']->getRow($sql);

    $packages_row['weight'] = floatval($row['weight']) + $package_row['weight'];//购物车商品总重量
    $packages_row['amount'] = floatval($row['amount']) + $package_row['amount'];
    $packages_row['number'] = intval($row['number'])   + $package_row['number'];    
    $packages_row['formated_weight'] = formated_weight($packages_row['weight']);//格式化

    return $packages_row;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:指定某个商品插入购物车
 * ----------------------------------------------------------------------------------------------------------------------
 */
function insert_cart($goods_id, $num=1, $is_gift=0, $price, $extension_code='')
{
	//取得商品信息
	$sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, ".
			"g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, ".
			"g.promote_end_date, g.goods_weight, g.integral, g.extension_code, ".
			"g.goods_number, g.is_alone_sale, g.is_shipping,".
			"IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
			" FROM " .$GLOBALS['ecs']->table('goods'). " AS g ".
			" LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
			" ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
			" WHERE g.goods_id = '$goods_id'" .
			" AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

	//初始化要插入购物车的基本件数据
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
		'goods_price'   => $price,
		'goods_number'  => $num,
		'parent_id'     => 0,
        'goods_attr'    => '',
        'goods_attr_id' => '',
        'is_real'       => $goods['is_real'],
		'can_handsel'   => 0,
        'extension_code'=> (!empty($extension_code))? trim($extension_code):$goods['extension_code'],
        'is_gift'       => $is_gift,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS,
		'zselect'       => '',
		'zcount'        => '',
		'yselect'       => '',
		'ycount'        => ''
    );	
	$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
	return $res;
}



/* ----------------------------------------------------------------------------------------------------------------------
 * 函数：添加商品到购物车【非常重要的函数】
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格
 * @param   integer $parent     基本件
 * @return  boolean             是否加入成功
 */
function addto_cart($goods_id=0, $num=1, $spec=array(), $parent=0, $zselect, $zcount, $yselect, $ycount, $goods_attr_cart='')
{
    $GLOBALS['err']->clean();

    $sql = "SELECT g.goods_name, g.goods_sn, b.b2b_is_on_sale, b.b2b_market_price, b.b2b_shop_price, b.is_wholesale, g.promote_price, ".
           "g.goods_weight, g.integral, g.extension_code, g.goods_number,  ".
           "IFNULL(mp.user_price, b.b2b_shop_price * '$_SESSION[discount]') AS b2b_shop_price ".
           " FROM ".$GLOBALS['ecs']->table('goods')." AS g ".
           " LEFT JOIN b2b_goods AS b ON g.goods_id = b.goods_id ".
           " LEFT JOIN ".$GLOBALS['ecs']->table('member_price')." AS mp ON mp.goods_id = g.goods_id AND mp.shop_id =2 AND mp.user_rank='$_SESSION[user_rank]' ".
           " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);//取得加入购物车的商品信息
    
    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    //判断商品是否正在销售
    if($goods['b2b_is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }
    
    //检查库存
    if($GLOBALS['_CFG']['use_storage'] == 1 && $num > $goods['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
        return false;
    }
    
    //判断是否设置了批发价格;批发商品rec_type=5;      
    $rec_type = $goods['is_wholesale'] == 1? 5:0;

	//---------------------------------------------------------加入购物车【功能】--------------------------------------------------------------------//
    //如果加入购物车商品数量大于0，作为基本件插入购物车中
    if($num > 0)
    {
        //检查该商品是否已经存在在购物车中
        $row = $GLOBALS['db']->getRow("SELECT *,sum(goods_number) AS num FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID.
        "' AND goods_id='$goods_id' AND parent_id=0 AND goods_attr='".get_goods_attr_info($spec).
        "' AND rec_type=".$rec_type." AND extension_code='$parent[extension_code]' 
         AND extension_id='$parent[extension_id]' AND is_gift=0 AND shop_id = 2");

        if($rec_type == 5){//批发价格计算
        
            $wholesale_arr = b2b_wholesale_info($goods_id);
            
            $num += $row['num'];
            
            foreach($wholesale_arr as $k =>$v)
            {
                if($num >= $v['quantity']){
                    $goods_price = $v['price'];
                }
            }
            if(!$goods_price){
                $goods_price = $wholesale_arr[0]['price'];
            }
            
        }else{//正常价格计算 
            $num += $row['num'];
            $goods_price            = $goods['b2b_shop_price'];
            
        }
    
        //初始化要插入购物车的基本件数据【非常重要】
        $parent = array(
            'user_id'       => $_SESSION['user_id'],
            'session_id'    => SESS_ID,
            'goods_id'      => $goods_id,
            'goods_sn'      => addslashes($goods['goods_sn']),
            'goods_name'    => addslashes($goods['goods_name']),
            'market_price'  => $goods['market_price'],
            'goods_attr'    => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real'       => $goods['is_real'],
            'extension_code'=> $goods['extension_code'],
    		'extension_id'  => isset($goods['extension_id'])? intval($goods['extension_id']): 0,
            'is_gift'       => 0,
    		'is_cx'         => $goods_is_cx,
            'is_shipping'   => $goods['is_shipping'],
            'rec_type'      => $rec_type
        );
        
		//--------------------------------购物车中已有此商品（如度数不同情况，则再插入一条记录）-------------------------------------------
        if($row['num'])
        {
			//----------------------------------1.没有度数情况--->只要更新商品数量------------------------------------------
			if(empty($row['zcount']))
			{
				//更新购物车商品数量
				
				if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
				{
					$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number='$num', " .
						   " goods_price='$goods_price', zselect='$zselect', zcount='$zcount' ".
						   " WHERE session_id='".SESS_ID."' AND goods_id='$goods_id' ".
						   " AND parent_id = 0 AND goods_attr = '".get_goods_attr_info($spec).
                           "' AND rec_type=".$rec_type.
                           " AND extension_code='$parent[extension_code]' and extension_id='$parent[extension_id]'
                            AND is_gift=0 AND shop_id = 2";
					$GLOBALS['db']->query($sql);
				}
				else
				{
				   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
				   return false;
				}
			}
			else
			{
				if($row['zselect']==$zselect && !empty($row['zcount']))
				{
					//---------------------------------------2.1两个都不为空且度数相等（更新数量）------------------------------------------
					$zcount = $zcount + $row['zcount'];

					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						//$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number = '$num', " .
							   " goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   " AND rec_type = ".$rec_type." AND extension_code='$parent[extension_code]' 
                               AND extension_id='$parent[extension_id]' AND is_gift=0 
                               AND zselect='".$zselect."' AND shop_id = 2 ";
                         
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}
				}
				else
				{
					//----------------------------------3.度数不同情况（度数不为空和其它的情况）-------------------------------------------
                    
                    
                    if($rec_type == 5){
                        /*
                            step1：针对于有度数的批发商品 ：查询本商品除本度数以外的度数数量
                            step2：减去这个数量
                            step3：插入新记录
                            step4：更新同商品不同度数的单价和总价(包括同度数不同记录)
                        */
                        $sql = "SELECT sum(goods_number) as num FROM ".$GLOBALS['ecs']->table('cart').
                        " WHERE session_id = '" .SESS_ID. "' AND goods_id = ".$goods_id." AND zselect !='".$zselect."'";
                        $decNum = $GLOBALS['db']->getOne($sql);
                        $num    = $num-$decNum;
                        print_r($num);die;
                        $sql = "UPDATE ".$GLOBALS['ecs']->table('cart') .
							   " SET goods_price = '$goods_price'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   " AND rec_type = ".$rec_type." AND extension_code='$parent[extension_code]' 
                               AND extension_id='$parent[extension_id]' AND is_gift=0 
                               AND zselect!='".$zselect."' AND shop_id = 2 ";
                         
						$GLOBALS['db']->query($sql);
                    }
                    
					$parent['goods_price']  = max($goods_price, 0);
					$parent['goods_number'] = $num;
					$parent['parent_id']    = 0;
					$parent['zselect']      = $zselect;
					$parent['zcount']       = $zcount;
                    $parent['rec_type']     = $rec_type;
                    $parent['shop_id']      = 2;	
            	
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
				}
			}
        }
        else //购物车没有此商品
        {
           // print_r(11);die;
			//$goods_price = get_final_price($goods_id, $num, true, $spec);
			$parent['goods_price']  = max($goods_price, 0);
			$parent['goods_number'] = $num;
			$parent['parent_id']    = 0;
			$parent['zselect']      = $zselect;
			$parent['zcount']       = $zcount;		
            $parent['rec_type']      = $rec_type;
            $parent['shop_id']      = 2;
            
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }

    return true;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:重写指定商品加入购物车【非常重要的函数】
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格属性
 * @param   integer $is_cx      是否促销
 * @param   integer $price      商品单价
 * @return  boolean             是否加入成功
 */
function addto_cart_user_define($goods_id=0, $num=1, $price=0, $spec=array(), $is_cx=0, $rec_type=0, $extension_code='', $extension_id=0, $zselect, $zcount, $yselect, $ycount)
{
	$sql = "select * from ecs_goods where goods_id='$goods_id' and is_on_sale=1 and is_alone_sale=1 and is_delete=0 and goods_number>0 limit 1";
	$goods = $GLOBALS['db']->getRow($sql);
	
    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;//该商品不存在。
    }
    
    //商品加入购物车的价格    
    if(empty($price) && !empty($goods['shop_price']))
    {
    	  $price = floatval($goods['shop_price']);  
    }

    //计算商品的促销价格
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info($spec);
    $goods_attr_id          = join(',', $spec);


    //初始化要插入购物车的基本件数据【非常重要】
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'extension_id'  => $extension_id,
		'is_cx'         => $is_cx,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS
    );    
    
    
    //在购物车中重新插入一个商品（这个商品不能再购物车中修改数量）
	//$goods_price = get_final_price($goods_id, $num, true, $spec);
	//$parent['rec_type']   = (!empty($rec_type))? intval($rec_type): $parent['rec_type'];	
	$parent['goods_price']  = max($price, 0);
	$parent['goods_number'] = $num;
	$parent['parent_id']    = 0;		
	$parent['extension_code'] = (!empty($extension_code))? trim($extension_code): $parent['extension_code'];	
	$parent['zselect']      = $zselect;
	$parent['zcount']       = $zcount;
	$parent['yselect']      = $yselect;
	$parent['ycount']       = $ycount;			

	$sql_res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT'); 
    return ($sql_res)? true: false;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:重写加入购物车【框架眼镜加入购物车】
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格属性
 * @param   integer $is_cx      是否促销
 * @param   integer $price      商品单价
 * @type    类型：1.镜架， 2.镜片。
 * @return  boolean             是否加入成功
 */
function addto_cart_kj($goods_id=0, $num=1, $price=0, $parent_id=0, $spec=array(), $is_cx=0, $rec_type=0, $extension_code='', $extension_id=0, $zselect, $zcount, $yselect, $ycount, $type=1, $kj_tongju='')
{
	$sql   = "select * from ".$GLOBALS['ecs']->table('goods')." where goods_id='$goods_id' and is_on_sale=1 and is_alone_sale=1 and is_delete=0 and goods_number>0 limit 1";
	$goods = $GLOBALS['db']->getRow($sql);
	
    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;//商品不存在。
    }
    
    //商品加入购物车的价格    
    if(empty($price) && !empty($goods['shop_price']))
    {
    	  $price = floatval($goods['shop_price']);  
    }

    //计算商品的促销价格
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info($spec);
    $goods_attr_id          = join(',', $spec);

	if($type==1 && $goods_id==1389)
	{
		$sql = "SELECT IFNULL(sum(goods_number),0) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND goods_id=1389 and extension_code='source_buy' limit 1;";
		if($GLOBALS['db']->getOne($sql) == 0)
		{
			$price = $goods_price;
			$goods['extension_code'] = 'source_buy';
			$extension_id            = 156;
		}
	}

	//框架有散光
	if($type == 2)
	{
		$goods_attr     = trim($extension_code);
		$extension_code = '';
	}

    //初始化要插入购物车的基本件数据【非常重要】
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'extension_id'  => $extension_id,
		'is_cx'         => $is_cx,
		'is_kj'         => 1,
		'ds_extention'  => $kj_tongju,
		'parent_id'     => $parent_id,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS
    );    
    
    
    //在购物车中重新插入一个商品（这个商品不能再购物车中修改数量）
	$parent['goods_price']  = max($price, 0);
	$parent['goods_number'] = $num;
	$parent['extension_code'] = (!empty($extension_code))? trim($extension_code): $parent['extension_code'];	
	$parent['zselect']      = $zselect;
	$parent['zcount']       = $zcount;
	$parent['yselect']      = $yselect;
	$parent['ycount']       = $ycount;			

	$sql_res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT'); 
    return ($sql_res)? true: false;
}

//【yi】再买功能实现
function addto_cart_buy($goods_id, $num = 1, $spec = array(), $parent = 0, $zselect, $zcount, $yselect, $ycount, $property = array())
{
    $GLOBALS['err']->clean();
    $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, g.promote_end_date, ".
           " g.goods_weight, g.integral, g.extension_code, g.goods_number, g.is_alone_sale, g.is_shipping, g.is_cx, ".
           " IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
           " FROM " .$GLOBALS['ecs']->table('goods'). " AS g ".
           " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
           " ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
           " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    /* 如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件 */
    if($parent > 0)
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('cart') ." WHERE goods_id='$parent' AND session_id='" . SESS_ID . "'";
        if ($GLOBALS['db']->getOne($sql) == 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['no_basic_goods'], ERR_NO_BASIC_GOODS);
            return false;
        }

        /* 检查该配件是否已经添加过了。 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('cart') .
                " WHERE goods_id = '$goods_id' AND parent_id='$parent' AND session_id='" . SESS_ID . "'";

        if ($GLOBALS['db']->getOne($sql) > 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['fitting_goods_exists'] , ERR_NOT_EXISTS);
            return false;
        }
    }

    /* 是否正在销售 */
    if($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    /* 不是配件时检查是否允许单独销售 */
    if(empty($parent) && $goods['is_alone_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);
        return false;
    }

    /* 检查库存 */
    if($GLOBALS['_CFG']['use_storage'] == 1 && $num > $goods['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
        return false;
    }

    /* 计算商品的促销价格 */
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info($spec);
    $goods_attr_id          = join(',', $spec);

	//yi:再买功能，添加商品属性等参数
	if(!empty($property['goods_attr']) && empty($goods_attr))
	{
		$goods_attr = trim($property['goods_attr']);
	}
	if(!empty($property['goods_attr_id']))
	{
		$goods_attr_id = $property['goods_attr_id'];
	}
	if(!empty($property['extension_code']))
	{
		$goods['extension_code'] = trim($property['extension_code']);
	}
	if(!empty($property['goods_sn']))
	{
		$goods['goods_sn'] = trim($property['goods_sn']);
	}
	$goods['is_real'] = isset($property['is_real'])? $property['is_real']:1;
	$goods['is_gift'] = !empty($property['is_gift'])? $property['is_gift']:0;

	//yi:促销商品的判断
	$goods_is_cx = ($goods['promote_price']>0 && $goods['promote_start_date']<$_SERVER['REQUEST_TIME'] && $goods['promote_end_date']>$_SERVER['REQUEST_TIME'])? 1:$goods['is_cx'];
	$goods_type  = $GLOBALS['db']->getOne("select goods_type from ecs_goods where goods_id=".$goods_id);
	if($goods_id>1392 && $goods_id<1399){ $goods_type = 15; }
	$goods['is_kj'] = ( 15 != $goods_type)? 0: 1;

    //【yi】初始基本件数据
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'is_gift'       => $goods['is_gift'],
		'is_cx'         => $goods_is_cx,
        'is_shipping'   => $goods['is_shipping'],
		'is_kj'         => $goods['is_kj'],
		'ds_extention'  => $property['ds_extention'],
        'rec_type'      => CART_GENERAL_GOODS
    );

    /* 取得该商品的基本件和该商品作为其配件的价格（条件是价格低） */
    $basic_list = array();
    $sql =  "SELECT parent_id, goods_price FROM " . $GLOBALS['ecs']->table('group_goods') .
            " WHERE goods_id = '$goods_id'" .
            " AND goods_price < '$goods_price'" .
            " ORDER BY goods_price";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $basic_list[$row['parent_id']] = $row['goods_price'];
    }

    /* 取得购物车中该商品每个基本件的数量 */
    $basic_count_list = array();
    if ($basic_list)
    {
        $sql = "SELECT goods_id, SUM(goods_number) AS count " .
                "FROM " . $GLOBALS['ecs']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND parent_id = 0" .
                " AND goods_id " . db_create_in(array_keys($basic_list)) .
                " GROUP BY goods_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['goods_id']] = $row['count'];
        }
    }
    /* 取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件 */
    if ($basic_count_list)
    {
        $sql = "SELECT parent_id, SUM(goods_number) AS count " .
                "FROM " . $GLOBALS['ecs']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND goods_id = '$goods_id'" .
                " AND parent_id " . db_create_in(array_keys($basic_count_list)) .
                " GROUP BY parent_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['parent_id']] -= $row['count'];
        }
    }

    /* 循环插入配件 */
    foreach ($basic_list as $parent_id => $fitting_price)
    {
        /* 如果已全部插入，退出 */
        if ($num <= 0)
        {
            break;
        }

        /* 如果该基本件不再购物车中，执行下一个 */
        if (!isset($basic_count_list[$parent_id]))
        {
            continue;
        }

        /* 如果该基本件的配件数量已满，执行下一个基本件 */
        if ($basic_count_list[$parent_id] <= 0)
        {
            continue;
        }

        /* 作为该基本件的配件插入 */
        $parent['goods_price']  = max($fitting_price, 0) + $spec_price;
        $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
        $parent['parent_id']    = $parent_id;
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');

        /* 改变数量 */
        $num -= $parent['goods_number'];
    }
	//---------------------------------------------------------加入购物车--------------------------------------------------------------------
    /* 如果数量不为0，作为基本件插入 */
    if ($num > 0)
    {
         /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
                "AND rec_type = 'CART_GENERAL_GOODS'";
        $row = $GLOBALS['db']->getRow($sql);

		//--------------------------------购物车中有此商品---度数不同情况，是再插入一条记录-------------------------------------------
        if($row)
        {		
			//----------------------------------1.没有度数情况--->就只要更新数量------------------------------------------
			if( empty($row['zcount']) && empty($row['ycount']) ){    //0,'',null
				//更新购物车商品数量
				$num += $row['goods_number'];
				if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
				{
					$goods_price = get_final_price($goods_id, $num, true, $spec);
					$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
						   " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
						   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
						   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
						   "AND rec_type = 'CART_GENERAL_GOODS'";
					$GLOBALS['db']->query($sql);
				}
				else
				{
				   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
				   return false;
				}
			}else{//---------------------------------------左右眼有一个不为空||两个都不为空---------------------------------------

				if(($row['zselect']==$zselect && $row['yselect']==$yselect) && !empty($row['zcount']) && !empty($row['ycount']) ){
					//---------------------------------------2.1两个都不为空且度数相等------------------------------------------------
					$num += $row['goods_number'];
					//左右眼数量累加
					$zcount = $zcount + $row['zcount'];
					$ycount = $ycount + $row['ycount'];

					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
							   " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   "AND rec_type = 'CART_GENERAL_GOODS' and zselect=".$zselect." and yselect=".$yselect." ";
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}
				}else if(!empty($zcount) && empty($ycount) && $row['zselect']==$zselect){//-----左眼不为空，右眼为空------
					$num += $row['goods_number'];
					//左眼数量累加
					$zcount = $zcount + $row['zcount'];
					
					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
							   " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   "AND rec_type = 'CART_GENERAL_GOODS' and zselect=".$zselect." ";
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}			

				}else if(!empty($ycount) && empty($zcount) && $row['yselect']==$yselect){//-----右眼不为空，左眼为空------
					$num += $row['goods_number'];
					//右眼数量累加
					$ycount = $ycount + $row['ycount'];
					
					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
							   " , goods_price = '$goods_price', yselect = '$yselect', ycount = '$ycount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   "AND rec_type = 'CART_GENERAL_GOODS' and yselect=".$yselect." ";
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}
				}else{
					//----------------------------------3.度数不同情况-----------------------------------------------------------
					$goods_price = get_final_price($goods_id, $num, true, $spec);
					$parent['goods_price']  = max($goods_price, 0);
					$parent['goods_number'] = $num;
					$parent['parent_id']    = 0;
					$parent['zselect']      = $zselect;
					$parent['zcount']       = $zcount;
					$parent['yselect']      = $yselect;
					$parent['ycount']       = $ycount;				
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
				}
			}
        }
        else //购物车没有此商品
        {
			$goods_price = get_final_price($goods_id, $num, true, $spec);
			$parent['goods_price']  = max($goods_price, 0);
			$parent['goods_number'] = $num;
			$parent['parent_id']    = 0;
			$parent['zselect']      = $zselect;
			$parent['zcount']       = $zcount;
			$parent['yselect']      = $yselect;
			$parent['ycount']       = $ycount;				
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }
    return true;
}

//散光片加入到购物车
function addto_cartsg($goods_id=0, $num=1, $spec=array(), $parent=0, $zselect, $zcount, $yselect, $ycount, $zsg, $ysg, $zzhou, $yzhou)
{
    $GLOBALS['err']->clean();
    $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, g.promote_end_date, ".
           " g.goods_weight, g.integral, g.extension_code, g.goods_number, g.is_alone_sale, g.is_shipping, g.is_cx, ".
           " IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
           " FROM ".$GLOBALS['ecs']->table('goods')." AS g ".
           " LEFT JOIN ".$GLOBALS['ecs']->table('member_price')." AS mp ".
           " ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
           " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    //如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件
    if($parent > 0)
    {
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('cart')." WHERE goods_id='$parent' AND session_id='" . SESS_ID . "'";
        if($GLOBALS['db']->getOne($sql) == 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['no_basic_goods'], ERR_NO_BASIC_GOODS);
            return false;
        }

        //检查该配件是否已经添加过了。
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('cart')." WHERE goods_id = '$goods_id' AND parent_id='$parent' AND session_id='" . SESS_ID . "'";
        if($GLOBALS['db']->getOne($sql) > 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['fitting_goods_exists'] , ERR_NOT_EXISTS);
            return false;
        }
    }

    //是否正在销售
    if($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    //不是配件时检查是否允许单独销售
    if(empty($parent) && $goods['is_alone_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);
        return false;
    }

    //检查库存
    if($GLOBALS['_CFG']['use_storage'] == 1 && $num > $goods['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
        return false;
    }

    //计算商品的促销价格
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info($spec);
    $goods_attr_id          = join(',', $spec);

	$sgds = '';
	if(isset($zsg) && isset($zzhou) && !empty($zzhou) && $zcount>0){
		$sgds .= '左眼散光:'.$zsg.'轴位:'.$zzhou;
	}
	if(isset($ysg) && isset($yzhou) && !empty($yzhou) && $ycount>0){
		$sgds .= '右眼散光:'.$ysg.'轴位:'.$yzhou;
	}

	//yi:促销商品的判断
	$goods_is_cx = ($goods['promote_price']>0 && $goods['promote_start_date']<$_SERVER['REQUEST_TIME'] && $goods['promote_end_date']>$_SERVER['REQUEST_TIME'])? 1:$goods['is_cx'];

    //初始化要插入购物车的基本件数据
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($sgds),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'is_gift'       => 0,
		'is_cx'         => $goods_is_cx,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS
    );

    //取得该商品的基本件和该商品作为其配件的价格（条件是价格低）
    $basic_list = array();
    $sql = "SELECT parent_id, goods_price FROM " . $GLOBALS['ecs']->table('group_goods') .
           " WHERE goods_id = '$goods_id'" .
           " AND goods_price < '$goods_price'" .
           " ORDER BY goods_price";
    $res = $GLOBALS['db']->query($sql);
    while($row = $GLOBALS['db']->fetchRow($res))
    {
        $basic_list[$row['parent_id']] = $row['goods_price'];
    }

    //取得购物车中该商品每个基本件的数量
    $basic_count_list = array();
    if($basic_list)
    {
        $sql = "SELECT goods_id, SUM(goods_number) AS count FROM " . $GLOBALS['ecs']->table('cart') .
               " WHERE session_id = '" . SESS_ID . "'" .
               " AND parent_id = 0" .
               " AND goods_id " . db_create_in(array_keys($basic_list)) .
               " GROUP BY goods_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['goods_id']] = $row['count'];
        }
    }

    //取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件
    if($basic_count_list)
    {
        $sql = "SELECT parent_id, SUM(goods_number) AS count FROM " . $GLOBALS['ecs']->table('cart') .
               " WHERE session_id = '" . SESS_ID . "'" .
               " AND goods_id = '$goods_id'" .
               " AND parent_id " . db_create_in(array_keys($basic_count_list)) .
               " GROUP BY parent_id";
        $res = $GLOBALS['db']->query($sql);
        while($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['parent_id']] -= $row['count'];
        }
    }

    //循环插入配件
    foreach ($basic_list as $parent_id => $fitting_price)
    {
        //如果已全部插入，退出
        if($num <= 0)
        {
            break;
        }

        //如果该基本件不再购物车中，执行下一个
        if (!isset($basic_count_list[$parent_id]))
        {
            continue;
        }

        //如果该基本件的配件数量已满，执行下一个基本件
        if ($basic_count_list[$parent_id] <= 0)
        {
            continue;
        }

        //作为该基本件的配件插入
        $parent['goods_price']  = max($fitting_price, 0) + $spec_price;
        $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
        $parent['parent_id']    = $parent_id;
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');

        //改变数量
        $num -= $parent['goods_number'];
    }
	//---------------------------------------------------------加入购物车--------------------------------------------------------------------
    /* 如果数量不为0，作为基本件插入 */
    if($num > 0)
    {
         /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('cart')." WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
               "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0";
        $row = $GLOBALS['db']->getRow($sql);

		//--------------------------------购物车中有此商品---度数不同情况，是再插入一条记录-------------------------------------------
        if($row)
        {		
			//----------------------------------1.没有度数情况--->就只要更新数量------------------------------------------
			if( empty($row['zcount']) && empty($row['ycount']) ){    //0,'',null
				//更新购物车商品数量
				$num += $row['goods_number'];
				if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
				{
					$goods_price = get_final_price($goods_id, $num, true, $spec);
					$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
						   " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
						   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
						   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
						   "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0";
					$GLOBALS['db']->query($sql);
				}
				else
				{
				   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
				   return false;
				}
			}else{//---------------------------------------左右眼有一个不为空||两个都不为空---------------------------------------

				if(($row['zselect']==$zselect && $row['yselect']==$yselect) && !empty($row['zcount']) && !empty($row['ycount']) ){
					//---------------------------------------2.1两个都不为空且度数相等------------------------------------------------
					$num += $row['goods_number'];
					//左右眼数量累加
					$zcount = $zcount + $row['zcount'];
					$ycount = $ycount + $row['ycount'];

					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
							   " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0 and zselect=".$zselect." and yselect=".$yselect." ";
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}
				}else if(!empty($zcount) && empty($ycount) && $row['zselect']==$zselect){//-----左眼不为空，右眼为空------
					$num += $row['goods_number'];
					//左眼数量累加
					$zcount = $zcount + $row['zcount'];
					
					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
							   " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0 and zselect=".$zselect." ";
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}			

				}else if(!empty($ycount) && empty($zcount) && $row['yselect']==$yselect){//-----右眼不为空，左眼为空------
					$num += $row['goods_number'];
					//右眼数量累加
					$ycount = $ycount + $row['ycount'];
					
					if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
					{
						$goods_price = get_final_price($goods_id, $num, true, $spec);
						$sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
							   " , goods_price = '$goods_price', yselect = '$yselect', ycount = '$ycount'".
							   " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
							   "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
							   "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0 and yselect=".$yselect." ";
						$GLOBALS['db']->query($sql);
					}
					else
					{
					   $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
					   return false;
					}
				}else{
					//----------------------------------3.度数不同情况-----------------------------------------------------------
					$goods_price = get_final_price($goods_id, $num, true, $spec);
					$parent['goods_price']  = max($goods_price, 0);
					$parent['goods_number'] = $num;
					$parent['parent_id']    = 0;
					$parent['zselect']      = $zselect;
					$parent['zcount']       = $zcount;
					$parent['yselect']      = $yselect;
					$parent['ycount']       = $ycount;	
                    $parent['shop_id']       = 2;				
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
				}
			}
        }
        else //购物车没有此商品
        {
			$goods_price = get_final_price($goods_id, $num, true, $spec);
			$parent['goods_price']  = max($goods_price, 0);
			$parent['goods_number'] = $num;
			$parent['parent_id']    = 0;
			$parent['zselect']      = $zselect;
			$parent['zcount']       = $zcount;
			$parent['yselect']      = $yselect;
			$parent['ycount']       = $ycount;	
            $parent['shop_id']       = 2;				
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }
    return true;
}

//---------------------------------------------------------加入购物车2--------------------------------------------------------------------------------------------
//----------------赠品加入购物车------------------
function addto_cart2($goods_id, $num = 1, $is_gift, $price)
{
    //取得要加入商品信息
    $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, g.promote_end_date, ".
           " g.goods_weight, g.integral, g.extension_code, g.goods_number, g.is_alone_sale, g.is_shipping, g.is_cx, ".
           " IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
           " FROM " .$GLOBALS['ecs']->table('goods'). " AS g ".
           " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
           " ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
           " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    //没在销售中
    if($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    //检查商品是否有库存
    if ($GLOBALS['_CFG']['use_storage'] == 1 && $num > $goods['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
        return false;
    }

	//---------------------------------------------------------赠品加入购物车--------------------------------------------------------------------
    if($num > 0)
    {
        //检查该商品是否已经存在在购物车中
        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND goods_id='$goods_id' AND parent_id=0 and is_gift=".$is_gift.
			   " and rec_type= 'CART_GENERAL_GOODS'";
        $row = $GLOBALS['db']->getRow($sql);
        if(empty($row))
        {
			$parent['goods_price']  = $price;
			$parent['goods_number'] = $num;
			$parent['parent_id']    = 0;
			$parent['is_gift']      = $is_gift;
			$parent['is_cx']        = 1;
			$parent['zselect']      = '';
			$parent['zcount']       = '';
			$parent['yselect']      = '';
			$parent['ycount']       = '';	
            $parent['shop_id']       = 2;				
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }
    return true;
}

/**
 * 清空购物车
 * @param   int     $type   类型：默认普通商品
 */
function clear_cart($type = CART_GENERAL_GOODS)
{
	//xyz edit(20130110) 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = '$type'";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '$type'";
		} else {
			$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type = '$type'";
		}
	}
    $GLOBALS['db']->query($sql);
}

/**
 * 获得指定的商品属性
 *
 * @access  public
 * @param   array   $arr
 * @return  string
 */
function get_goods_attr_info($arr)
{
    $attr   = '';

    if (!empty($arr))
    {
        $fmt = "%s:%s[%s] \n";

        $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price ".
                "FROM ".$GLOBALS['ecs']->table('goods_attr')." AS ga, ".
                    $GLOBALS['ecs']->table('attribute')." AS a ".
                "WHERE " .db_create_in($arr, 'ga.goods_attr_id')." AND a.attr_id = ga.attr_id";
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $attr_price = round(floatval($row['attr_price']), 2);
            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
        }

        $attr = str_replace('[0]', '', $attr);
    }

    return $attr;
}

/**
 * 取得用户信息
 * @param   int     $user_id    用户id
 * @return  array   用户信息
 */
function user_info($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('users') ." WHERE user_id='$user_id' limit 1;";
    $user = $GLOBALS['db']->getRow($sql);

    unset($user['question']);
    unset($user['answer']);

    /* 格式化帐户余额 */
    if ($user)
    {
//        if ($user['user_money'] < 0)
//        {
//            $user['user_money'] = 0;
//        }
        $user['formated_user_money'] = price_format($user['user_money'], false);
        $user['formated_frozen_money'] = price_format($user['frozen_money'], false);
    }

    return $user;
}

/**
 * 修改用户
 * @param   int     $user_id   订单id
 * @param   array   $user      key => value
 * @return  bool
 */
function update_user($user_id, $user)
{
    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'),
        $user, 'UPDATE', "user_id = '$user_id'");
}

/**
 * 取得用户地址列表
 * @param   int     $user_id    用户id
 * @return  array
 */
function address_list($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('user_address') .
            " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得用户地址信息
 * @param   int     $address_id     地址id
 * @return  array
 */
function address_info($address_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('user_address') .
            " WHERE address_id = '$address_id'";

    return $GLOBALS['db']->getRow($sql);
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:取得用户当前可用红包（通用红包除外）
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   int     $user_id        用户id
 * @param   float   $goods_amount   订单商品金额
 */
function user_bonus($user_id, $goods_amount = 0)
{
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
    $sql  = "SELECT t.type_id, t.type_name, t.type_money, b.bonus_id FROM ".$GLOBALS['ecs']->table('bonus_type')." AS t,".$GLOBALS['ecs']->table('user_bonus')." AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND t.use_start_date <= '$today' " .
            "AND t.use_end_date >= '$today' " .
            "AND t.min_goods_amount <= '$goods_amount' " .
            "AND b.user_id<>0 " .
            "AND b.user_id = '$user_id' " .
            "AND b.order_id = 0 and unlimit=0 ";		
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得红包信息
 * @param   int     $bonus_id   红包id唯一
 * @param   string  $bonus_sn   红包序列号唯一（通用红包除外）
 * @param   array   红包信息
 */
function bonus_info($bonus_id=0, $bonus_sn='')
{
    $sql = "SELECT t.*, b.* FROM ".$GLOBALS['ecs']->table('bonus_type')." AS t, ".$GLOBALS['ecs']->table('user_bonus')." AS b WHERE t.type_id = b.bonus_type_id ";
    if($bonus_id > 0)
    {
        $sql .= "AND b.bonus_id = '$bonus_id'";
    }
    else
    {
        $sql .= "AND b.bonus_sn = '$bonus_sn'";
    }
	$res    = $GLOBALS['db']->getRow($sql);
	$yi_ret = $res;

	if($res['send_type']==5 && $bonus_id==0)//如果是通用红包并且没有红包ID的情况。
	{
		$sql   .= " AND b.used_time=0 ";
		$yi_ret = $GLOBALS['db']->getRow($sql);
	}
    return $yi_ret;
}

/**
 * 检查红包是否已使用
 * @param   int $bonus_id   红包id
 * @return  bool
 */
function bonus_used($bonus_id)
{
    $sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('user_bonus') ." WHERE bonus_id = '$bonus_id'";
    return  $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 设置红包为已使用
 * @param   int     $bonus_id   红包id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function use_bonus($bonus_id=0, $order_id=0)
{
	use_unlimit_bonus_front($bonus_id);//通用红包使用前的操作

    $sql = "UPDATE ".$GLOBALS['ecs']->table('user_bonus')." SET order_id='$order_id', used_time='".gmtime()."' WHERE bonus_id='$bonus_id' LIMIT 1";
    return  $GLOBALS['db']->query($sql);
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:如果是通用红包。重新生成一个新的通用红包的拷贝，然后在使用这个红包。
 * ----------------------------------------------------------------------------------------------------------------------
 */
function use_unlimit_bonus_front($bonus_id=0)
{
	$bonus_info = $GLOBALS['db']->getRow("select * from ecs_user_bonus where bonus_id=".$bonus_id);
	if(!empty($bonus_info) && $bonus_info['unlimit'])
	{
		$bcount = "select count(bonus_id) from ecs_user_bonus where unlimit=1 AND bonus_type_id=".$bonus_info['bonus_type_id']." AND bonus_sn='".$bonus_info['bonus_sn'].
			      "' AND used_time=0 AND order_id=0";
		if($GLOBALS['db']->getOne($bcount)<2)
		{
			$b_ist = "insert into ecs_user_bonus(bonus_type_id, bonus_sn, unlimit) values(".$bonus_info['bonus_type_id'].", '".$bonus_info['bonus_sn']."', 1);";
			@mysql_query($b_ist);
		}
	}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:设置红包为未使用状态
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   int     $bonus_id   红包id
 * @param   int     $order_id   订单id
 */
function unuse_bonus($bonus_id=0)
{    
	//yi:如果是通用红包则user_id=0（线下红包在看看）
	$unlimit = $GLOBALS['db']->getOne("select unlimit from ecs_user_bonus where bonus_id=".$bonus_id." limit 1;");
	$sql_set = ($unlimit>0)? ", user_id=0 ": "";

	$sql      = "UPDATE ".$GLOBALS['ecs']->table('user_bonus')." SET order_id=0, used_time=0 ".$sql_set." WHERE bonus_id='$bonus_id' LIMIT 1";
    return  $GLOBALS['db']->query($sql);
}

/**
 * 计算积分的价值（能抵多少钱）
 * @param   int     $integral   积分
 * @return  float   积分价值
 */
function value_of_integral($integral)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);

    return $scale > 0 ? round(($integral / 100) * $scale, 2) : 0;
}

/**
 * 计算指定的金额需要多少积分
 *
 * @access  public
 * @param   integer $value  金额
 * @return  void
 */
function integral_of_value($value)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);

    return $scale > 0 ? round($value / $scale * 100) : 0;
}

/**
 * 订单退款
 * @param   array   $order          订单
 * @param   int     $refund_type    退款方式 1 到帐户余额 2 到退款申请（先到余额，再申请提款） 3 不处理
 * @param   string  $refund_note    退款说明
 * @param   float   $refund_amount  退款金额（如果为0，取订单已付款金额）
 * @return  bool
 */
function order_refund($order, $refund_type, $refund_note, $refund_amount = 0)
{
    /* 检查参数 */
    $user_id = $order['user_id'];
    if ($user_id == 0 && $refund_type == 1)
    {
        die('anonymous, cannot return to account balance');
    }

    $amount = $refund_amount > 0 ? $refund_amount : $order['money_paid'];
    if ($amount <= 0)
    {
        return true;
    }

    if (!in_array($refund_type, array(1, 2, 3)))
    {
        die('invalid params');
    }

    /* 备注信息 */
    if ($refund_note)
    {
        $change_desc = $refund_note;
    }
    else
    {
        include_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/admin/order.php');
        $change_desc = sprintf($GLOBALS['_LANG']['order_refund'], $order['order_sn']);
    }

    /* 处理退款 */
    if (1 == $refund_type)
    {
        log_account_change($user_id, $amount, 0, 0, 0, $change_desc);

        return true;
    }
    elseif (2 == $refund_type)
    {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0)
        {
            log_account_change($user_id, $amount, 0, 0, 0, $change_desc);
        }

        /* user_account 表增加提款申请记录 */
        $account = array(
            'user_id'      => $user_id,
            'amount'       => (-1) * $amount,
            'add_time'     => gmtime(),
            'user_note'    => $refund_note,
            'process_type' => SURPLUS_RETURN,
            'admin_user'   => $_SESSION['admin_name'],
            'admin_note'   => sprintf($GLOBALS['_LANG']['order_refund'], $order['order_sn']),
            'is_paid'      => 0
        );
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_account'), $account, 'INSERT');

        return true;
    }
    else
    {
        return true;
    }
}


/* -------------------------------------------------------------------------------------------------
 * 函数 获得购物车中的商品列表，购物车中的商品列表显示
 * -------------------------------------------------------------------------------------------------
 */
function get_cart_goods()
{
    $goods_list = array();
    $total = array(
        'goods_price'  => 0, // 本店售价合计（有格式）
        'market_price' => 0, // 市场售价合计（有格式）
        'saving'       => 0, // 节省金额（有格式）
        'save_rate'    => 0, // 节省百分比
        'goods_amount' => 0, // 本店售价合计（无格式）
    );
    //xyz edit(20130110) 保存购物车信息
    if ($_SESSION['user_id'] > 0) {
    	//登录用户
    	$sql =  "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
	            " FROM " . $GLOBALS['ecs']->table('cart') . " " .
	            " WHERE user_id = '" . $_SESSION['user_id'] . "'" .
	            " ORDER BY rec_id";
    } else {
    	if (isset($_COOKIE['cart_session_id'])) {
    		$sql =  "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
	            " FROM " . $GLOBALS['ecs']->table('cart') . " " .
	            " WHERE user_id <= 0 AND (session_id = '" . SESS_ID . "' OR session_id = '".$_COOKIE['cart_session_id']."') " .
	            " ORDER BY rec_id";
    	} else {
    		$sql =  "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
	            " FROM " . $GLOBALS['ecs']->table('cart') . " " .
	            " WHERE session_id = '" . SESS_ID . "' " .
	            " ORDER BY rec_id";
    	}
    }
	
	//yi：修改产品排序方式 old：ORDER BY pid, parent_id
    $res = $GLOBALS['db']->query($sql);

    /* 用于统计购物车中实体商品和虚拟商品的个数 */
    $virtual_goods_count = 0;
    $real_goods_count    = 0;

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
		//yi:商品是否有度数
		$row['eye_id'] = $GLOBALS['db']->getOne("select eye_id from ecs_goods where goods_id=".$row['goods_id']." limit 1;");

		//yi如果是积分兑换的商品 价格为0
		if( $row['extension_code'] == 'exchange'){
			$row['goods_price'] = 0;
		}
        $total['goods_price']  += $row['goods_price'] * $row['goods_number'];
        $total['market_price'] += $row['market_price'] * $row['goods_number'];

        $row['subtotal']     = price_format($row['goods_price'] * $row['goods_number'], false);
        $row['goods_price']  = price_format($row['goods_price'], false);
        $row['market_price'] = price_format($row['market_price'], false);

        //统计实体商品和虚拟商品的个数
        if($row['is_real'])
        {
            $real_goods_count++;
        }
        else
        {
            $virtual_goods_count++;
        }

        /* 查询规格 */
        if (trim($row['goods_attr']) != '')
        {
            $sql = "SELECT attr_value FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE goods_attr_id " .
            db_create_in($row['goods_attr_id']);
            $attr_list = $GLOBALS['db']->getCol($sql);
            foreach ($attr_list AS $attr)
            {
                $row['goods_name'] .= ' [' . $attr . '] ';
            }
        }

        //增加是否在购物车里显示商品图
        if(($GLOBALS['_CFG']['show_goods_in_cart'] == "2" || $GLOBALS['_CFG']['show_goods_in_cart'] == "3") && $row['extension_code'] != 'package_buy')
        {
			//yi:把商品的缩略图换成了100*100的图片
            $goods_thumb = $GLOBALS['db']->getOne("SELECT `goods_img` FROM " . $GLOBALS['ecs']->table('goods') . " WHERE `goods_id`=".$row['goods_id']);
            $row['goods_thumb'] = get_image_path($row['goods_id'], $goods_thumb, true);
        }
        if($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }        
		//xuyizhi:是否是秒杀商品(判断商品的促销时间段是否在当天的11点至12点)
		/*date_default_timezone_set('PRC'); 
		$b_time = mktime(11, 0, 0, date("m"), date("d"), date("Y"));
		$e_time = mktime(12, 0, 0, date("m"), date("d"), date("Y"));
		$is_miaosha = $GLOBALS['db']->getOne("SELECT `goods_id` FROM " . $GLOBALS['ecs']->table('goods') . " WHERE `goods_id`=".$row['goods_id']." AND is_promote=1 AND promote_start_date = $b_time AND promote_end_date = $e_time");
		$row['is_miaosha'] = $is_miaosha ? 1 : 0;*/
		        
        $goods_list[] = $row;
    }

    $total['goods_amount'] = $total['goods_price'];
    $total['saving']       = price_format($total['market_price'] - $total['goods_price'], false);
    if ($total['market_price'] > 0)
    {
        $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) *
        100 / $total['market_price']).'%' : 0;
    }

	$total['goods_pricex']  = $total['goods_price'];
	$total['goods_price']  = price_format($total['goods_price'], false);	
    $total['market_price'] = price_format($total['market_price'], false);
    $total['real_goods_count']    = $real_goods_count;
    $total['virtual_goods_count'] = $virtual_goods_count;

    return array('goods_list' => $goods_list, 'total' => $total);
}

/* =======================================================================================================================
 * yi:取得收货人信息
 * =======================================================================================================================
 */
function get_consignee($user_id=0)
{
    if(isset($_SESSION['flow_consignee']))
    {
        return $_SESSION['flow_consignee'];//session中的收货人信息
    }
    else
    {
        $arr = array();
        if($user_id > 0)
        {
            $sql = "SELECT ua.* FROM ".$GLOBALS['ecs']->table('user_address')."AS ua, ".$GLOBALS['ecs']->table('users').' AS u '.
                   " WHERE u.user_id='$user_id' AND ua.address_id = u.address_id";//默认地址
            $arr = $GLOBALS['db']->getRow($sql);
			if(empty($arr))
			{
				$arr = $GLOBALS['db']->getRow("select * from ecs_user_address where user_id=".$user_id." limit 1;");
			}
        }
        return $arr;
    }
}

/**
 * 查询购物车（订单id为0）或订单中是否有实体商品
 * @param   int     $order_id   订单id
 * @param   int     $flow_type  购物流程类型
 * @return  bool
 */
function exist_real_goods($order_id = 0, $flow_type = CART_GENERAL_GOODS)
{
    if ($order_id <= 0)
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "' AND is_real = 1 " .
                "AND rec_type = '$flow_type'";
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_goods') .
                " WHERE order_id = '$order_id' AND is_real = 1";
    }

    return $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 检查收货人信息是否完整
 * @param   array   $consignee  收货人信息
 * @param   int     $flow_type  购物流程类型
 * @return  bool    true 完整 false 不完整
 */
function check_consignee_info($consignee, $flow_type)
{
    if(exist_real_goods(0, $flow_type))
    {
        //如果存在实体商品
        $res = !empty($consignee['consignee']) &&
//             !empty($consignee['country']) &&
               !empty($consignee['email']) &&
               (!empty($consignee['tel'])||!empty($consignee['mobile']));
/*
        if($res)
        {
            if(empty($consignee['province']))
            {
                //没有设置省份，检查当前国家下面有没有设置省份
                $pro = get_regions(1, $consignee['country']);
                $res = empty($pro);
            }
            elseif (empty($consignee['city']))
            {
                //没有设置城市，检查当前省下面有没有城市
                $city = get_regions(2, $consignee['province']);
                $res = empty($city);
            }
            elseif(empty($consignee['district']))
            {
                $dist = get_regions(3, $consignee['city']);
                $res = empty($dist);
            }
        }
*/
        return $res;
    }
    else
    {
        //不存在实体商品的返回结果
        return !empty($consignee['consignee']) &&
               !empty($consignee['email']) &&
               !empty($consignee['tel']);
    }
}

//yi:获得配送方式的名称
function get_shipping_name($shipping_id){
	$sql = "select shipping_name from ".$GLOBALS['ecs']->table('shipping')." where shipping_id=".$shipping_id.";";
	return $GLOBALS['db']->GetOne($sql);
}

//yi:获得支付方式的名称
function get_pay_name($pay_id){
	$sql = "select pay_name from ".$GLOBALS['ecs']->table('payment')." where pay_id=".$pay_id.";";
	return $GLOBALS['db']->GetOne($sql);
}
/**
 * 获得上一次用户采用的支付和配送方式
 *
 * @access  public
 * @return  void
 */
function last_shipping_and_payment()
{
    $sql = "SELECT shipping_id, pay_id " .
            " FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE user_id = '$_SESSION[user_id]' " .
            " ORDER BY order_id DESC LIMIT 1";
    $row = $GLOBALS['db']->getRow($sql);

    if (empty($row))
    {
        /* 如果获得是一个空数组，则返回默认值 */
        $row = array('shipping_id' => 0, 'pay_id' => 0);
    }
    return $row;
}

/**
 * 取得当前用户应该得到的红包总额
 */
function get_total_bonus()
{
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    /* 按商品发的红包 */
    $sql = "SELECT SUM(c.goods_number * t.type_money)" .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, "
                    . $GLOBALS['ecs']->table('bonus_type') . " AS t, "
                    . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.session_id = '" . SESS_ID . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id = g.goods_id " .
            "AND g.bonus_type_id = t.type_id " .
            "AND t.send_type = '" . SEND_BY_GOODS . "' " .
            "AND t.send_start_date <= '$today' " .
            "AND t.send_end_date >= '$today' " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";
    $goods_total = floatval($GLOBALS['db']->getOne($sql));

    /* 取得购物车中非赠品总金额 */
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            " AND is_gift = 0 " .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $amount = floatval($GLOBALS['db']->getOne($sql));

    /* 按订单发的红包 */
    $sql = "SELECT FLOOR('$amount' / min_amount) * type_money " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') .
            " WHERE send_type = '" . SEND_BY_ORDER . "' " .
            " AND send_start_date <= '$today' " .
            "AND send_end_date >= '$today' " .
            "AND min_amount > 0 ";
    $order_total = floatval($GLOBALS['db']->getOne($sql));

    return $goods_total + $order_total;
}

/**
 * 处理红包（下订单时设为使用，取消（无效，退货）订单时设为未使用
 * @param   int     $bonus_id   红包编号
 * @param   int     $order_id   订单号
 * @param   int     $is_used    是否使用了
 */
function change_user_bonus($bonus_id, $order_id, $is_used = true)
{
	$send_unlimit = true;
    if ($is_used)
    {
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET ' .
                'used_time = ' . gmtime() . ', ' .
                "order_id = '$order_id' " .
                "WHERE bonus_id = '$bonus_id'";
    }
    else
    {
		//yi红包中漏洞修复：把红包设置为未使用状态

		$sqlb = "select bt.send_type from ".$GLOBALS['ecs']->table('user_bonus')." as ub, ".$GLOBALS['ecs']->table('bonus_type').
			    " as bt where ub.bonus_type_id = bt.type_id and ub.bonus_id = ".$bonus_id." ;";
		$send_type = $GLOBALS['db']->GetOne($sqlb);

		if( $send_type == 3){

			//如果是线下发放的红包，则跟用户id没有任何关系。
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET ' .
					'used_time = 0, ' .
					'order_id  = 0, ' .
					'user_id   = 0 ' .
					"WHERE bonus_id = '$bonus_id'";
		}
		elseif(5 == $send_type) //通用红包删除
		{
			$sql = "delete from ecs_user_bonus where bonus_id='$bonus_id' and order_id='$order_id'";
			$send_unlimit = false;
		}
		else
		{
			//按订单金额 按用户 按商品 这3种形式发放的红包。
			$sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_bonus') . ' SET ' .
					'used_time = 0, ' .
					'order_id  = 0 ' .
					"WHERE bonus_id = '$bonus_id'";
		}
    }
	if($send_unlimit)
	{
		use_unlimit_bonus_front($bonus_id);//通用红包使用前的操作
	}
    $GLOBALS['db']->query($sql);
}

/**
 * 获得订单信息
 *
 * @access  private
 * @return  array
 */
function flow_order_info()
{
    $order = isset($_SESSION['flow_order']) ? $_SESSION['flow_order'] : array();

    /* 初始化配送和支付方式 */
    if (!isset($order['shipping_id']) || !isset($order['pay_id']))
    {
        /* 如果还没有设置配送和支付 */
        if ($_SESSION['user_id'] > 0)
        {
            /* 用户已经登录了，则获得上次使用的配送和支付 */
            $arr = last_shipping_and_payment();

            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = $arr['shipping_id'];
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = $arr['pay_id'];
            }
        }
        else
        {
            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = 0;
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = 0;
            }
        }
    }

    if (!isset($order['pack_id']))
    {
        $order['pack_id'] = 0;  // 初始化包装
    }
    if (!isset($order['card_id']))
    {
        $order['card_id'] = 0;  // 初始化贺卡
    }
    if (!isset($order['bonus']))
    {
        $order['bonus'] = 0;    // 初始化红包
    }
    if (!isset($order['integral']))
    {
        $order['integral'] = 0; // 初始化积分
    }
    if (!isset($order['surplus']))
    {
        $order['surplus'] = 0;  // 初始化余额
    }

    /* 扩展信息 */
    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }

    return $order;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:合并订单【功能】
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   string  $from_order_sn  从订单号
 * @param   string  $to_order_sn    主订单号
 * @return  成功返回true，失败返回错误信息
 */
function merge_order($from_order_sn, $to_order_sn)
{
    if(trim($from_order_sn) == '' || trim($to_order_sn) == '')
    {
        return $GLOBALS['_LANG']['order_sn_not_null'];//检查订单号
    }
    if ($from_order_sn == $to_order_sn)
    {
        return $GLOBALS['_LANG']['two_order_sn_same'];
    }

	//取得订单信息 
    $from_order = order_info(0, $from_order_sn);
    $to_order   = order_info(0, $to_order_sn);

	//检查订单是否存在
    if(!$from_order)
    {
        return sprintf($GLOBALS['_LANG']['order_not_exist'], $from_order_sn);
    }
    elseif(!$to_order)
    {
        return sprintf($GLOBALS['_LANG']['order_not_exist'], $to_order_sn);
    }

    //检查合并的订单是否为普通订单，非普通订单不允许合并。
    if ($from_order['extension_code'] != '' || $to_order['extension_code'] != 0)
    {
        return $GLOBALS['_LANG']['merge_invalid_order'];
    }

	//yi:设定特殊条件的订单不能进行合并
	/*
	if($from_order['extension_code'] == 'exchange' || $to_order['extension_code'] == 'exchange')
	{
		return "很抱歉，含有积分兑换商品的订单，不能进行合并！";
	}*/


    //检查订单状态是否是（已确认或未确认）、（未付款）、（未发货）
    if($from_order['order_status'] != OS_UNCONFIRMED && $from_order['order_status'] != OS_CONFIRMED)
    {
        return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $from_order_sn);
    }
    elseif ($from_order['pay_status'] != PS_UNPAYED)
    {
        return sprintf($GLOBALS['_LANG']['ps_not_unpayed'], $from_order_sn);
    }
    elseif ($from_order['shipping_status'] != SS_UNSHIPPED)
    {
        return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $from_order_sn);
    }

    if ($to_order['order_status'] != OS_UNCONFIRMED && $to_order['order_status'] != OS_CONFIRMED)
    {
        return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $to_order_sn);
    }
    elseif ($to_order['pay_status'] != PS_UNPAYED)
    {
        return sprintf($GLOBALS['_LANG']['ps_not_unpayed'], $to_order_sn);
    }
    elseif ($to_order['shipping_status'] != SS_UNSHIPPED)
    {
        return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $to_order_sn);
    }

    //检查订单用户是否相同
    if ($from_order['user_id'] != $to_order['user_id'])
    {
        return $GLOBALS['_LANG']['order_user_not_same'];
    }

    //合并订单
    $order = $to_order; //主定单
    $order['order_id']  = '';
    $order['add_time']  = gmtime();

    //合并商品总额
    $order['goods_amount'] += $from_order['goods_amount'];

    //合并折扣
    $order['discount'] += $from_order['discount'];

	//重新计算配送费用
    if($order['shipping_id'] > 0)
    {        
        $weight_price       = order_weight_price($to_order['order_id']);
        $from_weight_price  = order_weight_price($from_order['order_id']);
        $weight_price['weight'] += $from_weight_price['weight'];
        $weight_price['amount'] += $from_weight_price['amount'];
        $weight_price['number'] += $from_weight_price['number'];

        $region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
        $shipping_area  = shipping_area_info($order['shipping_id'], $region_id_list);

        $order['shipping_fee'] = shipping_fee($shipping_area['shipping_code'],
            unserialize($shipping_area['configure']), $weight_price['weight'], $weight_price['amount'], $weight_price['number']);

        //如果保价了，重新计算保价费
        if($order['insure_fee'] > 0)
        {
            $order['insure_fee'] = shipping_insure_fee($shipping_area['shipping_code'], $order['goods_amount'], $shipping_area['insure']);
        }
    }

    // 重新计算包装费、贺卡费
    if($order['pack_id'] > 0)
    {
        $pack = pack_info($order['pack_id']);
        $order['pack_fee'] = $pack['free_money'] > $order['goods_amount'] ? $pack['pack_fee'] : 0;
    }
    if ($order['card_id'] > 0)
    {
        $card = card_info($order['card_id']);
        $order['card_fee'] = $card['free_money'] > $order['goods_amount'] ? $card['card_fee'] : 0;
    }

    //红包不变，合并积分、余额、已付款金额
    $order['integral']      += $from_order['integral'];
    //$order['integral_money'] = value_of_integral($order['integral']);
	$order['integral_money'] = 0;

    $order['surplus']       += $from_order['surplus'];
    $order['money_paid']    += $from_order['money_paid'];

    //计算应付款金额（不包括支付费用）
    $order['order_amount'] = $order['goods_amount'] - $order['discount']
                           + $order['shipping_fee']
                           + $order['insure_fee']
                           + $order['pack_fee']
                           + $order['card_fee']
                           - $order['bonus']
                           - $order['integral_money']
                           - $order['surplus']
                           - $order['money_paid'];

    //重新计算支付费
    if($order['pay_id'] > 0)
    {
        //货到付款手续费
        $cod_fee          = $shipping_area ? $shipping_area['pay_fee'] : 0;
        $order['pay_fee'] = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);

        //应付款金额加上支付费
        $order['order_amount'] += $order['pay_fee'];
    }

    /* 插入订单表 */
    do
    {
        $order['order_sn'] = get_order_sn();
        if($GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), addslashes_deep($order), 'INSERT'))
        {
            break;
        }
        else
        {
            if ($GLOBALS['db']->errno() != 1062)
            {
                die($GLOBALS['db']->errorMsg());
            }
        }
    }
    while (true); // 防止订单号重复

    //合并之后的新订单号
    $order_id   = $GLOBALS['db']->insert_id();
	$sql_in_str = db_create_in(array($from_order['order_id'], $to_order['order_id']));

    //更新订单商品表
	$sql = "INSERT INTO ".$GLOBALS['ecs']->table('order_goods').
		"( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
		" SELECT '$order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention from ecs_order_goods WHERE order_id ".$sql_in_str;
	$GLOBALS['db']->query($sql);

    include_once(ROOT_PATH . 'includes/lib_clips.php');    
    insert_pay_log($order_id, $order['order_amount'], PAY_ORDER);//插入支付日志

	//yi:把原订单状态设置为取消。
	$GLOBALS['db']->query("update ecs_order_info set order_status=2 where order_id ".$sql_in_str." and pay_status=0 ");

    //yi:删除原订单支付日志
    $GLOBALS['db']->query("DELETE FROM ". $GLOBALS['ecs']->table('pay_log') ." WHERE order_id ".$sql_in_str);

	//yi:记录订单合并记录
	$sql = "insert into ecs_order_merge(new_order_id, new_order_sn, old_order_sn2, old_order_sn1, add_time, user_id) ".
		   "values('$order_id','".$order['order_sn']."','$from_order_sn','$to_order_sn',".$_SERVER['REQUEST_TIME'].",'$to_order[user_id]')";
	$GLOBALS['db']->query($sql);


    //返还 from_order 的红包，因为只使用主订单to_order 的红包
    if($from_order['bonus_id'] > 0)
    {
        unuse_bonus($from_order['bonus_id']);
    }

    return true;//合并订单成功
}

/**
 * 查询配送区域属于哪个办事处管辖
 * @param   array   $regions    配送区域（1、2、3、4级按顺序）
 * @return  int     办事处id，可能为0
 */
function get_agency_by_regions($regions)
{
    if (!is_array($regions) || empty($regions))
    {
        return 0;
    }

    $arr = array();
    $sql = "SELECT region_id, agency_id " .
            "FROM " . $GLOBALS['ecs']->table('region') .
            " WHERE region_id " . db_create_in($regions) .
            " AND region_id > 0 AND agency_id > 0";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['region_id']] = $row['agency_id'];
    }
    if (empty($arr))
    {
        return 0;
    }

    $agency_id = 0;
    for ($i = count($regions) - 1; $i >= 0; $i--)
    {
        if (isset($arr[$regions[$i]]))
        {
            return $arr[$regions[$i]];
        }
    }
}

/**
 * 获取配送插件的实例
 * @param   int   $shipping_id    配送插件ID
 * @return  object     配送插件对象实例
 */
function &get_shipping_object($shipping_id)
{
    $shipping  = shipping_info($shipping_id);
    if (!$shipping)
    {
        $object = new stdClass();
        return $object;
    }

    $file_path = ROOT_PATH.'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';

    include_once($file_path);

    $object = new $shipping['shipping_code'];
    return $object;
}

/**
 * 改变订单中商品库存
 * @param   int     $order_id   订单号
 * @param   bool    $is_dec     是否减少库存
 * @param   bool    $storage     减库存的时机，1，下订单时；0，发货时；
 */
function change_order_goods_storage($order_id, $is_dec = true, $storage = 0)
{
    /* 查询订单商品信息 */
    switch ($storage)
    {
        case 0 :
            $sql = "SELECT zselect,zcount,yselect,ycount,goods_id, SUM(send_number) AS num, max(extension_code) as extension_code FROM " . $GLOBALS['ecs']->table('order_goods') .
                    " WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id ";
        break;

        case 1 :
            $sql = "SELECT zselect,zcount,yselect,ycount,goods_id, SUM(goods_number) AS num, max(extension_code) as extension_code FROM " . $GLOBALS['ecs']->table('order_goods') .
                    " WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id ";
        break;
    }

    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
    if(($row['zselect']!='' && $row['zcount']!='') || ($row['yselect']!='' && $row['ycount']!='')){
    	
    	if ($is_dec)
	    {//减库存
	    	change_ds_goods_storage($row['goods_id'], $row['zselect'],  - $row['zcount']);
	    	change_ds_goods_storage($row['goods_id'], $row['yselect'],  - $row['ycount']);
	    }else{
	     //加库存
	     	change_ds_goods_storage($row['goods_id'], $row['zselect'],  - $row['zcount']);
	    	change_ds_goods_storage($row['goods_id'], $row['yselect'],  - $row['ycount']);
	    }
    	
    	
    	
    }else{
	    if ($row['extension_code'] != "package_buy")
	        {
	            if ($is_dec)
	            {
	                change_goods_storage($row['goods_id'], - $row['num']);
	            }
	            else
	            {
	                change_goods_storage($row['goods_id'], $row['num']);
	            }
	            $GLOBALS['db']->query($sql);
	        }
	        else
	        {
	            $sql = "SELECT goods_id, goods_number" .
	                   " FROM " . $GLOBALS['ecs']->table('package_goods') .
	                   " WHERE package_id = '" . $row['goods_id'] . "'";
	            $res_goods = $GLOBALS['db']->query($sql);
	            while ($row_goods = $GLOBALS['db']->fetchRow($res_goods))
	            {
	                $sql = "SELECT is_real" .
	                   " FROM " . $GLOBALS['ecs']->table('goods') .
	                   " WHERE goods_id = '" . $row_goods['goods_id'] . "'";
	                $real_goods = $GLOBALS['db']->query($sql);
	                $is_goods = $GLOBALS['db']->fetchRow($real_goods);
	
	                if ($is_dec)
	                {
	                    change_goods_storage($row_goods['goods_id'], - ($row['num'] * $row_goods['goods_number']));
	                }
	                elseif ($is_goods['is_real'])
	                {
	                    change_goods_storage($row_goods['goods_id'], ($row['num'] * $row_goods['goods_number']));
	                }
	            }
	        }
    }
    
    
        
    }
}

/**
 * 商品库存增与减
 * @param   int    $good_id    商品ID
 * @param   int    $number     增减数量，默认0；
 * @return  bool               true，成功；false，失败；
 */
function change_goods_storage($good_id, $number = 0)
{
    if (empty($good_id) || ($number == '' && $number !== 0))
    {
        return false;
    }

    if ($number == 0)
    {
        return true; // 值为0即不做、增减操作，返回true
    }

    $number = ($number > 0) ? '+ ' . $number : $number;
    $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') ."
            SET goods_number = goods_number $number
            WHERE goods_id = '$good_id'
            LIMIT 1";
    $query = $GLOBALS['db']->query($sql);
    if ($query)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 度数商品库存增与减
 * @param   int    $good_id    商品ID
 * @param   str    $ds		        度数	
 * @param   int    $number     增减数量，默认0；
 * @return  bool               true，成功；false，失败；
 */
function change_ds_goods_storage($good_id, $ds ,$number = 0)
{
    if (empty($good_id) || ($ds == '') || ($number == '' && $number !== 0))
    {
        return false;
    }

    if ($number == 0)
    {
        return true; // 值为0即不做、增减操作，返回true
    }
    $number = ($number > 0) ? '+ ' . $number : $number;
    
    $ds = strip_tags($ds);
    $ds = trim($ds);
    if($ds=='平光'){
    	$sqlStr = " AND val like '%".$ds."%'";
    }else{
    	$sqlStr = " AND val = ".$ds;
    }
    $sql = "UPDATE " . $GLOBALS['ecs']->table('ds') ." SET stock = stock ".$number." 
    WHERE gid = '".$good_id."'".$sqlStr;
    $query = $GLOBALS['db']->query($sql);
    
    if ($query)
    {
        return true;
    }
    else
    {
        return false;
    }
}
/**
 * 取得支付方式id列表
 * @param   bool    $is_cod 是否货到付款
 * @return  array
 */
function payment_id_list($is_cod)
{
    $sql = "SELECT pay_id FROM " . $GLOBALS['ecs']->table('payment');
    if ($is_cod)
    {
        $sql .= " WHERE is_cod = 1";
    }
    else
    {
        $sql .= " WHERE is_cod = 0";
    }

    return $GLOBALS['db']->getCol($sql);
}

/**
 * 生成查询订单的sql
 * @param   string  $type   类型
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_query_sql($type = 'finished', $alias = '')
{
    /* 已完成订单 */
    if ($type == 'finished')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
               " AND {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " ";
    }
    /* 待发货订单 */
    elseif ($type == 'await_ship')
    {
        return " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
               " AND   {$alias}shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_PART, SS_SHIPPED_ING)) .
               " AND ( {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(true)) . ") ";
    }
    /* 待付款订单 */
    elseif ($type == 'await_pay')
    {
        return " AND   {$alias}order_status = '" . OS_CONFIRMED . "'" .
               " AND   {$alias}pay_status = '" . PS_UNPAYED . "'" .
               " AND ( {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(false)) . ") ";
    }
    /* 未确认订单 */
    elseif ($type == 'unconfirmed')
    {
        return " AND {$alias}order_status = '" . OS_UNCONFIRMED . "' ";
    }
    /* 未处理订单：用户可操作 */
    elseif ($type == 'unprocessed')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
               " AND {$alias}shipping_status = '" . SS_UNSHIPPED . "'" .
               " AND {$alias}pay_status = '" . PS_UNPAYED . "' ";
    }
    /* 未付款未发货订单：管理员可操作 */
    elseif ($type == 'unpay_unship')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING)) .
               " AND {$alias}pay_status = '" . PS_UNPAYED . "' ";
    }
    /* 已发货订单：不论是否付款 */
    elseif ($type == 'shipped')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " ";
    }
    else
    {
        die('函数 order_query_sql 参数错误');
    }
}

/**
 * 生成查询订单总金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_amount_field($alias = '')
{
    return "   {$alias}goods_amount + {$alias}tax + {$alias}shipping_fee" .
           " + {$alias}insure_fee + {$alias}pay_fee + {$alias}pack_fee" .
           " + {$alias}card_fee ";
}

/**
 * 生成计算应付款金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_due_field($alias = '')
{
    return order_amount_field($alias) .
            " - {$alias}money_paid - {$alias}surplus - {$alias}integral_money" .
            " - {$alias}bonus - {$alias}discount ";
}

/**
 * 计算折扣：根据购物车和优惠活动
 * @return  float   折扣
 */
function compute_discount()
{
    /* 查询优惠活动 */
    $now = gmtime();
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    
    $sql = "SELECT *" .
            "FROM " . $GLOBALS['ecs']->table('favourable_activity') .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . db_create_in(array(FAT_DISCOUNT, FAT_PRICE)).
            " AND shop_id = 2";
    $favourable_list = $GLOBALS['db']->getAll($sql);
    
    if (!$favourable_list)
    {
        return 0;
    }
    /* 查询购物车商品 */
    $sql = "SELECT c.extension_id,c.goods_id, c.goods_price * c.goods_number AS subtotal, c.goods_number, c.extension_code, g.cat_id, g.brand_id " .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.parent_id = 0 " .
            "AND c.is_gift = 0 " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "' ".
            "AND c.shop_id = 2 ";
    $goods_list = $GLOBALS['db']->getAll($sql);
 
    if (!$goods_list)
    {
        return 0;
    }
    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = array();

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable)
    {
        $total_amount = 0;
        $total_number = 0; //xu:现金立减(多买多减)
        if ($favourable['act_range'] == FAR_ALL)
        {
            foreach ($goods_list as $goods)
            {
                $total_amount += $goods['subtotal'];
                if($goods['extension_code'] == 'source_buy' && !empty($goods['extension_id'])){//YI:专享价是否享优惠的判断
                    $enjoy_fav = enjoy_fav_source($goods['extension_id']);
                    if(!$enjoy_fav){
                        $total_amount = 0;
                    }
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_CATEGORY)
        {
            /* 找出分类id的子分类id */
            $id_list = array();
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id)
            {
                $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false && $goods['extension_code'] != 'package_buy' && $goods['extension_code'] != 'tuan_buy' && $goods['extension_code'] != 'miaosha_buy' && $goods['extension_code'] != 'exchange_buy' && $goods['extension_code'] != 'exchange')
                {
                    $total_amount += $goods['subtotal'];
                    
                    if($goods['extension_code'] == 'source_buy' && !empty($goods['extension_id'])){//YI:专享价是否享优惠的判断
                        $enjoy_fav = enjoy_fav_source($goods['extension_id']);
                        if(!$enjoy_fav){
                            $total_amount = 0;
                        }
                    }
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_BRAND)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false && $goods['extension_code'] != 'package_buy' && $goods['extension_code'] != 'tuan_buy' && $goods['extension_code'] != 'miaosha_buy' && $goods['extension_code'] != 'exchange_buy' && $goods['extension_code'] != 'exchange')
                {
                    $total_amount += $goods['subtotal'];
                    
                    if($goods['extension_code'] == 'source_buy' && !empty($goods['extension_id'])){//YI:专享价是否享优惠的判断
                        $enjoy_fav = enjoy_fav_source($goods['extension_id']);
                        if(!$enjoy_fav){
                            $total_amount = 0;
                        }
                    }
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_GOODS)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false && $goods['extension_code'] != 'package_buy' && $goods['extension_code'] != 'tuan_buy' && $goods['extension_code'] != 'miaosha_buy' && $goods['extension_code'] != 'exchange_buy' && $goods['extension_code'] != 'exchange')
                {
                    $total_amount += $goods['subtotal'];
                    
                    if($goods['extension_code'] == 'source_buy' && !empty($goods['extension_id'])){//YI:专享价是否享优惠的判断
                        $enjoy_fav = enjoy_fav_source($goods['extension_id']);
                        if(!$enjoy_fav){
                            $total_amount = 0;
                        }
                    }
                    
                    $total_number += $goods['goods_number']; //统计数量：现金立减(多买多减)
                }
            }
        }
        else
        {
            continue;
        }

        /* 如果金额满足条件，累计折扣 */
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0))
        {
            
            if ($favourable['act_type'] == FAT_DISCOUNT)
            {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);

                $favourable_name[] = $favourable['act_name'];
            }
            elseif ($favourable['act_type'] == FAT_PRICE)
            {
                //$discount += $favourable['act_type_ext'];
                if ($favourable['act_type_ext2'] > 0) //现金立减(多买多减)
                {
                	$discount += $favourable['act_type_ext'] * $total_number;
                }
                else 
                {
                	$discount += $favourable['act_type_ext'];
                }

                $favourable_name[] = $favourable['act_name'];
            }
        }
    }

    return array('discount' => $discount, 'name' => $favourable_name);
}

/**
 * 取得购物车该赠送的积分数
 * @return  int     积分数
 */
function get_give_integral()
{
        $sql = "SELECT SUM(c.goods_number * IF(g.give_integral > -1, g.give_integral, c.goods_price))" .
                "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " .
                          $GLOBALS['ecs']->table('goods') . " AS g " .
                "WHERE c.goods_id = g.goods_id " .
                "AND c.session_id = '" . SESS_ID . "' " .
                "AND c.goods_id > 0 " .
                "AND c.parent_id = 0 " .
                "AND c.rec_type = 0 " .
                "AND c.is_gift = 0";

        return intval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单应该赠送的积分数
 * @param   array   $order  订单
 * @return  int     积分数
 */
function integral_to_give($order)
{
    /* 判断是否团购 */
    if ($order['extension_code'] == 'group_buy')
    {
        include_once(ROOT_PATH . 'includes/lib_goods.php');
        $group_buy = group_buy_info(intval($order['extension_id']));

        return array('custom_points' => $group_buy['gift_integral'], 'rank_points' => $order['goods_amount']);
    }
    else
    {
        $sql = "SELECT SUM(og.goods_number * IF(g.give_integral > -1, g.give_integral, og.goods_price)) AS custom_points, SUM(og.goods_number * IF(g.rank_integral > -1, g.rank_integral, og.goods_price)) AS rank_points " .
                "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og, " .
                          $GLOBALS['ecs']->table('goods') . " AS g " .
                "WHERE og.goods_id = g.goods_id " .
                "AND og.order_id = '$order[order_id]' " .
                "AND og.goods_id > 0 " .
                //"AND og.parent_id = 0 " .
                "AND (og.parent_id = 0 OR og.goods_id IN (1393,1394,1395,1396,1397,1398)) " . //xu:13-09-09 框架的镜片也计算积分
                "AND og.is_gift = 0 ";//yi:修改礼包也给积分：AND og.extension_code != 'package_buy'

        return $GLOBALS['db']->getRow($sql);
    }
}

//20140506积分翻倍和几款特定团购商品5倍积分
function integral_to_give_20140506($order)
{
	$return_points = array('custom_points' => 0, 'rank_points' => 0);
	$custom_points = 0;
	$rank_points = 0;
	$tuan_buy_goods = array(2998,2999,3000,3001,223,221,225,2913,2914,2915,2916,2917,2918,2919,997,1895,3038,1069,3272);
	
    $order_goods = $GLOBALS['db']->getAll("SELECT rec_id, goods_id,extension_code FROM ecs_order_goods WHERE order_id='$order[order_id]'");
    
    foreach ($order_goods as $v)
    {
    	if (in_array($v['goods_id'], $tuan_buy_goods) && $v['extension_code'] == 'tuan_buy')
    	{
    		$sql = "SELECT SUM(og.goods_number * IF(g.give_integral > -1, g.give_integral, og.goods_price)) AS custom_points, SUM(og.goods_number * IF(g.rank_integral > -1, g.rank_integral, og.goods_price)) AS rank_points " .
                "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og, " .
                          $GLOBALS['ecs']->table('goods') . " AS g " .
                " WHERE og.goods_id = g.goods_id " .
                " AND og.rec_id = " . $v['rec_id'] . 
                " AND (og.parent_id = 0 OR og.goods_id IN (1393,1394,1395,1396,1397,1398)) " . 
                " AND og.is_gift = 0  LIMIT 1";

        	$row = $GLOBALS['db']->getRow($sql);
        	
        	$custom_points += $row['custom_points'] * 5;
        	$rank_points += $row['rank_points'] * 5;
    	}
    	else
    	{
    		$sql = "SELECT SUM(og.goods_number * IF(g.give_integral > -1, g.give_integral, og.goods_price)) AS custom_points, SUM(og.goods_number * IF(g.rank_integral > -1, g.rank_integral, og.goods_price)) AS rank_points " .
                "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og, " .
                          $GLOBALS['ecs']->table('goods') . " AS g " .
                " WHERE og.goods_id = g.goods_id " .
                " AND og.rec_id = " . $v['rec_id'] . 
                " AND (og.parent_id = 0 OR og.goods_id IN (1393,1394,1395,1396,1397,1398)) " . 
                " AND og.is_gift = 0  LIMIT 1";

        	$row = $GLOBALS['db']->getRow($sql);
        	
        	$custom_points += $row['custom_points'] * 2;
        	$rank_points += $row['rank_points'] * 2;
    	}
    }
    
    $return_points['custom_points'] = $custom_points;
    $return_points['rank_points'] = $rank_points;
    
    return $return_points;
}

/**
 * 发红包：发货时发红包
 * @param   int     $order_id   订单号
 * @return  bool
 */
function send_order_bonus($order_id)
{
    //取得订单应该发放的红包
	//yi:已修复一个订单中红包重复发放的BUG||
    $bonus_list = order_bonus($order_id);

    //如果有红包，统计并发送
    if($bonus_list)
    {
        //用户信息
        $sql = "SELECT u.user_id, u.user_name, u.email " .
                "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o, " .
                          $GLOBALS['ecs']->table('users') . " AS u " .
                "WHERE o.order_id = '$order_id' " .
                "AND o.user_id = u.user_id ";
        $user = $GLOBALS['db']->getRow($sql);

        //统计
        $count = 0;
        $money = '';
        foreach ($bonus_list AS $bonus)
        {
            $count += $bonus['number'];
            $money .= price_format($bonus['type_money']) . ' [' . $bonus['number'] . '], ';

            //修改用户红包
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('user_bonus') . " (bonus_type_id, user_id) " .
                    "VALUES('$bonus[type_id]', '$user[user_id]')";
            for($i=0; $i < $bonus['number']; $i++)
            {
				$res = mysql_query($sql);
                if(!$res)
                {
                    return $GLOBALS['db']->errorMsg();
                }
            }			
        }

        /* 如果有红包，发送邮件 */
        if ($count > 0)
        {
            $tpl = get_mail_template('send_bonus');
            $GLOBALS['smarty']->assign('user_name', $user['user_name']);
            $GLOBALS['smarty']->assign('count', $count);
            $GLOBALS['smarty']->assign('money', $money);
            $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
            $GLOBALS['smarty']->assign('send_date', local_date($GLOBALS['_CFG']['date_format']));
            $GLOBALS['smarty']->assign('sent_date', local_date($GLOBALS['_CFG']['date_format']));
            $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
            send_mail($user['user_name'], $user['email'], $tpl['template_subject'], $content, $tpl['is_html']);
        }
    }
    return true;
}

/**
 * 返回订单发放的红包
 * @param   int     $order_id   订单id
 */
function return_order_bonus($order_id)
{
    /* 取得订单应该发放的红包 */
    $bonus_list = order_bonus($order_id);

    /* 删除 */
    if ($bonus_list)
    {
        /* 取得订单信息 */
        $order = order_info($order_id);
        $user_id = $order['user_id'];

        foreach ($bonus_list AS $bonus)
        {
            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('user_bonus') .
                    " WHERE bonus_type_id = '$bonus[type_id]' " .
                    "AND user_id = '$user_id' " .
                    "AND order_id = '0' LIMIT " . $bonus['number'];
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 取得订单应该发放的红包
 * @param   int     $order_id   订单id
 * @return  array
 */
function order_bonus($order_id)
{
	//*=========================================修改一个订单多个商品发多个红包的BUG==============================================*//
    //第一种情况，查询按商品发的红包
    //$day    = getdate();
    //$today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

	//yi:修改红包发放时间参照下单时间
	if(!empty($order_id))
	{
		$order_time = $GLOBALS['db']->GetOne("select add_time from ecs_order_info where order_id=".$order_id." limit 1;");
	}
	$order_time = (!isset($order_time) || empty($order_time))? $_SERVER['REQUEST_TIME']: $order_time;

	//YI:修改SUM(o.goods_number) AS number=> 1 as number
    $sql = "SELECT b.type_id, b.type_money, b.min_amount, b.max_amount, 1 AS number " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS o, " .
                      $GLOBALS['ecs']->table('goods') . " AS g, " .
                      $GLOBALS['ecs']->table('bonus_type') . " AS b " .
            " WHERE o.order_id = '$order_id' " .
            " AND o.is_gift = 0 " .
            " AND o.goods_id = g.goods_id " .
            " AND g.bonus_type_id = b.type_id " .
            " AND b.send_type = '" . SEND_BY_GOODS . "' " .
            " AND b.send_start_date <= '$order_time' " .
            " AND b.send_end_date >= '$order_time' " .
            " GROUP BY b.type_id ";
    $list = $GLOBALS['db']->getAll($sql);

	$amount = order_amount($order_id, false);//定单中非赠品总金额
	
	//yi:按商品发放红包功能增加，有订单金额区间限制发放。
	foreach($list as $k=>$v)
	{
		if(!empty($v['min_amount']) || !empty($v['max_amount']))
		{
			$max = empty($v['max_amount'])? 99999: intval($v['max_amount']);
			if(($amount<intval($v['min_amount'])) || ($amount>=$max))
			{
				unset($list[$k]);//删除不在区间内的红包
			}
		}
	}
	//*=========================================修改一个订单多个商品发多个红包的BUG-END===========================================*//    

    /* 查询订单日期 */
    //$sql = "SELECT add_time FROM " . $GLOBALS['ecs']->table('order_info') ." WHERE order_id = '$order_id' LIMIT 1";
    //$order_time = $GLOBALS['db']->getOne($sql);
	

    /* 第三种情况 查询按订单金额发的红包 */

	//yi:修改红包发放的规则  在按照订单金额发放红包的过程当中增加按区间发放的功能。2011-6-7 yi：解决一个订单发多个红包的bug.
	//1.有区间的红包存在的情况 (包括一个或者多个这样的红包的存在) 分两种情况区别对待 当中又区别对待，只在那一个区间才会发一个红包。
	
	$max_amount = array();
	$sqlmax = "SELECT type_id, type_money, IFNULL(FLOOR(min_amount / min_amount), 1) AS number " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') .
            "WHERE send_type = '" . SEND_BY_ORDER . "' " .
            "AND send_start_date <= '$order_time' " .
            "AND send_end_date >= '$order_time' and max_amount > 0";
	$max_amount = $GLOBALS['db']->getAll($sqlmax);

	if(!empty($max_amount)){
		$sqlm = "SELECT type_id, type_money, IFNULL(FLOOR(min_amount / min_amount), 1) AS number " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') .
            "WHERE send_type = '" . SEND_BY_ORDER . "' " .
            "AND send_start_date <= '$order_time' " .
            "AND send_end_date >= '$order_time' and max_amount > 0 and '$amount' >= min_amount and '$amount' < max_amount";
		$list = array_merge($list, $GLOBALS['db']->getAll($sqlm));
	}


	//2.没有区间的红包存在的情况  这时的订单上限是0 可以发多个红包
    $sql = "SELECT type_id, type_money, IFNULL(FLOOR('$amount' / min_amount), 1) AS number " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') .
            "WHERE send_type = '" . SEND_BY_ORDER . "' " .
            "AND send_start_date <= '$order_time' " .
            "AND send_end_date >= '$order_time' and max_amount = 0";
    $list = array_merge($list, $GLOBALS['db']->getAll($sql));
    return $list;
}

/**
 * 计算购物车中的商品能享受红包支付的总额
 * @return  float   享受红包支付的总额
 */
function compute_discount_amount()
{
    /* 查询优惠活动 */
    $now = gmtime();
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $sql = "SELECT *" .
            "FROM " . $GLOBALS['ecs']->table('favourable_activity') .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . db_create_in(array(FAT_DISCOUNT, FAT_PRICE));
    $favourable_list = $GLOBALS['db']->getAll($sql);
    if (!$favourable_list)
    {
        return 0;
    }

    /* 查询购物车商品 */
    $sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id " .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.parent_id = 0 " .
            "AND c.is_gift = 0 " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $goods_list = $GLOBALS['db']->getAll($sql);
    if (!$goods_list)
    {
        return 0;
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = array();

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable)
    {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL)
        {
            foreach ($goods_list as $goods)
            {
                $total_amount += $goods['subtotal'];
            }
        }
        elseif ($favourable['act_range'] == FAR_CATEGORY)
        {
            /* 找出分类id的子分类id */
            $id_list = array();
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id)
            {
                $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_BRAND)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_GOODS)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        else
        {
            continue;
        }
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0))
        {
            if ($favourable['act_type'] == FAT_DISCOUNT)
            {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);
            }
            elseif ($favourable['act_type'] == FAT_PRICE)
            {
                $discount += $favourable['act_type_ext'];
            }
        }
    }


    return $discount;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:添加随心配商品到购物车【2012/8/17】
 * ----------------------------------------------------------------------------------------------------------------------
 * $at_goods(goods_id, goods_ds);
 */
function add_at_goods_to_cart($at_goods=array())
{
	if(empty($at_goods)){return false;}

	$at_price = 0;
	foreach($at_goods as $k => $p)
	{
		$row = $GLOBALS['db']->getRow("select market_price*2 as market_price from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$p['goods_id']." limit 1");
		$at_price += $row['market_price'];
	}	
	
	//把随心配商品加入购物车
	foreach($at_goods as $k => $v)
	{
		$row = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$v['goods_id']." limit 1");
		if($k==0)
		{
			//随心配第一个插入进去的商品（第一个商品标记为主商品，goods_sn=1, market_price用来存放礼包id）
			$gg = array(
				'user_id'       => $_SESSION['user_id'],
				'session_id'    => SESS_ID,
				'goods_id'      => $v['goods_id'],
				'goods_sn'      => '1',
				'goods_name'    => addslashes($row['goods_name']),
				'market_price'  => $at_price,
				'goods_price'   => 99,
				'goods_number'  => 1,
				'goods_attr'    => $v['goods_ds'],      //商品度数
				'is_real'       => $row['is_real'],
				'extension_code'=> 'package_buy',
				'goods_attr_id' => $row['goods_img'],
				'is_gift'       => 0,
				'is_cx'         => 1,
				'rec_type'      => CART_GENERAL_GOODS
			);
		}
		else
		{
			$gg = array(
				'user_id'       => $_SESSION['user_id'],
				'session_id'    => SESS_ID,
				'goods_id'      => $v['goods_id'],
				'goods_sn'      => '',
				'goods_name'    => addslashes($row['goods_name']),
				'market_price'  => '',
				'goods_price'   => '',
				'goods_number'  => 1,
				'goods_attr'    => $v['goods_ds'],  //商品度数
				'is_real'       => $row['is_real'],
				'extension_code'=> 'package_buy',
				'goods_attr_id' => $row['goods_img'],
				'is_gift'       => 0,
				'is_cx'         => 1,
				'rec_type'      => CART_GENERAL_GOODS
			);
		}
		//插入商品
		$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $gg, 'INSERT');
	}
	return true;
}

//-------------------------------------------添加礼包中的每一个产品到购物车中-----------------------------------------
/*$dd：礼包商品度数数组。*/
function add_package_to_cart($package_id, $num = 1, $dd)
{
    $GLOBALS['err']->clean();
    $package = get_package_info($package_id);
    if(empty($package))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    /* 是否正在销售 */
    if($package['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    /*现有库存是否还能凑齐一个礼包 */
    if(judge_package_stock($package_id))
    {
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], 1), ERR_OUT_OF_STOCK);
        return false;
    }

	//--------------------------------------------------把礼包中的每一个产品插入到购物车当中----------------------------------------

	//获得礼包中每一个商品的详细数据列表
	$good_idp = get_pack_gid($package_id);

	//遍历礼包中商品，把商品按照升序逐一插入购物车-------------------------------------------------------
	for($i=0; $i<count($good_idp); $i++)
	{
		if(!isset($dd[$i])){$dd[$i]='';}

		//插入数据-----------------------------------------------------------------------------------------
		if($i==0){
			//礼包中第一个商品（第一个商品标记为主商品，goods_sn=1,market_price用来存放礼包id）
			$gg = array(
				'user_id'       => $_SESSION['user_id'],
				'session_id'    => SESS_ID,
				'goods_id'      => $good_idp[$i]['goods_id'],
				'goods_sn'      => '1',
				'goods_name'    => addslashes($good_idp[$i]['goods_name']),
				'market_price'  => $package_id,
				'goods_price'   => $package['package_price'],
				'goods_number'  => $num,
				'goods_attr'    => $dd[$i],
				'is_real'       => $package['is_real'],
				'extension_code'=> 'package_buy',
				'extension_id'	=> $package_id,
				'goods_attr_id' => $good_idp[$i]['goods_img'],
				'is_gift'       => 0,
				//'is_cx'         => $good_idp[$i]['is_cx'], //xu:礼包标记is_cx=1
				'is_cx'         => 1,
				'rec_type'      => CART_GENERAL_GOODS
			);
		}else{
			$gg = array(
				'user_id'       => $_SESSION['user_id'],
				'session_id'    => SESS_ID,
				'goods_id'      => $good_idp[$i]['goods_id'],
				'goods_sn'      => '',
				'goods_name'    => addslashes($good_idp[$i]['goods_name']),
				'market_price'  => '',
				'goods_price'   => '',
				'goods_number'  => $num,
				'goods_attr'    => $dd[$i],
				'is_real'       => $package['is_real'],
				'extension_code'=> 'package_buy',
				'extension_id'	=> $package_id,
				'goods_attr_id' => $good_idp[$i]['goods_img'],
				'is_gift'       => 0,
				//'is_cx'         => $good_idp[$i]['is_cx'],
				'is_cx'         => 1,
				'rec_type'      => CART_GENERAL_GOODS
			);
		}
		//yi:增加礼包商品加入购物车中的数量
		$p_gnum = intval($good_idp[$i]['p_goods_number']);
		if($p_gnum>1 && empty($gg['goods_attr']))
		{
			$gg['goods_name'] = $gg['goods_name']."x".$p_gnum;
		}

		//插入商品
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $gg, 'INSERT');
	}

    //----------------------（原先）把礼包插入购物车的数据表中-------------------------
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $package_id,
        'goods_sn'      => '',
        'goods_name'    => addslashes($package['package_name']),
        'market_price'  => $package['market_package'],
        'goods_price'   => $package['package_price'],
        'goods_number'  => $num,
        'goods_attr'    => '',
        'goods_attr_id' => '',
        'is_real'       => $package['is_real'],
        'extension_code'=> 'package_buy',
        'is_gift'       => 0,
        'rec_type'      => CART_GENERAL_GOODS
    );
    /*if($num > 0) //xu:2013-09-02 取消
    {
        //检查该商品是否已经存在在购物车中 
        $sql = "SELECT goods_number FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '" . $package_id . "' ".
                " AND parent_id = 0 AND extension_code = 'package_buy' " .
                " AND rec_type = '" . CART_GENERAL_GOODS . "'";

        $row = $GLOBALS['db']->getRow($sql);

        if($row) //如果购物车已经有此物品，则更新大礼包数量
        {
            $num += $row['goods_number'];
            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num > 0)
            {
                $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '" . $num . "'" .
                       " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$package_id' ".
                       " AND parent_id = 0 AND extension_code = 'package_buy' " .
                       " AND rec_type = '" . CART_GENERAL_GOODS . "'";
                $GLOBALS['db']->query($sql);
            }
            else
            {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                return false;
            }
        }
        else 
        {
			//购物车没有此物品，则插入大礼包（原先的语句，现在已关闭）
            //$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }*/

    //把赠品删除
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);
    return true;
}
/**
 * 添加礼包到购物车---备用---
 * @access  public
 * @param   integer $package_id   礼包编号
 * @param   integer $num          礼包数量
 * @return  boolean
 */
function add_package_to_cart2($package_id, $num = 1)
{
    $GLOBALS['err']->clean();
    $package = get_package_info($package_id);
    if (empty($package))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    /* 是否正在销售 */
    if ($package['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    /* 现有库存是否还能凑齐一个礼包 */
    if (judge_package_stock($package_id))
    {
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], 1), ERR_OUT_OF_STOCK);
        return false;
    }

    /* 初始化要插入购物车的基本件数据 */
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $package_id,
        'goods_sn'      => '',
        'goods_name'    => addslashes($package['package_name']),
        'market_price'  => $package['market_package'],
        'goods_price'   => $package['package_price'],
        'goods_number'  => $num,
        'goods_attr'    => '',
        'goods_attr_id' => '',
        'is_real'       => $package['is_real'],
        'extension_code'=> 'package_buy',
        'is_gift'       => 0,
        'rec_type'      => CART_GENERAL_GOODS
    );

    /* 如果数量不为0，作为基本件插入 */
    if ($num > 0)
    {
         /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT goods_number FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '" . $package_id . "' ".
                " AND parent_id = 0 AND extension_code = 'package_buy' " .
                " AND rec_type = '" . CART_GENERAL_GOODS . "'";

        $row = $GLOBALS['db']->getRow($sql);

        if($row) //如果购物车已经有此物品，则更新大礼包数量
        {
            $num += $row['goods_number'];
            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num > 0)
            {
                $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '" . $num . "'" .
                       " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$package_id' ".
                       " AND parent_id = 0 AND extension_code = 'package_buy' " .
                       " AND rec_type = '" . CART_GENERAL_GOODS . "'";
                $GLOBALS['db']->query($sql);
            }
            else
            {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                return false;
            }
        }
        else //购物车没有此物品，则插入大礼包
        {
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }

    /* 把赠品删除 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);

    return true;
}

/**
 * 得到新发货单号
 * @return  string
 */
function get_delivery_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 检查礼包内商品的库存
 * @return  boolen
 */
function judge_package_stock($package_id, $package_num = 1)
{
    $sql = "SELECT g.goods_id FROM ".$GLOBALS['ecs']->table('goods')."AS g, " .
             $GLOBALS['ecs']->table('package_goods') . " AS pg " .
           " WHERE pg.goods_id = g.goods_id AND pg.goods_number * $package_num > g.goods_number AND pg.package_id = '" . $package_id . "'";

    $row = $GLOBALS['db']->getAll($sql);

    if (empty($row[0]))
    {
        return false;
    }
    else
    {
        return true;
    }
}

/*
 * 获得礼包中每个商品的id,按照goods_id大小升序：返回商品id数组【yi】
 * -------------------------------------------
 * pid:礼包id
 */
function get_pack_gid($pid){
	if(empty($pid)){return false;}

	$sql = "SELECT g.*,pg.goods_number as p_goods_number FROM ".$GLOBALS['ecs']->table('package_goods')." as pg left join ".$GLOBALS['ecs']->table('goods')." as g on pg.goods_id=g.goods_id where package_id='$pid' order by g.goods_id;";
	$res = $GLOBALS['db']->GetAll($sql);
	return $res;
}


/**
 * 专享价是否计算优惠活动
*/
function enjoy_fav_source($extension_id){
    $enjoy_fav = $GLOBALS['db']->getOne("SELECT enjoy_fav FROM ecs_source WHERE rec_id = ".$extension_id);
    if($enjoy_fav == 1){
        $total_amount = 1;
    }else{
        $total_amount = false;
    }
    return $total_amount;
}
?>