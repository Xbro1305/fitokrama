<?php
	include_once  'mnn.php';
	//header('Content-Type: application/json');

	
function alfaPOST($method, $data) {
    GLOBAL $alfa_userName, $alfa_password;
	$data['userName'] = $alfa_userName;
	$data['password'] = $alfa_password;
	
	$url = 'https://ecom.alfabank.by/payment/rest/' . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];
    if (isset($dataJSON)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  // Устанавливаем тело запроса
        $headers[] = "Content-Length: " . strlen($data);
    }
   
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    // Устанавливаем заголовки
    curl_setopt($ch, CURLOPT_HEADER, true);           // Возвращать заголовки ответа
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);            // Таймаут для выполнения запроса (30 секунд)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);     // Таймаут для подключения (10 секунд)
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        send_warning_telegram('ALFA Ошибка cURL: '. curl_error($ch));
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return $response;
}

function new_alfa_invoice($order_number,$sum,$cart)		// сформировать новый счет по заказу и вернуть его id
{
	$invoice_id = 'FTKRM-'.$order_number.'-'.strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
	$data = json_encode([
		'orderNumber' => $invoice_id,
		'amount' => $sum,
		'returnUrl' => 'https://fitokrama.by/payment_received.php/alfa_incoming_ok',
		'failUrl' => 'https://fitokrama.by/payment_received.php/alfa_incoming_ok',
		'clientId' => $cart['client_id'],
		'jsonParams' => [
			'email'=>$cart['client_email'],
			'phone'=>$cart['client_phone'], 
			'backToShopUrl'=>'https://fitokrama.by/order_page.php?order='.$order_number,
			'backToShopName' => 'Вернуться к заказу'
			],
		'sessionTimeoutSecs' => 24*60*60		// время жизни счета 1 сутки
	]);

	$response = alfaPOST('register.do', $data);  
	die (json_encode($response));
	$alfa_orderId = $response['orderId'];
	if (is_null($billid))
		send_warning_telegram('Ошибка формирования счета ALFA  '.json_encode($data).'  ->  '.json_encode($response));
	$alfa_url = $response['formUrl'];
	
	return [$alfa_orderId,$alfa_url];
}





	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];

	if ($method == 'alfa_test') {

	[$alfa_orderId,$alfa_url] = new_alfa_invoice('123456',0.12,['client_name'=>'Кенгерли Эмиль Рафикович','client_email'=>'kenherli@gmail.com','client_address'=>'Ратомка, ул. Корицкого, 30А','client_phone'=>'+375296767861']);
	
	print_r([$alfa_orderId,$alfa_url]);
	echo PHP_EOL.PHP_EOL;
die;	

	

	// Выводим HTML с модальным окном
	echo $alfa_url;







}

