<?php
/* =======================================================================================================================
 * 物流
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

$user_id = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']): 0;
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'list';

if(empty($user_id)){
	header("Location:user.php");
}


if($action=='list'){
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    
    $wuliu_list = get_wuliu_list($user_id);

    $smarty->assign('wuliu_list', $wuliu_list);

    $smarty->assign('ur_here', "物流查询");
    $smarty->assign('page_title', "物流查询 - 易视网手机版");
    $smarty->display('wuliu_list.dwt');
}elseif($action=='detail'){
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //订单id

    //订单详情(信息)
    $order = get_order_detail($order_id, $user_id);

    /* 把快递单号和 快递公司名剥离 并且去掉以前的超链接 */
    if($order['shipping_status'] == 1){
        $invoice = explode("-",$order['invoice_no_old']);
        //var_dump($invoice);
        if(count($invoice) > 1){
            $order['invoice_number'] = $invoice[1];
            $order['invoice_name'] = $invoice[0];
        }
    }else{
        $order['invoice_number'] = "";
        $order['invoice_name'] = "";
    }
    /* 订单 支付 配送 状态语言项 */
    $order['order_status'] = @$_LANG['os'][$order['order_status']];
    $order['pay_status'] = @$_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = @$_LANG['ss'][$order['shipping_status']];
    
    /* 订单商品 */
    $goods_list = order_goods($order_id);
    if($goods_list){
        foreach ($goods_list AS $key => $value)
        {
            $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
            $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
            $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
        }
    }
    if(@$order['invoice_name'] == "申通"){
        /*物流信息-申通*/
        $wuliu = get_wuliu_st($order['invoice_number']);
        $wuliu = '暂时没有此物流信息,<a style="color:red" href="http://q1.sto.cn/chaxun/result" target="_blank">直接去申通官网查询</a>';
    }else{
        $wuliu = '暂时没有此物流信息,<a style="color:red" href="http://q1.sto.cn/chaxun/result" target="_blank">直接去申通官网查询</a>';
    }
    $smarty->assign('ur_here', "物流详情");
    $smarty->assign('page_title', "物流详情 - 易视网手机版");
    $smarty->assign('wuliu_info', $wuliu);
    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('order', $order);
    $smarty->display('wuliu.dwt');
}elseif($action=='eles'){

    $smarty->display('test.dwt');
}
/**
 *申通 
 */
function get_wuliu_st($txtbill){
    $url = 'http://q.sto.cn/track.aspx?wen='.$txtbill;
    $wuliu = file_get_contents($url);
    $patt='/<table cellpadding="0" cellspacing="0" class="tab_result" width="100%">(.*)<\/table>/s';
    preg_match($patt, $wuliu, $matches);
    if(!empty($matches)){
        $matches = $matches[1];
    }else{
        $matches = '暂时没有此物流信息';
    }
    return $matches;
}

/**
 *中通 
 */
function get_wuliu_zt($txtbill){
    $url = 'http://www.zto.cn/GuestService/Bill?txtbill='.$txtbill;
    $wuliu = file_get_contents($url);
    $patt='/<div class="state">(.*)<div class="clearfix "><\/div>/s';
    preg_match($patt, $wuliu, $matches);
    $patt2 ='/(.*)<\/div>
            <div class="clearfix "><\/div>/si';
    preg_match($patt2,$matches[0],$matches2);
    
    return $matches2[1];
}


/**
 * 物流list
 */
function get_wuliu_list($user_id){
    $res = '';
    if($user_id!=0){
        $last_day = strtotime(date('Y-m-d H:i:s', time() - 60*60*24*180));    //半年内数据
        $where = " and`add_time` >= '".$last_day."'";
        $sql = mysql_query("SELECT order_id, order_sn, order_status, shipping_status, pay_status, add_time, " .
               "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee FROM ".$GLOBALS['ecs']->table('order_info')." WHERE user_id = ".$user_id." and (order_status=1 or order_status=5) and shipping_status = 1 ".$where." ORDER BY add_time DESC");
    
        while($arr = mysql_fetch_array($sql,MYSQL_ASSOC)){
            $arr['add_time'] = date('Y-m-d H:i',$arr['add_time']);
            $arr['other']    = get_wuliu_order_row($arr['order_id']);
            $res[] = $arr;
        }
    }
    
    return $res;  
     
}
/**
 * 物流信息附加内容
 */
function get_wuliu_order_row($order_id){
        $res = '';
    if($order_id){
       $sql = "SELECT a.goods_id,a.goods_name,b.goods_thumb,sum(a.goods_number) as num FROM ".$GLOBALS['ecs']->table('order_goods')." as a left join ".$GLOBALS['ecs']->table('goods')." as b on a.goods_id=b.goods_id WHERE a.order_id  = ".$order_id;
        $res =$GLOBALS['db']->getRow($sql); 
    }
        return $res;
    
}

?>