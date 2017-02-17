<?php
include_once ("Product.php");
/**
 *	������Ϣ��
 *  =======================================================================================
 *  ---------------------------------------------------------------------------------------
 * 	�����ԣ�	
 * 		1.������ţ�    orderNo
 * 		2.�µ�ʱ�䣺    orderTime
 		3.��������ʱ��: updateTime
 * 		4.�ID��	    campaignId����cookie�ж�ȡ
 * 		5.վ���ʶfeedback:	feedback,��cookie�ж�ȡ
 * 		6.�˷ѣ�		fare
 * 		7.�Żݽ�	favorable
 * 		8.�Ż��룺	favorableCode
 * 		9.����״̬��  orderStatus������״̬���뿴
 * 		10.��Ʒ��	products,��Ʒ���뿴
 * 		11.����״̬��orderStatus
 *		12.֧��״̬��paymentStatus
 * 		13.֧����ʽ��paymentType
 * 		<p>
 *  ---------------------------------------------------------------------------------------
 *  =======================================================================================
 * @author LSJ
 * @see advertiser.Product
 * @see advertiser.Sender#send() 
 * @version 0.2
 *
 */
class Order {	
	public $orderNo; 
	public $orderTime;
	public $updateTime;
	public $campaignId; 
	public $feedback; 
	public $fare; 
	public $favorable; 
	public $favorableCode; 
	public $products = null;
	public $orderStatus; 
	public $paymentStatus; 
	public $paymentType;
	
	


	public function getProducts() {
		return $this->products;
	}

	public function getOrderNo() {
		return $this->orderNo;
	}

	public function getOrderTime() {
		return $this->orderTime;
	}

	public function getCampaignId() {
		return $this->campaignId;
	}

	public function getFeedback() {
		return $this->feedback;
	}

	public function getFare() {
		return $this->fare;
	}

	public function getFavorable() {
		return $this->favorable;
	}

	public function getFavorableCode() {
		return $this->favorableCode;
	}

	public function getOrderStatus() {
		return $this->orderstatus;
	}

	public function getPaymentStatus() {
		return $this->paymentStatus;
	}

	public function getPaymentType() {
		return $this->paymentType;
	}


	public function setOrderNo($orderNo) {
		$this->orderNo = $orderNo;
	}

	public function setOrderTime($orderTime) {
		$this->orderTime = $orderTime;
	}

	public function setCampaignId($campaignId) {
		$this->campaignId = $campaignId;
	}

	public function setFeedback($feedback) {
		$this->feedback = $feedback;
	}

	public function setFare($fare) {
		$this->fare = $fare;
	}

	public function setFavorable($favorable) {
		$this->favorable = $favorable;
	}

	public function setFavorableCode($favorableCode) {
		$this->favorableCode = $favorableCode;
	}
	
	
	public function getUpdateTime() {
		return $this->updateTime;
	}

	public function setUpdateTime($updateTime) {
		$this->updateTime = $updateTime;

	}
	public function setProducts($products){
		$this->products = $products;
	}

	public function setOrderStatus($orderstatus){
		$this->orderstatus = $orderstatus;
	}
	public function setPaymentStatus($paymentStatus) {
		$this->paymentStatus = $paymentStatus;
	}

	public function setPaymentType($paymentType) {
		$this->paymentType = $paymentType;
	}
	
}
?>