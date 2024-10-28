<?php
	include_once  'mnn.php';
	//header('Content-Type: application/json');

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

function hutkigrosh_new_GET($method) {
	GLOBAL $grosh_apiKey;
    $url = 'https://api.hgrosh.by/'.$method;
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
	
	$invId = '' . $order_number . '-' . strtoupper(substr(str_shuffle('0123456789'), 0, 4));
	$billData_arr = [
    'number' => $invId,  
    'currency' => 'BYN', 
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


if ($method == 'new_invoice') 
{

	$invoiceid = new_hutki_invoice('123456',1.10,['client_name'=>'Кенгерли Эмиль Рафикович','client_email'=>'kenherli@gmail.com','client_address'=>'Ратомка, ул. Корицкого, 30А','client_phone'=>'+375296767861']);
	die($invoiceid);
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


die;
}



if ($method == 'check') 
{
	$invoiceid = $_GET['invoiceid'];
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid?api-version=2.0");
	echo json_encode($response).PHP_EOL.PHP_EOL;
	die;
}
if ($method == 'bills') 
{
	
	$response = hutkigrosh_new_GET("invoicing/invoice?skip=0&take=10&beginDate=2024-10-01&endDate=2024-12-31&states=ACTIVE&queryType=EMPTY&sortType=BY_BEGIN_DATE&order=DESCENDING&api-version=2.0");
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
