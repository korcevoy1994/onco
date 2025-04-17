<?php
/**************************************
Version: 1.0.0
User: v.bragari
Date: 2018-07-07
www.paynet.md
*/

include $_SERVER['DOCUMENT_ROOT']."/psp/paynet/PaynetAPI.php";
include $_SERVER['DOCUMENT_ROOT']."/psp/paynet/PaynetConfig.php";
//-------------- create a SDK object
$api = new PaynetEcomAPI(MERCHANT_CODE,MERCHANT_SEC_KEY,MERCHANT_USER,MERCHANT_USER_PASS);
echo "Paynet API example using a SDK, ".$api->Version()."<br>";
echo "-------------------- the profile data -------------------------------------------<br>";
echo "Code: ".$api->merchant_code."<br>";
echo "Key:  ".$api->merchant_secret_key."<br>";
echo "User: ".$api->merchant_user."<br>";
echo "Pass: ".$api->merchant_user_password."<br>";
echo "Current time of web local server: ".$api->ExternalDate();
echo "<h3>1. ------------------- Get token  ------------------------------</h3>";
echo "<a href='token_get.php' >Authorize, This method uses parameters: a merchant code, a user and password for get a authorizing token!</a><br>";

echo "<h3>2. ------------------- Form generate, for model client-server ------------------------</h3>";
echo "<a href='client_server.php' >Build http form request, This method uses merchant code and secret key!</a><br>";

echo "<h3>3. ------------------- Form generate, for model server-server ------------------------</h3>";
echo "<a href='server_server.php' >Build http form request, This method uses merchant code and secret key!</a><br>";

echo "<h3>4. ------------------- Get status of transaction ------------------------</h3>";
$externalID = 12212121;
echo "Merchant order: ".$externalID."<br>" ;
$checkObj = $api->PaymentGet($externalID);
if($checkObj->IsOk())
{
	echo "4 - Complete,  Status: ".$checkObj->Data[0]['Status'] ;
	echo "<br> Merchant invoice: ".$checkObj->Data[0]['Invoice'] ;
	echo "<br> Amount of payment: ".$checkObj->Data[0]['Amount'] ;
} else
	echo $checkObj->Code.' - '.$checkObj->Message;
echo "<br>-----------------------------------------------------------------------------------<br>";