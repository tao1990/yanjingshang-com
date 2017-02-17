<?php
include_once 'Order.php';
include_once 'OrderStatus.php';
include_once 'Product.php';
include_once 'Service.php';
include_once '../util/Config.php';
/**
 * ʵʱ�ӿ���
 *
 * ==============================================================================================================================================
 * ˵����
 * 		�ͻ���������ƹ�Ĺ���������,��ʱ��Ҫ��ʱ���øýӿ�������push(����)����
 * 		����������ʱ�ȶ������������㽫����չ��
 * ==============================================================================================================================================
 * ʾ����
 * 		$sender = new Sender();
 * 		$sender -> send();
 * ==============================================================================================================================================
 * @author lsj
 * @package advertiser
 * @see advertiser.Order
 * @see advertiser.Product
 * @license 
 * @version 0.2
 */
 class Sender { 	
 	
 	/** ���𷢽ӿڵ�ַ */
	private $receiverPath = "http://o.yiqifa.com/servlet/handleCpsInterIn";
 	
	/** �������ͽ��,0,��ʾ��������.*/
	private static $SEND_STATUS_SUCCESS = 0;
	 
	/** �������ͽ��, 1��ʾȱ�ٱ�Ҫ�Ĳ��� */
	private static $SEND_STATUS_LACK_PARAMETERS = 1;
	
	/** �������ͽ��. 2��ʾ���͵�ַ����.*/
	private static $SEND_STATUS_TCP_ERROR= 2;
	
	/**	�������ͽ��,3��ʾ���ӳ�ʱ.*/
	private static $SEND_STATUS_TIMEOUT  = 3;
		
	/** �������ͽ��,4��ʾURL��ʽ����. */
	private static $SEND_STATUS_URL_ERRO = 4;
	
	/** �������ͽ��, 5��ʾIO�쳣.*/	
	private static $SEND_STATUS_IO_ERRO  = 5;
	
	/** �������ͽ��,-1��ʾ����ʧ��. 	*/
	private static $SEND_STATUS_OTHER_ERRO = -1;
	
	/** ���ӳ�ʱ��ʱ��.	*/
	private static $CONNECT_TIMEOUT = 3000;
	
	/** ��Ӧʱ�� */
	private static $READ_TIMEOUT 	= 3000;
		
	/** ������*/
	private $config = null;
	
	/** ����*/
	private $order  = NULL;
	
 	function Sender(){
		 $this->config = new Config();
		 $this->order  = new Order();
		 $this->orderStatus  = new OrderStatus();
	}

	public function getOrder() {
		return $this->order;
	}

	public function setOrder($order) {
		$this->order = $order;
	}

	public function getOrderStatus() {
		return $this->orderStatus;
	}

	public function setOrderStatus($orderStatus) {
		$this->orderStatus = $orderStatus;
	}
	

	/***************************************************************************
	 * pushʵʱ���ݷ���,�˷������ж�һЩ����Ĳ����Ƿ�Ϊ�գ����Ϊ�ս���������ֹ
	 * �������ȡcookie������sendParameters����������ӣ������𷢷�������.
	 * @license 
	 * @see sendParameters
	 * @version 1.0.1
	 ****************************************************************************/	
 	function sendOrder() { 	
		if ($this->order == null) {
			return self::$SEND_STATUS_LACK_PARAMETERS;
		}		
		
		if (count($this -> order -> getProducts()) == 0) {
			return self::$SEND_STATUS_LACK_PARAMETERS;
		}

		if (strlen($this -> order -> getCampaignId())== 0) {
			$cid = $this -> config -> getString("default_campaign_id");
			$this -> order -> setCampaignId($cid);
		}
		
		// ����һ��curl�ػ�
		$ch = curl_init();

		// ��ȡ�������ݵĵ�ַ
		$sendURL = $this -> sendURLByJSONOrder();

        curl_setopt($ch,CURLOPT_URL,$sendURL);
     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// ����POST��ʽ��������
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendURL);
		$fs = curl_exec($ch);
		curl_close($ch);

		return $sendURL;

	
		/*if ($fs == 0){
			return self::$SEND_STATUS_SUCCESS;
		}else if ($fs == 1){
			return self::$SEND_STATUS_URL_ERRO;
		}else if ($fs == 2){
			return self::SEND_STATUS_LACK_PARAMETERS;
		}*/
 	}
    
	function sendOrderStatus(){
	
		
		// ����һ��curl�ػ�
		$ch = curl_init();

		// ��ȡ�������ݵĵ�ַ
		$sendURL = $this -> sendURLByJSONOrderStatus();
		//header("Location: ".$sendURL);
        curl_setopt($ch,CURLOPT_URL,$sendURL);
     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// ����POST��ʽ��������
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendURL);
		$fs = curl_exec($ch);
		curl_close($ch);

		return $sendURL;
	
 	}
	
    /*************************
	 *������Ϣ������װ��Ϊjson�ĸ�ʽ����
	 **************************/
	function sendURLByJSONOrder(){


		$sb = $this->receiverPath . "?" ."interId=".$this ->config -> getString("interId"). "&json=".urlencode("{\"orders\":[" .$this->JSON($this->object_to_array($this->order))."]}")."&encoding=".$this->config->getString("default_charset");

		return $sb;
		//echo $sb;
		//exit;
	
	}
	 /*************************
	 *����״̬������װ��Ϊjson�ĸ�ʽ����
	 **************************/
		function sendURLByJSONOrderStatus(){

		//$this->order->setEncoding($this->config->getString("default_charset"));
		
		$sb = $this->receiverPath . "?" ."interId=".$this ->config -> getString("interId"). "&json=".urlencode("{\"orderStatus\":[" .$this->JSON($this->object_to_array($this->orderStatus))."]}")."&encoding=".$this->config->getString("default_charset");

		//return $sb;
		echo $sb;
		exit;
	}
	function object_to_array($obj)
	{
	    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		foreach ($_arr as $key => $val)
		{
			$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
			$arr[$key] = $val;
		}
		return $arr;
	}
	/**************************************************************
	 *
	 *	ʹ���ض�function������������Ԫ��������
	 *	@param	string	&$array		Ҫ������ַ���
	 *	@param	string	$function	Ҫִ�еĺ���
	 *	@return boolean	$apply_to_keys_also		�Ƿ�ҲӦ�õ�key��
	 *	@access public
	 *
	 *************************************************************/
	function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
		static $recursive_counter = 0;
		if (++$recursive_counter > 1000) {
			die('possible deep recursion attack');
		}
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
			} else {
				$array[$key] = $function($value);
			}
	 
			if ($apply_to_keys_also && is_string($key)) {
				$new_key = $function($key);
				if ($new_key != $key) {
					$array[$new_key] = $array[$key];
					unset($array[$key]);
				}
			}
		}
		$recursive_counter--;
	}
 
	/**************************************************************
	 *
	 *	������ת��ΪJSON�ַ������������ģ�
	 *	@param	array	$array		Ҫת��������
	 *	@return string		ת���õ���json�ַ���
	 *	@access public
	 *
	 *************************************************************/
	function JSON($array) {
		$this->arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
	}

	/********************************************************
	 * ��ȡ�������ļ�"yiqifa-config.php"��cookie������ͬ��cookie��
	 * 
	 * @param cookieName
	 * @return ���ط�������cookie��ֵ
	 *******************************************************/
	function getCookieValue($cookieName) {							
		return $_COOKIE[$cookieName];
	}
 }
?>