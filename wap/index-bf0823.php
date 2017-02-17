<?php
define('IN_ECS', true);
require_once(dirname(__FILE__) . '/includes/init.php');

$user_id = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']): 0;

//yi:网站检查页面
if('www.ysyj.com' == $_SERVER['HTTP_HOST'] || 'ysyj.com' == $_SERVER['HTTP_HOST'])
{
	header("Location: ysyj.php \n"); exit;
}

$smarty->assign('title',         '易视网EaseEyes 隐形眼镜 彩色隐形眼镜 美瞳 眼镜 护理液—专业隐形眼镜服务机构!');
$smarty->assign('keywords',      '易视网-让您的眼睛更舒适，专业隐形眼镜服务机构！30天退换货售后保障，无可比拟的价格优势，眼科医师的专业服务！博士伦、视康、海昌、强生美瞳、大美目、NEO隐形眼镜等品牌全球同步上市！');
$smarty->assign('here',          '首页' );

//易视公告文章（4篇）
$smarty->assign('report_yishi',  yi_get_article_info(12, 0, 4) );
//var_dump(get_tg_goods());
$smarty->assign('new_goods',     get_new_goods(1));          //新品
$smarty->assign('goods_reduce',  get_show_jiangjia(12));     //降价
$smarty->assign('ms',            get_ms_goods() );           //秒杀商品
$smarty->assign('tg',            get_tg_goods() );           //团购商品
// 隐形眼镜
$cpeyeslist1  = category_get_eyeslist(1, "",6,0);
// 框架眼镜
$cpeyeslist21 = category_get_eyeslist(159,"",5,0);
$smarty->assign('goods_list_tmp',$cpeyeslist1);
$smarty->assign('goods_list_kj', $cpeyeslist21);
$smarty->assign('brand_list',    get_brand_info_list(9));   //品牌列表

/*// 本地广告位置ID
$smarty->assign('big_ad',        ad_info(59,5) );            //5张焦点图
$smarty->assign('banner2',       ad_info(60,2) );           //中部焦点图（功能隐形眼镜上部）
$smarty->assign('banner3',       ad_info(61,2) );           //底部焦点图（品牌推荐上部）
$smarty->assign('ztbb',          ad_info(62,1) );           //主题街（大）
$smarty->assign('ztbs',          ad_info(63,1) );           //主题街（小）

$smarty->assign('hd1',           ad_info_wap(64) );           //新品抢先购
$smarty->assign('hd2',           ad_info_wap(65) );           //品牌特惠
$smarty->assign('hd3',           ad_info_wap(66) );           //周末狂欢场

$smarty->assign('yx1',           ad_info_wap(67) );           //散光定制
$smarty->assign('yx2',           ad_info_wap(68) );           //高度近视
$smarty->assign('yx3',           ad_info_wap(69) );           //远视片
$smarty->assign('yx4',           ad_info_wap(70) );           //防紫外线
$smarty->assign('yx5',           ad_info_wap(71) );           //色盲片
$smarty->assign('yx6',           ad_info_wap(72) );           //美容片

$smarty->assign('kj1',           ad_info_wap(73) );           //电脑眼镜
$smarty->assign('kj2',           ad_info_wap(74) );           //运动户外
$smarty->assign('kj3',           ad_info_wap(75) );           //偏光镜
$smarty->assign('kj4',           ad_info_wap(76) );           //老花镜
$smarty->assign('kj5',           ad_info_wap(77) );           //儿童太阳镜

$smarty->assign('zt1',           ad_info_wap(78) );           //透明隐形眼镜
$smarty->assign('zt2',           ad_info_wap(79) );           //彩色隐形眼镜
$smarty->assign('zt3',           ad_info_wap(80) );           //框架眼镜
$smarty->assign('zt4',           ad_info_wap(81) );           //太阳眼镜
$smarty->assign('zt5',           ad_info_wap(82) );           //护理液
$smarty->assign('zt6',           ad_info_wap(83) );           //润眼液
$smarty->assign('zt7',           ad_info_wap(84) );           //护理工具*/

//  线上的广告位置ID
$smarty->assign('big_ad',        ad_info_by_time(101,5) );           //5张焦点图
$smarty->assign('under_ad',      ad_info_by_time(103,1) );   //首焦下横幅
$smarty->assign('banner2',       ad_info(100,2) );           //中部焦点图（功能隐形眼镜上部）
$smarty->assign('banner3',       ad_info(99,2) );            //底部焦点图（品牌推荐上部）
$smarty->assign('ztbb',          ad_info(98,1) );            //主题街（大）
$smarty->assign('ztbs',          ad_info(97,1) );            //主题街（小）

$smarty->assign('hd1',           ad_info_wap(96) );           //新品抢先购
$smarty->assign('hd2',           ad_info_wap(95) );           //品牌特惠
$smarty->assign('hd3',           ad_info_wap(94) );           //周末狂欢场

$smarty->assign('yx1',           ad_info_wap(93) );           //散光定制
$smarty->assign('yx2',           ad_info_wap(92) );           //高度近视
$smarty->assign('yx3',           ad_info_wap(91) );           //远视片
$smarty->assign('yx4',           ad_info_wap(90) );           //防紫外线
$smarty->assign('yx5',           ad_info_wap(89) );           //色盲片
$smarty->assign('yx6',           ad_info_wap(88) );           //美容片

$smarty->assign('kj1',           ad_info_wap(87) );           //电脑眼镜
$smarty->assign('kj2',           ad_info_wap(86) );           //运动户外
$smarty->assign('kj3',           ad_info_wap(85) );           //偏光镜
$smarty->assign('kj4',           ad_info_wap(84) );           //老花镜
$smarty->assign('kj5',           ad_info_wap(83) );           //儿童太阳镜

$smarty->assign('zt1',           ad_info_wap(82) );           //透明隐形眼镜
$smarty->assign('zt2',           ad_info_wap(81) );           //彩色隐形眼镜
$smarty->assign('zt3',           ad_info_wap(80) );           //框架眼镜
$smarty->assign('zt4',           ad_info_wap(79) );           //太阳眼镜
$smarty->assign('zt5',           ad_info_wap(78) );           //护理液
$smarty->assign('zt6',           ad_info_wap(77) );           //润眼液
$smarty->assign('zt7',           ad_info_wap(76) );           //护理工具

// 获取自动轮播的图片信息
$smarty->assign('zt_img1',       get_zt_img(1) );           //透明隐形眼镜
$smarty->assign('zt_img2',       get_zt_img(6) );           //彩色隐形眼镜
$smarty->assign('zt_img3',       get_zt_img(159) );           //框架眼镜
$smarty->assign('zt_img4',       get_zt_img(190) );           //太阳眼镜
$smarty->assign('zt_img5',       get_zt_img(64) );           //护理液
$smarty->assign('zt_img6',       get_zt_img2(64) );           //润眼液
$smarty->assign('zt_img7',       get_zt_img2(76) );           //护理工具

// 3天包邮入口显示
$now = time();
if($now < strtotime('2015-12-14 00:00:00')){
    $main_mp_show = 1;
    $z_link = 'active151211.html';
}elseif($now > strtotime('2015-12-14 00:00:00') && $now < strtotime('2015-12-17 00:00:00')){ 
	$main_mp_show = 1;
    $z_link = 'active151214.html';
}else{
    $main_mp_show = false;
}
$smarty->assign('z_link', $z_link);
$smarty->assign('main_mp_show', $main_mp_show);

$act      = !empty($_GET['act']) ? trim($_GET['act']): "";
$page     = isset($_GET['page'])?intval($_GET['page']):1;
$perpage  = 8;
$offset=($page-1)*$perpage;
$hot_list = get_hot_goods($offset,$perpage);
$smarty->assign('hot_goods',     $hot_list);         //最热商品
$smarty->assign('m_page',        $page+1);         //最热商品
$smarty->assign('page_title', "首页 - 易视网手机版");

if($act == 'more'){
    if($page > 7){
        return false;
    }else{
        foreach($hot_list as $v):
            if($v['sales_tag'] != ""){
                $tag = '<span class="badge-pg badge-pg-goods">'.$v['sales_tag'].'</span>';
            }else{
                $tag = "";
            }
            if($v['saving'] > 0){
                $saving  = $v['promote_price'];
                $saving2 = 'active11_badge_2';
            }else{
                $saving  = "￥".$v['shop_price'];
                $saving2 = '';
            }
            echo '
            <div class="box">
                '.$tag.'
                <div class="image '.$saving2.'">
                    <a href="'.$v['url'].'"><img src="http://img.easeeyes.com/'.$v['original_img'].'" /></a>
                </div>
                <div class="text">
                    <h3><a href="'.$v['url'].'">'.$v['goods_name'].'</a></h3>
                    <span class="price">'.$saving.'</span> <del>￥'.$v['market_price'].'</del>
                </div>
            </div>
            ';
        endforeach;
    }
    die;
}

$smarty->display('index.dwt');

/*=========================================================================函数===============================================================================================*/
//yi:易视资讯文章cat_id=14 隐形眼镜知识文章cat_id=16 易视公告cat_id=12
function yi_get_article_info($cat_id, $start=0, $num=3){

    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
        ' FROM '.$GLOBALS['ecs']->table('article').' AS a, '.$GLOBALS['ecs']->table('article_cat').' AS ac'.
        ' WHERE a.is_open = 1 and a.is_hide=0 AND a.cat_id = ac.cat_id and ac.cat_id='.$cat_id.' AND ac.cat_type = 1' .
        ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT '.$start.','.$num.' ;' ;
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
        $arr[$idx]['title']       = $row['title'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
            sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date( $GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         = $row['open_type'] != 1 ? "article.php?act=detail&a_id=".$row['article_id'] : trim($row['file_url']);  //  zhang：150827  原url：build_uri('article', array('aid' => $row['article_id']), $row['title'])
        $arr[$idx]['cat_url']     = "article_cat.php?id=".$row['cat_id'];
        //  zhang：150827  原cat_url:$arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }
    return $arr;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:首页商品展示块获得最新商品列表
 * ----------------------------------------------------------------------------------------------------------------------
 * $size：商品数量。
 */
function get_new_goods($size = 4)
{
    $sql       = "select g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, IFNULL(mp.user_price, round(g.shop_price * '$_SESSION[discount]',2)) AS				  shop_price, g.promote_price, g.goods_type, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img from "
        .$GLOBALS['ecs']->table('goods')." as g left join ".$GLOBALS['ecs']->table('member_price').
        " as mp on mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'".
        " where g.is_new > 0 and g.is_on_sale = 1 and g.is_alone_sale = 1 and g.is_delete = 0 order by g.sort_order asc,g.goods_id desc limit 0,".$size.";";

    $new_goods = $GLOBALS['db']->GetAll($sql);
    foreach($new_goods as $k => $v)
    {
        //处理有特价的商品
        $promote_price = $new_goods[$k]['promote_price'];
        if( $promote_price > 0){
            $promote_price = bargain_price($promote_price, $new_goods[$k]['promote_start_date'], $new_goods[$k]['promote_end_date']);
        }else{
            $promote_price = 0;
        }
        $new_goods[$k]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';

        $new_goods[$k]['url'] = 'goods'.$new_goods[$k]['goods_id'].'.html';
    }
    return $new_goods;
}
//yi:降价给力榜商品
function get_show_jiangjia($size = 6)
{
    $sql = "select g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, IFNULL(mp.user_price, round(g.shop_price * '$_SESSION[discount]',2)) AS shop_price, g.promote_price, g.goods_type, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img from "
        .$GLOBALS['ecs']->table('goods')." as g left join ".$GLOBALS['ecs']->table('member_price')." as mp on mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' right join ecs_show_goods_cat as sg on g.goods_id = sg.goods_id ".
        " where sg.cat_id=1 and sg.is_show=1 and g.is_on_sale = 1 and g.is_alone_sale = 1 and g.is_delete = 0 order by sg.sort_order asc,g.goods_id desc limit 0,".$size.";";

    $goods = $GLOBALS['db']->GetAll($sql);
    foreach($goods as $k => $v ){
        $promote_price = $goods[$k]['promote_price'];
        if( $promote_price > 0){
            $promote_price = bargain_price($promote_price, $goods[$k]['promote_start_date'], $goods[$k]['promote_end_date']);
        }else{
            $promote_price = 0;
        }
        $goods[$k]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $goods[$k]['url'] = 'goods'.$goods[$k]['goods_id'].'.html';
    }
    return $goods;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:根据商品属性获取商品【归类显示】
 * ----------------------------------------------------------------------------------------------------------------------
 * catid:分类id----attstr:属性----size:商品数量--hot:是否热销。
 */
function category_get_eyeslist($catid, $attstr, $size, $hot)
{
    $children = get_children($catid);
    $display = isset($GLOBALS['display']) ? $GLOBALS['display'] : "";
    $strsql="";
    if(!$size) $size=4;

    if($attstr){

        $strsqld=" select gd.goods_id from ". $GLOBALS['ecs']->table('goods') ." as gd,". $GLOBALS['ecs']->table('goods_attr') ." as ad where ad.goods_id=gd.goods_id and ad.attr_value like '%".trim($attstr)."%'  group by ad.goods_id ";

        $resp = $GLOBALS['db']->selectLimit($strsqld, 40, 0);
        $k=0;
        while ($rowp = $GLOBALS['db']->fetchRow($resp))
        {
            if($k==0){$strsql=$rowp['goods_id'];}
            else{$strsql=$strsql.','.$rowp['goods_id'];}
            $k++;
        }
        if($strsql){$strsql="and g.goods_id in(".$strsql.")";}else{$strsql="and g.goods_id in(0)";}
    }

    if($hot){
        $strsql.=" and g.is_hot=1 ";
    }

    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
        "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, " .
        'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
        "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_best>0 AND $children $strsql ORDER BY sort_order asc,goods_id desc ";

    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $size, 0);
    if($catid==75)print_r($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0)
        {
            $watermark_img = "watermark_promote_small";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new_small";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best_small";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']         = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name']       = $row['goods_name'];
        }

        //商品属性---商品的具体参数
        $arr[$row['goods_id']]['name']             = $row['goods_name'];
        $arr[$row['goods_id']]['goods_brief']      = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);
        $arr[$row['goods_id']]['market_price']     = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']       = price_format($row['shop_price']);
        $arr[$row['goods_id']]['type']             = $row['goods_type'];
        $arr[$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    }
    return $arr;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:获取品牌列表数据信息
 * ----------------------------------------------------------------------------------------------------------------------
 * 参数：brand_id	brand_name	brand_logo	brand_desc	site_url sort_order	is_show
 */
function get_brand_info_list($num=20)
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('brand').' where is_show=1 ORDER BY sort_order limit 0,'.$num;
    $res = $GLOBALS['db']->getAll($sql);
    foreach($res as $k=>$v){
        // 处理品牌链接
        if(!empty($v['site_url'])){
            $arr1 = explode('_',$v['site_url']);
            if(isset($arr1[1])){
                $arr2 = explode('.',$arr1[1]);
                $res[$k]['site_url'] = "category.php?cat_id=" . $arr2[0];
            }else{
                $res[$k]['site_url'] = "";
            }
        }else{
            $res[$k]['site_url'] = "";
        }
    }
    //var_dump($res);eixt();
    return $res;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 zhang:获取最热商品
 * ----------------------------------------------------------------------------------------------------------------------
 * 参数：offset：偏移量，perpage：每页显示数
 */
function get_hot_goods($offset,$perpage)
{
    $sql = "select g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, IFNULL(mp.user_price, round(g.shop_price * '$_SESSION[discount]',2)) AS shop_price, g.promote_price, g.goods_type, g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img,g.original_img, g.sales_tag from "
        .$GLOBALS['ecs']->table('goods')." as g left join ".$GLOBALS['ecs']->table('member_price').
        " as mp on mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'".
        " where g.is_hot > 0 and g.is_on_sale = 1 and g.is_alone_sale = 1 and g.is_delete = 0 order by g.sort_order asc,g.goods_id desc limit ".$offset.",".$perpage.";";

    $new_goods = $GLOBALS['db']->GetAll($sql);
    foreach($new_goods as $k => $v)
    {
        //处理有特价的商品
        $promote_price = $new_goods[$k]['promote_price'];
        if( $promote_price > 0){
            $promote_price = bargain_price($promote_price, $new_goods[$k]['promote_start_date'], $new_goods[$k]['promote_end_date']);
        }else{
            $promote_price = 0;
        }
        if($v['promote_price'] > 0 && $v['promote_end_date'] > time() && $v['promote_start_date'] < time()){
            $new_goods[$k]['saving'] = $v['shop_price'] - $v['promote_price'];
        }else{
            $new_goods[$k]['saving'] = '';
        }
        $new_goods[$k]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';

        $new_goods[$k]['url'] = 'goods.php?id='.$new_goods[$k]['goods_id'];
    }
    return $new_goods;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 zhang:获取最热商品总数
 * ----------------------------------------------------------------------------------------------------------------------
 * 参数：size：商品数量
 */
function get_hot_goods_num($ext=''){
    $sql = 'SELECT count(*) as num FROM '.$GLOBALS['ecs']->table('goods').' where is_hot > 0, and is_on_sale = 1 '.$ext;
    return $GLOBALS['db']->getOne($sql);
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 zhang:获取秒杀商品
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_ms_goods()
{
    $ctime = time();
    $sql = "SELECT * FROM ecs_miaosha WHERE status=0 AND is_show_wap = 1 AND end_time > ".$ctime." ORDER BY start_time ASC LIMIT 1";

    $ms = $GLOBALS['db']->GetRow($sql);
    //var_dump($ms);exit();
    if ($ms){
        //格式化价格
        if ($ms['price']) {
			// zhang：150330  修改首页秒杀价格显示数据格式
			$ms['price_int'] = number_format($ms['price'],1);
            /* $format_cprice = explode('.', $ms['price']);
            $ms['price_int'] = $format_cprice[0];		//整数部分
            $ms['price_decimal'] = $format_cprice[1];	//小数部分 */
        }

        //市场价
        $ms['market_price'] = $GLOBALS['db']->GetOne("SELECT market_price FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
        if ( ! $ms['market_price'] OR $ms['market_price'] <= 0.00) {
            $ms['market_price'] = $GLOBALS['db']->GetOne("SELECT shop_price FROM ecs_goods WHERE goods_id=".$ms['goods_id']." LIMIT 1");
        }
        $ms['market_price'] = sprintf("%01.2f", $ms['market_price'] * $ms['ms_number']);

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
            $ms['djs_time']    = date('Y/m/d H:i', $ms['end_time']);
        } else {
            //秒杀未开始,格式化开始时间
            $format_ctime['time_type'] = '开始';
            $format_ctime['Y'] = date('Y', $ms['start_time']);
            $format_ctime['n'] = date('n', $ms['start_time']);
            $format_ctime['j'] = date('j', $ms['start_time']);
            $format_ctime['G'] = date('G', $ms['start_time']);
            $format_ctime['i'] = date('i', $ms['start_time']);
            $ms['djs_time']    = date('Y/m/d H:i', $ms['start_time']);
        }

        //秒杀状态标识：0:未开始  1:进行中	2:已结束
        if ($ms['start_time'] > $ctime) {
            $ms['ms_status'] = 0;
        } elseif ($ms['start_time'] <= $ctime && $ms['end_time'] >= $ctime) {
            $ms['ms_status'] = 1;
        } else {
            $ms['ms_status'] = 2;
        }

    }else{
        $ms = $GLOBALS['db']->GetRow("SELECT * FROM ecs_miaosha WHERE status=0 AND is_show_wap = 1 ORDER BY end_time DESC LIMIT 1");
        if ($ms['price']) {
            $format_cprice = explode('.', $ms['price']);
            $ms['price_int'] = $format_cprice[0];		//整数部分
            $ms['price_decimal'] = $format_cprice[1];	//小数部分
        }
        //秒杀已结束,格式化截止时间
        $format_ctime['time_type'] = '结束';
        $format_ctime['Y'] = date('Y', $ms['end_time']);
        $format_ctime['n'] = date('n', $ms['end_time']);
        $format_ctime['j'] = date('j', $ms['end_time']);
        $format_ctime['G'] = date('G', $ms['end_time']);
        $format_ctime['i'] = date('i', $ms['end_time']);
        $ms['djs_time']    = date('Y/m/d H:i', $ms['end_time']);
    }
    return $ms;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 zhang:获取团购商品
 * ----------------------------------------------------------------------------------------------------------------------
 */
function get_tg_goods()
{
    $ctime = time();
    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('tuan')." WHERE start_time<".$ctime." AND end_time>".$ctime." AND is_show_wap=1 ORDER BY `is_promotion_wap` desc,`start_time` asc,`end_time` asc LIMIT 0, 2";
    $res = $GLOBALS['db']->query($sql);
    $tuan_list = array();
    //echo $sql;die;
    while($row = $GLOBALS['db']->fetchRow($res))
    {
        //还原序列化信息,读取字段中的礼包价和市场价
        /*$ext_arr = unserialize($row['ext_info']);
        unset($row['ext_info']);
        if($ext_arr){
            foreach ($ext_arr as $key=>$val){$row[$key] = $val; }
        }*/
        $row['market_price']	= sprintf("%01.2f", get_package_market_price($row['rec_id']));			//市场价
        $row['tuan_price']		= sprintf("%01.2f", $row['tuan_price']); 									//团购价
        if ($row['market_price'] && $row['market_price'] > 0.00)
        {
            $row['zhekou']		= sprintf("%01.1f", ($row['tuan_price'] / $row['market_price']) * 10);	//折扣
            $row['saving']      = sprintf("%01.2f", $row['market_price'] - $row['tuan_price']); // 节省的价格
        }
        // 主推期间价格处理
        if($row['is_promotion'] == 1 && $row['promotion_start_time'] < $ctime && $row['promotion_end_time'] > $ctime){
            $row['zhekou']		= sprintf("%01.1f", ($row['promotion_price'] / $row['market_price']) * 10);	//折扣
            $row['saving']      = sprintf("%01.2f", $row['market_price'] - $row['promotion_price']); // 节省的价格
            $row['tuan_price']	= sprintf("%01.2f", $row['promotion_price']); 						//团购价
        }
        //处理团购价,用于前台显示
        $temp_tuan_price = explode('.', $row['tuan_price']);
        $row['tuan_price_1'] = $temp_tuan_price[0];	//整数部分
        $row['tuan_price_2'] = $temp_tuan_price[1];	//小数部分
        //var_dump($row);
        $tuan_list[] = $row;
    }
    return $tuan_list;

}
//计算礼包的市场价(各商品市场价累加)
function get_package_market_price($tuan_id=0) {
    if (intval($tuan_id) > 0) {
        $tuan_market_price = $GLOBALS['db']->GetOne("SELECT SUM(a.market_price*b.goods_number) AS package_market_price FROM ecs_goods a
									LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id WHERE b.tuan_id = $tuan_id");
        if ($tuan_market_price)
        {
            return $tuan_market_price;
        }
        else
        {
            return $GLOBALS['db']->GetOne("SELECT SUM(a.shop_price*b.goods_number) AS package_market_price FROM ecs_goods a
									LEFT JOIN ecs_tuan_goods b ON a.goods_id=b.goods_id WHERE b.tuan_id = $tuan_id");
        }
    }
}
//后台添加页面图片
//pid:图片的位置id ----size:图片张数------
function ad_info_wap($pid)
{
    $sql =  'SELECT ad_id,ad_code,ad_link,ad_name FROM ' . $GLOBALS['ecs']->table('ad') . ' '.
        ' WHERE enabled=1 and position_id='.$pid.' ' ;
    $res = $GLOBALS['db']->getRow($sql);
    return $res;
}
// 自动获取主题街下发7大板块商品图片
function get_zt_img($catid){
	$children = get_children($catid);
	$lim = date('w',time());
	$sql = "SELECT g.goods_thumb as goods_img FROM " . $GLOBALS['ecs']->table('goods') . " AS g " . 
			"WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_best>0 AND $children ORDER BY sort_order asc,goods_id desc LIMIT " . 
			$lim . ",1";
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}
function get_zt_img2($catid){
	$children = get_children($catid);
	$lim = date('w',time());
	if($lim > 3){
		$lim = $lim % 4;
	}
	if($catid == 64){
		$lim = $lim + 7;
	}
	$sql = "SELECT g.goods_thumb as goods_img FROM " . $GLOBALS['ecs']->table('goods') . " AS g " . 
			"WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_best>0 AND $children ORDER BY sort_order asc,goods_id desc LIMIT " . 
			$lim . ",1";
	$res = $GLOBALS['db']->getRow($sql);
	return $res;
}

?>