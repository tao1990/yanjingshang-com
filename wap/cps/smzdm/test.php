<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(dirname(__FILE__) . '/cps/smzdm/post_order.class.php');

$arr= array('2016110973717','2016111034803','2016111083923');
foreach($arr as $v){
        $order_id = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE order_sn = ".$v);
    
    if(!empty($order_id)){
        $yqf = new post_order();
        $referer = $yqf->get_order_info2($order_id,'46_0_184');//获取订单信息
        $dm_url  = $referer['url'];
        $post_data = array('key'=>$referer['key'],'order'=>$referer['order']);
        
        //$post_data = $yqf->get_orders($order_id);
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $dm_url);    // 设置你准备提交的URL
        		curl_setopt($curl, CURLOPT_POST, true);  // 设置POST方式提交
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//判断是否接收返回值，0：不接收，1：接收
		        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                $data = curl_exec($curl); // 运行curl，请求网页, 其中$data为接口返回内容
        
        print_r($data);
                curl_close($curl);        // 关闭curl请求
    }
}

?>