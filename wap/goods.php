<?php
/* =======================================================================================================================
 * 商城页面 产品详情页【2012/3/20】【Author:yijiangwen】【TIME:2012/11/26】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php'); 
if((DEBUG_MODE & 2) != 2){$smarty->caching = false;}

ini_set("display_errors", "Off");
error_reporting(0); 
$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$now      = $_SERVER['REQUEST_TIME'];

if($_SESSION['user_id'] > 0){$smarty->assign('user_info', get_user_info());}

//未注册用户是否跳回展示页(index_unck.dwt)
if(!index_unck_display($_SESSION['user_id'])){
    header("Location: user.html \n");
}

$is_b2b_goods = $GLOBALS['db']->getOne("SELECT id FROM b2b_goods WHERE goods_id = ".$goods_id);
if(!$is_b2b_goods){
    header('HTTP/1.1 404 Not Found'); 
	$smarty->display('404.html');
    exit();
}

$cache_id = $goods_id.'-'.$_SESSION['user_rank'].'-'.$_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));

if(!$smarty->is_cached('goods.dwt', $cache_id))
{
    
    $goods   = get_goods_info($goods_id);  //商品详细信息	
	$goodsds = get_goods_ds($goods_id);    //度数

	//-------------------------------------【散光片/镜片 数据】-------------------------------------------//
	$goods_sg = if_sg($goods_id);
    $goods_jp = if_jp($goods_id);
	$smarty->assign('goods_is_sg', $goods_sg);
    $smarty->assign('goods_is_jp', $goods_jp);
	if($goods_sg || $goods_jp)
	{
		$smarty->assign('goods_sgds', get_sgds_info($goods_id));//散光度数列表		
	}
	//-------------------------------------【散光片/镜片 数据】-------------------------------------------//
    
    if($goods === false)
    {
		header('HTTP/1.1 404 Not Found'); 
		$smarty->display('error.htm');
		exit;
    }
    else
    {
		$smarty->assign('user_id',      $_SESSION['user_id']);

		//============================================================【放大镜功能】============================================================//
		$ga_first = $GLOBALS['db']->GetRow("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=1 and shop_id = 2 limit 1;");
		$ga_list  = $GLOBALS['db']->GetAll("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=0 and shop_id = 2");
		array_unshift($ga_list, $ga_first);
        
		$smarty->assign('gallery',      $ga_list);

		//=========================================商品数据写入模板=============================================||
        
        $pifa_confirm    = '';
        $wholesale_arr   = '';
        if($goods['is_wholesale'] == 1){//批发价格
            
            $wholesale =$GLOBALS['db']->getRow("SELECT * FROM ecs_wholesale WHERE goods_id = ".$goods_id);
            $wholesale_arr = unserialize($wholesale['prices']);
            $wholesale_arr = $wholesale_arr[0]['qp_list'];
            $pifa_confirm = 1;//确认显示批发价格
            
        }else{
            $pifa_confirm = 0;
        }
        
        if($pifa_confirm == 0){//普通价格
            $shop_price     = $goods['b2b_shop_price'];
            $smarty->assign('shop_price',             $shop_price);   
        }
        
        $smarty->assign('market_price',           $goods['b2b_market_price']);
        $smarty->assign('pifa_confirm',           $pifa_confirm);
        $smarty->assign('wholesale_arr',           $wholesale_arr);
        $smarty->assign('wholesale_arr_serialize',           serialize($wholesale_arr));
   
		$properties = get_goods_properties($goods_id);//获得商品的规格和属性
		$smarty->assign('user_name',           stripslashes($_SESSION['user_name']));
		
		$brand_sq = array(2, 3, 4, 6, 13, 15, 16, 17, 20, 23, 35, 39, 53, 55,61, 65,72,85, 86, 87, 91, 94, 95,96,97, 98, 99, 100, 101, 103, 104, 105, 106, 109, 110, 111, 117, 120,121,122,123,124,125,126,128,160,164,139,141,132,140);		      //品牌授权书
		$brand_sq_double = array(35, 153, 191, 197, 202, 203, 215);//第2个品牌授权书。
		if(in_array($goods['brand_id'], $brand_sq))
		{
			$goods['brand_sq']  = 1;			
			$goods['brand_img'] = in_array($goods['cat_id'], $brand_sq_double)? $goods['brand_id'].'_2': $goods['brand_id'];
		}
		$goods['click_count']   = ceil($goods['click_count']*1); //销售数量

        //获取商品评论数
        //$goods['comment_num'] = $GLOBALS['db']->GetOne("select count(comment_id) as comment_num from ".$GLOBALS['ecs']->table('comment')." where id_value='{$goods_id}' and status = 1");
        
        $link_goods = get_link_goods_list($goods_id);//关联商品
        
		$smarty->assign('page_title',    $goods['goods_name'].'_眼镜行业全方位服务提供商');//页面标题
        $smarty->assign('keywords',      $goods['goods_name'].'_眼镜行业全方位服务提供商');
        $smarty->assign('description',   $goods['goods_name'].'_眼镜行业全方位服务提供商');
        
        $smarty->assign('goods',               $goods);
		$smarty->assign('goodsds',             $goodsds);
		$smarty->assign('goods_id',            $goods['goods_id']);
		$smarty->assign('bought_goods',        get_also_bought($goods_id));                      // 购买了该商品的用户还购买了哪些商品
		$smarty->assign('link_goods',          $link_goods);                                     // 关联商品
		$smarty->assign('back_act',            "goods".$goods_id.".html"); 
		$smarty->assign('user_rank',           isset($_SESSION['user_rank'])? intval($_SESSION['user_rank']): 0 ); //会员等级
		$user_rank_price = get_user_rank_prices($goods_id, $goods['b2b_shop_price']);
        //print_r($goods['rank_price']);
        
        $smarty->assign('rank_price',          $user_rank_price[$_SESSION['user_rank']]['price_pure']);                 // 会员等级价格
		$smarty->assign('rank_prices',         $user_rank_price);                 // 会员等级价格
		$smarty->assign('vip_prices',          $user_rank_price[2]['price_pure']);// 会员vip价格
       
        //print_r($user_rank_price);
        $smarty->assign('sales_charts1',    get_sales_charts_all(0,3));  //热销排行榜1
        $smarty->assign('sales_charts2',    get_sales_charts_all(3,3));  //热销排行榜2
        $smarty->assign('sales_charts3',    get_sales_charts_all(6,3));  //热销排行榜3
    
		//【产品评论】通过外部代码插入进来的。在lib_insert.php文件中{insert name='comments' type=$type id=$id}
		$smarty->assign('type',         0);
		$smarty->assign('id',           $goods_id);
		$smarty->assign("goods_ids",    $goods_id);

        
        /*
        留言功能暂时关闭（未开发）
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
		*/


		//xu:产品属性功能
		$smarty->assign('attrs',    b2b_get_goods_all_attr($goods_id));	
        
        /*
		//yi:附加数据
		$append = $GLOBALS['db']->GetRow("select * from ".$GLOBALS['ecs']->table('goods_append')." where goods_id=".$goods_id);
		$smarty->assign('append',  $append);
        */
        //tao:B2B独立出镜片作为商品
        if($goods_jp){
            $smarty->assign("is_jp",   1);
        }
        
        
		//===============================================【产品页面_赠品(没有金额限制且免费)提示】=========================================//
		$fav = include_goods_fav($goods_id, -1);

		$gift_tip = array();		
		foreach($fav as $k => &$v)
		{
			if(!empty($v['gift_tip']))
			{
				$arr = explode(',',$v['gift_tip']);
                $v['gift_tip'] =$arr[0];
                @$v['gift_tip_url'] = trim($arr[1]);
			}
			else
			{
				continue;
			}
            foreach($v['gift'] as $key =>&$val){
                $thumb= $GLOBALS['db']->GetRow("select goods_thumb from ".$GLOBALS['ecs']->table('goods')." where goods_id=".$val['id']);
                $val['thumb'] =$thumb['goods_thumb'];
            }
		}

        sort($fav);

        if($goods['is_promote'] == 1 && $goods['promote_start_date']  <=  time()  && $goods['promote_end_date'] >=time()){
            $goodsSale =  $goods['shop_price'] - $goods['promote_price_org'];
        }else{
            $goodsSale = 0 ;
        }

        $smarty->assign('sales', $goodsSale);
        $smarty->assign('count_fav', count($fav));
        $smarty->assign('arr', $fav);
		$smarty->assign('fav',      full_fav());

        assign_dynamic('goods');   
    }
}
/*--------------------------------------------【写在模板缓存之外的功能】--------------------------------------------*/

$smarty->assign('now_time',  $_SERVER['REQUEST_TIME']);//当前时间
//$smarty->assign('img_site',  IMG_SITE);
//$smarty->assign('img_site',  'http://file.easeeyes.com/');//当前时间

$smarty->display('goods.dwt', $cache_id);//所有页面都是正常商品模板，镜片需单独分出来（未实现）


//======================================================================【函数】======================================================================//


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
 * zwt:获取该商品所有属性参数
 * ----------------------------------------------------------------------------------------------------------------------
 */
function b2b_get_goods_all_attr($goods_id = 0)
{
	if($goods_id) {
		$attrs = array();
		$res = $GLOBALS['db']->query('SELECT a.attr_name, b.attr_value FROM ' .$GLOBALS['ecs']->table('attribute').' as a LEFT JOIN ' .$GLOBALS['ecs']->table('goods_attr'). 'as b ON a.attr_id = b.attr_id WHERE b.goods_id=' . $goods_id);
		while($row = $GLOBALS['db']->fetchRow($res)){
			$attrs[] = $row;
		}
		return $attrs;
	}
}





/* ----------------------------------------------------------------------------
 * 函数yi：取得商品的关联商品信息列表
 * ----------------------------------------------------------------------------
 */
function get_link_goods_list($goods_id=0)
{
    $sql = "select  b.b2b_shop_price, b.goods_id, g.goods_name from ecs_link_goods as l LEFT JOIN 
    ecs_goods as g ON l.link_goods_id=g.goods_id  LEFT JOIN 
    b2b_goods as b ON b.goods_id = g.goods_id WHERE l.goods_id=".$goods_id." 
    AND g.cat_id<>138 AND b.b2b_is_on_sale = 1 AND l.shop_id = 2 limit 0,3;";
    return $GLOBALS['db']->GetAll($sql);
}

/* ----------------------------------------------------------------------------
 * 函数yi：取得商品的关联商品信息列表【非卖品只关联非卖品】
 * ----------------------------------------------------------------------------
 */
function get_link_goods_list_un($goods_id=0)
{
	$sql = "select l.*, g.goods_img, g.goods_thumb, g.original_img from ecs_link_goods as l left join ecs_goods as g on l.link_goods_id=g.goods_id where l.goods_id=".
			$goods_id." and g.cat_id=138 and g.goods_number>0 and g.is_delete=0 and g.is_on_sale=1 and g.is_alone_sale=1 limit 0,3;";
	return $GLOBALS['db']->GetAll($sql);
}

/* ----------------------------------------------------------------------------
 * 评论_分页函数【yi】
 * ----------------------------------------------------------------------------
 */
function get_pager1($record_count, $page = 1, $size = 8, $styleid=1)
{
    $size = intval($size);
    if ($size < 1) $size = 8;
    $page = intval($page);
    if ($page < 1)$page = 1;
    $record_count = intval($record_count);

    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;
    if ($page > $page_count)
    {
        $page = $page_count;
    }
    /* 分页样式 */
    $pager['styleid'] = $styleid;

    $page_prev = ($page > 1) ? $page - 1 : 1;
    $page_next = ($page < $page_count) ? $page + 1 : $page_count;

	if ($pager['styleid'] == 0)
    {
        $pager['page_first']   = $url . $param_url . 'page=1';
        $pager['page_prev']    = $url . $param_url . 'page=' . $page_prev;
        $pager['page_next']    = $url . $param_url . 'page=' . $page_next;
        $pager['page_last']    = $url . $param_url . 'page=' . $page_count;
        $pager['array'] = array();
        for ($i = 1; $i <= $page_count; $i++)
        {
            $pager['array'][$i] = $i;
        }
    }
    else
    {
        $_pagenum = 10;     // 显示的页码
        $_offset = 2;       // 当前页偏移值
        $_from = $_to = 0;  // 开始页, 结束页
        if($_pagenum > $page_count)
        {
            $_from = 1;
            $_to = $page_count;
        }
        else
        {
            $_from = $page - $_offset;
            $_to = $_from + $_pagenum - 1;
            if($_from < 1)
            {
                $_to = $page + 1 - $_from;
                $_from = 1;
                if($_to - $_from < $_pagenum)
                {
                    $_to = $_pagenum;
                }
            }
            elseif($_to > $page_count)
            {
                $_from = $page_count - $_pagenum + 1;
                $_to = $page_count;
            }
        }
        $url_format = $url . $param_url . 'page=';
        $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 : '';
        $pager['page_prev'] = ($page > 1) ? $url_format . $page_prev : '';
        $pager['page_next'] = ($page < $page_count) ? $url_format . $page_next : '';
        $pager['page_last'] = ($_to < $page_count) ? $url_format . $page_count : '';
        $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
        $pager['page_number'] = array();
        for ($i=$_from;$i<=$_to;++$i)
        {
            $pager['page_number'][$i] = $url_format . $i;
        }
    }
    $pager['search'] = $param;
    return $pager;
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

/* ----------------------------------------------------------------------------
 * 获得指定商品的关联商品
 * ----------------------------------------------------------------------------
 */
function get_linked_goods($goods_id)
{
	$strcatstr = "0";
	$sql = "SELECT cat_id,goods_name FROM " . $GLOBALS['ecs']->table('goods') . " where goods_id=".$goods_id." ORDER BY goods_id DESC ";

	$res = $GLOBALS['db']->query($sql);
	while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$strcatstr=$strcatstr.",".$row['cat_id'];
		$cat_id=$row['cat_id'];
	}
	
		
	if($cat_id=='1'||$cat_id=='6'||$cat_id=='12'||$cat_id=='64'||$cat_id=='76')
	{
		$fcat_ids=$cat_id;
	}
	else
	{
		$sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";	
		$fcat_ids = $GLOBALS['db']->getOne($sql);
	}
	
	
	$children = get_children($fcat_ids);		
	$strcatstr=" and g.cat_id in(".$strcatstr.")";		   
	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb,RAND() AS rnd , g.goods_img, g.shop_price AS org_price, ' .
			"g.shop_price AS shop_price, ".
			'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' .
			'FROM ' . $GLOBALS['ecs']->table('goods') . ' g ' .
			"WHERE g.goods_id != '$goods_id' AND ".$children." AND g.is_on_sale = 1  AND g.is_delete = 0 ".
			"order by rnd LIMIT 0,7";
	//print_r($sql);

    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['goods_id']]['goods_id']     = $row['goods_id'];
        $arr[$row['goods_id']]['goods_name']   = $row['goods_name'];
        $arr[$row['goods_id']]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
            sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $arr[$row['goods_id']]['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']   = price_format($row['shop_price']);
        $arr[$row['goods_id']]['url']          = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);

        if ($row['promote_price'] > 0)
        {
            $arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
        }
        else
        {
            $arr[$row['goods_id']]['promote_price'] = 0;
        }
    }
    return $arr;
}

/* ----------------------------------------------------------------------------
 * 获得指定商品的关联商品
 * ----------------------------------------------------------------------------
 */
function get_linked_goods_back($goods_id = 0)
{
	//指定关联商品的设置
	//周期，颜色，含水量，直径，基弧，镜片风格。

	$size = isset($GLOBALS['_CFG']['related_goods_number'])? intval($GLOBALS['_CFG']['related_goods_number']): 7;

	//商品所在的父分类
	$sqlc = "select c.cat_id from ecs_goods as g, ecs_category as c where g.goods_id='$goods_id' and g.cat_id=c.cat_id and c.parent_id>0";
//print_r($sqlc);
	$cat_id = $GLOBALS['db']->GetOne($sqlc);


	//找出这个商品的所有属性
	$sqla = "select * from ecs_goods_attr where goods_id=$goods_id";
	$attr = $GLOBALS['db']->GetAll($sqla);
	//print_r($attr);
	//echo '<br/>=====<br/>';

	//同类产品数组
	$arr = array();

	//当中可以对属性值做排序操作【待做】

	//当中可以对选择属性进行同类操作【待做】


	//找出这个属性值的所有商品
	foreach($attr as $k=>$v){

		//查找所有这个属性值的商品
		$sqlg = "select a.goods_id from ecs_goods_attr as a left join ecs_goods as g on a.goods_id=g.goods_id where a.attr_value = '".$v['attr_value'].
			    "' and g.cat_id='$cat_id' and a.goods_id<>'$goods_id'";
		$res1 = $GLOBALS['db']->GetAll($sqlg);

		foreach($res1 as $k1=>$v1){
			if(!in_array($v1['goods_id'],$arr)){array_push($arr,$v1['goods_id']);}
		}


		if(count($res1)>=$size){
			//break;
		}
	}

	//print_r($arr);
	//echo '<br/>=====<br/>';

	$in = '0';
	foreach($arr as $k2 => $v2){
		$in .= ','.$v2;
	}

	$sql = "select goods_id, goods_name, goods_img, goods_thumb, shop_price, market_price from ecs_goods where goods_id in(".$in.") limit ".$size;
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['goods_id']]['goods_id']     = $row['goods_id'];
        $arr[$row['goods_id']]['goods_name']   = $row['goods_name'];
        $arr[$row['goods_id']]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
            sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $arr[$row['goods_id']]['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']   = price_format($row['shop_price']);
        $arr[$row['goods_id']]['url']          = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    }
    return $arr;
}

/**
 * 获得指定商品的关联文章
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function get_linked_articles_old_function($goods_id)
{
    $sql = 'SELECT a.article_id, a.title, a.file_url, a.open_type, a.add_time ' .
            'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS g, ' .
                $GLOBALS['ecs']->table('article') . ' AS a ' .
            "WHERE g.article_id = a.article_id AND g.goods_id = '$goods_id' AND a.is_open = 1 " .
            'ORDER BY a.add_time DESC';
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['url']         = $row['open_type'] != 1 ?
            build_uri('article', array('aid'=>$row['article_id']), $row['title']) : trim($row['file_url']);
        $row['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $row['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
            sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];

        $arr[] = $row;
    }

    return $arr;
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
 * 获得购买过该商品的人还买过的商品【yi】
 * ----------------------------------------------------------------------------
 * 随机变化推荐的商品
 */
function get_also_bought($goods_id = 0)
{
	return $GLOBALS['db']->getAll("select * from ".$GLOBALS['ecs']->table('goods_analysis')." where fgoods=".$goods_id." limit 5;");
}


function get_also_bought_back20121017($goods_id)
{
	$size  = isset($GLOBALS['_CFG']['bought_goods'])? intval($GLOBALS['_CFG']['bought_goods']): 5;
	//$start = rand(0,6)*$size;
	//$num   = 7*$size;
    $sql = 'SELECT COUNT(b.goods_id ) AS num, g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price, g.promote_price, g.promote_start_date, g.promote_end_date '.
           'FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS a ' .
           'LEFT JOIN ' . $GLOBALS['ecs']->table('order_goods') . ' AS b ON b.order_id = a.order_id ' .
           'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = b.goods_id ' .
           "WHERE a.goods_id = '$goods_id' AND b.goods_id <> '$goods_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " .
           'GROUP BY b.goods_id ' .
           'ORDER BY num DESC ' .
           'LIMIT '.$size;	
    $res = $GLOBALS['db']->query($sql);

    $key = 0;
    $arr = array();
    while($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$key]['goods_id']    = $row['goods_id'];
        $arr[$key]['goods_name']  = $row['goods_name'];
        $arr[$key]['short_name']  = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
            sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $arr[$key]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$key]['goods_img']   = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$key]['shop_price']  = price_format($row['shop_price']);
        $arr[$key]['url']         = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);

        if($row['promote_price'] > 0)
        {
            $arr[$key]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $arr[$key]['formated_promote_price'] = price_format($arr[$key]['promote_price']);
        }
        else
        {
            $arr[$key]['promote_price'] = 0;
        }

        $key++;
    }
    return $arr;
}

/**
 * 获得指定商品的销售排名
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  integer
 */
function get_goods_rank_old_function($goods_id)
{
    /* 统计时间段 */
    $period = intval($GLOBALS['_CFG']['top10_time']);
    if ($period == 1) // 一年
    {
        $ext = " AND o.add_time > '" . local_strtotime('-1 years') . "'";
    }
    elseif ($period == 2) // 半年
    {
        $ext = " AND o.add_time > '" . local_strtotime('-6 months') . "'";
    }
    elseif ($period == 3) // 三个月
    {
        $ext = " AND o.add_time > '" . local_strtotime('-3 months') . "'";
    }
    elseif ($period == 4) // 一个月
    {
        $ext = " AND o.add_time > '" . local_strtotime('-1 months') . "'";
    }
    else
    {
        $ext = '';
    }

    /* 查询该商品销量 */
    $sql = 'SELECT IFNULL(SUM(g.goods_number), 0) ' .
        'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
            $GLOBALS['ecs']->table('order_goods') . ' AS g ' .
        "WHERE o.order_id = g.order_id " .
        " AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status >= '" . OS_SPLITED . "') " .
        " AND o.shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
        " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
        " AND g.goods_id = '$goods_id'" . $ext;
    $sales_count = $GLOBALS['db']->getOne($sql);

    if ($sales_count > 0)
    {
        /* 只有在商品销售量大于0时才去计算该商品的排行 */
        $sql = 'SELECT DISTINCT SUM(goods_number) AS num ' .
                'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
                    $GLOBALS['ecs']->table('order_goods') . ' AS g ' .
                "WHERE o.order_id = g.order_id " .
                " AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status >= '" . OS_SPLITED . "') " .
                " AND o.shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) .
                " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . $ext .
                " GROUP BY g.goods_id HAVING num > $sales_count";
        $res = $GLOBALS['db']->query($sql);

        $rank = $GLOBALS['db']->num_rows($res) + 1;

        if ($rank > 10)
        {
            $rank = 0;
        }
    }
    else
    {
        $rank = 0;
    }

    return $rank;
}

/**
 * 获得商品选定的属性的附加总价格
 *
 * @param   integer     $goods_id
 * @param   array       $attr
 *
 * @return  void
 */
function get_attr_amount($goods_id, $attr)
{
    $sql = "SELECT SUM(attr_price) FROM " . $GLOBALS['ecs']->table('goods_attr') .
        " WHERE goods_id='$goods_id' AND " . db_create_in($attr, 'goods_attr_id');

    return $GLOBALS['db']->getOne($sql);
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
	return $source;
}


/**
 * f : 获取部分指定的优惠活动商品
 *
 */

function goods_fav_by_goods_id($goods_id){
    $now = $_SERVER['REQUEST_TIME'];
    $tsql= ($act_type==-1)? "": " and act_type=".$act_type;
    $sql = "select * from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' ".$tsql." ORDER BY `start_time` desc,`end_time` desc";
    $fav = $GLOBALS['db']->getAll($sql);
}

?>