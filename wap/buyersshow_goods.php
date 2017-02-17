<?php
/* 
 * 买家秀 商品购买页
 */
session_start();

header("content-Type: text/html; charset=utf-8");
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
include_once(ROOT_PATH . 'includes/lib_transaction.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
date_default_timezone_set('PRC');

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',          '真人佩戴效果图-易视网买家秀');
$smarty->assign('ur_here',             '买家秀');  
$smarty->assign('get_new_fl',          index_get_new_fl(21));     //热门分类
$smarty->assign('topbanner',           ad_info(31,1));            //头部横幅广告

//页尾部分
$smarty->assign('helps',				get_shop_help());					//网店帮助文章
$smarty->assign('new_articles_botter',	index_get_new_articles_botter());	//关于我们行	
$smarty->assign('botbanner',			ad_info(12,8));						//营业执照行
$smarty->assign('sale_order6',			yi_sale_sort_list(6, 5) );			//热销

$smarty->assign('ad_line1_r1',          ad_info(52,1));			//首页右上广告
$smarty->assign('latest_promote',       get_latest_promote());	//限时抢购
/*------------------------------------页头 页尾 数据end------------------------------------*/

$mjxid = isset($_REQUEST['mjxid']) ? $_REQUEST['mjxid'] : '0';
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

if(!$mjxid) {
	ecs_header("Location: ./\n");
	exit;
}

if($_SESSION['user_id'] > 0) {
	@$smarty->assign('user_info',	get_user_info());
}

//提交买家秀的评论
if ($act == 'comment') 
{
	$comment = isset($_REQUEST['comment']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['comment']))) : '';
	$user_id_commentator = isset($_REQUEST['user_id_commentator']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['user_id_commentator']))) : '0';
	$user_name_commentator = isset($_REQUEST['user_name_commentator']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['user_name_commentator']))) : '';
	$datetime = time();
	
	if (!empty($comment) && $user_id_commentator) 
	{
		$sql = "INSERT INTO " . $GLOBALS['ecs']->table('mjx_comment') . " (mjx_id, user_id_commentator, user_name_commentator, comment, datetime, is_show) VALUES ('$mjxid', '$user_id_commentator', '$user_name_commentator', '$comment', '$datetime', 1)";
		$sql_update = "UPDATE " . $GLOBALS['ecs']->table('mjx') . " SET comments=comments+1 WHERE id='$mjxid'";
		
		$sqlres = $GLOBALS['db']->query($sql);
		$GLOBALS['db']->query($sql_update);
	
		//---------------------------------站内信通知-------------------------------------------//
		/*if($sqlres)
		{
			$title = "您的买家秀收到新的点评，快去看看吧！";
			$msg   = "亲爱的：<b>".$user['user_name']."</b><br/>您好！您上传的买家秀图片收到了瞳学的点评，<a href=\'http://www.easeeyes.com/buyersshow_goods.php?mjxid=".$mjxid."\'>请点击查看</a>";		
			$sql = "select m.user_id, u.user_name from ecs_mjx as m left join ecs_users as u on m.user_id=u.user_id where m.id=".$mjxid." limit 1";
			$mjxer     = $GLOBALS['db']->getRow($sql);
			$user_id   = $mjxer['user_id'];
			$user_name = $mjxer['user_name'];
			$sql  = "insert into ecs_user_msg(user_id, user_name, add_time, title, msg, extension, extension_id) ".
					"values(".$user_id.", '".$user_name."', ".$_SERVER['REQUEST_TIME'].", '".$title."', '".$msg."', 'mjx_comment', ".$mjxid.")";
			$res  = mysql_query($sql);
			if($res){ unread_user_msg($user_id); }
		}*/
		
		//同步到外网
		/*if (isset($_POST['sync_blog'])) 
		{
			//echo '$_SESSION["token"]["access_token"] = ' . $_SESSION['token']['access_token'] . '<br/>';
			foreach ($_POST['sync_blog'] as $sync_v) 
			{
				//同步到新浪微博
				if ($sync_v == 'sync_sina' && isset($_SESSION['token']['access_token'])) 
				{
					require_once(dirname(__FILE__) . '/api/sina/weibodemo/config.php');
					require_once(dirname(__FILE__) . '/api/sina/weibodemo/saetv2.ex.class.php');
					
					$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
					
					$ret = $c->update($comment);//发送微博
					if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
						//echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
					} else {
						//echo "<p>发送成功</p>";
					}
				}
			}
		}*/
	}
	
	//exit;
}

$mjx_info = get_mjx_info($mjxid);							//买家秀信息
if(!$mjx_info || empty($mjx_info['goods_id'])) {
	ecs_header("Location: ./\n");
	exit;
}

$goods   = get_goods_info($mjx_info['goods_id']);			//商品详情
$goodsds = get_goodsds_info($mjx_info['goods_id']);			//度数列表
$link_goods = get_link_goods_list($mjx_info['goods_id']);	//关联商品
$goods_sg = if_sg($mjx_info['goods_id']);					//【散光片数据】

if($goods_sg) 
{
	$smarty->assign('goods_is_sg', $goods_sg);
	$smarty->assign('goods_sgds', get_sgds_info($goods_id));//散光度数列表		
}

$smarty->assign('mjxid',			$mjxid);					//买家秀ID
$smarty->assign('daren',			get_tj());					//美瞳达人
$smarty->assign('mjx_info',			$mjx_info);
$smarty->assign('mjx_comments',		get_mjx_comments($mjxid));	//买家秀评论
@$smarty->assign('user_goods', 		$user_goods);
$smarty->assign('goods', 			$goods);
@$smarty->assign('rank_prices',         get_user_rank_prices($goods_id,  $goods['shop_price']));    // 会员等级价格
$smarty->assign('goodsds', 			$goodsds);
$smarty->assign('link_goods', 		$link_goods);
$smarty->assign('hot_goods',		get_rand_hot_sales(3));		//热销：随机取3个类别的热销(展示在用户的购物单)

//已登录新浪微博
if (isset($_SESSION['token']['access_token'])) 
{
	//echo '$_SESSION["token"]["access_token"] = ' . $_SESSION['token']['access_token'];
	$smarty->assign('weibo_login',	'1');
}

$smarty->assign('page_title',          $goods['goods_name'].'-真人佩戴效果图-易视网买家秀');

$smarty->display('buyersshow_goods.dwt');

/*-----------------------------------------------------------------------------*/
//美瞳达人
function get_tj() 
{
	$tj = array();
	$sql = "SELECT id, img, user_id, tj_img FROM " .$GLOBALS['ecs']->table('mjx'). " WHERE tj=1 AND sh=1 ORDER BY id DESC LIMIT 8";
	$res = $GLOBALS['db']->getAll($sql);
	if ($res) {
		foreach ($res AS $row) {
			$tj[] = array (
					'id'		=>	$row['id'],
					'img'		=>	$row['img'],
					'user_id'	=>	$row['user_id'],
					'tj_img'	=>	$row['tj_img']
			);
		}
	}
	return $tj;
}

//获取买家秀信息
function get_mjx_info($mjxid=0) 
{
	$mjx_info = array();
	if ($mjxid) {
		$sql = "SELECT a.*, b.user_name FROM " . $GLOBALS['ecs']->table('mjx') . " a LEFT JOIN " . $GLOBALS['ecs']->table('users') . " b ON (a.user_id=b.user_id) WHERE a.id='".$mjxid."' LIMIT 1";
		$mjx_info = $GLOBALS['db']->getRow($sql);
		
		$mjx_info['publish_time'] = date('Y-m-d', $mjx_info['datetime']);
		
		$mjx_info['img_width'] = 0;			//原图的宽
		$mjx_info['img_height'] = 0;		//原图的高
		$mjx_info['thumb_img_width'] = 0;	//缩略图的宽度
		$mjx_info['thumb_img_height'] = 0;	//缩略图的高度
		
		if (file_exists($mjx_info['img'])) {
			$imginfo = getimagesize($mjx_info['img']);
			if ($imginfo) {
				$mjx_info['img_width'] = $imginfo[0];
				$mjx_info['img_height'] = $imginfo[1];
			}
		}
		
		if (file_exists($mjx_info['thumb_img'])) {
			$imginfo = getimagesize($mjx_info['thumb_img']);
			if ($imginfo) {
				$mjx_info['thumb_img_width'] = $imginfo[0];
				$mjx_info['thumb_img_height'] = $imginfo[1];
			}
		}
	}
	return $mjx_info;
}

//获取该买家秀的评论信息
function get_mjx_comments($mjxid=0) 
{
	$c_array = array();
	if ($mjxid) {
		//$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx_comment') . " WHERE mjx_id='$mjxid' AND is_show=1 ORDER BY id DESC LIMIT 100";
		$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('mjx_comment') . " WHERE mjx_id='$mjxid' AND user_id_commentator>0 ORDER BY id DESC LIMIT 100";
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

/* ----------------------------------------------------------------------------
 * 商品是否是散光片 
 * ----------------------------------------------------------------------------
 * goods_id 产品id  是：true  不是:false
 */
function if_sg($goods_id)
{
	$retu = false;

	//散光片id在ecs_goods_cat表中的cat_id=15。
	if(!empty($goods_id)){
		$sql = "select * from ".$GLOBALS['ecs']->table('goods_cat')." where cat_id=15 and goods_id=".$goods_id;
		$res = $GLOBALS['db']->getRow($sql);
		if(!empty($res)){$retu = true;}
	}
	return $retu;
}

/* ----------------------------------------------------------------------------
 * 函数yi：取得商品的关联商品信息列表
 * ----------------------------------------------------------------------------
 */
function get_link_goods_list($goods_id=0)
{
	$goods_id = intval($goods_id);
	$sql = "select l.*, g.goods_img, g.goods_thumb, g.original_img from ecs_link_goods as l left join ecs_goods as g on l.link_goods_id=g.goods_id where l.goods_id=".
			$goods_id." limit 0,10;";

	return $GLOBALS['db']->GetAll($sql);
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:获得最新的限时抢购商品
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_latest_promote()
{
	$now = $_SERVER['REQUEST_TIME'];
    $sql = "SELECT goods_id, goods_name, promote_start_date as start_time, promote_end_date as end_time, goods_thumb, goods_img, promote_img ".
            " FROM " . $GLOBALS['ecs']->table('goods') ." WHERE is_promote=1 AND is_on_sale=1 AND is_delete=0 " .
            " AND promote_start_date <= '$now' AND promote_end_date >= '$now' ORDER BY start_time DESC LIMIT 1";
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

/**
 * 获取热销商品(普通/彩色/框架 随机)
 */
function get_rand_hot_sales ($num = 1) 
{
	$sqlc   = "select cat_id from ".$GLOBALS['ecs']->table('category')." where parent_id IN (1,6,159) and is_show = 1;";
	$cat_id = $GLOBALS['db']->GetAll($sqlc);
	
	$in = "(0";
	foreach($cat_id as $k => $v)
	{
		if(!empty($v['cat_id']))
		{
			$in .= ",".$v['cat_id'];
		}
	}
	$in .= ")";
	
	//所有热门商品
	$all_hot = $GLOBALS['db']->GetAll("select goods_id, goods_name, goods_img from ecs_sales_charts where cat_id in".$in);
	
	$rand_hot_array = array();
	$rand_keys = array_rand($all_hot, $num); //取随机几个，返回数组索引
	foreach ($rand_keys as $key => $value) {
		$rand_hot_array[$key]['goods_id'] = $all_hot[$value]['goods_id'];
		$rand_hot_array[$key]['goods_name'] = $all_hot[$value]['goods_name'];
		$rand_hot_array[$key]['goods_img'] = $all_hot[$value]['goods_img'];
	}
	
	return $rand_hot_array;
}

/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_user_rank_prices($goods_id, $shop_price)
{
	$goods_id = intval($goods_id);
    $sql = "SELECT rank_id, IFNULL(mp.user_price, (r.discount*".floatval($shop_price).")/100) AS price, r.rank_name, r.discount " .
            'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
            "ON mp.goods_id =".$goods_id." AND mp.user_rank = r.rank_id " .
            "WHERE r.show_price = 1 OR r.rank_id = ".$_SESSION['user_rank'];
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[$row['rank_id']] = array(
                        'rank_name' => htmlspecialchars($row['rank_name']),
                        'price'     => price_format($row['price']));
    }

    return $arr;
}

?>