<?php
/* ============================================================================
 * 商城 得仕通支付插件【2013/9/3】
 * ============================================================================
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/days.php';
if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}
require_once(ROOT_PATH."includes/modules/payment/java/Java.inc");

class days
{

   /**
     * 生成支付代码
     * @param   array   $order    订单信息
     * @param   array   $payorder_paidment  支付方式信息
     */
   function get_code($order, $payment)
   {
   		$receive_url		= 'http://www.easeeyes.com/respond.php?code=days';     //请填写返回url,地址应为绝对路径,带有http协议
		$auto_receive_urlorder_paid	= 'http://www.easeeyes.com/days/autorespond.php';//请填写自动通知对帐url,地址应为绝对路径,带有http协议		
		$amount			= $order['order_amount'] * 100;				//订单金额。分为单位。
		$order_id		= $order['order_sn'];					//订单ID
		//$reserved		= $order['log_id'];

		$amount = str_replace('.','',$amount);

		$orderData = new Java("mpi.days.data.OrderData");

		$orderData->setTranCode(new Java("java.lang.String", "1000")); 					//交易代码，1000代表支付
		$orderData->setMerchantId(new Java("java.lang.String", "910130910100049"));			//商户ID，15位，910130815100145
		$orderData->setMerOrderNum(new Java("java.lang.String", $order_id.mt_rand(0,9)));		//订单号,14位数字。并不一定是6位商户号末尾+8位订单号。
		$orderData->setTranDateTime(new Java("java.lang.String",date('YmdHis',time())));		//交易时间，YYYYMMDDHHMMSS   
		$orderData->setTranAmt(new Java("java.lang.String", $amount));					//交易金额
		$orderData->setCurrencyType(new Java("java.lang.String", "156"));				//币种，156人民币
		$orderData->setMerUrl(new Java("java.lang.String", $receive_url));				//商户前台返回URL
		$orderData->setMerBackUrl(new Java("java.lang.String", $auto_receive_urlorder_paid));		//商户后台通知URL
		$orderData->setIsBackG(new Java("java.lang.String", "1"));					//是否支持后台返回交易结果

		$TopPayLink = new Java("mpi.days.trans.TopPayLink");

		$mpiReq = $TopPayLink->PayTrans($orderData); //得到交易报文

		//生成支付按钮代码
		$def_url  = '<div style="text-align:center"><form name="days" style="text-align:center;" method="post" action="https://www.dayspay.com.cn/dayspay/paygate/payRequest.do" target="_blank">';
		$def_url .= "<input type='hidden' name='Version' value='2.0.0' />";
		$def_url .= "<input type='hidden' name='MPIReq'	value='".$mpiReq."' />";
		$def_url .= "<input type='hidden' name='TermUrl' value='".$receive_url."' />";
		$def_url .= '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_901.gif" value="">';
		$def_url .= "</form></div>";

		return $def_url;
    }


	/* ----------------------------------------------------------------------------------------------------------------------
	 * 对支付结果做出响应。并即时变更这个订单的支付状态。这个方法和respond.php这个页面是一起的。
	 * ----------------------------------------------------------------------------------------------------------------------
	 */
    function respond()
    {

	$TopPayLink = new Java("mpi.days.trans.TopPayLink");
	$orderData = $TopPayLink->ConvXml2OrderData($_POST["MPIRes"]);

//	$orderId=$orderData->getMerOrderNum();
	$respCode=$orderData->getRespCode();

	if($respCode=="0000"){ 
		
		return TRUE;
	}else{
		return FALSE;
	}

    }
    	/* ----------------------------------------------------------------------------------------------------------------------
	 * 对支付结果做处理，更改订单支付状态。这个方法和days/autorespond.php这个页面是一起的。
	 * ----------------------------------------------------------------------------------------------------------------------
	 */
    function autorespond(){
	
	$mpiRes=file_get_contents('php://input');
	if(!$mpiRes){
		return FALSE;
	}
	$mpiRes=urldecode($mpiRes);

	$TopPayLink = new Java("mpi.days.trans.TopPayLink");
	$orderData = $TopPayLink->ConvXml2OrderData($mpiRes);

	$orderId=$orderData->getMerOrderNum();
	$respCode=$orderData->getRespCode();

	if($respCode=="0000"){
		$order_id = substr($orderId,0,13);//由于发送给网管的时候定义的订单号规则为：13位的订单号+1位的随机数。故作此处理。
		
		//由订单id得到order_log 的id
		$sql = 'SELECT l.log_id FROM ' . $GLOBALS['ecs']->table('pay_log').' as l,' . $GLOBALS['ecs']->table('order_info').' as o WHERE l.order_id = o.order_id and o.order_sn = '.$order_id;
		$log_id    = $GLOBALS['db']->getOne($sql);
		
		if($log_id){
			order_paid($log_id);
		}
		return TRUE;
	}else{
		return FALSE;
	}
    
    }
}
?>

