<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数AlipayFunction.logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 */

//==================================================支付宝登录处理 yijiangwen============================================||

define('IN_ECS', true);
require('../../includes/init.php');
include_once(ROOT_PATH . 'includes/lib_passport.php');

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");


//计算得出通知验证结果
$alipayNotify  = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyReturn();

//验证成功
if($verify_result){

    //返回参数
	//http://localhost/api/alipay/return_url.php?email=yijiangwen163%40163.com&is_success=T&notify_id=RqPnCoPT3K9%252Fvwbh3I7w5E2CxNZ%252FBEd%252FCT6G3LGMO7%252FQ9WnjLqkjSIbxJP7Dymo3Nyr2&real_name=%E6%98%93%E6%B1%9F%E6%96%87&token=201107209bf89b48ac694180a0e4bff76ac8b4a1&user_id=2088102420475405&sign=fe3510d840aefabfa0b3a8c91afd8700&sign_type=MD5

	//测试入口：http://localhost/api/alipay/alipay_auth_authorize.php

	/*=====================================接口中参数=====================================*/
    $user_id	= $_GET['user_id'];	   //支付宝用户id
    $token		= $_GET['token'];	   //授权令牌
	$is_success	= $_GET['is_success']; //
	
	$sign_type	= $_GET['sign_type'];
	$sign		= $_GET['sign'];
	$_SESSION['alipay_token'] = $token;//记录token到session中

	$user_name = isset($_GET['real_name'])? trim($_GET['real_name']): "";
	$email     = isset($_GET['email'])?     trim($_GET['email']): "";
	$etao_url  = isset($_GET['target_url'])?trim($_GET['target_url']): "";
	/*=====================================================================================*/
	
	//如果不存在真实姓名
	if(empty($user_name)){
		$user_name = "支付宝会员:".$user_id;
	}

	//如果没有返回用户邮箱/mobile
	if(empty($email)){
		$email = "ali".$user_id."@alipay.com";
	}else{
		if(!eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$',$email)){
			$email = $email.'@alipay.com';
		}
	}

	$url = "http://m.easeeyes.com/api/login_ali.php?real_name=".urlencode($user_name)."&email=".$email."&ali_user_id=".$user_id;

	//如果etao专用则增加跳转url
	if(!empty($etao_url)){
		$url .= "&turn_url=".urlencode($etao_url)."\n";
	}else{
		$url .= "\n";
	}
	header("Location: ".$url);
}
//验证失败
else
{	
    //如要调试，请看alipay_notify.php页面的return_verify函数，比对sign和mysign的值是否相等，或者检查$veryfy_result有没有返回true
    echo "很抱歉！用支付宝账号登录失败！<br/>";
	echo "<a href='http://m.easeeyes.com'>返回首页直接购买</a>";
}
?>