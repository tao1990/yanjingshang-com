<?php


//yi:Ìø×ª²ÎÊý
$jump = isset($_REQUEST['back_act'])? $_REQUEST['back_act']: '';

if(!empty($jump) || $_REQUEST['jump'] == 'fd')
{
	
	if($_REQUEST['jump']=='fd'){
		$jump_url = "http://www.easeeyes.com/flow.php?step=checkout";
	}else{
		$jump_url = $jump;
	}
	setcookie("jump_url", $jump_url, time()+3600, "/");
}
else
{
	setcookie("jump_url", '', time()-3600, "/");
}



require_once("../API/qqConnectAPI.php");
$qc = new QC();
$qc->qq_login();

