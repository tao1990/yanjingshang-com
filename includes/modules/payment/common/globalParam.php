<?php
$GLOBALS['localAddr'] = "http://127.0.0.1/HiSimplePHP";                                
$GLOBALS['characterSet'] = "00"; //00--GBK;01--GB2312;02--UTF-8 //�ַ���������                         
$GLOBALS['callbackUrl'] = $GLOBALS['localAddr']."/back_url.php";//�ص�url                                                                                                                     
$GLOBALS['notifyUrl'] = $GLOBALS['localAddr']."/notify_url.php";                                   
$GLOBALS['requestId'] = strtotime("now");                      
$GLOBALS['signType'] = "MD5";                                                            
$GLOBALS['version'] = "2.0.0"; 

$GLOBALS['merchantId'] = "�̻���id";                                                       
$GLOBALS['signKey'] = "�̻���Կ";
$GLOBALS['reqUrl'] ="https://ipos.10086.cn/ips/cmpayService";
?>