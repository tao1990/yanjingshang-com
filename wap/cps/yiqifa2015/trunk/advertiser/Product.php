<?php
/**
 * ��Ʒ��Ϣ��
 * 
 * ==================================================================================================
 * �������£�
 * 	1.��Ʒ��ţ�productNo
 * 	2.��Ʒ���ƣ�name
 * 	3.��Ʒ������amount
 * 	4.��Ʒ�۸�price
 * 	5.��Ʒ���category
 * 	6.Ӷ�����ͣ�commissionType
 * ==================================================================================================
 * @author lsj
 * @access public
 * @see advertiser.Order
 * @version 0.2.0
 */

class Product {
	
	public $productNo;
	public $name;
	public $amount;
	public $price;
	public $category;
	public $commissionType;
	
	

	function Product(){}
	
	public function setProductNo($productNo) {
		$this->productNo = $productNo;
	}
	
	public function getProductNo() {
		return $this->productNo;
	}

	public function getName() {
		return $this->name;
	}

	public function getAmount() {
		return $this->amount;
	}

	public function getPrice() {
		return $this->price;
	}

	public function getCategory() {
		return $this->category;
	}

	public function getCommissionType() {
		return $this->commissionType;
	}

	public function setName($name) {
		$this->name = $name;
	
	}

	public function setAmount($amount) {
		$this->amount = $amount;
	}

	public function setPrice($price) {
		$this->price = $price;
	}

	public function setCategory($category) {
		$this->category = $category;
	}

	public function setCommissionType($commissionType) {
		$this->commissionType = $commissionType;
	}
}

?>