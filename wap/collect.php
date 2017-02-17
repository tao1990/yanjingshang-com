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

$perpage = 6;

if(empty($sort)){
    $ext = '';                              //扩展条件
    if($cat_id!=0){
        
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
    if($cat_id!=0){
        
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
        $sortlist.= '
            <li>
                <a href="goods.php?id='.$v['goods_id'].'">
                    <img src="http://www.easeeyes.com/'.$v['goods_thumb'].'" />
                    <span class="sp01">'.$v['goods_name'].'</span>
                    <span class="sp02"><em class="price1">￥'.$v['shop_price'].'</em><em class="price2">￥'.$v['market_price'].'</em></span>
                    <span class="sp03">'.$v['click_count'].'人购买</span>
                    <span class="sp04">XXXXXX</span>
                </a>
            </li>
            ';
    endforeach;
    }else{
    foreach($goodslist as $v):
        $sortlist.= '
            <div class="list_zong_left">
              <div><a href="goods.php?id='.$v['goods_id'].'"><img src="http://www.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
              <div><a href="goods.php?id='.$v['goods_id'].'">'.$v['goods_name'].'</a></div>
              <div class="list_price">
                 <span class="xian_price">&yen;'.$v['shop_price'].'</span><span class="yuan_price">&yen;'.$v['market_price'].'</span>
                 <div class="clear"></div>
              </div>
              <div>已有'.$v['click_count'].'人购买</div>
              <div class="active">xxx</div>
           </div>
        ';
    endforeach;    
    }
    echo $sortlist;die;
}




if($act=='more'){
    if($st!=1){
        foreach($goodslist as $v):
        echo '
            <li>
                <a href="goods.php?id='.$v['goods_id'].'">
                    <img src="http://www.easeeyes.com/'.$v['goods_thumb'].'" />
                    <span class="sp01">'.$v['goods_name'].'</span>
                    <span class="sp02"><em class="price1">￥'.$v['shop_price'].'</em><em class="price2">￥'.$v['market_price'].'</em></span>
                    <span class="sp03">'.$v['click_count'].'人购买</span>
                    <span class="sp04">XXXXXX</span>
                </a>
            </li>
            ';
        endforeach;
    }else{
        foreach($goodslist as $v):
            echo '
                <div class="list_zong_left">
                  <div><a href="goods.php?id='.$v['goods_id'].'"><img src="http://www.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
                  <div><a href="goods.php?id='.$v['goods_id'].'">'.$v['goods_name'].'</a></div>
                  <div class="list_price">
                     <span class="xian_price">&yen;'.$v['shop_price'].'</span><span class="yuan_price">&yen;'.$v['market_price'].'</span>
                     <div class="clear"></div>
                  </div>
                  <div>已有'.$v['click_count'].'人购买</div>
                  <div class="active">xxx</div>
               </div>
            ';
        endforeach;    
        }
        
    die;
    }

if($act==''){//商品
    $smarty->assign('cat_id',   $cat_id);
    $smarty->assign('goods_list',   $goodslist);
    $smarty->assign('st',   $st);
    $smarty->assign('keyword', $keyword);
	$smarty->display('category2.dwt');
}
	
//=====================================================================【函数】===============================================================================//



function category_get_goods_wap($ext='',$offect=0,$perpage=10){
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('goods').' where is_on_sale = 1 '.$ext.' limit '.$offect.','.$perpage;
	return $GLOBALS['db']->getAll($sql);
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
?>