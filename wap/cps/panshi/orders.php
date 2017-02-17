<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../../includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$st = strtotime("-1month");
$et = strtotime("+1day");

$all_orders = get_orders($st, $et);
//print_r($all_orders);die;
if ($all_orders) {
    $api_key = '6b51bdf531cd206f9b837217a7722344';
    $order = json_encode($all_orders);
    $key = md5($api_key.$order);
    $url = "http://www.linkstars.com/api/adv/cps/effect.php";
    $p = array('key'=>$key,'order'=>$order);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);    // 设置你准备提交的URL
    curl_setopt($curl, CURLOPT_POST, true);  // 设置POST方式提交
    curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
    $data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容

    curl_close($curl);        // 关闭curl请求
    //print_r($data);
} else {
	echo '未查询到相关数据';
}

//获取时间段内的订单列表
function get_orders($st, $et) {
	$query_sql = "SELECT order_sn,order_status,shipping_status,pay_status,zipcode FROM ". $GLOBALS['ecs']->table('order_info') ." WHERE referer='smzdm' AND add_time > ". $st ." AND add_time < ". $et ." ";
    $order_rows = $GLOBALS['db'] -> getAll($query_sql);
    $orderlist = array();
    if($order_rows) {
        foreach ($order_rows as $k => $pre_order) {
            if($pre_order['pay_status'] == PS_PAYED && $pre_order['shipping_status'] == SS_RECEIVED && !in_array($pre_order['order_status'], array(OS_RETURNED, OS_CANCELED, OS_INVALID))) {
                $status = '1';
            }elseif(in_array($pre_order['order_status'], array(OS_RETURNED, OS_CANCELED, OS_INVALID))) {
                $status = '-1';
            }else{
                $status = '0';
            }
            if($status != 0){
                //订单状态（0未知；1有效订单；-1无效订单）
                $orderlist[$k] = array (
                    'feedback' 		=>	$pre_order['zipcode'],							//网站ID
                    'order_number'	=>	$pre_order['order_sn'],							//订单编号
                    'order_status'	=>	$status,										//订单状态
                );
            }else{
                $orderlist = false;
            }
        }
        return $orderlist;
    }
}

?>