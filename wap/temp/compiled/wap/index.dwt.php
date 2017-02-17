<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="format-detection" content="telephone=no" />
<meta name="screen-orientation" content="portrait" />
<meta name="x5-orientation" content="portrait" />
<meta name="full-screen" content="yes" />
<meta name="x5-fullscreen" content="true" />
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<link rel="stylesheet" href="http://file.yunjingshang.com/js/swiper.min.css" />
<style>
body{background:#f2f2f2 !important;padding-top:4.5rem;}
.loading{text-align:center;}
#loading{height:3rem;background:url(http://file.easeeyes.com/wap/images/loading.gif) no-repeat center;background-size:2rem auto;}
.swiper-pagination-bullet{background:#c1c1c1;opacity:1;}
.swiper-pagination-bullet.swiper-pagination-bullet-active{background:#60CFE0;}
.banMod .swiper-pagination,.smallBanMod .swiper-pagination{text-align:right;bottom:.25rem;}
.banMod .swiper-pagination .swiper-pagination-bullet,.smallBanMod .swiper-pagination .swiper-pagination-bullet{margin:0 .5rem 0 0;width:.5rem;height:.5rem;}
.smallBanMod{text-align:center;}
/** 降价排行 **/
.repriceMod .goodsbox .swiper-container{overflow:visible;}
.repriceMod .goodsbox .swiper-slide{border:1px solid #ececec;box-sizing:border-box;}
.repriceMod .goodsbox .swiper-pagination{bottom:-1.5rem;}
.repriceMod .goodsbox .swiper-pagination-bullet{width:.5rem;height:.5rem;margin:0 .2rem !important;}
.repriceMod .goodsbox .swiper-pagination-bullet-active{background:#60CFE0;}
.repriceMod .goodsbox .image{text-align:center;padding:.1rem;}
.repriceMod .goodsbox .image img{max-width:100%;}
.repriceMod .goodsbox .text{padding:0 .2rem;line-height:1.4;text-align:center;color:#999;font-size:.7rem;}
.repriceMod .goodsbox .price{font-size:.7rem;}
</style>
<script src="http://file.yunjingshang.com/js/swiper.min.js"></script>
</head>
<body>
<?php echo $this->fetch('library/header_index.lbi'); ?>


<div class="banMod container" id="swiper-01">
轮播图
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <?php $_from = $this->_var['ad_A1']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'banner');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['banner']):
        $this->_foreach['foo']['iteration']++;
?>
            <div class="swiper-slide"><a href="<?php echo $this->_var['banner']['ad_link']; ?>" title="<?php echo $this->_var['banner']['ad_name']; ?>"><img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['banner']['ad_code']; ?>" /></a></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>


<?php if ($this->_var['under_ad']): ?>
    <?php $_from = $this->_var['under_ad']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ubanner');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['ubanner']):
        $this->_foreach['foo']['iteration']++;
?>
        <div class="container"><a href="<?php echo $this->_var['ubanner']['ad_link']; ?>"><img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['ubanner']['ad_code']; ?>" /></a></div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<?php endif; ?>

<div class="navMod container">
    <div class="navlist">
        <div class="list">
            <a href="menu.php"><img src="http://file.yunjingshang.com/wap/images/index/05.jpg" />全部分类</a>
        </div>
        <div class="list">
            <a href="user.php"><img src="http://file.yunjingshang.com/wap/images/index/06.jpg" /><?php if ($_SESSION['user_id'] != 0): ?>会员中心<?php else: ?>注册/登陆<?php endif; ?></a>
        </div>
        <div class="list">
            <a href="flow.php"><img src="http://file.yunjingshang.com/wap/images/index/07.jpg" />购物车</a>
        </div>
        <div class="list">
            <a <?php if ($_SESSION['user_id'] != 0): ?>href="wuliu.php"<?php else: ?>href="user.php"<?php endif; ?>><img src="http://file.yunjingshang.com/wap/images/index/08.jpg" />物流查询</a>
        </div>
        
    </div>
</div>


<div class="announceMod container">
    <div class="title pull-left">
        镜商公告 <img src="http://file.yunjingshang.com/wap/images/index/09.jpg" />
    </div>
    <div class="text" id="newsList-01">
        <ul>
            <?php $_from = $this->_var['report_yishi']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'article');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['article']):
        $this->_foreach['foo']['iteration']++;
?>
            <?php if (($this->_foreach['foo']['iteration'] - 1) == 0): ?>
            <li><a href="<?php echo $this->_var['article']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['article']['title']); ?>" class="red"><?php echo $this->_var['article']['title']; ?></a></li>
            <?php else: ?>
            <li><a href="<?php echo $this->_var['article']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['article']['title']); ?>"><?php echo $this->_var['article']['title']; ?></a></li>
            <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
</div>





<div class="ppMod container">
    <h1 class="title-BL">
        <a class="pull-right">更多品牌 <i class="enter-pg enter-pg-blue"></i></a>
        <span>品牌推荐</span>
    </h1>
    <div class="main">
        <table class="table-default">
            <tbody>
            <tr>
                <?php $_from = $this->_var['brand_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['li']):
        $this->_foreach['foo']['iteration']++;
?>
                <?php if (($this->_foreach['foo']['iteration'] - 1) < 3 && ($this->_foreach['foo']['iteration'] - 1) >= 0): ?>
                <td>
                    <a title="<?php echo $this->_var['li']['brand_name']; ?>"><img src="http://img.easeeyes.com/brands/<?php echo $this->_var['li']['brand_id']; ?>.gif" width="124" height="41" /></a>
                </td>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </tr>
            <tr>
                <?php $_from = $this->_var['brand_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['li']):
        $this->_foreach['foo']['iteration']++;
?>
                <?php if (($this->_foreach['foo']['iteration'] - 1) < 6 && ($this->_foreach['foo']['iteration'] - 1) >= 3): ?>
                <td>
                    <a  title="<?php echo $this->_var['li']['brand_name']; ?>"><img src="http://img.easeeyes.com/brands/<?php echo $this->_var['li']['brand_id']; ?>.gif" width="124" height="41" /></a>
                </td>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </tr>
            <tr>
                <?php $_from = $this->_var['brand_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['li']):
        $this->_foreach['foo']['iteration']++;
?>
                <?php if (($this->_foreach['foo']['iteration'] - 1) < 9 && ($this->_foreach['foo']['iteration'] - 1) >= 6): ?>
                <td>
                    <a title="<?php echo $this->_var['li']['brand_name']; ?>"><img src="http://img.easeeyes.com/brands/<?php echo $this->_var['li']['brand_id']; ?>.gif" width="124" height="41" /></a>
                </td>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="hotSale container">
    <h3 class="title-BC hotSale-title"><span>热销推荐</span></h3>
    <div class="hotSale-navs clearfix" id="hotSaleNav">
        <a href="#nTmp" data-id='nTmp'>透明片</a>
        <a href="#nCp" data-id='nCp'>彩片</a>
        <a href="#nHly" data-id='nHly'>护理液</a>
        <a href="#nHlgj" data-id='nHlgj'>护理工具</a>
    </div>
    
    <div class="hotSale-group" id="nTmp">
        <div class="hotSale-group-nav">
            <a href="">更多推荐</a>
            <h3>透明片</h3>
        </div>
        <div class="hotSale-items clearfix">
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>

        </div>
    </div>
    
    <div class="hotSale-group hotSale-group-cp" id="nCp">
        <div class="hotSale-group-nav">
            <a href="">更多推荐</a>
            <h3>彩片</h3>
        </div>
        <div class="hotSale-items clearfix">
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            
        </div>
    </div>
    
    <div class="hotSale-group hotSale-group-hly" id="nHly">
        <div class="hotSale-group-nav">
            <a href="">更多推荐</a>
            <h3>护理液</h3>
        </div>
        <div class="hotSale-items clearfix">
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            
        </div>
    </div>
    
    <div class="hotSale-group hotSale-group-hlgj" id="nHlgj">
        <div class="hotSale-group-nav">
            <a href="">更多推荐</a>
            <h3>护理工具</h3>
        </div>
        <div class="hotSale-items clearfix">
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            <div class="hotSale-item">
                <a href="">
                    <div class="hotSale-item-thumb"><img src="http://img.easeeyes.com/promotion/20160301/test.jpg" alt=""></div>
                    <div class="hotSale-item-title">测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试测试</div>
                    <div class="hotSale-item-price">&yen;9.9 <del>&yen;29.9</del></div>
                </a>
            </div>
            
        </div>
    </div>
</div>



<div class="f-commitMod container">
    1F :透明片--->展示6个<br />
    2F :彩片--->展示6个<br />
    3F :护理液--->展示6个<br />
    4F :护理工具--->展示6个<br />
</div>



<div class="f-commitMod container">
    <ul class="f-commit clearfix">
        <li>
            <div class="image"><span><img src="http://file.yunjingshang.com/wap/images/index/f1.png" /></span></div>
            <div class="text">闪电发货<br />7天不停歇</div>
        </li>
        <li>
            <div class="image"><span><img src="http://file.yunjingshang.com/wap/images/index/f2.png" /></span></div>
            <div class="text">100%<br />正品保障</div>
        </li>
        <li>
            <div class="image"><span><img src="http://file.yunjingshang.com/wap/images/index/f3.png" /></span></div>
            <div class="text"> <br />30天退换货</div>
        </li>
        <li>
            <div class="image"><span><img src="http://file.yunjingshang.com/wap/images/index/f4.png" /></span></div>
            <div class="text"> <br />满68元包邮</div>
        </li>
        <li>
            <div class="image"><span><img src="http://file.yunjingshang.com/wap/images/index/f5.png" /></span></div>
            <div class="text">600城市<br />货到付款</div>
        </li>
        <li>
            <div class="image"><span><img src="http://file.yunjingshang.com/wap/images/index/f6.png" /></span></div>
            <div class="text">医疗器械<br />许可证</div>
        </li>
    </ul>
</div>

<script src="http://file.yunjingshang.com/js/countdown.js"></script>
<script>
    //var date=new Date();
    //var str=date.getFullYear()+"/"+(parseInt(date.getMonth())+1)+"/"+date.getDate()+" "+"16:00";
    //倒计时执行代码
    var countDown=new CountDown("<?php echo $this->_var['ms']['djs_time']; ?>");
</script>
<?php echo $this->fetch('library/footer_index.lbi'); ?>
<script src="http://file.yunjingshang.com/js/news.js"></script>
<script src="http://file.yunjingshang.com/js/scrollnav.js"></script>
<script>
// 导航高度识别
$(window).load(function(){
    scrollNav.init({
        nid:'hotSaleNav',
        ports:['nTmp','nCp','nHly','nHlgj'],
        start:function(){
            $('header').hide()
        },
        leave:function(){
            $('header').show()
        }
    })
})

// 加载更多热销产品
    var loaded=false;
    $(window).scroll(function(e){
        if($('#nomore').val()==0){

            if ($(window).scrollTop() + $(window).height() > $("#lookMore").offset().top){
                if(!loaded){
                    loaded=true;
                    $.ajax({
                        type : "get",
                        url : "index.php?act=more",
                        data:{page:$("#pageStie").val()},
                        beforeSend :function(msg){
                            $('#loading').fadeIn(500);
                        },
                        success : function(msg){
                            $('#loading').fadeOut(500);
                            if(msg){
                                $("#more").append(msg);
                                var  pageStie= parseInt($('#pageStie').val())+parseInt(1);
                                $('#pageStie').val(pageStie);
                            }else{
                                $('#nomore').val(1);
                            }
                        }
                    });

                }
                
            }else{
                loaded=false;
            }
        }else{
            $('#loading').hide();
            $('#nomoreresults').fadeIn(500);
        }
    });
    $("#add_more").click(function(e){
        $.ajax({
            type : "get",
            async:false,
            url : "index.php?act=more",
            data:{page:$("#pageStie").val()},
            success : function(msg){
                if(msg){
                    $("#more").append(msg);
                    var  pageStie= parseInt($('#pageStie').val())+parseInt(1);
                    $('#pageStie').val(pageStie);
                }else{
                    $("#add_more").html("没有更多了···");
                }
            }
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var mySwiper1=new Swiper('#swiper-01 .swiper-container',{
        direction:'horizontal',
        loop:true,
        pagination:'#swiper-01 .swiper-pagination',
        autoplay:5000,
        autoplayDisableOnInteraction:false
    });
    var mySwiper2=new Swiper('#swiper-02 .swiper-container',{
        direction:'horizontal',
        loop:true,
        pagination:'#swiper-02 .swiper-pagination',
        autoplay:3000,
        autoplayDisableOnInteraction:false
    });
    var mySwiper3=new Swiper('#swiper-03 .swiper-container',{
        direction:'horizontal',
        loop:true,
        pagination:'#swiper-03 .swiper-pagination',
        autoplay:3000,
        autoplayDisableOnInteraction:false
    });
    var swiper=new Swiper('#swiper-04 .swiper-container', {
        pagination:'#swiper-04 .swiper-pagination',
        slidesPerView:3,
        paginationClickable:true,
        spaceBetween:5
    });
},false);
</script>
</body>
</html>