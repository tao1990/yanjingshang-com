<?php
header("Content-type: text/html; charset=utf-8");

define('IN_ECS', true);
require('../../includes/init.php');
//require_once(dirname(__FILE__) . '/../../languages/' .$_CFG['lang']. '/user.php');
date_default_timezone_set('PRC');

$shop_id = 1158;
$shop_no = 'easeeyes';
$shop_key = '45cf46dcb67a1765'; 

//参数
//http://www.test.com/51fanlilogin.php?tracking_id=123123&channel_id=51fanli&u_id=6&target_url=&tracking_code=12345
//&code=d7b6e7b74aea236623ea1aa6830f6360&syncname=true&username=12345@51fanli&usersafekey=849b59ee2e3af476
//&action_time=1294820691&email=6@51fanli.com&show_name=qiubo%40%B7%B5%C0%FB%CD%F8&syncaddress=true&name=%D0%EC%B2%A8
//&province=%C9%CF%BA%A3%CA%D0&city=%C9%CF%BA%A3%CA%D0&area=%C6%D6%B6%AB%D0%C2%C7%F8&address=%D5%C5%D1%EE%C2%B7707&zip=200000&phone=021-58888400&mobile=15888888888
//http://192.168.1.52:3001/wap/cps/51fanli/transfer.php?tracking_id=123123&channel_id=51fanli&u_id=6&target_url=&tracking_code=12345&code=d7b6e7b74aea236623ea1aa6830f6360&syncname=true&username=12345@51fanli&usersafekey=849b59ee2e3af476&action_time=1294820691&email=6@51fanli.com&show_name=qiubo%40%B7%B5%C0%FB%CD%F8&syncaddress=true&name=%D0%EC%B2%A8&province=%C9%CF%BA%A3%CA%D0&city=%C9%CF%BA%A3%CA%D0&area=%C6%D6%B6%AB%D0%C2%C7%F8&address=%D5%C5%D1%EE%C2%B7707&zip=200000&phone=021-58888400&mobile=15888888888
$tracking_id = isset($_GET['tracking_id']) ? $_GET['tracking_id'] : '';
$channel_id = isset($_GET['channel_id']) ? $_GET['channel_id'] : '51fanli'; //写入COOKIES，写入订单表
$u_id = isset($_GET['u_id']) ? $_GET['u_id'] : ''; //写入COOKIES
$target_url = !empty($_GET['target_url']) ? urldecode($_GET['target_url']) : 'http://m.easeeyes.com';//'http://192.168.1.52:3001/wap';
$tracking_code = isset($_GET['tracking_code']) ? $_GET['tracking_code'] : ''; //写入COOKIES,效果追踪识别码
$code = isset($_GET['code']) ? $_GET['code'] : '';
$syncname = isset($_GET['syncname']) ? $_GET['syncname'] : ''; //联合登录判断参数
$username = isset($_GET['username']) ? $_GET['username'] : '';
$usersafekey = isset($_GET['usersafekey']) ? $_GET['usersafekey'] : '';
$action_time = isset($_GET['action_time']) ? $_GET['action_time'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$show_name = isset($_GET['show_name']) ? urldecode($_GET['show_name']) : '';
$syncaddress = isset($_GET['syncaddress']) ? $_GET['syncaddress'] : '';
$name = isset($_GET['name']) ? urldecode($_GET['name']) : '';
$province = isset($_GET['province']) ? urldecode($_GET['province']) : '';
$city = isset($_GET['city']) ? urldecode($_GET['city']) : '';
$area = isset($_GET['area']) ? urldecode($_GET['area']) : '';
$address = isset($_GET['address']) ? urldecode($_GET['address']) : '';
$zip = isset($_GET['zip']) ? $_GET['zip'] : '';
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$mobile = isset($_GET['mobile']) ? $_GET['mobile'] : '';

$pwd = isset($_GET['pwd']) ? $_GET['pwd'] : 'easeeyes';//自定义



//非本站则跳出404
if($target_url!=''){
    $expUrl = explode('.',$target_url);
    if(!strstr($expUrl[0],'easeeyes') && !strstr($expUrl[1],'easeeyes')){
        header("HTTP/1.1 404 Not Found");exit;  
    }
}

//清除其他cps合作cookie
if (isset($_COOKIE['LTINFO'])) setcookie('LTINFO', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_360'])) setcookie('cpsinfo_360', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_duomai'])) setcookie('cpsinfo_duomai', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_fanhuan_channel_id'])) setcookie('cpsinfo_fanhuan_channel_id', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_yiqifa_src'])) setcookie('cpsinfo_yiqifa_src', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_xunlei'])) setcookie('cpsinfo_xunlei', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_17elink'])) setcookie('cpsinfo_17elink', '', time()-3600, '/');
if (isset($_COOKIE['AELINFO'])) setcookie('AELINFO', '', time()-3600, '/');
if (isset($_COOKIE['cpsinfo_yiqifa_src_roi'])) setcookie('cpsinfo_yiqifa_src_roi', '', time()-3600, '/');

if ($channel_id && $u_id && $tracking_code) 
{
	setcookie('cpsinfo_51fanli_channel_id', $channel_id, time()+2592000, '/', '');
	setcookie('cpsinfo_51fanli_u_id', $u_id, time()+2592000, '/', '');	
	setcookie('cpsinfo_51fanli_tracking_code', $tracking_code, time()+2592000, '/', '');
	
	setcookie('cpsinfo_51fanli_uname', $username, time()+2592000, '/', '');	//推送订单需要
}

if ($syncname != 'true') 
{
	Header("Location:$target_url");
	exit;
}
else 
{
	//字符串转码操作（51fanli的数据:gb2312.-->utf-8）
	$username = mb_convert_encoding($username, 'utf-8', 'gb2312');
	$email    = mb_convert_encoding($email,    'utf-8', 'gb2312');
	$name     = mb_convert_encoding($name,     'utf-8', 'gb2312');
	$province = mb_convert_encoding($province, 'utf-8', 'gb2312');
	$city     = mb_convert_encoding($city,     'utf-8', 'gb2312');
	$area     = mb_convert_encoding($area,     'utf-8', 'gb2312');
	$address  = mb_convert_encoding($address,  'utf-8', 'gb2312');
	$zip      = mb_convert_encoding($zip,      'utf-8', 'gb2312');
	$tel      = mb_convert_encoding($phone,    'utf-8', 'gb2312');
	$mobile   = mb_convert_encoding($mobile,   'utf-8', 'gb2312');
	
	$show_name = mb_convert_encoding($show_name,   'utf-8', 'gb2312');
	
	//1.验证操作时间是否正常 （5分钟内）
	if (abs(intval($action_time) - time()) > 300)
	{	
		Header("Location:$target_url");
		exit;
	}
	
	//2.验证code: MD5(username + shop_key + action_time)
	if($code != MD5($username.$shop_key.$action_time)){		
		Header("Location:$target_url");
		exit;
	}
	
	$user_id = check_user($username);
	if ($user_id) 
	{
		if( ! ck_safekey($usersafekey, $username))
		{
			Header("Location:$target_url");
			exit;
		}
		else
		{
			//老用户:修改密码之后的联合登录  （这样就可以在我们这里修改密码也可以联合登录）
			$pwd_db = '';
			$pwd_fl = $pwd;
			$pwd_db = get_user_pwd($username,$email);
			$pwd = md5($pwd);			
			if($pwd_db == $pwd) {
				/*=======================正常登录=======================*/
			    if($user->login($username, $pwd_fl))
			    {	
			    	update_user_info();
			        recalculate_price();
			    }
			    else
			    {
			    	Header("Location:$target_url");
			    	exit;
			    }
			} else {
				/*=======================老用户非正常登录=======================*/
			    if($user->fanli_login($username, $pwd))
			    {	
			        update_user_info();
			        recalculate_price();
			    }
			    else 
			    {
			    	Header("Location:$target_url");
			    	exit;
			    }
			}
		}
	}
	else 
	{
		//注册新用户
		if( ! empty($username) && ! empty($pwd) && ! empty($email))
		{
			include_once(ROOT_PATH . 'includes/lib_passport.php');
						
			if(register($username, $pwd, $email) !== false){
				
				//注册成功 保存用户的safekey
				save_safekey($usersafekey, $username);

			    if($user->login($username, $pwd))
			    {	
			        update_user_info();
			        $GLOBALS['db']->query("UPDATE ecs_users SET refer_id = '" .$u_id. "', alias = '". $show_name. "'  WHERE user_name = '" .$username. "'");
			        //recalculate_price();
			    } 
			    else
			    {
			    	Header("Location:$target_url");
			    	exit;
			    }
			    				
			}
			else
			{			
				Header("Location:$target_url");
				exit;
			}	
		}
		else 
		{
			Header("Location:$target_url");
			exit;
		}
	}
	
	if(($tel == '' || $tel=='--') && !empty($mobile))
	{
		$tel = $mobile;
	}
	
	if($syncaddress == 'true' && !empty($name)&&!empty($province)&&!empty($city)&&!empty($area)&&!empty($address)&&!empty($tel)&&$tel!='--'){
	/*====================传递过来参数中有收获人地址， 同步该用户地址（即更新覆盖原先地址）==============================*/
		//print('收货人地址信息:<br/>');		
		$province = address_name_to_id($province,1);
		$city     = address_name_to_id($city,2);
		$area     = address_name_to_id($area,3);
		
		//如果该用户没有默认地址    更新收货地址.   如果有默认地址  只添加收货地址。------------根据用户名   获得该用户的user_id-------------
		$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$username.'" ;';
		$user_id = $GLOBALS['db']->getOne($sql);
		if(empty($user_id)){ 
			//如果该用户名不存在 且注册用户名不成功 ---则跳到首页,与返利网无关---
			header('Location: /');
		}	
		
		$sqla       = 'select address_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$username.'" ;';
		$address_id = $GLOBALS['db']->getOne($sqla);
		
		//address_id == 0 没有默认地址
		if($address_id == 0){
						
			//新增收货地址。从返利网传来地址
			$sql2 = 'insert into '.$GLOBALS['ecs']->table('user_address').'(address_name,user_id,consignee,email,country,province,city,district,address,zipcode,tel,mobile)'.
					' value("fanli_address",'.$user_id.',"'.$name.'","'.$email.'",1,'.$province.','.$city.','.$area.',"'.$address.'","'.$zip.'","'.$tel.'","'.$mobile.'");';
			$GLOBALS['db']->query($sql2);			
			$address_id = mysql_insert_id();
			
			//把该地址设置默认地址			
			$sqlu = 'update '.$GLOBALS['ecs']->table('users').' set address_id='.$address_id.' where user_id='.$user_id.';';
			$GLOBALS['db']->query($sqlu);
		}else{
			//第二次传地址过来  则只是更新地址
			
			//这个默认地址是返利网的地址 则更新地址  不是的则新增地址
			$sqln       = 'select address_name from '.$GLOBALS['ecs']->table('user_address').' where address_id='.$address_id.' ;';
			$address_name = $GLOBALS['db']->getOne($sqln);
			
			if($address_name == 'fanli_address'){
				$sqlua = 'update '.$GLOBALS['ecs']->table('user_address').' set consignee="'.$name.'", email="'.$email.'",province='.$province.',city='.$city.',
				district='.$area.',address="'.$address.'",zipcode="'.$zip.'",tel="'.$tel.'",mobile="'.$mobile.'" where address_id='.$address_id.';';
				$GLOBALS['db']->query($sqlua);
				//print_r('更新地址成功');
			}else{
				//新增收货地址。从返利网传来地址
				$sql2 = 'insert into '.$GLOBALS['ecs']->table('user_address').'(address_name,user_id,consignee,email,country,province,city,district,address,zipcode,tel,mobile)'.
						' value("fanli_address",'.$user_id.',"'.$name.'","'.$email.'",1,'.$province.','.$city.','.$area.',"'.$address.'","'.$zip.'","'.$tel.'","'.$mobile.'");';
				$GLOBALS['db']->query($sql2);				
			}
		}
	}
	
	Header("Location:$target_url");
	exit;
}

//检查用户是否已存在
function check_user($username){
	$sql = "SELECT user_id, referer FROM  " . $GLOBALS['ecs']->table('users'). " WHERE user_name='$username' LIMIT 1";
	$row = $GLOBALS['db']->getRow($sql);
	if ($row['user_id'] && ! $row['referer']) {
		//检查用户来源字段是否为空，为空则添加来源
		$GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('users'). " SET referer='51fanli' WHERE user_id=".$row['user_id']);
	}
	return $row['user_id'];
}

//验证安全码字段  只有51fanli的用户才做验证  仅仅放在question字段
function ck_safekey($key,$username)
{
	$safekey = '';
	$sql     = 'select question from '.$GLOBALS['ecs']->table('users').' where user_name="'.$username.'";';
	$safekey = $GLOBALS['db']->getOne($sql);
	if(empty($safekey)){
		return false;
	}else{
		if( $safekey == $key){
			return true;
		}else{
			return false;
		}
	}
}

//保存用户的安全码
function save_safekey($key,$username)
{
	if(!empty($key) && !empty($username)){
		$sql = 'update '.$GLOBALS['ecs']->table('users').' set question="'.$key.'" where user_name="'.$username.'";';
		$GLOBALS['db']->query($sql);
	}
}

//根据username  email 查询老用户的登录密码
function get_user_pwd($username,$email){
	$pwd = '';
	$sql = 'select password from '.$GLOBALS['ecs']->table('users').' where user_name="'.$username.'" and email="'.$email.'";';
	$pwd = $GLOBALS['db']->getOne($sql);
	return $pwd;
}

//根据地址名字获得地址的编号
function address_name_to_id($name, $region_type=1){
	$id = 0;
	
	if(!empty($name)){
		if( $region_type == 1){
			//省
			$sql = "select region_id from ".$GLOBALS['ecs']->table('region')." where region_type=1 and LOCATE(region_name,'".$name."')>0;";			
			$id  = $GLOBALS['db']->getOne($sql);

		}else if( $region_type == 2){
			//市
			$sql = "select region_id from ".$GLOBALS['ecs']->table('region')." where region_type=2 and LOCATE(region_name,'".$name."')>0;";			
			$id  = $GLOBALS['db']->getOne($sql);
		}else if( $region_type == 3){
			//区	(存在重名的情况)
			$dist = $GLOBALS['db']->getOne("select region_id from ".$GLOBALS['ecs']->table('region')." where region_type=3 and region_name='".$name."';");	
			if($dist > 0 ){
				$id = $dist;
			}else{
				$sql = "select region_id from ".$GLOBALS['ecs']->table('region')." where region_type=3 and LOCATE(region_name,'".$name."')>0;";			
				$id  = $GLOBALS['db']->getOne($sql);
			}
		}else{
			$id = 0;
		}
	}else{
		$id = 0;
	}
	return $id;
}

?>
