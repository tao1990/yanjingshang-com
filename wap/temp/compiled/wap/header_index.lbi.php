<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?778135963195c6f49b680e070b0b3724";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
<link rel="shortcut icon" type="image/ico" href="/favicon.ico" />
<link href="css/style2.css" rel="stylesheet" />
<script src="http://file.yunjingshang.com/js/jquery.min.js"></script>
<script type="text/javascript" src="http://tajs.qq.com/gdt.php?sId=48626407" charset="UTF-8"></script>

<div id="aside_main">
    <ul>
        <?php $_from = $this->_var['menu_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v']):
?>
        
        <li class="aside-list <?php if ($this->_var['k'] == 1): ?>open<?php else: ?>close<?php endif; ?>">
            <h2 onclick="toggleClass(this.parentNode,['aside-list open','aside-list close'])"><span class="pull-right">&and;</span><?php echo $this->_var['v']['cat_name']; ?></h2>
            
            <div class="aside-navs">
                <a href="category.php?cat_id=<?php echo $this->_var['k']; ?>" style="font-weight: 600;">全部</a>
                <?php $_from = $this->_var['v']['qbpp']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'v2');if (count($_from)):
    foreach ($_from AS $this->_var['v2']):
?>
                <a href="category.php?cat_id=<?php echo $this->_var['v2']['cat_id']; ?>" <?php if ($this->_var['v2']['is_show_red'] == 1): ?> class="hot"<?php endif; ?>><?php echo $this->_var['v2']['cat_name']; ?></a>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
        </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
</div>
<div id="aside_close"><span onclick='aside.close()'><i>&times;</i></span></div>


<header class="fixed">
    <div class="container">
        <div class="logo pull-left">
            <a href="/">
                <img src="http://file.yunjingshang.com/wap/images/index/02.jpg" />
            </a>
        </div>
        <div class="search pull-left">
            <form action="category.php" method="get">
                <input type="text" name="keyword" placeholder="博士伦纯视" />
            </form>
        </div>
        <div class="cars pull-right">
            <a href="flow.php">
                <img src="http://file.easeeyes.com/wap/images/cars.png" />购物车
                <span class="pg-cars-num"><?php 
$k = array (
  'name' => 'cart_num',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></span>
            </a>
        </div>
    </div>
</header>