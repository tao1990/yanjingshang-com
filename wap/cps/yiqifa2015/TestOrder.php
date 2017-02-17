<?php
define('IN_ECS', true);
require('../../includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

$domain_name = $_SERVER['SERVER_NAME'];
if (strstr($domain_name, 'ysyj'))
{
	header("Location: ysyj.php \n"); exit;
}
require_once('post_order.class.php');
		$yqf = new post_order();
		$yqf_url = $yqf->get_order_info(73156);//获取订单信息