<?php


define('IN_ECS', true);
require_once(dirname(__FILE__) . '/../../includes/init.php');
date_default_timezone_set('Asia/Shanghai');
header("Content-type:text/xml; charset=utf-8");
$cid = isset($_GET['cid']) ? $_GET['cid'] : '';
$d = isset($_GET['d']) ? $_GET['d'] : '';		//下单时间 日期格式自定位yyyy-mm-dd
$ud = isset($_GET['ud']) ? $_GET['ud'] : '';	//订单更新时间

if ($d) $query_time = $d;
if ($ud) $query_time = $ud;

//判断日期是否合法
if ($query_time) {
	$time_array = explode('-', $query_time);
	if (checkdate($time_array[1], $time_array[2], $time_array[0])) {
		//mktime(0,0,0,month,day,year);
		$st = mktime(0, 0, 0, $time_array[1], $time_array[2], $time_array[0]);
		$et = mktime(23, 59, 59, $time_array[1], $time_array[2], $time_array[0]);
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
echo '<?xml version="1.0" encoding="utf-8" ?>';
echo '<orders>';

if ($all_orders) {
	foreach ($all_orders as $key => $value) {
		echo '<order>';
		foreach ($value as $k => $v) {
			if ($k == 'cid') echo '<cid>'.$v.'</cid>';
			if ($k == 'wi') echo '<wi>'.$v.'</wi>';
			if ($k == 'order_time') echo '<order_time>'.$v.'</order_time>';
			if ($k == 'order_no') echo '<order_no>'.$v.'</order_no>';
		
			if ($k == 'products_all') {
				echo '<products_all>';
				foreach ($v as $gid => $goods_info) {
					echo '<product>';
					foreach ($goods_info as $k3 => $v3) {
						if ($k3 == 'product_id') echo '<product_id>'.$v3.'</product_id>';
						if ($k3 == 'product_name') echo '<product_name>'.xmlencode($v3).'</product_name>';
						if ($k3 == 'product_qty') echo '<product_qty>'.$v3.'</product_qty>';
						if ($k3 == 'product_price') echo '<product_price>'.$v3.'</product_price>';
						if ($k3 == 'product_type') echo '<product_type>'.xmlencode($v3).'</product_type>';
					}
					echo '</product>';
				}
				echo '</products_all>';
			}
			
			if ($k == 'order_status') echo '<order_status>'.$v.'</order_status>';
			if ($k == 'pay_status') echo '<pay_status>'.$v.'</pay_status>';
			if ($k == 'pay_name') echo '<pay_name>'.$v.'</pay_name>';
			if ($k == 'fare') echo '<fare>'.$v.'</fare>';
			if ($k == 'favorable') echo '<favorable>'.$v.'</favorable>';
			if ($k == 'favorable_code') echo '<favorable_code>'.$v.'</favorable_code>';
		}
		echo '</order>';
	}
}

echo '</orders>';


//获取时间段内的订单列表
function get_orders($st, $et, $cid) {
	if ($cid) $temp_cid = " AND zipcode LIKE '" . $cid . ",%' ";
	$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE referer='yiqifa2015' AND add_time > $st AND add_time < $et $temp_cid LIMIT 500";
		$order_rows = $GLOBALS['db'] -> getAll($query_sql);
        
		//print_r($order_rows);
		$orderlist = $order_ids = array();
		if($order_rows) {
			foreach ($order_rows as $pre_order) {
				$order_ids[] = $pre_order['order_id'];
				if($pre_order['pay_status'] == PS_PAYED) {
					$status = '已付款';
				}elseif($pre_order['shipping_status'] == SS_SHIPPED) {
					$status = '已发货';
				}elseif(in_array($pre_order['order_status'], array(OS_CONFIRMED,OS_SPLITED,OS_SPLITING_PART))) {
					$status = '已确认';
				}elseif(in_array($pre_order['order_status'], array(OS_RETURNED, OS_CANCELED, OS_INVALID))) {
					$status = '未确认';
				}else{
					$status = '未确认';
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
			
			$goods_info_array[$goods_info['rec_id']] = array (
					'product_id'		=>	$goods_info['goods_id'],		//商品ID
					'product_name'		=>	$goods_info['goods_name'],		//商品名称
					'product_qty'		=>	$goods_info['goods_number'],	//商品数量					
					'product_price'		=>	$new_goods_price,				//商品单价
					'product_type'		=>	$goods_cate_property[1]			//佣金分类
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
				$sql = "SELECT cat_id FROM  " . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = ' . $goods_id;
				$row = $GLOBALS['db']->getRow($sql);
				if (!empty($row)) {
					
					$cat_id = $row['cat_id']; //所属小类ID
					$perent_cat_id = $GLOBALS['db']->getOne("SELECT parent_id FROM ecs_category WHERE cat_id=$cat_id"); //所属大类ID
					if ($perent_cat_id == 76) {
						//护理工具
						$cate_property_array[0] = '1';
						$cate_property_array[1] = '护理工具';
						$cate_property_array[2] = '0.60';
					}else{
						//其他
						$cate_property_array[0] = '2';
						$cate_property_array[1] = '除护理工具产品';
						$cate_property_array[2] = '0.33';
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