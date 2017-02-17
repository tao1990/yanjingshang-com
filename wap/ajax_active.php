<?php
/* ======================================================================================================
 * ajax活动页面功能【2013/3/12】【Author:yijiangwen】
 * ======================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
date_default_timezone_set('PRC'); 
require_once('./upyun/upyun.class.php');
$upyun = new UpYun('easeeyes', 'zhuwentao', 's56766979');

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:交行抽奖活动
 * ----------------------------------------------------------------------------------------------------------------------
 */
if($_REQUEST['act'] == 'bocomm_chou')
{
	$order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']): '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '';
	
	if(!empty($order_sn))
	{
		$sql = "select * from ecs_order_info where order_sn='$order_sn' limit 1;";
		$res = $GLOBALS['db']->GetRow($sql);


		if(empty($res))
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_id']!=12)
		{
			$rt = 'no_bocomm'; $chou = false;//not bocomm pay
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		elseif(had_prize($order_sn))
		{
			$rt = 'had_prize'; $chou = false;//had_prize
		}
		else
		{
			//is ok
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1;");
		}
	}

	if($chou)
	{		
		$rank = get_prize(); //prize rank		

		//insert into db
		$sql = "insert into ecs_prize(user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values('$user_id', '$user_name', ".$_SERVER['REQUEST_TIME'].", '$rank', 'bocomm_chou', 'order_sn', '$order_sn');";
		if(!empty($rank))
		{
			$ret = mysql_query($sql);
		}

		//show date 
		$rt = 'ok'.','.$user_name.','.$order_sn.','.$rank;
	}
	else
	{
		if(empty($rt))
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
	}
	echo $rt;
}

//yi:限购商品能否加入购物车（一个商品在一个订单内的限购）
elseif($_REQUEST['act'] == 'goods_xg')
{
	$user_id      = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$goods_id     = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	$xg_goods_num = 0;
	$xg_num       = 1;//xg number
	
	if($user_id>0)
	{	
		$xg_goods_num = $GLOBALS['db']->getOne("select sum(goods_number) as cart_num from ecs_cart where user_id=".$user_id." and session_id='".SESS_ID."' and goods_id='$goods_id' ");
	}
	else
	{
		$xg_goods_num = $GLOBALS['db']->getOne("select sum(goods_number) as cart_num from ecs_cart where user_id=0 and session_id='".SESS_ID."' and goods_id='$goods_id' ");
	}	
	$can_qg = ($xg_goods_num>=$xg_num)? 0: 1;
	echo $can_qg;
}

//xu:周年庆限购：每个商品每天限购一个
elseif($_REQUEST['act'] == 'goods_xg_znq')
{
	$user_id      = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$goods_id     = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	$xg_goods_num = 0; //已购数量
	$xg_num       = 1;
	$can_qg		  = 1; //是否能购买:0.抢完 1.可购 2.已购
	
	$goods_id_str = ' (2636, 2637, 2638, 2639, 2640, 2641, 2643, 2644, 2648, 2645, 2647, 2665, 2650, 2651, 2652, 2653, 2662, 2664) ';
	
	$goods_define = array(2636, 2637, 2638, 2639, 2640, 2641, 2643, 2644, 2648, 2645, 2647, 2665, 2650, 2651, 2652, 2653, 2662, 2664);
	if (in_array($goods_id, $goods_define))
	{
		//商品每天限购数量
		$goods_limit = array();
		//if (date('G') >= 11 && date('G') < 13)
		if (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
		{
			$goods_limit = array(
					'2636' => 0,
					'2637' => 0,
					'2641' => 0,
					'2643' => 0,
					'2648' => 0,
					'2645' => 0,
					'2647' => 0,
					'2650' => 0,
					'2652' => 0,
					'2653' => 0,
					'2638' => 0,
					'2639' => 0,
					'2640' => 0,
					'2644' => 0,
					'2665' => 0,
					'2651' => 0,
					'2662' => 0,
					'2664' => 0
			);
		}
		else 
		{
			$goods_limit = array(
					'2636' => 0,
					'2637' => 0,
					'2641' => 0,
					'2643' => 0,
					'2648' => 0,
					'2645' => 0,
					'2647' => 0,
					'2650' => 0,
					'2652' => 0,
					'2653' => 0,
					'2638' => 0,
					'2639' => 0,
					'2640' => 0,
					'2644' => 0,
					'2665' => 0,
					'2651' => 0,
					'2662' => 0,
					'2664' => 0
			);
		}
		
		foreach ($goods_limit as $key => $value) 
		{
			//每天有总量限制的
			if ($goods_id == $key)
			{
				if ($value == 0) //不参与
				{
					$can_qg = 0;
				}
				else
				{
					//获取当天已销售数量(订单中和购物车中)
					$sales_volume = 0;
					
					$b_time = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
					$e_time = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
					
					$cart_add_time = '2013-08-'.date('d').' 10:54:00';

					//1.购物车商品数量
					//$c_num = $GLOBALS['db']->GetOne("select SUM(goods_number) from ecs_cart where goods_id=".$goods_id);
					$c_num = $GLOBALS['db']->GetOne("select SUM(goods_number) from ecs_cart where goods_id=".$goods_id." AND add_time > '".$cart_add_time."'");
					$cart_number = ($c_num)? $c_num: 0;
					
					//2.订单中商品的数量
					$u_order = $GLOBALS['db']->GetAll("SELECT order_id FROM ecs_order_info WHERE order_status <> 2 AND add_time > " .$b_time. " AND add_time < " .$e_time);
					$o_goods_num = 0;
					if(!empty($u_order))
					{
						foreach($u_order as $k => $v)
						{
							$sql = "SELECT SUM(goods_number) FROM ecs_order_goods WHERE order_id=".$v['order_id']." AND goods_id=".$goods_id;
							$g_num = $GLOBALS['db']->GetOne($sql);
							if($g_num) $o_goods_num += $g_num;
						}
					}
					
					$sales_volume = $o_goods_num + $cart_number;
				
					//已售数量超过限制,设置为0
					if ($sales_volume >= $value)
					{
						$can_qg = 0;
					}
				}
			}
		}
	}
	
	if ($can_qg == 1)
	{
		if($user_id>0)
		{	
			$xg_goods_num = $GLOBALS['db']->getOne("select sum(goods_number) as cart_num from ecs_cart where user_id=".$user_id." and session_id='".SESS_ID."' and goods_id IN ".$goods_id_str);
		}
		else
		{
			$xg_goods_num = $GLOBALS['db']->getOne("select sum(goods_number) as cart_num from ecs_cart where user_id=0 and session_id='".SESS_ID."' and goods_id IN ".$goods_id_str);
		}
		
		$b_time = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$e_time = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
		
		$u_order = $GLOBALS['db']->GetAll("SELECT order_id FROM ecs_order_info WHERE user_id=".$user_id." AND order_status <> 2 AND add_time > " .$b_time. " AND add_time < " .$e_time);
		if(!empty($u_order))
		{
			foreach($u_order as $k => $v)
			{
				$sql = "SELECT SUM(goods_number) FROM ecs_order_goods WHERE order_id=".$v['order_id']." AND goods_id IN " . $goods_id_str;
				$g_num = $GLOBALS['db']->GetOne($sql);
				if($g_num) $xg_goods_num += $g_num;
			}
		}
		
		$can_qg = ($xg_goods_num>=$xg_num)? 2: 1;
	}
	
	echo $can_qg;
}

//秒杀
elseif($_REQUEST['act'] == 'goods_xg_syms')
{
	$user_id      = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$goods_id     = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	
	$xg_goods_num = 0; //已购数量
	$xg_num       = 1;
	$can_qg		  = 1; //是否能购买:0.抢完 1.可购 2.已购
	
	//$goods_id_str = ' (4370,4376,4383,4378,4379,4377,4380,4381,4382) '; //同时秒杀多个的时候，需要判断购物车是否有其他秒杀商品（本次是一个时间段只秒杀一个商品,不需要）
	//$goods_define = array(891,921);//测试的商品
	$goods_define = array(4510,4515,4516,4513,4511,4509,4326,4514,4517); //891,921是测试
	
	if (in_array($goods_id, $goods_define))
	{
		//商品每天限购数量
		date_default_timezone_set('PRC');
		$now_time = time();
		$goods_limit = array();
		//秒杀商品数组：goods_id,秒杀时间段,总限制数量
		//$goods_limit[] = array('goods_id' => 891, 'begin' => '2014-11-10 13:51:00', 'end' => '2014-11-12 11:02:00', 'num' => 10);//测试的商品
		//$goods_limit[] = array('goods_id' => 921, 'begin' => '2014-11-11 11:00:00', 'end' => '2014-11-11 11:02:00', 'num' => 1);//测试的商品
		//$goods_limit[] = array('goods_id' => 4510, 'begin' => '2015-02-01 11:00:00', 'end' => '2015-02-03 11:01:00', 'num' => 10);
        $goods_limit[] = array('goods_id' => 4510, 'begin' => '2015-02-03 11:00:00', 'end' => '2015-02-03 11:01:00', 'num' => 100);
    	$goods_limit[] = array('goods_id' => 4517, 'begin' => '2015-02-03 16:00:00', 'end' => '2015-02-03 16:01:00', 'num' => 10);
    	$goods_limit[] = array('goods_id' => 4515, 'begin' => '2015-02-03 20:00:00', 'end' => '2015-02-03 20:01:00', 'num' => 10);
        
    	$goods_limit[] = array('goods_id' => 4516, 'begin' => '2015-02-05 11:00:00', 'end' => '2015-02-05 11:01:00', 'num' => 0);
        $goods_limit[] = array('goods_id' => 4513, 'begin' => '2015-02-05 16:00:00', 'end' => '2015-02-05 16:01:00', 'num' => 100);
    	$goods_limit[] = array('goods_id' => 4511, 'begin' => '2015-02-05 20:00:00', 'end' => '2015-02-05 20:01:00', 'num' => 100);
        
    	$goods_limit[] = array('goods_id' => 4509, 'begin' => '2015-02-09 11:00:00', 'end' => '2015-02-09 11:01:00', 'num' => 10);
        $goods_limit[] = array('goods_id' => 4515, 'begin' => '2015-02-09 16:00:00', 'end' => '2015-02-09 16:01:00', 'num' => 10);
    	$goods_limit[] = array('goods_id' => 4326, 'begin' => '2015-02-09 20:00:00', 'end' => '2015-02-09 20:01:00', 'num' => 50);
        
        $goods_limit[] = array('goods_id' => 4514, 'begin' => '2015-02-13 11:00:00', 'end' => '2015-02-13 11:01:00', 'num' => 21);
        $goods_limit[] = array('goods_id' => 4509, 'begin' => '2015-02-13 16:00:00', 'end' => '2015-02-13 16:01:00', 'num' => 5);
    	$goods_limit[] = array('goods_id' => 4517, 'begin' => '2015-02-13 20:00:00', 'end' => '2015-02-13 20:01:00', 'num' => 10);
        
        $goods_limit[] = array('goods_id' => 4516, 'begin' => '2015-02-16 11:00:00', 'end' => '2015-02-16 11:01:00', 'num' => 10);
        $goods_limit[] = array('goods_id' => 4513, 'begin' => '2015-02-16 16:00:00', 'end' => '2015-02-16 16:01:00', 'num' => 5);
    	$goods_limit[] = array('goods_id' => 4511, 'begin' => '2015-02-16 20:00:00', 'end' => '2015-02-16 20:01:00', 'num' => 21);
		//print_r($goods_limit);exit;
		
		foreach ($goods_limit as $gv)
		{
			if ($gv['goods_id'] == $goods_id && $now_time >= strtotime($gv['begin']) && $now_time <= strtotime($gv['end']) && $gv['num'] > 0)
			{
				//满足秒杀基本条件，则去判断购物车和已成交订单中的商品是否已达到限制数量
				//目前,秒杀的时间段设置为2分钟，很短,从简单逻辑考虑，只去判断购物车的数量,如果秒杀时间段很长，则需要去计算成交订单中的商品,参照备份文件:ajax_active.141109.php
				$c_num = $GLOBALS['db']->GetOne("SELECT SUM(goods_number) AS c_num FROM ecs_cart WHERE goods_id=".$goods_id);
				$cart_number = (intval($c_num) > 0) ? $c_num: 0;
				
				if ($cart_number >= $gv['num'])
				{
					//已售数量超过限制,设置为0
					$can_qg = 0;
				}else{
					$can_qg = 1;
				}
				break;
			}else{
				$can_qg = 0;
			}
		}
	}
	echo $can_qg;
}

//xu: 周年庆抽奖
elseif ($_REQUEST['act'] == '20130814') 
{
	$order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']): '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '';
	
	if(!empty($order_sn))
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2013-08-14 00:00:00');
		$active_time_end = strtotime('2013-08-14 23:59:59');
		
		$sql = "select *, SUM(goods_amount-discount-integral_money-bonus) AS prize_amount from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
		$res = $GLOBALS['db']->GetRow($sql);

		if(! $res['order_sn'])
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		elseif(had_prize($order_sn))
		{
			$rt = 'had_prize'; $chou = false;//had_prize
		}
		elseif($res['prize_amount'] < 200)
		{
			$rt = 'no_rank';  $chou = false; //达不到抽奖金额
		}
		else
		{
			//is ok
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1;");
		}
	}
	
	//抽奖名单总数限制
	if ($res['order_sn'] && $res['prize_amount'] >= 200 && $res['prize_amount'] < 300) {
		$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='3周年庆订单抽奖红包' AND prize_rank=1");
		if ($prize_total >= 100) {
			$rt = 'no_enough';  $chou = false;
		}
	} elseif ($res['order_sn'] && $res['prize_amount'] >= 300 && $res['prize_amount'] < 500) {
		$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='3周年庆订单抽奖红包' AND prize_rank=2");
		if ($prize_total >= 10) {
			$rt = 'no_enough';  $chou = false;
		}
	} elseif ($res['order_sn'] && $res['prize_amount'] >= 500) {
		$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='3周年庆订单抽奖红包' AND prize_rank=3");
		if ($prize_total >= 2) {
			$rt = 'no_enough';  $chou = false;
		}
	}

	if($chou)
	{		
		$rank = 0; //prize rank
		if ($res['prize_amount'] >= 200 && $res['prize_amount'] < 300) {
			$rank = 1;
		} elseif ($res['prize_amount'] >= 300 && $res['prize_amount'] < 500) {
			$rank = 2;
		} elseif ($res['prize_amount'] >= 500) {
			$rank = 3;
		}
		
		//insert into db
		if ($rank > 0)
		{
			$sql = "insert into ecs_prize(user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values('$user_id', '$user_name', ".$_SERVER['REQUEST_TIME'].", '$rank', '3周年庆订单抽奖红包', 'order_sn', '$order_sn');";
			if(!empty($rank))
			{
				$ret = mysql_query($sql);
			}
		}

		//show date 
		$rt = 'ok'.','.$user_name.','.$order_sn.','.$rank;
	}
	else
	{
		if(empty($rt))
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
	}
	echo $rt;
}

//yi:购物车的活动商品不能修改数量

elseif($_REQUEST['act'] == 'cart_extension_unchange')
{
	$user_id   = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$goods_id  = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	if($user_id>0)
	{	
		$res = mysql_query("update ecs_cart set extension_code='unchange' where user_id=".$user_id." and session_id='".SESS_ID."' and goods_id=".$goods_id);
	}
	else
	{
		$sql = "update ecs_cart set extension_code='unchange' where user_id=0 and session_id='".SESS_ID."' and goods_id=".$goods_id;
		$res = mysql_query($sql);
	}	
	echo $res? 'ok': 'fail';	
}
//yi：test
elseif($_REQUEST['act'] == 'send_mobile_code')
{
	
	$rt_msg = ''; //发送结果
	$code   = mt_rand(123456, 999999);
	$mobile = (isset($_REQUEST['tel']) && is_numeric($_REQUEST['tel']))? trim($_REQUEST['tel']): '';	
	$user_id= isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$from   = isset($_REQUEST['from'])?  trim($_REQUEST['from']): '';//短信验证类型。

	$msg    = "尊敬的易视网用户,您的手机验证码是：[".$code."],发送时间：".date("H:i:s")."【易视网】";
	
	if(!empty($mobile))
	{
		$resv = $GLOBALS['db']->GetOne("select rec_id from ecs_sms_verify where mobile='$mobile' and extension='$from' and verify=1 limit 1;");
		if(!empty($resv))
		{
			$hv_ck  = $GLOBALS['db']->GetOne("select user_id from ecs_users where user_id=".$user_id." and mobile_ck=1 limit 1;");
			$rt_msg = 'have_tel'; //已经验证过手机了。
			//$rt_msg = ($hv_ck>0)? 'have_tel': 'fail';
		}
		else
		{
			// ----------------- 发送短信 tao:2014-11-14更换------------------------
			include_once('api/sms/sms.php');
			$statusCode = sms_send($mobile,$msg);
						
			// ----------------- 发送短信 END ------------------------
			
			if($statusCode == 0)
			{			
				//send ok, save 
				$sql = "insert into ecs_sms_verify(user_id, mobile, send_time, extension, extension_id) values('$user_id', '$mobile', ".$_SERVER['REQUEST_TIME'].", '$from', '$code');";
				mysql_query($sql);
				$rt_msg	= 'ok';
			}else{
				$rt_msg = 'fail';
			}
			//$rt_msg = $res? 'ok': 'fail';
			//$rt_msg = $statusCode? 'fail': 'ok';
		}
	}

	echo $rt_msg;
	
}

//yi：send mobile check code
elseif($_REQUEST['act'] == 'send_mobile_code2')
{
	/*if(!function_exists(send_sms))
	{
		include_once('sms_fun.php');
	}*/

	$rt_msg = ''; //发送结果
	$code   = mt_rand(123456, 999999);
	$mobile = (isset($_REQUEST['tel']) && is_numeric($_REQUEST['tel']))? trim($_REQUEST['tel']): '';	
	$user_id= isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$from   = isset($_REQUEST['from'])?  trim($_REQUEST['from']): '';//短信验证类型。

	$msg    = "【易视网】尊敬的易视网用户,您的手机验证码是：【".$code."】,发送时间：".date("H:i:s")."";

	if(!empty($mobile))
	{
		$resv = $GLOBALS['db']->GetOne("select rec_id from ecs_sms_verify where mobile='$mobile' and extension='$from' and verify=1 limit 1;");
		if(!empty($resv))
		{
			$hv_ck  = $GLOBALS['db']->GetOne("select user_id from ecs_users where user_id=".$user_id." and mobile_ck=1 limit 1;");
			$rt_msg = 'have_tel'; //已经验证过手机了。
			//$rt_msg = ($hv_ck>0)? 'have_tel': 'fail';
		}
		else
		{
			// ----------------- 发送短信 ------------------------
			//$res = send_sms($mobile, $msg); //xu:2013-08-13更换
			define('SCRIPT_ROOT', 'api/mobile/');
			include_once('api/mobile/include/Client.php');
						
			//网关
			$gwUrl = 'http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl';

			//序列号（从销售人员获取）
			$serialNumber = '3SDK-EMY-0130-PFXUT';

			//密码（从销售人员获取）
			$password = '132447';

			//登录后所持有的SESSION KEY【B/S版则和密码一致】
			$sessionKey = $password;

			//连接超时时间，单位为秒
			$connectTimeOut = 20;

			//远程信息读取超时时间，单位为秒
			$readTimeOut    = 60;

			//代理服务器
			$proxyhost     = false;
			$proxyport     = false;
			$proxyusername = false;
			$proxypassword = false; 
			$client = new Client($gwUrl,$serialNumber,$password,$sessionKey,$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
			$client->setOutgoingEncoding("utf-8");
			
			$statusCode = $client->sendSMS(array($mobile),$msg);
			// ----------------- 发送短信 END ------------------------
			
			//if($res)
			if($statusCode == 0)
			{			
				//send ok, save 
				$sql = "insert into ecs_sms_verify(user_id, mobile, send_time, extension, extension_id) values('$user_id', '$mobile', ".$_SERVER['REQUEST_TIME'].", '$from', '$code');";
				mysql_query($sql);
			}
			//$rt_msg = $res? 'ok': 'fail';
			$rt_msg = $statusCode? 'fail': 'ok';
		}
	}

	echo $rt_msg;
}
//yi:check mobile code, send prize;
elseif($_REQUEST['act'] == 'send_fav_goods')
{	
	$code     = isset($_REQUEST['code'])? trim($_REQUEST['code']): '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$goods_id = 1685;//赠品
	$gift_id  = 888; //赠品活动ID
	$send_fav = true;//发赠品

	//check code
	$sql = "select rec_id, extension_id, verify from ecs_sms_verify where user_id='$user_id' order by send_time desc limit 1;";
	$res = $GLOBALS['db']->GetRow($sql);
	$code_true = trim($res['extension_id']);
	if(!empty($res))
	{
		if($code != $code_true)
		{
			$send_fav = false;
		}
		else
		{
			mysql_query("update ecs_sms_verify set verify=1 where rec_id=".$res['rec_id']);//这个手机号码已经验证。
		}
	}
	else
	{
		$send_fav = false;
	}


	if($send_fav)
	{
		if(!function_exists(insert_cart))
		{
			include_once(ROOT_PATH . 'includes/lib_order.php');
		}
		insert_cart($goods_id, 1, $gift_id, 0, 'unchange');
		echo 'ok';
	}
	else
	{
		echo 'fail';
	}
}
/* ----------------------------------------------------------------------------------------------------------------------
 * yi:会员手机验证，验证通过后发400消费积分。
 * ----------------------------------------------------------------------------------------------------------------------
 */
elseif($_REQUEST['act'] == 'mobile_ck')
{	
	$code     = (isset($_REQUEST['code']) && !empty($_REQUEST['code']))? trim($_REQUEST['code']): '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$ck_res   = false;//标记检查结果

	$is_ck    = $GLOBALS['db']->GetOne("select mobile_ck from ecs_users where user_id='$user_id' limit 1;");
	if(0 == $is_ck && !empty($code) && $user_id>0)
	{
		$sql = "select * from ecs_sms_verify where user_id='$user_id' and extension='mobile_ck' and verify=0 order by rec_id desc limit 1;";
		$sv  = $GLOBALS['db']->GetRow($sql);
		$code_true = trim($sv['extension_id']);

		if(!empty($sv) && $code == $code_true)
		{
			//验证通过
			$ck_res = true;
			mysql_query("update ecs_users set mobile_ck=1 where user_id='$user_id' limit 1;");
			mysql_query("update ecs_sms_verify set verify=1 where rec_id=".$sv['rec_id']." limit 1;");
			
			//20140507活动：注册送红包
			$time = time();
			if ($time >= strtotime('2014-05-07 00:00:00') && $time <= strtotime('2014-05-18 23:59:59'))
			{
				$swsql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$user_id' and bonus_type_id='1367' and order_id=0 and used_time=0";
				$swquan = $GLOBALS['db']->getAll($swsql3);
				if(empty($swquan))
				{
					//领取红包
					$yssql1 = "insert into ".$GLOBALS['ecs']->table('user_bonus'). "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values(1367, 0, '$user_id', 0, 0, 0);";
					$bonus1 = mysql_query($yssql1);
				}
			}
		}
	}

	if($ck_res)
	{
		if(!function_exists(log_account_change))
		{
			include_once(ROOT_PATH.'includes/lib_common.php');
		}
		$jf_num = 400;
		$desc   = "【".date('Y年m月d日 H时i分')."】会员验证手机号成功，奖励".$jf_num."积分。";
		log_account_change($user_id, 0, 0, 0, $jf_num, $desc);
		echo 'ok';
	}
	else
	{
		echo 'fail';
	}
}
//0元领取
elseif($_REQUEST['act'] == '20140402'){
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '111111';
	
	if ($user_id > 0)
	{

		if($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		else
		{
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
		}
		//活动开始和结束时间
		$active_time_begin = strtotime('2014-03-28 00:00:00');
		$active_time_end = strtotime('2014-04-13 23:59:59');
		$datetime = time();
		
		if ($datetime >= $active_time_begin && $datetime <= $active_time_end)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where user_id = '$user_id' and refer='0元领取' ");
			if ( ! $had_prize) 
			{
				$title = '0元领取';
				$msg = '';
					$msg = '恭喜您获得【海昌海俪恩除蛋白隐形眼镜护理液120ml】。';
					//洗眼液 1182
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '3298', '', '【0元领取】海昌海俪恩除蛋白隐形眼镜护理液120ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
				
					
				/*//发送站内信
						$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
						$res_prize = mysql_query($sql_prize);
						if($res_prize){ unread_user_msg($user_id); }*/
				
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '0元领取', 'order_sn', '')";
					$ret = mysql_query($sql);
				$rt = 'ok'.','.$rank;
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}
	echo $rt;
}
//问道抽奖
elseif($_REQUEST['act'] == '20140224'){
	//以下是订单抽奖的最新奖项设置
	/*1 5元 30  1130
	2 30元  25  1131 
	3 45元  20  1132
	4 谢谢参与  20
	5 伴侣盒  5   1133

	1 问道威威虎抱枕 （4）		 5%   
	2 体恤水墨太极熊系列  （10） 15%
	3 道定制钱包  （6）			 5%
	游戏道具
	4 优能洗眼液（30）			35%
	5 优能高水分润眼液（30）		35%
	框架镜
	强生美瞳
	*/
	$order_sn = isset($_REQUEST['order_sn']) && is_numeric($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '111111';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2014-02-24 00:00:00');
		$active_time_end = strtotime('2014-03-10 23:59:59');
		
		//判断订单是否符合条件
		$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
		$res = $GLOBALS['db']->GetRow($sql);

		if(! $res['order_sn'])
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		else
		{
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
		}
		
		if ($chou)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='问道专享抽奖' and extension_id='".$order_sn."'");
			if ( ! $had_prize) 
			{
				$rank = get_prize_20140224(); //prize rank
				$title = '问道专享抽奖';
				$msg = '';
				if ($rank == 4)
				{
					$msg = '恭喜您获得【优能洗眼液】。';
					//洗眼液 1182
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '1182', '', '[问道活动]Visine优能眼部清洗液30ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
				}else{
					if ($rank == 2)
					{
						$msg = '恭喜您获得【体恤水墨太极熊系列】。';
					}
					elseif ($rank == 3)
					{
						$msg = '恭喜您获得【道定制钱包】。';
					}
					elseif ($rank == 1)
					{
						$msg = '恭喜您获得【问道威威虎抱枕】。';
					}elseif($rank == 5){
						$msg = '恭喜您获得【优能高水分润眼液】。';
						//洗眼液 1182
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '1069', '', '[问道活动]Visine优能高水分滴眼液15ml', '29.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
					}
					
					//发送站内信
						$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
						$res_prize = mysql_query($sql_prize);
						if($res_prize){ unread_user_msg($user_id); }
				}
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '问道专享抽奖', 'order_sn', '$order_sn')";
					$ret = mysql_query($sql);
				$rt = 'ok'.','.$rank;
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}
	echo $rt;
}
//女人节抽奖
elseif($_REQUEST['act'] == '2014031101'){
	//以下是订单抽奖的最新奖项设置
	/*1 凯达伴侣盒（双联盒+镊子）  25  1130
	  2 优能洗眼液  25  1131 
	  3 易视网积分  50  1132
	*/
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id=$user_id limit 1");

	$chou = true;
	$rt   = '111111';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2014-03-10 00:00:00');
		$active_time_end = strtotime('2014-03-24 23:59:59');
		$datetime = time();
		
		if ($datetime >= $active_time_begin && $datetime <= $active_time_end)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='女人节抽奖奖品' and user_id='".$user_id."'");
			if ( ! $had_prize) 
			{
				$rank = get_prize_2014031101(); //prize rank
				$title = '女人节抽奖奖品';
				$msg = '';
				if ($rank == 3)
				{
					if(!function_exists(log_account_change))
					{
						include_once(ROOT_PATH.'includes/lib_common.php');
					}
					$jf_num = 100;
					$desc   = "【".date('Y年m月d日 H时i分')."】会员验证手机号成功，奖励".$jf_num."积分。";
					log_account_change($user_id, 0, 0, 0, $jf_num, $desc);

					$msg = '恭喜你获得100积分！';
				}else{
					if ($rank == 1)
					{
						//洗眼液 1182
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '1182', '', '[女人节抽奖]Visine优能眼部清洗液30ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
						$msg = '恭喜您获得【Visine优能眼部清洗液30ml】 奖品已自动加入您的购物车。';
					}
					elseif ($rank == 2)
					{
						//洗眼液 1182
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '3064', '', '[女人节抽奖]凯达伴侣盒', '15.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
						$msg = '恭喜您获得【凯达伴侣盒】奖品已自动加入您的购物车。';
					}
					
					//发送站内信
						$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
						$res_prize = mysql_query($sql_prize);
						if($res_prize){ unread_user_msg($user_id); }
				}
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '女人节抽奖奖品', 'user_id', '$user_id')";
					$ret = mysql_query($sql);
				$rt = 'ok'.','.$rank;
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}	
	echo $rt;
}
//财付通抽奖
elseif($_REQUEST['act'] == '20140107'){
	//以下是订单抽奖的最新奖项设置
	/*1 5元 30  1130
	2 30元  25  1131 
	3 45元  20  1132
	4 谢谢参与  20
	5 伴侣盒  5   1133*/
	$order_sn = isset($_REQUEST['order_sn']) && is_numeric($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '111111';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2014-02-18 00:00:00');
		$active_time_end = strtotime('2014-03-17 23:59:59');
		
		//判断订单是否符合条件
		$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
		$res = $GLOBALS['db']->GetRow($sql);

		if(! $res['order_sn'])
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		else
		{
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
		}
		
		if ($chou)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='财付通20140107专享抽奖' and extension_id='".$order_sn."'");
			if ( ! $had_prize) 
			{
				$rank = get_prize_20140107(); //prize rank
				$title = '财付通20140107专享抽奖';
				$msg = '';
				if ($rank == 4)
				{
					$msg = '谢谢参与。';
				}else{
					if ($rank == 2)
					{
						$msg = '恭喜您获得【150减30元】彩片券。';
					}
					elseif ($rank == 3)
					{
						$msg = '恭喜您获得【45元框架太阳镜】抵扣券。';
					}
					elseif ($rank == 1)
					{
						$msg = '恭喜您获得【5元】抵扣券。';
					}elseif($rank == 5){
						$msg = '恭喜您获得凯达趣伴侣盒。';
					}
					
					//发送站内信
						$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
						$res_prize = mysql_query($sql_prize);
						if($res_prize){ unread_user_msg($user_id); }
				}
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '财付通20140107专享抽奖', 'order_sn', '$order_sn')";
					$ret = mysql_query($sql);
				$rt = 'ok'.','.$rank;
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}
	echo $rt;
}
elseif($_REQUEST['act'] == "20140311rob"){
	//判断用户活动日期内注册的用户
	$reg_userid = isset($_REQUEST['user_id'])? $_REQUEST['user_id']: '';
	$has_act = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_prize WHERE refer='女人节抽奖奖品' and  user_id=$reg_userid"); //此用户是否已参与
	$choustatus = 1;
		if ($reg_userid > 0)
		{
			if (empty($has_act))
			{
				$starttime = strtotime("2014-03-10 00:00:00");
				$endtime = strtotime("2014-03-24 23:59:59");
				$prize_list = $GLOBALS['db']->GetAll("SELECT * FROM ecs_users WHERE user_id = $reg_userid and reg_time > $starttime and reg_time < $endtime ");
				if(empty($prize_list)){
					$choustatus = 1 ; //不在活动范围内注册
				}else{
					$choustatus = 2 ;//在活动范围内注册
				}
			}
		}
	echo $choustatus;

}
//财付通抽奖
elseif($_REQUEST['act'] == '20131226'){
	//以下是订单抽奖的最新奖项设置
	/*1 5元 30  1130
	2 30元  25  1131 
	3 45元  20  1132
	4 谢谢参与  20
	5 伴侣盒  5   1133*/
	$order_sn = isset($_REQUEST['order_sn']) && is_numeric($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '111111';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2014-02-18 00:00:00');
		$active_time_end = strtotime('2014-03-17 23:59:59');
		
		//判断订单是否符合条件
		$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
		$res = $GLOBALS['db']->GetRow($sql);

		if(! $res['order_sn'])
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		else
		{
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
		}
		
		if ($chou)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='招商20131226专享抽奖' and extension_id='".$order_sn."'");
			if ( ! $had_prize) 
			{
				$rank = get_prize_20140107(); //prize rank
				$title = '招商20131226专享抽奖';
				$msg = '';
				if ($rank == 4)
				{
					$msg = '谢谢参与。';
				}else{
					if ($rank == 2)
					{
						$msg = '恭喜您获得【150减30元】彩片券。';
					}
					elseif ($rank == 3)
					{
						$msg = '恭喜您获得【45元框架太阳镜】抵扣券。';
					}
					elseif ($rank == 1)
					{
						$msg = '恭喜您获得【5元】抵扣券。';
					}elseif($rank == 5){
						$msg = '恭喜您获得凯达趣伴侣盒。';
					}
					
					//发送站内信
						$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
						$res_prize = mysql_query($sql_prize);
						if($res_prize){ unread_user_msg($user_id); }
				}
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '招商20131226专享抽奖', 'order_sn', '$order_sn')";
					$ret = mysql_query($sql);
				$rt = 'ok'.','.$rank;
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}
	echo $rt;
}
//堆糖合作用户注册抽奖
elseif ($_REQUEST['act'] == '20131028')
{
	//以下是订单抽奖的最新奖项设置
	/*$order_sn = isset($_REQUEST['order_sn']) && is_numeric($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2013-11-11 00:00:00');
		$active_time_end = strtotime('2013-11-15 23:59:59');
		
		//判断订单是否符合条件
		$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
		$res = $GLOBALS['db']->GetRow($sql);

		if(! $res['order_sn'])
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		else
		{
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
		}
		
		if ($chou)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='堆糖网专享注册抽奖' and extension_id='".$order_sn."'");
			if ( ! $had_prize) 
			{
				$rank = get_prize_20131028(); //prize rank
				$title = '堆糖网专享注册抽奖';
				$msg = '';
				
				if ($rank == 2)
				{
					//验证奖品是否已抽完
					$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND prize_rank=2 AND user_id != 64660 AND add_time > 1384312501");
					if ($prize_total >= 5)
					{
						$rank = 1; //奖品抽完，改成优惠券
					}
					else
					{
						//洗眼液 1182
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '1182', '', '[双11抽奖]Visine优能眼部清洗液30ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
						$msg = '恭喜您获得【Visine优能眼部清洗液30ml】 奖品已自动加入您的购物车。';
					}
				}
				elseif ($rank == 3)
				{
					$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND prize_rank=3 AND user_id != 64660 AND add_time > 1384312501");
					if ($prize_total >= 5)
					{
						$rank = 1; //奖品抽完，改成优惠券
					}
					else
					{
						//卫康 2568
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '2568', '', '[双11抽奖]卫康视季清凉型护理液125ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
						$msg = '恭喜您获得【卫康视季清凉型护理液125ml】 奖品已自动加入您的购物车。';
					}
				}
				elseif ($rank == 4)
				{
					$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND prize_rank=4 AND user_id != 64660 AND add_time > 1384312501");
					if ($prize_total >= 5)
					{
						$rank = 1; //奖品抽完，改成优惠券
					}
					else
					{
						//伴侣盒 2775
						$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '2775', '', '[双11抽奖]酷视迪士尼史迪奇隐形眼镜伴侣盒', '12.00', '0.00', '1', '1', 'unchange', '1')";
						$res_cart = $GLOBALS['db']->query($sql_cart);
						$msg = '恭喜您获得【酷视迪士尼史迪奇隐形眼镜伴侣盒】 奖品已自动加入您的购物车。';
					}
					
				}
				
				if ($rank == 1)
				{
					$msg = '恭喜您获得【满50减5优惠券】';
				}
				
				//插入中奖记录
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '堆糖网专享注册抽奖', 'order_sn', '$order_sn')";
				$ret = mysql_query($sql);
				
				//发送站内信
				$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
				$res_prize = mysql_query($sql_prize);
				if($res_prize){ unread_user_msg($user_id); }
				
				$rt = 'ok'.','.$rank;
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}*/
	
	//订单抽奖的老的奖项
	/*if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2013-11-11 00:00:00');
		$active_time_end = strtotime('2013-11-15 23:59:59');
		
		//判断订单是否符合条件
		$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
		$res = $GLOBALS['db']->GetRow($sql);

		if(! $res['order_sn'])
		{
			$rt = 'no_sn';     $chou = false;//no this order
		}
		elseif($res['pay_status']!=2)
		{
			$rt = 'no_paid';   $chou = false;//not paid
		}
		elseif(0 == $user_id)
		{
			$rt = 'no_login';  $chou = false;//no login
		}
		else
		{
			$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
		}
		
		if ($chou)
		{
			//判断是否已参加抽奖
			$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='堆糖网专享注册抽奖' and extension_id='".$order_sn."'");
			if ( ! $had_prize) 
			{
				$rank = get_prize_20131028(); //prize rank
				$title = '堆糖网专享注册抽奖';
				$msg = '';
				
				if ($rank == 1)
				{
					//验证奖品是否已抽完
					$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND prize_rank=1");
					if ($prize_total >= 4)
					{
						$rt = 'no_enough';
					}
					else
					{
						$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '堆糖网专享注册抽奖', 'user_id', '$order_sn')";
						$ret = mysql_query($sql);
						$msg = '恭喜您获得【品牌美瞳一副】，请联系网站客服,奖品将在活动结束后统一发放！';
					}
				}
				else
				{
					$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '堆糖网专享注册抽奖', 'user_id', '$order_sn')";
					$ret = mysql_query($sql);
					
					//发送站内信
					if ($rank == 2) {
						$msg = '恭喜您获得【满199减12优惠券】';
					} elseif ($rank == 3) {
						$msg = '恭喜您获得【满50减5优惠券】';
					}
			
					//show date 
					$rt = 'ok'.','.$rank;
				}
				
				//发送站内信
				$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
				$res_prize = mysql_query($sql_prize);
				if($res_prize){ unread_user_msg($user_id); }
				
			}
			else
			{
				$rt = 'had_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_login';
	}*/
	
	//以下是注册抽奖 11.4后继续启用
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");

	$chou = true;
	$rt   = '';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2013-11-25 00:00:00');
		$active_time_end = strtotime('2013-12-02 23:59:59');
		
		//判断是否是否是来自堆糖
		$sql = "select * from ecs_users where user_id=$user_id and referer='duitang' and email_ck=1 and reg_time >= $active_time_begin and reg_time <= $active_time_end ";
		$res = $GLOBALS['db']->GetRow($sql);
		
		if ($res)
		{
			//判断是否已参加抽奖
			$has_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='堆糖网专享注册抽奖' and user_id = $user_id");
			if ( ! $has_prize) 
			{
				$rank = get_prize_20131028(); //prize rank
				$rt = 'ok'.','.$rank;
				
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '堆糖网专享注册抽奖', 'user_id', '$user_id')";
				$ret = mysql_query($sql);
			}
			else
			{
				$rt = 'has_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_chou';//不够资格抽奖，没有抽奖
	}
	
	echo $rt;
}

//双12领克特注册抽奖
elseif ($_REQUEST['act'] == '20131023')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");

	$chou = true;
	$rt   = '';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2013-12-02 00:00:00');
		$active_time_end = strtotime('2013-12-15 23:59:59');
		
		//判断用户是否是来自领克特
		$sql = "select * from ecs_users where user_id=$user_id and referer='linktech' and email_ck=1 and reg_time >= $active_time_begin and reg_time <= $active_time_end ";
		$res = $GLOBALS['db']->GetRow($sql);
		
		if ($res)
		{
			//判断是否已参加抽奖
			$has_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='领克特专享注册抽奖' and user_id = $user_id");
			if ( ! $has_prize) 
			{
				$rank = get_prize_20131028(); //prize rank
				$rt = 'ok'.','.$rank;
				
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '领克特专享注册抽奖', 'user_id', '$user_id')";
				$ret = mysql_query($sql);
			}
			else
			{
				$rt = 'has_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_chou';//不够资格抽奖，没有抽奖
	}
	
	echo $rt;
}

//双12多麦注册抽奖
elseif ($_REQUEST['act'] == '20131202')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");

	$chou = true;
	$rt   = '';
	
	if ($user_id > 0)
	{
		//活动开始和结束时间
		$active_time_begin = strtotime('2013-12-01 00:00:00');
		$active_time_end = strtotime('2013-12-15 23:59:59');
		
		//判断用户是否是来自多麦
		$sql = "select * from ecs_users where user_id=$user_id and referer='duomai' and email_ck=1 and reg_time >= $active_time_begin and reg_time <= $active_time_end ";
		$res = $GLOBALS['db']->GetRow($sql);
		
		if ($res)
		{
			//判断是否已参加抽奖
			$has_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='多麦专享注册抽奖' and user_id = $user_id");
			if ( ! $has_prize) 
			{
				$rank = get_prize_20131028(); //prize rank
				$rt = 'ok'.','.$rank;
				
				$sql = "insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '多麦专享注册抽奖', 'user_id', '$user_id')";
				$ret = mysql_query($sql);
			}
			else
			{
				$rt = 'has_prize'; //已参加抽奖
			}
			
		}
		else
		{
			$rt = 'no_chou';//不够资格抽奖，没有抽奖
		}
		
	}
	else
	{
		$rt = 'no_chou';//不够资格抽奖，没有抽奖
	}
	
	echo $rt;
}
//易视问道联合活动 
elseif ($_REQUEST['act'] == '20131211_rob')
{
	
	date_default_timezone_set('PRC'); 
	$yi_user_id    = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;

	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='".$yi_user_id."' limit 1");
	
			if(!empty($yi_user_id)){

				//判断用户是否领取过
				$robsql1 = "select * from ecs_wdcoupon where coupon_status = 1 and user_id = '".$yi_user_id."'";
				$robres1 = $GLOBALS['db']->GetRow($robsql1);

				if(!empty($robres1)){
					$info['rob_code'] = 0;//已领取
					$info['rob_msg']  = '您好，该礼包您已经领用过了！';
				}else{
					$curtime = strtotime(date('Y-m-d H:i:s'));
					$robsql2 = "select * from ecs_wdcoupon where coupon_status = 0  limit 1";
					$robres2 = $GLOBALS['db']->GetRow($robsql2);
					$title = "易视网问道联合活动礼包";
					if(!empty($robres2)){

						//领取问道礼包
						mysql_query("UPDATE ecs_wdcoupon SET coupon_status=1, user_id='".$yi_user_id."' WHERE  coupon_id = '".$robres2['coupon_id']."'"); //标记已使用
						$msg = "恭喜您获得【问道游戏礼包】,序号：".$robres2['coupon_no'].", 卡号：".$robres2['coupon_sn']."<br>";

						//易视网实物礼包
						//10元格瓦拉电影票折扣券
						$robgwlsql = "select * from lele_gwl where order_sn='".$yi_user_id."' and ticket_type=3 AND status=1 LIMIT 1";
						$robgwl = $GLOBALS['db']->GetRow($robgwlsql);
						if(empty($robgwl)){
							$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=3 AND status=0 LIMIT 1");
							if ($ticket)
							{
								mysql_query("UPDATE lele_gwl SET status=1,order_sn='".$yi_user_id."' WHERE ticket_type=3 AND ticket_NO = '".$ticket['ticket_NO']."'"); //标记已使用
								$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;恭喜您获得【格瓦拉电影10元抵扣券1张】,券号：'.$ticket['ticket_NO'].', 密码：'.$ticket['ticket_password']."</br>";
							}
						}
						//科莱博隐形眼镜月抛型 和 博士伦60ml多功能护理液
				
						$swsql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$yi_user_id' and bonus_type_id='1031' and order_id=0 and used_time=0;";
						$swquan = $GLOBALS['db']->getAll($swsql3);
						if(empty($swquan))
						{
							//领取红包
							$yssql1 = "insert into ".$GLOBALS['ecs']->table('user_bonus'). "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values(1031, 0, '$yi_user_id', 0, 0, 0);";
							$bonus1 = mysql_query($yssql1);
							if($bonus1 !== false)
							{
								$msg .=  '&nbsp;&nbsp;&nbsp;&nbsp;恭喜您获得【实物礼包】,请到会员中心我的红包中查看。（科莱博隐形眼镜的度数:请在订单附言中注明）'."</br>"."&nbsp;&nbsp;&nbsp;&nbsp;易视网【实物礼包】购物满150元，即免费随订单寄送且免运费，无购物或者不满150元，不得兑换该礼包。"."</br>";
							}
						}
						//40元框架，太阳镜红包券一个会员一种类型的券只能够领取1张优惠券。
				
						$sql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$yi_user_id' and bonus_type_id='1030' and order_id=0 and used_time=0;";
						$quan = $GLOBALS['db']->getAll($sql3);
						if(empty($quan))
						{
							//领取红包
							$yssql2 = "insert into ".$GLOBALS['ecs']->table('user_bonus'). "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values(1030, 0, '$yi_user_id', 0, 0, 0);";
							$bonus2 = mysql_query($yssql2);
							if($bonus2 !== false)
							{
								$msg .=  '&nbsp;&nbsp;&nbsp;&nbsp;恭喜您获得40元框架、太阳镜红包券。请到会员中心我的红包中查看。'."</br>";
							}
						}


						//插入站内信记录
						$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$yi_user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
						$res_prize = mysql_query($sql_prize);
						if($res_prize){ unread_user_msg($yi_user_id); }
						
						$info['rob_code'] = 1;//领取成功
						$info['rob_msg']  = '恭喜您，礼包领取成功！请到站内信中查看。';

					}else{
						$info['rob_code'] = 2;//礼包已经抢完，明天再来
						$info['rob_msg']  = '礼包已抢完，明天再来吧！';
					}

				}

			}else{
				$info['rob_code'] = 3;//很抱歉，礼包领取失败！
				$info['rob_msg']  = '很抱歉，礼包领取失败！';	
			}

	$str = json_encode($info);
	echo $str;

}
/*发彩票*/
elseif ($_REQUEST['act'] == '20140110_send'){
	
	date_default_timezone_set('PRC'); 
	$yi_user_id    = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='".$yi_user_id."' limit 1");
	if(!empty($yi_user_id)){

				//判断用户是否领取过
				$robsql1 = "select * from ecs_cpcoupon where cp_status = 1 and user_id = '".$yi_user_id."'";
				$robres1 = $GLOBALS['db']->GetRow($robsql1);

				if(!empty($robres1)){
					$info['rob_code'] = 0;//已领取
					$info['rob_msg']  = '您好，您已经分享过了！';
				}else{
					$robsql2 = "select * from ecs_cpcoupon where cp_status = 0  limit 1";
					$robres2 = $GLOBALS['db']->GetRow($robsql2);
					$title = "恭喜您获得【平安万里通彩票代金券】";

					if(!empty($robres2)){

								//领取彩票
								mysql_query("UPDATE ecs_cpcoupon SET cp_status=1, user_id='".$yi_user_id."' WHERE  cp_id = '".$robres2['cp_id']."'"); //标记已使用
								$msg = "恭喜您获得【平安万里通彩票代金券】</br>
										券码：".$robres2['cp_sn']."</br>
										可用于平安万里通彩票网，购买任意彩票一注。</br>
										兑换地址：</br>
										http://caipiao.wanlitong.com/index.php?track=quannonghui&act=management&st=voucher_one</br>
										温馨提示：</br>
										1.一个手机号仅限使用一张券码；</br>
										2.拿到代金券，请在2月10日前通过“兑换地址”进行绑定，逾期作废；</br>
										3. 彩票代金券使用详情及相关问题，请咨询万里通彩票网（caipiao.wanlitong.com）客服热线：400-636-6612</br>";

								

								//插入站内信记录
								$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$yi_user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
								$res_prize = mysql_query($sql_prize);
								if($res_prize){ unread_user_msg($yi_user_id); }
								
								$info['rob_code'] = 1;//领取成功
								$info['rob_msg']  = '恭喜您已成功分享，彩票代金券请在站内短信中查收"我的易视"→"系统通知"！';

					}else{
						$info['rob_code'] = 2;//已经抢完，明天再来
						$info['rob_msg']  = '很抱歉，彩票已经领完！';
					}
			  }
	}else{
		$info['rob_code'] = 3;//很抱歉，礼包领取失败！
		$info['rob_msg']  = '很抱歉，领取失败！';	
	}
	$str = json_encode($info);
	echo $str;
}
/*发放红包结束*/
//双11联合抽奖
elseif ($_REQUEST['act'] == '20131111_chou')
{
	$order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']): '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '';
	
	
	//活动开始和结束时间
	$active_time_begin = strtotime('2013-11-11 00:00:00');
	$active_time_end = strtotime('2013-11-17 23:59:59');
	
	//判断订单是否符合条件
	$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
	$res = $GLOBALS['db']->GetRow($sql);

	if(! $res['order_sn'])
	{
		$rt = 'no_sn';     $chou = false;//no this order
	}
	elseif($res['pay_status']!=2)
	{
		$rt = 'no_paid';   $chou = false;//not paid
	}
	/*elseif(0 == $user_id)
	{
		$rt = 'no_login';  $chou = false;//no login
	}*/
	else
	{
		$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");//is ok
	}
	
	if ($chou)
	{
		//判断是否已参加抽奖
		$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='2013双11联合抽奖' and extension_id='".$order_sn."'");
		if ( ! $had_prize) 
		{
			$rank = get_prize_20131111(); //prize rank
			
			//验证奖品是否已抽完
			/*$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='堆糖网专享注册抽奖' AND prize_rank=1");
			if ($prize_total >= 4)
			{
				$rt = 'no_enough';
			}*/
			//插入抽奖记录
			mysql_query("insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$res['user_name']."', ".$_SERVER['REQUEST_TIME'].", '$rank', '2013双11联合抽奖', 'order_sn', '$order_sn')");
			
			$title = '易视眼镜网双11联合抽奖';
			$msg = '中奖啦';
			
			if ($rank == 1)
			{
				//电影票
				$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=1 AND status=0 LIMIT 1");
				if ($ticket)
				{
					mysql_query("UPDATE lele_gwl SET status=1, order_sn='".$order_sn."' WHERE ticket_type=1 AND ticket_NO = '".$ticket['ticket_NO']."'"); //标记已使用
					$msg = '恭喜您获得【格瓦拉电影票1张】,券号：'.$ticket['ticket_NO'].', 密码：'.$ticket['ticket_password'];
				}
			}
			elseif ($rank == 2)
			{
				//5元电影抵扣券
				$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=2 AND status=0 LIMIT 1");
				if ($ticket)
				{
					mysql_query("UPDATE lele_gwl SET status=1, order_sn='".$order_sn."' WHERE ticket_type=2 AND ticket_NO = '".$ticket['ticket_NO']."'"); //标记已使用
					$msg = '恭喜您获得【格瓦拉电影5元抵扣券1张】,券号：'.$ticket['ticket_NO'].', 密码：'.$ticket['ticket_password'];
				}
			}
			elseif ($rank == 3)
			{
				//10电影抵扣券
				$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=3 AND status=0 LIMIT 1");
				if ($ticket)
				{
					mysql_query("UPDATE lele_gwl SET status=1, order_sn='".$order_sn."' WHERE ticket_type=3 AND ticket_NO = '".$ticket['ticket_NO']."'"); //标记已使用
					$msg = '恭喜您获得【格瓦拉电影10元抵扣券1张】,券号：'.$ticket['ticket_NO'].', 密码：'.$ticket['ticket_password'];
				}
			}
			elseif ($rank == 4 && $user_id > 0)
			{
				//本站499-50优惠券
			}
			elseif ($rank == 4 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			elseif ($rank == 5)
			{
				//洗眼液 1182
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '1182', '', '[双11抽奖]Visine优能眼部清洗液30ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
				$msg = '恭喜您获得【Visine优能眼部清洗液30ml】 奖品已自动加入您的购物车。';
			}
			elseif ($rank == 6)
			{
				//卫康 2568
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '2568', '', '[双11抽奖]卫康视季清凉型护理液125ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
				$msg = '恭喜您获得【卫康视季清凉型护理液125ml】 奖品已自动加入您的购物车。';
			}
			elseif ($rank == 7)
			{
				//伴侣盒 2775
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '2775', '', '[双11抽奖]酷视迪士尼史迪奇隐形眼镜伴侣盒', '12.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
				$msg = '恭喜您获得【酷视迪士尼史迪奇隐形眼镜伴侣盒】 奖品已自动加入您的购物车。';
			}
			
			//插入站内信记录
			//$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
			//$res_prize = mysql_query($sql_prize);
			//if($res_prize){ unread_user_msg($user_id); }
			
			send_mail($res['consignee'], $res['email'], $title, $msg, 0);
			
			$rt = 'ok'.','.$rank;
			
		}
		else
		{
			$rt = 'had_prize'; //已参加抽奖
		}
		
	}
		
	echo $rt;
}

//20131119感恩节抽奖
elseif ($_REQUEST['act'] == '20131119')
{
	$order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']): '';
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = '';

	$chou = true;
	$rt   = '';
	
	
	//活动开始和结束时间
	$active_time_begin = strtotime('2013-11-20 00:00:00');
	$active_time_end = strtotime('2013-12-08 23:59:59');
	
	//判断订单是否符合条件
	$sql = "select * from ecs_order_info where order_sn='$order_sn' and add_time >= $active_time_begin and add_time <= $active_time_end limit 1";
	$res = $GLOBALS['db']->GetRow($sql);

	if(! $res['order_sn'])
	{
		$rt = 'no_sn';     $chou = false;//no this order
	}
	elseif($res['pay_status']!=2)
	{
		$rt = 'no_paid';   $chou = false;//not paid
	}
	elseif(0 == $user_id)
	{
		$rt = 'no_login';  $chou = false;//no login
	}
	else
	{
		$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='".$res['user_id']."' limit 1");//is ok
	}
	
	if ($chou)
	{
		//判断是否已参加抽奖
		$had_prize = $GLOBALS['db']->GetOne("select COUNT(*) from ecs_prize where refer='2013感恩节抽奖' and extension_id='".$order_sn."'");
		if ( ! $had_prize) 
		{
			$rank = get_prize_20131119(); //prize rank
			
			$title = '易视眼镜网2013感恩节抽奖';
			$msg = '中奖啦';
			
			if ($rank == 1)
			{
				//验证奖品是否已抽完
				$prize_total = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS prize_num FROM ecs_prize WHERE refer='2013感恩节抽奖' AND prize_rank=1");
				if ($prize_total >= 3)
				{
					$rank = 2;
				}
				else
				{
					//电影票
					$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=1 AND status=0 LIMIT 1");
					if ($ticket)
					{
						mysql_query("UPDATE lele_gwl SET status=1, order_sn='".$order_sn."' WHERE ticket_type=1 AND ticket_NO = '".$ticket['ticket_NO']."'"); //标记已使用
						$msg = '恭喜您获得【格瓦拉电影票1张】,券号：'.$ticket['ticket_NO'].', 密码：'.$ticket['ticket_password'];
					}
				}
			}
			elseif ($rank == 2 && $user_id > 0)
			{
				//本站499-50优惠券
				$msg = '恭喜您获得【易视网499-50元红包】';
			}
			elseif ($rank == 2 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			elseif ($rank == 3)
			{
				//10电影抵扣券
				$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=3 AND status=0 LIMIT 1");
				if ($ticket)
				{
					mysql_query("UPDATE lele_gwl SET status=1, order_sn='".$order_sn."' WHERE ticket_type=3 AND ticket_NO = '".$ticket['ticket_NO']."'"); //标记已使用
					$msg = '恭喜您获得【格瓦拉电影10元抵扣券1张】,券号：'.$ticket['ticket_NO'].', 密码：'.$ticket['ticket_password'];
				}
			}
			elseif ($rank == 4 && $user_id > 0)
			{
				//本站99-5优惠券
				$msg = '恭喜您获得【易视网99-5元红包】';
			}
			elseif ($rank == 4 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			elseif ($rank == 5 && $user_id > 0)
			{
				//本站实物优惠券
				$msg = '恭喜您获得【隐形眼镜史迪奇伴侣盒 实物红包】';
			}
			elseif ($rank == 5 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			elseif ($rank == 6 && $user_id > 0)
			{
				//本站实物优惠券
				$msg = '恭喜您获得【隐形眼镜卫康护理液 实物红包】';
			}
			elseif ($rank == 6 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			elseif ($rank == 7 && $user_id > 0)
			{
				//本站实物优惠券
				$msg = '恭喜您获得【隐形眼镜优能洗眼液 实物红包】';
			}
			elseif ($rank == 7 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			elseif ($rank == 8 && $user_id > 0)
			{
				//本站实物优惠券
				$msg = '恭喜您获得【雅漾三件套 实物红包】';
			}
			elseif ($rank == 8 && $user_id <= 0)
			{
				$rt = 'no_login';
				echo $rt;
				exit;
			}
			
			//插入抽奖记录
			mysql_query("insert into ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '2013感恩节抽奖', 'order_sn', '$order_sn')");
			
			//插入站内信记录
			$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'prize')";
			$res_prize = mysql_query($sql_prize);
			if($res_prize){ unread_user_msg($user_id); }
			
			//send_mail($res['consignee'], $res['email'], $title, $msg, 0);
			
			$rt = 'ok'.','.$rank;
			
		}
		else
		{
			$rt = 'had_prize'; //已参加抽奖
		}
		
	}
		
	echo $rt;
}

//20140102活动领取5元格瓦拉优惠券
elseif ($_REQUEST['act'] == '20140102')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$info = array('info_code'=>0, 'info_msg'=>'');
	
	if ($user_id > 0)
	{
		//判断是否已领取
		$tmp = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE order_sn='".$user_id."' AND ticket_type=2 AND end_date=20140228 LIMIT 1");
		if (!$tmp)
		{
			$ticket = $GLOBALS['db']->GetRow("SELECT * FROM lele_gwl WHERE ticket_type=2 AND status=0 AND end_date=20140228 LIMIT 1");
			if ($ticket)
			{
				mysql_query("UPDATE lele_gwl SET status=1, order_sn='".$user_id."' WHERE ticket_type=2 AND ticket_password = '".$ticket['ticket_password']."'"); //标记已使用
				$msg = '恭喜您获得【格瓦拉电影5元抵扣券1张】,密码：'.$ticket['ticket_password'];
			}
			
			//插入站内信记录
			$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) values (".$user_id.", '', ".$_SERVER['REQUEST_TIME'].", '恭喜您获得5元电影票折扣券', '".$msg."', '20140102')";
			$res_prize = mysql_query($sql_prize);
			if($res_prize){ unread_user_msg($user_id); }
			
			$info['info_code'] = 1;
			$info['info_msg']  = $msg;
		}
		else
		{
			$info['info_code'] = 0;
			$info['info_msg']  = '领取失败，您已经领取过了!可在您的站内信中查询券码';
		}
		
	}
	else
	{
		$info['info_code'] = 0;
		$info['info_msg']  = '领取失败，请登录后再领取!';
	}
	
	$str = json_encode($info);
	echo $str;
}

//双11(返回客户购物车的金额)
elseif ($_REQUEST['act'] == '20131111')
{
	$my_amount = $GLOBALS['db']->getOne("SELECT SUM(goods_price*goods_number) AS my_amount FROM ecs_cart WHERE session_id='".SESS_ID."'");
	echo $my_amount;
}

//每周活动之秒杀限购
elseif ($_REQUEST['act'] == 'weekly_miaosha')
{
	$user_id      = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$goods_id     = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	$can_qg		  = 1; //是否能购买:0.抢完(或结束) 1.可购  2.已购  3.未登录 4.会员等级不够 , -1活动已经结束

    if(!$user_id)
    {
        echo $can_qg = 5; exit;
    }

    if($goods_id ==5041){ // 5041
        //查询用户10.2~11.10之间是否有招行支付的订单
        $st = strtotime('2015-10-02 00:00:00');
        $et = strtotime('2015-11-11 00:00:00');
        $sql = "SELECT * FROM ecs_order_info WHERE (pay_id = 15 OR pay_id = 201) AND pay_status = 2 AND user_id = " .$user_id. " AND pay_time >= $st AND pay_time <= $et";
        $order_CMB = $GLOBALS['db']->getOne($sql);

        if(!$order_CMB){
            echo $can_qg = 6; exit;
        }
    }

    // 站内IPHONE秒杀活动
    if($goods_id ==5045){ // 5045
        //查询用户10.2~11.10之间是否有支付成功的订单
        $st = strtotime('2015-10-30 00:00:00');
        $et = strtotime('2015-11-11 00:00:00');
        $sql = "SELECT * FROM ecs_order_info WHERE pay_status = 2 AND user_id = " .$user_id. " AND pay_time >= $st AND pay_time <= $et";
        $order_CMB = $GLOBALS['db']->getOne($sql);

        if(!$order_CMB){
            echo $can_qg = 4; exit;
        }
    }

    //当前的秒杀信息
	$req_time = $_SERVER['REQUEST_TIME'];
	$ms_info = $GLOBALS['db']->getRow("SELECT * FROM ecs_miaosha WHERE status=0 AND start_time <= " .$req_time. " AND end_time >= " .$req_time. " ORDER BY rec_id DESC LIMIT 1");

	if ($ms_info)
	{
        if($ms_info['user_rank'])
        {
            $rankArr = explode(',',$ms_info['user_rank']);
            if(!in_array($_SESSION['user_rank'] , $rankArr))
            {
                echo $can_qg = 4; exit;
            }
        }

		if ($ms_info['is_limited'] == 1)
		{
            $s_time = $ms_info['start_time'];
            $e_time = $ms_info['end_time'];
			//1.判断总限购数量
			//购物车商品数量
			$c_num = $GLOBALS['db']->GetOne("SELECT SUM(goods_number) FROM ecs_cart WHERE goods_id=".$goods_id." AND extension_code='miaosha_buy'
											AND add_time >= '".date('Y-m-d H:i:s', $s_time)."' AND add_time <= '".date('Y-m-d H:i:s', $e_time)."'");
			$cart_number = (intval($c_num) > 0) ? intval($c_num): 0;

			//订单中商品的数量
			$o_number = $GLOBALS['db']->GetOne("SELECT SUM(b.goods_number) FROM ecs_order_info a LEFT JOIN ecs_order_goods b ON a.order_id=b.order_id
												WHERE a.order_status != 2  AND a.add_time >= " .$s_time. " AND a.add_time <= " .$e_time. " AND
												b.goods_id=".$goods_id." AND b.extension_code='miaosha_buy'");
			$order_number = (intval($o_number) > 0) ? intval($o_number): 0;

			if (($cart_number + $order_number) >= $ms_info['total_limited'])
			{
				$can_qg = 0; //已售数量超过限制
			}
		}
        else if($ms_info['each_limited'])
        {
            $s_time = $ms_info['start_time'];
            $e_time = $ms_info['end_time'];
            //2.判断个人限购数量
            //购物车商品数量
            $c_num = $GLOBALS['db']->GetOne("SELECT SUM(goods_number) FROM ecs_cart WHERE user_id = ".$user_id." AND goods_id=".$goods_id." AND extension_code='miaosha_buy'
                                             AND add_time >= '".date('Y-m-d H:i:s', $s_time)."' AND add_time <= '".date('Y-m-d H:i:s', $e_time)."'");
            $cart_number = (intval($c_num) > 0) ? intval($c_num): 0;

            //订单中商品的数量
            $o_number = $GLOBALS['db']->GetOne("SELECT SUM(b.goods_number) FROM ecs_order_info a LEFT JOIN ecs_order_goods b ON a.order_id=b.order_id
                                        WHERE a.user_id = ".$user_id." AND a.order_status != 2 AND a.add_time >= " .$s_time. " AND a.add_time <= " .$e_time. " AND
                                        b.goods_id=".$goods_id." AND b.extension_code='miaosha_buy'");
            $order_number = (intval($o_number) > 0) ? intval($o_number): 0;
            if (($cart_number + $order_number) >=$ms_info['each_limited'] )
            {
                $can_qg = 2; //已购买
            }
        }
    }else{
        $can_qg = -1;
    }
	echo $can_qg; exit;
}

elseif ($_REQUEST['act'] == 'icbc_140624')
{
	$ticket_NO = isset($_REQUEST['ticket_NO'])? trim($_REQUEST['ticket_NO']): 0;
	if (is_numeric($ticket_NO))
	{
		$ticket_row = $GLOBALS['db']->GetRow("SELECT * FROM ecs_user_bonus WHERE (bonus_type_id=1422 OR bonus_type_id=1431) AND bonus_sn=".$ticket_NO." AND used_time=0");
		if ($ticket_row)
		{
			$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '3648', '', '【工行支付专享】海俪恩植物精灵清凉型护理液120ml', '21.00', '0.00', '1', '1', 'unchange', '1')";
			$res_cart = $GLOBALS['db']->query($sql_cart);
			if ($res_cart)
			{
				echo '赠品已加入购物车,请查看您的购物车,满50元即享包邮';
				mysql_query("UPDATE ecs_user_bonus SET used_time=1 WHERE (bonus_type_id=1422 OR bonus_type_id=1431) AND bonus_sn=".$ticket_NO);
			}
		}
		else 
		{
			echo '您输入的券号不正确,或已被使用';
		}
		
	}
	else 
	{
		echo '您输入的券号不正确!';
	}
}

//2014周年庆
elseif($_REQUEST['act'] == '140801')
{
	$order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']) : 0;
	$user_id = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");
	
	if (is_numeric($order_sn) && $user_id)
	{
		//判断这个订单号是否属于这个用户
		$add_time = $GLOBALS['db']->GetOne("SELECT add_time FROM ecs_order_info WHERE user_id = ".$user_id." AND order_sn = '" .$order_sn. "' AND (shipping_status=1 OR shipping_status=2) LIMIT 1");
		if ( ! empty($add_time))
		{
			if ($add_time >= strtotime('2014-08-01 00:00:00') && $add_time <= strtotime('2014-08-31 23:59:59'))
			{
				//是否已经参加抽奖
				$gotit = $GLOBALS['db']->GetOne("SELECT rec_id FROM ecs_prize WHERE refer='易视网4周年大抽奖' AND extension_id='".$order_sn."' LIMIT 1");
				if ( ! empty($gotit))
				{
					echo '您的订单已经抽过奖了，谢谢您的参与！';
				}
				else
				{
					//可以抽奖:1.谢谢参与25%, 2.再来一次20%, 3.赠送100积分40%, 4. 5元现金券15% 红包ID：1507
					$rank = get_prize_20140801();
					if ($rank == 2)
					{
						//再来一次
						echo '哎哟，还不错哦！再来一次试试看！';
					}
					else
					{
						$sql = "INSERT INTO ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '易视网4周年大抽奖', 'order_sn', '$order_sn')";
						mysql_query($sql);
						if ($rank == 1)
						{
							echo '谢谢参与！表紧，下次运气会更好！';
						}
						elseif ($rank == 3)
						{
							echo '恭喜您获得100积分，去您的账户里查看吧！';
							log_account_change($user_id, 0, 0, 0, 100, '易视网四周年大抽奖');
						}
						elseif ($rank == 4)
						{
							echo '太幸运了！您已经获得5元红包，已发放至您的账户！';
							$sql = "insert into ".$GLOBALS['ecs']->table('user_bonus')."(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values(1507, 0, '$user_id', 0, 0, 0);";
							mysql_query($sql);
						}
					}
				}
			}
			else 
			{
				echo '您的订单不在有效时间内!';
			}
		}
		else
		{
			echo '这不是您的有效订单!';
		}
	}
	elseif (empty($user_id))
	{
		echo '请您登录后再参加抽奖!';
	}
	else 
	{
		echo '错误的订单号!';
	}
}

//财付通领取赠品
elseif($_REQUEST['act'] == '140911')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	if ( ! empty($user_id))
	{
		//1.判断是否是新人(即是否是首次购物)
		$is_new_user = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS num FROM ecs_order_info WHERE user_id=".$user_id. " AND (order_status = 0 OR order_status = 1 OR order_status = 5) ");
		if (empty($is_new_user))
		{
			//2.判断购物车是否已领取赠品
			$condition1 = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS num FROM ecs_cart WHERE user_id=".$user_id. " AND goods_id=4216");
			if (empty($condition1))
			{
				//赠品插入购物车
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4216', '', '【财付通】保视宁水氧方隐形眼镜润眼液15ml', '15.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
				if ($res_cart)
				{
					echo 'ok';
				}
				else 
				{
					echo 'n';
				}
			}
			else
			{
				echo 'n';
			}
		}
		else 
		{
			echo 'old';
		}
		
		
	}
	else
	{
		echo 'n';
	}
}

//2014双11预热抽奖
elseif($_REQUEST['act'] == '141103')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");
	$now = time();
	if ( ! empty($user_id) && $now <= strtotime('2014-11-13 23:59:59'))
	{
		//1.今天是否已参加
		$str_today = date('Y-m-d', time());
		$begin_today = strtotime($str_today.' 00:00:00');
		$end_today = strtotime($str_today.' 23:59:59');
		
		$gotit = $GLOBALS['db']->GetOne("SELECT rec_id FROM ecs_prize WHERE refer='2014双11预热抽奖' AND user_id='".$user_id."' AND add_time >= ".$begin_today." AND add_time <= ".$end_today." LIMIT 1");
		if ( ! empty($gotit))
		{
			echo '您今天已经抽过奖了，谢谢您的参与！';
		}
		else 
		{
			$rank = get_prize_20141103();
			//5元现金券：1, 10元现金券：2, 50元现金券:3, 日本和风手绢： 4,  3M口罩：5,  暖宝宝：6,  高级运动随身杯： 7
			$sql = "INSERT INTO ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '2014双11预热抽奖', 'user_id', '$user_id')";
			mysql_query($sql);
			if ($rank == 1)
			{
				$sql = "INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (1688, 0, '$user_id', 0, 0, 0);";
				mysql_query($sql);
			}
			elseif ($rank == 2)
			{
				$sql = "INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (1689, 0, '$user_id', 0, 0, 0);";
				mysql_query($sql);
			}
			elseif ($rank == 3)
			{
				$sql = "INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (1690, 0, '$user_id', 0, 0, 0);";
				mysql_query($sql);
			}
			elseif ($rank == 4)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4157', '', '【双11抽奖】日本和风手绢', '45.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			elseif ($rank == 5)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4334', '', '【双11抽奖】3M口罩', '20.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			elseif ($rank == 6)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4335', '', '【双11抽奖】暖宝宝', '30.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			elseif ($rank == 7)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4329', '', '【双11抽奖】高级运动随身杯', '89.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			
			echo $rank;
		}
		
	}
	else
	{
		echo '您未登录,请先登录后参加！';
	}
}

//2014感恩节抽奖
elseif($_REQUEST['act'] == '141118')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$user_name = $GLOBALS['db']->getOne("select user_name from ecs_users where user_id='$user_id' limit 1");
	$order_id = empty($_REQUEST['order_id'])? 0 : addslashes($_REQUEST['order_id']);
	
	if(empty($order_id)){echo '请输入您的订单号！';die;}
	
	$order_add_time = $GLOBALS['db']->getOne("select pay_time from ecs_order_info where order_sn='".$order_id."' 
	and pay_status = 2 and shipping_status = 1 and user_id = '".$user_id."' limit 1");
	
	if(empty($order_add_time)||$order_add_time<1416324600){echo '请输入有效的订单号！';die;}
	
	$now = time();
	if ( ! empty($user_id)  && $now <= strtotime('2014-12-3 00:00:00'))
	{
		$had_prize = $GLOBALS['db']->GetOne("select * from ecs_prize where extension_id='".$order_id."' and refer = '2014感恩节抽奖'");//订单是否已使用过
		
		if($had_prize){echo '此订单号已经参与过抽奖^_^';die;}
		
			$rank = get_prize_20141118();
			//$rank =4;
			//么么哒	1 护理液2 暖宝宝3 电影票2张4 3M口罩5
			$sql = "INSERT INTO ecs_prize (user_id, user_name, add_time, prize_rank, refer, extension, extension_id) 
			values ('$user_id', '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '$rank', '2014感恩节抽奖', 'order_id', '$order_id')";
			mysql_query($sql);
			if ($rank == 1)
			{
				
			}
			elseif ($rank == 2)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) 
				VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4317', '', '【感恩节抽奖】海俪恩水涟隐形眼镜护理液500ml', '30.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			elseif ($rank == 3)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) 
				VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4335', '', '【感恩节抽奖】暖宝宝', '30.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			elseif ($rank == 4)
			{
				
						$robgwlsql = "select * from lele_gwl where order_sn='".$order_id."' and ticket_type=4 AND status=1 LIMIT 1";
						$robgwl = $GLOBALS['db']->GetRow($robgwlsql);
						if(empty($robgwl)){
							$ticket = $GLOBALS['db']->GetAll("SELECT * FROM lele_gwl WHERE ticket_type=4 AND status=0 LIMIT 2");
							if ($ticket && count($ticket)>=2)
							{
									$t = '';
									foreach($ticket as $v){
										mysql_query("UPDATE lele_gwl SET status=1,order_sn='".$order_id."' WHERE ticket_type=4 
										AND ticket_password = '".$v['ticket_password']."'"); //标记已使用
										$t.="<b>".$v['ticket_password']."</b>&nbsp;&nbsp;";
									}
									
								$msg .= '恭喜您获得【蜘蛛网 黄飞鸿之英雄有梦3D电影票2张】,<br/>
								抵用券代码：'.$t.'<br/>
								请登录蜘蛛网（<a target="_blank" href="http://film.spider.com.cn/">http://film.spider.com.cn/</a> ）,选择您最近的电影院,场次及座位，
								<br/>结算时输入此码即可价格为0元，此码仅限“黄飞鸿之英雄有梦”3D电影，活动截止到：2014年11月30日，有问题可咨询蜘蛛网客服400 1500 666';
								
								$sql_prize = "insert into ecs_user_msg (user_id, user_name, add_time, title, msg, extension) 
								values (".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '2014易视感恩节回馈', '".$msg."', 'prize')";
								$res_prize = mysql_query($sql_prize);
								if($res_prize){ unread_user_msg($user_id); }
								
							}
						}
						
				
			}
			elseif ($rank == 5)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) 
				VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '4334', '', '【感恩节抽奖】3M口罩', '20.00', '0.00', '1', '1', 'unchange', '1')";
				$res_cart = $GLOBALS['db']->query($sql_cart);
			}
			
			echo $rank;
		
	}
	else
	{
		echo '您未登录,请先登录后参加！';die;
	}
}
//2014韩都衣舍券码送免费护理液
elseif($_REQUEST['act'] == '141208')
{
	$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$bonus_sn  = isset($_REQUEST['bonus_sn'])? addslashes($_REQUEST['bonus_sn']): 0;
	$now = time();
	if ( ! empty($user_id) && ! empty($bonus_sn) && $now <= strtotime('2014-12-31 23:59:59'))
	{
		//券号是否存在
		$used_bonus = $GLOBALS['db']->GetRow("SELECT user_id,bonus_id FROM ecs_user_bonus 
		WHERE bonus_type_id='1737' and bonus_sn = '".$bonus_sn."'");
		
		if(!empty($used_bonus)){
			if($used_bonus['user_id']!=0){
				echo '此优惠券已被使用^_^';
				die;
			}else{
				//券号使用者改为此用户
			
				mysql_query("UPDATE ecs_user_bonus SET user_id = ".$user_id." ,used_time =".time()."
				 WHERE bonus_type_id = 1737 AND bonus_sn =".$bonus_sn);//标记已使用
				//此用户购物车添加护理液
				$query = insert_cart(4409, 1, 0, 0, 'unchange');
				
				if($query){
					echo '兑换成功！免费护理液已添加到您的购物车中^_^';die;
				}
			}
		
		}else{
			echo '请输出有效的券号^_^';die;
		}
	}
	else
	{
		echo '请登录后输入优惠券^_^！';die;
	}
}
elseif($_REQUEST['act'] == '150202')
{
    if($_SESSION['user_id'] > 0){
        //查询是否已有1861,1862,1863,1864		
		$lottery  = $GLOBALS['db']->GetAll("SELECT * from ecs_user_bonus 
		WHERE bonus_type_id in(1861,1862,1863,1864) AND user_id = ".$_SESSION['user_id']);
        if(!empty($lottery)){
            
            $re_mess = "抱歉，您已经抽过红包了！";
        }else{
                $m_s  = mt_rand(1, 100);
                switch($m_s)
                {
                    case($m_s<=10):
                        $bonus_id = 1861;
                        $mess = '2015新年红包5元';
                	break;
                	case($m_s>10 && $m_s<=40):
                	   $bonus_id = 1862; 
                       $mess = '2015新年红包15元';
                	break;
                	case($m_s>40 && $m_s<=70):
                		$bonus_id = 1863;
                        $mess = '2015新年红包30元';
                	break;
                	case($m_s>70 && $m_s<=100):
                	   $bonus_id = 1864;
                       $mess = '2015新年红包50元';
                    break;
                	default:
                	   $bonus_id = 1862;
                       $mess = '2015新年红包15元';
                	break;
                }
                $send_bonus = $GLOBALS['db']->query("INSERT INTO ecs_user_bonus (bonus_type_id,bonus_sn,user_id,used_time,order_id,emailed,unlimit) VALUES 
                        (".$bonus_id.",0,".$_SESSION['user_id'].",0,0,1,0)");
                if($send_bonus){
                    $re_mess = "恭喜您获得".$mess."!请至个人中心-我的优惠券/红包查看" ;
                }else{
                    $re_mess = "网络错误，请稍后再试!";
                }
        }
        
    }else{
        $re_mess = "您未登陆,请登陆后再试！";
    }
        echo $re_mess;die;    
}
elseif($_REQUEST['act'] == '150501')
{
    if($_SESSION['user_id'] > 0 && !empty($_POST['bonus_id'])){
        //查询是否已有2011,2012,2013		
		
                $bonus  = $_POST['bonus_id'];
                switch($bonus)
                {
                    case($bonus == 1):
                        $lottery  = $GLOBALS['db']->GetAll("SELECT * from ecs_user_bonus 
		WHERE bonus_type_id in(2011) AND user_id = ".$_SESSION['user_id']);
                        if(!empty($lottery)){
                            $re_mess = "抱歉，您已经领取过该红包了！";
                        }else{
                            $bonus_id = 2011;
                            $mess = '150-10红包';
                            $send_bonus = $GLOBALS['db']->query("INSERT INTO ecs_user_bonus (bonus_type_id,bonus_sn,user_id,used_time,order_id,emailed,unlimit) VALUES 
                        (".$bonus_id.",0,".$_SESSION['user_id'].",0,0,1,0)");
                        }
                	break;
                	case($bonus == 2):
                        $lottery  = $GLOBALS['db']->GetAll("SELECT * from ecs_user_bonus 
		WHERE bonus_type_id in(2012) AND user_id = ".$_SESSION['user_id']);
                        if(!empty($lottery)){
                            $re_mess = "抱歉，您已经领取过该红包了！";
                        }else{
                    	    $bonus_id = 2012; 
                            $mess = '300-25红包';
                            $send_bonus = $GLOBALS['db']->query("INSERT INTO ecs_user_bonus (bonus_type_id,bonus_sn,user_id,used_time,order_id,emailed,unlimit) VALUES 
                        (".$bonus_id.",0,".$_SESSION['user_id'].",0,0,1,0)");
                        }
                	break;
                	case($bonus == 3):
                        $lottery  = $GLOBALS['db']->GetAll("SELECT * from ecs_user_bonus 
		WHERE bonus_type_id in(2013) AND user_id = ".$_SESSION['user_id']);
                        if(!empty($lottery)){
                            $re_mess = "抱歉，您已经领取过该红包了！";
                        }else{
                    	    $bonus_id = 2013;
                            $mess = '500-50红包';
                            $send_bonus = $GLOBALS['db']->query("INSERT INTO ecs_user_bonus (bonus_type_id,bonus_sn,user_id,used_time,order_id,emailed,unlimit) VALUES 
                        (".$bonus_id.",0,".$_SESSION['user_id'].",0,0,1,0)");
                        }
                        
                	break;
                }
                
                if($send_bonus){
                    $re_mess = "恭喜您获得".$mess."!请至个人中心-我的优惠券/红包查看" ;
                }
       
        
    }else{
        $re_mess = "您未登陆,请登陆后再试！";
    }
        echo $re_mess;die;    
}
/* 2015蜘蛛网活动 */
elseif($_REQUEST['act'] == '160712'){
    
    if(time()>strtotime("2016-09-01 00:00:00")){
       // echo '活动已结束!';die;
    }
    if($_SESSION['user_id'] > 0){
        
        $is_new_user = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE user_id = ".$_SESSION['user_id']." AND (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5))) limit 1");
        if($is_new_user){
            echo '对不起！本次活动只限新用户申请！';die;
        }
        /*if (empty($_POST['captcha'])){
            echo '请输入验证码！';die;
        }*/
        if(empty($_POST['name'])){
            echo '请输入姓名！';die;
        }
        if(empty($_POST['phone'])){
            echo '请输入手机号！';die;
        }elseif(!preg_match("/^^[1][3578][0-9]{9}$/",$_POST['phone'])){
            echo "请输入正确的手机号！";die;
        }
        if(empty($_POST['ds'])){
            echo '请选择度数！';die;
        }
        if(empty($_POST['address'])){
            echo '请输入地址！';die;
        }

        /*//检查验证码
        include_once('includes/cls_captcha.php');
        $validator = new captcha();

        if(!$validator->check_word($_POST['captcha']))
        {
            echo '验证码错误！';die;
        }*/

        $user_id = $_SESSION['user_id'];
        $address = htmlspecialchars($_POST['address']);
        $phone   = htmlspecialchars($_POST['phone']);
        $ds      = htmlspecialchars($_POST['ds']);
        $ds2     = htmlspecialchars($_POST['ds2']);
        $ds_info = $ds.','.$ds2; 
        $have_askd = $GLOBALS['db']->getOne("SELECT id FROM temp_active WHERE act_id ='20161122' AND user_id = '$user_id' ");

        if(!$have_askd){
            $insert = $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`remarks`,`phone`,`address`) VALUES ('20161122','$user_id','$ds_info','$phone','$address');");
        }else{
            echo '您已申请成功！请勿重复申请谢谢！';die;
        }
        if($insert){
            echo '您已申请成功！';die;
        }

    }else{
        echo '请登陆后再试！';
    }
    die;

}
/* 20151118活动 */
elseif($_REQUEST['act'] == '151118'){

    if($_SESSION['user_id'] > 0){
        /*if (empty($_POST['captcha'])){
            echo '请输入验证码！';die;
        }*/
        if(empty($_POST['name'])){
            echo '请输入姓名！';die;
        }
        if(empty($_POST['phone'])){
            echo '请输入手机号！';die;
        }elseif(!preg_match("/^^[1][3578][0-9]{9}$/",$_POST['phone'])){
            echo "请输入正确的手机号！";die;
        }
        if(empty($_POST['ds'])){
            echo '请选择度数！';die;
        }
        if(empty($_POST['address'])){
            echo '请输入地址！';die;
        }

        /*//检查验证码
        include_once('includes/cls_captcha.php');
        $validator = new captcha();

        if(!$validator->check_word($_POST['captcha']))
        {
            echo '验证码错误！';die;
        }*/

        $user_id = $_SESSION['user_id'];
        $address = htmlspecialchars($_POST['address']);
        $phone   = htmlspecialchars($_POST['phone']);
        $ds      = htmlspecialchars($_POST['ds']);

        $have_askd = $GLOBALS['db']->getOne("SELECT id FROM temp_active WHERE act_id ='20151118' AND user_id = '$user_id' ");

        if(!$have_askd){
            $insert = $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`remarks`,`phone`,`address`) VALUES ('20151118','$user_id','$ds','$phone','$address');");
        }else{
            echo '您已申请成功！请勿重复申请谢谢！';die;
        }
        if($insert){
            echo '您已申请成功！';die;
        }

    }else{
        echo '请登陆后再试！';
    }
    die;

}elseif($_REQUEST['act'] == 'get_bonus_151127'){

    $user_id = $_SESSION['user_id'];
    if($user_id>0){
        $bonus_id = intval($_POST['bonus_id']);
        if($bonus_id == 1){
            $bonus_id = 2591;
        }elseif($bonus_id ==2){
            $bonus_id = 2592;
        }elseif($bonus_id ==3){
            $bonus_id = 2595;
        }elseif($bonus_id ==4){
            $bonus_id = 2593;
        }elseif($bonus_id ==5){
            $bonus_id = 2594;
        }else{
            $bonus_id = 2591;
        }

        $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')."
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);

        if(!empty($quan)){
            echo '3';//已经领取过
        }else{

            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}elseif($_REQUEST['act'] == 'get_bonus_151211'){

    $user_id = $_SESSION['user_id'];
    if($user_id>0){
        $bonus_id = intval($_POST['bonus_id']);
        if($bonus_id == 1){
            $bonus_id = 2633;
        }else{
            $bonus_id = 2634;
        }

        $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')."
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);

        if(!empty($quan)){
            echo '3';//已经领取过
        }else{

            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}elseif($_REQUEST['act'] == 'get_bonus_160905'){

    $user_id = $_SESSION['user_id'];
    if($user_id>0){
        $bonus_id = intval($_POST['bonus_id']);
        if($bonus_id == 1){
            $bonus_id = 3123;
        }elseif($bonus_id == 2){
            $bonus_id = 3124;
        }else{
            $bonus_id = 3125;
        }

        $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')."
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);

        if(!empty($quan)){
            echo '3';//已经领取过
        }else{

            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}elseif($_REQUEST['act'] == 'get_bonus_151224'){

    $user_id = $_SESSION['user_id'];
    if($user_id>0){
        $bonus_id = intval($_POST['bonus_id']);
        if($bonus_id == 1){
            $bonus_id = 2636;
        }elseif($bonus_id ==2){
            $bonus_id = 2638;
        }elseif($bonus_id ==3){
            $bonus_id = 2640;
        }elseif($bonus_id ==4){
            $bonus_id = 2639;
        }else{
            $bonus_id = 2636;
        }

        $quan = $GLOBALS['db']->getOne("select count(bonus_id) from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$user_id' and bonus_type_id = ".$bonus_id);
        //var_dump($quan);die;
        if($quan >= 6){
            echo '3';//已经领取过3次
        }else{

            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}
elseif($_REQUEST['act'] == 'get_bonus_160118'){

    $user_id = $_SESSION['user_id'];
    if($user_id>0){
        $bonus_id = intval($_POST['bonus_id']);
        if($bonus_id == 1){
            $bonus_id = 2700;
        }elseif($bonus_id ==2){
            $bonus_id = 2701;
        }elseif($bonus_id ==3){
            $bonus_id = 2702;
        }else{
            $bonus_id = 2700;
        }
        $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')."
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);

        if(!empty($quan)){
            echo '3';//已经领取过
        }else{

            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }

    die;

}
// 2016年春节活动第三波
elseif($_REQUEST['act'] == 'get_bonus_160208'){
    $user_id  = $_SESSION['user_id'];
    $order_sn = strtotime(date("Y-m-d")) - 28800;    // 获取当天时间作为当日唯一标识
    if($user_id>0){
        $bonus_id = rand(1,4);
        if($bonus_id == 1){
            $bonus_id = 2723;
        }elseif($bonus_id ==2){
            $bonus_id = 2724;
        }elseif($bonus_id ==3){
            $bonus_id = 2725;
        }elseif($bonus_id ==4){
            $bonus_id = 2726;
        }else{
            $bonus_id = 2723;
        }

        $quan = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = 20160208 AND order_sn = '".$order_sn."' AND user_id = '".$user_id."'");  // 判断当天是否中过笔记本

        if($quan > 0){
            echo '3';//已经领取过
        }else{
            // 将红包发放至账户
            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            // 记录红包领取时间
            $in_bonus   = $GLOBALS['db']->query("INSERT INTO `temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn`) VALUES (NULL , '20160208', '".$user_id."', '".$order_sn."')");
            echo $bonus_id;//领取成功
        }

    }else{
        echo '2';//未登录
    }

    die;

}
// 2016年女人节抽奖活动
elseif($_REQUEST['act'] == '160518'){
    $order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']): '';
    $user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
    if($user_id > 0){//是否登录
        if(time()<strtotime('2016-06-05 00:00:00')){
            $can_cj   = $GLOBALS['db']->getRow("SELECT order_status,shipping_status,pay_status,shipping_fee,money_paid,pay_id FROM ecs_order_info WHERE 
            order_sn = '".$order_sn."' AND add_time > '".strtotime('2016-05-20 00:00:00')."' AND add_time < '".strtotime('2016-06-05 00:00:00')."';");//查询订单状态;
            
            if($can_cj['pay_id'] == 15 && $can_cj['pay_status'] == 2){ // 订单状态为已付款发货中或者已付款确认收货时才可参与抽奖
                //是否已抽过（当天）
                $getTimes = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE act_id = 160518  AND order_sn = '".$order_sn."'   AND user_id = '".$user_id."';");//该订单号是否抽过奖
                
                if(!$getTimes){//没有抽过
                    $getPrice = mt_rand(1, 2);
                    
                    if($getPrice == 1){
                        $goods_name = '5元红包';
                        $bonus_id = 2881;
                        $award_id = array_rand(array('2'=>2,'7'=>7),1);
                        $res = array('id'=>$award_id,'name'=>$goods_name);
                    }elseif($getPrice == 2){
                        $goods_name = '10元红包';
                        $bonus_id = 2882;
                        $award_id = array_rand(array('4'=>4,'9'=>9),1);
                        $res = array('id'=>$award_id,'name'=>$goods_name);
                    }
                    if($getPrice){
                        $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['ecs']->table('user_bonus'). "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values($bonus_id, 0, '$user_id', 0, 0, 0);");
                        $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`order_sn`,`remarks`)  VALUES (160518,'$user_id','".$order_sn."','$getPrice');");//插入此订单号抽奖记录
                    }

                }else{//抽过
                    $res = array('err'=>'您的订单号已经抽过奖啦！');
                }
            }else{
                $res = array('err'=>'您的订单不符合抽奖要求');//不符合要求
            }
        }else{
            $res = array('err'=>'活动已过期');//活动过期
        }
    }else{
        $res = array('err'=>'请登录后再试');//未登录
    }
    echo json_encode($res);die;
}
// 2016年桃花节领券
elseif($_REQUEST['act'] == 'get_bonus_160317'){
    $user_id   = $_SESSION['user_id'];
    $bonus_id  = $_REQUEST['bonus_id'];
    $order_sn = strtotime(date("Y-m-d")) - 28800;    // 获取当天时间作为当日唯一标识
    if($user_id>0){
        $is_new = $GLOBALS['db']->getOne("SELECT count(order_id) FROM `ecs_order_info` WHERE pay_status = 2 AND user_id = ".$user_id);  // 判断用户是否下过订单并支付完成
        if($bonus_id == 1){
            $bonus_id = 2767;
            if($is_new > 0){echo '4';die;}// 老用户无法领取新用户专享券
        }elseif($bonus_id ==2){
            $bonus_id = 2768;
            if($is_new == 0){echo '5';die;}// 新用户无法领取老用户专享券
        }else{
            $bonus_id = 2767;
        }

        $quan = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = 20160317 AND order_sn = '".$order_sn."' AND user_id = '".$user_id."'");  // 判断是否领取过优惠券

        if($quan > 0){
            echo '3';//已经领取过
        }else{
            // 将红包发放至账户
            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            // 记录红包领取时间
            $in_bonus   = $GLOBALS['db']->query("INSERT INTO `temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn`) VALUES (NULL , '20160317', '".$user_id."', '".$order_sn."')");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }

    die;

}
// 2016年疯狂眼镜城活动领券
elseif($_REQUEST['act'] == 'get_bonus_160411'){
    $user_id   = $_SESSION['user_id'];
    $bonus_id  = $_SESSION['bonus_id'];
    $order_sn  = strtotime(date("Y-m-d")) - 28800;    // 获取当天时间作为当日唯一标识
    if($user_id>0){
        if($bonus_id == 1){
            $bonus_id = 2803;
        }elseif($bonus_id ==2){
            $bonus_id = 2804;
        }elseif($bonus_id ==3){
            $bonus_id = 2805;
        }else{
            $bonus_id = 2803;
        }

        $quan = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = 20160411 AND order_sn = '".$order_sn."' AND user_id = '".$user_id."'");  // 判断当天是否领取过

        if($quan > 0){
            echo '3';//已经领取过
        }else{
            // 将红包发放至账户
            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            // 记录红包领取时间
            $in_bonus   = $GLOBALS['db']->query("INSERT INTO `temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn`) VALUES (NULL , '20160411', '".$user_id."', '".$order_sn."')");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}// 2016年劳动节主会场 
elseif($_REQUEST['act'] == '160501'){
    $order_sn = isset($_REQUEST['order_sn'])? trim($_REQUEST['order_sn']): '';
    $user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
    log_account_change($user_id, 0, 0, 0, 100, '2016劳动节抽奖积分');
    
    if($user_id > 0){//是否登录
        if(time()<strtotime('2016-05-16 00:00:00')){
            $can_cj   = $GLOBALS['db']->getRow("SELECT user_id,order_status,shipping_status,pay_status,shipping_fee,money_paid FROM ecs_order_info WHERE order_sn = '".$order_sn."' AND add_time > '".strtotime('2016-03-15 00:00:00')."' AND add_time < '".strtotime('2016-05-15 00:00:00')."';");//查询订单状态;
            $paid     = $can_cj['money_paid'] - $can_cj['shipping_fee'];
            
            if($can_cj['user_id'] == $user_id){
                if(($can_cj['order_status'] == 5 || $can_cj['order_status'] == 1) && ($can_cj['shipping_status'] == 1 || $can_cj['shipping_status'] == 2) && $can_cj['pay_status'] == 2 && $paid >= 0.01){ // 订单状态为已付款发货中或者已付款确认收货时才可参与抽奖
                    //该订单号是否抽过奖
                    $getTimes = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE act_id = 160501  AND order_sn = '".$order_sn."'   AND user_id = '".$user_id."';");
                    
                    if(!$getTimes){//没有抽过
                        $getPrice = get_prize_160501();
                        if($getPrice == 1){
                            $goods_id = 827;
                            $goods_name = '蓝睛灵120ml';
                            $award_id = 9;
                            $res = array('id'=>$award_id);
    
                        }elseif($getPrice == 2){
                            $goods_id = 5190;
                            $goods_name = '菲士康焕彩日抛2片装';
                            $award_id = 2;
                            $res = array('id'=>$award_id);
    
                        }elseif($getPrice == 3){
                            $goods_id = 3225;
                            $goods_name = '凯达eyekan纯真年代A8101隐形眼镜保养盒-简约装';
                            $award_id = 7;
                            $res = array('id'=>$award_id);
    
                        }elseif($getPrice == 4){
                            $goods_id = rand(5212,5213);
                            $goods_name = '实瞳恋必顺双周抛6片';
                            $award_id = 5;
                            $res = array('id'=>$award_id);
    
                        }elseif($getPrice == 5){
                            $goods_name = '红包';
                            $award_id = 10;
                            $res = array('id'=>$award_id);
    
                        }elseif($getPrice == 6){
                            $goods_name = '100积分';
                            $award_id = 4;
                            $res = array('id'=>$award_id);
    
                        }else{
                            //$res = array('award_id'=>$award_id,'award_name'=>'再玩一次,再接再厉！');
                            $res = array('err'=>'奖品被抽完啦，我们将记录您的中奖信息，稍后给您相应的补偿！');//奖品抽完了，只记录中奖信息
                        }
                        
                        if($getPrice <5){
                            //实物插入购物车
                            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('cart') .
                                "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`, `add_time`)
                                    VALUES ('".$user_id."', '".SESS_ID."', '$goods_id', '00$goods_id', '[劳动节抽奖]$goods_name', '0.00', '0.00', '1', '', '1', 'unchange', '60308', '1', '', 'date(\"Y-m-d H:m:i\",time())')");
                            
                        }elseif($getPrice == 5){
                            //红包打入账户
                                $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (2820, 0, '$user_id', 0, 0, 0);");
                        }elseif($getPrice == 6){
                            //积分打入账户
                            log_account_change($user_id, 0, 0, 0, 100, '2016劳动节抽奖积分');
                            
                        }else{
                            $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`order_sn`,`remarks`)  VALUES (160501,'$user_id','".$order_sn."','奖品已抽完');");//插入此订单号抽奖记录
                        }
                        $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`order_sn`,`remarks`)  VALUES (160501,'$user_id','".$order_sn."','".$goods_name."');");//插入此订单号抽奖记录
                            
    
                    }else{//抽过
                        $res = array('err'=>'您的订单号已经抽过奖啦！');
                    }
                }else{
                    $res = array('err'=>'您的订单不符合抽奖要求');//不符合要求
                }
            }else{
                $res = array('err'=>'您的订单不符合抽奖要求');//活动过期
            }
        }else{
            $res = array('err'=>'活动已过期');//活动过期
        }
    }else{
        $res = array('err'=>'请登录后再试');//未登录
    }
    echo json_encode($res);die;
}elseif($_REQUEST['act'] == 'bm_160412'){
    $user_id   = $_SESSION['user_id'];
    if(!$_COOKIE['have_bm']){
        
        $username       = cleanInput($_POST['username']);
        $phone          = cleanInput($_POST['phone']);
        $diopter        = cleanInput($_POST['diopter']);
        $prefercolor    = cleanInput($_POST['prefercolor']);
        $email          = cleanInput($_POST['email']);
        $note           = cleanInput($_POST['note']);
        
        if ($_FILES['user_img']['tmp_name'] != '' && $_FILES['user_img']['tmp_name'] != 'none')
        {
            $img_name = md5(time()).'.jpg';
            /*CDN图片上传*/
            try {
                $fh = fopen($_FILES['user_img']['tmp_name'], 'rb');
                $GLOBALS['upyun']->writeFile('/active/160412/'.$img_name, $fh, True);   // 上传原图图片，自动创建目录
                fclose($fh);
            }catch(Exception $e) {
                echo $e->getCode();
                echo $e->getMessage();
                exit();
            }
        }
        $remarks = $username.",".$diopter.','.$diopter.','.$prefercolor.','.$email.','.$note.','.$img_name;
        $sql = $GLOBALS['db']->query("INSERT INTO temp_active (act_id,remarks,phone) VALUES ('160405','$remarks','$phone')");
        
        setcookie("have_bm",1, time()+3600*24);
        //show_message('恭喜您申请成功^_^');
        echo "<script type='text/javascript'>alert('恭喜您申请成功^_^');</script>";
        echo "<script>location.href='active.php?id=160405';</script>";   // 跳转到 t.php
        die;
    }else{
        show_message('您已申请成功,请勿重复申请谢谢^_^');
        exit();
    }
}
// 2016年防紫外线专场领券活动
elseif($_REQUEST['act'] == 'get_bonus_160503'){
    $user_id   = $_SESSION['user_id'];
    $bonus_id  = intval($_POST['bonus_id']);
    $order_sn  = strtotime(date("Y-m-d")) - 28800;    // 获取当天时间作为当日唯一标识
    if($user_id>0){
        if($bonus_id == 1){// 5
            $bonus_id = 2862;
        }elseif($bonus_id ==2){// 15
            $bonus_id = 2863;
        }elseif($bonus_id ==3){// 25
            $bonus_id = 2864;
        }elseif($bonus_id ==4){// 30
            $bonus_id = 2865;
        }else{
            $bonus_id = 2862;
        }

        $quan = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = '".$bonus_id."' AND order_sn = '".$order_sn."' AND user_id = '".$user_id."'");  // 判断当天是否领取过

        if($quan > 0){
            echo '3';//已经领取过
        }else{
            // 将红包发放至账户
            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            // 记录红包领取时间
            $in_bonus   = $GLOBALS['db']->query("INSERT INTO `temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn`) VALUES (NULL , '".$bonus_id."', '".$user_id."', '".$order_sn."')");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}// 2016 618领券
elseif($_REQUEST['act'] == 'get_bonus_160618'){
    $user_id  = $_SESSION['user_id'];
    $bonus_id = $_REQUEST['bonus_id'];
    
    if($user_id>0){
        if($bonus_id == 1){
            $bonus_id = 2897;
        }elseif($bonus_id ==2){
            $bonus_id = 2898;
        }elseif($bonus_id ==3){
            $bonus_id = 2901;
        }elseif($bonus_id ==4){
            $bonus_id = 2902;
        }elseif($bonus_id ==5){
            $bonus_id = 2899;
        }elseif($bonus_id ==6){
            $bonus_id = 2900;
        }elseif($bonus_id ==7){
            $bonus_id = 2904;
        }elseif($bonus_id ==8){
            $bonus_id = 2903;
        }else{
            $bonus_id = 2897;
        }
        
        $quan = $GLOBALS['db']->getOne("SELECT bonus_id FROM ".$GLOBALS['ecs']->table('user_bonus')." WHERE user_id = '".$user_id."' AND bonus_type_id = '".$bonus_id."' ");  // 判断是否领取过优惠券

        if($quan){
            echo '3';//已经领取过
        }else{
            // 将红包发放至账户
            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }

    die;

}elseif($_REQUEST['act'] == 'get_bonus_160801'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 3025;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3026;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 3027;
            }elseif($_POST['bonus_id'] == 4){
                $bonus_id = 3028;
            }elseif($_POST['bonus_id'] == 5){
                $bonus_id = 3029;
            }else{
                $bonus_id = 3025;
            }
            
            $quan = $GLOBALS['db']->getRow("select count(*) as num from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if($quan['num']>5){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}elseif($_REQUEST['act'] == 'get_bonus_16080101'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 3053;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3054;
            }else{
                $bonus_id = 3053;
            }
            
            $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if(!empty($quan)){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}
elseif($_REQUEST['act'] == 'get_price_160801'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            $order_sn = cleanInput($_POST['order']);
            //该订单号是否抽过奖
            $getTimes = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE act_id = 160801  AND order_sn = '".$order_sn."'   AND user_id = '".$user_id."';");
            if(!$getTimes){
                //是否符合条件
                $canUse = $GLOBALS['db']->getOne("SELECT order_id FROM ecs_order_info WHERE order_sn = '".$order_sn."'  AND user_id = '".$user_id."' AND (shipping_status = 1 OR shipping_status = 2);");
                if($canUse){
                    
                    $rand_id = rand(1,5);
                    if($rand_id == 1){//红包
                        $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (3050, 0, '$user_id', 0, 0, 0);");
                        $price_name = '5元无限制红包';
                        $price_id = 0;
                    }elseif($rand_id ==  2){//积分
                        log_account_change($user_id, 0, 0, 0, 100, '2016周年庆抽奖积分');
                        $price_name = '100会员积分';
                        $price_id = 5;
                    }elseif($rand_id ==  3){//护理液
                        
                        $price_id = 4;
                        $str = '160801_'.$price_id;//拼接查询条件
                        $beLeft = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE order_sn = '".$str."';");//剩余数量
                        if($beLeft >0){
                            $GLOBALS['db']->query("UPDATE temp_active SET remarks=remarks-1 WHERE order_sn = '".$str."';");//减去此数量
                            
                            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('cart') .
                                "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`, `add_time`)
                                    VALUES ('".$user_id."', '".SESS_ID."', '4152', '004152', '[周年庆抽奖]护理液', '0.00', '0.00', '1', '', '1', 'unchange', '60801', '1', '', 'date(\"Y-m-d H:m:i\",time())')");
                            
                            $price_name = '护理液,已加入您的购物车^_^';
                        }else{
                            log_account_change($user_id, 0, 0, 0, 100, '2016周年庆抽奖积分');
                            $price_name = '100会员积分';
                            $price_id = 5;
                        }
 
                    }elseif($rand_id ==  4){//祛皱美眼笔
                    
                        $price_id = 1;
                        $str = '160801_'.$price_id;//拼接查询条件
                        $beLeft = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE order_sn = '".$str."';");//剩余数量
                        if($beLeft >0){
                            $GLOBALS['db']->query("UPDATE temp_active SET remarks=remarks-1 WHERE order_sn = '".$str."';");//减去此数量
                            
                            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('cart') .
                                "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`, `add_time`)
                                    VALUES ('".$user_id."', '".SESS_ID."', '5347', '005347', '[周年庆抽奖]祛皱美眼笔', '0.00', '0.00', '1', '', '1', 'unchange', '60801', '1', '', 'date(\"Y-m-d H:m:i\",time())')");
                            
                            $price_name = '祛皱美眼笔,已加入您的购物车^_^';
                        }else{
                            log_account_change($user_id, 0, 0, 0, 100, '2016周年庆抽奖积分');
                            $price_name = '100会员积分';
                            $price_id = 5;
                        }
                    }elseif($rand_id ==  5){//舒缓眼贴
                    
                        $price_name = '舒缓眼贴,已加入您的购物车^_^';
                        $price_id = 2;
                        $str = '160801_'.$price_id;//拼接查询条件
                        $beLeft = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE order_sn = '".$str."';");//剩余数量
                        if($beLeft >0){
                            $GLOBALS['db']->query("UPDATE temp_active SET remarks=remarks-1 WHERE order_sn = '".$str."';");//减去此数量
                            
                            $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table('cart') .
                                "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `goods_attr`, `is_real`, `extension_code`, `extension_id`, `is_cx`, `goods_attr_id`, `add_time`)
                                    VALUES ('".$user_id."', '".SESS_ID."', '5348', '005348', '[周年庆抽奖]舒缓眼贴', '0.00', '0.00', '1', '', '1', 'unchange', '60801', '1', '', 'date(\"Y-m-d H:m:i\",time())')");
                            
                            $price_name = '舒缓眼贴,已加入您的购物车^_^';
                        }else{
                            log_account_change($user_id, 0, 0, 0, 100, '2016周年庆抽奖积分');
                            $price_name = '100会员积分';
                            $price_id = 5;
                        }
                    }  
                    
                    $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`order_sn`,`remarks`)  VALUES (160801,'$user_id','".$order_sn."','".$price_name."');");//插入此订单号抽奖记录
                    $res = array('status'=>'1','id'=>$price_id,'name'=>$price_name);
                
                }else{
                    $res = array('status'=>'0');
                }
                
            }else{
                $res = array('status'=>'2');
            }  
                     
        }else{
            //未登录
            $res = array('status'=>'3');
        }
        
        echo json_encode($res);die;

}elseif($_REQUEST['act'] == 'get_bonus_160703'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 2969;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 2970;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 2971;
            }else{
                $bonus_id = 2972;
            }
            
            $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if(!empty($quan)){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}
elseif($_REQUEST['act'] == 'get_bonus_16080108'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 3045;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3046;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 3047;
            }elseif($_POST['bonus_id'] == 4){
                $bonus_id = 3048;
            }else{
                $bonus_id = 3045;
            }
            
            $quan = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if(!empty($quan)){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}
// 2016年菲士康领券活动
elseif($_REQUEST['act'] == 'get_bonus_160718'){
    $user_id   = $_SESSION['user_id'];
    $bonus_id  = intval($_POST['bonus_id']);
    $order_sn  = strtotime(date("Y-m-d")) - 28800;    // 获取当天时间作为当日唯一标识
    if($user_id>0){
        $quan_all = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = '".$bonus_id."' AND user_id = '".$user_id."'");  // 查询领取过的总数
        $quan = $GLOBALS['db']->getOne("SELECT count(act_id) FROM `temp_active` WHERE act_id = '".$bonus_id."' AND order_sn = '".$order_sn."' AND user_id = '".$user_id."'");  // 判断当天是否领取过

        if($quan_all >= 3){
            echo '4';die; // 领取过的总数大于3张不能再领
        }
        if($quan > 0){
            echo '3';//今日已经领取过
        }else{
            // 将红包发放至账户
            $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");
            // 记录红包领取时间
            $in_bonus   = $GLOBALS['db']->query("INSERT INTO `temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn`) VALUES (NULL , '".$bonus_id."', '".$user_id."', '".$order_sn."')");
            echo '1';//领取成功
        }

    }else{
        echo '2';//未登录
    }
    die;
}elseif($_REQUEST['act'] == 'get_status_160815'){
 
        $today_d = date('d');
        $today_H = date('H');
        
        //初始化当前产品的状态 1：未开始 0：正在进行 -1：已结束
        $status = 1; 
        
        if($today_d <15){
            $days           = 1;
            $gid            = 1;//展示第几个产品
            $link           = 'miaosha_buy_100.html';
        }elseif($today_d == 15){//第1天
            $days           = 1;
            $link           = 'miaosha_buy_100.html';//初始化第一个产品链接
            $gid            = 1;//初始化第一个产品顺序id(1-21)
            $nid            = 1;//初始化导航顺序id(1-3)
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 1;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 2;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_356.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 2;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_356.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 3;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_2405.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 3;
                $nid        = 3;
                $status     = 0;
                $link       = 'miaosha_buy_2405.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 4;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_2983.html';
            }
        }elseif($today_d == 16){//第2天
            $days           = 2;
            $link           = 'miaosha_buy_2983.html';//初始化第一个产品链接
            $gid            = 4;//初始化第一个产品顺序id(1-21)
            $nid            = 1;
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 4;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 5;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_1069.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 5;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_1069.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 6;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_5309.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 6;
                $nid        = 3;
                $status     = 0;
                $link       = 'miaosha_buy_5309.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 7;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_4139.html';
            }
        }elseif($today_d == 17){//第3天
            $days           = 3;
            $link           = 'miaosha_buy_4139.html';//初始化第一个产品链接
            $gid            = 7;//初始化第一个产品顺序id(1-21)
            $nid            = 1;
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 7;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 8;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_2412.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 8;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_2412.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 9;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_4805.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 9;
                $nid        = 3;
                $status     = 0;
                $link       = 'miaosha_buy_4805.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 10;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_955.html';
            }
        }elseif($today_d == 18){//第4天
            $days           = 4;
            $link           = 'miaosha_buy_955.html';//初始化第一个产品链接
            $gid            = 10;//初始化第一个产品顺序id(1-21)
            $nid            = 1;
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 10;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 11;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_2581.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 11;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_2581.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 12;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_5428.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 12;
                $nid        = 3;
                $status     = 0;
                $link       = 'miaosha_buy_5428.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 13;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_2748.html';
            }
        }elseif($today_d == 19){//第5天  
            $days           = 5;
            $link           = 'miaosha_buy_2748.html';//初始化第一个产品链接
            $gid            = 13;//初始化第一个产品顺序id(1-21)
            $nid            = 1;
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 13;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 14;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_945.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 14;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_945.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 15;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_1121.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 15;
                $nid        = 3;
                $status     = 0;
                $link       = 'miaosha_buy_1121.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 16;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_5276.html';
            }
        }elseif($today_d == 20){//第6天
            $days           = 6;
            $link           = 'miaosha_buy_5276.html';//初始化第一个产品链接
            $gid            = 16;//初始化第一个产品顺序id(1-21)
            $nid            = 1;
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 16;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 17;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_4633.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 17;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_4633.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 18;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_620.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 18;
                $nid        = 3;
                $status     = 0;
                $link       = 'miaosha_buy_620.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 19;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_117.html';
            }
        }elseif($today_d == 21){//第7天
            $days           = 7;
            $link           = 'miaosha_buy_117.html';//初始化第一个产品链接
            $gid            = 19;//初始化第一个产品顺序id(1-21)
            $nid            = 1;
            if($today_H<10){
                $status     = 1;
            }elseif($today_H>=10 && $today_H<11){//第1场开始
                $gid        = 19;
                $status     = 0;
            }elseif($today_H>=11 && $today_H<15){
                $gid        = 20;
                $nid        = 2;
                $status     = 1;
                $link       = 'miaosha_buy_4934.html';
            }elseif($today_H>=15 && $today_H<16){//第2场开始
                $gid        = 20;
                $nid        = 2;
                $status     = 0;
                $link       = 'miaosha_buy_4934.html';
            }elseif($today_H>=16 && $today_H<20){
                $gid        = 21;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_4657.html';
            }elseif($today_H>=20 && $today_H<21){//第3场开始
                $gid        = 21;
                $status     = 0;
                $link       = 'miaosha_buy_4657.html';
            }elseif($today_H>=21){//三场结束
                $gid        = 21;
                $nid        = 3;
                $status     = 1;
                $link       = 'miaosha_buy_4657.html';
            }
        }
        
        //print_r($days.'#'.$gid."#".$nid."#".$status);die;
        $timeImg        = "http://file.easeeyes.com/wap/images/20160815/m1_$days.jpg";
        $timeImg2       = "http://file.easeeyes.com/wap/images/20160815/m2_$nid.jpg";
        $bgImg          = "http://file.easeeyes.com/wap/images/20160815/m3_$gid.jpg";
        $res  = array(
            'timeImg'=>$timeImg,
            'timeImg2'=>$timeImg2,
            'bgImg'=>$bgImg,
            'status'=>$status,
            'link'=>$link
        );
        header('Content-type:text/json'); 
        echo json_encode($res);die;
        
}elseif($_REQUEST['act'] == 'get_price_160915'){
    
        $user_id = $_SESSION['user_id'];

        if($user_id>0){
            
            //该用户是否抽过奖
            $getTimes = $GLOBALS['db']->getOne("SELECT count(remarks) FROM temp_active WHERE act_id = 160915  AND user_id = '".$user_id."';");
  
            if($getTimes<3){
                    $rand_id = rand(1,4);
                    if($rand_id == 1 || $rand_id == 2){//红包5
                        $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."
                        (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (3135, 0, '$user_id', 0, 0, 0);");
                        $price_name = '5元红包,已打入您的会员账户中';
                        $price_id = 2;
                    }elseif($rand_id ==  3){//积分
                        log_account_change($user_id, 0, 0, 0, 100, '2016中秋节抽奖积分');
                        $price_name = '100会员积分,已打入您的会员账户中';
                        $price_id = 6;
                    }elseif($rand_id ==  4){//红包10
                        $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['ecs']->table('user_bonus')."
                        (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values (3136, 0, '$user_id', 0, 0, 0);");
                        $price_name = '10元红包,已打入您的会员账户中';
                        $price_id = 5;
                    }
                    $GLOBALS['db']->query("INSERT INTO temp_active (`act_id`,`user_id`,`remarks`)  VALUES (160915,'$user_id','".$price_name."');");//插入抽奖记录
                    $res = array('status'=>'1','id'=>$price_id,'name'=>$price_name);
                    
            }else{
                $res = array('status'=>'2','err'=>'每个用户限领取三次^_^');
            }  
                     
        }else{
            //未登录
            $res = array('status'=>'0','err'=>'请登录后再试^_^');
        }
        
        echo json_encode($res);die;

}
elseif($_REQUEST['act'] == 'get_bonus_160915'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 3141;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3142;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 3143;
            }
            
            $quan = $GLOBALS['db']->getOne("select count(*) as num from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if($quan>=5){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}
elseif($_REQUEST['act'] == 'get_bonus_16110101'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 3230;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3231;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 3230;
            }
            
            $quan = $GLOBALS['db']->getOne("select count(*) as num from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if($quan>=5){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}elseif($_REQUEST['act'] == 'get_bonus_161101'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
             if($_POST['bonus_id'] == 1){
                $bonus_id = 3217;
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3218;
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 3219;
            }elseif($_POST['bonus_id'] == 4){
                $bonus_id = 3220;
            }
            
            $quan = $GLOBALS['db']->getOne("select count(*) as num from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if($quan>=2){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}elseif($_REQUEST['act'] == 'get_bonus_161101_2'){
    
        $user_id = $_SESSION['user_id'];
        if($user_id>0){
            
            if($_POST['bonus_id'] == 1){
                $bonus_id = 3338;//-20
            }elseif($_POST['bonus_id'] == 2){
                $bonus_id = 3339;//-50
            }elseif($_POST['bonus_id'] == 3){
                $bonus_id = 3340;//-50
            }elseif($_POST['bonus_id'] == 4){
                $bonus_id = 3338;//-50
            }
            
            $quan = $GLOBALS['db']->getOne("select count(*) as num from ".$GLOBALS['ecs']->table('user_bonus')." 
            where user_id='$user_id' and bonus_type_id = ".$bonus_id);
            
            if($quan>=2){
                echo '3';//已经领取过
            }else{
                $into_bonus = $GLOBALS['db']->query("insert into ".$GLOBALS['ecs']->table('user_bonus')."
                     (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('".$bonus_id."', 0, '".$user_id."', 0, 0, 0)");    
                echo '1';//领取成功
            }
            
        }else{
            echo '2';//未登录
        }
        
        die;

}
else
{
	//TODO
}





//=============================================================================【函数】=============================================================================//
/* ----------------------------------------------------------------------------------------------------------------------
 * yi:判断这个订单号是否已经抽过奖了
 * ----------------------------------------------------------------------------------------------------------------------
 */
function had_prize($order_sn='')
{
	$rt = false;
	if(!empty($order_sn))
	{
		$res = $GLOBALS['db']->getOne("select rec_id from ecs_prize where extension<>'' and extension_id='".trim($order_sn)."' limit 1;");
		if(!empty($res))
		{
			$rt = true;
		}
	}
	return $rt;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:按概率进行抽奖，返回中奖等级。
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_prize()
{
	$marr = array(5, 20, 20, 20, 35);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=5):
			return 1;break;
		case($m_s<=25 && $m_s>5):
			return 2;break;
		case($m_s<=45 && $m_s>25):
			return 3;break;
		case($m_s<=65 && $m_s>45):
			return 4;break;
		case($m_s<=65 && $m_s>100):
			return 5;break;
		default:
			return 5;break;
	}
}

//同上：按概率进行抽奖，返回中奖等级。(20131028注册抽奖)
//先前的奖品：
/*一等奖：框架  0%  
二等级：美瞳(4名)  5%
三等奖：199-12优惠券  75% 
四等奖：50-5优惠券 20%
*/
//免单 0
//50-5优惠券 55%
//其他三种赠品各15%
function get_prize_20131028()
{
	/*$marr = array(55, 15, 15, 15);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=55):
			return 1;break;
		case($m_s<=70 && $m_s>55):
			return 2;break;
		case($m_s<=85 && $m_s>70):
			return 3;break;
		case($m_s<=100 && $m_s>85):
			return 4;break;
		default:
			return 1;break;
	}*/
	$marr = array(10, 10, 10, 20, 20, 30);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=10):
			return 1;break;
		case($m_s<=20 && $m_s>10):
			return 2;break;
		case($m_s<=30 && $m_s>20):
			return 3;break;
		case($m_s<=50 && $m_s>30):
			return 4;break;
		case($m_s<=70 && $m_s>50):
			return 5;break;
		case($m_s<=100 && $m_s>70):
			return 6;break;
		default:
			return 1;break;
	}
}
//问道抽奖
/*
1 问道威威虎抱枕 （4）		 5%   
	2 体恤水墨太极熊系列  （10） 15%
	3 道定制钱包  （6）			 10%
	游戏道具
	4 优能洗眼液（30）			35%
	5 优能高水分润眼液（30）		35%
*/
function get_prize_20140224()
{
	$marr = array(5, 15, 10, 35, 35);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=5):
			return 1;break;
		case($m_s<=20 && $m_s>5):
			return 2;break;
		case($m_s<=30 && $m_s>20):
			return 3;break;
		case($m_s<=55 && $m_s>30):
			return 4;break;
		case($m_s<=100 && $m_s>55):
			return 5;break;
		default:
			return 4;break;
	}
}
//财付通抽奖
/*
1 凯达伴侣盒（双联盒+镊子）  25  3064

	  2 优能洗眼液  25  1182
 
	  3 易视网积分  50  
*/
function get_prize_2014031101()
{
	$marr = array(25, 25, 50);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=25):
			return 1;break;
		case($m_s<=25 && $m_s>50):
			return 2;break;
		case($m_s<=75 && $m_s>100):
			return 3;break;
		default:
			return 1;break;
	}
}
//财付通抽奖
/*
1 5元 30
2 30元  25
3 45元  20 
4 谢谢参与  20
5 伴侣盒  5
*/
function get_prize_20140107()
{
	$marr = array(30, 25, 20, 20, 5);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=30):
			return 1;break;
		case($m_s<=55 && $m_s>30):
			return 2;break;
		case($m_s<=75 && $m_s>55):
			return 3;break;
		case($m_s<=95 && $m_s>75):
			return 4;break;
		case($m_s<=100 && $m_s>95):
			return 5;break;
		default:
			return 4;break;
	}
}
//双11联合抽奖
function get_prize_20131111()
{
	$marr = array(1, 449, 200, 200, 50, 50, 50);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=1):
			return 1;
			break;
		case($m_s<=450 && $m_s>1):
			return 2;
			break;
		case($m_s<=650 && $m_s>450):
			return 3;
			break;
		case($m_s<=850 && $m_s>650):
			return 4;
			break;
		case($m_s<=900 && $m_s>850):
			return 5;
			break;
		case($m_s<=950 && $m_s>900):
			return 6;
			break;
		case($m_s<=1000 && $m_s>950):
			return 7;
			break;
		default:
			return 2;
			break;
	}
}

//20131119感恩节抽奖
function get_prize_20131119()
{
$marr = array(1, 13, 20, 25, 10, 20, 10, 1);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=1):
			return 1;
			break;
		case($m_s<=14 && $m_s>1):
			return 2;
			break;
		case($m_s<=34 && $m_s>14):
			return 3;
			break;
		case($m_s<=59 && $m_s>34):
			return 4;
			break;
		case($m_s<=69 && $m_s>59):
			return 5;
			break;
		case($m_s<=89 && $m_s>69):
			return 6;
			break;
		case($m_s<=99 && $m_s>89):
			return 7;
			break;
		case($m_s<=100 && $m_s>99):
			return 8;
			break;
		default:
			return 3;
			break;
	}
}

//2014周年庆抽奖
/*1.谢谢参与25%
2.再来一次20%
3.赠送100积分40%
4.5元现金券15% 红包ID：1507*/
function get_prize_20140801()
{
	$marr = array(25, 20, 40, 15);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=25):
			return 1;
			break;
		case($m_s<=45 && $m_s>25):
			return 2;
			break;
		case($m_s<=85 && $m_s>45):
			return 3;
			break;
		case($m_s<=100 && $m_s>85):
			return 4;
			break;
		default:
			return 1;
			break;
	}
}

//2014双11预热抽奖
/*免单一次：0
500元现金券：0
200元现金券：0
5元现金券：35%	1
10元现金券：30%	2
50元现金券：15%	3
日本和风手绢：5% 4
3M口罩：5%		5
暖宝宝：5%		6
高级运动随身杯：5% 7 */
function get_prize_20141103()
{
	$marr = array(60, 20, 16, 1, 1, 1, 1);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=60):
			return 1;
			break;
		case($m_s<=80 && $m_s>60):
			return 2;
			break;
		case($m_s<=96 && $m_s>80):
			return 3;
			break;
		case($m_s<=97 && $m_s>96):
			return 4;
			break;
		case($m_s<=98 && $m_s>97):
			return 5;
			break;
		case($m_s<=99 && $m_s>98):
			return 6;
			break;
		case($m_s<=100 && $m_s>99):
			return 7;
			break;
		default:
			return 1;
			break;
	}
}
//2014感恩节订单号抽奖
/*免单一次：0
实瞳   0
么么哒35%			1
护理液20%			2
暖宝宝20%			3
电影票2张5%			4
3M口罩20%			5
 */
function get_prize_20141118()
{
	$marr = array(35,20,20,5,20);
	$msum = array_sum($marr);
	$m_s  = mt_rand(1, $msum);
	switch($m_s)
	{
		case($m_s<=35):
			return 1;//么么哒
			break;
		case($m_s<=55 && $m_s>35):
			return 2;//护理液
			break;
		case($m_s<=75 && $m_s>55):
			return 3;//暖宝宝
			break;
		case($m_s<=80 && $m_s>75):
			//return 4;//电影票
			return 1;//么么哒
			break;
		case($m_s<=100 && $m_s>80):
			return 5;//3M口罩
			break;
		default:
			return 1;
			break;
	}
}
/**
 * 2016女人节抽奖
实物：
普通面膜			200份
自拍杆			    140份
KAPO面膜            72份
丽塔芙睫毛套装		30份
旅行套装            24份
化妆棉              15份

INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '160301',  '0',  '20160301_1',  '200');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '160301',  '0',  '20160301_2',  '140');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '160301',  '0',  '20160301_3',  '72');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '160301',  '0',  '20160301_4',  '30');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '160301',  '0',  '20160301_5',  '24');
INSERT INTO  `ecshopn`.`temp_active` (`id` ,`act_id` ,`user_id` ,`order_sn` ,`remarks`) VALUES (NULL ,  '160301',  '0',  '20160301_6',  '15');
 *   SELECT SUM( remarks ) FROM temp_active WHERE `act_id` =160301
 * @return 奖品id

 */
function get_prize_160301()
{
    $total = $GLOBALS['db']->getOne("SELECT SUM( remarks ) FROM temp_active WHERE act_id =160301;");//剩余赠品总数量
    if($total > 0){
        $m = get_random_160301();
    }else{
        $m = 7;
    }

    $str = '20160301_'.$m;//拼接查询条件
    $beLeft = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE order_sn = '".$str."';");//剩余数量
    if($beLeft >0){
        $GLOBALS['db']->query("UPDATE temp_active SET remarks=remarks-1 WHERE order_sn = '".$str."';");//减去此数量
    }
    return $m;
}
function get_random_160301(){

    $marr = array(42,29,15,6,5,3);//设定6个商品概率

    $m_s  = mt_rand(1, 100);

    switch($m_s)
    {
        case($m_s<=42):
            $m = 1;//普通面膜
            break;
        case($m_s<=71 && $m_s>42):
            $m = 2;//自拍杆
            break;
        case($m_s<=86 && $m_s>71):
            $m = 3;//KAPO面膜
            break;
        case($m_s<=92 && $m_s>86):
            $m = 4;//睫毛套
            break;
        case($m_s<=97 && $m_s>92):
            $m = 5;//旅行套
            break;
        case($m_s<=100 && $m_s>97):
            $m = 6;//化妆棉
            break;
        default:
            $m = 1;
            break;
    }
    $str = '20160301_'.$m;//拼接查询条件
    $beLeft = $GLOBALS['db']->getOne("SELECT remarks FROM temp_active WHERE order_sn = '".$str."';");//剩余数量
    if($beLeft >0){
        return $m;
    }else{
        get_random_160301();
    }
}
?>