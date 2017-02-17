<?php
/* =======================================================================================================================
 * 商城页面 产品详情页【2012/3/20】【Author:yijiangwen】【TIME:2012/11/26】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php'); 
if((DEBUG_MODE & 2) != 2){$smarty->caching = true;}

$goods_id = isset($_REQUEST['id']) ? addslashes(intval($_REQUEST['id'])) : 0;

//------------------------------------记录浏览历史--------------------------------//
if(!empty($_COOKIE['ECS']['history']))
{
    $history = explode(',', $_COOKIE['ECS']['history']);
    
    array_unshift($history, $goods_id);
    $history = array_unique($history);
    while(count($history) > $_CFG['history_number'])
    {
        array_pop($history);
    }
    //    unset($_COOKIE['ECS']);
     setcookie("ECS[history]", implode(',', $history), gmtime() + 3600 * 24 * 30);
    //var_dump($_COOKIE['ECS']);
}
else
{
    setcookie('ECS[history]', $goods_id, gmtime() + 3600 * 24 * 30);
}
$goods   = get_goods_info($goods_id);  //商品详细信息
/*-------------------------------------------------------------【外部活动过来的】----------------------------------------------------------------------*/
$now    = $_SERVER['REQUEST_TIME'];
$source = isset($_GET['from'])&&(!empty($_GET['from'])) ? trim($_GET['from']): '';
if(!empty($source))
{
    $arrfrom = explode('-_-', $source);
    $from    = array();
    if(!empty($arrfrom[0]) && !empty($arrfrom[2]) && !empty($goods_id) && is_numeric($arrfrom[2]))
    {
        $from = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('source')." where goods_id=$goods_id and source='$arrfrom[0]' and add_time=".$arrfrom[2]." limit 1;");
    }
    //var_dump($from);die;
    if(!empty($from))
    {
        if($from['start_time']<$now && $from['end_time']>$now && $from['inventory']>0)
        {
            //1.把这个活动的最新的信息记录到cookies中。供后边进行价格操作。记录专享商品的rec_id，如果有多个商品，用','添加。cookie有效时间30分钟。
            $from_rec_id = isset($_COOKIE['source_rec_id'])? trim($_COOKIE['source_rec_id']): '';

            if(!empty($from['rec_id']))
            {
                if(empty($from_rec_id))
                {
                    $from_rec_id = $from['rec_id'];
                }
                else
                {
                    $ep_from = explode(',', $from_rec_id);
                    if(!in_array($from['rec_id'], $ep_from))
                    {
                        $from_rec_id = $from_rec_id.','.$from['rec_id'];
                    }
                }
            }
            $cookie_time = ($from['end_time'] - $now>1800)? $now+1800: $from['end_time'];//cookie30分钟
            //setcookie('source_rec_id', $from_rec_id, $cookie_time);
            setcookie('source_rec_id', $from_rec_id, $from['end_time']);

            //2.控制这个活动在页面的显示价格
            //yi:专享活动限制活动商品数量
            $from['can_add'] = true;
            if($from['number_limit']>0)
            {
                $sql       = "select IFNULL(sum(goods_number),0) from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' and extension_id='$from[rec_id]' ";
                $hv_source = $GLOBALS['db']->getOne($sql);
                if($hv_source>=$from['number_limit'])
                {
                    $from['can_add'] = false;
                }
            }
            //3. 优惠金额以及是否显示同类商品
            $from['saving'] = $goods['shop_price'] - $from['exclusive_price'];
            if($from['exclusive_price'] > $goods['shop_price']){
                $from['show_sk'] = 1;
            }else{
                $from['show_sk'] = false;
            }
            $smarty->assign('source', $from);
        }
        // 针对多个产品（一个专享链接进来，多个产品享受专享价，用于美瞳的不同花色）
        $goods_arr1 = array(5330,5331,5332,5333,5334,5335);
        if(in_array($goods_id,$goods_arr1)){
			set_same_goods($goods_arr1,$arrfrom[0]);
        }
        $goods_arr2 = array(4142,4143,4144,4146);
		if(in_array($goods_id,$goods_arr2)){
			set_same_goods($goods_arr2,$arrfrom[0]);
		}
        $goods_arr3 = array(981,982,983);
		if(in_array($goods_id,$goods_arr3)){
			set_same_goods($goods_arr3,$arrfrom[0]);
		}
    }
    else
    {
        //商品有source cookies，但是有from，但是from不全。
        $smarty->assign('source', get_cookies_source($goods_id));
    }
}
else if(isset($_COOKIE['source_rec_id']) && !empty($_COOKIE['source_rec_id']))
{
    $smarty->assign('source', get_cookies_source($goods_id));
}
else
{}
/*-------------------------------------------------------------【外部活动过来的】end-------------------------------------------------------------------*/

//-------------------------------------【评论数据】-------------------------------------------//
$page=isset($_GET['page'])?intval($_GET['page']):1; 
$perpage = 15;
$offset=($page-1)*$perpage; 
$comment_rank = empty($_REQUEST['rank'])? 0:addslashes($_REQUEST['rank']);

$comment = get_comment($goods_id,0,1,0,$comment_rank,$offset,$perpage);  
$comment_num = get_comment_num($goods_id,0,1,0,$comment_rank);

$smarty->assign('comment', $comment);
$smarty->assign('comment_num', $comment_num);

//yi:附加数据
$append = $GLOBALS['db']->GetRow("select * from ".$GLOBALS['ecs']->table('goods_append')." where goods_id=".$goods_id);
$smarty->assign('append',  $append);

$act = isset($_REQUEST['act']) ? $_REQUEST['act']:'';
if($act=='all_comment'){
    
    if(isset($_REQUEST['get_more'])){
        $comment_list = '';
        foreach($comment as $v){
            $comment_list.='
            <div class="discuss_two_common">
         <div>
            <p class="discuss_name">'.$v['user_name'].'</p>
            <p class="discuss_date">发表于'.$v['add_time'].'</p>
         </div>
         <div class="user_discuss">'.$v['content'].'</div>';
         if(!empty($v['re_comment'])){
           foreach($v['re_comment'] as $v2)
           {
            $comment_list.=
            '<div class="content yishi">
             <div class="ys_return">'.$v2['user_name'].':</div>
             <div class="ys_return_con">'.$v2['content'].'</div>
             <div class="clear"></div>
             </div>
            ';
           }
         }
         $comment_list.='</div>';
        }
        echo $comment_list;die;
    }
    $smarty->assign('ur_here',  '全部评论');
    $smarty->assign('page_title',  '全部评论 - 易视网手机版');
    $smarty->assign('goods_id',  $goods_id);
    $smarty->display('goods_comment.dwt');
    die;
}


//-------------------------------------【评论数据】-------------------------------------------//





if($_SESSION['user_id'] > 0){$smarty->assign('user_info', get_user_info());}
$cache_id = $goods_id.'-'.$_SESSION['user_rank'].'-'.$_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));
if(!$smarty->is_cached('goods.dwt', $cache_id))
{

//-------------------------------------【散光片数据】-------------------------------------------//
$goods_sg = if_sg($goods_id);
$smarty->assign('goods_is_sg', $goods_sg);
if($goods_sg)
{
    $smarty->assign('goods_sgds', get_sgds_info($goods_id));//散光度数列表	
    
    //-------------------------------------【轴位数据】-------------------------------------------//
    $zw = array();
    for($i=1;$i<=36;$i++){
        $zw[$i] = $i*5;
    }	
    $smarty->assign('goods_zw', $zw);//轴位数据列表	
}




//-------------------------------------【散光片数据】-------------------------------------------//



//============================================================【放大镜功能】============================================================//
		$ga_first = $GLOBALS['db']->GetRow("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=1 limit 1;");
		$ga_list  = $GLOBALS['db']->GetAll("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=0");
		array_unshift($ga_list, $ga_first);
		//去除数据库中图片路径的'/'号 BY:TAO
		$goods['goods_img'] = str_replace("/b2c/","b2c/",$goods['goods_img']);
        $goods['goods_thumb'] = str_replace("/b2c/","b2c/",$goods['goods_thumb']);
        $goods['original_img'] = str_replace("/b2c/","b2c/",$goods['original_img']);
		foreach($ga_list as $k=>$v){
			$ga_list[$k]['img_url'] = str_replace("/b2c/","b2c/",$v['img_url']);
            $ga_list[$k]['thumb_url'] = str_replace("/b2c/","b2c/",$v['thumb_url']);
            $ga_list[$k]['img_original'] = str_replace("/b2c/","b2c/",$v['img_original']);
            $ga_list[$k]['img_pure'] = str_replace("/b2c/","b2c/",$v['img_pure']);
		}
		$smarty->assign('gallery',      $ga_list);

$goodsds = get_goods_ds($goods_id);    //度数

if($goods === false || $goods['shop_price'] == 0)
{
    header('HTTP/1.1 404 Not Found'); 
    $smarty->display('error.htm');
    exit;
}
else
{    
    //xuyizhi:秒杀商品,不能从此访问,跳到首页
    if ($goods['cat_id'] == 138 && $goods['is_promote'] == 1) {
       date_default_timezone_set('PRC'); 
	   $b_time = mktime(11, 0, 0, date("m"), date("d"), date("Y"));
	   $e_time = mktime(12, 0, 0, date("m"), date("d"), date("Y"));
	   if($goods['promote_start_date'] == $b_time && $goods['promote_end_date'] == $e_time)
	   {
            header('HTTP/1.1 404 Not Found'); 
			$smarty->display('error.htm');
			exit;
	   }
	}
    if($goods['is_promote'] == 1){
        $goods['saving'] = $goods['rank_price'] - $goods['promote_price_org'];
    }

    $link_goods = ($goods['cat_id']!=138)? get_link_goods_list($goods_id) :get_link_goods_list_un($goods_id);//关联商品
    //验光单功能
    if(!empty($_SESSION['user_id'])){
    	$receipt_type = ($goods['goods_type'] != 15)? 1: 2;
    	$sql = "select * from ecs_user_ds where user_id=".$_SESSION['user_id']." and receipt_type=".$receipt_type;
    	$user_ds = $GLOBALS['db']->GetAll($sql);
    	$smarty->assign('user_ds',      $user_ds);
    	$smarty->assign('user_id',      $_SESSION['user_id']);
    }
	
    //=========================================商品数据写入模板=============================================||
    $properties = get_goods_properties($goods_id);//获得商品的规格和属性
    $brand_sq = array(2, 3, 4, 6, 13, 15, 16, 17, 20, 23, 35, 39, 53, 55,61, 65,72,85, 86, 87, 91, 94, 95,96,97, 98, 99, 100, 101, 103, 104, 105, 106, 109, 110, 111, 117, 120,121,122,123,124,125,126,128,160,164,139,141,132);		      //品牌授权书
		$brand_sq_double = array(35, 153, 191, 197, 202, 203, 215);//第2个品牌授权书。
		if(in_array($goods['brand_id'], $brand_sq))
		{
			$goods['brand_sq']  = 1;			
			$goods['brand_img'] = in_array($goods['cat_id'], $brand_sq_double)? $goods['brand_id'].'_2': $goods['brand_id'];
		}
		else
		{
		}
		$goods['click_count']   = ceil($goods['click_count']*1); //销售数量
        
        $goods['goods_desc'] = str_replace('width: 750px','width: 100%',$goods['goods_desc']);
        $goods['goods_desc'] = str_replace('width=','width="100%"',$goods['goods_desc']);
        $goods['goods_desc'] = str_replace('height=','height="auto"',$goods['goods_desc']);
        $goods['goods_desc'] = str_replace('/images','http://www.easeeyes.com/images',$goods['goods_desc']);

        //Tao：获得商品是否设置包邮
        $now = time();
        $is_by = $GLOBALS['db']->getOne("SELECT rec_id FROM ecs_free_ship WHERE goods_id = ".$goods_id.
            " AND `start_time`<='$now' AND `end_time`>='$now'".
            " AND free_num = 1 AND ext_code = 0 AND kind = 0");
        if($is_by){
            $goods['is_by'] = 1;
        }

        $smarty->assign('goods',               $goods);
		$smarty->assign('goodsds',             $goodsds);
		$smarty->assign('goods_id',            $goods['goods_id']);
		$smarty->assign('back_act',            "goods".$goods_id.".html"); 
		$smarty->assign('user_rank',           isset($_SESSION['user_rank'])? intval($_SESSION['user_rank']): 0 ); //会员等级
		$user_rank_price = get_user_rank_prices($goods_id, $goods['shop_price']);
        $smarty->assign('link_goods',          $link_goods);                                     // 关联商品
		$smarty->assign('rank_prices',         $user_rank_price);                 // 会员等级价格
		$smarty->assign('vip_prices',          $user_rank_price[2]['price_pure']);// 会员vip价格

		/*----------------------------------------------产品页【有问必答】列表------------------------------------------------------------*/

		//页面大小
		$page_size = 5;

		//总记录数，当前页数，总页数
		$count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".$GLOBALS['ecs']->table('feedback')." WHERE goods_id =".$goods_id);
		$page  = (isset($_GET['pages'])&&!empty($_GET['pages']))? intval($_GET['pages']): 1; 
		$page_count = ($count>0)? ceil($count/$page_size): 1;

		//前一页,后一页
		$page_prev = ($page>1) ? $page-1 : 1;
		$page_next = ($page<$page_count)? $page+1 : $page_count;

		//所有提问留言
		$feedback = array();
		$sqlf = "select * from ".$GLOBALS['ecs']->table('feedback')." where goods_id=".$goods_id.
				" and msg_status=1 order by msg_time desc limit ".($page-1)*$page_size.",".$page_size.";";
		$feedback = $GLOBALS['db']->GetAll($sqlf);

		//遍历每条提问留言，然后找到它的回复留言。
		foreach($feedback as $k => $v)
		{
			$msg_id = $feedback[$k]['msg_id'];
			$sql_bk = "select msg_content from ".$GLOBALS['ecs']->table('feedback')." where parent_id='$msg_id' limit 1";
			$msg_re = $GLOBALS['db']->GetOne($sql_bk);
			$feedback[$k]['msg_re'] = $msg_re;
			$feedback[$k]['msg_time'] = date('Y-m-d', $feedback[$k]['msg_time']);
		}
        
		$smarty->assign("total1",     $count);
		$smarty->assign("page1",      $page);
		$smarty->assign("pagesize1",  $page_size);
		$smarty->assign("pagecount1", $page_count);	
		$smarty->assign("prev",       $page_prev);			      
		$smarty->assign("next",       $page_next);
		$smarty->assign("feedback",   $feedback);
        $smarty->assign('bought_goods',        get_also_bought($goods_id));                      // 购买了该商品的用户还购买了哪些商品
		/*----------------------------------------------产品页【有问必答】列表------------------------------------------------------------*/

		//xu:产品属性功能
		$smarty->assign('attrs',    ($goods['goods_type']==16)? get_goods_attr_pure($goods_id): get_goods_all_attr($goods_id));		
		$smarty->assign('attr_kj',  get_kuangjia_attr($goods_id));

		

		//===============================================【产品页面_赠品(没有金额限制且免费)提示】=========================================//
		$fav = include_goods_fav($goods_id, -1);
		$gift_tip = array();
		$is_cx_tip = 0; // 促销标签标记
		foreach($fav as $k => &$v)
		{
			if(!empty($v['gift_tip']))
			{
                $arr = explode(',',$v['gift_tip']);
                $v['gift_tip'] = $arr[0];
                $v['gift_tip_url'] = @trim($arr[1]);
			}
			else
			{
				continue;
			}
            foreach($v['gift'] as $key =>&$val){
                $thumb= $GLOBALS['db']->GetRow("select goods_thumb from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$val['id']);
                $val['thumb'] =$thumb['goods_thumb'];
            }
            $gift_tip[] = $v['act_type'];
			// 通过活动类型判断页面是否增加促销标签
			if($v['act_type'] == 1 || $v['act_type'] == 2){
				$is_cx_tip = 1;
			}
		}
        $gift_tip = array_unique($gift_tip);
    //var_dump($gift_tip); exit();
		$smarty->assign('is_cx_tip', $is_cx_tip);
		$smarty->assign('gift_tip', $gift_tip);
		$smarty->assign('arr', $fav);
		$smarty->assign('fav',      full_fav());
    }
$smarty->assign('ur_here', "产品详情");
$smarty->assign('page_title', "产品详情 - 易视网手机版");

}

$smarty->display('goods.dwt',$cache_id);

//======================================================================【函数】======================================================================//

/**
 * 获取评论
 * @$id_value  对应id(goods_id/article_id)
 * @$comment_type 评论类型(0:商品,1:文章)
 * @$status 是否被管理员批准显示(1:是,0:未批准显示)
 * @$parent_id 父id
 * @$rank 评论等级 1:好评2：中评3：差评
 */
function get_comment($id_value=0,$comment_type,$status=1,$parent_id=0,$rank=1,$offset,$perpage){
    $res=array();
    $rankStr = comment_rank_str($rank);
    $sql =mysql_query("select * from ".$GLOBALS['ecs']->table('comment')." where id_value= ".$id_value." and comment_type = ".$comment_type." and status = ".$status." and parent_id=".$parent_id.$rankStr." order by comment_id desc limit ".$offset.",".$perpage);	
    
    while($arr=mysql_fetch_array($sql,MYSQL_ASSOC)){
        $re_comment = $GLOBALS['db']->getAll("select * from ecs_comment where id_value=".$id_value." and parent_id=".$arr['comment_id']);
        $arr['add_time']=date('Y-m-d',$arr['add_time']);
        if(!empty($re_comment)){
            $arr['re_comment'] = $re_comment;
        }
        $res[]=$arr;
    }
    return $res;
	//return $GLOBALS['db']->getAll($sql);
}
/**
 * 获取评论数
 * @$id_value  对应id(goods_id/article_id)
 * @$comment_type 评论类型(0:商品,1:文章)
 * @$status 是否被管理员批准显示(1:是,0:未批准显示)
 * @$parent_id 父id
 * @$rank 评论等级
 */
function get_comment_num($id_value=0,$comment_type,$status=1,$parent_id=0,$rank=1){
    $rankStr = comment_rank_str($rank);
    $sql = "select count(*) as num from ".$GLOBALS['ecs']->table('comment')." where id_value= ".$id_value." and comment_type = ".$comment_type." and status = ".$status." and parent_id=".$parent_id.$rankStr;	
	return $GLOBALS['db']->getOne($sql);
}
/**
 * 评论等级
 * 1:4星-5星好评
 * 2:2-3中评
 * 3:1差评
 */
function comment_rank_str($rank){
    if($rank==1){
        $str = ' and comment_rank>=4 ';
    }elseif($rank==2){
        $str = ' and comment_rank>=2 and comment_rank<=3';
    }elseif($rank==3){
        $str = ' and comment_rank>0 and comment_rank <=1';
    }elseif($rank==0){
        $str = '';
    }
    return $str;
}

/**
 * 获取商品信息
 */
function get_goods_item($goods_id){
    $sql = "select * from ".$GLOBALS['ecs']->table('goods')." where goods_id= ".$goods_id;
    return $GLOBALS['db']->getAll($sql);	
}

/**
 * 获取关联商品
 */
function get_link_goods_list($goods_id=0)
{
	$sql = "select l.*, g.goods_img, g.goods_thumb, g.original_img from ecs_link_goods as l left join ecs_goods as g on l.link_goods_id=g.goods_id where l.goods_id=".
			$goods_id." and g.cat_id<>138 and g.goods_number>0 and g.is_delete=0 and g.is_on_sale=1 and g.is_alone_sale=1 limit 0,10;";
	return $GLOBALS['db']->GetAll($sql);
}

/* ----------------------------------------------------------------------------
 * 函数yi：取得商品的关联商品信息列表【非卖品只关联非卖品】
 * ----------------------------------------------------------------------------
 */
function get_link_goods_list_un($goods_id=0)
{
	$sql = "select l.*, g.goods_img, g.goods_thumb, g.original_img from ecs_link_goods as l left join ecs_goods as g on l.link_goods_id=g.goods_id where l.goods_id=".
			$goods_id." and g.cat_id=138 and g.goods_number>0 and g.is_delete=0 and g.is_on_sale=1 and g.is_alone_sale=1 limit 0,10;";
	return $GLOBALS['db']->GetAll($sql);
}


/* ----------------------------------------------------------------------------
 * 商品是否是散光片 
 * ----------------------------------------------------------------------------
 * goods_id 产品id  是：true  不是:false
 */
function if_sg($goods_id){
	$retu = false;

	//散光片id在ecs_goods_cat表中的cat_id=15。
	if(!empty($goods_id)){
		$sql = "select * from ".$GLOBALS['ecs']->table('goods_cat')." where cat_id=15 and goods_id=".$goods_id;
		$res = $GLOBALS['db']->getRow($sql);
		if(!empty($res)){$retu = true;}
	}
	return $retu;
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
    $sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " .
            'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
            "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " .
            "WHERE r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[$row['rank_id']] = array(
                        'rank_name' => htmlspecialchars($row['rank_name']),
                        'price'     => sprintf("￥%s", number_format($row['price'], 2, '.', '')),
						'price_pure'=> number_format($row['price'], 2, '.', '')
			);
    }

    return $arr;
}

/* ----------------------------------------------------------------------------
 * 产品买家秀选项卡页【yi】
 * ----------------------------------------------------------------------------
 */
function mjx_info($goods_id=0){
	$mjx = array();
	$sql = "SELECT a.*, b.user_name FROM ".$GLOBALS['ecs']->table('mjx')." a left join ".$GLOBALS['ecs']->table('users')." b on a.user_id=b.user_id where a.sh=1 and a.goods_id=".
		   $goods_id." order by a.id desc limit 5";
	$mjx = $GLOBALS['db']->GetAll($sql);
	return $mjx;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * xyz:获取该商品所有属性参数
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_goods_all_attr($goods_id = 0)
{
	if($goods_id) {
		$attrs = array();
		$res = $GLOBALS['db']->query('SELECT attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr'). ' WHERE goods_id=' . $goods_id);
		while($row = $GLOBALS['db']->fetchRow($res)){
			$attrs[] = $row;
		}
		return $attrs;
	}
}


//yi:获得框架眼镜的尺寸属性
function get_kuangjia_attr($goods_id = 0)
{
	if(!empty($goods_id))
	{
		$sql = "select attr_id, attr_value from ".$GLOBALS['ecs']->table('goods_attr')." where goods_id=".$goods_id." and attr_id>249 and attr_id<255 order by attr_id asc;";
		$attr_kj = $GLOBALS['db']->getAll($sql);
		return $attr_kj;
	}
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:包含该商品的(全部或指定类别)优惠活动
 * ----------------------------------------------------------------------------------------------------------------------
 */
function include_goods_fav($goods_id=0, $act_type=-1)
{
	$now = $_SERVER['REQUEST_TIME'];
	$tsql= ($act_type==-1)? "": " and act_type=".$act_type;
	$sql = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' ".$tsql." ORDER BY `start_time` desc,`end_time` desc";
	$fav = $GLOBALS['db']->getAll($sql);
    
	foreach($fav as $k => $v)
	{
		$fav[$k]['gift'] = unserialize($v['gift']);
		$fav_ok   = false;
		$bb       = explode(",", $fav[$k]['act_range_ext']);
		if(empty($bb))
		{
			unset($fav[$k]); continue;
		}
		switch($v['act_range'])
		{
			case 0: $fav_ok = true;  break;
			case 1:
				$goods_cat_id = get_cat_id($goods_id);
				if(in_array($goods_cat_id, $bb))
				{
					$fav_ok = true;
				}
				else
				{
					$gift_parent_id = $GLOBALS['db']->getOne("select parent_id from ecs_category where cat_id=".$goods_cat_id." limit 1;");
					if(in_array($gift_parent_id, $bb))
					{
						$fav_ok = true;
					}
				}
				break;
			case 2:
				$goods_brand = get_brand_id($goods_id);
				if(in_array($goods_brand, $bb))
				{
					$fav_ok = true;
				}
				break;
			case 3:
				if(in_array($goods_id, $bb))
				{
					$fav_ok = true;
				}
				break;
			default:
				break;
		}
		if(false === $fav_ok)
		{
			unset($fav[$k]);
		}
	}
	return $fav;
}


/* ----------------------------------------------------------------------------------------------------------------------
 * yi:全部的优惠活动
 * ----------------------------------------------------------------------------------------------------------------------
 */
function full_fav()
{
	$now = $_SERVER['REQUEST_TIME'];
	$sql = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' and not_show=0 ORDER BY `start_time` desc,`end_time` desc";	
	return $GLOBALS['db']->getAll($sql);
}


/* ----------------------------------------------------------------------------
 * yi: 商品是否有cookies记录外站活动信息
 * ----------------------------------------------------------------------------
 * return 获得的这个source的数据记录。
 */
function get_cookies_source($goods_id = 0)
{
	$source = array();

	//url中from参数为空或不正确，但这个商品有记录source cookies.
	$cookie_str = isset($_COOKIE['source_rec_id'])? trim($_COOKIE['source_rec_id']): '';
	$source_arr = explode(',', $cookie_str);
	if(!empty($source_arr))
	{
		$sql2    = "select * from ".$GLOBALS['ecs']->table('source')." where UNIX_TIMESTAMP() > start_time AND UNIX_TIMESTAMP() < end_time AND goods_id=".$goods_id;
		$sou_row = $GLOBALS['db']->getAll($sql2);
		foreach($sou_row as $k => $v)
		{
			if(in_array($sou_row[$k]['rec_id'], $source_arr))
			{
				$source = $sou_row[$k];
				break;
			}
		}
	}
	//yi:专享活动限制活动商品数量
	$source['can_add'] = true;
	if(!empty($source) && !empty($source['price_title']) && !empty($source['rec_id']))
	{
		$n_limit = $GLOBALS['db']->getOne("select number_limit from ecs_source where rec_id=".$source['rec_id']." limit 1;");
		if($n_limit>0)
		{
			$sql = "select IFNULL(sum(goods_number),0) from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' and extension_id='$source[rec_id]' ";
			$hv_source = $GLOBALS['db']->getOne($sql);	
			if($hv_source>=$n_limit)
			{
				$source['can_add'] = false;
			}
		}
	}
    // 优惠金额以及是否显示同类商品
    $shop_price = $GLOBALS['db']->getOne("select shop_price from ecs_goods where goods_id=".$goods_id." limit 1;");
    $source['saving'] = $shop_price - $source['exclusive_price'];
    if($source['exclusive_price'] > $shop_price){
        $source['show_sk'] = 1;
    }else{
        $source['show_sk'] = false;
    }
	return $source;
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:获得商品属性，并把多重属性进行合并
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_goods_attr_pure($goods_id=0)
{
	$sql = "select attr_id, attr_value from ecs_goods_attr where goods_id=".$goods_id." order by attr_id asc;";
	$res = $GLOBALS['db']->getAll($sql);
	if(!empty($res))
	{
		$temp = 0;
		$arrk = 0;
		$arr  = array();
		foreach($res as $k=>$v)	
		{
			if($temp == $v['attr_id'])
			{
				$arr[$arrk-1]['attr_value'] = $arr[$arrk-1]['attr_value'].'，'.$v['attr_value'];
			}
			else
			{
				$temp = $v['attr_id'];
				$arr[]= $v;
				$arrk ++;
			}
		}
	}
	return $arr;
}
/* ----------------------------------------------------------------------------
 * 获得购买过该商品的人还买过的商品【yi】
 * ----------------------------------------------------------------------------
 * 随机变化推荐的商品
 */
function get_also_bought($goods_id = 0)
{
	return $GLOBALS['db']->getAll("select * from ".$GLOBALS['ecs']->table('goods_analysis')." where fgoods=".$goods_id." limit 3;");
}
/**
 * 一个专享链接进来，多个产品享受专享价，用于美瞳的不同花色(操作方法,无返回值)
 * $goods_arr - 相同产品数组
 * $source - 活动来源
 */
function set_same_goods($goods_arr,$source){
    $froms_rec_id = isset($_COOKIE['source_rec_id'])? trim($_COOKIE['source_rec_id']): '';
    $cookie_time = time();
    foreach($goods_arr as $v){
        $froms = $GLOBALS['db']->getRow("select * from ".$GLOBALS['ecs']->table('source')." where goods_id=$v and source='$source' limit 1;");
        if($froms['start_time']<$_SERVER['REQUEST_TIME'] && $froms['end_time']>$_SERVER['REQUEST_TIME'] && $froms['inventory']>0)
        {
            //把这个活动对应的相关产品的最新的信息记录到cookies中。供后边进行价格操作。记录专享商品的rec_id，如果有多个商品，用','添加。cookie有效时间30分钟。
            if(!empty($froms['rec_id']))
            {
                if(empty($froms_rec_id))
                {
                    $froms_rec_id = $froms['rec_id'];
                }
                else
                {
                    $ep_froms = explode(',', $froms_rec_id);
                    if(!in_array($froms['rec_id'], $ep_froms))
                    {
                        $froms_rec_id = $froms_rec_id.','.$froms['rec_id'];
                    }
                }
            }
            $cookie_time = $froms['end_time'] < $_SERVER['REQUEST_TIME'] ? $_SERVER['REQUEST_TIME']+1800: $froms['end_time'];//cookie30分钟
        }
    }
    setcookie('source_rec_id', $froms_rec_id, $cookie_time);
}

?>