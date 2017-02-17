<?php

define('IN_ECS', true);
require_once(dirname(__FILE__) . '/../../includes/init.php');
date_default_timezone_set('Asia/Shanghai');
header("Content-type: text/xml");


$id_list = get_id_list();
//$count = get_id_count();



    $id_str = '';
    //for($i=0;$i<$count['num'];$i++){
      //$id_str .='<id>'.$id_list[$i]['goods_id'].'</id>';
    //}

	foreach($id_list as $v) {//
		$id_str .='<id>'.$v['goods_id'].'</id>';
	}

    
    $xml = '';
    $xml .= '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<item>';
    $xml .= $id_str;
    $xml .= '</item>';
echo $xml;

function get_id_list() {
        $sql = "SELECT goods_id FROM  " . $GLOBALS['ecs']->table('goods') . " WHERE cat_id <> 138 AND is_on_sale=1 AND is_alone_sale=1 AND is_delete=0 ";
	$result = $GLOBALS['db'] -> getAll($sql);
	return $result;
}

function get_id_count() {
        $sql = "SELECT count(goods_id) as num FROM  " . $GLOBALS['ecs']->table('goods');
	$result = $GLOBALS['db'] -> getRow($sql);
	return $result;
}
?>