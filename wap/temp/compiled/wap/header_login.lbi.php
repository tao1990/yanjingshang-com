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
<!--<link href="http://file.easeeyes.com/wap/css/style.css" rel="stylesheet" />-->
<script src="http://file.yunjingshang.com/js/jquery.js"></script>

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
<div id="aside_close"><span><i>&times;</i></span></div>


<div class="container">

    
    <div class="title-header">
        <h2 class="text-center">
            <a href="javascript:history.back();" class="btn-back"></a>
            <?php echo $this->_var['ur_here']; ?>
        </h2>
    </div>
</div>