<?php
include_once '../util/Config.php';
include_once ("Adenter.php");

	/**
	 * ��������
	 *
	 * ==============================================================================================================================================
	 * ���ã�
	 * 		����������ز�����֮�����AdEnter#jump($src,$channel,$campagin_id,$yiqifa_wi,$target_url}������
	 *
     * 
     *  http://192.168.1.52:3002/cps/yiqifa/trunk/advertiser/CallAdenter.php?source=emar&channel=cps&cid=101&wi=NDgwMDB8dGVzdA==&target=http://192.168.1.52:3002
     * 
	 * ==============================================================================================================================================
     * @auther lsj
	 * @see Adenter 
	 * @version 0.2
	 */
     
     
	
	$yqf_src = $_GET ['source'];
	$yqf_channel = $_GET ['channel'];
	$yqf_cid = $_GET ['cid'];
	$yqf_wi = $_GET ['wi'];
	$target_url = $_GET ['target'];
	
	$write_cookie = new Adenter();
	$write_cookie->jump ( $yqf_src, $yqf_channel, $yqf_cid, $yqf_wi, $target_url );
?>