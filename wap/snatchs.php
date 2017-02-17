<?php
/* =======================================================================================================================
 * 商城 秒杀抢购具体商品页面【Author:yijiangwen】【同步TIME:2012/8/30】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
date_default_timezone_set('PRC'); 
if((DEBUG_MODE & 2) != 2){$smarty->caching = true;}

$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;//$goods_id = 227;//测试

if($_SESSION['user_id'] > 0){$smarty->assign('user_info', get_user_info());}
$cache_id = $goods_id.'-'.$_SESSION['user_rank'].'-'.$_CFG['lang'];$cache_id = sprintf('%X', crc32($cache_id));

if(!$smarty->is_cached('snatchs.dwt', $cache_id))
{    
    $goods   = get_goods_info_yi($goods_id);//商品详细信息	
	$goodsds = get_goodsds_info($goods_id); //度数列表
	$link_goods = get_link_goods($goods_id);//关联商品
	//print_r($link_goods);

    if($goods === false)
    {        
        ecs_header("Location: ./\n");//如果没有找到任何记录则跳回到首页
        exit;
    }
	else
	{
		/*------------------------------------页头 页尾 数据---------------------------------------*/
		$position = assign_ur_here();
		$smarty->assign('page_title',          $position['title']);    
		$smarty->assign('ur_here',             $position['ur_here']);  
		$smarty->assign('get_new_fl',          index_get_new_fl(21));     //热门分类
		//$smarty->assign('topbanner',           ad_info(31,1));            //头部横幅广告
		
		//页尾部分
		$smarty->assign('helps',               get_shop_help());          //网店帮助文章
		$smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行	
		$smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
		
		//菜单数据
		$cat_tree = get_category_tree();                     //分类列表
		$smarty->assign('cat_1',        $cat_tree[1]);
		$smarty->assign('cat_6',		$cat_tree[6]);
		$smarty->assign('cat_12',		$cat_tree[12]);
		$smarty->assign('cat_64',		$cat_tree[64]);
		$smarty->assign('cat_76',		$cat_tree[76]);	
		$smarty->assign('hot_list1',    yi_get_hot_list(1) );//热门系列
		$smarty->assign('hot_list6',    yi_get_hot_list(6) );
		$smarty->assign('hot_list12',   yi_get_hot_list(12));
		$smarty->assign('hot_list64',   yi_get_hot_list(64));
		$smarty->assign('hot_list76',   yi_get_hot_list(76));	
		$smarty->assign('sale_order1',  yi_sale_sort_list(1) );//热销排行
		$smarty->assign('sale_order6',  yi_sale_sort_list(6) );
		$smarty->assign('sale_order12', yi_sale_sort_list(12));
		$smarty->assign('sale_order64', yi_sale_sort_list(64));
		$smarty->assign('sale_order76', yi_sale_sort_list(76));
		/*------------------------------------页头 页尾 数据end------------------------------------*/

		//处理商品信息
        if($goods['brand_id'] > 0)
        {
            $goods['goods_brand_url'] = build_uri('brand', array('bid'=>$goods['brand_id']), $goods['goods_brand']);
        }

		//判断这个商品是否在抢购当中	
		$left_time_end = '';
		$dtime = $_SERVER['REQUEST_TIME'];
		if($goods['promote_start_date']< $dtime && $goods['promote_end_date']>$dtime && $goods['goods_number']>0)
		{
			$goods['ok'] = 1;
			//智能判断抢购截止时间	
			/*
			if(date("G")<11 && date("G")>0)
			{
				$left_time_end = date("Y/m/d,h:m:s", mktime(11,0,0, date("m"), date("d"), date("Y")));
			}
			elseif(date("G")>11)
			{
				$left_time_end = date("Y/m/d,h:m:s", mktime(11,0,0, date("m"), date("d")+1, date("Y")));
			}
			else
			{
				//正在抢购中。
			}*/
			$left_time_end = date("Y/m/d,h:i:s", mktime(12,0,0, date("m"), date("d"), date("Y")));
		}
		else
		{
			if ($dtime < $goods['promote_start_date']) {
				$goods['ok'] = 0; //未开始
				if (date('Ymd', $dtime) == date('Ymd', $goods['promote_start_date'])) {
					//该产品是当天秒杀，则显示价格,否则不显示价格
					$smarty->assign('today', 1);
				}
			} else {
				$goods['ok'] = 2; //已结束
			}
		}
		//=========================================商品数据写入模板=============================================||
		$properties = get_goods_properties($goods_id);//获得商品的规格和属性
		$shop_price = $goods['shop_price'];

		//print_r($goods['promote_price']);

        $smarty->assign('goods',               $goods);
		$smarty->assign('goodsds',             $goodsds);
		$smarty->assign('link_goods',          $link_goods);									 //关联商品(同一商品)
		$smarty->assign('goods_qg_pr',         intval($goods['promote_price']));
		$smarty->assign('goods_id',            $goods['goods_id']);
        $smarty->assign('properties',          $properties['pro']);                              // 商品属性
        $smarty->assign('specification',       $properties['spe']);                              // 商品规格
        $smarty->assign('attribute_linked',    get_same_attribute_goods($properties));           // 相同属性的关联商品
        $smarty->assign('related_goods',       get_linked_goods($goods_id));                     // 关联商品或同类推荐
        $smarty->assign('goods_article_list',  get_linked_articles($goods_id));                  // 关联文章
        $smarty->assign('fittings',            get_goods_fittings(array($goods_id)));            // 配件
        $smarty->assign('rank_prices',         get_user_rank_prices($goods_id, $shop_price));    // 会员等级价格
        $smarty->assign('pictures',            get_goods_gallery($goods_id));                    // 商品相册
        $smarty->assign('bought_goods',        get_also_bought($goods_id));                      // 购买了该商品的用户还购买了哪些商品
        //$smarty->assign('goods_rank',          get_goods_rank($goods_id));                       // 商品的销售排名			
		$smarty->assign('categoriescsz',       get_categories_treecsz());                        // 参数分类树
		$smarty->assign('left_time_end',       $left_time_end);

		//========================================================================================================||

		//【产品评论】通过外部代码插入进来的。在lib_insert.php文件中{insert name='comments' type=$type id=$id}		
		$smarty->assign('type',         0);
		$smarty->assign('id',           $goods_id);
		$smarty->assign("goods_ids",    $goods_id);

		/*----------------------------------------------产品页【有问必答】列表------------------------------------------------------------*/
		//get_pager1()用表单进行会员留言的分页

		//页面大小
		$page_size = 5;

		//总记录数，当前页数，总页数
		$count      = $GLOBALS['db']->getOne("SELECT count(*) FROM ".$GLOBALS['ecs']->table('feedback')." WHERE goods_id =".$goods_id);
		$page       = (isset($_GET['pages'])&&!empty($_GET['pages']))? intval($_GET['pages']): 1; 
		$page_count = ($count>0)? ceil($count/$page_size): 1;
		$page_prev  = ($page>1) ? $page-1 : 1;
		$page_next  = ($page<$page_count)? $page+1 : $page_count;

		//所有提问留言
		$feedback = array();
		$sqlf = "select * from ".$GLOBALS['ecs']->table('feedback')." where goods_id=".$goods_id." and msg_status=1 order by msg_time desc limit ".($page-1)*$page_size.",".$page_size.";";
		$feedback = $GLOBALS['db']->GetAll($sqlf);

		//遍历每条提问留言，然后找到它的回复留言。
		foreach($feedback as $k => $v)
		{
			$msg_id = $feedback[$k]['msg_id'];
			$sql_bk = "select msg_content from ".$GLOBALS['ecs']->table('feedback')." where parent_id='$msg_id' limit 1";
			$msg_re = $GLOBALS['db']->GetOne($sql_bk);
			$feedback[$k]['msg_re'] = $msg_re;
		}

		$smarty->assign("total1",     $count);
		$smarty->assign("page1",      $page);
		$smarty->assign("pagesize1",  $page_size);
		$smarty->assign("pagecount1", $page_count);	
		$smarty->assign("prev",       $page_prev);			      
		$smarty->assign("next",       $page_next);
		$smarty->assign("feedback",   $feedback);
		/*----------------------------------------------产品页【有问必答】列表END------------------------------------------------------------*/

		//商品页面买家秀
		$smarty->assign('mjx_info', mjx_info($goods_id));

        assign_dynamic('snatchs');   
    }
}
/*--------------------------------------------【写在模板缓存之外的功能】--------------------------------------------*/
//会员是否登录
$user_id = (isset($_SESSION['user_id']) && $_SESSION['user_id']>0)? intval($_SESSION['user_id']): 0;
$smarty->assign('user_id', $user_id);


//剩余可供抢购的商品数量:库存 - 已购数量 (可通过修改商品库存来达到手动修改的目的)
$remainder_goods = $goods['goods_number'] - get_remainder_goods($goods_id);
$smarty->assign('remainder_goods',  $remainder_goods);

$smarty->assign('now_time',  gmtime());//当前系统时间
$smarty->display('snatchs.dwt');//$smarty->display('goods.dwt', $cache_id);






//======================================================================【函数】======================================================================//

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
function mjx_info($goods_id){
	$mjx = array();
	$sql = "SELECT a.user_id, a.id mjxid, a.title, a.img, a.attr, a.detail, a.goods_id, a.detail, a.datetime, a.vote, b.user_name FROM ".$GLOBALS['ecs']->table('mjx').
		   " a,".$GLOBALS['ecs']->table('users')." b where a.sh=1 and a.goods_id=".$goods_id." and a.user_id=b.user_id order by a.id desc ";
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
function get_linked_articles($goods_id)
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
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[$row['rank_id']] = array(
                        'rank_name' => htmlspecialchars($row['rank_name']),
                        'price'     => price_format($row['price']));
    }

    return $arr;
}

/* ----------------------------------------------------------------------------
 * 获得购买过该商品的人还买过的商品【yi】
 * ----------------------------------------------------------------------------
 * 随机变化推荐的商品
 */
function get_also_bought($goods_id)
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
function get_goods_rank($goods_id)
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

function get_goods_info_yi($goods_id=0)
{
	$sql = "select * from ecs_goods where goods_id=".$goods_id." and is_delete=0 and is_alone_sale=1 and is_on_sale=1 ";
	return $GLOBALS['db']->getRow($sql);
}

//获取该商品数量
function get_remainder_goods ($goods_id=0) {
	//购物车商品数量
	$cart_number = 0;
	$sql = "select SUM(goods_number) from ecs_cart where goods_id=".$goods_id;
	$c_num = $GLOBALS['db']->GetOne($sql);
	if ($c_num) $cart_number = $c_num;
	
	//订单表中的数量
	$dtime = mktime(11, 0, 0, date("m"), date("d"), date("Y"));
	$sql = "select * from ecs_order_info where order_status<>2 AND add_time>".$dtime;
	$u_order = $GLOBALS['db']->GetAll($sql);
	$goods_number = 0;
	if(!empty($u_order))
	{
		foreach($u_order as $k => $v)
		{
			$sql = "select SUM(goods_number) from ecs_order_goods where goods_id=".$goods_id." and is_cx=1 and order_id=".$v['order_id'];
			$g_num = $GLOBALS['db']->GetOne($sql);
			if ($g_num) $goods_number += $g_num;
		}
	}
	$res_number = $goods_number + $cart_number;
	return $res_number;
}

//取得商品的关联商品信息列表
function get_link_goods($goods_id=0)
{
	$sql = "SELECT g.goods_id, g.goods_name, g.market_price, g.shop_price, g.goods_img, g.goods_thumb, g.original_img FROM ". $GLOBALS['ecs']->table('goods') ." g LEFT JOIN ". $GLOBALS['ecs']->table('link_goods') ." AS l ON g.goods_id=l.link_goods_id WHERE l.goods_id=".$goods_id." LIMIT 1;";
	return $GLOBALS['db']->GetRow($sql);
}
?>