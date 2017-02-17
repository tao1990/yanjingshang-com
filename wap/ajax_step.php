<?php
/* ======================================================================================================
 * 商城前端 ajax功能设置【2012/4/27】【Author:yijiangwen】【同步TIME:2012/8/31】
 * ======================================================================================================
 * 采用最佳的ajax模式（其它的地方没有多余的数据输出，保证了准确性，单一设计模式）
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
date_default_timezone_set('PRC'); 

if($_REQUEST['act'] == 'send_bonus')
{
	//==============================================================【ajax发放红包（会员领取红包）】===============================================================//

	$yi_user_id    = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$bonus_type_id = isset($_REQUEST['bonus_type_id'])? intval($_REQUEST['bonus_type_id']): 0;

	$info          = array('info_code'=>0, 'info_msg'=>'', 'bonus_type_id'=>0); //领取结果 0表示失败, 1表示领取成功。

	if(!empty($bonus_type_id) && !empty($yi_user_id))
	{
		$info['bonus_type_id'] = intval($bonus_type_id);
	
		$sql2  = "select * from ".$GLOBALS['ecs']->table('bonus_type')." where type_id=".$bonus_type_id;
		$bonus = $GLOBALS['db']->getRow($sql2);
		
		//时间过期不能领用
		$time = $_SERVER['REQUEST_TIME'];
		if($bonus['send_start_date']<$time && $time<$bonus['send_end_date'])
		{
			//一个会员一种类型的券只能够领取1张优惠券。//用户领了没有用的优惠券
			$sql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$yi_user_id' and bonus_type_id='$bonus_type_id' and order_id=0 and used_time=0;";
			$quan = $GLOBALS['db']->getAll($sql3);
			if(count($quan)>=1)
			{
				$info['info_code'] = 0;
				$info['info_msg']  = '您好，该类型的红包您已经领用过了！';				
			}
			else
			{				
				//领取红包
				$sql = "insert into ".$GLOBALS['ecs']->table('user_bonus').
					   "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('$bonus_type_id', 0, '$yi_user_id', 0, 0, 0);";
				$res = mysql_query($sql);
				if($res !== false)
				{
					$info['info_code'] = 1;
					$info['info_msg']  = '恭喜您，红包领取成功！请到会员中心查看。';
				}
				else
				{
					$info['info_code'] = 0;
					$info['info_msg']  = '很抱歉，由于系统原因，红包领取失败，请稍后联系客服！';
				}
			}
		}
		else
		{ 
			//领用时间过期了
			$info['info_code'] = 0;
			$info['info_msg']  = '您好，该红包领取活动已经结束！';
		}
	}
	else
	{
		//领用时间过期了
		$info['info_code'] = 0;
		$info['info_msg']  = '很抱歉，红包领取失败！';
	}

	$str = json_encode($info);
	echo $str;
}
elseif($_REQUEST['act'] == 'send_bonus_no_limit')
{
	//==============================================================【ajax发放红包（会员领取红包）】===============================================================//

	$yi_user_id    = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$bonus_type_id = isset($_REQUEST['bonus_type_id'])? intval($_REQUEST['bonus_type_id']): 0;

	$info          = array('info_code'=>0, 'info_msg'=>'', 'bonus_type_id'=>0); //领取结果 0表示失败, 1表示领取成功。

	if(!empty($bonus_type_id) && !empty($yi_user_id))
	{
		$info['bonus_type_id'] = intval($bonus_type_id);
	
		$sql2  = "select * from ".$GLOBALS['ecs']->table('bonus_type')." where type_id=".$bonus_type_id;
		$bonus = $GLOBALS['db']->getRow($sql2);
		
		//时间过期不能领用
		$time = $_SERVER['REQUEST_TIME'];
		if($bonus['send_start_date']<$time && $time<$bonus['send_end_date'])
		{
			//一个会员一种类型的券只能够领取1张优惠券。//用户领了没有用的优惠券
			//$sql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$yi_user_id' and bonus_type_id='$bonus_type_id' and order_id=0 and used_time=0;";
			//$quan = $GLOBALS['db']->getAll($sql3);
			if(1)
			{				
				//领取红包
				$sql = "insert into ".$GLOBALS['ecs']->table('user_bonus').
					   "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('$bonus_type_id', 0, '$yi_user_id', 0, 0, 0);";
				$res = mysql_query($sql);
				if($res !== false)
				{
					$info['info_code'] = 1;
					$info['info_msg']  = '恭喜您，红包领取成功！请到会员中心查看。';
				}
				else
				{
					$info['info_code'] = 0;
					$info['info_msg']  = '很抱歉，由于系统原因，红包领取失败，请稍后联系客服！';
				}
			}
		}
		else
		{ 
			//领用时间过期了
			$info['info_code'] = 0;
			$info['info_msg']  = '您好，该红包领取活动已经结束！';
		}
	}
	else
	{
		//领用时间过期了
		$info['info_code'] = 0;
		$info['info_msg']  = '很抱歉，红包领取失败！';
	}

	$str = json_encode($info);
	echo $str;
}
//==============================================================【ajax获取活动商品的基本信息】===============================================================//
elseif($_REQUEST['act'] == 'get_at_goods_info')
{	
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	$tmp = $GLOBALS['db']->getRow("select goods_id, goods_name, shop_price*2 as goods_price, goods_img from ecs_goods where goods_id=".$goods_id." limit 1");	
	$str = json_encode($tmp);//数组数据传递给js端	
	echo $str;
}
//==============================================================【随心配商品加入购物车功能】===============================================================//
elseif($_REQUEST['act'] == 'add_at_to_cart')
{
	$goods_id1 = isset($_REQUEST['goods_id1'])? intval($_REQUEST['goods_id1']): 0;
	$goods_id2 = isset($_REQUEST['goods_id2'])? intval($_REQUEST['goods_id2']): 0;
	$ds1       = isset($_REQUEST['ds1'])? trim($_REQUEST['ds1']): '';
	$ds2       = isset($_REQUEST['ds2'])? trim($_REQUEST['ds2']): '';

	//随心配商品加入购物车
	$at_goods[0] = array('goods_id' => $goods_id1, 'goods_ds' => $ds1);
	$at_goods[1] = array('goods_id' => $goods_id2, 'goods_ds' => $ds2);

	$res = add_at_goods_to_cart($at_goods);	
	echo $res ? 'ok': 'fail';
}
//==============================================================【一个id只能购买1件功能】===============================================================//
elseif($_REQUEST['act'] == 'user_if_buy')
{
	//yi:判断用户今天是否已经特价抢购过这个商品

	$user_id  = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;

	//购物车商品
	$sql = "select SUM(goods_number) from ecs_cart where user_id=".$user_id." AND goods_id=".$goods_id;
	$cart_number = $GLOBALS['db']->GetOne($sql);

	//订单中商品
	$dtime = mktime(11,0,0, date("m"), date("d"), date("Y"));
	$sql = "select * from ecs_order_info where user_id=".$user_id." AND order_status<>2 AND add_time>".$dtime;
	$u_order = $GLOBALS['db']->GetAll($sql);
	
	$goods_number = 0;
	if(!empty($u_order))
	{
		foreach($u_order as $k => $v)
		{
			$sql = "select SUM(goods_number) from ecs_order_goods where goods_id=".$goods_id." and is_cx=1 and order_id=".$v['order_id'];
			$g_num = $GLOBALS['db']->GetOne($sql);
			$goods_number += $g_num;
		}
	}
	$res_number = $goods_number + $cart_number;
	echo ($res_number>0)? 'yes': 'no';//yes:已经购买，no:未购买
}
//==============================================================【组合商品加入购物车】===============================================================//
elseif($_REQUEST['act'] == 'add_group_buy')
{


	$buy_id   = isset($_REQUEST['buy_id'])? trim($_REQUEST['buy_id']): '';
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;//主商品id	
	$pei_ds   = isset($_REQUEST['pei_ds'])? trim($_REQUEST['pei_ds']): '';     //配件商品度数

	$g_zs  = isset($_REQUEST['g_zselect'])? trim($_REQUEST['g_zselect']):"";
	$g_ys  = isset($_REQUEST['g_yselect'])? trim($_REQUEST['g_yselect']):"";
	
	//组合购买的配件商品不为空，配件商品都加入购物车
	if(!empty($buy_id))
	{
		$buys = explode(",", $buy_id);
		$buys = array_unique($buys);		
		$pei_arr = explode('|_|', $pei_ds);
		foreach($pei_arr as $a => $b)
		{
			$tmp_ds       = explode('_', $b);	
			$pei_goods_ds = trim($tmp_ds[0]);  //配件商品度数 		
			$pei_id       = intval($tmp_ds[1]);//配件商品id			

			//全部配件商品加入购物车
			if(!empty($pei_id) && $pei_id != $goods_id)
			{
				//-----------------------------计算商品配件的价格-----------------------------//		
				$sql = "select a.goods_price, g.shop_price, g.group_fav from ecs_group_goods as a left join ecs_goods as g on a.goods_id=g.goods_id where a.parent_id=".$goods_id." and a.goods_id=".$pei_id." limit 1;";
				$pris = $GLOBALS['db']->GetRow($sql);				
				$pei_price = 0.0;				
				
				if($pris['shop_price']>0)
				{
					if($pris['goods_price'] < $pris['shop_price'])
					{
						$pei_price = floatval($pris['goods_price']);//商品本身的组合购买价格（优先）
					}
					else
					{
						$group_fav = abs($pris['group_fav']);
						if($group_fav <= $pris['shop_price'])
						{
							$pei_price = floatval($pris['shop_price']-$group_fav);
						}
					}
				}
				else
				{
					//TODO
				}
				$pei_price = (!empty($pei_price))? floatval($pei_price): $pris['shop_price'];	
				//-----------------------------计算商品配件的价格-----------------------------//	

				if(!empty($pei_goods_ds))
				{
					//配件有度数的情况	(rec_type:购物车商品类型，这个填写5。)
					addto_cart_user_define($pei_id, 1, $pei_price, array(), 0, 5, 'group_buy', $goods_id, $pei_goods_ds, '1', '', '');
				}
				else
				{
					addto_cart_user_define($pei_id, 1, $pei_price, array(), 0, 5, 'group_buy', $goods_id, '', '', '', '');					
				}
			}
		}		
	}
	
	//主体商品加入购物车
	if($goods_id>0)
	{
		if($g_zs != 'undefined' && $g_ys != 'undefined')
		{
			addto_cart($goods_id, 2, array(), 0, $g_zs, 1, $g_ys, 1);
		}
		else
		{
			addto_cart($goods_id, 1, array(), 0, '', '', '', '');//无度数商品加入购物车
		}
	}	
	echo 'ok';
}

//==============================================================【框架眼镜和镜片加入购物车】===============================================================//
elseif($_REQUEST['act'] == 'kuangjia_buy')
{
	//必须选项
	$goods_id     = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;        //镜架
	$glasses_type = isset($_REQUEST['glasses_type'])? intval($_REQUEST['glasses_type']): 0;//镜片
	$kj_tongju    = isset($_REQUEST['kj_tongju'])? trim($_REQUEST['kj_tongju']): '';       //瞳距
	$goods_number = isset($_REQUEST['goods_number'])? intval($_REQUEST['goods_number']): 1;//数量
	$zselect      = isset($_REQUEST['zselect'])? trim($_REQUEST['zselect']): "";
	$yselect      = isset($_REQUEST['yselect'])? trim($_REQUEST['yselect']): "";
	
	//周年庆临时：防止用户直接在浏览器直接输入网址加入购物车
	if ($goods_id == 2664) exit;

	$zsg    = isset($_REQUEST['zsg'])? trim($_REQUEST['zsg']):"";
	$ysg    = isset($_REQUEST['ysg'])? trim($_REQUEST['ysg']):"";
	$zzhou  = isset($_REQUEST['zzhou'])? trim($_REQUEST['zzhou']):"";
	$yzhou  = isset($_REQUEST['yzhou'])? trim($_REQUEST['yzhou']):"";
	$is_sg  = ((!empty($zsg) && !empty($zzhou)) || (!empty($ysg) && !empty($yzhou)) )? 1: 0;

	//-------------------------------------------------------------------------------------------//
	//解决远视没有'+'的情况
	if(!empty($zselect) && $zselect!='平光' && $zselect>0.00){
		$zselect = '+'.trim($zselect);
	}
	if(!empty($yselect) && $yselect!='平光' && $yselect>0.00){
		$yselect = '+'.trim($yselect);
	}
	//散光片的散光度数
	if(!empty($zsg) && $zsg>0.00){
		$zsg = '+'.trim($zsg);
	}
	if(!empty($ysg) && $ysg>0.00){
		$ysg = '+'.trim($ysg);
	}
	//-------------------------------------------------------------------------------------------//

	//check data
	if(!empty($goods_id) && !empty($glasses_type) && !empty($kj_tongju))
	{
		//-----------------------------------------//
		//镜片价格
		$jp_price = 0; $jp_id = 1393;
		switch($glasses_type)
		{
			case 1:
				$jp_price = 0; $jp_id = 1393; break;
			case 2:
				$jp_price = 50; $jp_id = 1394; break;
			case 3:
				$jp_price = 100; $jp_id = 1395; break;
			case 4:
				$jp_price = 320; $jp_id = 1396; break;
			case 5:
				$jp_price = 560; $jp_id = 1397; break;
			case 6:
				$jp_price = 780; $jp_id = 1398; break;
			default:
				$jp_price = 0; $jp_id = 1393; break;
		}

		//-----------------------------------------//
		//镜架价格
		$sql = "select IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.promote_start_date, g.promote_end_date ".
			" FROM " .$GLOBALS['ecs']->table('goods'). " AS g ".
			" LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
			" ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
			" WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 AND g.is_on_sale=1 AND g.is_alone_sale=1 ";
		$rs = $GLOBALS['db']->GetRow($sql);
		$promote_price = 0;
		$time = time();
		if ($rs['promote_price'] > 0 && $time > $rs['promote_start_date'] && $time < $rs['promote_end_date']) {
            $promote_price = $rs['promote_price'];
        } else {
            $promote_price = 0;
        }
		$goods_price = ($promote_price > 0) ? $promote_price : $rs['shop_price'];

		//镜架加入购物车
		$cart_res1 = addto_cart_kj($goods_id, $goods_number, $goods_price, 0, array(), 0, 0, '', 0, '', '', '', '', 1, '');	


		//镜片加入购物车
		if($is_sg)
		{
			$sgds = '';
			if(isset($zsg) && isset($zzhou) && !empty($zzhou) && !empty($zsg)){
				$sgds .= '左眼散光:'.$zsg.'轴位:'.$zzhou;
			}
			if(isset($ysg) && isset($yzhou) && !empty($yzhou) && !empty($ysg)){
				$sgds .= '右眼散光:'.$ysg.'轴位:'.$yzhou;
			}
			$goods_attr = $sgds;

			$cart_res2 = addto_cart_kj($jp_id, $goods_number, $jp_price, $goods_id, array(), 0, 0, $goods_attr, 0, $zselect, 1, $yselect, 1, 2, $kj_tongju);//框架有散光。
		}
		else
		{
			$cart_res2 = addto_cart_kj($jp_id, $goods_number, $jp_price, $goods_id, array(), 0, 0, '', 0, $zselect, 1, $yselect, 1, 1, $kj_tongju);	
		}

		//购物车中商品数量
		$sql = "select sum(goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' ;";
		$cart_num = $GLOBALS['db']->GetOne($sql);

		$zres  = ($cart_res1 && $cart_res2)? 'ok_'.$cart_num: 'fail_'.$cart_num;
		echo $zres;
	}
	else
	{
		echo 'fail';
	}
}
//==============================================================【会员登录2012/9/6】=======================================================//
elseif($_REQUEST['act'] == 'ajax_user_login')
{
	include_once('includes/cls_json.php');
    $json = new JSON;
	$result = array('error'=>0, 'ucdata'=>'', 'content'=>'', 'html'=>'');

	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

	//=============================yi:下次自动登录【功能设置】=============================||
	if(!empty($username) && !empty($password))
	{		
		$auto_login = isset($_POST['autologin'])? $_POST['autologin']: false;//自动登录
		if($auto_login)
		{			
			setcookie("uname",$username,time()+24*3600*30);//用户名,密码写入cookie【保存30天】
			setcookie("upass",$password,time()+24*3600*30);
		}
	}
	//=============================yi:下次自动登录【end】==================================||
	
	/*
	//验证码【已关闭】
    $captcha = intval($_CFG['captcha']);
    if(($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if(empty($_POST['captcha'])){
            show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'], 'user.php', 'error');
        }
        //检查验证码
        include_once('includes/cls_captcha.php');
        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if(!$validator->check_word($_POST['captcha'])){
            show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'], 'user.php', 'error');
        }
    }*/

    if($user->login($username, $password))
    {
        update_user_info();  //更新信息
        recalculate_price(); //更新购物车
        $smarty->assign('user_info', get_user_info());
		
		$result['error']   = 1;	
        $result['content'] = $smarty->fetch('library/member_info.lbi');
    }
    else
    {
        $_SESSION['login_fail']++;
        if($_SESSION['login_fail'] > 10)
        {
            //登录超过10次
        }
        $result['error']   = 0;
        $result['content'] = '很抱歉，您此次登录失败！';
    }
    die($json->encode($result));
}
//================================================【获得具体的验光单数据】================================================//
elseif($_REQUEST['act'] == 'get_user_receipt')
{
	include_once('includes/cls_json.php');
    $json = new JSON;

	$rec_id = isset($_POST['rec_id'])? intval($_POST['rec_id']): 0;
	
	$sql = "select * from ".$GLOBALS['ecs']->table('user_ds')." where rec_id=".$rec_id;
	$receipt = $GLOBALS['db']->GetRow($sql);

	die($json->encode($receipt));
}
//================================================【获得用户的全部验光单】================================================//
elseif($_REQUEST['act'] == 'get_user_receipt_list')
{
	include_once('includes/cls_json.php');
    $json = new JSON;

	$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']): 0;
	$sql = "select rec_id, receipt_name from ".$GLOBALS['ecs']->table('user_ds')." where user_id=".$user_id;
	$res = $GLOBALS['db']->GetAll($sql);
	
	die($json->encode($res));
}
//================================================【重新加载下一张买家秀】================================================//
elseif($_REQUEST['act'] == 'reload_next_mjx')
{
	include_once('includes/cls_json.php');
    $json = new JSON;

	//获得显示的最后一张买家秀id
	$mjx_id = isset($_REQUEST['mjx_id'])? intval($_REQUEST['mjx_id']): 0;
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;

	$sql = "select * from ecs_mjx where id<".$mjx_id." and sh=1 and img<>'' and goods_id=".$goods_id." order by id desc limit 1";
	$res = $GLOBALS['db']->getRow($sql);
	
	die($json->encode($res));
}
//================================================【重新加载上一张买家秀】================================================//
elseif($_REQUEST['act'] == 'reload_prev_mjx')
{
	include_once('includes/cls_json.php');
    $json = new JSON;

	//获得显示的最后一张买家秀id
	$mjx_id = isset($_REQUEST['mjx_id'])? intval($_REQUEST['mjx_id']): 0;
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;

	$sql = "select * from ecs_mjx where id>".$mjx_id." and sh=1 and img<>'' and goods_id=".$goods_id." limit 1";
	$res = $GLOBALS['db']->getRow($sql);
	
	die($json->encode($res));
}
//================================================【获得买家秀的具体信息】================================================//
elseif($_REQUEST['act'] == 'get_mjx_info')
{
	include_once('includes/cls_json.php');
    $json = new JSON;

	$mjx_id = isset($_REQUEST['mjx_id'])? intval($_REQUEST['mjx_id']): 0;

	$sql = "select m.*, u.user_name from ecs_mjx as m left join ecs_users as u on m.user_id=u.user_id where m.id=".$mjx_id." and m.sh=1 and m.img<>'' limit 1";
	$res = $GLOBALS['db']->getRow($sql);

	die($json->encode($res));
}
//================================================【买家秀投票】================================================//
elseif($_REQUEST['act'] == 'add_mjx_vote')
{
	$mjx_id = isset($_REQUEST['mjx_id'])? intval($_REQUEST['mjx_id']): 0;

	//今天还剩余多少时间
	$end   = mktime(23,59,59,date("m"),date("d"),date("Y"));
	//$htime = $end-$_SERVER['REQUEST_TIME'];

	$can = false;
	if(isset($_COOKIE['mjx_vote']))
	{
		$mjx_vote = intval($_COOKIE['mjx_vote']);

		if($mjx_vote<5)
		{
			//投票加一
			$mjx_vote +=1;
			setcookie("mjx_vote", $mjx_vote, $end, '/');			
			$can = true;
		}
		else
		{
			//今天不能投票
		}
	}
	else
	{
		//设置cookie
		setcookie("mjx_vote", 1, $end, '/');
		$can = true;
	}

	if($can)
	{
		$sql = "update ecs_mjx set vote=vote+1 where id=".$mjx_id;
		$res = mysql_query($sql);
		if(!empty($mjx_id) && $res)
		{
			echo 'ok';
		}
		else
		{
			echo 'no';
		}
	}
	else
	{
		echo 'limit';//今天超过5票。
	}
}
//================================================【判断用户是否对该商品进行评论】================================================//
elseif($_REQUEST['act'] == 'user_can_comment')
{
	include_once('includes/cls_json.php');
    $json = new JSON;
	$info     = array('code'=>0, 'msg'=>'您还未购买此商品或已经评论过了！');//0:否 1:能

	$user_id  = empty($_SESSION['user_id'])? 0 : $_SESSION['user_id'];
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;

	//判断用户是否有评论该商品的权利
	$sql = "select o.order_id, g.goods_id from ecs_order_info as o left join ecs_order_goods as g on o.order_id=g.order_id where o.user_id=61 and o.pay_status=2 and (o.shipping_status=1 or o.shipping_status=2) and g.goods_id=".$goods_id;
	$res = $GLOBALS['db']->GetAll($sql);

	if(!empty($res))
	{
		//判断用户是否已经评论过该商品了,评论了之后就不能再评论该商品了。
		if(!empty($user_id) && !empty($goods_id))
		{
			//获得客户对这个商品的全部的评价（然后进行比对还有没进行评论的机会没）
			$sql = "select order_id from ecs_comment where id_value=".$goods_id." and user_id=".$user_id." and order_id>0";
			$com = $GLOBALS['db']->GetAll($sql);
			if(!empty($com))
			{
				$can_com = 1;
				foreach($res as $k => $v)
				{
					if(in_array($res['order_id'], $com))
					{
						//这个订单的这个商品已经评论过了
						$info['code'] = 0;
						$info['msg']  = '^_^ 您已经对该商品评论过了！';
						$can_com      = 0;
						break;
					}
				}
				if($can_com)
				{
					$info['code'] = 1;
					$info['msg']  = '';
				}
			}
			else
			{
				$info['code'] = 1;
				$info['msg']  = '';
			}
		}
	}
	else
	{
		$info['msg'] = '^_^ 您还未购买过此商品，暂不能进行评论！';
	}

	die($json->encode($info));
}
elseif($_REQUEST['act'] == 'active121105')
{
	$order_sn = isset($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '0';
	$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '0';
	
	if (is_numeric($order_sn) && is_numeric($user_id)) {
		//
	} else {
		echo '订单号错误!';
		exit;
	}
	
	if ($order_sn && $user_id) {
		//判断是否已参加抽奖
		$temp = 0;
		$t = $GLOBALS['db']->GetOne("SELECT order_sn FROM ".$GLOBALS['ecs']->table('lottery')." WHERE order_sn='".$order_sn."' AND user_id='".$user_id."'");
		if ($t) {
			echo '此订单号已参加抽奖!';
			exit;
		}
		
		$sql = "INSERT INTO " . $GLOBALS['ecs']->table('lottery') . "(`order_sn`, `user_id`, `already`, `join_time`, `status`) VALUES ('".$order_sn."', '".$user_id."', '0', '".time()."', '0')";
		$res = $GLOBALS['db']->query($sql);
		if ($res) echo 'ok';
		
	} else {
		echo '请登录后输入您的订单号!';
	}
}

//-------------是否有足够的库存--------
elseif($_REQUEST['act'] == 'if_enough_goods')
{
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	
	//商品表中的库存数量
	$sql_all = "select goods_number from ecs_goods where goods_id=".$goods_id;
	$all_num = $GLOBALS['db']->GetOne($sql_all);
	
	//购物车商品数量
	$cart_number = 0;
	$sql = "select SUM(goods_number) from ecs_cart where goods_id=".$goods_id;
	$c_num = $GLOBALS['db']->GetOne($sql);
	if ($c_num) $cart_number = $c_num;
	
	//订单表中的数量
	$dtime = mktime(11, 0, 0, date("m"), date("d"), date("Y"));
	$sql = "select * from ecs_order_info where order_status<>2 AND add_time>".$dtime;
	$u_order = $GLOBALS['db']->GetAll($sql);
	$goods_number = 0;
	if(!empty($u_order))
	{
		foreach($u_order as $k => $v)
		{
			$sql = "select SUM(goods_number) from ecs_order_goods where goods_id=".$goods_id." and is_cx=1 and order_id=".$v['order_id'];
			$g_num = $GLOBALS['db']->GetOne($sql);
			if ($g_num) $goods_number += $g_num;
		}
	}
	$res_number = $all_num - $goods_number - $cart_number;
	echo ($res_number>0)? 'yes': 'no';//yes:有库存，no:库存不足
}
//yi:发放外站的红包【ajax功能】
elseif($_REQUEST['act'] == 'send_outsite_bonus')
{
	//===============================================【ajax发放红包（会员领取外站红包）】===================================//

	$yi_user_id    = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$bonus_type_id = isset($_REQUEST['bonus_type_id'])? intval($_REQUEST['bonus_type_id']): 0;	
	$coupon_id     = isset($_REQUEST['coupon_id'])? intval($_REQUEST['coupon_id']): 0;	

	$info          = array('info_code'=>0, 'info_msg'=>'', 'bonus_type_id'=>0); //领取结果 0表示失败, 1表示领取成功。

	if(!empty($bonus_type_id) && !empty($yi_user_id) && !empty($coupon_id))
	{
		$info['bonus_type_id'] = intval($bonus_type_id);
	
		$sql2   = "select * from ".$GLOBALS['ecs']->table('coupon')." where coupon_id=".$coupon_id;
		$coupon = $GLOBALS['db']->getRow($sql2);
		
		$sql_q  = "select * from ".$GLOBALS['ecs']->table('coupon_list')." where coupon_id=".$coupon_id." and user_id=0 limit 1;";
		$clist  = $GLOBALS['db']->getRow($sql_q);
		
		//时间过期不能领用
		$time = $_SERVER['REQUEST_TIME'];
		if($time < $coupon['end_date'] && !empty($clist))
		{
			//一个会员一种类型的券只能够领取1张优惠券。//用户领了没有用的优惠券
			$sql3 = "select * from ".$GLOBALS['ecs']->table('user_bonus')." where user_id='$yi_user_id' and bonus_type_id='$bonus_type_id' and order_id=0 and used_time=0;";
			$quan = $GLOBALS['db']->getAll($sql3);
			if(count($quan)>=1)
			{
				$info['info_code'] = 0;
				$info['info_msg']  = '您好，该类型的红包您已经领用过了！';				
			}
			else
			{				
				//领取红包
				$yi_bonuns_sn = intval($clist['list_id']);
				$sql = "insert into ".$GLOBALS['ecs']->table('user_bonus').
					   "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) values('$bonus_type_id', ".$yi_bonuns_sn.", '$yi_user_id', 0, 0, 0);";
				$res = mysql_query($sql);
				if($res !== false)
				{
					$sql   = "update ".$GLOBALS['ecs']->table('coupon_list')." set user_id=".$yi_user_id.", coupon_ext=".$bonus_type_id." where list_id=".$clist['list_id'];
					$res_u = mysql_query($sql);
					
					$info['info_code'] = 1;
					$info['info_msg']  = '恭喜您，红包领取成功！请到会员中心查看。';
				}
				else
				{
					$info['info_code'] = 0;
					$info['info_msg']  = '很抱歉，由于系统原因，红包领取失败，请稍后联系客服！';
				}
			}
		}
		else
		{ 
			//领用时间过期了
			$info['info_code'] = 0;
			$info['info_msg']  = '您好，该红包领取活动已经结束！';
		}
	}
	else
	{
		//数据无效，领用失败
		$info['info_code'] = 0;
		$info['info_msg']  = '很抱歉，红包领取失败！';
	}

	$str = json_encode($info);
	echo $str;
}

//yi:判断商品能否加入到购物车【控制活动商品一个会员只能购买1个功能】
elseif($_REQUEST['act'] == 'can_qg_goods')
{
	$goods_define = array(1127, 1302, 1307, 1308, 1309, 1310);//yi:这些商品一个id只能购买一个
	
	$user_id = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;
	$in = implode(',', $goods_define);
	
	if($user_id>0)
	{	
		//是否买了6个商品中的任何一个,则不能在继续购买了。弹窗然后提示用户信息。
		$sql = "select g.* from ecs_order_info as o right join ecs_order_goods as g on o.order_id=g.order_id where o.user_id=".$user_id." and o.order_status<>2 and o.order_status<>3 and o.add_time>1354700313 
		       and g.goods_price=2 and g.goods_id in(".$in.")";	//xg
		$qg_goods = $GLOBALS['db']->getAll($sql);
		
		//会员购物车中的商品
		$sql = "select * from ecs_cart where user_id=".$user_id." and session_id='".SESS_ID."' and goods_id in(".$in.") ";//xg
		$qg_goods2 = $GLOBALS['db']->getAll($sql);
	}
	else
	{
		//匿名购买用户
		$sql = "select * from ecs_cart where user_id=0 and session_id='".SESS_ID."' and goods_id in(".$in.") and goods_price=2";//xg
		$qg_goods2 = $GLOBALS['db']->getAll($sql);
	}	
	$can_qg = (!empty($qg_goods) || !empty($qg_goods2))? 0: 1;//用户是否还能抢购: 0,不能抢购

	echo $can_qg;
}

//页头显示购物车信息
elseif($_REQUEST['act'] == 'cart_info')
{
	if ($_SESSION['user_id'] > 0) {
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '" . SESS_ID . "' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '" . CART_GENERAL_GOODS . "'";
		} else {
			$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
		}
		
	}
    $res = $GLOBALS['db']->GetAll($sql);
    $total_count = count($res);
    
    $htmll = '';
    
    if (count($res) <= 0) {
    	//购物车为空
    	$html .= '<div id="head_cart2_box" style="height:40px;line-height:40px; text-align:center; overflow:hidden">';
    	$html .= '<ul style="height:1px;overflow:hidden"><li style="float:left;width:120px;height:1px;background-color:#ffffff;"></li><li style="float:left;width:198px;height:1px;background-color:#e7e7e7;"></li></ul>';//列表顶部的边框
    	$html .= '<ul style="height:39px"><li style="float:left;width:50px;text-align:center;"><img src="themes/default/images/common/head_cart.gif" alt="" style="padding-top:10px;" /></li><li style="float:left;line-height:39px;color:#cdcdcd;">您的购物车里还没有易视网的商品，欢迎选购</li></ul>';
    	$html .= '</div>';
    	echo $html;
    	exit;
    }
    $height = $total_count * 58; //购物车商品列表的高度,超过171px就出现下拉框(每一行高是50+6+1)
    if ($height >= 174) {
    	$box_height = 173;
    } else {
    	$box_height = $height;
    }
    
    $total_goods_num = 0;	//购物车商品总数
    $total_goods_money = 0.00;	//购物车总金额
    
    $html .= '<div id="head_cart2_box" style="height:'.$box_height.'px; overflow:auto">';
    $html .= '<ul style="height:1px;overflow:hidden"><li style="float:left;width:120px;height:1px;background-color:#ffffff;"></li><li style="float:left;width:198px;height:1px;background-color:#e7e7e7;"></li></ul>';//列表顶部的边框
    
    foreach ($res as $value) {
    	$html .= '<ul id="head_cart2_goods_'.$value['rec_id'].'" class="head_cart2_goods">';
    	$html .= '<li class="head_cart2_img"><a href="goods'.$value['goods_id'].'.html" target="_blank"><img src="http://www.easeeyes.com/thumb/goods/60x60/goods_'.$value['goods_id'].'_60x60.jpg" width="40" height="40" alt="" /></a></li>';
    	$html .= '<li class="head_cart2_title"><a href="goods'.$value['goods_id'].'.html" target="_blank">'.$value['goods_name'].'</a></li>';
    	if ($value['extension_code'] == 'package_buy' || $value['extension_code'] == 'tuan_buy' || $value['extension_code'] == 'exchange_buy' || $value['extension_code'] == 'group_buy') {
			//礼包产品不能在此删除
			$html .= '<li class="head_cart2_price"><span style="color:#e43232">￥'.$value['goods_price'].'</span><span style="color:#a7a7a7"> x'.$value['goods_number'].'</span><br /></li>';
		} else {
			$html .= '<li class="head_cart2_price"><span style="color:#e43232">￥'.$value['goods_price'].'</span><span style="color:#a7a7a7"> x'.$value['goods_number'].'</span><br /><a href="javascript:;" onclick="head_drop_goods('.$value['rec_id'].', '.$total_count.', '.$value['goods_number'].', '.$value['goods_number']*$value['goods_price'].');">删除</a></li>';
		}
    	$html .= '</ul>';
    	
    	$total_goods_num += $value['goods_number'];
    	$total_goods_money += $value['goods_number'] * $value['goods_price'];
    }
    
    $html .= '</div>';
    
    $html .= '<div class="head_cart_check">';
    $html .= '<ul>';
    $html .= '<li class="head_cart_check_num">共<span id="head_cart_goods_num" style="color:#e43232;">'.$total_goods_num.'</span>件商品</li>';
    $html .= '<li class="head_cart_check_price">合计：<span style="color:#e43232; font-size:14px; font-weight:bold;">￥<label id="head_cart_goods_money">'.number_format($total_goods_money, 2, '.', '').'</label></span></li>';
    $html .= '</ul>';
    $html .= '<ul><li style="width:305px;text-align:right;"><a href="flow.html"><img src="themes/default/images/common/head_cart_button.gif" alt"去购物车结算" /></a></li></ul>';
    $html .= '</div>';
    
    echo $html;
	
}
//yi:bt2:check mobile code, send fav.
elseif($_REQUEST['act'] == 'send_fav_goods')
{	
	$code = isset($_REQUEST['code'])? intval($_REQUEST['code']): '';
	$user_id  = 61;
	$goods_id = 1685;
	$gift_id  = 888;

	$send_fav = true;


	//check code
	$sql = "select rec_id, extension_id, verify from ecs_sms_verify where user_id='$user_id' order by send_time desc limit 1;";
	$res = $GLOBALS['db']->GetRow($sql);
	$code_true = $res['extension_id'];

	if(!empty($res))
	{
		if($code != $code_true)
		{
			$send_fav = false;
		}
		else
		{
			//check is ok
			mysql_query("update ecs_sms_verify set verify=1 where rec_id=".$res['rec_id']);
		}
	}
	else
	{
		$send_fav = false;
	}

	if(!function_exists(insert_cart))
	{
		include_once(ROOT_PATH . 'includes/lib_order.php');
	}

	if($send_fav)
	{
		insert_cart($goods_id, 1, $gift_id, 0);
		echo 'ok';
	}
	else
	{
		echo 'fail';
	}
}
//send_mobile_code
elseif($_REQUEST['act'] == 'send_mobile_code')
{
	include_once('sms_fun.php');
	$rt_msg = '';



	
	$code   = mt_rand(123456, 999999);
	$user_id= 61;
	$mobile = '15021879187';

	//$sql  = "select extension_id from ecs_sms_verify where mobile='$mobile' order by rec_id desc limit 1;";
	//$resc = $GLOBALS['db']->GetOne($sql);

	$msg    = "您的手机验证码是：【".$code."】，发送时间：".date("H:i:s")."【易视网】";

	//ck mobile if ok
	$sql  = "select rec_id from ecs_sms_verify where mobile='$mobile' and verify=1 limit 1;";
	$resv = $GLOBALS['db']->GetOne($sql);
	if(!empty($resv))
	{
		$rt_msg = 'have_tel';
	}
	else
	{
		$res = send_sms($mobile, $msg);
		if($res)
		{			
			//send ok, save 
			$sql = "insert into ecs_sms_verify(user_id, mobile, send_time, extension, extension_id) values('$user_id', '$mobile', ".$_SERVER['REQUEST_TIME'].", 'at_bocomm', '$code');";
			mysql_query($sql);
		}
		$rt_msg = $res? 'ok': 'fail';
	}
	echo $rt_msg;
}

//同步已发布买家秀图片到第三方应用
elseif ($_REQUEST['act'] == 'share_buyersshow')
{
	$app    = isset($_REQUEST['app'])? $_REQUEST['app']: '';
	$mjxid    = isset($_REQUEST['mjxid'])? intval($_REQUEST['mjxid']): 0;
	
	//获取买家秀信息
	$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx') . " WHERE id=".$mjxid." LIMIT 1";
	$mjx_info = $GLOBALS['db']->getRow($sql);
	
	//获取用户绑定的应用
	$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
	$sync =  $GLOBALS['db']->getAll($sql_sync);
	$user_sync = array();
	if ($sync) {
		foreach ($sync as $k => $v) {
			$user_sync[$v['app_name']]['sid'] = $v['sid'];
			$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
			$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
			$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
			$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
			$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
			$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
		}
	}
	$qq_sync = $user_sync['qq'];
	$sina_sync = $user_sync['sina'];
	$renren = $user_sync['renren'];
	$kaixin = $user_sync['kaixin'];
	
	//新浪微博
	if ($app == 'sina' && ! empty($_SESSION['user_id']))
	{
		include_once(dirname(__FILE__) . '/api/sina/weibodemo/config.php');
		include_once(dirname(__FILE__) . '/api/sina/weibodemo/saetv2.ex.class.php');
		
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $sina_sync['session_data']['access_token'] );
		
		//$ret = $c->update($detail);	//发送文字微博
		$weibo_text = $mjx_info['detail']." http://www.easeeyes.com/buyersshow_goods.php?mjxid=$mjxid";
		$weibo_img = 'http://www.easeeyes.com/'.$mjx_info['img'];
		$ret = $c->upload($weibo_text, $weibo_img); //图文微博
		if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
			//echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
		} else {
			echo '恭喜您成功分享您的买家秀到新浪微博!';
		}
	}
	
	//腾讯微博
	if ($app == 'qq' && ! empty($_SESSION['user_id']))
	{
		if ( ! $_SESSION['t_access_token']) {
			$_SESSION['t_access_token']		=	$qq_sync['session_data']['t_access_token'];
			$_SESSION['t_refresh_token']	=	$qq_sync['session_data']['t_refresh_token'];
			$_SESSION['t_expire_in']		=	$qq_sync['session_data']['t_expire_in'];
			$_SESSION['t_code']				=	$qq_sync['session_data']['t_code'];
			$_SESSION['t_openid']			=	$qq_sync['session_data']['t_openid'];
			$_SESSION['t_openkey']			=	$qq_sync['session_data']['t_openkey'];
		}
		
		include_once(dirname(__FILE__) . '/api/qq/phpsdk/Config.php');
		include_once(dirname(__FILE__) . '/api/qq/phpsdk/Tencent.php');
		
		OAuth::init($client_id, $client_secret);
		Tencent::$debug = $debug;
		
		$weibo_text = $mjx_info['detail']." http://www.easeeyes.com/buyersshow_goods.php?mjxid=$mjxid";
		$weibo_img = 'http://www.easeeyes.com/'.$mjx_info['img'];
		
		//用图片URL发表带图片的微博
	    $params = array(
	        'content'	=>	$weibo_text,
	        'pic_url'	=>	$weibo_img
	    );
	    $r = Tencent::api('t/add_pic_url', $params, 'POST');
	    //echo $r;
	    echo '恭喜您成功分享您的买家秀到腾讯微博!';
	}
}

//分享抽奖结果到微博
elseif ($_REQUEST['act'] == 'share_prize')
{
	$app = isset($_REQUEST['app'])? $_REQUEST['app']: '';
	$id = isset($_REQUEST['id'])? intval($_REQUEST['id']): 0;
	
	//获取买家秀信息
	$sql = "SELECT * FROM lele_zprize2 WHERE z_id=".$id." LIMIT 1";
	$prize_info = $GLOBALS['db']->getRow($sql);
	if ($prize_info)
	{
		$weibo_text = '哇！RP大爆发我居然中奖了！';
		if ($prize_info['z_prizeid'] == 1) {
			$weibo_text .= '我抽中易视网5元现金券。';
		} elseif ($prize_info['z_prizeid'] == 2) {
			$weibo_text .= '我抽中易视网50元现金券。';
		} elseif ($prize_info['z_prizeid'] == 3) {
			$weibo_text .= '我抽中易视网500积分。';
		} elseif ($prize_info['z_prizeid'] == 4) {
			$weibo_text .= '我抽中易视网全场通用满199减15现金券。';
		} elseif ($prize_info['z_prizeid'] == 5) {
			$weibo_text .= '我抽中易视网彩色片满199减30现金券。';
		} elseif ($prize_info['z_prizeid'] == 6) {
			$weibo_text .= '我抽中易视网全场通用满299减30现金券。';
		} elseif ($prize_info['z_prizeid'] == 7) {
			$weibo_text .= '我抽中易视网100积分。';
		} elseif ($prize_info['z_prizeid'] == 8) {
			$weibo_text .= '我抽中易视网框架镜架满199减30现金券。';
		} elseif ($prize_info['z_prizeid'] == 9) {
			$weibo_text .= '我抽中易视网10元现金券。';
		}
		$weibo_text .= "易视眼镜网#双11狂欢节#提前开抢，玩转大转盘100%中奖，百万红包即领即用，品牌5折再享折上折。 http://www.easeeyes.com/active131111.html";
		$weibo_img = 'http://www.easeeyes.com/themes/default/images/active/20131111/prize.jpg';
	}
	
	//获取用户绑定的应用
	$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
	$sync =  $GLOBALS['db']->getAll($sql_sync);
	$user_sync = array();
	if ($sync) {
		foreach ($sync as $k => $v) {
			$user_sync[$v['app_name']]['sid'] = $v['sid'];
			$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
			$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
			$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
			$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
			$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
			$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
		}
	}
	$qq_sync = $user_sync['qq'];
	$sina_sync = $user_sync['sina'];
	
	//新浪微博
	if ($app == 'sina' && ! empty($_SESSION['user_id']))
	{
		include_once(dirname(__FILE__) . '/api/sina/weibodemo/config.php');
		include_once(dirname(__FILE__) . '/api/sina/weibodemo/saetv2.ex.class.php');
		
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $sina_sync['session_data']['access_token'] );
		
		$ret = $c->upload($weibo_text, $weibo_img);
		if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
			echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
		} else {
			echo '恭喜您成功分享您的中奖信息到新浪微博! 哇，分享再送99-5红包一张，赚到了请在“我的易视”-“我的红包”中查看';
		}
	}
	
	//腾讯微博
	if ($app == 'qq' && ! empty($_SESSION['user_id']))
	{
		if ( ! $_SESSION['t_access_token']) {
			$_SESSION['t_access_token']		=	$qq_sync['session_data']['t_access_token'];
			$_SESSION['t_refresh_token']	=	$qq_sync['session_data']['t_refresh_token'];
			$_SESSION['t_expire_in']		=	$qq_sync['session_data']['t_expire_in'];
			$_SESSION['t_code']				=	$qq_sync['session_data']['t_code'];
			$_SESSION['t_openid']			=	$qq_sync['session_data']['t_openid'];
			$_SESSION['t_openkey']			=	$qq_sync['session_data']['t_openkey'];
		}
		
		include_once(dirname(__FILE__) . '/api/qq/phpsdk/Config.php');
		include_once(dirname(__FILE__) . '/api/qq/phpsdk/Tencent.php');
		
		OAuth::init($client_id, $client_secret);
		Tencent::$debug = $debug;
		
		//用图片URL发表带图片的微博
	    $params = array(
	        'content'	=>	$weibo_text,
	        'pic_url'	=>	$weibo_img
	    );
	    $r = Tencent::api('t/add_pic_url', $params, 'POST');
	    //echo $r;
	    echo '恭喜您成功分享您的中奖信息到腾讯微博! 哇，分享再送99-5红包一张，赚到了请在“我的易视”-“我的红包”中查看';
	}
}
//分享彩票到微博
elseif ($_REQUEST['act'] == 'share_cpprize')
{
	$app = isset($_REQUEST['app'])? $_REQUEST['app']: '';
	$weibo_text .= "#马上有钱#贺新春，上@易视网隐形眼镜商城 下订单分享即送彩票！加1元囤年货，红包即领即用！http://www.easeeyes.com/active140110.html";
	$weibo_img = 'http://www.easeeyes.com/themes/default/images/active/20140110/price.jpg';
	//获取用户绑定的应用
	$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
	$sync =  $GLOBALS['db']->getAll($sql_sync);
	$user_sync = array();
	if ($sync) {
		foreach ($sync as $k => $v) {
			$user_sync[$v['app_name']]['sid'] = $v['sid'];
			$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
			$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
			$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
			$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
			$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
			$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
		}
	}
	
	$qq_sync = $user_sync['qq'];
	$sina_sync = $user_sync['sina'];
	
	//新浪微博
	if ($app == 'sina' && ! empty($_SESSION['user_id']))
	{
		include_once(dirname(__FILE__) . '/api/sina/weibodemo/config.php');
		include_once(dirname(__FILE__) . '/api/sina/weibodemo/saetv2.ex.class.php');
		
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $sina_sync['session_data']['access_token'] );
		
		$ret = $c->upload($weibo_text, $weibo_img);
		if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
			echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
		} else {

			echo '恭喜您已成功分享，彩票代金券请在站内短信中查收“我的易视”→“系统通知”！';
		}
	}
	//腾讯微博
	if ($app == 'qq' && ! empty($_SESSION['user_id']))
	{
		if ( ! $_SESSION['t_access_token']) {
			$_SESSION['t_access_token']		=	$qq_sync['session_data']['t_access_token'];
			$_SESSION['t_refresh_token']	=	$qq_sync['session_data']['t_refresh_token'];
			$_SESSION['t_expire_in']		=	$qq_sync['session_data']['t_expire_in'];
			$_SESSION['t_code']				=	$qq_sync['session_data']['t_code'];
			$_SESSION['t_openid']			=	$qq_sync['session_data']['t_openid'];
			$_SESSION['t_openkey']			=	$qq_sync['session_data']['t_openkey'];
		}
		
		include_once(dirname(__FILE__) . '/api/qq/phpsdk/Config.php');
		include_once(dirname(__FILE__) . '/api/qq/phpsdk/Tencent.php');
		
		OAuth::init($client_id, $client_secret);
		Tencent::$debug = $debug;
		
		//用图片URL发表带图片的微博
	    $params = array(
	        'content'	=>	$weibo_text,
	        'pic_url'	=>	$weibo_img
	    );
	    $r = Tencent::api('t/add_pic_url', $params, 'POST');
	    //echo $r;
	    echo '恭喜您已成功分享，彩票代金券请在站内短信中查收“我的易视”→“系统通知”！';
	}

}
//团购页面选择不同颜色商品 tuan.php
elseif ($_REQUEST['act'] == 'tuan_select_color') 
{
	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;
	
	if ($goods_id) {
		$row = $GLOBALS['db']->GetRow("SELECT goods_id, goods_name, goods_img FROM ecs_goods WHERE goods_id=$goods_id");
		if ($row) {
			$goods['goods_id'] = $row['goods_id'];
			$goods['goods_name'] = $row['goods_name'];
			$goods['goods_img'] = $row['goods_img'];
			$goods['goods_ds'] = '';
			
			//$goods_ds = get_goodsds_info($goods_id); //度数
			$goods_ds = get_goods_ds($goods_id);
			if ($goods_ds) {
				/*$ds_value = $goods_ds['ds_values'];
				
				$goods['goods_ds'] .= '<option value="">请选择</option>';
				foreach ($ds_value as $v) {
					$goods['goods_ds'] .= '<option value="'.$v.'">'.$v.'</option>';
				}*/
				$goods['goods_ds'] .= '<option value="">请选择</option>';
				foreach ($goods_ds as $v) {
					if ($v['canbuy']) {
						$goods['goods_ds'] .= '<option value="'.trim($v['val']).'">'.$v['val'].'</option>';
					} else {
						$goods['goods_ds'] .= '<option value="">'.$v['val'].'(补货中)</option>';
					}
				}
			}
			
		} else {
			$goods['goods_id'] = '';
			$goods['goods_name'] = '';
			$goods['goods_img'] = '';
			$goods['goods_ds'] = '';
		}
		//print_r($goods);
		
		$str = json_encode($goods);
		echo $str;
	}
}

//20140507领取VIP赠品
elseif ($_REQUEST['act'] == 'get_vip_gift') 
{
	$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']): 0;
	$user_rank = (isset($_SESSION['user_rank']) && $_SESSION['user_rank']>0)? intval($_SESSION['user_rank']): 0;
	
	if ( ! empty($user_id) && $user_rank > 1)
	{
		//判断是否该用户是否已领取
		$had_get = FALSE;
		
		$had_get_cart = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS num FROM ecs_cart WHERE user_id = " . $user_id . " AND goods_id IN (3661,3662,3663)");
		$had_get_order = $GLOBALS['db']->GetOne("SELECT COUNT(*) AS num FROM ecs_order_info a LEFT JOIN ecs_order_goods b ON a.order_id = b.order_id WHERE a.user_id = ".$user_id." AND b.goods_id IN (3661,3662,3663)");
		
		if ($had_get_cart + $had_get_order >= 1)
		{
			$had_get = TRUE;
		}
		
		if ( ! $had_get)
		{
			$sql_cart = "";
			if ($user_rank == 2) 
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '3661', '', '普通VIP送易视定制凯达伴侣盒 颜色随机', '10.00', '0.00', '1', '1', 'unchange', '1')";
			}
			elseif ($user_rank == 8)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '3662', '', '白金VIP送海俪恩植物精灵护理液120ml', '21.00', '0.00', '1', '1', 'unchange', '1')";
			}
			elseif ($user_rank == 7)
			{
				$sql_cart = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(`user_id`, `session_id`, `goods_id`, `goods_sn`, `goods_name`, `market_price`, `goods_price`, `goods_number`, `is_real`, `extension_code`, `is_cx`) VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '3663', '', '钻石VIP送人气爆款时尚框架镜(款式随机)', '200.00', '0.00', '1', '1', 'unchange', '1')";
			}
			
			if ( ! empty($sql_cart))
			{
				$res_cart = $GLOBALS['db']->query($sql_cart);
				echo 1;
			}
			else 
			{
				echo 0;
			}
		}
		else 
		{
			echo 0;
		}
		
	}
	else
	{
		echo 0;
	}
}

else
{
	//TODO
}
?>