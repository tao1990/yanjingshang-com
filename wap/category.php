<?php
/* =======================================================================================================================
 * 商城页面 品牌分类 彩色片 透明片 护理液 护理工具栏目页面 2011-4-1【Author:yijiangwen】
 * =======================================================================================================================
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
//屏蔽错误
ini_set("display_errors", "Off");
error_reporting(0); 
$cat_ids =0;//分类id
$fcat_ids=0;//父类id
$act = isset($_GET['act'])? $_GET['act']:'';
$sort = isset($_GET['sort'])? $_GET['sort']:'';
$filter = isset($_GET['attr'])? $_GET['attr']:'';
$cat_id= !empty($_GET['cat_id']) ? addslashes(intval($_GET['cat_id'])):0;
$page=isset($_GET['page'])?intval($_GET['page']):1;
$st =empty($_GET['st'])? 0:1;
$keyword = isset($_GET['keyword'])?addslashes($_GET['keyword']):'';
$fil = str_replace('\\', '', $filter);
$attr = json_decode($fil,true);
if(!empty($attr)){
    $goodsids = "";
    foreach($attr as $k=>$v){
        if($k == '213' || $k == '221'){
            if($v == '37%以下(低含水量)'){
                $goodsids .= get_goods_id_from_attr(1,'hsl');
            }elseif($v == '38%～49%(中含水量)'){
                $goodsids .= get_goods_id_from_attr(2,'hsl');
            }elseif($v == '50%～58%(高含水量)'){
                $goodsids .= get_goods_id_from_attr(3,'hsl');
            }elseif($v == '59%以上(超高含水量)'){
                $goodsids .= get_goods_id_from_attr(4,'hsl');
            }else{
                $goodsids .= "";
            }
        }elseif($k == 'brand'){
            if(empty($v)){
                $goodsids .= "";
            }else{
                $goodsids .= " AND cat_id = ".$v;
            }
        }elseif($k == 'price'){
            if($v == ""){
                $ga = "";
            }else{
                $ga = explode(",",$v);
            }
            if(count($ga) > 1){
                $goodsids .= " AND shop_price > ".$ga[0]." AND shop_price < ".$ga[1];
            }elseif(isset($ga[0])){
                $goodsids .= " AND shop_price > ".$ga[0];
            }else{
                $goodsids .="";
            }
        }else{
            if(empty($v)){
                $goodsids .= "";
            }else{
                $goodsids .= get_goods_id_from_attr($v);
            }
        }
    }
}
$smarty->assign('filter',$fil);
//var_dump($attr);die;

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

// ============================================================筛选开始==============================================================//

$cat_ids = $cat_id;
if($cat_ids == 138)
{
    ecs_header("Location: ./\n"); exit;
}
$fcat_arr = array(1,6,12,64,76,159,190);
if(in_array($cat_ids, $fcat_arr))
{
    $fcat_ids = $cat_ids;
}
else
{
    $fcat_ids = $GLOBALS['db']->getOne("SELECT parent_id FROM ".$GLOBALS['ecs']->table('category')." WHERE cat_id=".$cat_ids." limit 1;");
}

$smarty->assign('cat_ids',  $cat_ids);
$smarty->assign('fcat_ids', $fcat_ids);
/*------------------------------------------------------品牌筛选-------------------------------------------------------------------------*/

if(empty($fcat_ids)){
    $sql = "SELECT cat_id as brand_id, cat_name as brand_name FROM ".$GLOBALS['ecs']->table('category')." where parent_id=1 AND is_show=1 ORDER BY sort_order, brand_id ASC";
}else{
    $sql = "SELECT cat_id as brand_id, cat_name as brand_name FROM ".$GLOBALS['ecs']->table('category')." where parent_id='$fcat_ids' AND is_show=1 ORDER BY sort_order, brand_id ASC";
}
$brands = $GLOBALS['db']->getAll($sql);
foreach($brands AS $key => $val)
{
    //---数组从1开始遍历--0位置用来放“全部”---
    $temp_key = $key + 1;
    $brands[$temp_key]['brand_name'] = $val['brand_name'];
    $brands[$temp_key]['url']="category.php?id=".$val['brand_id']."&brandx=".$val['brand_id']."&price_min=".$price_min."&price_max=".$price_max."&filter_attr=".$filter_attr_str."";

    if($brandx == $brands[$key]['brand_id']){
        $brands[$temp_key]['selected'] = 1;
    }else{
        $brands[$temp_key]['selected'] = 0;
    }

    //---用户初次进来蓝色显示选择品牌---
    $brand_yi = intval($_GET['id']);
    if($brand_yi == $brands[$key]['brand_id']){
        $brands[$temp_key]['selected'] = 1;
        $brands[0]['selected'] = 0;
    }
    //"全部"显示为蓝色
    if($brand_yi==1 ||$brand_yi==6 ||$brand_yi==64||$brand_yi==12||$brand_yi==76)
    {
        $brands[0]['selected'] = 1;
    }
}
$y_brands = array();
foreach($brands as $yk => $yv)
{
    if($yk < (count($brands)-1))
    {
        $y_brands[$yk]['brand_name']= $brands[$yk+1]['brand_name'];
        $y_brands[$yk]['brand_id']  = $brands[$yk]['brand_id'];
    }
}
//var_dump($y_brands);die;
$smarty->assign('y_brands', $y_brands);
switch($fcat_ids)
{
    case 1:
        $goods_type = 12; break;
    case 6:
        $goods_type = 10; break;
    case 64:
        $goods_type = 13; break;
    case 76:
        $goods_type = 14; break;
    case 159:
        $goods_type = 15; break;
    case 190:
        $goods_type = 16; break;
    default:
        $goods_type = 10; break;
}
$smarty->assign('categories',    get_categories_treecsz_wap($goods_type));//商品属性

// 筛选属性 - 价格筛选
if($goods_type == 10 || $goods_type == 12){// 透明片以及美瞳
    $price = array(
        array('id'=>'0,50','name'=>'50元及以下'),
        array('id'=>'50,100','name'=>'50元～100元'),
        array('id'=>'100,150','name'=>'100元～150元'),
        array('id'=>'150,200','name'=>'150元～200元'),
        array('id'=>'200,300','name'=>'200元～300元'),
        array('id'=>'300','name'=>'300元及以上')
    );
}elseif($goods_type == 13){// 护理液
    $price = array(
        array('id'=>'0,15','name'=>'15元及以下'),
        array('id'=>'16,30','name'=>'16元～30元'),
        array('id'=>'31,50','name'=>'31元～50元'),
        array('id'=>'51','name'=>'51元及以上')
    );
}elseif($goods_type == 14){// 护理工具
    $price = array(
        array('id'=>'0,5','name'=>'5元及以下'),
        array('id'=>'6,10','name'=>'6元～10元'),
        array('id'=>'11,20','name'=>'11元～20元'),
        array('id'=>'21,50','name'=>'21元～50元'),
        array('id'=>'51','name'=>'51元及以上')
    );
}elseif($goods_type == 15 || $goods_type == 16){// 框架眼镜以及太阳镜
    $price = array(
        array('id'=>'0,100','name'=>'100元及以下'),
        array('id'=>'100,200','name'=>'100元～200元'),
        array('id'=>'200,300','name'=>'200元～300元'),
        array('id'=>'300,500','name'=>'300元～500元'),
        array('id'=>'500,1000','name'=>'500元～1000元'),
        array('id'=>'1000','name'=>'1000元及以上')
    );
}else{// 其他
    $price = array(
        array('id'=>'0,50','name'=>'50元及以下'),
        array('id'=>'50,100','name'=>'50元～100元'),
        array('id'=>'100,150','name'=>'100元～150元'),
        array('id'=>'150,200','name'=>'150元～200元'),
        array('id'=>'200,300','name'=>'200元～300元'),
        array('id'=>'300','name'=>'300元及以上')
    );
}
$smarty->assign('price',$price);
//============================================================筛选结束==============================================================//

// 当前品牌展示
if(in_array($cat_id,array(1,6,12,64,76,159,190))){
    $cur = array('brand_id'=>'','brand_name'=>'全部');
}else{
    $sql = "SELECT cat_id as brand_id, cat_name as brand_name FROM ".$GLOBALS['ecs']->table('category')." where cat_id=".$cat_id;
    $cur = $GLOBALS['db']->getRow($sql);
}

$smarty->assign('cur',   $cur);
$smarty->assign('cat_id',   $cat_id);
$smarty->assign('st',   $st);
$smarty->assign('keyword', $keyword);

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
        $ext .= ' and g.cat_id in '.$in;
    }elseif($cat_id!=0 && $catt == false){
        $ext.= ' and g.cat_id = '.$cat_id;
    }
    if(!empty($keyword)){
        $ext.= ' and g.goods_name like "%'.$keyword.'%" ';
    }
    if(!empty($goodsids)){
        $ext .= $goodsids;
    }

    $num =category_get_goods_num_wap($ext);//获取商品列表数目

    $offset=($page-1)*$perpage;
    $goodslist =category_get_goods_wap($ext,$offset,$perpage);//获取商品列表
    //print_r($goodslist);
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
        $ext .= ' and g.cat_id in '.$in;
    }elseif($cat_id!=0 && $catt == false){
        $ext.= ' and g.cat_id = '.$cat_id;
    }
    if(!empty($keyword)){
        $ext.= ' and g.goods_name like "%'.$keyword.'%" '; 
    }
    if(!empty($goodsids)){
        $ext .= $goodsids;
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
        $sortlist.= '
            <li '.$saving2.'>'.$is_by.'
                <a class="list_a" href="goods.php?id='.$v['goods_id'].'">
                  <div class="goods_list_thumb pull-left">
                    <img src="http://img.yunjingshang.com/'.$v['b2b_goods_thumb'].'" />
                  </div>
                  <div class="goods_list_main">
                    <h2>'.$v['goods_name'].'</h2>
                    <div class="pg-tags-list">
                        '.$act.'
                    </div>
                    <div class="goods_list_price">
                        ￥'.$v['b2b_shop_price'].'
                    </div>
                  </div>
                </a>
            </li>
            ';
    endforeach;
        echo '<input type="hidden" id="filter" value=\''.$fil.'\' />';
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
            $saving  = '<span class="pg-tags pg-tags-red">直降'.$v['saving'].'元</span>';
            $saving2 = 'class="active11_badge_2"';
        }else{
            $saving  ='';
            $saving2 ='';
        }
        if($v['is_by']){
            $is_by  = '<span class="icon-chris"></span>';
        }else{
            $is_by  ='';
        }
        $sortlist.= '
            <div class="list_zong_left">
                <div '.$saving2.'>'.$is_by.'<a href="goods.php?id='.$v['goods_id'].'"><img src="http://img.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
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
        echo '<input type="hidden" id="filter" value=\''.$fil.'\' />';
    }
    echo $sortlist;die;
}

if($act=='more'){
    //var_dump($goodslist);die;
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
                $saving  = '<span class="pg-tags pg-tags-red">直降'.$v['saving'].'元</span>';
                $saving2 = 'class="active11_badge"';
            }else{
                $saving  ='';
                $saving2 ='';
            }
            if($v['is_by']){
                $is_by  = '<span class="icon-chris"></span>';
            }else{
                $is_by  ='';
            }
            echo '
                <li '.$saving2.'>'.$is_by.'
                    <a class="list_a" href="goods.php?id='.$v['goods_id'].'">
                      <div class="goods_list_thumb pull-left">
                        <img src="http://img.yunjingshang.com/'.$v['b2b_goods_thumb'].'" />
                      </div>
                      <div class="goods_list_main">
                        <h2>'.$v['goods_name'].'</h2>
                        <div class="pg-tags-list">'.$act.'</div>
                        <div class="goods_list_price">
                            ￥'.$v['b2b_shop_price'].$saving.'
                        </div>
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
                $saving  = '<span class="pg-tags pg-tags-red">直降'.$v['saving'].'元</span>';
                $saving2 = 'class="active11_badge_2"';
            }else{
                $saving  ='';
                $saving2 ='';
            }
            if($v['is_by']){
                $is_by  = '<span class="icon-chris"></span>';
            }else{
                $is_by  ='';
            }
            echo '
                <div class="list_zong_left">
                    <div '.$saving2.'>'.$is_by.'<a href="goods.php?id='.$v['goods_id'].'"><img src="http://img.easeeyes.com/'.$v['goods_thumb'].'"/></a></div>
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
}elseif($act == 'filter'){
    //var_dump($goodslist);die;
    //var_dump($goodsids);die;
    if($st!=1){
        if(empty($goodslist)){
            echo '<div style="font-size: 2rem; margin: 20px auto; width: 99%; text-align: center;">没有符合条件产品！</div>';
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
                    $saving  = '<span class="pg-tags pg-tags-red">直降'.$v['saving'].'元</span>';
                    $saving2 = 'class="active11_badge"';
                }else{
                    $saving  ='';
                    $saving2 ='';
                }
                if($v['is_by']){
                    $is_by  = '<span class="icon-chris"></span>';
                }else{
                    $is_by  ='';
                }
                echo '
                <li '.$saving2.'>'.$is_by.'
                    <a class="list_a" href="goods.php?id='.$v['goods_id'].'">
                      <div class="goods_list_thumb pull-left">
                        <img src="http://img.easeeyes.com/'.$v['goods_thumb'].'" />
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
        }
        echo '<input type="hidden" id="filter" value=\''.$fil.'\' />';
    }else{
        if(empty($goodslist)){
            echo '<div style="font-size: 2rem; margin: 20px auto; width: 99%; text-align: center;">没有符合条件产品！</div>';
        }else {
            foreach ($goodslist as $v) {
                $act = '';
                is_array($v['active']) ? null : $v['active'] = array();
                foreach ($v['active'] as $val) {
                    if ($val['act_type'] == 0) {    // 赠品
                        $act .= '<span class="pg-tags">赠</span>';
                    }
                    if ($val['act_type'] == 1) {    // 立减
                        $act .= '<span class="pg-tags pg-tags-orange">减</span>';
                    }
                    if ($val['act_type'] == 3) {    // 加价购
                        $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
                    }
                }
                if ($v['saving'] > 0) {
                    $saving = '<span class="pg-tags pg-tags-red">直降' . $v['saving'] . '元</span>';
                    $saving2 = 'class="active11_badge_2"';
                } else {
                    $saving = '';
                    $saving2 = '';
                }
                if ($v['is_by']) {
                    $is_by = '<span class="icon-chris"></span>';
                } else {
                    $is_by = '';
                }
                echo '
                    <div class="list_zong_left">
                        <div ' . $saving2 . '>' . $is_by . '<a href="goods.php?id=' . $v['goods_id'] . '"><img src="http://img.easeeyes.com/' . $v['goods_thumb'] . '"/></a></div>
                        <div><a href="goods.php?id=' . $v['goods_id'] . '">' . $v['goods_name'] . '</a></div>
                        <div class="pg-tags-list">' . $act . '</div>
                        <div class="goods_list_price">
                            ￥ ' . $v['shop_price'] . $saving . '
                        </div>
                        <p>已有 ' . $v['click_count'] . ' 人关注</p>
                        <div class="active"></div>
                    </div>
                ';
            }
        }
        echo '<input type="hidden" id="filter" value=\''.$fil.'\' />';
    }
    die;
}elseif($act == 'stp'){
    //var_dump($goodslist);die;
    if($st!=1){
        if(empty($goodslist)){
            echo '<div style="font-size: 2rem; margin: 20px auto; width: 99%; text-align: center;">没有符合条件产品！</div>';
        }else {
            foreach ($goodslist as $v) {
                $act = '';
                is_array($v['active']) ? null : $v['active'] = array();
                foreach ($v['active'] as $val) {
                    if ($val['act_type'] == 0) {    // 赠品
                        $act .= '<span class="pg-tags">赠</span>';
                    }
                    if ($val['act_type'] == 1) {    // 立减
                        $act .= '<span class="pg-tags pg-tags-orange">减</span>';
                    }
                    if ($val['act_type'] == 3) {    // 加价购
                        $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
                    }
                }
                if ($v['saving'] > 0) {
                    $saving = '<span class="pg-tags pg-tags-red">直降' . $v['saving'] . '元</span>';
                    $saving2 = 'class="active11_badge"';
                } else {
                    $saving = '';
                    $saving2 = '';
                }
                if ($v['is_by']) {
                    $is_by = '<span class="icon-chris"></span>';
                } else {
                    $is_by = '';
                }
                echo '
                    <li ' . $saving2 . '>' . $is_by . '
                        <a class="list_a" href="goods.php?id=' . $v['goods_id'] . '">
                          <div class="goods_list_thumb pull-left">
                            <img src="http://img.easeeyes.com/' . $v['goods_thumb'] . '" />
                          </div>
                          <div class="goods_list_main">
                            <h2>' . $v['goods_name'] . '</h2>
                            <div class="pg-tags-list">' . $act . '</div>
                            <div class="goods_list_price">
                                ￥' . $v['shop_price'] . $saving . '
                            </div>
                            <p>已有 ' . $v['click_count'] . ' 人关注</p>
                          </div>
                        </a>
                    </li>
                    ';
            }
        }
        echo '<input type="hidden" id="filter" value=\''.$fil.'\' />';
    }else{
        if(empty($goodslist)){
            echo '<div style="font-size: 2rem; margin: 20px auto; width: 99%; text-align: center;">没有符合条件产品！</div>';
        }else {
            foreach ($goodslist as $v) {
                $act = '';
                is_array($v['active']) ? null : $v['active'] = array();
                foreach ($v['active'] as $val) {
                    if ($val['act_type'] == 0) {    // 赠品
                        $act .= '<span class="pg-tags">赠</span>';
                    }
                    if ($val['act_type'] == 1) {    // 立减
                        $act .= '<span class="pg-tags pg-tags-orange">减</span>';
                    }
                    if ($val['act_type'] == 3) {    // 加价购
                        $act .= '<span class="pg-tags pg-tags-yellow">加</span>';
                    }
                }
                if ($v['saving'] > 0) {
                    $saving = '<span class="pg-tags pg-tags-red">直降' . $v['saving'] . '元</span>';
                    $saving2 = 'class="active11_badge_2"';
                } else {
                    $saving = '';
                    $saving2 = '';
                }
                if ($v['is_by']) {
                    $is_by = '<span class="icon-chris"></span>';
                } else {
                    $is_by = '';
                }
                echo '
                    <div class="list_zong_left">
                        <div ' . $saving2 . '>' . $is_by . '<a href="goods.php?id=' . $v['goods_id'] . '"><img src="http://img.easeeyes.com/' . $v['goods_thumb'] . '"/></a></div>
                        <div><a href="goods.php?id=' . $v['goods_id'] . '">' . $v['goods_name'] . '</a></div>
                        <div class="pg-tags-list">' . $act . '</div>
                        <div class="goods_list_price">
                            ￥ ' . $v['shop_price'] . $saving . '
                        </div>
                        <p>已有 ' . $v['click_count'] . ' 人关注</p>
                        <div class="active"></div>
                    </div>
                ';
            }
        }
        echo '<input type="hidden" id="filter" value=\''.$fil.'\' />';
    }
    die;
}elseif($act==''){//商品

    $smarty->assign('goods_list',   $goodslist);
    $smarty->assign('ur_here', "产品分类");
    $smarty->assign('page_title', "产品分类 - 云镜商手机版");
	$smarty->display('category.dwt');
}
//=====================================================================【函数】===============================================================================//



function category_get_goods_wap($ext='',$offect=0,$perpage=10){
    //$sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('goods').' where is_on_sale = 1 and cat_id != 138 and is_delete = 0 and shop_price > 0 '.$ext.' limit '.$offect.','.$perpage;
        
    $sql = 'SELECT b.b2b_goods_thumb,b.goods_id,b.b2b_market_price,b.b2b_shop_price,b.is_wholesale,g.goods_name FROM `b2b_goods` AS b LEFT JOIN '.$GLOBALS['ecs']->table('goods').' AS g ON b.goods_id = g.goods_id WHERE b.b2b_is_on_sale = 1 and g.cat_id != 138 
    AND g.is_delete = 0 AND b.b2b_shop_price > 0 '.$ext.' limit '.$offect.','.$perpage;
	
    $arr = $GLOBALS['db']->getAll($sql);
    // zhang: 160309修改，存在抢购价时列表页显示抢购价（待手机专享价测试通过后需要加上专享价的判断，优先显示专享价）
    foreach($arr as $k=>$v){
		//去除数据库中图片路径的'/'号 BY:TAO
		$arr[$k]['goods_img'] = str_replace("/b2c/","b2c/",$v['goods_img']);
        $arr[$k]['goods_thumb'] = str_replace("/b2c/","b2c/",$v['goods_thumb']);
        $arr[$k]['original_img'] = str_replace("/b2c/","b2c/",$v['original_img']);
    }
    return $arr;
}

function category_get_goods_num_wap($ext=''){
    $sql = 'SELECT count(*) as num FROM `b2b_goods` AS b LEFT JOIN '.$GLOBALS['ecs']->table('goods').' AS g ON b.goods_id = g.goods_id 
    WHERE b.b2b_is_on_sale = 1 and g.cat_id != 138 and g.is_delete = 0 and b.b2b_shop_price > 0 '.$ext;
	return $GLOBALS['db']->getOne($sql);
}

function get_sort_ext($sort){
    switch($sort){
        case '1'://默认排序
            $sortExt = ' order by g.goods_id asc';
        break;
        case '2'://销量从高到低
            $sortExt = ' order by g.click_count desc';
        break;
        case '3'://销量从低到高
            $sortExt = ' order by g.click_count asc';
        break;
        case '4'://价格从低到高
            $sortExt = ' order by b.b2b_shop_price asc';
        break;
        case '5'://价格从高到低
            $sortExt = ' order by b.b2b_shop_price desc';
        break;
        default:
            $sortExt = ' order by g.goods_id asc';
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

//---商品属性函数-----------------------------------------------------------------------------------------
function get_categories_treecsz_wap($cat_id = 10)
{
    //attr_id as parent_id,cat_id,attr_name,attr_values
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('attribute') . " WHERE cat_id = '$cat_id' ";
    if ($GLOBALS['db']->getOne($sql))
    {
        /* 获取当前分类及其子分类 */
        $sql = 'SELECT attr_id as cat_id,attr_name as cat_name,attr_values '.'FROM ' . $GLOBALS['ecs']->table('attribute')."WHERE cat_id = '$cat_id'  ORDER BY cat_id ASC";
        $res = $GLOBALS['db']->getAll($sql);
        //var_dump($res);die;

        foreach ($res AS $row)
        {
            if($cat_id == 10 && $row['cat_id'] < 216){// 美瞳

                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

                if($row['cat_id'] == 213){
                    $attr_values_list = array('37%以下(低含水量)','38%～49%(中含水量)','50%～58%(高含水量)','59%以上(超高含水量)');
                }else{
                    $attr_values       = str_replace("\r\n", "|||", $row['attr_values']);//yi:2012/9/7
                    $attr_values_list  = explode("|||",$attr_values);
                }
                //---属性列表----
                $cat_arr[$row['cat_id']]['attr_values']= $attr_values_list;
            }elseif($cat_id == 12 && $row['cat_id'] < 224 && $row['cat_id'] != 220){// 透明片

                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

                if($row['cat_id'] == 221){
                    $attr_values_list = array('37%以下(低含水量)','38%～49%(中含水量)','50%～58%(高含水量)','59%以上(超高含水量)');
                }else{
                    $attr_values       = str_replace("\r\n", "|||", $row['attr_values']);//yi:2012/9/7
                    $attr_values_list  = explode("|||",$attr_values);
                }
                //---属性列表----
                $cat_arr[$row['cat_id']]['attr_values']= $attr_values_list;
            }elseif($cat_id == 13 && $row['cat_id'] > 255){// 护理液

                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

                $attr_values       = str_replace("\r\n", "|||", $row['attr_values']);//yi:2012/9/7
                $attr_values_list  = explode("|||",$attr_values);
                //---属性列表----
                $cat_arr[$row['cat_id']]['attr_values']= $attr_values_list;
            }elseif($cat_id == 14 && $row['cat_id'] == 255){// 护理工具

                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

                $attr_values       = str_replace("\r\n", "|||", $row['attr_values']);//yi:2012/9/7
                $attr_values_list  = explode("|||",$attr_values);
                //---属性列表----
                $cat_arr[$row['cat_id']]['attr_values']= $attr_values_list;
            }elseif($cat_id == 15 && $row['cat_id'] < 249){// 框架眼镜

                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

                $attr_values       = str_replace("\r\n", "|||", $row['attr_values']);//yi:2012/9/7
                $attr_values_list  = explode("|||",$attr_values);
                //---属性列表----
                $cat_arr[$row['cat_id']]['attr_values']= $attr_values_list;
            }elseif($cat_id == 16 && $row['cat_id'] < 263){// 太阳镜

                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];

                $attr_values       = str_replace("\r\n", "|||", $row['attr_values']);//yi:2012/9/7
                $attr_values_list  = explode("|||",$attr_values);
                //---属性列表----
                $cat_arr[$row['cat_id']]['attr_values']= $attr_values_list;
            }
        }
        //var_dump($cat_arr);die;
    }
    if(isset($cat_arr))
    {
        return $cat_arr;
    }
}

// 将字符串转换成utf8
function fixEncoding($in_str){
    $cur_encoding = mb_detect_encoding($in_str) ;

    if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))

        return $in_str;

    else

        return utf8_encode($in_str);

}
//根据选择参数从ecs_goods_attr表中查询适应该概述的goods_id
function get_goods_id_from_attr($attr_value, $str='') {
    $goods_ids = '';
    $temp_array = array();

    $temp_sql = '';
    if ($str == 'hsl') {
        //含水量属性
        if ($attr_value == '1') {
            //37%以下
            $temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='24%' OR attr_value='33%' OR attr_value='36%'";
        } elseif ($attr_value == '2') {
            $temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='38%' OR attr_value='39%' OR attr_value='40%' OR attr_value='42%' OR attr_value='45%' OR attr_value='47%' OR attr_value='48%'";
        } elseif ($attr_value == '3') {
            $temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='52%' OR attr_value='55%' OR attr_value='58%'";
        } elseif ($attr_value == '4') {
            $temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_value='59%' OR attr_value='60%' OR attr_value='66%' OR attr_value='69%'";
        }
    } else {
        $temp_sql = 'SELECT goods_id FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' WHERE attr_value="' . $attr_value . '"';
    }

    $res_temp = $GLOBALS['db']->query($temp_sql);
    while($row = $GLOBALS['db']->fetchRow($res_temp)){
        $temp_array[] = $row['goods_id'];
    }
    if ($temp_array) {
        $goods_ids = ' AND goods_id IN ('.implode(',', $temp_array) . ') ';
    } else {
        $goods_ids = ' AND goods_id IN (0) ';
    }

    return $goods_ids;
}

?>