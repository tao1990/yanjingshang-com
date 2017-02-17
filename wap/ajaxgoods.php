<?php
header("content-Type: text/html; charset=utf-8");
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$keyword=$_GET['keyword'];
if($keyword == ''){	$keyword = 0; }

//选择品牌后----产品列表---
$sqlz="SELECT goods_id,goods_name FROM " . $GLOBALS['ecs']->table('goods') . " where cat_id=".$keyword." AND is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 ORDER BY goods_id DESC;";
$resz = $db->query($sqlz);
$bz=0;      //步骤
$str1="<select name='goods_id' class='pro_top_link_selss' style='width:360px;'><option value=''>请选择产品</option>";
$str = '';
$str2 = '';
$listz = array();
while($rowz = $db->fetchRow($resz)){
	$bz=1;
	$str.="<option value=".$rowz['goods_id'].">".$rowz['goods_name']."</option>";
}
$str2.="</select>";

if($bz){
	$str=$str1.$str.$str2;
}else{
	$str='&nbsp;<select name="goods_id" class="pro_top_link_selss"><option value="">请选择产品</option></select>';
}
echo $str;
?>