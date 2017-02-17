<?php
define('IN_ECS', true);
date_default_timezone_set('PRC');
//require('../../includes/init.php'); //被flow.php引用时，注释此行

class post_order {
	
	public function get_order_xml ($order_id=0, $u_id='') {
		$xml = '';
		$all_orders = $this->get_orders($order_id);
		//print_r($all_orders);
		
		$xml .= '<?xml version="1.0" encoding="utf-8" ?>';
		$xml .= '<orders version="4.0">';
		
		if ($all_orders) {
			foreach ($all_orders as $key => $value) {
				$xml .= '<order>';
				
				$xml .= '<s_id>1158</s_id>';
				$xml .= '<order_id_parent>'.$value['order_sn'].'</order_id_parent>';
				$xml .= '<order_id>'.$value['order_sn'].'</order_id>';
				$xml .= '<order_time>'.$value['add_time'].'</order_time>';
				
				if (! empty($u_id))
				{
					$xml .= '<uid>'.$u_id.'</uid>';
					$xml .= '<uname>'.$u_id.'@51fanli.com</uname>';
				}
				else 
				{
					//$u_info = $this->get_fanli_userinfo($value['user_id']);
					//$xml .= '<uid>'.$u_info[0].'</uid>';
					//$xml .= '<uname>'.$u_info[1].'</uname>';
					$xml .= '<uid></uid>';
					$xml .= '<uname></uname>';
				}
				
				$xml .= '<tc>'.$value['tc'].'</tc>';
				$xml .= '<pay_time></pay_time>';
				$xml .= '<status>'.$value['status'].'</status>';
				$xml .= '<locked>0</locked>';
				$xml .= '<lastmod>'.$value['add_time'].'</lastmod>';
				$xml .= '<is_newbuyer>0</is_newbuyer>';
				$xml .= '<platform>2</platform>';
				$xml .= '<code></code>';
				$xml .= '<remark></remark>';
				
				$xml .= '<products>';
				foreach ($value['products_all'] as $gid => $goods_info) {
					$xml .= '<product>';
					$xml .= '<pid>'.$goods_info['pid'].'</pid>';
					$xml .= '<title>' .$this->xmlencode($goods_info['title']). '</title>';
					$xml .= '<category>'.$goods_info['category'].'</category>';
					$xml .= '<category_title>'.$this->xmlencode($goods_info['category_title']).'</category_title>';
					$xml .= '<url>http://www.easeeyes.com/goods' .$goods_info['pid']. '.html</url>';
					$xml .= '<num>'.$goods_info['num'].'</num>';
					$xml .= '<price>'.$goods_info['price'].'</price>';
					$xml .= '<real_pay_fee>'.$goods_info['real_pay_fee'].'</real_pay_fee>';
					$xml .= '<refund_num>0</refund_num>';
					$xml .= '<commission>' .$goods_info['commission']. '</commission>';
					$xml .= '<comm_type>' .$goods_info['comm_type']. '</comm_type>';
					$xml .= '</product>';
				}
				$xml .= '</products>';
				
				$xml .= '</order>';
			}
		}
		
		$xml .= '</orders>';
		
		return $xml;
	}
	
	//获取指定订单
	public function get_orders($order_id=0) {
		$query_sql = "SELECT * FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE order_id=$order_id LIMIT 1";
		$order_rows = $GLOBALS['db'] -> getAll($query_sql);
		$orderlist = $order_ids = array();
		if($order_rows) {
			foreach ($order_rows as $pre_order) {
			$order_ids[] = $pre_order['order_id'];
			if($pre_order['pay_status'] == PS_PAYED) {
				$status = 2; //已付款已发货
			}elseif($pre_order['shipping_status'] == SS_SHIPPED) {
				$status = 2; //已付款已发货
			}elseif(in_array($pre_order['order_status'], array(OS_CONFIRMED,OS_SPLITED,OS_SPLITING_PART))) {
				$status = 1; //已确认
			}elseif(in_array($pre_order['order_status'], array(OS_RETURNED, OS_CANCELED, OS_INVALID))) {
				$status = 3; //无效订单
			}else{
				$status = 0; //新订单
			}
			
			//可计算提成的订单总金额=商品总价-优惠劵、积分、活动优惠金额
			$total_price = $pre_order['goods_amount'] - $pre_order['discount'] - $pre_order['integral_money'] - $pre_order['bonus'];
			$goods_amount = $pre_order['goods_amount'];
			$zipcode = explode('|', $pre_order['zipcode']);
			
			$orderlist[] = array (
					'user_id'			=>	$pre_order['user_id'],		
					'order_sn'			=>	$pre_order['order_sn'],							//订单号
					'add_time'			=>	date("Y-m-d H:i:s", $pre_order['add_time']),	//下单日期
					'pay_time'			=>	date("Y-m-d H:i:s", $pre_order['pay_time']),	//下单日期
					'products_all' 		=>	$this->get_goods_infos($pre_order['order_id'], $goods_amount, $total_price), 	//订单的商品详情
					'tc'				=>	$zipcode[0],
					'status'			=>	$status											//订单状态
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
				//商品类别和分成比例
				$goods_cate_property = $this->get_cateid($goods_info['goods_id'], $goods_info['extension_code'], $goods_info['is_cx']);
				
				//扣除优惠券
				if ($coupon > 0) {
					$new_goods_price = $goods_info['goods_price'] * (1-(1/$goods_amount)*$coupon);
				} else {
					$new_goods_price = $goods_info['goods_price'];
				}
				
				$goods_info_array[] = array (
						'pid'				=>	$goods_info['goods_id'],		//商品ID
						'title'				=>	$goods_info['goods_name'],		//商品名称
						'num'				=>	$goods_info['goods_number'],	//商品数量
						'price'				=>	$goods_info['goods_price'],		//商品单价
						'real_pay_fee'		=>	sprintf("%.2f", $new_goods_price * $goods_info['goods_number']), //商品结算总金额
						'category'			=>	$goods_cate_property[0],		//商品类别编码
						'category_title'	=>	$goods_cate_property[1],		//商品类别名称
						'commission'		=>	sprintf("%.2f", $new_goods_price * $goods_info['goods_number'] * $goods_cate_property[2]),	//佣金总额
						'comm_type'			=>	$goods_cate_property[0]			//佣金分类编码
				);
			}
		}
		return $goods_info_array;
	}
	
	/**
	  * 根据goods_id查询产品分类id,用于返回分别计算佣金
	  */
	public function get_cateid($goods_id, $extension_code, $is_cx) {
		$cate_property_array = array();
		if ($goods_id) {
			if ( ! empty($extension_code) || $is_cx == 1) {
				//组合商品或特价促销抢购的商品
				$cate_property_array[0] = '1';
				$cate_property_array[1] = '组合商品或特价促销抢购的商品';
				$cate_property_array[2] = '0.015';
			} else {
				$sql = "SELECT cat_id FROM  " . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = ' . $goods_id;
				$row = $GLOBALS['db']->getRow($sql);
				if (!empty($row)) {
					$cat_arr = array();
					$cat_arr2 = $GLOBALS['db']->getAll("select cat_id from ecs_category where parent_id=159 and is_show=1 ");//yi:修改
					foreach($cat_arr2 as $k => $v)
					{
						$cat_arr[] = $v['cat_id'];
					}
					$cat_id = $row['cat_id']; //所属小类ID
					$perent_cat_id = $GLOBALS['db']->getOne("SELECT parent_id FROM ecs_category WHERE cat_id=$cat_id"); //所属大类ID
					if (in_array($cat_id, array(4,5,29,65,134,154))) {
						//强生,博士伦系列 
						$cate_property_array[0] = '2';
						$cate_property_array[1] = '强生博士伦视康晴彩';
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
						//普通隐形眼镜
						$cate_property_array[0] = '3';
						$cate_property_array[1] = '普通隐形眼镜';
						$cate_property_array[2] = '0.08';
					} 
					elseif ($perent_cat_id == 6) {
						//彩色隐形眼镜
						$cate_property_array[0] = '4';
						$cate_property_array[1] = '彩色隐形眼镜';
						$cate_property_array[2] = '0.15';
					} 
					elseif ($perent_cat_id == 64) {
						//护理液润眼液
						$cate_property_array[0] = '5';
						$cate_property_array[1] = '护理液润眼液';
						$cate_property_array[2] = '0.07';
					} 
					elseif ($perent_cat_id == 76) {
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
	
	//会员u_id和username
	public function get_fanli_userinfo($user_id = 0) {
		$u_array = array();
		$sql = "SELECT user_name, refer_id FROM  " . $GLOBALS['ecs']->table('users') . "WHERE user_id = '" . $user_id . "' LIMIT 1";
		$row = $GLOBALS['db']->getRow($sql);
		if ($row) {
			$u_array[0] = $row['refer_id'];
			$u_array[1] = $row['user_name'];
		} else {
			$u_array[0] = 0;
			$u_array[1] = '';
		}
		return $u_array;
	}
	
	//xml转义
	public function xmlencode($tag) {
		$tag = str_replace("&", "&amp;", $tag);
		$tag = str_replace("<", "&lt;", $tag);
		$tag = str_replace(">", "&gt;", $tag);
		$tag = str_replace("'", "&apos;", $tag);
		$tag = str_replace("\"", '&quot;', $tag);
		return $tag;
	}

}

?>