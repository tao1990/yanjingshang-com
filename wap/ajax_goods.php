<?php
/**
 * ajax：商品相关数据
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');

$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;

/**
 * 栏目页获取商品列表数据
 */
if($_REQUEST['act'] == 'category_goods')
{
	require(dirname(__FILE__) . '/includes/pf_public.php');
	
	$parent_id = isset($_REQUEST['parent_id'])? intval($_REQUEST['parent_id']): 1;
	$params = isset($_REQUEST['params'])? trim(addslashes($_REQUEST['params'])): '';
	$page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
	$order_by = isset($_REQUEST['order_by'])? trim(addslashes($_REQUEST['order_by'])): '';
	
	$p_array = explode("||", $params);
	
	$where = ' WHERE 1=1 ';
	
	//0. 子栏目id
	if (empty($p_array[0]))
	{
		$children_cat_array = get_cat_id_by_parent($parent_id);
		$children_cat_str = implode(',', $children_cat_array);
		if ($children_cat_str) {
			$where .= ' AND cat_id IN (' . $children_cat_str .') ';
		} else {
			$where .= ' AND cat_id != 138 ';
		}
	}
	else
	{
		//根据传过来的栏目中文名查询栏目id
		$cat_id = get_cat_id_by_name($p_array[0]);
		if ($cat_id)
		{
			$where .= ' AND cat_id = '.$cat_id;
		}
		else
		{
			$children_cat_array = get_cat_id_by_parent($parent_id);
			$children_cat_str = implode(',', $children_cat_array);
			if ($children_cat_str) {
				$where .= ' AND cat_id IN (' . $children_cat_str .') ';
			} else {
				$where .= ' AND cat_id != 138 ';
			}
		}
	}
	
	//1. 周期
	if ( ! empty($p_array[1]))
	{
		$where .= get_goods_by_attr($p_array[1], 'zq', $parent_id);
	}
	
	//2. 含水量
	if ( ! empty($p_array[2]))
	{
		$where .= get_goods_by_attr($p_array[2], 'hsl', $parent_id);
	}
	
	//3. 直径
	if ( ! empty($p_array[3]))
	{
		$where .= get_goods_by_attr($p_array[3], 'zj', $parent_id);
	}
	
	//4. 基弧
	if ( ! empty($p_array[4]))
	{
		$where .= get_goods_by_attr($p_array[4], 'jh', $parent_id);
	}
	
	//5. 颜色
	if ( ! empty($p_array[5]))
	{
		$where .= get_goods_by_attr($p_array[5], 'color', $parent_id);
	}
	
	//6. 价格
	if ( ! empty($p_array[6]))
	{
		$where .= get_goods_by_attr($p_array[6], 'price', $parent_id);
	}
	
	//7. 护理液：功能
	if ( ! empty($p_array[7]))
	{
		$where .= get_goods_by_attr($p_array[7], 'gn', $parent_id);
	}
	
	//8. 护理液：规格
	if ( ! empty($p_array[8]))
	{
		$where .= get_goods_by_attr($p_array[8], 'gg', $parent_id);
	}
	
	//9. 护理工具：类型
	if ( ! empty($p_array[9]))
	{
		$where .= get_goods_by_attr($p_array[9], 'lx', $parent_id);
	}
	
	//10. 框架,太阳镜 款式
	if ( ! empty($p_array[10])) 
	{
		$where .= get_goods_by_attr($p_array[10], 'ks', $parent_id);
	}
	
	//11. 框架,太阳镜 框型
	if ( ! empty($p_array[11])) 
	{
		$where .= get_goods_by_attr($p_array[11], 'kx', $parent_id);
	}
	
	//12. 框架,太阳镜 框型
	if ( ! empty($p_array[12])) 
	{
		$where .= get_goods_by_attr($p_array[12], 'cm', $parent_id);
	}
	
	//13. 框架,太阳镜 材质
	if ( ! empty($p_array[13])) 
	{
		$where .= get_goods_by_attr($p_array[13], 'cz', $parent_id);
	}
	
	$where .= ' AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 ';
	
	//每页记录数,总记录数
	$size = 20;
	$total_rows  = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods') . $where);
	
	//总页数
	$total_pages = 1;
	if ($total_rows/$size > 1)
	{
		if (is_int($total_rows/$size)) $total_pages = $total_rows/$size;
		else $total_pages = intval($total_rows/$size) + 1;
	}
	
	//当前页
	if ($page > $total_pages) $page = $total_pages;
	
	//排序
	$order_sql = ' ORDER BY sort_order ASC, goods_id DESC ';
	//if ($order_by) $order_sql = ' ORDER BY ' . $order_by;
	if ( ! empty($order_by))
	{
		$temp_sort_arr = explode(' ', $order_by);
		$smarty->assign('sort_name', $temp_sort_arr[0]);
		$smarty->assign('sort_type', $temp_sort_arr[1]);
		
		//临时(等设置goods表中的评论字段后再更改)
		if ($temp_sort_arr[0] == 'comment_count') $temp_sort_arr[0] = 'click_count';
		$order_sql = ' ORDER BY ' . $temp_sort_arr[0] . ' ' . $temp_sort_arr[1];
	}
	
	//LIMIT
	$limit_sql = " LIMIT " . ($page - 1) * $size . ", " . $size;
	
	//查询结果(后期优化下)
	$sql = "SELECT * FROM " .$GLOBALS['ecs']->table('goods'). $where . $order_sql . $limit_sql;
	$rs = $GLOBALS['db']->getAll($sql);
	$goods_list = array();
	foreach ($rs as $k => $v)
	{
		if ($v['promote_price'] > 0)
        {
            $promote_price = bargain_price($v['promote_price'], $v['promote_start_date'], $v['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }
		$goods_list[$k]['goods_id'] = $v['goods_id'];
		$goods_list[$k]['goods_name'] = $v['goods_name'];
		$goods_list[$k]['goods_brief'] = $v['goods_brief'];
		$goods_list[$k]['goods_name_desc'] = $v['goods_name_desc'];
		$goods_list[$k]['market_price'] = $v['market_price'];
		$goods_list[$k]['shop_price'] = ($promote_price > 0)? $promote_price: $v['shop_price'];
		//$goods_list[$k]['goods_img_300'] = $v['original_img'];
		$goods_list[$k]['click_count'] = $v['click_count'];
		//$goods_list[$k]['is_new'] = $v['is_new'];
		//$goods_list[$k]['is_best'] = $v['is_best'];
		//$goods_list[$k]['is_hot'] = $v['is_hot'];
		//$goods_list[$k]['is_cx'] = $v['is_cx'];
		//$goods_list[$k]['is_promote'] = $v['is_promote'];
		//$goods_list[$k]['is_tj'] = $v['is_tj'];
		//$goods_list[$k]['promote_start_date'] = $v['promote_start_date'];
		//$goods_list[$k]['promote_end_date'] = $v['promote_end_date'];
		//$goods_list[$k]['url']			  = 'goods'.$v['goods_id'].'.html';
		//$goods_list[$k]['hv_gift']          = goods_hv_gift($v['goods_id']);//商品是否有赠品
		
		//标签(直降：新增字段)
		if ($v['is_promote'] > 0 && $v['promote_end_date'] > $_SERVER['REQUEST_TIME'] && $_SERVER['REQUEST_TIME'] > $v['promote_start_date']) {
			$goods_list[$k]['tag_promotion'] = 1;
		} elseif ($v['is_cx'] > 0) {
			$goods_list[$k]['tag_promotion'] = 1;
		} elseif ($v['is_tj']>0 || $v['is_hot']>0) {
			$goods_list[$k]['tag_hot'] = 1;
		} elseif ($v['is_new'] > 0) {
			$goods_list[$k]['tag_new'] = 1;
		} 
	}
	
	//分页选项
	$pages_str = "";
	if ($total_pages <= 9)
	{
		for ($p=1; $p<=$total_pages; $p++)
		{
			if ($page == $p)
			{
				$pages_str .= '<span class="p_on">'.$p.'</span>';
			}
			else 
			{
				$pages_str .= '<span class="p_default">'.$p.'</span>';
			}
		}
	}
	else
	{
		if ($page <= 6)
		{
			for ($p=1; $p<=8; $p++)
			{
				if ($page == $p)
				{
					$pages_str .= '<span class="p_on">'.$p.'</span>';
				}
				else 
				{
					$pages_str .= '<span class="p_default">'.$p.'</span>';
				}
			}
			$pages_str .= '<span class="p_ellipsis">...</span>';
			$pages_str .= '<span class="p_default">'.$total_pages.'</span>';
		}
		else
		{
			if ($total_pages - $page >= 6)
			{
				$pages_str .= '<span class="p_default">1</span>';
				$pages_str .= '<span class="p_ellipsis">...</span>';
				$pages_str .= '<span class="p_default">'.($page-3).'</span>';
				$pages_str .= '<span class="p_default">'.($page-2).'</span>';
				$pages_str .= '<span class="p_default">'.($page-1).'</span>';
				$pages_str .= '<span class="p_on">'.$page.'</span>';
				$pages_str .= '<span class="p_default">'.($page+1).'</span>';
				$pages_str .= '<span class="p_default">'.($page+2).'</span>';
				$pages_str .= '<span class="p_default">'.($page+3).'</span>';
				$pages_str .= '<span class="p_ellipsis">...</span>';
				$pages_str .= '<span class="p_default">'.$total_pages.'</span>';
			}
			else 
			{
				$pages_str .= '<span class="p_default">1</span>';
				$pages_str .= '<span class="p_ellipsis">...</span>';
				for ($p=7; $p>=0; $p--)
				{
					if ($page == $total_pages - $p)
					{
						$pages_str .= '<span class="p_on">'.($total_pages - $p).'</span>';
					}
					else 
					{
						$pages_str .= '<span class="p_default">'.($total_pages - $p).'</span>';
					}
				}
			}
		}
	}
	
	$smarty->assign('sql',			$sql); //打印sql：测试用
	$smarty->assign('total_rows',		$total_rows);
	$smarty->assign('total_pages',		$total_pages);
	$smarty->assign('page',				$page);
	$smarty->assign('order_by',			$order_by);
	$smarty->assign('goods_list',		$goods_list);
	$smarty->assign('pages_str',		$pages_str);
	
	$smarty->display('goods_list.dwt');
}

//商品加入购物车
elseif ($_REQUEST['act'] == 'add_to_cart')
{
    /*------------------------------------------------------*/
    //-- 添加商品到购物车
    /*------------------------------------------------------*/
    include_once('includes/cls_json.php');
    $_POST['goods'] = json_str_iconv($_POST['goods']);

    if(!empty($_REQUEST['goods_id']) && empty($_POST['goods']))
    {
        if(!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)
        {
            ecs_header("Location:./\n");
        }
        $goods_id = intval($_REQUEST['goods_id']);
        exit;
    }

    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json  = new JSON;

    if(empty($_POST['goods']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $goods = $json->decode($_POST['goods']);
    
    /**
     * 8月大促 指定商品必须登陆后进行兑换/购买（新用户专享）
     */
    if ($_SERVER['REQUEST_TIME'] > strtotime('2016-02-02 00:00:00') && $_SERVER['REQUEST_TIME'] < strtotime('2016-10-15 00:00:00')){
        //$recArr = array(838,876);
        if(in_array(971,explode(',',$_COOKIE['source_rec_id'])) && in_array($goods->goods_id,array(5483))){
            if($_SESSION['user_id']==0){
                $result['message'] = '此商品为活动商品 请登陆后再试^_^';
                $result['error'] = 1;
                $result['pageJump'] = 'http://m.easeeyes.com/user.php';
                die($json->encode($result));
            }else{
                $order_num  = $GLOBALS['db']->getOne('SELECT sum(b.goods_number) FROM ecs_order_info as a LEFT JOIN ecs_order_goods as b ON a.order_id = b.order_id 
                   WHERE a.user_id = "'.$_SESSION['user_id'].'" AND b.extension_code = "source_buy" AND b.goods_id = '.$goods->goods_id);
                    if($order_num>=1){
                        $result['message'] = '此商品限购 最多可购买1件^_^';
                        $result['error'] = 1;
                        die($json->encode($result));
                    }
            }
        }
    }
    if (($_SERVER['REQUEST_TIME'] < strtotime('2015-8-31 00:00:00'))){
        $login_goods_id = array(4887,4886,4885,4893,4894,4895);//登陆后才能购买/兑换的产品

        if(in_array($goods->goods_id,$login_goods_id)){
            $no_continue = 1;
        }else{
            $no_continue = "";
        }

        if($no_continue == 1){
            if($_SESSION['user_id'] == 0){
                $result['message'] = '此商品为特殊商品，请注册/登陆后购买^_^';
                $result['error'] = 1;
                die($json->encode($result));
            }else{

                $is_new_user = $GLOBALS['db']->getOne('SELECT order_id FROM ecs_order_info
               WHERE user_id = '.$_SESSION['user_id'].' AND pay_status = 2');
                if($is_new_user){//是否为新用户
                    $result['message'] = '此商品为新用户专享^_^';
                    $result['error'] = 1;
                    die($json->encode($result));
                }else{//限购
                    $cart_num = $GLOBALS['db']->getOne('SELECT sum(goods_number) FROM ecs_cart
               WHERE user_id = '.$_SESSION['user_id'].' AND goods_id = '.$goods->goods_id);
                    if($cart_num>=4 || $goods->number>=4){
                        $result['message'] = '此商品限购 新用户最多可购买4件^_^';
                        $result['error'] = 1;
                        die($json->encode($result));
                    }
                }
            }
        }

    }

    //-------------------------------------------------------------------------------------------//
    //解决远视没有'+'的情况
    if(!empty($goods->zselect) && $goods->zselect!='平光' && $goods->zselect>0.00){
        $goods->zselect = '+'.trim($goods->zselect);
    }
    if(!empty($goods->yselect) && $goods->yselect!='平光' && $goods->yselect>0.00){
        $goods->yselect = '+'.trim($goods->yselect);
    }
    //散光片的散光度数
    if(!empty($goods->zsg) && $goods->zsg>0.00){
        $goods->zsg = '+'.trim($goods->zsg);
    }
    if(!empty($goods->ysg) && $goods->ysg>0.00){
        $goods->ysg = '+'.trim($goods->ysg);
    }
    //-------------------------------------------------------------------------------------------//

    /* 如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
    if(empty($goods->spec) && empty($goods->quick) && $goods->lhj == 0)
    {
        $sql =  "SELECT a.attr_id, a.attr_name, a.attr_type,g.goods_attr_id, g.attr_value, g.attr_price " .
            'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
            "WHERE a.attr_type != 0 AND g.goods_id = '" . $goods->goods_id . "' " .
            'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';
        $res = $GLOBALS['db']->getAll($sql);

        if($res)
        {
            $spe_arr = array();
            foreach ($res AS $row)
            {
                $spe_arr[$row['attr_id']]['attr_type'] 	= $row['attr_type'];
                $spe_arr[$row['attr_id']]['name']     	= $row['attr_name'];
                $spe_arr[$row['attr_id']]['attr_id']    = $row['attr_id'];
                $spe_arr[$row['attr_id']]['values'][] 	= array(
                    'label'        => $row['attr_value'],
                    'price'        => $row['attr_price'],
                    'format_price' => price_format($row['attr_price'], false),
                    'id'           => $row['goods_attr_id']);
            }
            $i = 0;
            $spe_array = array();
            foreach ($spe_arr AS $row)
            {
                $spe_array[]=$row;
            }
            $result['error']   = ERR_NEED_SELECT_ATTR;
            $result['goods_id'] = $goods->goods_id;
            $result['parent'] = $goods->parent;
            $result['message'] = $spe_array;
            //var_dump($result);exit();
            die($json->encode($result));
        }
    }

    /* 如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1')
    {
        clear_cart();
    }

    //yi:验证该度数是否有库存
    if('nobuy' == @$goods->zselect || 'nobuy' == @$goods->yselect)
    {
        $result['error']   = 1;
        $result['message'] = "很抱歉，您购买的度数正在补货中，该度数暂不能购买。";
    }
    else
    {
        if (!is_numeric($goods->number) || intval($goods->number) <= 0)
        {
            $result['error']   = 1;
            $result['message'] = $_LANG['invalid_number'];//检查商品数量是否合法
        }
        else
        {
            //将session_id存进cookie,用于保存未登录用户的购物车信息(保存一天)
            if ($_SESSION['user_id'] <= 0) {
                if (!isset($_COOKIE['cart_session_id'])) {
                    //首次加入购物车
                    setcookie('cart_session_id', SESS_ID, time()+3600*24, '/', '');
                } else {
                    //非首次加入购物车,将之前的session_id改为当前的SESS_ID
                    if ($_COOKIE['cart_session_id'] != SESS_ID) {
                        $GLOBALS['db']->query("UPDATE ".$GLOBALS['ecs']->table('cart')." SET session_id='".SESS_ID."' WHERE user_id <= 0 AND session_id='".$_COOKIE['cart_session_id']."'");
                        setcookie('cart_session_id', SESS_ID, time()+3600*24, '/', '');
                    }
                }
            }

            /*--------------------------------------------------商品页添加到购物车----------------------------------------------*/
            if(empty($goods->issg)){
                //添加到购物车
                if(addto_cart_wap($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->zselect, $goods->zcount, $goods->yselect, $goods->ycount))
                {
                    if($_CFG['cart_confirm'] > 2)
                    {
                        $result['message'] = '';
                    }
                    else
                    {
                        $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
                    }
                    //插入购物车的反馈信息
                    $result['content'] = insert_cart_infotop();   //购物车导航条
                    $result['one_step_buy'] = $_CFG['one_step_buy'];
                }
                else
                {
                    $result['message']  = $err->last_message();
                    $result['error']    = $err->error_no;
                    $result['goods_id'] = stripslashes($goods->goods_id);
                }
            }else{
                /*-----------------散光片添加到购物车中-----------------*/
                if(addto_cartsg_wap($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $goods->zselect, $goods->zcount, $goods->yselect, $goods->ycount,$goods->zsg,$goods->ysg,$goods->zzhou,$goods->yzhou))
                {
                    if ($_CFG['cart_confirm'] > 2)
                    {
                        $result['message'] = '';
                    }
                    else
                    {
                        $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
                    }
                    //插入购物车的反馈信息
                    $result['content'] = insert_cart_infotop();
                    $result['one_step_buy'] = $_CFG['one_step_buy'];
                }
                else
                {
                    $result['message']  = $err->last_message();
                    $result['error']    = $err->error_no;
                    $result['goods_id'] = stripslashes($goods->goods_id);
                }
            }
        }
    }
    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    echo $result['content'];exit();
    die($json->encode($result));
}
//==============================================================【框架眼镜和镜片加入购物车】===============================================================//
elseif($_REQUEST['act'] == 'kuangjia_buy')
{
    //必须选项
    $goods_id     = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']): 0;        //镜架
    $glasses_type = isset($_REQUEST['glasses_type'])? intval($_REQUEST['glasses_type']): 0;//镜片
    $kj_tongju    = isset($_REQUEST['kj_tongju'])? trim($_REQUEST['kj_tongju']): '';       //瞳距
    $goods_number = isset($_REQUEST['goods_number'])? intval($_REQUEST['goods_number']): 1;//数量
    $zselect      = isset($_REQUEST['zselect'])? trim($_REQUEST['zselect']): "";
    $yselect      = isset($_REQUEST['yselect'])? trim($_REQUEST['yselect']): "";

    //周年庆临时：防止用户直接在浏览器直接输入网址加入购物车
    if ($goods_id == 2664) exit;

    $zsg    = isset($_REQUEST['zsg'])? trim($_REQUEST['zsg']):"";
    $ysg    = isset($_REQUEST['ysg'])? trim($_REQUEST['ysg']):"";
    $zzhou  = isset($_REQUEST['zzhou'])? trim($_REQUEST['zzhou']):"";
    $yzhou  = isset($_REQUEST['yzhou'])? trim($_REQUEST['yzhou']):"";
    $is_sg  = ((!empty($zsg) && !empty($zzhou)) || (!empty($ysg) && !empty($yzhou)) )? 1: 0;

    //-------------------------------------------------------------------------------------------//
    //解决远视没有'+'的情况
    if(!empty($zselect) && $zselect!='平光' && $zselect>0.00){
        $zselect = '+'.trim($zselect);
    }
    if(!empty($yselect) && $yselect!='平光' && $yselect>0.00){
        $yselect = '+'.trim($yselect);
    }
    //散光片的散光度数
    if(!empty($zsg) && $zsg>0.00){
        $zsg = '+'.trim($zsg);
    }
    if(!empty($ysg) && $ysg>0.00){
        $ysg = '+'.trim($ysg);
    }
    //-------------------------------------------------------------------------------------------//

    //check data
    if(!empty($goods_id) && !empty($glasses_type) && !empty($kj_tongju))
    {
        //-----------------------------------------//
        //镜片价格
        $jp_price = 0; $jp_id = 1393;
        switch($glasses_type)
        {
            case 1:
                $jp_price = 0; $jp_id = 1393; break;
            case 2:
                $jp_price = 50; $jp_id = 1394; break;
            case 3:
                $jp_price = 100; $jp_id = 1395; break;
            case 4:
                $jp_price = 320; $jp_id = 1396; break;
            case 5:
                $jp_price = 560; $jp_id = 1397; break;
            case 6:
                $jp_price = 780; $jp_id = 1398; break;
            default:
                $jp_price = 0; $jp_id = 1393; break;
        }

        //-----------------------------------------//
        //镜架价格
        $sql = "select IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.promote_start_date, g.promote_end_date ".
            " FROM " .$GLOBALS['ecs']->table('goods'). " AS g ".
            " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
            " ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 AND g.is_on_sale=1 AND g.is_alone_sale=1 ";
        $rs = $GLOBALS['db']->GetRow($sql);
        $promote_price = 0;
        $time = time();
        if ($rs['promote_price'] > 0 && $time > $rs['promote_start_date'] && $time < $rs['promote_end_date']) {
            $promote_price = $rs['promote_price'];
        } else {
            $promote_price = 0;
        }
        $goods_price = ($promote_price > 0) ? $promote_price : $rs['shop_price'];

        //镜架加入购物车
        $cart_res1 = addto_cart_kj_wap($goods_id, $goods_number, $goods_price, 0, array(), 0, 0, '', 0, '', '', '', '', 1, '');


        //镜片加入购物车
        if($is_sg)
        {
            $sgds = '';
            if(isset($zsg) && isset($zzhou) && !empty($zzhou) && !empty($zsg)){
                $sgds .= '左眼散光:'.$zsg.'轴位:'.$zzhou;
            }
            if(isset($ysg) && isset($yzhou) && !empty($yzhou) && !empty($ysg)){
                $sgds .= '右眼散光:'.$ysg.'轴位:'.$yzhou;
            }
            $goods_attr = $sgds;

            $cart_res2 = addto_cart_kj_wap($jp_id, $goods_number, $jp_price, $goods_id, array(), 0, 0, $goods_attr, 0, $zselect, 1, $yselect, 1, 2, $kj_tongju);//框架有散光。
        }
        else
        {
            $cart_res2 = addto_cart_kj_wap($jp_id, $goods_number, $jp_price, $goods_id, array(), 0, 0, '', 0, $zselect, 1, $yselect, 1, 1, $kj_tongju);
        }

        //购物车中商品数量
        $sql = "select sum(goods_number) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '".SESS_ID."' ;";
        $cart_num = $GLOBALS['db']->GetOne($sql);

        $zres  = ($cart_res1 && $cart_res2)? 'ok_'.$cart_num: 'fail_'.$cart_num;
        echo $zres;
    }
    else
    {
        echo 'fail';
    }
}
elseif ($_REQUEST['act'] == 'add_to_cart_old')
{
	require(dirname(__FILE__) . '/includes/pf_cart.php');

	$goods_id = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']) : 0;
	$goods_type = isset($_REQUEST['goods_type'])? intval($_REQUEST['goods_type']) : 0;
	$goods_number = isset($_REQUEST['goods_number'])? intval($_REQUEST['goods_number']) : 0;
	$degree_str = isset($_REQUEST['degree_str'])? trim(addslashes($_REQUEST['degree_str'])) : '';
	$glass_type = isset($_REQUEST['glass_type'])? intval($_REQUEST['glass_type']) : 0; //框架镜片的种类

	//右眼度数|右眼数量|左眼度数|左眼数量|右眼散光|右眼轴位|左眼散光|左眼轴位|框架瞳距
	if ($goods_type == 1 OR $goods_type == 2)
	{
		//正常隐形眼镜
		$ds_arr = explode('|', $degree_str);
		/*$r_degree = ( ! empty($ds_arr[0])) ? $ds_arr[0] : '';
	    $r_number = ( ! empty($ds_arr[1])) ? intval($ds_arr[1]) : 0;
	    $l_degree = ( ! empty($ds_arr[2])) ? $ds_arr[2] : '';
	    $l_number = ( ! empty($ds_arr[3])) ? intval($ds_arr[3]) : 0;
	    $r_sg = ( ! empty($ds_arr[4])) ? $ds_arr[4] : '';
	    $r_zw = ( ! empty($ds_arr[5])) ? $ds_arr[5] : '';
	    $l_sg = ( ! empty($ds_arr[6])) ? $ds_arr[6] : '';
	    $l_zw = ( ! empty($ds_arr[7])) ? $ds_arr[7] : '';
	    $kj_tongju = ( ! empty($ds_arr[8])) ? $ds_arr[8] : '';*/
	    $r_number = ( ! empty($ds_arr[1])) ? intval($ds_arr[1]) : 0;
	    $l_number = ( ! empty($ds_arr[3])) ? intval($ds_arr[3]) : 0;
	    $goods_number = $r_number + $l_number;

	}

	if ($goods_number > 0)
	{
       add_to_cart_normal($goods_id, $goods_number, $degree_str, $glass_type);

    }
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数：添加商品到购物车【非常重要的函数】
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格
 * @param   integer $parent     基本件
 * @return  boolean             是否加入成功
 */
function addto_cart_wap($goods_id=0, $num=1, $spec=array(), $parent=0, $zselect, $zcount, $yselect, $ycount, $goods_attr_cart='')
{
    $GLOBALS['err']->clean();
    $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, g.promote_end_date, ".
        "g.goods_weight, g.integral, g.extension_code, g.goods_number, g.is_alone_sale, g.is_shipping, g.is_cx, ".
        "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
        " FROM ".$GLOBALS['ecs']->table('goods')." AS g ".
        " LEFT JOIN ".$GLOBALS['ecs']->table('member_price')." AS mp ON mp.goods_id = g.goods_id AND mp.user_rank='$_SESSION[user_rank]' ".
        " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);//取得加入购物车的商品信息

    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    //==================================如果商品作为配件添加到购物车，需先检查购物车里面是否已经有基本件=============================//
//    if($parent>0)
//    {
//        $sql = "SELECT COUNT(rec_id) FROM ".$GLOBALS['ecs']->table('cart')." WHERE goods_id='$parent' AND session_id='".SESS_ID."'";
//        if($GLOBALS['db']->getOne($sql) == 0)
//        {
//            $GLOBALS['err']->add($GLOBALS['_LANG']['no_basic_goods'], ERR_NO_BASIC_GOODS);
//            return false;
//        }
//
//        //检查该配件是否已经添加过了
//        $sql = "SELECT COUNT(rec_id) FROM ".$GLOBALS['ecs']->table('cart')." WHERE goods_id = '$goods_id' AND parent_id='$parent' AND session_id='".SESS_ID."'";
//        if($GLOBALS['db']->getOne($sql) > 0)
//        {
//            $GLOBALS['err']->add($GLOBALS['_LANG']['fitting_goods_exists'] , ERR_NOT_EXISTS);
//            return false;
//        }
//    }

    //判断商品是否正在销售
    if($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    //不是配件时检查是否允许单独销售
    if(empty($parent) && $goods['is_alone_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);
        return false;
    }

    //检查库存
    if($GLOBALS['_CFG']['use_storage'] == 1 && $num > $goods['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
        return false;
    }

    //该活动商品一人只能购买一个，不能修改数量。
    $goods_xg = array(2813,2815,2816,2817,2818,2819,994,4887,4886,4885,4893,4894,4895);
    if (in_array($goods_id, $goods_xg))
    {
        $goods['extension_code'] = 'unchange'; //限购商品：购物车不能更改数量标识
    }

    //----------------------------------------【yi:单独的专享价格】----------------------------------//
    $source    = isset($_COOKIE['source_rec_id'])? trim($_COOKIE['source_rec_id']): '';
    $new_price = true; $merge = true;//同商品进行合并
    if(!empty($source))
    {
        $sql = "select rec_id,number_limit,exclusive_price from ".$GLOBALS['ecs']->table('source')." where UNIX_TIMESTAMP() > start_time and UNIX_TIMESTAMP() < end_time and goods_id="
            .$goods_id." and rec_id in(".$source.") limit 1;";
        $source_row = $GLOBALS['db']->getRow($sql);
        if(!empty($source_row))
        {
            $goods['extension_code'] = 'source_buy';
            $goods['extension_id']   = $source_row['rec_id'] ;

            //yi:这个专享商品的购买数量限制功能
            if($source_row['number_limit']>0)
            {
                $sql = "select IFNULL(sum(goods_number),0) from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' and extension_id='$source_row[rec_id]' ";
                $hv_source = $GLOBALS['db']->getOne($sql);
                if($hv_source>=$source_row['number_limit'])
                {
                    $new_price = false; $merge = false; //这个用原价购买（剔除专享价格）
                    $goods['extension_code']  = '';
                    $goods['extension_id']    = 0;
                }
                else
                {
                    if($num>($source_row['number_limit']-$hv_source))
                    {
                        $num = intval($source_row['number_limit']-$hv_source);
                    }
                }
            }
        }
    }
    //---------------------------------------【单独的专享价格】-------------------------------------//

    //计算商品的促销价格
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info_wap($spec);
    if (!empty($goods_attr_cart)) $goods_attr = $goods_attr_cart; //如果$goods_attr_cart参数不为空，则表示本次插入是：将保存的历史数据再次插入(散光镜片)
    $goods_attr_id          = join(',', $spec);

    //yi:专享商品限制数量，不按专享价格计算。
    if(!$new_price)
    {
        $goods_price = get_final_price_old($goods_id, $num, true, $spec);
    }

    //yi:标识是否是促销商品(抢购暂时不算是促销)
    //$goods_is_cx = ($goods['promote_price']>0 && $goods['promote_start_date']<$_SERVER['REQUEST_TIME'] && $goods['promote_end_date']>$_SERVER['REQUEST_TIME'])? 1:$goods['is_cx'];
    $goods_is_cx = $goods['is_cx'];
    
    if(!empty($source_row)){//tao:专享价标识促销的优先
        $goods_is_cx = $source_row['is_cx'];
    }else{
        $goods_is_cx = $goods['is_cx'];
    }
    //初始化要插入购物车的基本件数据【非常重要】
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'extension_id'  => isset($goods['extension_id'])? intval($goods['extension_id']): 0,
        'is_gift'       => 0,
        'is_cx'         => $goods_is_cx,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS
    );

    //---------------------------------------------------------加入购物车【功能】--------------------------------------------------------------------//
    //如果加入购物车商品数量大于0，作为基本件插入购物车中
    if($num > 0)
    {
        //检查该商品是否已经存在在购物车中
        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND goods_id='$goods_id' AND parent_id=0 AND goods_attr='".get_goods_attr_info_wap($spec).
            "' AND rec_type='CART_GENERAL_GOODS' AND extension_code='$parent[extension_code]' and extension_id='$parent[extension_id]' AND is_gift=0 ";
        $row = $GLOBALS['db']->getRow($sql);

        //--------------------------------购物车中已有此商品（如度数不同情况，则再插入一条记录）-------------------------------------------
        if($row && $merge)
        {
            //----------------------------------1.没有度数情况--->只要更新商品数量------------------------------------------
            if(empty($row['zcount']) && empty($row['ycount']))
            {
                //更新购物车商品数量
                $num += $row['goods_number'];
                if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                {
                    //$goods_price = get_final_price($goods_id, $num, true, $spec);
                    //xyz:2013.01.22 where子句添加了AND extension_code=''：在更新购物车数据时，只修改正常商品(下同)
                    $sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number='$num', " .
                        " goods_price='$goods_price', zselect='$zselect', zcount='$zcount', yselect='$yselect', ycount='$ycount'".
                        " WHERE session_id='".SESS_ID."' AND goods_id='$goods_id' ".
                        " AND parent_id = 0 AND goods_attr = '".get_goods_attr_info_wap($spec)."' AND rec_type='CART_GENERAL_GOODS' AND extension_code='$parent[extension_code]' and extension_id='$parent[extension_id]' AND is_gift=0";
                    $GLOBALS['db']->query($sql);
                }
                else
                {
                    $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                    return false;
                }
            }
            else
            {
                if(($row['zselect']==$zselect && $row['yselect']==$yselect) && !empty($row['zcount']) && !empty($row['ycount']))
                {
                    //---------------------------------------2.1两个都不为空且度数相等（更新数量）------------------------------------------
                    $num += $row['goods_number'];
                    $zcount = $zcount + $row['zcount'];
                    $ycount = $ycount + $row['ycount'];

                    if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                    {
                        //$goods_price = get_final_price($goods_id, $num, true, $spec);
                        $sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number = '$num', " .
                            " goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
                            " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                            " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' " .
                            " AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='$parent[extension_code]' and extension_id='$parent[extension_id]' AND is_gift=0 AND zselect='".$zselect."' and yselect='".$yselect."' ";
                        $GLOBALS['db']->query($sql);
                    }
                    else
                    {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                        return false;
                    }
                }
                else if(!empty($zcount) && empty($ycount) && $row['zselect']==$zselect)
                {
                    //-----左眼不为空，右眼为空（左眼数量累加）------
                    $num += $row['goods_number'];
                    $zcount = $zcount + $row['zcount'];

                    if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                    {
                        //$goods_price = get_final_price($goods_id, $num, true, $spec);
                        $sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number = '$num', " .
                            " goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount'".
                            " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                            " AND parent_id = 0 AND goods_attr = '".get_goods_attr_info_wap($spec)."' ".
                            " AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='$parent[extension_code]' and extension_id='$parent[extension_id]' AND is_gift=0 AND zselect='".$zselect."';";
                        $GLOBALS['db']->query($sql);
                    }
                    else
                    {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                        return false;
                    }

                }
                else if(!empty($ycount) && empty($zcount) && $row['yselect']==$yselect)
                {
                    //-----右眼不为空，左眼为空（右眼数量累加）------
                    $num += $row['goods_number'];
                    $ycount = $ycount + $row['ycount'];

                    if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                    {
                        //$goods_price = get_final_price($goods_id, $num, true, $spec);
                        $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num', " .
                            " goods_price = '$goods_price', yselect = '$yselect', ycount = '$ycount'".
                            " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                            " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' " .
                            " AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='$parent[extension_code]' and extension_id='$parent[extension_id]' AND is_gift=0 AND yselect='".$yselect."';";
                        $GLOBALS['db']->query($sql);
                    }
                    else
                    {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                        return false;
                    }
                }
                else
                {
                    //----------------------------------3.度数不同情况（2个度数都不为空和其它的情况）-------------------------------------------
                    //$goods_price = get_final_price($goods_id, $num, true, $spec);
                    $parent['goods_price']  = max($goods_price, 0);
                    $parent['goods_number'] = $num;
                    $parent['parent_id']    = 0;
                    $parent['zselect']      = $zselect;
                    $parent['zcount']       = $zcount;
                    $parent['yselect']      = $yselect;
                    $parent['ycount']       = $ycount;
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
                }
            }
        }
        else //购物车没有此商品
        {
            //$goods_price = get_final_price($goods_id, $num, true, $spec);
            $parent['goods_price']  = max($goods_price, 0);
            $parent['goods_number'] = $num;
            $parent['parent_id']    = 0;
            $parent['zselect']      = $zselect;
            $parent['zcount']       = $zcount;
            $parent['yselect']      = $yselect;
            $parent['ycount']       = $ycount;
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }

    //把赠品删除，此功能暂时无用yi
    /*
	$sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);
	*/
    return true;
}
//散光片加入到购物车
function addto_cartsg_wap($goods_id=0, $num=1, $spec=array(), $parent=0, $zselect, $zcount, $yselect, $ycount, $zsg, $ysg, $zzhou, $yzhou)
{
    $GLOBALS['err']->clean();
    $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, g.promote_end_date, ".
        " g.goods_weight, g.integral, g.extension_code, g.goods_number, g.is_alone_sale, g.is_shipping, g.is_cx, ".
        " IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
        " FROM ".$GLOBALS['ecs']->table('goods')." AS g ".
        " LEFT JOIN ".$GLOBALS['ecs']->table('member_price')." AS mp ".
        " ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
        " WHERE g.goods_id = '$goods_id' AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }

    //如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件
    if($parent > 0)
    {
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('cart')." WHERE goods_id='$parent' AND session_id='" . SESS_ID . "'";
        if($GLOBALS['db']->getOne($sql) == 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['no_basic_goods'], ERR_NO_BASIC_GOODS);
            return false;
        }

        //检查该配件是否已经添加过了。
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('cart')." WHERE goods_id = '$goods_id' AND parent_id='$parent' AND session_id='" . SESS_ID . "'";
        if($GLOBALS['db']->getOne($sql) > 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['fitting_goods_exists'] , ERR_NOT_EXISTS);
            return false;
        }
    }

    //是否正在销售
    if($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }

    //不是配件时检查是否允许单独销售
    if(empty($parent) && $goods['is_alone_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);
        return false;
    }

    //检查库存
    if($GLOBALS['_CFG']['use_storage'] == 1 && $num > $goods['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
        return false;
    }

    //计算商品的促销价格
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info_wap($spec);
    $goods_attr_id          = join(',', $spec);

    $sgds = '';
    if(isset($zsg) && isset($zzhou) && !empty($zzhou) && $zcount>0){
        $sgds .= '左眼散光:'.$zsg.'轴位:'.$zzhou;
    }
    if(isset($ysg) && isset($yzhou) && !empty($yzhou) && $ycount>0){
        $sgds .= '右眼散光:'.$ysg.'轴位:'.$yzhou;
    }

    //yi:促销商品的判断
    $goods_is_cx = ($goods['promote_price']>0 && $goods['promote_start_date']<$_SERVER['REQUEST_TIME'] && $goods['promote_end_date']>$_SERVER['REQUEST_TIME'])? 1:$goods['is_cx'];

    //初始化要插入购物车的基本件数据
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($sgds),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'is_gift'       => 0,
        'is_cx'         => $goods_is_cx,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS
    );

    //取得该商品的基本件和该商品作为其配件的价格（条件是价格低）
    $basic_list = array();
    $sql = "SELECT parent_id, goods_price FROM " . $GLOBALS['ecs']->table('group_goods') .
        " WHERE goods_id = '$goods_id'" .
        " AND goods_price < '$goods_price'" .
        " ORDER BY goods_price";
    $res = $GLOBALS['db']->query($sql);
    while($row = $GLOBALS['db']->fetchRow($res))
    {
        $basic_list[$row['parent_id']] = $row['goods_price'];
    }

    //取得购物车中该商品每个基本件的数量
    $basic_count_list = array();
    if($basic_list)
    {
        $sql = "SELECT goods_id, SUM(goods_number) AS count FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "'" .
            " AND parent_id = 0" .
            " AND goods_id " . db_create_in(array_keys($basic_list)) .
            " GROUP BY goods_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['goods_id']] = $row['count'];
        }
    }

    //取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件
    if($basic_count_list)
    {
        $sql = "SELECT parent_id, SUM(goods_number) AS count FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "'" .
            " AND goods_id = '$goods_id'" .
            " AND parent_id " . db_create_in(array_keys($basic_count_list)) .
            " GROUP BY parent_id";
        $res = $GLOBALS['db']->query($sql);
        while($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['parent_id']] -= $row['count'];
        }
    }

    //循环插入配件
    foreach ($basic_list as $parent_id => $fitting_price)
    {
        //如果已全部插入，退出
        if($num <= 0)
        {
            break;
        }

        //如果该基本件不再购物车中，执行下一个
        if (!isset($basic_count_list[$parent_id]))
        {
            continue;
        }

        //如果该基本件的配件数量已满，执行下一个基本件
        if ($basic_count_list[$parent_id] <= 0)
        {
            continue;
        }

        //作为该基本件的配件插入
        $parent['goods_price']  = max($fitting_price, 0) + $spec_price;
        $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
        $parent['parent_id']    = $parent_id;
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');

        //改变数量
        $num -= $parent['goods_number'];
    }
    //---------------------------------------------------------加入购物车--------------------------------------------------------------------
    /* 如果数量不为0，作为基本件插入 */
    if($num > 0)
    {
        /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('cart')." WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0";
        $row = $GLOBALS['db']->getRow($sql);

        //--------------------------------购物车中有此商品---度数不同情况，是再插入一条记录-------------------------------------------
        if($row)
        {
            //----------------------------------1.没有度数情况--->就只要更新数量------------------------------------------
            if( empty($row['zcount']) && empty($row['ycount']) ){    //0,'',null
                //更新购物车商品数量
                $num += $row['goods_number'];
                if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                {
                    $goods_price = get_final_price($goods_id, $num, true, $spec);
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
                        " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
                        " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                        "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' " .
                        "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0";
                    $GLOBALS['db']->query($sql);
                }
                else
                {
                    $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                    return false;
                }
            }else{//---------------------------------------左右眼有一个不为空||两个都不为空---------------------------------------

                if(($row['zselect']==$zselect && $row['yselect']==$yselect) && !empty($row['zcount']) && !empty($row['ycount']) ){
                    //---------------------------------------2.1两个都不为空且度数相等------------------------------------------------
                    $num += $row['goods_number'];
                    //左右眼数量累加
                    $zcount = $zcount + $row['zcount'];
                    $ycount = $ycount + $row['ycount'];

                    if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                    {
                        $goods_price = get_final_price($goods_id, $num, true, $spec);
                        $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
                            " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount', yselect = '$yselect', ycount = '$ycount'".
                            " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' " .
                            "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0 and zselect=".$zselect." and yselect=".$yselect." ";
                        $GLOBALS['db']->query($sql);
                    }
                    else
                    {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                        return false;
                    }
                }else if(!empty($zcount) && empty($ycount) && $row['zselect']==$zselect){//-----左眼不为空，右眼为空------
                    $num += $row['goods_number'];
                    //左眼数量累加
                    $zcount = $zcount + $row['zcount'];

                    if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                    {
                        $goods_price = get_final_price($goods_id, $num, true, $spec);
                        $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
                            " , goods_price = '$goods_price', zselect = '$zselect', zcount = '$zcount'".
                            " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' " .
                            "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0 and zselect=".$zselect." ";
                        $GLOBALS['db']->query($sql);
                    }
                    else
                    {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                        return false;
                    }

                }else if(!empty($ycount) && empty($zcount) && $row['yselect']==$yselect){//-----右眼不为空，左眼为空------
                    $num += $row['goods_number'];
                    //右眼数量累加
                    $ycount = $ycount + $row['ycount'];

                    if($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods['goods_number'])
                    {
                        $goods_price = get_final_price($goods_id, $num, true, $spec);
                        $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_number = '$num'" .
                            " , goods_price = '$goods_price', yselect = '$yselect', ycount = '$ycount'".
                            " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info_wap($spec). "' " .
                            "AND rec_type = 'CART_GENERAL_GOODS' AND extension_code='' AND is_gift=0 and yselect=".$yselect." ";
                        $GLOBALS['db']->query($sql);
                    }
                    else
                    {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                        return false;
                    }
                }else{
                    //----------------------------------3.度数不同情况-----------------------------------------------------------
                    $goods_price = get_final_price($goods_id, $num, true, $spec);
                    $parent['goods_price']  = max($goods_price, 0);
                    $parent['goods_number'] = $num;
                    $parent['parent_id']    = 0;
                    $parent['zselect']      = $zselect;
                    $parent['zcount']       = $zcount;
                    $parent['yselect']      = $yselect;
                    $parent['ycount']       = $ycount;
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
                }
            }
        }
        else //购物车没有此商品
        {
            $goods_price = get_final_price($goods_id, $num, true, $spec);
            $parent['goods_price']  = max($goods_price, 0);
            $parent['goods_number'] = $num;
            $parent['parent_id']    = 0;
            $parent['zselect']      = $zselect;
            $parent['zcount']       = $zcount;
            $parent['yselect']      = $yselect;
            $parent['ycount']       = $ycount;
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
        }
    }
    return true;
}
/**
 * 获得指定的商品属性
 *
 * @access  public
 * @param   array   $arr
 * @return  string
 */
function get_goods_attr_info_wap($arr)
{
    $attr   = '';

    if (!empty($arr))
    {
        $fmt = "%s:%s[%s] \n";

        $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price ".
            "FROM ".$GLOBALS['ecs']->table('goods_attr')." AS ga, ".
            $GLOBALS['ecs']->table('attribute')." AS a ".
            "WHERE " .db_create_in($arr, 'ga.goods_attr_id')." AND a.attr_id = ga.attr_id";
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $attr_price = round(floatval($row['attr_price']), 2);
            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
        }

        $attr = str_replace('[0]', '', $attr);
    }

    return $attr;
}
/* ----------------------------------------------------------------------------------------------------------------------
 * yi:重写加入购物车【框架眼镜加入购物车】
 * ----------------------------------------------------------------------------------------------------------------------
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格属性
 * @param   integer $is_cx      是否促销
 * @param   integer $price      商品单价
 * @type    类型：1.镜架， 2.镜片。
 * @return  boolean             是否加入成功
 */
function addto_cart_kj_wap($goods_id=0, $num=1, $price=0, $parent_id=0, $spec=array(), $is_cx=0, $rec_type=0, $extension_code='', $extension_id=0, $zselect, $zcount, $yselect, $ycount, $type=1, $kj_tongju='')
{
    $sql   = "select * from ".$GLOBALS['ecs']->table('goods')." where goods_id='$goods_id' and is_on_sale=1 and is_alone_sale=1 and is_delete=0 and goods_number>0 limit 1";
    $goods = $GLOBALS['db']->getRow($sql);

    if(empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);
        return false;//商品不存在。
    }

    //商品加入购物车的价格
    if(empty($price) && !empty($goods['shop_price']))
    {
        $price = floatval($goods['shop_price']);
    }

    //计算商品的促销价格
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info_wap($spec);
    $goods_attr_id          = join(',', $spec);

    if($type==1)
    {
        if($goods_id==1389){
            $sql = "SELECT IFNULL(sum(goods_number),0) FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id='".SESS_ID."' AND goods_id=1389 and extension_code='source_buy' limit 1;";
            if($GLOBALS['db']->getOne($sql) == 0)
            {
                $price = $goods_price;
                $goods['extension_code'] = 'source_buy';
                $extension_id            = 156;
            }
        }else{
            //----------------------------------------【yi:单独的专享价格】----------------------------------//
            $source    = isset($_COOKIE['source_rec_id'])? trim($_COOKIE['source_rec_id']): '';
            $new_price = true;   //是否启用专享价
            if(!empty($source))
            {
                $sql = "select rec_id,number_limit,exclusive_price,is_cx from ".$GLOBALS['ecs']->table('source')." where UNIX_TIMESTAMP() > start_time and UNIX_TIMESTAMP() < end_time and goods_id="
                    .$goods_id." and rec_id in(".$source.") limit 1;";
                $source_row = $GLOBALS['db']->getRow($sql);
                if(!empty($source_row))
                {
                    $goods['extension_code'] = 'source_buy';
                    $extension_id            = $source_row['rec_id'] ;

                    //yi:这个专享商品的购买数量限制功能
                    if($source_row['number_limit']>0)
                    {
                        $sql = "select IFNULL(sum(goods_number),0) from ecs_cart where session_id='".SESS_ID."' and extension_code='source_buy' and extension_id='$source_row[rec_id]' ";
                        $hv_source = $GLOBALS['db']->getOne($sql);
                        if($hv_source>=$source_row['number_limit'])
                        {
                            $new_price = false;  //这个用原价购买（剔除专享价格）
                            $goods['extension_code']  = '';
                            $extension_id             = 0;
                        }
                        else
                        {
                            if($num>($source_row['number_limit']-$hv_source))
                            {
                                $num = intval($source_row['number_limit']-$hv_source);
                            }
                        }
                    }
                }
            }
            //---------------------------------------【单独的专享价格】-------------------------------------//
            //yi:专享商品限制数量，不按专享价格计算。
            if(!$new_price)
            {
                $price = get_final_price_old($goods_id, $num, true, $spec);
            }else{
                $price = get_final_price($goods_id, $num, true, $spec);
            }

        }
    }

    //框架有散光
    if($type == 2)
    {
        $goods_attr     = trim($extension_code);
        $extension_code = '';
    }

    //初始化要插入购物车的基本件数据【非常重要】
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'extension_id'  => $extension_id,
        'is_cx'         => $is_cx,
        'is_kj'         => 1,
        'ds_extention'  => $kj_tongju,
        'parent_id'     => $parent_id,
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => CART_GENERAL_GOODS
    );


    //在购物车中重新插入一个商品（这个商品不能再购物车中修改数量）
    $parent['goods_price']  = max($price, 0);
    $parent['goods_number'] = $num;
    $parent['extension_code'] = (!empty($extension_code))? trim($extension_code): $parent['extension_code'];
    $parent['zselect']      = $zselect;
    $parent['zcount']       = $zcount;
    $parent['yselect']      = $yselect;
    $parent['ycount']       = $ycount;

    $sql_res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
    return ($sql_res)? true: false;
}
