<?php
define('IN_ECS', true);
date_default_timezone_set('PRC');
header("Content-type: text/html; charset=utf-8");     
require('../../includes/init.php'); //被flow.php引用时，注释此行

class post_order {

	public function get_order_info($order_id,$feedback) {
        $source   = 'zhuwenyan@easeeyes.com'; 
        $api_key = 'ec1f2c21c794c11513498518070c327f';
        
        list($t1, $t2) = explode(' ', microtime()); 
        $time    = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000); 
        $sign    = md5($source.$api_key.$time);
		$all_orders = $this->get_orders($order_id,$feedback);

        $res['timestamp']   = $time;
        $res['source']      = $source;
        $res['method']      = 'push';
        $res['sign']        = $sign;
        $res['v']           = '2.0';
        $res['orNo']        = $all_orders['order_number'];
        $res['orTime']      = date('Y-m-d H:i:s',$all_orders['order_time']);
        $res['code']        = $_COOKIE['wap_cpsinfo_panshi_info'];//'1|2|3|4|5';
        $res['orMoney']     = $all_orders['orders_price'];
        $res['cp']          = $all_orders['cp'];
        $res['psy']         = 'a';
        $res['status']      = 1;
        $res['proNum']      = $all_orders['num_all'];
        $res['proNo']       = $all_orders['sn_all'];
        $res['proPrice']    = $all_orders['price_all'];
        $res['proName']     = $all_orders['name_all'];
        $res['proCat']      = $all_orders['type_all'];
        $res['commRate']    = $all_orders['commRate'];
        
		return $res;
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
                
                $sn_all     =   '';
                $name_all   =   '';
                $num_all    =   '';
                $type_all   =   '';
                $price_all  =   '';
                foreach($products as $v){
                    $sn_all.= $v['product_id']."|";
                    $name_all.= $v['product_name']."|";
                    $num_all.= $v['product_qty']."|";
                    $type_all.= $v['comm_type']."|";
                    $price_all.= $v['product_price']."|";
                    $commRate.= $v['commRate']."|";
                }
                $sn_all     = substr($sn_all, 0, -1);
                $name_all   = substr($name_all, 0, -1);
                $num_all    = substr($num_all, 0, -1);
                $type_all   = substr($type_all, 0, -1);
                $price_all  = substr($price_all, 0, -1);
                $commRate   = substr($commRate, 0, -1);
                
                $orderlist = array (
                        'feedback'              => $feedback,                   //
                        'order_number'          => $pre_order['order_sn'],      //
                        'order_time'	        => $pre_order['add_time'],	    //下单时间
                        'orders_price'          => $goods_amount,  //
                        'cp'                    => $pre_order['discount']+$pre_order['bonus'],
                        'order_commission_type' => '1',                         //
                        'count'                 => $count,                      //
                        'order_status'          => '0',                         //
                        'sn_all'                => $sn_all,
                        'name_all'              => $name_all,
                        'num_all'               => $num_all,
                        'type_all'              => $type_all,
                        'price_all'             => $price_all,
                        'commRate'              => $commRate,
                        'goods'   	            => $products,                   //
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
					//$new_goods_price = sprintf("%.2f", $goods_info['goods_price'] * (1-(1/$goods_amount)*$coupon));
				} else {
					//$new_goods_price = $goods_info['goods_price'];
				}
				$new_goods_price = $goods_info['goods_price'];
				//商品类别和分成比例
				$goods_cate_property = $this->get_cateid($goods_info['goods_id'], $goods_info['extension_code'], $goods_info['is_cx']);
				
				$goods_info_array[$goods_info['rec_id']] = array (
						'product_id'			=>	$goods_info['goods_id'],		//商品ID
						'product_name'			=>	$goods_info['goods_name'],		//商品名称
						'product_qty'			=>	$goods_info['goods_number'],	//商品数量					
						'product_price'			=>	$new_goods_price,				//商品单价
						'product_type'			=>	$goods_cate_property[1],			//佣金分类编号
                        'real_pay_fee'		=>	sprintf("%.2f", $new_goods_price * $goods_info['goods_number']), //商品结算总金额
						'category'			=>	$goods_cate_property[0],		//商品类别编码
						'category_title'	=>	$goods_cate_property[1],		//商品类别名称
						'commission'		=>	sprintf("%.2f", $new_goods_price * $goods_info['goods_number'] * $goods_cate_property[2]),	//佣金总额
						'comm_type'			=>	$goods_cate_property[0],			//佣金分类编码
				        'commRate'          =>  ($goods_cate_property[2]),              //佣金比例
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
		
            //团购秒杀专享 都以is_cx字段来判定
            if($extension_code == 'tuan_buy' || $extension_code == 'miaosha_buy' || $extension_code == 'source_buy'){
                $extension_code = '';
            }
            if ($extension_code != '' || $is_cx == 1) {
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