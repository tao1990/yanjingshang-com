<?php
/*============================================51fanli.com 订单查询接口处理页面2011-5-17============================================*/
define('IN_ECS', true);
require('../includes/init.php');

/*===================================获得基本的查询参数===================================*/
$date       = isset($_GET['date']) ? $_GET['date']:'';
$begin_date = isset($_GET['begin_date']) ? $_GET['begin_date']:'';
$end_date   = isset($_GET['end_date']) ? $_GET['end_date']:'';
$unionid    = isset($_GET['unionid']) ? $_GET['unionid']:'';//unionid = 51fanli 返利网网站标识

//查询结果 文本输出

if(!empty($date) && $unionid=='51fanli'){
/*===================================查询一天的订单接口===================================*/
	
	/*----------------------查询这一天当中的订单------------------------*/		
	$order_day = array();
	$sql = 'select * from '.$GLOBALS['ecs']->table('order_info').' where FROM_UNIXTIME(add_time,"%Y-%m-%d") = "'.$date.'" and referer="51fanli";';
	$res = $GLOBALS['db']->query($sql);
	
	while($row = $GLOBALS['db']->fetchRow($res)){
		$order_day[] = $row;
	}
	
	/*=============遍历一天的返利网订单，输出订单数据==============================*/
	foreach($order_day as $key => $val){
		//下订单时间： add_time
		$order_time = $order_day[$key]['add_time']+3600*8;		 
	
		//订单号	
		$fanli_oid = $order_day[$key]['order_id'];
		
		//订单序列号
		$fanli_osn = $order_day[$key]['order_sn'];

		//该订单的 返利网用户id 订单信息中zipcode字段专门存放传递的u_id值。
		$fanli_uid = $order_day[$key]['zipcode'];
		
		//联合登录的用户名:$username。传递过来的用户名。非联合登录则为空。
		if($order_day[$key]['user_id']==0){
			$fanli_uname = '';
		}else{
			$sqlu = 'select user_name from '.$GLOBALS['ecs']->table('users').' where user_id='.$order_day[$key]['user_id'].';';
			$fanli_uname = $GLOBALS['db']->GetOne($sqlu); 
		}
		if(strstr($fanli_uname,"@51fanli")== false){
			$fanli_uname = '';
		}
		
		//商城代号 m_id=easeeyes 固定	
	
		//查询订单商品表中数据---根据订单号获得该订单的商品---------------------------------
		$order_goods = array();
		$sql2 = 'select * from '.$GLOBALS['ecs']->table('order_goods').' where order_id = '.$fanli_oid.';';
		$res2 = $GLOBALS['db']->query($sql2);
		
		while($row = $GLOBALS['db']->fetchRow($res2)){
			$order_goods[] = $row;
		}
		/*=============遍历一天的返利网多个商品订单中商品==============================*/
		foreach ($order_goods as $k => $v){
			//商品编号
			$goods_sn = 'goods'.$order_goods[$k]['goods_id'];
			
			//商品id
			$goods_id = $order_goods[$k]['goods_id'];		
			
			//商品数量	
			$goods_num = $order_goods[$k]['goods_number'];
			
			//商品单价
			$goods_price = $order_goods[$k]['goods_price'];
			
			//c_cd佣金编号
			$c_cd = goods_cat_cd($goods_id);			
			
			//comm佣金 没有则为0
			//$comm = '0';
		
			/*----------------------指定格式输出这一天当中的订单信息------------------------*/		
			$tr = date('Y-m-d H:i:s',$order_time).'|'.$fanli_osn.'|'.$fanli_uid.'|'.$goods_sn.'|'.$c_cd.'|'.$goods_num.'|'.$goods_price.'|0|easeeyes|'.$fanli_uname.'|_|';
			print($tr);	
		}
	}
}elseif(!empty($begin_date) && !empty($end_date) && $unionid=='51fanli'){

/*===================================查询一段时间的订单===================================*/
	$order_day = array();
	$sql = 'select * from '.$GLOBALS['ecs']->table('order_info').' where FROM_UNIXTIME(add_time,"%Y-%m-%d") between "'.$begin_date.'" and "'.$end_date.'" and referer="51fanli";';
	$res = $GLOBALS['db']->query($sql);
	
	while($row = $GLOBALS['db']->fetchRow($res)){
		$order_day[] = $row;
	}
	
	/*=============遍历一段时间的返利网订单，输出订单数据==============================*/
	foreach($order_day as $key => $val){
		//下订单时间： add_time
		$order_time = $order_day[$key]['add_time']+3600*8;		 
	
		//订单号	
		$fanli_oid = $order_day[$key]['order_id'];
		
		//订单序列号
		$fanli_osn = $order_day[$key]['order_sn'];

		//该订单的 返利网用户id
		$fanli_uid = $order_day[$key]['zipcode'];
		
		//联合登录的用户名:$username。传递过来的用户名。非联合登录则为空。
		if($order_day[$key]['user_id']==0){
			$fanli_uname = '';
		}else{
			$sqlu = 'select user_name from '.$GLOBALS['ecs']->table('users').' where user_id='.$order_day[$key]['user_id'].';';
			$fanli_uname = $GLOBALS['db']->GetOne($sqlu); 
		}
		if(strstr($fanli_uname,"@51fanli")== false){
			$fanli_uname = '';
		}
		
		//商城代号 m_id=easeeyes 固定	
	
		//查询订单商品表中数据---根据订单号获得该订单的商品---------------------------------
		$order_goods = array();
		$sql2 = 'select * from '.$GLOBALS['ecs']->table('order_goods').' where order_id = '.$fanli_oid.';';
		$res2 = $GLOBALS['db']->query($sql2);
		
		while($row = $GLOBALS['db']->fetchRow($res2)){
			$order_goods[] = $row;
		}
		/*=============遍历一天的返利网多个商品订单中商品==============================*/
		foreach ($order_goods as $k => $v){
			//商品编号
			$goods_sn = 'goods'.$order_goods[$k]['goods_id'];
			
			//商品id
			$goods_id = $order_goods[$k]['goods_id'];		
			
			//商品数量	
			$goods_num = $order_goods[$k]['goods_number'];
			
			//商品单价
			$goods_price = $order_goods[$k]['goods_price'];
			
			//c_cd佣金编号			
			if($order_goods[$k]['extension_code']=='package_buy'){
				//礼包商品的情况
				if($order_goods[$k]['goods_sn']==1 && $order_goods[$k]['market_price']>0){
					$c_cd = goods_cat_cd($goods_id,true);
				}else{
					//是礼包的价格上返利，不是商品的价格上返利。
					continue;
				}
			}else{
				$c_cd = goods_cat_cd($goods_id);
			}	
			
			//comm佣金 没有则为0
			//$comm = '0';
		
			/*----------------------指定格式输出这一天当中的订单信息------------------------*/
			//格式：otime|o_cd|u_id|p_cd|c_cd|it_cnt|price|comm|m_id|_|			
			$tr = date('Y-m-d H:i:s',$order_time).'|'.$fanli_osn.'|'.$fanli_uid.'|'.$goods_sn.'|'.$c_cd.'|'.$goods_num.'|'.$goods_price.'|0|easeeyes|'.$fanli_uname.'|_|';
			print($tr);	
		}
	}
	
}else{
	//查询参数异常的情况。不予处理
	print('非法入口!返回商城首页');
	header('Location: /');
}

/*====================================================================函数===============================================================================*/
//根据商品id 判断商品属于哪个分类，给予相应的返利

function goods_cat_cd($goods_id,$package = false){

//51fanli返利规则
/*----------------------------------------------------------------------*/
//    类别              佣金比例                   c_cd编号
/*----------------------------------------------------------------------*/
//普通隐形眼镜         8%              A   

//彩色隐形眼镜         15%             B    

//护理液润眼液         3%              C

//护理工具                  30%             D   

//强生博士伦             1.5%            E

//组合商品 特价抢购    1%           F

//如果后来新增类别     0%           G
/*----------------------------------------------------------------------*/

	$c_cd = '';
	//1.找出商品的分类
	$sql = 'select cat_id from '.$GLOBALS['ecs']->table('goods').' where goods_id='.$goods_id.';';
	$cat_id = $GLOBALS['db']->getOne($sql);
	
	//特价抢购
	if($goods_id == tejia_id()||$package){
		$c_cd = 'F';
	}else{
		
		//博士伦和强生 4 5 29 134 
		if($cat_id==4||$cat_id==5||$cat_id==29||$cat_id==134){
			$c_cd = 'E';
		}else{
			//找到父分类
			$sqls = 'select parent_id from '.$GLOBALS['ecs']->table('category').' where cat_id='.$cat_id.';';
			$p_id = $GLOBALS['db']->getOne($sqls);
			
			if( $p_id == 1 ){
				//透明片
				$c_cd = 'A';
			}elseif( $p_id == 6){
				//彩色片
				$c_cd = 'B';
			}elseif( $p_id == 64){
				//护理液
				$c_cd = 'C';
			}elseif( $p_id == 76){
				//护理液
				$c_cd = 'D';
			}else{
				//其它例外情况
				$c_cd = 'G';
			}
		}
	}
	return $c_cd;
}
//特价商品的id
function tejia_id(){
	$sql = 'SELECT goods_id FROM '.$GLOBALS['ecs']->table('goods').' WHERE `promote_price`>0 and `promote_start_date`<UNIX_TIMESTAMP(NOW()) and
`promote_end_date`> UNIX_TIMESTAMP(NOW());';
	$tejia = $GLOBALS['db']->getOne($sql);
	return $tejia;
}

?>