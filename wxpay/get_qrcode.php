<?php
ini_set('display_errors', 'on');
$PNG_TEMP_DIR = dirname(__FILE__).'/wxpic/';

$PNG_WEB_DIR = 'wxpic/';
//var_dump(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR);die;
include "phpqrcode/qrlib.php";

$data = 'weixin://wxpay/bizpayurl?pr=QFmUR9U'; // data
$ecc = 'M';	// L-smallest, M, Q, H-best
$size = 4; // 1-50

$filename = $PNG_TEMP_DIR.'qrcode_'.time().'.png';
QRcode::png($data, $filename, $ecc, $size, 2);
chmod($filename, 0777);
echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" />';
?>