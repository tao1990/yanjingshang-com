<?php
/*=========================================================================会员中心 2011-04-08 yi=========================================================================*/
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
require(dirname(__FILE__).'/includes/lib_user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/common.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
date_default_timezone_set('PRC');
error_reporting(E_ALL ^ E_NOTICE);
global $back_act_in;
$user_id = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']): 0;
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

$user_info = get_user_info($user_id);
$smarty->assign('user',			$user_info);

//不需要登录的操作或自己验证是否登录（如ajax处理）的action
$not_login_arr = array('login','act_login','register','act_register','act_edit_password','get_password','send_pwd_email','password', 'signin', 'add_tag', 'collect', 'return_to_cart',
                        'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email', 'ck_user_name', 'clear_history','qpassword_name',
                        'get_passwd_question', 'check_answer', 'ajax_login', 'ajax_register','if_email_ck','msgSend','check_code', 'check_login', 'msgSendReg', 'check_code_reg', 'get_users',
                        'is_registered_new');

//显示页面的action列表
$ui_arr = array('register', 'login', 'profile', 'order_list', 'order_detail', 'address_list', 'collection_list', 'message_list', 'tag_list', 'get_password', 'reset_password',
				'booking_list', 'add_booking', 'account_raply','account_deposit', 'account_log', 'account_detail', 'act_account', 'pay', 'default', 'bonus',
				'group_buy', 'group_buy_detail', 'affiliate', 'comment_list','validate_email','track_packages', 'transform_points','qpassword_name','get_passwd_question',
				'check_answer','member_rank','resetpw','have_buy', 'receipt', 'ajax_add_receipt', 'ajax_edit_receipt', 'edit_receipt', 'msg', 'msg_info', 'user_msg_remove','account_list');


//不需要通过账号验证的操作
$not_ck_arr =array('store_sub','act_store_sub','cancel_sub','storeInfo');

//未登录处理
if(empty($_SESSION['user_id']))
{
    if(!in_array($action, $not_login_arr))
    {
        if(in_array($action, $ui_arr))
        {
            /* 如果需要登录,并是显示页面的操作，记录当前操作，用于登录后跳回原来页面
            if($action == 'login')
            {
                if(isset($_REQUEST['back_act']))
                {
                    $back_act = trim($_REQUEST['back_act']);
                }
            }else{}*/

            if(!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'user.php?' . $_SERVER['QUERY_STRING'];
            }
            $action = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
    $smarty->assign('ur_here', '登录');
    $smarty->assign('page_title', '登录 - 易视网手机版');
}else{
    if(!in_array($action,$not_ck_arr)){
        index_unck_display(2);    
    }
}

// 如果是显示页面，对页面进行相应赋值
if(in_array($action, $ui_arr))
{
	// 页头页尾已显示
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
}

// 用户中心欢迎页【用户中心登录成功后的默认页面】
if($action == 'default')
{
    include_once(ROOT_PATH .'includes/lib_clips.php');
	include_once(ROOT_PATH .'includes/lib_transaction.php');

    if($rank = get_rank_info())
    {
        $smarty->assign('rank_name', $rank['rank_name']);//用户等级
        if(!empty($rank['next_rank_name']))
        {
            $smarty->assign('next_rank_point', $rank['next_rank']);
			$smarty->assign('next_rank_name',  $rank['next_rank_name']);
        }
    }

    $smarty->assign('info',        get_user_default($user_id));
    $smarty->assign('prompt',      get_user_prompt($user_id));
    $smarty->assign('user_info',    get_user_info());
	// 用户最近3个订单
	$smarty->assign('order_list', get_user_orders($user_id,3,0) );
	$smarty->assign('ur_here',              '用户中心' );

    $smarty->assign('page_title', "用户中心 - 易视网手机版");
    $smarty->display('user_center.dwt');
}

//*=====================================================注册会员================================================================*//
if($action == 'register')
{	
    if(!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

	//active registe
	$from = isset($_REQUEST['from']) ? trim($_REQUEST['from']): '';
	if(!empty($from))
	{
		$smarty->assign('from',       $from);
	}
    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    /* 增加是否关闭注册 */
    $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
	//$smarty->assign('back_act', $back_act);

    $smarty->assign('ur_here', "注册");
    $smarty->assign('page_title', "注册 - 易视网手机版");
    $smarty->display('user_passport.dwt');
}
//==================================注册会员【功能实现】==================================||
elseif($action == 'act_register')
{
    //【增加】是否关闭注册会员功能
    if($_CFG['shop_reg_closed'])
    {die('close');
        $smarty->assign('action',          'register');
        $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
        $smarty->display('user_passport.dwt');
    }
    else
    {
        include_once(ROOT_PATH . 'includes/lib_passport.php');
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $confirm_password = isset($_POST['password2']) ? trim($_POST['password2']) : '';
        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
        //$email    = isset($_POST['email'])    ? trim($_POST['email']) : '';
        //$other['msn'] = isset($_POST['extend_field1']) ? $_POST['extend_field1'] : '';
        //$other['qq']  = isset($_POST['extend_field2']) ? $_POST['extend_field2'] : '';
        //$other['office_phone'] = isset($_POST['extend_field3']) ? $_POST['extend_field3'] : '';
        //$other['home_phone']   = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';
        //$other['mobile_phone'] = isset($_POST['extend_field5']) ? $_POST['extend_field5'] : '';
        //$sel_question  = empty($_POST['sel_question']) ? '' : $_POST['sel_question'];
        //$passwd_answer = isset($_POST['passwd_answer']) ? trim($_POST['passwd_answer']) : '';
        $phone = preg_match( "/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/", $username);
        if($phone){//如果是手机则写入mobile_phone
                $other['mobile_phone'] = $username;
                $type     = 1;  
        }
     
        if ($password!=$confirm_password)
        {
            show_message_wap('两次输入的密码不一致');
        }
        
        if (strlen($password) < 6)
        {
            show_message_wap($_LANG['passport_js']['password_shorter']);
        }

        if (strpos($password, ' ') > 0)
        {
            show_message_wap($_LANG['passwd_balnk']);
        }
        // 判断是否启用验证码功能
        /*if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
        {
            if (empty($_POST['captcha']))
            {
                show_message_wap($_LANG['invalid_captcha'], $_LANG['sign_up'], 'user.php?act=register', 'error');
            }
            //检查验证码
            include_once('includes/cls_captcha.php');
            $validator = new captcha();
            if(!$validator->check_word($_POST['captcha']))
            {
                show_message_wap($_LANG['invalid_captcha'], $_LANG['sign_up'], 'user.php?act=register', 'error');
            }
        }*/

    
		//*==================注册新用户==================*//
        if(register_wap($username, $password, $email, $other,$type) !== false)
        {
        
            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id'; //读出所有自定义扩展字段的id
            $fields_arr = $db->getAll($sql);

            $extend_field_str = '';    //生成扩展字段的内容字符串
            foreach ($fields_arr AS $val)
            {
                $extend_field_index = 'extend_field' . $val['id'];
                if(!empty($_POST[$extend_field_index]))
                {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . $temp_field_content . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str)      //插入注册扩展数据
            {
                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $db->query($sql);
            }

            /* 写入密码提示问题和答案 */
            if (!empty($passwd_answer) && !empty($sel_question))
            {
                $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
                $db->query($sql);
            }
			//yi:会员是多重来源的情况处理。
			if(isset($_COOKIE['refer']) && $_COOKIE['refer']=='at_school')
			{
                $sql = "UPDATE ".$ecs->table('users')." SET answer='at_school' WHERE `user_id`='".$_SESSION['user_id']."' limit 1;";
                $db->query($sql);
			}
            $ucdata = empty($user->ucdata)? "" : $user->ucdata;

			 show_message_wap('注册成功', $_LANG['back_up_page'], './', 'info', true);
			
        }
        else
        {
            $err->show($_LANG['sign_up'], 'user.php?act=register');
        }
    }
}

//==================================验证用户注册邮箱是否有效==================================||
elseif($action == 'validate_email')
{
    $hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);
    if($hash)
    {
        include_once(ROOT_PATH . 'includes/lib_passport.php');
        $id = register_hash('decode', $hash);
        if($id > 0 && is_numeric($id))
        {
			//yi:用户是否已经验证过邮箱了
			$email_ck = $GLOBALS['db']->GetRow("select email_ck, email from ecs_users where user_id='$id' limit 1;");
			if(1==$email_ck['email_ck'])
			{
				show_message_wap("您的邮箱【".$email_ck['email']."】已经验证过了，请勿重复验证。", $_LANG['profile_lnk'], 'user.html');
			}
			else
			{
				$sql = "UPDATE ".$ecs->table('users')." SET is_validated=1, email_ck=1 WHERE user_id='$id' limit 1;";
				$res = $db->query($sql);

				if($res)
				{
					if(!function_exists(log_account_change))
					{
						include_once(ROOT_PATH.'includes/lib_common.php');
					}
					$jf_num = 300;
					$desc   = "【".date('Y年m月d日 H时i分')."】会员验证注册邮箱成功，奖励".$jf_num."积分。";
					log_account_change($id, 0, 0, 0, $jf_num, $desc);
				}

				$sql = 'SELECT user_name, email FROM ' . $ecs->table('users') . " WHERE user_id = '$id'";
				$row = $db->getRow($sql);
				show_message_wap(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),$_LANG['profile_lnk'], 'user.php');
			}
        }
    }
    show_message_wap($_LANG['validate_fail']);
}
//==================================验证用户注册用户名是否可以注册（新 zhang:160701）==================================||
elseif ($action == 'is_registered_new')
{
    $mobile    = $_GET['mobile'];
    $sql = "SELECT count(*) FROM ecs_users WHERE (mobile_phone = ".$mobile." OR user_name = ".$mobile.") AND shop_id = 2";
    $res = $GLOBALS['db']->GetOne($sql);
    if ($res > 0)
    {
        echo 0;
    }
    else
    {
        echo 1;
    }
}
//==================================验证用户注册用户名是否可以注册==================================||
elseif ($action == 'is_registered')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $username = trim($_REQUEST['username']);
	$username = str_replace("select", "", $username);//yi

	$username = json_str_iconv($username);
    if ($user->check_user($username) || admin_registered($username))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}
elseif ($action == 'ck_user_name')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $username = trim($_REQUEST['username']);
	$username = str_replace("select", "", $username);//yi

    if ($user->check_user($username) || admin_registered($username))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}
//==================================验证用户邮箱地址是否被注册==================================||
elseif($action == 'check_email')
{
    $email = trim($_REQUEST['email']);
    if($user->check_email($email))
    {
        echo 'false';
    }
    else
    {
        echo 'ok';
    }
}
//==================================用户登录【界面】==================================||
elseif($action == 'login')
{
    if(isset($GLOBALS['_SERVER']['HTTP_REFERER'])){
        //$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './' : $GLOBALS['_SERVER']['HTTP_REFERER'];
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? 'http://m.easeeyes.com/user.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }else{
        $back_act = 'http://m.easeeyes.com/user.php';
    }
    if($_GET['ex_back'] > 0){
        $back_act = 'ex_coupon.php?coupon_id='.$_GET['ex_back'];
    }
	//注:登录界面无需记录自动登录信息，已登录用户无登录页面。
    $smarty->assign('back_act', $back_act);
    $smarty->assign('page_title', "登录 - 易视网手机版");
    $smarty->display('user_passport.dwt');
}
//==================================用户登录【功能】==================================||
elseif($action == 'act_login')
{
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
    
    $account_choose = isset($_REQUEST['account_choose']) ? $_REQUEST['account_choose'] : '';
    if($account_choose == 1 && $_GET['pwd']){
        $username = isset($_GET['uname']) ? trim($_GET['uname']) : '';
        $password = isset($_GET['pwd']) ? trim(base64_decode($_GET['pwd'])) : '';
        $is_direct_login = $GLOBALS['db']->GetOne("SELECT user_id FROM ecs_users WHERE user_name = '" . $username . "' AND password = '" .md5($password) . "'");
        if(!$is_direct_login){
            $url = "user.php?uname=".$username."&account_choose=1";
            ecs_header("Location:".$url."\n");die;
        }
    }
	//=============================yi:下次自动登录【功能设置】=============================||
	if( !empty($username) && !empty($password))
	{
		$auto_login = isset($_POST['autologin']) ? trim($_POST['autologin']) : '';
        
		if($auto_login == 'on')
		{
			setcookie("uname",$username,time()+24*3600*30);
			setcookie("upass",$password,time()+24*3600*30);
		}
	}
	//=============================yi:下次自动登录【end】==================================||

    $login_wap = $user->login_wap($username, $password, $account_choose);
    if($login_wap){
        if(count($login_wap)>1){
            $_SESSION['back_act'] = $back_act;
            //返回多个账号，跳转到列表页面选择账号重新登陆
            $smarty->assign('action','account_list');
            $smarty->assign('account_list', $login_wap);
            $smarty->assign('pwd', base64_encode($password));
            $smarty->display('user_passport.dwt');
        }else{
            update_user_info();
            recalculate_price();
            $ucdata = isset($user->ucdata)? $user->ucdata : '';
            
            //登录成功 注册会员升级普通会员
            if(time()>strtotime('2016-09-13 00:00:00') && time()<strtotime('2016-09-22 00:00:00') && $_SESSION['user_rank'] == 1){
                
                $rank_points_now = $GLOBALS['db']->getOne("SELECT rank_points FROM ecs_users WHERE user_id = ".$_SESSION['user_id']);
                $rank_points = 1001 - $rank_points_now;
                log_account_change($_SESSION['user_id'], 0, 0, intval($rank_points), 0, '登录会员升级-WAP');
                $GLOBALS['db']->query("UPDATE ecs_users SET user_rank = 2 WHERE user_id = ".$_SESSION['user_id']);
                $_SESSION['user_rank'] = 2;
            }
  		    if($_SESSION['back_act'] != ""){$back_act = $_SESSION['back_act'];}
    		if(stristr($back_act,".html")!= false || stristr($back_act,".php")!= false){
    			ecs_header("Location:".$back_act."\n");
    		}else{
    			ecs_header("Location: ./\n");
    		}
        }  
        
    }else{
		//登录失败
        $_SESSION['login_fail'] ++ ;
        //echo '登陆失败!';
        show_message_wap($_LANG['login_failure'], $_LANG['relogin_lnk'], '', 'error',true);
    }
}
//==================================异步验证登陆状态==================================||
elseif($action == 'check_login')
{
    $username  = isset($_GET['username']) ? trim($_GET['username']) : '';
    $password1 = isset($_GET['password']) ? trim($_GET['password']) : '';
    $password  = md5($password1);
    $sql = "SELECT user_id FROM ecs_users WHERE (user_name = '" . $username . "' OR mobile_phone = '" . $username . "' OR email = '" . $username . "') AND password = '" .$password . "'";
    $res = $GLOBALS['db']->GetOne($sql);
    if($res){
        echo 1; die;
    }else{
        echo 2; die;
    }
}
elseif($action == 'if_email_ck')
{
	//fn:用户邮箱是否验证过
	$u_id	  = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$email_ck = $GLOBALS['db']->GetOne("select email_ck from ecs_users where user_id='$u_id' limit 1;");
	echo $email_ck;
}
//==================================处理ajax登录请求【功能】==================================||
elseif($action == 'signin')
{
    include_once('includes/cls_json.php');
    $json = new JSON;

    $username = !empty($_POST['username']) ? json_str_iconv(trim($_POST['username'])) : '';
    $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
    $captcha  = !empty($_POST['captcha'])  ? json_str_iconv(trim($_POST['captcha'])) : '';
    $result   = array('error' => 0, 'content' => '');

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($captcha))
        {
            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }
        //检查验证码
        include_once('includes/cls_captcha.php');
        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }
    }

    if ($user->login($username, $password))
    {
        update_user_info();  //更新用户信息
        recalculate_price(); //重新计算购物车中的商品价格
        $smarty->assign('user_info', get_user_info());
        $ucdata = empty($user->ucdata)? "" : $user->ucdata;
        $result['ucdata'] = $ucdata;
        $result['content'] = $smarty->fetch('library/member_info.lbi');
    }
    else
    {
        $_SESSION['login_fail']++;
        if ($_SESSION['login_fail'] > 2)
        {
            $smarty->assign('enabled_captcha', 1);
            $result['html'] = $smarty->fetch('library/member_info.lbi');
        }
        $result['error']   = 1;
        $result['content'] = $_LANG['login_failure'];
    }
    die($json->encode($result));
}
//==================================退出会员中心【注销登录】==================================||
elseif($action == 'logout')
{
    if(!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])){
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }
    $user->logout();
    $ucdata = empty($user->ucdata)? "" : $user->ucdata;

	//注销登录后 删除自动登录参数
	setcookie("uname",$username,time()-800);
	setcookie("upass",$password,time()-800);

	//注销登录后 跳转页面
	if(stristr($back_act,".html")!= false || stristr($back_act,".php")!= false){
		ecs_header("Location:".$back_act."\n");
	}else{
		ecs_header("Location: ./\n");
	}
}
/* 密码找回-->重置密码界面 */
elseif ($action == 'get_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    if($_POST) // 表单提交
    {
        $username = $_POST['user_name'];
        $password = md5($_POST['password']);
        $sql = "UPDATE ecs_users SET password = '".$password."' where user_name = '".$username."'";
        $res = $GLOBALS['db']->query($sql);
        if($res){
            echo 1; die;
        }
    }
    else
    {
        //显示用户名和email表单
        $smarty->assign('ur_here', '找回密码');
        $smarty->assign('page_title', '找回密码 - 易视网手机版');
        $smarty->display('user_passport.dwt');
    }
}
/* 找回密码 - 获取用户信息*/
elseif($action == 'get_users'){
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $username = $_GET['username'];
    $sql = "SELECT user_id, user_name FROM ecs_users WHERE mobile_phone = '".$username."'";//." OR user_name = ".$mobile;
    $res = $GLOBALS['db']->GetAll($sql);
    if($res){
        echo '<ul class="items-account">';
        foreach($res as $k => $v){
            echo '
            <li><label for="zh_'.$v['user_id'].'"><input type="radio" value="'.$v['user_name'].'" name="username" id="zh_'.$v['user_id'].'" /><span class="checkbox"></span>'.$v['user_name'].'</label></li>
        ';
        }
        echo '</ul>';
        die;
    }else{
        echo "";die;
    }

}
/* 注册-->获取验证码 */
elseif ($action == 'msgSendReg')
{
    if (empty($_GET['captcha']))
    {
            echo 3;die;   
    }
    //检查验证码
    include_once('api/securimage/securimage.php');      
    $validator = new Securimage();
    $valid = $validator->check($_GET['captcha']);
    
    if($valid != true) {
        echo 4;die; 
    }
 
    $mobile    = is_numeric($_GET['mobile'])? $_GET['mobile'] : 0;
    
    $sql = "SELECT count(*) FROM ecs_users WHERE (mobile_phone = ".$mobile." OR user_name = ".$mobile.") AND shop_id = 2";
    $res = $GLOBALS['db']->GetOne($sql);
    
    if($res > 0){
        echo 1;die;
    }else{

        $code = rand(111111,999999);
        
        $msg_con = "验证码：".$code."，欢迎注册成为易视网客户，您本次的验证码有效期为30分钟，请您尽快使用。";
        
        //$mobile = '13774439513';
        
        // 发送短信接口
        include_once('../api/sms/sms.php');
        $statusCode = sms_send($mobile,$msg_con);
        //短信发送成功，记录到数据库中
        if($statusCode=='0'){
            $sql_sv  = "insert into ".$GLOBALS['ecs']->table('sms_verify')."(mobile,send_time,extension,extension_id) value(".$mobile.",".time().",'register','".$code."');";
            $sql_sms = "insert into ".$GLOBALS['ecs']->table('sms_log')."(log_time,order_sn,mobile,sms) value('".date('Y-m-d H:i:s',time())."',0,".$mobile.",'".$msg_con."');";
            $GLOBALS['db']->query($sql_sv);
            $GLOBALS['db']->query($sql_sms);
        }
        /*$sql_sv  = "insert into ".$GLOBALS['ecs']->table('sms_verify')."(mobile,send_time,extension,extension_id) value(".$mobile.",".time().",'register','".$code."');";
        $sql_sms = "insert into ".$GLOBALS['ecs']->table('sms_log')."(log_time,order_sn,mobile,sms) value('".date('Y-m-d H:i:s',time())."',0,".$mobile.",'".$msg_con."');";
        $GLOBALS['db']->query($sql_sv);
        $GLOBALS['db']->query($sql_sms);*/
        echo 2;
    }
}
/* 注册-->校验验证码 */
elseif ($action == 'check_code_reg')
{
    echo 1;die;
    $mobile = is_numeric($_GET['mobile'])? $_GET['mobile'] : 0;
    $code   = $_GET['code'];
    $time_z = time() - 30*60;
    $sql = "SELECT user_id,extension_id FROM ecs_sms_verify WHERE mobile = ".$mobile." and extension = 'register' and send_time > ".$time_z." order by send_time desc";
    $res = $GLOBALS['db']->GetRow($sql);
    if($code == $res['extension_id']){
        echo 1;
    }
}
/* 密码找回-->获取验证码 */
elseif ($action == 'msgSend')
{
    if (empty($_GET['captcha']))
    {
        echo 3;
        die;
    }
    //检查验证码
    include_once('api/securimage/securimage.php');
    $validator = new Securimage();
    $valid = $validator->check($_GET['captcha']);

    if($valid != true) {
        echo 4;die;
    }

    $mobile    = $_GET['mobile'];
    $user_name = $_GET['user_name'];
    if($user_name == "" || $mobile == ""){
        echo "系统出错，请刷新页面重试！";die;
    }else{
        $sqls = "SELECT user_name,user_id,mobile_phone FROM ecs_users WHERE user_name = '".$user_name."'";
        $result = $GLOBALS['db']->GetRow($sqls);
        if($result['mobile_phone'] == $mobile){
            $code = rand(111111,999999);
            $msg_con = "验证码：".$code."，尊敬的易视网客户，您本次的验证码有效期为30分钟，请您尽快使用。";
            // 发送短信接口
            include_once('../api/sms/sms.php');
            $statusCode = sms_send($mobile,$msg_con);
            //短信发送成功，记录到数据库中
            if($statusCode=='0'){
                $sql_sv  = "insert into ".$GLOBALS['ecs']->table('sms_verify')."(user_id,mobile,send_time,extension,extension_id) value(".$result['user_id'].",".$mobile.",".time().",'forget_pwd','".$code."');";
                $sql_sms = "insert into ".$GLOBALS['ecs']->table('sms_log')."(log_time,order_sn,mobile,sms) value('".date('Y-m-d H:i:s',time())."',0,".$mobile.",'".$msg_con."');";
                $GLOBALS['db']->query($sql_sv);
                $GLOBALS['db']->query($sql_sms);
            }
            /*$sql_sv  = "insert into ".$GLOBALS['ecs']->table('sms_verify')."(user_id,mobile,send_time,extension,extension_id) value(".$result['user_id'].",".$mobile.",".time().",'forget_pwd','".$code."');";
            $sql_sms = "insert into ".$GLOBALS['ecs']->table('sms_log')."(log_time,order_sn,mobile,sms) value('".date('Y-m-d H:i:s',time())."',0,".$mobile.",'".$msg_con."');";
            $GLOBALS['db']->query($sql_sv);
            $GLOBALS['db']->query($sql_sms);*/
            echo 2;die;
        }else{
            echo "系统出错，请刷新页面重试！";die;
        }
    }
}
/* 密码找回-->校验验证码 */
elseif ($action == 'check_code')
{
    $mobile = $_GET['mobile'];
    $code   = $_GET['code'];
    $time_z = time() - 30*60;
    $sql = "SELECT user_id,extension_id FROM ecs_sms_verify WHERE mobile = ".$mobile." and extension = 'forget_pwd' and send_time > ".$time_z." order by send_time desc";
    $res = $GLOBALS['db']->GetRow($sql);
    if($code == $res['extension_id']){
        echo 1;
    }
}
// 原重置密码
elseif ($action == 'get_password_old')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    if(isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid  = intval($_GET['uid']);

        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
            show_message_wap($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
        }

        $smarty->assign('uid',    $uid);
        $smarty->assign('code',   $code);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
    else
    {
        //显示用户名和email表单
        $smarty->display('user_passport.dwt');
    }
}

/* 密码找回-->输入用户名界面 */
elseif ($action == 'qpassword_name')
{
    //显示输入要找回密码的账号表单
    $smarty->display('user_passport.dwt');
}

/* 密码找回-->根据注册用户名取得密码提示问题界面 */
elseif ($action == 'get_passwd_question')
{
    if (empty($_POST['user_name']))
    {
        show_message_wap($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }
    else
    {
        $user_name = trim($_POST['user_name']);
    }

    //取出会员密码问题和答案
    $sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $ecs->table('users') . " WHERE user_name = '" . $user_name . "'";
    $user_question_arr = $db->getRow($sql);

    //如果没有设置密码问题，给出错误提示
    if (empty($user_question_arr['passwd_answer']))
    {
        show_message_wap($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }

    $_SESSION['temp_user'] = $user_question_arr['user_id'];  //设置临时用户，不具有有效身份
    $_SESSION['temp_user_name'] = $user_question_arr['user_name'];  //设置临时用户，不具有有效身份
    $_SESSION['passwd_answer'] = $user_question_arr['passwd_answer'];   //存储密码问题答案，减少一次数据库访问

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }

    $smarty->assign('passwd_question', $_LANG['passwd_questions'][$user_question_arr['passwd_question']]);
    $smarty->assign('page_title', "找回密码 - 易视网手机版");
    $smarty->display('user_passport.dwt');
}

/* 密码找回-->根据提交的密码答案进行相应处理 */
elseif ($action == 'check_answer')
{
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            show_message_wap($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }

        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            show_message_wap($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }
    }

    if (empty($_POST['passwd_answer']) || $_POST['passwd_answer'] != $_SESSION['passwd_answer'])
    {
        show_message_wap($_LANG['wrong_passwd_answer'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'info');
    }
    else
    {
        $_SESSION['user_id'] = $_SESSION['temp_user'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_user_name']);
        $smarty->assign('uid',    $_SESSION['user_id']);
        $smarty->assign('action', 'reset_password');
        $smarty->assign('page_title', "找回密码 - 易视网手机版");
        $smarty->display('user_passport.dwt');
    }
}

/* 发送密码修改确认邮件 */
elseif ($action == 'send_pwd_email')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    //用户邮件地址
    $email = !empty($_POST['email']) ? trim(addslashes($_POST['email'])) : '';	
	$res = email_exit($email);	
    if($res){
			
		$sqle = "select * from ".$GLOBALS['ecs']->table('users')." where email='".$email."' limit 1;";
		$rrs  = $GLOBALS['db']->GetAll($sqle);
		$user_info = $rrs[0];

        //生成code
        $code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);

        //发送邮件
        if(send_pwd_email($user_info['user_id'], $user_info['user_name'], $email, $code))
        {
            show_message2("重置密码的邮件已发到您的邮箱：".$email, $_LANG['back_home_lnk'], './', 'info');
        }
        else
        {
            //发送邮件出错
            show_message_wap($_LANG['fail_send_password'], $_LANG['back_page_up'], './', 'info');
        }
    }else{

        //您输入的邮箱不存在！
        show_message1("您填写的电子邮件地址不存在，请重新输入！", $_LANG['back_page_up'], './', 'info');
    }
}

/* 重置新密码 */
elseif ($action == 'reset_password')
{
    //显示重置密码的表单
    $smarty->display('user_passport.dwt');
}
/*yi----修改密码*/
elseif($action == 'resetpw')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $smarty->assign('ur_here','密码修改');
    $smarty->assign('page_title','密码修改 - 易视网手机版');
    $smarty->display('resetpw.dwt');
}

/* 修改会员密码 */
elseif ($action == 'act_edit_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $user_id      = isset($_POST['uid'])  ? intval($_POST['uid']) : $user_id;
    $code         = isset($_POST['code']) ? trim($_POST['code'])  : '';

    if(strlen($new_password) < 6)
    {
        show_message_wap($_LANG['passport_js']['password_shorter']);
    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录

    if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password)))
    {
        
        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))
        {
            $user->logout();
            show_message_wap($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info');
        }
        else
        {
            show_message_wap($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
        }
    }
    else
    {   
        show_message_wap($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
    }
}

//----------------------【查看订单列表(我的订单页)】-------------------------------//
elseif($action == 'order_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $where = "";
    $states   = isset($_REQUEST['states']) ? intval($_REQUEST['states']) : 0;
    if($states == '100'){                    // states=100  待付款状态
        $where .= " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED)) .
            " AND   {$alias}pay_status = '" . PS_UNPAYED . "'" .
            " AND ( {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " OR {$alias}pay_id " . db_create_in(pid_list(false)) . ") ";
    }elseif($states == '101'){              // states=101  待发货状态
        $where .= " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
            " AND   {$alias}shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_PART, SS_SHIPPED_ING)) .
            " AND ( {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " OR {$alias}pay_id " . db_create_in(pid_list(true)) . ") ";
    }elseif($states == '102'){              // states=102  已完成状态
        $where .= " AND {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
            " AND {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
            " AND {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " ";
    }else{                                  // 全部订单

    }
    $page   = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'". $where);
    $pager  = get_pager('user.php', array('act' => $action), $record_count, $page);

    //$orders = get_user_orders($user_id, $pager['size'], $pager['start'],$where);
    $orders = get_user_orders($user_id, 10, $pager['start'],$where);
    $merge  = get_user_merge($user_id);

    $smarty->assign('merge',  $merge);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->assign('states', $states);
    $smarty->assign('ur_here', "我的订单");
    $smarty->assign('page_title', "我的订单 - 易视网手机版");
    $smarty->display('order.dwt');
    //$smarty->display('user_transaction.dwt');
}
//----------------------【查看订单列表(我的订单页[翻页功能])】-------------------------------//
elseif($action == 'more')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $where = "";
    $states   = isset($_REQUEST['states']) ? intval($_REQUEST['states']) : 0;
    if($states == '100'){                    // states=100  待付款状态
        $where .=  " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED)) .
            " AND   {$alias}pay_status = '" . PS_UNPAYED . "'" .
            " AND ( {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " OR {$alias}pay_id " . db_create_in(pid_list(false)) . ") ";
    }elseif($states == '101'){              // states=101  待发货状态
        $where .= " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
            " AND   {$alias}shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_PART, SS_SHIPPED_ING)) .
            " AND ( {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " OR {$alias}pay_id " . db_create_in(pid_list(true)) . ") ";
    }elseif($states == '102'){              // states=102  已完成状态
        $where .= " AND {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
            " AND {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
            " AND {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " ";
    }else{                                  // 全部订单

    }
    $page   = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'". $where);

    $offset=($page-1)*10;

    $orders = get_user_orders($user_id, 10, $offset,$where);
    //var_dump($orders);die;
    foreach($orders as $v){
        $do = '';
        if($v['timeout'] == 1){
            $do .= '<a href="user.php?act=order_detail&order_id='.$v['order_id'].'" class="order_option_button2">查看订单</a>';
        }else{
            if($v['os'] != 2 && $v['os'] != 3 && $v['ps'] == 0){
                $do .= '<a href="user.php?act=order_detail&order_id='.$v['order_id'].'" class="order_option_button1">付款</a>';
            }
            $do .= '<a href="user.php?act=order_detail&order_id='.$v['order_id'].'" class="order_option_button2">查看订单</a>';
            if(($v['os'] == 1 || $v['os'] == 5) && $v['ss'] == 1){
                $do .= '<a href="wuliu.php?act=detail&order_id='.$v['order_id'].'" class="order_option_button2">跟踪包裹</a>';
            }
            if(($v['os'] == 1 || $v['os'] == 5) && $v['ss'] == 1 && $v['ps'] == 2){
                $do .= '<a class="order_option_button2" href="user.php?act=affirm_received&order_id='.$v['order_id'].'" onclick="if(!confirm('.'您确认已收到您的货物!'.'))return false;">确认收货</a>';
            }
            if($v['os'] == 0 && $v['ps'] == 0 && $v['ss'] == 0){
                $do .= '<a class="order_option_button1" href="user.php?act=cancel_order&order_id='.$v['order_id'].'" onclick="if(!confirm('.'您确实要取消该订单!'.'))return false;">取消</a>';
            }
        }

        echo '<tr>
            <td><a href="user.php?act=order_detail&order_id='.$v['order_id'].'" class="order_id_a" id="{$item.order_id}">'.$v['order_sn'].'</a></td>
            <td>'.$v['order_time'].'</td>
            <td>'.$v['total_fee'].'</td>
            <td>'.$v['order_status'].'</td>
            <td style="white-space:nowrap">'.$do.'</td>
        </tr>';
    }
    die;
}
//yi:ajax订单商品列表 #传入要查询的订单号
elseif ($action == 'ajax_order_list')
{
	 include_once('includes/cls_json.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');
	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //订单id
	/* 订单商品 */
    $goods_list = order_goods($order_id);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }

	//print_r($goods_list);
	//把订单商品返回到服务器端
	$json = new JSON;
    echo $json->encode($goods_list);
    exit;
}
//查看订单详情
elseif ($action == 'order_detail')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //订单id

    //订单详情(信息)
    $order = get_order_detail($order_id, $user_id);

	//把快递单号和 快递公司名剥离 并且去掉以前的超链接.
	$invoice = explode("-",$order['invoice_no_old']);
	if(count($invoice) > 1){		
		$order['invoice_number'] = $invoice[1];
	}

    if($order === false)
    {
        $err->show($_LANG['back_home_lnk'], './');
        exit;
    }

    /* 是否显示添加到购物车 */
    if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods')
    {
        $smarty->assign('allow_to_cart', 1);
    }

    /* 订单商品 */
    $goods_list = order_goods($order_id);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }

    /* 设置能否修改使用余额数 */
    if ($order['order_amount'] > 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)
        {
            $user = user_info($order['user_id']);
            if ($user['user_money'] + $user['credit_line'] > 0)
            {
                $smarty->assign('allow_edit_surplus', 1);
                $smarty->assign('max_surplus', sprintf($_LANG['max_surplus'], $user['user_money']));
            }
        }
    }

    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED)
    {
        $payment_list = available_payment_list(false, 0, true);

        /* 过滤掉当前支付方式和余额支付方式 */
        foreach ($payment_list as $key => $payment)
        {
            if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance')
            {
                unset($payment_list[$key]);
            }
        }
        $smarty->assign('payment_list', $payment_list);
    }

    /* 订单 支付 配送 状态语言项 */
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];

	//yi:配货地址-------------------------------------------------------------------------------- 

	$order['country']  = isset($order['country'])  ? intval($order['country'])  : 0;
	$order['province'] = isset($order['province']) ? intval($order['province']) : 0;
	$order['city']     = isset($order['city'])     ? intval($order['city'])     : 0;
	$province_list = get_regions(1, $order['country']);
	$city_list     = get_regions(2, $order['province']);
	$district_list = get_regions(3, $order['city']);

	//print_r($district_list);
	$smarty->assign('country_list',   get_regions());
	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list',     $city_list);
	$smarty->assign('district_list', $district_list);
	//var_dump($order);
	$smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));//地区列表
	//------------------------------------------------------------------------------------------------------------------------------------------------------------

	/*---------------------------------------收货人地址名字---------------------------------*/
	$order['provincena'] = get_regions_name($order['province']);
	$order['cityna']     = get_regions_name($order['city']);
	$order['districtna'] = get_regions_name($order['district']);
	/*---------------------------------------------------------------------------------------*/

	/*---------------------------------------取得订单操作记录-------------------------------*/
    $act_list = array();
    $sql = "SELECT * FROM ".$ecs->table('order_action')." WHERE order_id = '$order[order_id]' ORDER BY log_time DESC,action_id DESC";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        $row['order_status']    = $_LANG['os'][$row['order_status']];
        $row['pay_status']      = $_LANG['ps'][$row['pay_status']];
        $row['shipping_status'] = $_LANG['ss'][$row['shipping_status']];
        $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
        $act_list[] = $row;
    }
    $smarty->assign('action_list', $act_list);
	/*---------------------------------------------------------------------------------------*/

	/*---------------------------------------购物流程id-------------------------------------*/
	$order_flow = 1;
	if( $order['pay_id'] != 3){
		if($order['pay_status'] == '未付款'){
			$order_flow = 1;
		}elseif($order['pay_status'] == '已付款' && $order['shipping_status'] == '未发货'){
			$order_flow = 2;
		}elseif($order['pay_status'] == '已付款' && $order['shipping_status'] == '配货中'){
			$order_flow = 3;
		}elseif($order['pay_status'] == '已付款' && $order['shipping_status'] == '已发货'){
			$order_flow = 4;
		}elseif($order['pay_status'] == '已付款' && $order['shipping_status'] == '收货确认'){
			$order_flow = 5;
		}else{
		}
	}else{
		//货到付款
		if(trim($order['order_status'])=='未确认') 
		{
			$order_flow = 11;//未确认
		}
		elseif(trim($order['order_status'])=='已取消'||trim($order['order_status'])=='无效')
		{
			$order_flow = 12;//已取消
		}
		elseif(trim($order['order_status'])=='退货')
		{
			$order_flow = 14;//退货
		}
		else
		{
			//已确认|已分单
			if($order['shipping_status'] == '未发货'){
				$order_flow = 1;
			}elseif($order['shipping_status'] == '配货中'){
				$order_flow = 2;
			}elseif($order['shipping_status'] == '已发货'){
				$order_flow = 3;
			}elseif($order['shipping_status'] == '收货确认'){
				$order_flow = 4;
			}else{
			}
		}
	}
	$order['order_flow'] = $order_flow;
	/*---------------------------------------------------------------------------------------*/
    if($order['pay_id'] == 4 && $order['pay_status'] == '未付款'){//支付宝支付且未支付的订单
        $order['timeout'] = time()-$order['add_time']>7200? 1:0;
    }

	$smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('ur_here', "订单详情");
    $smarty->assign('page_title', "订单详情 - 易视网手机版");
    $smarty->display('order_detail.dwt');
}

/* 取消订单 */
elseif ($action == 'cancel_order')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (cancel_order($order_id, $user_id))
    {
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

//收货地址列表界面(管理收货地址)
elseif ($action == 'address_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
	$add_id =  isset($_GET['id']) ? intval($_GET['id']) : 0;//修改的地址的id


    $smarty->assign('lang',  $_LANG);

    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

    /* 获得用户所有的收货人信息 */
    $consignee_list = get_consignee_list($_SESSION['user_id']);
	$add_dis = array();//显示的地址

    $add_list_num = 0;//地址列表中的顺序.
	//yi重新修改地址管理
	if($add_id > 0){
		foreach($consignee_list as $key => $val){
			if($consignee_list[$key]['address_id'] == $add_id){
				$add_dis = $consignee_list[$key];
				$add_list_num = $key;
			}
		}		
	}else{
		//空白显示.
	}
	$smarty->assign('add_display',    $add_dis);//显示地址

    //取得国家列表，如果有收货人列表，取得省市区列表
    foreach ($consignee_list AS $region_id => $consignee)
    {
        $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
        $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
        $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

        $province_list[$region_id] = get_regions(1, $consignee['country']);
        $city_list[$region_id]     = get_regions(2, $consignee['province']);
        $district_list[$region_id] = get_regions(3, $consignee['city']);

		//yi:所在地区显示出来.
		$consignee_list[$region_id]['provincena'] = get_regions_name($consignee_list[$region_id]['province']);
		$consignee_list[$region_id]['cityna']     = get_regions_name($consignee_list[$region_id]['city']);
		$consignee_list[$region_id]['districtna'] = get_regions_name($consignee_list[$region_id]['district']);	
    }

    $smarty->assign('consignee_list', $consignee_list);

	//yi:默认地址功能
    $address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");

	//yi:默认邮箱功能
	$default_email = $db->getOne("SELECT email FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");

    //赋值于模板
    $smarty->assign('real_goods_count', 1);
    $smarty->assign('shop_country',     $_CFG['shop_country']);
    $smarty->assign('shop_province',    get_regions(1, $_CFG['shop_country']));

    $smarty->assign('province_list',    $province_list[$add_list_num]);
    $smarty->assign('city_list',        $city_list[$add_list_num]);
    $smarty->assign('district_list',    $district_list[$add_list_num]);

	$smarty->assign('default_add',      $address_id); //默认地址id
	$smarty->assign('default_email',    $default_email); //默认email

    $smarty->assign('currency_format',  $_CFG['currency_format']);
    $smarty->assign('integral_scale',   $_CFG['integral_scale']);


    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));//地区列表

    $smarty->assign('provinces',    get_district_lsit(1,1));//城市选择
    //$smarty->assign('act',  'address_list');
    $smarty->assign('ur_here', "管理收货地址");
    $smarty->assign('page_title', "管理收货地址 - 易视网手机版");

    $smarty->display('address_list.dwt');
}
elseif ($action == 'edit_address')
{   
    	//yi:默认地址功能
    $default_add  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");
    
    $address_id = isset($_REQUEST['address_id'])   ? intval($_REQUEST['address_id'])  : 0;
    
    $consignee_info = get_consignee_row($_SESSION['user_id'],$address_id);
    
    $smarty->assign('default_add',      $default_add); //默认地址id
    $smarty->assign('consignee_info',    $consignee_info);//地址详情
    $smarty->assign('provinces',    get_district_lsit(1,1));//城市列
    $smarty->assign('city',         get_district_lsit(2,$consignee_info['province']));//省份列
    $smarty->assign('district',         get_district_lsit(3,$consignee_info['city']));//地区列
    
    $smarty->assign('method',         $_REQUEST['method']);//地区列
    $smarty->assign('page_title', "管理收货地址 - 易视网手机版");
    $smarty->assign('ur_here', "编辑收货地址");
    $smarty->display('address_list.dwt');
}
elseif($action == 'act_edit_address_def')
{
	//yi:添加更改收货地址新.可以设置默认地址.
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang', $_LANG);
    $method = isset($_REQUEST['method']) ? trim($_REQUEST['method']) : false;	
    $default_add = isset($_REQUEST['default_add']) ? trim($_REQUEST['default_add']) : false;	
    // show_message_wap('设置成功','user.php?act=address_list','user.php?act=address_list','info',1);

    if($method=='set_default'){
    //设置默认地址		
        $default_add = isset($_REQUEST['default_add']) ? trim($_REQUEST['default_add']) : false;	
		set_default_address($user_id, $default_add );
        
        show_message_wap('设置成功','返回上一页','user.php?act=address_list','info',1);
    }elseif($method=='add_new'){
        $address = array(
            'user_id'    => $user_id,
            'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,
            'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
            'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
            'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
            'address'    => isset($_POST['address'])   ? trim($_POST['address'])    : '',
            'consignee'  => isset($_POST['consignee']) ? trim($_POST['consignee'])  : '',
            'email'      => isset($_POST['email']) ? trim($_POST['email'])  : '',
            'tel'        => isset($_POST['tel'])       ? make_semiangle(trim($_POST['tel'])) : '',
            'sign_building' => isset($_POST['sign_building']) ? trim($_POST['sign_building']) : '',
        );
         update_address($address);
         if($default_add){
            set_default_address($user_id, mysql_insert_id() );
        }
        if($_REQUEST['ajax_data']=='true'){
            echo 1;die;
        }else{
            show_message_wap('新增收货地址成功','返回上一页','user.php?act=address_list','info',1);
        }
        
    }else{
    //修改地址
	   $address = array(
            'user_id'    => $user_id,
            'address_id' => intval($_POST['address_id']),
            'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,
            'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
            'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
            'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
            'address'    => isset($_POST['address'])   ? trim($_POST['address'])    : '',
            'consignee'  => isset($_POST['consignee']) ? trim($_POST['consignee'])  : '',
            'email'      => isset($_POST['email']) ? trim($_POST['email'])  : '',
            'tel'        => isset($_POST['tel'])       ? make_semiangle(trim($_POST['tel'])) : '',
            'sign_building' => isset($_POST['sign_building']) ? trim($_POST['sign_building']) : '',
        );
        if($default_add){
            set_default_address($user_id, intval($_POST['address_id']) );
        }
        if(update_address($address))
        {
            show_message_wap($_LANG['edit_address_success'],'返回上一页','user.php?act=address_list','info',1);
        }
    }
    
}

/* 删除收货地址 */
elseif ($action == 'drop_consignee')
{
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))
    {
        ecs_header("Location: user.php?act=address_list\n");
        exit;
    }
    else
    {
        show_message_wap($_LANG['del_address_false']);
    }
}

/* 显示收藏商品列表 */
elseif ($action == 'collection_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM ".$ecs->table('collect_goods')." WHERE user_id='$user_id' ORDER BY add_time DESC");
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    $smarty->assign('pager', $pager);
    //$smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));
    $smarty->assign('goods_list', get_collection_goods($user_id, 100, $pager['start'])); //2014-01-13临时更改为100，后续更改翻页的兼容性问题
    $smarty->assign('url',        $ecs->url());
    $lang_list = array(
        'UTF8'   => $_LANG['charset']['utf8'],
        'GB2312' => $_LANG['charset']['zh_cn'],
        'BIG5'   => $_LANG['charset']['zh_tw'],
    );
    $smarty->assign('lang_list',  $lang_list);
    $smarty->assign('user_id',  $user_id);
    $smarty->assign('ur_here',  '我的收藏');

    $smarty->assign('page_title', "我的收藏 - 易视网手机版");
    $smarty->display('user_collect.dwt');
}
/* 删除收藏的商品 */
elseif ($action == 'delete_collection')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;

    if ($collection_id > 0)
    {
        $db->query('DELETE FROM ' .$ecs->table('collect_goods'). " WHERE rec_id='$collection_id' AND user_id ='$user_id'" );
    }

    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}
// 我的客服页面
elseif ($action == 'my_kefu'){
    $smarty->assign('ur_here',    '我的客服' );
    $smarty->assign('page_title', "我的客服 - 易视网手机版");
    $smarty->display('user_my_kefu.dwt');
}
//yi:系统信息
elseif ($action == 'msg')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$size = 8;

	$list_size = get_user_msg_number($user_id);
    $pager     = get_pager('user.php', array('act' => $action), $list_size, $page, $size);    
    $smarty->assign('pager',        $pager);
	$smarty->assign('pager1',       get_pager('user.php', array('act' => $action), $user_info['unread_msg'], $page, $size));

	$list      = get_user_msg_list($user_id,   $page, $size);
	$unread    = get_user_msg_unread($user_id, $page, $size);

	$smarty->assign('unlist',		$unread);//未读消息
	$smarty->assign('list',			$list);  //全部消息
	$smarty->assign('list_size',    $list_size);
    $smarty->assign('action',    'list');
    $smarty->assign('ur_here', "站内信息");
    $smarty->assign('page_title', "站内信息 - 易视网手机版");

    $smarty->display('user_msg.dwt');
}

//yi:站内信详细内容
elseif ($action == 'msg_info')
{
	$rec_id = isset($_REQUEST['rec_id'])? intval($_REQUEST['rec_id']) : 0;
	if(empty($rec_id))
	{
		header("Location: user.php"); exit;
	}

	$sql = "select count(m.rec_id) from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.is_show=1 and (m.user_id='$user_id') or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id') ";
	$list_size = $GLOBALS['db']->GetOne($sql);
	$smarty->assign('list_size',    $list_size);

	//系统信息详细内容
	$sql = "select *, FROM_UNIXTIME(add_time) AS f_add_time from ecs_user_msg where rec_id=".$rec_id." and is_show=1 limit 1;";
	$msg = $GLOBALS['db']->GetRow($sql);
	$sqlr= '';
	if(0 == $msg['user_id'] && 1 == $msg['is_bat'])
	{
		$sql = "select * from ecs_msg_bat where msg_id=".$rec_id." and user_id=".$user_id." limit 1";
		$msg_bat = $GLOBALS['db']->GetRow($sql);
		if(empty($msg_bat))
		{
			//null
		}
		else
		{
			$msg['is_read'] = $msg_batp['is_read'];
			$sqlr = "update ecs_msg_bat set is_read=1 where msg_id=".$rec_id." and user_id=".$user_id;
		}
	}
	elseif($user_id == $msg['user_id'] && 0 == $msg['is_bat'])
	{
		$sqlr = "update ecs_user_msg set is_read=1 where rec_id=".$rec_id." and user_id=".$user_id;
	}
	else
	{
		header("Location: user.php?act=msg"); exit;
	}

	//这条消息标记已读【功能】
	if(!empty($rec_id) && !empty($msg) && (0 == $msg['is_read']) )
	{		
		mysql_query($sqlr);
		unread_user_msg($user_id, 1, 'minus');
	}
	$smarty->assign('user_name',        $user_info['user_name']);
	$smarty->assign('msg',        $msg);
    $smarty->assign('action',    'msg');
    $smarty->assign('page_title',    '站内信息 - 易视网手机版');
    $smarty->assign('ur_here',    '站内信息');
    $smarty->display('user_msg.dwt');
}

//yi:删除用户站内信息【功能】
elseif ($action == 'user_msg_remove')
{
	//判断用户信息是否正确
	$rec_id = isset($_REQUEST['rec_id'])? intval($_REQUEST['rec_id']) : 0;
	if(empty($user_id))
	{
		show_message_wap('您还未登录，不能进行删除消息操作。', '返回消息列表', '', 'error');
	}
	
	$sql = "select * from ecs_user_msg where rec_id=".$rec_id;	
	$msg = $GLOBALS['db']->GetRow($sql);
	if(1 == $msg['is_bat'])
	{
		$sql = "delete from ecs_msg_bat where msg_id=".$rec_id." and user_id=".$user_id." limit 1;";//is_bat
	}
	else
	{		
		$sql = "delete from ecs_user_msg where rec_id=".$rec_id." and user_id=".$user_id." limit 1;";//one to one msg
	}
   
	$res_del = mysql_query($sql);
	
	if($res_del)
	{
		header("Location: user.php?act=msg");
	}
	else
	{
		//delete msg fail
	}
}

/* 确认收货 */
elseif ($action == 'affirm_received')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (affirm_received($order_id, $user_id))
    {
    	//邀请已购物客户评论晒单(发送邮件)
    	$cfg = $_CFG['me_warning_invite_user'];
        if ($cfg == '1')
        {
        	$user_order = $GLOBALS['db']->getRow("SELECT consignee,email FROM ecs_order_info WHERE order_id=".$order_id);
        	$order_goods = $GLOBALS['db']->getAll('SELECT * FROM ecs_order_goods WHERE order_id='.$order_id);
        	if ($user_order) {
        		$tpl = get_mail_template('warning_invite_user');
	            $smarty->assign('user_name', $user_order['consignee']);
	            $goods_list_str = '';
	            if (count($order_goods) > 0) {
	            	foreach ($order_goods as $v) {
	            		$goods_list_str .= '<tr>';
	            		$goods_list_str .= '<td height="28" style="padding-left:6px; border-bottom:1px solid #f3f3f3; background-color:#f8f8f8;"><a href="http://www.easeeyes.com/goods'.$v['goods_id'].'.html" target="_blank" style="color:#17b9cd; text-decoration:none;">'.$v['goods_name'].'</a></td>';
	            		$goods_list_str .= '<td style="padding-right:6px; border-bottom:1px solid #f3f3f3; background-color:#f8f8f8; text-align:right;">￥'.$v['goods_price'].'</td>';
	            		$goods_list_str .= '<td style="padding-right:6px; border-bottom:1px solid #f3f3f3; background-color:#f8f8f8; text-align:right;">'.$v['goods_number'].'</td>';
	            		$goods_list_str .= '</tr>';
	            	}
	            }
	            $smarty->assign('goods_list_str', $goods_list_str);
	            
	            $content = $smarty->fetch('str:' . $tpl['template_content']);
	            send_mail($user_order['consignee'], $user_order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
        	}
        }
	        
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}


/* 添加收藏商品(ajax) */
elseif ($action == 'collect')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '');
    $goods_id = $_GET['id'];

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
    {
        $result['error'] = 1;//未登录
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    else
    {
        /* 检查是否已经存在于用户的收藏夹 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('collect_goods') .
            " WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";
        if ($GLOBALS['db']->GetOne($sql) > 0)
        {
            $delCollect = $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['ecs']->table('collect_goods')." WHERE user_id = ".$_SESSION['user_id']." AND goods_id = ".$goods_id);
            if($delCollect == 1){
                $result['error'] = 0;
                $result['message'] = '已取消关注';
                die($json->encode($result));
            }
        }
        else
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

            if ($GLOBALS['db']->query($sql) === false)
            {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['db']->errorMsg();
                die($json->encode($result));
            }
            else
            {
                $result['error'] = 0;
                $result['message'] = $GLOBALS['_LANG']['collect_success'];
                die($json->encode($result));
            }
        }
    }
}


/* 保存订单详情收货地址 */
elseif ($action == 'save_order_address')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    $address = array(		
        'consignee' => isset($_POST['consignee']) ? trim($_POST['consignee'])  : '',
        'email'     => isset($_POST['email'])     ? trim($_POST['email'])      : '',
        'address'   => isset($_POST['address'])   ? trim($_POST['address'])    : '',
        'zipcode'   => isset($_POST['zipcode'])   ? make_semiangle(trim($_POST['zipcode'])) : '',
        'tel'       => isset($_POST['tel'])       ? trim($_POST['tel'])        : '',
        'mobile'    => isset($_POST['mobile'])    ? trim($_POST['mobile'])     : '',
        'sign_building' => isset($_POST['sign_building']) ? trim($_POST['sign_building']) : '',
        'best_time' => isset($_POST['best_time']) ? trim($_POST['best_time'])  : '',
        'order_id'  => isset($_POST['order_id'])  ? intval($_POST['order_id']) : 0
    );
    if(save_order_address($address, $user_id))
    {
        ecs_header('Location: user.php?act=order_detail&order_id=' .$address['order_id']. "\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 我的红包列表 */
elseif ($action == 'bonus')
{//203955
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " WHERE user_id = '$user_id'");

	//yi:红包总金额.
    //$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    //var_dump($pager);die;
    $bonus = get_user_bouns_list($user_id, 20, 0);
    //$bonus = get_user_bouns_list($user_id, $pager['size'], $pager['start']);
        
    foreach($bonus as $k => $v)
    {
    	if($v['type_id'] == 277)//指定特定的站外红包的操作。
    	{
    		if(!empty($v['bonus_sn']))
    		{
    			$sql = "select coupon_sn from ".$GLOBALS['ecs']->table('coupon_list')." where list_id=".intval($v['bonus_sn']);    			
    			$coupon_sn = $db->getOne($sql); 
    			$bonus[$k]['bonus_sn'] = (!empty($coupon_sn))? $coupon_sn: '';	
    			$bonus[$k]['min_goods_amount'] = 0.00;		
    		}
    	}
        $bonus[$k]['status'] = trim($v['status']);
    }

    $smarty->assign('pager', $pager);
    $smarty->assign('bonus', $bonus);
	$smarty->assign('bonus_number', $record_count);
    $smarty->assign('ur_here', "我的红包");
    $smarty->assign('page_title', "我的红包 - 易视网手机版");
    $smarty->display('user_bonus.dwt');
}

/* 我的团购列表 */
elseif ($action == 'group_buy')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    //待议
    $smarty->display('user_transaction.dwt');
}



/* 团购订单详情 */
elseif ($action == 'group_buy_detail')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    //待议
    $smarty->assign('page_title', "我的团购 - 易视网手机版");
    $smarty->display('user_transaction.dwt');
}

elseif ($action == 'store_sub')
{
    $smarty->assign('page_title',      '店铺信息'.$title_ext);
    $smarty->assign('keywords',        '店铺信息'.$keywords_ext);
    $smarty->assign('description',     '店铺信息'.$description_ext);
    $storeInfo = $GLOBALS['db']->getRow("SELECT * FROM b2b_store_info WHERE user_id = ".$_SESSION['user_id']);
    
    if($storeInfo){
        if($storeInfo['b2b_ck'] == 1){
            $b2b_ck = 1;
        }else{
            $b2b_ck = 0;
            $is_sub = $storeInfo['is_sub'] == 1? 1:0;
        }
    }
    $smarty->assign('storeInfo',$storeInfo);
    @$smarty->assign('b2b_ck',   $b2b_ck);
    @$smarty->assign('is_sub',   $is_sub);
    $smarty->display('store_info_sub.dwt');
}
elseif ($action == 'act_store_sub')
{
        
        $store_name = isset($_POST['store_name']) ? cleanInput(trim($_POST['store_name'])) : '';
        $id_card    = isset($_POST['id_card']) ? cleanInput($_POST['id_card']) : '';
        $phone      = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';
        $address    = isset($_POST['address']) ? cleanInput($_POST['address']) : '';
       
        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '/';        
        
        //if(empty($_REQUEST['store_name']) || empty($_REQUEST['id_card']) || empty($_REQUEST['phone']) || empty($_REQUEST['address'])){
//            show_message('请正确填写您的镜商名称|负责人身份证号码|负责人联系方式|眼镜商地址');
        if(empty($_REQUEST['store_name'])  || empty($_REQUEST['phone']) || empty($_REQUEST['address'])){
            show_message('请正确填写您的镜商名称|负责人联系方式|眼镜商地址');
        }
       // if(empty($_FILES["licences"]) || empty($_FILES["id_card_img"])){
//           show_message('请上传营业执照|身份证照');
        if(empty($_FILES["licences"])){
            show_message('请上传营业执照');
        }
        
        $error = 0;
        $error_msg = '';

        /*营业执照*/
        //if ((($_FILES["licences"]["type"] == "image/gif") || ($_FILES["licences"]["type"] == "image/jpeg") || ($_FILES["id_card_img"]["type"] == "image/png") || ($_FILES["licences"]["type"] == "image/pjpeg")) && ($_FILES["licences"]["size"] < 5000000)){
        if ((($_FILES["licences"]["type"] == "image/gif") || ($_FILES["licences"]["type"] == "image/jpeg")  || ($_FILES["licences"]["type"] == "image/pjpeg")) && ($_FILES["licences"]["size"] < 5000000)){
            
            if ($_FILES["licences"]["error"] > 0){
                $error = $_FILES["licences"]["error"];
            }else{           
                move_uploaded_file($_FILES["licences"]["tmp_name"],ROOT_PATH."temp/upload/" . md5($_FILES["licences"]["name"]).".jpg");
                $img_up_info = "temp/upload/" . md5($_FILES["licences"]["name"]).".jpg";
            }
        }else{
            $error = 1;
            $error_msg = '图片格式错误,请重新上传营业执照';
        }
        
        $img_name= md5($_SESSION['user_id'].'licences').'.jpg';
        $fh = fopen(ROOT_PATH.$img_up_info, 'rb');
        $licences_img = '/data/store/licences/'.$_SESSION['user_id'].'/'.$img_name;
        $upyun->writeFile('/'.$licences_img, $fh, True);   // 上传图片，自动创建目录
        @unlink(ROOT_PATH.$img_up_info);
        fclose($fh);
        
        /*身份证照(暂取消)*/
        $id_card_img = '';
        /*
        if ((($_FILES["id_card_img"]["type"] == "image/gif") || ($_FILES["id_card_img"]["type"] == "image/jpeg") || ($_FILES["id_card_img"]["type"] == "image/png") || ($_FILES["id_card_img"]["type"] == "image/pjpeg")) && ($_FILES["id_card_img"]["size"] < 5000000)){
            if ($_FILES["id_card_img"]["error"] > 0){
                $error = $_FILES["id_card_img"]["error"];
            }else{           
                move_uploaded_file($_FILES["id_card_img"]["tmp_name"],ROOT_PATH."temp/upload/" . md5($_FILES["id_card_img"]["name"]).".jpg");
                $img_up_info = "temp/upload/" . md5($_FILES["id_card_img"]["name"]).".jpg";
            }
        }else{
            $error = 1;
            $error_msg = '图片格式错误,请重新上传身份证扫描件';
        }
        $img_name= md5($_SESSION['user_id'].'id_card_img').'.jpg';
        $fh = fopen(ROOT_PATH.$img_up_info, 'rb');
        $id_card_img = 'data/store/id_card_img/'.$_SESSION['user_id'].'/'.$img_name;
        $upyun->writeFile('/'.$id_card_img, $fh, True);   // 上传图片，自动创建目录
        @unlink(ROOT_PATH.$img_up_info);
        fclose($fh);
        */
        if($error>0){
            show_message($error_msg);
        }
        
        $store_info = $GLOBALS['db']->getOne("SELECT id FROM b2b_store_info WHERE user_id = ".$_SESSION['user_id']);
        
        if($store_info){
            $sql = "UPDATE b2b_store_info SET `store_name`='$store_name', `license`='$licences_img', `id_card`='$id_card', 
            `id_card_img`='$id_card_img', `phone`='$phone', `address`='$address', `is_sub`=1  
            WHERE `user_id`=" . $_SESSION['user_id'];
        }else{
            $sql = "INSERT INTO b2b_store_info (`user_id`, `store_name`, `license`, `id_card`, `id_card_img`, `phone`, `address`, `is_sub`) 
            VALUES ('".$_SESSION['user_id']."','".$store_name."','".$licences_img."','".$id_card."','".$id_card_img."','".$phone."','".$address."',1);";
        }
        if($GLOBALS['db']->query($sql)){
           
            show_message('您的资料已提交完成,我们的客服会在2个工作日之内完成验证。','','user.php');
        }else{
            show_message('网络繁忙,请稍后再试');
        }
}
elseif ($action == 'cancel_sub')
{
    if($_SESSION['user_id']){
        $cancel = $GLOBALS['db']->query("UPDATE b2b_store_info SET is_sub = 0 WHERE user_id = ".$_SESSION['user_id']);
        if($cancel){
            
            show_message('资料撤回成功，请重新填写信息后提交！','','user.php');
            
        }
    }
}
/* 清除商品浏览历史 */
elseif ($action == 'clear_history')
{
    setcookie('ECS[history]',   '', 1);
}

// zhang：新增方法（会员中心订单功能）
/**
 * 取得支付方式id列表
 * @param   bool    $is_cod 是否货到付款
 * @return  array
 */
function pid_list($is_cod)
{
    $sql = "SELECT pay_id FROM " . $GLOBALS['ecs']->table('payment');
    if ($is_cod)
    {
        $sql .= " WHERE is_cod = 1";
    }
    else
    {
        $sql .= " WHERE is_cod = 0";
    }

    return $GLOBALS['db']->getCol($sql);
}

?>