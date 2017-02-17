<?php
/*=====================================================================首页20150624 Tao===========================================================*/
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
//define('IMG_SITE','http://192.168.1.53:3001/');
if((DEBUG_MODE & 2) != 2){$smarty->caching = false;}
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-'. $_SESSION['user_id'] .'-'. $_CFG['lang']));

$user_id    = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;    
$smarty->assign('user_id',               $user_id);	
$smarty->assign('user_name',               $_SESSION['user_name']);	


//未注册用户是否跳回展示页(index_unck.dwt)
index_unck_display(1);

if(!$smarty->is_cached('index.dwt', $cache_id))
{
    	/*-------------------------------系统配置选项START-----------------------------------*/
        	$smarty->assign('page_title',      '云镜商_眼镜行业全方位服务提供商');
        	$smarty->assign('keywords',        '云镜商_眼镜行业全方位服务提供商');
            $smarty->assign('description',     '云镜商_眼镜行业全方位服务提供商');
            $smarty->assign('index_site',       1);
            
            $position = assign_ur_here();
        
        /*-------------------------------系统配置选项END-----------------------------------*/
        
        //首焦A1
            $carousel_ad     =   ad_info_by_time(110,5);
        //新品A2
            $new_goods       =   ad_info_by_time(111,3);
        //广告图左A3
            $ad_left         =   ad_info_by_time(112,1);
        //广告图右A4
            $ad_right        =   ad_info_by_time(113,3);
        //中部横幅A5
            $center_ad       =   ad_info_by_time(114,1);
        //热推产品图A6
            $re_goods        =   ad_info_by_time(115,6);
        //底部横幅A7
            $bottom_ad       =   ad_info_by_time(116,1);
        //文章-公告
            $article_1       =   get_cat_articles(34,1,6);
        //文章-规则
            $article_2       =   get_cat_articles(33,1,6);
        //文章-买家
            $article_3       =   get_cat_articles(35,1,6);
        //文章-卖家
            $article_4       =   get_cat_articles(36,1,6);    
            
            $smarty->assign('brand_1',         get_brand_by_cat(1));       // 透明隐形眼镜
            $smarty->assign('brand_6',         get_brand_by_cat(6));       // 彩色隐形眼镜
            $smarty->assign('brand_64',        get_brand_by_cat(64));      // 护理液
            //$smarty->assign('brand_76',        get_brand_by_cat(76));      // 护理工具 
            $smarty->assign('brand_159',       get_brand_by_cat(159));     // 框架眼镜
            $smarty->assign('brand_190',       get_brand_by_cat(190));     // 太阳眼镜
         

            
            $smarty->assign('ad_A1',             $carousel_ad);
            $smarty->assign('ad_A2',             $new_goods);
            $smarty->assign('ad_A3',             $ad_left);
            $smarty->assign('ad_A4',             $ad_right);
            $smarty->assign('ad_A5',             $center_ad);
            $smarty->assign('ad_A6',             $re_goods);
            $smarty->assign('ad_A7',             $bottom_ad);
            $smarty->assign('article_1',             $article_1);
            $smarty->assign('article_2',             $article_2);
            $smarty->assign('article_3',             $article_3);
            $smarty->assign('article_4',             $article_4);
            
            //首页分类推荐子产品
            $smarty->assign('index_cat_rec_1',               index_cat_rec(104,6));
            $smarty->assign('index_cat_rec_6',               index_cat_rec(105,6));
            $smarty->assign('index_cat_rec_64',              index_cat_rec(106,6));
            $smarty->assign('index_cat_rec_76',              index_cat_rec(107,6));
            $smarty->assign('index_cat_rec_159',             index_cat_rec(108,6));
            $smarty->assign('index_cat_rec_190',             index_cat_rec(109,6));
            
            
            $smarty->assign('type_order_num',             type_order_num($user_id));//分类订单数
        //信用背书模块 (医疗机械经营许可证,支付宝财务通支付保障,顺丰快递急速到达,开具正规发票)
        
        //清仓,近效期商品展示
        
        //搜索
      
}
$smarty->display('index.dwt', $cache_id);


/**
 * @name 首页分类推荐子产品数据调取
 */
function index_cat_rec($pid = 0 ,$size = 1){
    $ad_info = ad_info($pid,$size);
    $cat_goods = explode(',',$ad_info[0]['ad_name']);
    
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

?>
