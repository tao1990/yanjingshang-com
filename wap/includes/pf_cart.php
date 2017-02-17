<?php
/**
 * 购物车相关函数
 * @version 2014
 * @author xuyizhi
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 获取购物车商品信息和促销信息（结算前）
 * @return 购物车商品列表、合计信息、商品对应的促销活动
 */
function get_cart_info_for_flow()
{
	$return_array = array();
	
	//1.购物车商品列表
	$sql = "";
	if ($_SESSION['user_id'] > 0) {
    	$sql =  "SELECT *, (goods_number*goods_price) AS subtotal FROM " . $GLOBALS['ecs']->table('cart') . " WHERE user_id = '" . $_SESSION['user_id'] . "' ORDER BY rec_id";
    } else {
    	if (isset($_COOKIE['cart_session_id'])) {
    		$sql =  "SELECT *, (goods_number*goods_price) AS subtotal FROM " . $GLOBALS['ecs']->table('cart') . " WHERE user_id <= 0 AND (session_id = '" . SESS_ID . "' OR session_id = '".$_COOKIE['cart_session_id']."') ORDER BY rec_id";
    	} else {
    		$sql =  "SELECT *, (goods_number*goods_price) AS subtotal FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' ORDER BY rec_id";
    	}
    }
    $rs = $GLOBALS['db']->getAll($sql);
    
    //框架镜片的数据格式：左眼度数,散光,轴位|右眼|瞳距：-3.25,-3.50,16|-3.50,-3.75,13|60.5
    foreach ($rs as $k => $v)
    {
    	if ( ! empty($v['property_kj']))
    	{
    		$tmp_arr = explode('|', $v['property_kj']);
    		$left_data = explode(',', $tmp_arr[0]);
    		$right_data = explode(',', $tmp_arr[1]);
    		$str_property_kj = '';
    		$str_property_kj .= '右眼度数：'.$right_data[0];
    		if ( ! empty($right_data[1])) $str_property_kj .= ' 散光：' .$right_data[1]. ' 轴位：' .$right_data[2];
    		$str_property_kj .= '<br />左眼度数：'.$left_data[0];
    		if ( ! empty($left_data[1])) $str_property_kj .= ' 散光：' .$left_data[1]. ' 轴位：' .$left_data[2];
    		$str_property_kj .= '<br />瞳距：'.$tmp_arr[2];
    		
    		$rs[$k]['str_property_kj'] = $str_property_kj;
    	}
    	else 
    	{
    		$rs[$k]['str_property_kj'] = '';
    	}
    }
    $return_array['cart_goods'] = $rs;
    
    //2.购物合计信息
    $total = array(
    	'goods_number' => 0,					//商品数量总计
    	'goods_weight' => get_cart_weight(),	//商品总重量
    	'goods_amount' => 0,					//商品金额总计
        'goods_amount_float'  => 0,                    //商品金额总计(float)
    	'payment_amount' => 0,					//应付款金额(不含运费)
    	'discount_amount' => 0  				//全部折扣金额
    );
	foreach ($rs as $k => $v)
    {
    	$total['goods_amount'] += $v['goods_price'] * $v['goods_number'];
        $total['goods_amount_float'] += $v['goods_price'] * $v['goods_number'];
    	$total['goods_number'] += $v['goods_number'];
    }
    $total['goods_amount'] = sprintf('%.2f', $total['goods_amount']);
    $return_array['total'] = $total;
    
    //3.对应的促销活动
    require('pf_promotion.php');
    $current_promotion = get_current_promotion();	//当前时间段的促销活动
    $return_array['effective_promotion'] = array();	//对当前购物车商品起作用的促销活动(可和其他促销同时生效)
    $return_array['effective_promotion_not_compossible'] = array(); //不可和其他促销活动同时生效
    
	foreach ($current_promotion as $cp_key => $cp_val)
    {
    	foreach ($rs as $k => $v)
    	{
	    	if ($cp_val['scope_type'] == 0)
	    	{
	    		//促销使用范围：全场商品
	    		if (empty($cp_val['is_compossible']))
	    		{
	    			$return_array['effective_promotion_not_compossible'][] = $cp_val;
	    		}
	    		else 
	    		{
	    			$return_array['effective_promotion'][] = $cp_val;
	    		}
	    		break;
	    	}
	    	elseif ($cp_val['scope_type'] == 1)
	    	{
	    		//促销使用范围：指定分类
	    		if (in_array($v['category_id'], explode(',', $cp_val['scope_detail'])))
	    		{
		    		if (empty($cp_val['is_compossible']))
		    		{
		    			$return_array['effective_promotion_not_compossible'][] = $cp_val;
		    		}
		    		else 
		    		{
		    			$return_array['effective_promotion'][] = $cp_val;
		    		}
	    			break;
	    		}
	    	}
	    	elseif ($cp_val['scope_type'] == 2)
	    	{
	    		//促销使用范围：指定具体商品
	    		if (in_array($v['goods_id'], explode(',', $cp_val['scope_detail'])))
	    		{
		    		if (empty($cp_val['is_compossible']))
		    		{
		    			$return_array['effective_promotion_not_compossible'][] = $cp_val;
		    		}
		    		else 
		    		{
		    			$return_array['effective_promotion'][] = $cp_val;
		    		}
	    			break;
	    		}
	    	}
    	}
    }
    
    return $return_array;
}

/**
 * 普通商品加入购物车(包括促销活动范围内的商品)
 * @param int $goods_id
 * @param int $num
 * @param string $degree 度数数据
 * @param int $glass_type 框架镜片的种类
 * 度数数据格式：右眼度数|右眼数量|左眼度数|左眼数量|右眼散光|右眼轴位|左眼散光|左眼轴位|框架瞳距
 */
function add_to_cart_normal($goods_id=0, $num=1, $degree='', $glass_type=0)
{
	if ( ! empty($goods_id) && $num > 0)
	{
    	$sql = "SELECT g.cat_id,g.goods_sn, g.goods_name, g.shop_price AS org_price, g.promote_price, g.promote_start_date, g.promote_end_date,
    				g.is_cx, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price
    			FROM ".$GLOBALS['ecs']->table('goods')." AS g
    			LEFT JOIN ".$GLOBALS['ecs']->table('member_price')." AS mp
    			ON mp.goods_id = g.goods_id AND mp.user_rank='$_SESSION[user_rank]'
    			WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    	$goods = $GLOBALS['db']->getRow($sql);

    	if (empty($goods))
    	{
    		return false;
    	}

    	//商品售价(如果促销价格有效,则执行促销价)、促销标记
    	$final_price = $goods['shop_price'];
    	$is_promotion = 0;
		if($goods['promote_price'] > 0)
	    {
	        $final_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
	    }
	    if ($goods['is_cx'] > 0 OR $final_price != $goods['shop_price'])
	    {
	    	$is_promotion = 1;
	    }

	    //度数等属性数据
	    $ds_arr = array();
	    if ( ! empty($degree))
	    {
	    	$ds_arr = explode('|', $degree);
	    }
	    $r_degree = ( ! empty($ds_arr[0]) && $ds_arr[0] != 'undefined') ? addslashes($ds_arr[0]) : '';
	    $r_number = ( ! empty($ds_arr[1])) ? intval($ds_arr[1]) : 0;
	    $l_degree = ( ! empty($ds_arr[2]) && $ds_arr[2] != 'undefined') ? addslashes($ds_arr[2]) : '';
	    $l_number = ( ! empty($ds_arr[3])) ? intval($ds_arr[3]) : 0;
	    $r_sg = ( ! empty($ds_arr[4]) && $ds_arr[4] != 'undefined') ? addslashes($ds_arr[4]) : '';
	    $r_zw = ( ! empty($ds_arr[5]) && $ds_arr[5] != 'undefined') ? addslashes($ds_arr[5]) : '';
	    $l_sg = ( ! empty($ds_arr[6]) && $ds_arr[6] != 'undefined') ? addslashes($ds_arr[6]) : '';
	    $l_zw = ( ! empty($ds_arr[7]) && $ds_arr[7] != 'undefined') ? addslashes($ds_arr[7]) : '';
	    $kj_tongju = ( ! empty($ds_arr[8]) && $ds_arr[8] != 'undefined') ? addslashes($ds_arr[8]) : '';

	    //商品数量：左右眼数量相加或参数$num
	    $goods_number = (($r_number + $l_number) > 0) ? $r_number + $l_number : $num;

	    //新增记录时间和到期时间
	    $add_time = $_SERVER['REQUEST_TIME'];
	    $expiry_date = $add_time + 86400 * 30;

	    //插入记录
	    $cart_info = array();

	    if ( ! empty($glass_type))
	    {
	    	//框架镜架+镜片:
	    	//1.镜架
	    	$cart_info1 = array(
		        'user_id'       	=> $_SESSION['user_id'],
		        'session_id'    	=> SESS_ID,
	    		'promotion_type'	=> 0,
	    		'promotion_id'		=> 0,
		        'goods_id'      	=> $goods_id,
		        'goods_name'    	=> $goods['goods_name'],
	    		'goods_price'      	=> $final_price,
	    		'goods_number'      => $goods_number,
	    		'category_id'		=> $goods['cat_id'],
	    		'property_degree'	=> '',
	    		'property_sg'		=> '',
	    		'property_zw'		=> '',
	    		//'r_degree'			=> '',
	    		//'r_number'			=> '',
		    	//'l_degree'			=> '',
		    	//'l_number'			=> '',
		    	//'r_sg'				=> '',
		    	//'l_sg'				=> '',
		    	//'r_zw'				=> '',
		    	//'l_zw'				=> '',
		    	'property_kj'		=> '',
	    		'parent_id'			=> 0,
	    		'is_gift'			=> 0,
	    		'is_promotion'		=> $is_promotion,
	    		'is_free_postage'	=> 0,
	    		'add_time'			=> $add_time,
	    		'expiry_date'		=> $expiry_date
		    );
		    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info1, 'INSERT');
		    $rec_id = mysql_insert_id();

		    //2.镜片
		    require('pf_goods.php');
		    $glass_goods_id = 0;
		    $glass_info = get_glass_type_info($glass_type);
		    $cart_info2 = array(
		        'user_id'       	=> $_SESSION['user_id'],
		        'session_id'    	=> SESS_ID,
	    		'promotion_type'	=> 0,
	    		'promotion_id'		=> 0,
		        'goods_id'      	=> $glass_info['goods_id'],
		        'goods_name'    	=> $glass_info['goods_name'],
	    		'goods_price'      	=> $glass_info['shop_price'],
	    		'goods_number'      => $goods_number,
	    		'category_id'		=> $glass_info['cat_id'],
	    		//'r_degree'			=> $r_degree,
	    		//'r_number'			=> $r_number,
		    	//'l_degree'			=> $l_degree,
		    	//'l_number'			=> $l_number,
		    	//'r_sg'				=> $r_sg,
		    	//'l_sg'				=> $l_sg,
		    	//'r_zw'				=> $r_zw,
		    	//'l_zw'				=> $l_zw,
		    	'property_kj'		=> $l_degree. ',' . $l_sg. ',' . $l_zw . '|' . $r_degree . ',' . $r_sg . ',' . $r_zw . '|' . $kj_tongju, //左眼度数,散光,轴位|右眼|瞳距
	    		'parent_id'			=> $rec_id, //做为镜架的配件
	    		'is_gift'			=> 0,
	    		'is_promotion'		=> 0,
	    		'is_free_postage'	=> 0,
	    		'add_time'			=> $add_time,
	    		'expiry_date'		=> $expiry_date
		    );

		    //3.镜盒和眼镜布

		    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info2, 'INSERT');
	    }
	    else
	    {
	    	//非框架
            if ( empty($r_degree) && empty($l_degree))
            {
                $cart_info = array(
    		        'user_id'       	=> $_SESSION['user_id'],
    		        'session_id'    	=> SESS_ID,
    	    		'promotion_type'	=> 0,
    	    		'promotion_id'		=> 0,
    		        'goods_id'      	=> $goods_id,
    		        'goods_name'    	=> $goods['goods_name'],
    	    		'goods_price'      	=> $final_price,
    	    		'goods_number'      => $goods_number,
    	    		'category_id'		=> $goods['cat_id'],
    		    	'property_degree'	=> '',
    	    		'property_sg'		=> '',
    	    		'property_zw'		=> '',
                    'property_kj'		=> '',
    	    		'parent_id'			=> 0,
    	    		'is_gift'			=> 0,
    	    		'is_promotion'		=> $is_promotion,
    	    		'is_free_postage'	=> 0,
    	    		'add_time'			=> $add_time,
    	    		'expiry_date'		=> $expiry_date
    		    );
                $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
            }
            else
            {
    	    	if ( ! empty($r_degree) && ! empty($r_number))
    	    	{
    	    		$cart_info = array(
    			        'user_id'       	=> $_SESSION['user_id'],
    			        'session_id'    	=> SESS_ID,
    		    		'promotion_type'	=> 0,
    		    		'promotion_id'		=> 0,
    			        'goods_id'      	=> $goods_id,
    			        'goods_name'    	=> $goods['goods_name'],
    		    		'goods_price'      	=> $final_price,
    		    		'goods_number'      => $r_number,
    		    		'category_id'		=> $goods['cat_id'],
    		    		'property_degree'	=> $r_degree,
    	    			'property_sg'		=> $r_sg,
    	    			'property_zw'		=> $r_zw,
    		    		//'r_degree'			=> $r_degree,
    	    			//'r_number'			=> $r_number,
    			    	//'l_degree'			=> $l_degree,
    			    	//'l_number'			=> $l_number,
    			    	//'r_sg'				=> $r_sg,
    			    	//'l_sg'				=> $l_sg,
    			    	//'r_zw'				=> $r_zw,
    			    	//'l_zw'				=> $l_zw,
    			    	'property_kj'		=> '',
    		    		'parent_id'			=> 0,
    		    		'is_gift'			=> 0,
    		    		'is_promotion'		=> $is_promotion,
    		    		'is_free_postage'	=> 0,
    		    		'add_time'			=> $add_time,
    		    		'expiry_date'		=> $expiry_date
    			    );
    			    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
    	    	}

    	    	if ( ! empty($l_degree) && ! empty($l_number))
    	    	{
    	    		$cart_info = array(
    			        'user_id'       	=> $_SESSION['user_id'],
    			        'session_id'    	=> SESS_ID,
    		    		'promotion_type'	=> 0,
    		    		'promotion_id'		=> 0,
    			        'goods_id'      	=> $goods_id,
    			        'goods_name'    	=> $goods['goods_name'],
    		    		'goods_price'      	=> $final_price,
    		    		'goods_number'      => $l_number,
    		    		'category_id'		=> $goods['cat_id'],
    	    			'property_degree'	=> $l_degree,
    	    			'property_sg'		=> $l_sg,
    	    			'property_zw'		=> $l_zw,
    		    		//'r_degree'			=> $r_degree,
    		    		//'r_number'			=> $r_number,
    			    	//'l_degree'			=> $l_degree,
    			    	//'l_number'			=> $l_number,
    			    	//'r_sg'				=> $r_sg,
    			    	//'l_sg'				=> $l_sg,
    			    	//'r_zw'				=> $r_zw,
    			    	//'l_zw'				=> $l_zw,
    			    	'property_kj'		=> '',
    		    		'parent_id'			=> 0,
    		    		'is_gift'			=> 0,
    		    		'is_promotion'		=> $is_promotion,
    		    		'is_free_postage'	=> 0,
    		    		'add_time'			=> $add_time,
    		    		'expiry_date'		=> $expiry_date
    			    );
    			    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
    	    	}
            }
	    }
	}
}

/**
 * 团购商品加入购物车
 * @param int $tuan_id 团购活动id
 * @param array $goods_array POST过来的商品数组
 */
function add_to_cart_tuan($tuan_id=0, $goods_array=array()) 
{
	if (intval($tuan_id) > 0 && $goods_array)
	{
		$now = $_SERVER['REQUEST_TIME'];
		$tuan_info = $GLOBALS['db']->getRow("SELECT * FROM ecs_tuan WHERE rec_id=$tuan_id LIMIT 1");
		
		//团购价格：在促销主推期间,执行促销价
		$tuan_price = $tuan_info['tuan_price'];
		if ($tuan_info['is_promotion'] == 1 && ! empty($tuan_info['promotion_price']) && $tuan_info['promotion_start_time'] <= $now && $tuan_info['promotion_end_time'] >= $now )
		{
			$tuan_price = $tuan_info['promotion_price'];
		}
		
		//新增记录时间和到期时间
	    $add_time = $now;
	    $expiry_date = $add_time + 86400;
		
	    //团购商品数组循环插入购物车
		$i = 0;
		$parent_rec_id = 0; //团购主商品在购物车中的自增id,用于团购副商品的parent_id字段
		foreach ($goods_array as $cv)
		{
			$temp_arr = explode('|', $cv);			
			$goods = $GLOBALS['db']->getRow("SELECT cat_id, goods_name FROM ecs_goods WHERE goods_id=".intval($temp_arr[0])." LIMIT 1");
			
			//价格字段：主商品是团购价,其他商品是0
			$insert_goods_price = ($i == 0) ? $tuan_price : 0.00;
			$cart_info = array(
		        'user_id'       	=> intval($_SESSION['user_id']),
		        'session_id'    	=> SESS_ID,
	    		'promotion_type'	=> 1,
	    		'promotion_id'		=> $tuan_id,
		        'goods_id'      	=> intval($temp_arr[0]),
		        'goods_name'    	=> $goods['goods_name'],
	    		'goods_price'      	=> $insert_goods_price,
	    		'goods_number'      => 1,
	    		'category_id'		=> $goods['cat_id'],
                'property_degree'   => $temp_arr[1],
	    		'parent_id'			=> $parent_rec_id,
	    		'is_gift'			=> 0,
	    		'is_promotion'		=> 0,
	    		'is_free_postage'	=> $tuan_info['is_no_freight'],
	    		'add_time'			=> $add_time,
	    		'expiry_date'		=> $expiry_date
		    );
		    
		    //插入记录
		    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
			
			if ($i == 0) 
			{
				$parent_rec_id = mysql_insert_id();
			}
			$i++;
		}
		
		//购买人数加1
		$GLOBALS['db']->query("UPDATE ecs_tuan SET buyers=buyers+1 WHERE rec_id=".$tuan_id);
        return $parent_rec_id;
	}
}

/**
 * 秒杀商品加入购物车
 * @param int $miaosha_id 秒杀活动ID
 * @param string $r_degree 右眼度数
 * @param string $l_degree 左右度数
 */
function add_to_cart_miaosha($miaosha_id=0, $r_degree='', $r_number=0, $l_degree='', $l_number=0) 
{
	if ( ! empty($miaosha_id))
	{
		$ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE rec_id = '".$miaosha_id."' LIMIT 1");
		if ($ms)
		{
			$goods = $GLOBALS['db']->getRow("SELECT cat_id, goods_name FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
			$goods_number = $r_number + $l_number;
			
			//新增记录时间和到期时间
			$add_time = $_SERVER['REQUEST_TIME'];
			$expiry_date = $add_time + 86400;
			
			$cart_info = array(
		        'user_id'       	=> intval($_SESSION['user_id']),
		        'session_id'    	=> SESS_ID,
	    		'promotion_type'	=> 2,
	    		'promotion_id'		=> $ms['rec_id'],
		        'goods_id'      	=> $ms['goods_id'],
		        'goods_name'    	=> $goods['goods_name'],
	    		'goods_price'      	=> $ms['price'],
	    		'goods_number'      => $goods_number,
	    		'category_id'		=> $goods['cat_id'],
	    		'r_degree'			=> $r_degree,
	    		'r_number'			=> $r_number,
		    	'l_degree'			=> $l_degree,
		    	'l_number'			=> $l_number,
		    	'r_sg'				=> '',
		    	'l_sg'				=> '',
		    	'r_zw'				=> '',
		    	'l_zw'				=> '',
		    	'kj_tongju'			=> '',
	    		'parent_id'			=> 0,
	    		'is_gift'			=> 0,
	    		'is_promotion'		=> 0,
	    		'is_free_postage'	=> 0,
	    		'add_time'			=> $add_time,
	    		'expiry_date'		=> $expiry_date
		    );
		    
		    //插入记录
		    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
		}
	}
}

/**
 * 免费赠品加入购物车
 * @param $goods_id
 * @param $num
 * @param $degree
 */
function add_to_cart_gift($goods_id=0, $num=1, $degree='')
{
	if ( ! empty($goods_id) && $num > 0)
	{
    	$sql = "SELECT cat_id, goods_name, FROM ".$GLOBALS['ecs']->table('goods')." WHERE g.goods_id = $goods_id";
    	$goods = $GLOBALS['db']->getRow($sql);
    	
    	if (empty($goods))
    	{
    		return false;
    	}
    	
	    //度数等属性数据
	    $ds_arr = array();
	    if ( ! empty($degree))
	    {
	    	$ds_arr = explode('|', $degree);
	    }
	    $r_degree = ( ! empty($ds_arr[0]) && $ds_arr[0] != 'undefined') ? addslashes($ds_arr[0]) : '';
	    $r_number = ( ! empty($ds_arr[1])) ? intval($ds_arr[1]) : 1;
	    $l_degree = ( ! empty($ds_arr[2]) && $ds_arr[2] != 'undefined') ? addslashes($ds_arr[2]) : '';
	    $l_number = ( ! empty($ds_arr[3])) ? intval($ds_arr[3]) : 1;
	    $r_sg = ( ! empty($ds_arr[4]) && $ds_arr[4] != 'undefined') ? addslashes($ds_arr[4]) : '';
	    $r_zw = ( ! empty($ds_arr[5]) && $ds_arr[5] != 'undefined') ? addslashes($ds_arr[5]) : '';
	    $l_sg = ( ! empty($ds_arr[6]) && $ds_arr[6] != 'undefined') ? addslashes($ds_arr[6]) : '';
	    $l_zw = ( ! empty($ds_arr[7]) && $ds_arr[7] != 'undefined') ? addslashes($ds_arr[7]) : '';
	    $kj_tongju = ( ! empty($ds_arr[8]) && $ds_arr[8] != 'undefined') ? addslashes($ds_arr[8]) : '';
	    
	    //商品数量：左右眼数量相加或参数$num
	    $goods_number = (($r_number + $l_number) > 0) ? $r_number + $l_number : $num;
	    
	    //新增记录时间和到期时间
	    $add_time = $_SERVER['REQUEST_TIME'];
	    $expiry_date = $add_time + 86400;
	    
    	$cart_info = array(
	        'user_id'       	=> $_SESSION['user_id'],
	        'session_id'    	=> SESS_ID,
    		'promotion_type'	=> 0,
    		'promotion_id'		=> 0,
	        'goods_id'      	=> $goods_id,
	        'goods_name'    	=> $goods['goods_name'],
    		'goods_price'      	=> 0.00,
    		'goods_number'      => $goods_number,
    		'category_id'		=> $goods['cat_id'],
    		'r_degree'			=> $r_degree,
    		'r_number'			=> $r_number,
	    	'l_degree'			=> $l_degree,
	    	'l_number'			=> $l_number,
	    	'r_sg'				=> $r_sg,
	    	'l_sg'				=> $l_sg,
	    	'r_zw'				=> $r_zw,
	    	'l_zw'				=> $l_zw,
	    	'kj_tongju'			=> $kj_tongju,
    		'parent_id'			=> 0,
    		'is_gift'			=> 1,
    		'is_promotion'		=> 0,
    		'is_free_postage'	=> 0,
    		'add_time'			=> $add_time,
    		'expiry_date'		=> $expiry_date
	    );
	    
	    //插入记录
	    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
	}
}

/**
 * 全额积分兑换、积分加钱购买
 * @param $goods_id
 * @param $num
 * @param $degree
 */
function add_to_cart_integral($goods_id=0, $num=1, $degree='')
{
	if ( ! empty($goods_id) && $num > 0)
	{
		//获取积分兑换商品信息(ecs_exchange_goods.goods_id字段需添加索引)
		$sql = "SELECT g.cat_id, g.goods_name, e.* 
				FROM ecs_goods g 
				LEFT JOIN ecs_exchange_goods e 
				ON g.goods_id = e.goods_id 
				WHERE e.goods_id=".$goods_id." AND e.`type` = 1 AND e.is_exchange = 1 LIMIT 1";
		$row = $GLOBALS['db']->getRow($sql);
		
		//购物类型：积分兑换、加钱购
		$promotion_type = 3;
		if ($row['exchange_money'] > 0) $promotion_type = 4;
		
		//度数等属性数据
	    $ds_arr = array();
	    if ( ! empty($degree))
	    {
	    	$ds_arr = explode('|', $degree);
	    }
	    $r_degree = ( ! empty($ds_arr[0]) && $ds_arr[0] != 'undefined') ? addslashes($ds_arr[0]) : '';
	    $r_number = ( ! empty($ds_arr[1])) ? intval($ds_arr[1]) : 0;
	    $l_degree = ( ! empty($ds_arr[2]) && $ds_arr[2] != 'undefined') ? addslashes($ds_arr[2]) : '';
	    $l_number = ( ! empty($ds_arr[3])) ? intval($ds_arr[3]) : 0;
	    $r_sg = ( ! empty($ds_arr[4]) && $ds_arr[4] != 'undefined') ? addslashes($ds_arr[4]) : '';
	    $r_zw = ( ! empty($ds_arr[5]) && $ds_arr[5] != 'undefined') ? addslashes($ds_arr[5]) : '';
	    $l_sg = ( ! empty($ds_arr[6]) && $ds_arr[6] != 'undefined') ? addslashes($ds_arr[6]) : '';
	    $l_zw = ( ! empty($ds_arr[7]) && $ds_arr[7] != 'undefined') ? addslashes($ds_arr[7]) : '';
	    $kj_tongju = ( ! empty($ds_arr[8]) && $ds_arr[8] != 'undefined') ? addslashes($ds_arr[8]) : '';
	    
	    //商品数量：左右眼数量相加或参数$num
	    $goods_number = (($r_number + $l_number) > 0) ? $r_number + $l_number : $num;
	    
	    //新增记录时间和到期时间
	    $add_time = $_SERVER['REQUEST_TIME'];
	    $expiry_date = $add_time + 86400;
	    
    	$cart_info = array(
	        'user_id'       	=> $_SESSION['user_id'],
	        'session_id'    	=> SESS_ID,
    		'promotion_type'	=> $promotion_type,
    		'promotion_id'		=> $row['rec_id'],
	        'goods_id'      	=> $goods_id,
	        'goods_name'    	=> $row['goods_name'],
    		'goods_price'      	=> $row['exchange_money'],
    		'goods_number'      => $goods_number,
    		'category_id'		=> $row['cat_id'],
    		'r_degree'			=> $r_degree,
    		'r_number'			=> $r_number,
	    	'l_degree'			=> $l_degree,
	    	'l_number'			=> $l_number,
	    	'r_sg'				=> $r_sg,
	    	'l_sg'				=> $l_sg,
	    	'r_zw'				=> $r_zw,
	    	'l_zw'				=> $l_zw,
	    	'kj_tongju'			=> $kj_tongju,
    		'parent_id'			=> 0,
    		'is_gift'			=> 0,
    		'is_promotion'		=> 0,
    		'is_free_postage'	=> 0,
    		'add_time'			=> $add_time,
    		'expiry_date'		=> $expiry_date
	    );
	    
	    //插入记录
	    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
	}
}

/**
 * 外部渠道的专享价格商品
 * @param int $goods_id
 * @param int $num
 * @param string $degree
 * @param string $source 外部渠道简称,如：cps,360等
 */
function add_to_cart_special($goods_id=0, $num=1, $degree='', $source='')
{
	if ( ! empty($goods_id) && $num > 0 && ! empty($source))
	{
		//新增记录时间和到期时间
	    $add_time = $_SERVER['REQUEST_TIME'];
	    $expiry_date = $add_time + 86400;
		
		//获取外部渠道专享商品信息
		$sql = "SELECT g.cat_id, g.goods_name, s.* 
				FROM ecs_goods g 
				LEFT JOIN ecs_source s 
				ON g.goods_id = s.goods_id 
				WHERE s.goods_id = " . $goods_id . " AND s.source = '".$source."' 
				AND s.start_time <= " . $add_time . " AND s.end_time >= " .$add_time. " 
				ORDER BY s.rec_id DESC LIMIT 1";
		$row = $GLOBALS['db']->getRow($sql);
		
		//度数等属性数据
	    $ds_arr = array();
	    if ( ! empty($degree))
	    {
	    	$ds_arr = explode('|', $degree);
	    }
	    $r_degree = ( ! empty($ds_arr[0]) && $ds_arr[0] != 'undefined') ? addslashes($ds_arr[0]) : '';
	    $r_number = ( ! empty($ds_arr[1])) ? intval($ds_arr[1]) : 0;
	    $l_degree = ( ! empty($ds_arr[2]) && $ds_arr[2] != 'undefined') ? addslashes($ds_arr[2]) : '';
	    $l_number = ( ! empty($ds_arr[3])) ? intval($ds_arr[3]) : 0;
	    $r_sg = ( ! empty($ds_arr[4]) && $ds_arr[4] != 'undefined') ? addslashes($ds_arr[4]) : '';
	    $r_zw = ( ! empty($ds_arr[5]) && $ds_arr[5] != 'undefined') ? addslashes($ds_arr[5]) : '';
	    $l_sg = ( ! empty($ds_arr[6]) && $ds_arr[6] != 'undefined') ? addslashes($ds_arr[6]) : '';
	    $l_zw = ( ! empty($ds_arr[7]) && $ds_arr[7] != 'undefined') ? addslashes($ds_arr[7]) : '';
	    $kj_tongju = ( ! empty($ds_arr[8]) && $ds_arr[8] != 'undefined') ? addslashes($ds_arr[8]) : '';
	    
	    //商品数量：左右眼数量相加或参数$num
	    $goods_number = (($r_number + $l_number) > 0) ? $r_number + $l_number : $num;
	    if ($row['number_limit'] >= 1 && $goods_number > $row['number_limit']) 
	    {
	    	$goods_number = $row['number_limit'];
	    }
	    
	    //促销标记(是否可以使用红包)
	    $is_promotion = ($row['can_use_bonus'] == 0) ? 1 : 0;
	    
	    //是否包邮
	    $is_free_postage = ($row['is_by'] == 1) ? 1 : 0;
	    
	    $cart_info = array(
	        'user_id'       	=> $_SESSION['user_id'],
	        'session_id'    	=> SESS_ID,
    		'promotion_type'	=> 5,
    		'promotion_id'		=> $row['rec_id'],
	        'goods_id'      	=> $goods_id,
	        'goods_name'    	=> $row['goods_name'],
    		'goods_price'      	=> $row['exclusive_price'],
    		'goods_number'      => $goods_number,
    		'category_id'		=> $row['cat_id'],
    		'r_degree'			=> $r_degree,
    		'r_number'			=> $r_number,
	    	'l_degree'			=> $l_degree,
	    	'l_number'			=> $l_number,
	    	'r_sg'				=> $r_sg,
	    	'l_sg'				=> $l_sg,
	    	'r_zw'				=> $r_zw,
	    	'l_zw'				=> $l_zw,
	    	'kj_tongju'			=> $kj_tongju,
    		'parent_id'			=> 0,
    		'is_gift'			=> 0,
    		'is_promotion'		=> $is_promotion,
    		'is_free_postage'	=> $is_free_postage,
    		'add_time'			=> $add_time,
    		'expiry_date'		=> $expiry_date
	    );
	    
	    //插入记录
	    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
	}
}

//指定日期包邮
function add_to_cart_free_postage($goods_id=0, $num=1, $degree='')
{
	
}

/**
 * 获取购物车商品总重量
 */
function get_cart_weight()
{
	$sql = '';
	if ($_SESSION['user_id'] > 0) 
	{
		$sql = "SELECT c.goods_number, g.goods_weight FROM ".$GLOBALS['ecs']->table('cart')." AS c
				LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON c.goods_id=g.goods_id 
				WHERE user_id = '".$_SESSION['user_id']."'";
	} 
	else
	{
		if (isset($_COOKIE['cart_session_id'])) 
		{
			$sql = "SELECT c.goods_number, g.goods_weight FROM ".$GLOBALS['ecs']->table('cart')." AS c
					LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON c.goods_id=g.goods_id 
					WHERE c.user_id <= 0 AND (c.session_id = '".SESS_ID."' OR c.session_id = '".$_COOKIE['cart_session_id']."')";
		}
		else
		{
			$sql = "SELECT c.goods_number, g.goods_weight FROM ".$GLOBALS['ecs']->table('cart')." AS c
					LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON c.goods_id=g.goods_id 
					WHERE c.session_id = '".SESS_ID."'";
		}
	}
    $arr = $GLOBALS['db']->getAll($sql);
    	
	$goods_weight = 0;
    foreach($arr as $key => $value)
    {        
		$goods_weight += $arr[$key]['goods_weight']*$arr[$key]['goods_number'];
    }
    
    return round($goods_weight/1000, 2);
}

/**
 * 清空购物车
 */
function empty_cart()
{
	$sql = '';
	if ($_SESSION['user_id'] > 0) 
	{
		$sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE user_id='" . $_SESSION['user_id'] . "'";
	} 
	else 
	{
		if (isset($_COOKIE['cart_session_id'])) 
		{
			$sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."')";
		} 
		else 
		{
			$sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id='" . SESS_ID . "'";
		}
	}
    $GLOBALS['db']->query($sql);
    ecs_header("Location:./\n");
}

