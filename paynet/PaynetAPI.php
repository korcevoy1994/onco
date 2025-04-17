<?php
/**************************************
Version: 1.0
User: v.bragari
Email: v.bragari@paynet.md
Date: 2018-07-07
*/
class PaynetRequest
{
	public $ExternalDate;			
	public $ExternalID;
	public $Currency = 498;
    public $Merchant;			
	public $LinkSuccess;
	public $LinkCancel;
	public $ExpiryDate;	
	//---------  ru, ro, en
	public $Lang;
	public $Service = array();
	public $Products = array();
	public $Customer = array();
	public $Amount;
}
class PaynetResult
{
	public $Code ; 
	public $Message ; 
	public $Data ; 
	public function IsOk()
	{
		return $this->Code === PaynetCode::SUCCESS;
	}
}

class PaynetCode
{
	const SUCCESS = 0;
	const TECHNICAL_ERROR = 1;
	const DATABASE_ERROR = 2;
	const USERNAME_OR_PASSWORD_WRONG = 3;
	const CONNECTION_ERROR = 12;
}

class PaynetEcomAPI
{
	const API_VERSION = "Version 1.2";
	/**
		Paynet merchant code.
	*/
	private $merchant_code;

	/**
		Paynet merchant secret key.
	*/
	private $merchant_secret_key;

	/**
		Paynet merchant user for access to API.
	*/
	private $merchant_user;

	/**
		Paynet merchant user's password.
	*/
	private $merchant_user_password;	
	/**
		The base URL to UI
	*/
	const PAYNET_BASE_UI_URL =  "https://test.paynet.md/acquiring/setecom";        // production link: https://paynet.md/acquiring/setecom
	/**
		The base URL to UI
	*/
	const PAYNET_BASE_UI_SERVER_URL = "https://test.paynet.md/acquiring/getecom";  // production link: https://paynet.md/acquiring/getecom
	
	/**
		The base URL to API
	*/
	const PAYNET_BASE_API_URL = 'https://test.paynet.md:4446';                     // production link: https://paynet.md:4446 
	/**
		The expiry time for this operation, in hours
	*/
	const EXPIRY_DATE_HOURS = 4 ;//  hours

	const ADAPTING_HOURS = 1 ;//  hours

	public function __construct($merchant_code = null,$merchant_secret_key = null, $merchant_user = null, $merchant_user_password = null)
	{
		$this->merchant_code = $merchant_code;
		$this->merchant_secret_key = $merchant_secret_key;
		$this->merchant_user = $merchant_user;
		$this->merchant_user_password = $merchant_user_password;
		$this->api_base_url = self::PAYNET_BASE_API_URL;
	}

	public function Version()
	{
		return self::API_VERSION;
	}

	public function TokenGet($addHeader = false)
	{
		$path = '/auth';
		$params = [
            'grant_type' 	=> 'password',
            'username'      => $this->merchant_user,
            'password'    	=> $this->merchant_user_password
        ];
		
		$tokenReq =  $this->callApi($path, 'POST', $params);
		$result = new PaynetResult();
		
		if($tokenReq->Code == PaynetCode::SUCCESS)
		{
			if(array_key_exists('access_token', $tokenReq->Data))
			{
				$result->Code = PaynetCode::SUCCESS;
				if($addHeader)	
					$result->Data = ["Authorization: Bearer ".$tokenReq->Data['access_token']];
				else
					$result->Data = $tokenReq->Data['access_token'];
			}else
			{
				$result->Code = PaynetCode::USERNAME_OR_PASSWORD_WRONG;
				if(array_key_exists('Message', $tokenReq->Data))
					$result->Message = $tokenReq->Data['Message'];		
				if(array_key_exists('error', $tokenReq->Data))
					$result->Message = $tokenReq->Data['error'];	
			}		
		} else
		{
			$result->Code = $tokenReq->Code;
			$result->Message = $tokenReq->Message;
		}
		return $result;
	}

	public function PaymentGet($externalID)
	{
		$path = '/api/Payments';
		$params = [
            'ExternalID' 	=> $externalID
        ];
		
		$tokenReq =  $this->TokenGet(true);
		$result = new PaynetResult();

		if($tokenReq->IsOk())
		{			
			$resultCheck = $this->callApi($path, 'GET',null, $params, $tokenReq->Data);	
			if($resultCheck->IsOk())
			{    
				$result->Code = $resultCheck->Code;
				
				if(array_key_exists('Code',$resultCheck->Data))
				{
						$result->Code = $resultCheck->Data['Code'];
						$result->Message = $resultCheck->Data['Message'];
				}else
				{
					$result->Data = $resultCheck->Data;
				}
				
			}else			
				$result = $resultCheck;
		}else
		{ 
			$result->Code = $tokenReq->Code;
			$result->Message = $tokenReq->Message;
		}
		return $result;
	}
	
	public function FormCreate($pRequest)
	{
		$result = new PaynetResult();
		$result->Code = PaynetCode::SUCCESS;

			//----------------- preparing a service  ----------------------------		
			$_service_name = '';
			$product_line = 0;
			$_service_item = "";
			//-------------------------------------------------------------------
			$pRequest->ExpiryDate = $this->ExpiryDateGet(self::EXPIRY_DATE_HOURS);

			$amount = 0;
			foreach ( $pRequest->Service["Products"] as $item ) {				
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][LineNo]" value="'.htmlspecialchars_decode($item['LineNo']).'"/>';					
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][Code]" value="'.htmlspecialchars_decode($item['Code']).'"/>';
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][BarCode]" value="'.htmlspecialchars_decode($item['Barcode']).'"/>';
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][Name]" value="'.htmlspecialchars_decode($item['Name']).'"/>';
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][Description]" value="'.htmlspecialchars_decode($item['Descrption']).'"/>';
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][Quantity]" value="'.htmlspecialchars_decode($item['Quantity'] ).'"/>';
					$_service_item .='<input type="hidden" name="Services[0][Products]['.$product_line.'][UnitPrice]" value="'.htmlspecialchars_decode(($item['UnitPrice'])).'"/>';					
					$product_line++;	
					$amount += $item['Quantity']/100 * $item['UnitPrice'];
			}

			$pRequest->Service["Amount"] = $amount;	
		    $signature = $this->SignatureGet($pRequest);
			$pp_form =  '<form method="POST" action="'.self::PAYNET_BASE_UI_URL.'">'.	
						'<input type="hidden" name="ExternalID" value="'.$pRequest->ExternalID.'"/>'.
						'<input type="hidden" name="Services[0][Description]" value="'.htmlspecialchars_decode($pRequest->Service["Description"]).'"/>'.
						'<input type="hidden" name="Services[0][Name]" value="'.htmlspecialchars_decode($pRequest->Service["Name"]).'"/>'.
						'<input type="hidden" name="Services[0][Amount]" value="'.$amount.'"/>'.
						$_service_item.
						'<input type="hidden" name="Currency" value="'.$pRequest->Currency.'"/>'.
						'<input type="hidden" name="Merchant" value="'.$this->merchant_code.'"/>'.
						'<input type="hidden" name="Customer.Code"   value="'.htmlspecialchars_decode($pRequest->Customer['Code']).'"/>'.
						'<input type="hidden" name="Customer.Name"   value="'.htmlspecialchars_decode($pRequest->Customer['Name']).'"/>'.
						'<input type="hidden" name="Customer.Address"   value="'.htmlspecialchars_decode($pRequest->Customer['Address']).'"/>'.
						'<input type="hidden" name="Payer.Email"   value="v.bragari@ggg.md"/>'.
						'<input type="hidden" name="Payer.Name"   value="Oleg"/>'.
						'<input type="hidden" name="Payer.Surname"   value="Stoianov"/>'.
						'<input type="hidden" name="Payer.Mobile"   value="37360000000"/>'.
						'<input type="hidden" name="ExternalDate" value="'.htmlspecialchars_decode($this->ExternalDate()).'"/>'.
						'<input type="hidden" name="LinkUrlSuccess" value="'.htmlspecialchars_decode($pRequest->LinkSuccess).'"/>'.
						'<input type="hidden" name="LinkUrlCancel" value="'.htmlspecialchars_decode($pRequest->LinkCancel).'"/>'.						
						'<input type="hidden" name="ExpiryDate"   value="'.htmlspecialchars_decode($pRequest->ExpiryDate).'"/>'.
						'<input type="hidden" name="Signature" value="'.$signature.'"/>'.
						'<input type="hidden" name="Lang" value="'.$pRequest->Lang.'"/>'.
						'<input type="submit" value="GO to a payment gateway of paynet" />'.
						'</form>';
		$result->Data = $pp_form;
		return $result;
	}
	
	public  function PaymentReg($pRequest)
	{
		$path = '/api/Payments/Send';
		$pRequest->ExpiryDate = $this->ExpiryDateGet(self::EXPIRY_DATE_HOURS);
		//------------- calculating total amount
		foreach ( $pRequest->Service[0]['Products'] as $item ) {			
					
							$pRequest->Service[0]['Amount'] += ($item['Quantity']/100) * $item['UnitPrice'];
		}
		
		$params = [
			'Invoice' => $pRequest->ExternalID,
			'MerchantCode' => $this->merchant_code,
			'LinkUrlSuccess' =>  $pRequest->LinkSuccess,
			'LinkUrlCancel' => $pRequest->LinkCancel,
			'Customer' => $pRequest->Customer,
			'Payer' => $pRequest->Customer,
			'Currency' => 498,
			'ExternalDate' => $this->ExternalDate(),
			'ExpiryDate' => $this->ExpiryDateGet(self::EXPIRY_DATE_HOURS),
			'Services' => $pRequest->Service,										
			'Lang' => $pRequest->Lang			
        ];
		
		$tokenReq =  $this->TokenGet(true);
		$result = new PaynetResult();
	
		if($tokenReq->IsOk())
		{			
		//	print_r($tokenReq); 
		//	echo "<br>";
		//	print_r($path); 			echo "<br>";
		//	print_r($params); 			echo "<br>";
			//print_r($tokenReq->Data[0]); 
			//return;
			$resultCheck = $this->callApi($path, 'POST', $params,[], $tokenReq->Data);	
			if($resultCheck->IsOk())
			{    
				$result->Code = $resultCheck->Code;
				
				if(array_key_exists('Code',$resultCheck->Data))
				{
						$result->Code = $resultCheck->Data['Code'];
						$result->Message = $resultCheck->Data['Message'];
				}else
				{
					//print_r($resultCheck->Data);
					//print_r($pRequest);
					$pp_form =  '<form method="POST" action="'.self::PAYNET_BASE_UI_SERVER_URL.'">'.
					'<input type="hidden" name="operation" value="'.htmlspecialchars_decode($resultCheck->Data['PaymentId']).'"/>'.
					'<input type="hidden" name="LinkUrlSucces" value="'.htmlspecialchars_decode($pRequest->LinkSuccess).'"/>'.
					'<input type="hidden" name="LinkUrlCancel" value="'.htmlspecialchars_decode($pRequest->LinkCancel).'"/>'.						
					'<input type="hidden" name="ExpiryDate"   value="'.htmlspecialchars_decode($pRequest->ExpiryDate).'"/>'.
					'<input type="hidden" name="Signature" value="'.$resultCheck->Data['Signature'].'"/>'.
					'<input type="hidden" name="Lang" value="'.$pRequest->Lang.'"/>'.
					'<input type="submit" value="GO to a payment gateway of paynet" />'.
					'</form>';
					$result->Data = $pp_form;
				}
				
			}else			
				$result = $resultCheck;
		}else
		{ 
			$result->Code = $tokenReq->Code;
			$result->Message = $tokenReq->Message;
		}
		return $result;
	}
	private function callApi($path, $method = 'GET', $params = [], $query_params = [], $headers = [])
    {
		$result = new PaynetResult();
		
        $url = $this->api_base_url . $path;

        if (count($query_params) > 0) {
            $url .= '?' . http_build_query($query_params);
        }

        $ch = curl_init($url);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));// json_encode($params));			
		}

        $json_response = curl_exec($ch);

        if ($json_response === false) {
            /*
             * If an error occurred, remember the error
             * and return false.
             */
            $result->Message = curl_error($ch).', '.curl_errno($ch);
			$result->Code = PaynetCode::CONNECTION_ERROR;
            //print_r(curl_errno($ch));

            // Remember to close the cURL object
            curl_close($ch);
            return $result;
        }

        /*
         * No error, just decode the JSON response, and return it.
         */
        $result->Data = json_decode($json_response, true);

        // Remember to close the cURL object
        curl_close($ch);
		$result->Code = PaynetCode::SUCCESS;
        return $result;
    }
    
	private function ExpiryDateGet($addHours)
	{
		$date = strtotime("+".$addHours." hour");
		return date('Y-m-d', $date).'T'.date('H:i:s', $date);
	}
	
	public function ExternalDate($addHours = self::ADAPTING_HOURS)
	{		
		$date = strtotime("+".$addHours." hour");
		return date('Y-m-d', $date).'T'.date('H:i:s', $date);
	}
	private function SignatureGet($request)
	{
			$_sing_raw  = $request->Currency;
			$_sing_raw .= $request->Customer['Address'].$request->Customer['Code'].$request->Customer['Name'];
			$_sing_raw .= $request->ExpiryDate.strval($request->ExternalID).$this->merchant_code;
			$_sing_raw .= $request->Service['Amount'].$request->Service['Name'].$request->Service['Description'];			
			$_sing_raw .= $this->merchant_secret_key;			

			return base64_encode(md5($_sing_raw, true));	
	}
	public function __get ($name) {
        return $this->$name ?? null;
    }
}