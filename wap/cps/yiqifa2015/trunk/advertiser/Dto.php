<?php
include_once '../util/Config.php';
	/**
	 *�û��Զ���ʵ�ֶ����ݿ�Ĳ���,��ȡ��������Ϣ
	 **/
class Dto{
	/**
	 * ���ݻid���µ�ʱ���ѯ������Ϣ 
	 * @param �id $campaignId
	 * @param �µ�ʱ�� $date
	 * @throws Exception
	 */
	public function getOrderByOrderTime($campaignId,$orderStatTime,$orderEndTime){
	 	if (empty($campaignId) || empty($orderStatTime)||empty($orderEndTime)){
	 		throw new Exception("campaignId ,orderStatTime or orderEndTime is null", 613, "");
	 	}
		$date = date('Y-m-d H',$date);//ת����ʱ��,�����ݿ�Ĳ�ѯ
		
		
		$orderlist [] = null;
	
        $order = new Order();
        $a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno = $a+$b.$c;      	

        $order -> setOrderNo($orderno);
        $order -> setOrderTime("2012-04-18 10:09:09");  // �����µ�ʱ��
        $order -> setUpdateTime("2012-04-18 20:09:09"); // ���ö�������ʱ�䣬���û���µ�ʱ�䣬Ҫ��ǰ�Խ�����ǰ˵��
        $order -> setCampaignId("111");                 // ����ʱʹ��"101"����ʽ����֮��id����Ҫ�����ݿ�����ȡ
        $order -> setFeedback("NDgwMDB8dGVzdA==");
        $order -> setFare("10");                        // �����ʷ�
        $order -> setFavorable("10HYQ");                   // �����Ż�ȯ

		$orderStatus = new OrderStatus();
        $orderStatus -> setOrderNo($order -> getOrderNo());
		$orderStatus -> setOrderStatus("active");             // ���ö���״̬
        $orderStatus -> setPaymentStatus("δ����");   				// ����֧��״̬
        $orderStatus -> setPaymentType("����֧��(֧����)");		// ֧����ʽ

		$order -> setOrderStatus($orderStatus);

        $pro = new Product();                           // ������Ʒ����1
        $pro -> setProductNo("1001");                   // ������Ʒ���
        $pro -> setName("������Ʒ1");                   // ������Ʒ����
        $pro -> setCategory("a");                    // ������Ʒ����
        $pro -> setCommissionType("B");                 // ����Ӷ�����ͣ��磺��ͨ��Ʒ Ӷ�������10%��Ӷ���ţ������ж���Ȼ��֪ͨ˫������A
        $pro -> setAmount("1");                         // ������Ʒ����
        $pro -> setPrice("2550");                       // ������Ʒ�۸�

		$pro1 = new Product();                           // ������Ʒ����1
        $pro1 -> setProductNo("1001");                   // ������Ʒ���
        $pro1 -> setName("������Ʒ1");                   // ������Ʒ����
        $pro1 -> setCategory("b");                    // ������Ʒ����
        $pro1 -> setCommissionType("A");                 // ����Ӷ�����ͣ��磺��ͨ��Ʒ Ӷ�������10%��Ӷ���ţ������ж���Ȼ��֪ͨ˫������A
        $pro1 -> setAmount("1");                         // ������Ʒ����
        $pro1 -> setPrice("3100");                       // ������Ʒ�۸�

		$pro2 = new Product();                           // ������Ʒ����1
        $pro2 -> setProductNo("1004");                   // ������Ʒ���
        $pro2 -> setName("������Ʒ1");                   // ������Ʒ����
        $pro2 -> setCategory("c");                    // ������Ʒ����
        $pro2 -> setCommissionType("A");                 // ����Ӷ�����ͣ��磺��ͨ��Ʒ Ӷ�������10%��Ӷ���ţ������ж���Ȼ��֪ͨ˫������A
        $pro2 -> setAmount("1");                         // ������Ʒ����
        $pro2 -> setPrice("3000");                       // ������Ʒ�۸�



 

        $products = array($pro,$pro1,$pro2);    // ʵ����Ʒ��Ϣ����

		$order->setProducts($products);

		$order1 = new Order();
        $a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno1 = $a.$b.$c;      	

        $order1 -> setOrderNo($orderno1);
        $order1 -> setOrderTime("2012-04-18 10:09:09");  // �����µ�ʱ��
        $order1 -> setUpdateTime("2012-04-18 20:09:09"); // ���ö�������ʱ�䣬���û���µ�ʱ�䣬Ҫ��ǰ�Խ�����ǰ˵��
        $order1 -> setCampaignId("111");                 // ����ʱʹ��"101"����ʽ����֮��id����Ҫ�����ݿ�����ȡ
        $order1 -> setFeedback("NDgwMDB8dGVzdA");
        $order1 -> setFare("30");                        // �����ʷ�
        $order1 -> setFavorable("30YHQ");                   // �����Ż�ȯ
		$order1 -> setFavorableCode("30YHM"); 
		$order1 -> setOrderStatus("active");             // ���ö���״̬
        $order1 -> setPaymentStatus("1");   				// ����֧��״̬
        $order1 -> setPaymentType("֧����");		// ֧����ʽ



		$pro3 = new Product();                           // ������Ʒ����1
        $pro3 -> setProductNo("1888");                   // ������Ʒ���
        $pro3 -> setName("������Ʒ2");                   // ������Ʒ����
        $pro3 -> setCategory("d");                    // ������Ʒ����
        $pro3 -> setCommissionType("A");                 // ����Ӷ�����ͣ��磺��ͨ��Ʒ Ӷ�������10%��Ӷ���ţ������ж���Ȼ��֪ͨ˫������A
        $pro3 -> setAmount("2");                         // ������Ʒ����
        $pro3 -> setPrice("3000");                       // ������Ʒ�۸�

		 $products1 = array($pro3);    // ʵ����Ʒ��Ϣ����

		 $order1 -> setProducts($products1);

       

		$orderlist[0]=$order;
		$orderlist[1]=$order1;
		//echo json_encode($orderlist);
		
	 	return $orderlist;
	}
	
	/**
	 * ���ݻid�Ͷ�������ʱ���ѯ������Ϣ
	 * @param �id $campaignId
	 * @param ��������ʱ�� $date
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
		$orderStatus -> setUpdateTime("2012-04-18 20:09:09"); // ���ö�������ʱ�䣬���û���µ�ʱ�䣬Ҫ��ǰ�Խ�����ǰ˵��
		$orderStatus -> setFeedback("NDgwMDB8dGVzdA");
		$orderStatus -> setOrderStatus("active");             // ���ö���״̬
        $orderStatus -> setPaymentStatus("pay");   				// ����֧��״̬
        $orderStatus -> setPaymentType("1");		// ֧����ʽ


		//$a = rand(0,999999);
        //$b = rand(1,100000);
       // $c = rand(0,999999);
    	//$orderno = $a+$b.$c;
		$orderStatus1 = new OrderStatus();
        $orderStatus1 -> setOrderNo('376557338653');
		$orderStatus -> setFeedback("NDgwMDB8dGVzdA==");
		$orderStatus1 -> setUpdateTime("2012-04-18 20:09:09");
		$orderStatus1 -> setOrderStatus("�����");             // ���ö���״̬
        $orderStatus1 -> setPaymentStatus("�Ѹ���");   				// ����֧��״̬
        $orderStatus1 -> setPaymentType("����֧��(֧����)");		// ֧����ʽ


		$orderStatusList[0]=$orderStatus;
		$orderStatusList[1]=$orderStatus1;

		//echo json_encode($orderlist);
		
	 	return $orderStatusList;
	}
	
 }
?>