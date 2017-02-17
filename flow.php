<?php
/* =======================================================================================================================
 * 商城页面 购物车流程【2012/5/29】【Author:yijiangwen】【同步TIME:2012/8/13】 
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
//date_default_timezone_set('PRC');
$domain_name = $_SERVER['SERVER_NAME'];
if (strstr($domain_name, 'ysyj'))
{
	header("Location: ysyj.php \n"); exit;
}


//未注册用户是否跳回展示页(index_unck.dwt)
if(!index_unck_display($_SESSION['user_id'])){
    header("Location: user.html \n");
}


//购物车第一步（默认）
if(!isset($_REQUEST['step'])){$_REQUEST['step'] = "cart";}

/*------------------------------------页头，页尾 数据---------------------------------------*/
assign_template();
assign_dynamic('flow');
$position = assign_ur_here(0,       $_LANG['shopping_flow']);

$smarty->assign('page_title',       $position['title']);            //页面标题
$smarty->assign('ur_here',			$position['ur_here']);          //当前位置
$smarty->assign('lang',             $_LANG);
$smarty->assign('img_site',  IMG_SITE);
/*------------------------------------页头，页尾 数据_end-----------------------------------*/

//----------------------------------------------------【根据购物车中商品，自动加入0元赠品】-----------------------------------------------------||
$sump    = get_cart_sump();     //购物车总金额
$c_goods = yi_get_cart_goods(1);//购物车商品记录, 一个rec_id为一条记录。
$cart_have_fav = false;

//tao:0元赠判定是折后价
$discount = compute_discount();
$discount = $discount['discount'];
//tao:0元赠判定是折后价


/*自动添加/清除赠品*/
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
	
}

/*yi:自动删除多余的加价购赠品*/
if($cart_have_fav)
{
    del_fav_goods_jjg();
}

/*xu:2013-08-09合并完全重复的赠品记录*/
    merge_same_gift();

/*检查购物车保存的历史数据*/
if ($_REQUEST['step'] == 'cart' && empty($_REQUEST['act'])) {
    ck_history_cart();
}

/*--------------------------------------------------------------商品加入购物车（一）--------------------------------------------------------------*/
if($_REQUEST['step'] == 'add_to_cart')
{
    /*------------------------------------------------------*/
    //-- 添加商品到购物车
    /*------------------------------------------------------*/
    include_once('includes/cls_json.php');

    $_POST['goods'] = json_str_iconv($_POST['goods']);

    if(!empty($_REQUEST['goods_id']) && empty($_POST['goods']))
    {
        if(!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)
        {
            ecs_header("Location:./\n");
        }
        $goods_id = intval($_REQUEST['goods_id']);
        exit;
    }

    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json  = new JSON;

    if(empty($_POST['goods']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $goods = $json->decode($_POST['goods']);
	//-------------------------------------------------------------------------------------------//
	//解决远视没有'+'的情况
	if(!empty($goods->zselect) && $goods->zselect!='平光' && $goods->zselect>0.00){
		$goods->zselect = '+'.trim($goods->zselect);
	}
	//散光片的散光度数
	if(!empty($goods->zsg) && $goods->zsg>0.00){
		$goods->zsg = '+'.trim($goods->zsg);
	}
	//-------------------------------------------------------------------------------------------//

    /* 如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
    if(empty($goods->spec) && empty($goods->quick))
    {
        $sql =  "SELECT a.attr_id, a.attr_name, a.attr_type,g.goods_attr_id, g.attr_value, g.attr_price " .
		        'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' .
		        'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
		        "WHERE a.attr_type != 0 AND g.goods_id = '" . $goods->goods_id . "' " .
		        'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';
        $res = $GLOBALS['db']->getAll($sql);
   
        if($res)
        {
            $spe_arr = array();
            foreach ($res AS $row)
            {
                $spe_arr[$row['attr_id']]['attr_type'] 	= $row['attr_type'];
                $spe_arr[$row['attr_id']]['name']     	= $row['attr_name'];
                $spe_arr[$row['attr_id']]['attr_id']    = $row['attr_id'];
                $spe_arr[$row['attr_id']]['values'][] 	= array(
                                                            'label'        => $row['attr_value'],
                                                            'price'        => $row['attr_price'],
                                                            'format_price' => price_format($row['attr_price'], false),
                                                            'id'           => $row['goods_attr_id']);
            }
            $i = 0;
            $spe_array = array();
            foreach ($spe_arr AS $row)
            {
                $spe_array[]=$row;
            }
            $result['error']   = ERR_NEED_SELECT_ATTR;
            $result['goods_id'] = $goods->goods_id;
            $result['parent'] = $goods->parent;
            $result['message'] = $spe_array;

            die($json->encode($result));
        }
    }
    
    /* 如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1')
    {
        clear_cart();
    }

	//yi:验证该度数是否有库存
	if('nobuy' == $goods->zselect)
	{
        $result['error']   = 1;
        $result['message'] = "很抱歉，您购买的度数正在补货中，该度数暂不能购买。";
	}
	else
	{
		if (!is_numeric($goods->number) || intval($goods->number) <= 0)
		{
			$result['error']   = 1; 
			$result['message'] = $_LANG['invalid_number'];//检查商品数量是否合法
		}
		else
		{
			//将session_id存进cookie,用于保存未登录用户的购物车信息(保存一天)
			if ($_SESSION['user_id'] <= 0) {
				if (!isset($_COOKIE['cart_session_id'])) {
					//首次加入购物车
					setcookie('cart_session_id', SESS_ID, time()+3600*24, '/', '');
				} else {
					//非首次加入购物车,将之前的session_id改为当前的SESS_ID
					if ($_COOKIE['cart_session_id'] != SESS_ID) {
						$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart').
                        " SET session_id='".SESS_ID."' WHERE user_id <= 0 AND session_id='".$_COOKIE['cart_session_id']."'");
						setcookie('cart_session_id', SESS_ID, time()+3600*24, '/', '');
					}
				}
			}
			
			/*--------------------------------------------------商品页添加到购物车----------------------------------------------*/
			if(!isset($goods->issg)){
	
				//添加到购物车
				if(addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->zselect, $goods->zcount, $goods->yselect, $goods->ycount))
				{
					if($_CFG['cart_confirm'] > 2)
					{
						$result['message'] = '';
					}
					else
					{
						$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
					}
					//插入购物车的反馈信息
					$result['content'] = insert_cart_infotop();   //购物车导航条
					$result['one_step_buy'] = $_CFG['one_step_buy'];
				}
				else
				{
					$result['message']  = $GLOBALS['err']->last_message();
					$result['error']    = $GLOBALS['err']->error_no;
					$result['goods_id'] = stripslashes($goods->goods_id);
				}
                
			}else{
				/*-----------------散光片添加到购物车中(包含单独镜片商品)-----------------*/
		
    
                if(empty($goods->is_jp)){
                    $addto_cartsg = addto_cartsg($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->zselect, $goods->zcount, $goods->yselect, $goods->ycount,$goods->zsg,$goods->zzhou);
                }else{//镜片加入购物车
                    //$addto_cartsg = addto_cartjp($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->zselect, $goods->zcount, $goods->yselect, $goods->ycount,$goods->zsg,$goods->zzhou,$goods->tongju);
                    $addto_cartsg = addto_cartsg($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->zselect, $goods->zcount, $goods->yselect, $goods->ycount,$goods->zsg,$goods->zzhou);
                }
                
                if($addto_cartsg)
				{
					if ($_CFG['cart_confirm'] > 2)
					{
						$result['message'] = '';
					}
					else
					{
						$result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
					}
					//插入购物车的反馈信息
					$result['content'] = insert_cart_infotop();
					$result['one_step_buy'] = $_CFG['one_step_buy'];
				}
				else
				{
					$result['message']  = $err->last_message();
					$result['error']    = $err->error_no;
					$result['goods_id'] = stripslashes($goods->goods_id);
				}
			}
		}
	}
    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    
    //die('{"error":0,"message":"","content":36,"goods_id":"","one_step_buy":0,"confirm_type":"3"}');//测试
    
    die($json->encode($result));
}
elseif($_REQUEST['step'] == 'link_buy')
{
    $goods_id = intval($_GET['goods_id']);

    if(!cart_goods_exists($goods_id,array()))
    {
        addto_cart($goods_id);
    }
    ecs_header("Location:./flow.php\n");
    exit;
}
elseif($_REQUEST['step'] == 'login')
{
    ecs_header("Location: user.php");
}
elseif ($_REQUEST['step'] == 'consignee')
{
	/*------------------------------------------------------确定收货人信息(合并到checkout步骤当中)------------------------------------------------------*/
    include_once('includes/lib_transaction.php');
	
	//从登陆页面跳转进入地址页面
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /*
         * 收货人信息填写界面
         */
        if(isset($_REQUEST['direct_shopping']))
        {
            $_SESSION['direct_shopping'] = 1; //可直接购买,不用注册
        }

        /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
        $smarty->assign('country_list',       get_regions());
        $smarty->assign('shop_country',       $_CFG['shop_country']);
        $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

		$user_id = $_SESSION['user_id'];
		$add_not_null = true; //地址不为空标记:默认不为空.

        //用户已经登录
        if ($_SESSION['user_id'] > 0)
        {
            $consignee_list = get_consignee_list($_SESSION['user_id']);//取得地址列表(5个)

            if(count($consignee_list) == 0)
            {
                //原先:如果用户收货人地址的总数0 则增加一个新的收货人信息
                $consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
				$add_not_null = false; //标记地址为空。
            }
        }
        else
        {
			//用户没有登录.匿名购买....

			//如果匿名用户以前填写过地址,并且会话没有过期.
            if(isset($_SESSION['flow_consignee'])){
                $consignee_list = array($_SESSION['flow_consignee']);
            }
            else
            {
                $consignee_list[] = array('country' => $_CFG['shop_country']);
            }
        }        
        
		//yi:------------------------------------重新更改地址--------------------------------------------------
		foreach($consignee_list AS $region_id => $consignee)
		{
			//yi:所在地区名字显示出来.
			$consignee_list[$region_id]['provincena'] = get_regions_name($consignee_list[$region_id]['province']);
			$consignee_list[$region_id]['cityna']     = get_regions_name($consignee_list[$region_id]['city']);
			$consignee_list[$region_id]['districtna'] = get_regions_name($consignee_list[$region_id]['district']);
		}

		$smarty->assign('consignee_list', $consignee_list);
		$smarty->assign('add_not_null',   $add_not_null);  //地址为空情况标记(默认地址是不为空)
		$smarty->assign('user_id',        $user_id);
		
		//yi:登录用户默认地址id
		$address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");
		$smarty->assign('default_addres', $address_id);
		$smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
		//---------------------------------------------end----------------------------------------------------


        //取得每个收货地址的省市区列表
        $province_list = array();
        $city_list     = array();
        $district_list = array();
        foreach($consignee_list as $region_id => $consignee)
        {
            $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
            $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
            $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

            $province_list[$region_id] = get_regions(1, $consignee['country']);
            $city_list[$region_id]     = get_regions(2, $consignee['province']);
            $district_list[$region_id] = get_regions(3, $consignee['city']);
        }
        $smarty->assign('province_list', $province_list);
        $smarty->assign('city_list',     $city_list);
        $smarty->assign('district_list', $district_list);

        /* 返回收货人页面代码 */
        $smarty->assign('real_goods_count', exist_real_goods(0, $flow_type) ? 1 : 0);//购物车当中真实商品的数量
    }
    else
    {
        /*
         * 保存收货人信息
         */
        $consignee = array(
            'address_id'    => empty($_POST['address_id']) ? 0  : compile_str(cleanInput(intval($_POST['address_id']))),
            'consignee'     => empty($_POST['consignee'])  ? '' : compile_str(cleanInput(trim($_POST['consignee']))),
            'country'       => empty($_POST['country'])    ? '' : compile_str(cleanInput($_POST['country'])),
            'province'      => empty($_POST['province'])   ? '' : compile_str(cleanInput($_POST['province'])),
            'city'          => empty($_POST['city'])       ? '' : compile_str(cleanInput($_POST['city'])),
            'district'      => empty($_POST['district'])   ? '' : compile_str(cleanInput($_POST['district'])),
            'email'         => empty($_POST['email'])      ? '' : compile_str(cleanInput($_POST['email'])),
            'address'       => empty($_POST['address'])    ? '' : compile_str(cleanInput($_POST['address'])),
            'zipcode'       => empty($_POST['zipcode'])    ? '' : compile_str(cleanInput(make_semiangle(trim($_POST['zipcode'])))),
            'tel'           => empty($_POST['tel'])        ? '' : compile_str(cleanInput(make_semiangle(trim($_POST['tel'])))),
            'mobile'        => empty($_POST['mobile'])     ? '' : compile_str(cleanInput(make_semiangle(trim($_POST['mobile'])))),
            'sign_building' => empty($_POST['sign_building']) ? '' : compile_str(cleanInput($_POST['sign_building'])),
            'best_time'     => empty($_POST['best_time'])  ? '' : compile_str(cleanInput($_POST['best_time'])),
        );

        if ($_SESSION['user_id'] > 0)
        {
            include_once(ROOT_PATH . 'includes/lib_transaction.php');
            /* 如果用户已经登录，则保存收货人信息 */
            $consignee['user_id'] = $_SESSION['user_id'];
            save_consignee($consignee, true);
        }

        /* 保存到session */
        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);


		/*------------------------------保存收货人信息之后 页面跳转------------------------------*/
        ecs_header("Location: flow.php?step=checkout\n");
        exit;
    }
}
elseif($_REQUEST['step'] == 'drop_consignee')
{
	/*--------------------------------删除收货人信息--------------------------------*/
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if(drop_consignee($consignee_id))
    {
        ecs_header("Location: flow.php?step=consignee\n");
        exit;
    }
    else
    {
        show_message($_LANG['not_fount_consignee']);
    }
}
elseif($_REQUEST['step'] == 'ajax_save_addres')
{
	include_once('includes/lib_transaction.php');
	/*--------------------------------保存 收货人信息--------------------------------*/
	$consignee = array(
		'address_id'    => empty($_POST['address_id']) ? 0  : intval($_POST['address_id']),
		'consignee'     => empty($_POST['consignee'])  ? '' : trim($_POST['consignee']),
		'country'       => empty($_POST['country'])    ? '' : $_POST['country'],
		'province'      => empty($_POST['province'])   ? '' : $_POST['province'],
		'city'          => empty($_POST['city'])       ? '' : $_POST['city'],
		'district'      => empty($_POST['district'])   ? '' : $_POST['district'],
		'email'         => empty($_POST['email'])      ? '' : $_POST['email'],
		'address'       => empty($_POST['address'])    ? '' : $_POST['address'],
		'zipcode'       => empty($_POST['zipcode'])    ? '' : make_semiangle(trim($_POST['zipcode'])),
		'tel'           => empty($_POST['tel'])        ? '' : make_semiangle(trim($_POST['tel'])),
		'mobile'        => empty($_POST['mobile'])     ? '' : make_semiangle(trim($_POST['mobile'])),
		'sign_building' => empty($_POST['sign_building']) ? '' : $_POST['sign_building'],
		'best_time'     => empty($_POST['best_time'])  ? '' : $_POST['best_time'],
	);	
	

	/*如果是会员用户,保存收货人信息到数据库中*/
	if($_SESSION['user_id'] > 0)
	{
		$same_add = false;//是否有相同地址
		
		$sql = "select * from ".$GLOBALS['ecs']->table('user_address')." where user_id=".$_SESSION['user_id'].";";
		$u_address = $GLOBALS['db']->GetAll($sql);
		
		//遍历用户地址 匹配是否有重复的地址
		foreach($u_address as $k => $v){
			if( $u_address[$k]['consignee']==$consignee['consignee']&&$u_address[$k]['province']==$consignee['province']&&$u_address[$k]['city']==$consignee['city']
				&&$u_address[$k]['district']==$consignee['district']&&$u_address[$k]['email']==$consignee['email']&&$u_address[$k]['address']==$consignee['address']
				&&$u_address[$k]['tel']==$consignee['tel']){
				$same_add = true;
			}
		}

		if(!$same_add){	
			//这里只是更新 用户的收获地址
			$consignee['user_id'] = $_SESSION['user_id'];
			save_consignee($consignee, true);
		}
	}

	/*同时 临时保存一份到session*/
	$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
}

elseif($_REQUEST['step'] == 'checkout')
{
	/*====================================================================订单确认【页面】(购物车第二步)===============================================================*/
	include_once('includes/lib_transaction.php');
	$user_id = $_SESSION['user_id'];
    date_default_timezone_set('PRC');
    
    $flow_step = 'flow_checkout.dwt';
    
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    if($flow_type == CART_GROUP_BUY_GOODS)
    {
        $smarty->assign('is_group_buy', 1);//团购
    }
    elseif($flow_type == CART_EXCHANGE_GOODS)
    {
        $smarty->assign('is_exchange_goods', 1);//积分兑换
    }
    else
    {
        $_SESSION['flow_order']['extension_code'] = '';//正常购物流程  清空其他购物流程情况
    }

    /* 检查购物车中是否有商品 */
    if ($_SESSION['user_id'] > 0) {
    	$sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . 
        "' AND parent_id = 0 AND is_gift = 0 AND shop_id = 2";
    } else {
        //B2B 不允许未登录状态下操作购物车
        header("Location: user.html \n");
    }
    
    if($db->getOne($sql) == 0)
    {
        show_message($_LANG['no_goods_in_cart'], '', '', 'warning');
    }

	/*------------------------------------------------------------------------------------
     * 检查用户是否已经登录
     * 1.已经登录 判断是否有默认收货地址->有则显示默认地址
     * 2.没有登录 跳转到登录和注册页面
     ------------------------------------------------------------------------------------*/
    if(empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
		header("Location: user.html \n");
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
		$address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id' AND shop_id = 2");
		$smarty->assign('default_addres', $address_id);
	}
	else
	{
		header("Location: user.html \n");
	}
	//----------------------------------------------------------------------------------------------------------------
	foreach($consignee_list AS $region_id => $consignee)
	{
		//yi:地区名称
		$consignee_list[$region_id]['provincena'] = get_regions_name($consignee_list[$region_id]['province']);
		$consignee_list[$region_id]['cityna']     = get_regions_name($consignee_list[$region_id]['city']);
		$consignee_list[$region_id]['districtna'] = get_regions_name($consignee_list[$region_id]['district']);
	}
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

	/*=======================如果是登陆用户,并且有默认地址.进来即显示默认地址==============*/
	$smarty->assign('user_default_addres', ($_SESSION['user_id']>0 && $address_id)? true : false);

	/*--------------------------------------------------------------收货人地址页面【end】----------------------------------------------------------------*/

    $consignee = get_consignee($_SESSION['user_id']);//会话中默认收货地址

    //检查收货人信息是否完整 如果不完整则转向到收货人信息填写界面
    if(!check_consignee_info($consignee, $flow_type))
    {
		//yi:如果无完整配送信息的情况.ajax中没有地址的转向了.
		$smarty->assign('consignee_is_null', 1);
    }

    $_SESSION['flow_consignee'] = $consignee;
    $smarty->assign('consignees', $consignee);//当前的收货人地址(配送区域显示默认值)

    //对【购物车】商品信息赋值
    $cart_goods = cart_goods($flow_type);    //取得商品列表，计算合计
    $smarty->assign('goods_list', $cart_goods);

    //取得购物流程设置     
    $smarty->assign('config', $_CFG);

	/*------------------------------------------------------本次购物订单处理------------------------------------------------------*/
	/*=======================================================================*/
	//用户上一次的支付方式和配送方式【cookie保存】
	$old_payment  = $_COOKIE["payment"];
	$old_shipping = $_COOKIE["shipping"];
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
    
	$sou_g  = $GLOBALS['db']->getAll("select * from ecs_cart where user_id='".$user_id."' and extension_code='source_buy' ;");	
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
	if(!$_SESSION['direct_shopping'])
	{
		$order['pay_id']      = $old_payment;
		$order['shipping_id'] = $old_shipping;
	}	
    
    //不能货到付款的情况
	if(no_cod_goods() || goods_in_cart(3178, '') || goods_in_cart(4554, '') || goods_in_cart(4846, '') || goods_in_cart(767, 'tuan_buy') || by_tuan_in_cart())
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
    /*------------------------------------------------------计算订单的费用------------------------------------------------------*/	
    
    if(by_tuan_in_cart()){//订单中包含包邮团购则包邮对应done
        $total = order_fee($order, $cart_goods, $consignee,true);    
    }else{
        $total = order_fee($order, $cart_goods, $consignee);    
    }
    /**
     *秒杀商品里面设置了包邮， 所以所有的商品都是包邮性质
     */
    if(miaosha_free_ship())
    {
        $total['amount'] = $total['amount'] - $total['shipping_fee'];
        $total['shipping_fee']= 0;
        $total['amount_formated'] = price_format($total['amount'],false);
    }

    $smarty->assign('total',             $total);
    $smarty->assign('shopping_money',    sprintf($_LANG['shopping_money'], $total['formated_goods_price']));
    $smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));
    /*------------------------------------------------------计算订单的费用end--------------------------------------------------*/	

	/*------------------------------------------------------配送方式列表-----------------------------------------------------------*/
	//msg:根据配送地址计算配送费用(选择这里的时候必须选择好配送地址)
    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
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

	//礼包中商品的运费按照普通商品计算,超过68同样免运费，超重同样收费。    
    $sql = 'SELECT count(*) FROM ' . $ecs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `is_shipping` = 0";
    $shipping_count = $db->getOne($sql);
	//查看购物车中是否全为免运费商品，若是则把运费赋为零

    foreach($shipping_list AS $key => $val)
    {
        $shipping_cfg = unserialize_config($val['configure']);
        $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
        $cart_weight_price['weight'], $cart_weight_price['amount']-$discount['discount'], $cart_weight_price['number']);

    	if($_SESSION['base_line']==1 && $goods_price_total<1 && $total['area_id'] != 22 || miaosha_free_ship()){
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
        }
        
        /**
         * B2B不支持货到付款
         */
        if($val['shipping_id'] == 8){
            unset($shipping_list[$key]);
        }
    }
    $smarty->assign('shipping_list',      $shipping_list);
    $smarty->assign('insure_disabled',    $insure_disabled);
    $smarty->assign('cod_disabled',       $cod_disabled);
	$smarty->assign('shipping_flow_type', $flow_type);
	//----------------------------------------修改礼包运费问题end------------------------------------------------------------------------

	//yi:指定支付方式包邮标志
	//$ship_gid_arr = array(1260,1261,1265,1264,1263,1262,1269,1268,1267,1266,1164,1097,978,977,979,1437,1441,1436,1321,1323,1319,1386,1390,1399,1359,1355,1357,1145,1144);
    $ship_gid_arr = array();
	if(include_ship_fee_goods($ship_gid_arr))
	{
		$smarty->assign('ship_fee_goods',  1);
	}

	/*----------------------------------------------------------支付方式列表------------------------------------------------------------------*/
    if($order['shipping_id'] == 0 )
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
    } 

	/*----------------初始加载所有可用的支付方式列表(包括余额支付)----------------*/

   
    
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
		$goods_price = $total['goods_price']-$total['discount'];
        $user_bonus = user_bonus($_SESSION['user_id'], $goods_price);//取得基本红包

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
 
	//订单信息数组 保存在SESSION中
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'select_shipping')
{
	//选择配送方式 改变配送方式【功能】

	include_once('includes/cls_json.php');
    $json   = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    $flow_type  = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    $consignee  = get_consignee($_SESSION['user_id']); //收货人信息
    $cart_goods = cart_goods($flow_type);             //取得商品列表信息，计算合计

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
		
        
        /* 计算订单的费用 */
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
elseif ($_REQUEST['step'] == 'select_insure')
{
    /*------------------------------------------------------ */
    //-- 选定/取消配送的保价
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['need_insure'] = intval($_REQUEST['insure']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
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

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }
        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }
    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_payment')
{
    /*------------------------------------------------------*/
    //-- 改变支付方式
    /*------------------------------------------------------*/

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);

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
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['pay_id'] = intval($_REQUEST['payment']);
        $payment_info = payment_info($order['pay_id']);
        $result['pay_code'] = $payment_info['pay_code'];

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
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

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_pack')
{
    /*------------------------------------------------------ */
    //-- 改变商品包装
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['pack_id'] = intval($_REQUEST['pack']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
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

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_card')
{
    /*------------------------------------------------------ */
    //-- 改变贺卡
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['card_id'] = intval($_REQUEST['card']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
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

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $order['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'change_surplus')
{
    /*------------------------------------------------------ */
    //-- 改变余额
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');

    $surplus   = floatval($_GET['surplus']); //使用余额数
    $user_info = user_info($_SESSION['user_id']);

    if($user_info['user_money'] + $user_info['credit_line'] < $surplus)
    {
        $result['error'] = $_LANG['surplus_not_enough'];
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type); //取得商品列表，计算合计

        if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
        {
			if( empty($cart_goods) ){
				$result['error'] = $_LANG['no_goods_in_cart'];
			}else{
				//收货地址不全
                $result['error'] = '请先填写您的收货地址';
			}
        }
        else
        {
            //取得订单信息
            $order = flow_order_info();
            $order['surplus'] = $surplus;//订单中使用余额

            //计算订单的费用(重新计算)
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

            //团购标志
            if($flow_type == CART_GROUP_BUY_GOODS)
            {
                $smarty->assign('is_group_buy', 1);
            }
			//重新获得要显示内容的模板lib
            $result['content'] = $smarty->fetch('library/order_total.lbi');
        }
    }
    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_integral')
{
    /*------------------------------------------------------ */
    //改变积分
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');

    $points    = floatval($_GET['points']);
    $user_info = user_info($_SESSION['user_id']);

    /* 取得订单信息 */
    $order = flow_order_info();

    $flow_points = flow_available_points();  // 该订单允许使用的积分
    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)
    {
        $result['error'] = $_LANG['integral_not_enough'];
    }
    elseif ($points > $flow_points)
    {
        $result['error'] = sprintf($_LANG['integral_too_much'], $flow_points);
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        $order['integral'] = $points;

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type); //取得商品列表，计算合计

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
            /* 计算订单的费用 */
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


            $smarty->assign('total',  $total);
            $smarty->assign('config', $_CFG);

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $smarty->assign('is_group_buy', 1);
            }

            $result['content'] = $smarty->fetch('library/order_total.lbi');
            $result['error'] = '';
        }
    }

    $json = new JSON();
    die($json->encode($result));
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
        
    	if (in_array($bonus['type_id'], array(818, 819, 822, 823, 903, 904, 922, 923, 924, 925, 996, 997, 1031, 1102, 1107, 1236, 1355, 1620, 1634, 1635,1704,1762,1769,1823,1824,1869,1879,1950,1991,2063,2165,2176,2178)))
        {
        	$smarty->assign('special_bouns', 1); //标识实物红包
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_needinv')
{
    /*------------------------------------------------------ */
    //-- 改变发票的设置
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');
    $json = new JSON();
    $_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';
    $_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';
    $_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';

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
        die($json->encode($result));
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1)
        {
            $order['need_inv']    = 1;
            $order['inv_type']    = trim(stripslashes($_GET['inv_type']));
            $order['inv_payee']   = trim(stripslashes($_GET['inv_payee']));
            $order['inv_content'] = trim(stripslashes($_GET['inv_content']));
        }
        else
        {
            $order['need_inv']    = 0;
            $order['inv_type']    = '';
            $order['inv_payee']   = '';
            $order['inv_content'] = '';
        }

        /* 计算订单的费用 */
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

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        die($smarty->fetch('library/order_total.lbi'));
    }
}
elseif ($_REQUEST['step'] == 'change_oos')
{
    /*------------------------------------------------------ */
    //-- 改变缺货处理时的方式
    /*------------------------------------------------------ */

    /* 取得订单信息 */
    $order = flow_order_info();

    $order['how_oos'] = intval($_GET['oos']);

    /* 保存 session */
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'check_surplus')
{
    /*------------------------------------------------------ */
    //-- 检查用户输入的余额
    /*------------------------------------------------------ */
    $surplus   = floatval($_GET['surplus']);
    $user_info = user_info($_SESSION['user_id']);

    if (($user_info['user_money'] + $user_info['credit_line'] < $surplus))
    {
        die($_LANG['surplus_not_enough']);
    }

    exit;
}
elseif ($_REQUEST['step'] == 'check_integral')
{
    /*------------------------------------------------------ */
    //-- 检查用户输入的积分
    /*------------------------------------------------------ */
    $points      = floatval($_GET['integral']);
    $user_info   = user_info($_SESSION['user_id']);
    $flow_points = flow_available_points();  // 该订单允许使用的积分
    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)
    {
        die($_LANG['integral_not_enough']);
    }

    if ($points > $flow_points)
    {
        die(sprintf($_LANG['integral_too_much'], $flow_points));
    }

    exit;
}
//===================================================前端完成所有订单操作。购物车订单提交数据库【功能】================================================//
elseif($_REQUEST['step'] == 'done')
{
    include_once('includes/lib_clips.php');
    include_once('includes/lib_payment.php');
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    
    $flow_step = 'flow_done.dwt';
    //xyz:20130110 保存购物车信息
    if ($_SESSION['user_id'] > 0) {
    	$sql = "SELECT COUNT(*) FROM ".$ecs->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND parent_id = 0 AND (is_gift = 0 or is_gift=888) ";
    } else {
    	ecs_header("Location: user.html\n");
    }
    
    if($db->getOne($sql) == 0)
    {
        show_message($_LANG['no_goods_in_cart'], '返回购物车', 'flow.php', 'warning');//检查购物车中是否有商品
		exit;
    }
	
    //----------------------------------------------检查用户是否已经登录---------------------------------------//
    if(empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
        //用户没有登录且没有选定匿名购物，转向到登录页面
        ecs_header("Location: user.html\n");
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
        'add_time'        => time(),
        'order_status'    => OS_UNCONFIRMED,
        'shipping_status' => SS_UNSHIPPED,
        'pay_status'      => PS_UNPAYED,
        'agency_id'       => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'])),
        'shop_id'         => 2,  
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
        
        if (in_array($bonus['type_id'], array(818, 819, 822, 823, 903, 904, 922, 923, 924, 925, 996, 997, 1031, 1102, 1107, 1236, 1355, 1620, 1634, 1635,1704,1762,1769,1823,1824,1869,1879,1950,1991,2063,2165,2176,2178)) && $bonus['order_id'] <= 0)
        {
        	$order['bonus_id'] = $bonus['bonus_id']; //临时处理0元红包送赠品
        }
    }
    //购物车中商品数组
    $cart_goods = cart_goods($flow_type);
    
    if(empty($cart_goods))
    {
		show_message($_LANG['no_goods_in_cart'], 'flow.html', 'flow.html', 'warning');
    }


    //过滤收货人信息（添加反斜杠）
    foreach($consignee as $key => $value)
    {
        $order[$key] = addslashes($value);
    }

    //订单中的总金额计算
    if(by_tuan_in_cart()){//订单中包含包邮团购则包邮对应checkout
        $total = order_fee($order, $cart_goods, $consignee,true);    
    }else{
        $total = order_fee($order, $cart_goods, $consignee);    
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
            show_message($_LANG['balance_not_enough']);
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
			" SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE user_id = '".$_SESSION['user_id']."' AND shop_id = 2";
    } else {
    	if (isset($_COOKIE['cart_session_id'])) {
    		$sql =  "INSERT INTO ".$ecs->table('order_goods').
		    "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
			" SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND shop_id = 2";
    	} else {
    		$sql =  "INSERT INTO ".$ecs->table('order_goods').
		    "( order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention) ".
			" SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_cx, goods_attr_id, zselect, zcount, yselect, ycount, ds_extention FROM ".$ecs->table('cart') ." WHERE session_id = '".SESS_ID."' AND shop_id = 2";
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
    


    if(miaosha_free_ship())
    {
        $total['amount'] = $total['amount'] - $total['shipping_fee'];
        $order['order_amount'] = $total['amount'];
        $total['shipping_fee']= 0;
        $order['shipping_fee']=0;
        $total['amount_formated'] = price_format($order['order_amount'], false);
    }
    
    //清空购物车
    clear_cart();
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

	//-----------------------------------清除session中收货人信息-----------------------------------//
    unset($_SESSION['flow_consignee']); 
    unset($_SESSION['flow_order']);
    unset($_SESSION['direct_shopping']);
}
elseif($_REQUEST['step'] == 'update_cart')
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
	$tnum         = cart_goods_total_num($flow_type);
	$total_weight = cart_goods_total_weight($flow_type);

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
elseif($_REQUEST['step'] == 'drop_goods')
{
    /*------------------------------------------------------ */
    //-- 删除购物车中的商品
    /*------------------------------------------------------ */
    $rec_id = intval($_GET['id']);
    flow_drop_cart_goods($rec_id);
    ecs_header("Location: flow.html\n");	
    exit;
}
elseif($_REQUEST['step'] == 'drop_goods_sel')
{
    /*------------------------------------------------------ */
    //-- 删除购物车中选中的商品
    /*------------------------------------------------------ */
    $rec_id_arr = explode(',',trim($_POST['id']));
    
    foreach($rec_id_arr as $v){
        flow_drop_cart_goods($v);
    }
    ecs_header("Location: flow.html\n");	
    exit;
}

elseif($_REQUEST['step'] == 'drop_head_cart_goods'){
	//删除页面头部导航的购物车商品
	$rec_id = intval($_GET['id']);
    flow_drop_cart_goods($rec_id);
    exit;
}
elseif($_REQUEST['step'] == 'drop_exchange_goods')
{
    //删除购物车中积分兑换和积分折扣商品
    //========================================================//
    $rec_id  = intval($_GET['rec_id']);
	$user_id = $_SESSION['user_id'];
	reback_exchange_jf($rec_id, $user_id);
    flow_drop_cart_goods($rec_id);
    ecs_header("Location: flow.html\n");	
    exit;
}
elseif($_REQUEST['step'] == 'ajax_drop_goods')
{
	//删除商品
    $rec_id = intval($_GET['id']);	
    flow_drop_cart_goods($rec_id);
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
elseif($_REQUEST['step'] == 'ajax_drop_package')
{
	//--------------------------------------------删除购物车中的大礼包--------------------------------------
	$rec_id = intval($_GET['id']);
	//找出礼包id
	$sql = "select market_price from ".$ecs->table('cart')." where rec_id=".$rec_id.";";
	$package_id = intval($GLOBALS['db']->getOne($sql));

	//找出礼包商品数量
	$sql2 = "select count(*) from ".$ecs->table('package_goods')." where package_id=".$package_id.";";
	$num = intval($GLOBALS['db']->getOne($sql2));

	$rec_idarr = "";
	//删除该礼包中的商品
	if($num>0){
		for($i=0; $i<$num; $i++){
			$rr_id = $rec_id+$i;
			//逐个删除
			flow_drop_cart_goods($rr_id);
			$rec_idarr .= $rr_id;
			if($i < ($num-1) ){ $rec_idarr .= ",";}
		}
	}
	//更新数据
	$cnum = insert_cart_num();
	$csum = insert_cart_sum();
	echo $cnum.",".$csum.",".$rec_idarr;
	exit;
}
elseif($_REQUEST['step'] == 'update_package')
{
	/*--------------------------------------------更新购物车中的大礼包-------------------------------------------------------------------*/

	$key = intval($_GET['key']);
	$num = intval($_GET['number']);
	$goods_num = array();
	$goods_num[$key]=$num;
	$restr = flow_update_cart2($goods_num);
	//---------------------------------礼包中的情况---------------------------------
	$sql = "select market_price from ".$ecs->table('cart')." where rec_id=".$key.";";
	$package_id = intval($GLOBALS['db']->getOne($sql));

	//找出礼包商品数量
	$sql2 = "select count(*) from ".$ecs->table('package_goods')." where package_id=".$package_id.";";
	$goodn = intval($GLOBALS['db']->getOne($sql2));
	if($goodn>0){
		for($i=0;$i<$goodn;$i++){
			$aa = $key+$i;
			$goods_num[$aa] = $num;
		}
	}

	//---------------------------------更新购物车--返回礼包价格---------------------------------
	$restraa = flow_update_cart2($goods_num);

	//---------------------------------重新计算商品数量，积分，价格总计传到前端---------------------------------
	$tnum = cart_goods_total_num($flow_type);

	//商品金额总计
    $cart_goods = get_cart_goods();
	$total_sum  = $cart_goods['total']['goods_price'];

	//购物后获得的总积分
	$points = $cart_goods['total']['goods_amount'];

	//68免运费
	$base_line  = isset($_SESSION['base_line'])? intval($_SESSION['base_line']): 68;
	if(($cart_goods['total']['goods_pricex']-$base_line)>0){
		$freepx = 0;
	}else{
		$freepx = $base_line-$cart_goods['total']['goods_pricex'];
	}

	echo $key.','.$num.','.$goodn.','.$restr.','.$tnum.','.$total_sum.','.$points.','.$freepx.','.$base_line;
	exit;
}
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
			flow_drop_cart_goods($rr_id);
		}
	}
	ecs_header("Location: flow.php\n");
	exit;
}
elseif($_REQUEST['step'] == 'drop_tuan')
{
	//删除团购商品
	$rec_id = intval($_GET['id']);
	//团购ID
	$tuan_id = intval($GLOBALS['db']->getOne("SELECT market_price FROM ".$ecs->table('cart')." WHERE rec_id=".$rec_id));

	$sql = "DELETE FROM " .$GLOBALS['ecs']->table('cart'). " WHERE session_id='" . SESS_ID . "' AND extension_code = 'tuan_buy' AND extension_id = $tuan_id";
	$GLOBALS['db']->query($sql);
	
	ecs_header("Location: flow.php\n");
	exit;
}
//------------------------------------------优惠活动加入购物车(原)------------------------------------------------------------
elseif ($_REQUEST['step'] == 'add_favourable')
{	
    /* 取得优惠活动信息 */
    $act_id = intval($_POST['act_id']);
    $favourable = favourable_info($act_id);
    if (empty($favourable))
    {
        show_message($_LANG['favourable_not_exist']);
    }

    /* 判断用户能否享受该优惠 */
    if (!favourable_available($favourable))
    {
        show_message($_LANG['favourable_not_available']);
    }

    /* 检查购物车中是否已有该优惠 */
    $cart_favourable = cart_favourable();
    if (favourable_used($favourable, $cart_favourable))
    {
        show_message($_LANG['favourable_used']);
    }

    /* 赠品（特惠品）优惠 */
    if ($favourable['act_type'] == FAT_GOODS)
    {
        /* 检查是否选择了赠品 */
        if (empty($_POST['gift']))
        {
            show_message($_LANG['pls_select_gift']);
        }

        /* 检查是否已在购物车 */
        $sql = "SELECT goods_name" .
                " FROM " . $ecs->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND rec_type = '" . CART_GENERAL_GOODS . "'" .
                " AND is_gift = '$act_id'" .
                " AND goods_id " . db_create_in($_POST['gift']);
        $gift_name = $db->getCol($sql);
        if (!empty($gift_name))
        {
            show_message(sprintf($_LANG['gift_in_cart'], join(',', $gift_name)));
        }

        /* 检查数量是否超过上限 */
        $count = isset($cart_favourable[$act_id]) ? $cart_favourable[$act_id] : 0;
        if ($favourable['act_type_ext'] > 0 && $count + count($_POST['gift']) > $favourable['act_type_ext'])
        {
            show_message($_LANG['gift_count_exceed']);
        }

        /* 添加赠品到购物车 */
        foreach ($favourable['gift'] as $gift)
        {
            if (in_array($gift['id'], $_POST['gift']))
            {
                add_gift_to_cart($act_id, $gift['id'], $gift['price']);
            }
        }
    }
    elseif ($favourable['act_type'] == FAT_DISCOUNT)
    {
        add_favourable_to_cart($act_id, $favourable['act_name'], cart_favourable_amount($favourable) * (100 - $favourable['act_type_ext']) / 100);
    }
    elseif ($favourable['act_type'] == FAT_PRICE)
    {
        add_favourable_to_cart($act_id, $favourable['act_name'], $favourable['act_type_ext']);
    }

    /* 刷新购物车 */
    ecs_header("Location: flow.php\n");
    exit;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:优惠活动商品加入购物车（包括加价购活动）
 * ----------------------------------------------------------------------------------------------------------------------
 */
elseif($_REQUEST['step'] == 'add_fav')
{
	$fav      = '';
	$showmsg  = '';
	$rec_id   = ''; //加入购物车后的ecs_cart.rec_id号。
	$ds       = ''; //加钱赠品的眼镜度数。护理液为空。


	$goods_id = isset($_GET['goods_id'])? intval($_GET['goods_id']): 0;//加钱赠品ID
	$price    = isset($_GET['price'])   ? floatval($_GET['price']): 0; //加钱多少
	$num      = isset($_GET['num'])     ? intval($_GET['num']): 1;     //加价购数量
	$fa_id    = isset($_GET['fa_id'])   ? intval($_GET['fa_id']): 0;
	$act_id   = isset($_GET['act_id'])  ? intval($_GET['act_id']):0;

	$favourable = favourable_info($act_id);//该优惠活动信息

    if($favourable['act_type'] == 3)//优惠活动之：加价购。
    {        
		//特惠商品加入购物车
		if($num == 1)
		{
			$ds      = $_GET['ds'];
			$zselect = $_GET['ds']; 				
			$zcount  = 1;
			$yselect = null;
			$ycount  = 0;
		}
		elseif($num == 2)
		{
			$ds      = $_GET['zselect'].','.$_GET['yselect'];//眼镜度数字符串
			$zselect = $_GET['zselect'];
			$zcount  = 1;
			$yselect = $_GET['yselect'];
			$ycount  = 1;				
		}

		//----------------------------------------检查这一个商品是否已在购物车--------------------------------------------//
		//a.购物车中已加优惠品数量
		$fav_g_num   = $GLOBALS['db']->getOne("select sum(goods_number) from ecs_cart where session_id='".SESS_ID."' AND is_gift=".$act_id.";");
		$buy_number  = intval($favourable['buy_number']); //买几
		$gift_nunber = intval($favourable['gift_number']);//送几

		$fav_can_num = in_fav_number($act_id);			  //母体商品数
		$fav_can_div = ($buy_number>0)? floor($fav_can_num/$buy_number):0;
		$fav_can_get = $fav_can_div*$gift_nunber;		  //购物车能加赠品数

		if(($fav_g_num+$buy_number)>$fav_can_get)
		{			
			$goods_id = 0;//表示已经超过优惠商品数量，不能继续加价购。
		}
		else
		{
			$rec_id = add_gift_to_cart2($act_id, $goods_id, $price, $num, $zselect, $zcount, $yselect, $ycount);//加钱赠品加入购物车
		}
    }

	//-------------------------------------------------加价购成功后，返回数据给回调函数--------------------------------------------//
	$tnum       = cart_goods_total_num();              //购物车总商品数	
	$cart_goods = get_cart_goods();                    //购物车商品
	$total_sum  = $cart_goods['total']['goods_price']; //购物车总金额
	$points     = $cart_goods['total']['goods_amount'];//购物车总积分
	$cart_weight= cart_goods_total_weight();           //购物车商品总重
	$freepx     = ($cart_goods['total']['goods_pricex']>68)? 0 :(68-$cart_goods['total']['goods_pricex']);//免运费句子
    
	$addg       = $GLOBALS['db']->getRow("select goods_name,goods_img from ecs_goods where goods_id=".$goods_id." limit 1;");
	$goods_name = trim($addg['goods_name']);//加入购物车中商品名字
	$goods_img  = trim($addg['goods_img']); //加入购物车中商品图片
	//---------0--------------1---------------2------------3----------4-----------5-----------6------------7-----------8-----------9-----------10--------11------12----------13-----
	$fav = $goods_id.','.$goods_name.','.$goods_img.','.$price.','.$rec_id.','.$tnum.','.$total_sum.','.$points.','.$freepx.','.$act_id.','.$fa_id.','.$num.','.$ds.','.$cart_weight;
	echo $fav;
	exit;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:优惠活动商品加入购物车（加价购中护理液老的）
 * ----------------------------------------------------------------------------------------------------------------------
 */
elseif($_REQUEST['step'] == 'add_fav2')
{
	$fav      = '';
	$showmsg  = '';
	$rec_id   = '';
	$ds       = '';
	$goods_id = isset($_GET['goods_id'])? intval($_GET['goods_id']): 0;
	$price    = isset($_GET['price'])   ? floatval($_GET['price']): 0;
	$num      = isset($_GET['num'])     ? intval($_GET['num']): 1;
	$fa_id    = isset($_GET['fa_id'])   ? intval($_GET['fa_id']): 0;
	$act_id   = isset($_GET['act_id'])  ? intval($_GET['act_id']):0;
   
	$favourable = favourable_info($act_id);


	//------------------------如果是赠品-------------------------------------------
    if($favourable['act_type'] == FAT_GOODS)
    {
        //--------------------检查这一个商品是否已在购物车------------------------
        foreach($favourable['gift'] as $gift)
        {	
			//遍历特惠商品数组，逐个特惠商品加入购物车
			$zselect = null;
			$zcount  = 0;
			$yselect = null;
			$ycount  = 0;
			$rec_id = add_gift_to_cart2($act_id, $gift['id'], $gift['price'], $num, $zselect, $zcount, $yselect, $ycount);
        }
    }

	//-------------------------------------------------返回字符串数据到前端--------------------------------------------
	$tnum       = cart_goods_total_num();	
	$cart_goods = get_cart_goods();                    //购物车商品
	$total_sum  = $cart_goods['total']['goods_price']; //购物车总金额
	$points     = $cart_goods['total']['goods_amount'];//获得积分
	$cart_weight= cart_goods_total_weight();           //商品总重
	$freepx     = ($cart_goods['total']['goods_pricex']>68)? 0 :(68-$cart_goods['total']['goods_pricex']);//免运费句子
	$addg       = $GLOBALS['db']->getRow("select goods_name,goods_img from ecs_goods where goods_id=".$goods_id);
	$goods_name = $addg['goods_name'];
	$goods_img  = $addg['goods_img'];
	$fav = $goods_id.','.$goods_name.','.$goods_img.','.$price.','.$rec_id.','.$tnum.','.$total_sum.','.$points.','.$freepx.','.$act_id.','.$fa_id.','.$num.','.$ds.','.$cart_weight;
	echo $fav;
	exit;
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
			
			if(($fav_mother <= $favourable['buy_number']) && ($fav_mother > $fav_g_num)){
				$fav_g_must = floatval($favourable['buy_number']);
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
//清空购物车
elseif ($_REQUEST['step'] == 'clear')
{
	//判断是否有积分加钱购商品，有则退还积分。
	$sql2 = "select * from ".$ecs->table('cart')." where session_id='" . SESS_ID . "' and extension_code='exchange_buy'; AND shop_id = 2";
	$exchange_buy = $GLOBALS['db']->GetAll($sql2);
	if(!empty($exchange_buy))
	{
		foreach($exchange_buy as $k => $v)
		{
			//$zhe_jifen = $v['extension_id'];
			$zhe_jifen = $v['extension_id'] * $v['goods_number']; //xu:2013-12-11 修改为积分*数量(修正兑换多个，只退回一个的bug)
			$user_id   = $v['user_id'];
			if($zhe_jifen>0)
			{
				$log_msg = date('Y年m月d日 H时i分', $_SERVER['REQUEST_TIME']+8*3600).' 取消积分折扣商品：退回'.$zhe_jifen.'积分';
				log_account_change($user_id, 0, 0, 0, $zhe_jifen, $log_msg);
			}
		}
	}
	
	if ($_SESSION['user_id'] > 0) {
		$sql = "DELETE FROM " . $ecs->table('cart') . " WHERE user_id='" . $_SESSION['user_id'] . "' AND shop_id = 2";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "DELETE FROM " . $ecs->table('cart') . " WHERE (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND shop_id = 2";
		} else {
			$sql = "DELETE FROM " . $ecs->table('cart') . " WHERE session_id='" . SESS_ID . "' AND shop_id = 2";
		}
	}
    $db->query($sql);
    ecs_header("Location:./\n");
}
elseif ($_REQUEST['step'] == 'drop_to_collect')
{
    if ($_SESSION['user_id'] > 0)
    {
        $rec_id = intval($_GET['id']);
        $goods_id = $db->getOne("SELECT  goods_id FROM " .$ecs->table('cart'). " WHERE rec_id = '$rec_id' AND session_id = '" . SESS_ID . "' ");
        $count = $db->getOne("SELECT goods_id FROM " . $ecs->table('collect_goods') . " WHERE user_id = '$_SESSION[user_id]' AND goods_id = '$goods_id'");
        if (empty($count))
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";
            $db->query($sql);
        }
        flow_drop_cart_goods($rec_id);
    }
    ecs_header("Location: flow.php\n");
    exit;
}
//验证红包序列号(Ajax中调用的功能)
elseif($_REQUEST['step'] == 'validate_bonus')
{
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

    if($bonus['send_type'] == '5'){
        $used_num = $GLOBALS['db']->GetOne("SELECT  COUNT(*) FROM ".$ecs->table("user_bonus")." WHERE bonus_type_id = ".$bonus['bonus_type_id']." AND user_id != 0");
        if($used_num>=$bonus['over_number']){
            $result['error']   = "该红包已被用完，无法再次使用此红包！";
        }
    }

	//yi:促销商品能否作用于红包
	if(!$bonus['cx_can_use'])
	{
		foreach($tmp_carts as $k1 => $v1)
		{
	
           if($v1['extension_code'] != 'unchange'){
             //yi:每个产品需查询是否正在做活动
             $fav_list = include_goods_fav($v1['goods_id']);
           }else{
             $fav_list = array();   
           }
      
           foreach($fav_list as $v){
            
                if($v['act_type'] == '1' || $v['act_type'] == '2'){
                    $is_cx = 1;
                }
           }
		    
            if($is_cx  || $v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
            {
         
				unset($tmp_carts[$k1]);
				$result['error']   = "很抱歉，您购买的产品不能使用该优惠券！";
				$json = new JSON();
    			die($json->encode($result));
			}
		}
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
		$sql = "SELECT SUM(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='".$_SESSION['user_id']."' and is_gift=0 and shop_id = 2";

    	if(!$bonus['cx_can_use'])
		{
			$sql .= " and is_cx=0 ";
		}
		$cart_amounts = $GLOBALS['db']->GetOne($sql);
        
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
		}
    }
    else
    {
        $smarty->assign('config', $_CFG);//购物流程设置        
        $order = flow_order_info();      //取得订单信息
 
        if(((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
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
        else
        {
            //$order['bonus_kill'] = 0;
            $order['bonus_id'] = '';
            $result['error'] = "您输入的优惠券不存在!";
        }   

        //重新计算购物车中订单的费用，局部更新购物车。
        $total = order_fee($order, $cart_goods, $consignee);
        
        $smarty->assign('total', $total);


        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }
    $json = new JSON();
    die($json->encode($result));
}
/*------------------------------------------------------ */
//-- 添加礼包到购物车
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'add_package_to_cart')
{
    include_once('includes/cls_json.php');
    $_POST['package_info'] = json_str_iconv($_POST['package_info']);

    $result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');
    $json  = new JSON;

    if (empty($_POST['package_info']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }
	//获得用户提交的礼包
    $package = $json->decode($_POST['package_info']);

	//---------------------------------------------------------------
	   $dd1 = $package->d1;$dd2 = $package->d2;
	   $dd3 = $package->d3;$dd4 = $package->d4;
	   $dd5 = $package->d5;$dd6 = $package->d6;
	   $dd7 = $package->d7;$dd8 = $package->d8;
	   $dd = array(0=>$dd1,1=>$dd2,2=>$dd3,3=>$dd4,4=>$dd5,5=>$dd6,6=>$dd7,7=>$dd8);
	//---------------------------------------------------------------
	//把属性插入购物车中--

    /* 如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1')
    {
        clear_cart();
    }

    /* 商品数量是否合法 */
    if (!is_numeric($package->number) || intval($package->number) <= 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['invalid_number'];
    }
    else
    {
        /* 添加到购物车 */
        if (add_package_to_cart($package->package_id, $package->number, $dd))
        {
            if ($_CFG['cart_confirm'] > 2)
            {
                $result['message'] = '';
            }
            else
            {
                $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
            }

            $result['content'] = insert_cart_info();
            $result['one_step_buy'] = $_CFG['one_step_buy'];
        }
        else
        {
            $result['message']    = $err->last_message();
            $result['error']      = $err->error_no;
            $result['package_id'] = stripslashes($package->package_id);
        }
    }
    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    die($json->encode($result));
}
else
{
	/*--------------------------------------------------------------购物车第一步(step=cart)-------------------------------------------------------*/
    $_SESSION['flow_type'] = CART_GENERAL_GOODS;//普通商品
    
    $flow_step = 'flow_cart.dwt';
    
    if($_CFG['one_step_buy'] == '1')
    {
        ecs_header("Location: flow.html?step=checkout\n");
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


    //是否在购物车里显示商品图片，商品属性
    $smarty->assign('show_goods_thumb',     $GLOBALS['_CFG']['show_goods_in_cart']);
    $smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);

    
    $smarty->assign('sort_order_list',get_goods_by_sort_order());   //商品精选

}
	//------------------------------------------------购物车公共数据----------------------------------------------||
	//$flow_type:购物类型
	$total_num    = cart_goods_total_num($flow_type);
	$total_weight = cart_goods_total_weight($flow_type);
    
	$smarty->assign('total_num',       $total_num);
	$smarty->assign('total_weight',    $total_weight);
	$smarty->assign('currency_format', $_CFG['currency_format']);
	$smarty->assign('integral_scale',  $_CFG['integral_scale']);
	$smarty->assign('step',            $_REQUEST['step']);

	assign_dynamic('shopping_flow');
    
    $smarty->display($flow_step);



/*===============================================================================【函数】============================================================================*/


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
 * yi:团购包邮商品是否在购物车中
 * ----------------------------------------------------------------------------------------------------------------------
 */
function by_tuan_in_cart()
{
	$sql = "select rec_id from ecs_cart where session_id='".SESS_ID."' and extension_code= 'tuan_buy' and is_shipping = 1 limit 1;";

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
		$sql  = "SELECT sum(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = '".CART_GENERAL_GOODS."' AND shop_id = 2";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql  = "SELECT sum(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '".CART_GENERAL_GOODS."'  AND shop_id = 2";
		} else {
			$sql  = "SELECT sum(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type = '".CART_GENERAL_GOODS."'  AND shop_id = 2";
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
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."'  AND shop_id = 2 ";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."')  AND shop_id = 2 ";
		} else {
			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."'  AND shop_id = 2 ";
		}
		
	}
	if(!$hv_pk)
	{
		$sql .= " and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' and extension_code<>'exchange_buy' and extension_code<>'exchange' ";
	}
	$cart_goods = $GLOBALS['db']->GetAll($sql);
	
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

	$carts   = $GLOBALS['db']->GetAll("SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND shop_id = 2 ".$sql_pk." ;");

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
	
	//tao:0元赠判定是折后价
	$discount  = compute_discount();
	$discount  = $discount['discount'];
	$in_fav    = $in_fav -$discount;
	//tao:0元赠判定是折后价
	
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
				//$same_gift_id = $GLOBALS['db']->getAll("SELECT rec_id FROM ecs_cart WHERE session_id='".SESS_ID."' AND is_gift='$act_id' ORDER BY rec_id");
				//tao：20141201
				$same_gift_id = $GLOBALS['db']->getAll("SELECT rec_id,goods_id FROM ecs_cart 
				WHERE session_id='".SESS_ID."' 
				AND is_gift='$act_id' AND goods_id NOT IN (3273,3986) ORDER BY rec_id");
				
				if (count($same_gift_id) > 1) //有多个重复赠品
				{
					//tao：20141201start
					foreach($same_gift_id as $v){
						$arr[$v['rec_id']]= $v['goods_id'];
					}
					// 获取去掉重复数据的数组  
				    $unique_arr = array_unique ( $arr );  
				    // 获取重复数据的数组  
				    $repeat_arr = array_diff_assoc ( $arr, $unique_arr );  
	
					foreach($repeat_arr as $k=> $v2){//$k为欲删除的rec_id
						$GLOBALS['db']->query("DELETE FROM ecs_cart 
						WHERE session_id='".SESS_ID."' AND is_gift='".$act_id."' AND rec_id = ".$k);
					}
					//tao：20141201end
					
					//$saved_rec_id = $same_gift_id[0];
					//if ($saved_rec_id['rec_id']) //欲保留的rec_id
					//{
					//	$GLOBALS['db']->query("DELETE FROM ecs_cart WHERE session_id='".SESS_ID."' AND is_gift='".$act_id."' AND rec_id <> ".$saved_rec_id['rec_id']);
					//}
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
	$sqlf    = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time` <= '$now' AND `end_time` >= '$now' AND act_type=0 AND shop_id = 2";
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
				$gift_ds  = $bv['selectds'];//赠品是否要选择度数
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
 * yi:更新购物车中的商品数量(b2b只有左眼) *只能用user_id判断
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
        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code`,`goods_price`,`rec_type`,`goods_number` FROM" .
        $GLOBALS['ecs']->table('cart'). " WHERE rec_id='$key' AND user_id='" . $_SESSION['user_id'] . "'";
        $goods = $GLOBALS['db']->getRow($sql);
		$restr = $goods['goods_price'];
      
        /*
        $goods['goods_number']            该商品本条rec_id的现有数量
        $num2                             该商品不同记录的总数量
        $zb                               该商品本条rec_id的更新数量
        $num3                             非此条rec_id的所有数量
        $num                              计算批发价格的数量
        */
        if($goods['rec_type'] == 5){//批发价格计算
            //查询该商品批发信息
            
            $wholesale_arr = b2b_wholesale_info($goods['goods_id']);
            
            $num2 = $GLOBALS['db']->getRow("SELECT  sum(goods_number) AS num2  FROM ".$GLOBALS['ecs']->table('cart').
            " WHERE user_id='" . $_SESSION['user_id'] . "' AND goods_id =".$goods['goods_id']);
            $num3 = $GLOBALS['db']->getOne("SELECT  sum(goods_number) AS num3  FROM ".$GLOBALS['ecs']->table('cart').
            " WHERE user_id='" . $_SESSION['user_id'] . "' AND goods_id =".$goods['goods_id']." AND rec_id != '$key'");
                                      
            $num = $num3 + $zb;
            
            foreach($wholesale_arr as $k =>$v)
            {
                if($num >= $v['quantity']){
                    $goods_price = $v['price'];
                }
            }
            if(!$goods_price){
                $goods_price = $wholesale_arr[0]['price'];
            }
            $restr = $goods_price;
            
        }else{
            $attr_id    = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);
            $goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);
            $restr = $goods_price;
        }
        

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

        if ($val > 0)
        {
//----------------------更新购物车中的商品数量--------------------------------
           
                /* 更新购物车中的商品数量 */
                if($zb>0){
					$sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val',zcount='".$zb.
                        "', goods_price = '$goods_price' WHERE rec_id='$key' AND user_id='" . $_SESSION['user_id'] . "' AND shop_id = 2";
                    
                    //更新同产品的所有价格
                    $GLOBALS['db']->query("UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_price = '$goods_price' WHERE  user_id='" . $_SESSION['user_id'] . "' AND goods_id =".$goods['goods_id']
                        ." AND rec_type = '".$goods['rec_type']."' AND shop_id = 2");
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
                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "' AND shop_id = 2";
        }

        $GLOBALS['db']->query($sql);
    }

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
    $row = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id='$id' AND shop_id = 2 limit 1;");
    if($row)
    {
        if($_SESSION['user_id'] > 0){
            //删除普通商品，同时删除其配件
            if($row['parent_id'] == 0 && $row['is_gift'] == 0)
            {
    	            $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='".$_SESSION['user_id']."' ".
    	                   "AND (rec_id='$id' or parent_id='$row[goods_id]') AND shop_id = 2";
            }
            else//删除非普通商品，只删除该商品即可
            {
    	            $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='".$_SESSION['user_id']."' AND rec_id='$id' AND shop_id = 2 limit 1;";
            }
        }else{
            //删除普通商品，同时删除其配件
            if($row['parent_id'] == 0 && $row['is_gift'] == 0)
            {
    	            $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' ".
    	                   "AND (rec_id='$id' or parent_id='$row[goods_id]') AND shop_id = 2";
            }
            else//删除非普通商品，只删除该商品即可
            {
    	            $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_id='$id' AND shop_id = 2 limit 1;";
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
	$bonus     = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table("bonus_type")." where type_id= ".$bonus_type_id);
	
	//yi:促销商品能否作用于红包金额累计
	if(!$bonus['cx_can_use'])
	{
		foreach($tmp_carts as $k1 => $v1)
		{
			if($GLOBALS['total']['discount']>0 || $v1['is_cx'] || $v1['extension_code']=='package_buy' || $v1['extension_code']=='tuan_buy' || $v1['extension_code']=='miaosha_buy' || $v1['extension_code']=='exchange_buy' || $v1['extension_code']=='exchange')
			{
				unset($tmp_carts[$k1]);
			}
		}
	}
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
			$sql = "SELECT SUM(goods_price*goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='".$_SESSION['user_id']."' 
             and is_gift=0 and extension_code<>'package_buy' and extension_code<>'tuan_buy' and extension_code<>'miaosha_buy' 
             and extension_code<>'exchange_buy' and extension_code<>'exchange' and shop_id = 2 ";
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
	$same_gif = $GLOBALS['db']->GetAll("SELECT * FROM ecs_cart a WHERE a.session_id='".SESS_ID."' AND a.shop_id = 2 
					AND (a.session_id, a.goods_id, a.goods_number, a.goods_attr, a.extension_code, a.parent_id, a.is_gift, a.goods_attr_id, a.zselect, a.zcount, a.yselect, a.ycount) 
					IN (SELECT session_id, goods_id, goods_number, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, zcount, yselect, ycount FROM ecs_cart WHERE shop_id = 2 
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
				$same_goods = $GLOBALS['db']->GetAll("SELECT * FROM ecs_cart a WHERE a.session_id='".SESS_ID."' AND a.shop_id = 2 AND a.goods_id='".$gv."'
					AND (a.session_id, a.goods_id, a.goods_number, a.goods_attr, a.extension_code, a.parent_id, a.is_gift, a.goods_attr_id, a.zselect, a.zcount, a.yselect, a.ycount) 
					IN (SELECT session_id, goods_id, goods_number, goods_attr, extension_code, parent_id, is_gift, goods_attr_id, zselect, zcount, yselect, ycount FROM ecs_cart WHERE shop_id = 2 
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
						$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_id='".$v_rec."' AND shop_id = 2 limit 1;");
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
				$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND rec_id='".$v_rec."' AND shop_id = 2 limit 1;");
			}
			
			//更新保留记录的数量
			$GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=$total_goods_number, zcount='$total_zcount', ycount='$total_ycount' WHERE rec_id='$saved_rec_id'");
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


function getMiaosha($c_goods){
    
    foreach($c_goods as $v){
        $goods_arr[] = $v['goods_id'];
    }
    
    $req_time = $_SERVER['REQUEST_TIME'];
    $ms_info = $GLOBALS['db']->getAll("SELECT * FROM ecs_miaosha WHERE status=0 AND online_pay = 1 AND start_time <= " .$req_time. " AND end_time >= " .$req_time. " ORDER BY rec_id DESC LIMIT 1");
    foreach($ms_info as $v){
        
        if(in_array($v['goods_id'],$goods_arr)){
            $have_ms_goods = 1;
        }
    }
    
    return $have_ms_goods;
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


/**
 * 自动删除多余的加价购赠品(新整合)
 */
function del_fav_goods_jjg(){
    
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
			$fav_can_get = intval($temp_gf_arr['number']);
		}
		if($fav_can_get == 0)
		{
			continue;
		}

		//【已加入的】该活动的加价购商品数
		$fav_g_num = $db->getOne("select IFNULL(sum(goods_number),0) from ecs_cart where session_id='".SESS_ID."' and is_gift='$is_gift';");

		if($fav_g_num > $fav_can_get)
		{
			$fav_diff = floor(($fav_g_num - $fav_can_get)/$buy_number);
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
/**
 * 检查购物车保存的历史数据(新整合)
 */
function ck_history_cart(){
    	
	//取得非当天的数据
	$current_date = date('Y').'-'.date('m').'-'.date('d').' 00:00:00';
	if ($_SESSION['user_id'] > 0) {
		$sql_save = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '".$_SESSION['user_id']."' AND rec_type = 0 AND add_time < '$current_date' AND shop_id = 2 ORDER BY rec_id";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql_save = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = 0 AND add_time < '$current_date' AND shop_id = 2 ORDER BY rec_id";
		} else {
			$sql_save = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' AND rec_type = 0 AND add_time < '$current_date' AND shop_id = 2 ORDER BY rec_id";
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
			
			//礼包团购不保存
			foreach ($save_goods as $k => $v) 
			{
				if ($v['extension_code'] == 'package_buy' OR $v['extension_code'] == 'tuan_buy' OR $v['extension_code'] == 'miaosha_buy' OR $v['extension_code'] == 'unchange') $remove_k[] = $k;
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
			
			//5.移除赠品
			foreach ($save_goods as $k => $v) {
				if ($v['is_gift'] > 0) $remove_k[] = $k;
			}
			
			//6.移除无效项
			if (count($remove_k) > 0) {
				foreach ($remove_k as $v) {
					unset($save_goods[$v]);
				}
			}
			
			//7.先清空历史数据
			$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id IN (" .implode(',', $rec_id_array). ") AND shop_id = 2");
			
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
						'ds_extention' 	=> $v['ds_extention']
						//'add_time' 		=> $v['add_time']
				    );
				    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $cart_info, 'INSERT');
				}
			}
			
			 ecs_header("Location: flow.html\n");
			 exit;
			
		}
	}
    
}

function compile_str($str)
{
    $arr = array('<' => '＜', '>' => '＞');
    return strtr($str, $arr);
}
?>