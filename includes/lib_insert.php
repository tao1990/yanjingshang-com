<?php

/**
 * ECSHOP 动态内容函数库
 * ============================================================================
 * ============================================================================
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 获得查询次数以及查询时间
 *
 * @access  public
 * @return  string
 */
function insert_query_info()
{
    if ($GLOBALS['db']->queryTime == '')
    {
        $query_time = 0;
    }
    else
    {
        if (PHP_VERSION >= '5.0.0')
        {
            $query_time = number_format(microtime(true) - $GLOBALS['db']->queryTime, 6);
        }
        else
        {
            list($now_usec, $now_sec)     = explode(' ', microtime());
            list($start_usec, $start_sec) = explode(' ', $GLOBALS['db']->queryTime);
            $query_time = number_format(($now_sec - $start_sec) + ($now_usec - $start_usec), 6);
        }
    }

    /* 内存占用情况 */
    if ($GLOBALS['_LANG']['memory_info'] && function_exists('memory_get_usage'))
    {
        $memory_usage = sprintf($GLOBALS['_LANG']['memory_info'], memory_get_usage() / 1048576);
    }
    else
    {
        $memory_usage = '';
    }

    /* 是否启用了 gzip */
    $gzip_enabled = gzip_enabled() ? $GLOBALS['_LANG']['gzip_enabled'] : $GLOBALS['_LANG']['gzip_disabled'];

    $online_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('sessions'));

    /* 加入触发cron代码 */
    $cron_method = empty($GLOBALS['_CFG']['cron_method']) ? '<img src="api/cron.php?t=' . gmtime() . '" alt="" style="width:0px;height:0px;" />' : '';

    return sprintf($GLOBALS['_LANG']['query_info'], $GLOBALS['db']->queryCount, $query_time, $online_count) . $gzip_enabled . $memory_usage . $cron_method;
}

/**
 * 调用浏览历史->原生版本
 * 使用的本地cookie记录本地浏览记录
 * @access  public
 * @return  string
 */
function insert_history()
{
    $str = '';
    if (!empty($_COOKIE['ECS']['history']))
    {
        $where = db_create_in($_COOKIE['ECS']['history'], 'goods_id');
        $sql   = 'SELECT goods_id, market_price,goods_name, goods_thumb,goods_img, shop_price FROM ' . $GLOBALS['ecs']->table('goods') .
                " WHERE $where AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0";
        $query = $GLOBALS['db']->query($sql);
        $res = array();
        while ($row = $GLOBALS['db']->fetch_array($query))
        {
            $goods['goods_id']   = $row['goods_id'];
            $goods['goods_name'] = $row['goods_name'];
            $goods['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods['goods_thumb']= get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods['goods_img']  = get_image_path($row['goods_id'], $row['goods_img'], true);
            $goods['shop_price'] = price_format($row['shop_price']);
            $goods['url'] = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
            $str.='<ul class="clearfix"><li class="goodsimg"><a href="'.$goods['url'].'" target="_blank"><img src="'.$goods['goods_img'].'" alt="'.$goods['goods_name'].'" class="B_blue" /></a></li><li><a href="'.$goods['url'].'" target="_blank" title="'.$goods['goods_name'].'" class="color_lv">'.$goods['short_name'].'</a><br /><span class="shanchux">市场价：<s>'.price_format($row['market_price']).'</s></span><br />易视价：<span class="redhong fontsize13">'.$goods['shop_price'].'</span><br /></li></ul>';
        }
        $str .= '<ul id="clear_history"><a onclick="clear_history()">' . $GLOBALS['_LANG']['clear_history'] . '</a></ul>';
    }
    return $str;
}

//yi:重写插入历史记录功能
function insert_historys()
{
    $str = '';
    if (!empty($_COOKIE['ECS']['history']))
    {
        $where = db_create_in($_COOKIE['ECS']['history'], 'goods_id');
        $sql   = 'SELECT goods_id, goods_name, goods_thumb, goods_img, shop_price, market_price FROM ' . $GLOBALS['ecs']->table('goods') .
                " WHERE $where AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 order by INSTR('".$_COOKIE['ECS']['history']."',goods_id) ASC";
        $query = $GLOBALS['db']->query($sql);
        $res = array();
        while ($row = $GLOBALS['db']->fetch_array($query))
        {
            $goods['goods_id']   = $row['goods_id'];
            $goods['market_price'] = $row['market_price'];
			$goods['goods_name'] = $row['goods_name'];
            $goods['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods['goods_thumb']= get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods['goods_img']  = get_image_path($row['goods_id'], $row['goods_img'], true);
            $goods['shop_price'] = price_format($row['shop_price']);
            $goods['url']        = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);

			$str.='<a href="'.$goods['url'].'" title="'.$goods['goods_name'].'" target="_blank" class="history_li"><img src="thumb/goods/60x60/goods_'.$goods['goods_id'].'_60x60.jpg" width="60" height="60" alt="'.$goods['goods_name'].'"/></a>';
        }

        $str .= '<div class="history_clear"><a onclick="clear_history()" style="cursor:pointer">'.$GLOBALS['_LANG']['clear_history'].'</a></div>';
    }
    return $str;
}

/**
 * 调用购物车信息
 *
 * @access  public
 * @return  string
 */
function insert_cart_info()
{
    $sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $row = $GLOBALS['db']->GetRow($sql);
    if ($row)
    {
        $number = intval($row['number']);
        $amount = floatval($row['amount']);
    }
    else
    {
        $number = 0;
        $amount = 0;
    }

    $str = sprintf($GLOBALS['_LANG']['cart_info'], $number, price_format($amount, false));

    return '<a href="flow.php" title="' . $GLOBALS['_LANG']['view_cart'] . '">' . $str . '</a>';
}

//写出购物车情况并插入页面-修改了样式同时要修改这里--
function insert_cart_infotop()
{
    $sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $row = $GLOBALS['db']->GetRow($sql);
    if ($row)
    {
        $number = intval($row['number']);
        $amount = floatval($row['amount']);
    }
    else
    {
        $number = 0;
        $amount = 0;
    }

    //$str = sprintf($GLOBALS['_LANG']['cart_infotop'], $number);
    //return '<a href="javascript:void(0)" title="'.$GLOBALS['_LANG']['view_cart'].'">'.$str.'</a>';
	//yi:修改成为只返回购物车中商品数目
	return $number;
}

//yi:购物车商品数量 仅仅显示数字
function insert_cart_num(){
	//xyz edit(20130110) 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql = "SELECT SUM(goods_number) AS sum FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id = '" . $_SESSION['user_id'] . "' AND shop_id = 2;";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = "SELECT SUM(goods_number) AS sum FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id <= 0 AND (session_id = '" . SESS_ID . "' OR session_id = '".$_COOKIE['cart_session_id']."') AND shop_id = 2;";
            
		} else {
			$sql = "SELECT SUM(goods_number) AS sum FROM ".$GLOBALS['ecs']->table('cart')." WHERE session_id = '" . SESS_ID . "' AND shop_id = 2;";
		}
		
	}
    $num = $GLOBALS['db']->GetOne($sql);
	$num = empty($num) ? 0 : intval($num);
	return $num;
}

//总金额
function insert_cart_sum(){
	//xyz edit(20130110) 保存购物车信息
	if ($_SESSION['user_id'] > 0) {
		$sql = 'SELECT SUM(goods_number) AS sum, SUM(goods_price * goods_number) AS amount' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE user_id = '" . $_SESSION['user_id'] . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
	} else {
		if (isset($_COOKIE['cart_session_id'])) {
			$sql = 'SELECT SUM(goods_number) AS sum, SUM(goods_price * goods_number) AS amount' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE user_id <= 0 AND (session_id = '" . SESS_ID . "' OR session_id = '".$_COOKIE['cart_session_id']."') AND rec_type = '" . CART_GENERAL_GOODS . "'";
		} else {
			$sql = 'SELECT SUM(goods_number) AS sum, SUM(goods_price * goods_number) AS amount' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
		}
	}
    $row = $GLOBALS['db']->GetRow($sql);
    if($row)
    {
        $sum = intval($row['sum']);
        $amount = floatval($row['amount']);
    }
    else
    {
        $sum = 0;
        $amount = 0;
    }
	$str = "".$amount;
	return $str;
}

//yi:购物车商品具体信息 (名字,id,图片,单价,数量,购物车id)
function insert_cart_infopan(){
	$sql = 'SELECT SUM(goods_number) AS sum, SUM(goods_price * goods_number) AS amount' .
           ' FROM ' . $GLOBALS['ecs']->table('cart') .
           " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $row = $GLOBALS['db']->GetRow($sql);
    if($row)
    {
        $sum = intval($row['sum']);
        $amount = floatval($row['amount']);
    }
    else
    {
        $sum = 0;
        $amount = 0;
    }

	$dall = '';
	if($sum > 0){
		//商品数量大于0
		$cart_info = array();
		$sql = "select c.rec_id,c.goods_id,c.goods_name,c.goods_price,c.goods_number,c.is_gift,c.extension_code,g.goods_img from ".$GLOBALS['ecs']->table('cart')." as c,".$GLOBALS['ecs']->table('goods')." as g where c.goods_id = g.goods_id and c.session_id = '".SESS_ID."' order by c.rec_id desc";

		$res = mysql_query($sql);	
		while($row = $GLOBALS['db']->fetchRow($res)){
			$cart_info[] = $row;
		}

		//yi:购物车的头部行
		$dall = '';

		//循环加商品图片
		for( $i = 0; $i<count($cart_info); $i++){
			
			$name        = $cart_info[$i]["goods_name"];
			$img         = $cart_info[$i]["goods_img"];
			$id          = $cart_info[$i]["goods_id"];
			$num         = $cart_info[$i]["goods_number"];
			$rec_id      = $cart_info[$i]["rec_id"];
			$goods_price = $cart_info[$i]["goods_price"];
			$ec_code     = $cart_info[$i]["extension_code"];
			$is_gift     = $cart_info[$i]["is_gift"];

			//礼包商品删除 赠品也也不可以删除

			$dd  = "<dl id='".$rec_id."'><dt><a href='http://www.easeeyes.com/goods".$id.".html' target='_blank'><img src='".$img."' width='50' height='50' border=0 /></a></dt>";
			$dd .= "<dd><a href='http://www.easeeyes.com/goods".$id.".html' target='_blank' title='".$name."'>".$name."</a><span> X ".$num."</span></dd>";
			$dd .= "<dd><span class='cart_price'>".$goods_price."</span>";
			if($ec_code != "package_buy" && $is_gift != 1){
				$dd .= "<a class='cart_del' href=javascript:dropHeadFlowNum(\"".$rec_id."\",\"您确实要把该商品移出购物车吗？\")>[删除]</a>";
			}else if($goods_price > 0 ){
				$dd .= "<a class='cart_del' href=javascript:dropPackage(\"".$rec_id."\",\"您确实要把该礼包移出购物车吗？\")>[删除]</a>";
			}else{
				$dd .= "<a class='cart_del' style='color:#999;' href=javascript:void(0)>[删除]</a>";
			}
			$dd .= "<div class='clear'></div></dd></dl>";
			$dall.= $dd;
		}

		//共多少件商品
		$dg    = "<p class='cart_count'>共<font>".$sum."</font>件商品<br/>金额总计：<font>￥".$amount.".00元</font></p><a class='add_cart' href='flow.html'></a>";
		$dall .= $dg;

	}else{$dall = '';}
	return $dall;
}

/**
 * 调用指定的广告位的广告
 * @access  public
 * @param   integer $id     广告位ID
 * @param   integer $num    广告数量
 * @return  string
 */
function insert_ads($arr)
{
    static $static_res = NULL;

    $time = gmtime();
    if (!empty($arr['num']) && $arr['num'] != 1)
    {
        $sql  = 'SELECT a.ad_id, a.position_id, a.media_type, a.ad_link, a.ad_code, a.ad_name, p.ad_width, ' .
                    'p.ad_height, p.position_style, RAND() AS rnd ' .
                'FROM ' . $GLOBALS['ecs']->table('ad') . ' AS a '.
                'LEFT JOIN ' . $GLOBALS['ecs']->table('ad_position') . ' AS p ON a.position_id = p.position_id ' .
                "WHERE enabled = 1 AND start_time <= '" . $time . "' AND end_time >= '" . $time . "' ".
                    "AND a.position_id = '" . $arr['id'] . "' " .
                'ORDER BY rnd LIMIT ' . $arr['num'];
        $res = $GLOBALS['db']->GetAll($sql);
    }
    else
    {
        if ($static_res[$arr['id']] === NULL)
        {
            $sql  = 'SELECT a.ad_id, a.position_id, a.media_type, a.ad_link, a.ad_code, a.ad_name, p.ad_width, '.
                        'p.ad_height, p.position_style, RAND() AS rnd ' .
                    'FROM ' . $GLOBALS['ecs']->table('ad') . ' AS a '.
                    'LEFT JOIN ' . $GLOBALS['ecs']->table('ad_position') . ' AS p ON a.position_id = p.position_id ' .
                    "WHERE enabled = 1 AND a.position_id = '" . $arr['id'] .
                        "' AND start_time <= '" . $time . "' AND end_time >= '" . $time . "' " .
                    'ORDER BY rnd LIMIT 1';
            $static_res[$arr['id']] = $GLOBALS['db']->GetAll($sql);
        }
        $res = $static_res[$arr['id']];
    }
    $ads = array();
    $position_style = '';

    foreach ($res AS $row)
    {
        if ($row['position_id'] != $arr['id'])
        {
            continue;
        }
        $position_style = $row['position_style'];
        switch ($row['media_type'])
        {
            case 0: // 图片广告
                $src = (strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false) ?
                        DATA_DIR . "/afficheimg/$row[ad_code]" : $row['ad_code'];
                $ads[] = "<a href='affiche.php?ad_id=$row[ad_id]&amp;uri=" .urlencode($row["ad_link"]). "'
                target='_blank'><img src='$src' width='" .$row['ad_width']. "' height='$row[ad_height]'
                border='0' /></a>";
                break;
            case 1: // Flash
                $src = (strpos($row['ad_code'], 'http://') === false && strpos($row['ad_code'], 'https://') === false) ?
                        DATA_DIR . "/afficheimg/$row[ad_code]" : $row['ad_code'];
                $ads[] = "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" " .
                         "codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\"  " .
                           "width='$row[ad_width]' height='$row[ad_height]'>
                           <param name='movie' value='$src'>
                           <param name='quality' value='high'>
                           <embed src='$src' quality='high'
                           pluginspage='http://www.macromedia.com/go/getflashplayer'
                           type='application/x-shockwave-flash' width='$row[ad_width]'
                           height='$row[ad_height]'></embed>
                         </object>";
                break;
            case 2: // CODE
                $ads[] = $row['ad_code'];
                break;
            case 3: // TEXT
                $ads[] = "<a href='affiche.php?ad_id=$row[ad_id]&amp;uri=" .urlencode($row["ad_link"]). "'
                target='_blank'>" .htmlspecialchars($row['ad_code']). '</a>';
                break;
        }
    }
    $position_style = 'str:' . $position_style;

    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;

    $GLOBALS['smarty']->assign('ads', $ads);
    $val = $GLOBALS['smarty']->fetch($position_style);

    $GLOBALS['smarty']->caching = $need_cache;

    return $val;
}

/**
 * 调用会员信息
 *
 * @access  public
 * @return  string
 */
function insert_member_info()
{
    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;

    if($_SESSION['user_id'] > 0)
    {
        $user_info = get_user_info();
        
        /*会员等级*/
        if($user_info['user_rank'] == 1){
            $user_info['user_rank_level'] = 1;
        }elseif($user_info['user_rank'] == 2){
            $user_info['user_rank_level'] = 2;
        }elseif($user_info['user_rank'] == 8){
            $user_info['user_rank_level'] = 3;
        }elseif($user_info['user_rank'] == 7){
            $user_info['user_rank_level'] = 4;
        }
        
        $GLOBALS['smarty']->assign('user_info', $user_info);
    }
    else
    {
        if (!empty($_COOKIE['ECS']['username']))
        {
            $GLOBALS['smarty']->assign('ecs_username', stripslashes($_COOKIE['ECS']['username']));
        }
        $captcha = intval($GLOBALS['_CFG']['captcha']);
        if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
        {
            $GLOBALS['smarty']->assign('enabled_captcha', 1);
            $GLOBALS['smarty']->assign('rand', mt_rand());
        }
    }
    $output = $GLOBALS['smarty']->fetch('library/member_info.lbi');

    $GLOBALS['smarty']->caching = $need_cache;

    return $output;
}

/**
 * 调用会员下级等级经验差值
 *
 * @access  public
 * @return  string
 */
function insert_member_level_difference($data)
{

    $user_rank_level = $data['user_rank_level'];
    
    $rank_points = $data['rank_points'];
    
    if($rank_points<1000){//v1
        $dif_points = 1000-$rank_points;
        $user_rank_level_tip = '<p>距离下一等级还差'.$dif_points.'点成长值</p>';
    }elseif($rank_points>=1000 && $rank_points<2800){//v2
        $dif_points = 2800-$rank_points;
        $user_rank_level_tip = '<p>距离下一等级还差'.$dif_points.'点成长值</p>';
    }elseif($rank_points>=2800 && $rank_points<5000){//v3
        $dif_points = 5000-$rank_points;
        $user_rank_level_tip = '<p>距离下一等级还差'.$dif_points.'点成长值</p>';
    }elseif($rank_points>=5000){//v4
        $user_rank_level_tip = '<p>您的账户已经是最高等级</p>';
    }
    $res = '';
    $res .= '<h4>采购等级 <span class="orange">VIP'.$user_rank_level.'</span></h4>';
    if($user_rank_level == 4){
        $res .= '<div class="p-grade">
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
				</div>';
    }else{
        $res .= '<div class="p-grade">
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span class="p-grade-true"></span>
					<span></span>
				</div>';
    }
    $res .=$user_rank_level_tip;

    return $res;
}
/**
 * 调用评论信息
 *
 * @access  public
 * @return  string
 */
function insert_comments($arr)
{
    $need_cache   = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;

    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->force_compile = true;

    /* 验证码相关设置 */
    if ((intval($GLOBALS['_CFG']['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }
    $GLOBALS['smarty']->assign('username',     stripslashes($_SESSION['user_name']));
    $GLOBALS['smarty']->assign('email',        $_SESSION['email']);
    $GLOBALS['smarty']->assign('comment_type', $arr['type']);
    $GLOBALS['smarty']->assign('id',           $arr['id']);
    $cmt = assign_comment($arr['id'],          $arr['type']);
    $GLOBALS['smarty']->assign('comments',     $cmt['comments']);
    $GLOBALS['smarty']->assign('pager',        $cmt['pager']);

	$val = $GLOBALS['smarty']->fetch('library/comments_list.lbi');

    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;

    return $val;
}

/**
 * 调用在线调查信息
 *
 * @access  public
 * @return  string
 */
function insert_vote()
{
    $vote = get_vote();
    if (!empty($vote))
    {
        $GLOBALS['smarty']->assign('vote_id',     $vote['id']);
        $GLOBALS['smarty']->assign('vote',        $vote['content']);
    }
    $val = $GLOBALS['smarty']->fetch('library/vote.lbi');
    return $val;
}
/**
 * 用于商品详情页（度数数据）
*/
function insert_ds_list($arr){
  //<!--{foreach from=$goodsds item=li}--><option value="{if $li.canbuy}{$li.val}{else}{/if}">{$li.val}{$li.status}</option><!--{/foreach}-->
    
    $id = empty($_GET['id'])? $arr['id']:$_GET['id'];
    $goodsds = get_goods_ds($id);    //度数
    
    $res = '';
    foreach($goodsds as $v){
        $canbuy = empty($v['canbuy'])? '':trim($v['val']);
        $res .= "<option value='".$canbuy."'>".trim($v['val']).$v['status']."</option>";
    }
    return $res;
}


/**
 * 用于商品列表页（区分更详细的度数数据）
 * @param $arr['id'](goods_id)
 * @return 商品详细度数/散光/轴位
*/
function insert_cat_ds_list($arr){

    $goods_id = $arr['id'];
    
    $goodsds = get_goods_ds($goods_id);    //度数
    
    if($goodsds){//有度数
        
        $res ='';
        $res .= '度数:';
        $res .= '<select name="goods_select" id="ds_'.$goods_id.'"><option value="">请选择</option>';
        foreach($goodsds as $v){
            $canbuy = empty($v['canbuy'])? 'nobuy':trim($v['val']);
            $res .= "<option value='".$canbuy."'>".trim($v['val']).$v['status']."</option>";
        }
        $res .= '</select>';
        $res .= "<input id='is_ds_$goods_id' value='1' type='hidden'/>";
        
        $goods_sg = if_sg($goods_id);
        $goods_jp = if_jp($goods_id);
        if($goods_sg)
    	{//是否有散光
    		$sgds = get_sgds_info($goods_id);
            $res .= '<br />散光:';
            $res .= '<select name="zsg" class="pro_top_link_selse" id="sg_'.$goods_id.'"><option value="">请选择</option>';
            foreach($sgds['ds_values'] as $v){
            $res .= "<option value='".$v."'>".$v."</option>";
            }
            $res .= "</select>";
            
            $res .= '<br />轴位:';
            $res .= '<select name="zzhou" class="pro_top_link_selse" id="zw_'.$goods_id.'"><option value="">请选择</option>';
            for($i=5;$i<=180;$i+=5){
            $res .= "<option value='".$i."'>".$i."</option>";
            }
            $res .= "</select>";
            $res .= "<input id='is_sg_$goods_id' value='1' type='hidden' />";
    	}elseif($goods_jp){
    	   //是否为镜片
            $sgds = get_sgds_info($goods_id);
            $res .= '<br />散光:';
            $res .= '<select name="zsg" class="pro_top_link_selse" id="sg_'.$goods_id.'"><option value="">请选择</option>';
            foreach($sgds['ds_values'] as $v){
            $res .= "<option value='".$v."'>".$v."</option>";
            }
            $res .= "</select>";
            $res .= "<input id='is_jp_$goods_id' value='1' type='hidden' />";
        }
        
    }else{//无度数
        
    }
    
    
    return $res;
}


/**
 * 头部菜单
 *
 * @access  public
 * @return  string
 */
function insert_header_menu()
{
    $val = $GLOBALS['smarty']->fetch('library/menu.lbi');
    return $val;
}
?>