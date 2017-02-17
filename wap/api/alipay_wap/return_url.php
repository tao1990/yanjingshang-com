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

define('IN_ECS', true);
require('../../includes/init.php');
include_once(ROOT_PATH . 'includes/lib_passport.php');
include_once(ROOT_PATH . 'includes/lib_payment.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
include_once(ROOT_PATH . 'includes/modules/payment/alipay_wap.php');

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

//计算得出通知验证结果
$alipayNotify  = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyReturn();
//var_dump($verify_result);exit();

//验证成功
if($verify_result){
    // 修改订单操作
    $alipay = new alipay();
    $res = $alipay->respond();
    if($res){          // 修改订单成功
        header("Location: respond.php");
    }else{             // 修改订单失败
        echo "很抱歉！系统出现异常，请重新操作！";
        echo "<a href='/'>返回个人中心</a>";
    }
}
//验证失败
else
{
    //如要调试，请看alipay_notify.php页面的return_verify函数，比对sign和mysign的值是否相等，或者检查$veryfy_result有没有返回true
    echo "很抱歉！用支付宝账号登录失败！<br/>";
    echo "<a href='http://www.easeeyes.com'>返回首页直接购买</a>";
}
?>