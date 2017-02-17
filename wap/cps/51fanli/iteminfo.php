<?php

define('IN_ECS', true);
require_once(dirname(__FILE__) . '/../../includes/init.php');
date_default_timezone_set('Asia/Shanghai');
header("Content-type: text/xml");
$goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : '';

$goods_info = get_goods_infos($goods_id);


if($goods_info){
    
    $mainImg = getImg($goods_info['goods_id'],1);
    $extraImg = getImg($goods_info['goods_id'],0);
    $e_count = count($extraImg);
    $extra_pic_str = '';
    for($i=0;$i<$e_count;$i++){
      $extra_pic_str .=xmlencode_cdata('<img><url><![CDATA[http://www.easeeyes.com/'.$extraImg[$i]['img_original'].']]></url><size>400x400</size></img>');
    }
    
    $xml = '';
    $xml .= '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<item>';
    $xml .= '<id>'.$goods_info['goods_id'].'</id>';
    $xml .= xmlencode_cdata('<title><![CDATA['.xmlencode($goods_info['goods_name']).']]></title>');
    $xml .= xmlencode_cdata('<url><![CDATA[http://www.easeeyes.com/goods'.$goods_info['goods_id'].'.html]]></url>');
    $xml .= xmlencode_cdata('<url_wap><![CDATA[http://www.easeeyes.com/goods'.$goods_info['goods_id'].'.html]]></url_wap>');
    $xml .= '<price>'.$goods_info['shop_price'].'</price>';
    $xml .= '<wap_price>'.$goods_info['shop_price'].'</wap_price>';
    $xml .= imgUrlChange(xmlencode_cdata('<detail><![CDATA['.xmlencode($goods_info['goods_desc']).']]></detail>'));
    $xml .= '<status>'.$goods_info['is_on_sale'].'</status>';
    $xml .= xmlencode_cdata('<pic_main>
                <img>
                    <url><![CDATA[http://www.easeeyes.com/'.$mainImg[0]['img_original'].']]></url>
                    <size>400x400</size>
                </img>
            </pic_main>');//主图
    $xml .= '<pic_extra>
            '.$extra_pic_str.'
            </pic_extra>';//列表
    $xml .= '</item>';
    
}
echo $xml;
//echo xmlencode_cdata($xml);

//根据ID获取商品信息
function get_goods_infos($goods_id) {
    if(!$goods_id){
        exit();
    }else{
        $goods_id = addslashes($goods_id);
        $sql = "SELECT * FROM  " . $GLOBALS['ecs']->table('goods') . 'WHERE goods_id = ' . $goods_id;
	$result = $GLOBALS['db'] -> getRow($sql);
	return $result;
    }	
}


//根据商品id取图片;type:1主图;type0：列表图
function getImg($goods_id,$type=1){
    if(!$goods_id){
        exit();
    }else{
        $goods_id = addslashes($goods_id);
        $str = '';
        if($type==0){
            $str = ' and is_main = 0 limit 0,5';
        }else{
            $str = ' and is_main = 1';
        }
        
        $sql = "SELECT * FROM  " . $GLOBALS['ecs']->table('goods_gallery') . 'WHERE goods_id = ' . $goods_id.$str;
	$result = $GLOBALS['db'] -> getAll($sql);
	return $result;
    }
    
}


//xml转义
function xmlencode($tag) {
	$tag = str_replace("&", "&amp;", $tag);
	$tag = str_replace("<", "&lt;", $tag);
	$tag = str_replace(">", "&gt;", $tag);
	$tag = str_replace("'", "&apos;", $tag);
	$tag = str_replace('"', '&quot;', $tag);
    $tag = str_replace('<![CDATA[', '&lt;![CDATA', $tag);
	$tag = str_replace(']]>', ']]&gt;', $tag);
	return $tag;
}
//cdata转义
function xmlencode_cdata($xml){
    $xml = str_replace('<![CDATA[', '&lt;![CDATA[', $xml);
	$xml = str_replace(']]>', ']]&gt;', $xml);
	return $xml;
}

//描述中图片改为绝对路径//src="/images
function imgUrlChange($desc){
    $desc = str_replace('src=&quot;/images', 'src=&quot;http://www.easeeyes.com/images', $desc);
	return $desc;
}
?>