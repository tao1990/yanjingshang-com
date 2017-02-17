<?php
define('IN_ECS', true);
require_once(dirname(__FILE__) . '/../../includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$d = isset($_GET['d']) ? $_GET['d'] : '';		//下单时间 日期格式自定位yyyy-mm-dd
$d2 = isset($_GET['d2']) ? $_GET['d2'] : '';

//判断日期是否合法
if ($d && $d2) {
	$time_array = explode('-', $d);
	$time_array2 = explode('-', $d2);
	if (checkdate($time_array[1], $time_array[2], $time_array[0]) && checkdate($time_array2[1], $time_array2[2], $time_array2[0])) {
		//mktime(0,0,0,month,day,year);
		$st = mktime(0, 0, 0, $time_array[1], $time_array[2], $time_array[0]);
		$et = mktime(23, 59, 59, $time_array2[1], $time_array2[2], $time_array2[0]);
		if (!$st || !$et) {
			echo '您输入的日期格式不正确';
			exit;
		}
	} else {
		echo '您输入的日期格式不正确';
		exit;
	}
} else {
	echo '您输入的日期格式不正确';
	exit;
}

$all_orders = get_orders($st, $et, $cid);
//print_r($all_orders); exit;

echo '<table border="1">';
echo '<tr>';
echo '<th>网站ID</th>';
echo '<th>下单日期</th>';
echo '<th>订单号</th>';
echo '<th>商品明细(编号|名称|数量|单价|佣金种类|应付佣金)</th>';
echo '<th>订单状态</th>';
//echo '<th>付款状态</th>';
//echo '<th>付款方式</th>';
echo '<th>订单商品总数</th>';
echo '<th>订单金额</th>';
echo '<th>订单佣金总额</th>';
echo '</tr>';

if ($all_orders) {
	foreach ($all_orders as $key => $value) {
		$goods_num = 0;
		$total_money = 0;
		$commission = 0;
		
		echo '<tr>';
		
		foreach ($value as $k => $v) {
			//if ($k == 'cid') echo '<td>'.$v.'</td>';
			if ($k == 'wi') echo '<td>'.substr(base64_decode($v), 0, strpos(base64_decode($v), '|')).'</td>';
			if ($k == 'order_time') echo '<td>'.$v.'</td>';
			if ($k == 'order_no') echo '<td>'.$v.'</td>';
		
			if ($k == 'products_all') {
				echo '<td>';
				foreach ($v as $gid => $goods_info) {
					foreach ($goods_info as $k3 => $v3) {
						if ($k3 == 'product_id') echo $v3.'|';
						if ($k3 == 'product_name') echo xmlencode($v3).'|';
						if ($k3 == 'product_qty') {echo $v3.'|'; $goods_num += $v3; }
						if ($k3 == 'product_price') echo $v3.'|';
						if ($k3 == 'product_money') {$total_money += $v3;}
						if ($k3 == 'product_type') echo xmlencode($v3).'|';
						if ($k3 == 'product_commission') {echo $v3.'<br />'; $commission += $v3;}
					}
				}
				echo '</td>';
			}
			
			if ($k == 'order_status') {
				if ($v == 4) echo '<td>已支付</td>';
				elseif ($v == 1) echo '<td>新订单</td>';
				elseif ($v == -1) echo '<td>已取消</td>';
				elseif ($v == 2) echo '<td>已确认未发货</td>';
				else echo '<td>'.$v.'</td>';
			}
			//if ($k == 'pay_status') echo '<td>'.$v.'</td>';
			//if ($k == 'pay_name') echo '<td>'.$v.'</td>';
		}
		echo '<td>'.$goods_num.'</td>';
		echo '<td>'.$total_money.'</td>';
		echo '<td>'.$commission.'</td>';
		
		echo '</tr>';
	}
}

echo '</table>';


//获取时间段内的订单列表
function get_orders($st, $et, $cid) {
	if ($cid) $temp_cid = " AND zipcode LIKE '" . $cid . ",%' ";
	$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE referer='yiqifa' AND add_time > $st AND add_time < $et $temp_cid LIMIT 500";
		$order_rows = $GLOBALS['db'] -> getAll($query_sql);
		//print_r($order_rows);
		$orderlist = $order_ids = array();
		if($order_rows) {
			foreach ($order_rows as $pre_order) {
				$order_ids[] = $pre_order['order_id'];
				if($pre_order['pay_status'] == PS_PAYED) {
					$status = 4;
				}elseif($pre_order['shipping_status'] == SS_SHIPPED) {
					$status = 4;
				}elseif(in_array($pre_order['order_status'], array(OS_CONFIRMED,OS_SPLITED,OS_SPLITING_PART))) {
					$status = 2;
				}elseif(in_array($pre_order['order_status'], array(OS_RETURNED, OS_CANCELED, OS_INVALID))) {
					$status = -1;
				}else{
					$status = 1;
				}
				
				//可计算提成的订单总金额=商品总价-优惠劵、积分、活动优惠金额
				$total_price = $pre_order['goods_amount'] - $pre_order['discount'] - $pre_order['integral_money'] - $pre_order['bonus'];
				$goods_amount = $pre_order['goods_amount'];
				$favorable = sprintf("%.2f", $pre_order['discount'] + $pre_order['integral_money'] + $pre_order['bonus']);
				$temp_array = explode(',', $pre_order['zipcode']);
				if (count($temp_array) > 1) {
					$cid_pre = $temp_array['0'];
					$wi_pre = $temp_array['1'];
				} else {
					$cid_pre = '';
					$wi_pre = $temp_array['0'];
				}
				
				$orderlist[$pre_order['order_id']] = array (
						'cid'				=>	$cid_pre,										//在亿起发的活动ID
						'wi'				=>	$wi_pre,										//yiqifa下属网站ID
						'order_time'		=>	date("Y-m-d H:i:s", $pre_order['add_time']),	//下单时间
						'order_no'			=>	$pre_order['order_sn'],							//订单编号
						'products_all' 		=>	get_goods_infos($pre_order['order_id'], $goods_amount, $total_price), 		//订单的商品详情
						'order_status'		=>	$status,										//订单状态
						'pay_status'		=>	$pre_order['pay_status'],						//支付状态
						'pay_name'			=>	$pre_order['pay_name'],							//支付方式
						'fare'				=>	$pre_order['shipping_fee'],						//运费
						'favorable'			=>	$favorable,										//优惠金额
						'favorable_code'	=>	''												//优惠券,积分卡代码等
				);
				
			}
			return $orderlist;
		}
}

//根据订单ID获取订单商品信息
function get_goods_infos($order_id, $goods_amount, $total_price) {
	$sql = "SELECT * FROM  " . $GLOBALS['ecs']->table('order_goods') . 'WHERE order_id = ' . $order_id;
	$result = $GLOBALS['db'] -> getAll($sql);
	$goods_info_array = array();
	if($result) {
		//如果有优惠券或其他优惠折扣金额
		$coupon = 0;
		if (($goods_amount - $total_price) > 0) {
			$coupon = $goods_amount - $total_price;
		}
		foreach ($result as $goods_info) {
			//商品类别和分成比例
			$goods_cate_property = get_cateid($goods_info['goods_id'], $goods_info['extension_code'], $goods_info['is_cx']);
			
			//扣除优惠券
			if ($coupon > 0) {
				$new_goods_price = sprintf("%.2f", $goods_info['goods_price'] * (1-(1/$goods_amount)*$coupon));
			} else {
				$new_goods_price = $goods_info['goods_price'];
			}
			
			$goods_info_array[$goods_info['goods_id']] = array (
					'product_id'		=>	$goods_info['goods_id'],		//商品ID
					'product_name'		=>	$goods_info['goods_name'],		//商品名称
					'product_qty'		=>	$goods_info['goods_number'],	//商品数量					
					//'product_price'		=>	$new_goods_price,				//商品单价
					'product_price'		=>	$goods_info['goods_price'],		//商品单价(提供给自己看的数据)
					'product_money'		=>	sprintf("%.2f", $goods_info['goods_number']*$goods_info['goods_price']),
					'product_type'		=>	$goods_cate_property[1],		//佣金分类
					'product_commission' =>	sprintf("%.2f", $goods_cate_property[2]*$goods_info['goods_number']*$new_goods_price)		//佣金
			);
		}
	}
	return $goods_info_array;
}

/**
  * 根据goods_id查询产品分类id,用于返回分别计算佣金
  */
/*  #yi:新增返利类别
	me&city 班尼路      11%              H		7
	框架眼镜单品≤250元  21%             I		8
	框架眼镜单品>250元  11%             J		9
*/
function get_cateid($goods_id, $extension_code, $is_cx) {
	$cate_property_array = array();
	if ($goods_id) {
		if ($extension_code == 'package_buy' || $is_cx == 1) {
			//组合商品或特价促销抢购的商品
			$cate_property_array[0] = '1';
			$cate_property_array[1] = '组合商品或特价促销抢购的商品';
			$cate_property_array[2] = '0.015';
		} else {
			$sql = "SELECT cat_id FROM  " . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = ' . $goods_id;
			$row = $GLOBALS['db']->getRow($sql);
			if (!empty($row)) {
				$cat_arr2 = $GLOBALS['db']->getAll("select cat_id from ecs_category where parent_id=159 and is_show=1 ");//yi:修改
				foreach($cat_arr2 as $k => $v)
				{
					$cat_arr[] = $v['cat_id'];
				}
				$cat_id = $row['cat_id'];
				if (in_array($cat_id, array(4,5,29,65,134))) {
					//强生,博士伦系列 
					$cate_property_array[0] = '2';
					$cate_property_array[1] = '强生博士伦系列';
					$cate_property_array[2] = '0.025';
				}
				elseif(in_array($cat_id, array(175, 177)))
				{
					$cate_property_array[0] = '7';
					$cate_property_array[1] = 'me&city 班尼路系列';
					$cate_property_array[2] = '0.11';
				}
				elseif(in_array($cat_id, $cat_arr))
				{
					$shop_price = $GLOBALS['db']->getOne("select shop_price from ecs_goods where goods_id=".$goods_id." limit 1;");
					if($shop_price>250)
					{
						$cate_property_array[0] = '9';
						$cate_property_array[1] = '框架眼镜单品大于250';
						$cate_property_array[2] = '0.11';
					}
					else
					{
						$cate_property_array[0] = '8';
						$cate_property_array[1] = '框架眼镜单品不大于250';
						$cate_property_array[2] = '0.21';
					}
				}
				elseif (in_array($cat_id, array(2,3,17,18,19,20,21,23,24,26,27,122,123,124,125,126,141,142,151,153))) {
					//普通隐形眼镜
					$cate_property_array[0] = '3';
					$cate_property_array[1] = '普通隐形眼镜';
					$cate_property_array[2] = '0.08';
				} elseif (in_array($cat_id, array(7,8,9,11,30,31,32,33,34,35,36,38,39,42,43,44,45,46,47,48,49,51,52,55,56,58,59,129,130,131,132,133,137,139,140,143,145,149,150,152))) {
					//彩色隐形眼镜
					$cate_property_array[0] = '4';
					$cate_property_array[1] = '彩色隐形眼镜';
					$cate_property_array[2] = '0.15';
				} elseif (in_array($cat_id, array(66,67,68,69,70,73,74,75,128,135,144,146,147))) {
					//护理液润眼液
					$cate_property_array[0] = '5';
					$cate_property_array[1] = '护理液润眼液';
					$cate_property_array[2] = '0.07';
				} elseif (in_array($cat_id, array(77,79,127,156))) {
					//护理工具
					$cate_property_array[0] = '6';
					$cate_property_array[1] = '护理工具';
					$cate_property_array[2] = '0.28';
				} else {
					//其他
					$cate_property_array[0] = '1';
					$cate_property_array[1] = '组合商品或特价促销抢购的商品';
					$cate_property_array[2] = '0.015';
				}
			}
		}
	}
	return $cate_property_array;
}

//xml转义
function xmlencode($tag) {
	$tag = str_replace("&", "&amp;", $tag);
	$tag = str_replace("<", "&lt;", $tag);
	$tag = str_replace(">", "&gt;", $tag);
	$tag = str_replace("'", "&apos;", $tag);
	$tag = str_replace('"', '&quot;', $tag);
	return $tag;
}
?>