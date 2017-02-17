<?php
//==================================================【ajax处理专页】购物车的业务逻辑==============================================//
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');

/*=========================根据收货人地址重新计算运费,(包括超重的费用) 返回给前端最新的配送方式=========================*/

if($_REQUEST['action'] == 'shipping')
{
	/*=========================保存支付方式和配送方式到数据库中=========================*/
	//获取前端传递过来的省市区编号
	$addres_pro  = isset($_REQUEST['pro']) ? $_REQUEST['pro'] :0;
	$addres_city = isset($_REQUEST['city'])? $_REQUEST['city']:0;
	$addres_dist = isset($_REQUEST['dist'])? $_REQUEST['dist']:0;
	$flow_type   = isset($_REQUEST['type'])? $_REQUEST['type']:0;		

	$consignee = array("country"=> 1, "province" => $addres_pro ,"city"=> $addres_city ,"district"=> $addres_dist);

	$region        = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);	
	$shipping_list = array();//配送方式列表

	//------------------------------------【收货人地址在上海【321】才有上门自提配送方式】-----------------------------||
	$shipping_list = (intval($addres_city)==321)? get_shipping_list($region,true):get_shipping_list($region,false);

	//------------------------------------【收货人地址在上海【321】才有上门自提配送方式】-----------------------------||

	/*-----------------------购物商品超重的费用-----------------------*/
    $cart_weight_price = cart_weight_price($flow_type);
    $insure_disabled   = true;
    $cod_disabled      = true;

	//礼包中商品的运费按照普通商品计算,超过200同样免运费
    $sql = 'SELECT count(*) FROM ' . $ecs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `is_shipping` = 0";
    $shipping_count = $db->getOne($sql);

	//查看购物车中是否全为免运费商品，若是则把运费赋为零
    foreach ($shipping_list AS $key => $val)
    {
        $shipping_cfg = unserialize_config($val['configure']);
        $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
        $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);

        $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
        $shipping_list[$key]['shipping_fee']        = $shipping_fee;
        $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);
        $shipping_list[$key]['insure_formated']     = strpos($val['insure'], '%') === false ?
        price_format($val['insure'], false) : $val['insure'];

        /* 当前的配送方式是否支持保价 */
    //    if ($val['shipping_id'] == $order['shipping_id'])
//        {
//            $insure_disabled = ($val['insure'] == 0);
//            $cod_disabled    = ($val['support_cod'] == 0);
//        }
    }

    $jshipping = json_encode($shipping_list);
    
    echo $jshipping; 
}
elseif($_REQUEST['action'] == 'add_new_address')
{	
	//=========================【新增收货人地址】return 0:没有新增地址, 大于0:新增地址add_id=========================//
	include_once(ROOT_PATH.'includes/lib_transaction.php');
	$user_id = isset($_SESSION['user_id'])?intval($_SESSION['user_id']): 0;
	$add_id  = 0;

	//获得客户端表单提交的【收货人地址】
	$consignee = array(
        'user_id'      => $user_id,
        'address_id'   => '',
        'country'      => isset($_POST['country'])   ? intval($_POST['country']) : 1,
        'province'     => isset($_POST['province'])  ? intval($_POST['province']): 0,
        'city'         => isset($_POST['city'])      ? intval($_POST['city'])    : 0,
        'district'     => isset($_POST['district'])  ? intval($_POST['district']): 0,
        'address'      => isset($_POST['address'])   ? trim($_POST['address'])   : '',
        'consignee'    => isset($_POST['consignee']) ? trim($_POST['consignee']) : '',
        'email'        => isset($_POST['email'])     ? trim($_POST['email'])     : '',
        'tel'          => isset($_POST['tel'])       ? make_semiangle(trim($_POST['tel'])) : '',
        'mobile'       => isset($_POST['mobile'])    ? make_semiangle(trim($_POST['mobile'])) : '',
        'best_time'    => isset($_POST['best_time']) ? trim($_POST['best_time'])  : '',
        'sign_building'=> isset($_POST['sign_building']) ? trim($_POST['sign_building']) : '',
        'zipcode'      => isset($_POST['zipcode'])       ? make_semiangle(trim($_POST['zipcode'])) :''
    );	

	if($user_id>0)
	{
		$same_add  = false;//标记是否有相同地址		
		$sql       = "select * from ".$GLOBALS['ecs']->table('user_address')." where user_id=".$user_id.";";
		$u_address = $GLOBALS['db']->GetAll($sql);
		
		//匹配是否有重复的地址
		foreach($u_address as $k => $v)
		{
			if( $u_address[$k]['consignee']==$consignee['consignee']&&$u_address[$k]['province']==$consignee['province']&&$u_address[$k]['city']==$consignee['city']
				&&$u_address[$k]['district']==$consignee['district']&&$u_address[$k]['email']==$consignee['email']&&$u_address[$k]['address']==$consignee['address']
				&&$u_address[$k]['tel']==$consignee['tel'])
			{
				$same_add = true;
			}
		}

		//如果不存在重复的地址则新增地址 并且返回刚刚更新的地址id		
		if(!$same_add)
		{
			if($consignee['province']!=0 && $consignee['city']!=0 && $consignee['district']!=0)
			{
				$add_id = yi_update_address($consignee);
			}
		}	
	}

	//同时保存一份地址到session中
	$_SESSION['flow_consignee'] = stripslashes_deep($consignee);

	//返回最新插入的一条用户地址的id编号
	echo $add_id;die;
}
elseif($_REQUEST['action'] == 'delete_address')
{
	/*=========================【删除单个收获人地址】=========================*/
	$address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
	if(!empty($address_id))
	{
        $res = $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('user_address')." WHERE address_id=".$address_id);
		if($res)
		{
			echo('success');
		}
		else
		{
			echo('fail');
		}                	
	}
	else
	{
		echo('fail');
	}
}
elseif($_REQUEST['action'] == 'pay_and_shipping')
{	
	//=========================支付方式和配送方式表单提交=========================||
	//暂无
}
elseif($_REQUEST['action'] == 'other_info')
{
	/*=========================处理其它信息(用户留言 开发票)提交数据库(暂未处理)=========================*/
	//暂无
}
elseif($_REQUEST['action'] == 'load_citys')
{	
	//=========================ajax加载省下面的城市[备份可删]=========================//

	$type   = !empty($_REQUEST['type'])   ? intval($_REQUEST['type'])   : 0;
	$parent = !empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0;
	
	$arr['regions'] = get_regions($type, $parent);
	$arr['type']    = $type;
	$arr['target']  = !empty($_REQUEST['target']) ? stripslashes(trim($_REQUEST['target'])) : '';
	$arr['target']  = htmlspecialchars($arr['target']);
	print_r($arr);
}
elseif($_REQUEST['action'] == 'get_city')
{	
	//=========================获取省下面的所有的市=========================//
	$pro      = isset($_REQUEST['pro']) ? intval($_REQUEST['pro']): 0;
	$def_city = isset($_REQUEST['city'])? intval($_REQUEST['city']):0;
	$sql  = "select * from ".$GLOBALS['ecs']->table('region')." where parent_id=".$pro." AND region_type=2;";
	$city = $GLOBALS['db']->GetAll($sql);

	//字符串返回客户端
	$arr = '<option value="0">请选择市</option>';
	foreach($city as $k => $v)
	{
		if( $city[$k]['region_id'] == $def_city)
		{
			$arr .= '<option selected="selected" value="'.$city[$k]['region_id'].'">'.$city[$k]['region_name'].'</option>';
		}
		else
		{
			$arr .= '<option value="'.$city[$k]['region_id'].'">'.$city[$k]['region_name'].'</option>';
		}
	}
	echo($arr);
}
elseif($_REQUEST['action'] == 'get_dist')
{
	//=========================获取市下面的所有的区=========================//
	$city     = isset($_REQUEST['city']) ? intval($_REQUEST['city']): 0;
	$def_dist = isset($_REQUEST['dist']) ? intval($_REQUEST['dist']): 0;
	$sql  = "select * from ".$GLOBALS['ecs']->table('region')." where parent_id=".$city." AND region_type=3;";
	$dist = $GLOBALS['db']->GetAll($sql);

	//字符串返回客户端
	$arr = '<option value="0">请选择区</option>';
	foreach($dist as $k => $v)
	{
		if( $dist[$k]['region_id'] == $def_dist)
		{
			$arr .= '<option selected="selected" value="'.$dist[$k]['region_id'].'">'.$dist[$k]['region_name'].'</option>';
		}
		else
		{
			$arr .= '<option value="'.$dist[$k]['region_id'].'">'.$dist[$k]['region_name'].'</option>';
		}
	}
	echo($arr);
}
/**
   zhang:添加保存修改后的收货地址   2015-08-07
 **/
elseif($_REQUEST['action'] == 'save_address')
{
	//=========================保存修改后的收货地址=========================//
    include_once(ROOT_PATH.'includes/lib_transaction.php');
    $user_id = isset($_SESSION['user_id'])?intval($_SESSION['user_id']): 0;
    $add_id  = isset($_POST['address_id'])  ? intval($_POST['address_id']): 0;
    $arr = $GLOBALS['db']->getRow("select * from ecs_user_address where address_id=".$add_id." limit 1;");
    //获得客户端表单提交的【收货人地址】
    $consignee = array(
        'user_id'      => $user_id,
        'address_id'   => $add_id,
        'country'      => isset($_POST['country'])   ? intval($_POST['country']) : $arr['country'],
        'province'     => isset($_POST['province'])  ? intval($_POST['province']): $arr['province'],
        'city'         => isset($_POST['city'])      ? intval($_POST['city'])    : $arr['city'],
        'district'     => isset($_POST['district'])  ? intval($_POST['district']): $arr['district'],
        'address'      => isset($_POST['address'])   ? trim($_POST['address'])   : $arr['address'],
        'consignee'    => isset($_POST['consignee']) ? trim($_POST['consignee']) : $arr['consignee'],
        'email'        => isset($_POST['email'])     ? trim($_POST['email'])     : $arr['email'],
        'tel'          => isset($_POST['tel'])       ? make_semiangle(trim($_POST['tel'])) : $arr['tel'],
        'mobile'       => isset($_POST['mobile'])    ? make_semiangle(trim($_POST['mobile'])) : $arr['mobile'],
        'best_time'    => isset($_POST['best_time']) ? trim($_POST['best_time'])  : $arr['best_time'],
        'sign_building'=> isset($_POST['sign_building']) ? trim($_POST['sign_building']) : $arr['sign_building'],
        'zipcode'      => isset($_POST['zipcode'])       ? make_semiangle(trim($_POST['zipcode'])) : $arr['zipcode']
    );
    //同时保存一份地址到session中
    $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
    //返回用户地址的id编号
    echo $add_id;die;
}
elseif($_REQUEST['action'] == 'update_cart')
{
    /*----------------------------------------------------------Ajax更新购物车-------------------------------------------------------*/
    //ajax，获取get数据：key:rec_id. number:商品数量. zb:左眼数量. yb:右眼数量. $flow_type:购物类型.
    $zb = !empty($_GET['zb']) ? $_GET['zb'] : 0;
    $yb = !empty($_GET['yb']) ? $_GET['yb'] : 0;
    $key = $_GET['key'];
    $num = $_GET['number']; //rec_id对应的商品数量

    $goods_num = array();
    $goods_num[$key] = $num;

    //购物车中该商品更新,返回价格
    if($zb+$yb>0)
    {
        //有度数
        if($zb==0 && $yb>0 && $num > 0){$yb = $num;}
        if($yb==0 && $zb>0 && $num > 0){$zb = $num;}
        $restr = flow_update_cart($goods_num, $zb, $yb);
    }
    else
    {
        //没有度数
        $restr = flow_update_cart2($goods_num);
    }

    //购物车商品总数量,总重量
    $tnum         = cart_goods_total_num();
    $total_weight = cart_goods_total_weight();

    //购物车商品总金额
    $cart_goods = get_cart_goods();
    $total_sum  = $cart_goods['total']['goods_price'];

    //购物车中商品能获得的总积分
    $points = $cart_goods['total']['goods_amount'];

    //距免运费还差多少钱(68免运费)
    $base_line  = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 68;
    if(($cart_goods['total']['goods_pricex']-$base_line)>0){
        $freepx = 0;
    }else{
        $freepx = $base_line-$cart_goods['total']['goods_pricex'];
    }

    //返回ajax更新数据:数据用','构造，且顺序不能变更。
    echo $key.",".$restr.",".$tnum.",".$total_sum.",".$points.",".$freepx.",".$num.','.$total_weight.','.$base_line;
    exit;
}
else
{
	//其它逻辑
}


//===================================================================函数=====================================================================||

//yi:取得可用的配送方式列表【注意数据库中上门自提id不能够改变】 
//region_id_list：收货人地区id数组（包括国家、省、市、区）|| self:true是包括上门自提
function get_shipping_list($region_id_list, $self = true)
{	
	$sql = '';
	if($self)
	{
		$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, s.shipping_desc, s.insure, s.support_cod, a.configure FROM '.
			   $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' . $GLOBALS['ecs']->table('area_region') . ' AS r ' .
			   ' WHERE r.region_id ' . db_create_in($region_id_list) .
			   ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';
	}
	else
	{
		//不包括上门自提的配送方式
		$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, s.shipping_desc, s.insure, s.support_cod, a.configure FROM '.
			   $GLOBALS['ecs']->table('shipping') . ' AS s, ' . $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' . $GLOBALS['ecs']->table('area_region') . ' AS r ' .
			   ' WHERE r.region_id ' . db_create_in($region_id_list) .
			   ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 and s.shipping_id<>12';
	}
    return $GLOBALS['db']->getAll($sql);
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
                show_message_wap(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                    $row['goods_number'], $row['goods_number']));
                exit;
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message_wap($GLOBALS['_LANG']['package_stock_insufficiency']);
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
                show_message_wap(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                    $row['goods_number'], $row['goods_number']));
                exit;
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val))
            {
                show_message_wap($GLOBALS['_LANG']['package_stock_insufficiency']);
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
?>