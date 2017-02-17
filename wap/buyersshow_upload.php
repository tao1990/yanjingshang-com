<?php
/* 
 * 买家秀 上传晒单
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$upload_type = isset($_REQUEST['upload_type']) ? $_REQUEST['upload_type'] : '0';
$cat_id = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '0';
$goods_id = isset($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '0';
$detail = isset($_REQUEST['detail']) ? trim($_REQUEST['detail']) : '';
$select_img = isset($_REQUEST['select_img']) ? trim($_REQUEST['select_img']) : '';
$thumb_img = isset($_REQUEST['thumb_img']) ? trim($_REQUEST['thumb_img']) : '';
$index_img = isset($_REQUEST['index_img']) ? trim($_REQUEST['index_img']) : '';


$newsshow = ''; //处理结果，提示消息
if(empty($_SESSION['user_id']))
{
	$newsshow = '^_^您还没有登录,请先登陆，再上传买家秀！ ';
	$smarty->assign('newsshow', $newsshow); 
	$smarty->display('buyersshow_upload.dwt');
	exit;
}

$smarty->assign('categoriesp',	get_categories_option()); //商品类别目录
//获取当前登录用户的已上传的买家秀
$user_mjx = get_user_mjx($_SESSION['user_id']);
$smarty->assign('user_mjx',  $user_mjx);

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (!$upload_type || !$goods_id) {
		$newsshow = '请选晒单传类型和商品！';
		$smarty->assign('newsshow', $newsshow); 
		$smarty->display('buyersshow_upload.dwt');
		exit;
	}

	//买家秀图片路径插入数据表中
	$sql = "INSERT INTO " . $GLOBALS['ecs']->table('mjx') . " (detail, img, thumb_img, index_img, datetime, user_id, goods_id, cat_id, sh, effect, comments, upload_type) VALUES ('" .$detail. "', '" .$select_img. "', '" .$thumb_img. "', '" .$index_img. "', '". time() ."', '" .$_SESSION['user_id']. "', '" .$goods_id. "', '" .$cat_id. "', '0', '', 0, '" .$upload_type. "')";
	$sqlres = $GLOBALS['db']->query($sql);
	$mjxid = mysql_insert_id();
	
	if($sqlres !== false)
	{
		//--------------------------------------------------------对已经上传上去的图片添加水印--------------------------------------------------------//
		$filename = ROOT_PATH.$select_img;           //刚上传的图片文件路径
		//$filename = str_replace("/", "\\", $filename);//windows下是要打开。
		
		//yi:扩展各种图片格式适用--------------------------------------------------------
		$img_size = @getimagesize($filename);
		$img_type = ".jpg";
	
		switch($img_size[2])
		{
			case 'image/gif':
			case 1:
				$image = @imagecreatefromgif($filename);
				$img_type = ".gif";
				break;
	
			case 'image/pjpeg':
			case 'image/jpeg':
			case 2:
				$img_type = ".jpg";
				$image = @imagecreatefromjpeg($filename);
				break;
	
			case 'image/x-png':
			case 'image/png':
			case 3:
				$img_type = ".png";
				$image = @imagecreatefrompng($filename);
				break;
	
			default:
				return false;
		}
		//yi:扩展各种图片格式适用--------------------------------------------------------
	
	
		if($image)
		{
			$iwidth   = imagesx($image);
			$iheight  = imagesy($image);
	
			//水印图片
			$waters   = ROOT_PATH.'cc.png';    //logo水印
			//$waters = str_replace("/", "\\", $waters);//windows下是要打开。
			$watermark= imagecreatefrompng($waters);
	
			//png图片处理：true:启用混色模式，不透明。false:不启用，则看不见水印了。
			imagealphablending($watermark, true);
			$wmwidth  = imagesx($watermark);
			$wmheight = imagesy($watermark);
	
			//水印位置：水印打在买家秀图片的右下脚
			$x = $iwidth - $wmwidth;
			$y = $iheight - $wmheight;
			$rrse = imagecopy($image, $watermark, $x, $y, 0, 0, $wmwidth, $wmheight); //打水印
	
			//如果打水印成功，则保存图片到文件中
			//yi:扩展各种图片格式适用--------------------------------------------------------
			switch($img_size[2])
			{
				case 'image/gif':
				case 1:
					$cres = imagegif($image, $filename);
					break;
	
				case 'image/pjpeg':
				case 'image/jpeg':
				case 2:
					$cres = imagejpeg($image, $filename);
					break;
	
				case 'image/x-png':
				case 'image/png':
				case 3:
					$cres = imagepng($image, $filename);
					break;
	
				default:
					return false;
			}
			//yi:扩展各种图片格式适用--------------------------------------------------------
	
			
			//yi:创建不变形的缩略图，制作电子相册
			$bi   = 180/228;         //标准图宽/高
			$i_bi = $iwidth/$iheight;//实际图宽/高
	
			$s_width  = 180;
			$s_height = 228;
	
			if($i_bi>$bi)    //宽大了
			{
				$s_width  = 180;
				$s_height = ceil((180*$iheight)/$iwidth);		
			}
			elseif($i_bi<$bi)//高大了
			{
				$s_height = 228;
				$s_width  = ceil((228*$iwidth)/$iheight);
			}
			elseif($i_bi==$bi)
			{
				//等比例
			}
			else
			{
				//TODO
			}
	
		}
		//--------------------------------------------------------对已经上传上去的图片添加水印END---------------------------------------------------//
	
	
		//图片上传成功提示语言	
		$newsshow = "<font color='red'>恭喜您！买家秀已提交成功！审核通过后，会显示在页面!</font>";
		$smarty->assign('newsshow',   $newsshow);
	    var_dump($newsshow);die;
		//$smarty->display('buyersshow_upload.dwt');
		exit;
	}
	else
	{
		$newsshow = "<font color='red'>对不起，由于网络原因导致买家秀上传失败，您可稍后再试或联系客服!</font>";
		$smarty->assign('newsshow',   $newsshow); 
		
        var_dump($newsshow);die;
        //$smarty->display('buyersshow_upload.dwt');
		exit;
	}
}

$smarty->display('buyersshow_upload.dwt');


//获取当前登录用户的已上传的买家秀
function get_user_mjx ($userid) {
	$user_mjx = array();
	if ($userid) {
		$sql = 'SELECT id, title, thumb_img FROM ' . $GLOBALS['ecs']->table('mjx') . ' WHERE user_id=' . $userid . ' ORDER BY ID DESC LIMIT 0, 19';
		$result = $GLOBALS['db'] -> getAll($sql);
		if ($result) {
			foreach ($result as $v) {
				$user_mjx[] = array (
						'id'			=>	$v['id'],
						'title'			=>	$v['title'],
						'thumb_img'		=>	$v['thumb_img']
				);
			}
		}
	}
	return $user_mjx;
}

//获取所有目录
function get_categories_option() {
	//获取顶级目录
	$all_categorys = array();
	$sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = 0 AND is_show = 1 AND cat_id != 12 AND cat_id != 138';
	$parent_cate = $GLOBALS['db']->getAll($sql);
	foreach ($parent_cate as $value) {
		$all_categorys[$value['cat_id']]['cat_id'] = $value['cat_id'];
		$all_categorys[$value['cat_id']]['cat_name'] = '='.$value['cat_name'].'=';
		
		//获取子目录
		$children = get_child_category($value['cat_id']);
		foreach ($children as $v) {
			$all_categorys[$v['cat_id']]['cat_id'] = $v['cat_id'];
			$all_categorys[$v['cat_id']]['cat_name'] = $v['cat_name'];
		}
	}
	return $all_categorys;
}

//获取子目录
function get_child_category($parent_id=0) {
	if ($parent_id) {
		$sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE parent_id = '.$parent_id.' AND is_show = 1';
		return $GLOBALS['db']->getAll($sql);
	}
}

//获取用户绑定的应用
function get_user_app_sync($user_id=0) {
	if (intval($user_id) > 0) {
		//$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$user_id.' AND sync_status = 1';
		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$user_id;
		return $GLOBALS['db']->getAll($sql);
	}
}
?>