<?php

/**
 * ECSHOP 文章及文章分类相关函数库
 * ============================================================================
 * 版权所有 2005-2009 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: lib_article.php 16881 2009-12-14 09:19:16Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 获得文章分类下的文章列表
 *
 * @access  public
 * @param   integer     $cat_id
 * @param   integer     $page
 * @param   integer     $size
 *
 * @return  array
 */
function get_cat_articles($cat_id, $page = 1, $size = 20 ,$requirement='')
{
    //取出所有非0的文章
    if ($cat_id == '-1')
    {
        $cat_str = 'cat_id > 0';
    }
    else
    {
        $cat_str = get_article_children($cat_id);
    }
    //增加搜索条件，如果有搜索内容就进行搜索 ----Yi--增加了列表的内容---
    if ($requirement != '')
    {
        $sql = 'SELECT article_id, title, author, add_time, file_url, open_type, content' .
               ' FROM ' .$GLOBALS['ecs']->table('article') .
               ' WHERE is_open = 1 and is_hide=0 AND ' . $cat_str . ' AND  title like \'%' . $requirement . '%\' ' .
               ' ORDER BY article_type DESC, article_id DESC';
    }
    else
    {

        $sql = 'SELECT article_id, title, author, add_time, file_url, open_type, content' .
               ' FROM ' .$GLOBALS['ecs']->table('article') .
               ' WHERE is_open = 1 and is_hide=0 AND ' . $cat_str .
               ' ORDER BY article_type DESC, article_id DESC';
    }


    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);

    $arr = array();
    if ($res)
    { 
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $article_id = $row['article_id'];

            $arr[$article_id]['id']          = $article_id;
            $arr[$article_id]['title']       = $row['title'];
            $arr[$article_id]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
            $arr[$article_id]['author']      = empty($row['author']) || $row['author'] == '_SHOPHELP' ? $GLOBALS['_CFG']['shop_name'] : $row['author'];
            //$arr[$article_id]['url']         = $row['open_type'] != 1 ? build_uri('article', array('aid'=>$article_id), $row['title']) : trim($row['file_url']);
            $arr[$article_id]['url']         = 'article-'.$article_id.'.html';
            $arr[$article_id]['add_time']    = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
            $arr[$article_id]['content']     = delhtml($row['content']);
            $arr[$article_id]['short_content']     = mb_substr(strip_tags($row['content']) ,0,100,'utf-8');
        }
    }

    return $arr;
}

//yi-------------------------------去除html中空白字符----------------------
function delhtml($str){
	$str = trim($str);
	$str = str_replace("\t","",$str);
	$str = str_replace("\n","",$str);
	$str = str_replace("&nbsp;","",$str);
	$str = str_replace("<br />","",$str);
	$str = str_replace("color","color1",$str);
	$str = str_replace("<p","<span",$str);
	$str = str_replace("</p>","</span>",$str);
	$str = str_replace("<div","<span",$str);
	$str = str_replace("</div>","</span>",$str);
	$str = str_replace("<h1","<span",$str);
	$str = str_replace("</h1>","</span>",$str);
	$str = str_replace("<strong","<span",$str);
	$str = str_replace("</strong>","</span>",$str);
	$str = str_replace("<img","<img style='display:none;'",$str);
	$str = str_replace("font-size:","font-size1:",$str);
	return $str;
}
//yi-------------------------------去除html中空白字符----------------------

/**
 * 获得指定分类下的文章总数
 *
 * @param   integer     $cat_id
 *
 * @return  integer
 */
function get_article_count($cat_id ,$requirement='')
{
    global $db, $ecs;
    if ($requirement != '')
    {
        $count = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('article') . ' WHERE ' . get_article_children($cat_id) . ' AND  title like \'%' . $requirement . '%\'  AND is_open = 1 and is_hide=0 ');
    }
    else
    {
        $count = $db->getOne("SELECT COUNT(*) FROM " . $ecs->table('article') . " WHERE " . get_article_children($cat_id) . " AND is_open = 1 and is_hide=0 ");
    }
    return $count;
}

?>
