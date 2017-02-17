<?php
define('IN_ECS', true);
date_default_timezone_set('PRC');
//require('../../includes/init.php'); //被flow.php引用时，注释此行

class post_order {

	public function get_order_info($order_id) {
		$info_str = '';	//返回字符串
		$order_arr = array();
		$all_orders = $this->get_orders($order_id);
        //return $all_orders;die;
		if ($all_orders) {
            $info_str = "";
			foreach ($all_orders as $key => $value) {

                $info_str .= $key."=".urlencode($value)."&";
			}
            $referer = "http://c.duomai.com/track.php?sid=10010&aid=123&euid=".$_COOKIE['cpsinfo_duomai_siteid']."&t=http://m.easeeyes.com/";
            $info_str .= 'encoding=utf-8&referer='.urlencode($referer);
		}

        //$info_str = '&json='.urlencode('{"order":['.$json.']}').'&encoding=utf-8';
        //$info_str = 'euid=1&order_sn=20140303111111&suborder_sn=050601&order_time=2013-03-03+11%3A11%3A11&orders_prices=3110&order_status=0&goods_cate=1%7C2%7C2%7C3&goods_cate_name=%E4%B9%A6%E7%B1%8D%7C%E5%8C%96%E5%A6%86%E5%93%81%7C%E5%8C%96%E5%A6%86%E5%93%81%7C%E7%94%B5%E8%84%91&goods_ta=1%7C2%7C3%7C4&goods_ta=1%7C2%7C3%7C4&goods_id=10086%7C10001%7C10000%7C12315&goods_name=%E5%8D%81%E4%B8%87%E4%B8%AA%E4%B8%BA%E4%BB%80%E4%B9%88%7C%E5%A4%A7%E5%AE%9D%7C%E6%B5%B7%E9%A3%9E%E4%B8%9D%7C%E7%A5%9E%E8%88%9F%E7%AC%94%E8%AE%B0%E6%9C%AC&goods_price=51%7C22%7C38%7C2999&rate=0.02%7C0.05%7C0.05%7C0.03&commission=1%7c2%7c5.5%7c359.88&commission_type=A%7cB%7cB%7cC&referer=http%3a%2f%2fc.duomai.com%2ftrack.php%3fsid%3d10010%26aid%3d123%26euid%3d%26t%3dhttp%3a%2f%2fwww.yourdomain.com%2f';

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
                    $products = $this->get_goods_infos($pre_order['order_id'], $goods_amount, $total_price); 	//订单的商品详情
                    $pro_arr  = array();
                    foreach($products as $pk => $pv){
                        $pro_arr['goods_cate'][] = $pv['cate_id'];
                        $pro_arr['goods_cate_name'][] = $pv['category'];
                        $pro_arr['goods_ta'][] = $pv['amount'];
                        $pro_arr['goods_id'][] = $pv['goods_id'];
                        $pro_arr['goods_name'][] = $pv['name'];
                        $pro_arr['goods_price'][] = $pv['price'];
                        $pro_arr['totalPrice'][] = $pv['price'] * $pv['amount'];
                        $pro_arr['rate'][] = $pv['commission'];
                        $pro_arr['commission'][] = $pv['price'] * $pv['amount'] * $pv['commission'];
                    }
                    foreach($pro_arr as $pak =>$pav){
                        $pro_arr[$pak] = implode("|",$pav);
                    }
					$orderlist = array (
                            'order_sn'        => $pre_order['order_sn'],
							'order_time'	  => date("Y-m-d H:i:s", $pre_order['add_time']),	//下单时间
                            'orders_price'    => $pre_order['order_amount'],
                            'order_status'    => $pre_order['order_status'],
							'goods_cate'   	  => $pro_arr['goods_cate'],
							'goods_cate_name' => $pro_arr['goods_cate_name'],
							'goods_ta'   	  => $pro_arr['goods_ta'],
							'goods_id'   	  => $pro_arr['goods_id'],
							'goods_name'   	  => $pro_arr['goods_name'],
							'goods_price'     => $pro_arr['goods_price'],
							'totalPrice'      => $pro_arr['totalPrice'],
							'rate'   	      => $pro_arr['rate'],
							'commission'   	  => $pro_arr['commission']
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
						'goods_id'		 =>	$goods_info['goods_id'],		//商品ID
						'name'			 =>	$goods_info['goods_name'],		//商品名称
						'amount'		 =>	$goods_info['goods_number'],	//商品数量
						'price'		     =>	$new_goods_price,				//商品单价
						'cate_id'		 =>	$goods_cate_property[0],		//商品类别编号
						'category'		 =>	$goods_cate_property[1],		//商品类别名称
                        'commission'     =>	$goods_cate_property[2]			//佣金比例
				);
			}
		}
		//print_r($goods_info_array);

		return $goods_info_array;
	}

	//根据goods_id查询产品分类id,用于返回分别计算佣金
	public function get_cateid($goods_id, $extension_code, $is_cx) {
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
                    foreach ($cat_arr2 as $k => $v) {
                        $cat_arr[] = $v['cat_id'];
                    }
                    $cat_id = $row['cat_id']; //所属小类ID
                    $perent_cat_id = $GLOBALS['db']->getOne("SELECT parent_id FROM ecs_category WHERE cat_id=$cat_id"); //所属大类ID
                    if (in_array($cat_id, array(4, 5, 29, 65, 134))) {
                        //强生,博士伦系列
                        $cate_property_array[0] = '2';
                        $cate_property_array[1] = '强生博士伦系列';
                        $cate_property_array[2] = '0.025';
                    } elseif (in_array($cat_id, array(175, 177))) {
                        $cate_property_array[0] = '7';
                        $cate_property_array[1] = 'me&city 班尼路系列';
                        $cate_property_array[2] = '0.11';
                    } elseif (in_array($cat_id, $cat_arr)) {
                        $shop_price = $GLOBALS['db']->getOne("select shop_price from ecs_goods where goods_id=" . $goods_id . " limit 1;");
                        if ($shop_price > 250) {
                            $cate_property_array[0] = '9';
                            $cate_property_array[1] = '框架眼镜单品大于250';
                            $cate_property_array[2] = '0.11';
                        } else {
                            $cate_property_array[0] = '8';
                            $cate_property_array[1] = '框架眼镜单品不大于250';
                            $cate_property_array[2] = '0.21';
                        }
                    } elseif ($perent_cat_id == 1) {
                        //elseif (in_array($cat_id, array(2,3,17,18,19,20,21,23,24,26,27,122,123,124,125,126,141,142,151,153))) {
                        //普通隐形眼镜
                        $cate_property_array[0] = '3';
                        $cate_property_array[1] = '普通隐形眼镜';
                        $cate_property_array[2] = '0.085';
                    } elseif ($perent_cat_id == 6) {
                        //elseif (in_array($cat_id, array(7,8,9,11,30,31,32,33,34,35,36,38,39,42,43,44,45,46,47,48,49,51,52,55,56,58,59,129,130,131,132,133,137,139,140,143,145,149,150,152))) {
                        //彩色隐形眼镜
                        $cate_property_array[0] = '4';
                        $cate_property_array[1] = '彩色隐形眼镜';
                        $cate_property_array[2] = '0.16';
                    } elseif ($perent_cat_id == 64) {
                        //elseif (in_array($cat_id, array(66,67,68,69,70,73,74,75,128,135,144,146,147))) {
                        //护理液润眼液
                        $cate_property_array[0] = '5';
                        $cate_property_array[1] = '护理液润眼液';
                        $cate_property_array[2] = '0.07';
                    } elseif ($perent_cat_id == 76) {
                        //elseif (in_array($cat_id, array(77,79,127))) {
                        //护理工具
                        $cate_property_array[0] = '6';
                        $cate_property_array[1] = '护理工具';
                        $cate_property_array[2] = '0.3';
                    } elseif ($perent_cat_id == 190) {
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

}


?>