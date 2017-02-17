<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . '/includes/lib_payment.php');
require(ROOT_PATH . '/includes/lib_order.php');
include_once(ROOT_PATH . 'includes/modules/payment/wxzf_wap.php');

if (isset($_GET['success']) && $_GET['success'] == 'Y')
{
	$payment = new wxzf();
	$msg = ($payment->respond()) ? '支付成功' : '支付失败';
}else{
    $msg = '支付失败';
}

assign_template();
/*------------------------------------页头 页尾 数据---------------------------------------*/
$smarty->assign('page_title',           '易视网手机版');
$smarty->assign('ur_here',              '信息提示');
/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('message',    @$msg);
$smarty->assign('shop_url',   $ecs->url());
$smarty->assign('pay_code',   18);//支付方式代码

$smarty->display('respond.dwt');
?>

