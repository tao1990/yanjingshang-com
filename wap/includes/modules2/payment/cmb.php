<?php
/**
 * 招商银行直连支付接口
 * @author xuyizhi 2013-11-27
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/cmb.php';

if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}

//require_once(ROOT_PATH."includes/modules/payment/java/Java.inc");
require_once("java/Java.inc");

class cmb {
	
    /**
     * 生成支付代码
     * @param $order
     * @param $payment
     */
    function get_code($order, $payment)
    {
    	date_default_timezone_set('PRC');
    	
    	$strKey = 'sZrLozDnF260MF9e';					//商户密匙
		$strDate = date('Ymd', $order['add_time']);		//订单日期
		$strBranchID = '0021';							//开户分行号
		$strCono = '003427';							//商户号
		$strBillNo = $this->get_bill_no($order['order_id']);			//订单号(招行需要的当天唯一订单标识：order_id并补零成10位唯一数)
		$strAmount = $order['order_amount'];			//订单金额
		$strMerchantPara = $order['order_sn']."||".md5($strKey.$order['order_sn']);			//商户自定义参数(易视订单号)
		$strMerchantUrl = 'http://www.easeeyes.com/respond_cmb.php';	//商户接受通知的URL
		$strPayerID = $order['user_id'];				//付款方用户标识
		$strPayeeID = '15';								//收款方的用户标识
		$strClientIP = $this->get_ip();					//商户取得的客户端IP
		$strGoodsType = '54011600';						//商品类型
		$strReserved = '';								//保留
		
		$cmb_code = new java("cmb.MerchantCode"); //调用java,生成订单校验码
        
		$MerchantCode = $cmb_code->genMerchantCode($strKey, $strDate, $strBranchID, $strCono, $strBillNo, $strAmount, $strMerchantPara, $strMerchantUrl, $strPayerID, $strPayeeID, $strClientIP, $strGoodsType, $strReserved);
		
        
		//生成支付按钮代码
		$def_url  = '<div style="text-align:center">';
		$def_url .= '<form name="cmb" style="text-align:center;" method="post" action="https://netpay.cmbchina.com/netpayment/BaseHttp.dll?PrePayC2" target="_blank">';
		$def_url .= "<input type='hidden' name='date' value='$strDate' />";
		$def_url .= "<input type='hidden' name='cono'	value='$strCono' />";
		$def_url .= "<input type='hidden' name='branchid' value='$strBranchID' />";
		$def_url .= "<input type='hidden' name='billno' value='$strBillNo' />";
		$def_url .= "<input type='hidden' name='amount' value='$strAmount' />";
		$def_url .= "<input type='hidden' name='MerchantUrl' value='$strMerchantUrl' />";
		$def_url .= "<input type='hidden' name='MerchantPara' value='".$order['order_sn']."||".md5($strKey.$order['order_sn'])."' />";//将商户密钥+订单号加密,用于返回时验证
		$def_url .= "<input type='hidden' name='MerchantCode' value='$MerchantCode' />";
		$def_url .= '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_15.gif" value="">';
		$def_url .= "</form>";
		$def_url .= "</div>";
		
		return $def_url;
    }
    
    /**
     * 支付结果操作
     */
    function respond($log_id=0)
    {
    	$strKey = 'sZrLozDnF260MF9e';
    	$Succeed = $_GET['Succeed'];
    	$BillNo = $_GET['BillNo'];
    	$Amount = $_GET['Amount'];
    	$MerchantPara = $_GET['MerchantPara']; //商家自定义参数，内容是订单号 + md5(商户密匙+订单号)
    	
    	//支付成功
    	if ($Succeed == 'Y')
    	{
    		//验证签名
    		if ( ! empty($MerchantPara) )
    		{
    			$sign_arr = explode('||', $MerchantPara);
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