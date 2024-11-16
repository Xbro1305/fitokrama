<?php
	include_once  'mnn.php';
	header('Content-Type: application/json');

function hutkigrosh_new_POST($method, $dataJSON = null,$test=false) {
    GLOBAL $grosh_apiKey;
  $url = 'https://api.hgrosh.by/'.$method;    
  $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json",
		"x-api-version: 2.0",
        "Authorization: Bearer $grosh_apiKey" 
		
    ];
    if (isset($dataJSON)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);  // Устанавливаем тело запроса
        $headers[] = "Content-Length: " . strlen($dataJSON);
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
    curl_close($ch);  // Закрываем cURL
    return ['headers' => $headers, 'body' => $body];
}

function hutkigrosh_new_GET($method,$test=false) {
	GLOBAL $grosh_apiKey;
    $url = 'https://api.hgrosh.by/'.$method;
	if ($test) echo ('URL --- '.$url.PHP_EOL);
	if ($test) echo ('IP by url--- '.gethostbyname($url).PHP_EOL);
	if ($test) curl_setopt($ch, CURLOPT_VERBOSE, true);

	
	$ch = curl_init();
    // Настройка URL и метода запроса
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    // Установка заголовков
    $headers = [
        'x-api-version: 2.0',
        'Accept: application/json',
        'Authorization: Bearer ' . $grosh_apiKey  // Подстановка API ключа
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Возврат ответа как строки вместо вывода напрямую
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Выполнение запроса
    $response = curl_exec($ch);
	if ($test) echo ('response --- '.$response.PHP_EOL);
	if ($test) echo ('error --- '.curl_error($ch).PHP_EOL);
	
    // Проверка на ошибки
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return json_decode($response, true);
}

function new_hutki_invoice($order_number,$sum,$cart)		// сформировать новый счет по заказу и вернуть его id
{
	
	$nowUtcPlus2Hours = (new DateTime('now', new DateTimeZone('UTC')))
                    ->modify('+2 hours')
                    ->format('Y-m-d\TH:i:s\Z');					
	$addDateUTC = (new DateTime('now', new DateTimeZone('UTC')))
                    ->format('Y-m-d\TH:i:s\Z');					
	
	$invId = '' . $order_number . '-' . strtoupper(substr(str_shuffle('0123456789'), 0, 4));
	$billData_arr = [
    'number' => $invId,  
    'currency' => 'BYN', 
	'invoiceDates' => [
		'addDateUTC' => $addDateUTC,
		'dateInAirUTC' => $addDateUTC,
		'lastPayDateUTC' => $nowUtcPlus2Hours
	],
    'serviceProviderInfo' => [
        'serviceCode' => 20032002  
    ],
    'paymentChannels' => [  // каналы оплаты
        'ERIP', 'WEBPAY'
    ],
    'payerInfo' => [  // информация о плательщике
        'contact' => [
            'firstName' => $cart['client_name'],  // Имя клиента
            'lastName' => '',  
            'middleName' => '' 
        ],
        'address' => [
            'country' => 'Беларусь',
            'line1' => $cart['client_address'], 
        ],
		
        'email' => $cart['client_email'],
        'notifyParams' => [  
            'eventTypes' => [
                'INVOICE_CREATED' 
            ],
            'notifyByEMail' => false, 
            'notifyBySMS' => false  
        ]
    ],
    'items' => [  // Продукты в счете
        [
            'code' => '',  // Код товара
            'name' => 'Заказ '.$order_number,  // Наименование товара
            'description' => '',  // Описание товара
            'quantity' => 1,  // Количество товаров
            'measure' => 'шт.',  // Единица измерения (например, штуки)
            'unitPrice' => $sum
            ]
        ],
    'note' => 'Заказ № '.$order_number,
    'amount' => $sum,  
    'transactions' => []  
	];
	//foreach $cart['items'] as $item1
	
	$billData = json_encode($billData_arr);

	$responseBill = hutkigrosh_new_POST('invoicing/invoice?api-version=2.0', $billData);  
	
	$body = $responseBill['body'];
	$invoiceid = json_decode($body, true)['invoiceId'];	
	if (is_null($invoiceid))
		send_warning_telegram('Ошибка формирования счета hutki '.json_encode($billData).'  ->  '.json_encode($responseBill));
	return $invoiceid;
}


	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];


if ($method == 'new_invoice_test') 
{
	[$order_number,$sum,$cart] = ['123456', 1.11, ['client_name'=>'Имя клиента','client_address'=>'Адрес клиента','client_email'=>'client@client.com']];
		
	$nowUtcPlus2Hours = (new DateTime('now', new DateTimeZone('UTC')))
                    ->modify('+2 hours')
                    ->format('Y-m-d\TH:i:s\Z');					
	$addDateUTC = (new DateTime('now', new DateTimeZone('UTC')))
                    ->format('Y-m-d\TH:i:s\Z');					
	
	$invId = '' . $order_number . '-' . strtoupper(substr(str_shuffle('0123456789'), 0, 4));
	$billData_arr = [
    'number' => $invId,  
    'currency' => 'BYN', 
	'invoiceDates' => [
		'addDateUTC' => $addDateUTC,
		'dateInAirUTC' => $addDateUTC,
		'lastPayDateUTC' => $nowUtcPlus2Hours
	],
    'serviceProviderInfo' => [
        'serviceCode' => 20032002  
    ],
    'paymentChannels' => [  // каналы оплаты
        'ERIP', 'WEBPAY'
    ],
    'payerInfo' => [  // информация о плательщике
        'contact' => [
            'firstName' => $cart['client_name'],  // Имя клиента
            'lastName' => '',  
            'middleName' => '' 
        ],
        'address' => [
            'country' => 'Беларусь',
            'line1' => $cart['client_address'], 
        ],
		
        'email' => $cart['client_email'],
        'notifyParams' => [  
            'eventTypes' => [
                'INVOICE_CREATED' 
            ],
            'notifyByEMail' => false, 
            'notifyBySMS' => false  
        ]
    ],
    'items' => [  // Продукты в счете
        [
            'code' => '',  // Код товара
            'name' => 'Заказ '.$order_number,  // Наименование товара
            'description' => '',  // Описание товара
            'quantity' => 1,  // Количество товаров
            'measure' => 'шт.',  // Единица измерения (например, штуки)
            'unitPrice' => $sum
            ]
        ],
    'note' => 'Заказ № '.$order_number,
    'amount' => $sum,  
    'transactions' => []  
	];
	//foreach $cart['items'] as $item1
	
	$billData = json_encode($billData_arr);
	header('Content-Type: application/json');	
	
	echo('--- PAYLOAD --- '.PHP_EOL.json_encode($billData_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);

	$responseBill = json_decode(hutkigrosh_new_POST('invoicing/invoice?api-version=2.0', $billData)['body'],true);  
	
	echo('--- RESPONSE (BODY)--- '.PHP_EOL.json_encode($responseBill, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);
	$invoiceid = $responseBill['invoiceId'];
	echo('--- GET_URL --- '.PHP_EOL."invoicing/invoice/$invoiceid?api-version=2.0");
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid?api-version=2.0");
	echo('--- RESPONSE --- '.PHP_EOL.json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);


}

if ($method == 'linkbyid') 
{
	$invoiceid = $_GET['invoiceid'];
	$paymentChannel = 'ERIP'; // Enum: "ERIP" "EPOS" "WEBPAY"
	$returnUrl = "https://fitokrama.by/payment_received.php/hutki_incoming_ok";
	$cancelReturnUrl = "https://fitokrama.by/payment_received.php/hutki_incoming_no";
	$submitValue = '%3Cstring%3E';
	$url = "invoicing/invoice/$invoiceid/webpay?paymentChannel=$paymentChannel&returnUrl=%3Cstring%3E&cancelReturnUrl=%3Cstring%3E&submitValue=$submitValue&api-version=2.0";
	$response = hutkigrosh_new_GET($url);
	
	// Выводим HTML форму
	echo $response['form'];
	die;

	// Добавляем JavaScript для автоматического отправления формы
	echo '
	<script type="text/javascript">
		window.onload = function() {
			document.forms[0].submit(); // Автоматически отправляем первую форму на странице
		};
	</script>';

die;


}

function erip_check($invoiceid)  		// проверить статус оплаты 
{
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid?api-version=2.0",false);
	
	if ($response['state']=='PAID')		
			return ['erip','erip_check tr_id '.$response['transactions'][0]['id'],$response['amount']];
	else 	return [NULL,NULL,0];
}


function erip_kill ($invoiceid,$test=NULL) 	// отключить неоплаченный счет
{
	GLOBAL $grosh_apiKey;
	header('Content-Type: application/json');
    
	[,,$sum] = erip_check($invoiceid);
	if ($sum!=0) return ('error: invoce has been paid');
	$url = "https://api.hgrosh.by/invoicing/invoice/$invoiceid/draft?api-version=2.0";
	if ($test) echo ('URL --- '.$url.PHP_EOL);
	if ($test) echo ('method --- '.'PATCH'.PHP_EOL);
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    $headers = [
        'x-api-version: 2.0',
        'Accept: application/json',
        'Authorization: Bearer ' . $grosh_apiKey  // Подстановка API ключа
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
	if ($test) echo ('response --- '.$response.PHP_EOL);
	if ($test) echo ('error --- '.curl_error($ch).PHP_EOL);
	
    return('ok');
}
	
if ($method == 'erip_kill') 
{
	$invoiceid = $_GET['invoiceid'];
	$res = erip_kill($invoiceid);
	echo json_encode($res).PHP_EOL.PHP_EOL;
	die;
}	



if ($method == 'check') 
{
	$invoiceid = $_GET['invoiceid'];
	$res = erip_check($invoiceid);
	echo json_encode($res).PHP_EOL.PHP_EOL;
	die;
}

if ($method == 'bills') 
{
	
	$response = hutkigrosh_new_GET("invoicing/invoice?skip=0&take=10&beginDate=2024-10-01&endDate=2024-12-31&states=ACTIVE&queryType=EMPTY&sortType=BY_BEGIN_DATE&order=DESCENDING&api-version=2.0",true);
	echo json_encode($response).PHP_EOL.PHP_EOL;
	die;
}

if ($method == 'newqr') 
{
	$invoiceid = $_GET['invoiceid'];
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid/qr?channelType=ERIP&width=256&height=256&api-version=2.0");
	$base64Image = $response['image']; // Извлечение base64-строки из ответа
// Выводим HTML для отображения картинки
echo '<img src="data:image/png;base64,' . $base64Image . '" alt="QR Code" />';

	die;
}
if ($method == 'newlink') 
{
	$invoiceid = $_GET['invoiceid'];
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid/link?paymentChannel=ERIP&api-version=2.0");
	$url = $response['url']; // Извлечение base64-строки из ответа
	echo json_encode($url);
	die;
}

if ($method == 'aboutdev') 
{
	//$invoiceid = $_GET['invoiceid'];
	$response = hutkigrosh_new_GET("invoicing/metadata?api-version=2.0");
	$url = $response['url']; // Извлечение base64-строки из ответа
	echo json_encode($url);
	die;
}
