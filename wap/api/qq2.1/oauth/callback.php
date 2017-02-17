<?php
require_once("../API/qqConnectAPI.php");
$qc = new QC();
$acs = $qc->qq_callback();//callback主要是验证 code和state,返回token信息，并写入到文件中存储，方便get_openid从文件中度  
$open_id = $qc->get_openid();//根据callback获取到的token信息得到openid,所以callback必须在openid前调用  
$qc = new QC($acs,$open_id);  
$arr = $qc->get_user_info();  



/* ==============================================================================================================
 * QQ联合登录 API授权成功后用户注册，登录页面【同步2013/4/24】【Author:yijiangwen】
 * ==============================================================================================================
 * open_id  ：open_id和qq账号对应且唯一值。全部qq开放平台的open_id都是这个，永远不变。由一串数字和字母的字符串组成。
 * user_name：取的open_id的前8位。存在雷同的情况，真是没有多考虑一下。
 * pwd      ：用户登录密码和user_name相同，而且不变。
 * email    ：构造的，用$open_id8位@qq.com组成。固定不变。但是会有重复的危险。
 *            qq用户登录一次就记录住用户的open_id，把open_id保存在refer_id中保持不变。
 */
define('IN_ECS', true);

require_once('../../../includes/init.php');

include_once(ROOT_PATH . 'includes/lib_passport.php');

//user_name:用户名 email:注册邮箱 pwd:密码为用户名  qq:openid(一一对应一个qq号码)
$open_id   = isset($open_id) ? trim($open_id): "";//唯一值，不变
$user_name = trim(substr($open_id, -8)); //用户名,邮箱自构造。保持不变。
$pwd       = trim($user_name);                                              //不变
$email     = $user_name.'@qq.com';                //注册邮箱.不变
$qq        = trim($user_name);                                                //不变
$alias     = trim($arr['nickname']);                                        //不变 qq昵称，做为我们的用户昵称。



$jump_url  = (isset($_COOKIE["jump_url"]) && !empty($_COOKIE["jump_url"])) ? trim($_COOKIE["jump_url"])."\n" : "http://www.easeeyes.com\n";
//$turn_url  = (isset($_REQUEST['turn_url']) && !empty($_REQUEST['turn_url'])) ? trim($_REQUEST['turn_url'])."\n" : $jump_url;
$turn_url  =  $jump_url;
if (isset($_COOKIE['users_src']) && $_COOKIE['users_src']=='tenpay_active')
{
	$turn_url = 'http://www.easeeyes.com/active131228.html'; //2013.12.28活动
}

//添加指定的登录后跳转地址2014-03-31
//if (isset($_COOKIE['defined_url']))
//{
//	$turn_url = $_COOKIE['defined_url'];
//}


/* 测试地址：http://localhost/api/login_qq.php?user_name=%E6%98%93%E6%B1%9F%E6%96%87&email=12345678@qq.com&open_id=2088102420475405
 *
 * 联合登录的开始路径：http://www.easeeyes.com/api/qq/oauth/redirect_to_login.php=>经过签名验证=>然后通过login_qq.php登录到我们商城。
 *
 * qq联合登录，qq彩贝联合登录，其它qq开放平台是共用的一个open_id。open_id是唯一对应一个qq号码的。同时应该在我们中唯一对应一个会员账号。
 */
setcookie('qq_head', '', time()-88);//清除彩贝cookies,控制是否显示qq_head.


//测试专用板块
//$open_id = '00176989EE063839EA81765976779F9B';
//if($user_name == '1BEF0180' && empty($alias)){$alias = "<script>nike name is null</script>";}

if(refer_user_exist('qq', $open_id))
{

	//====================【qq open_id 已经和我们的会员账号一一对应， 直接登录】====================//
	
	$sql		= "select user_name, user_id, union_login_bind from ecs_users where referer='qq' and refer_id='$open_id' limit 1";
	$tuname		= $GLOBALS['db']->getRow($sql);
	$uname		= $tuname['user_name'];
    $pwd		= $tuname['user_name'];
	$tuser_id	= intval($tuname['user_id']);
	update_alias($tuser_id, $alias);		//更新用户昵称。
	
	$sql = "update ecs_users set msn=2, email=replace(email, '@qq.com', '@qq.login.com') where user_id=".$tuser_id." and msn=1 limit 1;";
	mysql_query($sql);						//更新用户邮箱为qq.login.com

	if($_REQUEST['step']=='ununion')		//不绑定账号登录
	{
	  
		if($user->login($uname, $pwd))
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
	elseif($_REQUEST['step']=='bind_user')	//绑定用户信息
	{
		$fname = isset($_REQUEST['user_name'])? addslashes($_REQUEST['user_name']): '';
		$pwd   = isset($_REQUEST['pwd'])? trim($_REQUEST['pwd']): '';
		$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
		$tel   = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
		$fopen = isset($_REQUEST['open_id'])? trim($_REQUEST['open_id']): '';

		//更新会员信息
		if($open_id == $fopen && $tuser_id>0)
		{
			$sql =  "update ecs_users set ".
					" user_name	='".$fname."', ".
				    " password	='".md5($pwd)."', ".
					" email		='".$email."', ".
					" union_login_bind =1, ".
					" mobile_phone ='".$tel."' ".
					" where user_id=".$tuser_id." limit 1;";
			$ures = mysql_query($sql);
			if($ures)
			{
				if($user->login($fname, $pwd))
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
		if(0 == $tuname['union_login_bind'])
		{
			$smarty->assign('open_id', $open_id);
			$smarty->assign('alias',   $alias);
			$smarty->assign('url',     $_SERVER['QUERY_STRING']);
            //header("location:callback.php?step=ununion&".$_SERVER['QUERY_STRING']);
			//$smarty->display('bind.dwt');		
            //不绑定账号登录
            
            if($user->login($uname, $pwd))
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
			//yi:已经绑定过用户信息了,直接登录。		
			if($user->login_no_password($uname, 'qq'))
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
}
elseif(have_qq($qq))//open_id放入在qq字段中的情况
{
	//====================用户已经用过qq联合登录（qq已经和我们的会员账号绑定）未记录open_id字段====//

	//1.1 获取这个用户的全部信息（根据qq字段）
	$sql = "select user_id, user_name, email from ".$GLOBALS['ecs']->table('users')." where qq='$qq' limit 1;";
	$qqu = $GLOBALS['db']->getRow($sql);
	
	if(!empty($qqu['user_id']))
	{
		//1.2 匹配数据库上和现在的用户名是否正确：如果不正确 则重新更新现在的用户名
		if($qqu['user_name']!=$user_name)
		{
			$sqlu = "update ".$GLOBALS['ecs']->table('users')." set user_name='$user_name' where user_id=".$qqu['user_id'];
			mysql_query($sqlu);				
		}
		$email = $qqu['email'];
	}
	update_alias($qqu['user_id'], $alias); //更新用户的昵称。

	//用现在的用户名 进行登录。
	if(!empty($user_name))
	{		
		if($user->login($user_name, $pwd))
		{	
			update_user_info();
			recalculate_price();
			update_refer_field($user_name, $open_id, 'qq');
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
	//==============================================open_id, qq字段都为空的情况，【用户联合登录并记录open_id】=================================================//

	//如果曾经用qq联合登录过 则重新更新一下用户联合登录数据，然后登录。【解决以前没写好的地方】
	$sql1  = "select * from ".$GLOBALS['ecs']->table('users')." where user_name like '".$user_name."%' limit 1;";
	$user1 = $GLOBALS['db']->getRow($sql1);

	if(!empty($user1['user_id']))
	{
		//用户最早登录存在，更新该用户信息（处理最早登录用户的兼容处理）
		if($user1['user_name']==$user_name && $user1['email']==$email)
		{			
			$sqlu = "update ".$GLOBALS['ecs']->table('users')." set qq='$qq' where user_id=".$user1['user_id'];
		}
		else
		{
			$sqlu = "update ".$GLOBALS['ecs']->table('users')." set user_name='$user_name', email='$email', qq='$qq' where user_id=".$user1['user_id'];
		}
		mysql_query($sqlu);

		update_alias($user1['user_id'], $alias); //更新用户的昵称。
        
		if($user->login($user_name, $pwd))
		{	
			update_user_info();
			recalculate_price();
			update_refer_field($user_name, $open_id, 'qq'); //更新联合登录用户的open_id值。
			ecs_header("Location: ".$turn_url);			
		}
		else
		{
			union_login_fail();
		}
	}
	else
	{	
		//一直没有用qq联合登录过，则注册新用户信息，然后联合登录。
		if(!empty($user_name) && !empty($pwd) && !empty($email))
		{
			$email = str_replace('@qq.com', '@qq.login.com', $email);

			if($_REQUEST['step']=='ununion')		//不绑定账号登录(老方法)
			{
				//1.该用户名被使用则新增会员 用户名_1
				if(check_user($user_name) && !have_email($email))
				{
					$user_name = $user_name.'_1';
					$email     = '1'.$email;
				}
				//2.该注册邮箱被使用则新增会员 用户名_2
				if(!check_user($user_name) && have_email($email))
				{			
					$user_name = $user_name.'_2';
					$email     = '2'.$email;
				}
				//3.注册新用户
                
				if(register($user_name, $pwd, $email) !== false)
				{
					if($user->login($user_name, $pwd))
					{	
						update_user_info();
						recalculate_price();
						update_refer_field($user_name, $open_id, 'qq');
						//更新用户的昵称。
						$yi_user_id = $GLOBALS['db']->getOne("select user_id from ecs_users where user_name='$user_name' and referer='qq' limit 1");
						update_alias($yi_user_id, $alias); 
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
			elseif($_REQUEST['step']=='bind_user')	//绑定用户信息
			{
				$fname = isset($_REQUEST['user_name'])? addslashes($_REQUEST['user_name']): '';
				$pwd   = isset($_REQUEST['pwd'])? trim($_REQUEST['pwd']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel   = isset($_REQUEST['tel'])?	addslashes($_REQUEST['tel']): '';
				$fopen = isset($_REQUEST['open_id'])? trim($_REQUEST['open_id']): '';

				//yi：验证注册信息.
				$can_reg = true;
				if(empty($fname) || empty($pwd) || empty($email) || empty($fopen) || $fopen!=$open_id)
				{
					$can_reg = false;
				}
				if(hv_user_name($fname) || hv_email($email))
				{
					$can_reg = false;
				}

				if($can_reg)
				{
					if(register($fname, $pwd, $email) !== false)
					{
						if($user->login($fname, $pwd))
						{	
							update_user_info();
							recalculate_price();

							//更新联合登录信息
							$sql = "update ecs_users set referer='qq', refer_id='$open_id', union_login_bind=1, mobile_phone='$tel', alias='$alias'  where user_name='$fname' limit 1;";
							mysql_query($sql);

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
					union_login_fail();
				}
			}
			else //yi:新qq联合登录.
			{				
				$smarty->assign('open_id', $open_id); 
				$smarty->assign('alias',   $alias);
				$smarty->assign('url',     $_SERVER['QUERY_STRING']);
                //header("location:callback.php?step=ununion&".$_SERVER['QUERY_STRING']);
				//$smarty->display('bind.dwt');		
                
                
                //1.该用户名被使用则新增会员 用户名_1
				if(check_user($user_name) && !have_email($email))
				{
					$user_name = $user_name.'_1';
					$email     = '1'.$email;
				}
				//2.该注册邮箱被使用则新增会员 用户名_2
				if(!check_user($user_name) && have_email($email))
				{			
					$user_name = $user_name.'_2';
					$email     = '2'.$email;
				}
				//3.注册新用户
                
				if(register($user_name, $pwd, $email) !== false)
				{
					if($user->login($user_name, $pwd))
					{	
						update_user_info();
						recalculate_price();
						update_refer_field($user_name, $open_id, 'qq');
						//更新用户的昵称。
						$yi_user_id = $GLOBALS['db']->getOne("select user_id from ecs_users where user_name='$user_name' and referer='qq' limit 1");
						update_alias($yi_user_id, $alias); 
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


/*=======================================================================【函数】==============================================================================*/

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:查找用户名
 * ----------------------------------------------------------------------------------------------------------------------
 */
function hv_user_name($user_name='')
{
	$res = false;
	if(!empty($user_name))
	{
		$res = $GLOBALS['db']->getOne("select user_id from ecs_users where user_name='$user_name' limit 1;");
	}
	return ($res)? true: false; 
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:查找注册邮箱
 * ----------------------------------------------------------------------------------------------------------------------
 */
function hv_email($email='')
{
	$res = false;
	if(!empty($email))
	{
		$res = $GLOBALS['db']->getOne("select user_id from ecs_users where email='$email' limit 1;");
	}
	return ($res)? true: false; 
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:更新用户的昵称（只限qq登录的用户）
 * ----------------------------------------------------------------------------------------------------------------------
 */
function update_alias($user_id=0, $alias='')
{
	if(empty($user_id)){return false;}

	//判断qq登录用户是否有别名。没有则增加昵称。
	if(!empty($alias))
	{
		//取得现在的用户的别名
		$talias = $GLOBALS['db']->getOne("select alias from ecs_users where user_id='$user_id' limit 1;");

		if(!empty($talias))
		{			
			if($talias != $alias)
			{
				//原先有昵称，如果最新昵称修改了 则更新最新的昵称.
				$sql = "update ecs_users set alias='$alias' where user_id='$user_id' limit 1;";
				mysql_query($sql);
			}
		}
		else
		{
			//原先没有昵称 则更新昵称
			$sql = "update ecs_users set alias='$alias' where user_id='$user_id' limit 1;";
			mysql_query($sql);
		}
	}
}


/* ------------------------------------------------------------------------------------------------------
 * 判断这个用户的唯一码是否存在，存在返回true，不存在返回false。referer:'qq'，refer_id:qq的open_id。
 * ------------------------------------------------------------------------------------------------------
 * referer:会员来源代码，refer_id:用户在来源网站的唯一身份标识码。
 */
function refer_user_exist($referer, $refer_id)
{
	if(empty($refer_id)){return false;}
	$user_id = $GLOBALS['db']->getOne("select user_id from ecs_users where referer='".$referer."' and refer_id='".$refer_id."' limit 1;");
	return empty($user_id)? false: true;
}


/* -------------------------------------------------------------------------------------------------
 * 局部更新外部会员字段 在ecs_users表中referer, refer_id字段。
 * -------------------------------------------------------------------------------------------------
 * $user_name：我们商城用户名(唯一)。
 */
function update_refer_field($user_name, $refer_id, $refer='')
{
	if(!empty($refer_id) && !empty($user_name))
	{
		$sql = "update ecs_users set referer='".$refer."', refer_id='".$refer_id."' where user_name='".$user_name."' limit 1;";
		mysql_query($sql);
	}
	else
	{
		return false;
	}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断用户名是否已经存在
 * ----------------------------------------------------------------------------------------------------------------------
 */
function check_user($user_name)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'" limit 1;';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){ return true;}else{ return false;}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断用户邮箱是否已注册
 * ----------------------------------------------------------------------------------------------------------------------
 */
function have_email($email)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where email="'.$email.'" limit 1;';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){ return true;}else{ return false;}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:判断联合登录的qq是否已经联合登录过了，用户联合登录的qq字段是唯一。取自open_id字段。唯一对应qq字段。
 * ----------------------------------------------------------------------------------------------------------------------
 */
function have_qq($qq)
{
	if(empty($qq))
	{
		return false;
	}
	else
	{
		$qq = trim($qq);
	}
	$row = $GLOBALS['db']->getOne("select user_id from ".$GLOBALS['ecs']->table('users')." where qq='$qq' limit 1;");
	if(!empty($row)){ return true;}else{ return false;}
}

/* -------------------------------------------------------------------------------------------------
 * 联合登录失败的 信息提示
 * -------------------------------------------------------------------------------------------------
 */
function union_login_fail()
{
	//联合登录失败，引导用户在本站注册购买。
	$content = '很抱歉！QQ联合登录失败，建议您直接注册登录！';
	show_message_any_dir($content, $links = '10秒快速注册', $hrefs = '../user_register.html', $type = 'info', $auto_redirect = true);
}