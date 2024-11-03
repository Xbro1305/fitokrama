<?php
	include_once  'mnn.php';
	header('Content-Type: application/json');

function get_epos_token() // получить новый токен
{		
	GLOBAL $ssp_epos_client_id;
	GLOBAL $ssp_epos_client_secret;
	
	$epos_token =  json_decode(file_get_contents('epos_token.json'),TRUE); 
	
	if ((strtotime($epos_token['until']) - time()) > 60) 
		return $epos_token['access_token'];	// возвращаем сохраненный токен

	$url = 'https://iii.by/connect/token';

	$data = http_build_query([			'grant_type' => 'client_credentials',			'scope' => 'epos.public.invoice',			'client_id' => $ssp_epos_client_id,			'client_secret' => $ssp_epos_client_secret		]);
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);		curl_setopt($curl, CURLOPT_POST, true);		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);		curl_setopt($curl, CURLOPT_TIMEOUT, 30); 	curl_setopt($curl, CURLOPT_HTTPHEADER, [			'Content-Type: application/x-www-form-urlencoded',		]);
	$response = curl_exec($curl);

	if (curl_errno($curl)) {
		send_warning_telegram(" Ошибка при получении токена EPOS ". $response);
		return null;
	}
	curl_close($curl);
	
	$epos_token =  json_decode($response,TRUE);
	$epos_token ['until'] = date('Y-m-d H:i:s', $epos_token['expires_in'] + time() );
	file_put_contents('epos_token.json', json_encode($epos_token));
	
	return $epos_token['access_token'];
}
	
function new_epos_invoice($invoice_number,$epos_sum,$cart)	//	создать новый инвойс epos
{
		GLOBAL $ssp_epos_client_id;
		GLOBAL $ssp_epos_client_secret;
		GLOBAL $link;
		
		$epos_token =  get_epos_token();

		$text_epos = 'Оплата по заказу '.$invoice_number;
		$nowUtc = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
		$nowUtcPlus14Days = (new DateTime('now', new DateTimeZone('UTC')))
                    ->modify('+14 days')
                    ->format('Y-m-d\TH:i:s\Z');
		$nowUtcPlus2Hours = (new DateTime('now', new DateTimeZone('UTC')))
                    ->modify('+2 hours')
                    ->format('Y-m-d\TH:i:s\Z');					

		$body = [
			"number" => $invoice_number,
			"currency" => "933",
			"merchantInfo" => [
				"serviceId" => 1,
				"retailOutlet" => [
					"code" => 1
				]
			],
			"note" => $text_epos,
			"paymentDueTerms" => [
				"dueUTC" => $nowUtcPlus2Hours, // +2 часа
				"termsDay" => 0
			],
			"billingInfo" => [
				"contact" => [
					"firstName" => " ",
					"lastName" => $cart['client_name'],
					"middleName" => " "
				],
			"email" => $cart['client_email']
			],
			"paymentRules" => [
				"requestAmount" => false,
				"requestPersonName" => true
			],
			"items" => [
				[
					"name" => $text_epos,
					"description" => null,
					"quantity" => 1,
					"measure" => "",
					"unitPrice" => [
						"value" => $epos_sum
					],
					"discount" => [
						"percent" => null,
						"amount" => null
					]
				]
			],
			"dateInAirUTC" => $nowUtc // Текущее время в формате UTC
		];

		$jsonBody = json_encode($body);
		
		
		
		$url ='https://api-epos.hgrosh.by/public/v1/invoicing/invoice?canPayAtOnce=true';
		
		$curl = curl_init();		curl_setopt_array($curl, array(		  CURLOPT_URL => $url,		  CURLOPT_RETURNTRANSFER => true,		  CURLOPT_ENCODING => '',		  CURLOPT_MAXREDIRS => 10,		  CURLOPT_TIMEOUT => 0,	      CURLOPT_POSTFIELDS => $jsonBody,		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,		  CURLOPT_CUSTOMREQUEST => 'POST',		  CURLOPT_HTTPHEADER => array(    'Content-Type: application/json',										'Authorization: Bearer '.$epos_token )		));
		
		$response = curl_exec($curl);
		
		// Проверка на наличие ошибок в запросе
		if (curl_errno($curl)) {
			send_warning_telegram(" Ошибка при отправке инвойса $invoice_number EPOS ". $response);
			return null;
		}
	
		$res =  json_decode($response,TRUE);
		 
		if (!isset($res[0]['id']))
		{
			send_warning_telegram(" Ошибка при отправке инвойса $invoice_number EPOS ". $response);
			return null;
		}
		curl_close($curl);

		$invoice_id = $res[0]['id'];
		
		$url = 'https://api-epos.hgrosh.by/public/v1/invoicing/invoice/' . $invoice_id . '/qrcode?getImage=true';
		
		$curl = curl_init();		curl_setopt_array($curl, array(		  CURLOPT_URL => $url,		  CURLOPT_RETURNTRANSFER => true,		  CURLOPT_ENCODING => '',		  CURLOPT_MAXREDIRS => 10,		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_FOLLOWLOCATION => true,		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,		  CURLOPT_CUSTOMREQUEST => 'GET',		  CURLOPT_HTTPHEADER => array(    'Content-Type: application/json',										'Authorization: Bearer '.$epos_token )		));
		
		$response = curl_exec($curl);

		// Проверка на наличие ошибок в запросе
		if (curl_errno($curl)) {
			send_warning_telegram(" Ошибка при получении кода инвойса $order_number EPOS ". $response);
			return null;
		curl_close($curl);
			
		}
		
		$res = json_decode($response,true);
		
		$epos_id = $res['result']['num'];
		$qr_link = $res['result']['tinyLink'];
		$qr_Data = $res['result']['qrData'];
		$qr_DataEncoded = $res['result']['qrDataEncoded'];

		return [$qr_link,$invoice_id];
}

function epos_kill ($invoice_id, $test = NULL) {    
    header('Content-Type: application/json');
    
    [, , $sum] = epos_check($invoice_id);
    if ($sum != 0) return 'error: invoice has been paid';

    $epos_token = get_epos_token();
    $url = 'https://api-epos.hgrosh.by/public/v1/invoicing/invoice/' . $invoice_id.'/cancel';


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $epos_token
        ),
    ));

    $response = curl_exec($curl);

    // Additional diagnostic output
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($test) {
        echo 'URL --- ' . $url . PHP_EOL;
        echo 'Response --- ' . $response . PHP_EOL;
        echo 'HTTP Code --- ' . $httpCode . PHP_EOL;
        echo 'Error --- ' . curl_error($curl) . PHP_EOL;
    }

    curl_close($curl);

    $res = json_decode($response, TRUE);

    return 'ok';
}


function epos_check($invoice_id)		// проверить статус оплаты 
{
		$epos_token =  get_epos_token();
		$url = 'https://api-epos.hgrosh.by/public/v1/invoicing/invoice/' . $invoice_id ;
		$curl = curl_init();		curl_setopt_array($curl, array(		  CURLOPT_URL => $url,		  CURLOPT_RETURNTRANSFER => true,		  CURLOPT_ENCODING => '',		  CURLOPT_MAXREDIRS => 10,		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_FOLLOWLOCATION => true,		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,		  CURLOPT_CUSTOMREQUEST => 'GET',		  CURLOPT_HTTPHEADER => array(    'Content-Type: application/json',										'Authorization: Bearer '.$epos_token )		));
		$response = curl_exec($curl);
		
		$res = json_decode($response, TRUE);
		if ($res['state']==20)	 // оплачено ли?
				return ['epos','epos_check',$res['totalCurrencyAmount']];
		else 	return [NULL,NULL,0];
}

function epos_pay_check($invoice_id)		// проверить статус оплаты 
{
		$epos_token =  get_epos_token();
		$url = 'https://api-epos.hgrosh.by/public/v1/invoicing/invoice/' . $invoice_id ;
		$curl = curl_init();		curl_setopt_array($curl, array(		  CURLOPT_URL => $url,		  CURLOPT_RETURNTRANSFER => true,		  CURLOPT_ENCODING => '',		  CURLOPT_MAXREDIRS => 10,		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_FOLLOWLOCATION => true,		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,		  CURLOPT_CUSTOMREQUEST => 'GET',		  CURLOPT_HTTPHEADER => array(    'Content-Type: application/json',										'Authorization: Bearer '.$epos_token )		));
		$response = curl_exec($curl);
		$res = json_decode($response, TRUE);
		$pay_res = ($res['state']==20);
		return $pay_res;
}

$link = firstconnect ();
$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method == 'epos_kill') 
{
	$invoice_id = $_GET['invoice_id'];
	$res = epos_kill($invoice_id,false);
	
	echo json_encode($res).PHP_EOL.PHP_EOL;
	die;
}	


if ($method=='epos_check') 
{
		$invoice_id = $_GET['invoice_id'];
		$payres = epos_check($invoice_id);
			echo(json_encode($payres));
		exit;	
}
