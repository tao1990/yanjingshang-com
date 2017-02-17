<?php
define('IN_ECS', true);

require_once(dirname(__FILE__) . '/includes/init.php');
require_once(dirname(__FILE__) . '/../includes/lib_article.php');

if((DEBUG_MODE & 2) != 2){$smarty->caching = false;}
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-'. $_CFG['lang']));

$user_id    = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;    
$smarty->assign('user_id',               $user_id);	
$smarty->assign('user_name',               $_SESSION['user_name']);	

//未注册用户是否跳回展示页(index_unck.dwt)
index_unck_display_wap(2);

if(!$smarty->is_cached('index.dwt', $cache_id))
{
    	/*-------------------------------系统配置选项START-----------------------------------*/
        	$smarty->assign('page_title',      '云镜商_眼镜行业全方位服务提供商');
        	$smarty->assign('keywords',        '云镜商_眼镜行业全方位服务提供商');
            $smarty->assign('description',     '云镜商_眼镜行业全方位服务提供商');
            $smarty->assign('index_site',       1);
            
        /*-------------------------------系统配置选项END-----------------------------------*/
        
        //首焦A1
            $carousel_ad     =   ad_info_by_time(110,5);

            
            //首页分类推荐子产品
            $smarty->assign('index_cat_rec_1',               index_cat_rec(104,6));
            $smarty->assign('index_cat_rec_6',               index_cat_rec(105,6));
            $smarty->assign('index_cat_rec_64',              index_cat_rec(106,6));
            $smarty->assign('index_cat_rec_76',              index_cat_rec(107,6));
            $smarty->assign('index_cat_rec_159',             index_cat_rec(108,6));
            $smarty->assign('index_cat_rec_190',             index_cat_rec(109,6));
            
        //品牌列表
            $smarty->assign('brand_list',    get_brand_info_list(9));   
      
}
$smarty->display('index.dwt', $cache_id);


/**
 * @name 首页分类推荐子产品数据调取
 */
function index_cat_rec($pid = 0 ,$size = 1){
    $ad_info = ad_info($pid,$size);
    @$cat_goods = explode(',',$ad_info[0]['ad_name']);
    
    if(is_array($cat_goods) && !empty($cat_goods[0]) && is_numeric($cat_goods[0])){
        $resArr =array();
        foreach($cat_goods as $k=>$v){
          $res = getMinMaxPrice($v);
          $goods_info = $GLOBALS['db']->getRow('SELECT g.goods_name,b.b2b_goods_thumb,g.goods_id FROM ecs_goods AS g LEFT JOIN 
          b2b_goods AS b ON g.goods_id = b.goods_id WHERE g.goods_id = '.$v);  
          
          $resArr[$k]['goods_id'] = $v;
          $resArr[$k]['goods_name'] = $goods_info['goods_name'];
          $resArr[$k]['goods_img']  = $goods_info['b2b_goods_thumb'];
          $resArr[$k]['min']        = $res['min'];  
        }
    }else{
        $resArr = array();
    }
    return $resArr;
}

/**
 * @name 获取各个订单数量
 */
function type_order_num($user_id){
   
        //待付款
        $sqlStr1 = ' AND order_status=0 AND pay_status = 0 AND shop_id = 2';
        //待发货
        $sqlStr2 = ' AND order_status=1 AND shipping_status = 0 AND (pay_status=2 OR (pay_id=3 AND (order_status=1 OR order_status=5))) AND shop_id = 2';
        //待确认收货
        $sqlStr3 = ' AND shipping_status = 1 AND (order_status =1 OR order_status =5) AND shop_id = 2';
        //待评价
        $sqlStr4 = ' AND shipping_status=2 AND shop_id = 2';
        //退款退货中
        $sqlStr5 = ' AND order_status=4 AND shop_id = 2';

        $arr['type1'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
        " WHERE user_id = '$user_id' $sqlStr1 ");
        $arr['type2'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
        " WHERE user_id = '$user_id' $sqlStr2 ");
        $arr['type3'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
        " WHERE user_id = '$user_id' $sqlStr3 ");
        $arr['type4'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
        " WHERE user_id = '$user_id' $sqlStr4 ");
        $arr['type5'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). 
        " WHERE user_id = '$user_id' $sqlStr5 ");
        return $arr;
}

function get_brand_by_cat($parent_id=1)
{
    $sql = "select distinct b.*, c.cat_id from ecs_goods as g left join ecs_category as c on g.cat_id=c.cat_id left join ecs_brand as b on g.brand_id=b.brand_id ".
        " where c.parent_id=".$parent_id." and b.brand_id is not null and b.b2b_is_show=1 order by b.b2b_sort_order asc limit 0,20;";
    $arr = $GLOBALS['db']->getAll($sql);
    foreach($arr as $k => $v)
    {
        //$arr[$k]['url'] = ($v['site_url']=='' || $v['site_url']=='http://' || $v['site_url']=='http://#')? 'brand_'.$v['brand_id'].'-1-update_last-desc.html':$v['site_url'];
        //$arr[$k]['url'] = 'brand2_'.$v['brand_id'].'-'.$parent_id.'-1-update_last-desc.html';   zhang：150915修改品牌链接
        $arr[$k]['url'] = 'category_'.$v['cat_id'].'.html';
        $arr[$k]['brand_logo'] = 'data/brandlogo/'.$v['b2b_brand_logo'];
        $arr[$k]['sign'] = ($k+1)%5==0? 1:0;
    }
   //print_r($arr);die;
    return $arr;
}

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
?>