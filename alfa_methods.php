<?php
	include_once  'mnn.php';
//	header('Content-Type: application/json');

	
function alfaPOST($method, $data) {
   GLOBAL $alfa_login, $alfa_password;

    $data['userName'] = $alfa_login;
    $data['password'] = $alfa_password;
	$data['amount'] =$data['amount']*100;	// надо передавать в копейках!
    $dataQuery = http_build_query($data);
    $url = 'https://abby.rbsuat.com/payment/rest/' . $method;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        "Content-Type: application/x-www-form-urlencoded",
        "Accept: application/json"
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataQuery);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, false);    
    //curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+')); // Логировать в поток

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        send_warning_telegram('ALFA Ошибка cURL: '. curl_error($ch));
        curl_close($ch);
        return false;
    }

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
	
    curl_close($ch);
    return json_decode($body,true);
        
}

function new_alfa_invoice($order_number,$sum,$cart)		// сформировать новый счет по заказу и вернуть его id
{
	$invoice_id = 'FTKRM-'.$order_number.'-'.strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
	$data = ([
		'orderNumber' => $invoice_id,
		'amount' => $sum,
		'returnUrl' => 'https://fitokrama.by/payment_received.php/alfa_incoming_ok',
		'failUrl' => 'https://fitokrama.by/payment_received.php/alfa_incoming_no',
		'clientId' => $cart['client_id'],
		'jsonParams' => json_encode([
			'email'=>$cart['client_email'],
			'phone'=>$cart['client_phone'], 
			'backToShopUrl'=>'https://fitokrama.by/order_page.php?order='.$order_number,
			'backToShopName' => 'Вернуться к заказу'
			]),
		'sessionTimeoutSecs' => 24*60*60		// время жизни счета 1 сутки
	]);

	$response = alfaPOST('register.do', $data);  
	$alfa_orderId = $response['orderId'];
	if (is_null($alfa_orderId))
		send_warning_telegram('Ошибка формирования счета ALFA  '.json_encode($data).'  ->  '.json_encode($response));
	$alfa_url = $response['formUrl'];
		
	return [$alfa_orderId,$alfa_url];
}

function alfa_pay_check($alfa_orderId)		// проверить статус оплаты заказа
{
	$data = ([
		'orderId' => $alfa_orderId
	]);

	$response = alfaPOST('getOrderStatusExtended.do', $data);  
	$pay_res = ($response['errorCode']==1 || $response['errorCode']==2);
	return $pay_res;
}




	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];

	if ($method == 'alfa_test') {
	$alfa_orderId = $_GET['alfa_orderId'];
	$result = alfa_pay_check($alfa_orderId)	;
	

	echo json_encode($result);







}

