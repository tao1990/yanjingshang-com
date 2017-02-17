<?php
define('IN_ECS', true);
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once("icbc.php");
$order = Array("shipping_id" => "9", "pay_id" => "16", "pack_id" => "0", "card_id" => "0", "card_message" =>"", "surplus" => "0", "integral" => "0", "bonus_id" => "0", "need_inv" => "0", "inv_type" =>"", "inv_payee" =>"", "inv_content" =>"", "postscript" =>"", "how_oos" => "等待所有商品备齐后再发", "need_insure" => "0", "user_id" => "18356", "add_time" => "1389075436", "order_status" => "0", "shipping_status" => "0", "pay_status" => "0", "agency_id" => "0", "extension_code" =>"", "extension_id" => "0", "address_id" => "6416", "address_name" =>"", "consignee" => "wangyang", "email" => "1507880808@qq.com", "country" => "1", "province" => "3", "city" => "36", "district" => "398", "address" => "安徽安庆迎江区", "zipcode" =>"", "tel" => "15839874569", "mobile" =>"", "sign_building" =>"", "best_time" =>"", "provincena" => "安徽", "cityna" => "安庆", "districtna" => "迎江区", "bonus" => "0", "goods_amount" => "474", "discount" =>"", "tax" => "0", "shipping_name" => "快递", "shipping_fee" => "0", "insure_fee" => "0", "pay_name" => "工商银行网银支付", "pay_fee" => "0", "cod_fee" => "0", "pack_fee" => "0", "card_fee" => "0", "order_amount" => "474.00", "integral_money" => "0", "from_ad" => "0", "referer" => "本站", "parent_id" => "0", "order_sn" => "2014010733449", "order_id" => "8420", "log_id" => "8488", "bank_id" => "0");

$pay_obj    = new icbc();
$pay_online = $pay_obj->get_code($order, 1);
echo $pay_online;
?>