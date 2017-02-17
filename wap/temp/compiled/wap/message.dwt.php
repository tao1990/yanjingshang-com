<!DOCTYPE html>
<head>
<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
	<meta name="apple-mobile-web-app-capable" content="yes"/>
	<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<?php if ($this->_var['auto_redirect']): ?><meta http-equiv="refresh" content="3;URL=<?php echo $this->_var['message']['back_url']; ?>" /><?php endif; ?>
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<link rel="icon" href="animated_favicon.gif" type="image/gif" />
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link href="themes/default/style/base.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.prompt{margin-bottom: 20px;}
.prompt h1{text-align:center;margin-top:20px;margin-bottom:10px;font-size:16px}
.prompt_button{margin-top:15px;margin-left:30%;width:37%;border:1px solid;border-radius:9px; background:#75D6E9;color:#fff;padding:4px 0; text-align:center; display:block;font-weight:bold}
</style>
</head>
<body>
<?php echo $this->fetch('library/header.lbi'); ?>
<script type="text/javascript" src="<?php echo $this->_var['image_url']; ?>js/yi_common.js"></script>
<div class="prompt">
  <h1>
    <?php if ($this->_var['message']['content']): ?>
        <?php echo $this->_var['message']['content']; ?>
    <?php else: ?>
        问题已提交成功，我们会及时处理，谢谢您的支持！
    <?php endif; ?>
  </h1>
  <input type="button" onclick="javascript:window.location.href='./'" value="返回首页" class="prompt_button"/>
  <input type="button" onclick="javascript:window.location.href='<?php echo $this->_var['message']['back_url']; ?>'" value="<?php echo $this->_var['link']; ?>" class="prompt_button"/>
</div>
<?php echo $this->fetch('library/footer.lbi'); ?>
</body>
</html>