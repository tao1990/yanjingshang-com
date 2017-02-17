<?php
/**
 * 工商银行直连支付接口
 * @author xuyizhi 2014-01-07
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
//$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/icbc.php';
/*
if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}
*/
//require_once(ROOT_PATH."includes/modules/payment/java/Java.inc");
require_once("Java.inc");

class icbc {
	
    /**
     * 生成支付代码
     * @param $order
     * @param $payment
     */
    function get_code($order, $payment)
    {
    	date_default_timezone_set('PRC');
		$orderdate  = 20140102112700 ;//date('YmdHis',$order['add_time'])
		$ordersn = trim($order['order_sn']);
		$orderamount = trim($order['order_amount'])*100;
		$customip = trim($this->get_ip());
		$userid = trim($order['user_id']);
		$tel = trim($order['tel']);
		$log_id = trim($order['log_id']);

		$password = "12345678";
    	$tranData = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"no\"?>
		<B2CReq><interfaceName>ICBC_PERBANK_B2C</interfaceName><interfaceVersion>1.0.0.11</interfaceVersion><orderInfo><orderDate>".$orderdate."</orderDate><curType>001</curType><merID>1001EC23725729</merID><subOrderInfoList><subOrderInfo><orderid>".$ordersn."</orderid><amount>".$orderamount."</amount><installmentTimes>1</installmentTimes><merAcct>1001215519300406213</merAcct><goodsID>100</goodsID><goodsName>easeeyes</goodsName><goodsNum>1</goodsNum><carriageAmt>10</carriageAmt></subOrderInfo></subOrderInfoList></orderInfo><custom><verifyJoinFlag>0</verifyJoinFlag><Language>ZH_CN</Language></custom><message><creditType>2</creditType><notifyType>HS</notifyType><resultType>1</resultType><merReference>www.easeeyes.com</merReference><merCustomIp>".$customip."</merCustomIp><goodsType>1</goodsType><merCustomID>".$userid."</merCustomID><merCustomPhone>".$tel."</merCustomPhone><goodsAddress>easeeyes</goodsAddress><merOrderRemark>remark</merOrderRemark><merHint>liuyan</merHint><remark1>1</remark1><remark2>2</remark2><merURL>http://www.easeeyes.com/icbc/002.php</merURL><merVAR>".$log_id."</merVAR></message></B2CReq>";
	
		$sign = new java("com.icbc.b2c.Signature");
		$tranData_base64 = $sign->tranDataBase64($tranData); //订单数据BASE64编码
		$signMsgBase64 = $sign->signMsgBase64($tranData, "/data/www/icbc/1121.key", $password); //签名信息base64编码
		$certBase64 = $sign->certBase64($tranData, "/data/www/icbc/1121.key", "/data/www/icbc/1121.crt", $password); //证书信息base编码
		
		$def_url  = '<div style="text-align:center">';
		$def_url .= '<FORM id=FORM1 name=FORM1 action="https://mybank3.dccnet.com.cn/servlet/NewB2cMerPayReqServlet" method=post">';
		$def_url .= '<font face="Arial" size="4" color="white">商户订单数据签名页面</font>';
		$def_url .=	'<INPUT ID="interfaceName" NAME="interfaceName" TYPE="hidden" value="ICBC_PERBANK_B2C" size="120" >';
		$def_url .=	'<INPUT ID="interfaceVersion" NAME="interfaceVersion" TYPE="hidden" value="1.0.0.11" size="120">';
		$def_url .=	'<INPUT ID="tranData" name="tranData" TYPE="hidden" value="'.$tranData_base64.'" size="120">';
		$def_url .=	'<INPUT ID="merSignMsg" NAME="merSignMsg" TYPE="hidden" size="120" value="'.$signMsgBase64.'">';
		$def_url .=	'<INPUT ID="merCert" NAME="merCert" TYPE="hidden" size="120" value="'.$certBase64.'">';
		$def_url .=	'<input TYPE="submit"  width="180" height="40" style="background:url(http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_101.gif);width:180px;height:40px;border: medium none;cursor: pointer;" value="">';
		$def_url .=	'</FORM>';
		$def_url .= '</div>';

		return $def_url;
    }
    
    /**
     * 支付结果操作
     */
    function respond($log_id=0)
    {
		require_once("Java.inc");
		$notifyData = trim($_REQUEST['notifyData']);	//交易数据base64编码
		$signMsg = trim($_REQUEST['signMsg']);			//签名信息base64编码
		$merVAR = trim($_REQUEST['merVAR']);			//商户自定义参数

		$bankCertPath = "/data/www/icbc/1.cer";			//银行证书文件路径
		$verifyNotify = new java("com.icbc.b2c.Signature");
		$result = $verifyNotify->verifyNotify($notifyData, $signMsg, $bankCertPath);
		if(!empty($merVAR)){
			if ($result == 0){
				order_paid($log_id);
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
    
    /*
     * 根据order_id生成10位订单号
     */
    function get_bill_no($order_id=0)
    {
		$intNum = 10 - strlen(strval($order_id));
		$str = '';
		if ($intNum > 0) {
			for ($i=0; $i<$intNum; $i++) {
				$str .= '0';
			}
		}
    	
    	return $str.strval($order_id);
    }
    
	function get_ip()
	{
	    static $realip = NULL;
	
	    if ($realip !== NULL)
	    {
	        return $realip;
	    }
	
	    if (isset($_SERVER))
	    {
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        {
	            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	
	            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
	            foreach ($arr AS $ip)
	            {
	                $ip = trim($ip);
	
	                if ($ip != 'unknown')
	                {
	                    $realip = $ip;
	
	                    break;
	                }
	            }
	        }
	        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
	        {
	            $realip = $_SERVER['HTTP_CLIENT_IP'];
	        }
	        else
	        {
	            if (isset($_SERVER['REMOTE_ADDR']))
	            {
	                $realip = $_SERVER['REMOTE_ADDR'];
	            }
	            else
	            {
	                $realip = '0.0.0.0';
	            }
	        }
	    }
	    else
	    {
	        if (getenv('HTTP_X_FORWARDED_FOR'))
	        {
	            $realip = getenv('HTTP_X_FORWARDED_FOR');
	        }
	        elseif (getenv('HTTP_CLIENT_IP'))
	        {
	            $realip = getenv('HTTP_CLIENT_IP');
	        }
	        else
	        {
	            $realip = getenv('REMOTE_ADDR');
	        }
	    }
	
	    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
	
	    return $realip;
	}
	
}