<?php
/**************************************
Version: 1.0.0
User: v.bragari
Date: 2018-07-07
*/

include $_SERVER['DOCUMENT_ROOT']."/psp/paynet/PaynetAPI.php";
include $_SERVER['DOCUMENT_ROOT']."/psp/paynet/PaynetConfig.php";

//-------------- create a SDK object
$api = new PaynetEcomAPI(MERCHANT_CODE,MERCHANT_SEC_KEY,MERCHANT_USER,MERCHANT_USER_PASS);
echo "Paynet SDK, ".$api->Version()."<br>";
echo "---------------------------------------------------------------<br>";
echo "Code: ".$api->merchant_code."<br>";
echo "Key:  ".$api->merchant_secret_key."<br>";
echo "User: ".$api->merchant_user."<br>";
echo "Pass: ".$api->merchant_user_password."<br>";
echo "------------------- Get object by merchant external id  ------------------------------<br>";
if($_GET["id"] != null)
{
	echo "GET id: ".$_GET['id'];
	$prequest = new PaynetRequest();
	$externalID = $_GET['id'];
	$checkObj = $api->PaymentGet($externalID);
	echo "<br>-------------------  returning object  ----------------<br>";
	print_r($checkObj);	
	if($checkObj->IsOk())
	{
		echo "<br>-------------------  main parameters, the final status is 4 (Complete),  ----------------<br>";
		echo " Status: ".$checkObj->Data[0]['Status'] ;
		echo "<br> Merchant invoice: ".$checkObj->Data[0]['Invoice'] ;
		echo "<br> Amount of payment: ".$checkObj->Data[0]['Amount'] ;
	}
}else
{
	echo "The input parameter id is Empty: ";
}
echo "<br>-----------------------------------------------------------------------------------<br>";
echo "<a href='/psp' > < Back</a><br>";