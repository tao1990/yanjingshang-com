<?php
include_once '../util/Config.php';
	/**
	 *用户自定义实现对数据库的操作,获取订单的信息
	 **/
class Dto{
	/**
	 * 根据活动id和下单时间查询订单信息 
	 * @param 活动id $campaignId
	 * @param 下单时间 $date
	 * @throws Exception
	 */
	public function getOrderByOrderTime($campaignId,$orderStatTime,$orderEndTime){
	 	if (empty($campaignId) || empty($orderStatTime)||empty($orderEndTime)){
	 		throw new Exception("campaignId ,orderStatTime or orderEndTime is null", 613, "");
	 	}
		$date = date('Y-m-d H',$date);//转化成时间,到数据库的查询
		
		
		$orderlist [] = null;
	
        $order = new Order();
        $a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno = $a+$b.$c;      	

        $order -> setOrderNo($orderno);
        $order -> setOrderTime("2012-04-18 10:09:09");  // 设置下单时间
        $order -> setUpdateTime("2012-04-18 20:09:09"); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
        $order -> setCampaignId("111");                 // 测试时使用"101"，正式上线之后活动id必须要从数据库里面取
        $order -> setFeedback("NDgwMDB8dGVzdA==");
        $order -> setFare("10");                        // 设置邮费
        $order -> setFavorable("10HYQ");                   // 设置优惠券

		$orderStatus = new OrderStatus();
        $orderStatus -> setOrderNo($order -> getOrderNo());
		$orderStatus -> setOrderStatus("active");             // 设置订单状态
        $orderStatus -> setPaymentStatus("未付款");   				// 设置支付状态
        $orderStatus -> setPaymentType("在线支付(支付宝)");		// 支付方式

		$order -> setOrderStatus($orderStatus);

        $pro = new Product();                           // 设置商品集合1
        $pro -> setProductNo("1001");                   // 设置商品编号
        $pro -> setName("测试商品1");                   // 设置商品名称
        $pro -> setCategory("a");                    // 设置商品类型
        $pro -> setCommissionType("B");                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
        $pro -> setAmount("1");                         // 设置商品数量
        $pro -> setPrice("2550");                       // 设置商品价格

		$pro1 = new Product();                           // 设置商品集合1
        $pro1 -> setProductNo("1001");                   // 设置商品编号
        $pro1 -> setName("测试商品1");                   // 设置商品名称
        $pro1 -> setCategory("b");                    // 设置商品类型
        $pro1 -> setCommissionType("A");                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
        $pro1 -> setAmount("1");                         // 设置商品数量
        $pro1 -> setPrice("3100");                       // 设置商品价格

		$pro2 = new Product();                           // 设置商品集合1
        $pro2 -> setProductNo("1004");                   // 设置商品编号
        $pro2 -> setName("测试商品1");                   // 设置商品名称
        $pro2 -> setCategory("c");                    // 设置商品类型
        $pro2 -> setCommissionType("A");                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
        $pro2 -> setAmount("1");                         // 设置商品数量
        $pro2 -> setPrice("3000");                       // 设置商品价格



 

        $products = array($pro,$pro1,$pro2);    // 实现商品信息集合

		$order->setProducts($products);

		$order1 = new Order();
        $a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno1 = $a.$b.$c;      	

        $order1 -> setOrderNo($orderno1);
        $order1 -> setOrderTime("2012-04-18 10:09:09");  // 设置下单时间
        $order1 -> setUpdateTime("2012-04-18 20:09:09"); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
        $order1 -> setCampaignId("111");                 // 测试时使用"101"，正式上线之后活动id必须要从数据库里面取
        $order1 -> setFeedback("NDgwMDB8dGVzdA");
        $order1 -> setFare("30");                        // 设置邮费
        $order1 -> setFavorable("30YHQ");                   // 设置优惠券
		$order1 -> setFavorableCode("30YHM"); 
		$order1 -> setOrderStatus("active");             // 设置订单状态
        $order1 -> setPaymentStatus("1");   				// 设置支付状态
        $order1 -> setPaymentType("支付宝");		// 支付方式



		$pro3 = new Product();                           // 设置商品集合1
        $pro3 -> setProductNo("1888");                   // 设置商品编号
        $pro3 -> setName("测试商品2");                   // 设置商品名称
        $pro3 -> setCategory("d");                    // 设置商品类型
        $pro3 -> setCommissionType("A");                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
        $pro3 -> setAmount("2");                         // 设置商品数量
        $pro3 -> setPrice("3000");                       // 设置商品价格

		 $products1 = array($pro3);    // 实现商品信息集合

		 $order1 -> setProducts($products1);

       

		$orderlist[0]=$order;
		$orderlist[1]=$order1;
		//echo json_encode($orderlist);
		
	 	return $orderlist;
	}
	
	/**
	 * 根据活动id和订单更新时间查询订单信息
	 * @param 活动id $campaignId
	 * @param 订单更新时间 $date
	 */
	public function getOrderByUpdateTime($campaignId,$updateStatTime,$updateEndTime){
	 	if (empty($campaignId) || empty($updateStatTime)||empty($updateEndTime)){
	 		throw new Exception("CampaignId or date is null!", 648, "");
	 	}
	    
	 	$orderStatusList [] = null;
		$a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno = $a+$b.$c; 
	    $orderStatus = new OrderStatus();
        $orderStatus -> setOrderNo($orderno);
		$orderStatus -> setUpdateTime("2012-04-18 20:09:09"); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
		$orderStatus -> setFeedback("NDgwMDB8dGVzdA");
		$orderStatus -> setOrderStatus("active");             // 设置订单状态
        $orderStatus -> setPaymentStatus("pay");   				// 设置支付状态
        $orderStatus -> setPaymentType("1");		// 支付方式


		//$a = rand(0,999999);
        //$b = rand(1,100000);
       // $c = rand(0,999999);
    	//$orderno = $a+$b.$c;
		$orderStatus1 = new OrderStatus();
        $orderStatus1 -> setOrderNo('376557338653');
		$orderStatus -> setFeedback("NDgwMDB8dGVzdA==");
		$orderStatus1 -> setUpdateTime("2012-04-18 20:09:09");
		$orderStatus1 -> setOrderStatus("已完成");             // 设置订单状态
        $orderStatus1 -> setPaymentStatus("已付款");   				// 设置支付状态
        $orderStatus1 -> setPaymentType("在线支付(支付宝)");		// 支付方式


		$orderStatusList[0]=$orderStatus;
		$orderStatusList[1]=$orderStatus1;

		//echo json_encode($orderlist);
		
	 	return $orderStatusList;
	}
	
 }
?>