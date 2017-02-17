<?php
/**
 * 民生银行直连支付
 * @author zhuwentao
 */

define('IN_ECS', true);
require(dirname(__FILE__) . '/../../../includes/init.php');
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

require_once("java/Java.inc");


//$str = $back_info->payresult;

$back_info = json_decode('{"payresult":"EoAEAACAAPHsJo37VFlQS67gXR8TVqBmmoQcBFWJprA+CgjePX8jgRLRjtLLmZnY2fAeiOi35nKERgocVnq4OlTY9gSscjhcBPbSUcFPamgz8oQUv1f5AMRpsip3SkFMIhoPWG+r7m7eK0inUD3wDXJV4C7vqf2RImdSUVRAAuVoxS9Vg4CLOL+Nz9LyzPnFsXmYquslzFa0RRwjns1xpMh5tfIFgMopZJp07WgqPsnH1aHTPCE1BHBWA0J1T8gwWPQ02YzfOZ1iC+PCGJpcjokFATIjZGRnohyI/bYP2FbzG8RVZQODaWjFfJnNhbjdBO7YSk66EFNbO6fC+7gOTTXcFdUYhfts7HEKfupHUj4/1wyzmKEjPpYswf7+klyq88VZd6HqpXw1jVKbzFN2v2stAdotbB4H9fgdcaRcEFsVPnZ0J57rrVSGr58LgwM73NI2SogYM7I++0aOfWHeH0wrDt3ZLUkcLUGx0bdASxQ8lmbGCohIzLz7/IUinA47fZahfy7dteJHg8PNd38DIIE5NGhxmdYxCsQYRjUDOP2UNcOAa8N1VrM/379yDQx83OjNNQrralNNGi+vULrnukLl7stsMGNP9kxUSkIUuu2P6AMShNvfmHZY4ugK/o6xmmkyRPlUxeJGbk5dljzWx4bjt3WKR3PxioaMrX/RA7oprqortdgaIMwbl9MltZk3e92p/kIq6OnEj2aIjq+nlpFp1h/fisAs26wkwtD7HtyPiRAnitdmrnZtS+NPqn9+12blD/iXx1iD5e3juhUDgNu2sntTn5xcLLb1Y7r9asUm4a182Rmww7aAPorszb17sA9WhC9AxAF2qbVtOMZrBnMRKQNd4wYAh/UFuX8s0T3TBA8uQ+F0/ZeqFZZBi0dQl26beCze0hU+Qwif66aHFRtEe/qC5Gff4VR3aEw4CbGdAHBfnYSA68mqDUOoctfsT50johnnCYOBcxr9V90oqXtXgl3L1fVc+eVOvIhMmNL5NmBw7f5tqsIPyOPCFVVV5g5f9T4kWBoTy0e55i+TGmzw2Y3Lzwvluf88SdhUad3OPxffZLAjzi8iCHhdcqI380OCu0TweuINnTorxYidkyvum53PUTw8x8+EQhRgAEueJYh335kyUpDgboBfWqltyRbIxwa1oWVurCoe2yt2bx8BoSHEm5sPZjpoUL/J4dwOwtfNw1I/lTfaWhGKig4gBzqYm+rxE/uoAN/b44OtSNfxHY/JeeGUwpgqX/qtl+oaOGvA8Wv8BTFVLXgUnxeNLFTaTkIcp1witn5bgi3jraQgT89oqV873CEmUZ+WtieeM2eXyQtkwlBUj5wr8sP7UbwbA6MEtc6yZ7PmBaryWl0/AsP6cKrWN+l4ci0UYKiT9fYEbj1cqRxDPSmCd2St5T2WC/4IVw+8ogBOo6rwYG0+Lw5Gc3jk2JySyZMl9p5pVtgZQpTB62OUrb/PfaOWugEaGA5XYV5BsF9IjpuCFVfeBcJ/idSaqAKCwfRcVH8NAUhRk4xhczhhXsjYPQf01ejtCubnRJYfbKOO9Zlw3n2T0gvMShpEyPngcsTk7QyWUje0JMyyE10mL/Pj/dkTxpEZORP43it4cz02FNTN3hciRdPpVFmMIR7NZe84rcMiWOmMSofB8GaddhjqgvhH1L0BBHiSY2GMdeRNsV6u9kkLQDnEDJFRbpMM9IWoWB79V60ccQUb"}');


//$back_info = $_REQUEST;//online
//$back_info = $back_info['payresult'];
$back_info = $back_info->payresult;



if($back_info){
    $sign = new java("Union.JnkyServer", '/data/www/easeeyes.com/current/cmbc/banknew1024.cer','/data/www/easeeyes.com/current/cmbc/66002.pfx','1111');
    $decrypt_str = $sign->DecryptData($back_info,"utf-8");   
    
    $decrypt_str = java_values($decrypt_str);
    
    //$GLOBALS['db']->query("INSERT INTO  temp_order SET goods_number = 5,address='".json_encode($_REQUEST)."'");
    //$GLOBALS['db']->query("INSERT INTO  temp_order SET goods_number = 6,address='".$decrypt_str."'");
    
    if(is_string($decrypt_str)){
        //拆分|-》0为支付成功
        $decrypt_str = explode('|',$decrypt_str);
        
        $order_sn   = $decrypt_str[0];
        $corpID     = $decrypt_str[1];
        $amount     = $decrypt_str[2];
        $isTrue     = $decrypt_str[5];//订单状态
        if($isTrue == 0){
            
        //order_sn查询order_id-》order_id查询log_id
        $order_id = $GLOBALS['db']->getOne("SELECT order_id FROM ".$GLOBALS['ecs']->table('order_info')."  WHERE order_sn = ".$order_sn." LIMIT 1");
        $log_id   = $GLOBALS['db']->getOne("SELECT log_id FROM ".$GLOBALS['ecs']->table('pay_log')."  WHERE order_id = ".$order_id." LIMIT 1");
        //order_paid($log_id);
        }

        
    }
    
        
    assign_template();
    /*------------------------------------页头 页尾 数据---------------------------------------*/
    $position = assign_ur_here();
    $smarty->assign('page_title',           $position['title']);    
    $smarty->assign('ur_here',              $position['ur_here']);  
    $smarty->assign('topbanner',            ad_info(31,1));           //头部横幅广告
    //页尾
    $smarty->assign('helps',                get_shop_help());         //网店帮助文章
    $smarty->assign('new_articles_botter',  index_get_new_articles_botter());//关于我们行	
    $smarty->assign('botbanner',            ad_info(12,8));           //营业执照行
    $cat_tree = get_category_tree();                     			  //分类列表
    $smarty->assign('cat_1',        		$cat_tree[1]);
    $smarty->assign('cat_6',				$cat_tree[6]);
    $smarty->assign('cat_64',				$cat_tree[64]);
    $smarty->assign('cat_76',				$cat_tree[76]);	
    $smarty->assign('cat_159',				$cat_tree[159]);
    $smarty->assign('cat_190',				$cat_tree[190]);
    $smarty->assign('sale_order1',  		yi_sale_sort_list(1) );	  //热销排行
    $smarty->assign('sale_order6',  		yi_sale_sort_list(6) );
    $smarty->assign('sale_order64', 		yi_sale_sort_list(64));
    $smarty->assign('sale_order76', 		yi_sale_sort_list(76));
    $smarty->assign('sale_order159', 		yi_sale_sort_list(159));
    $smarty->assign('sale_order190', 		yi_sale_sort_list(190));
    /*------------------------------------页头 页尾 数据end------------------------------------*/
    
    $smarty->assign('message',    $msg);
    $smarty->assign('shop_url',   $ecs->url());
    $smarty->assign('pay_code',   $pay_code);//支付方式代码
    
    $smarty->display('respond.dwt');

}
    



/*
$a = "abc123456";
$m=$sign2->EnvelopData($a, 'utf-8');
$e=java_values($m);
print_r($e);

$sign2->DecryptData($e, 'utf-8');
*/



/*

$str = json_decode('{"payresult":"EoAEAACAAPHsJo37VFlQS67gXR8TVqBmmoQcBFWJprA+CgjePX8jgRLRjtLLmZnY2fAeiOi35nKERgocVnq4OlTY9gSscjhcBPbSUcFPamgz8oQUv1f5AMRpsip3SkFMIhoPWG+r7m7eK0inUD3wDXJV4C7vqf2RImdSUVRAAuVoxS9Vg4CLOL+Nz9LyzPnFsXmYquslzFa0RRwjns1xpMh5tfIFgMopZJp07WgqPsnH1aHTPCE1BHBWA0J1T8gwWPQ02YzfOZ1iC+PCGJpcjokFATIjZGRnohyI/bYP2FbzG8RVZQODaWjFfJnNhbjdBO7YSk66EFNbO6fC+7gOTTXcFdUYhfts7HEKfupHUj4/1wyzmKEjPpYswf7+klyq88VZd6HqpXw1jVKbzFN2v2stAdotbB4H9fgdcaRcEFsVPnZ0J57rrVSGr58LgwM73NI2SogYM7I++0aOfWHeH0wrDt3ZLUkcLUGx0bdASxQ8lmbGCohIzLz7/IUinA47fZahfy7dteJHg8PNd38DIIE5NGhxmdYxCsQYRjUDOP2UNcOAa8N1VrM/379yDQx83OjNNQrralNNGi+vULrnukLl7stsMGNP9kxUSkIUuu2P6AMShNvfmHZY4ugK/o6xmmkyRPlUxeJGbk5dljzWx4bjt3WKR3PxioaMrX/RA7oprqortdgaIMwbl9MltZk3e92p/kIq6OnEj2aIjq+nlpFp1h/fisAs26wkwtD7HtyPiRAnitdmrnZtS+NPqn9+12blD/iXx1iD5e3juhUDgNu2sntTn5xcLLb1Y7r9asUm4a182Rmww7aAPorszb17sA9WhC9AxAF2qbVtOMZrBnMRKQNd4wYAh/UFuX8s0T3TBA8uQ+F0/ZeqFZZBi0dQl26beCze0hU+Qwif66aHFRtEe/qC5Gff4VR3aEw4CbGdAHBfnYSA68mqDUOoctfsT50johnnCYOBcxr9V90oqXtXgl3L1fVc+eVOvIhMmNL5NmBw7f5tqsIPyOPCFVVV5g5f9T4kWBoTy0e55i+TGmzw2Y3Lzwvluf88SdhUad3OPxffZLAjzi8iCHhdcqI380OCu0TweuINnTorxYidkyvum53PUTw8x8+EQhRgAEueJYh335kyUpDgboBfWqltyRbIxwa1oWVurCoe2yt2bx8BoSHEm5sPZjpoUL/J4dwOwtfNw1I/lTfaWhGKig4gBzqYm+rxE/uoAN/b44OtSNfxHY/JeeGUwpgqX/qtl+oaOGvA8Wv8BTFVLXgUnxeNLFTaTkIcp1witn5bgi3jraQgT89oqV873CEmUZ+WtieeM2eXyQtkwlBUj5wr8sP7UbwbA6MEtc6yZ7PmBaryWl0/AsP6cKrWN+l4ci0UYKiT9fYEbj1cqRxDPSmCd2St5T2WC/4IVw+8ogBOo6rwYG0+Lw5Gc3jk2JySyZMl9p5pVtgZQpTB62OUrb/PfaOWugEaGA5XYV5BsF9IjpuCFVfeBcJ/idSaqAKCwfRcVH8NAUhRk4xhczhhXsjYPQf01ejtCubnRJYfbKOO9Zlw3n2T0gvMShpEyPngcsTk7QyWUje0JMyyE10mL/Pj/dkTxpEZORP43it4cz02FNTN3hciRdPpVFmMIR7NZe84rcMiWOmMSofB8GaddhjqgvhH1L0BBHiSY2GMdeRNsV6u9kkLQDnEDJFRbpMM9IWoWB79V60ccQUb"}');


*/