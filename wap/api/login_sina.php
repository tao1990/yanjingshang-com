<?php
/* ==============================================================================================================
 * sina微博联合登录 API授权成功后用户注册,登录页面【2012/3/2】【Author:yijiangwen】【TIME:2013/5/10】
 * ==============================================================================================================
 */
define('IN_ECS', true);
require('../includes/init.php');
include_once(ROOT_PATH . 'includes/lib_passport.php');

//http://www.easeeyes.com/api/login_sina.php?user_name=".urlencode($me['name'])."&email=".$email."&open_id=".$me['id'];
//user_name：用户名（昵称），email：id.'@sina.weibo.com';

$user_name = isset($_REQUEST['user_name']) ? urldecode($_REQUEST['user_name']) : '';
$password  = 'yishi168';     //固定不可改变。
$email     = isset($_REQUEST['email']) ? trim($_REQUEST['email']): '';
$turn_url  = (isset($_REQUEST['turn_url'])&& !empty($_REQUEST['turn_url']))? trim($_REQUEST['turn_url'])."\n": "http://m.easeeyes.com\n";

$open_id   = (isset($_REQUEST['open_id'])&& !empty($_REQUEST['open_id']))? trim($_REQUEST['open_id']): '';//唯一值，不变[用这个值就可登录了]
$alias     = $user_name;//昵称

//添加指定的登录后跳转地址2014-03-31
session_start();
if (isset($_SESSION['defined_url']))
{
	$turn_url = $_SESSION['defined_url'];
	unset($_SESSION['defined_url']);
}


if(refer_user_exist('sina', $open_id))
{
	//直接通过open_id登录。
	$u_name = $GLOBALS['db']->getOne("select user_name from ecs_users where referer='sina' and refer_id='".$open_id."' limit 1;");
	if($user->login($u_name, $password))
	{	
		update_user_info();
		recalculate_price();
		ecs_header("Location: ".$turn_url);			
	}
	else
	{
		union_login_fail();
	}
}
else
{
	if(have_user_email($user_name, $email))//同时存在用户名和邮箱。直接登录
	{
		if($user->login($user_name, $password))
		{	
			update_user_info();
			recalculate_price();
			update_refer_field($user_name, $open_id, 'sina', $alias);
			ecs_header("Location: ".$turn_url);		
		}
		else
		{
			union_login_fail();
		}
	}
	else
	{
		//如果不存在该用户名,邮箱,注册后再登录
		if(!empty($user_name)&& !empty($email))
		{		
			//新注册用户一定要保证用户名，邮箱唯一。
			if(hv_user_name($user_name))
			{
				$user_name = "新浪用户_".$open_id;
			}
			if(have_email($email))
			{	
				$email = $open_id.'@sina.weibo.com';
			}

			if(register($user_name, $password, $email)!==false)
			{
				if($user->login($user_name, $password))
				{	
					update_user_info();
					recalculate_price();
					update_refer_field($user_name, $open_id, 'sina', $alias);
					ecs_header("Location: ".$turn_url);	
				}
				else
				{
					union_login_fail();
				}
			}
			else
			{
				union_login_fail();
			}
		}	
	}
}


//=======================================================================【函数】==============================================================================//

/* ------------------------------------------------------------------------------------------------------
 * 判断这个用户的唯一码是否存在，存在返回true，不存在返回false。referer:'qq'，refer_id:qq的open_id。
 * ------------------------------------------------------------------------------------------------------
 * referer:会员来源代码，refer_id:用户在来源网站的唯一身份标识码。
 */
function refer_user_exist($referer, $refer_id)
{
	if(empty($refer_id)){return false;}
	$user_id = $GLOBALS['db']->getOne("select user_id from ecs_users where referer='".$referer."' and refer_id='".$refer_id."' limit 1;");
	return (empty($user_id))? false: true;
}


//判断用户名是否已经存在
function hv_user_name($user_name)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'" limit 1;';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){
		return true;
	}else{
		return false;
	}
}

//判断用户邮箱是否已注册
function have_email($email)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where email="'.$email.'" limit 1;';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){
		return true;
	}else{
		return false;
	}
}

//判断用户名,邮箱是否同时存在
function have_user_email($user_name, $email)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'" and email="'.$email.'" limit 1;';
	$row = $GLOBALS['db']->getOne($sql);
	if(!empty($row)){
		return true;
	}else{
		return false;
	}
}

/* -------------------------------------------------------------------------------------------------
 * 联合登录失败的 信息提示
 * -------------------------------------------------------------------------------------------------
 */
function union_login_fail()
{
	$content = '很抱歉！新浪微博登录失败，建议您直接注册购买！';
	show_message_any_dir($content, $links = '10秒快速注册', $hrefs = '../user_register.html', $type = 'info', $auto_redirect = true);
}


/* -------------------------------------------------------------------------------------------------
 * 局部更新外部会员字段 在ecs_users表中referer,refer_id字段。
 * -------------------------------------------------------------------------------------------------
 * $user_name：我们商城用户名(唯一).
 */
function update_refer_field($user_name, $refer_id, $refer='', $alias='')
{	
	$sql = "update ecs_users set referer='".$refer."', refer_id='".$refer_id."', alias='".$alias."' where user_name='".$user_name."';";
	mysql_query($sql);
}
?>