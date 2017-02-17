<?php

/**
 * ECSHOP 网汇通插件
 * ============================================================================
 * $Author: mathcn remend$  www.x-playboy.com  mathcn@qq.com
 * $Date: 2009-07-16 18:07:02 +0800 (星期三, 31 一月 2007) $
 * $Id: udpay.php 4853 2007-01-31 10:07:02Z  $
 * ============================================================================
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/udpay.php';

if (file_exists($payment_lang))
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
    $modules[$i]['desc']    = 'udpay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECSHOP TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.udpay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'udpay_account', 'type' => 'text', 'value' => ''),
        array('name' => 'udpay_merchantPrivateModulus', 'type' => 'text', 'value' => ''),
        array('name' => 'udpay_merchantPrivateExponent', 'type' => 'text', 'value' => ''),
        array('name' => 'udpay_whtpublicModulus', 'type' => 'text', 'value' => ''),
        array('name' => 'udpay_whtpublicExponent', 'type' => 'text', 'value' => ''),
        array('name' => 'udpay_orderInfo', 'type' => 'text', 'value' => ''),
        array('name' => 'udpay_errorfile', 'type' => 'text', 'value' => $GLOBALS['ecs']->url().'respond.php')
    );

    return;
}

/**
 * 类
 */
class udpay
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function udpay()
    {
        if (!function_exists('bcadd'))
        {
            trigger_error('This web server don\'t support BC match module.', E_USER_ERROR);
        }
    }

    function __construct()
    {
        $this->udpay();
    }

    /**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {
        $data_order_id    = $order['log_id'];
		$data_order_time  = local_date("Ymd",gmtime()) ;
		
        $data_amount      = $order['order_amount']*100; //金额单位为分
		
        $data_return_url  = $GLOBALS['ecs']->url();
        $data_pay_account = $payment['udpay_account'];
        $udpay_merchantPrivateModulus  = $payment['udpay_merchantPrivateModulus'];
        $udpay_merchantPrivateExponent = $payment['udpay_merchantPrivateExponent'];
        $udpay_orderInfo  = $payment['udpay_orderInfo'];
        $data_notify_url  = return_url(basename(__FILE__, '.php'));
//      $udpay_orderInfo  = iconv('UTF-8','gbk',$udpay_orderInfo);

         $msg="txCode=TP001&merchantId=$data_pay_account&transDate=$data_order_time&transFlow=$data_order_id&orderId=$order[order_sn]&curCode=156&amount=$data_amount&orderInfo=&comment=&merURL=$data_notify_url&interfaceType=3"; //交易数据签名信息原始串
	
		 
        $privateModulus  = $udpay_merchantPrivateModulus;  // 交易数据签名信息商户私钥
        $privateExponent = $udpay_merchantPrivateExponent; // 交易数据签名信息商户私钥
        $RsaDecrypt      = $this->generateSigature($msg, $privateExponent, $privateModulus);
        $def_url         = '<form style="text-align:center;" action="https://www.udpay.com.cn/gateway/transForward.jsp" method="post" name=sendOrder>' . // 不能省略
            "<input type='hidden' name='txCode' value='TP001'>" .                  // 交易代码 (固定值不可修改)
            "<input type='hidden' name='merchantId' value='$data_pay_account'>" .  // 网汇通商户号
            "<input type='hidden' name='transDate' value='$data_order_time'>" .    // 交易日期
            "<input type='hidden' name='transFlow' value='$data_order_id'>" .      // 交易流水号
            "<input type='hidden' name='orderId' value='$order[order_sn]'>" .      // 订单号
            "<input type='hidden' name='curCode' value='156'>" .                   // 币种（固定值不可修改）
            "<input type='hidden' name='amount' value='$data_amount'>" .           // 订单金额
            "<input type='hidden' name='orderInfo' value=''>" .      // 订单信息（测试商户请在此输入贵公司名称信息）
            "<input type='hidden' name='comment' value=''>" .               // 附加信息
            "<input type='hidden' name='merURL' value='$data_notify_url'>" .       // 接收网汇通系统的支付结果信息的URL
            "<input type='hidden' name='interfaceType' value='3'>" .               // 接口模式
            "<input type='hidden' name='sign' value='$RsaDecrypt'>" .              // 交易数据签名信息
			//"<input type='submit' value='" . $GLOBALS['_LANG']['udpay_button'] . "'>" . // 按钮
            '<input type="image" class="cart_end_bt" src="http://www.easeeyes.com/themes/default/images/cart/pay_'.$order['pay_id'].'.gif" value="">'. 
            "</form>";
			
			//file_put_contents('youzheng.txt', $def_url) ;		

        return $def_url;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        $payment     = get_payment('udpay');
        $merchant_id = $payment['udpay_account']; // 获取商户编号

        $udpay_whtpublicModulus  = $payment['udpay_whtpublicModulus'];
        $udpay_whtpublicExponent = $payment['udpay_whtpublicExponent'];

        /* read the post from udpay system and add 'cmd' */
        $req = 'cmd=_notify-validate';
        foreach ($_GET AS $key => $value)
        {
            $value = urlencode(stripslashes($value));
            $req  .= "&$key=$value";
        }

        /* post back to udpay system to validate */
        //$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        //$header .= "Content-Length: " . strlen($req) ."\r\n\r\n";

        /* 获取网汇通get过来的需要签名的数据 */
        $sign = $_GET['sign'];
          $msg = "txCode=$_GET[txCode]&merchantId=$_GET[merchantId]&transDate=$_GET[transDate]&transFlow=$_GET[transFlow]&orderId=$_GET[orderId]&curCode=$_GET[curCode]&amount=$_GET[amount]&orderInfo=$_GET[orderInfo]&comment=$_GET[comment]&whtFlow=$_GET[whtFlow]&success=$_GET[success]&errorType=$_GET[errorType]";
        /* 公钥数据 */
        $publicModulus  = $udpay_whtpublicModulus;
        $publicExponent = $udpay_whtpublicExponent;
        $verifySigature = $this-> verifySigature($msg, $sign, $publicExponent, $publicModulus);
        if ($verifySigature)
        {
            order_paid($_GET['transFlow']);
        }

        return $verifySigature;
    }

    function generateSigature($message, $exponent, $modulus)
    {
        $md5Message = md5($message);
        $fillStr    = '01ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff003020300c06082a864886f70d020505000410';
        $md5Message = $fillStr.$md5Message;
        $intMessage = $this->bin2int(@$this->hex2bin($md5Message));
        $intE       = $this->bin2int(@$this->hex2bin($exponent));
        $intM       = $this->bin2int(@$this->hex2bin($modulus));
        $intResult  = $this->powmod($intMessage, $intE, $intM);
        $hexResult  = bin2hex($this->int2bin($intResult));

        return $hexResult;
    }

    function verifySigature($message, $sign, $exponent, $modulus)
    {
        $intSign     = @$this->bin2int($this->hex2bin($sign));
        $intExponent = @$this->bin2int($this->hex2bin($exponent));
        $intModulus  = @$this->bin2int($this->hex2bin($modulus));
        $intResult   = $this->powmod($intSign, $intExponent, $intModulus);
        $hexResult   = bin2hex($this->int2bin($intResult));
        $md5Message  = md5($message);
        if ($md5Message == substr($hexResult, -32))
        {
            return '1';
        }
        else
        {
            return '0';
        }
    }

    function hex2bin($hexdata)
    {
        for ($i = 0, $count = strlen($hexdata); $i < $count; $i += 2)
        {
            $bindata = chr(hexdec(substr($hexdata, $i, 2))) . $bindata;
        }

        return $bindata;
    }

    function bin2int($str)
    {
        $result = '0';
        $n = strlen($str);

        do
        {
            $result = bcadd(bcmul($result, '256'), ord($str{--$n}));
        } while ($n > 0);

        return $result;
    }

    function int2bin($num)
    {
        $result = '';

        do
        {
            $result= chr(bcmod($num, '256')) . $result;
            $num = bcdiv($num, '256');
        } while (bccomp($num, '0'));

        return $result;
    }

    function powmod($num, $pow, $mod)
    {
        $result = '1';

        do
        {
          if (!bccomp(bcmod($pow, '2'), '1'))
          {
              $result = bcmod(bcmul($result, $num), $mod);
          }
          $num = bcmod(bcpow($num, '2'), $mod);
          $pow = bcdiv($pow, '2');
      } while (bccomp($pow, '0'));

      return $result;
    }
}

?>