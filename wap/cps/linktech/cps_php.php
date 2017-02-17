<?
//*==================================================领克特cps接口2 2011-9-7 yijiangwen==============================================*//
$lt_o_cd   = "||".$merchant_order_code;
$lt_p_cd  .= "||".$merchant_product_id;
$lt_it_cnt.= "||".$merchant_product_count;
$lt_price .= "||".$merchant_product_price;
$lt_c_cd  .= "||".$merchant_product_category_code;

$lt_o_cd   = substr($lt_o_cd, 2);
$lt_p_cd   = substr($lt_p_cd, 2);
$lt_it_cnt = substr($lt_it_cnt, 2);
$lt_price  = substr($lt_price, 2);
$lt_c_cd   = substr($lt_c_cd, 2);

$merchant_id 	= m_id;
$lt_user_id 	= $merchant_user_id;
$lt_user_name	= $merchant_user_name;

if(isset($LTINFO))
{
	//make url
	$lt_log = "http://service.linktech.cn/purchase_cps.php?a_id=$LTINFO".
			  "&m_id=$merchant_id&mbr_id=$lt_user_id($lt_user_name)&o_cd=$lt_o_cd&p_cd=$lt_p_cd".
			  "&price=$lt_price&it_cnt=$lt_it_cnt&c_cd=$lt_c_cd";
	$lt_output="<script src=\"".$lt_log."\"></script>";

	//output script
	echo $lt_output;
}
?>

