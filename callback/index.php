<?php
/**************************************
Version: 1.0.0
User: v.bragari
Date: 2018-07-07
page; /paynet/callback
*/

include $_SERVER['DOCUMENT_ROOT']."/psp/paynet/PaynetAPI.php";
include $_SERVER['DOCUMENT_ROOT']."/psp/paynet/PaynetConfig.php";

//-------------- create a PaynetEcom object--------------------------------------
$api = new PaynetEcomAPI(MERCHANT_CODE,MERCHANT_SEC_KEY,MERCHANT_USER,MERCHANT_USER_PASS);
echo "Paynet SDK, ".$api->Version()."<br>";
echo "---------------------------------------------------------------<br>";
//------------------------ get an input stream -----------------------------
$paymentInfo = file_get_contents('php://input');
$paymentObj = json_decode($paymentInfo);
if (!$paymentObj) {
    echo "The returning object has not found !";
    return;
}
echo 'Signature of confirm responding: '.apache_request_headers()['Hash'];
//echo $hash;

echo "---------------------------------------------------------------<br>";
//echo "Code: ".$api->merchant_code."<br>";
//echo "Key:  ".$api->merchant_secret_key."<br>";
//echo "User: ".$api->merchant_user."<br>";
//echo "Pass: ".$api->merchant_user_password."<br>";
echo "--------------------- Returning object -------------------------<br>";
print_r($paymentObj);
echo "--------------------- Check if PAID ---------------------------<br>";
if($paymentObj->EventType !== 'PAID'){
	echo "NOT SUCCESS";
	return;
}
$prequest = new PaynetRequest();
echo " ExternalId --->  ".$paymentObj->Payment->ExternalId;
echo "<br>-------------------  check a payment on the pgw site  by merchant id  ----------------<br>";
$checkObj = $api->PaymentGet($paymentObj->Payment->ExternalId);
print_r($checkObj);
if($checkObj->IsOk())
{
	echo $checkObj->Data[0]['Status'] ;
	echo "<br>".$checkObj->Data[0]['Invoice'] ;
	echo "<br>".$checkObj->Data[0]['Amount'] ;
	//The successfull operation has Status has to be as: 4
	if ($checkObj->Data[0]['Status'] !== 4) {
		echo 'The payment status is not complete. Please wait and try again !!!';
		return;
	}
	//------------- here you can confirm your transaction !
	echo 'The payment has confirmed';
}
echo "<br>-----------------------------------------------------------------------------------<br>";

