<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta content="telephone=no" name="format-detection"/>
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link rel="stylesheet" type="text/css" href="css/user_center.css"/>
</head>
<body>
<?php echo $this->fetch('library/header.lbi'); ?>
<div class="content">
<div class="default_welcome">
    <div class="default_w_title"><?php echo $this->_var['info']['username']; ?> 欢迎您! <span style="float: right;padding:0 1rem;line-height:2;background:#2BBDD6;color:#fff;border-radius:3px;"><a href="user.php?act=logout">退出</a></span></div>
    <div class="member_info" style="margin-top: 15px">
        <span class="vip_level">您的等级: <?php echo $this->_var['rank_name']; ?></span>
        <span>
        <a href="user.php?act=msg" class="message">
        <em  id="com_um" <?php if (! $this->_var['user_info']['slur']): ?>class="com_um"<?php endif; ?> ><?php if (! $this->_var['user_info']['slur']): ?><?php echo $this->_var['user_info']['unread_msg']; ?><?php endif; ?> </em>
        </a>
        </span>
    </div>    
</div>
<div class="wealth">
    <div class="wealth yue">
    <div>
        <p>账户余额</p>
        <p><b style="color:#d63d40"><?php echo $this->_var['info']['surplus']; ?></b></p>
    </div>
    </div>
    <div class="wealth jifen">
    <div>
        <p>易视积分</p>
        <p><b style="color:#d63d40"><?php echo $this->_var['info']['integral']; ?></b></p>
    </div>
    </div>
</div>

<div class="wuliu">
    <a href="wuliu.php">物流查询</a>
</div>

<div class="order">
    <a class="a1" >订单管理</a><a href="user.php?act=order_list" class="a2">更多订单>></a>
</div>

<div class="order_list">
<ul>
    <li><a href="user.php?act=order_list&states=100">待付款订单</a></li>
    <li><a href="user.php?act=order_list&states=101">待发货订单</a></li>
    <li><a href="user.php?act=order_list&states=101">待收货订单</a></li>
    <li><a href="user.php?act=order_list&states=102">已完成订单</a></li>
</ul>
</div>


<div class="account">
    <a class="a1" >账户管理</a>
</div>
<div class="account_list">
<ul>
    <li><a href="user.php?act=collection_list">我的收藏</a></li>
    <li><a href="user.php?act=bonus">我的红包/优惠券</a></li>
    <li><a href="user.php?act=my_kefu">我的客服</a></li>
    <li><a href="user.php?act=address_list">收货地址</a></li>
    <li><a href="user.php?act=resetpw">密码修改</a></li>
</ul>
</div>

</div>
<?php echo $this->fetch('library/footer.lbi'); ?>
</body>
</html>