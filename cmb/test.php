<?php
define('IN_ECS', true);
include_once('../includes/modules/payment/cmb.php');

/*$order['add_time'] = '1386735202';
$order['order_id'] = '129091';
$order['order_amount']= '54.32';
$order['order_sn'] = '2013121185889';
$order['user_id'] = '64660';

$t = new cmb();
$str = $t->get_code($order, $payment);

echo $str;*/

/*require_once("Java.inc");
//require_once("config.php");

$test = new java("cmb.netpayment.Security", '/data/www/cmb/public.key');

$get_param = 'Succeed=Y&CoNo=003427&BillNo=0000127100&Amount=0.01&Date=20131129&MerchantPara=2013112913375&Msg=00210034272013112913312902200000002200&Signature=26|23|137|168|93|51|125|205|213|120|123|4|106|64|58|245|39|22|18|217|233|250|105|76|165|21|252|83|26|86|208|75|21|125|164|48|190|201|160|137|41|125|228|44|211|185|105|204|76|170|155|188|140|240|115|179|105|135|16|132|166|123|196|8|';
$bytes_array = getBytes($get_param);

$sign_is_true = FALSE;

$sign_is_true = $test->checkInfoFromBank($bytes_array);

if ($sign_is_true === TRUE) {
	echo 'ok';
} else {
	echo 'false';
}*/
//print_r($a);

//$str = $test->checkInfoFromBank();
//echo $str;

//$test = new java("cmb.MerchantCode");

//$MerchantCode = $test->genMerchantCode($strKey, $strDate, $strBranchID, $strCono, $strBillNo, $strAmount, $strMerchantPara, $strMerchantUrl, $strPayerID, $strPayeeID, $strClientIP, $strGoodsType, $strReserved);
//echo $str;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMB</title>
</head>

<body>


</body>
</html>

<?php
function getBytes($str) {
	$len = strlen($str);
	$bytes = array();
	   for($i=0;$i<$len;$i++) {
		   if(ord($str[$i]) >= 128){
			   $byte = ord($str[$i]) - 256;
		   }else{
			   $byte = ord($str[$i]);
		   }
		$bytes[] =  $byte ;
	}
	return $bytes;
}
?>
