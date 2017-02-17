<?php
date_default_timezone_set('PRC');

$strKey = 'sZrLozDnF260MF9e';	//商户密匙
$strDate = date('Ymd', time());	//订单日期
$strBranchID = '0021';			//开户分行号
$strCono = '003427';			//商户号
$strBillNo = '000001';	//订单号
$strAmount = '123.12';			//订单金额
$strMerchantPara = '2013112584188';			//商户自定义参数(易视订单号)
$strMerchantUrl = 'http://www.easeeyes.com/respond.php';	//商户接受通知的URL
$strPayerID = '64660';			//付款方用户标识
$strPayeeID = '15';				//收款方的用户标识
$strClientIP = '127.0.01';		//商户取得的客户端IP
$strGoodsType = '54011600';		//商品类型
$strReserved = '';				//保留