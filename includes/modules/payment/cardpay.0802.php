<?php
/* ============================================================================
 * 商城 预付费卡支付插件【2013/4/9】【author:yijiangwen】
 * ============================================================================
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$payment_lang = ROOT_PATH.'languages/'.$GLOBALS['_CFG']['lang'].'/payment/cardpay.php';
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
    $modules[$i]['desc']      = 'cardpay_desc';             //描述对应的语言项
    $modules[$i]['is_cod']    = '0';                        //是否支持货到付款
    $modules[$i]['is_online'] = '1';                        //是否支持在线支付
    $modules[$i]['author']    = 'YI JIANGWEN';              //作者
    $modules[$i]['website']   = 'http://www.ce9.com';       //网址
    $modules[$i]['version']   = '1.0';                      //版本号

    //配置信息
    $modules[$i]['config'] = array(
        array('name' => 'kq_account', 'type' => 'text', 'value' => ''),
        array('name' => 'kq_key', 'type' => 'text', 'value' => ''),
    );
    return;
}

class cardpay
{
	//构造函数
    function cardpay()
    {
    }

    function __construct()
    {
        $this->cardpay();
    }

   /**
     * 生成支付代码
     * @param   array   $order    订单信息
     * @param   array   $payment  支付方式信息
     */
   function get_code($order, $payment)
   {

		$merchant_id		= 20012;								//商户编号，人家提供。
		$m_key				= "5f69n3c9nstd89q6w5e0vg655nxlk31v";	//商户密钥，人家提供。

		//$page_url         = return_url(basename(__FILE__,'.php'));//返回支付结果处理。
		$receive_url		= 'http://www.easeeyes.com/respond.php?code=cardpay';//请填写返回url,地址应为绝对路径,带有http协议
		$auto_receive_url	= 'http://www.easeeyes.com/autorespond.php?code=cardpay';//请填写自动通知对帐url,地址应为绝对路径,带有http协议	
		$input_charset		= "UTF-8";								//商户网站编码，UTF-8或GBK/GB2312
		$channel_type		= "ct002";								//支付渠道类型 不变
		
		$amount				= $order['order_amount'];				//订单金额。元为单位。
		$name				= "眼镜商品";							//商品名字
		$desc				= "易视网 保证正品";					//商品描述
		$remark				= $order['log_id'];						//订单备注信息（pay_log）
		$order_id			= $order['order_sn'];					//订单ID


		//【开通网银直链功能】【购物车进来和非购物车进来】
		$pay_id     = isset($order['pay_id'])? intval($order['pay_id']) :13;//卡支付ID【13】
		$bank_id_yi = isset($order['bank_id'])?intval($order['bank_id']):0; //银行编号

		if($pay_id>800)
		{
			$bank_id_yi = $pay_id;
			$pay_id     = 13;
		}

		$code = array(
				'801'=>'sumpay',	'802'=>'',		'803'=>'heepay',	'804'=>'',
				'805'=>'fuioupay',  '806'=>'jxjft',	'807'=>'',			'808'=>'',
				'809'=>'',			'810'=>'',		'811'=>'verypass',	'812'=>'',
				'813'=>'',			'814'=>'',		'815'=>'',			'816'=>'sanwing',
				'817'=>'',			'818'=>'',		'819'=>'',			'820'=>'aosicard'
			);
		$prepaid_card = $code[$bank_id_yi]; //预付卡代码


		$key_end = md5($merchant_id.'&'.$order_id.'&'."ct002".'&'.$prepaid_card.'&'.$amount.'&'.$name.'&'.$desc.'&'.$remark.'&'.$receive_url.'&'.$auto_receive_url.'&'.$m_key);//注意顺序不变
		//接口通行密钥。做验证使用。

		//生成支付按钮代码
		$def_url  = '<div style="text-align:center"><form name="cardpay" style="text-align:center;" method="post" action="http://pc.ce9.com:8080/pay_gate.ce" target="_blank">';
		$def_url .= "<input type='hidden' name='merchant_id'	value='".$merchant_id."' />";
		$def_url .= "<input type='hidden' name='input_charset'	value='".$input_charset."' />";
		$def_url .= "<input type='hidden' name='order_id'		value='".$order_id."' />";
		$def_url .= "<input type='hidden' name='channel_type'	value='".$channel_type."' />";

		$def_url .= "<input type='hidden' name='prepaid_card' value='".$prepaid_card."' />";
		$def_url .= "<input type='hidden' name='amount' value='".$amount."' />";
		$def_url .= "<input type='hidden' name='name' value='".$name."' />";
		$def_url .= "<input type='hidden' name='desc' value='".$desc."' />";

		$def_url .= "<input type='hidden' name='receive_url' value='".$receive_url."' />";
		$def_url .= "<input type='hidden' name='auto_receive_url' value='".$auto_receive_url."' />";
		$def_url .= "<input type='hidden' name='key' value='".$key_end."' />";
		$def_url .= "<input type='hidden' name='remark' value='".$remark."' />";

		if($bank_id_yi > 800)
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


	/* ----------------------------------------------------------------------------------------------------------------------
	 * yi:对支付结果做出响应。并即时变更这个订单的支付状态。这个方法和respond.php这个页面是一起的。
	 * ----------------------------------------------------------------------------------------------------------------------
	 */
    function respond()
    {
        $payment        = get_payment($_GET['code']);
		//验证接收到的数据源的合法性

		//key的计算方式md5(merchant_id+t_number+order_id+status+message+amount+remark+m_key)
		$m_key			= "5f69n3c9nstd89q6w5e0vg655nxlk31v";	//商户密钥，人家提供。
		$merchant_id	= 20012;								//商户编号，人家提供。
		$order_id		= trim($_REQUEST['order_id']);
		$t_number		= intval($_REQUEST['t_number']);
		$status			= intval($_REQUEST['status']);			//0等待支付，1支付成功，2无效订单，3,退款成功
		$message		= trim($_REQUEST['message']);
		$amount			= floatval($_REQUEST['amount']);
		$remark			= trim($_REQUEST['remark']);
		$key            = $_REQUEST['key'];

		//$this_key = md5($merchant_id.$t_number.$order_id.$status.$message.$amount.$remark.$m_key);//加工后无效。
		$this_key   = md5($merchant_id.$_REQUEST['t_number'].$_REQUEST['order_id'].$_REQUEST['status'].$_REQUEST['amount'].$_REQUEST['remark'].$m_key);


		if($merchant_id == '')
		{
			//return false;//商户编号错误
		}

        if(strtoupper($this_key) == strtoupper($key))
        {
            if(1 == $status)
            {
                order_paid($remark);
                return true;
            }
            else
            {
                return false;//支付结果失败
            }
        }
        else
        {            
            return false;//密钥校对错误
        }
    }
}
?>