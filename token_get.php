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
echo "Paynet API example using a SDK, <b>TOKEN GET</b>, ".$api->Version()."<br>";
echo "------------------- Get token  ------------------------------<br>";
$tokenObj = $api->TokenGet();
//print_r($tokenObj);
if($tokenObj->Code == PaynetCode::SUCCESS)
{
	echo 'Token is: '.$tokenObj->Data;
}else
	echo $tokenObj->Code.' - '.$tokenObj->Message;

echo "<br>--------------- end --------------------------------------<br>";
echo "<a href='/psp' > < Back</a><br>";