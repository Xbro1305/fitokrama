<?php
	include_once  'mnn.php';
	//header('Content-Type: application/json');

	
function hutkigroshPOST($method, $dataJSON = null, $cookies = null) {
    $url = 'https://www.hutkigrosh.by/API/v1/' . $method;
    $ch = curl_init();

    // Настройки cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Формирование заголовков запроса
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    // Устанавливаем тело запроса, если оно передано
    if (isset($dataJSON)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);  // Устанавливаем тело запроса
        $headers[] = "Content-Length: " . strlen($dataJSON);
    }

    // Если переданы куки, добавляем их в запрос
    if ($cookies) {
        $cookieString = implode('; ', $cookies);
        curl_setopt($ch, CURLOPT_COOKIE, $cookieString);  // Передаем куки напрямую
    }

    // Устанавливаем заголовки
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    // Устанавливаем заголовки

    curl_setopt($ch, CURLOPT_HEADER, true);           // Возвращать заголовки ответа

    // Добавляем таймауты
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);            // Таймаут для выполнения запроса (30 секунд)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);     // Таймаут для подключения (10 секунд)

    // Выполняем запрос
    $response = curl_exec($ch);

    // Проверка на ошибки
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: '. curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Получаем заголовки и тело ответа
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);    // Заголовки ответа
    $body = substr($response, $header_size);          // Тело ответа (результат)

    // Извлечение куки из заголовков, если это не метод LogOut
    $responseCookies = [];
    if ($method !== 'Security/LogOut') {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
        foreach ($matches[1] as $item) {
            $responseCookies[] = $item;  // Сохраняем куки как строки
        }
    }

    curl_close($ch);  // Закрываем cURL

    // Возвращаем тело, заголовки и куки ответа
    return ['headers' => $headers, 'body' => $body, 'cookies' => $responseCookies];
}






$method = explode("/", $_SERVER["SCRIPT_URL"])[2];

if ($method == 'test_sms') {
//	$res = send_sms_mysim ('375296767861', 'tt1');
	$url ='http://195.222.74.241:5038/cgi/WebCGI?1500101=account=apisms&password=2rGFHuuprBBN4563&port=1&destination=375296767861&content=t1fr';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);
	echo $response;
	die;
	
	
	
}


if ($method == 'get_grosh_token') {

	$responseLogin = hutkigroshPOST('Security/LogIn', json_encode(['user' => $grosh_login,'pwd' => $grosh_password]));
	$cookies = $responseLogin['cookies'];  // Получаем куки для дальнейшего использования

// Используем полученные куки для создания счета
$invId = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
$billData = json_encode([
    'eripId' => 16743001,
    'invId' => $invId ,
    'dueDt' => "/Date(" . (time() * 1000 + 86400 * 1000) . "+0300)/",
    'addedDt' => "/Date(" . (time() * 1000) . "+0300)/",
    'payedDt' => null,
    'fullName' => 'Пупкин Василий Иванович',
    'mobilePhone' => '+375296767861',
    'notifyByMobilePhone' => true,
    'email' => null,
    'notifyByEMail' => false,
    'fullAddress' => 'г.Минск, ул. Надеждинская, 2-1',
    'amt' => 1.10,
    'curr' => 'BYN',
    'statusEnum' => 0,
    'info' => null,
    'products' => [
        [
            'invItemId' => 'Артикул 123',
            'desc' => 'Товар',
            'count' => 1,
            'amt' => 1.10
        ]
    ]
]);

$responseBill = hutkigroshPOST('Invoicing/Bill', $billData, $cookies);  
$billid = json_decode($responseBill['body'], true)['billID'];
print_r($billID);
echo PHP_EOL.PHP_EOL;



$paydata = '{
"billId":'.$billid.',
"returnUrl":"http://localhost/?success",
"cancelReturnUrl":"http://localhost/?error",
"submitValue":"Оплатить картой",
}';

//echo PHP_EOL.$paydata.PHP_EOL;

$payresponse = hutkigroshPOST('Pay/WebPay', $paydata, $cookies);  
$html = json_decode($payresponse['body'], true)['form'];
$htmlWithAutoSubmit = $html . '<script>document.forms[0].submit();</script>';

// Выводим HTML с автосабмитом
echo $htmlWithAutoSubmit;
























// Выполняем LogOut, используя куки
$responseLogout = hutkigroshPOST('Security/LogOut', null, $cookies);

    // Выводим полученные куки
}

