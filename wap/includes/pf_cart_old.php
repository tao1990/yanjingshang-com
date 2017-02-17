<?php
/**
 * 购物车相关函数原程序
 * @version 2014
 * @author xuyizhi
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}



/* ----------------------------------------------------------------------------------------------------------------------
 * yi:购物车中是否有不能使用货到付款的商品，true:不能使用货到付款。
 * ----------------------------------------------------------------------------------------------------------------------
 * 框架眼镜,太阳眼镜和散光定制片不能货到付款
 */
function no_cod_goods()
{	
	/*$sql = "select c.rec_id from ecs_cart as c left join ecs_goods as g on c.goods_id=g.goods_id left join ecs_goods_cat as gc on c.goods_id=gc.goods_id ".
		   " where session_id='".SESS_ID."' and (g.goods_type=15 or g.goods_type=16 or gc.cat_id=15 or gc.cat_id=13) limit 1;";*/
	//以下临时让一款赠品框架支持货到付款
	$sql = "select c.rec_id from ecs_cart as c left join ecs_goods as g on c.goods_id=g.goods_id left join ecs_goods_cat as gc on c.goods_id=gc.goods_id ".
		   " where session_id='".SESS_ID."' and (g.goods_id <> 3319) and (g.goods_type=15 or g.goods_type=16 or gc.cat_id=15 or gc.cat_id=13) limit 1;";
	$res = $GLOBALS['db']->getOne($sql);
	return empty($res)? false: true;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:某个商品是否在购物车中
 * ----------------------------------------------------------------------------------------------------------------------
 */
function goods_in_cart($goods_id=0, $extension_code='')
{
	$sql = "select rec_id from ecs_cart where session_id='".SESS_ID."' and goods_id=".intval($goods_id);
	if(!empty($extension_code))
	{
		$sql .= " and extension_code='".$extension_code."'";
	}
	$sql .= " limit 1;";
	$res = $GLOBALS['db']->getOne($sql);
	return empty($res)? false: true;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:退回积分兑换商品的积分
 * ----------------------------------------------------------------------------------------------------------------------
 */
function reback_exchange_jf($rec_id=0, $user_id=0)
{
	if(!empty($rec_id))
	{
		//$sql = "select extension_id from ".$GLOBALS['ecs']->table('cart')." where session_id='".SESS_ID."' and extension_code='exchange_buy' and rec_id=".$rec_id." limit 1";
		//$zhe_jifen = $GLOBALS['db']->getOne($sql);
		//xu:203-12-11 修改为积分*数量 修正兑换多个，取消时只退回一个的bug
		$sql = "select extension_id,goods_number from ".$GLOBALS['ecs']->table('cart')." where session_id='".SESS_ID."' and extension_code='exchange_buy' and rec_id=".$rec_id." limit 1";
		$zhe_info = $GLOBALS['db']->getRow($sql);
		$zhe_jifen = $zhe_info['extension_id'] * $zhe_info['goods_number'];
		$log_msg = date('Y年m月d日 H时i分', $_SERVER['REQUEST_TIME']+8*3600).' 取消积分折扣商品：退回'.$zhe_jifen.'积分';
		log_account_change($user_id, 0, 0, 0, $zhe_jifen, $log_msg);
	}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:更新订单来源为linktech，用zipcode:记录LINKTECH COOKIES字段。
 * ----------------------------------------------------------------------------------------------------------------------
 */
function update_cps_from($referer='', $cook='', $order_id=0, $order_sn='')
{
	$sql = "update ".$GLOBALS['ecs']->table('order_info')." set referer='$referer', zipcode='$cook' where order_id='$order_id' and order_sn='$order_sn' ;";
	$GLOBALS['db']->query($sql);
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断o_id的线下红包是否来自f_id类型的红包
 * ----------------------------------------------------------------------------------------------------------------------
 * 如果是则返回true。否则返回fasle。 $f_id：要找的红包类型的id。
 */
function bonus_come($o_id=0, $f_id=0)
{
	$ret = false;
	if(!empty($o_id) && !empty($f_id))
	{
		$t_bonus = $GLOBALS['db']->getOne("select bonus_type_id from ecs_user_bonus where bonus_id='$o_id' and order_id>0 limit 1;");
		if($t_bonus==$f_id)
		{
			$ret = true;
		}
	}
	return $ret;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:这个订单的用户是否来自qq联合登录
 * ----------------------------------------------------------------------------------------------------------------------
 * 购买订单的用户id。匿名用户的user_id = 0;
 */
function from_qq_login($user_id = 0)
{
	if(empty($user_id)){return false;}

	$ref = $GLOBALS['db']->getRow("select referer,refer_id from ecs_users where user_id='$user_id' limit 1");
	if(!empty($ref['refer_id']) && $ref['referer']=='qq')
	{
		return true;
	}
	else
	{
		return false;
	}
}


/* ----------------------------------------------------------------------------
 * 删除数组中任意指定元素【yi】
 * ----------------------------------------------------------------------------
 */
function array_remove(&$array,$offset,$length=1){
	return array_splice($array,$offset,$length);
}

/* ----------------------------------------------------------------------------
 * 取得购物车中商品总金额(包含礼包)【yi】
 * ----------------------------------------------------------------------------
 */
function get_cart_sump()
{
	//xyz edit(20130110) 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql  = "SELECT sum(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = '".CART_GENERAL_GOODS."'";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql  = "SELECT sum(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '".CART_GENERAL_GOODS."'";
		} else {
			$sql  = "SELECT sum(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type = '".CART_GENERAL_GOODS."'";
		}
	}
	$sump = $GLOBALS['db']->GetOne($sql);
	return $sump;
}


/* ----------------------------------------------------------------------------
 * 取得购物车中所有商品【yi】,并且礼包商品不享受优惠活动。
 * ----------------------------------------------------------------------------
 */
function yi_get_cart_goods($hv_pk=0, $type=CART_GENERAL_GOODS)
{
	//xyz edit(20130110) 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type ='$type' ";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type ='$type'";
		} else {
			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type ='$type'";
		}
		
	}
	if(!$hv_pk)
	{
		$sql .= " and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' ";
	}
	$cart_goods = $GLOBALS['db']->GetAll($sql);
	
	//--- 2014.04.18 限购 start----
	$temp_remove_rec_id = array();
	$goods_id_cnt_1226 = 0;
	$goods_id_cnt_3444 = 0;
	$goods_id_cnt_1073 = 0;
	
	$goods_id_cnt_3671 = 0;
	$goods_id_cnt_3672 = 0;
	$goods_id_cnt_1628 = 0;
	$goods_id_cnt_3319 = 0;
	$goods_id_cnt_3648 = 0;
	$goods_id_cnt_3650 = 0;
	foreach ($cart_goods as $k => $v)
	{
		if ($v['goods_id'] == 1226)
		{
			$goods_id_cnt_1226++;
			if ($goods_id_cnt_1226 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 3444)
		{
			$goods_id_cnt_3444++;
			if ($goods_id_cnt_3444 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 1073)
		{
			$goods_id_cnt_1073++;
			if ($goods_id_cnt_1073 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		
		if ($v['goods_id'] == 3671)
		{
			$goods_id_cnt_3671++;
			if ($goods_id_cnt_3671 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 3672)
		{
			$goods_id_cnt_3672++;
			if ($goods_id_cnt_3672 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 1628)
		{
			$goods_id_cnt_1628++;
			if ($goods_id_cnt_1628 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 3319)
		{
			$goods_id_cnt_3319++;
			if ($goods_id_cnt_3319 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 3648)
		{
			$goods_id_cnt_3648++;
			if ($goods_id_cnt_3648 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
		if ($v['goods_id'] == 3650)
		{
			$goods_id_cnt_3650++;
			if ($goods_id_cnt_3650 >= 2)
			{
				$temp_remove_rec_id[] = $v['rec_id'];
			}
		}
	}
	foreach ($temp_remove_rec_id as $k => $v)
	{
		unset($cart_goods[$v]);
		$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id=".$v);
	}
	//--- 2014.04.18 限购 end ----
	
	return $cart_goods;
}

/* ----------------------------------------------------------------------------
 * 判断购物车中该优惠活动商品(赠品)是否合法，删除非法赠品【yi.2012/3/08】
 * ----------------------------------------------------------------------------
 * $goods_id:购物车中赠品goods_id，$sum：购物车总金额
 */
function delete_unless_gift($goods_id, $sum)
{	
	$now    = $_SERVER['REQUEST_TIME'];
	$gifts  = array(); //购物车应获得的优惠活动商品
	$is_fav = false;   //false:该赠品不在全部赠品内

	//全部有效优惠活动数组
	$sql = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where start_time<='$now' AND end_time>='$now' AND act_type<3 ORDER BY start_time desc, end_time desc";
	$fav = $GLOBALS['db']->GetAll($sql);	
	foreach($fav as $k => $v)
	{
		$max       = ($fav[$k]['max_amount']==0)? 99999: $fav[$k]['max_amount']; //订单金额上限
		$fav_gifts = unserialize($fav[$k]['gift']);//具体赠品数组
		$fav_kind  = $fav[$k]['act_range'];        //优惠活动优惠范围
		switch($fav_kind)
		{
			case 0 ://全部商品		

				if($sum>0 && $sum>=$fav[$k]['min_amount'] && $sum<$max)
				{						
					foreach($fav_gifts as $k1 => $v1)
					{
						if($v1['price']==0 && $v1['number']==1)
						{
							//数量为1的0元赠品加入gifts数组
							$gifts[] = $fav_gifts[$k1]['id'];
						}
						else
						{
							//加钱送赠品【未完:要增加添加金额功能】
							$gifts[] = $fav_gifts[$k1]['id'];
						}
					}
				}
				break;
			case 1 ://选定分类

				$fav_cat = explode(',', $fav[$k]['act_range_ext']);//能享受优惠的商品分类	

				//遍历购物车普通商品
				$cart_goods = yi_get_cart_goods();
				foreach($cart_goods as $k => $v)
			    {
					//找产品所在的分类
					$f_cat_id = $GLOBALS['db']->GetOne("select cat_id from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$cart_goods[$k]['goods_id']);
					if(in_array($f_cat_id, $fav_cat))
					{
						if($sum>0 && $sum>=$fav[$k]['min_amount'] && $sum<$max)
						{						
							foreach($fav_gifts as $k1 => $v1)
							{
								if($v1['price']==0 && $v1['number']==1)
								{
									//数量为1的0元赠品加入gifts数组
									$gifts[] = $fav_gifts[$k1]['id'];
								}
								else
								{
									//加钱送赠品【未完:要增加添加金额功能】
									$gifts[] = $fav_gifts[$k1]['id'];
								}
							}
						}
					}
				}				
				break;
			case 2 ://选定品牌

				$fav_brand = explode(',', $fav[$k]['act_range_ext']);//能享受优惠的商品品牌

				//遍历购物车普通商品
				$cart_goods = yi_get_cart_goods();
				foreach($cart_goods as $k => $v)
			    {
					//找产品所在的分类
					$f_brand_id = $GLOBALS['db']->GetOne("select brand_id from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$cart_goods[$k]['goods_id']);

					if(in_array($f_brand_id, $fav_brand))
					{
						if($sum>0 && $sum>=$fav[$k]['min_amount'] && $sum<$max)
						{						
							foreach($fav_gifts as $k1 => $v1)
							{
								if($v1['price']==0 && $v1['number']==1)
								{
									//数量为1的0元赠品加入gifts数组
									$gifts[] = $fav_gifts[$k1]['id'];
								}
								else
								{
									//加钱送赠品【未完:要增加添加金额功能】
									$gifts[] = $fav_gifts[$k1]['id'];
								}
							}
						}
					}
				}
				break;
			case 3 ://选定商品

				$fav_goods = explode(',', $fav[$k]['act_range_ext']);//能享受优惠的商品	

				//遍历购物车普通商品
				$cart_goods = yi_get_cart_goods();
				foreach($cart_goods as $k => $v)
			    {
					if(in_array($cart_goods[$k]['goods_id'], $fav_goods))
					{
						if($sum>0 && $sum>=$fav[$k]['min_amount'] && $sum<$max)
						{						
							foreach($fav_gifts as $k1 => $v1)
							{
								if($v1['price']==0 && $v1['number']==1)
								{
									//数量为1的0元赠品加入gifts数组
									$gifts[] = $fav_gifts[$k1]['id'];
								}
								else
								{
									//加钱送赠品【未完:要增加添加金额功能】
									$gifts[] = $fav_gifts[$k1]['id'];
								}
							}
						}
					}
				}
				break;
			default:;
		}
	}
	if(empty($gifts) || (!in_array($goods_id, $gifts)))
	{
		//后台的0元赠品为空,或该赠品不在后台优惠赠品内 删除
		$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' and goods_id='$goods_id' and is_gift>0 AND goods_price=0;");		
	}
}

//-----------------------------------------------------------2种情况的0元赠品加入购物车----------------------------------------------------------


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:购物车中在优惠活动范围内母体商品总数
 * ----------------------------------------------------------------------------------------------------------------------
 * fav_id:优惠活动ID
 */
function in_fav_number($fav_id = 0, $hv_pk=0)
{
	$num   = 0;
	$sql_pk  = ($hv_pk > 0)? " and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' " : "";
	//echo $hv_pk.':'.$sql_pk.'<br/>';
	$carts = $GLOBALS['db']->GetAll("SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' and is_gift=0 ".$sql_pk);	
	if(!empty($carts))
	{
		foreach($carts as $k => $v)
		{		
			if(goods_in_fav_rang($v['goods_id'], $fav_id))
			{					
				$num += $v['goods_number'];
			}
			else
			{					
				continue;
			}
		}
	}
	return $num;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:购物车中在指定的优惠活动的范围内的全部商品价格金额总和。
 * ----------------------------------------------------------------------------------------------------------------------
 * fav_id:优惠活动的id。
 */
function in_fav_sum($fav_id=0, $hv_pk=0)
{
	$fav_sum = 0;
	$sql_pk  = ($hv_pk > 0)? " and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' " : "";
	$carts   = $GLOBALS['db']->GetAll("SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' and is_gift=0 ".$sql_pk." ;");
	
    //循环购物车产品,判断出专享产品,并查询专享产品是否享受优惠活动（指定商品的加价购）BY:tao
    foreach($carts as $k=>$v){
        $enjoy_fav = enjoy_fav_source($v['extension_id']);
        if($v['extension_code']=='source_buy' && !$enjoy_fav){
            unset($carts[$k]);
        }
    }
    
    if(!empty($carts))
	{
		foreach($carts as $k => $v)
		{
			if(goods_in_fav_rang($v['goods_id'], $fav_id))
			{
				$fav_sum += $v['goods_price']*$v['goods_number'];
			}
			else
			{
				continue;
			} 		
		}
	}
	return $fav_sum;
}

//yi：删除购物车中非法赠品，包括加价购商品
//goods_id:商品ID  rec_id:购物车记录编号  act_id:优惠活动ID
function delete_fav_gift($goods_id=0, $act_id=0, $rec_id=0)
{
	//yi:优惠活动范围为全部商品的时候。礼包也算购物金额。
	$act_range = $GLOBALS['db']->getOne("select act_range from ecs_favourable_activity where act_id='$act_id' limit 1;");
	$hv_pk     = (0==$act_range)? 1: 0;
	//$in_fav    = in_fav_sum($act_id, $hv_pk);//享优惠的商品总金额
	$in_fav    = in_fav_sum($act_id, $act_range);

	if(empty($in_fav))
	{
		$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' and goods_id='$goods_id' and is_gift='$act_id';");
	}
	else
	{
		//商品的金额范围
		$act_id = (empty($act_id))? 0: intval($act_id);
		$y_res  = $GLOBALS['db']->GetRow("select * from ecs_favourable_activity where act_id=".$act_id." limit 1;");
		$min = $y_res['min_amount'];
		$max = $y_res['max_amount'];
		$max = ($max==0)? 999999: $max; 

		if($in_fav>=0 && $in_fav>=$min && $in_fav<=$max)
		{
			//xu:20130808:不是多买多送，删除多余赠品(删除重复记录保留一条)
			if ( ! $y_res['is_duo'])
			{
				$same_gift_id = $GLOBALS['db']->getAll("SELECT rec_id FROM ecs_cart WHERE session_id='".SESS_ID."' AND is_gift='$act_id' ORDER BY rec_id");
				if (count($same_gift_id) > 1) //有多个重复赠品
				{
					$saved_rec_id = $same_gift_id[0];
					if ($saved_rec_id['rec_id']) //欲保留的rec_id
					{
						$GLOBALS['db']->query("DELETE FROM ecs_cart WHERE session_id='".SESS_ID."' AND is_gift='".$act_id."' AND rec_id <> ".$saved_rec_id['rec_id']);
					}
				}
			}
		}
		else
		{
			//删除购物车中的这个赠品
			$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' and goods_id='$goods_id' and is_gift='$act_id';");
		}
	}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断该商品是否在某一个优惠活动范围内
 * ----------------------------------------------------------------------------------------------------------------------
 * $fav['act_range']: 优惠活动范围；0，全部商品；1，按分类；2，按品牌；3，按商品 
 */
function goods_in_fav_rang($goods_id=0, $fav_id=0)
{
	$is_have = false;
	$fav     = $GLOBALS['db']->getRow("select * from ecs_favourable_activity where act_id='$fav_id' limit 1;");
	$bb      = explode(",", $fav['act_range_ext']);
	if(!empty($bb))
	{
		switch($fav['act_range'])
		{
			case 0: $is_have = true;   break;
			case 1:
				$goods_cat_id = get_cat_id($goods_id);
				if(in_array($goods_cat_id, $bb))
				{
					$is_have = true;
				}
				else
				{
					$gift_parent_id = $GLOBALS['db']->getOne("select parent_id from ecs_category where cat_id=".$goods_cat_id." limit 1;");
					if(in_array($gift_parent_id, $bb))
					{
						$is_have = true;
					}
				}
				break;
			case 2:
				$goods_brand = get_brand_id($goods_id);
				if(in_array($goods_brand, $bb))
				{
					$is_have = true;
				}
				break;
			case 3:
				if(in_array($goods_id, $bb))
				{
					$is_have = true;
				}
				break;
			default:
				break;
		}
	}
	return !empty($goods_id)? $is_have : false;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:优惠活动之【赠品】购物车自动添加该商品应该享有的赠品。
 * ----------------------------------------------------------------------------------------------------------------------
 * a.sum:购物车商品总金额, rec_id:购物车list ID.
 */
function add_fav_cart($goods_id=0, $sum=0, $rec_id=0)
{
	$now     = $_SERVER['REQUEST_TIME'];
	$cur_gid = $goods_id;
	$sqlf    = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." 
    where `start_time` <= '$now' AND `end_time` >= '$now' AND act_type=0";
	$fav     = $GLOBALS['db']->GetAll($sqlf);
    
	foreach($fav as $k => $v)
	{
		$fav[$k]['gift'] = unserialize($v['gift']);
		$fav_sum = (0==$v['act_range'] && $sum>0)? $sum: in_fav_sum($v['act_id'], $v['act_range']);//购物车中指定范围内商品的总金额	
		$min = $fav[$k]['min_amount'];
		$max = $fav[$k]['max_amount'];
		$max = ($max==0)? 999999: $max; 
		if($fav_sum>0 && $fav_sum>=$min && $fav_sum<=$max)
		{
			continue;
		}
		else
		{
			unset($fav[$k]);
		}
	}

	//each all active,add fav goods to cart.
	foreach($fav as $k => $v)
	{
		//-------------------------------------------------------【单个优惠活动的公共数据】-------------------------------------------------------//
		$fav_kind    = $fav[$k]['act_range'];		//优惠活动范围代码{ 0:全部商品, 1:按分类, 2:按品牌, 3:按商品 }
		$gift_number = $fav[$k]['gift_number'];		//0元赠品数量{默认为0,表示这个参数无效，1：这个活动赠送1个，可以累加赠品。}
		$buy_number  = $fav[$k]['buy_number'];		//购买多少件商品才会有赠品{默认为1}
    	$gg          = $fav[$k]['gift'];  //赠品数组
		$fav_id      = $fav[$k]['act_id'];//活动ID
		$is_gift     = $fav_id;
		$is_duo      = $fav[$k]['is_duo'];//多买多送
		//-------------------------------------------------------【----------------------】-------------------------------------------------------//
		
		//购物车中全部应该享受该商品优惠活动的代码。
		$add_gift = false;
		switch($fav_kind)
		{
			case 0: $add_gift = true;  break;
			case 1:
				$bb = explode(",", $fav[$k]['act_range_ext']);//active range:by category.包括1,6,12,64,76父类.
				if(empty($bb) || empty($gg))
				{
					continue;
				}
				$goods_cat_id = get_cat_id($cur_gid);
				if(in_array($goods_cat_id, $bb))
				{
					$add_gift = true;
				}
				else
				{
					$gift_parent_id = $GLOBALS['db']->getOne("select parent_id from ecs_category where cat_id=".$goods_cat_id." limit 1;");
					if(in_array($gift_parent_id, $bb))
					{
						$add_gift = true;
					}
				}
				break;
			case 2:
				//---------------------------------------------------------------------------------------------------------------------------------------------------//
				$bb = explode(",", $fav[$k]['act_range_ext']);//active range:by brand。 
				if(empty($bb) || empty($gg))
				{
					continue;
				}
				$goods_brand = get_brand_id($cur_gid);
				if(in_array($goods_brand, $bb))
				{
					$add_gift = true;
				}
				//---------------------------------------------------------------------------------------------------------------------------------------------------//
				break;
			case 3:
				//---------------------------------------------------------------------------------------------------------------------------------------------------//
				$bb = explode(",",  $fav[$k]['act_range_ext']);//active range:by goods
				if(empty($bb) || empty($gg))
				{
					continue;
				}
				if(in_array($cur_gid, $bb))
				{
					$add_gift = true;
				}
				//---------------------------------------------------------------------------------------------------------------------------------------------------//
				break;
			default:
				break;
		}

		//赠品加入购物车逻辑
		if(true === $add_gift)
		{
			foreach($gg as $b => $bv)
			{
				$goods_id = $bv['id'];      //赠品ID
				$price    = $bv['price'];	//赠品价格
				$gift_num = !empty($bv['number'])? intval($bv['number']): 1;  //赠品数量
				$gift_ds  = empty($bv['selectds'])?'':$bv['selectds'];//赠品是否要选择度数
				if($is_duo)
				{							
					$sql      = "select sum(goods_number) as cart_goods_num from ecs_cart where goods_id='$cur_gid' and session_id='".SESS_ID."' and is_gift=0;";
					$cart_goods_num = $GLOBALS['db']->getOne($sql);							
					if($cart_goods_num>0)
					{
						$give_num = (floor($cart_goods_num/$buy_number))*$gift_number;	
						
						if(!have_fav($goods_id, $is_gift, $price) && $give_num>0)
						{
							insert_cart($goods_id, $give_num, $is_gift, $price, 'unchange');
						}
						else
						{
							if($give_num>0)
							{
								//更新购物车该赠品数
								$sql = "select sum(goods_number) as give_num from ecs_cart where goods_id='$goods_id' and is_gift='$fav_id' and session_id='".SESS_ID."' ";
								$cart_give_num = $GLOBALS['db']->getOne($sql);
								if($give_num != $cart_give_num)
								{
									$sql = "update ecs_cart set goods_number=".$give_num." where goods_id='$goods_id' and is_gift='$fav_id' and session_id='".SESS_ID."';";
									mysql_query($sql);
								}
							}
							else
							{
								$sql = "delete from ecs_cart where goods_id='$goods_id' and is_gift='$fav_id' and session_id='".SESS_ID."';";
								mysql_query($sql);
							}
						}
					}							
				}
				else//非多买多送
				{
					if($gift_ds == 0)
					{
						if(!have_fav($goods_id, $is_gift, $price))
						{
							insert_cart($goods_id, $gift_num, $is_gift, $price, 'unchange');
						}
						else
						{
							//更新购物车该赠品数
							$sql = "select sum(goods_number) as give_num from ecs_cart where goods_id='$goods_id' and is_gift='$fav_id' and session_id='".SESS_ID."' ;";
							$cart_give_num = $GLOBALS['db']->GetOne($sql);
							if($gift_num != $cart_give_num)
							{
								$sql = "update ecs_cart set goods_number=".$gift_num." where goods_id='$goods_id' and is_gift='$fav_id' and session_id='".SESS_ID."' ;";
								mysql_query($sql);
							}
						}
					}
				}
			}			
		}//end add_gift
	}
}




/* ----------------------------------------------------------------------------------------------------------------------
 * yi:购物车中是否已经有【某个活动下】的0元赠品 true:有。
 * ----------------------------------------------------------------------------------------------------------------------
 */
function have_fav($goods_id=0, $is_gift=0, $price=0)
{
	$tsql = ($is_gift>0)? " and is_gift=".$is_gift: " and is_gift>0";	
	$psql = ($is_gift>0 && $price>0)? " and goods_price=".$price: " and goods_price=0";
	$sql  = "select * from ecs_cart where session_id='".SESS_ID."' and rec_type=0 ".$tsql." ".$psql." and goods_id=".$goods_id." limit 1;";
	$res  = $GLOBALS['db']->getRow($sql);
	return (empty($res)? false: true);
}


//yi:根据购物车中优惠商品取得符合其条件的优惠的商品list
function fav_goods_list($goods_id){
	$now  = gmtime();
	$sql  = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time` <= '$now' AND `end_time` >= '$now' and min_amount=0 and max_amount =0 ORDER BY `start_time` desc,`end_time` desc";
	$resf = $GLOBALS['db']->query($sql);
	$fav  = array();
	while( $favr = $GLOBALS['db']->fetchRow($resf)){
		$favr['gift'] = unserialize($favr['gift']);
		$fav[] = $favr;
	}	
	//print_r($fav);
	//echo "<br/><br/>";
	$dd = array();
	for( $i=0; $i< count($fav); $i++){
		$fav_kind = $fav[$i]['act_range'];//优惠范围；0，全部商品；1，按分类；2，按品牌；3，按商品
		$rt = array();
		if($fav_kind == 0){
			$rt= $fav[$i];	
			
		}else if($fav_kind == 1){
			$aa = $fav[$i]['act_range_ext'];
			$bb = explode(",",$aa);      //有优惠的cart_id数组,包括1,6,12,64,76顶级分类。 
			/*处理顶级分类*/		
			for($n=0; $n<count($bb); $n++){
				$cat_id = $bb[$n];
				if($cat_id==1||$cat_id==6||$cat_id==12||$cat_id==64||$cat_id==76){
					//-------------------从分类表找出所有的子分类--------------------------------
					$topcat = array();
					$sql = "select cat_id from ".$GLOBALS['ecs']->table('category')." where parent_id=".$cat_id.";";
					$res = $GLOBALS['db']->query($sql);
					while($row = $GLOBALS['db']->fetchRow($res)){
							//把这些重新加入到分类数组中
							$topcat[] = $row['cat_id'];
					}
					//把这些分类加入到cat_id数组中
					$bb = array_merge($bb,$topcat);
				}
			}
			for($n=0; $n<count($bb); $n++){
				$cat_id = $bb[$n];
				//-------------------参加优惠的品牌的所有商品--------------------------------
				$sql = "select goods_id from ".$GLOBALS['ecs']->table('goods')." where cat_id=".$cat_id.";";
				$res = $GLOBALS['db']->query($sql);
				while($row = $GLOBALS['db']->fetchRow($res)){
					if($row['goods_id'] == $goods_id){
						//print_r($fav[$i]);
						$rt = $fav[$i];
					}
				}	
			}
		}else if($fav_kind == 2){
			$aa = $fav[$i]['act_range_ext'];
			$bb = explode(",",$aa);           //有优惠的brand_id数组   
			for($k=0; $k<count($bb); $k++){
				$brand_id = $bb[$k];
				//-------------------参加优惠的品牌的所有商品--------------------------------
				$sql = "select goods_id from ".$GLOBALS['ecs']->table('goods')." where brand_id=".$brand_id.";";
				$res = $GLOBALS['db']->query($sql);
				while($row = $GLOBALS['db']->fetchRow($res)){
					if($row['goods_id'] == $goods_id){
						$rt = $fav[$i];
					}
				}
			}
		}else if($fav_kind == 3){
			$aa = $fav[$i]['act_range_ext'];
			$bb = explode(",",$aa);           //有优惠的商品数组
			for($j=0; $j< count($bb);$j++){
				if($bb[$j] == $goods_id){
					$rt = $fav[$i];
				}
			}		
		}else{
			//没有赠品
		}
		if(!empty($rt)){
			array_push($dd,$rt);
		}
	}
	return $dd;
}

/**
 * 获得用户的可用积分
 * @access  private
 * @return  integral
 */
function flow_available_points()
{
    $sql = "SELECT SUM(g.integral * c.goods_number) ".
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.session_id = '" . SESS_ID . "' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";

    $val = intval($GLOBALS['db']->getOne($sql));

    return integral_of_value($val);
}

/* -------------------------------------------------------------------------------------------------
 * yi:更新购物车中的商品数量
 * -------------------------------------------------------------------------------------------------
 * @$arr:要更新的商品数组. $zb:商品左眼数量. $yb:商品右眼数量.
 */
function flow_update_cart($arr, $zb, $yb)
{ 
	$restr = null;
    foreach($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
		//初始化
		$sszb = array(); 
		$ssyb = array();
        if($val <= 0)
		{
			continue;
        }

		//查询需要的数据
        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code`,`goods_price` FROM" .$GLOBALS['ecs']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);
		$restr = $goods['goods_price'];

        $sql = "SELECT g.goods_name, g.goods_number ".
                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
                    $GLOBALS['ecs']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

        $row = $GLOBALS['db']->getRow($sql);

        /* 系统启用了库存，检查输入的商品数量是否有效 */
        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
                exit;
            }
        }

        /* 检查该项是否为基本件以及有没有配件存在 */
        $sql = "SELECT a.goods_number, a.rec_id FROM " .$GLOBALS['ecs']->table('cart') . " AS b ".
                "LEFT JOIN " . $GLOBALS['ecs']->table('cart') . " AS a ".
                "ON a.parent_id = b.goods_id AND a.session_id = '" . SESS_ID . "' AND a.extension_code <> 'package_buy'".
                "WHERE b.rec_id = '$key'";

        $fittings = $GLOBALS['db']->getAll($sql);

        if ($val > 0)
        {
            foreach ($fittings AS $k => $v)
            {
                if ($v['goods_number'] != null && $v['rec_id'] != null)
                {
                    /* 该商品有配件，更新配件的商品数量 */
                    $num = ($v['goods_number']) > $val ? $val : $v['goods_number'];

                    $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') .
                            " SET goods_number = '$num' WHERE rec_id = $v[rec_id]";
                    $GLOBALS['db']->query($sql);
                }
            }
//----------------------更新购物车中的商品数量--------------------------------
            if ($goods['extension_code'] == 'package_buy')
            {
                //商品为大礼包的情况---更新数量----
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',zcount='".$sszb[$key]."',ycount='".$ssyb[$key]."' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
            else
            {
                $attr_id    = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);
                $goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);
				
                /* 更新购物车中的商品数量 */
				if($zb == 0 && $yb>0){
					$sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',ycount='".$yb."', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
				}
				if($yb == 0 && $zb>0){
					$sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',zcount='".$zb."', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
				}
				if($zb>0 && $yb>0){
					$sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',zcount='".$zb."',ycount='".$yb."', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
				}
            }
        }
        else
        {	//清空购物车的情况
            if (is_object($fittings) && $fittings->goods_number != null && $fittings->rec_id != null)
            {
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart'). " WHERE rec_id=$fittings[rec_id]";
                $GLOBALS['db']->query($sql);
            }

            $sql = "DELETE FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "'";
        }

        $GLOBALS['db']->query($sql);
    }

    /* 删除所有赠品 yi:2012/9/20 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" .SESS_ID. "' AND is_gift>50;";
    //$GLOBALS['db']->query($sql);
	return $restr;
}

//---------------------重写更新购物车：一个参数的数量更新--------------------------------------------------------------
function flow_update_cart2($arr)
{ 
    foreach ($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
		//有左右眼数量的情况
		$sszb = array(); $ssyb = array();
        if($val <= 0){
            continue;
        }
		//查询需要的数据
        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code`,`goods_price` FROM" .$GLOBALS['ecs']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);
		$restr = $goods['goods_price'];

        $sql = "SELECT g.goods_name, g.goods_number ".
                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
                    $GLOBALS['ecs']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

        $row = $GLOBALS['db']->getRow($sql);

        /* 系统启用了库存，检查输入的商品数量是否有效 */
        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
                exit;
            }
        }

        /* 检查该项是否为基本件以及有没有配件存在 */
        $sql = "SELECT a.goods_number, a.rec_id FROM " .$GLOBALS['ecs']->table('cart') . " AS b ".
                "LEFT JOIN " . $GLOBALS['ecs']->table('cart') . " AS a ".
                    "ON a.parent_id = b.goods_id AND a.session_id = '" . SESS_ID . "' AND a.extension_code <> 'package_buy'".
                "WHERE b.rec_id = '$key'";

        $fittings = $GLOBALS['db']->getAll($sql);

        if ($val > 0)
        {
            foreach ($fittings AS $k => $v)
            {
                if ($v['goods_number'] != null && $v['rec_id'] != null)
                {
                    /* 该商品有配件，更新配件的商品数量 */
                    $num = ($v['goods_number']) > $val ? $val : $v['goods_number'];

                    $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') .
                            " SET goods_number = '$num' WHERE rec_id = $v[rec_id]";
                    $GLOBALS['db']->query($sql);
                }
            }
//----------------------更新购物车中的商品数量--------------------------------
            if ($goods['extension_code'] == 'package_buy')
            {
                //商品为大礼包的情况---更新数量----
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',zcount='".$sszb[$key]."',ycount='".$ssyb[$key]."' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
            else
            {
                $attr_id    = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);
                $goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);

                /* 更新购物车中的商品数量 */
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',zcount='".$sszb[$key]."',ycount='".$ssyb[$key]."', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
        }
        else
        {	//清空购物车的情况
            if (is_object($fittings) && $fittings->goods_number != null && $fittings->rec_id != null)
            {
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart'). " WHERE rec_id=$fittings[rec_id]";
                $GLOBALS['db']->query($sql);
            }

            $sql = "DELETE FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "'";
        }

        $GLOBALS['db']->query($sql);
    }

    /* 删除所有赠品 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" .SESS_ID. "' AND is_gift <> 0";
    //$GLOBALS['db']->query($sql);
	return $restr;
}

/**
 * 检查订单中商品库存
 *
 * @access  public
 * @param   array   $arr
 * @return  void
 */
function flow_cart_stock($arr)
{
    foreach ($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
        if ($val <= 0)
        {
            continue;
        }

        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code` FROM" .$GLOBALS['ecs']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number ".
                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
                    $GLOBALS['ecs']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

        $row = $GLOBALS['db']->getRow($sql);

        /* 系统启用了库存，检查输入的商品数量是否有效 */
        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
                exit;
            }
        }
    }

}


/* ----------------------------------------------------------------------------------------------------------------------
 * 删除购物车中的商品。 id:购物车ID.
 * ----------------------------------------------------------------------------------------------------------------------
 * yi修改：去掉or (is_gift <> 0 and is_gift<>888 and is_gift<>70 and goods_price=0)
 */
function flow_drop_cart_goods($id=0)
{
    $row = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id='$id' limit 1;");
    if($row)
    {
    	//yi:删除组合购买的主体商品，则更新组合购买商品。
    	$sql = "select * from ecs_cart where session_id='". SESS_ID ."' and extension_code='group_buy' and extension_id=".$row['goods_id'];
    	$group_buy = $GLOBALS['db']->getAll($sql);
    	if(!empty($group_buy))
    	{
    		foreach($group_buy as $k => $v)
    		{    			
    			$shop_price = $GLOBALS['db']->getOne("select shop_price from ecs_goods where goods_id=".$group_buy[$k]['goods_id']);
    			if($shop_price>0)
    			{
	    			$sql = "update ecs_cart set goods_price='$shop_price', extension_code='', extension_id=0 where rec_id=".$group_buy[$k]['rec_id'];
	    			$GLOBALS['db']->query($sql);
    			}
    		}
    	}
    	
    	//2013.12.18新增删除随心配商品(parent_id为主商品的rec_id)
    	if ($row['extension_code'] == 'unchange' && $row['extension_id'] == 1212)
    	{
    		if($row['parent_id'] == 0)
    		{
    			$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND (rec_id=$id OR parent_id=$id)";
    		}
    		else 
    		{
    			$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND (rec_id=$id OR rec_id=".$row['parent_id'].")";
    		}
    	}
    	else
    	{
	        //删除普通商品，同时删除其配件
	        if($row['parent_id'] == 0 && $row['is_gift'] == 0)
	        {
	            $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' ".
	                   "AND (rec_id='$id' or parent_id='$row[goods_id]')";
	        }
	        else//删除非普通商品，只删除该商品即可
	        {
	            $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_id='$id' limit 1;";
	        }
    	}
        $GLOBALS['db']->query($sql);
    }
}

/**
 * 比较优惠活动的函数，用于排序（把可用的排在前面）
 * @param   array   $a      优惠活动a
 * @param   array   $b      优惠活动b
 * @return  int     相等返回0，小于返回-1，大于返回1
 */
function cmp_favourable($a, $b)
{
    if ($a['available'] == $b['available'])
    {
        if ($a['sort_order'] == $b['sort_order'])
        {
            return 0;
        }
        else
        {
            return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
        }
    }
    else
    {
        return $a['available'] ? -1 : 1;
    }
}

/**
 * 取得某用户等级当前时间可以享受的优惠活动
 * @param   int     $user_rank      用户等级id，0表示非会员
 * @return  array
 */
function favourable_list($user_rank)
{
    /* 购物车中已有的优惠活动及数量 */
    $used_list = cart_favourable();

    /* 当前用户可享受的优惠活动 */
    $favourable_list = array();
    $user_rank = ',' . $user_rank . ',';
    $now = gmtime();
    $sql = "SELECT * " .
            "FROM " . $GLOBALS['ecs']->table('favourable_activity') .
            " WHERE CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND start_time <= '$now' AND end_time >= '$now'" .
            " AND act_type = '" . FAT_GOODS . "'" .
            " ORDER BY sort_order";
    $res = $GLOBALS['db']->query($sql);
    while ($favourable = $GLOBALS['db']->fetchRow($res))
    {
        $favourable['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $favourable['start_time']);
        $favourable['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $favourable['end_time']);
        $favourable['formated_min_amount'] = price_format($favourable['min_amount'], false);
        $favourable['formated_max_amount'] = price_format($favourable['max_amount'], false);
        $favourable['gift']       = unserialize($favourable['gift']);
        foreach ($favourable['gift'] as $key => $value)
        {
            $favourable['gift'][$key]['formated_price'] = price_format($value['price'], false);
        }

        $favourable['act_range_desc'] = act_range_desc($favourable);
        $favourable['act_type_desc'] = sprintf($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']], $favourable['act_type_ext']);

        /* 是否能享受 */
        $favourable['available'] = favourable_available($favourable);
        if ($favourable['available'])
        {
            /* 是否尚未享受 */
            $favourable['available'] = !favourable_used($favourable, $used_list);
        }

        $favourable_list[] = $favourable;
    }

    return $favourable_list;
}

/**
 * 根据购物车判断是否可以享受某优惠活动
 * @param   array   $favourable     优惠活动信息
 * @return  bool
 */
function favourable_available($favourable)
{
    /* 会员等级是否符合 */
    $user_rank = $_SESSION['user_rank'];
    if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank . ',') === false)
    {
        return false;
    }

    /* 优惠范围内的商品总额 */
    $amount = cart_favourable_amount($favourable);

    /* 金额上限为0表示没有上限 */
    return $amount >= $favourable['min_amount'] &&
        ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);
}

/**
 * 取得优惠范围描述
 * @param   array   $favourable     优惠活动
 * @return  string
 */
function act_range_desc($favourable)
{
    if ($favourable['act_range'] == FAR_BRAND)
    {
        $sql = "SELECT brand_name FROM " . $GLOBALS['ecs']->table('brand') .
                " WHERE brand_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    elseif ($favourable['act_range'] == FAR_CATEGORY)
    {
        $sql = "SELECT cat_name FROM " . $GLOBALS['ecs']->table('category') .
                " WHERE cat_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    elseif ($favourable['act_range'] == FAR_GOODS)
    {
        $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('goods') .
                " WHERE goods_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    else
    {
        return '';
    }
}

/**
 * 取得购物车中已有的优惠活动及数量
 * @return  array
 */
function cart_favourable()
{
    $list = array();
    $sql = "SELECT is_gift, COUNT(*) AS num " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "'" .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'" .
            " AND is_gift > 0" .
            " GROUP BY is_gift";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $list[$row['is_gift']] = $row['num'];
    }

    return $list;
}

/**
 * 购物车中是否已经有某优惠
 * @param   array   $favourable     优惠活动
 * @param   array   $cart_favourable购物车中已有的优惠活动及数量
 */
function favourable_used($favourable, $cart_favourable)
{
    if ($favourable['act_type'] == FAT_GOODS)
    {
        return isset($cart_favourable[$favourable['act_id']]) &&
            $cart_favourable[$favourable['act_id']] >= $favourable['act_type_ext'] &&
            $favourable['act_type_ext'] > 0;
    }
    else
    {
        return isset($cart_favourable[$favourable['act_id']]);
    }
}
//----------------------------------------------------------特惠商品插入购物车------------------------------------------------------------------
//return rec_id;//赠品不可修改数量。
function add_gift_to_cart2($act_id, $goods_id, $price, $num, $zselect, $zcount, $yselect, $ycount){

	$sql = "insert into ".$GLOBALS['ecs']->table('cart')." (".
		   "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, goods_number, is_real, extension_code, parent_id, is_gift, rec_type) ".
           "SELECT '$_SESSION[user_id]', '" . SESS_ID . "', goods_id, goods_sn, goods_name, market_price, ".
           "'$price', 1, is_real, 'unchange', 0, '$act_id', '" . CART_GENERAL_GOODS . "' " .
           "FROM ".$GLOBALS['ecs']->table('goods').
           " WHERE goods_id = '$goods_id'";
	$GLOBALS['db']->query($sql);
	
	//xu:130806修改,获取刚才插入的自增id,并应用到下面的update语句中
	$temp_new_rec_id = mysql_insert_id();

	//$sqlu = "update ".$GLOBALS['ecs']->table('cart')." set goods_number='$num', zselect='$zselect',zcount='$zcount',yselect='$yselect',ycount='$ycount' where session_id='".SESS_ID."' and goods_id='$goods_id' and is_gift='$act_id' ";
	$sqlu = "update ".$GLOBALS['ecs']->table('cart')." set goods_number='$num', zselect='$zselect',zcount='$zcount',yselect='$yselect',ycount='$ycount' where rec_id='".$temp_new_rec_id."' and goods_id='$goods_id' and is_gift='$act_id' ";
	$GLOBALS['db']->query($sqlu);
	//返回购物车单号
	$sqlr = "select rec_id from ".$GLOBALS['ecs']->table('cart')." where session_id='".SESS_ID."' and goods_id='$goods_id' ";
	$rec = $GLOBALS['db']->GetOne($sqlr);
	return $rec;
}

/**
 * 添加优惠活动（赠品）到购物车
 * @param   int     $act_id     优惠活动id
 * @param   int     $id         赠品id
 * @param   float   $price      赠品价格
 */
function add_gift_to_cart($act_id, $id, $price)
{
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . " (" .
                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".
                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".
            "SELECT '$_SESSION[user_id]', '" . SESS_ID . "', goods_id, goods_sn, goods_name, market_price, ".
                "'$price', 1, is_real, extension_code, 0, '$act_id', '" . CART_GENERAL_GOODS . "' " .
            "FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE goods_id = '$id'";
    $GLOBALS['db']->query($sql);
}


/**
 * 添加优惠活动（非赠品）到购物车
 * @param   int     $act_id     优惠活动id
 * @param   string  $act_name   优惠活动name
 * @param   float   $amount     优惠金额
 */
function add_favourable_to_cart($act_id, $act_name, $amount)
{
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(" .
                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".
                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".
            "VALUES('$_SESSION[user_id]', '" . SESS_ID . "', 0, '', '$act_name', 0, ".
                "'" . (-1) * $amount . "', 1, 0, '', 0, '$act_id', '" . CART_GENERAL_GOODS . "')";
    $GLOBALS['db']->query($sql);
}

/**
 * 取得购物车中某优惠活动范围内的总金额
 * @param   array   $favourable     优惠活动
 * @return  float
 */
function cart_favourable_amount($favourable)
{
    /* 查询优惠范围内商品总额的sql */
    $sql = "SELECT SUM(c.goods_price * c.goods_number) " .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id > 0 ";

    /* 根据优惠范围修正sql */
    if ($favourable['act_range'] == FAR_ALL)
    {
        // sql do not change
    }
    elseif ($favourable['act_range'] == FAR_CATEGORY)
    {
        /* 取得优惠范围分类的所有下级分类 */
        $id_list = array();
        $cat_list = explode(',', $favourable['act_range_ext']);
        foreach ($cat_list as $id)
        {
            $id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0, false)));
        }

        $sql .= "AND g.cat_id " . db_create_in($id_list);
    }
    elseif ($favourable['act_range'] == FAR_BRAND)
    {
        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.brand_id " . db_create_in($id_list);
    }
    else
    {
        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.goods_id " . db_create_in($id_list);
    }

    /* 优惠范围内的商品总额 */
    return $GLOBALS['db']->getOne($sql);
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:返回返利比例。用2位小数表示。
 * ----------------------------------------------------------------------------------------------------------------------
 */
function goods_cat_cd_bili($c_cd='')
{
	$bili = 0.00;
	if(!empty($c_cd))
	{
		$zhitui = array(
			'A'=>0.08,
			'B'=>0.15,
			'C'=>0.07,
			'D'=>0.28,
			'E'=>0.025,
			'F'=>0.015,
			'G'=>0.00,
			'H'=>0.11,
			'I'=>0.21,
			'J'=>0.11			
		);
		$linktech = array(
			'A'=>0.08,
			'B'=>0.15,
			'C'=>0.07,
			'D'=>0.28,
			'E'=>0.025,
			'F'=>0.015,
			'G'=>0.00,
			'H'=>0.11,
			'I'=>0.21,
			'J'=>0.11			
		);
		$bili = $zhitui[$c_cd];
	}
	return $bili;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:获得该商品的c_cd编号。
 * ----------------------------------------------------------------------------------------------------------------------
 * package：是否礼包商品
	---------------------------------------------------------------------------------------------------------------------
	linktech返利规则[2013/7/18]
	/----------------------------------------------------------------------/
        类别           佣金比例      c_cd编号
	/----------------------------------------------------------------------/
	普通隐形眼镜         8%              A   

	彩色隐形眼镜         15%             B    

	护理液润眼液         7%              C

	护理工具             28%			  D   //QQ彩贝中护理工具是35%

	强生博士伦           2.5%			  E

	礼包，促销/特价抢购  1.5%			  F

	me&city,班尼路        11%			  H

	框架眼镜单品≤250元   21%             I

	太阳眼镜,框架单品>250元   11%        J

	后来新增类别(待定)  0%               G	
	/----------------------------------------------------------------------/
 */
function goods_cat_cd($goods_id=0, $package=false)
{
	$c_cd = '';
	if(empty($goods_id)){return false;}

	//商品分类
	$cat_arr = $GLOBALS['db']->getRow('select cat_id,is_cx,shop_price from '.$GLOBALS['ecs']->table('goods').' where goods_id='.$goods_id.' limit 1;');
	$cat_id  = !empty($cat_arr)? intval($cat_arr['cat_id']): 0;
	
	if($package || $cat_arr['is_cx'] || $cat_id==138)
	{
		$c_cd = 'F';    //礼包/特价商品/促销商品
	}
	else
	{		
		if(in_array($cat_id, array(4,5,29,65,134,154)))
		{
			$c_cd = 'E';//强生/博士伦/视康睛彩
		}
		elseif($cat_id==175 || $cat_id==177)
		{
			$c_cd = 'H';
		}
		else
		{
			//商品父分类
			$p_id = $GLOBALS['db']->getOne('select parent_id from '.$GLOBALS['ecs']->table('category').' where cat_id='.$cat_id.' limit 1;');
			switch($p_id)
			{
				case 1: 
					$c_cd = 'A'; //透明片
					break;
				case 6: 
					$c_cd = 'B'; //彩色片
					break;
				case 64: 
					$c_cd = 'C'; //护理液
					break;
				case 76: 
					$c_cd = 'D'; //护理工具
					break;
				case 159: 
					$c_cd = ($cat_arr['shop_price'] > 250)? 'J': 'I';//框架眼镜
					break;
				case 190: 
					$c_cd = 'J'; //太阳眼镜
					break;
				default:
					$c_cd = 'G'; //后来新增类别(待定)
					break;
			}
		}
	}
	return $c_cd;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:linktech返利编码函数
 * ----------------------------------------------------------------------------------------------------------------------
 * linktech返利规则，分类和51返利一样
 */
function goods_cat_cd2($goods_id=0, $package = false)
{
	$c_cd = '';
	if(empty($goods_id)){return false;}

	//商品分类
	$cat_arr = $GLOBALS['db']->getRow('select cat_id,is_cx from '.$GLOBALS['ecs']->table('goods').' where goods_id='.$goods_id);	
	$cat_id = !empty($cat_arr)? intval($cat_arr['cat_id']): 0;

	if(is_tejia($goods_id, $_SERVER['REQUEST_TIME'])||$package)
	{
		$c_cd = 'F';
	}
	elseif($cat_arr['is_cx']==1)
	{
		$c_cd = 'F';
	}
	else
	{	
		//单独列出的分类：cat_id=4 5 29 134 65（博士伦，博士伦护理液，博士伦蕾丝，强生，强生美瞳，视康睛美）
		if($cat_id==4||$cat_id==5||$cat_id==29||$cat_id==65||$cat_id==134||$cat_id==154)
		{
			$c_cd = 'E';
		}
		elseif($cat_id==138)
		{
			$c_cd = 'F';
		}
		elseif($cat_id==175 || $cat_id==177)
		{
			$c_cd = 'H';
		}
		else
		{
			//商品分类的父类
			$sqls = 'select parent_id from '.$GLOBALS['ecs']->table('category').' where cat_id='.$cat_id.';';
			$p_id = $GLOBALS['db']->getOne($sqls);
			switch($p_id)
			{
				case 1: 
					$c_cd = 'A'; //透明片
					break;
				case 6: 
					$c_cd = 'B'; //彩色片
					break;
				case 64: 
					$c_cd = 'C'; //护理液
					break;
				case 76: 
					$c_cd = 'D'; //护理工具
					break;
				case 159: 
					$shop_price = $GLOBALS['db']->getOne("select shop_price from ecs_goods where goods_id=".$goods_id." limit 1;");
					$c_cd = ($shop_price > 250)? 'J': 'I';//框架眼镜
					break;
				case 190: 
					$c_cd = 'J'; //太阳眼镜
					break;
				default:
					$c_cd = 'G'; //后来新增类别(待定)
					break;
			}
		}
	}
	return $c_cd;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断是否特价抢购商品。 
 * ----------------------------------------------------------------------------------------------------------------------
 * buy_time:用的unix_timestamp.
 */
function is_tejia($goods_id=0, $buy_time=0)
{
	$sql = "SELECT goods_id FROM ".$GLOBALS['ecs']->table('goods')." WHERE `promote_price`>0 and `promote_start_date`<".
		   $buy_time." and `promote_end_date`>".$buy_time." AND goods_id=".$goods_id.";";
	$gd  = $GLOBALS['db']->getOne($sql);
	return !empty($gd)? true: false;
}


/* -------------------------------------------------------------------------------------------------
 * 函数yi：红包数组的红包是否合法
 * -------------------------------------------------------------------------------------------------
 * bonus_type_id：红包类型id
 * 红包有效，return true.  无效 return false;
 */
function user_bonus_validate($bonus_type_id=0)
{
	$ret       = '';                     //返回结果：error表示红包无效，right表示红包有效
	$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
	$tmp_carts = cart_goods($flow_type); //购物车商品列表
    $tmp_carts2 = $tmp_carts;            //定义tmp_carts用于判断是否存在促销产品从而剔除促销不可用红包
	$bonus     = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table("bonus_type")." where type_id= ".$bonus_type_id);
    
    /*
    促销商品可使用红包{
        原逻辑
    }促销不可用红包{
        有限定范围{
            计算出非促销商品的总价（指定范围）
        }无限定范围{
            计算出非促销商品的总价
        }
    }
    */
    if($bonus['cx_can_use'] == 1)
    {
        //yi:红包使用有限定购买商品范围：只有在范围内商品达到最小使用金额才能够使用这个红包
    	if($bonus['is_scope'] == 1 && !empty($bonus['scope_ext']))
    	{
    		$scope_m = 0 ;          //购物车中有效购物金额
    		if($bonus['scope'] == 1)//分类对待检查$bonus['scope']=1:按分类， 2，按品牌， 3，按商品
    		{
    			//统计购物车中商品在优惠范围内的优惠金额是多少
    			$cat_id = explode(',', $bonus['scope_ext']);
        
    			if(!empty($tmp_carts))
    			{
    				foreach($tmp_carts as $k => $v)
    				{
    					if($tmp_carts[$k]['is_gift']==0 && $tmp_carts[$k]['is_real']==1 && $tmp_carts[$k]['goods_price']>0)
    					{
    						$goods_cat_id = $GLOBALS['db']->GetOne('SELECT cat_id FROM '.$GLOBALS['ecs']->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
    						
                            if(in_array(strval($goods_cat_id), $cat_id))
    						{
    							$scope_m += $tmp_carts[$k]['subtotal'];
    						}	
    					}
    				}
    			}
    		}
    		else if($bonus['scope'] == 2)
    		{
    			//2.按品牌检查
    			$brands = explode(',', $bonus['scope_ext']);
    			if(!empty($tmp_carts))
    			{
    				foreach($tmp_carts as $k => $v)
    				{
    					if($tmp_carts[$k]['is_gift']==0 && $tmp_carts[$k]['is_real']==1 && $tmp_carts[$k]['goods_price']>0)
    					{
    						$goods_band_id = $GLOBALS['db']->GetOne('SELECT brand_id FROM '.$GLOBALS['ecs']->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
                            
    						if(in_array(strval($goods_band_id), $brands))
    						{
    							$scope_m += $tmp_carts[$k]['subtotal'];
    						}
    					}
    				}
    			}
    		}
    		else
    		{	//3.按商品检查
    			$goods = explode(',', $bonus['scope_ext']);
    			if(!empty($tmp_carts))
    			{
    				foreach($tmp_carts as $k=>$v)
    				{
    					if($tmp_carts[$k]['is_gift']==0 && $tmp_carts[$k]['is_real']==1 && $tmp_carts[$k]['goods_price']>0)
    					{
    						if(in_array(strval($tmp_carts[$k]['goods_id']), $goods))
    						{
    							$scope_m += $tmp_carts[$k]['subtotal'];
    						}
    					}					
    				}
    			}
    		}
            if($bonus['min_goods_amount'] == 0 ){//红包使用最小金额为0时，置为1 by:tao
                $bonus['min_goods_amount'] =1;
            }
            
    		if($bonus['min_goods_amount'] > $scope_m)
    		{
    			$ret = 'error';//条件不满足，红包无效
    		}
    	}
    	else
    	{
    		//yi：红包使用没有限定购买商品范围
    		$sql = "SELECT SUM(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_type = '$type' and is_gift=0 and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy'  and extension_code<>'exchange_buy' and extension_code<>'exchange' ";
    		if(!$bonus['cx_can_use'])
    		{
    			$sql .= " and is_cx=0 ";
    		}
    		$cart_amounts = $GLOBALS['db']->GetOne($sql);
    		if($bonus['min_goods_amount'] > $cart_amounts)
    		{
    			$ret = 'error';
    		}
    	}
    	
    }
    else
    {
       ###############################################################
       $no_cx_amount = 0;
       if($bonus['is_scope'] == 1 && !empty($bonus['scope_ext'])){
            $no_cx_amount = get_no_cx_amount($tmp_carts,$bonus['is_scope'],$bonus['scope'],$bonus['scope_ext']);
       }else{
            $no_cx_amount = get_no_cx_amount($tmp_carts);
       }
       
       if($bonus['min_goods_amount'] > $no_cx_amount)
	   {
			//$result['error'] = '您的有效购物金额(非促销商品)未达该券使用金额:'.$bonus['min_goods_amount'].',不能用该红包哦!';
            $ret = 'error';
	   }
       ##############################################################
        
    }
    if(empty($tmp_carts))
   	{
        $ret = 'error';
   	}
   	else
   	{
   	//if(((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
    //2013.12.06 去掉 $bonus['type_money'] > 0 的限制
	   if(((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || (empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
	   {
	       if($_SERVER['REQUEST_TIME']>$bonus['use_end_date'])//红包过期不能使用。
	       {
	           $ret = 'error';
	       }
	   }
	   else
	   {
            $ret = 'error';
	   }
   	}
    
	//yi:促销商品能否作用于红包金额累计
    /*
	if(!$bonus['cx_can_use'])
	{
		foreach($tmp_carts as $k1 => $v1)
		{
			//if($GLOBALS['total']['discount']>0 || $v1['is_cx'] || $v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
			if($v1['is_cx'] || $v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
			{
				unset($tmp_carts[$k1]);
			}
		}
        //查询是否有促销商品在购物车中 有的话剔除该红包 by:tao
        if(have_cx_in_cart($tmp_carts2)){
            $ret = 'error';
	    }
     }
     */
	return ($ret=='error')? false: true;
}

/* -------------------------------------------------------------------------------------------------
 * 函数yi：判断红包序列号是否有效
 * -------------------------------------------------------------------------------------------------
 * 红包有效，return true.  无效 return false;
 */
function bonus_sn_validate($bonus_sn = '')
{
	//模拟测试地址：http://localhost/flow.php?step=validate_bonus&bonus_sn=1048567650

	$ret = '';//返回结果：error：红包无效，right：红包有效。

	if(!empty($bonus_sn))
	{
		$bonus = (is_numeric($bonus_sn))? bonus_info(0, $bonus_sn): array();//获得红包的详细信息

		$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
		$consignee = get_consignee($_SESSION['user_id']);
		$tmp_carts = cart_goods($flow_type);//购物车商品列表
		$cart_goods= $tmp_carts;

		//yi:促销商品能否作用于红包
		if(!$bonus['cx_can_use'])
		{
			foreach($tmp_carts as $k1 => $v1)
			{
				if($v1['is_cx'] || $v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
				{
					unset($tmp_carts[$k1]);
				}
			}
		}

		//yi:红包使用时候限定购买商品范围：只有在范围内商品达到最小使用金额才能够使用这个红包
		if($bonus['is_scope'] == 1 && !empty($bonus['scope_ext']))
		{
			$scope_m = 0 ;          //有效金额
			if($bonus['scope'] == 1)//分类对待检查 1:按分类， 2，按品牌， 3，按商品
			{
				//统计购物车中商品在优惠范围内的优惠金额是多少
				$cat_id = explode(',', $bonus['scope_ext']);
				if(!empty($tmp_carts))
				{
					foreach($tmp_carts as $k => $v)
					{
						if($tmp_carts[$k]['is_gift']==0 && $tmp_carts[$k]['is_real']==1 && $tmp_carts[$k]['goods_price']>0)
						{
							$goods_cat_id = $GLOBALS['db']->GetOne('SELECT cat_id FROM '.$GLOBALS['ecs']->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
							if(in_array(strval($goods_cat_id), $cat_id))
							{
								$scope_m += $tmp_carts[$k]['subtotal'];
							}
						}
					}
				}
			}
			else if($bonus['scope'] == 2)
			{
				//2.按品牌检查
				$brands = explode(',', $bonus['scope_ext']);
				if(!empty($tmp_carts))
				{
					foreach($tmp_carts as $k => $v)
					{
						if($tmp_carts[$k]['is_gift']==0 && $tmp_carts[$k]['is_real']==1 && $tmp_carts[$k]['goods_price']>0)
						{
							$goods_band_id = $GLOBALS['db']->GetOne('SELECT brand_id FROM '.$GLOBALS['ecs']->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
							if(in_array(strval($goods_band_id), $brands))
							{
								$scope_m += $tmp_carts[$k]['subtotal'];
							}
						}
					}
				}
			}
			else
			{	//3.按商品检查
				$goods = explode(',', $bonus['scope_ext']);
				if(!empty($tmp_carts))
				{
					foreach($tmp_carts as $k=>$v)
					{
						if($tmp_carts[$k]['is_gift']==0 && $tmp_carts[$k]['is_real']==1 && $tmp_carts[$k]['goods_price']>0)
						{
							if(in_array(strval($tmp_carts[$k]['goods_id']), $goods))
							{
								$scope_m += $tmp_carts[$k]['subtotal'];
							}
						}					
					}
				}
			}		

			
			if($bonus['min_goods_amount'] > $scope_m)
			{
				$ret = 'error';//条件不满足，红包无效
			}
		}
		else
		{
			//yi：红包使用没有限定购买商品范围
			@$sql = "SELECT SUM(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_type = '$type' and is_gift=0 and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' ";
			if(!$bonus['cx_can_use'])
			{
				$sql .= " and is_cx=0 ";
			}
			$cart_amounts = $GLOBALS['db']->GetOne($sql);
			if($bonus['min_goods_amount'] > $cart_amounts)
			{
				$ret = 'error';
			}
		}

		if(empty($tmp_carts))
		{
			$ret = 'error';
		}
		else
		{
			if(((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
			{
				if($_SERVER['REQUEST_TIME']>$bonus['use_end_date'])
				{
					$ret = 'error';//红包已经过期
				}
			}
			else
			{
				$ret = 'error';
			}
		}
	}
	return ($ret=='error' || empty($bonus_sn))? false: true;
}

//查询完全重复的赠品记录，并合并为一条(删除多余的)
function merge_same_gift()
{
	$same_gif = $GLOBALS['db']->GetAll("SELECT * FROM ecs_cart a WHERE a.session_id='".SESS_ID."' 
					AND (a.session_id, a.goods_id, a.goods_number, a.goods_attr, a.extension_code, a.parent_id, a.is_gift, a.goods_attr_id, a.zselect, a.zcount, a.yselect, a.ycount) 
					IN (SELECT session_id, goods_id, goods_number, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, zcount, yselect, ycount FROM ecs_cart 
					GROUP BY session_id, goods_id, goods_number, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, zcount, yselect, ycount HAVING COUNT(*) > 1)");
	if (count($same_gif) > 0)
	{
		//判断goods_id是否相同(即是否有多个goods_id的完全重复记录)
		$cart_goods_id_array = array();
		foreach ($same_gif as $key => $v)
		{
			$cart_goods_id_array[] = $v['goods_id'];
		}
		$cart_goods_id_array = array_unique($cart_goods_id_array); //去除重复
		
		if (count($cart_goods_id_array) > 1)
		{
			//有不同goods_id的重复值,则分别合并不同重复的good_id
			foreach ($cart_goods_id_array as $gv)
			{
				$same_goods = $GLOBALS['db']->GetAll("SELECT * FROM ecs_cart a WHERE a.session_id='".SESS_ID."' AND a.goods_id='".$gv."'
					AND (a.session_id, a.goods_id, a.goods_number, a.goods_attr, a.extension_code, a.parent_id, a.is_gift, a.goods_attr_id, a.zselect, a.zcount, a.yselect, a.ycount) 
					IN (SELECT session_id, goods_id, goods_number, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, zcount, yselect, ycount FROM ecs_cart 
					GROUP BY session_id, goods_id, goods_number, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, zcount, yselect, ycount HAVING COUNT(*) > 1)");
				if (count($same_goods) > 0)
				{
					$saved_rec_id = 0;		//欲保留的记录的rec_id
					$del_rec_id = array();	//欲删除的记录rec_id数组
					$total_goods_number = 0;
					$total_zcount = 0;
					$total_ycount = 0;
					foreach ($same_goods as $key => $v)
					{
						if ($saved_rec_id == 0)
						{
							$saved_rec_id = $v['rec_id'];
						}
						else
						{
							$del_rec_id[] = $v['rec_id'];
						}
						
						$total_goods_number += $v['goods_number'];
						$total_zcount += intval($v['zcount']);
						$total_ycount += intval($v['ycount']);
					}
					
					//删除多余的记录
					foreach ($del_rec_id as $v_rec)
					{
						$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_id='".$v_rec."' limit 1;");
					}
					
					//更新保留记录的数量
					$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=$total_goods_number, zcount='$total_zcount', ycount='$total_ycount' WHERE rec_id='$saved_rec_id'");
				}
			}
		}
		else
		{
			$saved_rec_id = 0;		//欲保留的记录的rec_id
			$del_rec_id = array();	//欲删除的记录rec_id数组
			$total_goods_number = 0;
			$total_zcount = 0;
			$total_ycount = 0;
			foreach ($same_gif as $key => $v)
			{
				if ($saved_rec_id == 0)
				{
					$saved_rec_id = $v['rec_id'];
				}
				else
				{
					$del_rec_id[] = $v['rec_id'];
				}
				
				$total_goods_number += $v['goods_number'];
				$total_zcount += intval($v['zcount']);
				$total_ycount += intval($v['ycount']);
			}
			
			//删除多余的记录
			foreach ($del_rec_id as $v_rec)
			{
				$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_id='".$v_rec."' limit 1;");
			}
			
			//更新保留记录的数量
			$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=$total_goods_number, zcount='$total_zcount', ycount='$total_ycount' WHERE rec_id='$saved_rec_id'");
		}
		
	}
	
}

function get_district_lsit($type=0, $parent=0)
{
	return $GLOBALS['db']->GetAll("SELECT region_id, region_name FROM " . $GLOBALS['ecs']->table('region') . " WHERE region_type = ".intval($type)." AND parent_id = ".intval($parent));
}

/**
 * 手机端检查收货人信息是否完整
 * @param   array   $consignee  收货人信息
 * @param   int     $flow_type  购物流程类型
 * @return  bool    true 完整 false 不完整
 */
function check_consignee_info_wap($consignee, $flow_type)
{
    if(exist_real_goods(0, $flow_type))
    {
        //如果存在实体商品
        $res = !empty($consignee['consignee']) &&
//             !empty($consignee['country']) &&
               (!empty($consignee['tel'])||!empty($consignee['mobile']));
/*
        if($res)
        {
            if(empty($consignee['province']))
            {
                //没有设置省份，检查当前国家下面有没有设置省份
                $pro = get_regions(1, $consignee['country']);
                $res = empty($pro);
            }
            elseif (empty($consignee['city']))
            {
                //没有设置城市，检查当前省下面有没有城市
                $city = get_regions(2, $consignee['province']);
                $res = empty($city);
            }
            elseif(empty($consignee['district']))
            {
                $dist = get_regions(3, $consignee['city']);
                $res = empty($dist);
            }
        }
*/
        return $res;
    }
    else
    {
        //不存在实体商品的返回结果
        return !empty($consignee['consignee']) &&
               !empty($consignee['tel']);
    }
}

/**
 * 查询购物车中是否有促销商品（促销包括:is_cx=1 act_type=1[立减] act_type=2[折扣]）
 */
function have_cx_in_cart($cart_goods){
    $have_cx = 0;
    foreach($cart_goods as $k1 => $v1)
    {
    	if($v1['extension_code'] != 'unchange'){
            //yi:每个产品需查询是否正在做活动
            $fav_list = include_goods_fav($v1['goods_id']);
        }else{
            $fav_list = array();   
        }
        
        if($v1['is_cx'] == 1){//标识促销商品的商品
            $have_cx = 1;
        }
        foreach($fav_list as $v){//享受立减和折扣的商品
            //if($v['act_type'] == '1' || $v['act_type'] == '2'){
            if(($v['act_type'] == '1' && $v['act_range'] != 0) || ($v['act_type'] == '2' && $v['act_range'] != 0)){    
                $have_cx = 1;
            }
        }
	}
    return $have_cx;
}