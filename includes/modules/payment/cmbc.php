<?php
/**
 * 民生银行直连支付
 * @author zhuwentao
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
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
        $corpID             = '66002';                          //商户代码
        $corpName           = '易视眼镜';                       //商户名称
        $CorpRetType        = 0;                                //是否实时返回标志0：即时返回 1：查询
        $retUrl             = 'http://www.easeeyes.com';                               //处理结果返回的URL
        
        $str = $billNo."|".$txAmt."|".$PayerCurr."|".$txDate."|".$txTime."|".$corpID."|".$corpName."|||".$CorpRetType."|".$retUrl."|";      
        $sign2 = new java("Union.JnkyServer");
     
        @$sign2->JnkyServer('/data/www/cmbc/banknew1024.cer','/data/www/cmbc/66002.pfx','1111');
        die('22');
        $encrypt_str = $sign2->EnvelopData($str,"utf-8");       //对原文进行签名并用银行公钥进行加密
        
		$def_url  = '<div style="text-align:center">';
		$def_url .= '<FORM id="FORM1" name="FORM1" action="http://111.205.207.118:55000/pweb/b2cprelogin.do" method="post">';
		
		$def_url .=	'<INPUT TYPE="hidden" ID="orderinfo" NAME="orderinfo" value="'.$encrypt_str.'">';
	
		$def_url .= '<INPUT TYPE="submit" value=" " class="cart_end_bt" border="0" style="width:180px; height=40px; border:none; background:url(http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_301.gif) top center no-repeat; cursor:pointer" >';
		$def_url .=	'</FORM>';
		$def_url .= '</div>';

		return $def_url;
    }
  
    
    
    /**
     * 产生加密字符串
     * @param  $param 银行需要的字段字符串
     */
    function gen_string($param)
    {
	//java里发布的对外访问链接jsp
    $url = "http://www.你的网址.com/envelop.jsp?param=$param";
	$return_str = $this->get_content_by_url_post($url);
	return $return_str;//返回加密信息
    }
    
    
    /**
     * 获得url地址的网页内容
     * @param  $url 链接url
     * @param  $parm_data 内容 //格式如"name=admin&pwd=marcofly";
     * @return url返回内容
     */
    function get_content_by_url_post($url,$param_data)
    {
    	$ch = curl_init();
    	$timeout = 15;
    	curl_setopt ($ch, CURLOPT_URL,$url);
    	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    	curl_setopt ($ch, CURLOPT_POST,1);//post方法提交url数据
    	curl_setopt ($ch, CURLOPT_POSTFIELDS,$param_data);//格式如"name=admin&pwd=marcofly";
    	$file_contents = curl_exec($ch);
    	curl_close($ch);
    	return $file_contents;
    }
	
}