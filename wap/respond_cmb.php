<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . '/includes/lib_payment.php');
require(ROOT_PATH . '/includes/lib_order.php');
include_once('includes/modules/payment/cmb.php');
//require_once("Java.inc");
/*
//$test = new java("cmb.netpayment.Security", '/data/www/cmb/public.key');
//echo $_SERVER['QUERY_STRING'].'<br/>';

$bytes_array = getBytes($_SERVER['QUERY_STRING']);
$sign_is_true = FALSE;

$sign_is_true = $test->checkInfoFromBank($bytes_array);

if ($sign_is_true) {
	echo 'ok';
} else {
	echo 'false';
}*/

if (isset($_GET['Succeed']) && $_GET['Succeed'] == 'Y')
{
	$payment = new cmb();
	$log_id = 0;
	$order_id = 0;
	if (isset($_GET['MerchantPara']))
	{
		$t_array = explode('||', $_GET['MerchantPara']);
		$order_sn = $t_array[0];
		if (is_numeric($order_sn))
		{
			$order_id = $GLOBALS['db']->getOne("SELECT order_id FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_sn='".$order_sn."' LIMIT 1");
			if ($order_id)
			{
				$log_id = $GLOBALS['db']->getOne("SELECT log_id FROM ".$GLOBALS['ecs']->table('pay_log')." WHERE order_id=$order_id LIMIT 1");
			}
		}
	}
	$msg = ($payment->respond($log_id)) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
	
	//--- 5.19-5.31送海里恩护理液3642
	/*if ($msg == $_LANG['pay_success'])
	{
		date_default_timezone_set('PRC');
		if ($_SERVER['REQUEST_TIME'] >= strtotime('2014-05-19 00:00:00') && $_SERVER['REQUEST_TIME'] <= strtotime('2014-05-31 23:59:59'))
		{
			//检查订单商品是否已包含该赠品
			$gift = $GLOBALS['db']->getOne("SELECT goods_id FROM ecs_order_goods WHERE order_id = ".$order_id." AND goods_name = '(招行支付赠品)海俪恩植物精灵清凉润眼型多效隐形眼镜护理液120ml' LIMIT 1");
			if (!$gift)
			{
				$sql = "INSERT INTO ecs_order_goods (order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr, is_real, extension_code) 
						VALUES (".$order_id.", 3642, '(招行支付赠品)海俪恩植物精灵清凉润眼型多效隐形眼镜护理液120ml', '003642', 1, 21.00, 0.00, '', 1, 'unchange')";
				$GLOBALS['db']->query($sql);
			}
		}
	}*/
	//--- END ---
	
	//--- 2014.03.10~2014.03.31,全场日抛满50元送5元红包,红包ID:1249
	/*if ($msg == $_LANG['pay_success'] && $_COOKIE['cmb_bonus'] != strval($order_id))
	{
		date_default_timezone_set('PRC');
		if ($_SERVER['REQUEST_TIME'] >= 1394380800 && $_SERVER['REQUEST_TIME'] <= 1396281599)
		{
			$order_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_id = $order_id LIMIT 1");
			if ($order_info)
			{
				if ($order_info['user_id'] > 0)
				{
					//获取日抛产品ID
					$arr = $GLOBALS['db']->getAll("SELECT goods_id FROM `ecs_goods_attr` WHERE (attr_id=211 OR attr_id=219) AND attr_value = '日抛'");
					$arr1 = array();
					foreach ($arr as $v)
					{
						$arr1[] = $v['goods_id'];
					}
					$goods_str = implode(',', $arr1);
					
					//获取订单中日抛商品的金额
					$goods_amount = $GLOBALS['db']->getOne("SELECT SUM(goods_number * goods_price) AS gamount FROM `ecs_order_goods` WHERE order_id = $order_id AND goods_id IN ($goods_str)");
					if ($goods_amount >= 50)
					{
						//取得一个未使用的优惠券
						$bonus_info = $GLOBALS['db']->getRow("SELECT * FROM `ecs_user_bonus` WHERE bonus_type_id=1249 AND user_id=0 AND order_id=0 LIMIT 1");
						if ($bonus_info)
						{
							$GLOBALS['db']->query("UPDATE `ecs_user_bonus` SET user_id = " .$order_info['user_id']. " WHERE bonus_id = " .$bonus_info['bonus_id']);
							setcookie('cmb_bonus', $order_id, time()+3600*24*30, '/', '');//设置cooike
						}
					}
				}
			}
		}
	}*/
	//--- END ---
	
	//--2014.10.27-11.10 全场买200元返现15元 红包ID:1686
	if ($msg == $_LANG['pay_success'])
	{
		date_default_timezone_set('PRC');
		if ($_SERVER['REQUEST_TIME'] >= 1414339200 && $_SERVER['REQUEST_TIME'] <= 1415635200)
		{
			$order_amount = $GLOBALS['db']->getOne("SELECT order_amount FROM ".$GLOBALS['ecs']->table('pay_log')." WHERE log_id=$log_id LIMIT 1");
			$order_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_id = $order_id LIMIT 1");
			if ($order_amount >= 200 && $order_info['user_id'] > 0)
			{
				//判断是否已领取
				$get_it = $GLOBALS['db']->getOne("SELECT bonus_id FROM `ecs_user_bonus` WHERE bonus_type_id=1686 AND user_id='".$order_info['user_id']."' LIMIT 1");
				if (empty($get_it))
				{
					//取得一个未使用的优惠券
					$bonus_info = $GLOBALS['db']->getRow("SELECT * FROM `ecs_user_bonus` WHERE bonus_type_id=1686 AND user_id=0 AND order_id=0 LIMIT 1");
					if ($bonus_info)
					{
						$GLOBALS['db']->query("UPDATE `ecs_user_bonus` SET user_id = " .$order_info['user_id']. " WHERE bonus_id = " .$bonus_info['bonus_id']);
					}
				}
			}
		}
	}
	//--- End ---
}

assign_template();
/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',           '易视网手机版');
$smarty->assign('ur_here',              '信息提示');
$smarty->assign('topbanner',            ad_info(31,1));           //头部横幅广告
//页尾
$smarty->assign('helps',                get_shop_help());         //网店帮助文章
$smarty->assign('new_articles_botter',  index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',            ad_info(12,8));           //营业执照行
/*$cat_tree = get_category_tree();                     			  //分类列表
$smarty->assign('cat_1',        		$cat_tree[1]);
$smarty->assign('cat_6',				$cat_tree[6]);
$smarty->assign('cat_64',				$cat_tree[64]);
$smarty->assign('cat_76',				$cat_tree[76]);	
$smarty->assign('cat_159',				$cat_tree[159]);
$smarty->assign('cat_190',				$cat_tree[190]);*/
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('message',    @$msg);
$smarty->assign('shop_url',   $ecs->url());
$smarty->assign('pay_code',   @$pay_code);//支付方式代码

$smarty->display('respond.dwt');


/*foreach ($_GET as $k => $v) {
	echo $k . '=' .  $v.'<br/>';
}*/
//echo $_SERVER['QUERY_STRING'];
//echo 'HTTP_REFERER='.$_SERVER['HTTP_REFERER'].'<br>';
//echo 'HTTP_HOST='.$_SERVER['HTTP_HOST'].'<br>';

/*function getBytes($str) {
	$len = strlen($str);
	$bytes = array();
	   for($i=0;$i<$len;$i++) {
		   if(ord($str[$i]) >= 128){
			   $byte = ord($str[$i]) - 256;
		   }else{
			   $byte = ord($str[$i]);
		   }
		$bytes[] =  $byte ;
	}
	return $bytes;
}*/

function getBytes($str) {
    //$str = iconv('utf-8','GB2312',$str);
    $str = iconv('utf-8','UTF-16BE',$str);
    $len = strlen($str);
    $bytes = array();
    for($i=0;$i<$len;$i++) {
        $bytes[$i] = ord($str[$i]);
    }
    return $bytes;
}
?>

