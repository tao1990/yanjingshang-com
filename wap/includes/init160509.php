<?php

/**
 * ECSHOP wap前台公共函数
 * ============================================================================
 * 版权所有 2005-2009 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: init.php 16881 2009-12-14 09:19:16Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
define('ECS_WAP', true);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 取得当前ecshop所在的根目录 */
define('ROOT_PATH', str_replace('wap/includes/init.php', '', str_replace('\\', '/', __FILE__)));
/* wap目录 */

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);
@ini_set("arg_separator.output","&amp;");
@ini_set('session.use_trans_sid', 0);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path',      '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path',      '.:' . ROOT_PATH);
}

require(ROOT_PATH . 'data/config.php');

if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (PHP_VERSION >= '5.3' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF', $php_self);
require(ROOT_PATH . 'includes/cls_ecshop.php');
require(ROOT_PATH . 'includes/lib_goods.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . 'includes/lib_time.php');
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'wap/includes/lib_main.php');
require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/lib_insert.php');

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    $_REQUEST  = addslashes_deep($_REQUEST);
}

/* 创建 ECSHOP 对象 */
$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());
/* 初始化数据库类 */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new ecs_error('message.dwt');


/* 载入系统参数 */
$_CFG = load_config();
/* 载入语言文件 */
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');
/* 初始化session */
require(ROOT_PATH . 'includes/cls_session.php');
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'));

define('SESS_ID', $sess->get_session_id());


if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset=utf-8');

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'includes/cls_template.php');
    $smarty = new cls_template;

    $smarty->cache_lifetime = $_CFG['cache_time'];
    $smarty->template_dir   = ROOT_PATH . 'wap/templates';
    $smarty->cache_dir      = ROOT_PATH . 'wap/temp/caches';
    $smarty->compile_dir    = ROOT_PATH . 'wap/temp/compiled/wap';

    if ((DEBUG_MODE & 2) == 2)
    {
        $smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }
}

if (!defined('INIT_NO_USERS'))
{
    /* 会员信息 */
    $user =& init_users();
    if (empty($_SESSION['user_id']))
    {
        if ($user->get_cookie())
        {
            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
            if ($_SESSION['user_id'] > 0 && !isset($_SESSION['user_money']))
            {
                update_user_info();
            }
        }
        else
        {
            $_SESSION['user_id']     = 0;
            $_SESSION['user_name']   = '';
            $_SESSION['email']       = '';
            $_SESSION['user_rank']   = 0;
            $_SESSION['discount']    = 1.00;
        }
    }
}

if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ E_NOTICE);
}
ini_set("display_errors", "Off");
error_reporting(0);
//
//if ((DEBUG_MODE & 4) == 4)
//{
//    include(ROOT_PATH . 'includes/lib.debug.php');
//}

/* 判断是否支持gzip模式 */
if (gzip_enabled())
{
    ob_start('ob_gzhandler');
}
/*---------------------20150821     zhang：添加公共头开始------------------------------------*/
// 分类列表
$menu_list = get_menu();
$smarty->assign('menu_list',      $menu_list);
// 搜索历史
if(!empty($_COOKIE['search_history']))
{
    $history = explode(',', $_COOKIE['search_history']);
}
else
{
    $history = array();
}
//var_dump($history);die;
$smarty->assign('search_history', $history);
// 热搜关键词
//$pid = '85';       // 本地热搜词ID
$pid = '102';       // 线上热搜词ID
$sql =  'SELECT ad_name FROM ' . $GLOBALS['ecs']->table('ad') . ' '.
    ' WHERE enabled=1 and position_id='.$pid.' limit 0,20' ;
$res = $GLOBALS['db']->getAll($sql);
//var_dump($res);die;
$search_hot = array();
foreach($res as $k=>$v){
    $search_hot[] =$v['ad_name'];
}
if(!$search_hot){
    $aa = '';
}else{
    $aa = json_encode($search_hot);
}
//var_dump($aa);die;
$smarty->assign('search_hot', $aa);
// 定义图片根目录路径
//define('IMAGE_URL', $_SERVER['HTTP_HOST']);      // 线下
define('IMAGE_URL', "http://www.easeeyes.com/");    // 线上
define('IMG_URL', "http://img.easeeyes.com/");    // CDN 

$smarty->assign('image_url', IMAGE_URL);
$smarty->assign('img_url', IMG_URL);

// 判断是不是微信浏览器
if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
    $smarty->assign('is_wx', 1);
}else{
    $smarty->assign('is_wx', 0);
}

//销毁session值(防止wap端再次访问www的链接而跳转至pc端)
unset($_SESSION['referer_phone']);

/*=========================================函数==================================================*/
/**
    获取客户端真实IP
 */
function get_real_ip(){

}

/**
 * 获取产品分类
 */
function category_list($parent_id=0)
{
    $sql = "select cat_id,cat_name from ".$GLOBALS['ecs']->table('category')." where cat_id!=138  and parent_id= ".$parent_id;
    return $GLOBALS['db']->GetAll($sql);
}

/**
 * 获取分类目录
 */
function get_menu(){
    $sql = 'SELECT cat_id,cat_name FROM ' . $GLOBALS['ecs']->table('category') .
        " WHERE parent_id =0 AND is_show = 1 and cat_id <> 12 ORDER BY  sort_order ASC";
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($res AS $key => $row){

        //栏目数据
        $menu_arr[$row['cat_id']]['cat_name']	=	$row['cat_name'];
        //全部品牌数据
        $sql_2 = 'SELECT cat_id,cat_name,is_show_red FROM ' . $GLOBALS['ecs']->table('category') .
            " WHERE parent_id =".$row['cat_id']." AND is_show = 1 ORDER BY  cat_id ASC";
        $menu_arr[$row['cat_id']]['qbpp'] = $GLOBALS['db']->getAll($sql_2);

        //热门参数数据(手动)

        //热门系列数据
        $sql_3 = 'SELECT hot_id,hot_name,show_red FROM ' . $GLOBALS['ecs']->table('hot') .
            " WHERE hot_fcat =".$row['cat_id'];
        $menu_arr[$row['cat_id']]['rmxl'] = $GLOBALS['db']->getAll($sql_3);

        //热门品牌数据(估计后期还得改手动╮(╯▽╰)╭)
        $sql_4 = "SELECT distinct g.brand_id as gbrand,g.cat_id,b.brand_logo,c.cat_id from ecs_goods AS g LEFT JOIN ecs_brand AS b ON g.brand_id = b.brand_id LEFT JOIN ecs_category AS c ON c.cat_id = g.cat_id WHERE b.is_show=1 AND b.brand_logo != '' AND c.parent_id =".$row['cat_id']."  GROUP BY gbrand ORDER BY b.sort_order ASC limit 8";
        $menu_arr[$row['cat_id']]['rmpp'] = $GLOBALS['db']->getAll($sql_4);

        //热门品牌数据(图片)
        if($row['cat_id']==1){
            $rmpp_pic = ad_info(62,2);
        }elseif($row['cat_id']==6){
            $rmpp_pic = ad_info(64,2);
        }elseif($row['cat_id']==64){
            $rmpp_pic = ad_info(70,2);
        }elseif($row['cat_id']==76){
            $rmpp_pic = ad_info(74,1);
        }elseif($row['cat_id']==159){
            $rmpp_pic = ad_info(66,2);
        }elseif($row['cat_id']==190){
            $rmpp_pic = ad_info(68,2);
        }
        $menu_arr[$row['cat_id']]['rmpp_pic'] = $rmpp_pic;
        //var_dump($arr);
    }
    //var_dump($menu_arr);exit();
    return $menu_arr;
}
/*---------------------20150821     zhang：添加公共头结束------------------------------------*/

?>