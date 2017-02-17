<?php
/*=========================================================================会员中心 2016-01-12 tao=========================================================================*/
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'includes/lib_recomm.php');
date_default_timezone_set('PRC');
error_reporting(E_ALL);

require_once('./upyun/upyun.class.php');
$upyun = new UpYun('yunjingshang', 'zhuwentao', 's56766979');

$user_id = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']): 0;
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
//不需要登录的操作
$not_login_arr = array('login','act_login','act_login_ajax','register','act_register','is_registered', 'check_email', 'ck_user_name','ck_captcha' ,'if_email_ck' ,
 'get_password','send_pwd_email', 'act_edit_password');

//展示型页面的action列表
$ui_arr = array('default', 'profile', 'order_list', 'order_detail','act_edit_profile','address_list','act_edit_address_def','drop_consignee');

//不需要通过账号验证的操作
$not_ck_arr =array('store_sub','act_store_sub','cancel_sub','storeInfo');


$title_ext              = '_眼镜行业全方位服务提供商';
$keywords_ext           = '_眼镜行业全方位服务提供商';
$description_ext        = '_眼镜行业全方位服务提供商';

$smarty->assign('page_title',      '云镜商'.$title_ext);
$smarty->assign('keywords',        '云镜商'.$keywords_ext);
$smarty->assign('description',     '云镜商'.$description_ext);



//未登录处理
if(empty($_SESSION['user_id']))
{
    if(!in_array($action, $not_login_arr))
    {
        if(in_array($action, $ui_arr))//是否是展示页面
        {
            if(!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'user.php?' . $_SERVER['QUERY_STRING'];
            }
            $action = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            header("Location:user.php");
        }
    }
}else{
    if(!in_array($action,$not_ck_arr)){
        index_unck_display(2);    
    }
    
    //登录后公共数据
    $user_info = get_user_info($user_id);
    //print_r($action);
    /*会员等级*/
    if($user_info['user_rank'] == 1){
        $user_info['user_rank_level'] = 1;
    }elseif($user_info['user_rank'] == 2){
        $user_info['user_rank_level'] = 2;
    }elseif($user_info['user_rank'] == 8){
        $user_info['user_rank_level'] = 3;
    }elseif($user_info['user_rank'] == 7){
        $user_info['user_rank_level'] = 4;
    }
    
    $smarty->assign('article_1',    get_cat_articles(34,1,6));  //文章-公告
    $smarty->assign('article_2',    get_cat_articles(33,1,6));  //文章-规则
    $smarty->assign('article_3',    get_cat_articles(35,1,6));  //文章-买家
    $smarty->assign('article_4',    get_cat_articles(36,1,6));  //文章-卖家
    
                
    $smarty->assign('sales_charts1',    get_sales_charts_all(0,3));  //热销排行榜1
    $smarty->assign('sales_charts2',    get_sales_charts_all(3,3));  //热销排行榜2
    $smarty->assign('sales_charts3',    get_sales_charts_all(6,3));  //热销排行榜3
    
    
    $smarty->assign('type_order_num', type_order_num($user_id));    //各状态个订单数
    $smarty->assign('user',			$user_info);
    
    $smarty->assign('sort_order_list',get_goods_by_sort_order());   //商品精选
    //二级页面广告位1
    $smarty->assign('ad_B1',			ad_info_by_time(118,1));
}
//页头页尾已显示
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
    $smarty->assign('img_site',  IMG_SITE);
//用户中心欢迎页【用户中心登录成功后的默认页面】
if($action == 'default')
{
    index_unck_display(2);
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
    $smarty->assign('user_notice', $_CFG['user_notice']);
    $smarty->assign('prompt',      get_user_prompt($user_id));

	//用户最近3个订单
	$smarty->assign('order_list', get_user_orders($user_id,3,0) );
    $smarty->display('user_center_default.dwt');
}
//*=====================================================注册会员================================================================*//
elseif($action == 'register')
{
    $smarty->assign('page_title',      '用户注册'.$title_ext);
    $smarty->assign('keywords',        '用户注册'.$keywords_ext);
    $smarty->assign('description',     '用户注册'.$description_ext);

    if(!empty($user_id)){
        ecs_header("Location:user.html");
    }
    if(!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }
    
    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);
    $smarty->assign('extend_info_list', $extend_info_list);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }

    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);

    /* 增加是否关闭注册 */
    $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
	@$smarty->assign('back_act', $back_act);

    $smarty->display('user_register.dwt');
}
//==================================注册会员【功能实现】==================================||
elseif($action == 'act_register')
{
        include_once(ROOT_PATH . 'includes/lib_passport.php');

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $email    = isset($_POST['email'])    ? trim($_POST['email']) : '';
        $other['mobile_phone'] = isset($_POST['mobile_phone'])    ? trim($_POST['mobile_phone']) : '';
        $sel_question  = empty($_POST['sel_question']) ? '' : $_POST['sel_question'];
        $passwd_answer = isset($_POST['passwd_answer']) ? trim($_POST['passwd_answer']) : '';
        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '/';        
        
	//print_r($_POST);die;
        if(empty($_POST['agreement']))
        {
            show_message($_LANG['passport_js']['agreement']);
        }
        if (strlen($username) < 5)
        {
            show_message($_LANG['passport_js']['username_shorter']);
        }

        if (strlen($password) < 6)
        {
            show_message($_LANG['passport_js']['password_shorter']);
        }

        if (strpos($password, ' ') > 0)
        {
            show_message($_LANG['passwd_balnk']);
        }

        if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
        {
            if (empty($_POST['captcha']))
            {
                show_message($_LANG['invalid_captcha']);
            }
            //检查验证码
            include_once('includes/cls_captcha.php');
            $validator = new captcha();
            
            if(!$validator->check_word($_POST['captcha']))
            {
                show_message($_LANG['invalid_captcha']);
            }
        }
		//*==================注册新用户==================*//
        if(register($username, $password, $email, $other) !== false)
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
		
			$show_message_str = sprintf($_LANG['register_success'], $username.$ucdata);
			
			//active registe
			show_message($show_message_str, array($_LANG['back_up_page'], $_LANG['profile_lnk']), array($back_act,'user.php'), 'info', true);
        }
        else
        {
            $err->show('返回上一页', 'user.php?act=register');
        }
}
//==================================用户登录【界面】==================================||
elseif($action == 'login')
{
    $smarty->assign('page_title',      '用户登录'.$title_ext);
    $smarty->assign('keywords',        '用户登录'.$keywords_ext);
    $smarty->assign('description',     '用户登录'.$description_ext);
    if(empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])){
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php')? './' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }else{
        $back_act = 'user.php';
    }
	//*------------------------------登录使用验证码【已关闭】-------------------------------------*//
    $captcha = intval($_CFG['captcha']);
    if(($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }
	//*------------------------------登录使用验证码【已关闭】-------------------------------------*//

	//注:登录界面无需记录自动登录信息，已登录用户无登录页面。
    $smarty->assign('back_act', $back_act);
    $smarty->display('user_login.dwt');
}
//==================================用户登录【功能】==================================||
/*elseif($action == 'act_login')
{
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';

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

    if($user->login($username, $password)){
        
        update_user_info();
        recalculate_price();
        $ucdata = isset($user->ucdata)? $user->ucdata : '';
		
		//登录成功 跳转页面
		if(stristr($back_act,".html")!= false || stristr($back_act,".php")!= false){
			ecs_header("Location:".$back_act."\n");
		}else{
			ecs_header("Location: ./\n");
		}
    }else{
		//登录失败
        $_SESSION['login_fail'] ++ ;
        show_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'user.php', 'error');
    }
}*/
//==================================用户登录【功能】==================================||
elseif($action == 'act_login_ajax')
{
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';

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

    if($user->login($username, $password)){
        
        update_user_info();
        recalculate_price();
        $ucdata = isset($user->ucdata)? $user->ucdata : '';
		
		//登录成功 跳转页面
		if(stristr($back_act,".html")!= false || stristr($back_act,".php")!= false){
		    $res['res']       = 1;  
            $res['back_act']  = $back_act; 
			//ecs_header("Location:".$back_act."\n");
		}else{
		    $res['res'] = 1;  
            
			//ecs_header("Location: ./\n");
		}
    }else{
		//登录失败
        $_SESSION['login_fail'] ++ ;
        //show_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'user.php', 'error');
        $res['res'] = 0;
    }
    echo json_encode($res);die;
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
				show_message("您的邮箱【".$email_ck['email']."】已经验证过了，请勿重复验证。", $_LANG['profile_lnk'], 'user.html');
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
				show_message(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),$_LANG['profile_lnk'], 'user.php');
			}
        }
    }
    show_message($_LANG['validate_fail']);
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
//==================================ajax验证码==================================||
elseif ($action == 'ck_captcha')
{
    //检查验证码
    include_once('includes/cls_captcha.php');
    $validator = new captcha();
            
    if(!$validator->check_word($_REQUEST['captcha']))
    {
        echo 0;
    }else{
        echo 1;
    }
    die;
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
elseif($action == 'if_email_ck')
{
	//fn:用户邮箱是否验证过
	$u_id	  = isset($_REQUEST['user_id'])? intval($_REQUEST['user_id']): 0;
	$email_ck = $GLOBALS['db']->GetOne("select email_ck from ecs_users where user_id='$u_id' limit 1;");
	echo $email_ck;
}
//==================================退出会员中心【注销登录】==================================//
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
//==================================订单列表==================================//
elseif($action == 'order_list')
{
    $smarty->assign('page_title',      '订单列表'.$title_ext);
    $smarty->assign('keywords',        '订单列表'.$keywords_ext);
    $smarty->assign('description',     '订单列表'.$description_ext);
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    
    $type   = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : '';
    $page   = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    
    
   
    $sqlStr = '';
    if(!empty($type)){
        //待付款
        if($type == 1){
            $sqlStr = ' AND order_status=0 AND pay_status = 0 AND shop_id = 2';
           }
        //待发货
        if($type ==2){
            //$sqlStr = ' AND order_status=1 AND (pay_status=2 OR (pay_status=0 AND pay_id=3 AND (order_status=1 OR order_status=5))) ';
            $sqlStr = ' AND order_status=1 AND shipping_status = 0 AND (pay_status=2  OR (pay_status=0 AND pay_id=3 AND (order_status=1 OR order_status=5))) AND shop_id = 2';
        }
        //待确认收货
        if($type ==3){
            $sqlStr = ' AND shipping_status = 1 AND (order_status =1 OR order_status =5) AND shop_id = 2';
        }
        //待评价
        if($type ==4){
            $sqlStr = ' AND shipping_status=2 AND shop_id = 2';
        }
        //退款退货中
        if($type ==5){
            $sqlStr = ' AND order_status=4 AND shop_id = 2';
        }
    }else{
        $sqlStr = ' AND shop_id = 2';
    }
    
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'".$sqlStr);
 
    $pager  = get_pager('user.php', array('act' => $action,'type'=> $type), $record_count, $page);

    $orders = get_user_orders($user_id, $pager['size'], $pager['start'],$sqlStr);
    
    $smarty->assign('type',  $type);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->display('user_transaction.dwt');
}
//==================================查看订单详情==================================//
elseif ($action == 'order_detail')
{
    $smarty->assign('page_title',      '订单详情'.$title_ext);
    $smarty->assign('keywords',        '订单详情'.$keywords_ext);
    $smarty->assign('description',     '订单详情'.$description_ext);
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
    if(!empty($goods_list)){
        foreach ($goods_list AS $key => $value)
        {
            $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
            $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
            $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
        }
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

	$smarty->assign('country_list',   get_regions());
	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list',     $city_list);
	$smarty->assign('district_list', $district_list);
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
        $row['action_time']     = date('Y-m-d H:i:s',$row['log_time']);
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

	$smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->display('user_transaction.dwt');
}
//==================================会员个人资料【页面】==================================||
elseif($action == 'profile')
{
    $smarty->assign('page_title',      '会员资料'.$title_ext);
    $smarty->assign('keywords',        '会员资料'.$keywords_ext);
    $smarty->assign('description',     '会员资料'.$description_ext);
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $user_info = get_profile($user_id);

	//取出注册扩展字段 修改type < 2
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';

    $extend_info_list = $db->getAll($sql);

    $sql = 'SELECT reg_field_id, content ' .
           'FROM ' . $ecs->table('reg_extend_info') .
           " WHERE user_id = $user_id";
    $extend_info_arr = $db->getAll($sql);

    $temp_arr = array();
    foreach ($extend_info_arr AS $val)
    {
        $temp_arr[$val['reg_field_id']] = $val['content'];
    }
    foreach ($extend_info_list AS $key => $val)
    {
        switch ($val['id'])
        {
            case 1:     $extend_info_list[$key]['content'] = $user_info['msn']; break;
            case 2:     $extend_info_list[$key]['content'] = $user_info['qq']; break;
            case 3:     $extend_info_list[$key]['content'] = $user_info['office_phone']; break;
            case 4:     $extend_info_list[$key]['content'] = $user_info['home_phone']; break;
            case 5:     $extend_info_list[$key]['content'] = $user_info['mobile_phone']; break;
            default:    $extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']] ;
        }
    }
    $smarty->assign('extend_info_list', $extend_info_list);
	
    //密码提示问题
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);
    $smarty->assign('profile', $user_info);
    $smarty->display('user_transaction.dwt');
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
//==================================会员个人资料【功能】==================================||
elseif($action == 'act_edit_profile')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $email = trim($_POST['email']);
    $other['mobile_phone'] = $mobile_phone = isset($_POST['extend_field5']) ? trim($_POST['extend_field5']) : '';
    $sel_question = empty($_POST['sel_question']) ? '' : $_POST['sel_question'];
    $passwd_answer = isset($_POST['passwd_answer']) ? trim($_POST['passwd_answer']) : '';

    if (!is_email($email))
    {
        show_message($_LANG['msg_email_format']);
    }

    if (empty($mobile_phone) || !preg_match('/^[\d-\s]+$/', $mobile_phone))
    {
        show_message($_LANG['passport_js']['mobile_phone_invalid']);
    }

    $profile  = array(
        'user_id'  => $user_id,
        'email'    => isset($_POST['email']) ? trim($_POST['email']) : '',
        'other'    => isset($other) ? $other : array()
        );

	if (edit_profile($profile))
    {
        show_message($_LANG['edit_profile_success'], $_LANG['profile_lnk'], 'user.php?act=profile', 'info');
    }
    else
    {
        if ($user->error == ERR_EMAIL_EXISTS)
        {
            $msg = sprintf($_LANG['email_exist'], $profile['email']);
        }
        else
        {
            $msg = $_LANG['edit_profile_failed'];
        }
        show_message($msg, '', '', 'info');
    }
}
//收货地址列表界面(管理收货地址)
elseif ($action == 'address_list')
{
    $smarty->assign('page_title',      '地址管理'.$title_ext);
    $smarty->assign('keywords',        '地址管理'.$keywords_ext);
    $smarty->assign('description',     '地址管理'.$description_ext);
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
	//print_r($add_dis);

	$smarty->assign('add_display',    $add_dis);//显示地址
    
    $province_list = array();
    $city_list     = array(); 
    $district_list = array();
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

    $smarty->assign('province_list',    @$province_list[$add_list_num]);
    $smarty->assign('city_list',        @$city_list[$add_list_num]);
    $smarty->assign('district_list',    @$district_list[$add_list_num]);

	$smarty->assign('default_add',      $address_id); //默认地址id
	$smarty->assign('default_email',    $default_email); //默认email

    $smarty->assign('currency_format',  $_CFG['currency_format']);
    $smarty->assign('integral_scale',   $_CFG['integral_scale']);


    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));//地区列表

    $smarty->display('user_transaction.dwt');
}
elseif($action == 'act_edit_address_def')
{
	//yi:添加更改收货地址新.可以设置默认地址.
    $smarty->assign('page_title',      '地址管理'.$title_ext);
    $smarty->assign('keywords',        '地址管理'.$keywords_ext);
    $smarty->assign('description',     '地址管理'.$description_ext);
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang', $_LANG);

    $address = array(
        'user_id'    => $user_id,
        'address_id' => intval($_POST['address_id']),
        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 0,
        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
        'address'    => isset($_POST['address'])   ? trim($_POST['address'])    : '',
        'consignee'  => isset($_POST['consignee']) ? trim($_POST['consignee'])  : '',
        'email'      => isset($_POST['email'])     ? trim($_POST['email'])      : '',
        'tel'        => isset($_POST['tel'])       ? make_semiangle(trim($_POST['tel'])) : '',
        'mobile'     => isset($_POST['mobile'])    ? make_semiangle(trim($_POST['mobile'])) : '',
        'best_time'  => isset($_POST['best_time']) ? trim($_POST['best_time'])  : '',
        'sign_building' => isset($_POST['sign_building']) ? trim($_POST['sign_building']) : '',
        'zipcode'       => isset($_POST['zipcode'])       ? make_semiangle(trim($_POST['zipcode'])) : '',
    );

    if(update_address($address))
    {
		//是否设置默认地址		
		$default_add = isset($_POST['default_add']) ? trim($_POST['default_add']) : false;	

		if($default_add)
		{
			$address_id = intval($_POST['address_id']);

			//新增地址时候
			if( $address_id == 0){
				//最后一条记录的地址id
				$address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('user_address'). " WHERE user_id='$user_id' order by address_id desc limit 1;");
			}
			set_default_address($user_id, $address_id );
		}
		//echo("<script type='javascript'>alert('您的地址修改成功!')</script>");
		ecs_header("Location: user.php?act=address_list\n");
		//exit;

        //show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');
		//***更改成ajax提交表单的方式****
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
        show_message($_LANG['del_address_false']);
    }
}

/*yi----修改密码*/
elseif($action == 'resetpw')
{
    $smarty->assign('page_title',      '修改密码'.$title_ext);
    $smarty->assign('keywords',        '修改密码'.$keywords_ext);
    $smarty->assign('description',     '修改密码'.$description_ext);
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $smarty->display('user_transaction.dwt');
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
        show_message($_LANG['passport_js']['password_shorter']);
    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录

    if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password)))
    {
        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))
        {
            $user->logout();
            show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info');
        }
        else
        {
            show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
        }
    }
    else
    {
        show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
    }
}
/*yi----会员等级*/
elseif($action == 'member_rank')
{
    $smarty->assign('page_title',      '会员等级'.$title_ext);
    $smarty->assign('keywords',        '会员等级'.$keywords_ext);
    $smarty->assign('description',     '会员等级'.$description_ext);
    include_once(ROOT_PATH .'includes/lib_clips.php');
	include_once(ROOT_PATH .'includes/lib_transaction.php');

    if($rank = get_rank_info())
    {
        $smarty->assign('rank_name', $rank['rank_name']);//用户等级
		$smarty->assign('user_rank', $rank['user_rank']);//用户积分
        if(!empty($rank['next_rank_name']))
        {
            $smarty->assign('next_rank_point', $rank['next_rank']);
			$smarty->assign('next_rank_name',  $rank['next_rank_name']);
        }
    }
	$smarty->assign('email_is_validate',user_email_is_validate($user_id));
    $smarty->display('user_transaction.dwt');
}
/* 密码找回-->重置密码界面 */
elseif ($action == 'get_password')
{   
    $smarty->assign('page_title',      '密码找回'.$title_ext);
    $smarty->assign('keywords',        '密码找回'.$keywords_ext);
    $smarty->assign('description',     '密码找回'.$description_ext);
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    if(isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid  = intval($_GET['uid']);

        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
            show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
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
            show_message("重置密码的邮件已发到您的邮箱：".$email, $_LANG['back_home_lnk'], './', 'info');
        }
        else
        {
            //发送邮件出错
            show_message($_LANG['fail_send_password'], $_LANG['back_page_up'], './', 'info');
        }
    }else{

        //您输入的邮箱不存在！
        show_message("您填写的电子邮件地址不存在，请重新输入！", $_LANG['back_page_up'], './', 'info');
    }
}
elseif($action == 'store_sub'){
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
elseif ($action == 'act_store_sub'){
        
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
}elseif($action == 'cancel_sub'){
    if($_SESSION['user_id']){
        $cancel = $GLOBALS['db']->query("UPDATE b2b_store_info SET is_sub = 0 WHERE user_id = ".$_SESSION['user_id']);
        if($cancel){
            
            show_message('资料撤回成功，请重新填写信息后提交！','','user.php');
            
        }
    }
}
//==================================店铺资料【页面】==================================||
elseif($action == 'storeInfo')
{
    $smarty->assign('page_title',      '店铺信息'.$title_ext);
    $smarty->assign('keywords',        '店铺信息'.$keywords_ext);
    $smarty->assign('description',     '店铺信息'.$description_ext);
    $store_info = $GLOBALS['db']->getRow("SELECT * FROM b2b_store_info WHERE user_id = ".$_SESSION['user_id']);
    $smarty->assign('store_info', $store_info);
    $smarty->display('user_transaction.dwt');
}
//==================================我的消息【页面】==================================||
elseif ($action == 'msg')
{
    $smarty->assign('page_title',      '消息管理'.$title_ext);
    $smarty->assign('keywords',        '消息管理'.$keywords_ext);
    $smarty->assign('description',     '消息管理'.$description_ext);
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

    $smarty->display('user_transaction.dwt');
}
elseif($action == 'pages')
{
	$page     = isset($_REQUEST['page'])? intval($_REQUEST['page']): 1;
	$size     = isset($_REQUEST['size'])? intval($_REQUEST['size']): 10;
	$count    = isset($_REQUEST['count'])? intval($_REQUEST['count']): 1;
	$pan_kind = isset($_REQUEST['pan_kind'])? trim($_REQUEST['pan_kind']): '';

	if('user_msg' == $pan_kind)
	{
		$pager     = get_pager('user.php', array('act' => $action), $count, $page, $size);    
		$smarty->assign('pager',        $pager);
		$smarty->assign('list',			get_user_msg_list($user_id,  $page, $size));  //全部消息
		$smarty->display('pap_user_msg_list.dwt');
	}
	elseif('unread_msg' == $pan_kind)
	{
		$pager1    = get_pager('user.php', array('act' => $action), $count, $page, $size);    
		$smarty->assign('pager1',        $pager1);
		$smarty->assign('unlist',	     get_user_msg_unread($user_id,  $page, $size));//未读消息
		$smarty->display('pap_user_msg_unread.dwt');
	}
	else
	{}
}
//yi:站内信详细内容
elseif ($action == 'msg_info')
{
    $smarty->assign('page_title',      '消息管理'.$title_ext);
    $smarty->assign('keywords',        '消息管理'.$keywords_ext);
    $smarty->assign('description',     '消息管理'.$description_ext);
	$rec_id = isset($_REQUEST['rec_id'])? intval($_REQUEST['rec_id']) : 0;
	if(empty($rec_id))
	{
		header("Location: user_msg.html"); exit;
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
		header("Location: user_msg.html"); exit;
	}

	//这条消息标记已读【功能】
	if(!empty($rec_id) && !empty($msg) && (0 == $msg['is_read']) )
	{		
		mysql_query($sqlr);
		unread_user_msg($user_id, 1, 'minus');
	}
	
	$smarty->assign('msg',        $msg);
    $smarty->display('user_transaction.dwt');
}
/**
 * @name 获取各个订单数量
 */
function type_order_num($user_id){
        $arr = array();
        if(!empty($user_id)){
            //待付款
            $sqlStr1 = ' AND order_status=0 AND pay_status = 0 AND shop_id = 2';
            //待发货
            $sqlStr2 = ' AND order_status=1 AND shipping_status = 0 AND (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5))) AND shop_id = 2 ';
            //待确认收货
            $sqlStr3 = ' AND shipping_status = 1 AND (order_status =1 OR order_status =5) AND shop_id = 2 ';
            //待评价
            $sqlStr4 = ' AND shipping_status=2 AND shop_id = 2 ';
            //退款退货中
            $sqlStr5 = ' AND order_status=4 AND shop_id = 2 ';
    
            $arr['type1'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
            " WHERE user_id = '$user_id' $sqlStr1 ");
            $arr['type2'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
            " WHERE user_id = '$user_id' $sqlStr2 ");
            $arr['type3'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
            " WHERE user_id = '$user_id' $sqlStr3 ");
            $arr['type4'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
            " WHERE user_id = '$user_id' $sqlStr4 ");
            $arr['type5'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
            " WHERE user_id = '$user_id' $sqlStr5 ");
        }
        return $arr;
}

function get_user_msg_number($user_id=0)
{
	$sql = "select count(m.rec_id) from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.shop_id = 2 AND m.is_show=1 and (m.user_id='$user_id') or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id') ";
	return $GLOBALS['db']->GetOne($sql);
}

function get_user_msg_list($user_id=0, $page=1, $size=10)
{
	$page  = ($page<1)? 1: intval($page);
	$start = ($page-1)*$size;
	$sql = "select m.*, FROM_UNIXTIME(m.add_time) AS f_add_time, IF(m.is_bat=1, b.is_read, m.is_read) as is_read from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.shop_id = 2 AND m.is_show=1 and (m.user_id='$user_id') or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id') order by m.rec_id desc limit ".$start.",".$size;
	$list = $GLOBALS['db']->GetAll($sql);
	return $list;
}

function get_user_msg_unread($user_id=0, $page=1, $size=10)
{
	$page  = ($page<1)? 1: intval($page);
	$start = ($page-1)*$size;
	$sql = "select m.*, FROM_UNIXTIME(m.add_time) AS f_add_time from ecs_user_msg as m left join ecs_msg_bat as b on m.rec_id=b.msg_id where m.shop_id = 2 AND m.is_show=1 and (m.user_id='$user_id' and m.is_read=0) or (m.user_id=0 and m.is_bat=1 and b.user_id='$user_id' and b.is_read=0) order by m.rec_id desc limit ".$start.",".$size;
	$unread = $GLOBALS['db']->GetAll($sql);	
    /*查询慢原因 暂时关闭 by:tao 20150415*/
    $unread = '';
    
	return $unread;
}
?>