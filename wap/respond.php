<?php
/* ============================================================================
 * 支付响应页面 2012/12/19
 * ============================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');





//支付方式代码
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
$pay_code = htmlspecialchars($pay_code);

//参数是否为空
if(empty($pay_code))
{
    $msg = $_LANG['pay_not_exist'];
}
elseif($pay_code == 'kuaiqian')
{
    //检查code里面有没有问号
    if (strpos($pay_code, '?') !== false)
    {
        $arr1 = explode('?', $pay_code);
        $arr2 = explode('=', $arr1[1]);

        $_REQUEST['code']   = $arr1[0];
        $_REQUEST[$arr2[0]] = $arr2[1];
        $_GET['code']       = $arr1[0];
        $_GET[$arr2[0]]     = $arr2[1];
        $pay_code           = $arr1[0];
    }

    //判断是否启用
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
    if ($db->getOne($sql) == 0)
    {
        $msg = $_LANG['pay_disabled'];
    }
    else
    {
        $plugin_file = ROOT_PATH.'includes/modules/payment/'.$pay_code.'.php';

        //检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息
        if (file_exists($plugin_file))
        {
            //根据支付方式代码创建支付类的对象并调用其响应操作方法
            include_once($plugin_file);
            
            $payment = new $pay_code();
            $msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];

        }
        else
        {
            $msg = $_LANG['pay_not_exist'];
        }
    }
}elseif($pay_code == 'alipay'){
    

        $msg = '支付成功';
}


/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',           $position['title']);    
$smarty->assign('ur_here',              $position['ur_here']);
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('page_title', "支付成功 - 易视网手机版");
$smarty->assign('message',    $msg);
$smarty->display('respond.dwt');
?>