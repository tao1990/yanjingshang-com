<?php
/**
 * 秒杀购买
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

$_REQUEST['act'] = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$now = $_SERVER['REQUEST_TIME'];

if ($_SESSION['user_id'] > 0) 
{
	$smarty->assign('user_info', get_user_info());
}

//秒杀商品提交到购物车
if ($_REQUEST['act'] == 'ms_to_cart')
{
	$ms_rec_id = isset($_POST['ms_rec_id'])? intval($_POST['ms_rec_id']): 0;
	if ($ms_rec_id > 0)
	{
		$zselect = isset($_POST['left_eye_ds'])? addslashes($_POST['left_eye_ds']): '';
		$yselect = isset($_POST['right_eye_ds'])? addslashes($_POST['right_eye_ds']): '';
		$no_ds = isset($_POST['no_ds'])? addslashes($_POST['no_ds']): '';
		
		$ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE rec_id = '".$ms_rec_id."' LIMIT 1");
		
        
        //限购判断 
        if($ms['is_limited'] == 1){
            if($ms['each_limited']>0){
                $cart_ms_num = $GLOBALS['db']->GetOne("SELECT count(*) as num FROM ecs_cart WHERE 
                user_id = ".$_SESSION['user_id']." AND goods_id = ".$ms['goods_id']." AND extension_code ='miaosha_buy'");
                
                $order_ms_num = $GLOBALS['db']->GetOne("SELECT COUNT(*) as num FROM ecs_order_info a left join ecs_order_goods b ON a.order_id = b.order_id 
                WHERE a.user_id = ".$_SESSION['user_id']." AND b.goods_id = ".$ms['goods_id']." AND b.extension_code = 'miaosha_buy'");
                
                $total_ms_num = $cart_ms_num + $order_ms_num;
                
                if($total_ms_num>=$ms['each_limited']){
                      show_message_wap('您购买的秒杀产品已超出限购数量^_^');
                }
            }
            
            if($ms['total_limited']>0){
                $cart_ms_num_t = $GLOBALS['db']->GetOne("SELECT count(*) as num FROM ecs_cart WHERE 
                 goods_id = ".$ms['goods_id']." AND extension_code ='miaosha_buy'");
                
                $order_ms_num_t = $GLOBALS['db']->GetOne("SELECT COUNT(*) as num FROM ecs_order_info a left join ecs_order_goods b ON a.order_id = b.order_id 
                WHERE  b.goods_id = ".$ms['goods_id']." AND b.extension_code = 'miaosha_buy'");
                
                $total_ms_num_t = $cart_ms_num_t + $order_ms_num_t;
                
                if($total_ms_num_t>=$ms['total_limited']){
                      show_message_wap('此商品已被秒完^_^');
                }
            }
        }
        $ms_is_cx = $ms['is_cx'];
		if ($zselect != '' OR $yselect != '')
		{
			$goods_ds_array = array($zselect, $yselect);
			$goods_attr = implode(',', $goods_ds_array);
			
			$sql = "INSERT INTO ecs_cart (user_id, session_id, goods_id, goods_sn, goods_name, market_price, 
					goods_price, goods_number, goods_attr, is_real, extension_code, extension_id, is_cx) 
					VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$ms['goods_id']."', '1', '".$ms['ms_name']."', '".$ms_rec_id."', 
					'".$ms['price']."', 1, '".$goods_attr."', 1, 'miaosha_buy', $ms_rec_id, $ms_is_cx)" ;
			$GLOBALS['db']->query($sql);
			
			Header("Location:flow.php");
			exit;
		}
		elseif (intval($no_ds) == 1)
		{
			//无度数商品
			$sql = "INSERT INTO ecs_cart (user_id, session_id, goods_id, goods_sn, goods_name, market_price, 
					goods_price, goods_number, goods_attr, is_real, extension_code, extension_id, is_cx) 
					VALUES ('".$_SESSION['user_id']."', '".SESS_ID."', '".$ms['goods_id']."', '1', '".$ms['ms_name']."', '".$ms_rec_id."', 
					'".$ms['price']."', 1, '', 1, 'miaosha_buy', $ms_rec_id, 1)" ;
			$GLOBALS['db']->query($sql);
			Header("Location:flow.php");
			exit;
		}
	}
	else
	{
		Header("Location:index.php");
		exit;
	}
}else{
    $smarty->assign('cfg',                 $_CFG);
    $smarty->assign('lang',                $_LANG);
    $goods   = get_goods_info($goods_id);  //商品详细信息
    $goodsds = get_goods_ds($goods_id);    //度数

    //-------------------------------------【散光片数据】-------------------------------------------//
    $goods_sg = if_sg($goods_id);
    $smarty->assign('goods_is_sg', $goods_sg);
    if($goods_sg)
    {
        $smarty->assign('goods_sgds', get_sgds_info($goods_id));//散光度数列表
    }
    //-------------------------------------【散光片数据】-------------------------------------------//


    if($goods === false)
    {
        header('HTTP/1.1 404 Not Found');
        $smarty->display('error.htm');
        exit;
    }
    else
    {
        /*------------------------------------页头 页尾 数据---------------------------------------*/
        $seo_desc = '易视网为您提供'.$goods['goods_name'].'价格、效果图片，购买'.$goods['goods_name'].'就去易视网，想了解'.$goods['goods_name'].'怎么样,咨询全国免费热线：4006-177-176';
        $position = assign_ur_here($goods['cat_id'], $goods['goods_name']);
        $smarty->assign('page_title',    '最给力的隐形眼镜秒杀 - 易视网手机版');//页面标题
        $ur_here = str_replace(' <a href="category_138.html">前台不显示非卖品</a> <code>&gt;</code>', '', $position['ur_here']);
        $smarty->assign('ur_here',       '秒杀商品');
        $smarty->assign('keywords',      htmlspecialchars($goods['goods_name']));
        $smarty->assign('description',   $seo_desc);
        $smarty->assign('topbanner',           ad_info(31,1));            //头部横幅广告
        //页尾
        $smarty->assign('helps',               get_shop_help());          //网店帮助文章
        $smarty->assign('new_articles_botter', index_get_new_articles_botter());//关于我们行
        $smarty->assign('botbanner',           ad_info(12,8));            //营业执照行
        /*   分类列表没有东西
        $cat_tree = get_category_tree();                     			  //分类列表
        var_dump($cat_tree);exit();
        $smarty->assign('cat_1',        		$cat_tree[1]);
        $smarty->assign('cat_6',				$cat_tree[6]);
        $smarty->assign('cat_64',				$cat_tree[64]);
        $smarty->assign('cat_76',				$cat_tree[76]);
        $smarty->assign('cat_159',				$cat_tree[159]);
        $smarty->assign('cat_190',				$cat_tree[190]);*/
        $smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
        $smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
        $smarty->assign('sale_order64', 		yi_sale_sort_list(64));
        $smarty->assign('sale_order76', 		yi_sale_sort_list(76));
        $smarty->assign('sale_order159', 		yi_sale_sort_list(159));
        $smarty->assign('sale_order190', 		yi_sale_sort_list(190));
        /*------------------------------------页头 页尾 数据end------------------------------------*/

        $link_goods = ($goods['cat_id']!=138)? get_link_goods_list($goods_id) :get_link_goods_list_un($goods_id);//关联商品

        //验光单功能
        $receipt_type = ($goods['goods_type'] != 15)? 1: 2;
        $sql = "select * from ecs_user_ds where user_id=".$_SESSION['user_id']." and receipt_type=".$receipt_type;
        $user_ds = $GLOBALS['db']->GetAll($sql);
        $smarty->assign('user_ds',      $user_ds);
        $smarty->assign('user_id',      $_SESSION['user_id']);

        //============================================================【放大镜功能】============================================================//
        $ga_first = $GLOBALS['db']->GetRow("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=1 limit 1;");
        $ga_list  = $GLOBALS['db']->GetAll("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=0");
        array_unshift($ga_list, $ga_first);
        $smarty->assign('gallery',      $ga_list);

        // 产品详情中的图片URL替换
        $change = array('/images/upload/Image/'=>'http://www.easeeyes.com/images/upload/Image/','width: 750px'=>'width: 100%','width='=>'width="100%"','height='=>'height="auto"');
        $goods['goods_desc'] = strtr($goods['goods_desc'],$change);

        //============================================================【组合购买功能】============================================================//
        //主商品价格（护理液一件，眼镜为2件）
        $g_market_p = (empty($goodsds))? $goods['market_price_nochar']: $goods['market_price_nochar']*2;
        $g_shop_p   = (empty($goodsds))? $goods['shop_price']: $goods['shop_price']*2;
        $fitting_id = $goods_id;//组合购买ID字符串

        //商品配件数据。
        $sql = "select a.*, g.goods_id, g.goods_name, g.shop_price, g.market_price, g.goods_img, g.group_fav, g.eye_id from ecs_group_goods as a left join ecs_goods as g on a.goods_id=g.goods_id ".
            " where a.parent_id=".$goods_id." and g.goods_number>0 and g.is_on_sale=1 and g.is_alone_sale=1 limit 0,4;";
        $pei = $GLOBALS['db']->GetAll($sql);

        if(empty($pei))
        {
            //$pei为空的时候随机推荐4个护理液
            $sql = "select g.goods_id, g.goods_name, g.shop_price, g.market_price, g.goods_img, g.group_fav, g.eye_id, g.shop_price as goods_price from ecs_goods as g left join ecs_category as c on g.cat_id=c.cat_id where c.parent_id>=64 limit 10, 4;";
            $pei = $GLOBALS['db']->GetAll($sql);
        }

        //遍历配件商品，计算初始进入的价格
        foreach($pei as $k => $v)
        {
            //配件有优惠价格
            if($v['shop_price']>0)
            {
                if($v['goods_price'] < $v['shop_price'])
                {
                    $pei[$k]['group_price'] = floatval($v['goods_price']);//优先单个优惠价
                }
                else
                {
                    $group_fav = abs($v['group_fav']);
                    if($group_fav<=$v['shop_price'])
                    {
                        $pei[$k]['group_price'] = floatval($v['shop_price']-$group_fav);
                    }
                }
            }
            else
            {
                //TODO:配件商品价格为0
            }
            $pei[$k]['group_price'] = (!empty($pei[$k]['group_price']))? floatval($pei[$k]['group_price']): $pei[$k]['shop_price'];

            $g_market_p += $v['market_price'];
            //$g_shop_p   += $v['shop_price'];

            $g_shop_p   += $pei[$k]['group_price'] ;//组合购买价
            $fitting_id .= ','.$v['goods_id'];

            //yi:配件商品有度数的情况
            if($v['eye_id']>0)
            {
                $pei[$k]['goodsds'] = get_goods_ds($v['goods_id']);//配件商品度数列表
            }
        }

        if($goods['goods_number']<=0)
        {
            $pei = array();//主商品库存为0
        }
        $smarty->assign('peijian',            $pei);//配件商品

        // 获取评论内容
        $comment = assign_comment_wap($goods['goods_id'],0);
        $smarty->assign('comment',            $comment);//评论内容

        $g_save = ($g_shop_p<=$g_market_p)? floatval($g_market_p-$g_shop_p): 0;//节省多少钱
        $smarty->assign('g_market_price',  $g_market_p);//配件商品
        $smarty->assign('g_shop_price',    $g_shop_p);  //配件商品
        $smarty->assign('g_save',          $g_save);    //配件商品
        $smarty->assign('fitting_id',      $fitting_id);//配件商品
        //============================================================【组合购买功能END】=========================================================//

        //=========================================商品数据写入模板=============================================||
        $properties = get_goods_properties($goods_id);//获得商品的规格和属性
        $shop_price = $goods['shop_price'];
        $smarty->assign('user_name',           stripslashes($_SESSION['user_name']));

        $brand_sq = array(2, 3, 4, 6, 13, 15, 17, 20, 23, 35, 39, 53, 55, 61, 65, 85, 86, 87, 91, 94, 95,96,97, 98, 99, 100, 101, 103, 104, 105, 106, 109, 110, 111, 117, 120,121,122,123,124,125,126,128,160,164);		      //品牌授权书
        $brand_sq_double = array(35, 153, 191, 197, 202, 203);//第2个品牌授权书。
        if(in_array($goods['brand_id'], $brand_sq))
        {
            $goods['brand_sq']  = 1;
            $goods['brand_img'] = in_array($goods['cat_id'], $brand_sq_double)? $goods['brand_id'].'_2': $goods['brand_id'];
        }
        else
        {
        }
        $goods['click_count']   = ceil($goods['click_count']*1); //销售数量

        $smarty->assign('goods',               $goods);
        $smarty->assign('goodsds',             $goodsds);
        $smarty->assign('goods_id',            $goods['goods_id']);
        $smarty->assign('bought_goods',        get_also_bought($goods_id));                      // 购买了该商品的用户还购买了哪些商品
        $smarty->assign('link_goods',          $link_goods);                                     // 关联商品
        $smarty->assign('back_act',            "goods".$goods_id.".html");
        $smarty->assign('user_rank',           isset($_SESSION['user_rank'])? intval($_SESSION['user_rank']): 0 ); //会员等级
        $user_rank_price = get_user_rank_prices($goods_id, $goods['shop_price']);
        $smarty->assign('rank_prices',         $user_rank_price);                 // 会员等级价格
        $smarty->assign('vip_prices',          $user_rank_price[2]['price_pure']);// 会员vip价格

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
        /*----------------------------------------------产品页【有问必答】列表------------------------------------------------------------*/

        //商品页面买家秀
        $smarty->assign('mjx_info', mjx_info($goods_id));

        //xu:产品属性功能
        $smarty->assign('attrs',    ($goods['goods_type']==16)? get_goods_attr_pure($goods_id): get_goods_all_attr($goods_id));
        $smarty->assign('attr_kj',  get_kuangjia_attr($goods_id));

        //yi:附加数据
        $append = $GLOBALS['db']->GetRow("select * from ".$GLOBALS['ecs']->table('goods_append')." where goods_id=".$goods_id);
        $smarty->assign('append',  $append);


        //===============================================【产品页面_赠品(没有金额限制且免费)提示】=========================================//
        $fav = include_goods_fav($goods_id, 0);
        $gift_tip = array();
        foreach($fav as $k => $v)
        {
            if(!empty($v['gift_tip']))
            {
                $gift_tip[] = trim($v['gift_tip']);
            }
            else
            {
                continue;
            }
        }
        $smarty->assign('gift_tip', $gift_tip);
        $smarty->assign('fav',      full_fav());

        assign_dynamic('goods');
    }

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
        setcookie('ECS[history]', implode(',', $history), gmtime() + 3600 * 24 * 30);
    }
    else
    {
        setcookie('ECS[history]', $goods_id, gmtime() + 3600 * 24 * 30);
    }
    //--------------------------------------------------------------------------------//


    //-----------当前秒杀商品信息------------
    $ctime = $now;
    //$ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE status=0  AND start_time <= $ctime AND end_time >= $ctime ORDER BY rec_id DESC LIMIT 1");
    $ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE status=0 AND goods_id = ".$_GET['id']." ORDER BY rec_id DESC LIMIT 1");

    if ($ms && $ms['goods_id'] == $goods_id)
    {
        //秒杀状态标识：0:未开始  1:进行中	2:已结束
        if ($ms['start_time'] > $now) {
            $ms['ms_status'] = 0;
        } elseif ($ms['start_time'] <= $now && $ms['end_time'] >= $now) {
            $ms['ms_status'] = 1;
        } else {
            $ms['ms_status'] = 2;
        }

        //格式化价格
        if ($ms['price']) {
            $format_cprice = explode('.', $ms['price']);
            $ms['price_int'] = $format_cprice[0];		//整数部分
            $ms['price_decimal'] = $format_cprice[1];	//小数部分
            $ms['price_int_real'] = $format_cprice[0]*$ms['ms_number'];		//整数部分

        }
        //市场价
        $ms['market_price'] = $GLOBALS['db']->GetOne("SELECT market_price FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
        if ( ! $ms['market_price'] OR $ms['market_price'] <= 0.00) {
            $ms['market_price'] = $GLOBALS['db']->GetOne("SELECT shop_price FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
        }
        $ms['market_price'] = sprintf("%01.2f", $ms['market_price'] * $ms['ms_number']);
        $ms['format_market_price'] = price_format($ms['market_price']);

        //节省的金额
        $ms['saving'] = sprintf("%01.2f", $ms['market_price'] - $ms['price']);

        //折扣
        $ms['zhekou'] = sprintf("%01.1f", ($ms['price'] / $ms['market_price']) * 10);

        //格式化秒杀商品的开始或截止时间
        if ($ctime >= $ms['start_time']) {
            //秒杀已开始,格式化截止时间
            $format_ctime['time_type'] = '结束';
            $format_ctime['Y'] = date('Y', $ms['end_time']);
            $format_ctime['n'] = date('n', $ms['end_time']);
            $format_ctime['j'] = date('j', $ms['end_time']);
            $format_ctime['G'] = date('G', $ms['end_time']);
            $format_ctime['i'] = date('i', $ms['end_time']);
        } else {
            //秒杀未开始,格式化开始时间
            $format_ctime['time_type'] = '开始';
            $format_ctime['Y'] = date('Y', $ms['start_time']);
            $format_ctime['n'] = date('n', $ms['start_time']);
            $format_ctime['j'] = date('j', $ms['start_time']);
            $format_ctime['G'] = date('G', $ms['start_time']);
            $format_ctime['i'] = date('i', $ms['start_time']);
        }

    }
    $goods['shop_price'] = sprintf("%.2f", $goods['shop_price']*$ms['ms_number']);
    $smarty->assign('goods',               $goods);
    $smarty->assign('ms', $ms);
    $smarty->assign('format_ctime', @$format_ctime);
    //-----------当前秒杀商品信息------------ END

    //更新点击次数
    $db->query('UPDATE '.$ecs->table('goods')." SET click_count = click_count + 1 WHERE goods_id='$_REQUEST[id]' limit 1;");

    if (1 == 1)
    {
        //商品每天限购数量
        date_default_timezone_set('PRC');
        $now_time = time();
        $goods_limit = array();
        //if (date('G') >= 11 && date('G') < 13)
        if ($now_time > strtotime('2013-09-'.date('d').' 10:55:00') && $now_time < strtotime('2013-09-'.date('d').' 11:30:00'))
        {
            $goods_limit = array(
                '2813' => 10,
                '2815' => 5,
                '2816' => 10,
                '2817' => 10,
                '2818' => 10,
                '2819' => 5
            );
        }
        else
        {
            $goods_limit = array(
                '2813' => 0,
                '2815' => 0,
                '2816' => 0,
                '2817' => 0,
                '2818' => 0,
                '2819' => 0
            );
        }

        foreach ($goods_limit as $key => $value)
        {
            //每天有总量限制
            if ($goods_id == $key)
            {
                if ($value == 0)
                {
                    $goods['goods_number'] = 0;
                    $smarty->assign('goods',	$goods);
                }
                else
                {
                    //获取当天已销售数量(订单中和购物车中)
                    $sales_volume = 0;

                    $b_time = mktime(11, 0, 0, date("m"), date("d"), date("Y"));
                    $e_time = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

                    $cart_add_time = '2013-09-'.date('d').' 10:55:00';

                    //1.购物车商品数量
                    $c_num = $GLOBALS['db']->GetOne("select SUM(goods_number) from ecs_cart where goods_id=".$goods_id." AND add_time > '".$cart_add_time."'");
                    $cart_number = ($c_num)? $c_num: 0;

                    //2.订单中商品的数量
                    $u_order = $GLOBALS['db']->GetAll("SELECT order_id FROM ecs_order_info WHERE order_status <> 2 AND add_time > " .$b_time. " AND add_time < " .$e_time);
                    $o_goods_num = 0;
                    if(!empty($u_order))
                    {
                        foreach($u_order as $k => $v)
                        {
                            $sql = "SELECT SUM(goods_number) FROM ecs_order_goods WHERE order_id=".$v['order_id']." AND goods_id=".$goods_id;
                            $g_num = $GLOBALS['db']->GetOne($sql);
                            if($g_num) $o_goods_num += $g_num;
                        }
                    }

                    $sales_volume = $o_goods_num + $cart_number;
                    //echo $sales_volume;

                    //已售数量超过限制,设置为0
                    if ($sales_volume >= $value)
                    {
                        $goods['goods_number'] = 0;
                        $smarty->assign('goods',	$goods);
                    }

                }
            }
        }


        /**
         * 超过活动商品销售总数， 加入购物车按钮失效(qinqinglin)
         */

        //当前的秒杀信息
        $req_time = $_SERVER['REQUEST_TIME'];
        $ms_info = $GLOBALS['db']->getRow("SELECT * FROM ecs_miaosha WHERE status=0 AND start_time <= " .$req_time. " AND end_time >= " .$req_time. " ORDER BY rec_id DESC LIMIT 1");

        $s_time = $ms_info['start_time'];
        $e_time = $ms_info['end_time'];

        if(!empty($s_time) && !empty($e_time)){
            //1.判断总限购数量
            //购物车商品数量
            $c_num = $GLOBALS['db']->GetOne("SELECT SUM(goods_number) FROM ecs_cart WHERE goods_id=".$goods_id." AND extension_code='miaosha_buy'	AND add_time >= '".date('Y-m-d H:i:s', $s_time)."' AND add_time <= '".date('Y-m-d H:i:s', $e_time)."'");
            $cart_number = (intval($c_num) > 0) ? intval($c_num): 0;

            //订单中商品的数量
            $o_number = $GLOBALS['db']->GetOne("SELECT SUM(b.goods_number) FROM ecs_order_info a LEFT JOIN ecs_order_goods b ON a.order_id=b.order_id   WHERE a.order_status != 2  AND a.add_time >= " .$s_time. " AND a.add_time <= " .$e_time. " AND  b.goods_id=".$goods_id." AND b.extension_code='miaosha_buy'");
            $order_number = (intval($o_number) > 0) ? intval($o_number): 0;

            if($cart_number+ $order_number >= $ms_info['total_limited']){
                $smarty->assign('num_over',	-1);
            }
        }

        $smarty->display('goods_miaosha.dwt');

        /*if($goods['goods_type'] != 15)
        {
            $smarty->display('goods_miaosha.dwt');
        }
        else
        {
            if($goods['cat_id'] != 182 && $goods['cat_id']!=183 && $goods['cat_id']!=185)
            {
                $smarty->display('goods_kj.dwt', $cache_id);//框架模板
            }
            else if($goods['cat_id'] == 182)
            {
                $smarty->display('goods_lhj.dwt', $cache_id);//老花镜
            }
            else
            {
                $smarty->display('goods_pure.dwt', $cache_id);//运动眼镜
            }
        }*/

    }
}

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


/* ----------------------------------------------------------------------------
 * 函数yi：取得商品的关联商品信息列表
 * ----------------------------------------------------------------------------
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
 * 查询评论内容
 *
 * @access  public
 * @params  integer     $id
 * @params  integer     $type
 * @params  integer     $page
 * @return  array
 */
function assign_comment_wap($id, $type)
{
    /* 取得评论列表 */

    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
        " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0".
        ' ORDER BY comment_id DESC limit 0,10';
    $cmt = $GLOBALS['db']->getAll($sql);
    foreach($cmt as $k=>$v){
        $cmt[$k]['add_time'] = date('Y-m-d',$v['add_time']);
    }
    return $cmt;
}

?>
