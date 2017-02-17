<?php
/*============================================51fanli.com广告跳转接口2011-5-17============================================*/
define('IN_ECS', true);
require('../includes/init.php');

//注销掉session变量
//unset($_SESSION['user_id']);

/*===================================用户登录接口===================================*/

/*------------获取get参数-----------------*/
$channelid = isset($_GET['channelid']) ? $_GET['channelid'] : '';
$u_id      = isset($_GET['u_id']) ?      $_GET['u_id']      : ''; //返利网会员id 是唯一的, 保存在订单表中
$url       = isset($_GET['url']) ?       $_GET['url']       : ''; //turn url 参数
$syncname  = isset($_GET['syncname'])?   $_GET['syncname']  : 'false';
$url       = empty($url)? " / ": trim($url);

/*==============================保存channelid u_id到cookies(30天)==============================*/
if(!empty($channelid)){ setcookie('channelid', $channelid, time()+2592000,'/');}//固定值:'51fanli';
if(!empty($u_id)){      setcookie('fanli_uid', $u_id, time()+2592000,'/');}
/*==============================保存channelid u_id到cookies end ==============================*/

//*============================清除其它网盟的cookie防止重复返利=============================*//
if(isset($_COOKIE['channelid']))
{
	include_once('../clear_cookie_fun.php');//yi	
}
//*============================清除其它网盟的cookie防止重复返利end==========================*//

if($syncname == 'true')
{
	/*========================================联合登录========================================*/

	//获取基本参数
	$code        = isset($_GET['code']) ? $_GET['code'] : '';
	
	//返利网用户名--一般：*@51fanli  没有时候默认使用：username = $u_id@51fanli    
	$username    = isset($_GET['username']) ? $_GET['username']: '';
	$usersafekey = isset($_GET['usersafekey']) ? $_GET['usersafekey'] : '';
	$action_time = isset($_GET['action_time']) ? $_GET['action_time'] : '';
	$email       = isset($_GET['email']) ? $_GET['email'] : '';
	
	$syncaddress = isset($_GET['syncaddress']) ? $_GET['syncaddress'] : 'false';
	$name        = isset($_GET['name']) ? $_GET['name'] : '';
	$province    = isset($_GET['province']) ? $_GET['province'] : '';
	$city        = isset($_GET['city']) ? $_GET['city'] : '';
	$area        = isset($_GET['area']) ? $_GET['area'] : '';
	$address     = isset($_GET['address']) ? $_GET['address'] : '';
	$zip         = isset($_GET['zip']) ? $_GET['zip'] : '';
	$phone       = isset($_GET['phone']) ? $_GET['phone'] : '';
	$mobile      = isset($_GET['mobile']) ? $_GET['mobile'] : '';	
	$pwd         = isset($_GET['pwd']) ? $_GET['pwd'] : 'easeeyes';	
	//今后设定初始用户登录密码：统一为：easeeyes。
	
	//字符串转码操作（51fanli的数据:gb2312.-->utf-8）
	$username = mb_convert_encoding($username, 'utf-8','gb2312');
	$email    = mb_convert_encoding($email,    'utf-8','gb2312');
	$name     = mb_convert_encoding($name,     'utf-8','gb2312');
	$province = mb_convert_encoding($province, 'utf-8','gb2312');
	$city     = mb_convert_encoding($city,     'utf-8','gb2312');
	$area     = mb_convert_encoding($area,     'utf-8','gb2312');
	$address  = mb_convert_encoding($address,  'utf-8','gb2312');
	$zip      = mb_convert_encoding($zip,      'utf-8','gb2312');
	$tel      = mb_convert_encoding($phone,    'utf-8','gb2312');
	$mobile   = mb_convert_encoding($mobile,   'utf-8','gb2312');
	$pwd      = mb_convert_encoding($pwd,      'utf-8','gb2312');
	
	
	//1.验证操作时间是否正常 （5分钟内）
	if(time()-$action_time > 300){		
		//响应超时跳转 则非联合登录
		ununion_login($url);
	}
	
	//2.验证code，判断参数是否合法无篡改
	if( $code != MD5($username.'123456'.$action_time)){		
		//ununion_login($url);
	}
	
	//新添验证：用户名 邮箱  username不能为空
	if(empty($username)||empty($email)||empty($u_id)){
		ununion_login($url);
	}
	
	
	//验证用户数据表中是否存在该用户名
	if( check_user($username)){
		/*=============================================老用户=========================================*/	
	
		//验证安全码    用户表的question存放安全码字段。
		if(!ck_safekey($usersafekey,$username)){			
			ununion_login($url);
		}else{			
			//老用户:修改密码之后的联合登录  （这样就可以在我们这里修改密码也可以联合登录）
			$pwd_db = get_user_pwd($username,$email);
			$pwd_temp = $pwd;
			$pwd    = md5($pwd);
			if($pwd_db == $pwd) {
				/*=======================登录=======================*/
			    if($user->login($username, $pwd_temp))
			    {	
			        update_user_info();
			        recalculate_price();    
			    }else{
			    	ununion_login($url);
			    }
			   /*=======================登录end=====================*/ 
			}else{
				/*=======================登录=======================*/
			    if($user->fanli_login($username, $pwd_temp))
			    {	
			        update_user_info();
			        recalculate_price();    
			    }else{
			    	ununion_login($url);
			    }
			   /*=======================登录end=====================*/ 			
			}
		}
	}else{	
		/*=============================================新用户=============================================*/
		if(!empty($username) && !empty($pwd) && !empty($email) ){				
			include_once(ROOT_PATH . 'includes/lib_passport.php');
			
			//注册新用户(用户名 和邮箱都不能够重复才会成功)
			if( register($username, $pwd, $email) !== false){
				
				//注册成功 保存用户的safekey
				save_safekey($usersafekey,$username);
				//print('<br/>保存安全码成功<br/>');

				/*=======================登录=======================*/
			    if($user->login($username, $pwd))
			    {	
			        update_user_info();
			        recalculate_price();
			        //注册成功 登录成功 返回首页  
					//print('<br/>新用户注册后登录成功<br/>');   
			    }else{
			    	//注册后 登录失败
			    	ununion_login($url);
			    }
			   /*=======================登录end=====================*/			    
			    				
			}else{			
				//注册失败，非联合登录
				ununion_login($url);
			}	
		}else{
			//注册的数据不全，非联合登录
			ununion_login($url);
		}
	}
	
	if($syncaddress == 'true'){
		$province = address_name_to_id($province,1);
		$city     = address_name_to_id($city,2);
		$area     = address_name_to_id($area,3);
	}

	/*=======================电话号码处理=====================*/	
	if(($tel == '' || $tel=='--') && !empty($mobile)){
		$tel = $mobile;
	}
	/*=======================电话号码处理end==================*/
	
	if($syncaddress == 'true' && !empty($name)&&!empty($province)&&!empty($city)&&!empty($area)&&!empty($address)&&!empty($tel)&&$tel!='--')
	{
	/*====================传递过来参数中有收获人地址， 同步该用户地址（即更新覆盖原先地址）==============================*/
		
		//如果该用户没有默认地址    更新收货地址.   如果有默认地址  只添加收货地址。------------根据用户名   获得该用户的user_id-------------
		$sql     = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$username.'" ;';
		$user_id = $GLOBALS['db']->getOne($sql);
		if(empty($user_id)){ 
			//如果该用户名不存在 且注册用户名不成功 ---则跳到首页,与返利网无关---
			header('Location: '.$url);
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
	//删除非联合登录的cookie
	setcookie('fanli_ununion','true',time()-1200, '/');
	header('Location: '.$url);
		
}else{
	/*========================================非联合登录========================================*/
    //注销上次登录数据
	ununion_login($url);
}

/*==========================================================函数============================================================*/
//非联合登录
//$url =>登录成功后的跳转页面
function ununion_login($url = ''){	

	//非联合登录的标志 cookie时间为30分钟
	setcookie('fanli_ununion','true',time()+1800,'/');

	//print('<br/>==========非联合登录=============<br/>');	
	unset_cookie();
	
	//跳转
	if(empty($url)){
		header('Location: /');
	}else{	
		header('Location:'.$url);
	}
	exit();
}

//删除上次的cookie == 清空
function unset_cookie(){	
	unset($_SESSION['user_id']);
	setcookie('ECS[username]','77',time()-1200,'/');
	setcookie('ECS[password]','77',time()-1200,'/');	
	setcookie('ECS[user_id]','77',time()-1200,'/');
}

//根据username  email 查询老用户的登录密码
function get_user_pwd($username,$email){
	$pwd = '';
	$sql = 'select password from '.$GLOBALS['ecs']->table('users').' where user_name="'.$username.'" and email="'.$email.'";';
	$pwd = $GLOBALS['db']->getOne($sql);
	return $pwd; //trim();
}

//判断用户名是否已经存在数据库中
function check_user($user_name){
	$sql = 'select * from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'";';
	$row = $GLOBALS['db']->getRow($sql);
	if( !empty($row)){
		return true;
	}else{
		return false;
	}
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
//验证安全码字段  只有51fanli的用户才做验证  仅仅放在question字段
function ck_safekey($key,$username){
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
function save_safekey($key,$username){
	if(!empty($key) && !empty($username)){
		$sql = 'update '.$GLOBALS['ecs']->table('users').' set question="'.$key.'" where user_name="'.$username.'";';
		$GLOBALS['db']->query($sql);
	}
}

?>