<?php
/* =======================================================================================================================
 * linktech CPS订单查询接口处理页面【2011/9/7】【Author:yijiangwen】
 * =======================================================================================================================
 * 查询当天linktech的订单数据
 * 测试格式：http://localhost/cps/linktech/daily_fix.php?yyyymmdd=20120506
 */
define('IN_ECS', true);
require('../../../includes/init.php');


/* ================================================================================================================================================
 * 基本的查询参数:date 日期。表示具体查询某一天的全部linktech订单详情。
 * linktech用模糊匹配。因为后期扩展会有linktech^TC linktech^qq_union_login等的其它情况出现。规定linktech项目的订单来源都是linktech开头。
 * 修改扩展功能的时候一定要系统修改。
 * ================================================================================================================================================
 */
$date = isset($_GET['yyyymmdd']) ? trim($_GET['yyyymmdd']):'';

if(!empty($date))
{
	$sql = "select * from ".$GLOBALS['ecs']->table('order_info')." where FROM_UNIXTIME(add_time,'%Y%m%d')='".$date."' and (referer like 'linktech%' or referer='qq_union_login') ";
	$res = $GLOBALS['db']->GetAll($sql);
	if(!empty($res))
	{
		foreach($res as $k => $v)
		{
			$order_id = intval($res[$k]['order_id']);

			$line  = "2\t"; //cps:2  cpa:1
			//下单时间
			$time  = date('His',$res[$k]['add_time']+3600*8)."\t";
			//cookie
			$cook  = trim($res[$k]['zipcode'])."\t";
			//订单号
			$order_sn = $res[$k]['order_sn']."\t";
			//用户id
			$user_id  = "user_".$res[$k]['user_id']."\t";
			//订单状态
			$or_status = get_order_status($order_id)."\n";	
			
			//CT项目订单（a_id固定使用A100126293）
			if(!empty($res[$k]['bonus_id']) && !empty($res[$k]['bonus']) && bonus_come($res[$k]['bonus_id'], 141))
			{
				$cook = "A100126293\t";
			}

			//遍历订单中每一个商品
			$goods = get_goods_by_order_id($order_id);
			if(!empty($goods))
			{
				foreach($goods as $k1 => $v1){
					$line  = "2\t";
					$line .= $time;
					$line .= $cook;
					$line .= $order_sn;

					//商品编号
					$line .= "goods".$goods[$k1]['goods_id']."\t";
					$line .= $user_id;

					//商品数量
					$line .= $goods[$k1]['goods_number']."\t";
					//商品价格
					$line .= $goods[$k1]['goods_price']."\t";
					//商品分类
					$c_cd = '';
					if($goods[$k1]['extension_code']=='package_buy')
					{			
						if($goods[$k1]['goods_sn']==1 && $goods[$k1]['market_price']>0)
						{
							$c_cd = goods_cat_cd($goods[$k1]['goods_id'], true);	
						}
						else
						{			
							continue;
						}
					}
					elseif ($goods[$k1]['extension_code']=='tuan_buy' || $goods[$k1]['extension_code']=='miaosha_buy')
					{
						$lt_c_cd .= "||".goods_cat_cd2($goods_id, true);
					}
					else
					{
						$c_cd = goods_cat_cd($goods[$k1]['goods_id']);	
					}
					$line .= $c_cd."\t";				
					$line .= $or_status;
					echo $line;
				}
			}
		}
	}
	else
	{
		echo "==============================未查询到".$date."从Linktech过来的订单==============================";
	}
}
else
{
	echo "参数错误，请检查。";
}

//*==============================================================【函数】==============================================================*//

//获得订单下面商品数组
function get_goods_by_order_id($order_id=0){
	if($order_id ==0){
		return false;
	}
	$sql = "select * from ".$GLOBALS['ecs']->table('order_goods')." where order_id=".$order_id.";";
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}


//获得订单状态 返回给linktech
function get_order_status($order_id=0){
	if($order_id ==0){
		return false;
	}
	$sql = "select order_status,pay_status from ".$GLOBALS['ecs']->table('order_info')." where order_id=".$order_id.";";
	$res = $GLOBALS['db']->getRow($sql);
	
	$ret = 100;
	if($res['order_status'] ==1 && $res['pay_status'] ==2){
		$ret = 200;
	}elseif($res['order_status'] > 1){
		$ret = 300;
	}
	return intval($ret);
}

/* ----------------------------------------------------------------------------------------------------------------------
 *  函数 yi:linktech返利编码函数
 * ----------------------------------------------------------------------------------------------------------------------
	linktech返利规则
	/----------------------------------------------------------------------/
        类别           佣金比例      c_cd编号
	/----------------------------------------------------------------------/
	普通隐形眼镜         8%              A   

	彩色隐形眼镜         15%             B    

	护理液润眼液         7%              C

	护理工具             28%			  D   //QQ彩贝中护理工具是35%

	强生博士伦           2.5%			  E

	组合商品,特价抢购   1.5%			  F

	me&city 班尼路        11%			  H

	框架眼镜单品≤250元   21%             I

	太阳眼镜,框架单品>250元   11%        J

	后来新增类别(待定)  0%               G	
	/----------------------------------------------------------------------/
 */
function goods_cat_cd($goods_id=0, $package = false)
{
	$c_cd = '';
	if(empty($goods_id)){return false;}

	$cat_id = $GLOBALS['db']->getOne('select cat_id from '.$GLOBALS['ecs']->table('goods').' where goods_id='.$goods_id.';');	

	if($goods_id == tejia_id()||$package)
	{
		$c_cd = 'F';
	}
	else
	{	
		//单独列出的分类：cat_id=4 5 29 134 65（博士伦，博士伦护理液，博士伦蕾丝，强生，强生美瞳）
		if($cat_id==4||$cat_id==5||$cat_id==29||$cat_id==65||$cat_id==134||$cat_id==154)
		{
			$c_cd = 'E';
		}
		else if($cat_id==175 || $cat_id==177)
		{
			$c_cd = 'H';
		}
		else
		{
			//商品分类的父类
			$p_id = $GLOBALS['db']->getOne('select parent_id from '.$GLOBALS['ecs']->table('category').' where cat_id='.$cat_id.' limit 1;');
			if(0 == $p_id)
			{
				$p_id = $cat_id;
			}
			switch($p_id)
			{
				case 1: 
					$c_cd = 'A'; //透明片
					break;
				case 6: 
					$c_cd = 'B'; //彩色片
					break;
				case 64: 
					$c_cd = 'C'; //护理液
					break;
				case 76: 
					$c_cd = 'D'; //护理工具
					break;
				case 159: 
					$shop_price = $GLOBALS['db']->getOne("select shop_price from ecs_goods where goods_id=".$goods_id." limit 1;");
					$c_cd = ($shop_price > 250)? 'J': 'I';//框架眼镜
					break;
				case 190: 
					$c_cd = 'J'; //太阳眼镜
					break;
				default:
					$c_cd = 'G'; //后来新增类别(待定)
					break;
			}
		}
	}
	return $c_cd;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:当前特价商品(限时抢购)的goods_id
 * ----------------------------------------------------------------------------------------------------------------------
 */
function tejia_id()
{
	$sql = 'SELECT goods_id FROM '.$GLOBALS['ecs']->table('goods').
		   ' WHERE `promote_price`>0 and `promote_start_date`<UNIX_TIMESTAMP(NOW()) and `promote_end_date`> UNIX_TIMESTAMP(NOW()) limit 1;';
	return $GLOBALS['db']->getOne($sql);
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断o_id的线下红包是否来自f_id类型的红包
 * ----------------------------------------------------------------------------------------------------------------------
 * 是：true 否：fasle。 $f_id：要找的红包类型的id。
 */
function bonus_come($o_id=0, $f_id=0)
{
	$ret = false;
	if(!empty($o_id) && !empty($f_id))
	{
		$t_bonus = $GLOBALS['db']->getOne("select bonus_type_id from ecs_user_bonus where bonus_sn='$o_id' and order_id>0 limit 1;");
		if($t_bonus==$f_id)
		{
			$ret = true;
		}
	}
	return $ret;
}
?>