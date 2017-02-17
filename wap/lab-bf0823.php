<?php
/* =======================================================================================================================
 * 商城页面 品牌分类 彩色片 透明片 护理液 护理工具栏目页面 2011-4-1【Author:yijiangwen】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$act = isset($_GET['act'])? $_GET['act']:'';
$sort = isset($_GET['sort'])? $_GET['sort']:'';
//$cat_id= !empty($_GET['cat_id']) ? addslashes(intval($_GET['cat_id'])):0;
$page=isset($_GET['page'])?intval($_GET['page']):1; 
$st =empty($_GET['st'])? 0:1; 
$keyword = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
$lab_id = isset($_REQUEST['lab_id']) ? intval($_REQUEST['lab_id']) : 0;
$perpage = 6;
$smarty->assign('ad_hot_list',          ad_info(51,1));//焦点图片广告
if($lab_id == 0)
{
    ecs_header("Location: ./\n"); exit;
}
//标签归属商品分类，和热销排行商品
$fcat = get_lab_list_cat($lab_id);
switch($fcat)
{
    case 1:
        $smarty->assign('cat2', $fcat);
        $smarty->assign('sale_order',  sale_sort_list(1));
        break;
    case 6:
        $smarty->assign('cat3', $fcat);
        $smarty->assign('sale_order',  sale_sort_list(6));
        break;
    case 64:
        $smarty->assign('cat4', $fcat);
        $smarty->assign('sale_order',  sale_sort_list(64));
        break;
    case 76:
        $smarty->assign('cat5', $fcat);
        $smarty->assign('sale_order',  sale_sort_list(76));
        break;
    case 159:
        $smarty->assign('cat6', $fcat);
        $smarty->assign('sale_order',  sale_sort_list(159));
        break;
    case 190:
        $smarty->assign('cat7', $fcat);	$smarty->assign('sale_order',  sale_sort_list(190)); break;
    default:
        $lab_cat_arr = array(62,63,64,65,66);
        if(in_array($lab_id, $lab_cat_arr))
        {
            $smarty->assign('cat6', 159);
            $smarty->assign('sale_order',  sale_sort_list(159));
            $smarty->assign('gn_cat', true);
        }
        else
        {
            $smarty->assign('cat3', 6);
            $smarty->assign('sale_order',  sale_sort_list(6));
        }
        break;
}

if(in_array($lab_id, array(30, 31, 32, 34, 35, 43)))
{
    $smarty->assign('lab_img', true);
    $smarty->assign('lab_id',  $lab_id);
}
if(empty($sort)){
    $ext = '';                              //扩展条件
    if($lab_id != 0) {
        //根据cat_id 找出所有该父分类的分类id
        $lab_goods = $GLOBALS['db']->GetOne("select lab_goods from " . $GLOBALS['ecs']->table('lab') . " where is_show=1 AND lab_id=" . $lab_id . " limit 1;");
        $lab_goods = empty($lab_goods) ? '0' : trim($lab_goods);
        $in = "(" . $lab_goods . ")";
        // 父分类下的产品搜索条件
        $ext .= ' and goods_id in ' . $in;
    }
    if(!empty($keyword)){
        $ext.= ' and goods_name like "%'.$keyword.'%" '; 
    }
    
    $num =category_get_goods_num_wap($ext);//获取商品列表数目
    
    $offset=($page-1)*$perpage; 
    $goodslist =category_get_goods_wap($ext,$offset,$perpage);//获取商品列表
}else{
    $ext = '';                              //扩展条件
    if($lab_id != 0) {
        //根据cat_id 找出所有该父分类的分类id
        $lab_goods = $GLOBALS['db']->GetOne("select lab_goods from " . $GLOBALS['ecs']->table('lab') . " where is_show=1 AND lab_id=" . $lab_id . " limit 1;");
        $lab_goods = empty($lab_goods) ? '0' : trim($lab_goods);
        $in = "(" . $lab_goods . ")";
        // 父分类下的产品搜索条件
        $ext .= ' and goods_id in ' . $in;
    }
    if(!empty($keyword)){
        $ext.= ' and goods_name like "%'.$keyword.'%" '; 
    }
    $sortExt = get_sort_ext($sort);
    
    $ext.= $sortExt; //扩展条件
    $num =category_get_goods_num_wap($ext);//获取商品列表数目
    $offset=($page-1)*$perpage; 
    $goodslist =category_get_goods_wap($ext,$offset,$perpage);//获取商品列表
    $sortlist = '';
    if($st!=1){
    foreach($goodslist as $v):
        $act = '';
        is_array($v['active'])?null:$v['active'] = array();
        foreach($v['active'] as $val){
            if($val['act_type'] == 0){    // 赠品
                $act .= '<span class="pg-tags">赠</span>';
            }
            if($val['act_type'] == 1){    // 立减
                $act .= '<span class="pg-tags pg-tags-orange">减</span>';
            }
            if($val['act_type'] == 3){    // 加价购
                $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
            }
        }
        if($v['saving'] > 0){
            $saving  = '<span class="pg-tags pg-tags-red">已优惠'.$v['saving'].'元</span>';
            $saving2 = 'class="active11_badge"';
        }else{
            $saving  ='';
            $saving2 ='';
        }
        $sortlist.= '
            <li '.$saving2.'>
                <a class="list_a" href="goods.php?id='.$v['goods_id'].'">
                  <div class="goods_list_thumb pull-left">
                    <img src="http://img.easeeyes.com/'.$v['goods_thumb'].'" />
                  </div>
                  <div class="goods_list_main">
                    <h2>'.$v['goods_name'].'</h2>
                    <div class="pg-tags-list">
                        '.$act.'
                    </div>
                    <div class="goods_list_price">
                        ￥'.$v['shop_price'].$saving.'
                    </div>
                    <p>已有 '.$v['click_count'].' 人关注</p>
                  </div>
                </a>
            </li>
            ';
    endforeach;
    }else{
    foreach($goodslist as $v):
        $act = '';
        is_array($v['active'])?null:$v['active'] = array();
        foreach($v['active'] as $val){
            if($val['act_type'] == 0){    // 赠品
                $act .= '<span class="pg-tags">赠</span>';
            }
            if($val['act_type'] == 1){    // 立减
                $act .= '<span class="pg-tags pg-tags-orange">减</span>';
            }
            if($val['act_type'] == 3){    // 加价购
                $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
            }
        }
        if($v['saving'] > 0){
            $saving  = '<span class="pg-tags pg-tags-red">已优惠'.$v['saving'].'元</span>';
            $saving2 = 'class="active11_badge_2"';
        }else{
            $saving  ='';
            $saving2 ='';
        }
        $sortlist.= '
            <div class="list_zong_left">
                <div '.$saving2.'><a href="goods.php?id='.$v['goods_id'].'"><img src="http://img.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
                <div><a href="goods.php?id='.$v['goods_id'].'">'.$v['goods_name'].'</a></div>
                <div class="pg-tags-list">'.$act.'</div>
                <div class="goods_list_price">
                    ￥ '.$v['shop_price'].$saving.'
                </div>
                <p>已有 '.$v['click_count'].' 人关注</p>
                <div class="active"></div>
            </div>
        ';
    endforeach;    
    }
    echo $sortlist;die;
}




if($act=='more'){
    if($st!=1){
        foreach($goodslist as $v):
            $act = '';
            is_array($v['active'])?null:$v['active'] = array();
            foreach($v['active'] as $val){
                if($val['act_type'] == 0){    // 赠品
                    $act .= '<span class="pg-tags">赠</span>';
                }
                if($val['act_type'] == 1){    // 立减
                    $act .= '<span class="pg-tags pg-tags-orange">减</span>';
                }
                if($val['act_type'] == 3){    // 加价购
                    $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
                }
            }
            if($v['saving'] > 0){
                $saving  = '<span class="pg-tags pg-tags-red">已优惠'.$v['saving'].'元</span>';
                $saving2 = 'class="active11_badge"';
            }else{
                $saving  ='';
                $saving2 ='';
            }
            echo '
            <li '.$saving2.'>
                <a class="list_a" href="goods.php?id='.$v['goods_id'].'">
                  <div class="goods_list_thumb pull-left">
                    <img src="http://img.easeeyes.com/'.$v['goods_thumb'].'" />
                  </div>
                  <div class="goods_list_main">
                    <h2>'.$v['goods_name'].'</h2>
                    <div class="pg-tags-list">
                        '.$act.'
                    </div>
                    <div class="goods_list_price">
                        ￥'.$v['shop_price'].$saving.'
                    </div>
                    <p>已有 '.$v['click_count'].' 人关注</p>
                  </div>
                </a>
            </li>
            ';
        endforeach;
    }else{
        foreach($goodslist as $v):
            $act = '';
            is_array($v['active'])?null:$v['active'] = array();
            foreach($v['active'] as $val){
                if($val['act_type'] == 0){    // 赠品
                    $act .= '<span class="pg-tags">赠</span>';
                }
                if($val['act_type'] == 1){    // 立减
                    $act .= '<span class="pg-tags pg-tags-orange">减</span>';
                }
                if($val['act_type'] == 3){    // 加价购
                    $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
                }
            }
            if($v['saving'] > 0){
                $saving  = '<span class="pg-tags pg-tags-red">已优惠'.$v['saving'].'元</span>';
                $saving2 = 'class="active11_badge_2"';
            }else{
                $saving  ='';
                $saving2 ='';
            }
            echo '
            <div class="list_zong_left">
                <div '.$saving2.'><a href="goods.php?id='.$v['goods_id'].'"><img src="http://img.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
                <div><a href="goods.php?id='.$v['goods_id'].'">'.$v['goods_name'].'</a></div>
                <div class="pg-tags-list">'.$act.'</div>
                <div class="goods_list_price">
                    ￥ '.$v['shop_price'].$saving.'
                </div>
                <p>已有 '.$v['click_count'].' 人关注</p>
                <div class="active"></div>
            </div>
        ';
        endforeach;    
        }
        
    die;
    }

if($act==''){//商品
    $smarty->assign('lab_id',   $lab_id);
    $smarty->assign('goods_list',   $goodslist);
    $smarty->assign('st',   $st);
    $smarty->assign('keyword', $keyword);
    $smarty->assign('ur_here', "分类列表");
    $smarty->assign('page_title', "分类列表 - 易视网手机版");
    $lab_arr = array(56, 57, 58, 59, 60, 67, 68, 70, 85, 86, 87,88,89,90,91,93,94,95,96,97,99,100,101,102,104,105,106,109,107,108,110,111,113,112,114,115,116,125,126,127,128,129,130,131,132,133,124,154,155,157,158);//有大广告活动图片的标签页
    if(in_array($lab_id, $lab_arr))
    {
        $smarty->assign('lab_at_id',     $lab_id);
        $smarty->display('lab_active.dwt');
    }
    else
    {
        $smarty->display('lab.dwt'); //普通标签，与热门系列共用模板。
    }
}
	
//=====================================================================【函数】===============================================================================//



function category_get_goods_wap($ext='',$offect=0,$perpage=10){
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('goods').' where is_on_sale = 1 '.$ext.' limit '.$offect.','.$perpage;
    $arr = $GLOBALS['db']->getAll($sql);
    foreach($arr as $k=>$v){
        if($v['promote_price'] > 0 && $v['promote_end_date'] > time() && $v['promote_start_date'] < time()){
            $arr[$k]['saving'] = $v['shop_price'] - $v['promote_price'];
            $arr[$k]['shop_price'] = $v['promote_price'];
        }else{
            $arr[$k]['saving'] = '';
        }
        $act_type = include_goods_fav($v['goods_id']);
        foreach($act_type as $ak=>$av){
            $arr[$k]['active'][$ak] = $av['act_type'];
        }
        @$arr[$k]['active'] = array_unique($arr[$k]['active']);
    }
    return $arr;
}

function category_get_goods_num_wap($ext=''){
    $sql = 'SELECT count(*) as num FROM '.$GLOBALS['ecs']->table('goods').' where is_on_sale = 1 '.$ext;
	return $GLOBALS['db']->getOne($sql);
}

function get_sort_ext($sort){
    switch($sort){
        case '1'://默认排序
            $sortExt = ' order by goods_id asc';
        break;
        case '2'://销量从高到低
            $sortExt = ' order by click_count desc';
        break;
        case '3'://销量从低到高
            $sortExt = ' order by click_count asc';
        break;
        case '4'://价格从低到高
            $sortExt = ' order by shop_price asc';
        break;
        case '5'://价格从高到低
            $sortExt = ' order by shop_price desc';
        break;
        default:
            $sortExt = ' order by goods_id asc';
    }
    return $sortExt;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * yi:包含该商品的(全部或指定类别)优惠活动
 * ----------------------------------------------------------------------------------------------------------------------
 */
function include_goods_fav($goods_id=0, $act_type=-1)
{
    $now = $_SERVER['REQUEST_TIME'];
    $tsql= ($act_type==-1)? "": " and act_type=".$act_type;
    $sql = "select gift,act_range,act_range_ext,act_type from ".$GLOBALS['ecs']->table('favourable_activity')." where `start_time`<='$now' AND `end_time`>='$now' ".$tsql." ORDER BY `start_time` desc,`end_time` desc";
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
//yi:根据标签id找出它属于商品板块
function get_lab_list_cat($lab_id){
    $sql = "select f.cat_belong from ".$GLOBALS['ecs']->table('lab')." as l,".$GLOBALS['ecs']->table('lab_cat')." as f where l.lab_cat=f.cat_id AND lab_id=".$lab_id.";";
    return $GLOBALS['db']->GetOne($sql);
}
//yi:商城所有商品的分类销售排行列表（可以根据分类 算出分类的销售排行） 最近一周(7天)销售排行榜
//fcat_id：父分类id。 num：排行商品个数。
function sale_sort_list( $fcat_id, $num = 5){

    //根据商品fcat_id 找出所有该父分类的分类id
    $sqlc   = "select cat_id from ".$GLOBALS['ecs']->table('category')." where parent_id = ".$fcat_id." and is_show = 1;";
    $cat_id = $GLOBALS['db']->GetAll($sqlc);

    $in = "(0";
    foreach($cat_id as $k => $v){
        if(!empty($cat_id[$k]['cat_id'])){
            $in .= ",".$cat_id[$k]['cat_id'];
        }
    }
    $in .= ")";

    //获取商城销售排行列表 耗时：0.0007秒
    $sql  = "select * from ecs_sales_charts where cat_id in".$in." limit 0,".$num.";";
    $sale = $GLOBALS['db']->GetAll($sql);
    return $sale;
}

?>