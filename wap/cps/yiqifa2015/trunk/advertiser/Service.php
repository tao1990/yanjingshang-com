<?php
include_once 'Order.php';
include_once 'OrderStatus.php';
include_once 'Product.php';
include_once '../util/Config.php';
include_once 'Dto.php';

class Service{
	private $config = null;
	
	
	function Service(){
		$this->config = new Config();
	}
	public $dto = null;
	
///////////////////////////////////JSON��ʽ�ķ���////////////////////////////////////
	/**************************************************
	 * ���ݻid���µ�ʱ���ѯ������Ϣ,���صĸ�ʽ��JSON
	 * @param �id $campaignId
	 * @param �µ�ʱ�� $date
	 **************************************************/
	public function getOrderInfoByJSON($campaignId,$orderStartTime,$orderEndTime){
		if (empty($campaignId) || empty($orderStartTime)||empty($orderEndTime)){
			throw new Exception("campaignId ,orderStartTime or orderEndTime is null!", 119, "");
		}
		$this->dto = new Dto();
		$orderlist = 0;
		$orderlist = $this -> dto -> getOrderByOrderTime($campaignId,$orderStartTime,$orderEndTime);
		
		if(!$orderlist == 0){
			echo '{"orders":';
			echo $this->JSON($this->object_to_array($orderlist));
			echo '}';
		}else{
			echo 'no data!';
		}
	
	
	}

	 /**************************************************************
	 * ���ݻid����������ʱ���ѯ����״̬�����صĸ�ʽ��JSON
	 * @param �id $campaignId
	 * @param ��������ʱ�� $date
	 *************************************************************/
public function getOrderStatusByJSON($campaignId, $updateStartTime,$updateEndTime){
		if (empty($campaignId) || empty($updateStartTime)||empty($updateEndTime)){
			throw new Exception("campaignId ,updateStartTime or updateEndTime is null!", 119);
		}
		$this->dto = new Dto();
		$orderstatuslist = 0;
		$orderstatuslist =	$this -> dto -> getOrderByUpdateTime($campaignId,$updateStartTime,$updateEndTime);
		
		if(!$orderstatuslist == 0){
			echo '{"orderStatus":';
			echo $this->JSON($this->object_to_array($orderstatuslist));
			echo '}';
		}else{
			echo 'no data!';
		}
	              	
	}
	/***************************************************
	*  ����ת�������� 
	*
	***************************************************/

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
	


}
?>