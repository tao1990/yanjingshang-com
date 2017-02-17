<?php
/* ============================================================================
 * 商城 交通银行直接对接支付插件【2012/12/27】【author:yijiangwen】
 * ============================================================================
 * 用途：
 *		1。直接链接到交通银行的支付网关。
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/bocomm.php';

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
    $modules[$i]['desc']      = 'bocomm_desc';              //描述对应的语言项
    $modules[$i]['is_cod']    = '0';                        //是否支持货到付款
    $modules[$i]['is_online'] = '1';                        //是否支持在线支付
    $modules[$i]['author']    = 'YI JIANGWEN';              //作者
    $modules[$i]['website']   = 'http://www.bankcomm.com';  //网址
    $modules[$i]['version']   = '1.0.0.0';                  //版本号

    //配置信息
    $modules[$i]['config'] = array(
        array('name' => 'kq_account', 'type' => 'text', 'value' => ''),
        array('name' => 'kq_key', 'type' => 'text', 'value' => ''),
    );
    return;
}

class bocomm
{
	//构造函数
    function bocomm()
    {
    }

    function __construct()
    {
        $this->bocomm();
    }

   /**
     * 生成支付代码
     * @param   array   $order    订单信息
     * @param   array   $payment  支付方式信息
     */
   function get_code($order, $payment)
   {
		$pay_log_id         = $order['log_id'];//支付编码

		$return_url  = "http://www.easeeyes.com/zhifu/merchant_result.php";
		$return_msg  = "http://www.easeeyes.com/zhifu/merchant_result_msg.php";//主动通知url


		//【开通网银直链功能】【购物车进来和非购物车进来】
		$pay_id     = isset($order['pay_id'])? intval($order['pay_id']) :12;//代码是【12】
		$bank_id_yi = isset($order['bank_id'])?intval($order['bank_id']):0; //银行编号


		$order_id           = $order['order_sn'];                                      //商户订单号   不可空
		$order_amount       = $order['order_amount'];						           //商户订单金额 不可空 ‘分’为单位

		$order_date         = local_date('Ymd', $order['add_time']);				   //商户订单提交时间 不可空 14位
		$order_time         = local_date('His', $order['add_time']);				   //商户订单提交时间 不可空 14位

		//生成交行的支付按钮。
		$def_url  = '<div style="text-align:center"><form name="bocomm" style="text-align:center;" method="post" action="http://www.easeeyes.com/zhifu/merchant.php" target="_blank">';
		$def_url .= "<input type='hidden' name='orderid' value='".$order_id."' />";
		$def_url .= "<input type='hidden' name='orderDate' value='".$order_date."' />";
		$def_url .= "<input type='hidden' name='orderTime' value='".$order_time."' />";
		$def_url .= "<input type='hidden' name='amount' value='".$order_amount."' />";

		$def_url .= "<input type='hidden' name='notifyType' value='1' />";             //通知方式 0 不通知 1 通知 2 抓取页面
		$def_url .= "<input type='hidden' name='merURL' value='".$return_msg."' />";   //主动通知URL

		$def_url .= "<input type='hidden' name='orderContent' value='' />";
		$def_url .= "<input type='hidden' name='orderMono' value='".$pay_log_id."' />";//商户备注
		$def_url .= "<input type='hidden' name='phdFlag' value='' />";

		$def_url .= "<input type='hidden' name='goodsURL' value='".$return_url."' />"; //去商城取货URL
		$def_url .= "<input type='hidden' name='jumpSeconds' value='1' />";            //去商城取货自动跳转时间

		$def_url .= "<input type='hidden' name='payBatchNo' value='' />";
		$def_url .= "<input type='hidden' name='proxyMerName' value='' />";
		$def_url .= "<input type='hidden' name='proxyMerType' value='' />";
		$def_url .= "<input type='hidden' name='proxyMerCredentials' value='' />";
		$def_url .= "<input type='hidden' name='issBankNo' value='' />";
		$def_url .= '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/bank_bt/pay_'.$pay_id.'.gif" value="">';

		$def_url .= "</form></div>";
		return $def_url;
    }

	//yi
	function respond()
	{
		$pay_log_id  = intval(urldecode($_REQUEST['pay_log_id']));

		if(!empty($pay_log_id))
		{
			order_paid($pay_log_id);
			return true;
		}
		else
		{
			return false;
		}
	}

    /**
     * 响应操作
     */
    function respond_back()
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
            //return false;
        }

        if(strtoupper($sign_msg) == strtoupper($merchant_signmsg))
        {
            if($pay_result == 10 || $pay_result == 00)
            {
                //order_paid($ext1);
                //return true;
            }
            else
            {
                //'支付结果失败';
               // return false;
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
    function append_param($strs, $key, $val)
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
                $strs = $key.'='.$val;
            }
        }
        return $strs;
    }
}
?>