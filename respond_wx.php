<?php
define('IN_ECS', true);
ini_set("display_errors",1);
require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_payment.php');
require(dirname(__FILE__) . '/includes/lib_order.php');
require_once ROOT_PATH."wxpay/lib/WxPay.Api.php";
require_once ROOT_PATH.'wxpay/lib/WxPay.Notify.php';
require_once ROOT_PATH.'wxpay/unit/log.php';


//测试回传数据
//$data = '{"appid":"wxf5e3f7df9c201bf9","attach":"u5faeu4fe1u652fu4ed8","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1242270802","nonce_str":"uizrtovsnhtecepwhexoeg8euwwfw76o","openid":"opkvltw2qWR7jCaqZr-7DtsSZk5U","out_trade_no":"2015080560956","result_code":"SUCCESS","return_code":"SUCCESS","sign":"5D2D8431EB2D6237CF76BD1FFAAC5098","time_end":"20150805142357","total_fee":"1","trade_type":"NATIVE","transaction_id":"1004360236201508050554872842"}';
//$data = json_decode($data);
//$msg = 'OK';
//var_dump($data);die;

//初始化日志
$logHandler= new CLogFileHandler(ROOT_PATH."wxpay/logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
	
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}else{
          //order_sn查询order_id-》order_id查询log_id
          $order_id = $GLOBALS['db']->getOne("SELECT order_id FROM ".$GLOBALS['ecs']->table('order_info')."  WHERE order_sn = ".$data["out_trade_no"]." LIMIT 1");
          $log_id   = $GLOBALS['db']->getOne("SELECT log_id FROM ".$GLOBALS['ecs']->table('pay_log')."  WHERE order_id = ".$order_id." LIMIT 1");
          
          order_paid($log_id);
 
		  return true;
		}
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
//$notify->NotifyProcess($data,$msg);
$notify->Handle(true);








exit();

assign_template();
/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',           $position['title']);    
$smarty->assign('ur_here',              $position['ur_here']);  
$smarty->assign('topbanner',            ad_info(31,1));           //头部横幅广告
//页尾
$smarty->assign('helps',                get_shop_help());         //网店帮助文章
$smarty->assign('new_articles_botter',  index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',            ad_info(12,8));           //营业执照行
$cat_tree = get_category_tree();                     			  //分类列表
$smarty->assign('cat_1',        		$cat_tree[1]);
$smarty->assign('cat_6',				$cat_tree[6]);
$smarty->assign('cat_64',				$cat_tree[64]);
$smarty->assign('cat_76',				$cat_tree[76]);	
$smarty->assign('cat_159',				$cat_tree[159]);
$smarty->assign('cat_190',				$cat_tree[190]);
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $ecs->url());
$smarty->assign('pay_code',   $pay_code);//支付方式代码

$smarty->display('respond.dwt');
