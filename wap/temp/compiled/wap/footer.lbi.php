
<div style="height:3.75rem;clear: both"></div>
<footer class="fixed">
    <div class="f-nav container">
        <a href="/">
            <img src="http://file.easeeyes.com/wap/images/f_home.png" /><br />首页
        </a>
        <a href="javascript:" id="btn_menu">
            <img src="http://file.easeeyes.com/wap/images/f_cate.png" /><br />分类
        </a>
        <a href="flow.php">
            <img src="http://file.easeeyes.com/wap/images/f_cars.png" /><br />购物车
            <span class="pg-cars-num"><?php 
$k = array (
  'name' => 'cart_num',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></span>
        </a>
        <?php if ($_SESSION['user_id'] != 0): ?>
        <a href="user.php">
            <img src="http://file.easeeyes.com/wap/images/f_member.png" /><br />个人中心
        </a>
        <?php else: ?>
        <a href="user.php">
            <img src="http://file.easeeyes.com/wap/images/f_member.png" /><br />注册/登陆
        </a>
        <?php endif; ?>
    </div>
</footer>

<span id="goTop"></span>

<div id="shop_cars"></div>


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
            <?php $_from = $this->_var['search_history']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v_0_94566900_1485321099');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v_0_94566900_1485321099']):
?>
            <li><a href="category.php?keyword=<?php echo $this->_var['v_0_94566900_1485321099']; ?>"><?php echo $this->_var['v_0_94566900_1485321099']; ?></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    
    <div class="p-search-clearbtn">
        <button id="clearCookie">清空历史搜索</button>
    </div>
</div>


<script src="http://file.easeeyes.com/wap/js/gotop.js"></script>
<script>
    //自适应代码
    (function(){
        var win=window||{},
                doc=document,
                root=doc.documentElement;
        function changeSize(){
            root.style.fontSize=parseInt(root.clientWidth)*20/640>20?"20px":parseInt(root.clientWidth)*20/640+"px";
        }
        if(doc.addEventListener){
            var evt="orientationchange" in win?"orientationchange":"resize";
            doc.addEventListener(evt,changeSize,false);
            doc.addEventListener("DOMContentLoaded",changeSize,false);
        }
    })();

    //侧边导航控制
    var aside={
        G_aside_left:document.getElementById("aside_main"),
        G_aside_right:document.getElementById("aside_close"),
        open:function(){
            this.G_aside_left.className="show";
            this.G_aside_right.className="show";
        },
        close:function(){
            this.G_aside_left.className="hide";
            this.G_aside_right.className="hide";
        },
        init:function(){
            var that=this;
            document.getElementById("btn_menu").addEventListener("click",function(){
                that.open.apply(that,arguments);
            },false);
            that.G_aside_right.addEventListener("click",function(){
                that.close.apply(that,arguments);
            },false);
        }
    }
    aside.init();
    /**新增20150908侧导航**/
    function toggleClass(elem,arr){
        elem.className=elem.className==arr[0]?arr[1]:arr[0];
    }
    function hasClass(el,cname){
        var arr=el.className.split(" "),
                i,len=arr.length;
        for(i=0;i<len;i++){
            if(arr[i]==cname)	return true;
        }
        return false;
    }
    function switchClass(elem,cname){
        elem.className=hasClass(elem,cname)?elem.className.replace(cname,""):elem.className+" "+cname;
    }
    // 品牌显隐控制
    var brands_banner=document.getElementsByClassName("brands-main-banner"),
            i,len=brands_banner.length;
    for(i=0;i<len;i++){
        brands_banner[i].addEventListener("click",function(){
            switchClass(this,"close");
        },false);
    }
   
    // 显隐控制
    function ShowControl(opts){
        var opts=opts||{};
        this.bar=opts.bar||{};
        this.elem=opts.elem||{};
        this.init.apply(this,arguments);
    }
    ShowControl.prototype.init=function(){
        var that=this,
            bar=that.bar,
            elem=that.elem;
        elem.style.display="none";
        bar.dataset.clicked=false;  
        bar.addEventListener("click",function(){
            if(bar.dataset.clicked=="false"){
                elem.style.display="block";
                bar.dataset.clicked=true;
            }else{
                elem.style.display="none";
                bar.dataset.clicked=false;
            }
        },false);
        document.addEventListener("click",function(e){
            if(e.target!=bar && e.target!=elem && elem.compareDocumentPosition(e.target)!=16 && elem.compareDocumentPosition(e.target)!=20){
                elem.style.display="none";
                bar.dataset.clicked=false;
            }
        },false);
    }
    var searchControl=new ShowControl({
        bar:document.getElementById("search-switch"),
        elem:document.getElementById("search-main")
    });

    // 搜索卡
    document.getElementById("search-main").getElementsByTagName("input")[0].addEventListener("focus",function(){
        document.getElementById("search-page").style.display="block";
        document.getElementById("search-close").dataset.clicked=true;
        document.getElementById("search-main").style.display="none";
        document.getElementById("p-search-input").focus();
    },false);
    var searchControl=new ShowControl({
        bar:document.getElementById("search-close"),
        elem:document.getElementById("search-page")
    });
</script>
<script src="http://file.easeeyes.com/wap/js/random.js"></script>
<script>
// 随机关键词
var randomData=JSON.parse('<?php echo $this->_var['search_hot']; ?>');  //后台获取
//document(randomData);
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

</script>
