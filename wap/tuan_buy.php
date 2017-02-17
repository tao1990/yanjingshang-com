<?php
/* ============================================================================
 * 商城页面 超值礼包详情页面
 * ============================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/pf_cart.php');
date_default_timezone_set('PRC');

$now       = $_SERVER['REQUEST_TIME'];
$rec_id    = isset($_REQUEST['rec_id'])? intval($_REQUEST['rec_id']): 0;

//团购商品提交到购物车
if (@$_REQUEST['act'] == 'tuan_to_cart')
{

	$tuan_id = isset($_POST['tuan_id'])? intval($_POST['tuan_id']): 0;
	$to_cart_value = isset($_POST['to_cart_value'])? $_POST['to_cart_value']: NULL;	//提交过来的商品参数数组

    /*YI：新增度数商品是否选择度数的判断*/
    $tuan_goods = $GLOBALS['db']->getAll("SELECT goods_id FROM ecs_tuan_goods WHERE tuan_id = '".$tuan_id."'");
    rsort($tuan_goods);
    foreach($to_cart_value as $k => $v){

        $v = explode('|', $v);
        $have_ds = $GLOBALS['db']->getOne("SELECT val FROM ecs_ds WHERE gid = ".$v[0]);

        if(!empty($have_ds)){
            if(empty($v[1])){
                show_message_wap('请选择您的团购商品度数！(づ-3-)づ╭?～！');
            }
        }
    }

    
    if ($tuan_id && $to_cart_value) {

        $tuan_info = $GLOBALS['db']->getRow("SELECT * FROM ecs_tuan WHERE rec_id='$tuan_id' LIMIT 1");

        
        /*团购限购订单数tao*/
        if($tuan_info['order_limit'] > 0 ){
            $order_arr = array();
            $orderIdArr = $GLOBALS['db']->getAll("SELECT order_id FROM ecs_order_goods WHERE goods_sn = ".$tuan_id." AND extension_code = 'tuan_buy'");
            foreach($orderIdArr as $v){
                $order_arr[] = $v['order_id'];
            }
            $order_arr = array_unique($order_arr);
            $i = 0;
            foreach($order_arr as $v){
                $have_paid = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE order_id = ".$v." AND (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5)))  ");
                
                if($have_paid){
                    $i+=1;
                }
            }
            if($i >= $tuan_info['order_limit']){
                //取消该团购
                $over_time = time()-86400;
                $GLOBALS['db']->query("UPDATE ecs_tuan SET end_time = '".$over_time."', tuan_name =concat('【已结束】',tuan_name) WHERE rec_id = ".$tuan_id);
                echo "<script>alert('本团购活动已经结束，请关注易视网更多其他精彩活动');window.history.go(-1);</script>";die;
                exit();
            }
        }
        
        
        //促销状态(在促销期间,执行促销价)
        if ($tuan_info['is_promotion'] == 1 && $tuan_info['promotion_start_time'] <= $now && $tuan_info['promotion_end_time'] >= $now ) {
            $tuan_info['promotion_status'] = 1;
        } else {
            $tuan_info['promotion_status'] = 0;
        }

        //插入购物车
        $i = 0;
        $sjs = rand(111,999);
        foreach ($to_cart_value as $cv) {
            $temp_arr = explode('|', $cv);
            if ($i == 0) {
                $goods_sn = $tuan_id;//$goods_sn = '1';							//设置主商品标识(用于插入礼包价格和在购物车页面更改数量)
                $market_price = $tuan_id;					//团购活动ID
                $parent_id = 0;
                if ($tuan_info['promotion_status'] == 1) {
                    $goods_price = $tuan_info['promotion_price'];	//团购价格：促销价
                } else {
                    $goods_price = $tuan_info['tuan_price'];		//团购价格：正常价格
                }

            } else {
                $goods_sn = '';
                $market_price = 0.00;
                $goods_price = 0.00;
                $parent_id = $sjs;
            }

            $goods_info = get_simple_goods_info($temp_arr[0]);
            $tuan_is_cx = $tuan_info['is_cx'];
            $sql = "INSERT INTO ecs_cart (user_id, session_id, goods_id, goods_sn, goods_name, market_price,
					goods_price, goods_number, goods_attr, is_real, extension_code, extension_id, is_cx, is_shipping, goods_attr_id,parent_id)
					VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '$temp_arr[0]', '$goods_sn', '".$goods_info['goods_name']."',
					$market_price, $goods_price, 1, '".$temp_arr[1]."', 1, 'tuan_buy', $sjs, $tuan_is_cx, ".$tuan_info['is_no_freight'].", '".$goods_info['goods_img']."',$parent_id)" ;
            $GLOBALS['db']->query($sql);
            
            $i++;
        }

        //购买人数加1
        $add_cart_tuan = $GLOBALS['db']->query("UPDATE ecs_tuan SET buyers=buyers+1 WHERE rec_id=".$tuan_id);

        if($add_cart_tuan){
            Header("Location:flow.php");
        }else{
            echo "<script>alert('网络错误,请稍后重试^_^');window.history.go(-1);</script>";die;
        }
    }
}

/*------------------------------------页头 页尾 数据---------------------------------------*/
$page_title = $GLOBALS['db']->getOne("SELECT tuan_name from ecs_tuan WHERE rec_id=$rec_id limit 1;");
$position = assign_ur_here();
$smarty->assign('page_title',          $page_title.' - 给力的隐形眼镜团购');
$smarty->assign('ur_here',             '团购详情');
$smarty->assign('get_new_fl',          index_get_new_fl(21));			
$smarty->assign('topbanner',           ad_info(31,1));					
$smarty->assign('helps',               get_shop_help());				
$smarty->assign('new_articles_botter', index_get_new_articles_botter());	
$smarty->assign('botbanner',           ad_info(12,8));					
$cat_tree = get_category_tree();										
/*$smarty->assign('cat_1',        		$cat_tree[1]);
$smarty->assign('cat_6',				$cat_tree[6]);
$smarty->assign('cat_64',				$cat_tree[64]);
$smarty->assign('cat_76',				$cat_tree[76]);	
$smarty->assign('cat_159',				$cat_tree[159]);	
$smarty->assign('cat_190',				$cat_tree[190]);*/
$smarty->assign('sale_order1',  		yi_sale_sort_list(1) );
$smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
$smarty->assign('sale_order64', 		yi_sale_sort_list(64));
$smarty->assign('sale_order76', 		yi_sale_sort_list(76));
$smarty->assign('sale_order159', 		yi_sale_sort_list(159));
$smarty->assign('sale_order190', 		yi_sale_sort_list(190));
/*------------------------------------页头 页尾 数据end------------------------------------*/

$sql = "SELECT * FROM ecs_tuan WHERE rec_id = '$rec_id' LIMIT 1;";
$pak = $GLOBALS['db']->getRow($sql);

if(empty($pak))
{
	show_message_wap('很抱歉，该礼包已经结束或下架！'); exit;//团购不存在
}
else
{
	$tuan_info = $pak; //礼包信息
	
	//缩略图
	//$temp_img = explode('.', $tuan_info['focus_img']);
	//$tuan_info['thumb_img'] = $temp_img[0] . '_thumb.' . $temp_img[1];
	
	//团购状态标识：0:未开始  1:进行中	2:已结束
	if ($pak['start_time'] > $now) {
		$tuan_info['tuan_status'] = 0;
	} elseif ($pak['start_time'] <= $now && $pak['end_time'] >= $now) {
		$tuan_info['tuan_status'] = 1;
	} else {
		$tuan_info['tuan_status'] = 2;
	}
	
	//促销状态(在促销期间,执行促销价)
	if ($pak['is_promotion'] == 1 && $pak['promotion_start_time'] <= $now && $pak['promotion_end_time'] >= $now ) {
		$tuan_info['promotion_status'] = 1;
	} else {
		$tuan_info['promotion_status'] = 0;
	}
	
	//市场价、原价
	$package_price = $GLOBALS['db']->GetRow("SELECT SUM(a.market_price*b.goods_number) AS market_price, SUM(a.shop_price*b.goods_number) AS shop_price FROM ecs_goods a LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id WHERE b.tuan_id = $rec_id");
	$tuan_info['market_price']	= 	sprintf("%01.2f", $package_price['market_price']);	//市场价
	$tuan_info['shop_price'] 	= 	sprintf("%01.2f", $package_price['shop_price']);	//易视原价(各商品销售价总和)
    
    //促销活动价(如果在促销期间,显示促销价)
    if ($tuan_info['promotion_status'] == 1) {
    	$temp_package_price = explode('.', $tuan_info['promotion_price']);
    } else {
    	$temp_package_price = explode('.', $tuan_info['tuan_price']);
    }
    $tuan_info['package_price_1'] = $temp_package_price[0];	//整数部分
    $tuan_info['package_price_2'] = $temp_package_price[1];	//小数部分
    
    //主商品信息
    $parent_goods_id = $GLOBALS['db']->GetOne("SELECT goods_id FROM ecs_tuan_goods WHERE tuan_id = $rec_id AND is_parent=1 LIMIT 1");
    if (!$parent_goods_id) $parent_goods_id = $GLOBALS['db']->GetOne("SELECT goods_id FROM ecs_tuan_goods WHERE tuan_id = $rec_id LIMIT 1");
    $goods = get_goods_info($parent_goods_id);
    // 产品详情中的图片URL替换
    $change = array('/images/upload/Image/'=>'http://www.easeeyes.com/images/upload/Image/','width: 750px'=>'width: 100%','width='=>'width="100%"','height='=>'height="auto"');
    $goods['goods_desc'] = strtr($goods['goods_desc'],$change);

    //团购活动所有商品信息
	$tuan_goods = $GLOBALS['db']->GetAll("SELECT * FROM ecs_tuan_goods WHERE tuan_id = $rec_id ORDER BY is_parent DESC");
	
	//所有商品总数
	$total_number = get_tuan_goods_number($rec_id);
	
	$p_goods_id_array = array();	//团购商品ID数组
	$p_goods_info = array();		//团购商品信息
	if ($tuan_goods)
	{
		$index = 0;
		foreach ($tuan_goods as $pk => $pv)
		{
			$p_goods_id_array[] = $pv['goods_id'];
			
			$goods_info = get_simple_goods_info($pv['goods_id']);	//商品简要信息
			//$goods_ds = get_goodsds_info($pv['goods_id']);			//商品度数
			$goods_ds = get_goods_ds($pv['goods_id']);
			
			if ($pv['goods_number'] > 1)
			{
				for ($x=0; $x<$pv['goods_number']; $x++)
				{
					$p_goods_info[$index]['goods_id'] = $pv['goods_id'];
					$p_goods_info[$index]['goods_name'] = $goods_info['goods_name'];
					$p_goods_info[$index]['goods_img'] = $goods_info['goods_img'];
					if ($pv['same_goods']) 
					{
						$p_goods_info[$index]['same_goods'] = get_same_goods_color($pv['goods_id'], $pv['same_goods']);
					}
					$p_goods_info[$index]['goods_ds'] = $goods_ds;
					
					$index++;
				}
			}
			else
			{
				$p_goods_info[$index]['goods_id'] = $pv['goods_id'];
				$p_goods_info[$index]['goods_name'] = $goods_info['goods_name'];
				$p_goods_info[$index]['goods_img'] = $goods_info['goods_img'];
				if ($pv['same_goods']) 
				{
					$p_goods_info[$index]['same_goods'] = get_same_goods_color($pv['goods_id'], $pv['same_goods']);
				}
				$p_goods_info[$index]['goods_ds'] = $goods_ds;
				
				$index++;
			}
			
			/*$p_goods_info[$pk]['goods_id'] = $pv['goods_id'];						//商品ID
			$goods_info = get_simple_goods_info($pv['goods_id']);					//名称、图片简要信息
			$p_goods_info[$pk]['goods_name'] = $goods_info['goods_name'];			//商品名称
			$p_goods_info[$pk]['goods_img'] = $goods_info['goods_img'];				//商品图片
			$p_goods_info[$pk]['goods_number'] = $pv['goods_number'];				//数量
			$p_goods_info[$pk]['is_parent'] = $pv['is_parent'];
			
			if ($pv['same_goods']) 
			{
				$p_goods_info[$pk]['same_goods'] = get_same_goods_color($pv['goods_id'], $pv['same_goods']); //商品及其同款商品信息(id,颜色,图片地址)
			}
			
			//商品度数(如果商品数量有多个，就有多个度数，值是一样的)
			$goods_ds = get_goodsds_info($pv['goods_id']);
			if ($goods_ds)
			{
				for ($j=0; $j<$pv['goods_number']; $j++)
				{
					$p_goods_info[$pk]['goods_ds'][$j] = $goods_ds;
				}
			}*/
			
		}
		$p_goods_id_str = implode(',', $p_goods_id_array);
	}
	
	//所有商品图片
	if ($p_goods_id_str)
	{
		$goods_img_array = $GLOBALS['db']->GetAll("SELECT img_url, thumb_url, img_original FROM ecs_goods_gallery WHERE goods_id IN ($p_goods_id_str) AND is_main=1");
	}
	
	//print_r($p_goods_info);exit;
	//print_r($tuan_info);
	//print_r($goods);
    $tuan_img_arr = array('thumb_url'=>$tuan_info['tuan_img']);
    //array_unshift($goods_img_array,$tuan_img_arr);                         //把团购图片也加入商品数组

	$smarty->assign('goods', $goods);									//主商品详情
	$smarty->assign('tuan_info', $tuan_info);							//团购详情
	$smarty->assign('p_goods_info', $p_goods_info);						//团购商品信息
	//$smarty->assign('p_goods_type_number', count($tuan_goods));			//团购商品种类数
	$smarty->assign('total_number', $total_number);						//团购商品数(包含同款商品)
	$smarty->assign('goods_img_array', $goods_img_array);				//礼包商品图片数组
    $smarty->assign('goods_img_main', $tuan_info['tuan_img']);			//主商品图片
    
	$smarty->assign('user_id',		$_SESSION['user_id']);
	$smarty->assign('user_name',	stripslashes($_SESSION['user_name']));
	$smarty->assign('cfg',			$_CFG);
	$smarty->assign('lang',			$_LANG);
	
	//格式化截止时间
	$format_end_time['Y'] = date('Y', $tuan_info['end_time']);
	$format_end_time['n'] = date('n', $tuan_info['end_time']);
	$format_end_time['j'] = date('j', $tuan_info['end_time']);
	$format_end_time['G'] = date('G', $tuan_info['end_time']);
	$format_end_time['i'] = date('i', $tuan_info['end_time']);
	$smarty->assign('format_end_time',		$format_end_time);
	
	//促销开始时间
	if ($tuan_info['promotion_start_time'] > $now) {
		$smarty->assign('promotion_start_time',	date('Y-m-d H:i:s', $tuan_info['promotion_start_time']));
		$smarty->assign('promotion_price',	$tuan_info['promotion_price']);
	}
	
	//【产品评论】通过外部代码插入进来的。在lib_insert.php文件中{insert name='comments' type=$type id=$id}		
	$smarty->assign('type',         0);
	$smarty->assign('id',           $parent_goods_id);
	$smarty->assign("goods_ids",    @$act_id);
	
	//页面大小
	$page_size = 5;

	//总记录数，当前页数，总页数
	$count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".$GLOBALS['ecs']->table('feedback')." WHERE goods_id =".$parent_goods_id);
	$page  = (isset($_GET['pages'])&&!empty($_GET['pages']))? intval($_GET['pages']): 1; 
	$page_count = ($count>0)? ceil($count/$page_size): 1;

	//前一页,后一页
	$page_prev = ($page>1) ? $page-1 : 1;
	$page_next = ($page<$page_count)? $page+1 : $page_count;

	//所有提问留言
	$feedback = array();
	$sqlf = "select * from ".$GLOBALS['ecs']->table('feedback')." where goods_id=".$parent_goods_id.
			" and msg_status=1 order by msg_time desc limit ".($page-1)*$page_size.",".$page_size.";";
	$feedback = $GLOBALS['db']->GetAll($sqlf);

	//遍历每条提问留言，然后找到它的回复留言。
	foreach($feedback as $k => $v)
	{
		$msg_id = $feedback[$k]['msg_id'];
		$sql_bk = "select msg_content from ".$GLOBALS['ecs']->table('feedback')." where parent_id='$msg_id' limit 1";
		$msg_re = $GLOBALS['db']->GetOne($sql_bk);
		$feedback[$k]['msg_re'] = $msg_re;
		$feedback[$k]['msg_time'] = date('Y-m-d', $feedback[$k]['msg_time']);
	}

	$smarty->assign("total1",     $count);
	$smarty->assign("page1",      $page);
	$smarty->assign("pagesize1",  $page_size);
	$smarty->assign("pagecount1", $page_count);	
	$smarty->assign("prev",       $page_prev);			      
	$smarty->assign("next",       $page_next);
	$smarty->assign("feedback",   $feedback);
	/*----------------------------------------------产品页【有问必答】列表------------------------------------------------------------*/


	//xu:产品属性功能 2012/9/9
	$attrs = get_goods_all_attr($parent_goods_id);
	$smarty->assign('attrs',    $attrs);		
	$smarty->assign('attr_kj',  get_kuangjia_attr($parent_goods_id));
	
	//yi:附加数据
	$append = $GLOBALS['db']->GetRow("select * from ".$GLOBALS['ecs']->table('goods_append')." where goods_id=".$parent_goods_id);
	$smarty->assign('append',  $append);
}

$smarty->display('tuan_buy.dwt');

//团购活动包含商品总数
function get_tuan_goods_number($tuan_id)
{
	if (intval($tuan_id) > 0) {
		return $GLOBALS['db']->getOne("SELECT SUM(goods_number) AS gn FROM ecs_tuan_goods WHERE tuan_id=$tuan_id");
	}
}

//获取商品信息(简明信息)
function get_simple_goods_info($goods_id=0) 
{
	if (intval($goods_id) > 0) {
		return $GLOBALS['db']->getRow("SELECT goods_id, goods_name, goods_img FROM ecs_goods WHERE goods_id=$goods_id");
	}
}

//获取同款产品的颜色属性(参数：主商品id, 同类商品id字符串)
function get_same_goods_color($goods_id=0, $goods_id_str='') 
{
	if ($goods_id && $goods_id_str)
	{
		$same_goods_array = array();
		
		//主商品
		$same_goods_array[0] = $GLOBALS['db']->getRow("SELECT goods_id, attr_value FROM ecs_goods_attr WHERE attr_id=212 AND goods_id=$goods_id LIMIT 1");
		$tmp = $GLOBALS['db']->getRow("SELECT goods_name, goods_img FROM ecs_goods WHERE goods_id=$goods_id");
		$same_goods_array[0]['goods_name'] = $tmp['goods_name'];		//商品名称
		$same_goods_array[0]['goods_img'] = $tmp['goods_img'];			//商品图片地址
		//$same_goods_array[0]['goods_ds'] = get_goodsds_info($goods_id);	//商品度数
		
		//同类商品
		$others = $GLOBALS['db']->getAll("SELECT goods_id, attr_value FROM ecs_goods_attr WHERE attr_id=212 AND goods_id IN ($goods_id_str)");
		foreach ($others as $k => $v)
		{
			$key = $k + 1;
			$same_goods_array[$key] = $v;
			//$same_goods_array[$key]['goods_img'] = $GLOBALS['db']->getOne("SELECT goods_img FROM ecs_goods WHERE goods_id=".$v['goods_id']);
			$tmp = $GLOBALS['db']->getRow("SELECT goods_name, goods_img FROM ecs_goods WHERE goods_id=".$v['goods_id']);
			$same_goods_array[$key]['goods_name'] = $tmp['goods_name'];
			$same_goods_array[$key]['goods_img'] = $tmp['goods_img'];
			//$same_goods_array[$key]['goods_ds'] = get_goodsds_info($v['goods_id']);
			unset($tmp);
		}
		
		return $same_goods_array;
	}
}

//买家秀页面
function mjx_info($goods_id=0)
{
	$mjx = array();
	$sql = "SELECT a.*, b.user_name FROM ".$GLOBALS['ecs']->table('mjx')." a left join ".$GLOBALS['ecs']->table('users')." b on a.user_id=b.user_id where a.sh=1 and a.goods_id=".
		   $goods_id." order by a.id desc limit 5";
	$mjx = $GLOBALS['db']->GetAll($sql);
	return $mjx;
}

/*
 * xu：获取该商品所有属性参数
 */
function get_goods_all_attr ($goods_id = 0) 
{
	if ($goods_id) {
		$attrs = array();
		$res = $GLOBALS['db']->query('SELECT attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr'). ' WHERE goods_id=' . $goods_id);
		while($row = $GLOBALS['db']->fetchRow($res)){
			$attrs[] = $row;
		}
		return $attrs;
	}
}

//yi:获得框架眼镜的尺寸属性
function get_kuangjia_attr($goods_id = 0)
{
	if(!empty($goods_id))
	{
		$sql = "select attr_id, attr_value from ".$GLOBALS['ecs']->table('goods_attr')." where goods_id=".$goods_id." and attr_id>249 and attr_id<255 order by attr_id asc;";
		$attr_kj = $GLOBALS['db']->getAll($sql);
		return $attr_kj;
	}
}

?>