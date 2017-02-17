<?php

define('IN_ECS', true);
require('../includes/init.php');


$act = isset($_REQUEST['act']) ? $_REQUEST['act'] :'';

if($act == 'ajax'){
    $sanguang = $_POST['cc'] ? explode('-',$_POST['cc']):'';

    if($sanguang && $sanguang > 0.00){
        $jinshi = $_POST['aa']+$_POST['bb']+(end($sanguang)/2);
    }else{
        $jinshi = $_POST['aa']+$_POST['bb'];
    }


    if($jinshi < 4){
        $dushu = $jinshi;
        echo json_encode(array('status' => 1 , 'msg'=>'success' ,'textHtml'=> -$jinshi)); exit;
    }else if($jinshi >= 4.00 && $jinshi <= 5.25){
        $dushu  = $jinshi - 0.25;
        echo json_encode(array('status' => 2 , 'msg'=>'success' ,'textHtml'=> -$dushu)); exit;
    }else if($jinshi >= 5.50 && $jinshi <= 7){
        $dushu  = $jinshi - 0.50;
        echo json_encode(array('status' => 2 , 'msg'=>'success' ,'textHtml'=> -$dushu)); exit;
    }else if($jinshi >= 7.25 && $jinshi <= 8.5){
        $dushu  = $jinshi - 0.75;
        echo json_encode(array('status' => 2 , 'msg'=>'success' ,'textHtml'=> -$dushu)); exit;
    }else if($jinshi >=8.75 && $jinshi <=9.75){
        $dushu  = $jinshi - 1.00;
        echo json_encode(array('status' => 2 , 'msg'=>'success' ,'textHtml'=> -$dushu)); exit;
    }else if($jinshi >=10.00 && $jinshi <=11.00){
        $dushu  = $jinshi - 1.25;
        echo json_encode(array('status' => 2 , 'msg'=>'success' ,'textHtml'=> -$dushu)); exit;
    }else if($jinshi >=11.25){
        $dushu  = $jinshi - 1.50;
        echo json_encode(array('status' => 2 , 'msg'=>'success' ,'textHtml'=> -$dushu)); exit;
    }
}





$smarty->display('myopia_convert1.dwt');



?>