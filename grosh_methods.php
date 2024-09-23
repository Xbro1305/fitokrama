<?php
	include_once  'mnn.php';
	//header('Content-Type: application/json');

	
function hutkigroshPOST($method, $dataJSON = null, $cookies = null) {
    $url = 'https://www.hutkigrosh.by/API/v1/' . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];
    if (isset($dataJSON)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);  // Устанавливаем тело запроса
        $headers[] = "Content-Length: " . strlen($dataJSON);
    }
    if ($cookies) {
        $cookieString = implode('; ', $cookies);
        curl_setopt($ch, CURLOPT_COOKIE, $cookieString);  // Передаем куки напрямую
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    // Устанавливаем заголовки
    curl_setopt($ch, CURLOPT_HEADER, true);           // Возвращать заголовки ответа
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);            // Таймаут для выполнения запроса (30 секунд)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);     // Таймаут для подключения (10 секунд)
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: '. curl_error($ch);
        curl_close($ch);
        return false;
    }
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);    // Заголовки ответа
    $body = substr($response, $header_size);          // Тело ответа (результат)
    $responseCookies = [];
    if ($method !== 'Security/LogOut') {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
        foreach ($matches[1] as $item) {
            $responseCookies[] = $item;  // Сохраняем куки как строки
        }
    }
    curl_close($ch);  // Закрываем cURL
    return ['headers' => $headers, 'body' => $body, 'cookies' => $responseCookies];
}

function new_hutki_invoice($order_number,$sum,$cart)		// сформировать новый счет по заказу и вернуть его id
{
	GLOBAL $grosh_login, $grosh_password, $grosh_eripId;
	$responseLogin = hutkigroshPOST('Security/LogIn', json_encode(['user' => $grosh_login,'pwd' => $grosh_password]));
	$cookies = $responseLogin['cookies'];

	$invId = 'FTKRM-'.$order_number.'-'.strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
	$billData = json_encode([
		'eripId' => $grosh_eripId,
		'invId' => $invId ,
		'dueDt' => "/Date(" . (time() * 1000 + 86400 * 1000) . "+0300)/",
		'addedDt' => "/Date(" . (time() * 1000) . "+0300)/",
		'payedDt' => null,
		'fullName' => $cart['client_name'],
		'mobilePhone' => $cart['client_phone'],
		'notifyByMobilePhone' => false,
		'email' => $cart['client_email'],
		'notifyByEMail' => false,
		'fullAddress' => $cart['client_address'],
		'amt' => $sum,
		'curr' => 'BYN',
		'statusEnum' => 0,
		'info' => null,
		'products' => [
			[
				'invItemId' => 'Артикул 123',
				'desc' => 'Товар',
				'count' => 1,
				'amt' => $sum
			]
		]
	]);

	$responseBill = hutkigroshPOST('Invoicing/Bill', $billData, $cookies);  
	$billid = json_decode($responseBill['body'], true)['billID'];	
	if (is_null($billid))
		send_warning_telegram('Ошибка формирования счета hutki '.json_encode($billData).'  ->  '.json_encode($responseBill));
	return $billid;
}





	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];

	if ($method == 'get_grosh_token') {
	GLOBAL $grosh_login, $grosh_password, $grosh_eripId;

	$billid = new_hutki_invoice('123456',1.10,['client_name'=>'Кенгерли Эмиль Рафикович','client_email'=>'kenherli@gmail.com','client_address'=>'Ратомка, ул. Корицкого, 30А','client_phone'=>'+375296767861']);
	
	$responseLogin = hutkigroshPOST('Security/LogIn', json_encode(['user' => $grosh_login,'pwd' => $grosh_password]));
	$cookies = $responseLogin['cookies'];

	print_r($billid);
	echo PHP_EOL.PHP_EOL;
	

	$paydata = '{
	"billId":'.$billid.',
	"returnUrl":		"https://fitokrama.by/payment_received.php/hutki_incoming_ok",
	"cancelReturnUrl":	"https://fitokrama.by/payment_received.php/hutki_incoming_no",
	"submitValue":"Оплатить картой",
	}';

	//echo PHP_EOL.$paydata.PHP_EOL;

	$payresponse = hutkigroshPOST('Pay/WebPay', $paydata, $cookies);
	$html = json_decode($payresponse['body'], true)['form'];
	//die($payresponse['body']);

	// Генерируем HTML для модального окна
	$htmlWithModal = '
	<div id="paymentModal" style="display:block; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0, 0, 0, 0.5);">
		<div style="width: 400px; margin: 100px auto; padding: 20px; background-color: white;">
			<h2>Оплата</h2>
			' . $html . '
			<button onclick="document.getElementById(\'paymentModal\').style.display = \'none\';">Закрыть</button>
		</div>
	</div>

	<script>
		// Дождаться загрузки документа и автоматической отправки формы
		window.onload = function() {
			var form = document.forms[0]; // Находим первую форму в модальном окне
			if (form) {
				form.submit(); // Автоматически отправляем форму
			}
		};
	</script>';

	// Выводим HTML с модальным окном
	echo $htmlWithModal;











	/*echo json_encode($html);*/

	/*$htmlWithAutoSubmit = $html . '<script>document.forms[0].submit();</script>'; переадресация на адрес
	echo $htmlWithAutoSubmit;  // переадресация 
	$html;*/

	/*
	$paydata = '{
	"billId":'.$billid.',
	"phone":"+375296562441"
	}';

	$alfapayresponse = hutkigroshPOST('Pay/AlfaClick', $paydata, $cookies);  

	echo PHP_EOL.json_encode($alfapayresponse).PHP_EOL;

	*/

	// Выполняем LogOut, используя куки
	$responseLogout = hutkigroshPOST('Security/LogOut', null, $cookies);

		// Выводим полученные куки
}

