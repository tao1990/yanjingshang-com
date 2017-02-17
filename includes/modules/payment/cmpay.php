<?php
/* ============================================================================
 * 商城 手机支付插件【2012/8/1】【Author:yijiangwen】
 * ============================================================================
 * 手机支付即时到帐（双向确认）网关接口：
 *		1。链接到手机支付平台页面支付方式。
 *      2。网址：http://cmpay.10086.cn
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/cmpay.php';

if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}
require_once("common/callcmpay.php"); 

//支付插件 模块信息
if(isset($set_modules) && $set_modules == true)
{
    $i = isset($modules) ? count($modules) : 0;
    $modules[$i]['code']      = basename(__FILE__, '.php'); //支付方式代码
    $modules[$i]['desc']      = 'cmpay_desc';               //描述对应的语言项
    $modules[$i]['is_cod']    = '0';                        //是否支持货到付款
    $modules[$i]['is_online'] = '1';                        //是否支持在线支付
    $modules[$i]['author']    = 'YI JIANGWEN';              //作者
    $modules[$i]['website']   = 'http://cmpay.10086.cn';    //官网
    $modules[$i]['version']   = '1.0.0';                    //版本号

    //商家账户信息
    $modules[$i]['config'] = array(
        array('name' => 'cmpay_account', 'type' => 'text', 'value' => ''),
        array('name' => 'cmpay_key',     'type' => 'text', 'value' => ''),
    );
    return;
}

class cmpay
{
	//构造函数
    function cmpay()
    {
    }

    function __construct()
    {
        $this->cmpay();
    }

   /** 生成支付代码
     * @param   array   $order    全部订单信息数组
     * @param   array   $payment  全部支付方式信息
     */
   function get_code($order, $payment)
   {
		//----------------------------------------------【公共参数设置】------------------------------------------------------//
		$merchantId   = trim($payment['cmpay_account']);         //商户ID
		$key          = trim($payment['cmpay_key']);             //账号对应key
		$type         = "DirectPayConfirm";	                     //接口类型
		$reqUrl       = "https://ipos.10086.cn/ips/cmpayService";//网关
		$ipAddress    = getClientIP();                           //客户端IP
		$characterSet = '02';                                    //字符集：00-GBK，01-GB2312，02-UTF-8。
		$localAddr    = "http://www.easeeyes.com/includes/modules/payment/common";//本地测试改回localhost
		$callbackUrl  = ""; //$localAddr."/back_url.php";        //页面通知交易结果时返回到这个URL
		$notifyUrl    = return_url(basename(__FILE__, '.php'));	 //后台通知URL

		$requestId    = strtotime("now");						 //商户请求的交易流水号，要求唯一。
		$signType     = "MD5";                                   //签名方式
		$version      = "2.0.0";								 //版本号

		//手机支付 订单参数
		$amount 	  = $order['order_amount']*100;//订单金额，以分为单位。
		$bankAbbr     = "";                        //银行代码
		$currency     = "00";                      //币种 00=>CNY
		$orderDate    = local_date('Ymd', $order['add_time']); //订单提交日期
		$orderId 	  = $order['order_sn'];		  //商户系统订单号
		$merAcDate    = $orderDate;               //商户会计日期
		$period 	  = "8";                      //有效期数量
		$periodUnit   = "02";                     //有效期单位：00-分 01-小时 02-天
		$merchantAbbr = decodeUtf8("易视网");     //商户展示名称
		$productDesc  = "";                       //商品描述。
		$productId    = "";                       //商品编号
		$productName  = decodeUtf8("易视网商品");//商品名称
		$productNum   = 1;                        //商品数量
		$reserved1    = $order['log_id'];         //保留字段1（用户支付确认）
		$reserved2    = "";                       //保留字段2
		$userToken    = isset($order['tel'])? trim($order['tel']):'';  //用户标识:待支付用户手机
		$showUrl 	  = "";                       //商品展示地址
		$couponsFlag  = "00";                     //营销工具控件
		$pay_id       = $order['pay_id'];
		//------------------------------------------------【公共参数设置 END】---------------------------------------------------//

		//组织签名数据	
		$signData =   $characterSet.$callbackUrl.$notifyUrl.$ipAddress
					 .$merchantId  .$requestId  .$signType .$type
					 .$version     .$amount     .$bankAbbr .$currency
					 .$orderDate   .$orderId    .$merAcDate.$period   .$periodUnit
					 .$merchantAbbr.$productDesc.$productId.$productName
					 .$productNum  .$reserved1  .$reserved2.$userToken
					 .$showUrl     .$couponsFlag;
		$signKey = $key;					
		$hmac = MD5sign($signKey, $signData);//MD5方式签名


		//网关请求数据
		$requestData = array();
		$requestData["characterSet"] = $characterSet;
		$requestData["callbackUrl"]  = $callbackUrl;
		$requestData["notifyUrl"]    = $notifyUrl;
		$requestData["ipAddress"]    = $ipAddress;
		$requestData["merchantId"]   = $merchantId;
		$requestData["requestId"]    = $requestId;
		$requestData["signType"]     = $signType;
		$requestData["type"]         = $type; 
		$requestData["version"]      = $version;
		$requestData["hmac"]         = $hmac;	 
		$requestData["amount"]       = $amount; 	      
		$requestData["bankAbbr"]     = $bankAbbr;      
		$requestData["currency"]     = $currency;      
		$requestData["orderDate"]    = $orderDate;     
		$requestData["orderId"]      = $orderId; 	 
		$requestData["merAcDate"]    = $merAcDate;   
		$requestData["period"]       = $period; 	      
		$requestData["periodUnit"]   = $periodUnit; 
		$requestData["merchantAbbr"] = $merchantAbbr;   
		$requestData["productDesc"]  = $productDesc;   
		$requestData["productId"]    = $productId;     
		$requestData["productName"]  = $productName;   
		$requestData["productNum"]   = $productNum;    
		$requestData["reserved1"]    = $reserved1;     
		$requestData["reserved2"]    = $reserved2;     
		$requestData["userToken"]    = $userToken;         	      
		$requestData["showUrl"] 	 = $showUrl; 		  
		$requestData["couponsFlag"]  = $couponsFlag;

		//http请求到手机支付平台
		$sTotalString = POSTDATA($reqUrl, $requestData);
		$recv = $sTotalString["MSG"];
		$recvArray = parseRecv($recv);
		$code = $recvArray["returnCode"];
		$payUrl;
		if($code!="000000")
		{
			echo "code:".$code."</br>msg:".decodeUtf8($recvArray["message"]);
			exit();
		}
		else
		{
			$vfsign = $recvArray["merchantId"].$recvArray["requestId"]
					 .$recvArray["signType"]  .$recvArray["type"]
					 .$recvArray["version"]   .$recvArray["returnCode"]
					 .$recvArray["message"]   .$recvArray["payUrl"];
			$hmac = MD5sign($signKey, $vfsign);
			$vhmac= $recvArray["hmac"];   
			if($hmac!=$vhmac)
			{
				echo "验证签名失败!";
				exit();
			}
			else
			{
				$payUrl = $recvArray["payUrl"];			
				$rpayUrl= parseUrl($payUrl); //返回url处理
			}     
		}	

		//生成支付按钮代码，把支付代码显示在页面当中。
		$def_url  = '<div style="text-align:center"><form name="kqPay" method="'.$rpayUrl["method"].'" action="'.$rpayUrl["url"].'" target="_blank">';
		$def_url .= '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_'.$pay_id.'.gif" value="">';
		$def_url .= "</form></div></br>";
		return $def_url;
    }

    /**
     * 手机支付 响应操作
     */
    function respond()
    {
		$my_merchant_id = "888009953110653";//商户ID
		$signKey        = "e1SL6mLF8zlpiVMQpqB3qY7O0tl8ipDWqVwJGK33Gn0fOg1w1YKcoP33QSav1hgf";//KEY

		//接收手机支付平台后台通知数据start
		$merchantId 	= $_POST["merchantId"];//手机支付传过来的商户号
		$payNo 	  		= $_POST["payNo"];
		$returnCode 	= $_POST["returnCode"];
		$message	  	= $_POST["message"];
		$signType       = $_POST["signType"];
		$type         	= $_POST["type"];
		$version        = $_POST["version"];
		$amount         = $_POST["amount"];
		$amtItem		= $_POST["amtItem"];		
		$bankAbbr	  	= $_POST["bankAbbr"];
		$mobile 		= $_POST["mobile"];
		$orderId		= $_POST["orderId"];
		$payDate		= $_POST["payDate"];
		$accountDate    = $_POST["accountDate"];
		$reserved1	  	= $_POST["reserved1"];
		$reserved2	  	= $_POST["reserved2"];
		$status			= $_POST["status"];
		$payType        = $_POST["payType"];
		$orderDate      = $_POST["orderDate"];
		$fee            = $_POST["fee"];
		$vhmac			= $_POST["hmac"];
		//接收手机支付平台后台通知数据end
		
		if($my_merchant_id != $merchantId)
		{
            return false;//商户号错误
		}

		if($returnCode!=000000)
		{		
			//echo $returnCode.decodeUtf8($message);//此处表示后台通知产生错误
			//exit();
			return false;
		}
		$signData = $merchantId .$payNo       .$returnCode .$message
				   .$signType   .$type        .$version    .$amount
				   .$amtItem    .$bankAbbr    .$mobile     .$orderId
				   .$payDate    .$accountDate .$reserved1  .$reserved2
				   .$status     .$orderDate   .$fee;
		$hmac = MD5sign($signKey,$signData);

		if($hmac!=$vhmac)
		{	  
			//签名验证失败，此处无法保证信息数据来自手机支付平台
			return false;
		}
		else
		{		
			//签名验证成功：商户在此处做业务处理，处理完毕必须响应SUCCESS
			if($status=='SUCCESS')
			{
				order_paid($reserved1);//支付成功
                return true;
			}
			else
			{
				return false;//支付失败
			}
		}
    }
}
?>