<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../../includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$stime = isset($_GET['stime']) ? $_GET['stime'] : '';
$etime = isset($_GET['etime']) ? $_GET['etime'] : '';

//判断日期是否合法
if ($stime && $etime) {
	$stime_array = explode('-', $stime);
	$etime_array = explode('-', $etime);
	if (checkdate($stime_array[1], $stime_array[2], $stime_array[0]) && checkdate($etime_array[1], $etime_array[2], $etime_array[0])) {
		//mktime(0,0,0,month,day,year);
		$st = mktime(0, 0, 0, $stime_array[1], $stime_array[2], $stime_array[0]);
		$et = mktime(23, 59, 59, $etime_array[1], $etime_array[2], $etime_array[0]);
		if (!$st || !$et) {
			echo '您输入的日期格式不正确';
			exit;
		}
	} else {
		echo '您输入的日期格式不正确';
		exit;
	}
}

$all_orders = get_orders($st, $et);
//print_r($all_orders);

$output_orders = array();

$j=0;
if ($all_orders) {
	foreach ($all_orders as $key => $value) {
		//echo $key;
		//$output_orders[$j]['order_id'] = $key;
		$order_id = $key;
		foreach ($value as $key2 => $detail) {
			//echo count($detail['order_sn']).'<br/>';
			//if ($key2 == 'goods_infos') echo count($detail);
			//if ($key2 == 'order_sn') echo $detail."\t";
			if ($key2 == 'siteid') { $siteid=$detail; $output_orders[$j]['siteid'] = $detail;}
			if ($key2 == 'order_sn') { $order_sn=$detail; $output_orders[$j]['order_sn'] = $detail;}
			if ($key2 == 'create_time') { $create_time=$detail; $output_orders[$j]['create_time'] = $detail;}
			if ($key2 == 'status') { $status=$detail; $output_orders[$j]['status'] = $detail;}
			if ($key2 == 'goods_infos') {
				//读取商品参数
				if (count($detail) <= 1) {
					//订单中只有一个商品
					foreach ($detail as $gid => $goods_info) {
						//echo '=='.count($detail).'<br><br/>';
						foreach ($goods_info as $key3 => $goods_detail) {
							if ($key3 == 'goods_id') { $output_orders[$j]['goods_id'] = $goods_detail;}
							if ($key3 == 'goods_name') { $output_orders[$j]['goods_name'] = $goods_detail;}
							if ($key3 == 'goods_num') { $output_orders[$j]['goods_num'] = $goods_detail;}
							if ($key3 == 'goods_price') { $output_orders[$j]['goods_price'] = $goods_detail;}
							if ($key3 == 'goods_cateid') { $output_orders[$j]['goods_cateid'] = $goods_detail;}
							if ($key3 == 'goods_catename') { $output_orders[$j]['goods_catename'] = $goods_detail;}
							if ($key3 == 'percent') { $output_orders[$j]['percent'] = $goods_detail;}
						}
						//echo "<br />";
					}
				} else {
					//订单中有多个商品
					foreach ($detail as $gid => $goods_info) {
						//echo '=='.count($detail).'<br><br/>';
						foreach ($goods_info as $key3 => $goods_detail) {
							//echo $goods_detail.'===<br/>';
							//$output_orders[$j]['order_id'] = $order_id;
							$output_orders[$j]['siteid'] = $siteid;
							$output_orders[$j]['order_sn'] = $order_sn;
							$output_orders[$j]['create_time'] = $create_time;
							$output_orders[$j]['status'] = $status;
							if ($key3 == 'goods_id') { $output_orders[$j]['goods_id'] = $goods_detail;}
							if ($key3 == 'goods_name') { $output_orders[$j]['goods_name'] = $goods_detail;}
							if ($key3 == 'goods_num') { $output_orders[$j]['goods_num'] = $goods_detail;}
							if ($key3 == 'goods_price') { $output_orders[$j]['goods_price'] = $goods_detail;}
							if ($key3 == 'goods_cateid') { $output_orders[$j]['goods_cateid'] = $goods_detail;}
							if ($key3 == 'goods_catename') { $output_orders[$j]['goods_catename'] = $goods_detail;}
							if ($key3 == 'percent') { $output_orders[$j]['percent'] = $goods_detail;}
						}
						$j++;
						//echo "<br />";
					}
				}
				
			}
		}
		//echo "<br />";
		$j++;
	}
} else {
	echo '未查询到相关数据';
}

//print_r($output_orders);die;
/* echo "<table border='1'><tr><td>网站ID</td><td>订单号</td><td>下单时间</td><td>订单状态</td><td>商品ID</td><td>商品名称</td><td>数量</td><td>价格</td><td>类别ID</td><td>类别名称</td><td>提成比例</td></tr>";
foreach ($output_orders as $k => $v) {
	echo "<tr>";
	foreach ($v as $v2) {
		echo "<td>".$v2."</td>"; 
	}
	echo "</tr>";
}
echo "</table>"; */

foreach ($output_orders as $k => $v) {
	foreach ($v as $v2) {
		echo $v2."\t"; 
	}
	echo "\n";
}

//获取时间段内的订单列表
function get_orders($st, $et) {
	//$query_sql = 'SELECT cps.*, o.order_id as id,o.add_time as create_time,o.order_status,o.shipping_status,o.pay_status,o.goods_amount,o.discount,o.integral_money,o.bonus FROM ' . $GLOBALS['ecs']->table('cps_duomai') . ' cps LEFT JOIN '. $GLOBALS['ecs']->table('order_info') .' o ON cps.order_sn = o.order_sn WHERE o.order_status=5 AND o.pay_status=2 AND o.add_time > '. $st .' AND o.add_time < '. $et .' ORDER BY cps.order_sn ASC LIMIT 200';
	//$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE referer='duomai' AND order_status=5 AND pay_status=2 AND add_time > ". $st ." AND add_time < ". $et ." ORDER BY order_sn ASC LIMIT 300";
	$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE referer='wap_duomai' AND add_time > ". $st ." AND add_time < ". $et ." ";
		//echo $query_sql.'<br>';
		$order_rows = $GLOBALS['db'] -> getAll($query_sql);
		//print_r($order_rows);
		$orderlist = $order_ids = array();
		if($order_rows) {
			foreach ($order_rows as $pre_order) {
				$order_ids[] = $pre_order['order_id'];
				if($pre_order['pay_status'] == PS_PAYED) {
					$status = 4;
					//$status = "已支付";
				}elseif($pre_order['shipping_status'] == SS_SHIPPED) {
					$status = 3;
					//$status = "已发货";
				}elseif(in_array($pre_order['order_status'], array(OS_CONFIRMED,OS_SPLITED,OS_SPLITING_PART))) {
					$status = 2;
					//$status = "已确认尚未发货和支付";
				}elseif(in_array($pre_order['order_status'], array(OS_RETURNED, OS_CANCELED, OS_INVALID))) {
					$status = 6;
					//$status = "已取消";
				}else{
					$status = 1;
					//$status = "新订单";
				}
				//订单状态（1新订单；2已确认尚未发货和支付；3已发货；4已支付；5已完成；6已取消）
				//echo $status;
				
				//可计算提成的订单总金额=商品总价-优惠劵、积分、活动优惠金额
				$total_price = $pre_order['goods_amount'] - $pre_order['discount'] - $pre_order['integral_money'] - $pre_order['bonus'];
				$goods_amount = $pre_order['goods_amount'];
				$orderlist[$pre_order['order_id']] = array (
						//'siteid' 		=>	$pre_order['siteid'],				//网站ID
						'siteid' 		=>	$pre_order['zipcode'],							//网站ID
						'order_sn'		=>	$pre_order['order_sn'],							//订单编号
						'create_time'	=>	date("Y-m-d H:i:s", $pre_order['add_time']),	//下单时间
						'goods_amount'	=>	$goods_amount,									//商品总价
						'total_price'	=>	$total_price,									//可计算提成的订单总金额
						'status'		=>	$status,										//订单状态
						'goods_infos' 	=>	get_goods_infos($pre_order['order_id'], $goods_amount, $total_price) 	//订单的商品详情
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
        //$i = 0;// zhang: 160321  注释
        foreach ($result as $goods_info) {
            //$i++;// zhang: 160321  注释
            /* 原计算可算佣金的价格 zhang: 160321  注释
             if ($i == 1 && $coupon > 0) {
                //将优惠金额减去，重新计算出商品单价
                $new_goods_price = ($goods_info['goods_number'] * $goods_info['goods_price'] - $coupon) / $goods_info['goods_number'];
            } else {
                $new_goods_price = $goods_info['goods_price'];
            }*/
            // 新计算可算佣金的价格   zhang：160321
            if ($coupon > 0) {
                $new_goods_price = sprintf("%.2f", $goods_info['goods_price'] * $total_price/$goods_amount);
            } else {
                $new_goods_price = $goods_info['goods_price'];
            }
			//商品类别和分成比例
			$goods_cate_property = get_cateid($goods_info['goods_id'], $goods_info['extension_code'], $goods_info['is_cx']);
			
			$goods_info_array[$goods_info['rec_id']] = array (
					'goods_id'			=>	$goods_info['goods_id'],		//商品ID
					'goods_name'		=>	$goods_info['goods_name'],		//商品名称
					'goods_num'			=>	$goods_info['goods_number'],	//商品数量					
					'goods_price'		=>	$new_goods_price,				//商品单价
					'goods_cateid'		=>	$goods_cate_property[0],		//商品分类ID
					'goods_catename'	=>	$goods_cate_property[1],		//商品分类名称
					'percent'			=>	$goods_cate_property[2]			//提成比例
			);
		}
	}
	//print_r($goods_info_array);
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
    //团购秒杀专享 都以is_cx字段来判定
    if($extension_code == 'tuan_buy' || $extension_code == 'miaosha_buy' || $extension_code == 'source_buy'){
        $extension_code = '';
    }
	if ($goods_id) {
		if ($extension_code == 'package_buy' || $is_cx == 1) {
			//组合商品或特价促销抢购的商品
			$cate_property_array[0] = '1';
			$cate_property_array[1] = '组合商品或特价促销抢购的商品';
			$cate_property_array[2] = '0.02';
		} else {
			$sql = "SELECT cat_id FROM  " . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = ' . $goods_id;
			$row = $GLOBALS['db']->getRow($sql);
			if (!empty($row)) {
				$cat_arr2 = $GLOBALS['db']->getAll("select cat_id from ecs_category where parent_id=159 and is_show=1 ");//yi:修改
				foreach($cat_arr2 as $k => $v)
				{
					$cat_arr[] = $v['cat_id'];
				}
				$cat_id = $row['cat_id']; //所属小类ID
				$perent_cat_id = $GLOBALS['db']->getOne("SELECT parent_id FROM ecs_category WHERE cat_id=$cat_id"); //所属大类ID
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
				elseif ($perent_cat_id == 1) {
				//elseif (in_array($cat_id, array(2,3,17,18,19,20,21,23,24,26,27,122,123,124,125,126,141,142,151,153))) {
					//普通隐形眼镜
					$cate_property_array[0] = '3';
					$cate_property_array[1] = '普通隐形眼镜';
					$cate_property_array[2] = '0.085';
				} 
				elseif ($perent_cat_id == 6) {
				//elseif (in_array($cat_id, array(7,8,9,11,30,31,32,33,34,35,36,38,39,42,43,44,45,46,47,48,49,51,52,55,56,58,59,129,130,131,132,133,137,139,140,143,145,149,150,152))) {
					//彩色隐形眼镜
					$cate_property_array[0] = '4';
					$cate_property_array[1] = '彩色隐形眼镜';
					$cate_property_array[2] = '0.16';
				} 
				elseif ($perent_cat_id == 64) {
				//elseif (in_array($cat_id, array(66,67,68,69,70,73,74,75,128,135,144,146,147))) {
					//护理液润眼液
					$cate_property_array[0] = '5';
					$cate_property_array[1] = '护理液润眼液';
					$cate_property_array[2] = '0.07';
				} 
				elseif ($perent_cat_id == 76) {
				//elseif (in_array($cat_id, array(77,79,127))) {
					//护理工具
					$cate_property_array[0] = '6';
					$cate_property_array[1] = '护理工具';
					$cate_property_array[2] = '0.3';
				} 
				elseif ($perent_cat_id == 190) {
					//太阳眼镜
					$cate_property_array[0] = '9';
					$cate_property_array[1] = '太阳眼镜';
					$cate_property_array[2] = '0.11';
				} else {
					//其他
					$cate_property_array[0] = '1';
					$cate_property_array[1] = '组合商品或特价促销抢购的商品';
					$cate_property_array[2] = '0.02';
				}
			}
		}
	}
	return $cate_property_array;
}
?>