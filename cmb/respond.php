<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_payment.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
include_once('../includes/modules/payment/cmb.php');
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
	//echo $msg;
}

assign_template();
/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',           $position['title']);    
$smarty->assign('ur_here',              $position['ur_here']);  
$smarty->assign('topbanner',            ad_info(31,1));           //头部横幅广告
//页尾
$smarty->assign('helps',                get_shop_help());         //网店帮助文章
$smarty->assign('new_articles_botter',  index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',            ad_info(12,8));           //营业执照行
$cat_tree = get_category_tree();                     			  //分类列表
$smarty->assign('cat_1',        		$cat_tree[1]);
$smarty->assign('cat_6',				$cat_tree[6]);
$smarty->assign('cat_64',				$cat_tree[64]);
$smarty->assign('cat_76',				$cat_tree[76]);	
$smarty->assign('cat_159',				$cat_tree[159]);
$smarty->assign('cat_190',				$cat_tree[190]);
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $ecs->url());
$smarty->assign('pay_code',   $pay_code);//支付方式代码

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

