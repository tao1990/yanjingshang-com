<?php
/* =======================================================================================================================
 * 商城页面 品牌分类 彩色片 透明片 护理液 护理工具栏目页面 2011-4-1【Author:yijiangwen】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$act = isset($_GET['act'])? $_GET['act']:'';
$sort = isset($_GET['sort'])? $_GET['sort']:'';
$cat_id= !empty($_GET['cat_id']) ? addslashes(intval($_GET['cat_id'])):0;
$page=isset($_GET['page'])?intval($_GET['page']):1;
$st =empty($_GET['st'])? 0:1;
$keyword = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';

//------------------------------------保存搜索记录 zhang:151023 start--------------------------------//
if(!empty($_COOKIE['search_history']))
{
    $history = explode(',', $_COOKIE['search_history']);

    array_unshift($history, $keyword);
    $history = array_unique($history);
    while(count($history) > 6)
    {
        array_pop($history);
    }
    setcookie("search_history", implode(',', $history), gmtime() + 3600 * 24 * 30);
    $smarty->assign('search_history', $history);
}
else
{
    setcookie('search_history', $keyword, gmtime() + 3600 * 24 * 30);
    $smarty->assign('search_history', $keyword);
}
//------------------------------------保存搜索记录 zhang:151023 end--------------------------------//

$perpage = 6;

if(empty($sort)){
    $ext = '';                              //扩展条件
    $catt = in_array($cat_id,array(1,6,12,64,76,159,190));   // 判断cat_id是不是父分类
    if($catt == true){
        //根据cat_id 找出所有该父分类的分类id
        $sqlc   = "select cat_id from ".$GLOBALS['ecs']->table('category')." where parent_id = ".$cat_id." and is_show = 1;";
        $catid = $GLOBALS['db']->GetAll($sqlc);

        $in = "(0";
        foreach($catid as $k => $v){
            if(!empty($catid[$k]['cat_id'])){
                $in .= ",".$catid[$k]['cat_id'];
            }
        }
        $in .= ")";
        // 父分类下的产品搜索条件
        $ext .= ' and cat_id in '.$in;
    }elseif($cat_id!=0 && $catt == false){
        $ext.= ' and cat_id = '.$cat_id; 
    }
    if(!empty($keyword)){
        $ext.= ' and goods_name like "%'.$keyword.'%" '; 
    }
    
    $num =category_get_goods_num_wap($ext);//获取商品列表数目
    
    $offset=($page-1)*$perpage; 
    $goodslist =category_get_goods_wap($ext,$offset,$perpage);//获取商品列表
}else{
    $ext = '';                              //扩展条件
    $catt = in_array($cat_id,array(1,6,12,64,76,159,190));   // 判断cat_id是不是父分类
    if($catt == true){
        //根据cat_id 找出所有该父分类的分类id
        $sqlc   = "select cat_id from ".$GLOBALS['ecs']->table('category')." where parent_id = ".$cat_id." and is_show = 1;";
        $catid = $GLOBALS['db']->GetAll($sqlc);

        $in = "(0";
        foreach($catid as $k => $v){
            if(!empty($catid[$k]['cat_id'])){
                $in .= ",".$catid[$k]['cat_id'];
            }
        }
        $in .= ")";
        // 父分类下的产品搜索条件
        $ext .= ' and cat_id in '.$in;
    }elseif($cat_id!=0 && $catt == false){
        $ext.= ' and cat_id = '.$cat_id;
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
                    <img src="http://www.easeeyes.com/'.$v['goods_thumb'].'" />
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
                <div '.$saving2.'><a href="goods.php?id='.$v['goods_id'].'"><img src="http://www.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
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
//var_dump($goodslist);
if($act=='more'){
    if($st!=1){
        foreach($goodslist as $v){
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
                        <img src="http://www.easeeyes.com/'.$v['goods_thumb'].'" />
                      </div>
                      <div class="goods_list_main">
                        <h2>'.$v['goods_name'].'</h2>
                        <div class="pg-tags-list">'.$act.'</div>
                        <div class="goods_list_price">
                            ￥'.$v['shop_price'].$saving.'
                        </div>
                        <p>已有 '.$v['click_count'].' 人关注</p>
                      </div>
                    </a>
                </li>
                ';
        }
    }else{
        foreach($goodslist as $v){
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
                    <div '.$saving2.'><a href="goods.php?id='.$v['goods_id'].'"><img src="http://www.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
                    <div><a href="goods.php?id='.$v['goods_id'].'">'.$v['goods_name'].'</a></div>
                    <div class="pg-tags-list">'.$act.'</div>
                    <div class="goods_list_price">
                        ￥ '.$v['shop_price'].$saving.'
                    </div>
                    <p>已有 '.$v['click_count'].' 人关注</p>
                    <div class="active"></div>
                </div>
            ';
        }
    }
        
    die;
}

if($act==''){//商品
    $smarty->assign('cat_id',   $cat_id);
    $smarty->assign('goods_list',   $goodslist);
    $smarty->assign('st',   $st);
    $smarty->assign('keyword', $keyword);
    $smarty->assign('ur_here', "产品分类");
    $smarty->assign('page_title', "产品分类 - 易视网手机版");
	$smarty->display('category2.dwt');
}
//=====================================================================【函数】===============================================================================//



function category_get_goods_wap($ext='',$offect=0,$perpage=10){
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('goods').' where is_on_sale = 1 and cat_id != 138 and shop_price > 0 '.$ext.' limit '.$offect.','.$perpage;
	$arr = $GLOBALS['db']->getAll($sql);
    foreach($arr as $k=>$v){
        if($v['promote_price'] > 0 && $v['promote_end_date'] > time()){
            $arr[$k]['saving'] = $v['shop_price'] - $v['promote_price'];
        }else{
            $arr[$k]['saving'] = '';
        }
        $act_type = include_goods_fav($v['goods_id']);
        //var_dump($act_type);
        foreach($act_type as $ak=>$av){
            $arr[$k]['active'][$ak] = $av['act_type'];
            //var_dump($av);
        }
        @$arr[$k]['active'] = array_unique($arr[$k]['active']);
    }
    return $arr;
}

function category_get_goods_num_wap($ext=''){
    $sql = 'SELECT count(*) as num FROM '.$GLOBALS['ecs']->table('goods').' where is_on_sale = 1 and cat_id != 138 and shop_price > 0 '.$ext;
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

?>