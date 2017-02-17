<?php
/**
 * 公共函数
 * @version 2014
 * @author xuyizhi
 */
if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 根据栏目名称获取栏目ID
 * @param String $cate_name
 */
function get_cat_id_by_name($cate_name='')
{
	if ( ! empty($cate_name))
	{
		return $GLOBALS['db']->GetOne("SELECT cat_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_name = '".addslashes($cate_name)."' LIMIT 1");
	}
	else
	{
		return 0;
	}
}

/**
 * 根据栏目ID获取栏目信息
 * @param int $cat_id
 */
function get_category_info_by_id($cat_id=0)
{
	if( ! empty($cat_id))
	{
		return $GLOBALS['db']->GetRow("SELECT * FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_id = ".intval($cat_id)." LIMIT 1");
	}
}

/**
 * 获取父目录ID
 * @param int $cat_id
 */
function get_parent_category_id($cat_id=0)
{
	if( ! empty($cat_id))
	{
		$p_id = $GLOBALS['db']->GetOne("SELECT parent_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE cat_id = ".intval($cat_id)." LIMIT 1");
		if (empty($p_id)) return $cat_id;
		else return $p_id;
	}
	else 
	{
		return 0;
	}
}

/**
 * 根据父目录ID返回子目录ID和名称数组
 * @param int $parent_id
 */
function get_subdirectory($parent_id=1)
{
	if ( ! empty($parent_id))
	{
		return $GLOBALS['db']->GetAll("SELECT cat_id, cat_name FROM " . $GLOBALS['ecs']->table('category') . " WHERE parent_id=" . $parent_id . " AND is_show=1");
	}
}

/**
 * 根据父目录ID返回子目录ID数组
 * @param int $parent_id
 */
function get_cat_id_by_parent($parent_id=1)
{
	if ( ! empty($parent_id))
	{
		$rs = $GLOBALS['db']->GetAll("SELECT cat_id FROM " . $GLOBALS['ecs']->table('category') . " WHERE parent_id=" . $parent_id . " AND is_show=1");
		$cate_array = array();
		foreach ($rs as $v)
		{
			$cate_array[] = $v['cat_id'];
		}
		return $cate_array;
	}
}

/**
 * 根据大类ID获取颜色属性值
 * @param int $parent_id
 */
function get_color_attr($parent_id=6)
{
	if ( ! empty($parent_id))
	{
		$sql = '';
		if ($parent_id == 6)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 212";
		}
		elseif ($parent_id == 159)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 248";
		}
		elseif ($parent_id == 190)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 262";
		}
		
		if ( ! empty($sql))
		{
			$str = $GLOBALS['db']->GetOne($sql);
			return explode("\n", $str);
		}
	}
}

/**
 * 根据大类ID获取材质属性值
 * @param int $parent_id
 */
function get_cz_attr($parent_id=159)
{
	if ( ! empty($parent_id))
	{
		$sql = '';
		if ($parent_id == 159)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 247";
		}
		elseif ($parent_id == 190)
		{
			$sql = "SELECT attr_values FROM " .$GLOBALS['ecs']->table('attribute'). " WHERE attr_id = 261";
		}
		
		if ( ! empty($sql))
		{
			$str = $GLOBALS['db']->GetOne($sql);
			return explode("\n", $str);
		}
	}
}

/**
 * 根据属性值获取商品ID
 * @param String $attr_value 属性值
 * @param String $attr_type 属性类别
 * @param int $parent_id 大类id
 * @param String $str 保留参数
 */
function get_goods_by_attr($attr_value='', $attr_type='zq', $parent_id=1, $str='')
{
	$rs = array();
	$attr_value = trim($attr_value);
	
	//周期
	if ($attr_type == 'zq')
	{
		if ($parent_id == 1)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 219 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 6) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 211 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//含水量
	if ($attr_type == 'hsl')
	{
		$temp_sql = '';
		if ($parent_id == 1)
		{
			if ($attr_value == '37%以下(低含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 221 AND (attr_value='24%' OR attr_value='33%' OR attr_value='36%')";
			} elseif ($attr_value == '38%～49%(中含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 221 AND (attr_value='38%' OR attr_value='39%' OR attr_value='40%' OR attr_value='42%' OR attr_value='43%' OR attr_value='45%' OR attr_value='47%' OR attr_value='48%')";
			} elseif ($attr_value == '50%～58%(高含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 221 AND (attr_value='52%' OR attr_value='55%' OR attr_value='58%')";
			} elseif ($attr_value == '59%以上(超高含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 221 AND (attr_value='59%' OR attr_value='60%' OR attr_value='66%' OR attr_value='69%')";
			}
		}
		elseif ($parent_id == 6) 
		{
			if ($attr_value == '37%以下(低含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 213 AND (attr_value='24%' OR attr_value='33%' OR attr_value='36%' OR attr_value='37%')";
			} elseif ($attr_value == '38%～49%(中含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 213 AND (attr_value='38%' OR attr_value='39%' OR attr_value='40%' OR attr_value='42%' OR attr_value='45%' OR attr_value='47%')";
			} elseif ($attr_value == '50%～58%(高含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 213 AND (attr_value='52%' OR attr_value='55%' OR attr_value='58%')";
			} elseif ($attr_value == '59%以上(超高含水量)') {
				$temp_sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 213 AND (attr_value='59%' OR attr_value='60%' OR attr_value='66%' OR attr_value='69%')";
			}
		}
		$rs = $GLOBALS['db']->GetAll($temp_sql);
	}
	
	//直径
	if ($attr_type == 'zj')
	{
		if ($parent_id == 1)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 222 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 6) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 214 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//基弧
	if ($attr_type == 'jh')
	{
		if ($parent_id == 1)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 223 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 6) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 215 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//颜色
	if ($attr_type == 'color')
	{
		if ($parent_id == 6)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 212 AND attr_value='" . $attr_value . "'");
		}
		elseif ($parent_id == 159)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 248 AND attr_value='" . $attr_value . "'");
		}
		elseif ($parent_id == 190)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 262 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//价格
	if ($attr_type == 'price')
	{
		if ($parent_id == 1 OR $parent_id == 6)
		{
			//隐形眼镜
			if ($attr_value == '50元以下') 	{
				return ' AND shop_price < 50 ';
			} elseif ($attr_value == '50元～99元') {
				return ' AND shop_price >= 50 AND shop_price <= 99 ';
			} elseif ($attr_value == '100元～149元') {
				return ' AND shop_price >= 100 AND shop_price <= 149 ';
			} elseif ($attr_value == '150元～199元') {
				return ' AND shop_price >= 150 AND shop_price <= 199 ';
			} elseif ($attr_value == '200元～299元') {
				return ' AND shop_price >= 200 AND shop_price <= 299 ';
			} elseif ($attr_value == '300元及以上') {
				return ' AND shop_price >= 300 ';
			} else {
				return ' AND shop_price > 0';
			}
		}
		elseif ($parent_id == 64)
		{
			//护理液
			if ($attr_value == '15元及以下') 	{
				return ' AND shop_price <= 15 ';
			} elseif ($attr_value == '16元～30元') {
				return ' AND shop_price >= 16 AND shop_price <= 30 ';
			} elseif ($attr_value == '31元～50元') {
				return ' AND shop_price >= 31 AND shop_price <= 50 ';
			} elseif ($attr_value == '51元及以上') {
				return ' AND shop_price >= 51 ';
			} else {
				return ' AND shop_price > 0';
			}
		}
		elseif ($parent_id == 76)
		{
			//护理工具
			if ($attr_value == '5元及以下') 	{
				return ' AND shop_price <= 5 ';
			} elseif ($attr_value == '6元～10元') {
				return ' AND shop_price >= 6 AND shop_price <= 10 ';
			} elseif ($attr_value == '11元～20元') {
				return ' AND shop_price >= 11 AND shop_price <= 20 ';
			} elseif ($attr_value == '21元～50元') {
				return ' AND shop_price >= 21 AND shop_price <= 50 ';
			} elseif ($attr_value == '51元及以上') {
				return ' AND shop_price >= 51 ';
			} else {
				return ' AND shop_price > 0';
			}
		}
		elseif ($parent_id == 159 OR $parent_id == 190)
		{
			//框架,太阳眼镜
			if ($attr_value == '100元以下') 	{
				return ' AND shop_price < 100 ';
			} elseif ($attr_value == '100元～199元') {
				return ' AND shop_price >= 100 AND shop_price <= 199 ';
			} elseif ($attr_value == '200元～299元') {
				return ' AND shop_price >= 200 AND shop_price <= 299 ';
			} elseif ($attr_value == '300元～499元') {
				return ' AND shop_price >= 300 AND shop_price <= 499 ';
			} elseif ($attr_value == '500元～999元') {
				return ' AND shop_price >= 500 AND shop_price <= 999 ';
			} elseif ($attr_value == '1000元及以上') {
				return ' AND shop_price >= 1000 ';
			} else {
				return ' AND shop_price > 0';
			}
		}
	}
	
	//护理液：功能
	if ($attr_type == 'gn')
	{
		$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 256 AND attr_value='" . $attr_value . "'");
	}
	
	//护理液：规格
	if ($attr_type == 'gg')
	{
		$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 257 AND attr_value='" . $attr_value . "'");
	}
	
	//护理工具：类型
	if ($attr_type == 'lx')
	{
		$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 255 AND attr_value='" . $attr_value . "'");
	}
	
	//框架,太阳镜：款式
	if ($attr_type == 'ks')
	{
		if ($parent_id == 159)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 244 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 190) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 258 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//框架,太阳镜：框型
	if ($attr_type == 'kx')
	{
		if ($parent_id == 159)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 245 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 190) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 259 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//框架,太阳镜：尺码
	if ($attr_type == 'cm')
	{
		if ($parent_id == 159)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 246 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 190) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 260 AND attr_value='" . $attr_value . "'");
		}
	}
	
	//框架,太阳镜：材质
	if ($attr_type == 'cz')
	{
		if ($parent_id == 159)
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 247 AND attr_value='" . $attr_value . "'");
		} 
		elseif ($parent_id == 190) 
		{
			$rs = $GLOBALS['db']->GetAll("SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE attr_id = 261 AND attr_value='" . $attr_value . "'");
		}
	}
	
	
	//返回商品ID字符串
	if ($rs)
	{
		$temp_array = array();
		foreach ($rs as $v)
		{
			$temp_array[] = $v['goods_id'];
		}
		return ' AND goods_id IN (' . implode(',', $temp_array) . ') ';
	}
	else
	{
		return ' AND goods_id IN (0) ';
	}
	
}
