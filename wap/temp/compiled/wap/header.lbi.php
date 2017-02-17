<script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "//hm.baidu.com/hm.js?0b2bbbfa2efacf5d67553c9fddab504d";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
<link rel="shortcut icon" type="image/ico" href="/favicon.ico" />
<link href="http://file.easeeyes.com/wap/css/style.css" rel="stylesheet" />
<script src="<?php echo $this->_var['file_url']; ?>js/jquery.js"></script>


<div id="aside_main">
    <ul>
        <?php $_from = $this->_var['menu_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v']):
?>
        
        <li class="aside-list <?php if ($this->_var['k'] == 1): ?> open<?php else: ?> close<?php endif; ?>">
            <h2 onclick="toggleClass(this.parentNode,['aside-list open','aside-list close'])"><span class="pull-right">^</span><?php echo $this->_var['v']['cat_name']; ?></h2>
            
            <div class="aside-navs">
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



<div class="container inside-header">
    <a href="javascript:history.back();" class="pull-left btn-back"></a>
    <span class="pull-right btn-right" id="search-switch"></span>
    <h2 class="text-center"><?php echo $this->_var['ur_here']; ?></h2>
    <div class="search-box" id="search-main">
        <form action="category.php" method="get">
            <input type="text" name="keyword" placeholder="博士伦纯视" />
        </form>
    </div>
</div>