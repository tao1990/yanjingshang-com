<?php
/**
 * 用户相关函数
 * @version 2014
 * @author xuyizhi
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 检查注册用户名是否合法
 */
function check_username($username='')
{
	$arr = array('usable' => TRUE, 'error_info' => '');
	
	if ( ! empty($username))
	{
		if (strlen($username) < 3)
		{
			$arr = array('usable' => FALSE, 'error_info' => '用户名过短');
			return $arr;
		}
		if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username))
        {
            $arr = array('usable' => FALSE, 'error_info' => '包含非法字符');
            return $arr;
        }
        if (same_name_of_admin(addslashes($username)))
	    {
	        $arr = array('usable' => FALSE, 'error_info' => '该用户名已存在');
	        return $arr;
	    }
		if (same_name_of_user(addslashes($username)))
	    {
	        $arr = array('usable' => FALSE, 'error_info' => '该用户名已存在');
	        return $arr;
	    }
	}
	else 
	{
		$arr = array('usable' => FALSE, 'error_info' => '用户名不得为空');
	}
	
	return $arr;
}

/**
 * 检查注册用户的密码是否符合规范
 */
function check_password($password='')
{
	$arr = array('usable' => TRUE, 'error_info' => '');
	
	if ( ! empty($password))
	{
		if (strlen($password) < 6)
		{
			$arr = array('usable' => FALSE, 'error_info' => '密码不应小于6位');
		}
		if (strpos($password, ' ') > 0)
		{
			$arr = array('usable' => FALSE, 'error_info' => '密码不得包含空格');
		}
	}
	else 
	{
		$arr = array('usable' => FALSE, 'error_info' => '密码不得为空');
	}
	
	return $arr;
}

/**
 * 检查注册email是否合法
 */
function check_email($email='')
{
	$arr = array('usable' => TRUE, 'error_info' => '');
	
	if ( ! empty($email))
	{
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$arr = array('usable' => FALSE, 'error_info' => '错误的邮箱格式');
		}
		if (same_email($email))
	    {
	        $arr = array('usable' => FALSE, 'error_info' => '该邮箱已存在');
	        return $arr;
	    }
	}
	else 
	{
		$arr = array('usable' => FALSE, 'error_info' => 'email不得为空');
	}
	
	return $arr;
}

/**
 * 检查注册用户名是否和管理员同名
 */
function same_name_of_admin($username='')
{
    $res = $GLOBALS['db']->getOne("SELECT COUNT(*) AS num FROM " . $GLOBALS['ecs']->table('admin_user') . " WHERE user_name = '$username'");
    return $res;
}

/**
 * 检查注册用户名是否有同名
 */
function same_name_of_user($username='')
{
    $res = $GLOBALS['db']->getOne("SELECT COUNT(*) AS num FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_name = '$username'");
    return $res;
}

/**
 * 检查注册email是否有同名
 */
function same_email($email='')
{
	$res = $GLOBALS['db']->getOne("SELECT COUNT(*) AS num FROM " . $GLOBALS['ecs']->table('users') . " WHERE email = '$email'");
    return $res;
}

/**
 * 新增用户
 */
function add_new_user($username='', $password='', $email='')
{
	$reg_date = time();
	$ip = real_ip();
	$password = md5($password);
	
	$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table("users") . 
			"(`email`, `user_name`, `password`, `reg_time`, `last_login`, `last_ip`) 
			VALUES ('$email', '$username', '$password', '$reg_date', '$reg_date', '$ip')";
	$res = $GLOBALS['db']->query($sql);
	
	if ($res)
	{
		//设置成登录状态
		$GLOBALS['user']->set_session($username);
		$GLOBALS['user']->set_cookie($username);
		
		//积分
		if ( ! empty($GLOBALS['_CFG']['register_points']))
        {
            log_account_change($_SESSION['user_id'], 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], $GLOBALS['_LANG']['register_points']);
        }
        
        return TRUE;
	}
}

/**
 * 用户登录验证
 */
function user_login($username='', $password='')
{
	if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username))
	{
		return FALSE;
	}
	else 
	{
		$username = addslashes($username);
		
		$user = $GLOBALS['db']->getRow("SELECT user_id, email, user_name, alias FROM " . $GLOBALS['ecs']->table('users') . " WHERE (user_name = '".$username."' OR email = '".$username."') AND password = '".md5($password)."'");
		if (empty($user))
		{
			return FALSE;
		}
		else 
		{
			//设置成登录状态
			//$GLOBALS['user']->set_session($username);
			//$GLOBALS['user']->set_cookie($username);
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['username'] = $user['user_name'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['alias'] = $user['alias'];
			
			//更新用户购物车session_id,同时更改未登录时加入购物车的信息的user_id
			$GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('carts') . " SET session_id = '" .SESS_ID . "' WHERE user_id = '" .$_SESSION['user_id'] . "'");
			$GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('carts') . " SET user_id = '" .$_SESSION['user_id'] . "' WHERE user_id = 0 AND (session_id = '".SESS_ID."' OR session_id = '".$_COOKIE['cart_session_id']."')");	
			
			return TRUE;
		}
	}
}

/**
 * 用户退出
 */
function user_logout()
{
	$GLOBALS['user']->set_session();
	$GLOBALS['user']->set_cookie();
	return TRUE;
}

/**
 * 获取用户的收货地址
 */
function get_user_address ($user_id=0)
{
    return $GLOBALS['db']->getAll("SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE user_id = ".intval($user_id)." LIMIT 10");
}

/**
 * 获取用户默认收货地址ID
 */
function get_user_default_address_id($user_id=0)
{
    return $GLOBALS['db']->getOne("SELECT address_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = " . intval($user_id));
}

/**
 * 获取指定address_id的收货地址
 */
function get_the_address($address_id=0)
{
    return $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE address_id  = ".intval($address_id));
}

/**
 * 获取地区名称
 */
function get_area_name_for_cart($region_id=0)
{
    return $GLOBALS['db']->GetOne('SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . ' WHERE region_id = ' . intval($region_id));
}

function get_user_usable_bonus($user_id=0)
{
	
}
