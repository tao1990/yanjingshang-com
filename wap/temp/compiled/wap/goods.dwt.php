<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0"/>
<title><?php echo $this->_var['page_title']; ?></title>
<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="http://file.easeeyes.com/index2015/css/ani.css" />
<link rel="stylesheet" href="http://file.easeeyes.com/wap/js/idangerous.swiper.css">
</head>
<body>

	
	<div class="title-header">
		<h2 class="text-center">
			<a href="javascript:history.go(-1);" class="btn-back"></a>
			产品详情
            <span class="menuBar" id="menuBar"></span>
		</h2>
	</div>
    
    <div class="menuMore clearfix" id="menusMore">
        <div class="menuItem"><a href="/"><img src="http://file.easeeyes.com/wap/images/h_home.png" alt=""><br>首页</a></div>
        <div class="menuItem"><a href="javascript:;" id="search_btn"><img src="http://file.easeeyes.com/wap/images/h_search.png" alt=""><br>搜索</a></div>
        <div class="menuItem"><a href="flow.php"><img src="http://file.easeeyes.com/wap/images/h_car.png" alt=""><br>购物车</a></div>
        <div class="menuItem"><a href="user.php"><img src="http://file.easeeyes.com/wap/images/h_member.png" alt=""><br><?php if ($_SESSION['user_id'] != 0): ?>个人中心<?php else: ?>注册/登陆<?php endif; ?></a></div>
    </div>

	<div style="background:#fff;">
		
        <div class="swiper-container" id="swiperBanner">
          <div class="swiper-wrapper">
            <?php $_from = $this->_var['gallery']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['li']):
        $this->_foreach['foo']['iteration']++;
?>
            <div class="swiper-slide"><img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['li']['thumb_url']; ?>" /></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </div>
          <span class="goodsSlider-tip"><i id="goodsSlider-index">1</i>/<i id="goodsSlider-count"><?php echo $this->_var['ga_sum']; ?></i></span>
        </div>
		
		
        <?php if ($this->_var['link_goods'] && $this->_var['source']['show_sk'] == false): ?>
		<div class="thumbGallery">
			<div class="thumbGallery-left" id="thumbGallery-btn"><img src="css/slice/d_3.jpg" /></div>
			<div class="thumbGallery-main" id="thumbGallery-main">
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide current"><img width="60" height="60" src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['goods']['goods_img']; ?>" alt="<?php echo $this->_var['li']['title']; ?>" /></div>
                        <?php $_from = $this->_var['link_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['li']):
        $this->_foreach['foo']['iteration']++;
?>
                        <div class="swiper-slide"><a href="goods.php?id=<?php echo $this->_var['li']['link_goods_id']; ?>"><img width="60" height="60" src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['li']['goods_img']; ?>" alt="<?php echo $this->_var['li']['title']; ?>" /></a></div>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </div>
                </div>
				
			</div>
			<div class="thumbGallery-right"><img src="css/slice/d_4.jpg" /></div>
		</div>
        <?php endif; ?>
	</div>	
		
	<div class="detailTab-navs" id="detailTab-navs">
		<div class="detailTab-nav current">简 介</div>
		<div class="detailTab-nav">详 情</div>
	</div>	
	
	<div class="detailTab-main">
		<div class="goodsInfo">
			<div class="goodsInfo-left">
				<h4 class="goodsInfo-title"><?php echo $this->_var['goods']['goods_name']; ?></h4>
                   
                   <?php echo $this->fetch('library/goods_price.lbi'); ?>
			</div>
		</div>
	               
                   <?php if ($this->_var['is_jp']): ?>
                       <?php echo $this->fetch('library/goods_jp.lbi'); ?>
                   <?php else: ?>
                       <?php if ($this->_var['goodsds']): ?>
        				    <?php echo $this->fetch('library/goods_info_ds.lbi'); ?>
                       <?php else: ?>     
                            <?php echo $this->fetch('library/goods_info_nods.lbi'); ?>
                       <?php endif; ?>
                   <?php endif; ?>
      
		
        <?php echo $this->fetch('library/goods_attr.lbi'); ?>
        
		<div class="connectBox">
			<a href="tel:4008168887" class="item"><img src="css/slice/d_10.jpg" /> 4008-168-887</a>
		</div>
	</div>
	
	
	<div class="detailTab-main">
        <?php echo $this->_var['goods']['goods_desc']; ?>
	</div>



<div style="height:6.5rem;"></div>
<div class="fix-bottom">
	
	<div class="left">
       
		<a href="flow.php" class="fix-bottom-link"><img src="css/slice/d_12.jpg" style="height:1.5rem;" /><br />购物车</a>
	</div>
	
	<div class="right">
        <?php if ($this->_var['goods']['is_on_sale'] == 1): ?>
            <?php if ($this->_var['goods']['goods_number'] > 0): ?>
            <!--<a href="goods.php?act=choose_goods_attr&id=<?php echo $this->_var['goods']['goods_id']; ?>" class="">加入购物车</a>-->
			 <a class="btn-pg btn-pg-nobg J_cars_btn" onclick="showDiv('mydiv',<?php echo $this->_var['goods']['goods_id']; ?>)">加入购物车</a>
            <?php else: ?>
            <a class="" style="background-color: #777;">已售空</a>
            <?php endif; ?>
        <?php else: ?>
            <a class="" style="background-color: #777;">已下架</a>
        <?php endif; ?>
	</div>
</div>


<div id="J-fullpage" class="pics-fullpage">
	<div class="pics-picer" id="pics-picer">
		<ul id="pics-ulist">
			<li><img src="css/slice/test.jpg" /></li>
			<li><img src="css/slice/test.jpg" /></li>
			<li><img src="css/slice/test.jpg" /></li>
			<li><img src="css/slice/test.jpg" /></li>
		</ul>
	</div>
	<span id="pics-page"></span>
</div>


<div class="p-search" id="search-page">
    
    <div class="clearfix p-search-header">
        <span class="pull-left p-search-header-close" id="search-close">&times;</span>
        <div class="p-search-searchbar">
            <form action="category.php" method="get" id="search_form">
                <input type="input" name="keyword" class="p-search-input" id="p-search-input" />
                <button class="p-search-submit" onclick="document.getElementById('search_form').submit()"><img src="http://file.easeeyes.com/wap/images/search.png" /></button>
            </form>
        </div>
    </div>
    
    <div class="p-search-toolbar">
        <span class="pull-right p-search-toolbar-change" id="keychange"><img src="http://file.easeeyes.com/wap/images/icon-cw.png" /> 换一批</span>
        <b>热搜</b>
    </div>
    
    <div class="p-search-keysbox">
        <a class="p-search-key" href="category.php?keyword=1">选项1</a>
        <a class="p-search-key" href="category.php?keyword=2">选项2</a>
        <a class="p-search-key" href="category.php?keyword=3">选项3</a>
        <a class="p-search-key" href="category.php?keyword=4">选项4</a>
        <a class="p-search-key" href="category.php?keyword=5">选项5</a>
        <a class="p-search-key" href="category.php?keyword=6">选项6</a>
    </div>
    
    <div class="p-search-historys">
        <ul id="search-history">
            <?php $_from = $this->_var['search_history']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v']):
?>
            <li><a href="category.php?keyword=<?php echo $this->_var['v']; ?>"><?php echo $this->_var['v']; ?></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    
    <div class="p-search-clearbtn">
        <button id="clearCookie">清空历史搜索</button>
    </div>
</div>


<script src="http://file.easeeyes.com/js/response.js"></script>
<script src="http://file.easeeyes.com/js/jquery.min.js"></script>
<script src="http://file.easeeyes.com/wap/js/idangerous.swiper.min.js"></script>
<script type="text/javascript" src="/js/goods.js"></script>
<script src="http://file.easeeyes.com/wap/js/fastclick.min.js"></script>
<script src="http://file.easeeyes.com/wap/js/tab.js"></script>
<script src="http://file.easeeyes.com/wap/js/random.js"></script>
<script src="js/gbjs/fold.js"></script>
<script src="js/common.js"></script>
<script>      
$(function(){
    // 焦点图
    $('#goodsSlider-count').text($('#swiperBanner .swiper-slide').length)
    var swiperBanner=new Swiper('#swiperBanner',{
        mode:"horizontal",
        calculateHeight:true,
        onSlideChangeEnd:function(swiper){
            $('#goodsSlider-index').text(swiper.activeIndex+1)
        }
    })

    // 缩略图
    var swiperThumb=new Swiper('#thumbGallery-main .swiper-container',{
        calculateHeight:true,
        slidesPerView:5
    })

    // 推荐
    var swiperRec=new Swiper('#swiperRec',{
        calculateHeight:true,
        slidesPerView:3
    })

})



/** fastClick **/
var nav_ot,fixNav,tabNav
window.addEventListener('load',function(){
    FastClick.attach(document.body);
    /**选项卡**/
    tabNav=new Tab({
    	navs:document.querySelectorAll(".detailTab-nav"),
    	tabs:document.querySelectorAll(".detailTab-main")
    })
    /**折叠**/
    var bars=Array.prototype.slice.call(document.querySelectorAll('.detailpage-title'),0),
    	elems=Array.prototype.slice.call(document.querySelectorAll('.detailpage-main'),0);
    bars.forEach(function(bar,index){
    	new Folder({
    		bar:bar,
    		elem:elems[index],
    		state:1
    	})
    });
    // 新增隐藏菜单折叠
    new Folder({
        bar:document.getElementById('menuBar'),
        elem:document.getElementById('menusMore'),
        state:0
    })
    // 新增搜索
    try{
        var searchBar=document.getElementById('search_btn')
        var searchPage=document.getElementById('search-page')
        var searchClose=document.getElementById('search-close')
        var searchIpt=document.getElementById('p-search-input')
        searchBar.addEventListener('click',function(){
            searchPage.style.display='block';
            searchIpt.focus();
        },false)
        searchClose.addEventListener('click',function(){
            searchPage.style.display='none'
        },false)

        var randomData=JSON.parse('<?php echo $this->_var['search_hot']; ?>');  //后台获取
        var showers=document.getElementsByClassName("p-search-key");
        var random=new initMatchColor(randomData,showers);
        document.getElementById("keychange").addEventListener("click",function(){
            var that=this;
            that.classList.remove("clicked");
            new initMatchColor(randomData,showers);
            setTimeout(function(){
                that.classList.add("clicked");
            },20); 
        },false);
        //Cookie管理
        var Cookie={
            get:function(name){
                var cookie_name=encodeURIComponent(name)+"=",
                    start=document.cookie.indexOf(cookie_name),
                    cookie_value=null;
                if(start>-1){
                    var end=document.cookie.indexOf(";",start);
                    if(end>-1){
                        cookie_value=document.cookie.slice(start+cookie_name.length,end);
                    }else{
                        cookie_value=document.cookie.slice(start+cookie_name.length);
                    }
                }
                return decodeURIComponent(cookie_value);
            },
            set:function(options){
                var cookie_txt=encodeURIComponent(options.name)+"="+encodeURIComponent(options.value);
                cookie_txt+=(options.expires instanceof Date)?";expires="+options.expires.toGMTString():";expires="+new Date(new Date().getTime()+3600*1000).toGMTString();
                cookie_txt+=options.path?";path="+options.path:"";
                cookie_txt+=options.domain?";domain="+options.domain:"";
                document.cookie=cookie_txt;
            },
            unset:function(name){
                this.set({
                    name:name,
                    value:"",
                    expires:new Date(0)
                });
            }
        };
        document.getElementById("clearCookie").onclick=function(){
            if(confirm("清空历史记录？")){
                Cookie.unset("search_history");
                document.getElementById("search-history").innerHTML="";
            }
        }

    }catch(e){

    }
    
    
    /**缩略图开关**/
    var thumb_btn=document.getElementById('thumbGallery-btn'),
    	thumb_main=document.getElementById('thumbGallery-main');
    new Folder({
    	bar:thumb_btn,
    	elem:thumb_main,
    	state:0
    });
    
    
    fixNav=$('#detailTab-navs')
    nav_ot=fixNav.offset().top

    // 导航固定
    $(window).scroll(function(){
        var sTop=document.documentElement.scrollTop || document.body.scrollTop
        if(sTop>=nav_ot){
            fixNav.addClass('fixed')
        }else{
            fixNav.removeClass('fixed')
        }
    })

},false);


</script>
</body>
</html>