<?php
/* =======================================================================================================================
 * �ֻ�֧���ӿ� �̻���̨֪ͨ��2012/8/2����Author:yijiangwen��
 * =======================================================================================================================
 * �ֻ�֧���ɹ�����ֻ�֧����ӡһ��success��ǡ�
 */
	require("callcmpay.php"); 

	//�����ֻ�֧��ƽ̨��̨֪ͨ����start
	$merchantId 	= $_POST["merchantId"];
	$payNo 	  		= $_POST["payNo"];
	$returnCode 	= $_POST["returnCode"];
	$message	  	= $_POST["message"];
	$signType       = $_POST["signType"];
	$type         	= $_POST["type"];
	$version        = $_POST["version"];
	$amount         = $_POST["amount"];
	$amtItem		= $_POST["amtItem"];		
	$bankAbbr	  	= $_POST["bankAbbr"];
	$mobile 		= $_POST["mobile"];
	$orderId		= $_POST["orderId"];
	$payDate		= $_POST["payDate"];
	$accountDate    = $_POST["accountDate"];
	$reserved1	  	= $_POST["reserved1"];
	$reserved2	  	= $_POST["reserved2"];
	$status			= $_POST["status"];
	$payType        = $_POST["payType"];
	$orderDate      = $_POST["orderDate"];
	$fee            = $_POST["fee"];
	$vhmac			= $_POST["hmac"];
	//�����ֻ�֧��ƽ̨��̨֪ͨ����end

	//print_r($vhmac);
	//print_r('8888');
	//$signKey        = $GLOBALS['signKey'];
	$signKey        = "e1SL6mLF8zlpiVMQpqB3qY7O0tl8ipDWqVwJGK33Gn0fOg1w1YKcoP33QSav1hgf";

	if($returnCode!=000000)
	{		
		echo $returnCode.decodeUtf8($message);//�˴���ʾ��̨֪ͨ��������
		exit();
	}

	$signData = $merchantId .$payNo       .$returnCode .$message
			   .$signType   .$type        .$version    .$amount
			   .$amtItem    .$bankAbbr    .$mobile     .$orderId
			   .$payDate    .$accountDate .$reserved1  .$reserved2
			   .$status     .$orderDate   .$fee;
	$hmac = MD5sign($signKey,$signData);	

print_r("key:".$signKey."<br/>");
print_r("signData:".$signData."<br/>");


	if($hmac!=$vhmac)
	{	  
		echo "ǩ����֤ʧ��";//�˴��޷���֤��Ϣ���������ֻ�֧��ƽ̨

		//print_r("vhmac:".$vhmac."<br/>");
		//print_r("hmac:".$hmac."<br/>");
	}
	else
	{		
		echo "SUCCESS";//�̻��ڴ˴���ҵ����������ϱ�����ӦSUCCESS
	}
?>