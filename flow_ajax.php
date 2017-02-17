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
	
    //获取并传入area_id
    $area_id = $db->getOne('SELECT shipping_area_id FROM '. $ecs->table('area_region'). ' WHERE region_id = '.$_REQUEST['pro']);
    $area_id = empty($area_id)? 5 : $area_id;
    
	//查看购物车中是否全为免运费商品，若是则把运费赋为零
    foreach ($shipping_list AS $key => $val)
    {
        $shipping_cfg = unserialize_config($val['configure']);
        $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
        $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number'],$area_id);
	    if($_SESSION['base_line']==1 && $area_id != 22 ){
			$shipping_fee = 0;
		}    	
        $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
        $shipping_list[$key]['shipping_fee']        = $shipping_fee;
        $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);
        $shipping_list[$key]['insure_formated']     = strpos($val['insure'], '%') === false ?
        price_format($val['insure'], false) : $val['insure'];

        /* 当前的配送方式是否支持保价 */
        if ($val['shipping_id'] == $order['shipping_id'])
        {
            $insure_disabled = ($val['insure'] == 0);
            $cod_disabled    = ($val['support_cod'] == 0);
        }
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
	echo $add_id;
}
elseif($_REQUEST['action'] == 'delete_address')
{
    include_once(ROOT_PATH.'includes/lib_transaction.php');
	/*=========================【删除单个收获人地址】=========================*/
	$address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
    
	if(!empty($address_id))
	{
		if(drop_consignee($address_id))
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

?>