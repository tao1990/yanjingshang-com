<?php
/**
 * 工商银行直连支付
 * @author Xuyizhi 2014-03-24
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/icbc.php';

if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}

require_once("java/Java.inc");

class icbc {
	
	/**
     * 生成支付代码
     * @param $order
     * @param $payment
     */
    function get_code($order, $payment)
    {
    	date_default_timezone_set('PRC');
    	
    	$merID 		= '1001EC24075824';			//商城代码
    	$merAcct	= '1001331619300000458';	//企业帐号
    	$password	= "12345678";				//证书密匙
    	//$addTime	= date('YmdHis', $order['add_time']); //交易时间
    	$addTime	= date('YmdHis', time()); //交易时间
    	$amount		= $order['order_amount'] * 100; //订单金额(单位:分)
    	
    	$strKey = 'sZrLozDnF260MF9e';			//商户自定义密匙
    	//$merVAR = $order['order_sn']."||".md5($strKey.$order['order_sn']);		//商家自定义参数,内容是:订单号 + md5(商户密匙+订单号)
    	$merVAR = $order['order_sn']."_".md5($strKey.$order['order_sn']);
		
    	$tranData = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"no\"?><B2CReq><interfaceName>ICBC_PERBANK_B2C</interfaceName><interfaceVersion>1.0.0.11</interfaceVersion><orderInfo><orderDate>".$addTime."</orderDate><curType>001</curType><merID>".$merID."</merID><subOrderInfoList><subOrderInfo><orderid>".$order['order_sn']."</orderid><amount>".$amount."</amount><installmentTimes>1</installmentTimes><merAcct>".$merAcct."</merAcct><goodsID></goodsID><goodsName>www.easeeyes.com</goodsName><goodsNum></goodsNum><carriageAmt></carriageAmt></subOrderInfo></subOrderInfoList></orderInfo><custom><verifyJoinFlag>0</verifyJoinFlag><Language>ZH_CN</Language></custom><message><creditType>2</creditType><notifyType>HS</notifyType><resultType>1</resultType><merReference>*.easeeyes.com</merReference><merCustomIp></merCustomIp><goodsType>1</goodsType><merCustomID></merCustomID><merCustomPhone></merCustomPhone><goodsAddress></goodsAddress><merOrderRemark></merOrderRemark><merHint></merHint><remark1>1</remark1><remark2>2</remark2><merURL>http://www.easeeyes.com/respond_icbc.php</merURL><merVAR>".$merVAR."</merVAR></message></B2CReq>";
    	
    	$sign = new java("com.icbc.b2c.Signature");
		
		$tranData_base64 = $sign->tranDataBase64($tranData); //订单数据BASE64编码
		$signMsgBase64 = $sign->signMsgBase64($tranData, "/data/www/icbc/Easeeyes.key", $password); //签名信息base64编码
		$certBase64 = $sign->certBase64($tranData, "/data/www/icbc/Easeeyes.key", "/data/www/icbc/Easeeyes.crt", $password); //证书信息base编码
		
		$def_url  = '<div style="text-align:center">';
		$def_url .= '<FORM id="FORM1" name="FORM1" action="https://B2C.icbc.com.cn/servlet/ICBCINBSEBusinessServlet" method="post">';
		$def_url .=	'<INPUT TYPE="hidden" ID="interfaceName" NAME="interfaceName" value="ICBC_PERBANK_B2C" >';
		$def_url .=	'<INPUT TYPE="hidden" ID="interfaceVersion" NAME="interfaceVersion" value="1.0.0.11" >';
		$def_url .=	'<INPUT TYPE="hidden" ID="tranData" NAME="tranData" value="'.$tranData_base64.'" >';
		$def_url .=	'<INPUT TYPE="hidden" ID="merSignMsg" NAME="merSignMsg" value="'.$signMsgBase64.'">';
		$def_url .=	'<INPUT TYPE="hidden" ID="merCert" NAME="merCert" value="'.$certBase64.'">';
		//$def_url .=	'<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_101.gif" value="">';//这种按钮工行不行，用下面
		$def_url .= '<INPUT TYPE="submit" value=" " class="cart_end_bt" border="0" style="width:180px; height=40px; border:none; background:url(http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_101.gif) top center no-repeat; cursor:pointer" >';
		$def_url .=	'</FORM>';
		$def_url .= '</div>';

		return $def_url;
    }
    
	/**
     * 支付结果操作
     */
    function respond($log_id=0)
    {
    	$strKey			= 'sZrLozDnF260MF9e';				//商户自定义密匙
    	$notifyData		= trim($_REQUEST['notifyData']);	//交易数据base64编码
    	$signMsg		= trim($_REQUEST['signMsg']);		//签名信息base64编码
    	$merVAR			= trim($_REQUEST['merVAR']);		//商家自定义参数，内容是订单号 + md5(商户密匙+订单号)
    	$bankCertPath	= "/data/www/icbc/ebb2cpublic.crt";	//银行证书文件路径
    	//echo $notifyData.'--'.$signMsg.'--'.$merVAR;exit;
    	
    	$verifyNotify = new java("com.icbc.b2c.Signature");
    	$result = $verifyNotify->verifyNotify($notifyData, $signMsg, $bankCertPath);
    	
    	if ($result == 0) 
    	{
    		//支付成功,验证签名
    		if ( ! empty($merVAR) )
    		{
    			//$sign_arr = explode('||', $merVAR);
    			$sign_arr = explode('_', $merVAR);
    			if ($sign_arr[1] == md5($strKey.$sign_arr[0]))
    			{
    				//验证通过
    				order_paid($log_id);
    				return true;
    			}
    			else
    			{
    				return false;
    			}
    		}
    		else
    		{
    			return false;
    		}
    	} 
    	else 
    	{
    		return false;
    	}
    }
	
}