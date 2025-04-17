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
//---------- merchant id , for example the order from eshop 
$prequest = new PaynetRequest();
$prequest->ExternalID =  round(microtime(true) * 1000);
$prequest->LinkSuccess = "http://localhost:8080/psp/ok?id=".$prequest->ExternalID;
$prequest->LinkCancel =  "http://localhost:8080/psp/cancel?id=".$prequest->ExternalID;
$prequest->Lang = 'ru';

$prequest->Products = array(
						 array   (  'LineNo' => '1',
						   'Code' => 'code1001',
						   'Barcode' => '1001',
					       'Name' => 'Ticket mini', 
						   'Description' => 'Description your product MINI', 
						   'Quantity' => 200, 	// // 200 = 2.00  two 				
						   'UnitPrice' => 2000),
						 array   (  'LineNo' => '2',
					       'Name' => 'Ticket MAX', 
						   'Code' => 'code1002',
						   'Barcode' => '1002',
						   'Description' => 'Description your product MAX', 
						   'Quantity' => 100,    // 100 = 1.00  one 
						   'UnitPrice' => 1050),
						 array   (  'LineNo' => '3',
					       'Name' => 'Ticket MAX 3', 
						   'Code' => 'code1003',
						   'Barcode' => '1003',
						   'Description' => 'Description your product MAX', 
						   'Quantity' => 300,    // 300 = 3.00  three 
						   'UnitPrice' => 500) 							   
);
	
$prequest->Service = array ( 
							array ( 'Name'		 => 'Demo eshop',
									'Description'=> 'Demo eShop online desc',
									'Amount'	=> $prequest->Amount,
									'Products'	=> $prequest->Products)
							);

$prequest->Customer = array(
							'Code' 		=> 'v.bragari@paynet.md',
							'Address' 	=> 'www.paynet.md',
							'Name' 		=> 'Slavan'
							);

$paymentRegObj = $api->PaymentReg($prequest);
echo $paymentRegObj->Data;

echo "<br>-----------------------------------------------------------------------------------<br>";
echo "<a href='/psp' > < Back</a><br>";