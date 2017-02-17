<?php
/* =======================================================================================================================
 * 商城页面 购物车流程【2012/5/29】【Author:yijiangwen】【同步TIME:2012/8/13】 
 * =======================================================================================================================
 */

define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(dirname(__FILE__).'/includes/pf_cart_old.php');
require(dirname(__FILE__) . '/includes/pf_cart.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
	
//购物车第一步（默认）
if(!isset($_REQUEST['step'])){$_REQUEST['step'] = "cart";}

$smarty->assign('lang',             $_LANG);
$smarty->assign('data_dir',         DATA_DIR);
$smarty->assign('ur_here', "购物车");
$smarty->assign('page_title', "购物车 - 易视网手机版");

/*--------------购物车公共数据---------------------------------------------*/
//$flow_type:购物类型
$user_id    = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;  
$flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

//----------------------------------------------------【根据购物车中商品，自动加入0元赠品】2015-08-17添加----开始-----------------------------------------------------||
$sump    = get_cart_sump();     //购物车总金额
$c_goods = yi_get_cart_goods(1);//购物车商品记录, 一个rec_id为一条记录。
$cart_have_fav = false;

//tao:0元赠判定是折后价
$discount = compute_discount();
$discount = $discount['discount'];
//tao:0元赠判定是折后价

foreach($c_goods as $k => $v)
{
    if($v['is_gift']==0)
    {
        add_fav_cart($v['goods_id'], $sump-$discount, $v['rec_id']);//自动添加赠品（is_gift==0普通商品）
    }
    elseif($v['is_gift']==888)
    {
        continue;//888:专门用来表示没有父商品的赠品
    }
    else
    {
        delete_fav_gift($v['goods_id'], $v['is_gift'], $v['rec_id']);//自动删除购物车中多余0元赠品
        $cart_have_fav = true;//购物车中有赠品
    }

    if (($v['goods_id'] == 1226 OR $v['goods_id'] == 3444 OR $v['goods_id'] == 3672 OR $v['goods_id'] == 1628 OR $v['goods_id'] == 3948  OR $v['goods_id'] == 762 OR $v['goods_id'] == 4089 OR $v['goods_id'] == 4048 OR $v['goods_id'] == 4154) && $v['goods_number'] > 2)
    {
        if ($v['zcount'] <= 0) {
            $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=2, ycount=2 WHERE rec_id=".$v['rec_id']);
        } elseif ($v['ycount'] <= 0) {
            $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=2, zcount=2 WHERE rec_id=".$v['rec_id']);
        } else {
            $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=2, zcount=1, ycount=1 WHERE rec_id=".$v['rec_id']);
        }
    }
    if ($v['goods_id'] == 4050 OR $v['goods_id'] == 4071 OR $v['goods_id'] == 4047)
    {
        $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=1, zcount=1, ycount=0 WHERE rec_id=".$v['rec_id']);
    }
    if (($v['goods_id'] == 1073 OR $v['goods_id'] == 3319 OR $v['goods_id'] == 3648 OR $v['goods_id'] == 3650 OR $v['goods_id'] == 3671 OR $v['goods_id'] == 3672 OR $v['goods_id'] == 3966) && $v['goods_number'] > 2)
    {
        $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=2 WHERE rec_id=".$v['rec_id']);
    }
    if (($v['goods_id'] == 4074 OR $v['goods_id'] == 4109 OR $v['goods_id'] == 4084 OR $v['goods_id'] == 2690 OR $v['goods_id'] == 4132 OR $v['goods_id'] == 4133 OR $v['goods_id'] == 4155 OR $v['goods_id'] == 4156) && $v['goods_number'] > 1)
    {
        $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=1 WHERE rec_id=".$v['rec_id']);
    }

    //秒杀数量设置1
    if (in_array($v['goods_id'], array(4370,4376,4383,4378,4379,4377,4380,4381,4382))) //891,921是测试，上线需删除
    {
        if ($v['zcount'] <= 0) {
            $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=1 WHERE rec_id=".$v['rec_id']);
        } else {
            $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=1, zcount=1, ycount=0 WHERE rec_id=".$v['rec_id']);
        }
    }
    //删除团购错误商品
    if($v['extension_code'] == 'tuan_buy' && $v['parent_id'] != 0){
        del_error_tuan($v);
    }
     //删除父商品id不存在的子商品
    if($v['extension_id'] == '1212' && $v['parent_id'] != 0){
        del_no_parent($v);
    }
    //20140624工行活动：包含这个赠品，不提示：“差xx元包邮字样”
    /*if ($v['goods_id'] == 3648) {
        $smarty->assign('icbc_promotion_3648',  1);
    } else {
        $smarty->assign('icbc_promotion_3648',  1);
    }*/
}

sort_out_cart_goods(); // 将购物车中区分左右眼的隐形眼镜数据整理为统一使用左眼的记录 zhang:160811

//yi:自动删除多余的加价购赠品

if($cart_have_fav)
{
    $now  = $_SERVER['REQUEST_TIME'];
    $tfav = $GLOBALS['db']->GetAll("select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' and act_type=3;");

    foreach($tfav as $tk => $tv)
    {
        $is_gift     = $tv['act_id'];
        $buy_number  = $tv['buy_number'];
        $gift_number = $tv['gift_number'];
        $fav_can_get = 0;

        //能够添加的加价购商品数
        if(1 == $tv['is_duo'])
        {
            $fav_can_num = in_fav_number($is_gift);//母体商品数
            $fav_can_get = floor($fav_can_num/$buy_number) * $gift_number;
        }
        else
        {
            $temp_gf_arr = unserialize($tv['gift']);
            // zhang：$temp_gf_arr是二元数组，原来的$temp_gf_arr['number']取不到值-------150824
	    if(isset($temp_gf_arr[0])){
            	$fav_can_get = intval(@$temp_gf_arr[0]['number']);
	    }else{
	    	$fav_can_get = intval(@$temp_gf_arr['number']);
	    }
        }
        if($fav_can_get == 0)
        {
            continue;
        }

        //【已加入的】该活动的加价购商品数
        $fav_g_num = $db->getOne("select IFNULL(sum(goods_number),0) from ecs_cart where session_id='".SESS_ID."' and is_gift='$is_gift';");

        if($fav_g_num > $fav_can_get)
        {
            //$fav_diff = floor(($fav_g_num - $fav_can_get)/$buy_number);
            // zhang：160201 判断加价购商品是否多余逻辑错误，修改后为
            $fav_diff = $fav_g_num - $fav_can_get;
            if($fav_diff<=0)
            {
                continue;
            }
            else
            {
                foreach($c_goods as $k => $v)
                {
                    if($v['is_gift'] == $is_gift)
                    {
                        $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' and rec_id=".$v['rec_id']." limit 1;");
                        $fav_diff = $fav_diff-1;
                    }
                }
            }
        }
    }
}
/*---------------------2015-08-17添加----结束--------------------------*/
//xu:2013-08-09合并完全重复的赠品记录
merge_same_gift_new();

//------------------ 检查购物车保存的历史数据 --------------------
if ($_REQUEST['step'] == 'cart' && empty($_REQUEST['act'])) {
	
	//取得非当天的数据
	$current_date = date('Y').'-'.date('m').'-'.date('d').' 00:00:00';
	if ($_SESSION['user_id'] > 0) {
		$sql_save = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = 0 AND add_time < '$current_date' ORDER BY rec_id";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql_save = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = 0 AND add_time < '$current_date' ORDER BY rec_id";
		} else {
			$sql_save = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type = 0 AND add_time < '$current_date' ORDER BY rec_id";
		}
		
	}
	$save_goods = $GLOBALS['db']->GetAll($sql_save);
	
	if (count($save_goods) > 0) {
		$rec_id_array = array();
		foreach($save_goods as $k => $v) {
			$rec_id_array[] = $v['rec_id'];
		}
		if (count($rec_id_array) > 0) {
			//1.判断是否有组合购买商品，并移除(组合购买不好判断是否有效)
			$remove_k = array(); //欲删除的元素key
			$group_by_parentid = array(); //组合购买的主商品的id
			foreach ($save_goods as $k => $v) {
				if ($v['extension_code'] == 'group_buy') {
					$group_by_parentid[] = $v['extension_id'];
				}
			}
			if ($group_by_parentid) {
				$group_by_parentid = array_unique($group_by_parentid); //去掉重复值
				//移除组合购买项
				
				foreach ($save_goods as $k => $v) {
					if ($v['extension_code'] == 'group_buy') $remove_k[] = $k;
				}
				foreach ($group_by_parentid as $gv) {
					foreach ($save_goods as $k => $v) {
						if ($v['goods_id'] == $gv) $remove_k[] = $k; //组合购买主商品
					}
				}
			}
			
			//礼包团购秒杀商品处理
            $now = time();
			foreach ($save_goods as $k => $v) 
			{
				if ($v['extension_code'] == 'package_buy'){
                    // 礼包商品直接删除
                    $remove_k[] = $k;
                }elseif($v['extension_code'] == 'tuan_buy'){
                    // 团购商品处理，判断是否过期
                    if($v['market_price'] > 0){
                        $rec_id = floor($v['market_price']);
                    }else{
                        $res = $GLOBALS['db']->getOne("SELECT market_price FROM ".$GLOBALS['ecs']->table('cart')." WHERE extension_id = '".$v['extension_id']."' AND market_price > 0 AND user_id = '".$v['user_id']."' AND session_id = '".$v['session_id']."' AND extension_code = 'tuan_buy'");
                        $rec_id = floor($res);
                    }
                    $sql_tuan  = "SELECT start_time,end_time FROM ".$GLOBALS['ecs']->table('tuan')." WHERE rec_id = ".$rec_id;
                    $tuan_info = $GLOBALS['db']->getRow($sql_tuan);
                    if($tuan_info['start_time'] > $now || $now > $tuan_info['end_time']){
                        $remove_k[] = $k;
                    }
                }elseif($v['extension_code'] == 'miaosha_buy'){
                    // 秒杀商品处理，判断是否过期
                    $sql_ms  = "SELECT start_time,end_time FROM ".$GLOBALS['ecs']->table('miaosha')." WHERE rec_id = ".$v['extension_id'];
                    $ms_info = $GLOBALS['db']->getRow($sql_ms);
                    if($ms_info['start_time'] > $now || $now > $ms_info['end_time']){
                        $remove_k[] = $k;
                    }
                }else{
                    // 其他逻辑
                }
			}
			
			//3.积分兑换商品是否有效
			foreach ($save_goods as $k => $v) {
				if ($v['extension_code'] == 'exchange') {
					$exchange_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('exchange_goods')." WHERE goods_id='".$v['goods_id']."' AND type=1 AND is_exchange=1 LIMIT 1");
					if ($exchange_info) {
						$user_pay_points = $GLOBALS['db']->getOne("SELECT pay_points FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='".$_SESSION['user_id']."' LIMIT 1");
						if ($user_pay_points < $exchange_info['exchange_integral']) $remove_k[] = $k; //用户积分不够兑换用
					} else {
						$remove_k[] = $k;
					}
				}
			}
			
			//4.积分折扣商品是否有效
			foreach ($save_goods as $k => $v) {
				if ($v['extension_code'] == 'exchange_buy') {
					$exchange_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('exchange_goods')." WHERE goods_id='".$v['goods_id']."' AND type=2 AND is_exchange=1 LIMIT 1");
					if ($exchange_info) {
						if ($exchange_info['exchange_money'] != $v['goods_price']) {
							//价格发生了变动，移除商品，并退回积分
							reback_exchange_jf($v['rec_id'], $_SESSION['user_id']);
							$remove_k[] = $k;
						}
					} else {
						reback_exchange_jf($v['rec_id'], $_SESSION['user_id']);
						$remove_k[] = $k;
					}
				}
			}
			
            //5.移除赠品以及失效商品
            foreach ($save_goods as $k => $v) {
                if ($v['is_gift'] > 0) $remove_k[] = $k;   // 赠品直接移除
                $t = floor((time() - strtotime($v['add_time']) + 28800)/86400);
                if($t >= $v['effective_time'] && $v['effective_time'] != 0) $remove_k[] = $k;  // 有时效的产品判断是否失效，失效的移除
            }
			
			//6.移除无效项
			if (count($remove_k) > 0) {
				foreach ($remove_k as $v) {
					unset($save_goods[$v]);
				}
			}
			
			//7.先清空历史数据
			$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id IN (" .implode(',', $rec_id_array). ")");
			
			//8.将有效数据再次插入购物车
			foreach ($save_goods as $k => $v) {
				if ($v['extension_code'] == '') {
					if ($v['is_kj'] == 1) {
						//框架眼镜和镜片
						addto_cart_kj($v['goods_id'], $v['goods_number'], $v['goods_price'], $v['parent_id'], array(), 0, 0, $v['goods_attr'], 0, $v['zselect'], $v['zcount'], $v['yselect'], $v['ycount'], 2, $v['ds_extention']);
					} else {
						addto_cart($v['goods_id'], $v['goods_number'], array(), 0, $v['zselect'], $v['zcount'], $v['yselect'], $v['ycount'], $v['goods_attr']);
					}
				} else {
					$cart_info = array(
				        'user_id'       => $v['user_id'],
				        'session_id'    => $v['session_id'],
				        'goods_id'      => $v['goods_id'],
				        'goods_sn'      => $v['goods_sn'],
				        'goods_name'    => $v['goods_name'],
				        'market_price'  => $v['market_price'],
						'goods_price'  	=> $v['goods_price'],
						'goods_number'  => $v['goods_number'],
				        'goods_attr'    => $v['goods_attr'],
						'is_real'       => $v['is_real'],
						'extension_code'=> $v['extension_code'],
						'extension_id'	=> $v['extension_id'],
						'parent_id'		=> $v['parent_id'],
						'rec_type'      => $v['rec_type'],
						'is_gift'       => $v['is_gift'],
						'is_cx'         => $v['is_cx'],
						'is_shipping'   => $v['is_shipping'],
						'can_handsel'   => $v['can_handsel'],
				        'goods_attr_id' => $v['goods_attr_id'],
						'zselect' 		=> $v['zselect'],
						'zcount' 		=> $v['zcount'],
						'yselect' 		=> $v['yselect'],
						'ycount' 		=> $v['ycount'],
						'is_kj' 		=> $v['is_kj'],
						'ds_extention' 	=> $v['ds_extention'],
						'effective_time'=> $v['effective_time']
						//'add_time' 		=> $v['add_time']
				    );
                    if($v['effective_time'] > 0){
                        $t = floor((time() - strtotime($v['add_time']) + 28800)/86400);
                        $cart_info['effective_time'] = $v['effective_time'] - $t;
                    }
				    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
				}
			}

			 ecs_header("Location: flow.html\n");
			 exit;
			
		}
	}

}
//------------------ 检查购物车保存的历史数据 END --------------------

$smarty->assign('step',            $_REQUEST['step']);

/*-----------------------------------购物车第一步(step=cart)--------------------------------*/
if($_REQUEST['step'] == 'cart'){

    $_SESSION['flow_type'] = CART_GENERAL_GOODS;//普通商品
    $type = CART_GENERAL_GOODS;//普通商品
    // 1.购物车列表
    if ($_SESSION['user_id'] > 0) {
        $sql =  "SELECT *, (goods_number*goods_price) AS subtotal FROM " . $GLOBALS['ecs']->table('cart') . " WHERE user_id = '" . $_SESSION['user_id'] . "' AND rec_type='$type' ORDER BY rec_id";
    } else {
        if (isset($_COOKIE['cart_session_id'])) {
            $sql =  "SELECT *, (goods_number*goods_price) AS subtotal FROM " . $GLOBALS['ecs']->table('cart') . " WHERE user_id <= 0 AND (session_id = '" . SESS_ID . "' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type='$type' ORDER BY rec_id";
        } else {
            $sql =  "SELECT *, (goods_number*goods_price) AS subtotal FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND rec_type='$type' ORDER BY rec_id";
        }
    }
    $save_goods = $GLOBALS['db']->GetAll($sql);

    // 2.购物合计信息
    $total = array(
        'goods_number' => 0,					//商品数量总计
        'goods_weight' => get_cart_weight(),	//商品总重量
        'goods_amount' => 0,					//商品金额总计
        'goods_amount_float'  => 0,                    //商品金额总计(float)
        'payment_amount' => 0,					//应付款金额(不含运费)
        'discount_amount' => 0  				//全部折扣金额
    );
    foreach ($save_goods as $k => $v)
    {
        $total['goods_amount'] += $v['goods_price'] * $v['goods_number'];
        $total['goods_amount_float'] += $v['goods_price'] * $v['goods_number'];
        $total['goods_number'] += $v['goods_number'];
        //zhang 150922:把商品的缩略图换成了100*100的图片
        $goods_thumb = $GLOBALS['db']->getOne("SELECT `goods_img` FROM " . $GLOBALS['ecs']->table('goods') . " WHERE `goods_id`=".$v['goods_id']);
        $save_goods[$k]['goods_thumb'] = get_image_path($v['goods_id'], $goods_thumb, true);
    }
    $total['goods_amount'] = sprintf('%.2f', $total['goods_amount']);
    /*var_dump($save_goods);
    var_dump($total);*/
    $total_num    = cart_goods_total_num_wap($flow_type);
    $total_weight = cart_goods_total_weight_wap($flow_type);

    //zhang  20150820 步骤一页面显示折后的价格
    $discount_mess = compute_discount();
    $discount_price = $discount_mess['discount'];
    $cart_price = $total['goods_amount']-$discount_price;
    if(!empty($cart_price)&&$cart_price>0){
        $total['goods_price'] = sprintf("%.2f",$cart_price);
    }
    //优惠活动之：计算商品折扣
    $favour_name = empty($discount_mess['name']) ? '' : join(',', $discount_mess['name']);
    $smarty->assign('discount',      $discount_mess['discount']);
    $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount_mess['discount'])));
    //var_dump($discount);
    //zhang  20150820 end
    //var_dump($save_goods);
    $smarty->assign('total_num',       $total_num);
    $smarty->assign('total_weight',    $total_weight);
    $smarty->assign('goods_list',      $save_goods);
    $smarty->assign('total',           $total);
    $smarty->assign('shopping_money',  '￥'.$total['goods_amount']);//商品金额总计
    $smarty->assign('shopping_moneyn', '￥'.@$total['goods_price']);//商品金额总计
    $smarty->assign('user_id',         $user_id);

    //yi:会员福利：不同会员，免邮额度不同
    $base_line = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 68;
    if((@$total['goods_price']-$base_line)>0){
        $smarty->assign('goods_pricex',    0);
        $smarty->assign('base_line',       $base_line);
    }else{
        $smarty->assign('goods_pricex',    $base_line-@$total['goods_price']);
    }
    //优惠信息

    //-------------------------------------------------------------------【加
    //钱赠品展示】-------------------------------------------------------------------------||
    //优惠活动之加价购。
    $now = $_SERVER['REQUEST_TIME'];
    $fav = $GLOBALS['db']->GetAll("select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' and act_type=3;");

    //filter
    foreach($fav as $k => $v)
    {
        $fav[$k]['gift'] = unserialize($v['gift']);
        //var_dump($fav[$k]['gift']);
        $user_rank		 = yget_user_rank($user_id);

        if(!in_array($user_rank, explode(',', $v['user_rank'])))
        {
            unset($fav[$k]);
        }

        //if($v['is_duo'])
        //{
        $fav_number  = in_fav_number($v['act_id'], $v['act_range']);//母体有效商品数

        /*20150107tao注释
        if($fav_number < $v['buy_number'])
        {
            unset($fav[$k]);
        }
        */
        //}

        $cart_sump = get_cart_sump()-$discount;//加价购算优惠前价格 by：tao
        $fav_sum = ($v['act_range']>0)? in_fav_sum($v['act_id'], $v['act_range']): $cart_sump; //母体有效总金额
        $min     = $v['min_amount'];
        $max     = $v['max_amount'];
        $max     = ($max==0)? 999999: $max;

        if($fav_sum>=0 && $fav_sum>=$min && $fav_sum<=$max)
        {
            continue;
        }
        else
        {
            unset($fav[$k]);
        }
    }

    $all_gift = array();//加价购商品
    foreach($fav as $k => $v)
    {
        $gg = $v['gift'];
        foreach($gg as $b => $bv)
        {
            $goods_id  = intval($bv['id']);		//赠品ID
            $gift_numb = intval($bv['number']);	//赠品数量
            $g_good    = $GLOBALS['db']->GetRow("select goods_img,shop_price from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$goods_id." limit 1;");

            $gg[$b]['act_id']    = $v['act_id'];
            $gg[$b]['act_name']  = $v['act_name'];
            $gg[$b]['goods_img'] = $g_good['goods_img'];       //图片100x100
            $gg[$b]['goods_ds']  = get_goods_ds($goods_id);//商品度数
            if(2 == $gift_numb)
            {
                $gg[$b]['price']      = $bv['price']*2;
                $gg[$b]['shop_price'] = $g_good['shop_price']*2;
            }
            else
            {
                $gg[$b]['shop_price'] = $g_good['shop_price']*1;
            }
        }
        $fav[$k]['gift'] = $gg;
        $all_gift[$k] = $gg;
    }
    $all_gift = array_merge($all_gift);

    $sqlc = "SELECT count(*) FROM ".$ecs->table('cart')." WHERE session_id='".SESS_ID."' AND rec_type='".CART_GENERAL_GOODS."' and is_gift<>0 and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' and goods_price<>'0.00'";
    $cart_len		= $GLOBALS['db']->GetOne($sqlc);//购物车加价购赠品数
    $cart_fav_goods = count($all_gift);				//加价购商品数

    $smarty->assign('cart_fav_goods',    $cart_fav_goods);
    $smarty->assign('hot_goods',         get_hot_goods_flow());
    $smarty->assign('gift_len',          $cart_fav_goods);
    $smarty->assign('gift_list',         $all_gift);

    //购物车中总共能够获得多少商品
    //$smarty->assign('cart_len',    $hv_fav);//购物车中活动范围内商品数量
    //-------------------------------------------------------------------【加钱赠品展示end】-------------------------------------------------------------------------||

}
// 团购更新购物车商品数量
elseif($_REQUEST['step'] == 'update_tuan')
{
    //更新购物车团购数量
    $key = intval($_POST['key']);
    $num = intval($_POST['number']);
    //团购ID
    $tuan_id = intval($GLOBALS['db']->getOne("SELECT market_price FROM ".$ecs->table('cart')." WHERE rec_id=".$key));

    $sql = "UPDATE " .$GLOBALS['ecs']->table('cart'). " SET goods_number = '$num' WHERE session_id='" . SESS_ID . "' AND extension_code = 'tuan_buy' AND extension_id = $tuan_id";
    $GLOBALS['db']->query($sql);
    exit;
}
// 积分兑换商品删除
elseif($_REQUEST['step'] == 'drop_exchange_goods')
{
    //删除购物车中积分兑换和积分折扣商品
    //========================================================//
    $rec_id  = intval($_GET['rec_id']);
    $user_id = $_SESSION['user_id'];
    reback_exchange_jf_wap($rec_id, $user_id);
    flow_drop_cart_goods_wap($rec_id);
    ecs_header("Location: flow.php\n");
    exit;
}
// 团购商品删除
elseif($_REQUEST['step'] == 'drop_tuan')
{
    //删除团购商品
    $rec_id = intval($_GET['id']);
    //团购ID
    $tuan_id = intval($GLOBALS['db']->getOne("SELECT extension_id FROM ".$ecs->table('cart')." WHERE rec_id=".$rec_id));

    // 解决多方登陆后无法删除购物车商品问题  zhang: 160119
    if($_SESSION['user_id'] > 0){  // 登陆状态下以用户id为删除条件
        $where_id = " user_id = '".$_SESSION['user_id']."'";
    }else{
        $where_id = " session_id = '". SESS_ID ."'";
    }

    $sql = "DELETE FROM " .$GLOBALS['ecs']->table('cart'). " WHERE " . $where_id . " AND extension_code = 'tuan_buy' AND extension_id = $tuan_id";
    
    $GLOBALS['db']->query($sql);

    ecs_header("Location: flow.php\n");
    exit;
}
// 礼包商品删除
elseif($_REQUEST['step'] == 'drop_package')
{
//--------------------------------------------删除购物车中的大礼包-------------------------------------------------------------------
    $rec_id = intval($_GET['id']);
    //找出礼包id
    $sql = "select market_price from ".$ecs->table('cart')." where rec_id=".$rec_id.";";
    $package_id = intval($GLOBALS['db']->getOne($sql));

    //找出礼包商品数量
    $sql2 = "select count(*) from ".$ecs->table('package_goods')." where package_id=".$package_id.";";
    $num = intval($GLOBALS['db']->getOne($sql2));

    //删除该礼包中的商品
    if($num>0){
        for($i=0; $i<$num; $i++){
            $rr_id = $rec_id+$i;
            //-------------------------------逐个删除该礼包的产品----------------------------------------
            flow_drop_cart_goods_wap($rr_id);
        }
    }
    ecs_header("Location: flow.php\n");
    exit;
}
/*    2015-08-06之前的代码
if($_REQUEST['step'] == 'cart'){
    
    $_SESSION['flow_type'] = CART_GENERAL_GOODS;//普通商品
    
    $cart_info = get_cart_info_for_flow();  //购物车商品列表	
    $total_num    = cart_goods_total_num_wap($flow_type);
    $total_weight = cart_goods_total_weight_wap($flow_type);
    
    
    $smarty->assign('total_num',       $total_num);
    $smarty->assign('total_weight',    $total_weight);
    $smarty->assign('goods_list',            $cart_info['cart_goods']);
    $smarty->assign('total',                 $cart_info['total']);
    $smarty->assign('shopping_money',        '￥'.$cart_info['total']['goods_amount']);//商品金额总计
	$smarty->assign('user_id',               $user_id);	
    
	//yi:会员福利：不同会员，免邮额度不同
	$base_line = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 150;
	if(($cart_info['total']['goods_amount_float']-$base_line)>0){
		$smarty->assign('goods_pricex',    0);
		$smarty->assign('base_line',       $base_line);
	}else{
		$smarty->assign('goods_pricex',    $base_line-$cart_info['total']['goods_amount_float']);
	}
    //优惠信息

}
*/

/*-----------------------------------订单确认【页面】(购物车第二步)--------------------------------*/
elseif($_REQUEST['step'] == 'checkout')
{
	include_once('includes/lib_transaction.php');
	$user_id = $_SESSION['user_id'];

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 检查购物车中是否有商品 */
    if ($_SESSION['user_id'] > 0) {
        $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
    } else {
        if (isset($_COOKIE['cart_session_id'])) {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
        } else {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE session_id = '" . SESS_ID . "' AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
        }
    }

    if($db->getOne($sql) == 0)
    {
        $sqlg = "select count(rec_id) from ecs_cart where session_id='" . SESS_ID ."' and is_gift=888;";
        if($db->getOne($sqlg) == 0)
        {
            show_message_wap($_LANG['no_goods_in_cart'], '', '', 'warning');
        }
    }

	/*------------------------------------------------------------------------------------
     * 检查用户是否已经登录
     * 1.已经登录 判断是否有默认收货地址->有则显示默认地址
     * 2.没有登录 跳转到登录和注册页面
     ------------------------------------------------------------------------------------*/
    if(empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
		if(isset($_REQUEST['direct_shopping']))
		{
			$_SESSION['direct_shopping'] = 1;//直接购物
		}
		else
		{
			//用户没有登录且没有选定匿名购物,转向到登录页面
			ecs_header("Location: user.php?step=login\n");
			exit;
		}
    }


	/*---------------------------------------------------收货人地址列表管理-------------------------------------------------------------*/
	//商店所在国家,省列表(数据未使用)
	$smarty->assign('user_id',            $user_id);
	$smarty->assign('shop_country',       $_CFG['shop_country']);
	$smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
	$add_not_null = true; //用户地址是否为空,true:地址不为空。

	//登录用户: 获得所有的收货人地址
	if($user_id > 0)
	{
		$consignee_list = get_consignee_list($user_id);//收货人地址列表(约定最多只能存放十个地址)

		//登录用户,还没有收货人地址
		if(count($consignee_list) == 0)
		{
			//增加一条空新信息
			$consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
			$add_not_null     = false;
		}

		//登录用户默认地址id
		$address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");

        if($address_id){
            $address_default  = $db->getRow("SELECT * FROM " .$ecs->table('user_address'). " WHERE address_id='$address_id'");

            $smarty->assign('default_address',$address_default);
            $smarty->assign('default_province', $db->getRow("SELECT * FROM " .$ecs->table('region'). " WHERE region_id='".$address_default['province']."'"));
            $smarty->assign('default_city', $db->getRow("SELECT * FROM " .$ecs->table('region'). " WHERE region_id='".$address_default['city']."'"));
            $smarty->assign('default_district', $db->getRow("SELECT * FROM " .$ecs->table('region'). " WHERE region_id='".$address_default['district']."'"));
        }


		$smarty->assign('default_address_id', $address_id);
	}
	else
	{
		//匿名用户
		if(isset($_SESSION['flow_consignee']) && !empty($_SESSION['flow_consignee']))
		{
			$consignee_list = array($_SESSION['flow_consignee']);         //会话中已经保存有用户地址
		}
		else
		{
			$consignee_list[] = array('country' => $_CFG['shop_country']);//会话中没有用户地址
		}
	}

	//----------------------------------------------------------------------------------------------------------------
	foreach($consignee_list AS $region_id => $consignee)
	{
		//yi:地区名称
		@$consignee_list[$region_id]['provincena'] = get_regions_name($consignee_list[$region_id]['province']);
		@$consignee_list[$region_id]['cityna']     = get_regions_name($consignee_list[$region_id]['city']);
		@$consignee_list[$region_id]['districtna'] = get_regions_name($consignee_list[$region_id]['district']);
	}
    //var_dump($consignee_list);die;
	$smarty->assign('add_not_null',   $add_not_null);
	$smarty->assign('consignee_list', $consignee_list);
	$smarty->assign('name_of_region', array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
	//----------------------------------------------------------------------------------------------------------------

	/*==========================================================================================*/
	//yi：把收货人地址写入到会话中，临时保存一份到session
	if( $user_id>0 && count($consignee_list) > 0)
	{
		$_SESSION['flow_consignee'] = stripslashes_deep($consignee_list[0]);
	}
    //var_dump($_SESSION['flow_consignee']);
	/*==========================================================================================*/

	//地址列表数组
	$province_list = array();
	$city_list     = array();
	$district_list = array();

	//默认地址列表数组
	$city_list     = !empty($consignee_list[0]['city'])?     get_regions(2, $consignee_list[0]['province']): 0;
	$district_list = !empty($consignee_list[0]['district'])? get_regions(3, $consignee_list[0]['city']): 0;

	$smarty->assign('country_list',  get_regions());
	$smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
	$smarty->assign('city_list',     $city_list);
	$smarty->assign('district_list', $district_list);

    $smarty->assign('provinces',    get_district_lsit(1,1));//城市列
    $smarty->assign('city',         get_district_lsit(2));//省份列
    $smarty->assign('district',         get_district_lsit(3));//地区列

	/*=======================如果是登陆用户,并且有默认地址.进来即显示默认地址==============*/
	$smarty->assign('user_default_addres', ($_SESSION['user_id']>0 && $address_id)? true : false);

	/*--------------------------------------------------------------收货人地址页面【end】----------------------------------------------------------------*/


    $consignee = get_consignee($_SESSION['user_id']);//会话中默认收货地址
    //var_dump($consignee);
    //检查收货人信息是否完整 如果不完整则转向到收货人信息填写界面
    if(!check_consignee_info($consignee, $flow_type))
    {
		//yi:修改 转向购物第二步.填写地址【注释掉】
        //ecs_header("Location: flow.php?step=consignee\n");
        //exit;

		//yi:如果无完整配送信息的情况.ajax中没有地址的转向了.
		$smarty->assign('consignee_is_null', 1);
    }

    $_SESSION['flow_consignee'] = $consignee;
    $smarty->assign('consignees', $consignee);//当前的收货人地址(配送区域显示默认值)

    //对【购物车】商品信息赋值
    $cart_goods = cart_goods($flow_type);    //取得商品列表，计算合计
    $smarty->assign('goods_list', $cart_goods);

    //是否允许修改购物车(商店设置)
    if($flow_type != CART_GENERAL_GOODS || $_CFG['one_step_buy'] == '1'){
        $smarty->assign('allow_edit_cart', 0);
    }else{
        $smarty->assign('allow_edit_cart', 1);
    }

    //取得购物流程设置
    $smarty->assign('config', $_CFG);


	/*------------------------------------------------------本次购物订单处理------------------------------------------------------*/
	/*=======================================================================*/
	//用户上一次的支付方式和配送方式【cookie保存】
    if(!empty($_COOKIE["payment"])){
	   $old_payment  = $_COOKIE["payment"];
    }
    if(!empty($_COOKIE["shipping"])){
	   $old_shipping = $_COOKIE["shipping"];
    }
	/*=======================================================================*/
	
    //取得本次订单信息   
    $order = flow_order_info();

	//--------------------------------------------------------------【红包1】--------------------------------------------------------------//
	//yi:检查线下红包是否在这个范围之内【2012/4/19】
	if(!empty($order['bonus_sn']))
	{
		if(!bonus_sn_validate($order['bonus_sn']))
		{
			$order['bonus_id'] = 0;
			$order['bonus_sn'] = 0;
		}
	}
	//yi:购物车中商品是否包含[订单不能使用红包商品] 只判断正常实体商品。【2013/5/24】
	$can_use_bonus  = true;
	$sou_g  = $GLOBALS['db']->getAll("select * from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' ;");	
	if(!empty($sou_g))
	{
		foreach($sou_g as $k => $v)
		{
			$source = !empty($v['extension_id'])? intval($v['extension_id']): 0;
			$sql = "select rec_id from ".$GLOBALS['ecs']->table('source')." where UNIX_TIMESTAMP() > start_time and UNIX_TIMESTAMP() < end_time and goods_id=".
					$v['goods_id']." and can_use_bonus=0 and rec_id=".$source." limit 1;";
			$source_row = $GLOBALS['db']->getOne($sql);
			if(!empty($source_row) && $source_row>0)
			{
				$can_use_bonus = false; break;
			}				
		}
	}
	//echo $can_use_bonus? 'ok': 'no';
	if(!$can_use_bonus)
	{
			$order['bonus_id'] = 0;
			$order['bonus_sn'] = 0;
	}
	//--------------------------------------------------------------【红包1】END--------------------------------------------------------------//

	//排除匿名购物的情况
	if(!empty($_SESSION['direct_shopping']))
	{
		$order['pay_id']      = $old_payment;
		$order['shipping_id'] = $old_shipping;
	}	
	//if(no_cod_goods() || goods_in_cart(1685, 'unchange')) //xu:2013-08-08取消1685不能货到付款
	//if(no_cod_goods() || goods_in_cart(2813, '') || goods_in_cart(2815, '') || goods_in_cart(2816, '') || goods_in_cart(2817, '') || goods_in_cart(2818, '') || goods_in_cart(2819, ''))
	if(no_cod_goods() || goods_in_cart(3178, '')|| by_tuan_in_cart() || no_pay_after_by_source($cart_goods))
	{
		if(3==intval($order['pay_id']))
		{
			$order['pay_id']        = 4;
			$order['shipping_id']   = 9;
		}
		$smarty->assign('no_cod_goods', true);//yi:不能使用货到付款支付（购物车中包含交行活动赠品）
	}

	if(!empty($order['shipping_id']) && !empty($order['pay_id']))
	{
		//获得支付方式 配送方式中的名称
		$order['shipping_name'] = get_shipping_name($order['shipping_id']);	
		$order['pay_name']      = get_pay_name($order['pay_id']);
	}
    $smarty->assign('order', $order);

    /*-------------------计算订单折扣金额【积分兑换和团购除外】----------------*/
    if($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS)
    {
        $discount = compute_discount();
        $smarty->assign('discount', $discount['discount']);

        $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
        $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));
    }
    
    /*专享价获取自定义邮费对应done by:tao20151123*/
    if(!empty($source)){
        $postage = $GLOBALS['db']->getOne("SELECT postage FROM ".$GLOBALS['ecs']->table('source')." WHERE rec_id = ".$source." LIMIT 1");
        if($postage>0){
            $custom_fee = $postage;
        }else{
            $custom_fee = false;
        }
    }
    
    /*------------------------------------------------------计算订单的费用------------------------------------------------------*/	
    if(by_tuan_in_cart() || by_source_in_cart($cart_goods)){//订单中包含包邮团购则包邮对应done
        $total = order_fee($order, $cart_goods, $consignee,true); 
    }else{
        $total = order_fee($order, $cart_goods, $consignee,false,$custom_fee);    
    }
    
    //$total = order_fee($order, $cart_goods, $consignee);
    //var_dump($total);
    //$smarty->assign('total',             $total);
    $smarty->assign('shopping_money',    sprintf($_LANG['shopping_money'], $total['formated_goods_price']));
    $smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));
    /*------------------------------------------------------计算订单的费用end--------------------------------------------------*/	


	/*------------------------------------------------------配送方式列表-----------------------------------------------------------*/
	//msg:根据配送地址计算配送费用(选择这里的时候必须选择好配送地址)
    @$region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
    $shipping_list     = available_shipping_list($region); //可选配送方式
    $cart_weight_price = cart_weight_price($flow_type);    //运费费用数组

    $insure_disabled   = true;
    $cod_disabled      = true;

	/* 参数：
	 * $cart_weight_price['weight']=>商品总重量（包含礼包）
	 * $total['ship_cart_wei']     =>商品总重量。
	 * $total['shipping_base_fee'] =>首重运费。（数据正确）
	 */
	/*------------------------------------------------------配送方式列表end--------------------------------------------------------*/


	/*--------------------------------------------修改大礼包运费为零的情况--去掉AND `extension_code` != 'package_buy'-------------------------------------*/

	//礼包中商品的运费按照普通商品计算,超过150同样免运费，超重同样收费。    
    $sql = 'SELECT count(*) FROM ' . $ecs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `is_shipping` = 0";
    $shipping_count = $db->getOne($sql);

	//查看购物车中是否全为免运费商品，若是则把运费赋为零
    foreach($shipping_list AS $key => $val)
    {
        $shipping_cfg = unserialize_config($val['configure']);
        $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
        $cart_weight_price['weight'], $cart_weight_price['amount']-$discount['discount'], $cart_weight_price['number']);

        if($_SESSION['base_line']==1 && @$goods_price_total<1 && $total['area_id'] != 22 || miaosha_free_ship()){
            //vip会员金额小于1的邮费判断
            $shipping_fee = 0;
        }

        $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
        $shipping_list[$key]['shipping_fee']        = $shipping_fee;
        $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);
        $shipping_list[$key]['insure_formated']     = strpos($val['insure'], '%') === false ?
        price_format($val['insure'], false) : $val['insure'];

        //当前的配送方式是否支持保价
        if ($val['shipping_id'] == $order['shipping_id'])
        {
            $insure_disabled = ($val['insure'] == 0);
            $cod_disabled    = ($val['support_cod'] == 0);
        }

        /**
         * 秒杀商品只可以快递购买,不支持其他配送方式
         */
        if(miaosha_free_ship()){
            if($val['shipping_id'] != 9){
                unset($shipping_list[$key]);
            }
            $smarty->assign('no_cod_goods', true);//yi:不能使用货到付款支付（购物车中包含交行活动赠品）
        }
    }
    $smarty->assign('shipping_list',      $shipping_list);
    $smarty->assign('insure_disabled',    $insure_disabled);
    $smarty->assign('cod_disabled',       $cod_disabled);
	$smarty->assign('shipping_flow_type', $flow_type);
	//----------------------------------------修改礼包运费问题end------------------------------------------------------------------------

	//yi:指定支付方式包邮标志
	$ship_gid_arr = array(1260,1261,1265,1264,1263,1262,1269,1268,1267,1266,1164,1097,1010,978,977,979,1437,1441,1436,1321,1323,1319,1386,1390,1399,1359,1355,1357,1145,1144);
	if(include_ship_fee_goods($ship_gid_arr))
	{
		$smarty->assign('ship_fee_goods',  1);
	}

	/*----------------------------------------------------------支付方式列表------------------------------------------------------------------*/
    if($order['shipping_id'] == 0)
    {
		//没选配送方式
        $cod      = true;//配送方式是否货到付款
        $cod_fee  = 0;   //货到付款手续费0
    }
    else
    {
		/*=================================================原先程序:由配送方式控制支付方式【已改】=================================================*/

		//货到付款的这种情况
        $shipping = shipping_info($order['shipping_id']);
        $cod      = $shipping['support_cod']; //是否货到付款

        if($cod)
        {
			//货到付款情况:

            /* 如果是团购，且保证金大于0，不能使用货到付款 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $group_buy_id = $_SESSION['extension_id'];
                if ($group_buy_id <= 0)
                {
                    show_message_wap('error group_buy_id');
                }
                $group_buy = group_buy_info($group_buy_id);
                if (empty($group_buy))
                {
                    show_message_wap('group buy not exists: ' . $group_buy_id);
                }
                if ($group_buy['deposit'] > 0)
                {
                    $cod = false;
                    $cod_fee = 0;

                    /* 赋值保证金 */
                    $smarty->assign('gb_deposit', $group_buy['deposit']);
                }
            }
			/*普通购物情况:配送区域,配送手续费用*/
            if($cod)
            {
                $shipping_area_info = shipping_area_info($order['shipping_id'], $region);
                $cod_fee            = $shipping_area_info['pay_fee'];
            }
        }
        else
        {
            $cod_fee = 0;//配送手续费用为0
        }
    } 
    $payment_list = available_payment_list(1, $cod_fee);
    if(isset($payment_list))
    {
        foreach($payment_list as $key => $payment)
        {
			/*-------------------------给货到付款的手续费加<span id>，以便改变配送的时候动态显示-------------------------*/
            if ($payment['is_cod'] == '1')
            {
                $payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
            }
            /* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */
            if ($payment['pay_code'] == 'yeepayszx' && $total['amount'] > 300)
            {
                unset($payment_list[$key]);
            }

            /*-------------------------如果有余额支付-------------------------*/
            if($payment['pay_code'] == 'balance')
            {
                /*未登录，不显示余额支付的支付方式*/
                if($_SESSION['user_id'] == 0)
                {
                    unset($payment_list[$key]);
                }
                else
                {
                    if($_SESSION['flow_order']['pay_id'] == $payment['pay_id'])
                    {
                        $smarty->assign('disable_surplus', 1);//选择的支付方式 是余额支付-->余额支付不可以更改
                    }
                }
            }
        }
    }
    $smarty->assign('payment_list', $payment_list);
	/*----------------------------------------------------------支付方式结束------------------------------------------------------------------------*/

	/*----------------------------------------------------------------------------------------------------------------------------------------------*/
	//红包与贺卡（只有有实体商品,才要判断包装和贺卡）
    if($total['real_goods_count'] > 0)
    {
        if(!isset($_CFG['use_package']) || $_CFG['use_package'] == '1')
        {
			//如果使用包装，取得包装列表及用户选择的包装
            $smarty->assign('pack_list', pack_list());
        }

        if(!isset($_CFG['use_card']) || $_CFG['use_card'] == '1')
        {
			//如果使用贺卡，取得贺卡列表及用户选择的贺卡
            $smarty->assign('card_list', card_list());
        }
    }
	/*----------------------------------------------------------------------------------------------------------------------------------------------*/

    $user_info = user_info($_SESSION['user_id']); //用户会员信息

	/*----------------------------------------------------------------------------------------------------------------------------------------------*/
    //用余额支付
    if((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1') && $_SESSION['user_id'] > 0 && $user_info['user_money'] > 0)
    {
        //用户能使用余额
        $smarty->assign('allow_use_surplus', 1);
        $smarty->assign('your_surplus',      $user_info['user_money']);
    }
	/*----------------------------------------------------------------------------------------------------------------------------------------------*/

	/*----------------------------------------------------------------------------------------------------------------------------------------------*/
    //用户使用积分
    if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')
        && $_SESSION['user_id'] > 0 && $user_info['pay_points'] > 0 && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        //取得用户可用积分及本订单最多可以使用的积分
        $smarty->assign('allow_use_integral', 1);
        $smarty->assign('order_max_integral', flow_available_points());  //订单可用积分
        $smarty->assign('your_integral',      $user_info['pay_points']); //用户积分
    }
	/*----------------------------------------------------------------------------------------------------------------------------------------------*/


	//--------------------------------------------------------------【红包2】--------------------------------------------------------------//
    //如果使用红包，取得用户可以使用的红包及用户选择的红包
	
    if((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1') && $can_use_bonus && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        $user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price']);//取得基本红包

        if(!empty($user_bonus))
        {
			//yi: 遍历红包，剔除不合理的红包
            foreach ($user_bonus AS $key => $val)
            {
				if(!user_bonus_validate($user_bonus[$key]['type_id']))
				{
					unset($user_bonus[$key]);
				}
				else
				{
					$user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
				}
            }
            $smarty->assign('bonus_list', $user_bonus);
        }
    }
	else
	{
		$smarty->assign('no_use_bonus', true);//订单不能使用红包。

	}
	//yi:秒杀等商品能否作用于红包
    foreach($cart_goods as $k1 => $v1)
    {
	    //if($is_cx  || $v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
		if($v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
		{
		    /*16年中秋活动临时注释 9.19号之后取消注释  by：tao */
			$smarty->assign('bonus_list', false);//订单不能使用红包。
            $_SESSION['flow_order']['bonus_id'] = 0; //并清除bonusid的session,$order,$total数据
            $order['bonus']                     = 0;
            $order['bonus_id']                  = 0;
            $total['bonus']                     = 0;
            $total['bonus_formated']            = 0;
            //$total['amount_formated']           = $total['formated_goods_price'];	
		}
	}
	//--------------------------------------------------------------【红包2】END--------------------------------------------------------------//

    /* 如果使用缺货处理，取得缺货处理列表 */
    if (!isset($_CFG['use_how_oos']) || $_CFG['use_how_oos'] == '1')
    {
        if (is_array($GLOBALS['_LANG']['oos']) && !empty($GLOBALS['_LANG']['oos']))
        {
            $smarty->assign('how_oos_list', $GLOBALS['_LANG']['oos']);
        }
    }

    /* 如果能开发票，取得发票内容列表 */
    if( ( !isset($_CFG['can_invoice']) || $_CFG['can_invoice'] == '1') && isset($_CFG['invoice_content']) && trim($_CFG['invoice_content']) != '' 
		&& $flow_type != CART_EXCHANGE_GOODS )
    {
		//是否开发票
        $inv_content_list = explode("\n", str_replace("\r", '', $_CFG['invoice_content']) );
        $smarty->assign('inv_content_list', $inv_content_list);

        $inv_type_list = array();
        foreach ($_CFG['invoice_type']['type'] as $key => $type)
        {
            if (!empty($type))
            {
                $inv_type_list[$type] = $type . ' [' . floatval($_CFG['invoice_type']['rate'][$key]) . '%]';
            }
        }
        $smarty->assign('inv_type_list', $inv_type_list);
    }
    $smarty->assign('total',             $total);
	//订单信息数组 保存在SESSION中
    $_SESSION['flow_order'] = $order;
}
//验证红包序列号(Ajax中调用的功能)
elseif($_REQUEST['step'] == 'validate_bonus')
{
    //模拟测试地址: http://localhost/flow.php?step=validate_bonus&bonus_sn=1048567650
    $bonus_sn = trim($_REQUEST['bonus_sn']);

    

    if(!isset($bonus)){$bonus = array();}

    $bonus = is_numeric($bonus_sn)? bonus_info(0, $bonus_sn): array();//获得红包的详细信息

    $bonus_kill = price_format($bonus['type_money'], false);
    
    //json数据格式返回给ajax调用
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');

    $flow_type  = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;//购物类型
    $consignee  = get_consignee($_SESSION['user_id']);//收货人信息
    $tmp_carts  = cart_goods($flow_type);             //购物车商品列表
    $cart_goods = $tmp_carts;
    
    /*团购商品不能用优惠券 BY:tao 20160804*/
    foreach($cart_goods as $v){
        if($v['extension_code'] == 'tuan_buy'){
            $result['error'] = "购物车中包含团购商品，无法使用优惠券！";
            $json = new JSON();
            die($json->encode($result));
        }
    }

    if($bonus['send_type'] == '5'){
        $used_num = $GLOBALS['db']->GetOne("SELECT  COUNT(*) FROM ".$ecs->table("user_bonus")." WHERE bonus_type_id = ".$bonus['bonus_type_id']." AND user_id != 0");
        if($used_num>=$bonus['over_number']){
            $result['error']   = "该红包已被用完，无法再次使用此红包！";
        }
    }

    //yi:红包不能使用2次
    if (($_SERVER['REQUEST_TIME'] < strtotime('2015-12-31 00:00:00'))){
        $wangyi_type_id = array(2458,2459);
        if(in_array($bonus['type_id'],$wangyi_type_id)){
            $goods_cat_id = $GLOBALS['db']->GetOne('SELECT bonus_id FROM '.$ecs->table("user_bonus").'
			WHERE bonus_type_id = '.$bonus['type_id'].' AND user_id = '.$_SESSION['user_id']);
            if($goods_cat_id){
                $result['error']   = "您之前已使用过同类红包，无法再次使用此红包！";
            }
        }
    }

    // 判断红包是否仅限专享商品
    $can = "";
    if($bonus['exclusive_only'] == 1){
        foreach($tmp_carts as $k=>$v){
            if($v['extension_code'] == 'source_buy'){
                $can = 1;
            }
        }
        if($can ==""){
            $result['error'] = "该红包仅限专享商品！";
            $json = new JSON();
            die($json->encode($result));
        }
    }

    //2016.8.12~10.31号新客专享红包
	if (($_SERVER['REQUEST_TIME'] < strtotime('2016-11-01 00:00:00')) && $bonus['type_id'] == 3065){
	    
		if($_SESSION['user_id'] != 0){
			$is_old  = $GLOBALS['db']->GetOne('SELECT count(order_id) FROM ecs_order_info WHERE pay_status = 2 AND user_id = '.$_SESSION['user_id']);
			$is_used = $GLOBALS['db']->GetOne('SELECT count(bonus_id) FROM ecs_user_bonus WHERE bonus_type_id = '.$bonus['type_id'].' AND user_id = '.$_SESSION['user_id']);
			if($is_old > 0){
				$result['error']   = "此优惠券为新客专享哦^_^";
			}elseif($is_used > 0){
				$result['error']   = "您已经使用过此优惠券咯^_^";
			}else{
				$total = order_fee($order, $cart_goods, $consignee,true);
			}
		}else{
			$result['error']   = "请登录后再使用此优惠券^_^";
		}
	}
    
    //促销商品可使用红包
    if($bonus['cx_can_use'] == 1)
	{
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
    						$goods_cat_id = $GLOBALS['db']->GetOne('SELECT cat_id FROM '.$ecs->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
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
    						$goods_band_id = $GLOBALS['db']->GetOne('SELECT brand_id FROM '.$ecs->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
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
            $bonus['min_goods_amount'] = $bonus['min_goods_amount'] <1 ? 1:$bonus['min_goods_amount'];
            
    		//红包无效，提示语句
    		if($bonus['min_goods_amount'] > $scope_m)
    		{
    			$order['bonus_id'] = '';
    			if($scope_m ==0)
    			{
    				$result['error'] = '很抱歉，您购买的产品不能使用该优惠券！'; 
    			}
    			else
    			{
    				$result['error'] = '该优惠券仅限于指定范围内商品消费满'.$bonus['min_goods_amount'].'元。';
    			}
    		}
    	}
    	else
    	{
    		//yi：红包使用没有限定购买商品范围
    		$sql = "SELECT SUM(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_type = '$type' and is_gift=0 ";
    		if(!$bonus['cx_can_use'])
    		{
    			$sql .= " and is_cx=0 ";
    		}
    		$cart_amounts = $GLOBALS['db']->GetOne($sql);
    
    		
    		//tao: 优惠券需要用折后价判定start
    		/*
    		$get_discount =	order_fee($order, $cart_goods, $consignee);
    		$discount	  =	$get_discount['discount'];
    		$cart_amounts = $sump-$discount;
    		*/
    		//tao: 优惠券需要用折后价判定end
    		
    		if($bonus['min_goods_amount'] > $cart_amounts)
    		{
    			$order['bonus_id'] = '';
    			$result['error'] = '您的有效购物金额未达该券使用金额:'.$bonus['min_goods_amount'].',不能用该红包哦!';
    		}
    	}
    }
    else
    {
	   /*
        新增：
        有限定范围{
            计算出非促销商品的总价（指定范围）
        }无限定范围{
            计算出非促销商品的总价
        }
       */
       ##############################################################
       if($bonus['is_scope'] == 1 && !empty($bonus['scope_ext'])){
            $no_cx_amount = get_no_cx_amount($tmp_carts,$bonus['is_scope'],$bonus['scope'],$bonus['scope_ext']);
        
       }else{
            $no_cx_amount = get_no_cx_amount($tmp_carts);
            
       }
       
       if($bonus['min_goods_amount'] > $no_cx_amount)
	   {
			$order['bonus_id'] = '';
			$result['error'] = '您的有效购物金额(非促销商品)未达该券使用金额:'.$bonus['min_goods_amount'].',不能用该红包哦!';
            $json = new JSON();
    		die($json->encode($result));
	   }
       ##############################################################
	}
    
    //yi:如果红包有 限制支付方式
    if($bonus['limit_pay'] && !empty($bonus['pay_id']))
    {
        $user_pay_id = isset($_GET['pay_id'])? intval($_GET['pay_id']): 0;
        if(0 == $user_pay_id)
        {
            //$order['bonus_id'] = 0;
            $result['error']   = "请先选择并保存好支付方式，再使用红包！";
        }
        else
        {
            if($user_pay_id != $bonus['pay_id'])
            {
                //$order['bonus_id'] = 0;
                $limit_pay_name    = $GLOBALS['db']->getOne("select pay_name from ecs_payment where pay_id=".$bonus['pay_id']." limit 1;");
                $result['error']   = "很抱歉，该红包仅限用【".$limit_pay_name."】的订单才能使用！";
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
                        $goods_cat_id = $GLOBALS['db']->GetOne('SELECT cat_id FROM '.$ecs->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
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
                        $goods_band_id = $GLOBALS['db']->GetOne('SELECT brand_id FROM '.$ecs->table("goods").' WHERE goods_id='.$tmp_carts[$k]['goods_id']);
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
        $bonus['min_goods_amount'] = $bonus['min_goods_amount'] <1 ? 1:$bonus['min_goods_amount'];

        //红包无效，提示语句
        if($bonus['min_goods_amount'] > $scope_m)
        {
            $order['bonus_id'] = '';
            if($scope_m ==0)
            {
                $result['error'] = '很抱歉，您购买的产品不能使用该优惠券！';
            }
            else
            {
                $result['error'] = '该优惠券仅限于指定范围内商品消费满'.$bonus['min_goods_amount'].'元。';
            }
        }
    }
    else
    {
        //yi：红包使用没有限定购买商品范围
        $sql = "SELECT SUM(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_type = '$type' and is_gift=0 ";
        if(!$bonus['cx_can_use'])
        {
            $sql .= " and is_cx=0 ";
        }
        $cart_amounts = $GLOBALS['db']->GetOne($sql);

        //tao: 优惠券需要用折后价判定start
        /*
        $get_discount =	order_fee($order, $cart_goods, $consignee);
        $discount	  =	$get_discount['discount'];
        $cart_amounts = $sump-$discount;
        */
        //tao: 优惠券需要用折后价判定end

        if($bonus['min_goods_amount'] > $cart_amounts)
        {
            $order['bonus_id'] = '';
            $result['error'] = '您的有效购物金额未达该券使用金额:'.$bonus['min_goods_amount'].',不能用该红包哦!';
        }
    }

    if(empty($tmp_carts) || !check_consignee_info($consignee, $flow_type))
    {
        if(empty($tmp_carts))
        {
            $result['error'] = '对不起，您购买的商品暂未达使用该红包的条件！';
        }
        else
        {
            //收货地址不全
            $result['error'] = '对不起，请先填写您的收货信息！';
        }
    }
    else
    {
        
        $smarty->assign('config', $_CFG);//购物流程设置
        $order = flow_order_info();      //取得订单信息

        //if(((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
        if( ($bonus['send_type'] == 5 || ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id'])))) && $bonus['order_id'] <= 0)
        {


            //$order['bonus_kill'] = $bonus['type_money'];
            if($_SERVER['REQUEST_TIME'] > $bonus['use_end_date'])
            {
                $order['bonus_id'] = '';
                $result['error']=$_LANG['bonus_use_expire'];//红包已经过期
            }
            else
            {
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;//红包有效
            }
        }
        //临时添加0元红包(使用红包送赠品，不是抵扣金额)
        elseif (in_array($bonus['type_id'], array(1107, 1236, 1355, 1620, 1634, 1635,1704,1762,1769,1823,1824,1869,1879,1950,1991,2063,2165,2176,2178,2299,2318,2320,2321,2339,2342,2458,2459)) && $bonus['order_id'] <= 0)
        {
            if($_SERVER['REQUEST_TIME'] > $bonus['use_end_date'])
            {
                $order['bonus_id'] = '';
                $result['error']=$_LANG['bonus_use_expire'];//红包已经过期
            }
            else
            {
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;//红包有效
            }

        }
        else
        {
            //$order['bonus_kill'] = 0;
            $order['bonus_id'] = '';
            $result['error'] = "您输入的优惠券不存在!";
        }

        //重新计算购物车中订单的费用，局部更新购物车。
        //$total = order_fee($order, $cart_goods, $consignee);
        /*专享价获取自定义邮费*/
        $source  = $GLOBALS['db']->getOne("select extension_id from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' ;");
        if(!empty($source)){
            $postage = $GLOBALS['db']->getOne("SELECT postage FROM ".$GLOBALS['ecs']->table('source')." WHERE rec_id = ".$source." LIMIT 1");
            if($postage>0){
                $custom_fee = $postage;
            }else{
                $custom_fee = false;
            }
        }
        //订单中的总金额计算
        if(by_tuan_in_cart() || by_source_in_cart($cart_goods)){//订单中包含包邮团购则包邮对应checkout
            $total = order_fee($order, $cart_goods, $consignee,true);
        }else{
            $total = order_fee($order, $cart_goods, $consignee,false,$custom_fee);
        }

       
        //宝粉活动使用指定红包包邮
        $baofen_arr = array(1776,1777);
        if(in_array($bonus['type_id'], $baofen_arr) && $order['shipping_id']!=8){
            $total = order_fee($order, $cart_goods, $consignee,true);
        }

        //4.27~6.1号使用红包指定商品包邮（同账号限用一次）(step=done 也需加此判断)
        if (($_SERVER['REQUEST_TIME'] < strtotime('2015-6-2 00:00:00'))){
            if($bonus['type_id'] == 2055){
                if($_SESSION['user_id'] != 0){
                    $goods_cat_id = $GLOBALS['db']->GetOne('SELECT bonus_id FROM '.$ecs->table("user_bonus").'
    			WHERE bonus_type_id = '.$bonus['type_id'].' AND user_id = '.$_SESSION['user_id']);
                    $total = order_fee($order, $cart_goods, $consignee,true);

                    if(!empty($goods_cat_id)){
                        $result['error']   = "您已使用过此优惠券^_^";
                    }
                }else{
                    $result['error']   = "请登录后再使用此优惠券^_^";
                }

            }
        }


        //5.22~6.30号使用红包指定商品免费/包邮（同账号限用一次）(99连锁)(step=done 也需加此判断)
        if (($_SERVER['REQUEST_TIME'] < strtotime('2015-6-30 23:59:59'))){

            if($bonus['type_id'] == 2095 || $bonus['type_id'] == 2096){
                if($_SESSION['user_id'] != 0){
                    $goods_cat_id = $GLOBALS['db']->GetOne('SELECT bonus_id FROM '.$ecs->table("user_bonus").'
   			          WHERE bonus_type_id = '.$bonus['type_id'].' AND user_id = '.$_SESSION['user_id']);
                    if(!empty($goods_cat_id)){
                        $result['error']   = "您已使用过此优惠券^_^";
                    }else{
                        $total = order_fee($order, $cart_goods, $consignee,true);
                    }
                }else{
                    $result['error']   = "请登录后再使用此优惠券^_^";
                }
            }
        }

        //5.22~7.30号使用红包指定商品免费/包邮（同账号限用一次）(兑吧)(step=done 也需加此判断)
        if (($_SERVER['REQUEST_TIME'] < strtotime('2015-7-30 23:59:59'))){

            if($bonus['type_id'] == 2090 || $bonus['type_id'] == 2091){
                if($_SESSION['user_id'] != 0){
                    $goods_cat_id = $GLOBALS['db']->GetOne('SELECT bonus_id FROM '.$ecs->table("user_bonus").'
   			          WHERE bonus_type_id = '.$bonus['type_id'].' AND user_id = '.$_SESSION['user_id']);
                    if(!empty($goods_cat_id)){
                        $result['error']   = "您已使用过此优惠券^_^";
                    }else{
                        $total = order_fee($order, $cart_goods, $consignee,true);
                    }
                }else{
                    $result['error']   = "请登录后再使用此优惠券^_^";
                }
            }

            if($bonus['type_id'] == 2092){
                if($_SESSION['user_id'] != 0){
                    $goods_cat_id = $GLOBALS['db']->GetOne('SELECT count(bonus_id) FROM '.$ecs->table("user_bonus").'
   			          WHERE bonus_type_id = '.$bonus['type_id'].' AND user_id = '.$_SESSION['user_id']);

                    if($goods_cat_id>=2){
                        $result['error']   = "您已使用过此优惠券^_^";
                    }else{
                        $total = order_fee($order, $cart_goods, $consignee,true);
                    }
                }else{
                    $result['error']   = "请登录后再使用此优惠券^_^";
                }
            }
        }



        $smarty->assign('total', $total);


        if (in_array($bonus['type_id'], array(1107, 1236, 1355, 1620, 1634, 1635, 1704, 1762, 1769, 1823, 1824, 1869,1879,1950,1991,2063,2165,2176,2178,2299,2318,2320,2321,2339,2342,2458,2459)))
        {
            $smarty->assign('special_bouns', 1); //标识实物红包
        }

        if($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }
    $json = new JSON();
    //echo($result['content']);die;
    die($json->encode($result));
}
//===========================提交订单前检测库存(未上线):begin===================================//         zhang:151221
elseif($_REQUEST['step'] == 'check_kc'){
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    //保存购物车信息
    if ($_SESSION['user_id'] > 0) {
        $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND parent_id = 0 AND rec_type = '$flow_type'";
    } else {
        if (isset($_COOKIE['cart_session_id'])) {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND parent_id = 0 AND rec_type = '$flow_type'";
        } else {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE session_id = '" . SESS_ID . "' AND parent_id = 0 AND rec_type = '$flow_type'";
        }
    }
    if($db->getOne($sql) == 0){  // 购物车中没有商品
        echo "2";die;
    }
    // 检查用户是否登陆
    if(empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0){    // 用户没登陆
        echo "3";die;
    }else{     //  有缺货产品
        $res = $db->getAll("SELECT * FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND parent_id = 0  AND rec_type = '$flow_type'");
        $kc = "";
        foreach($res as $k=>$v){
            //var_dump($v);
            if(!empty($v['zselect'])){
                $zgn = $db->getOne("SELECT stock FROM ".$ecs->table('ds')." WHERE gid = '".$v['goods_id']."' AND val like '%".trim($v['zselect'])."%'");
                if($zgn - $v['zcount'] <= -51){
                    $kc .= $v['goods_name']."\n";
                }
            }elseif(!empty($v['yselect'])){
                $ygn = $db->getOne("SELECT stock FROM ".$ecs->table('ds')." WHERE gid = '".$v['goods_id']."' AND val like '%".trim($v['yselect'])."%'");
                if($ygn - $v['ycount'] <= -51){
                    $kc .= $v['goods_name']."\n";
                }
            }else{
                $ogn = $db->getOne("SELECT goods_number FROM ".$ecs->table('goods')." WHERE goods_id = '".$v['goods_id']."'");
                if($ogn < $v['goods_number']){
                    $kc .= $v['goods_name']."\n";
                }
            }
            
            /*赠品限定数量逻辑 临时 TAO*/
            if(time()<strtotime('2016-07-15 00:00:00')){
                $giftsFav = array(2296); 
                if(in_array($v['is_gift'],$giftsFav)){
                    
                    $num = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE act_id = 160711 AND order_sn = ".$v['is_gift']);
                    
                        if($num > 0){
                            $GLOBALS['db']->query("UPDATE temp_active SET remarks = remarks-1 WHERE act_id = 160711 AND order_sn =".$v['is_gift']);
                        }else{
                            //查询该活动是否已经设置了过期，没有则设置,并删除购物车所有此活动的商品
                            
                            $have_overdue = $GLOBALS['db']->getOne("SELECT end_time FROM ecs_favourable_activity WHERE act_id = ".$v['is_gift']);
                            if(time()<$have_overdue){
                                $end_time = time()-86400;
                                $GLOBALS['db']->query("UPDATE ecs_favourable_activity SET end_time = '".$end_time."',act_name =concat('【已赠完】',act_name) WHERE act_id = ".$v['is_gift']);  
                                $GLOBALS['db']->query("DELETE FROM ecs_cart WHERE is_gift = ".$v['is_gift']);
                            }
                            $kc .= $v['goods_name']."缺货<br />";
                            
                        }
                }
            }
        }
        if(!empty($kc)){
            echo "由于\n".$kc."库存不足，需要3-5个工作日后发货！";die;
        }else{
            echo 1;die;
        }
    }
}
//==============================提交订单前检测库存:end====================================//
//==========前端完成所有订单操作。购物车订单提交数据库【功能】====================//
elseif($_REQUEST['step'] == 'done'){
    include_once('includes/lib_clips.php');
    include_once('includes/lib_payment.php');

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    //购物车中商品数组
    $cart_goods = cart_goods($flow_type);
    if(empty($cart_goods))
    {
        show_message_wap($_LANG['no_goods_in_cart'], 'flow.php', 'flow.php', 'warning');
    }
    /*var_dump($cart_goods);exit;*/

    /*var_dump($_POST);exit;*/
    //xyz:20130110 保存购物车信息
    if ($_SESSION['user_id'] > 0) {
        $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND parent_id = 0 AND (is_gift = 0 or is_gift=888) AND rec_type = '$flow_type'";
    } else {
        if (isset($_COOKIE['cart_session_id'])) {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
        } else {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE session_id = '" . SESS_ID . "' AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
        }
    }
    if($db->getOne($sql) == 0)
    {
        show_message_wap($_LANG['no_goods_in_cart'], 'flow.php', 'flow.php', 'warning');//检查购物车中是否有商品
        exit;
    }

    //----------------------------------------------检查用户是否已经登录---------------------------------------//
    if(empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
        //用户没有登录且没有选定匿名购物，转向到登录页面
        ecs_header("Location: user.php\n");
        exit;
    }

    //check consignee
    $consignee = get_consignee($_SESSION['user_id']);
    if(empty($consignee['consignee'])||empty($consignee['province'])||empty($consignee['city'])||empty($consignee['district']))
    {
        ecs_header("Location: flow.php?step=checkout&error=addres_err \n"); exit;
    }

    if(12 == @$_POST['shipping'] && $consignee['city'] != 321)
    {
        $_POST['shipping'] = 9;//非上海地区不能上门自提
    }

    //yi:5041 id限定招行支付(对应：checkout)
    if( time() >= strtotime('2015-05-11 00:00:00') && time() <= strtotime('2015-11-11 23:59:59')){

        $cart_goods_4755 = $GLOBALS['db']->getAll("SELECT goods_id FROM ecs_cart WHERE user_id = ".$_SESSION['user_id']);
        foreach($cart_goods_4755 as $v){
            //var_dump($v);die;
            if($v['goods_id'] == '5041'){
                $hav_goods_4755 = 1;
                if($_POST['payment'] =='15'){
                    $can_next_4755 = 1;
                }
            }
        }

        if(@$hav_goods_4755 && !$can_next_4755){
            show_message_wap('您的订单中包含指定支付方式的产品，请返回重新选择支付方式^_^', 'flow.php?step=checkout', 'flow.php?step=checkout', 'warning');//检查购物车中是否有商品
        }

    }


    //处理【购物车】表单提交数据   【默认支付方式配送方式为网银和普通快递】
    $_POST['how_oos']      = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
    $_POST['card_message'] = isset($_POST['card_message']) ? htmlspecialchars($_POST['card_message']) : '';
    $_POST['inv_type']     = !empty($_POST['inv_type']) ? htmlspecialchars($_POST['inv_type']) : '';
    $_POST['inv_payee']    = isset($_POST['inv_payee']) ? htmlspecialchars($_POST['inv_payee']) : '';
    $_POST['inv_content']  = isset($_POST['inv_content']) ? htmlspecialchars($_POST['inv_content']) : '';
    $_POST['postscript']   = isset($_POST['postscript']) ? htmlspecialchars($_POST['postscript']) : '';

    $order = array(
        'shipping_id'     => isset($_POST['shipping'])? intval($_POST['shipping']): 9,//配送方式（9：快递）
        'pay_id'          => isset($_POST['payment']) ? intval($_POST['payment']) : 4,//支付方式（4：支付宝）
        'pack_id'         => isset($_POST['pack']) ? intval($_POST['pack']) : 0,
        'card_id'         => isset($_POST['card']) ? intval($_POST['card']) : 0,
        'card_message'    => trim($_POST['card_message']),
        'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,//余额
        'integral'        => isset($_POST['integral']) ? intval($_POST['integral']) : 0,//积分
        'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,//红包id
        'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,
        'inv_type'        => $_POST['inv_type'],
        'inv_payee'       => trim($_POST['inv_payee']),
        'inv_content'     => $_POST['inv_content'],
        'postscript'      => trim($_POST['postscript']),
        'how_oos'         => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',
        'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
        'user_id'         => $_SESSION['user_id'],
        'add_time'        => gmtime(),
        'order_status'    => OS_UNCONFIRMED,
        'shipping_status' => SS_UNSHIPPED,
        'pay_status'      => PS_UNPAYED,
        'agency_id'       => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']))
    );

    //订单的扩展信息
    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }
    else
    {
        $order['extension_code'] = '';
        $order['extension_id'] = 0;
    }

    //yi:卡支付不能给用户开发票
    if($order['pay_id']>800 && $order['pay_id']<821 && !empty($order['inv_payee']))
    {
        $order['inv_payee'] = '';
    }

    //检查用户积分余额是否合法
    $user_id = $_SESSION['user_id'];
    if( isset($user_id) && !empty($user_id))
    {
        $smarty->assign('user_id', $user_id);
    }


    if($user_id > 0)
    {
        $user_info = user_info($user_id);

        $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
        if($order['surplus'] < 0)
        {
            $order['surplus'] = 0;
        }

        //查询用户有多少积分
        $flow_points = flow_available_points();  // 该订单允许使用的积分
        $user_points = $user_info['pay_points']; // 用户的积分总数
        $order['integral'] = min($order['integral'], $user_points, $flow_points);
        if($order['integral'] < 0)
        {
            $order['integral'] = 0;
        }
    }
    else
    {
        $order['surplus']  = 0;
        $order['integral'] = 0;
    }

    //检查红包是否存在
    $bonus = array();
    if($order['bonus_id'] > 0)
    {
        $bonus = bonus_info($order['bonus_id']);

        if(empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))
        {
            $order['bonus_id'] = 0;
        }

        //yi:如果用户不是使用的交行的支付方式, 排查这些支付方式
        $limit_pay_id = $GLOBALS['db']->getOne("select IFNULl(b.pay_id, 0) as limit_pay_id from ecs_user_bonus as ub left join ecs_bonus_type as b on ub.bonus_type_id=b.type_id where ub.bonus_id=".$order['bonus_id']." and b.limit_pay=1 limit 1;");
        if(!empty($limit_pay_id) && !empty($order['pay_id']))
        {
            if($order['pay_id']!=$limit_pay_id)
            {
                $order['bonus_id'] = 0;
            }
        }
    }
    elseif(!empty($_POST['bonus_sn']))    // 原来代码是isset($_POST['bonus_sn'])  值为空时会报错   2015-08-17  zhang
    {
        $bonus_sn = trim($_POST['bonus_sn']);
        $bonus = bonus_info(0, $bonus_sn);
        $now = gmtime();

        if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type) || $now > $bonus['use_end_date'])
        {

        }
        else
        {
            //检查线下红包是否在这个范围之内
            if(bonus_sn_validate($bonus_sn))
            {
                if($user_id > 0)
                {
                    $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                    $db->query($sql);
                }
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;

            }
            else
            {
                $order['bonus_id'] = 0;
                $order['bonus_sn'] = 0;
            }
        }

        if (in_array($bonus['type_id'], array(818, 819, 822, 823, 903, 904, 922, 923, 924, 925, 996, 997, 1031, 1102, 1107, 1236, 1355, 1620, 1634, 1635,1704,1762,1769,1823,1824,1869,1879,1950,1991,2063,2165,2176,2178,2299)) && $bonus['order_id'] <= 0)
        {
            $order['bonus_id'] = $bonus['bonus_id']; //临时处理0元红包送赠品
        }
    }
    //过滤收货人信息（添加反斜杠）
    foreach($consignee as $key => $value)
    {
        $order[$key] = addslashes($value);
    }

    /*专享价获取自定义邮费对应checkout by:tao20151123*/
    $source  = $GLOBALS['db']->getOne("select extension_id from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' ;");
    if(!empty($source)){
        $postage = $GLOBALS['db']->getOne("SELECT postage FROM ".$GLOBALS['ecs']->table('source')." WHERE rec_id = ".$source." LIMIT 1");
        if($postage>0){
            $custom_fee = $postage;
        }else{
            $custom_fee = false;
        }
    }

    //订单中的总金额计算
    if($order['shipping_id'] == '8'){           // 货到付款不调用专享价邮费
        $total = order_fee($order, $cart_goods, $consignee);
    }else{
        if(by_tuan_in_cart() || by_source_in_cart($cart_goods)){//订单中包含包邮团购则包邮对应checkout
            $total = order_fee($order, $cart_goods, $consignee,true);
        }else{
            $total = order_fee($order, $cart_goods, $consignee,false,$custom_fee);
        }
    }


    //宝粉活动使用指定红包包邮
    $baofen_arr = array(1776,1777);
    if(in_array(@$bonus['type_id'], $baofen_arr) && $order['shipping_id']!=8){
        $total = order_fee($order, $cart_goods, $consignee,true);
    }

    //菲士康套装 优惠券（赠2295 2294 2293 红包，并发站内信）
    if (($_SERVER['REQUEST_TIME'] < strtotime('2015-9-10 23:59:59'))){
        if($bonus['type_id'] == 2290){
            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('user_bonus') .
                " (bonus_type_id, user_id) " ."VALUES('2295', '$user_id')");
            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('user_bonus') .
                " (bonus_type_id, user_id) " ."VALUES('2294', '$user_id')");
            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('user_bonus') .
                " (bonus_type_id, user_id) " ."VALUES('2293', '$user_id')");

            $msg  = '亲爱的用户：<br />
                    您已获得【75元易视网现金抵用套券】，<br />
                    内涵：40元（太阳镜、框架镜专享）；20元（全场通用）；15元（彩色隐形眼镜专享）<br />
                    <a href="user.php?act=bonus" target="_blank" class="red">点击查看优惠券</a>
                    优惠券使用时间：8月3日-9月10日<br />
                    使用步骤：<br />
                    登录/注册【易视网】 → 我的优惠券/红包输入券号 → 结算页面选择使用优惠券<br />
                    使用规则：<br />
                    1.使用范围：可在【易视眼镜官网www.easeeyes.com】购物使用<br />
                    2.券有效期：请在15年9月10日前使用，逾期无效<br />
                    3.使用提示：每笔订单限用一张优惠券，（强生、促销团购商品除外）；此券不挂失，不合并，不找零，不兑换现金，不可以抵扣运费<br />
                    4.取消订单：如您使用优惠券后又取消了订单，不影响优惠券下次使用，有效期不变。<br />
                    5.退货说明：如您购买后发生退货行为，只可退还您交易中实际支付的金额，优惠券金额不退回。<br />
                    6.服务保证：全场商品均为100%正品，30天退换货服务，支持货到付款。<br />
                    7.客服电话：4006-177-176<br />
                    8.免责声明：该优惠券最终解释权为易视眼镜网，如有任何疑问欢迎拨打易视眼镜网客服热线或咨询在线客服<br />';
            $GLOBALS['db']->query("INSERT INTO ecs_user_msg (user_id, user_name, add_time, title, msg, extension)
								values (".$user_id.", '".$_SESSION['user_name']."', ".$_SERVER['REQUEST_TIME'].", '【75元易视网现金抵用套券】', '".$msg."', 'prize')");

            $GLOBALS['db']->query("update ecs_users set unread_msg=unread_msg+1 where user_id=".$user_id);

        }
    }


    $order['bonus']        = $total['bonus'];
    $order['goods_amount'] = $total['goods_price'];
    $order['discount']     = $total['discount'];
    $order['surplus']      = $total['surplus'];
    $order['tax']          = $total['tax'];

    //yi:购物车中的商品能享受红包支付的总额
    $discount_amout = compute_discount_amount();

    //红包和积分最多能支付的金额为商品总额
    $temp_amout = $order['goods_amount'] - $discount_amout;
    if ($temp_amout <= 0)
    {
        $order['bonus_id'] = 0;
    }
    $order['pay_id']      = empty($order['pay_id'])? 4: $order['pay_id'];
    $order['shipping_id'] = empty($order['shipping_id'])? 9: $order['shipping_id'];

    //配送方式
    if ($order['shipping_id'] > 0)
    {
        $shipping = shipping_info($order['shipping_id']);
        $order['shipping_name'] = addslashes($shipping['shipping_name']);
    }
    $order['shipping_fee'] = $total['shipping_fee'];
    $order['insure_fee']   = $total['shipping_insure'];

    //支付方式
    if ($order['pay_id'] > 0)
    {
        $payment = payment_info($order['pay_id']);

        if(intval($order['pay_id'])<100){
            $order['pay_name'] = addslashes($payment['pay_name']);
        }else{
            $order['pay_name'] = isset($_POST['bank_name'])?trim($_POST['bank_name']): "直接网银";
        }
    }
    $order['pay_fee'] = $total['pay_fee'];
    $order['cod_fee'] = $total['cod_fee'];

    //yi:数据检查之，使用红包后未满200的货到付款运费永远不为0。
    if(3==$order['pay_id'] && 0==$order['shipping_fee'] && ($order['goods_amount']-$order['bonus']<200))
    {
        if(in_array($consignee['province'], array(16,25,31)))
        {
            $order['shipping_fee'] = 10.00;
        }
        else if(in_array($consignee['province'], array(2,3,4,6,7,10,11,13,14,17,22,23,24,27)))
        {
            $order['shipping_fee'] = 18.00;
        }
        else if(in_array($consignee['province'], array(8,9,12,15,18,26,30,32)))
        {
            $order['shipping_fee'] = 20.00;
        }
        else if(in_array($consignee['province'], array(5,19,20,21,28,29)))
        {
            $order['shipping_fee'] = 25.00;
        }
        else
        {
            $order['shipping_fee'] = 25.00;
        }
        $order['order_amount'] += $order['shipping_fee'];
    }

    //商品包装
    if ($order['pack_id'] > 0)
    {
        $pack               = pack_info($order['pack_id']);
        $order['pack_name'] = addslashes($pack['pack_name']);
    }
    $order['pack_fee'] = $total['pack_fee'];

    //祝福贺卡
    if ($order['card_id'] > 0)
    {
        $card               = card_info($order['card_id']);
        $order['card_name'] = addslashes($card['card_name']);
    }
    $order['card_fee']      = $total['card_fee'];

    $order['order_amount']  = number_format($total['amount'], 2, '.', '');

    //如果全部使用余额支付，检查余额是否足够
    if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)
    {
        if($order['surplus'] >0) //余额支付里如果输入了一个金额
        {
            $order['order_amount'] = $order['order_amount'] + $order['surplus'];
            $order['surplus'] = 0;
        }
        if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
        {
            show_message_wap($_LANG['balance_not_enough']);
        }
        else
        {
            $order['surplus'] = $order['order_amount'];
            $order['order_amount'] = 0;
        }
    }


    //如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款
    if ($order['order_amount'] <= 0)
    {
        $order['order_status'] = OS_CONFIRMED;
        $order['confirm_time'] = gmtime();
        $order['pay_status']   = PS_PAYED;
        $order['pay_time']     = gmtime();
        $order['order_amount'] = 0;
    }

    $order['integral_money']   = $total['integral_money'];
    $order['integral']         = $total['integral'];
    $order['integral_money']   = 0;

    if($order['extension_code'] == 'exchange_goods')
    {
        $order['integral_money']   = 0;
        $order['integral']         = $total['exchange_integral'];
    }

    $order['from_ad']          = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';

    // 来源  手机端加上wap标示
    $referer = addslashes(@$_SESSION['referer']);
    // 判断是不是微信浏览器
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        $order['referer'] = 'wap_weixin_本站';
    }else{
        $order['referer'] = !empty($_SESSION['referer']) ? 'wap_'.$referer : 'wap_本站';
    }

    //记录扩展信息
    if ($flow_type != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id']   = $_SESSION['extension_id'];
    }

    //-----------------------------------推荐订单分成模块-----------------------------------//
    $affiliate = unserialize($_CFG['affiliate']);
    if(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1)
    {
        //推荐订单分成
        $parent_id = get_affiliate();
        if($user_id == $parent_id)
        {
            $parent_id = 0;
        }
    }
    elseif(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0)
    {
        //推荐注册分成
        $parent_id = 0;
    }
    else
    {
        //分成功能关闭
        $parent_id = 0;
    }
    //-----------------------------------推荐订单分成模块end-----------------------------------//

    //yi:把积分兑换商品应该扣除的积分加入到订单积分中
    $exchange_integral = order_exchange_goods_integral($order['user_id']);
    $order['integral'] = $order['integral'] + $exchange_integral;
    $order['parent_id']= $parent_id;
    
    //手机号加密BY:TAO
    include_once('includes/lib_crypto.php');
    $order['tel'] = encrypt($order['tel'],'tel888');
    
    //插入订单表
    $error_no = 0;
    do{
        $order['order_sn'] = get_order_sn(); //获取新订单编号
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

        $error_no = $GLOBALS['db']->errno();
        if($error_no > 0 && $error_no != 1062)
        {
            die($GLOBALS['db']->errorMsg());
        }
    }while($error_no == 1062); //如果是订单号重复则重新提交数据

    $new_order_id      = $db->insert_id();//刚插入的订单id
    $order['order_id'] = $new_order_id;

    //如果有现金折扣优惠活动:2013-10-31
    if ($total['discount'] && $total['favourable_name'])
    {
        $db->query("INSERT INTO ".$ecs->table('order_discount')." (order_id, favourable_name) VALUES (".$new_order_id.", '".serialize($total['favourable_name'])."')");
    }

    //购物车中全部商品插入订单商品表
    //xyz edit(20130110) 保存购物车信息
    if ($_SESSION['user_id'] > 0) {
        $sql =  "INSERT INTO ".$ecs->table('order_goods').
            "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
            " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = '$flow_type'";
    } else {
        if (isset($_COOKIE['cart_session_id'])) {
            $sql =  "INSERT INTO ".$ecs->table('order_goods').
                "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
                " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '$flow_type'";
        } else {
            $sql =  "INSERT INTO ".$ecs->table('order_goods').
                "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
                " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
        }
    }

    $db->query($sql);

    //若是使用特定优惠券的139用户，更改某款商品名称 9.30日后取消
    if (isset($bonus['bonus_type_id']) && $bonus['bonus_type_id'] == 725)
    {
        $db->query("UPDATE ecs_order_goods SET goods_name=concat(goods_name, '【139用户】') WHERE order_id=".$new_order_id." AND goods_id=757");
    }

    //-----------------------------------订单插入成功, 进行后续余额，积分，红包，拍卖商品处理-----------------------------------//

    //====================更新会员账号信息（积分，余额，红包）【功能】====================//
    if($order['user_id'] > 0 && $order['surplus'] > 0)
    {
        //余额
        log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
    }

    if($order['user_id'] > 0 && $order['integral'] > 0)
    {
        //积分
        $exchange_lang = ($exchange_integral>0)? '积分兑换订单：'.$order['order_sn'].'中商品扣除'.$order['integral'].'积分': sprintf($_LANG['pay_order'], $order['order_sn']);
        log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), $exchange_lang);
    }

    if($order['bonus_id'] > 0 && $temp_amout > 0)
    {
        //红包
        use_bonus($order['bonus_id'], $new_order_id);
    }

    //-----------------------------------如果订单金额为0 处理虚拟卡-----------------------------------//
    if($order['order_amount'] <= 0)
    {
        $sql = "SELECT goods_id, goods_name, goods_number AS num FROM ".$GLOBALS['ecs']->table('cart') .
            " WHERE is_real = 0 AND extension_code = 'virtual_card' AND session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
        $res = $GLOBALS['db']->getAll($sql);
        if(!empty($res))
        {
            $virtual_goods = array();
            foreach($res AS $row)
            {
                $virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
            }

            if($virtual_goods AND $flow_type != CART_GROUP_BUY_GOODS)
            {
                //虚拟卡发货
                if (virtual_goods_ship($virtual_goods,$msg, $order['order_sn'], true))
                {
                    $sql =  "SELECT COUNT(*) FROM " . $ecs->table('order_goods') .
                        " WHERE order_id = '$order[order_id]' " .
                        " AND is_real = 1";
                    if($db->getOne($sql) <= 0)
                    {
                        //修改订单状态
                        update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

                        //如果订单用户不为空，计算积分，并发给用户；发红包 .
                        if($order['user_id'] > 0)
                        {
                            //取得用户信息
                            $user = user_info($order['user_id']);

                            //计算并发放积分
                            $integral = integral_to_give($order);
                            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

                            //发放红包
                            send_order_bonus($order['order_id']);
                        }
                    }
                }
            }
        }
    }

	// 招行专享活动  2016.1.14 ~ 2016.02.02
    if ($order['pay_id'] == 15 && $_SERVER['REQUEST_TIME'] >= strtotime('2016-01-14 00:00:00') && $_SERVER['REQUEST_TIME'] < strtotime('2016-02-02 23:59:59') && $order['shipping_id'] == 9)
    {
        //招行专享活动商品数组（favnum：数量 favprice:价格[favnum'=>2,'favprice'=>128 表示两盒卖128]）BY:tao
        $cmb_goods = array(
            array('gid'=>91,'favnum'=>2,'favprice'=>128),
            array('gid'=>757,'favnum'=>2,'favprice'=>152),
            array('gid'=>119,'favnum'=>2,'favprice'=>160),
            array('gid'=>767,'favnum'=>2,'favprice'=>136),
            array('gid'=>4321,'favnum'=>2,'favprice'=>64),
            array('gid'=>2581,'favnum'=>2,'favprice'=>104),
            array('gid'=>4477,'favnum'=>2,'favprice'=>356),
            array('gid'=>891,'favnum'=>2,'favprice'=>39.8),
            array('gid'=>4925,'favnum'=>1,'favprice'=>76),
            array('gid'=>589,'favnum'=>1,'favprice'=>14),
            array('gid'=>596,'favnum'=>1,'favprice'=>35),
            array('gid'=>609,'favnum'=>1,'favprice'=>26),
        );
        //拆分数组获取商品id数组
        foreach($cmb_goods as $v){
            $cmb_gid[] = $v['gid']; 
        }
        foreach($cart_goods as $cmb) {
    	   if(in_array($cmb['goods_id'],$cmb_gid) && $cmb['is_gift']!=1 && !$cmb['extension_code']){
    	           $cmbArr[$cmb['goods_id']]['goodsPrice']=$cmb['goods_price'];    //商品价格
    	           $cmbArr[$cmb['goods_id']]['goodsNum']+=$cmb['goods_number'];    //商品总数
    	   }  
	    }
        $discount = 0;
        foreach($cmb_goods as $v){
            if(!empty($cmbArr[$v['gid']])){
                
                $favPrice       = $v['favprice'];
                $gTotalPrice    = $cmbArr[$v['gid']]['goodsPrice']*$cmbArr[$v['gid']]['goodsNum'];          //该商品总价
                $favTimes       = floor($cmbArr[$v['gid']]['goodsNum']/$v['favnum']);                       //享受两盒优惠的次数
                $favLeft        = $cmbArr[$v['gid']]['goodsNum'] % $v['favnum'];                            //未优惠商品的余数
                $favTotalPrice  = $favLeft==0? $favPrice*$favTimes:($favPrice*$favTimes)+$cmbArr[$v['gid']]['goodsPrice'];  //两盒优惠后加上此商品剩余未优惠的价格
                $discount+=$gTotalPrice-$favTotalPrice;
                
            }
        }
        $total['amount_formated'] = price_format($order['order_amount']-$discount, false); //应支付金额(格式化)
		$order['order_amount'] = $order['order_amount'] - $discount; //应支付金额
		
	    //更改订单总金额
		$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('order_info')." SET order_amount='".$order['order_amount']."', discount=discount+".$discount." WHERE order_id=".$order['order_id']);
	    
    }
	// 
	if ($order['pay_id'] == 15 && $_SERVER['REQUEST_TIME'] >= strtotime('2016-05-23 00:00:00') && $_SERVER['REQUEST_TIME'] < strtotime('2016-09-20 23:59:59'))
	{
            /*
			$zhekou_money = 0;
			$order['goods_amount'] = $order['goods_amount'] - $order['discount'];
			$zhekou_money = intval($order['goods_amount'] * 0.05);
			$total['amount_formated'] = price_format($order['order_amount']-$zhekou_money, false); //应支付金额(格式化)
			$order['order_amount'] = $order['order_amount'] - $zhekou_money; //应支付金额
            */
            
            $cmb_amount = 0;
    	    $zhekou_money = 0;
            $temp_cmb_amount = 0;//用来算折扣的总金额变量
    	    foreach ($cart_goods as $cmb) {
    	       $brand_id = $GLOBALS['db']->getOne("SELECT brand_id FROM ecs_goods WHERE goods_id = ".$cmb['goods_id']);
	    		$cmb_amount = $cmb_amount+($cmb['goods_price']*$cmb['goods_number']);
                if($brand_id == 3 && $cmb['is_cx'] == 0){
                    $temp_cmb_amount = $temp_cmb_amount+($cmb['goods_price']*$cmb['goods_number']);
                }
    	    }
    	   	$cmb_amount = $cmb_amount-$order['discount'];//减去折扣
    	   
            $zhekou_money = intval($temp_cmb_amount * 0.3);
            $total['amount_formated'] = price_format($order['order_amount']-$zhekou_money, false); //应支付金额(格式化)
      		$order['order_amount'] = $order['order_amount'] - $zhekou_money; //应支付金额
        
			//更改订单总金额
			$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('order_info')." 
			SET order_amount='".$order['order_amount']."', discount=discount+".$zhekou_money." WHERE order_id=".$order['order_id']);
	}
	/*招行支付 指定团购享受7折*/
    if ($order['pay_id'] == 15 && $_SERVER['REQUEST_TIME'] >= strtotime('2016-08-01 00:00:00') && $_SERVER['REQUEST_TIME'] < strtotime('2016-11-15 23:59:59'))
    {
        //860 859 858 857 856 855 854 853 852 610
        $tuan_arr = array(885,889,890,892,891,893,878,894,882,895,883,884,886,887,888);
	    $cmb_amount = 0;
	    $zhekou_money = 0;
	    $temp_cmb_amount = 0;//用来算折扣的总金额变量
        
        foreach ($cart_goods as $cmb) {
	    		$cmb_amount = $cmb_amount+($cmb['goods_price']*$cmb['goods_number']);
                if($cmb['extension_code'] == 'tuan_buy' && in_array($cmb['goods_sn'],$tuan_arr)){
                    $temp_cmb_amount = $temp_cmb_amount+($cmb['goods_price']*$cmb['goods_number']);
                }
	    }
        
	   	$cmb_amount = $cmb_amount-$order['discount'];//减去折扣
	   
        $zhekou_money = intval($temp_cmb_amount * 0.3);
        $total['amount_formated'] = price_format($order['order_amount']-$zhekou_money, false); //应支付金额(格式化)
  		$order['order_amount'] = $order['order_amount'] - $zhekou_money; //应支付金额
       
	    //更改订单总金额
		$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('order_info')." SET order_amount='".$order['order_amount']."', discount=discount+".$zhekou_money." WHERE order_id=".$order['order_id']);
	    
    }
    //迅雷，15元邮费0元试用
    $xunlei_15 = FALSE;
    $xunlei_rs = $GLOBALS['db']->getAll("select * from ecs_cart where session_id='".SESS_ID."' AND goods_id=4291");
    if ($xunlei_rs) {
        $xunlei_15 = TRUE;
    }

    if(miaosha_free_ship())
    {
        $total['amount'] = $total['amount'] - $total['shipping_fee'];
        $order['order_amount'] = $total['amount'];
        $total['shipping_fee']= 0;
        $order['shipping_fee']=0;
        $total['amount_formated'] = price_format($order['order_amount'], false);
    }

    //清空购物车
    clear_cart($flow_type);
    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);//插入支付日志
    clear_all_files();//清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少

    //-----------------------------------取得支付信息，生成支付代码，页面显示出来-----------------------------------//
    if($order['order_amount'] > 0)
    {
        $order['bank_id'] = 0;

        //如果pay_id是3位数 则是网银直接支付【pay_id支付代码】
        if(intval($order['pay_id'])>100 && intval($order['pay_id'])<800)
        {
            $bank_id            = intval($order['pay_id']);
            $order['bank_id']   = $bank_id;
            $order['pay_id']    = 10;   //快钱网银：pay_id=10;
            $order['bank_name'] = isset($_POST['bank_name'])? trim($_POST['bank_name']):"";
        }
        elseif(intval($order['pay_id']) == 901) //得仕通支付
        {
            $bank_id            = intval($order['pay_id']);
            $order['bank_id']   = $bank_id;
            $order['pay_id']    = 14;   //得仕通：pay_id=14;
            $order['bank_name'] = isset($_POST['bank_name'])? trim($_POST['bank_name']):"";
        }
        elseif(intval($order['pay_id'])>800)//预付费卡支付
        {
            $bank_id            = intval($order['pay_id']);
            $order['bank_id']   = $bank_id;
            $order['pay_id']    = 13;   //预付费卡：pay_id=13;
            $order['bank_name'] = isset($_POST['bank_name'])? trim($_POST['bank_name']):"";
        }

        $payment = payment_info($order['pay_id']);

        if(!empty($payment['pay_code']))
        {
            if($payment['pay_code'] == 'alipay' || $payment['pay_code'] == 'cmb' || $payment['pay_code'] == 'wxzf'){
                include_once('includes/modules/payment/'.$payment['pay_code'].'_wap.php');

            }else{
                include_once('includes/modules/payment/'.$payment['pay_code'].'.php');
            }
        }
        else
        {
            $payment = payment_info(10);
            include_once('includes/modules/payment/'.$payment['pay_code'].'.php');
        }

        if ($xunlei_15)
        {
            $total['amount_formated'] = price_format($order['order_amount']-$total['shipping_base_fee']+15, false); //应支付金额(格式化)
            $order['order_amount'] = $order['order_amount'] - $total['shipping_base_fee'] + 15; //应支付金额
            $j_shipping_fee = $order['shipping_fee'] - $total['shipping_base_fee'] + 15; //免首重后运费
            $order['shipping_fee'] = $j_shipping_fee;

            //更改订单中的邮费为15,订单总金额减首种运费
            $GLOBALS['db']->query("update ".$GLOBALS['ecs']->table('order_info')." set shipping_fee='".$j_shipping_fee."', order_amount='".$order['order_amount']."' where order_id=".$order['order_id']);
        }

        $pay_obj    = new $payment['pay_code'];
        $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));

        $order['pay_desc'] = $payment['pay_desc'];
        //支付代码按钮 写到前端
        $smarty->assign('pay_online', $pay_online);
    }
    if(!empty($order['shipping_name']))
    {
        $order['shipping_name']=trim(stripcslashes($order['shipping_name']));
    }

    /*-----------------------------------订单信息(前台)-----------------------------------*/
    $smarty->assign('order',      $order);
    $smarty->assign('total',      $total);
    $smarty->assign('goods_list', $cart_goods); //购物车中的商品列表
    $smarty->assign('order_submit_back', sprintf($_LANG['order_submit_back'], $_LANG['back_home'], $_LANG['goto_user_center'])); //返回提示


    //===========================================================cps接口 订单推送============================================================================//

    /*==============获取返利网接口cookie数据=======================================*/
    $fanli_uid     = isset($_COOKIE['fanli_uid'])? $_COOKIE['fanli_uid']:'';
    $channelid     = isset($_COOKIE['channelid'])? $_COOKIE['channelid']:'';
    $fanli_uname   = isset($_COOKIE['ECS']['username'])? $_COOKIE['ECS']['username']:'';
    $fanli_ununion = isset($_COOKIE['fanli_ununion'])? $_COOKIE['fanli_ununion']:''; //"true"表示非联合登录
    /*==============获取cookie数据  end=============================================*/

    //对全局条件的返利的判断（比如说使用了CT优惠券的就不给其它返利）
    $no_whole_cps = true;
    if(!empty($order['bonus_id']) && $order['bonus']>0 && bonus_come($order['bonus_id'], 141))
    {
        $no_whole_cps = false;
    }

    // smzdm(什么值得买)
    if(isset($_COOKIE['cpsinfo_smzdm']) && isset($_COOKIE['cpsinfo_smzdm_feedback'])){
		// 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_smzdm';
        }else{
            $cps_ref = 'wap_smzdm';
        }
        update_cps_from($cps_ref,$_COOKIE['cpsinfo_smzdm_feedback'], $order['order_id'], $order['order_sn']);
        require_once(dirname(__FILE__) . '/cps/smzdm/post_order.class.php');
        $yqf = new post_order();
        $referer = $yqf->get_order_info2($order['order_id'],$_COOKIE['cpsinfo_smzdm_feedback']);//获取订单信息
        $dm_url  = $referer['url'];
        $post_data = array('key'=>$referer['key'],'order'=>$referer['order']);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $dm_url);    // 设置你准备提交的URL
		curl_setopt($curl, CURLOPT_POST, true);  // 设置POST方式提交
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容

        curl_close($curl);        // 关闭curl请求
        $smarty->assign('fanli_src',  $dm_url);
    }
	
    //51fanli
    if(isset($_COOKIE['cpsinfo_51fanli_channel_id']) && isset($_COOKIE['cpsinfo_51fanli_tracking_code']) && ! empty($_COOKIE['cpsinfo_51fanli_channel_id']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_51fanli';
        }else{
            $cps_ref = 'wap_51fanli';
        }
        update_cps_from($cps_ref, $_COOKIE['cpsinfo_51fanli_tracking_code'].'|'.$_COOKIE['cpsinfo_51fanli_u_id'], $order['order_id'], $order['order_sn']);

        require_once(dirname(__FILE__) . '/cps/51fanli/post_order.class.php');

        $t = new post_order();
        $xml = $t->get_order_xml($order['order_id'], $_COOKIE['cpsinfo_51fanli_u_id']);

        $urn = "http://union.51fanli.com/dingdan/push";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urn);
        $post_data = array(
            "content" => $xml
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
    }

    //tianyi
    if(isset($_COOKIE['cpsinfo_ty_appid']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_tianyi';
        }else{
            $cps_ref = 'wap_tianyi';
        }
        update_cps_from($cps_ref, $_COOKIE['cpsinfo_ty_trackingcode'].'|'.$_COOKIE['cpsinfo_ty_uid'], $order['order_id'], $order['order_sn']);

        require_once(dirname(__FILE__) . '/cps/tianyi/post_order.class.php');
        if(empty($_COOKIE['cpsinfo_ty_uid'])){
            $_COOKIE['cpsinfo_ty_uid'] = 0;
        }
        if(empty($_COOKIE['cpsinfo_ty_trackingcode'])){
            $_COOKIE['cpsinfo_ty_trackingcode'] = '';
        }

        $t = new post_order();
        $p = $t->get_order($order['order_id'], 'yishiwang', $_COOKIE['cpsinfo_ty_uid'], $_COOKIE['cpsinfo_ty_trackingcode']);

        $post_data = array(
            "content" => $p
        );
        //$post_data = array();//订单数据
        $urn = "http://42.51.8.144/apis.php";
        //$urn = "http://42.51.8.144/tapis.php";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urn);    // 设置你准备提交的URL
        curl_setopt($curl, CURLOPT_POST, true);  // 设置POST方式提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
        $data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容

        curl_close($curl);        // 关闭curl请求

    }
    /*=====================================================================linktech cps接口===================================================================*/
    elseif(isset($_COOKIE['LTINFO']) && !empty($_COOKIE['LTINFO']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_linktech';
        }else{
            $cps_ref = 'wap_linktech';
        }
        if(!empty($order))
        {
            update_cps_from($cps_ref, $_COOKIE['LTINFO'], $order['order_id'], $order['order_sn']);
        }
        $lt_a_id   = trim($_COOKIE['LTINFO']);
        $lt_m_id   = "easeeyes";
        $lt_mbr_id = "user_".$order['user_id'];
        $lt_o_cd   = trim($order['order_sn']);
        $lt_p_cd   = '';//商品编号
        $lt_it_cnt = '';//商品数量
        $lt_price  = '';//商品单价
        $lt_c_cd   = '';//商品分类
        if(!$no_whole_cps){$lt_a_id = 'A100126293';}//CT来源订单 固定a_id.

        //订单红包金额：$order['bonus'], 订单应付金额：$order['order_amount'], 订单产品总金额：$order['goods_amount'], 购物车商品：$cart_goods。
        $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
        $lt_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;//商品价格比例

        //遍历该订单中商品，生成接口数据
        foreach($cart_goods as $k => $v)
        {
            $goods_id   = $cart_goods[$k]['goods_id'];
            $lt_price_bi= floor($cart_goods[$k]['goods_price']*$lt_bili);
            $lt_p_cd   .= "||goods".$goods_id;
            $lt_it_cnt .= "||".$cart_goods[$k]['goods_number'];
            $lt_price  .= "||".$lt_price_bi;

            //c_cd(单独计算礼包产品c_cd)
            if($cart_goods[$k]['extension_code']=='package_buy')
            {
                if($cart_goods[$k]['goods_sn']==1 && $cart_goods[$k]['market_price']>0)
                {
                    $lt_c_cd .= "||".goods_cat_cd2($goods_id, true);
                }
                else
                {
                    continue;
                }
            }
            elseif ( ! empty($cart_goods[$k]['extension_code']))
            {
                $lt_c_cd .= "||".goods_cat_cd2($goods_id, true);
            }
            else
            {
                $lt_c_cd .= "||".goods_cat_cd2($goods_id);
            }
        }

        if(count($cart_goods)>0)
        {
            $lt_p_cd   = substr($lt_p_cd,   2);
            $lt_it_cnt = substr($lt_it_cnt, 2);
            $lt_price  = substr($lt_price,  2);
            $lt_c_cd   = substr($lt_c_cd,   2);
        }

        //生成URL，并发送数据给linktech服务器
        if(!empty($lt_p_cd) && !empty($lt_it_cnt) && !empty($lt_c_cd))
        {
            $lt_url = "http://service.linktech.cn/purchase_cps.php?a_id=".$lt_a_id.
                "&m_id=".$lt_m_id."&mbr_id=".$lt_mbr_id."&o_cd=".$lt_o_cd."&p_cd=".$lt_p_cd.
                "&price=".$lt_price."&it_cnt=".$lt_it_cnt."&c_cd=".$lt_c_cd;
            if(from_qq_login($order['user_id']) && $no_whole_cps)
            {
                $open_id = $GLOBALS['db']->getOne("select refer_id from ecs_users where user_id=".$order['user_id']." limit 1;");
                $lt_url .= "&mbr_name=A100136514".$open_id;
            }
            $smarty->assign('fanli_src',  $lt_url);//统一返利接口
        }
    }
    elseif(isset($_COOKIE['zhitui_info']) && !empty($_COOKIE['zhitui_info']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_zhitui';
        }else{
            $cps_ref = 'wap_zhitui';
        }
        //yi:智推网cps接口
        if(!empty($order))
        {
            update_cps_from($cps_ref, $_COOKIE['zhitui_info'], $order['order_id'], $order['order_sn']);
        }
        $zhitui_info = trim($_COOKIE['zhitui_info']);
        $ar_zhitui   = explode('|_|', $zhitui_info);

        //处理红包等数据
        $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
        $jg_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;//商品价格比例

        if(!empty($ar_zhitui))
        {
            $zhitui_a_id	= intval($ar_zhitui[1]);
            $zhitui_subid	= trim($ar_zhitui[2]);
            $zhitui_o_cd	= trim($order['order_sn']);
            $zhitui_o_date	= date('YmdHis', time()+3600*8);
            $zhitui_status	= 0;
            $zt_goods_id	= '';
            $zt_price		= '';
            $zt_it_cnt		= '';
            $zt_rate		= 0;
            $zt_rate_memo	= '';

            //订单商品
            foreach($cart_goods as $k => $v)
            {
                $tejia				= ($v['is_cx']==1 || $v['extension_code']=='package_buy')? true: false;
                $zt_c_cd			= goods_cat_cd($v['goods_id'], $tejia);
                $zt_c_bili			= goods_cat_cd_bili($zt_c_cd);
                $v['goods_price']	= round($v['goods_price']*$jg_bili, 2);

                $zt_goods_id .= empty($zt_goods_id)? 'goods'.$v['goods_id']: "||goods".$v['goods_id'];
                $zt_price    .= empty($zt_price)? floor($v['goods_price']):  "||".floor($v['goods_price']);
                $zt_it_cnt   .= empty($zt_it_cnt)? intval($v['goods_number']): "||".intval($v['goods_number']);
                $zt_rate     .= empty($zt_rate)?    $zt_c_bili: "||".$zt_c_bili;
                $zt_rate_memo.= empty($zt_rate_memo)? $zt_c_cd: "||".$zt_c_cd;
            }

            if(!empty($zt_goods_id) && !empty($zt_price) && !empty($zt_it_cnt))
            {
                $zt_url = 'http://api.zhitui.com/recive.php?a_id='.$zhitui_a_id.'&subid='.$zhitui_subid.'&o_cd='.$zhitui_o_cd.'&p_cd='.$zt_goods_id.'&price='.$zt_price.'&it_cnt='.
                    $zt_it_cnt.'&o_date='.$zhitui_o_date.'&rate='.$zt_rate.'&rate_memo='.$zt_rate_memo.'&status='.$zhitui_status.'&note=';
                $smarty->assign('fanli_src',  $zt_url);//统一返利接口
            }
        }
    }
    elseif(isset($_COOKIE['lergao_info']) && !empty($_COOKIE['lergao_info']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_lergao';
        }else{
            $cps_ref = 'wap_lergao';
        }
        //yi:乐告网cps接口
        if(!empty($order))
        {
            update_cps_from($cps_ref, $_COOKIE['lergao_info'], $order['order_id'], $order['order_sn']);
        }
        $lergao_info = trim($_COOKIE['lergao_info']);


        $ar_lergao = explode('|_|', $lergao_info);

        //处理红包等数据
        $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
        $jg_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;//商品价格比例

        if(!empty($ar_lergao))
        {
            $lergao_a_id	= intval($ar_lergao[1]);
            $lergao_subid	= trim($ar_lergao[2]);
            $lergao_o_cd	= trim($order['order_sn']);
            $lergao_o_date	= date('YmdHis', time()+8*3600);
            $lergao_status	= 0;
            $lg_goods_id	= '';
            $lg_price		= '';
            $lg_it_cnt		= '';
            $lg_rate		= 0;
            $lg_rate_memo	= '';

            //订单商品
            foreach($cart_goods as $k => $v)
            {
                $tejia        = ($v['is_cx']==1 || $v['extension_code']=='package_buy')? true: false;
                $lg_c_cd	  = goods_cat_cd($v['goods_id'], $tejia);
                $lg_c_bili    = goods_cat_cd_bili($lg_c_cd);
                $v['goods_price'] = round($v['goods_price']*$jg_bili, 2);

                $lg_goods_id .= empty($lg_goods_id)? 'goods'.$v['goods_id']: "||goods".$v['goods_id'];
                $lg_price    .= empty($lg_price)? floor($v['goods_price']):  "||".floor($v['goods_price']);
                $lg_it_cnt   .= empty($lg_it_cnt)? intval($v['goods_number']): "||".intval($v['goods_number']);
                $lg_rate     .= empty($lg_rate)?    $lg_c_bili: "||".$lg_c_bili;
                $lg_rate_memo.= empty($lg_rate_memo)? $lg_c_cd: "||".$lg_c_cd;
            }

            if(!empty($lg_goods_id) && !empty($lg_price) && !empty($lg_it_cnt))
            {
                //示例：/public.aspx?v_id=taobao&w_id=00000&u_id=123&o_cd=123123123&o_s=new&o_d=20081013121312&p_cd=P123||P124&p_p=45.00||30.00&p_cnt=1||1&p_ctg=122||122
                $lg_url = 'http://dc.lergao.com/sync/vendor/public.aspx?v_id=easeeyes&w_id='.$lergao_subid.'&u_id=&o_cd='.$lergao_o_cd.'&o_s=new&o_d='.$lergao_o_date
                    .'&p_cd='.$lg_goods_id.'&p_p='.$lg_price.'&p_cnt='.$lg_it_cnt.'&p_ctg='.$lg_rate_memo;
                $smarty->assign('fanli_src',  $lg_url);//统一返利接口
            }
        }
    }
    elseif(isset($_COOKIE['cpsinfo_360']))
    {
        /*=====================================================================360返利接口 start ======================================================*/
        require_once ROOT_PATH.'cps/360cps/core/CPS360_api.class.php';
        CPS360_api::order_save($order['order_sn'], $data = array());//360_cps_api
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_360';
        }else{
            $cps_ref = 'wap_360';
        }
        if(!empty($order)) {
            update_cps_from($cps_ref, '', $order['order_id'], $order['order_sn']);
        }
    }
    elseif (isset($_COOKIE['cpsinfo_duomai']) && isset($_COOKIE['cpsinfo_duomai_siteid']))
    {
        //多麦订单推送接口 注：取消了推送
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_duomai';
        }else{
            $cps_ref = 'wap_duomai';
        }
        if(!empty($order)){
            update_cps_from($cps_ref, $_COOKIE['cpsinfo_duomai_siteid'], $order['order_id'], $order['order_sn']);
        }
        // 订单推送zhang:160223
        require_once(dirname(__FILE__) . '/cps/duomai/post_order.class.php');
        $yqf = new post_order();
        $dm_mess = $yqf->get_order_info($order['order_id']);//获取订单信息

        $dm_url = "http://www.duomai.com/api/order.php?hash=3eb7ad80fc84acd0139e47389b8faa7b&euid=".urlencode($_COOKIE['cpsinfo_duomai_siteid'])."&".$dm_mess;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $dm_url);    // 设置你准备提交的URL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容

        curl_close($curl);        // 关闭curl请求
        $smarty->assign('fanli_src',  $dm_url);
    }
    elseif (isset($_COOKIE['cpsinfo_xunlei']) && isset($_COOKIE['xunlei_cps_login_user']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_xunlei';
        }else{
            $cps_ref = 'wap_xunlei';
        }
        if(!empty($order)) {
            update_cps_from($cps_ref, $_COOKIE['xunlei_cps_login_user'], $order['order_id'], $order['order_sn']);
        }

        //迅雷订单推送
        require_once(dirname(__FILE__) . '/cps/xunlei/post_order.class.php');
        $t = new post_order();
        $post_data = $t->get_order_info($order['order_id'], 1, $order['add_time']);

        //$post_url = "http://test.jifen.xunlei.com/call?c=owner&a=gateway"; //测试地址
        $post_url = "http://jifen.xunlei.com/call?c=owner&a=gateway"; //正式地址
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($curl);
        curl_close($curl);
    }
    elseif (isset($_COOKIE['cpsinfo_yiqifa_src']) && isset($_COOKIE['cpsinfo_yiqifa_wi']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_qiyifa';
        }else{
            $cps_ref = 'wap_qiyifa';
        }
        //亿起发订单推送
        if(!empty($order)) {
            $cid_wi = $_COOKIE['cpsinfo_yiqifa_cid'] . ',' . $_COOKIE['cpsinfo_yiqifa_wi'];
            update_cps_from($cps_ref, $cid_wi, $order['order_id'], $order['order_sn']);
        }

        require_once(dirname(__FILE__) . '/cps/yiqifa/post_order.class.php');
        $yqf = new post_order();
        $yqf_url = $yqf->get_order_info($order['order_id']);//获取订单信息

        $cpsinfo_yiqifa_cid = isset($_COOKIE['cpsinfo_yiqifa_cid']) ? $_COOKIE['cpsinfo_yiqifa_cid'] : '';
        $cpsinfo_yiqifa_wi = isset($_COOKIE['cpsinfo_yiqifa_wi']) ? $_COOKIE['cpsinfo_yiqifa_wi'] : '';

        //$yiqifa_url = "http://o.yiqifa.com/servlet/handleCpsIn?cid=".$cpsinfo_yiqifa_cid."&wi=".$cpsinfo_yiqifa_wi."&on=".$order['order_sn'].$yqf_url;//2013.05.31要求更改接口地址
        $yiqifa_url = "http://o.yiqifa.com/servlet/handleCpsInterIn?interId=5191fd5fe03bbcaa579e8b03&cid=".$cpsinfo_yiqifa_cid."&wi=".$cpsinfo_yiqifa_wi."&on=".$order['order_sn'].$yqf_url;
        $smarty->assign('fanli_src',  $yiqifa_url);
    }
    elseif (isset($_COOKIE['cpsinfo_yiqifa_src_roi']) && isset($_COOKIE['cpsinfo_yiqifa_wi_roi']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_yiqifa2015';
        }else{
            $cps_ref = 'wap_yiqifa2015';
        }
        //亿起发ROI2015订单推送
        if(!empty($order)) {
            $cid_wi = $_COOKIE['cpsinfo_yiqifa_cid_roi'] . ',' . $_COOKIE['cpsinfo_yiqifa_wi_roi'];
            update_cps_from($cps_ref, $cid_wi, $order['order_id'], $order['order_sn']);
        }
        require_once(dirname(__FILE__) . '/cps/yiqifa2015/post_order.class.php');
        $yqf = new post_order();
        $yqf_json = $yqf->get_order_info($order['order_id']);//获取订单信息

        $yiqifa_url = "http://o.yiqifa.com/servlet/handleCpsInterIn?interId=56e7d913b52a0689c5cef251&".$yqf_json;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $yiqifa_url);    // 设置你准备提交的URL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容

        curl_close($curl);        // 关闭curl请求
        $smarty->assign('fanli_src',  $yiqifa_url);
    }
    elseif (isset($_COOKIE['AELINFO']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_17elink';
        }else{
            $cps_ref = 'wap_17elink';
        }
        //17elink订单
        update_cps_from($cps_ref, $_COOKIE['AELINFO'], $order['order_id'], $order['order_sn']);

        require_once(dirname(__FILE__) . '/cps/17elink/post_order.class.php');
        $elink = new post_order();
        $elink_src = $elink->get_order_info($order['order_id']);
        $smarty->assign('elink_src',  $elink_src);
    }
    elseif(!@$_COOKIE['LTINFO'] && from_qq_login($order['user_id']) && $no_whole_cps)
    {
        /* ----------------------------------------------------------------------------------------------------------------------
         * 返利规则：只要是qq联合登录的用户都要返利。qq联合登录放在最后。
         * ----------------------------------------------------------------------------------------------------------------------
           a_id = A100136514$open_id     A100136514固定值，后面必须加上腾讯传送的openid值
           c_cd = qq_login               固定值，必须加上
         */
        $open_id   = $GLOBALS['db']->getOne("select refer_id from ecs_users where user_id=".$order['user_id']." limit 1;");
        $a_id      = 'A100136514'.$open_id;
        $m_id      = "easeeyes";
        $o_cd      = trim($order['order_sn']);
        $lt_p_cd   = '';
        $lt_it_cnt = '';
        $lt_price  = '';
        $lt_c_cd   = '';
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_qq_union_login';
        }else{
            $cps_ref = 'wap_qq_union_login';
        }
        if(!empty($order))
        {
            update_cps_from($cps_ref, $a_id, $order['order_id'], $order['order_sn']);
        }

        //yi:不管是红包，还是积分，全部的情况都处理了。$cart_goods购物车商品。 $order订单信息。
        $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
        $lt_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;

        //遍历该订单中商品，生成接口数据
        foreach($cart_goods as $k => $v)
        {
            $goods_id   = $cart_goods[$k]['goods_id'];
            $lt_price_bi= floor($cart_goods[$k]['goods_price']*$lt_bili);

            $lt_p_cd   .= "||goods".$goods_id;			        //商品编号
            $lt_it_cnt .= "||".$cart_goods[$k]['goods_number']; //商品数量
            $lt_price  .= "||".$lt_price_bi;                    //商品单价
            $lt_c_cd   .= "||qq_login";                         //c_cd
        }
        if(count($cart_goods)>0)
        {
            $lt_p_cd   = substr($lt_p_cd,   2);
            $lt_it_cnt = substr($lt_it_cnt, 2);
            $lt_price  = substr($lt_price,  2);
            $lt_c_cd   = substr($lt_c_cd,   2);
        }
        $lt_url = "http://service.linktech.cn/purchase_cps.php?a_id=".$a_id."&m_id=".$m_id."&o_cd=".$o_cd."&p_cd=".$lt_p_cd."&it_cnt=".$lt_it_cnt."&price=".$lt_price."&c_cd=".$lt_c_cd;
        $smarty->assign('fanli_src',  $lt_url);//统一返利接口
    }
    elseif (isset($_COOKIE['cpsinfo_shuntian']))
    {
        // 判断是不是微信浏览器
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $cps_ref = 'wap_weixin_shuntian';
        }else{
            $cps_ref = 'wap_shuntian';
        }
        update_cps_from($cps_ref, '', $order['order_id'], $order['order_sn']); //顺天cps
    }
    else
    {
        //TODO:其它cps订单推送（后期）
    }
    //=====================================================================处理cps接口操作 end===================================================================//
    //统计来自外部活动或广告链接的订单数量和金额
    if (isset($_COOKIE['click_session_id'])) {
        if(!empty($order)) {
            $sql_at = "UPDATE ".$GLOBALS['ecs']->table('active_stat_new'). "SET order_num=order_num+1, order_money=order_money+".$order['order_amount'].", order_id=concat(order_id, ',".$order['order_id']."'), user_id=concat(user_id, ',".$_SESSION['user_id']."')  WHERE cookieid = '".$_COOKIE['click_session_id']."' AND access_time = '".$_COOKIE['click_time']."'";
            $GLOBALS['db']->query($sql_at);
        }
    }

    //TEST:DPS所需产品数据
    //orderid=&orderprice=&pid1=&catid1=&quantity1=&price1=&pid2=&catid2=&quantity2=&price2=&pid3=&catid3=&quantity3=&price3=
    /*$dps_str = '&orderid='.$order['order_sn'].'&orderprice='.$order['order_amount'];
    $x = 1;
    $y = count($cart_goods);
    foreach($cart_goods as $k => $v)
    {
        if ($x < 4)
        {
            $dps_str .= '&pid'.$x.'='.$cart_goods[$k]['goods_id'].'&catid'.$x.'=1&quantity'.$x.'='.$cart_goods[$k]['goods_number'].'&price'.$x.'='.$cart_goods[$k]['goods_price'];
        }
        $x++;
    }
    if ($y < 3)
    {
        $xx = 3 - $y;
        for($z=1; $z<=$xx; $z++)
        {
            $yy = $y+$z;
            $dps_str .= "&pid".$yy."=&catid".$yy."=&quantity".$yy."=&price".$yy."=";
        }
    }
    $smarty->assign('dps_str',  $dps_str);*/
    //TEST:DPS所需产品数据 END

    //-----------------------------------清除session中收货人信息-----------------------------------//
    unset($_SESSION['flow_consignee']);
    unset($_SESSION['flow_order']);
    unset($_SESSION['direct_shopping']);
}
/*---------------------------旧版提交订单-------------------------*/
elseif($_REQUEST['step'] == 'done_old')
{
    include_once('includes/lib_clips.php');
    include_once('includes/lib_payment.php');
	
    if(empty($_SESSION['bd'])&&empty($_GET['orderid'])){

        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        //xyz:20130110 保存购物车信息
        if ($_SESSION['user_id'] > 0) {
            $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND parent_id = 0 AND (is_gift = 0 or is_gift=888) AND rec_type = '$flow_type'";
        } else {
            if (isset($_COOKIE['cart_session_id'])) {
                $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
            } else {
                $sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE session_id = '" . SESS_ID . "' AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";
            }
        }
        if($db->getOne($sql) == 0)
        {
            show_message_wap($_LANG['no_goods_in_cart'], 'flow.php', 'flow.php', 'warning');//检查购物车中是否有商品
            exit;
        }

        //----------------------------------------------检查商品库存----------------------------------------------//
        //如果使用库存且下订单时减库存，则减少库存
        if($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
        {
            $cart_goods_stock = get_cart_goods();
            $_cart_goods_stock = array();
            foreach ($cart_goods_stock['goods_list'] as $value)
            {
                $_cart_goods_stock[$value['rec_id']] = $value['goods_number'];
            }
            flow_cart_stock($_cart_goods_stock);
            unset($cart_goods_stock, $_cart_goods_stock);
        }

        //----------------------------------------------检查用户是否已经登录---------------------------------------//
        if(empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
        {
            //用户没有登录且没有选定匿名购物，转向到登录页面
            ecs_header("Location: user.php?step=login\n");
            exit;
        }

        //check consignee
        $consignee = get_consignee($_SESSION['user_id']);
        if(empty($consignee['consignee'])||empty($consignee['province'])||empty($consignee['city'])||empty($consignee['district']))
        {
            ecs_header("Location: flow.php?step=checkout&error=addres_err \n"); exit;
        }

        if(12 == $_POST['shipping'] && $consignee['city'] != 321)
        {
            $_POST['shipping'] = 9;//非上海地区不能上门自提
        }

        //处理【购物车】表单提交数据   【默认支付方式配送方式为网银和普通快递】
        $_POST['how_oos']      = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
        $_POST['card_message'] = isset($_POST['card_message']) ? htmlspecialchars($_POST['card_message']) : '';
        $_POST['inv_type']     = !empty($_POST['inv_type']) ? htmlspecialchars($_POST['inv_type']) : '';
        $_POST['inv_payee']    = isset($_POST['inv_payee']) ? htmlspecialchars($_POST['inv_payee']) : '';
        $_POST['inv_content']  = isset($_POST['inv_content']) ? htmlspecialchars($_POST['inv_content']) : '';
        $_POST['postscript']   = isset($_POST['postscript']) ? htmlspecialchars($_POST['postscript']) : '';

        $order = array(
            'shipping_id'     => isset($_POST['shipping'])? intval($_POST['shipping']): 9,//配送方式（9：快递）
            'pay_id'          => isset($_POST['payment']) ? intval($_POST['payment']) : 4,//支付方式（4：支付宝）
            'pack_id'         => isset($_POST['pack']) ? intval($_POST['pack']) : 0,
            'card_id'         => isset($_POST['card']) ? intval($_POST['card']) : 0,
            'card_message'    => trim($_POST['card_message']),
            'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,//余额
            'integral'        => isset($_POST['integral']) ? intval($_POST['integral']) : 0,//积分
            'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,//红包id
            'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,
            'inv_type'        => $_POST['inv_type'],
            'inv_payee'       => trim($_POST['inv_payee']),
            'inv_content'     => $_POST['inv_content'],
            'postscript'      => trim($_POST['postscript']),
            'how_oos'         => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',
            'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
            'user_id'         => $_SESSION['user_id'],
            'add_time'        => gmtime(),
            'order_status'    => OS_UNCONFIRMED,
            'shipping_status' => SS_UNSHIPPED,
            'pay_status'      => PS_UNPAYED,
            'agency_id'       => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']))
        );

        //订单的扩展信息
        if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)
        {
            $order['extension_code'] = $_SESSION['extension_code'];
            $order['extension_id'] = $_SESSION['extension_id'];
        }
        else
        {
            $order['extension_code'] = '';
            $order['extension_id'] = 0;
        }

        //yi:卡支付不能给用户开发票
        if($order['pay_id']>800 && $order['pay_id']<821 && !empty($order['inv_payee']))
        {
            $order['inv_payee'] = '';
        }

        //检查用户积分余额是否合法
        $user_id = $_SESSION['user_id'];
        if( isset($user_id) && !empty($user_id))
        {
            $smarty->assign('user_id', $user_id);
        }


        if($user_id > 0)
        {
            $user_info = user_info($user_id);

            $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
            if($order['surplus'] < 0)
            {
                $order['surplus'] = 0;
            }

            //查询用户有多少积分
            $flow_points = flow_available_points();  // 该订单允许使用的积分
            $user_points = $user_info['pay_points']; // 用户的积分总数
            $order['integral'] = min($order['integral'], $user_points, $flow_points);
            if($order['integral'] < 0)
            {
                $order['integral'] = 0;
            }
        }
        else
        {
            $order['surplus']  = 0;
            $order['integral'] = 0;
        }

        //检查红包是否存在
        if($order['bonus_id'] > 0)
        {
            $bonus = bonus_info($order['bonus_id']);

            if(empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))
            {
                $order['bonus_id'] = 0;
            }

            //yi:如果用户不是使用的交行的支付方式, 排查这些支付方式
            $limit_pay_id = $GLOBALS['db']->getOne("select IFNULl(b.pay_id, 0) as limit_pay_id from ecs_user_bonus as ub left join ecs_bonus_type as b on ub.bonus_type_id=b.type_id where ub.bonus_id=".$order['bonus_id']." and b.limit_pay=1 limit 1;");
            if(!empty($limit_pay_id) && !empty($order['pay_id']))
            {
                if($order['pay_id']!=$limit_pay_id)
                {
                    $order['bonus_id'] = 0;
                }
            }
        }
        elseif(isset($_POST['bonus_sn']))
        {
            $bonus_sn = trim($_POST['bonus_sn']);
            $bonus = bonus_info(0, $bonus_sn);
            $now = gmtime();
            if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type) || $now > $bonus['use_end_date'])
            {

            }
            else
            {
                //检查线下红包是否在这个范围之内
                if(bonus_sn_validate($bonus_sn))
                {
                    if($user_id > 0)
                    {
                        $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                        $db->query($sql);
                    }
                    $order['bonus_id'] = $bonus['bonus_id'];
                    $order['bonus_sn'] = $bonus_sn;
                }
                else
                {
                    $order['bonus_id'] = 0;
                    $order['bonus_sn'] = 0;
                }
            }

            if (in_array($bonus['type_id'], array(818, 819, 822, 823, 903, 904, 922, 923, 924, 925, 996, 997, 1031, 1102, 1107, 1236, 1355)) && $bonus['order_id'] <= 0)
            {
                $order['bonus_id'] = $bonus['bonus_id']; //临时处理0元红包送赠品
            }
        }

        //购物车中商品数组
        $cart_goods = cart_goods($flow_type);
        if(empty($cart_goods))
        {
            show_message_wap($_LANG['no_goods_in_cart'], 'flow.php', 'flow.php', 'warning');
        }

        //过滤收货人信息（添加反斜杠）
        foreach($consignee as $key => $value)
        {
            $order[$key] = addslashes($value);
        }

        //订单中的总金额计算
        $total = order_fee($order, $cart_goods, $consignee);

        $order['bonus']        = $total['bonus'];
        $order['goods_amount'] = $total['goods_price'];
        $order['discount']     = $total['discount'];
        $order['surplus']      = $total['surplus'];
        $order['tax']          = $total['tax'];

        //yi:购物车中的商品能享受红包支付的总额
        $discount_amout = compute_discount_amount();

        //红包和积分最多能支付的金额为商品总额
        $temp_amout = $order['goods_amount'] - $discount_amout;
        if ($temp_amout <= 0)
        {
            $order['bonus_id'] = 0;
        }
        $order['pay_id']      = empty($order['pay_id'])? 4: $order['pay_id'];
        $order['shipping_id'] = empty($order['shipping_id'])? 9: $order['shipping_id'];

        //配送方式
        if ($order['shipping_id'] > 0)
        {
            $shipping = shipping_info($order['shipping_id']);
            $order['shipping_name'] = addslashes($shipping['shipping_name']);
        }
        $order['shipping_fee'] = $total['shipping_fee'];
        $order['insure_fee']   = $total['shipping_insure'];

        //支付方式
        if ($order['pay_id'] > 0)
        {
            $payment = payment_info($order['pay_id']);
            if(intval($order['pay_id'])<100){
                $order['pay_name'] = addslashes($payment['pay_name']);
            }else{
                $order['pay_name'] = isset($_POST['bank_name'])?trim($_POST['bank_name']): "直接网银";
            }
        }
        $order['pay_fee'] = $total['pay_fee'];
        $order['cod_fee'] = $total['cod_fee'];

        //yi:数据检查之，货到付款运费永远不为0。
        if(3==$order['pay_id'] && 0==$order['shipping_fee'])
        {
            if(in_array($consignee['province'], array(16,25,31)))
            {
                $order['shipping_fee'] = 10.00;
            }
            else if(in_array($consignee['province'], array(2,3,4,6,7,10,11,13,14,17,22,23,24,27)))
            {
                $order['shipping_fee'] = 18.00;
            }
            else if(in_array($consignee['province'], array(8,9,12,15,18,26,30,32)))
            {
                $order['shipping_fee'] = 20.00;
            }
            else if(in_array($consignee['province'], array(5,19,20,21,28,29)))
            {
                $order['shipping_fee'] = 25.00;
            }
            else
            {
                $order['shipping_fee'] = 25.00;
            }
            $order['order_amount'] += $order['shipping_fee'];
        }

        //商品包装
        if ($order['pack_id'] > 0)
        {
            $pack               = pack_info($order['pack_id']);
            $order['pack_name'] = addslashes($pack['pack_name']);
        }
        $order['pack_fee'] = $total['pack_fee'];

        //祝福贺卡
        if ($order['card_id'] > 0)
        {
            $card               = card_info($order['card_id']);
            $order['card_name'] = addslashes($card['card_name']);
        }
        $order['card_fee']      = $total['card_fee'];

        $order['order_amount']  = number_format($total['amount'], 2, '.', '');

        //如果全部使用余额支付，检查余额是否足够
        if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)
        {
            if($order['surplus'] >0) //余额支付里如果输入了一个金额
            {
                $order['order_amount'] = $order['order_amount'] + $order['surplus'];
                $order['surplus'] = 0;
            }
            if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
            {
                show_message_wap($_LANG['balance_not_enough']);
            }
            else
            {
                $order['surplus'] = $order['order_amount'];
                $order['order_amount'] = 0;
            }
        }

        //如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款
        if ($order['order_amount'] <= 0)
        {
            $order['order_status'] = OS_CONFIRMED;
            $order['confirm_time'] = gmtime();
            $order['pay_status']   = PS_PAYED;
            $order['pay_time']     = gmtime();
            $order['order_amount'] = 0;
        }

        $order['integral_money']   = $total['integral_money'];
        $order['integral']         = $total['integral'];
        $order['integral_money']   = 0;

        if($order['extension_code'] == 'exchange_goods')
        {
            $order['integral_money']   = 0;
            $order['integral']         = $total['exchange_integral'];
        }

        $order['from_ad']          = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
        $order['referer']          = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

        //记录扩展信息
        if ($flow_type != CART_GENERAL_GOODS)
        {
            $order['extension_code'] = $_SESSION['extension_code'];
            $order['extension_id']   = $_SESSION['extension_id'];
        }

        //-----------------------------------推荐订单分成模块-----------------------------------//
        $affiliate = unserialize($_CFG['affiliate']);
        if(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1)
        {
            //推荐订单分成
            $parent_id = get_affiliate();
            if($user_id == $parent_id)
            {
                $parent_id = 0;
            }
        }
        elseif(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0)
        {
            //推荐注册分成
            $parent_id = 0;
        }
        else
        {
            //分成功能关闭
            $parent_id = 0;
        }
        //-----------------------------------推荐订单分成模块end-----------------------------------//

        //yi:把积分兑换商品应该扣除的积分加入到订单积分中
        $exchange_integral = order_exchange_goods_integral($order['user_id']);
        $order['integral'] = $order['integral'] + $exchange_integral;
        $order['parent_id']= $parent_id;

        //插入订单表
        $error_no = 0;
        do{
            $order['order_sn'] = get_order_sn(); //获取新订单编号
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

            $error_no = $GLOBALS['db']->errno();
            if($error_no > 0 && $error_no != 1062)
            {
                die($GLOBALS['db']->errorMsg());
            }
        }while($error_no == 1062); //如果是订单号重复则重新提交数据

        $new_order_id      = $db->insert_id();//刚插入的订单id
        $order['order_id'] = $new_order_id;

        //如果有现金折扣优惠活动:2013-10-31
        if ($total['discount'] && $total['favourable_name'])
        {
            $db->query("INSERT INTO ".$ecs->table('order_discount')." (order_id, favourable_name) VALUES (".$new_order_id.", '".serialize($total['favourable_name'])."')");
        }

        //购物车中全部商品插入订单商品表
        //xyz edit(20130110) 保存购物车信息
        if ($_SESSION['user_id'] > 0) {
            $sql =  "INSERT INTO ".$ecs->table('order_goods').
                "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
                " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = '$flow_type'";
        } else {
            if (isset($_COOKIE['cart_session_id'])) {
                $sql =  "INSERT INTO ".$ecs->table('order_goods').
                "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
                " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '$flow_type'";
            } else {
                $sql =  "INSERT INTO ".$ecs->table('order_goods').
                "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
                " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
            }
        }

        $db->query($sql);

        //====================更新会员账号信息（积分，余额，红包）【功能】====================//
        if($order['user_id'] > 0 && $order['surplus'] > 0)
        {
            //余额
            log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
        }

        if($order['user_id'] > 0 && $order['integral'] > 0)
        {
            //积分
            $exchange_lang = ($exchange_integral>0)? '积分兑换订单：'.$order['order_sn'].'中商品扣除'.$order['integral'].'积分': sprintf($_LANG['pay_order'], $order['order_sn']);
            log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), $exchange_lang);
        }

        if($order['bonus_id'] > 0 && $temp_amout > 0)
        {
            //红包
            use_bonus($order['bonus_id'], $new_order_id);
        }


        //-----------------------------------如果订单金额为0 处理虚拟卡-----------------------------------//
        if($order['order_amount'] <= 0)
        {
            $sql = "SELECT goods_id, goods_name, goods_number AS num FROM ".$GLOBALS['ecs']->table('cart') .
                   " WHERE is_real = 0 AND extension_code = 'virtual_card' AND session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
            $res = $GLOBALS['db']->getAll($sql);
            if(!empty($res))
            {
                $virtual_goods = array();
                foreach($res AS $row)
                {
                    $virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
                }

                if($virtual_goods AND $flow_type != CART_GROUP_BUY_GOODS)
                {
                    //虚拟卡发货
                    if (virtual_goods_ship($virtual_goods,$msg, $order['order_sn'], true))
                    {
                        $sql =  "SELECT COUNT(*) FROM " . $ecs->table('order_goods') .
                                " WHERE order_id = '$order[order_id]' " .
                                " AND is_real = 1";
                        if($db->getOne($sql) <= 0)
                        {
                            //修改订单状态
                            update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

                            //如果订单用户不为空，计算积分，并发给用户；发红包 .
                            if($order['user_id'] > 0)
                            {
                                //取得用户信息
                                $user = user_info($order['user_id']);

                                //计算并发放积分
                                $integral = integral_to_give($order);
                                log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

                                //发放红包
                                send_order_bonus($order['order_id']);
                            }
                        }
                    }
                }
            }
        }

        //清空购物车
        clear_cart($flow_type);
        $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);//插入支付日志
        clear_all_files();//清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少

        //-----------------------------------取得支付信息，生成支付代码，页面显示出来-----------------------------------//
        if($order['order_amount'] > 0)
        {
            $order['bank_id'] = 0;

            //如果pay_id是3位数 则是网银直接支付【pay_id支付代码】
            if(intval($order['pay_id'])>100 && intval($order['pay_id'])<800)
            {
                $bank_id            = intval($order['pay_id']);
                $order['bank_id']   = $bank_id;
                $order['pay_id']    = 10;   //快钱网银：pay_id=10;
                $order['bank_name'] = isset($_POST['bank_name'])? trim($_POST['bank_name']):"";
            }
            elseif(intval($order['pay_id']) == 901) //得仕通支付
            {
                $bank_id            = intval($order['pay_id']);
                $order['bank_id']   = $bank_id;
                $order['pay_id']    = 14;   //得仕通：pay_id=14;
                $order['bank_name'] = isset($_POST['bank_name'])? trim($_POST['bank_name']):"";
            }
            elseif(intval($order['pay_id'])>800)//预付费卡支付
            {
                $bank_id            = intval($order['pay_id']);
                $order['bank_id']   = $bank_id;
                $order['pay_id']    = 13;   //预付费卡：pay_id=13;
                $order['bank_name'] = isset($_POST['bank_name'])? trim($_POST['bank_name']):"";
            }

            $payment = payment_info($order['pay_id']);
            if(!empty($payment['pay_code']))
            {
                include_once('includes/modules/payment/'.$payment['pay_code'].'.php');
            }
            else
            {
                $payment = payment_info(10);
                include_once('includes/modules/payment/'.$payment['pay_code'].'.php');
            }

            //交行支付满99免邮(免首重)
            if ($order['pay_id'] == 12 && $bocomm_is_free_shipping && $order['shipping_fee'] > 0)
            {
                $total['amount_formated'] = price_format($order['order_amount']-$total['shipping_base_fee'], false); //应支付金额(格式化)
                $order['order_amount'] = $order['order_amount'] - $total['shipping_base_fee']; //应支付金额
                $j_shipping_fee = $order['shipping_fee'] - $total['shipping_base_fee']; //免首重后运费
                $order['shipping_fee'] = $j_shipping_fee;

                //更改订单中的邮费为0,订单总金额减首种运费
                $GLOBALS['db']->query("update ".$GLOBALS['ecs']->table('order_info')." set shipping_fee='".$j_shipping_fee."', order_amount='".$order['order_amount']."' where order_id=".$order['order_id']);
            }
            //------- 交行免邮 END --------------

            //------- 招行支付免邮 ---------
            if ($order['pay_id'] == 15 && $cmb_free_shipping && $order['shipping_fee'] > 0)
            {
                $total['amount_formated'] = price_format($order['order_amount']-$total['shipping_base_fee'], false); //应支付金额(格式化)
                $order['order_amount'] = $order['order_amount'] - $total['shipping_base_fee']; //应支付金额
                $j_shipping_fee = $order['shipping_fee'] - $total['shipping_base_fee']; //免首重后运费
                $order['shipping_fee'] = $j_shipping_fee;

                //更改订单中的邮费为0,订单总金额减首种运费
                $GLOBALS['db']->query("update ".$GLOBALS['ecs']->table('order_info')." set shipping_fee='".$j_shipping_fee."', order_amount='".$order['order_amount']."' where order_id=".$order['order_id']);
            }
            //------- 招行支付免邮 END ---------

            //------- 交行支付框架满减 ----------
            if ($bocomm_201405)
            {
                //更改订单中应支付金额和折扣
                $order['order_amount'] = $order['order_amount'] - 50; //应支付金额
                $total['amount_formated'] = price_format($order['order_amount'], false); //应支付金额(格式化),显示在前台
                $GLOBALS['db']->query("update ".$GLOBALS['ecs']->table('order_info')." SET order_amount='".$order['order_amount']."', discount=50 where order_id=".$order['order_id']);
            }
            //------- 交行支付框架满减 ----------

            //--------- 亿起发领赠品 12元运费 ---------
            if ($yiqifa_12)
            {
                $total['amount_formated'] = price_format($order['order_amount']-$total['shipping_base_fee']+12, false); //应支付金额(格式化)
                $order['order_amount'] = $order['order_amount'] - $total['shipping_base_fee'] + 12; //应支付金额
                $j_shipping_fee = $order['shipping_fee'] - $total['shipping_base_fee'] + 12; //免首重后运费
                $order['shipping_fee'] = $j_shipping_fee;

                //更改订单中的邮费为12,订单总金额减首种运费
                $GLOBALS['db']->query("update ".$GLOBALS['ecs']->table('order_info')." set shipping_fee='".$j_shipping_fee."', order_amount='".$order['order_amount']."' where order_id=".$order['order_id']);
            }
            //--------- 亿起发领赠品 12元运费 ---------


            $pay_obj    = new $payment['pay_code'];
            $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));

            $order['pay_desc'] = $payment['pay_desc'];
            //支付代码按钮 写到前端
            $smarty->assign('pay_online', $pay_online);
        }
        if(!empty($order['shipping_name']))
        {
            $order['shipping_name']=trim(stripcslashes($order['shipping_name']));
        }

        /*-----------------------------------订单信息(前台)-----------------------------------*/
        $smarty->assign('order',      $order);
        $smarty->assign('total',      $total);
        $smarty->assign('goods_list', $cart_goods); //购物车中的商品列表
        $smarty->assign('order_submit_back', sprintf($_LANG['order_submit_back'], $_LANG['back_home'], $_LANG['goto_user_center'])); //返回提示


        //===========================================================cps接口 订单推送============================================================================//

        /*==============获取返利网接口cookie数据=======================================*/
        $fanli_uid     = isset($_COOKIE['fanli_uid'])? $_COOKIE['fanli_uid']:'';
        $channelid     = isset($_COOKIE['channelid'])? $_COOKIE['channelid']:'';
        $fanli_uname   = isset($_COOKIE['ECS']['username'])? $_COOKIE['ECS']['username']:'';
        $fanli_ununion = isset($_COOKIE['fanli_ununion'])? $_COOKIE['fanli_ununion']:''; //"true"表示非联合登录
        /*==============获取cookie数据  end=============================================*/

        //对全局条件的返利的判断（比如说使用了CT优惠券的就不给其它返利）
        $no_whole_cps = true;
        if(!empty($order['bonus_id']) && $order['bonus']>0 && bonus_come($order['bonus_id'], 141))
        {
            $no_whole_cps = false;
        }

        /*=====================================================================linktech cps接口===================================================================*/
        //elseif(isset($_COOKIE['LTINFO']) && !empty($_COOKIE['LTINFO']))
        if(isset($_COOKIE['LTINFO']) && !empty($_COOKIE['LTINFO']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_linktech';
            }else{
                $cps_ref = 'wap_linktech';
            }
            if(!empty($order))
            {
                update_cps_from($cps_ref, $_COOKIE['LTINFO'], $order['order_id'], $order['order_sn']);
            }
            $lt_a_id   = trim($_COOKIE['LTINFO']);
            $lt_m_id   = "easeeyes";
            $lt_mbr_id = "user_".$order['user_id'];
            $lt_o_cd   = trim($order['order_sn']);
            $lt_p_cd   = '';//商品编号
            $lt_it_cnt = '';//商品数量
            $lt_price  = '';//商品单价
            $lt_c_cd   = '';//商品分类
            if(!$no_whole_cps){$lt_a_id = 'A100126293';}//CT来源订单 固定a_id.

            //订单红包金额：$order['bonus'], 订单应付金额：$order['order_amount'], 订单产品总金额：$order['goods_amount'], 购物车商品：$cart_goods。
            $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
            $lt_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;//商品价格比例

            //遍历该订单中商品，生成接口数据
            foreach($cart_goods as $k => $v)
            {
                $goods_id   = $cart_goods[$k]['goods_id'];
                $lt_price_bi= floor($cart_goods[$k]['goods_price']*$lt_bili);
                $lt_p_cd   .= "||goods".$goods_id;
                $lt_it_cnt .= "||".$cart_goods[$k]['goods_number'];
                $lt_price  .= "||".$lt_price_bi;

                //c_cd(单独计算礼包产品c_cd)
                if($cart_goods[$k]['extension_code']=='package_buy')
                {
                    if($cart_goods[$k]['goods_sn']==1 && $cart_goods[$k]['market_price']>0)
                    {
                        $lt_c_cd .= "||".goods_cat_cd2($goods_id, true);
                    }
                    else
                    {
                        continue;
                    }
                }
                elseif ( ! empty($cart_goods[$k]['extension_code']))
                {
                    $lt_c_cd .= "||".goods_cat_cd2($goods_id, true);
                }
                else
                {
                    $lt_c_cd .= "||".goods_cat_cd2($goods_id);
                }
            }

            if(count($cart_goods)>0)
            {
                $lt_p_cd   = substr($lt_p_cd,   2);
                $lt_it_cnt = substr($lt_it_cnt, 2);
                $lt_price  = substr($lt_price,  2);
                $lt_c_cd   = substr($lt_c_cd,   2);
            }

            //生成URL，并发送数据给linktech服务器
            if(!empty($lt_p_cd) && !empty($lt_it_cnt) && !empty($lt_c_cd))
            {
                $lt_url = "http://service.linktech.cn/purchase_cps.php?a_id=".$lt_a_id.
                          "&m_id=".$lt_m_id."&mbr_id=".$lt_mbr_id."&o_cd=".$lt_o_cd."&p_cd=".$lt_p_cd.
                          "&price=".$lt_price."&it_cnt=".$lt_it_cnt."&c_cd=".$lt_c_cd;
                if(from_qq_login($order['user_id']) && $no_whole_cps)
                {
                    $open_id = $GLOBALS['db']->getOne("select refer_id from ecs_users where user_id=".$order['user_id']." limit 1;");
                    $lt_url .= "&mbr_name=A100136514".$open_id;
                }
                $smarty->assign('fanli_src',  $lt_url);//统一返利接口
            }
        }
        elseif(isset($_COOKIE['zhitui_info']) && !empty($_COOKIE['zhitui_info']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_zhitui';
            }else{
                $cps_ref = 'wap_zhitui';
            }
            //yi:智推网cps接口
            if(!empty($order))
            {
                update_cps_from($cps_ref, $_COOKIE['zhitui_info'], $order['order_id'], $order['order_sn']);
            }
            $zhitui_info = trim($_COOKIE['zhitui_info']);
            $ar_zhitui   = explode('|_|', $zhitui_info);

            //处理红包等数据
            $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
            $jg_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;//商品价格比例

            if(!empty($ar_zhitui))
            {
                $zhitui_a_id	= intval($ar_zhitui[1]);
                $zhitui_subid	= trim($ar_zhitui[2]);
                $zhitui_o_cd	= trim($order['order_sn']);
                $zhitui_o_date	= date('YmdHis', time()+3600*8);
                $zhitui_status	= 0;
                $zt_goods_id	= '';
                $zt_price		= '';
                $zt_it_cnt		= '';
                $zt_rate		= 0;
                $zt_rate_memo	= '';

                //订单商品
                foreach($cart_goods as $k => $v)
                {
                    $tejia				= ($v['is_cx']==1 || $v['extension_code']=='package_buy')? true: false;
                    $zt_c_cd			= goods_cat_cd($v['goods_id'], $tejia);
                    $zt_c_bili			= goods_cat_cd_bili($zt_c_cd);
                    $v['goods_price']	= round($v['goods_price']*$jg_bili, 2);

                    $zt_goods_id .= empty($zt_goods_id)? 'goods'.$v['goods_id']: "||goods".$v['goods_id'];
                    $zt_price    .= empty($zt_price)? floor($v['goods_price']):  "||".floor($v['goods_price']);
                    $zt_it_cnt   .= empty($zt_it_cnt)? intval($v['goods_number']): "||".intval($v['goods_number']);
                    $zt_rate     .= empty($zt_rate)?    $zt_c_bili: "||".$zt_c_bili;
                    $zt_rate_memo.= empty($zt_rate_memo)? $zt_c_cd: "||".$zt_c_cd;
                }

                if(!empty($zt_goods_id) && !empty($zt_price) && !empty($zt_it_cnt))
                {
                    $zt_url = 'http://api.zhitui.com/recive.php?a_id='.$zhitui_a_id.'&subid='.$zhitui_subid.'&o_cd='.$zhitui_o_cd.'&p_cd='.$zt_goods_id.'&price='.$zt_price.'&it_cnt='.
                              $zt_it_cnt.'&o_date='.$zhitui_o_date.'&rate='.$zt_rate.'&rate_memo='.$zt_rate_memo.'&status='.$zhitui_status.'&note=';
                    $smarty->assign('fanli_src',  $zt_url);//统一返利接口
                }
            }
        }
        elseif(isset($_COOKIE['lergao_info']) && !empty($_COOKIE['lergao_info']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_lergao';
            }else{
                $cps_ref = 'wap_lergao';
            }
            //yi:乐告网cps接口
            if(!empty($order))
            {
                update_cps_from($cps_ref, $_COOKIE['lergao_info'], $order['order_id'], $order['order_sn']);
            }
            $lergao_info = trim($_COOKIE['lergao_info']);


            $ar_lergao = explode('|_|', $lergao_info);

            //处理红包等数据
            $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
            $jg_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;//商品价格比例

            if(!empty($ar_lergao))
            {
                $lergao_a_id	= intval($ar_lergao[1]);
                $lergao_subid	= trim($ar_lergao[2]);
                $lergao_o_cd	= trim($order['order_sn']);
                $lergao_o_date	= date('YmdHis', time()+8*3600);
                $lergao_status	= 0;
                $lg_goods_id	= '';
                $lg_price		= '';
                $lg_it_cnt		= '';
                $lg_rate		= 0;
                $lg_rate_memo	= '';

                //订单商品
                foreach($cart_goods as $k => $v)
                {
                    $tejia        = ($v['is_cx']==1 || $v['extension_code']=='package_buy')? true: false;
                    $lg_c_cd	  = goods_cat_cd($v['goods_id'], $tejia);
                    $lg_c_bili    = goods_cat_cd_bili($lg_c_cd);
                    $v['goods_price'] = round($v['goods_price']*$jg_bili, 2);

                    $lg_goods_id .= empty($lg_goods_id)? 'goods'.$v['goods_id']: "||goods".$v['goods_id'];
                    $lg_price    .= empty($lg_price)? floor($v['goods_price']):  "||".floor($v['goods_price']);
                    $lg_it_cnt   .= empty($lg_it_cnt)? intval($v['goods_number']): "||".intval($v['goods_number']);
                    $lg_rate     .= empty($lg_rate)?    $lg_c_bili: "||".$lg_c_bili;
                    $lg_rate_memo.= empty($lg_rate_memo)? $lg_c_cd: "||".$lg_c_cd;
                }

                if(!empty($lg_goods_id) && !empty($lg_price) && !empty($lg_it_cnt))
                {
                    //示例：/public.aspx?v_id=taobao&w_id=00000&u_id=123&o_cd=123123123&o_s=new&o_d=20081013121312&p_cd=P123||P124&p_p=45.00||30.00&p_cnt=1||1&p_ctg=122||122
                    $lg_url = 'http://dc.lergao.com/sync/vendor/public.aspx?v_id=easeeyes&w_id='.$lergao_subid.'&u_id=&o_cd='.$lergao_o_cd.'&o_s=new&o_d='.$lergao_o_date
                              .'&p_cd='.$lg_goods_id.'&p_p='.$lg_price.'&p_cnt='.$lg_it_cnt.'&p_ctg='.$lg_rate_memo;
                    $smarty->assign('fanli_src',  $lg_url);//统一返利接口
                }
            }
        }
        elseif(isset($_COOKIE['cpsinfo_360']))
        {
            /*=====================================================================360返利接口 start ======================================================*/
            require_once ROOT_PATH.'cps/360cps/core/CPS360_api.class.php';
            CPS360_api::order_save($order['order_sn'], $data = array());//360_cps_api
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_360';
            }else{
                $cps_ref = 'wap_360';
            }
            if(!empty($order)) {
                update_cps_from($cps_ref, '', $order['order_id'], $order['order_sn']);
            }
        }
        elseif (isset($_COOKIE['cpsinfo_duomai']) && isset($_COOKIE['cpsinfo_duomai_siteid']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_duomai';
            }else{
                $cps_ref = 'wap_duomai';
            }
            //多麦订单推送接口 注：取消了推送
            if(!empty($order)){
                update_cps_from($cps_ref, $_COOKIE['cpsinfo_duomai_siteid'], $order['order_id'], $order['order_sn']);
            }
        }
        elseif (isset($_COOKIE['cpsinfo_xunlei']) && isset($_COOKIE['xunlei_cps_login_user']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_xunlei';
            }else{
                $cps_ref = 'wap_xunlei';
            }
            if(!empty($order)) {
                update_cps_from($cps_ref, $_COOKIE['xunlei_cps_login_user'], $order['order_id'], $order['order_sn']);
            }

            //迅雷订单推送
            require_once(dirname(__FILE__) . '/cps/xunlei/post_order.class.php');
            $t = new post_order();
            $post_data = $t->get_order_info($order['order_id'], 1, $order['add_time']);

            //$post_url = "http://test.jifen.xunlei.com/call?c=owner&a=gateway"; //测试地址
            $post_url = "http://jifen.xunlei.com/call?c=owner&a=gateway"; //正式地址
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $post_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HEADER, 0);

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            $data = curl_exec($curl);
            curl_close($curl);
        }
        elseif (isset($_COOKIE['cpsinfo_yiqifa_src']) && isset($_COOKIE['cpsinfo_yiqifa_wi']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_yiqifa';
            }else{
                $cps_ref = 'wap_yiqifa';
            }
            //亿起发订单推送
            if(!empty($order)) {
                $cid_wi = $_COOKIE['cpsinfo_yiqifa_cid'] . ',' . $_COOKIE['cpsinfo_yiqifa_wi'];
                update_cps_from($cps_ref, $cid_wi, $order['order_id'], $order['order_sn']);
            }

            require_once(dirname(__FILE__) . '/cps/yiqifa/post_order.class.php');
            $yqf = new post_order();
            $yqf_url = $yqf->get_order_info($order['order_id']);//获取订单信息

            $cpsinfo_yiqifa_cid = isset($_COOKIE['cpsinfo_yiqifa_cid']) ? $_COOKIE['cpsinfo_yiqifa_cid'] : '';
            $cpsinfo_yiqifa_wi = isset($_COOKIE['cpsinfo_yiqifa_wi']) ? $_COOKIE['cpsinfo_yiqifa_wi'] : '';

            //$yiqifa_url = "http://o.yiqifa.com/servlet/handleCpsIn?cid=".$cpsinfo_yiqifa_cid."&wi=".$cpsinfo_yiqifa_wi."&on=".$order['order_sn'].$yqf_url;//2013.05.31要求更改接口地址
            $yiqifa_url = "http://o.yiqifa.com/servlet/handleCpsInterIn?interId=5191fd5fe03bbcaa579e8b03&cid=".$cpsinfo_yiqifa_cid."&wi=".$cpsinfo_yiqifa_wi."&on=".$order['order_sn'].$yqf_url;
            $smarty->assign('fanli_src',  $yiqifa_url);
        }
        elseif (isset($_COOKIE['AELINFO']))
        {
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_17elink';
            }else{
                $cps_ref = 'wap_17elink';
            }
            //17elink订单
            update_cps_from($cps_ref, $_COOKIE['AELINFO'], $order['order_id'], $order['order_sn']);

            require_once(dirname(__FILE__) . '/cps/17elink/post_order.class.php');
            $elink = new post_order();
            $elink_src = $elink->get_order_info($order['order_id']);
            $smarty->assign('elink_src',  $elink_src);
        }


        elseif(!$_COOKIE['LTINFO'] && from_qq_login($order['user_id']) && $no_whole_cps)
        {
            /* ----------------------------------------------------------------------------------------------------------------------
             * 返利规则：只要是qq联合登录的用户都要返利。qq联合登录放在最后。
             * ----------------------------------------------------------------------------------------------------------------------
               a_id = A100136514$open_id     A100136514固定值，后面必须加上腾讯传送的openid值
               c_cd = qq_login               固定值，必须加上
             */
            $open_id   = $GLOBALS['db']->getOne("select refer_id from ecs_users where user_id=".$order['user_id']." limit 1;");
            $a_id      = 'A100136514'.$open_id;
            $m_id      = "easeeyes";
            $o_cd      = trim($order['order_sn']);
            $lt_p_cd   = '';
            $lt_it_cnt = '';
            $lt_price  = '';
            $lt_c_cd   = '';
            // 判断是不是微信浏览器
            if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
                $cps_ref = 'wap_weixin_qq_union_login';
            }else{
                $cps_ref = 'wap_qq_union_login';
            }
            if(!empty($order))
            {
                update_cps_from($cps_ref, $a_id, $order['order_id'], $order['order_sn']);
            }

            //yi:不管是红包，还是积分，全部的情况都处理了。$cart_goods购物车商品。 $order订单信息。
            $bili_temp = !empty($order['goods_amount'])? $order['order_amount']/$order['goods_amount']: 1;
            $lt_bili   = (1>$bili_temp && $bili_temp>=0)? round($bili_temp, 3): 1;

            //遍历该订单中商品，生成接口数据
            foreach($cart_goods as $k => $v)
            {
                $goods_id   = $cart_goods[$k]['goods_id'];
                $lt_price_bi= floor($cart_goods[$k]['goods_price']*$lt_bili);

                $lt_p_cd   .= "||goods".$goods_id;			        //商品编号
                $lt_it_cnt .= "||".$cart_goods[$k]['goods_number']; //商品数量
                $lt_price  .= "||".$lt_price_bi;                    //商品单价
                $lt_c_cd   .= "||qq_login";                         //c_cd
            }
            if(count($cart_goods)>0)
            {
                $lt_p_cd   = substr($lt_p_cd,   2);
                $lt_it_cnt = substr($lt_it_cnt, 2);
                $lt_price  = substr($lt_price,  2);
                $lt_c_cd   = substr($lt_c_cd,   2);
            }
            $lt_url = "http://service.linktech.cn/purchase_cps.php?a_id=".$a_id."&m_id=".$m_id."&o_cd=".$o_cd."&p_cd=".$lt_p_cd."&it_cnt=".$lt_it_cnt."&price=".$lt_price."&c_cd=".$lt_c_cd;
            $smarty->assign('fanli_src',  $lt_url);//统一返利接口
        }
        else
        {
            //TODO:其它cps订单推送（后期）
        }
        //=====================================================================处理cps接口操作 end===================================================================//
        //统计来自外部活动或广告链接的订单数量和金额
        if (isset($_COOKIE['click_session_id'])) {
            if(!empty($order)) {
                $sql_at = "UPDATE ".$GLOBALS['ecs']->table('active_stat_new'). "SET order_num=order_num+1, order_money=order_money+".$order['order_amount'].", order_id=concat(order_id, ',".$order['order_id']."'), user_id=concat(user_id, ',".$_SESSION['user_id']."')  WHERE cookieid = '".$_COOKIE['click_session_id']."' AND access_time = '".$_COOKIE['click_time']."'";
                $GLOBALS['db']->query($sql_at);
            }
        }

            /*新增代码*/
            $_SESSION['bd']['order'] = $order;
            $_SESSION['bd']['total'] = $total;
            $_SESSION['bd']['goods_list'] = $cart_goods;
            $_SESSION['bd']['pay_online'] = $pay_online;
            ecs_header("location:flow.php?step=done&orderid=".$order['order_sn']);
    }else{
		/* 订单信息 */
		$smarty->assign('order',      $_SESSION['bd']['order']);
		$smarty->assign('total',      $_SESSION['bd']['total']);
		$smarty->assign('goods_list', $_SESSION['bd']['goods_list']);
		$smarty->assign('order_submit_back', sprintf($_LANG['order_submit_back'], $_LANG['back_home'], $_LANG['goto_user_center'])); // 返回提示
		$smarty->assign('pay_online', $_SESSION['bd']['pay_online']);
		user_uc_call('add_feed', array($_SESSION['bd']['order']['order_id'], BUY_GOODS)); //推送feed到uc
	
	}
	//-----------------------------------清除session中收货人信息-----------------------------------//
    unset($_SESSION['flow_consignee']); 
    unset($_SESSION['flow_order']);
    unset($_SESSION['direct_shopping']);
	unset($_SESSION['bd']);
}
// 删除购物车中的单个商品
elseif($_REQUEST['step'] == 'drop_goods')
{
    /*------------------------------------------------------ */
    //-- 删除购物车中的商品
    /*------------------------------------------------------ */
    $rec_id = intval($_GET['id']);
    flow_drop_cart_goods_wap($rec_id);
    ecs_header("Location: flow.php\n");
    exit;
}
elseif($_REQUEST['step'] == 'ajax_drop_goods')
{
	//删除商品
    $rec_id = intval($_GET['id']);
    flow_drop_cart_goods_wap($rec_id);
	//改变的部分内容重新输出到前端
	$cnum = insert_cart_num();
	$csum = insert_cart_sum();
	echo $cnum.",".$rec_id.",".$csum;
	exit;
}
elseif($_REQUEST['step'] == 'ajax_cart_money')
{
	//返回购物车金额    
	$csum = insert_cart_sum();
	echo $csum;
	exit;
}
elseif ($_REQUEST['step'] == 'select_shipping')
{
    //选择配送方式 改变配送方式【功能】

    include_once('includes/cls_json.php');

    $json   = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    $flow_type   = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    $consignee   = get_consignee($_SESSION['user_id']); //收货人信息
    $cart_goods  = cart_goods($flow_type);             //取得商品列表信息，计算合计

    //-------------------------------------------yi:修正购物车中没有商品提示------------------------------------||
    //check_consignee_info():验证收货人地址是否完全
    if(empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        if( empty($cart_goods) ){
            $result['error'] = $_LANG['no_goods_in_cart'];
        }else{
            //收货地址不全
        }
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();
        $order['shipping_id'] = intval($_GET['shipping']);

        $regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
        $shipping_info = shipping_area_info($order['shipping_id'], $regions);

        /*专享价获取自定义邮费对应checkout by:tao20151123*/
        $source  = $GLOBALS['db']->getOne("select extension_id from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' ;");
        if(!empty($source)){
            $postage = $GLOBALS['db']->getOne("SELECT postage FROM ".$GLOBALS['ecs']->table('source')." WHERE rec_id = ".$source." LIMIT 1");
            if($postage>0){
                $custom_fee = $postage;
            }else{
                $custom_fee = false;
            }
        }else{
            $custom_fee = false;
        }
        //订单中的总金额计算
        if($order['shipping_id'] == '8'){           // 货到付款不调用专享价邮费
            $total = order_fee($order, $cart_goods, $consignee);
        }else{
            if(by_tuan_in_cart() || by_source_in_cart($cart_goods)){//订单中包含包邮团购则包邮对应checkout
                $total = order_fee($order, $cart_goods, $consignee,true);
            }else{
                $total = order_fee($order, $cart_goods, $consignee,false,$custom_fee);
            }
        }
//var_dump($total);die;
        /* 计算订单的费用 - 旧版 */
        /*$total = order_fee($order, $cart_goods, $consignee);*/

        /**
         *秒杀商品里面设置了包邮， 所以所有的商品都是包邮性质
         */
        if(miaosha_free_ship())
        {
            $total['amount'] = $total['amount'] - $total['shipping_fee'];
            $total['shipping_fee']= 0;
            $total['amount_formated'] = price_format($total['amount'],false);
        }

        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }
        $result['cod_fee']     = $shipping_info['pay_fee'];

        if(strpos($result['cod_fee'], '%') === false)
        {
            $result['cod_fee'] = price_format($result['cod_fee'], false);
        }

        $result['need_insure'] = ($shipping_info['insure'] > 0 && !empty($order['need_insure'])) ? 1 : 0;

        //------------------------ajax更新订单费用统计---------------------------------||
        $result['content']     = $smarty->fetch('library/order_total.lbi');
    }
    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'change_bonus')
{
    /*------------------------------------------------------ */
    //-- 改变红包
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        if( empty($cart_goods) ){
            $result['error'] = $_LANG['no_goods_in_cart'];
        }else{
            //收货地址不全
        }
    }
    else
    {
        //取得购物流程设置
        $smarty->assign('config', $_CFG);

        //取得订单信息
        $order = flow_order_info();

        $bonus = bonus_info(intval($_GET['bonus']));

        if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0)
        {
            $order['bonus_id'] = $_GET['bonus'];
        }
        else
        {
            $order['bonus_id'] = 0;
            $result['error'] = $_LANG['invalid_bonus'];
        }

        //yi:如果红包有 限制支付方式
        if($bonus['limit_pay'] && !empty($bonus['pay_id']))
        {
            $user_pay_id = isset($_GET['pay_id'])? intval($_GET['pay_id']): 0;
            if(0 == $user_pay_id)
            {
                $order['bonus_id'] = 0;
                $result['error']   = "请先选择并保存好支付方式，再使用红包！";
            }
            else
            {
                if($user_pay_id != $bonus['pay_id'])
                {
                    $order['bonus_id'] = 0;
                    $limit_pay_name    = $GLOBALS['db']->getOne("select pay_name from ecs_payment where pay_id=".$bonus['pay_id']." limit 1;");
                    $result['error']   = "很抱歉，该红包仅限用【".$limit_pay_name."】的订单才能使用！";
                }
            }
        }

        //计算订单的费用
        $total = order_fee($order, $cart_goods, $consignee);

        /**
         *秒杀商品里面设置了包邮， 所以所有的商品都是包邮性质
         */
        if(miaosha_free_ship())
        {
            $total['amount'] = $total['amount'] - $total['shipping_fee'];
            $total['shipping_fee']= 0;
            $total['amount_formated'] = price_format($total['amount'],false);
        }


        $smarty->assign('total', $total);

        //团购
        if($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        if (in_array($bonus['type_id'], array(818, 819, 822, 823, 903, 904, 922, 923, 924, 925, 996, 997, 1031, 1102, 1107, 1236, 1355, 1620, 1634, 1635,1704,1762,1769,1823,1824,1869,1879,1950,1991,2063,2165,2176,2178,2299)))
        {
            $smarty->assign('special_bouns', 1); //标识实物红包
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    $json = new JSON();
    die($json->encode($result));
}
/* ----------------------------------------------------------------------------------------------------------------------
 * yi 优惠活动之：加钱赠品加入购物车
 * ----------------------------------------------------------------------------------------------------------------------
 * 1.购物车中加价购（包括护理液和眼镜）。
 *
 * 2.0元赠品手动加入购物车活动。
 *
 * 3.加价购这个活动可以添加多个商品，但是点击购买一次只能添加其中的一个商品到购物车。对数量的限制也是这样的。
 */
elseif($_REQUEST['step'] == 'yi_add_fav')
{
    $fav      = '';
    $rec_id   = '';
    $ds       = ''; //度数字符串

    $goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
    $price    = isset($_REQUEST['price'])   ? floatval($_REQUEST['price']): 0.00;
    $num      = isset($_REQUEST['num'])     ? intval($_REQUEST['num']): 1;
    $fa_id    = isset($_REQUEST['fa_id'])   ? intval($_REQUEST['fa_id']): 0;//备用
    $act_id   = isset($_REQUEST['act_id'])  ? intval($_REQUEST['act_id']):0;//活动ID

    $favourable = favourable_info($act_id);

    if($favourable['act_type'] == 3 && $act_id>0)//优惠活动方式：加价购。
    {
        //购物车中已加该优惠活动的赠品数。
        $fav_g_num  = $GLOBALS['db']->GetOne("select IFNULL(sum(goods_number), 0) from ecs_cart where session_id='".SESS_ID."' AND is_gift=".$act_id.";");
        $fav_mother = in_fav_number($act_id);   //母体商品个数
        $fav_g_must = 1;						//可添加的赠品数（非多买多送为1）

        if(1 == $favourable['is_duo'])
        {
            //$temp_f_div = ($favourable['buy_number']>0)? floor($fav_mother/$favourable['buy_number']): 0;
            //$fav_g_must = $temp_f_div * $favourable['gift_number'];

            //$fav_mother （购物车母体商品个数）
            //$favourable['buy_number'] （buy_number）
            //$favourable['gift_number'] (gift_number)
            //$fav_g_must （可添加的赠品数）
            //$fav_g_num  (已添加的赠品数)

            //if(($fav_mother <= $favourable['buy_number']) && ($fav_mother > $fav_g_num)){
            // zhang：160201   修改多买多赠逻辑
            if(($fav_mother >= $favourable['buy_number']) && ($fav_mother > $fav_g_num)){
                $fav_g_must = floor($fav_mother/$favourable['buy_number']);
            }else{
                $fav_g_must = floatval(0);
            }

        }

        if(intval($fav_g_num) >= intval($fav_g_must))
        {
            $goods_id = 0;//已经超过优惠商品数量
        }
        else
        {
            //特惠商品加入购物车（当中包括各种情况）
            if(!empty($favourable['gift']))
            {
                foreach($favourable['gift'] as $gift)
                {
                    //遍历特惠商品数组（允许一个优惠活动设置多个特惠商品，但是一次只能添加一个优惠品）
                    $ggid	 = !empty($gift['id'])? $gift['id']: 0;
                    if($goods_id == $ggid)
                    {
                        $gprice  = floatval($gift['price']); //赠品价格
                        $num	 = intval($gift['number']);  //赠品数量
                        $zselect = null;
                        $zcount  = 0;
                        $yselect = null;
                        $ycount  = 0;

                        //赠品是否有度数。
                        $eye_id = $GLOBALS['db']->GetOne("select eye_id from ecs_goods where goods_id='$ggid' limit 1;");
                        if($eye_id > 0)
                        {
                            if(1 == $num)
                            {
                                $ds      = $_REQUEST['ds'];
                                $zselect = $_REQUEST['ds'];
                                $zcount  = 1;
                                $yselect = null;
                                $ycount  = 0;
                            }
                            elseif(2 == $num)
                            {
                                $ds      = $_REQUEST['zselect'].','.$_REQUEST['yselect'];//眼镜度数字符串
                                $zselect = $_REQUEST['zselect'];
                                $zcount  = 1;
                                $yselect = $_REQUEST['yselect'];
                                $ycount  = 1;
                            }
                        }
                        if ($ggid == 1542) {
                            $zcount  = 1;
                            $zselect = $_REQUEST['ds']; //如果赠品是老花镜20140509
                        }
                        $rec_id = add_gift_to_cart2($act_id, $ggid, $gprice, $num, $zselect, $zcount, $yselect, $ycount);

                        break;
                    }
                }
            }
        }
    }

    //-------------------------------------------------返回添加到购物车的商品字符串到前端--------------------------------------------//
    $tnum       = cart_goods_total_num();			   //商品总数
    $cart_goods = get_cart_goods();                    //购物车商品
    $total_sum  = $cart_goods['total']['goods_price']; //购物车总金额
    $points     = $cart_goods['total']['goods_amount'];//获得积分
    $cart_weight= cart_goods_total_weight();           //商品总重
    $base_line  = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 68;
    $freepx     = ($cart_goods['total']['goods_pricex']>$base_line)? 0 :($base_line-$cart_goods['total']['goods_pricex']);//免运费句子

    //获取加入购物车中商品名字，图片
    $addg       = $GLOBALS['db']->getRow("select goods_name,goods_img from ecs_goods where goods_id=".$goods_id);
    $goods_name = $addg['goods_name'];
    $goods_img  = $addg['goods_img'];

    $fav = $goods_id.','.$goods_name.','.$goods_img.','.$price.','.$rec_id.','.$tnum.','.$total_sum.','.$points.','.$freepx.','.$act_id.','.$fa_id.','.$num.','.$ds.','.$cart_weight.','.$base_line;
    echo $fav;
    exit;
}

else
{
    $_SESSION['flow_type'] = CART_GENERAL_GOODS;//普通商品

    if($_CFG['one_step_buy'] == '1')
    {
        ecs_header("Location: flow.php?step=checkout\n");
        exit;
    }
    $user_id    = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
    $cart_goods = get_cart_goods();//购物车商品列表

    //tao 20141204 步骤一页面显示折后的价格
    $cart_price = $cart_goods['total']['goods_amount'];
    $discount_price = compute_discount();
    $discount_price = $discount_price['discount'];
    $cart_price = $cart_price-$discount_price;
    if(!empty($cart_price)&&$cart_price>0){
        $cart_goods['total']['goods_price'] = '￥'.sprintf("%.2f",$cart_price);
    }
    //tao 20141204 end

    $smarty->assign('goods_list',            $cart_goods['goods_list']);
    $smarty->assign('total',                 $cart_goods['total']);
    $smarty->assign('shopping_money',        sprintf($_LANG['shopping_money'], $cart_goods['total']['goods_price']));
    $smarty->assign('shopping_moneyn',       $cart_goods['total']['goods_price']);//商品金额总计
    $smarty->assign('user_id',               $user_id);
    $smarty->assign('shopping_integral',     $cart_goods['total']['goods_amount']);//购物后获得的总积分

    //yi:会员福利：不同会员，免邮额度不同
    $base_line = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 68;
    if(($cart_goods['total']['goods_pricex']-$discount)>0){
        $cart_goods['total']['goods_pricex'] = $cart_goods['total']['goods_pricex']-$discount;
    }
    if(($cart_goods['total']['goods_pricex']-$base_line)>0){
        $smarty->assign('goods_pricex',    0);
        $smarty->assign('base_line',       $base_line);
    }else{
        $smarty->assign('goods_pricex',    $base_line-$cart_goods['total']['goods_pricex']);
    }
    //yi:订单是否包邮功能，包邮提示语句【唯一】flow,add_to_cart 2个页面中引用。
    if(miaosha_free_ship())
    {
        $smarty->assign('goods_pricex', -1);
    }
    $smarty->assign('market_price_desc',   sprintf($_LANG['than_market_price'],$cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate']));


    //是否使用红包
    if((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1'))
    {
        $user_bonus = user_bonus($_SESSION['user_id'], $cart_goods['total']['goods_pricex']);
        if (!empty($user_bonus))
        {
            foreach ($user_bonus AS $key => $val)
            {
                $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
                $iii++;
            }
            $smarty->assign('bonus_list', $user_bonus);
            $smarty->assign('bonus_list_num', count($user_bonus));
        }
        $smarty->assign('allow_use_bonus', 1);
    }

    //-------------------------------------------------------------------【加钱赠品展示】-------------------------------------------------------------------------||
    //购物车中已经存在优惠活动商品。//可删
    /*
    $sql = "SELECT is_gift FROM ".$ecs->table('cart')." WHERE session_id='".SESS_ID."' and is_gift<>0 and extension_code <>'package_buy' and goods_price <> '0.00' and zselect<>'' limit 1";
    $hv_fav      = $GLOBALS['db']->GetOne($sql);
    $fav_can_add = empty($hv_fav)? 1: 0;
    $smarty->assign('fav_can_add', $fav_can_add);//购物车中已经存在则不能购买。
    $sql = "SELECT is_gift FROM ".$ecs->table('cart')." WHERE session_id='".SESS_ID."' and is_gift<>0 and extension_code<>'package_buy' and goods_price<>'0.00' and zselect='' limit 1";
    $hv_fav2 = $GLOBALS['db']->GetOne($sql);
    $fav_can_add2 = empty($hv_fav2)? 1: 0;
    $smarty->assign('fav_can_add2', $fav_can_add2);//没有度数的商品加入购物车数量限制。
    */
    //$in_fav = in_fav_number(64); //取得购物车中的能享受[86]的赠品的数量。实现[86]活动买2送2功能。

    //优惠活动之加价购。
    $now = $_SERVER['REQUEST_TIME'];
    $fav = $GLOBALS['db']->GetAll("select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' and act_type=3;");

    //filter
    foreach($fav as $k => $v)
    {
        $fav[$k]['gift'] = unserialize($v['gift']);
        $user_rank		 = yget_user_rank($user_id);

        if(!in_array($user_rank, explode(',', $v['user_rank'])))
        {
            unset($fav[$k]);
        }

        //if($v['is_duo'])
        //{
        $fav_number  = in_fav_number($v['act_id'], $v['act_range']);//母体有效商品数

        /*20150107tao注释
        if($fav_number < $v['buy_number'])
        {
            unset($fav[$k]);
        }
        */
        //}

        $cart_sump = get_cart_sump()-$discount;//加价购算优惠前价格 by：tao
        $fav_sum = ($v['act_range']>0)? in_fav_sum($v['act_id'], $v['act_range']): $cart_sump; //母体有效总金额
        $min     = $v['min_amount'];
        $max     = $v['max_amount'];
        $max     = ($max==0)? 999999: $max;

        if($fav_sum>=0 && $fav_sum>=$min && $fav_sum<=$max)
        {
            continue;
        }
        else
        {
            unset($fav[$k]);
        }
    }

    $all_gift = array();//加价购商品
    foreach($fav as $k => $v)
    {
        $gg = $v['gift'];
        foreach($gg as $b => $bv)
        {
            $goods_id  = intval($bv['id']);		//赠品ID
            $gift_numb = intval($bv['number']);	//赠品数量
            $g_good    = $GLOBALS['db']->GetRow("select goods_img,shop_price from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$goods_id." limit 1;");

            $gg[$b]['act_id']    = $v['act_id'];
            $gg[$b]['act_name']  = $v['act_name'];
            $gg[$b]['goods_img'] = $g_good['goods_img'];       //图片100x100
            $gg[$b]['goods_ds']  = get_goods_ds($goods_id);//商品度数
            if(2 == $gift_numb)
            {
                $gg[$b]['price']      = $bv['price']*2;
                $gg[$b]['shop_price'] = $g_good['shop_price']*2;
            }
            else
            {
                $gg[$b]['shop_price'] = $g_good['shop_price']*1;
            }
        }
        $fav[$k]['gift'] = $gg;
        $all_gift = array_merge($gg, $all_gift);
    }

    $sqlc = "SELECT count(*) FROM ".$ecs->table('cart')." WHERE session_id='".SESS_ID."' AND rec_type='".CART_GENERAL_GOODS."' and is_gift<>0 and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' and goods_price<>'0.00'";
    $cart_len		= $GLOBALS['db']->GetOne($sqlc);//购物车加价购赠品数
    $cart_fav_goods = count($all_gift);				//加价购商品数

    $smarty->assign('cart_fav_goods',    $cart_fav_goods);
    $smarty->assign('gift_len',          $cart_fav_goods);
    $smarty->assign('gift_list',         $all_gift);

    //购物车中总共能够获得多少商品
    $smarty->assign('cart_len',    $hv_fav);//购物车中活动范围内商品数量
    //-------------------------------------------------------------------【加钱赠品展示end】-------------------------------------------------------------------------||


    //优惠活动之：计算商品折扣
    $discount    = compute_discount();
    $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
    $smarty->assign('discount',      $discount['discount']);
    $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));
//var_dump($discount);
    //是否在购物车里显示商品图片，商品属性
    $smarty->assign('show_goods_thumb',     $GLOBALS['_CFG']['show_goods_in_cart']);
    $smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);

    //-----------------------------------------------购物车配件模块-----------------------------------------------//
    $sql = "SELECT goods_id FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type ='".CART_GENERAL_GOODS."' AND is_gift=0 AND parent_id=0 ";
    $parent_list = $GLOBALS['db']->getCol($sql);
    $fittings_list = get_goods_fittingsp($parent_list);
    $smarty->assign('fittings_list', $fittings_list);
    //-----------------------------------------------购物车配件模块END--------------------------------------------//

}

if($_REQUEST['step']=='cart'){
    $smarty->display('flow_cart.dwt');      // 购物车第一步页面
}elseif($_REQUEST['step']=='checkout'){
    $smarty->display('flow_checkout.dwt');  // 购物车第二步页面
}elseif($_REQUEST['step']=='done'){
    $smarty->display('flow_done.dwt');         // 购物车第三步页面
}
    

/*===============================================================================【新增函数】============================================================================*/
/**
 * 购物车数量
 */
function cart_goods_total_num_wap($type = CART_GENERAL_GOODS)
{
	$num = 0;
	if ($_SESSION['user_id'] > 0) {
		$sql =  "select goods_number FROM ".$GLOBALS['ecs']->table('cart')."
        WHERE user_id = '" . $_SESSION['user_id'] . "' AND rec_type = '$type'";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql =  "select goods_number FROM ".$GLOBALS['ecs']->table('cart')."
            WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') 
            AND rec_type = '$type'";
		} else {
			$sql =  "select goods_number FROM ".$GLOBALS['ecs']->table('cart')."
            WHERE session_id = '" . SESS_ID . "' AND rec_type = '$type'";
		}
	}
    $arr = $GLOBALS['db']->getAll($sql);	
    
    foreach($arr as $key => $value)
    {        
		$num = $num + $value['goods_number'];
    }
    return $num;
}
/**
 * 购物车重量
 */
function cart_goods_total_weight_wap($type = CART_GENERAL_GOODS)
{
	if ($_SESSION['user_id'] > 0) {
		$sql = "select c.goods_number, g.goods_weight from ".$GLOBALS['ecs']->table('cart')."
        as c left join ".$GLOBALS['ecs']->table('goods')." as g on c.goods_id=g.goods_id 
        WHERE user_id = '".$_SESSION['user_id']."' AND c.rec_type = '$type' ";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "select c.goods_number, g.goods_weight from ".$GLOBALS['ecs']->table('cart')."
            as c left join ".$GLOBALS['ecs']->table('goods')." as g on c.goods_id=g.goods_id 
            WHERE c.user_id <= 0 AND (session_id = '".SESS_ID."' 
            OR session_id = '".$_COOKIE['cart_session_id']."') AND c.rec_type = '$type' ";
		} else {
			$sql = "select c.goods_number, g.goods_weight from ".$GLOBALS['ecs']->table('cart')."
            as c left join ".$GLOBALS['ecs']->table('goods')." as g on c.goods_id=g.goods_id 
            WHERE session_id = '".SESS_ID."' AND c.rec_type = '$type' ";
		}
	}
    $arr = $GLOBALS['db']->getAll($sql);	
	$goods_weight = 0;
    foreach($arr as $key => $value)
    {        
		$goods_weight += $arr[$key]['goods_weight']*$arr[$key]['goods_number'];
    }
    return $goods_weight;
}

/** 
*检查购物车中是否有商品 
*/
function check_carts($flow_type){
    if ($_SESSION['user_id'] > 0) {
    	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('carts')." WHERE user_id = '" . $_SESSION['user_id'] . "'
        AND parent_id = 0 AND is_gift = 0 AND promotion_type = '$flow_type'";
    } else {
    	if (isset($_COOKIE['cart_session_id'])) {
    		$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('carts')." WHERE user_id <= 0
            AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') 
            AND parent_id = 0 AND is_gift = 0 AND promotion_type = '$flow_type'";
    	} else {
    		$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('carts')." WHERE session_id = '" . SESS_ID . "'
            AND parent_id = 0 AND is_gift = 0 AND promotion_type = '$flow_type'";
    	}
    }
    if($GLOBALS['db']->getOne($sql) == 0)
    {
		$sqlg = "select count(rec_id) from ecs_carts where session_id='" . SESS_ID ."' and is_gift=888;";
		if($GLOBALS['db']->getOne($sqlg) == 0)
		{
	        
            show_message_wap('购物车中没有商品', '', 'flow.php', 'warning');
		}
    }
}

/**
 * 秒杀包邮
 * 购物车中包含秒杀的包邮产品
 */

function miaosha_free_ship(){
    $req_time = $_SERVER['REQUEST_TIME'];
    $ms_info = $GLOBALS['db']->getRow("SELECT * FROM ecs_miaosha WHERE status=0 AND start_time <= " .$req_time. " AND end_time >= " .$req_time. " ORDER BY rec_id DESC LIMIT 1");
    if($ms_info['free_ship'] == 0){
        $cart_goods = get_cart_goods();
        foreach ($cart_goods['goods_list']  as $goods ){
            if($goods['goods_id'] == $ms_info['goods_id']){
                return true;
                break;
            }
        }
        return false;;
    }
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:团购包邮商品是否在购物车中
 * ----------------------------------------------------------------------------------------------------------------------
 */
function by_tuan_in_cart(){
    $sql = "select rec_id from ecs_cart where session_id='".SESS_ID."' and extension_code= 'tuan_buy' and is_shipping = 1 limit 1;";

    $res = $GLOBALS['db']->getOne($sql);
    return empty($res)? false: true;
}

/**
 * 函数 zhang:获取最热商品
 * 参数：size 获取的数量
*/
function get_hot_goods_flow($size=12)
{
    $sql = "select g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, IFNULL(mp.user_price, round(g.shop_price * '$_SESSION[discount]',2)) AS shop_price, g.promote_price, g.goods_type, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img,g.original_img, g.sales_tag from "
        .$GLOBALS['ecs']->table('goods')." as g left join ".$GLOBALS['ecs']->table('member_price').
        " as mp on mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'".
        " where g.is_hot > 0 and g.is_on_sale = 1 and g.is_alone_sale = 1 and g.is_delete = 0 order by g.sort_order asc,g.goods_id desc limit 0," . $size;

    $new_goods = $GLOBALS['db']->GetAll($sql);
    foreach($new_goods as $k => $v)
    {
        //处理有特价的商品
        $promote_price = $new_goods[$k]['promote_price'];
        if( $promote_price > 0){
            $promote_price = bargain_price($promote_price, $new_goods[$k]['promote_start_date'], $new_goods[$k]['promote_end_date']);
        }else{
            $promote_price = 0;
        }
        $new_goods[$k]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';

        $new_goods[$k]['url'] = 'goods.php?id='.$new_goods[$k]['goods_id'];
    }
    return $new_goods;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:退回积分兑换商品的积分
 * ----------------------------------------------------------------------------------------------------------------------
 */
function reback_exchange_jf_wap($rec_id=0, $user_id=0)
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
        log_account_change_wap($user_id, 0, 0, 0, $zhe_jifen, $log_msg);
    }
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 删除购物车中的商品。 id:购物车ID.
 * ----------------------------------------------------------------------------------------------------------------------
 * yi修改：去掉or (is_gift <> 0 and is_gift<>888 and is_gift<>70 and goods_price=0)
 */
function flow_drop_cart_goods_wap($id=0)
{
    $row = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id='$id' limit 1;");
    if($row)
    {
        // 解决多方登陆后无法删除购物车商品问题  zhang: 160119
        if($_SESSION['user_id'] > 0){  // 登陆状态下
            $where_id = " user_id = '".$_SESSION['user_id']."'";
        }else{
            $where_id = " session_id = '". SESS_ID ."'";
        }
        //yi:删除组合购买的主体商品，则更新组合购买商品。
        $sql = "select * from ecs_cart where ". $where_id ." and extension_code='group_buy' and extension_id=".$row['goods_id'];
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
                $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE ".$where_id." AND (rec_id=$id OR parent_id=$id)";
            }
            else
            {
                //$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE ".$where_id." AND (rec_id=$id OR rec_id=".$row['parent_id'].")";
            }
        }
        else
        {
            //删除普通商品，同时删除其配件
            if($row['parent_id'] == 0 && $row['is_gift'] == 0)
            {
                $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE ".$where_id." AND (rec_id='$id' or parent_id='$row[goods_id]')";
            }
            else//删除非普通商品，只删除该商品即可
            {
                $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE ".$where_id." AND rec_id='$id' limit 1;";
            }
        }
        $GLOBALS['db']->query($sql);
    }
}

/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   float   $user_money     可用余额变动
 * @param   float   $frozen_money   冻结余额变动
 * @param   int     $rank_points    等级积分变动
 * @param   int     $pay_points     消费积分变动
 * @param   string  $change_desc    变动说明
 * @param   int     $change_type    变动类型：参见常量文件
 * @return  void
 */
function log_account_change_wap($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER)
{
    /* 插入帐户变动记录 */
    $account_log = array(
        'user_id'       => $user_id,
        'user_money'    => $user_money,
        'frozen_money'  => $frozen_money,
        'rank_points'   => $rank_points,
        'pay_points'    => $pay_points,
        'change_time'   => gmtime(),
        'change_desc'   => $change_desc,
        'change_type'   => $change_type
    );
    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');

    /* 更新用户信息 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('users') .
        " SET user_money = user_money + ('$user_money')," .
        " frozen_money = frozen_money + ('$frozen_money')," .
        " rank_points = rank_points + ('$rank_points')," .
        " pay_points = pay_points + ('$pay_points')" .
        " WHERE user_id = '$user_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
}
/**
 *  yi:包含该商品的(全部或指定类别)优惠活动
 */
function include_goods_fav($goods_id=0, $act_type=-1)
{
    $now = $_SERVER['REQUEST_TIME'];
    $tsql= ($act_type==-1)? "": " and act_type=".$act_type;
    $sql = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' ".$tsql." ORDER BY `start_time` desc,`end_time` desc";
    $fav = $GLOBALS['db']->getAll($sql);

    foreach($fav as $k => $v)
    {
        $fav[$k]['gift'] = unserialize($v['gift']);
        $fav_ok   = false;
        $bb       = explode(",", $fav[$k]['act_range_ext']);

        if(empty($bb))
        {
            unset($fav[$k]); continue;
        }

        switch($v['act_range'])
        {
            case 0: $fav_ok = true;  break;
            case 1:
                $goods_cat_id = get_cat_id($goods_id);
                if(in_array($goods_cat_id, $bb))
                {
                    $fav_ok = true;
                }
                else
                {
                    $gift_parent_id = $GLOBALS['db']->getOne("select parent_id from ecs_category where cat_id=".$goods_cat_id." limit 1;");
                    if(in_array($gift_parent_id, $bb))
                    {
                        $fav_ok = true;
                    }
                }
                break;
            case 2:
                $goods_brand = get_brand_id($goods_id);
                if(in_array($goods_brand, $bb))
                {
                    $fav_ok = true;
                }
                break;
            case 3:
                if(in_array($goods_id, $bb))
                {
                    $fav_ok = true;
                }
                break;
            default:
                break;
        }
        if(false === $fav_ok)
        {
            unset($fav[$k]);
        }
    }

    return $fav;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * tao:专享价商品是否在购物车中(是否支持货到付款)
 * ----------------------------------------------------------------------------------------------------------------------
 */
function no_pay_after_by_source($cart_goods)
{
    foreach($cart_goods as $v){
        if($v['extension_code'] == 'source_buy'){
            $no_pay_after = $GLOBALS['db']->getOne("SELECT no_pay_after FROM " . $GLOBALS['ecs']->table('source') ." WHERE rec_id = ".$v['extension_id']);
        }
    }
	return empty($no_pay_after)? false: true;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * yi:专享价包邮商品是否在购物车中
 * ----------------------------------------------------------------------------------------------------------------------
 */
function by_source_in_cart($cart_goods)
{
    foreach($cart_goods as $v){
        if($v['extension_code'] == 'source_buy'){
            $is_by = $GLOBALS['db']->getOne("SELECT is_by FROM " . $GLOBALS['ecs']->table('source') ." WHERE rec_id = ".$v['extension_id']);

        }
    }
    return empty($is_by)? false: true;
}

/**
 * BY:TAO
 * @name 删除错误的团购商品
 * @param 传入购物车单品信息
 */
function del_error_tuan($v){
    $sqlStr = " AND extension_id=".$v['extension_id']." AND extension_code = 'tuan_buy' AND parent_id = 0";
        if ($_SESSION['user_id'] > 0) {
    		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type ='$type' ".$sqlStr;
    	} else {
    		if (isset($_COOKIE['cart_session_id'])) {
    			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type ='$type'".$sqlStr;
    		} else {
    			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type ='$type'".$sqlStr;
    		}
    	}
        $have_parent_tuan = $GLOBALS['db']->getOne($sql);
        if(!$have_parent_tuan){
            $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id = ".$v['rec_id']);
        }
}

/**
 * BY:TAO
 * @name 删除父商品id不存在的子商品
 * @param 传入购物车单品信息
 */
function del_no_parent($v){
    $sqlStr = " AND extension_id=".$v['extension_id']." AND extension_code = 'unchange' AND extension_id = '1212' AND parent_id = 0";
    
        if ($_SESSION['user_id'] > 0) {
    		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type ='$type' ".$sqlStr;
    	} else {
    		if (isset($_COOKIE['cart_session_id'])) {
    			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type ='$type'".$sqlStr;
    		} else {
    			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type ='$type'".$sqlStr;
    		}
    	}
        $have_parent_tuan = $GLOBALS['db']->getOne($sql);
        if(!$have_parent_tuan){
            $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id = ".$v['rec_id']);
        }
}
//查询完全重复的赠品记录，并合并为一条(删除多余的)  zhang: 160108修改
function merge_same_gift_new()
{
	$same_gif = $GLOBALS['db']->GetAll("SELECT * FROM ecs_cart a WHERE a.session_id='".SESS_ID."' 
					AND (a.session_id, a.goods_id, a.goods_attr, a.extension_code, a.parent_id, a.is_gift, a.goods_attr_id, a.zselect, a.yselect, a.is_kj)
					IN (SELECT session_id, goods_id, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, yselect, is_kj FROM ecs_cart
					GROUP BY session_id, goods_id, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, yselect, is_kj HAVING COUNT(*) > 1)");

	if (count($same_gif) > 0)
	{
		//判断goods_id是否相同(即是否有多个goods_id的完全重复记录)
		$cart_goods_id_array = array();
		foreach ($same_gif as $key => $v)
		{
		    //当标识为unchange并且不为赠品的时候，取消合并 by:tao
            if($v['extension_code'] == 'unchange' && $v['is_gift'] == 0){
                unset($same_gif[$key]);
            }else{
                $cart_goods_id_array[$key]['goods_id']       = $v['goods_id'];
    			$cart_goods_id_array[$key]['goods_attr']     = $v['goods_attr'];
    			$cart_goods_id_array[$key]['extension_code'] = $v['extension_code'];
    			$cart_goods_id_array[$key]['zselect']        = $v['zselect'];
    			$cart_goods_id_array[$key]['yselect']        = $v['yselect'];
    			$cart_goods_id_array[$key]['parent_id']      = $v['parent_id'];
    			$cart_goods_id_array[$key]['is_gift']        = $v['is_gift'];
    			$cart_goods_id_array[$key]['is_kj']          = $v['is_kj'];
            }
			
		}

        // 数组去重begin
        foreach ($cart_goods_id_array as $gk=>$gv){
            $gv = implode(',',$gv);  //降维,将一维数组转换为用逗号连接的字符串
            $temp[$gk] = $gv;
        }
        $temp = empty($temp) ? "" : array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
        if($temp != ""){
            foreach ($temp as $tk => $tv){
                $array = explode(',',$tv); //再将拆开的数组重新组装
                $cart_goods_id_array2[$tk]['goods_id']        = $array[0];
                $cart_goods_id_array2[$tk]['goods_attr']      = $array[1];
                $cart_goods_id_array2[$tk]['extension_code']  = $array[2];
                $cart_goods_id_array2[$tk]['zselect']         = $array[3];
                $cart_goods_id_array2[$tk]['yselect']         = $array[4];
                $cart_goods_id_array2[$tk]['parent_id']       = $array[5];
                $cart_goods_id_array2[$tk]['is_gift']         = $array[6];
                $cart_goods_id_array2[$tk]['is_kj']           = $array[7];
            }
        }
        // 数组去重end

		if (count($cart_goods_id_array2) > 1)
		{
			//有不同goods_id的重复值,则分别合并不同重复的good_id
			foreach ($cart_goods_id_array2 as $gv)
			{
				$same_goods = $GLOBALS['db']->GetAll("SELECT * FROM ecs_cart WHERE session_id='".SESS_ID."' AND goods_id = '".$gv['goods_id']."' AND goods_attr = '".$gv['goods_attr']."'
				    AND extension_code = '".$gv['extension_code']."' AND parent_id = '".$gv['parent_id']."' AND is_gift = '".$gv['is_gift']."' AND zselect = '".$gv['zselect']."'
				    AND yselect = '".$gv['yselect']."' AND is_kj = '".$gv['is_kj']."' ORDER BY rec_id DESC");
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

/*
 * 将隐形眼镜分左右眼度数的记录整理成左眼度数，以便之后数据展示
 */
function sort_out_cart_goods(){

    if ($_SESSION['user_id'] > 0) {
        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = 0 AND is_kj = 0 AND ds_extention != '1' AND is_gift = 0 ORDER BY rec_id";
    } else {
        if (isset($_COOKIE['cart_session_id'])) {
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = 0 AND is_kj = 0 AND ds_extention != '1' AND is_gift = 0 ORDER BY rec_id";
        } else {
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type = 0 AND is_kj = 0 AND ds_extention != '1' AND is_gift = 0 ORDER BY rec_id";
        }
    }
    $goods_cart = $GLOBALS['db']->GetAll($sql);
    foreach($goods_cart as $k=>$v){

        if($v['zcount'] + $v['ycount'] != $v['goods_number'] && ($v['extension_code'] == "" || $v['extension_code'] == 'source_buy') && ($v['zcount'] > 0 || $v['ycount'] > 0)){
            // 左右眼数量之和不等于商品总数的普通商品，删除
            $sqld = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id = ".$v['rec_id'];
            $GLOBALS['db']->query($sqld);
        }elseif(empty($v['zselect']) && !empty($v['yselect'])){
            // 左眼为空 右眼非空 将右眼信息转移到左眼
            $sqlz = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET zselect = '".$v['yselect']."', zcount = '".$v['ycount']."', yselect = '', ycount = '' WHERE rec_id = ".$v['rec_id'];
            $GLOBALS['db']->query($sqlz);
        }elseif(!empty($v['zselect']) && !empty($v['yselect'])){
            // 左右眼都不为空 将右眼信息作为一条新增记录插入 原记录删除右眼信息
            $sqly = "INSERT INTO ".$GLOBALS['ecs']->table('cart')." (`rec_id`, `user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`,
                `is_real`, `extension_code`, `extension_id`, `parent_id`, `rec_type`, `is_gift`, `is_cx`, `is_shipping`, `can_handsel`, `goods_attr_id`, `zselect`, `zcount`, `yselect`, `ycount`,`is_kj`, 
                `ds_extention`, `add_time`, `shop_id`, `effective_time`) VALUES('','".$v['user_id']."','".$v['session_id']."','".$v['goods_id']."','".$v['goods_sn']."','".$v['goods_name']."','".$v['market_price']."',
                '".$v['goods_price']."','".$v['ycount']."','".$v['goods_attr']."','".$v['is_real']."','".$v['extension_code']."','".$v['extension_id']."','".$v['parent_id']."','".$v['rec_type']."',
                '".$v['is_gift']."','".$v['is_cx']."','".$v['is_shipping']."','".$v['can_handsel']."','".$v['goods_attr_id']."','".$v['yselect']."','".$v['ycount']."','','','".$v['is_kj']."',
                '".$v['ds_extention']."','".$v['add_time']."','".$v['shop_id']."','".$v['effective_time']."')";
            $GLOBALS['db']->query($sqly);
            // 删除原纪录右眼记录
            $del = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET yselect = '', ycount = '', goods_number = '".$v['zcount']."' WHERE rec_id = ".$v['rec_id'];
            $GLOBALS['db']->query($del);
        }elseif(empty($v['yselect']) && $v['ycount'] == 0){
            // 无右眼度数和数量的时候将这两个字段清空，以便后边的合并
            $sqlk = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET yselect = '', ycount = '' WHERE rec_id = ".$v['rec_id'];
            $GLOBALS['db']->query($sqlk);
        }else{
            // 其他逻辑
        }
    }
}
?>