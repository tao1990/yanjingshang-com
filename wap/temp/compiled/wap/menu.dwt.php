<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta content="telephone=no" name="format-detection"/>
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link rel="stylesheet" type="text/css" href="css/menu.css"/>
</head>
<body>

<?php echo $this->fetch('library/header.lbi'); ?>
<div class="content" >
<ul class="menu_ul">
    <?php $_from = $this->_var['menu_list_bot']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v']):
?>
    <li id="menu_li_<?php echo $this->_var['k']; ?>">
        <h3 class="menu_h3" id="menu_h3_<?php echo $this->_var['k']; ?>">
        <a href="#" >
            <i><img src="<?php echo $this->_var['image_url']; ?>wap/images/menu_<?php echo $this->_var['k']; ?>.png" /></i>
            <abbr><?php echo $this->_var['v']['1']; ?></abbr>
        </a>
        </h3>
        <div class="collapsible" id="collapsible_<?php echo $this->_var['k']; ?>">
                    <?php $_from = $this->_var['v']['son']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'v2');if (count($_from)):
    foreach ($_from AS $this->_var['v2']):
?>
                    <a  href='category.php?cat_id=<?php echo $this->_var['v2']['cat_id']; ?>'><?php echo $this->_var['v2']['cat_name']; ?></a>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </li>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
</div>
<?php echo $this->fetch('library/footer.lbi'); ?>
<div id="bt"></div>
<script>
$(".menu_h3").bind("click",function(){
        focusId = $(this).attr('id');
        focusDivId = $("#"+focusId).next().attr('id');
        if($("#"+focusDivId).css('display')=="none"){
            $(".collapsible").css('display','none');
            $("#"+focusDivId).show();
        }else{
            $("#"+focusDivId).hide();
        }
});
</script>
</body>
</html>
