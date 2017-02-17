<?php
define('IN_ECS', true);
date_default_timezone_set('PRC');
//require('../../includes/init.php'); //被flow.php引用时，注释此行

class post_order {

	public function get_order_info($order_id) {
		$info_str = '';	//返回字符串
		$sd = '';		//下单时间
		$pn = '';		//商品编号
		$pna = '';		//商品名称
		$ct = '';		//佣金类型
		$ta = '';		//商品数量
		$pp = '';		//商品单价
		
		$all_orders = $this->get_orders($order_id);
		//print_r($all_orders);
		
		if ($all_orders) {
			foreach ($all_orders as $key => $value) {
				foreach ($value as $k => $v) {
					if ($k == 'order_time') $sd = $v;
					if ($k == 'products_all') {
						//$xml .= '<products_all>';
						foreach ($v as $gid => $goods_info) {
							//$xml .= '<product>';
							foreach ($goods_info as $k3 => $v3) {
								if ($k3 == 'product_id') $pn .= '|' . $v3;
								if ($k3 == 'product_name') $pna .= '|' . $v3;
								if ($k3 == 'product_qty') $ta .= '|' . $v3;
								if ($k3 == 'product_price') $pp .= '|' . $v3;
								if ($k3 == 'product_type') $ct .= '|' . $v3;
							}
							//$xml .= '</product>';
						}
						//$xml .= '</products_all>';
					}
					
				}
				
			}
		}
		
		$pn = substr($pn, 1);
		$pna = urlencode(substr($pna, 1));
		$ct = urlencode(substr($ct, 1));
		//$ta = urlencode(substr($ta, 1));
		$ta = substr($ta, 1);
		$pp = substr($pp, 1);
		$sd = urlencode($sd);
		
		$info_str = '&pn=' .$pn. '&pna=' .$pna. '&ct=' .$ct. '&ta=' .$ta. '&pp=' .$pp. '&sd=' .$sd. '&dt=w&encoding=utf-8';
		return $info_str;
	}
	
	
	//获取指定订单
	public function get_orders($order_id) {
		$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE order_id=$order_id LIMIT 1";
			$order_rows = $GLOBALS['db'] -> getAll($query_sql);
			$orderlist = $order_ids = array();
			if($order_rows) {
				foreach ($order_rows as $pre_order) {
					$order_ids[] = $pre_order['order_id'];
					
					//可计算提成的订单总金额=商品总价-优惠劵、积分、活动优惠金额
					$total_price = $pre_order['goods_amount'] - $pre_order['discount'] - $pre_order['integral_money'] - $pre_order['bonus'];
					$goods_amount = $pre_order['goods_amount'];
					
					$orderlist[$pre_order['order_id']] = array (
							'order_time'	=>	date("Y-m-d H:i:s", $pre_order['add_time']),	//下单时间
							'products_all' 	=>	$this->get_goods_infos($pre_order['order_id'], $goods_amount, $total_price) 	//订单的商品详情
					);
				}
				return $orderlist;
			}
	}
	
	//根据订单ID获取订单商品信息
	public function get_goods_infos($order_id, $goods_amount, $total_price) {
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
				//扣除优惠券
				if ($coupon > 0) {
					$new_goods_price = sprintf("%.2f", $goods_info['goods_price'] * (1-(1/$goods_amount)*$coupon));
				} else {
					$new_goods_price = $goods_info['goods_price'];
				}
				
				//商品类别和分成比例
				$goods_cate_property = $this->get_cateid($goods_info['goods_id'], $goods_info['extension_code'], $goods_info['is_cx']);
				
				$goods_info_array[$goods_info['rec_id']] = array (
						'product_id'			=>	$goods_info['goods_id'],		//商品ID
						'product_name'			=>	$goods_info['goods_name'],		//商品名称
						'product_qty'			=>	$goods_info['goods_number'],	//商品数量					
						'product_price'			=>	$new_goods_price,				//商品单价
						'product_type'			=>	$goods_cate_property[1]			//佣金分类编号
				);
			}
		}
		//print_r($goods_info_array);
		return $goods_info_array;
	}
	
	//根据goods_id查询产品分类id,用于返回分别计算佣金
	public function get_cateid($goods_id, $extension_code, $is_cx) {
		$cate_property_array = array();
		if ($goods_id) {
			//超级返利商品
			$cf_goods_arr_30 = array(3035,138,3036);
			$cf_goods_arr_16 = array(3038,2403);
			$cf_goods_arr_50 = array(773);
			$cf_goods_arr_35 = array(4191,4193,4192);
			$cf_goods_arr_15 = array(788);
			$cf_goods_arr_20 = array(4212,4213,4214);
			$current_time = time();
			$in_time = FALSE;
			if ($current_time >= strtotime('2014-09-15 00:00:00') && $current_time <= strtotime('2014-09-21 23:59:59')) {
				$in_time = TRUE;
			}
			
			if (in_array($goods_id, $cf_goods_arr_30) && $in_time) {
				$cate_property_array[0] = '11';
				$cate_property_array[1] = '超级返30%佣金商品';
				$cate_property_array[2] = '0.3';
			} elseif (in_array($goods_id, $cf_goods_arr_16) && $in_time) {
				$cate_property_array[0] = '12';
				$cate_property_array[1] = '超级返16%佣金商品';
				$cate_property_array[2] = '0.16';
			} elseif (in_array($goods_id, $cf_goods_arr_50) && $in_time) {
				$cate_property_array[0] = '13';
				$cate_property_array[1] = '超级返50%佣金商品';
				$cate_property_array[2] = '0.5';
			} elseif (in_array($goods_id, $cf_goods_arr_35) && $in_time) {
				$cate_property_array[0] = '14';
				$cate_property_array[1] = '超级返35%佣金商品';
				$cate_property_array[2] = '0.35';
			} elseif (in_array($goods_id, $cf_goods_arr_15) && $in_time) {
				$cate_property_array[0] = '15';
				$cate_property_array[1] = '超级返15%佣金商品';
				$cate_property_array[2] = '0.15';
			} elseif (in_array($goods_id, $cf_goods_arr_20) && $in_time) {
				$cate_property_array[0] = '16';
				$cate_property_array[1] = '超级返20%佣金商品';
				$cate_property_array[2] = '0.2';
			} elseif ($extension_code != '' || $is_cx == 1) {
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
						$cate_property_array[2] = '0.08';
					} 
					elseif ($perent_cat_id == 6) {
					//elseif (in_array($cat_id, array(7,8,9,11,30,31,32,33,34,35,36,38,39,42,43,44,45,46,47,48,49,51,52,55,56,58,59,129,130,131,132,133,137,139,140,143,145,149,150,152))) {
						//彩色隐形眼镜
						$cate_property_array[0] = '4';
						$cate_property_array[1] = '彩色隐形眼镜';
						$cate_property_array[2] = '0.15';
					} 
					elseif ($perent_cat_id == 64) {
					//elseif (in_array($cat_id, array(66,67,68,69,70,73,74,75,128,135,144,146,147))) {
						//护理液润眼液
						$cate_property_array[0] = '5';
						$cate_property_array[1] = '护理液润眼液';
						$cate_property_array[2] = '0.07';
					} 
					elseif ($perent_cat_id == 76) {
					//elseif (in_array($cat_id, array(77,79,127,156))) {
						//护理工具
						$cate_property_array[0] = '6';
						$cate_property_array[1] = '护理工具';
						$cate_property_array[2] = '0.28';
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
						$cate_property_array[2] = '0.015';
					}
				}
			}
		}
		return $cate_property_array;
	}

}
?>