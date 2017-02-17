<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

error_reporting(0); //有时会发生异常.在此屏蔽错误,但不影响文件上传

$path = $_SERVER["DOCUMENT_ROOT"]."/upfile/";
$valid_formats = array("jpg", "png", "gif", "bmp", "jpeg");
if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
{
	$name = $_FILES['photoimg']['name'];
	$size = $_FILES['photoimg']['size'];
   
	if(strlen($name))
	{
		//list($txt, $ext) = explode(".", $name);
		//获取文件后缀
		$extend = explode(".", $name);
		$va = count($extend)-1;
		$ext = strtolower($extend[$va]);
		
		if(in_array($ext, $valid_formats))
		{
			if($size<(1024*1024)) // Image size max 1MB
			{
				$filename_pre = time() . "_" . $_SESSION['user_id'] ;	//文件名前缀
				$filename_ext = $ext; //文件名后缀
				$actual_image_name = $filename_pre . "." . $ext;
				$tmp = $_FILES['photoimg']['tmp_name'];
				if(move_uploaded_file($tmp, $path.$actual_image_name))
				{
					//echo '恭喜您刚刚上传了一张买家秀的图片，请点击右上的“发表”按钮，就可以分享您的买家秀了！<br/ >: <img src="upfile/' .$actual_image_name. '" width="90" height="90" style="border:1px solid #EBEBEB" align="absmiddle" />';
					echo '<div style="width:197px; height:243px; background:url(/themes/default/images/buyersshow/bg_preview.gif) left top no-repeat;">
							<div style="padding:20px 0 2px 25px; color:#939393;">上传成功</div>
							<div style="margin:0 auto; width:150px; height:150px; text-align:center; border:1px solid #d9d9d9;"><img src="/upfile/'.$actual_image_name.'" width="150" height="150" title="" alt="" border="0" /></div>
							<div style="padding:14px 0 0 30px;"><span><a href="javascript:;" onclick="document.getElementById(\'preview\').style.display=\'none\';">关闭</a></span></div>
						</div>';
					//<div style="padding:14px 0 0 30px;"><span><a href="javascript:;" id="upload_again">更换</a></span><span style="padding-left:25px;"><a href="javascript:;" onclick="document.getElementById(\'preview\').style.display=\'none\';">关闭</a></span></div>

					echo '<input type="hidden" id="filename" value="upfile/' .$actual_image_name. '" />';
					echo '<input type="hidden" id="filename2" value="upfile/' .$filename_pre. '_thumb.'.$filename_ext.'" />';
					echo '<input type="hidden" id="filename3" value="upfile/' .$filename_pre. '_index.'.$filename_ext.'" />';
					
					try {
						$src_img = $path.$actual_image_name;
						$dst_img = $path.$filename_pre.'_thumb.'.$filename_ext; //缩略图路径
						$stat = img2thumb($src_img, $dst_img, 390, 490, 1, 0); //生成裁剪缩略图
						/*if($stat){
							echo 'Resize Image Success!<br />';
							echo '<img src="'.$dst_img.'" />';	
						}else{
							echo 'Resize Image Fail!';	
						}*/
						
						//生成首页显示展示图：最大宽度218
						createSmallImg($path, $actual_image_name, '_index', '218', '');
					} catch (Exception $e) {
						//
					}
				}
				else
					echo "上传图片失败! 请重试";
			}
			else
				echo "您上传的图片超过1M,请裁剪到小于1M再上传!";
			}
		else
			echo "您只能上传图片文件...";
	}
	else
		echo "请选择合适的图片文件上传!";
	exit;
}


/**
 * 生成缩略图
 * @author yangzhiguo0903@163.com
 * @param string     源图绝对完整地址{带文件名及后缀名}
 * @param string     目标图绝对完整地址{带文件名及后缀名}
 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
 * @param int        是否裁切{宽,高必须非0}
 * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
 * @return boolean
 */
function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
{
    if(!is_file($src_img))
    {
        return false;
    }
    $ot = fileext($dst_img);
    $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
    $srcinfo = getimagesize($src_img);
    $src_w = $srcinfo[0];
    $src_h = $srcinfo[1];
    $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
    $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

    $dst_h = $height;
    $dst_w = $width;
    $x = $y = 0;

    /**
     * 缩略图不超过源图尺寸（前提是宽或高只有一个）
     */
    if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
    {
        $proportion = 1;
    }
    if($width> $src_w)
    {
        $dst_w = $width = $src_w;
    }
    if($height> $src_h)
    {
        $dst_h = $height = $src_h;
    }

    if(!$width && !$height && !$proportion)
    {
        return false;
    }
    if(!$proportion)
    {
        if($cut == 0)
        {
            if($dst_w && $dst_h)
            {
                if($dst_w/$src_w> $dst_h/$src_h)
                {
                    $dst_w = $src_w * ($dst_h / $src_h);
                    $x = 0 - ($dst_w - $width) / 2;
                }
                else
                {
                    $dst_h = $src_h * ($dst_w / $src_w);
                    $y = 0 - ($dst_h - $height) / 2;
                }
            }
            else if($dst_w xor $dst_h)
            {
                if($dst_w && !$dst_h)  //有宽无高
                {
                    $propor = $dst_w / $src_w;
                    $height = $dst_h  = $src_h * $propor;
                }
                else if(!$dst_w && $dst_h)  //有高无宽
                {
                    $propor = $dst_h / $src_h;
                    $width  = $dst_w = $src_w * $propor;
                }
            }
        }
        else
        {
            if(!$dst_h)  //裁剪时无高
            {
                $height = $dst_h = $dst_w;
            }
            if(!$dst_w)  //裁剪时无宽
            {
                $width = $dst_w = $dst_h;
            }
            $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
            $dst_w = (int)round($src_w * $propor);
            $dst_h = (int)round($src_h * $propor);
            $x = ($width - $dst_w) / 2;
            $y = ($height - $dst_h) / 2;
        }
    }
    else
    {
        $proportion = min($proportion, 1);
        $height = $dst_h = $src_h * $proportion;
        $width  = $dst_w = $src_w * $proportion;
    }

    $src = $createfun($src_img);
    $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    if(function_exists('imagecopyresampled'))
    {
        imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    else
    {
        imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    $otfunc($dst, $dst_img);
    imagedestroy($dst);
    imagedestroy($src);
    return true;
}

function fileext($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}


//createSmallImg('uploads/', '2.jpg', '_index', '218', '');
/**
 * 创建等比例图片
 * $maxwidth和$maxheight只能传递一个，如果传最大宽度将自动计算高度
 * 如果创建成功返回文件保存的地址，否则返回false
 * @param $dir			保存路径
 * @param $source_img	原图片名称
 * @param $small_ex		缩率图文件名后缀（生成后的图片名如：name_thumb.jpg(原图名：name.jpg)）
 * @param $maxwidth		最大宽度
 * @param $maxheight	最大高度
 */
function createSmallImg($dir, $source_img, $small_ex="_index", $maxwidth='', $maxheight='') {
    
	if(!empty($maxwidth) && !empty($maxheight)) {
		return false;
	}
	
	$img_name=substr($source_img,0,-4);
	$img_ex = strtolower(substr(strrchr($source_img,"."),1));
	
	$imginfo = getimagesize($dir.$source_img);
	switch($imginfo[2])
	{
		case 'image/gif':
		case 1:
			$src_img=imagecreatefromgif($dir.$source_img);
			break;

		case 'image/pjpeg':
		case 'image/jpeg':
		case 2:
			$src_img=imagecreatefromjpeg($dir.$source_img);
			break;

		case 'image/x-png':
		case 'image/png':
		case 3:
			$src_img=imagecreatefrompng($dir.$source_img);
			break;

		default:
			return false;
	}
    
	/*switch($img_ex) {
		case "jpg":
			$src_img=imagecreatefromjpeg($dir.$source_img);
			break;
		case "gif":
			$src_img=imagecreatefromgif($dir.$source_img);
			break;
		case "png":
			$src_img=imagecreatefrompng($dir.$source_img);
			break;
	}*/
	
	$old_width=imagesx($src_img);
	$old_height=imagesy($src_img);
	if (intval($old_width) < intval($maxwidth)) return false; //如果原图小于指定宽度
	
	if(!empty($maxheight) && $old_height>=$maxheight) {
		$new_height=$maxheight;
		$new_width=round(($old_width*$new_height)/$old_height);
	} elseif(!empty($maxwidth) && $old_width>=$maxwidth) {
		$new_width=$maxwidth;
		$new_height=round(($old_height*$new_width)/$old_width);
	}
	
	if(!empty($new_width) || !empty($new_height)) {
		if($img_ex=="jpg" || $img_ex=="png") {
			$dst_img=imagecreatetruecolor($new_width,$new_height);
		} else {
			$dst_img=imagecreate($new_width,$new_height);
		}
		
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_width,$new_height,$old_width,$old_height);
		$smallname=$dir.$img_name.$small_ex.".".$img_ex;
		
		switch($img_ex) {
			case "jpg":
				imagejpeg($dst_img,$smallname,100);
				break;
			case "gif":
				imagegif($dst_img,$smallname);
				break;
			case "png":
				imagepng($dst_img,$smallname);
				break;
		}
	}
	return $smallname;
}
?>