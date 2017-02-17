<?php
/* ===================================================================================================================================
 * 商城页面 支付宝联合登录接口【2012/8/21】【Author:yijiangwen】【同步TIME:2012/8/21】
 * ===================================================================================================================================
 * $url = "http://www.easeeyes.com/api/login_api.php?real_name=".urlencode($user_name)."&email=".$email."&ali_user_id=".$user_id;//线上用的
 * user_id   支付宝用户id（唯一值）
 * email     没有数据
 * real_name 用户姓名/昵称（有重名）
 * {只有OPEN_ID用联合登录网站，其它都是自己的数据}
 */
define('IN_ECS', true);
require('../includes/init.php');
include_once(ROOT_PATH.'includes/lib_passport.php');
header("Content-type: text/html; charset=utf-8");

//本地测试地址：http://localhost/api/login_ali.php?real_name=%E6%98%93%E6%B1%9F%E6%96%87&email=yijiangwen163@163.com&ali_user_id=2088102420475405

$user_name   = isset($_REQUEST['real_name']) ? trim($_REQUEST['real_name']): '';
$password    = $user_name;                                                   //这样就不怕用户昵称改变影响密码了。
$email       = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '';    //现在已经关闭了
$ali_user_id = isset($_REQUEST['ali_user_id'])? $_REQUEST['ali_user_id']: '';//支付宝open_id（唯一值）
$turn_url    = (isset($_REQUEST['turn_url']) && !empty($_REQUEST['turn_url']))? trim(urldecode($_REQUEST['turn_url']))."\n": "http://m.easeeyes.com\n";
$real_name   = $user_name;//寄存
$real_email  = $email;

//添加指定的登录后跳转地址2014-03-31
session_start();
if (isset($_SESSION['defined_url']))
{
	$turn_url = $_SESSION['defined_url'];
	unset($_SESSION['defined_url']);
}

if(!is_utf8($user_name))
{
	$user_name = mb_convert_encoding($user_name, "UTF-8");//中文user_name转化成utf-8编码
}

if(refer_user_exist('alipay', $ali_user_id))
{
	//通过open_id判断这个用户存在。存在则直接登录【这个是最新的逻辑】
	$t_password = 'yishi168';

	$temp     = $GLOBALS['db']->getRow("select user_name,password from ecs_users where referer='alipay' and refer_id='$ali_user_id' limit 1;");
	$uname    = $temp['user_name'];

	if(md5($t_password) == $temp['password'])
	{
		if($user->login($uname, $t_password))
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
		//自己制定用户密码。这样就不怕用户昵称改变影响登录密码
		mysql_query("update ecs_users set password=md5('".$t_password."') where referer='alipay' and refer_id='$ali_user_id';");
		if($user->login($uname, $t_password))
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
}
else
{
	if(have_user_email($user_name, $email))
	{
		//一, 用户真名和邮箱同时存在(情况唯一)
		if($user->login($user_name, $password))
		{	
			update_user_info();
			recalculate_price();
			update_refer_field($user_name, $ali_user_id, 'alipay', $real_name);//更新OPEN_ID
			ecs_header("Location: ".$turn_url);				
		}
		else
		{
			union_login_fail();
		}
	}
	else
	{
		//二, 用户真名和邮箱不同时存在(新的联合登录用户)真名重复的很多。

		if(!empty($user_name) && !empty($email))
		{		
			if(check_user($user_name) && have_email($email))
			{
				//2.1 用户名，邮箱同时存在，但不在同一个账户中				
				
				$pwd_old = $GLOBALS['db']->getOne("select password from ecs_users where email='$email'");
				if(md5($password) == $pwd_old)
				{
					//根据邮箱找密码，如密码相等，立即登录邮箱账户。
					$user_name3 = $GLOBALS['db']->getOne("select user_name from ecs_users where email='$email'");
					if($user->login($user_name3, $password))
					{	
						update_user_info();
						recalculate_price();
						update_refer_field($user_name3, $ali_user_id, 'alipay', $real_name);//更新OPEN_ID
						ecs_header("Location: ".$turn_url);	
					}
					else
					{
						union_login_fail();
					}
				}
				else
				{
					//在邮箱用户的密码不合的情况下重新注册
					for($i=0; $i<10; $i++)
					{
						if(check_user($user_name))
						{
							$user_name = $user_name.'1';
							$email     = '1'.$email;
						}
						else
						{
							break;
						}
					}

					if(register($user_name, $password, $email)!== false)
					{
						//注册新用户，进行登录(用户名和邮箱都不存在)
						if($user->login($user_name, $password))
						{	
							update_user_info();
							recalculate_price();
							update_refer_field($user_name, $ali_user_id, 'alipay', $real_name);
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
			else
			{
				//目的：之前的用户的逻辑一定要能够让用户真正登录到用户曾经购买过的账号上面去。

				//2.2 真名||邮箱在数据表中有重复的情况
				if(check_user($user_name) && !have_email($email))
				{
					$user_name = $user_name.'_1';
					$email     = '1'.$email;
				}

				//邮箱重复1
				if(have_email($email) && !check_user($user_name))
				{			
					$user_name = $user_name.'_2';
					$email     = '2'.$email;
				}
				//------------------------------------后续对邮箱的判断，虽然有重名但是用户的邮箱必定不同的-------------------------------------//

				//if这个用户名也存在， 同时汉字名也存在，但是这个邮箱和这个用户——2的邮箱是不同的，则这个时候这个用户的用户名还要继续变化。
                //王_1用户真名还存在
				if(check_user($user_name))
				{
					$f_email = $GLOBALS['db']->getOne("select email from ecs_users where user_name='$user_name'");
					if(strpos($f_email, $real_email))
					{
						//是原本用户，用户进行登录
						if($user->login($user_name, $password))
						{	
							update_user_info();
							recalculate_price();
							update_refer_field($user_name, $ali_user_id, 'alipay', $real_name);
							ecs_header("Location: ".$turn_url);	
						}
						else
						{
							union_login_fail();
						}
					}
					else
					{
						//真名重复的处理 用户名继续变化。
						for($i=0; $i<10; $i++)
						{
							if(check_user($user_name))
							{
								$user_name = $user_name.'1';
								$email     = '1'.$email;
							}
							else
							{
								break;
							}
						}
						if(register($user_name, $password, $email)!== false)
						{
							//注册新用户后进行登录(用户名和邮箱都不存在)
							if($user->login($user_name, $password))
							{	
								update_user_info();
								recalculate_price();
								update_refer_field($user_name, $ali_user_id, 'alipay', $real_name);
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
				else
				{
					//用户名不存在了，再检查邮箱
					if(have_email($email))
					{
						$user_name = $user_name.'1';
						$email     = '1'.$email;
					}
					
					//用户名和邮箱都不存在 注册新用户后进行登录
					if(!check_user($user_name) && !have_email($email))
					{
						if(register($user_name, $password, $email)!== false)
						{							
							if($user->login($user_name, $password))
							{	
								update_user_info();
								recalculate_price();
								update_refer_field($user_name, $ali_user_id, 'alipay', $real_name);
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
					else
					{
						$user_name = $user_name.'3';
						$email     = '3'.$email;
						if(register($user_name, $password, $email)!== false)
						{
							if($user->login($user_name, $password))
							{	
								update_user_info();
								recalculate_price();
								update_refer_field($user_name, $ali_user_id, 'alipay', $real_name);
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
		}
		else
		{
			union_login_fail();
		}
	}
}


//=================================================================================【函数】==================================================================================//

/* -------------------------------------------------------------------------------------------------
 * 判断字符串是否是utf-8的编码
 * -------------------------------------------------------------------------------------------------
 */
function is_utf8($string){       
	return preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $string);  
}


/* -------------------------------------------------------------------------------------------------
 * 判断这个用户的唯一码是否存在，referer:alipay. 存在返回true，不存在返回false。
 * -------------------------------------------------------------------------------------------------
 * referer:会员来源，refer_id:来源网站用户的唯一身份标识码。
 */
function refer_user_exist($referer, $refer_id)
{
	if(empty($refer_id)){return false;}

	$user_id = $GLOBALS['db']->getOne("select user_id from ecs_users where referer='".$referer."' and refer_id='".$refer_id."' limit 1;");
	return empty($user_id)? false: true;
}


//判断用户名是否已经存在
function check_user($user_name){
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'";';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){
		return true;
	}else{
		return false;
	}
}


//判断用户邮箱是否已注册
function have_email($email){
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where email="'.$email.'";';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){
		return true;
	}else{
		return false;
	}
}


//判断用户名,邮箱是否同时存在
function have_user_email($user,$email){
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'" and email="'.$email.'";';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){
		return true;
	}else{
		return false;
	}
}


/* -------------------------------------------------------------------------------------------------
 * 局部更新外部会员字段 在ecs_users表中referer,refer_id字段。
 * -------------------------------------------------------------------------------------------------
 * $user_name：用户名(唯一).
 */
function update_refer_field($user_name, $ali_user_id, $refer='', $real_name='')
{	
	if(!empty($ali_user_id))
	{
		$sql = "update ecs_users set referer='".$refer."', refer_id='".$ali_user_id."', alias='".$real_name."' where user_name='".$user_name."' ;";
		mysql_query($sql);
	}
	else
	{
		return false;
	}
}


/* -------------------------------------------------------------------------------------------------
 * 联合登录失败的 信息提示
 * -------------------------------------------------------------------------------------------------
 */
function union_login_fail()
{
	$content = '很抱歉！支付宝联合登录失败，建议您直接注册购买！';
	show_message_any_dir($content, $links = '10秒快速注册', $hrefs = '../user_register.html', $type = 'info', $auto_redirect = true);
}
?>