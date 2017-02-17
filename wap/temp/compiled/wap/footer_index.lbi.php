
<div style="height:3.75rem;clear: both"></div>
<footer class="fixed">
    <div class="f-nav container">
        <a href="/">
            <img src="http://file.easeeyes.com/wap/images/f_home.png" /><br />首页
        </a>
        <a href="javascript:aside.open();">
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

    //头部导航控制
    window.addEventListener("scroll",function(){
        var sTop=parseInt(document.body.scrollTop)||parseInt(document.documentElement.scrollTop),
                head_elem=document.getElementsByTagName("header")[0],
                head_height=parseInt(head_elem.clientHeight);
        sTop>=head_height?head_elem.className="fixed":head_elem.className="";
    },false);

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
        }
    }
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
</script>
