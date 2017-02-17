<?php
/**
 * 民生银行直连支付
 * @author zhuwentao
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
require_once("java/Java.inc");

class cmbc {
	/**
     * 生成支付代码
     * @param $order
     * @param $payment
     */
    function get_code($order, $payment)
    {
    	date_default_timezone_set('PRC');
    	
        
        $billNo             = $order['order_sn'];               //订单号
        $txAmt              = $order['order_amount'];           //金额
        $PayerCurr          = '01';                             //01：人民币
        $txDate             = date('Ymd',time());               //交易日期
        $txTime             = date('His',time());               //交易时间
        $corpID             = '02123';                          //商户代码
        //$corpID             = '66002';
        $corpName           = '易视眼镜';                       //商户名称
        $CorpRetType        = 0;                                //是否实时返回标志0：即时返回 1：查询
        //$retUrl             = 'http://www.easeeyes.com/includes/modules/payment/cmbc_callback.php';        //处理结果返回的URL
        $retUrl             = 'http://www.easeeyes.com/respond_cmbc.php';        //处理结果返回的URL
        
        $str = $billNo."|".$txAmt."|".$PayerCurr."|".$txDate."|".$txTime."|".$corpID."|".$corpName."|||".$CorpRetType."|".$retUrl."|";      
        //$sign2 = new java("Union.JnkyServer", '/data/www/easeeyes.com/current/cmbc/banknew1024.cer','/data/www/easeeyes.com/current/cmbc/66002.pfx','1111');
        $sign2 = new java("Union.JnkyServer", '/data/www/easeeyes.com/current/cmbc/cmbc1024-DER.cer','/data/www/easeeyes.com/current/cmbc/yishi.pfx','yishi888',false);
        $encrypt_str = $sign2->EnvelopData($str,"utf-8");       //对原文进行签名并用银行公钥进行加密
        
		$def_url  = '<div style="text-align:center">';
		//$def_url .= '<FORM id="FORM1" name="FORM1" action="http://111.205.207.118:55000/pweb/b2cprelogin.do" method="post">';
        $def_url .= '<FORM id="FORM1" name="FORM1" action="https://per.cmbc.com.cn/pweb/b2cprelogin.do" method="post">';
		
		$def_url .=	'<INPUT TYPE="hidden" ID="orderinfo" NAME="orderinfo" value="'.$encrypt_str.'">';
	
		$def_url .= '<INPUT TYPE="submit" value=" " class="cart_end_bt" border="0" style="width:180px; height=40px; border:none; background:url(http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_301.gif) top center no-repeat; cursor:pointer" >';
		$def_url .=	'</FORM>';
		$def_url .= '</div>';

		return $def_url;
    }
    
    
    /**
     * 支付结果操作
     */
    /* 
    function respond($log_id=0)
    {
        
        
        $res = json_encode($_REQUEST);
        $sql = "INSERT INTO temp_order SET goods_number = 2,address = '$res'";
        $pay_log = $GLOBALS['db']->query($sql);
        
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') .
                " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);
        if ($pay_log && $pay_log['is_paid'] == 0)
        
        
    	$strKey			= 'sZrLozDnF260MF9e';				//商户自定义密匙
    	$notifyData		= trim($_REQUEST['notifyData']);	//交易数据base64编码
    	$signMsg		= trim($_REQUEST['signMsg']);		//签名信息base64编码
    	$merVAR			= trim($_REQUEST['merVAR']);		//商家自定义参数，内容是订单号 + md5(商户密匙+订单号)
    	$bankCertPath	= "/var/www/easeeyes.com/current/icbc/ebb2cpublic.crt";	//银行证书文件路径
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
  */

	
}
