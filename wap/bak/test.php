<?php
define('IN_ECS', true);
date_default_timezone_set ('Asia/Shanghai'); 
header("Content-type: text/html; charset=utf-8");

require_once('../includes/init.php');

//require_once('leading_in_order.php');
//require_once('edb_order_update.php');
//require_once('class_add.php');
//require_once('brand_add.php');
//require_once('goods_add_all.php');
require_once('goods_add.php');
//require_once('goods_add_tuan.php');
//require_once('goods_update.php');
//require_once('class_add.php');
//require_once('edb_trade_get.php');
//require_once('class_add_tz.php');
//require_once('tz_excel.php');
//require_once('leading_in_order_one.php');
//leading_in_order_one(245396);
//edb_class_add(242);
//tz_excel();
//edb_class_add_tz();
//edb_trade_get();
//edb_order_update('237563',2);
//edb_leading_in_order();
//edb_brand_add(156);
//edb_class_add(240);
edb_goods_add(5554);die;
edb_goods_add(5556);
edb_goods_add(5557);
die;
edb_goods_add(5489);
edb_goods_add(5488);
edb_goods_add(5487);
edb_goods_add(5486);
//edb_goods_add_all();
//goods_add_tuan(137);
//edb_goods_update(4617);
?>