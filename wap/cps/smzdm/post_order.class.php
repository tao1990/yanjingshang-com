<?php
define('IN_ECS', true);
date_default_timezone_set('PRC');
//require('../../includes/init.php'); //被flow.php引用时，注释此行

class post_order {

	public function get_order_info($order_id,$feedback) {
        $api_key = '6b51bdf531cd206f9b837217a7722344';
		$all_orders = $this->get_orders($order_id,$feedback);
        $all_orders = json_encode($all_orders);
        $key = md5($api_key.$all_orders);
        $order = urlencode($all_orders);
        $referer = "http://www.linkstars.com/api/adv/cps/order.php?key=".$key."&order=".$order;

		return $referer;
	}
    
    public function get_order_info2($order_id,$feedback) {
	    $referer = array();
        $api_key = '6b51bdf531cd206f9b837217a7722344';
		$all_orders = $this->get_orders($order_id,$feedback);
        $all_orders = json_encode($all_orders);
        $key = md5($api_key.$all_orders);
        //$order = urlencode($all_orders);
        $order = $all_orders;
        //$referer['url'] = "http://www.linkstars.com/api/adv/cps/order.php?key=".$key."&order=".$order;
        $referer['url'] = "http://www.linkstars.com/api/adv/cps/order.php";
        $referer['key'] = $key;
        $referer['order'] = $order;
		return $referer;
	}

	//获取指定订单
	public function get_orders($order_id,$feedback) {
        $count = $GLOBALS['db'] -> getOne("SELECT SUM(goods_number) FROM ". $GLOBALS['ecs']->table('order_goods') ." WHERE order_id=$order_id");
		$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE order_id=$order_id LIMIT 1";
        $order_rows = $GLOBALS['db'] -> getAll($query_sql);
        $orderlist = array();
        if($order_rows) {
            foreach ($order_rows as $pre_order) {

                //可计算提成的订单总金额=商品总价-优惠劵、积分、活动优惠金额
                $total_price = $pre_order['goods_amount'] - $pre_order['discount'] - $pre_order['integral_money'] - $pre_order['bonus'];
                $goods_amount = $pre_order['goods_amount'];
                $products = $this->get_goods_infos($pre_order['order_id'], $goods_amount, $total_price); 	//订单的商品详情

                $orderlist = array (
                        'feedback'              => $feedback,                   //
                        'order_number'          => $pre_order['order_sn'],      //
                        'order_time'	        => $pre_order['add_time'],	    //下单时间
                        'orders_price'          => $pre_order['order_amount'],  //
                        'order_commission_type' => '1',                         //
                        'count'                 => $count,                      //
                        'order_status'          => '0',                         //
                        'goods'   	            => $products                    //
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
				$goods_cate_property = array('1','0.07');

				$goods_info_array[] = array (
						'goods_id'		        =>	$goods_info['goods_id'],		//商品ID
						'goods_name'		    =>	$goods_info['goods_name'],		//商品名称
						'goods_count'		    =>	$goods_info['goods_number'],	//商品数量
						'goods_price'		    =>	$new_goods_price,				//商品单价
						'goods_commission_type' =>	$goods_cate_property[0],		//商品类别名称
                        'commission'            =>	$goods_cate_property[1]			//佣金比例
				);
			}
		}
		return $goods_info_array;
	}
}


?>