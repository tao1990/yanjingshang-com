<?php
/**
 * 买家秀 用户中心
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',          '买家秀-美瞳隐形眼镜佩戴效果图-易视网');    
$smarty->assign('ur_here',             $position['ur_here']);  
$smarty->assign('get_new_fl',          index_get_new_fl(21));     //热门分类
$smarty->assign('topbanner',           ad_info(31,1));            //头部横幅广告

//页尾部分
$smarty->assign('helps',               get_shop_help());          //网店帮助文章
$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
/*------------------------------------页头 页尾 数据end------------------------------------*/

$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';
$ut = isset($_REQUEST['ut']) ? $_REQUEST['ut'] : '0';

if($_SESSION['user_id'] > 0)
{
	$smarty->assign('user_info',		get_user_info());
	$smarty->assign('user_mjx',			get_user_mjx($_SESSION['user_id'], 0));		//用户晒单(全部)
	$smarty->assign('user_mjx1',		get_user_mjx($_SESSION['user_id'], 1));		//用户晒单(佩戴效果图)
	$smarty->assign('user_mjx2',		get_user_mjx($_SESSION['user_id'], 2));		//用户晒单(晒订单)
	$smarty->assign('user_mjx3',		get_user_mjx($_SESSION['user_id'], 3));		//用户晒单(随便晒晒)
	$smarty->assign('categoriesp',		get_categories_option());						//商品分类列表
	
	//获取用户绑定的应用
	$sql_sync = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_app_sync') . ' WHERE user_id = '.$_SESSION['user_id'];
	$sync =  $GLOBALS['db']->getAll($sql_sync);
	$user_sync = array();
	if ($sync) {
		foreach ($sync as $k => $v) {
			$user_sync[$v['app_name']]['sid'] = $v['sid'];
			$user_sync[$v['app_name']]['user_id'] = $v['user_id'];
			$user_sync[$v['app_name']]['app_name'] = $v['app_name'];
			$user_sync[$v['app_name']]['add_time'] = $v['add_time'];
			$user_sync[$v['app_name']]['session_data'] = unserialize($v['session_data']);
			$user_sync[$v['app_name']]['sync_option'] = unserialize($v['sync_option']);
			$user_sync[$v['app_name']]['sync_status'] = $v['sync_status'];
			$user_sync[$v['app_name']]['sign_date'] = intval((time()-$v['add_time'])/86400); //上次签名至今的天数(判断签名是否还有效)
		}
	}

}


$smarty->assign('tj_mjx',	get_tj_mjx());	//推荐买家秀信息 页面右边显示

//已上传广告列表
$sql_ad = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx_ad') . " ORDER BY status DESC, sort ASC LIMIT 4";
$res_ad = $GLOBALS['db']->GetAll($sql_ad);
$smarty->assign('ad_list',  $res_ad);

$smarty->display('buyersshow_user.dwt');


//推荐买家秀信息
function get_tj_mjx() {
	$mjx = array();
	
	//2013.05.15取消：WHERE a.tj=1
	$sql = "SELECT a.*, b.user_name FROM " . $GLOBALS['ecs']->table('mjx') . " a 
	LEFT JOIN " . $GLOBALS['ecs']->table('users') . " b ON (a.user_id=b.user_id) 
	WHERE a.sh=1 ORDER BY a.vote DESC LIMIT 4";
	$res = $GLOBALS['db']->query($sql);
	while($row = $GLOBALS['db']->fetchRow($res)) {
		$mjx[$row['id']]['id'] = $row['id'];
		$mjx[$row['id']]['title'] = $row['title'];
		$mjx[$row['id']]['detail'] = $row['detail'];
		$mjx[$row['id']]['classid'] = $row['classid'];
		$mjx[$row['id']]['img'] = $row['img'];
		$mjx[$row['id']]['thumb_img'] = $row['thumb_img'];
		$mjx[$row['id']]['datetime'] = date('Y-m-d H:i:s', $row['datetime']);
		$mjx[$row['id']]['user_id'] = $row['user_id'];
		$mjx[$row['id']]['goods_id'] = $row['goods_id'];
		$mjx[$row['id']]['cat_id'] = $row['cat_id'];
		$mjx[$row['id']]['brand_id'] = $row['brand_id'];
		$mjx[$row['id']]['attr'] = $row['attr'];
		$mjx[$row['id']]['sh'] = $row['sh'];
		$mjx[$row['id']]['tj'] = $row['tj'];
		$mjx[$row['id']]['vote'] = $row['vote'];
		$mjx[$row['id']]['effect'] = $row['effect'];
		$mjx[$row['id']]['comments'] = $row['comments'];
		$mjx[$row['id']]['upload_type'] = $row['upload_type'];
		$mjx[$row['id']]['user_name'] = $row['user_name'];
		$mjx[$row['id']]['user_comments'] = get_user_mjx_comments($row['id']);	//用户评论
	}
	
	return $mjx;
}

//我的晒单
function get_user_mjx($userid=0, $upload_type=0) {
	$mjx = array();
	$where = '';
	if ($upload_type > 0) $where = " AND upload_type='$upload_type' ";
	if ($userid) {
		$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx') . " WHERE user_id='".$userid."' " .$where. " ORDER BY id DESC ";
		$res = $GLOBALS['db']->query($sql);
		while($row = $GLOBALS['db']->fetchRow($res)) {
			$mjx[$row['id']]['id'] = $row['id'];
			$mjx[$row['id']]['title'] = $row['title'];
			$mjx[$row['id']]['detail'] = $row['detail'];
			$mjx[$row['id']]['classid'] = $row['classid'];
			$mjx[$row['id']]['img'] = $row['img'];
			$mjx[$row['id']]['thumb_img'] = $row['thumb_img'];
			$mjx[$row['id']]['index_img'] = $row['index_img'];
			$mjx[$row['id']]['datetime'] = date('Y-m-d H:i:s', $row['datetime']);
			$mjx[$row['id']]['user_id'] = $row['user_id'];
			$mjx[$row['id']]['goods_id'] = $row['goods_id'];
			$mjx[$row['id']]['cat_id'] = $row['cat_id'];
			$mjx[$row['id']]['brand_id'] = $row['brand_id'];
			$mjx[$row['id']]['attr'] = $row['attr'];
			$mjx[$row['id']]['sh'] = $row['sh'];
			$mjx[$row['id']]['tj'] = $row['tj'];
			$mjx[$row['id']]['vote'] = $row['vote'];
			$mjx[$row['id']]['effect'] = $row['effect'];
			$mjx[$row['id']]['comments'] = $row['comments'];
			$mjx[$row['id']]['upload_type'] = $row['upload_type'];
			$mjx[$row['id']]['user_comments'] = get_user_mjx_comments($row['id']);	//用户评论
			
			//获取图片高度(宽度是218px,根据宽度,按照百分比获取)
			if (empty($row['index_img'])) {
				if (file_exists($row['thumb_img'])) {
					$imginfo = getimagesize($row['thumb_img']);
				}
				if ($imginfo) {
					$img_width = $imginfo[0];
					if ($img_width > 218) {
						$mjx[$row['id']]['img_width'] = 218;
						$percent = 218 / $img_width;
						$mjx[$row['id']]['img_height'] = floor($imginfo[1] * $percent);
					} else {
						$mjx[$row['id']]['img_width'] = $imginfo[0];
						$mjx[$row['id']]['img_height'] = $imginfo[1];
					}
				}
			}
			
		}
	}
	return $mjx;
}

//获取用户对我的买家秀的评论
function get_user_mjx_comments($mjxid=0) {
	$c_array = array();
	if ($mjxid) {
		//$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx_comment') . " WHERE mjx_id='$mjxid' AND is_show=1 ORDER BY id DESC LIMIT 10";
		$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx_comment') . " WHERE mjx_id='$mjxid' AND user_id_commentator>0 ORDER BY id DESC LIMIT 10";
		$res = $GLOBALS['db']->query($sql);
		while($row = $GLOBALS['db']->fetchRow($res)) {
			$c_array[$row['id']]['id'] = $row['id'];
			$c_array[$row['id']]['mjx_id'] = $row['mjx_id'];
			$c_array[$row['id']]['user_id_commentator'] = $row['user_id_commentator'];
			$c_array[$row['id']]['user_name_commentator'] = stripslashes($row['user_name_commentator']);
			$c_array[$row['id']]['comment'] = stripslashes($row['comment']);
			$c_array[$row['id']]['official_reply'] = stripslashes($row['official_reply']);
			$c_array[$row['id']]['datetime'] = date('Y-m-d H:i:s', $row['datetime']);
			$c_array[$row['id']]['is_show'] = $row['is_show'];
			$c_array[$row['id']]['is_on_index'] = $row['is_on_index'];
		}
	}
	return $c_array;
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
