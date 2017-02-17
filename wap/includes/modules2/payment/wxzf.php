<?php
/* ============================================================================
 * 微信支付【2015/06/02】
 * ============================================================================
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
require_once(ROOT_PATH . 'includes/lib_order.php');
$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/wxzf.php';
if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}
/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'wxzf_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'YI SHI';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.wxpay.com';

    /* 版本号 */
    $modules[$i]['version'] = '2.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'wxzf_account',   'type' => 'text', 'value' => ''),
        array('name' => 'wxzf_key',       'type' => 'text', 'value' => ''),
        array('name' => 'magic_string',     'type' => 'text', 'value' => '')
    );

    return;
}

class wxzf
{

   /**
     * 生成支付代码
     * @param   array   $order    订单信息
     * @param   array   $payorder_paidment  支付方式信息
     */
   function get_code($order, $payment)
   {
        ini_set("display_errors",1);
        $goods_name = $GLOBALS['db']->getOne("SELECT goods_name FROM ".$GLOBALS['ecs']->table('order_goods')."  WHERE order_id = ".$order['order_id']." LIMIT 1");
      
        require_once ROOT_PATH."/wxpay/lib/WxPay.Api.php";
        require_once ROOT_PATH."/wxpay/unit/WxPay.NativePay.php";
        require_once ROOT_PATH."/wxpay/unit/log.php";
        include ROOT_PATH."/wxpay/phpqrcode/qrlib.php";
      
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();
        
        $input->SetBody($goods_name);
        $input->SetAttach("微信支付");
        $input->SetOut_trade_no($order['order_sn']);
        $input->SetTotal_fee($order['order_amount']*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 259200));
        $input->SetGoods_tag("易视网");
        $input->SetNotify_url("http://www.easeeyes.com/respond_wx.php");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("0");
        $result = $notify->GetPayUrl($input);
        $url = $result["code_url"];

        $PNG_TEMP_DIR = ROOT_PATH.'/wxpay/wxpic/';
        $filename = $PNG_TEMP_DIR.'qrcode_'.$order['order_sn'].'.png';
        QRcode::png($url, $filename, 'M', 4, 2);// L-smallest, M, Q, H-best; size: 1-50
        
        chmod($filename, 777);
        
        $def_url  = '<div style="text-align:left">';
		$def_url .= "<img src='/wxpay/wxpic/".basename($filename)."'  />";
		$def_url .= "</div>";
        
        return $def_url;
        
                
    }

    
	/* ----------------------------------------------------------------------------------------------------------------------
	 * 对支付结果做出响应。并即时变更这个订单的支付状态。这个方法和respond.php这个页面是一起的。
	 * ----------------------------------------------------------------------------------------------------------------------
	 */
    function respond()
    {ini_set("display_errors",1);
        
        $GLOBALS['db']->query("INSERT INTO  temp_order SET goods_number = 9,address='".json_encode($_REQUEST)."'");
        
        require_once ROOT_PATH."/wxpay/lib/WxPay.Api.php";
        require_once ROOT_PATH."/wxpay/unit/WxPay.NativePay.php";
        require_once ROOT_PATH."/wxpay/unit/log.php";
        
        var_dump($_REQUEST);die;
        //echo "处理回调";
		Log::DEBUG("call back:" . json_encode($data));
		
		if(!array_key_exists("openid", $data) ||
			!array_key_exists("product_id", $data))
		{
			$msg = "回调数据异常";
			return false;
		}
		 
		$openid = $data["openid"];
		$product_id = $data["product_id"];
		
		//统一下单
		$result = $this->unifiedorder($openid, $product_id);
		if(!array_key_exists("appid", $result) ||
			 !array_key_exists("mch_id", $result) ||
			 !array_key_exists("prepay_id", $result))
		{
		 	$msg = "统一下单失败";
		 	return false;
		 }
		
		$this->SetData("appid", $result["appid"]);
		$this->SetData("mch_id", $result["mch_id"]);
		$this->SetData("nonce_str", WxPayApi::getNonceStr());
		$this->SetData("prepay_id", $result["prepay_id"]);
		$this->SetData("result_code", "SUCCESS");
		$this->SetData("err_code_des", "OK");
		return true;

    }
    	/* ----------------------------------------------------------------------------------------------------------------------
	 * 对支付结果做处理，更改订单支付状态。这个方法和days/autorespond.php这个页面是一起的。
	 * ----------------------------------------------------------------------------------------------------------------------
	 */
    function autorespond(){
        
         $GLOBALS['db']->query("INSERT INTO  temp_order SET goods_number = 9,address='".json_encode($_REQUEST)."'");
        die;
	
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

