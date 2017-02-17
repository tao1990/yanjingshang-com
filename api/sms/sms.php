<?php

function Post($data, $target) {
    $url_info = parse_url($target);
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader .= "Content-Length:" . strlen($data) . "\r\n";
    $httpheader .= "Connection:close\r\n\r\n";
    //$httpheader .= "Connection:Keep-Alive\r\n\r\n";
    $httpheader .= $data;

    $fd = fsockopen($url_info['host'], 80);
    fwrite($fd, $httpheader);
    $gets = "";
    while(!feof($fd)) {
        $gets .= fread($fd, 128);
    }
    fclose($fd);
    if($gets != ''){
        $start = strpos($gets, '<?xml');
        if($start > 0) {
            $gets = substr($gets, $start);
        }        
    }
    return $gets;
}


/**
 * 
 * 短信发送方法
 * @param $msg_number	手机号
 * @param $msg			短信内容
 * @return	0:发送成功
 */

function sms_send($msg_number,$msg){
	$res = -99;
	$target = "http://175.102.15.131/msg/HttpBatchSendSM";
	//替换成自己的测试账号,参数顺序和wenservice对应
	$post_data = "account=yunjingshang&pswd=gJeEzEp@50M&mobile=".$msg_number."&msg=".rawurlencode($msg)."&needstatus=true";
	$statusCode = Post($post_data, $target);
    $statusCode = explode('close',$statusCode);
    $statusCode = explode(',',$statusCode[1]);
    $statusCode = $statusCode[1];
    if($statusCode == 0){
        $res = 0;
    }
	//请自己解析$gets字符串并实现自己的逻辑
	//<State>0</State>表示成功,其它的参考文档

	return $res;
}

/**
 * 
 * 短信发送方法(B2B)
 * @param $msg_number	手机号
 * @param $msg			短信内容
 * @return	0:发送成功
 */

function sms_send_b2b($msg_number,$msg){
	$res = -99;
	$target = "http://175.102.15.131/msg/HttpBatchSendSM";
	//替换成自己的测试账号,参数顺序和wenservice对应
	$post_data = "account=yunjingshang&pswd=gJeEzEp@50M&mobile=".$msg_number."&msg=".rawurlencode($msg)."&needstatus=true";
	$statusCode = Post($post_data, $target);
    $statusCode = explode('close',$statusCode);
    $statusCode = explode(',',$statusCode[1]);
    $statusCode = $statusCode[1];
    if($statusCode == 0){
        $res = 0;
    }
	//请自己解析$gets字符串并实现自己的逻辑
	//<State>0</State>表示成功,其它的参考文档

	return $res;
}
/*
function sms_send($msg_number,$msg){
	$res = -99;
	$target = "http://cf.lmobile.cn/submitdata/Service.asmx/g_Submit";
	//替换成自己的测试账号,参数顺序和wenservice对应
	$post_data = "sname=dlmtmy00&spwd=yishi888&scorpid=&sprdid=1012818&sdst=".$msg_number."&smsg=".rawurlencode($msg);
	$statusCode = Post($post_data, $target);
	//请自己解析$gets字符串并实现自己的逻辑
	//<State>0</State>表示成功,其它的参考文档
	$statusCode	= simplexml_load_string($statusCode);
	if($statusCode){
		$res = $statusCode->State;
	}
	return $res;
}
*/
?>
