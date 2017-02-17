<?php
/* 
 * ajax读取买家秀信息
 */
header("content-Type: text/html; charset=utf-8");
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$where="";

$ut=$_REQUEST['ut'];
if($ut) {@$where .= " and a.upload_type='".$ut."'";}

$cat_id=$_REQUEST['cat_id'];
if($cat_id) {@$where .= " and a.cat_id='".$cat_id."'";}

$attr=$_REQUEST['attr'];
if( $attr){@$where .= " and a.attr='".$attr."'";}

$sqlmjx="SELECT a.user_id,a.id mjxid,a.title,a.img,a.thumb_img, a.attr, a.detail,a.goods_id,a.detail, FROM_UNIXTIME(a.datetime, '%Y-%m-%d') as datetime,a.vote,a.effect,a.comments,a.upload_type,b.user_name FROM " . $GLOBALS['ecs']->table('mjx') . " a," . $GLOBALS['ecs']->table('users') . " b where 1=1 and a.sh=1 and a.user_id=b.user_id".$where." order by a.id desc ";

//$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$beginRow = !empty($_REQUEST['beginRow']) ? intval($_REQUEST['beginRow']) : 0; //每次读取的开始记录 就是 limit 0, 20 中的0
$beginRow = ($beginRow-1)*10;
$size = 4;

//$resmjx = $GLOBALS['db']->selectLimit($sqlmjx, $size, ($page-1) * $size);
$resmjx = $GLOBALS['db']->selectLimit($sqlmjx, $size, $beginRow);

$listmjx = array();
while ($rowmjx = $db->fetchRow($resmjx))
{	
	$sqlhsx = 'SELECT goods_name from  '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('mjx').' m where g.goods_id='.($rowmjx['goods_id']+0).' and g.goods_id= m.goods_id;';

	$hscountxd = $GLOBALS['db']->getOne($sqlhsx);	 
	if(trim($hscountxd)){$rowmjx['title']=$hscountxd;}
	
	$rowmjx['user_comments'] = get_mjx_comments($rowmjx['mjxid']); //获取评论
	
	$listmjx[] = $rowmjx;
}
//print_r($listmjx);
//echo json_encode($listmjx);

$str = '';
foreach ($listmjx as $v) {
	$str .= '<li class="shaitu_detail">';
	
	//按百分比获取图片的高(必须的,否则瀑布样式会出错)
	$img_height = 0;
	if (file_exists('http://www.easeeyes.com/'.$v['img'])) {
		$imginfo = getimagesize('http://www.easeeyes.com/'.$v['img']);
        
	}
	if (@$imginfo) {
		$img_width = $imginfo[0];
		$percent = 218 / $img_width;
		$img_height = floor($imginfo[1] * $percent);
	}else{
	   $img_height = 218;
	}
	
    $str .= '<div class="shaitu_img">
                 <a href="buyersshow_goods.php?mjxid='.$v['mjxid'].'"><img src="http://www.easeeyes.com/'.$v['thumb_img'].'"  /></a>
              </div>';
    
    $str .= ' <div class="shaitu_fav">
                  <span><img onclick="showUser('.$v['vote'].', '.$v['goods_id'].', '.$v['user_id'].', '.$v['mjxid'].')" src="/wap/images/fav.png"><em id="vote'.$v['mjxid'].'">'.$v['vote'].'</em></span>
                  <span><img src="/wap/images/mess_one.png"><em>'.$v['comments'].'</em></span>
              </div>';
    
    
    $str .= '<div class="shaitu_name">
                  <span>'.$v['user_name'].'</span>说
              </div>
              <div class="shaitu_time">
                  '.$v['datetime'].'
              </div>
              <div class="shaitu_mess">
                  '.$v['detail'].'
              </div>
        </li>';
	
}
echo $str;


//获取该买家秀的评论信息
function get_mjx_comments($mjxid=0) {
	$comments_array = array();
	if ($mjxid) {
		$sql = 'SELECT * FROM  '.$GLOBALS['ecs']->table('mjx_comment'). ' WHERE mjx_id= '.$mjxid.' AND is_show=1 AND is_on_index=1 LIMIT 3';
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) {
			foreach ($res AS $row) {
				$comments_array[] = array (
						'user_id_commentator'		=>	$row['user_id_commentator'],
						'user_name_commentator'		=>	$row['user_name_commentator'],
						'comment'	=>	$row['comment']
				);
			}
		}
	}
	return $comments_array;
}
?>