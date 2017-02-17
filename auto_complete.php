<?php
header("content-Type: text/html; charset=utf-8");
define('IN_ECS', true);
require_once(dirname(__FILE__) . '/includes/init.php');

$q = strtolower($_GET["q"]);
if (!$q) return;


$items = array();
$sql = "select goods_id,goods_name from ".$GLOBALS['ecs']->table('goods')." where goods_name like '%$q%' limit 0,20";
$res = mysql_query($sql);

while($row = mysql_fetch_array($res))
{
	$items[$row['goods_name']] = $row['goods_id'] ;
}

foreach ($items as $key=>$value) {
	if (strpos(strtolower($key), $q) !== false) {
		echo "$key|$value\n";
	}
}
?>