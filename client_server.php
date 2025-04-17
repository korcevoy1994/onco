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
echo "Paynet API example using a SDK, <b>FORM CREATE</b>, ".$api->Version()."<br>";

echo "<br>------------------- Form generate ------------------------<br>";
$prequest = new PaynetRequest();
//---------- merchant id , for example the order from eshop 
$prequest->ExternalID =  round(microtime(true) * 1000);
$prequest->LinkSuccess = "http://localhost:8080/psp/ok?id=".$prequest->ExternalID;
$prequest->LinkCancel =  "http://localhost:8080/psp/cancel?id=".$prequest->ExternalID;
$prequest->Lang = 'ru';

$prequest->Products = array(
						array   (  'LineNo' => '1',
						   'Code' => 'code1001',
						   'Barcode' => '1001',
					       'Name' => 'Ticket mini', 
						   'Descrption' => 'Description your product MINI', 
						   'Quantity' => 100, 			 // 100 = 1.00		
						   'UnitPrice' => 2000),
						   array   (  'LineNo' => '2',
					       'Name' => 'Ticket MAX', 
						   'Code' => 'code1002',
						   'Barcode' => '1002',
						   'Descrption' => 'Description your product MAX', 
						   'Quantity' => 100,    // 100 = 1.00
						   'UnitPrice' => 1200) 	
												   
);
$prequest->Amount = 3200;

$prequest->Service = array ( 'Name' => 'eshop',
							 'Description' => ' eShop online',
							 'Amount'	=> $prequest->Amount,
							 'Products'	=> $prequest->Products
							);
							
$prequest->Customer = array(
							'Code' 		=> 'v.bragari@paynet.md',
							'Address' 	=> 'www.paynet.md',
							'Name' 		=> 'Slavan'
							);
$formObj = $api->FormCreate($prequest);
if($formObj->Code == PaynetCode::SUCCESS)
{
	echo "<br>Invoice ID: ".$prequest->ExternalID."<br>";
	echo $formObj->Data;
}
echo "-----------------------------------------------------------------------------------<br>";
echo "<a href='/psp' > < Back</a><br>";