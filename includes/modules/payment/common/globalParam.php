<?php
$GLOBALS['localAddr'] = "http://127.0.0.1/HiSimplePHP";                                
$GLOBALS['characterSet'] = "00"; //00--GBK;01--GB2312;02--UTF-8 //字符编码设置                         
$GLOBALS['callbackUrl'] = $GLOBALS['localAddr']."/back_url.php";//回调url                                                                                                                     
$GLOBALS['notifyUrl'] = $GLOBALS['localAddr']."/notify_url.php";                                   
$GLOBALS['requestId'] = strtotime("now");                      
$GLOBALS['signType'] = "MD5";                                                            
$GLOBALS['version'] = "2.0.0"; 

$GLOBALS['merchantId'] = "商户号id";                                                       
$GLOBALS['signKey'] = "商户密钥";
$GLOBALS['reqUrl'] ="https://ipos.10086.cn/ips/cmpayService";
?>