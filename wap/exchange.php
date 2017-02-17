<?php
/* =======================================================================================================================
 * 商城页面 产品详情页【2012/3/20】【Author:yijiangwen】【TIME:2012/11/26】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_clips.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
if((DEBUG_MODE & 2) != 2){$smarty->caching = true;}

$act      = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';


if($act == '')
{
    /*=======================================参数================================================*/
    $goods_id = isset($_REQUEST['id'])? intval($_REQUEST['id']): 0;//积分兑换的商品id
    $exchange_gd = $GLOBALS['db']->getRow("select * from ecs_exchange_goods where goods_id=".$goods_id." limit 1;");
    if(empty($goods_id) || empty($exchange_gd))
    {
        ecs_header("Location: /index.php\n");
        exit;
    }

    if($_SESSION['user_id'] > 0){ $smarty->assign('user_info', get_user_info()); }//yi:用户登录信息
    $smarty->assign('ur_here',    '积分兑换');
    $smarty->assign('page_title', "积分兑换 - 易视网手机版");
    $smarty->assign('exchange_gd', $exchange_gd);
    /*=======================================参数================================================*/

    //============================================================【放大镜功能】============================================================//
    $ga_first = $GLOBALS['db']->GetRow("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=1 limit 1;");
    $ga_list  = $GLOBALS['db']->GetAll("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=0");
    array_unshift($ga_list, $ga_first);
    $smarty->assign('gallery',      $ga_list);

    /*--------------------------------------------------------整套的评论 && 提问模块数据----------------------------------------------------*/

    //是否起用验证码
    if((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',    mt_rand());
    }
    //提问内容分页
    $sql  = "SELECT count(*) as total1 FROM " .$GLOBALS['ecs']->table('feedback')." WHERE goods_id =".$goods_id.";";
    $info = $GLOBALS['db']->getOne($sql);
    $total1=@$info['total1'];		//总数据条数

    if(empty($_GET['pages'])==true || is_numeric($_GET['pages'])==false){	//pages是否为空
        $page1=1;				//赋值为1
    }else{						//获取变量的值
        $page1=intval($_GET[pages]);
    }
    $pagesize1=5;			    //每页条数

    if($total1<$pagesize1){	    //总数据小于每页条数
        $pagecount1=1;		    //pagecount值为1
    }else{
        if($total1%$pagesize1==0){
            $pagecount1=intval($total1/$pagesize1);	//共有几页
        }else{
            $pagecount1=intval($total1/$pagesize1)+1;
        }
    }

    $page_prev = ($page1 > 1) ? $page1 - 1 : 1;
    $page_next = ($page1 < $pagecount1) ? $page1 + 1 : $pagecount1;

    $smarty->assign("total1",    $total1);
    $smarty->assign("pagesize1", $pagesize1);
    $smarty->assign("prev",      $page_prev);
    $smarty->assign("next",      $page_next);
    $smarty->assign("page1",     $page1);
    $smarty->assign("pagecount1",$pagecount1);

    $sql_all = "select * from ".$GLOBALS['ecs']->table('feedback')." where goods_id=".$goods_id." and msg_status=1 order by msg_time desc limit ".($page1-1)*$pagesize1.",".$pagesize1.";";
    $query   = mysql_query($sql_all);
    $array   = array();
    while( $myrow=mysql_fetch_array($query) ){
        array_push($array,$myrow);
    }
    //-------------------合并-------------------
    $len = sizeof($array);
    for($i=0; $i< $len; $i++){
        //yi:取得msg_id
        $msg_id  = $array[$i]["msg_id"];
        $sql_con = "select msg_content from ".$GLOBALS['ecs']->table('feedback')." where parent_id=".$msg_id.";";
        $querys  = mysql_query($sql_con);
        $array1  = array();
        while( $myrow=mysql_fetch_array($querys) ){
            array_push($array1,$myrow);
        }
        $array[$i]['msg']=$array1[0]['msg_content'];
    }
    $smarty->assign("len",$len);

    //---msg_id 取得答复数据放到对应的数组后面---

    if(!$array){
        $smarty->assign("iscommo","F");	//判断如果执行失败则输出模板变量iscommo的值为F
    }else{
        $smarty->assign("iscommo","T");	//判断如果执行成功，则输出模板变量iscommo的值为T，
        //yi:$arr用户留言和问答
        $smarty->assign("arr",$array);	//定义模板变量arraybbstell，输出数据库中数据
    }
    /*--------------------------------------------------------整套评论 && 提问end--------------------------------------------------------------------*/

    //基本的配置数据板块
    $smarty->assign('cfg',        $_CFG);
    $smarty->assign('lang',       $_LANG);
    $smarty->assign('affiliate',  unserialize($GLOBALS['_CFG']['affiliate']));//分享给好友
    $smarty->assign('type',       0);

    //商品详细信息
    $goods   = get_goods_info($goods_id);
    $goodsds = get_goods_ds($goods_id);//度数列表
    $exgoods = get_exchange_brief_info($goods_id);//积分兑换商品信息

    if($goods === false || false === $exgoods)
    {
        ecs_header("Location: /index.php\n");
        exit;
    }
    else
    {
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
                $pei[$k]['ds'] = get_goodsds_info($v['goods_id']);//配件商品度数列表
            }
        }

        if($goods['goods_number']<=0)
        {
            $pei = array();//主商品库存为0
        }
        // 获取评论内容
        $comment = assign_comment_wap($goods['goods_id'],0);
        $smarty->assign('comment',            $comment);//评论内容

        $smarty->assign('peijian',            $pei);//配件商品

        $g_save = ($g_shop_p<=$g_market_p)? floatval($g_market_p-$g_shop_p): 0;//节省多少钱
        $smarty->assign('g_market_price',  $g_market_p);//配件商品
        $smarty->assign('g_shop_price',    $g_shop_p);  //配件商品
        $smarty->assign('g_save',          $g_save);    //配件商品
        $smarty->assign('fitting_id',      $fitting_id);//配件商品
        //============================================================【组合购买功能END】=========================================================//

        $smarty->assign('goods',              $goods);
        $smarty->assign('goodsds',            $goodsds);//隐形眼镜兑换度数
        $smarty->assign('exgoods',            $exgoods);
        $smarty->assign('goods_id',           $goods['goods_id']);
        $smarty->assign('id',                 $goods['goods_id']); //评论中使用的id

        //页面关键字
        $smarty->assign('keywords',           htmlspecialchars($goods['keywords']));
        $smarty->assign('description',        htmlspecialchars($goods['goods_brief']));

        //商品页面买家秀
        //$smarty->assign('mjx_info', mjx_info($goods_id));

        //xu:产品属性功能 2012/9/9
        $attrs = get_goods_all_attr($goods_id);
        $smarty->assign('attrs',  $attrs);

        //yi:附加数据
        $append = $GLOBALS['db']->GetRow("select * from ".$GLOBALS['ecs']->table('goods_append')." where goods_id=".$goods_id);
        $smarty->assign('append',  $append);

    }

    $smarty->display('exchange_goods.dwt');
}
elseif($act == 'zk')
{
    $rec_id = isset($_GET['rec_id'])? intval($_GET['rec_id']): 0;
    $sql    = "select * from ".$GLOBALS['ecs']->table('exchange_goods')." where rec_id=".$rec_id." and is_show=1 limit 1;";
    $exchange_info = $GLOBALS['db']->GetRow($sql);
    if(empty($exchange_info))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /*=======================================参数================================================*/
    $goods_id = intval($exchange_info['goods_id']);//兑换商品
    $cache_id = $goods_id . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'] . '-exchange';
    $cache_id = sprintf('%X', crc32($cache_id));

    if($_SESSION['user_id'] > 0){ $smarty->assign('user_info', get_user_info()); }//yi:用户登录信息

    $smarty->assign('ur_here',    '积分兑换');
    $smarty->assign('page_title', "积分兑换 - 易视网手机版");
    /*=======================================参数================================================*/

    //============================================================【放大镜功能】============================================================//
    $ga_first = $GLOBALS['db']->GetRow("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=1 limit 1;");
    $ga_list  = $GLOBALS['db']->GetAll("select * from ecs_goods_gallery where goods_id=".$goods_id." and is_main=0");
    array_unshift($ga_list, $ga_first);
    $smarty->assign('gallery',      $ga_list);

    /*--------------------------------------------------------整套的评论 && 提问模块数据----------------------------------------------------*/

    //提问内容分页
    $sql  = "SELECT count(*) as total1 FROM " .$GLOBALS['ecs']->table('feedback')." WHERE goods_id =".$goods_id.";";
    $info = $GLOBALS['db']->getOne($sql);
    $total1=$info['total1'];		//总数据条数

    if(empty($_GET['pages'])==true || is_numeric($_GET['pages'])==false){	//pages是否为空
        $page1=1;				//赋值为1
    }else{						//获取变量的值
        $page1=intval($_GET['pages']);
    }
    $pagesize1=5;			    //每页条数
    if($total1<$pagesize1){	    //总数据小于每页条数
        $pagecount1=1;		    //pagecount值为1
    }else{
        if($total1%$pagesize1==0){
            $pagecount1=intval($total1/$pagesize1);	//共有几页
        }else{
            $pagecount1=intval($total1/$pagesize1)+1;
        }
    }
    $page_prev = ($page1 > 1) ? $page1 - 1 : 1;
    $page_next = ($page1 < $pagecount1) ? $page1 + 1 : $pagecount1;

    $smarty->assign("total1",    $total1);
    $smarty->assign("pagesize1", $pagesize1);
    $smarty->assign("prev",      $page_prev);
    $smarty->assign("next",      $page_next);
    $smarty->assign("page1",     $page1);
    $smarty->assign("pagecount1",$pagecount1);

    $sql_all = "select * from ".$GLOBALS['ecs']->table('feedback')." where goods_id=".$goods_id." and msg_status=1 order by msg_time desc limit ".($page1-1)*$pagesize1.",".$pagesize1.";";
    $query   = mysql_query($sql_all);
    $array   = array();
    while( $myrow=mysql_fetch_array($query) ){
        array_push($array,$myrow);
    }
    //-------------------合并-------------------
    $len = sizeof($array);
    for($i=0; $i< $len; $i++){
        //yi:取得msg_id
        $msg_id  = $array[$i]["msg_id"];
        $sql_con = "select msg_content from ".$GLOBALS['ecs']->table('feedback')." where parent_id=".$msg_id.";";
        $querys  = mysql_query($sql_con);
        $array1  = array();
        while( $myrow=mysql_fetch_array($querys) ){
            array_push($array1,$myrow);
        }
        $array[$i]['msg']=$array1[0]['msg_content'];
    }
    $smarty->assign("len",$len);
    //---msg_id 取得答复数据放到对应的数组后面---//
    if(!$array){
        $smarty->assign("iscommo","F");	//判断如果执行失败则输出模板变量iscommo的值为F
    }else{
        $smarty->assign("iscommo","T");	//判断如果执行成功，则输出模板变量iscommo的值为T，
        //yi:$arr用户留言和问答
        $smarty->assign("arr",$array);	//定义模板变量arraybbstell，输出数据库中数据
    }
    /*--------------------------------------------------------整套评论 && 提问end--------------------------------------------------------------------*/

    //基本的配置数据板块
    $smarty->assign('cfg',        $_CFG);
    $smarty->assign('lang',       $_LANG);
    $smarty->assign('affiliate',  unserialize($GLOBALS['_CFG']['affiliate']));//分享给好友
    $smarty->assign('type',       0);
    $goods   = get_goods_info($goods_id);  //商品详细信息
    $goodsds = get_goods_ds($goods_id);//度数列表
    $exgoods = get_exchange_brief_info($goods_id);//积分兑换商品信息

    if($goods === false)
    {
        ecs_header("Location: ./\n");
        exit;
    }
    else
    {
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
            $g_shop_p   += $pei[$k]['group_price'] ;//组合购买价
            $fitting_id .= ','.$v['goods_id'];

            //yi:配件商品有度数的情况
            if($v['eye_id']>0)
            {
                $pei[$k]['ds'] = get_goodsds_info($v['goods_id']);//配件商品度数列表
            }
        }

        if($goods['goods_number']<=0)
        {
            $pei = array();//主商品库存为0
        }
        // 获取评论内容
        $comment = assign_comment_wap($goods['goods_id'],0);
        $smarty->assign('comment',            $comment);//评论内容

        $smarty->assign('peijian',            $pei);//配件商品


        $g_save = ($g_shop_p<=$g_market_p)? floatval($g_market_p-$g_shop_p): 0;//节省多少钱
        $smarty->assign('g_market_price',  $g_market_p);//配件商品
        $smarty->assign('g_shop_price',    $g_shop_p);  //配件商品
        $smarty->assign('g_save',          $g_save);    //配件商品
        $smarty->assign('fitting_id',      $fitting_id);//配件商品
        //============================================================【组合购买功能END】=========================================================//


        $smarty->assign('goods',              $goods);
        $smarty->assign('goodsds',            $goodsds);//隐形眼镜兑换度数
        $smarty->assign('exgoods',            $exgoods);
        $smarty->assign('goods_id',           $goods['goods_id']);
        $smarty->assign('id',                 $goods['goods_id']); //评论中使用的id

        //页面关键字
        $smarty->assign('keywords',           htmlspecialchars($goods['keywords']));
        $smarty->assign('description',        htmlspecialchars($goods['goods_brief']));

        //商品页面买家秀
        //$smarty->assign('mjx_info', mjx_info($goods_id));

        //xu:产品属性功能 2012/9/9
        $attrs = get_goods_all_attr($goods_id);
        $smarty->assign('attrs',  $attrs);

        //yi:附加数据
        $append = $GLOBALS['db']->GetRow("select * from ".$GLOBALS['ecs']->table('goods_append')." where goods_id=".$goods_id);
        $smarty->assign('append',  $append);
        $smarty->assign('exchange_info',      $exchange_info);
    }
    $cache_id = 0;
    $smarty->display('exchange_goods_zhe.dwt',   $cache_id);
}
/*=========================================================积分兑换_立即兑换【功能】=========================================================*/
elseif($act == 'buy')
{
    //echo $act; die;
    //上一页
    if(!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'exchange.php?') ? $GLOBALS['_SERVER']['HTTP_REFERER'] : './';
    }

    //判断用户是否登录
    if($_SESSION['user_id'] <= 0){
        show_message_ex($_LANG['eg_error_login'], $_LANG['back_up_page'], $back_act, 'error');
    }

    //积分兑换商品ID
    $goods_id = isset($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
    if($goods_id <= 0)
    {
        ecs_header("Location: /index.php\n");
        exit;
    }

    //兑换商品的信息
    $goods = get_exchange_goods_info_wap($goods_id);
    if(empty($goods))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    //检查兑换商品是否有库存
    if($goods['goods_number'] == 0 && $_CFG['use_storage'] == 1)
    {
        show_message_ex($_LANG['eg_error_number'], $_LANG['back_up_page'], $back_act, 'error');
    }

    //===============美国片专场日限制用户兑换量========================
    $now = time();

    $goods_arr = array(4154,3672,4491,981,3671,4464,4492,221,3000,4462,2983,2368,4443,4383);

    if(in_array($goods_id,$goods_arr)){

        if($now > strtotime('2015-01-12 00:00:00') && $now < strtotime('2015-01-31 00:00:00')){
            date_default_timezone_set ('Asia/Shanghai');
            if($goods_id == '4154'){
                if(($now > strtotime('2015-01-13 11:00:00') && $now < strtotime('2015-01-13 11:01:00')) || ($now > strtotime('2015-01-14 11:00:00') && $now < strtotime('2015-01-14 11:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '3672'){
                if(($now > strtotime('2015-01-13 16:00:00') && $now < strtotime('2015-01-13 16:01:00')) || ($now > strtotime('2015-01-14 16:00:00') && $now < strtotime('2015-01-14 16:01:00')) || ($now > strtotime('2015-01-27 16:00:00') && $now < strtotime('2015-01-27 16:01:00')) || ($now > strtotime('2015-01-28 16:00:00') && $now < strtotime('2015-01-28 16:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '4491'){
                if(($now > strtotime('2015-01-15 11:00:00') && $now < strtotime('2015-01-15 11:01:00')) || ($now > strtotime('2015-01-16 11:00:00') && $now < strtotime('2015-01-16 11:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '981'){
                show_message_ex("商品已被抢完！", $_LANG['back_up_page'], $back_act, 'error');
            }elseif($goods_id == '3671'){
                if(($now > strtotime('2015-01-17 11:00:00') && $now < strtotime('2015-01-17 11:01:00')) || ($now > strtotime('2015-01-18 11:00:00') && $now < strtotime('2015-01-18 11:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '4464'){
                show_message_ex("商品已被抢完！", $_LANG['back_up_page'], $back_act, 'error');
            }elseif($goods_id == '4492'){
                if(($now > strtotime('2015-01-19 11:00:00') && $now < strtotime('2015-01-19 11:01:00')) || ($now > strtotime('2015-01-20 11:00:00') && $now < strtotime('2015-01-20 11:01:00')) || ($now > strtotime('2015-01-21 16:00:00') && $now < strtotime('2015-01-21 16:01:00')) || ($now > strtotime('2015-01-22 16:00:00') && $now < strtotime('2015-01-22 16:01:00')) || ($now > strtotime('2015-01-29 11:00:00') && $now < strtotime('2015-01-29 11:01:00')) || ($now > strtotime('2015-01-30 11:00:00') && $now < strtotime('2015-01-30 11:01:00')) ){
                    $isok = 1;
                }
            }elseif($goods_id == '221'){
                show_message_ex("商品已被抢完！", $_LANG['back_up_page'], $back_act, 'error');
            }elseif($goods_id == '3000'){
                show_message_ex("商品已被抢完！", $_LANG['back_up_page'], $back_act, 'error');
            }elseif($goods_id == '4462'){
                if(($now > strtotime('2015-01-23 11:00:00') && $now < strtotime('2015-01-23 11:01:00')) || ($now > strtotime('2015-01-24 11:00:00') && $now < strtotime('2015-01-24 11:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '2983'){
                if(($now > strtotime('2015-01-23 16:00:00') && $now < strtotime('2015-01-23 16:01:00')) || ($now > strtotime('2015-01-24 16:00:00') && $now < strtotime('2015-01-24 16:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '2368'){
                if(($now > strtotime('2015-01-25 11:00:00') && $now < strtotime('2015-01-25 11:01:00')) || ($now > strtotime('2015-01-26 11:00:00') && $now < strtotime('2015-01-26 11:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '4443'){
                if(($now > strtotime('2015-01-25 16:00:00') && $now < strtotime('2015-01-25 16:01:00')) || ($now > strtotime('2015-01-26 16:00:00') && $now < strtotime('2015-01-26 16:01:00'))){
                    $isok = 1;
                }
            }elseif($goods_id == '4383'){
                if(($now > strtotime('2015-01-27 11:00:00') && $now < strtotime('2015-01-27 11:01:00')) || ($now > strtotime('2015-01-28 11:00:00') && $now < strtotime('2015-01-28 11:01:00'))){
                    $isok = 1;
                }
            }


            if($isok!=1){
                show_message_ex("对不起！活动尚未开始或以结束！", $_LANG['back_up_page'], $back_act, 'error');
            }



            $cartitems_num = $GLOBALS['db']->getOne("SELECT sum(goods_number) as num FROM ecs_cart WHERE session_id = '".SESS_ID."' AND extension_code = 'exchange' AND goods_id = ".$goods_id);

            $orderitems_num = $GLOBALS['db']->getOne("SELECT sum(b.goods_id) as num FROM ecs_order_info AS a left join ecs_order_goods AS b on a.order_id = b.order_id WHERE a.user_id =".$_SESSION['user_id']." AND b.extension_code = 'exchange' AND b.goods_id = ".$goods_id);

            if($cartitems_num>=2 || $orderitems_num>=2){
                show_message_ex("对不起！此商品为秒杀商品，每人限购2件！", $_LANG['back_up_page'], $back_act, 'error');
            }
        }

    }




    //===============周年庆积分兑换商品，每日限制兑换总量========================
    $goods_define = array(2655, 2368, 2656, 2657, 2658, 2311, 2659, 2660, 2661);
    if (in_array($goods_id, $goods_define))
    {
        //商品每天限购数量
        date_default_timezone_set('PRC');
        $goods_limit = array();
        if (time() > strtotime('2013-08-'.date('d').' 10:54:00') && date('G') < 13)
        {
            $goods_limit = array(
                '2655' => 10,
                '2368' => 5,
                '2656' => 16,
                '2657' => 20,
                '2311' => 17,
                '2659' => 16,
                '2660' => 10,
                '2661' => 16
            );
        }
        else
        {
            $goods_limit = array(
                '2655' => 0,
                '2368' => 0,
                '2656' => 0,
                '2657' => 0,
                '2658' => 0,
                '2311' => 0,
                '2659' => 0,
                '2660' => 0,
                '2661' => 0
            );
        }

        foreach ($goods_limit as $key => $value)
        {
            //每天有总量限制的
            if ($goods_id == $key)
            {
                if ($value == 0) //不参与
                {
                    $goods['goods_number'] = 0;
                    show_message_ex($_LANG['eg_error_number'], $_LANG['back_up_page'], $back_act, 'error');
                }
                else
                {
                    //获取当天已销售数量(订单中和购物车中)
                    $sales_volume = 0;

                    $b_time = mktime(10, 54, 0, date("m"), date("d"), date("Y"));
                    $e_time = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

                    $cart_add_time = '2013-08-'.date('d').' 10:54:00';

                    //1.购物车商品数量
                    $c_num = $GLOBALS['db']->GetOne("select SUM(goods_number) from ecs_cart where goods_id=".$goods_id." AND extension_code='exchange' AND add_time > '".$cart_add_time."'");
                    $cart_number = ($c_num)? $c_num: 0;

                    //2.订单中商品的数量
                    $u_order = $GLOBALS['db']->GetAll("SELECT order_id FROM ecs_order_info WHERE order_status <> 2 AND add_time > " .$b_time. " AND add_time < " .$e_time);
                    $o_goods_num = 0;
                    if(!empty($u_order))
                    {
                        foreach($u_order as $k => $v)
                        {
                            $sql = "SELECT SUM(goods_number) FROM ecs_order_goods WHERE order_id=".$v['order_id']." AND goods_id=".$goods_id." AND extension_code='exchange'";
                            $g_num = $GLOBALS['db']->GetOne($sql);
                            if($g_num) $o_goods_num += $g_num;
                        }
                    }

                    $sales_volume = $o_goods_num + $cart_number;

                    //已售数量超过限制,设置为0
                    if ($sales_volume >= $value)
                    {
                        $goods['goods_number'] = 0;
                        show_message_ex($_LANG['eg_error_number'], $_LANG['back_up_page'], $back_act, 'error');
                    }
                }
            }
        }
    }
    //=========================周年庆 限量 END===========================


    //检查兑换商品是否是取消
    if($goods['is_exchange'] == 0)
    {
        show_message_ex($_LANG['eg_error_status'], $_LANG['back_up_page'], $back_act, 'error');
    }

    //判断会员积分是否足够兑换（包含购物车中已经兑换的商品）

    //购物车中商品所扣积分
    //get_cart_integral();

    //会员积分是否足够兑换该商品
    $user_info   = get_user_info($_SESSION['user_id']);
    $user_points = $user_info['pay_points'] - order_exchange_goods_integral($_SESSION['user_id']);//用户会员积分-购物车中积分兑换商品应扣除的积分=当前会员可用积分

    if($goods['exchange_integral'] > $user_points)
    {
        show_message_ex($_LANG['eg_error_integral'], $_LANG['back_up_page'], $back_act, 'error');
    }

    /*-------------------------------------------取得商品属性-----------------------------------------*/
    $specs = '0';//商品属性id
    foreach($_POST as $key => $value)
    {
        if(strpos($key, 'spec_') !== false)
        {
            $specs .= ',' . intval($value);
        }
    }
    //查询规格名称和值，不考虑价格
    $attr_list = array();
    $sql = "SELECT a.attr_name, g.attr_value " .
        "FROM " . $ecs->table('goods_attr') . " AS g, " .$ecs->table('attribute') . " AS a " .
        "WHERE g.attr_id = a.attr_id " .
        "AND g.goods_attr_id " . db_create_in($specs);
    $res = $db->query($sql);
    while($row = $db->fetchRow($res))
    {
        $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
    }
    $goods_attr = join(chr(13) . chr(10), $attr_list);
    /*-------------------------------------------取得商品属性end-----------------------------------*/

    //清空购物车中所有 积分兑换商品
    //include_once(ROOT_PATH . 'includes/lib_order.php');
    //clear_cart(CART_EXCHANGE_GOODS);

    /*============================================yi:积分兑换商品 加入购物车==========================================================*/

    //隐形眼镜度数,统一使用左眼度数，和左眼数量
    $goods_ds = (isset($_POST['goods_ds']) && !empty($_POST['goods_ds'])) ? trim($_POST['goods_ds']) : '';
    $zcount   = empty($goods_ds) ? '' : 1;
    $number   = 1;

    //积分兑换商品加入购物车 积分:$goods['exchange_integral']
    $cart = array(
        'user_id'        => $_SESSION['user_id'],
        'session_id'     => SESS_ID,
        'goods_id'       => $goods['goods_id'],
        'goods_sn'       => addslashes($goods['goods_sn']),
        'goods_name'     => addslashes($goods['goods_name']),
        'market_price'   => $goods['market_price'],
        'goods_price'    => 0,                       //积分兑换商品，价格为0
        'goods_number'   => $number,                 //积分兑换商品，数量为1
        'goods_attr'     => addslashes($goods_attr),
        'goods_attr_id'  => $specs,
        'is_real'        => $goods['is_real'],
        'extension_code' => 'exchange',              //积分兑换商品，唯一标识
        'parent_id'      => 0,
        'rec_type'       => CART_GENERAL_GOODS,
        'is_gift'        => 0,
        'zselect'        => $goods_ds,
        'zcount'         => $zcount,
        'yselect'        => '',
        'ycount'         => ''
    );
    $db->autoExecute($ecs->table('cart'), $cart, 'INSERT');

    //跳转到购物车页面
    ecs_header("Location: flow.php\n");
    exit;
    /*============================================积分兑换商品 加入购物车end==========================================================*/
}
/*=========================================================积分兑换_加钱购买兑换【功能】=========================================================*/
elseif($_REQUEST['act'] == 'buy_zhe')
{
    //上一页
    if(!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'exchange.php?') ? $GLOBALS['_SERVER']['HTTP_REFERER'] : './';
    }

    //判断用户是否登录
    if($_SESSION['user_id'] <= 0){
        show_message_ex($_LANG['eg_error_login'], $_LANG['back_up_page'], $back_act, 'error');
    }

    //积分兑换商品ID
    $goods_id = isset($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
    if($goods_id <= 0)
    {
        ecs_header("Location: /index.php\n");
        exit;
    }

    //2014-08-19限制几款产品的积分购买数量: FROM  `ecs_exchange_goods` rec_id IN ( 206, 261, 230 )
    $goods_define = array(861, 981, 4050);
    $goods_limit = array('1456' => 3, '1298' => 3, '4047' => 2);
    $limit_number = 1;
    foreach ($goods_limit as $key => $value) {
        if ($goods_id == $key) {
            $limit_number = $value;
        }
    }
    if (in_array($goods_id, $goods_define))
    {
        date_default_timezone_set('PRC');
        //1.购物车数量
        $c_num = $GLOBALS['db']->GetOne("select SUM(goods_number) from ecs_cart WHERE goods_id=".$goods_id." AND extension_code='exchange_buy' ");
        $cart_number = ($c_num)? $c_num: 0;
        //2.订单数量(时间很短，暂且只检查购物车)
        /*$u_order = $GLOBALS['db']->GetAll("SELECT order_id FROM ecs_order_info WHERE order_status <> 2 ");
        $o_goods_num = 0;
        if($u_order)
        {
            foreach($u_order as $k => $v)
            {
                $sql = "SELECT SUM(goods_number) FROM ecs_order_goods WHERE order_id=".$v['order_id']." AND goods_id=".$goods_id." AND extension_code='exchange_buy'";
                $g_num = $GLOBALS['db']->GetOne($sql);
                if($g_num) $o_goods_num += $g_num;
            }
        }*/

        //$sales_volume = $o_goods_num + $cart_number;
        $sales_volume = $cart_number;

        //已售数量超过限制,设置为0
        //if ($sales_volume >= $limit_number)
        if ($sales_volume >= 1)
        {
            show_message_ex($_LANG['eg_error_number'], $_LANG['back_up_page'], $back_act, 'error');
        }

    }
    //2014-08-19限制几款产品的积分购买数量 END

    //yi：积分兑换数据
    $zhe_price = isset($_POST['zhe_price']) ? floatval($_POST['zhe_price']): 0.0;
    $zhe_jifen = isset($_POST['zhe_jifen']) ? intval($_POST['zhe_jifen']): 0;
    $user_id   = $_SESSION['user_id'];
    $goods     = get_goods_info($goods_id);

    //兑换商品的信息
//    $goods = get_exchange_goods_info($goods_id);
//    if(empty($goods))
//    {
//        ecs_header("Location: ./\n");
//        exit;
//    }

    //检查兑换商品是否有库存
//    if($goods['goods_number'] == 0 && $_CFG['use_storage'] == 1)
//    {
//        show_message($_LANG['eg_error_number'], array($_LANG['back_up_page']), array($back_act), 'error');
//    }

    //检查兑换商品是否是取消
//    if($goods['is_exchange'] == 0)
//    {
//        show_message($_LANG['eg_error_status'], array($_LANG['back_up_page']), array($back_act), 'error');
//    }

    //判断会员积分是否足够兑换（包含购物车中已经兑换的商品）

    //购物车中商品所扣积分
    //get_cart_integral();

    //会员积分是否足够兑换该商品
    $user_info   = get_user_info($_SESSION['user_id']);
    $user_points = $user_info['pay_points'] - order_exchange_goods_integral($_SESSION['user_id']);//用户会员积分-购物车中积分兑换商品应扣除的积分=当前会员可用积分

    if($zhe_jifen > $user_points)
    {
        show_message_ex($_LANG['eg_error_integral'], $_LANG['back_up_page'], $back_act, 'error');
    }

    /*-------------------------------------------取得商品属性-----------------------------------------*/
    $specs = '0';//商品属性id
    foreach($_POST as $key => $value)
    {
        if(strpos($key, 'spec_') !== false)
        {
            $specs .= ',' . intval($value);
        }
    }
    //查询规格名称和值，不考虑价格
    $attr_list = array();
    $sql = "SELECT a.attr_name, g.attr_value " .
        "FROM " . $ecs->table('goods_attr') . " AS g, " .$ecs->table('attribute') . " AS a " .
        "WHERE g.attr_id = a.attr_id " .
        "AND g.goods_attr_id " . db_create_in($specs);
    $res = $db->query($sql);
    while($row = $db->fetchRow($res))
    {
        $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
    }
    $goods_attr = join(chr(13) . chr(10), $attr_list);
    /*-------------------------------------------取得商品属性end-----------------------------------*/

    //清空购物车中所有 积分兑换商品
    //include_once(ROOT_PATH . 'includes/lib_order.php');
    //clear_cart(CART_EXCHANGE_GOODS);

    /*============================================yi:积分兑换商品 加入购物车==========================================================*/

    //隐形眼镜度数,统一使用左眼度数，和左眼数量
    $goods_ds = (isset($_POST['goods_ds']) && !empty($_POST['goods_ds'])) ? trim($_POST['goods_ds']) : '';
    $zcount   = empty($goods_ds) ? '' : 1;
    $number   = 1;


    //积分兑换商品加入购物车 积分:$goods['exchange_integral']
    $cart = array(
        'user_id'        => $_SESSION['user_id'],
        'session_id'     => SESS_ID,
        'goods_id'       => $goods_id,
        'goods_sn'       => addslashes($goods['goods_sn']),
        'goods_name'     => addslashes($goods['goods_name']),
        'market_price'   => $goods['market_price_nochar'],
        'goods_price'    => $zhe_price,              //积分兑换商品，折扣价格
        'goods_number'   => $number,                 //积分兑换商品，数量为1
        'goods_attr'     => addslashes($goods_attr),
        'goods_attr_id'  => $specs,
        'is_real'        => $goods['is_real'],
        'extension_code' => 'exchange_buy',          //积分兑换商品，唯一标识
        'extension_id'   => $zhe_jifen,              //消耗的积分
        'parent_id'      => 0,
        'rec_type'       => CART_GENERAL_GOODS,
        'is_gift'        => 0,
        'zselect'        => $goods_ds,
        'zcount'         => $zcount,
        'yselect'        => '',
        'ycount'         => ''
    );
    $db->autoExecute($ecs->table('cart'), $cart, 'INSERT');

    //加入购物车之后，扣除用户积分并记录
    if($zhe_jifen>0)
    {
        $log_msg = date('Y年m月d日 H时i分', $_SERVER['REQUEST_TIME']+8*3600).' 积分折扣购买商品：消费'.$zhe_jifen.'积分';
        log_account_change($user_id, 0, 0, 0, $zhe_jifen*(-1), $log_msg);
    }

    //跳转到购物车页面
    ecs_header("Location: flow.php\n");
    exit;
    /*============================================积分兑换商品 加入购物车end==========================================================*/
}


//======================================================================【函数】======================================================================//
//yi:获得积分兑换的商品的兑换信息
function get_exchange_brief_info($goods_id)
{
    if(empty($goods_id))
    {
        return false;
    }
    else
    {
        $sql = "select * from ".$GLOBALS['ecs']->table('exchange_goods')." where goods_id='$goods_id' ";
        $row = $GLOBALS['db']->getRow($sql);
        return $row;
    }
}
/*
 * xu：获取该商品所有属性参数
 */
function get_goods_all_attr ($goods_id = 0)
{
    if ($goods_id) {
        $attrs = array();
        $res = $GLOBALS['db']->query('SELECT attr_id, attr_value FROM ' . $GLOBALS['ecs']->table('goods_attr'). ' WHERE goods_id=' . $goods_id);
        while($row = $GLOBALS['db']->fetchRow($res)){
            $attrs[] = $row;
        }
        return $attrs;
    }
}
/**
 * 获得积分兑换商品的详细信息
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function get_exchange_goods_info_wap($goods_id)
{
    $time = gmtime();
    $sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, eg.exchange_integral, eg.is_exchange ' .
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('exchange_goods') . ' AS eg ON g.goods_id = eg.goods_id ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' .
        "WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " .
        'GROUP BY g.goods_id';

    $row = $GLOBALS['db']->getRow($sql);

    if ($row !== false)
    {
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot';
        }

        if ($watermark_img != '')
        {
            $row['watermark_img'] =  $watermark_img;
        }

        /* 修正重量显示 */
        $row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
            $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
            ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

        /* 修正上架时间显示 */
        $row['add_time']      = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

        /* 修正商品图片 */
        $row['goods_img']     = get_image_path($goods_id, $row['goods_img']);
        $row['original_img']  = get_image_path($goods_id, $row['original_img']);
        $row['goods_thumb']   = get_image_path($goods_id, $row['goods_thumb'], true);
        return $row;
    }
    else
    {
        return false;
    }
}
/**
 * 显示一个提示信息 *
 * @access  public
 * @param   string  $content            显示内容
 * @param   string  $link				返回上一页
 * @param   string  $href				---yi---专门自动跳转到首页---
 * @param   string  $type               信息类型：warning, error, info
 * @param   string  $auto_redirect      是否自动跳转--默认改成false--yi---
 * @return  void
 */
function show_message_ex($content, $links = '', $hrefs = '', $type = 'info', $auto_redirect = false)
{
    assign_template();
    $msg['content'] = $content;
    if(is_array($links) && is_array($hrefs))
    {
        if(!empty($links) && count($links) == count($hrefs))
        {
            foreach($links as $key =>$val)
            {
                $msg['url_info'][$val] = $hrefs[$key];
            }
            $msg['back_url'] = $hrefs['0'];//自动跳转url
        }
    }
    else
    {
        $link   = empty($links) ? $GLOBALS['_LANG']['back_up_page'] : $links;
        $href   = empty($hrefs) ? 'javascript:history.back()'       : $hrefs;
        //$msg['url_info'][$link] = $link;//返回上一页
        $msg['back_url'] = $href;       //首页
    }

    $msg['type']    = $type;
    $position = assign_ur_here(0, $GLOBALS['_LANG']['sys_msg']);
    $GLOBALS['smarty']->assign('page_title', '系统提示 - 易视网手机版');   // 页面标题
    $GLOBALS['smarty']->assign('ur_here',    '系统提示'); // 当前位置

    if(is_null($GLOBALS['smarty']->get_template_vars('helps')))
    {
        $GLOBALS['smarty']->assign('helps', get_shop_help());       // 网店帮助

    }

    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);    //自动跳转
    $GLOBALS['smarty']->assign('link', $links);     // 按钮名称
    $GLOBALS['smarty']->assign('message', $msg);
    $GLOBALS['smarty']->display('message.dwt');
    exit;
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