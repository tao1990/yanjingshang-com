<?php
/**
 * ajax：商品相关数据
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('PRC');
//ini_set("display_errors", "On");
//error_reporting(E_ALL);
$user_id  = isset($_SESSION['user_id'])? intval($_SESSION['user_id']): 0;

/**
 * 栏目页获取商品列表数据
 */
if($_REQUEST['act'] == 'category_goods')
{
	require(dirname(__FILE__) . '/includes/pf_public.php');
	
	$parent_id = isset($_REQUEST['parent_id'])? intval($_REQUEST['parent_id']): 1;
	$params = isset($_REQUEST['params'])? trim(addslashes($_REQUEST['params'])): '';
    $getParams = isset($_REQUEST['getParams'])? trim(addslashes($_REQUEST['getParams'])): '';
	$page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
	$order_by = isset($_REQUEST['order_by'])? trim(addslashes($_REQUEST['order_by'])): '';
	
	$p_array = explode("||", $params);
    $p_array_url = explode("||", $getParams);
	
	$where = ' WHERE 1=1 ';
	
    $where .= get_p_array($p_array,$parent_id);
    
	//$where .= get_p_array($p_array_url,$parent_id);
    
    //14. 搜索条件
    if ( $_REQUEST['search'] ==1){
        $keyword = cleanInput($_REQUEST['keyword']);
            /*if($fattrnamestr)
        	{
        		$fattrnamestr = fattrname($attr5).">".$keyword;
        	}
        	else
        	{
        		$fattrnamestr = $keyword;
        	}*/
        	$keyword = trim($keyword);
        	$key_arr = explode(' ', $keyword);
        	if(count($key_arr)>=1)	
        	{
        		foreach($key_arr as $k => $v)
        		{
        			if(!empty($key_arr[$k]))
        			{
        				$where .= " AND g.goods_name like '%".$key_arr[$k]."%' ";
        			}
        		}
        	}
            
        $where .= " AND g.goods_name LIKE '%$keyword%' ";
    }
	
	$where .= ' AND b.b2b_is_on_sale = 1  ';

	//每页记录数,总记录数
	$size = 20;
	//$total_rows  = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods') . $where);
	$total_rows = $GLOBALS['db']->getOne("SELECT count(*) FROM b2b_goods AS b LEFT JOIN " .$GLOBALS['ecs']->table('goods').
     " AS g ON b.goods_id = g.goods_id " .$where);
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
	$order_sql = ' ORDER BY b.b2b_sort_order ASC, g.goods_id DESC ';
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
	//$sql = "SELECT * FROM " .$GLOBALS['ecs']->table('goods'). $where . $order_sql . $limit_sql;
   
    $sql = "SELECT * FROM b2b_goods AS b LEFT JOIN " .$GLOBALS['ecs']->table('goods'). " AS g ON b.goods_id = g.goods_id ". $where . $order_sql . $limit_sql;
    
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
        $goods_list[$k]['goods_thumb'] = $v['b2b_goods_thumb'];
		$goods_list[$k]['market_price'] = $v['market_price'];
		//$goods_list[$k]['shop_price'] = ($promote_price > 0)? $promote_price: $v['b2b_shop_price'];
        $goods_list[$k]['shop_price'] = $v['b2b_shop_price'];
        
		//$goods_list[$k]['goods_img_300'] = $v['original_img'];
		$goods_list[$k]['click_count'] = $v['click_count'];
        $goods_list[$k]['goods_ds'] = get_goods_ds($v['goods_id']);

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

	//$smarty->assign('sql',			$sql); //打印sql：测试用
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

//更改商品购买数量(返回对应价格 只限批发商品)
elseif ($_REQUEST['act'] == 'change_buy_num')
{
	$goods_id  = isset($_REQUEST['goods_id'])? intval($_REQUEST['goods_id']) : 0;
	$num       = isset($_REQUEST['num'])? intval($_REQUEST['num']) : 0;
    $goods   = get_goods_info($goods_id);  //商品详细信息	

    if($goods['is_wholesale'] == 1){//批发价格
        $wholesale =$GLOBALS['db']->getRow("SELECT * FROM ecs_wholesale WHERE goods_id = ".$goods_id);
        $rank_ids = explode(',',$wholesale['rank_ids']);
        
        /* 删除批发价格针对于会员等级的判断
        if(in_array($_SESSION['user_rank'],$rank_ids)){
            $wholesale_arr = unserialize($wholesale['prices']);
            $wholesale_arr = $wholesale_arr[0]['qp_list'];
        }*/
        $wholesale_arr = unserialize($wholesale['prices']);
        $wholesale_arr = $wholesale_arr[0]['qp_list'];
        
        foreach($wholesale_arr as $k =>$v)
        {
            if($num >= $v['quantity']){
                $res = $v['price']*$num;
            }
        }
        if(!$res){
            $res = $wholesale_arr[0]['price']*$num;
        }
    }
    echo $res;die;
}

/**
 * 获取拼接sql by:TAO
 * @return $where;
 */
function get_p_array($p_array,$parent_id){
    
    $where = '';
    //0. 子栏目id
	if (empty($p_array[0]))
	{
		$children_cat_array = get_cat_id_by_parent($parent_id);
        if($children_cat_array){
            $children_cat_str = implode(',', $children_cat_array);
        }
        
		if ($children_cat_str) {
			$where .= ' AND g.cat_id IN (' . $children_cat_str .') ';
		} else {
			$where .= ' AND g.cat_id != 138 ';
		}
        
	}
	else
	{
		//根据传过来的栏目中文名查询栏目id
		$cat_id = get_cat_id_by_name($p_array[0],$parent_id);
		if ($cat_id)
		{
			$where .= ' AND g.cat_id = '.$cat_id;
		}
		else
		{
			$children_cat_array = get_cat_id_by_parent($parent_id);
			$children_cat_str = implode(',', $children_cat_array);
			if ($children_cat_str) {
				$where .= ' AND g.cat_id IN (' . $children_cat_str .') ';
			} else {
				$where .= ' AND g.cat_id != 138 ';
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
    return $where;
}

