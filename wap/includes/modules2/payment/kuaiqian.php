<?php
/* ============================================================================
 * 商城 快钱支付插件【2012/4/5】【author:yi】
 * ============================================================================
 * 快钱人民币网关接口用途：
 *		1。链接到块钱支付页面支付方式。
 *	    2。列出各个图标出来，直链到各个银行支付方式。
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/kuaiqian.php';

if(file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}

/**
 * 模块信息
 */
if(isset($set_modules) && $set_modules == true)
{
    $i = isset($modules) ? count($modules) : 0;
    $modules[$i]['code']      = basename(__FILE__, '.php'); //代码
    $modules[$i]['desc']      = 'kuaiqian_desc';            //描述对应的语言项
    $modules[$i]['is_cod']    = '0';                        //是否支持货到付款
    $modules[$i]['is_online'] = '1';                        //是否支持在线支付
    $modules[$i]['author']    = 'YI JIANGWEN';              //作者
    $modules[$i]['website']   = 'http://www.99bill.com';    //网址
    $modules[$i]['version']   = '1.2';                      //版本号

    //配置信息
    $modules[$i]['config'] = array(
        array('name' => 'kq_account', 'type' => 'text', 'value' => ''),
        array('name' => 'kq_key', 'type' => 'text', 'value' => ''),
    );
    return;
}

class kuaiqian
{
	//构造函数
    function kuaiqian()
    {
    }

    function __construct()
    {
        $this->kuaiqian();
    }

   /**
     * 生成支付代码
     * @param   array   $order    订单信息
     * @param   array   $payment  支付方式信息
     */
   function get_code($order, $payment)
   {
		$merchant_acctid    = trim($payment['kq_account']);                 //人民币账号不可空,后台设置
		$key                = trim($payment['kq_key']);                     //账号对应key
		$input_charset      = 1;                                            //字符集 默认1=utf-8
		$page_url           = return_url(basename(__FILE__, '.php'));
		$bg_url             = '';
		$version            = 'v2.0';
		$language           = 1;
		$sign_type          = 1;                                            //签名类型 不可空 固定值 1:md5

		//【开通网银直链功能】【购物车进来和非购物车进来】
		$pay_id     = isset($order['pay_id'])? intval($order['pay_id']) :10;//快钱代码是【10】
		$bank_id_yi = isset($order['bank_id'])?intval($order['bank_id']):0; //银行编号

		//如果pay_id是直接的银行编号，$order['bank_id']不存在。
		if($pay_id>100)
		{
			$bank_id_yi = $pay_id;
			$pay_id     = 10;
		}

		//bank_code。406:电话支付，407:信用卡支付。
		$bank_code_arr = array(
			101=>'ICBC', 102 => 'CCB',
			103=>'ABC',  104 => 'BOC',
			105=>'BCOM', 201 => 'CMB',
			202=>'CEB',  203 => 'CITIC',
			204=>'CIB',  205 => 'SPDB',
			301=>'CMBC', 302 => 'SDB',
			303=>'SHB',  304 => 'CZB',
			305=>'NBCB', 401 => 'GDB',
			402=>'PAB',  403 => 'BOB',
			404=>'UPOP', 405 => 'PSBC',
			406=>'TELP'
		);

		$payer_name         = isset($order['consignee'])?trim($order['consignee']):''; //支付人姓名
		$payer_contact_type = '1';                                                     //支付人联系方式:1.电子邮件, 2.手机
		$payer_contact      = '';													   //支付人联系号码或者邮箱
		$order_id           = $order['order_sn'];                                      //商户订单号   不可空
		$order_amount       = $order['order_amount'] * 100;						       //商户订单金额 不可空 ‘分’为单位
		$order_time         = local_date('YmdHis', $order['add_time']);				   //商户订单提交时间 不可空 14位
		$product_name       = '';
		$product_num        = '';
		$product_id         = '';
		$product_desc       = '';
		$ext1               = $order['log_id'];
		$ext2               = '';
		$pay_type           = '00';                                         //支付方式一般为00，代表所有的支付方式。银行直连为10。信用卡支付为15。必填。
		$bank_id            = '';										    //银行代码，如果payType为00，该值为空；如果payType为10，该值必填。

		//直连支付（15:表示信用卡支付）
		if($bank_id_yi>100 && !empty($bank_code_arr[$bank_id_yi]))
		{
			if($bank_id_yi != 406)
			{
				$pay_type   = '10'; 
				$bank_id    = $bank_code_arr[$bank_id_yi];	
			}
			else
			{
				$pay_type   = '11'; 
				$bank_id    = '';	
			}
		}
		$redo_flag          = '0';                                          //可重复提交订单，设为1则只能提交一次订单,0代表在支付不成功情况下可以再提交。
		$pid                = '';                                           //快钱合作伙伴的帐户号，即商户编号，可为空。

        /* 生成加密签名串 请务必按照如下顺序和规则组成加密串！*/
        $signmsgval = '';
        $signmsgval = $this->append_param($signmsgval, "inputCharset", $input_charset);
        $signmsgval = $this->append_param($signmsgval, "pageUrl", $page_url);
        $signmsgval = $this->append_param($signmsgval, "bgUrl", $bg_url);
        $signmsgval = $this->append_param($signmsgval, "version", $version);
        $signmsgval = $this->append_param($signmsgval, "language", $language);
        $signmsgval = $this->append_param($signmsgval, "signType", $sign_type);
        $signmsgval = $this->append_param($signmsgval, "merchantAcctId", $merchant_acctid);
        $signmsgval = $this->append_param($signmsgval, "payerName", $payer_name);
        $signmsgval = $this->append_param($signmsgval, "payerContactType", $payer_contact_type);
        $signmsgval = $this->append_param($signmsgval, "payerContact", $payer_contact);
        $signmsgval = $this->append_param($signmsgval, "orderId", $order_id);
        $signmsgval = $this->append_param($signmsgval, "orderAmount", $order_amount);
        $signmsgval = $this->append_param($signmsgval, "orderTime", $order_time);
        $signmsgval = $this->append_param($signmsgval, "productName", $product_name);
        $signmsgval = $this->append_param($signmsgval, "productNum", $product_num);
        $signmsgval = $this->append_param($signmsgval, "productId", $product_id);
        $signmsgval = $this->append_param($signmsgval, "productDesc", $product_desc);
        $signmsgval = $this->append_param($signmsgval, "ext1", $ext1);
        $signmsgval = $this->append_param($signmsgval, "ext2", $ext2);
        $signmsgval = $this->append_param($signmsgval, "payType", $pay_type);
        $signmsgval = $this->append_param($signmsgval, "bankId", $bank_id);
        $signmsgval = $this->append_param($signmsgval, "redoFlag", $redo_flag);
        $signmsgval = $this->append_param($signmsgval, "pid", $pid);
        $signmsgval = $this->append_param($signmsgval, "key", $key);
        $signmsg    = strtoupper(md5($signmsgval));    //签名字符串 不可空

		//生成支付按钮代码
		$def_url  = '<div style="text-align:center"><form name="kqPay" style="text-align:center;" method="post" action="https://www.99bill.com/gateway/recvMerchantInfoAction.htm" target="_blank">';
		$def_url .= "<input type='hidden' name='inputCharset' value='" . $input_charset . "' />";
		$def_url .= "<input type='hidden' name='bgUrl' value='" . $bg_url . "' />";
		$def_url .= "<input type='hidden' name='pageUrl' value='" . $page_url . "' />";
		$def_url .= "<input type='hidden' name='version' value='" . $version . "' />";
		$def_url .= "<input type='hidden' name='language' value='" . $language . "' />";
		$def_url .= "<input type='hidden' name='signType' value='" . $sign_type . "' />";
		$def_url .= "<input type='hidden' name='signMsg' value='" . $signmsg . "' />";
		$def_url .= "<input type='hidden' name='merchantAcctId' value='" . $merchant_acctid . "' />";
		$def_url .= "<input type='hidden' name='payerName' value='" . $payer_name . "' />";
		$def_url .= "<input type='hidden' name='payerContactType' value='" . $payer_contact_type . "' />";
		$def_url .= "<input type='hidden' name='payerContact' value='" . $payer_contact . "' />";
		$def_url .= "<input type='hidden' name='orderId' value='" . $order_id . "' />";
		$def_url .= "<input type='hidden' name='orderAmount' value='" . $order_amount . "' />";
		$def_url .= "<input type='hidden' name='orderTime' value='" . $order_time . "' />";
		$def_url .= "<input type='hidden' name='productName' value='" . $product_name . "' />";
		$def_url .= "<input type='hidden' name='payType' value='" . $pay_type . "' />";
		$def_url .= "<input type='hidden' name='productNum' value='" . $product_num . "' />";
		$def_url .= "<input type='hidden' name='productId' value='" . $product_id . "' />";
		$def_url .= "<input type='hidden' name='productDesc' value='" . $product_desc . "' />";
		$def_url .= "<input type='hidden' name='ext1' value='" . $ext1 . "' />";
		$def_url .= "<input type='hidden' name='ext2' value='" . $ext2 . "' />";
		$def_url .= "<input type='hidden' name='bankId' value='" . $bank_id . "' />";
		$def_url .= "<input type='hidden' name='redoFlag' value='" . $redo_flag ."' />";
		$def_url .= "<input type='hidden' name='pid' value='" . $pid . "' />";
		//$def_url .= "<input type='submit' name='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "' />";
		if($bank_id_yi > 100)
	    {
			$def_url .= '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_'.$bank_id_yi.'.gif" value="">';
		}
		else
	    {
			$def_url .= '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_'.$pay_id.'.gif" value="">';
		}
		$def_url .= "</form></div>";
		return $def_url;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        $payment             = get_payment($_GET['code']);
        $merchant_acctid     = $payment['kq_account'];                 //人民币账号 不可空
        $key                 = $payment['kq_key'];
        $get_merchant_acctid = trim($_REQUEST['merchantAcctId']);
        $pay_result          = trim($_REQUEST['payResult']);
        $version             = trim($_REQUEST['version']);
        $language            = trim($_REQUEST['language']);
        $sign_type           = trim($_REQUEST['signType']);
        $pay_type            = trim($_REQUEST['payType']);
        $bank_id             = trim($_REQUEST['bankId']);
        $order_id            = trim($_REQUEST['orderId']);
        $order_time          = trim($_REQUEST['orderTime']);
        $order_amount        = trim($_REQUEST['orderAmount']);
        $deal_id             = trim($_REQUEST['dealId']);
        $bank_deal_id        = trim($_REQUEST['bankDealId']);
        $deal_time           = trim($_REQUEST['dealTime']);
        $pay_amount          = trim($_REQUEST['payAmount']);
        $fee                 = trim($_REQUEST['fee']);
        $ext1                = trim($_REQUEST['ext1']);
        $ext2                = trim($_REQUEST['ext2']);
        $err_code            = trim($_REQUEST['errCode']);
        $sign_msg            = trim($_REQUEST['signMsg']);

        //生成加密串。必须保持如下顺序。
        $merchant_signmsgval = '';
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"merchantAcctId",$merchant_acctid);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"version",$version);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"language",$language);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"signType",$sign_type);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"payType",$pay_type);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"bankId",$bank_id);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"orderId",$order_id);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"orderTime",$order_time);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"orderAmount",$order_amount);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"dealId",$deal_id);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"bankDealId",$bank_deal_id);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"dealTime",$deal_time);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"payAmount",$pay_amount);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"fee",$fee);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"ext1",$ext1);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"ext2",$ext2);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"payResult",$pay_result);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"errCode",$err_code);
        $merchant_signmsgval = $this->append_param($merchant_signmsgval,"key",$key);
        $merchant_signmsg    = md5($merchant_signmsgval);

        //首先对获得的商户号进行比对
        if($get_merchant_acctid != $merchant_acctid)
        {
            //商户号错误
            return false;
        }

        if(strtoupper($sign_msg) == strtoupper($merchant_signmsg))
        {
            if($pay_result == 10 || $pay_result == 00)
            {
                order_paid($ext1);
                return true;
            }
            else
            {
                //'支付结果失败';
                return false;
            }

        }
        else
        {
            //'密钥校对错误';
            return false;
        }
    }

    /**
    * 将变量值不为空的参数组成字符串
    * @param   string   $strs  参数字符串
    * @param   string   $key   参数键名
    * @param   string   $val   参数键对应值
    */
    function append_param($strs,$key,$val)
    {
        if($strs != "")
        {
            if($key != '' && $val != '')
            {
                $strs .= '&' . $key . '=' . $val;
            }
        }
        else
        {
            if($val != '')
            {
                $strs = $key . '=' . $val;
            }
        }
            return $strs;
    }
}
?>