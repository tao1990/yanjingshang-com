<?php
require_once("../API/qqConnectAPI.php");
$qc = new QC();
$acs = $qc->qq_callback();//callback��Ҫ����֤ code��state,����token��Ϣ����д�뵽�ļ��д洢������get_openid���ļ��ж�  
$open_id = $qc->get_openid();//����callback��ȡ����token��Ϣ�õ�openid,����callback������openidǰ����  
$qc = new QC($acs,$open_id);  
$arr = $qc->get_user_info();  



/* ==============================================================================================================
 * QQ���ϵ�¼ API��Ȩ�ɹ����û�ע�ᣬ��¼ҳ�桾ͬ��2013/4/24����Author:yijiangwen��
 * ==============================================================================================================
 * open_id  ��open_id��qq�˺Ŷ�Ӧ��Ψһֵ��ȫ��qq����ƽ̨��open_id�����������Զ���䡣��һ�����ֺ���ĸ���ַ�����ɡ�
 * user_name��ȡ��open_id��ǰ8λ��������ͬ�����������û�ж࿼��һ�¡�
 * pwd      ���û���¼�����user_name��ͬ�����Ҳ��䡣
 * email    ������ģ���$open_id8λ@qq.com��ɡ��̶����䡣���ǻ����ظ���Σ�ա�
 *            qq�û���¼һ�ξͼ�¼ס�û���open_id����open_id������refer_id�б��ֲ��䡣
 */
define('IN_ECS', true);

require_once('../../../includes/init.php');

include_once(ROOT_PATH . 'includes/lib_passport.php');

//user_name:�û��� email:ע������ pwd:����Ϊ�û���  qq:openid(һһ��Ӧһ��qq����)
$open_id   = isset($open_id) ? trim($open_id): "";//Ψһֵ������
$user_name = trim(substr($open_id, -8)); //�û���,�����Թ��졣���ֲ��䡣
$pwd       = trim($user_name);                                              //����
$email     = $user_name.'@qq.com';                //ע������.����
$qq        = trim($user_name);                                                //����
$alias     = trim($arr['nickname']);                                        //���� qq�ǳƣ���Ϊ���ǵ��û��ǳơ�



$jump_url  = (isset($_COOKIE["jump_url"]) && !empty($_COOKIE["jump_url"])) ? trim($_COOKIE["jump_url"])."\n" : "http://www.easeeyes.com\n";
//$turn_url  = (isset($_REQUEST['turn_url']) && !empty($_REQUEST['turn_url'])) ? trim($_REQUEST['turn_url'])."\n" : $jump_url;
$turn_url  =  $jump_url;
if (isset($_COOKIE['users_src']) && $_COOKIE['users_src']=='tenpay_active')
{
	$turn_url = 'http://www.easeeyes.com/active131228.html'; //2013.12.28�
}

//���ָ���ĵ�¼����ת��ַ2014-03-31
//if (isset($_COOKIE['defined_url']))
//{
//	$turn_url = $_COOKIE['defined_url'];
//}


/* ���Ե�ַ��http://localhost/api/login_qq.php?user_name=%E6%98%93%E6%B1%9F%E6%96%87&email=12345678@qq.com&open_id=2088102420475405
 *
 * ���ϵ�¼�Ŀ�ʼ·����http://www.easeeyes.com/api/qq/oauth/redirect_to_login.php=>����ǩ����֤=>Ȼ��ͨ��login_qq.php��¼�������̳ǡ�
 *
 * qq���ϵ�¼��qq�ʱ����ϵ�¼������qq����ƽ̨�ǹ��õ�һ��open_id��open_id��Ψһ��Ӧһ��qq����ġ�ͬʱӦ����������Ψһ��Ӧһ����Ա�˺š�
 */
setcookie('qq_head', '', time()-88);//����ʱ�cookies,�����Ƿ���ʾqq_head.


//����ר�ð��
//$open_id = '00176989EE063839EA81765976779F9B';
//if($user_name == '1BEF0180' && empty($alias)){$alias = "<script>nike name is null</script>";}

if(refer_user_exist('qq', $open_id))
{

	//====================��qq open_id �Ѿ������ǵĻ�Ա�˺�һһ��Ӧ�� ֱ�ӵ�¼��====================//
	
	$sql		= "select user_name, user_id, union_login_bind from ecs_users where referer='qq' and refer_id='$open_id' limit 1";
	$tuname		= $GLOBALS['db']->getRow($sql);
	$uname		= $tuname['user_name'];
    $pwd		= $tuname['user_name'];
	$tuser_id	= intval($tuname['user_id']);
	update_alias($tuser_id, $alias);		//�����û��ǳơ�
	
	$sql = "update ecs_users set msn=2, email=replace(email, '@qq.com', '@qq.login.com') where user_id=".$tuser_id." and msn=1 limit 1;";
	mysql_query($sql);						//�����û�����Ϊqq.login.com

	if($_REQUEST['step']=='ununion')		//�����˺ŵ�¼
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
	elseif($_REQUEST['step']=='bind_user')	//���û���Ϣ
	{
		$fname = isset($_REQUEST['user_name'])? addslashes($_REQUEST['user_name']): '';
		$pwd   = isset($_REQUEST['pwd'])? trim($_REQUEST['pwd']): '';
		$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
		$tel   = isset($_REQUEST['tel'])? addslashes($_REQUEST['tel']): '';
		$fopen = isset($_REQUEST['open_id'])? trim($_REQUEST['open_id']): '';

		//���»�Ա��Ϣ
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
            //�����˺ŵ�¼
            
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
			//yi:�Ѿ��󶨹��û���Ϣ��,ֱ�ӵ�¼��		
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
elseif(have_qq($qq))//open_id������qq�ֶ��е����
{
	//====================�û��Ѿ��ù�qq���ϵ�¼��qq�Ѿ������ǵĻ�Ա�˺Ű󶨣�δ��¼open_id�ֶ�====//

	//1.1 ��ȡ����û���ȫ����Ϣ������qq�ֶΣ�
	$sql = "select user_id, user_name, email from ".$GLOBALS['ecs']->table('users')." where qq='$qq' limit 1;";
	$qqu = $GLOBALS['db']->getRow($sql);
	
	if(!empty($qqu['user_id']))
	{
		//1.2 ƥ�����ݿ��Ϻ����ڵ��û����Ƿ���ȷ���������ȷ �����¸������ڵ��û���
		if($qqu['user_name']!=$user_name)
		{
			$sqlu = "update ".$GLOBALS['ecs']->table('users')." set user_name='$user_name' where user_id=".$qqu['user_id'];
			mysql_query($sqlu);				
		}
		$email = $qqu['email'];
	}
	update_alias($qqu['user_id'], $alias); //�����û����ǳơ�

	//�����ڵ��û��� ���е�¼��
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
	//==============================================open_id, qq�ֶζ�Ϊ�յ���������û����ϵ�¼����¼open_id��=================================================//

	//���������qq���ϵ�¼�� �����¸���һ���û����ϵ�¼���ݣ�Ȼ���¼���������ǰûд�õĵط���
	$sql1  = "select * from ".$GLOBALS['ecs']->table('users')." where user_name like '".$user_name."%' limit 1;";
	$user1 = $GLOBALS['db']->getRow($sql1);

	if(!empty($user1['user_id']))
	{
		//�û������¼���ڣ����¸��û���Ϣ�����������¼�û��ļ��ݴ���
		if($user1['user_name']==$user_name && $user1['email']==$email)
		{			
			$sqlu = "update ".$GLOBALS['ecs']->table('users')." set qq='$qq' where user_id=".$user1['user_id'];
		}
		else
		{
			$sqlu = "update ".$GLOBALS['ecs']->table('users')." set user_name='$user_name', email='$email', qq='$qq' where user_id=".$user1['user_id'];
		}
		mysql_query($sqlu);

		update_alias($user1['user_id'], $alias); //�����û����ǳơ�
        
		if($user->login($user_name, $pwd))
		{	
			update_user_info();
			recalculate_price();
			update_refer_field($user_name, $open_id, 'qq'); //�������ϵ�¼�û���open_idֵ��
			ecs_header("Location: ".$turn_url);			
		}
		else
		{
			union_login_fail();
		}
	}
	else
	{	
		//һֱû����qq���ϵ�¼������ע�����û���Ϣ��Ȼ�����ϵ�¼��
		if(!empty($user_name) && !empty($pwd) && !empty($email))
		{
			$email = str_replace('@qq.com', '@qq.login.com', $email);

			if($_REQUEST['step']=='ununion')		//�����˺ŵ�¼(�Ϸ���)
			{
				//1.���û�����ʹ����������Ա �û���_1
				if(check_user($user_name) && !have_email($email))
				{
					$user_name = $user_name.'_1';
					$email     = '1'.$email;
				}
				//2.��ע�����䱻ʹ����������Ա �û���_2
				if(!check_user($user_name) && have_email($email))
				{			
					$user_name = $user_name.'_2';
					$email     = '2'.$email;
				}
				//3.ע�����û�
                
				if(register($user_name, $pwd, $email) !== false)
				{
					if($user->login($user_name, $pwd))
					{	
						update_user_info();
						recalculate_price();
						update_refer_field($user_name, $open_id, 'qq');
						//�����û����ǳơ�
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
			elseif($_REQUEST['step']=='bind_user')	//���û���Ϣ
			{
				$fname = isset($_REQUEST['user_name'])? addslashes($_REQUEST['user_name']): '';
				$pwd   = isset($_REQUEST['pwd'])? trim($_REQUEST['pwd']): '';
				$email = isset($_REQUEST['email'])? addslashes($_REQUEST['email']): '';
				$tel   = isset($_REQUEST['tel'])?	addslashes($_REQUEST['tel']): '';
				$fopen = isset($_REQUEST['open_id'])? trim($_REQUEST['open_id']): '';

				//yi����֤ע����Ϣ.
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

							//�������ϵ�¼��Ϣ
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
			else //yi:��qq���ϵ�¼.
			{				
				$smarty->assign('open_id', $open_id); 
				$smarty->assign('alias',   $alias);
				$smarty->assign('url',     $_SERVER['QUERY_STRING']);
                //header("location:callback.php?step=ununion&".$_SERVER['QUERY_STRING']);
				//$smarty->display('bind.dwt');		
                
                
                //1.���û�����ʹ����������Ա �û���_1
				if(check_user($user_name) && !have_email($email))
				{
					$user_name = $user_name.'_1';
					$email     = '1'.$email;
				}
				//2.��ע�����䱻ʹ����������Ա �û���_2
				if(!check_user($user_name) && have_email($email))
				{			
					$user_name = $user_name.'_2';
					$email     = '2'.$email;
				}
				//3.ע�����û�
                
				if(register($user_name, $pwd, $email) !== false)
				{
					if($user->login($user_name, $pwd))
					{	
						update_user_info();
						recalculate_price();
						update_refer_field($user_name, $open_id, 'qq');
						//�����û����ǳơ�
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


/*=======================================================================��������==============================================================================*/

/* ----------------------------------------------------------------------------------------------------------------------
 * ���� yi:�����û���
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
 * ���� yi:����ע������
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
 * ���� yi:�����û����ǳƣ�ֻ��qq��¼���û���
 * ----------------------------------------------------------------------------------------------------------------------
 */
function update_alias($user_id=0, $alias='')
{
	if(empty($user_id)){return false;}

	//�ж�qq��¼�û��Ƿ��б�����û���������ǳơ�
	if(!empty($alias))
	{
		//ȡ�����ڵ��û��ı���
		$talias = $GLOBALS['db']->getOne("select alias from ecs_users where user_id='$user_id' limit 1;");

		if(!empty($talias))
		{			
			if($talias != $alias)
			{
				//ԭ�����ǳƣ���������ǳ��޸��� ��������µ��ǳ�.
				$sql = "update ecs_users set alias='$alias' where user_id='$user_id' limit 1;";
				mysql_query($sql);
			}
		}
		else
		{
			//ԭ��û���ǳ� ������ǳ�
			$sql = "update ecs_users set alias='$alias' where user_id='$user_id' limit 1;";
			mysql_query($sql);
		}
	}
}


/* ------------------------------------------------------------------------------------------------------
 * �ж�����û���Ψһ���Ƿ���ڣ����ڷ���true�������ڷ���false��referer:'qq'��refer_id:qq��open_id��
 * ------------------------------------------------------------------------------------------------------
 * referer:��Ա��Դ���룬refer_id:�û�����Դ��վ��Ψһ��ݱ�ʶ�롣
 */
function refer_user_exist($referer, $refer_id)
{
	if(empty($refer_id)){return false;}
	$user_id = $GLOBALS['db']->getOne("select user_id from ecs_users where referer='".$referer."' and refer_id='".$refer_id."' limit 1;");
	return empty($user_id)? false: true;
}


/* -------------------------------------------------------------------------------------------------
 * �ֲ������ⲿ��Ա�ֶ� ��ecs_users����referer, refer_id�ֶΡ�
 * -------------------------------------------------------------------------------------------------
 * $user_name�������̳��û���(Ψһ)��
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
 * ���� yi:�ж��û����Ƿ��Ѿ�����
 * ----------------------------------------------------------------------------------------------------------------------
 */
function check_user($user_name)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where user_name="'.$user_name.'" limit 1;';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){ return true;}else{ return false;}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * ���� yi:�ж��û������Ƿ���ע��
 * ----------------------------------------------------------------------------------------------------------------------
 */
function have_email($email)
{
	$sql = 'select user_id from '.$GLOBALS['ecs']->table('users').' where email="'.$email.'" limit 1;';
	$row = $GLOBALS['db']->getRow($sql);
	if(!empty($row)){ return true;}else{ return false;}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * ���� yi:�ж����ϵ�¼��qq�Ƿ��Ѿ����ϵ�¼���ˣ��û����ϵ�¼��qq�ֶ���Ψһ��ȡ��open_id�ֶΡ�Ψһ��Ӧqq�ֶΡ�
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
 * ���ϵ�¼ʧ�ܵ� ��Ϣ��ʾ
 * -------------------------------------------------------------------------------------------------
 */
function union_login_fail()
{
	//���ϵ�¼ʧ�ܣ������û��ڱ�վע�Ṻ��
	$content = '�ܱ�Ǹ��QQ���ϵ�¼ʧ�ܣ�������ֱ��ע���¼��';
	show_message_any_dir($content, $links = '10�����ע��', $hrefs = '../user_register.html', $type = 'info', $auto_redirect = true);
}