<?php
/* =======================================================================================================================
 * 重构商城公共函数库 【2012/9/15】【Author:yijiangwen】
 * =======================================================================================================================
 * include_once(ROOT_PATH. '/includes/yi_common.php');
 */
if(!defined('IN_ECS')){ die('Hacking attempt'); }

/* ======================================================================================================================
 * yi:发送即时短信接口二，20s内收到短信。
 * ======================================================================================================================
 * return: true--成功， false--失败。
 */
function send_sms2($mobile='', $msg='')
{
	$res_code = false;
	if(!empty($mobile) && !empty($msg))
	{
		try
		{
			$client   = new SoapClient('http://www.sms-10086.cn/Service.asmx?wsdl');
			$parm     = array('zh'=>'sh-mt', 'mm'=>'sh-mt', 'hm'=>$mobile, 'nr'=>$msg, 'dxlbid'=>"27");
			$result   = $client->sendsms($parm);
			$res_code = ($result->sendsmsResult==0)? true: false;
		}
		catch(SoapFault $e)
		{
			echo $e->faultstring; 	
		}
	}
	else
	{
		//echo "手机号码或短信内容错误，请检查!";
	}
	return $res_code;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:打水印函数
 * ----------------------------------------------------------------------------------------------------------------------
 * size（水印规格）:1,750px, 2,300px, 
 * filename add root_path;
 */
function add_watermark($filename = '', $save = '', $size = 1)
{
	//获得图片资源
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

	if($image)
	{
		//开始给图片打水印	
		
		$iwidth   = imagesx($image);//图片大小
		$iheight  = imagesy($image);

		$waters   = ($size==1)? ROOT_PATH.'images/common/watermark.png': ROOT_PATH.'images/common/watermark2.png';//水印图片
		$watermark= imagecreatefrompng($waters);

		//png处理：true:启用混色模式，不透明。false:不启用，则看不见水印了。
		imagealphablending($watermark, true);
		$wmwidth  = imagesx($watermark);
		$wmheight = imagesy($watermark);
		$x = 0;
		$y = 0;
		
		//水印位置，x，y分别为水印坐标。
		if($size==1)
		{
			if($iwidth>500)//750的水印
			{
				$x = ($iwidth - $wmwidth)/2-130;
				$y = ($iheight - $wmheight)/2+130;
			}
			else
			{
				$x = ($iwidth - $wmwidth)/2;
				$y = ($iheight - $wmheight)/2;
			}
		}
		else
		{
			if($iwidth>290)//300的水印
			{
				$x = ($iwidth - $wmwidth)/2-52;
				$y = ($iheight - $wmheight)/2+52;
			}
			else
			{
				$x = ($iwidth - $wmwidth)/2;
				$y = ($iheight - $wmheight)/2;
			}
		}

		$rres = imagecopy($image, $watermark, $x, $y, 0, 0, $wmwidth, $wmheight); //打水印

		if(empty($save))
		{
			$save = $filename;//没有保存路径，则水印打在原图上。
		}

		//如果打水印成功，则保存图片到文件中
		if($rres)
		{
			switch($img_size[2])
			{
				case 'image/gif':
				case 1:
					$cres = imagegif($image, $save, 95);
					break;
				case 'image/pjpeg':
				case 'image/jpeg':
				case 2:
					$cres = imagejpeg($image, $save, 95);
					break;
				case 'image/x-png':
				case 'image/png':
				case 3:
					$cres = imagepng($image, $save, 9);
					break;
				default:
					return false;
			}
			//echo "打水印成功<br/><br/>";
		}
		else
		{
			echo "打水印失败:".$filename."<br/>";
		}			
	}
	else
	{
		echo "图片不存在:".$filename."<br/>";
	}
}






/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:生成商品缩略图。自定义图片大小，保存路径，生成图片名称。
 * ----------------------------------------------------------------------------------------------------------------------
 * source 原图路径, thumb_w=100 缩略宽, thumb_h=100 缩略高, gid 商品ID, thumb_q=95 缩略质量度
 * path:  生成图片保存的路径(relation path)
 * name_ext 生成图片名
 */
function gen_thumb_path($source='', $thumb_w=100, $thumb_h=100, $gid=0, $thumb_q=95, $path='', $name_ext='')
{
	$dir = ROOT_PATH.$path;//最后的图片存放路径

	//【1】检查原始图片文件是否存在
	$im_size = @getimagesize($source);
	if(!$im_size)
	{		
		echo "原始图片:".$source."不存在！<br/>";
		return false;
	}

	//【2】检查保存图片路径，如果目录不存在，则创建目录。
	if(!file_exists($dir))
	{
		if(!make_dir($dir))
		{
			echo "文件存放目录创建失败！<br/>";
			return false;
		}
	}

	//yi:扩展各种图片格式适用--------------------------------------------------------
	$img_type = ".jpg";
	switch($im_size[2])
	{
		case 'image/gif':
		case 1:
			$im = @imagecreatefromgif($source);
			$img_type = ".gif";
			break;
		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			$img_type = ".jpg";
			$im = @imagecreatefromjpeg($source);
			break;
		case 'image/x-png':
		case 'image/png':
		case 3:
			$img_type = ".png";
			$im = @imagecreatefrompng($source);
			break;
		default:
			return false;
	}
	//yi:扩展各种图片格式适用--------------------------------------------------------


	//原始图片尺寸
	$sim_x = imagesx($im);
	$sim_y = imagesy($im);


	//生成缩略图片（真彩图片）
	$img_thumb = imagecreatetruecolor($thumb_w, $thumb_h);
	$im_color  = imagecolorallocate($img_thumb, 255, 255, 255);
	imagefilledrectangle($img_thumb, 0, 0, $thumb_w, $thumb_h, $im_color);
	//生成缩略图片完成


	//cp缩略图
	imagecopyresampled($img_thumb, $im, 0, 0, 0, 0, $thumb_w, $thumb_h, $sim_x, $sim_y);

	//新缩略图的路径。
	if(empty($name_ext))
	{
		$pth = $dir."goods_".$gid."_".$thumb_w."x".$thumb_h.$img_type;
	}
	else
	{
		$pth  = $dir.trim($name_ext).$img_type;
		$spth = $path.trim($name_ext).$img_type;//生成图片存放路径
	}

	//生成最新的图片
	switch($im_size[2])
	{
		case 'image/gif':
		case 1:
			$cres = imagegif($img_thumb, $pth, $thumb_q);
			break;
		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			$cres = imagejpeg($img_thumb, $pth, $thumb_q);
			break;
		case 'image/x-png':
		case 'image/png':
		case 3:
			$cres = imagepng($img_thumb, $pth, 9);
			break;
		default:
			return false;
	}
	
	imagedestroy($im);
	imagedestroy($img_thumb);

	return $spth;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:生成商品缩略图。可自定义图片的大小
 * ----------------------------------------------------------------------------------------------------------------------
 * source 原图路径, thumb_w=100 缩略宽, thumb_h=100 缩略高, gid 商品ID, thumb_q=95 缩略质量度
 */
function gen_thumb($source='', $thumb_w=100, $thumb_h=100, $gid=0, $thumb_q=95)
{
	//$source = ROOT_PATH."images/201206/source_img/961_G_1339676708191.jpg";//图片路径用绝对路径，或者url路径。相对路径要小心。
	$dir    = ROOT_PATH."thumb/goods/".$thumb_w."x".$thumb_h."/";//最后的图片存放路径


	//【1】检查原始图片文件是否存在
	$im_size = @getimagesize($source);
	if(!$im_size)
	{		
		echo "原始图片:".$source."不存在！<br/>";
		return false;
	}

	//【2】检查图片路径 如果目录不存在，则创建目录。
	if(!file_exists($dir))
	{
		if(!make_dir($dir))
		{
			echo "文件存放目录创建失败！<br/>";
			return false;
		}
	}

	//yi:扩展各种图片格式适用--------------------------------------------------------
	$img_type = ".jpg";
	switch($im_size[2])
	{
		case 'image/gif':
		case 1:
			$im = @imagecreatefromgif($source);
			$img_type = ".gif";
			break;

		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			$img_type = ".jpg";
			$im = @imagecreatefromjpeg($source);
			break;

		case 'image/x-png':
		case 'image/png':
		case 3:
			$img_type = ".png";
			$im = @imagecreatefrompng($source);
			break;

		default:
			//$im = @imagecreatefromjpeg($source);
			return false;
	}
	//yi:扩展各种图片格式适用--------------------------------------------------------


	//原始图片尺寸
	$sim_x = imagesx($im);
	$sim_y = imagesy($im);


	//生成缩略图片（真彩图片）
	$img_thumb = imagecreatetruecolor($thumb_w, $thumb_h);
	$im_color  = imagecolorallocate($img_thumb, 255, 255, 255);
	imagefilledrectangle($img_thumb, 0, 0, $thumb_w, $thumb_h, $im_color);
	//生成缩略图片完成


	//cp缩略图
	imagecopyresampled($img_thumb, $im, 0, 0, 0, 0, $thumb_w, $thumb_h, $sim_x, $sim_y);

	//新缩略图的路径。
	$pth = $dir."goods_".$gid."_".$thumb_w."x".$thumb_h.$img_type;

	//生成最新的图片
	switch($im_size[2])
	{
		case 'image/gif':
		case 1:
			$cres = imagegif($img_thumb, $pth, $thumb_q);
			break;
		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			$cres = imagejpeg($img_thumb, $pth, $thumb_q);
			break;
		case 'image/x-png':
		case 'image/png':
		case 3:
			$cres = imagepng($img_thumb, $pth, 9);
			break;
		default:
			return false;
	}
	//echo("<html><head></head><body><img src='http://localhost/upfile/yijiangwen.jpg' width='200' height='200'></img></body></html>");
	//echo "生成缩略图成功！";

	imagedestroy($im);
	imagedestroy($img_thumb);
}

?>