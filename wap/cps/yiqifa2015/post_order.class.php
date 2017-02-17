<?php
define('IN_ECS', true);
date_default_timezone_set('PRC');
//require('../../includes/init.php'); //被flow.php引用时，注释此行

class post_order {

	public function get_order_info($order_id) {
		$info_str = '';	//返回字符串
		$order_arr = array();
		$all_orders = $this->get_orders($order_id);
		if ($all_orders) {
			foreach ($all_orders as $key => $value) {
			 
                $json = json_encode($value);
			}
          
		}
        
        $info_str = '&json='.urlencode('{"order":['.$json.']}').'&encoding=utf-8';
      
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
                    $pay_name = $pre_order['pay_name'];
                    if($pay_name != '货到付款' && $pay_name != '支付宝'){
                        $pay_name = '网银支付';
                    }
                    
					$orderlist[] = array (
                            'orderNo'       => $pre_order['order_sn'],
							'orderTime'	    => date("Y-m-d H:i:s", $pre_order['add_time']),	//下单时间
                            'updateTime'    => '',
                            'campaignId'    => $_COOKIE['cpsinfo_yiqifa_cid_roi'],
                            'feedback'      => $_COOKIE['cpsinfo_yiqifa_wi_roi'],
                            'fare'          => $pre_order['shipping_fee'],
                            'favorable'     => $pre_order['bonus'],
                            'favorableCode' => $pre_order['bonus_id'],
							'products'   	=> $this->get_goods_infos($pre_order['order_id'], $goods_amount, $total_price), 	//订单的商品详情
                            'orderStatus'   => '未确认',
                            'paymentStatus' => '未付款',
                            'paymentType'   => $pay_name
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
				
				$goods_info_array[] = array (
						'productNo'			    =>	$goods_info['goods_id'],		//商品ID
						'name'			        =>	$goods_info['goods_name'],		//商品名称
						'amount'			    =>	$goods_info['goods_number'],	//商品数量					
						'price'			        =>	$new_goods_price,				//商品单价
						'category'			    =>	$goods_cate_property[1],		//商品类别
                        'commissionType'		=>	$goods_cate_property[1]			//佣金类型
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

}


?>