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
				"dueUTC" => $nowUtcPlus14Days, // +14 дней
				"termsDay" => 0
			],
			"billingInfo" => [
				"contact" => [
					"firstName" => " ",
					"lastName" => $cart['client_name'],
					"middleName" => " "
				]
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

$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	
/*
if ($method=='epos_incoming') // вызванный webhook при совершенной оплате	 - перенесен на payment_received.php 23/09/2024
	{
		$link = firstconnect ();
		$payload = file_get_contents("php://input");
		if ($payload==NULL) exit ('no data');
		$data = json_decode($payload,TRUE);
		file_put_contents('epos_log.txt', json_encode($data, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );
		
		$invoice_number = $data['claimId'];
		$payment_id = $data['id'];
		$sum = $data['amount']['amt'];
		$eripid = $data['memorialSlip']['tranEripId'];
		$eposid = $data['memorialSlip']['transEposId'];
		$invoice_id = $data['parentId'];
		
		$orders = ExecSQL($link,"SELECT * FROM orders WHERE epos_id='$invoice_id'");
		
		if (count($orders)==0)
		{
			send_warning_telegram("Не найдена оплата в журнале invoice_number=$invoice_number payment_id=$payment_id sum=$sum");
		}
		else
		{
			$order_id = $orders[0]['id'];
			$order_number = $orders[0]['number'];
			
		
			
			$que = "INSERT INTO payments (order_id,sum,datetime,payment_method,payment_report)
					VALUES ('$order_id',$sum,CURRENT_TIMESTAMP,'epos','EPOS $invoice_number - $eposid - $eripid')";
			ExecSQL($link,$que);
			send_warning_telegram("Зарегистрирована оплата по заказу $order_number в сумме $sum руб.");
			
		
		}
		$paid = ExecSQL($link,"SELECT SUM(`sum`) AS paid FROM `payments` WHERE order_id=$order_id")[0]['paid'];
		if ($paid>=$orders[0]['sum']) 
		{
			$que = "UPDATE orders SET datetime_paid = CURRENT_TIMESTAMP WHERE id=$order_id";
			ExecSQL($link,$que);
		}
		
		exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	}
*/