<?php
/* 
 * 买家秀首页
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

/*------------------------------------页头 页尾 数据---------------------------------------*/
$position = assign_ur_here();
$smarty->assign('page_title',          '买家秀-美瞳隐形眼镜佩戴效果图-易视网');    
$smarty->assign('ur_here',             '买家秀');  


/*------------------------------------页头 页尾 数据end------------------------------------*/

if($_SESSION['user_id'] > 0) {
	$smarty->assign('user_id', $_SESSION['user_id']);
}

//按颜色查找
$sqlys="SELECT attr_id,attr_values FROM " . $GLOBALS['ecs']->table('attribute') . " where attr_id=212";
$resys = $db->query($sqlys);
$listys = array();
while ($rowys = $db->fetchRow($resys))
{
	$rowys['attr_values']      = str_replace("\n", ", ", $rowys['attr_values']);
	$rowys['attr_values_list']=explode(", ",$rowys['attr_values']);
	$listys[] = $rowys;
}

//----------------mjx分页-----------------------
$where="";

@$cat_id=$_REQUEST['cat_id'];//按眼镜品牌找
if($cat_id) {
	$where .= " and a.cat_id='".$cat_id."'";
}

@$attr=$_REQUEST['attr'];
if($attr) {$where .= " and a.attr='".$attr."'";}

@$ef = $_REQUEST['ef'];//按佩戴效果找
if ($ef) {
	if ($ef == '1') {
		$where .= " AND (a.attr='1' OR a.attr='2')";
	} elseif ($ef == '2') {
		$where .= " AND a.attr='3'";
	} elseif ($ef == '3') {
		$where .= " AND (a.attr='4' OR a.attr='5' OR a.attr='6' OR a.attr='7')";
	}
}
 
$sqlmjx="SELECT a.user_id,a.id mjxid,a.title,a.img,a.thumb_img,a.index_img, a.attr, a.detail,a.goods_id,a.detail, FROM_UNIXTIME(a.datetime, '%Y-%m-%d') as datetime,a.vote,a.effect,a.comments,a.upload_type,b.user_name FROM " . $GLOBALS['ecs']->table('mjx') . " a," . $GLOBALS['ecs']->table('users') . " b where 1=1 and a.sh=1 and a.user_id=b.user_id".$where." order by a.id desc ";

$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$size = 60;
$sqlcount = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('mjx') . " a," . $GLOBALS['ecs']->table('users') . " b where 1=1 and a.sh=1 and a.user_id=b.user_id".$where;
$count = $GLOBALS['db']->getOne($sqlcount);

$pages  = ($count > 0) ? ceil($count / $size) : 1;
if ($page > $pages)
{
	$page = $pages;
}
		
//$resmjx = $GLOBALS['db']->selectLimit($sqlmjx, $size, ($page-1) * $size);
$resmjx = $GLOBALS['db']->selectLimit($sqlmjx, 10, ($page-1) * $size); //注:虽然定义每页显示60条,但每次默认读取20条,其余通过buyersshow_json.php读取

$listmjx = array();
while ($rowmjx = $db->fetchRow($resmjx))
{	
	$sqlhsx = 'SELECT goods_name from  '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('mjx').' m where g.goods_id='.($rowmjx['goods_id']+0).' and g.goods_id= m.goods_id;';

	$hscountxd = $GLOBALS['db']->getOne($sqlhsx);	 
	if(trim($hscountxd)){$rowmjx['title']=$hscountxd;}
	
	$rowmjx['user_comments'] = get_mjx_comments($rowmjx['mjxid']); //获取评论
	
	//获取图片高度(宽度是218px,根据宽度,按照百分比获取)
	if (file_exists('http://www.easeeyes.com/'.$rowmjx['img'])) {
		$imginfo = getimagesize($rowmjx['img']);
	}
	if (@$imginfo) {
		$img_width = $imginfo[0];
		$percent = 218 / $img_width;
		$rowmjx['img_height'] = floor($imginfo[1] * $percent);
	} else {
		$rowmjx['img_height'] = 218;
	}
	
	if (!file_exists($rowmjx['index_img'])) {
		$rowmjx['index_img'] = '';
	}
    //if(file_exists('http://www.easeeyes.com/'.$rowmjx['img'])||file_exists('http://www.easeeyes.com/'.$rowmjx['thumb_img'])){
       $listmjx[] = $rowmjx;
    //}
}
//----------------mjx分页-----------------------

$pager['search']['cat_id'] = $cat_id;
$pager['search']['attr'] = $attr;
$pager = get_pager('buyersshow.php', $pager['search'], $count, $page, $size);

$smarty->assign('pager',		$pager);					//分页信息
$smarty->assign('categoriesp',	get_categories_treepp());	//分类品牌树
$smarty->assign('listmjx',		$listmjx);					//买家秀信息列表
$smarty->assign('listys',		$listys);					//所有颜色属性值

$page_array = array();

$smarty->assign('page_array',   $page_array);
$smarty->assign('page',   $page);
$smarty->assign('total_page',   $pages);
if ($page > 1) $smarty->assign('prePage',   $page-1);
if ($page < $pages) $smarty->assign('nextPage',   $page+1);


$smarty->display('buyersshow.dwt');


//==================================================================【函数】==================================================================//
//获得品牌分类树
function get_categories_treepp($cat_id = 0)
{
    if($cat_id > 0){
        $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";
        $parent_id = $GLOBALS['db']->getOne($sql);
    }else{
        $parent_id = 0;
    }

    /*
     判断当前分类中全是是否是底级分类，
     如果是取出底级分类上级分类，
     如果不是取当前分类及其下的子分类
    */
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . " WHERE  cat_id=6 and parent_id = '$parent_id' AND is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql) || $parent_id == 0)
    {	
        /* 获取当前分类及其子分类 */
        $sql = 'SELECT cat_id,cat_name ,parent_id,is_show ' .
                'FROM ' . $GLOBALS['ecs']->table('category') .
                "WHERE parent_id = '$parent_id'  AND cat_id<>80 AND  cat_id=6 and is_show = 1 ORDER BY sort_order ASC, cat_id ASC";

        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res AS $row)
        {
            if ($row['is_show'])
            {
                $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
                $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                $cat_arr[$row['cat_id']]['url']  = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);

                if (isset($row['cat_id']) != NULL)
                {
                    $cat_arr[$row['cat_id']]['cat_id'] = get_child_tree($row['cat_id']);
                }
            }
        }
    }
    if(isset($cat_arr))
    {		
        return $cat_arr;
    }
}

//获取该买家秀的评论信息
function get_mjx_comments($mjxid=0) {
	$comments_array = array();
	if ($mjxid) {
		$sql = 'SELECT user_id_commentator, user_name_commentator, comment FROM  '.$GLOBALS['ecs']->table('mjx_comment'). ' WHERE mjx_id= '.$mjxid.' AND user_id_commentator>0 ORDER BY id DESC LIMIT 2';
		$res = $GLOBALS['db']->getAll($sql);
		if ($res) {
			foreach ($res AS $row) {
				$comments_array[] = array (
						'user_id_commentator'		=>	$row['user_id_commentator'],
						'user_name_commentator'		=>	stripslashes($row['user_name_commentator']),
						'comment'	=>	stripslashes($row['comment'])
				);
			}
		}
	}
	return $comments_array;
}
