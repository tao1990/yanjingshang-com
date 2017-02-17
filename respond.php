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


//获取首信支付方式
if(empty($pay_code) && !empty($_REQUEST['v_pmode']) && !empty($_REQUEST['v_pstring']))
{
    $pay_code = 'cappay';
}
//获取快钱神州行支付方式
if (empty($pay_code) && ($_REQUEST['ext1'] == 'shenzhou') && ($_REQUEST['ext2'] == 'ecshop'))
{
    $pay_code = 'shenzhou';
}

//参数是否为空
if(empty($pay_code))
{
    $msg = $_LANG['pay_not_exist'];
}
else
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
            
            if($msg == $_LANG['pay_success'] && $pay_code == 'kuaiqian' && $_REQUEST['redirect']!= 1){
                //快钱返回标识（必须）
                echo '<result>1</result> <redirecturl>http://www.easeeyes.com/respond.php?code=kuaiqian&redirect=1</redirecturl>';die;
                //$smarty->assign('kq_res',				1);
            }
            if($msg == $_LANG['pay_success'] && $pay_code == 'alipay' && $_REQUEST['isnotify'] == 1){
                //快钱返回标识（必须）
                echo 'success';die;
                //$smarty->assign('kq_res',				1);
            }elseif($msg != $_LANG['pay_success'] && $pay_code == 'alipay' && $_REQUEST['isnotify'] == 1){
                echo 'fail';die;
            }
            
			//-------------------------------------------------------支付成功页面活动-------------------------------------------------------//
			
            
			//-------------------------------------------------------支付成功页面活动-------------------------------------------------------//   
        }
        else
        {
            $msg = $_LANG['pay_not_exist'];
        }
    }
}

assign_template();
/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',           $position['title']);    
$smarty->assign('ur_here',              $position['ur_here']);  
$smarty->assign('topbanner',            ad_info(31,1));           //头部横幅广告
//页尾

/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('message',    $msg);
$smarty->assign('captype',    $captype);
$smarty->assign('quan',    $quan);
$smarty->assign('shop_url',   $ecs->url());
$smarty->assign('pay_code',   $pay_code);//支付方式代码

$smarty->display('respond.dwt');
?>