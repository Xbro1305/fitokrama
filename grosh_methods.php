<?php
	include_once  'mnn.php';
	//header('Content-Type: application/json');

function hutkigroshLogIn() {			// авторизация 
	GLOBAL $grosh_login, $grosh_password, $grosh_eripId;
	$responseLogin = hutkigroshPOST('Security/LogIn', json_encode(['user' => $grosh_login,'pwd' => $grosh_password]));
	$cookies = $responseLogin['cookies'];
	return $cookies;

	
}
	
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

function hutkigrosh_new_POST($method, $dataJSON = null) {
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
	
	die($response);
	
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

function hutkigroshGET($method, $cookies = null) {
    $url = 'https://www.hutkigrosh.by/API/v1/' . $method;

    // Инициализация cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Устанавливаем заголовки
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Передача куки, если они заданы
    $cookieString = '';
    if ($cookies) {
        $cookieString = implode('; ', $cookies);
        curl_setopt($ch, CURLOPT_COOKIE, $cookieString);
    }

    // Захватываем и заголовки, и тело ответа
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    // Выполняем запрос
    $response = curl_exec($ch);

    // Проверка на ошибки
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Получаем информацию о заголовках и теле ответа
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers_response = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // Закрываем cURL
    curl_close($ch);

    // Формируем понятный вывод для разработчика
    echo "=== Request Information ===\n";
    echo "URL: $url\n";
    echo "Method: GET\n";
    echo "Headers Sent:\n";
    foreach ($headers as $header) {
        echo "  $header\n";
    }
    echo "\nCookies Sent: " . ($cookieString ?: 'None') . "\n";
    
    echo "\n=== Response Information ===\n";
    echo "Status Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    echo "Response Headers:\n$headers_response\n";
    echo "Response Body:\n$body\n";
    
    // Возвращаем заголовки и тело в массиве, если нужно для дальнейшей обработки
    return ['headers' => $headers_response, 'body' => $body];
}

function hutkigrosh_new_GET($method) {
    GLOBAL $grosh_apiKey;
    $url = 'https://api.hgrosh.by/'.$method;

    // Инициализация cURL
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

    // Закрываем cURL
    curl_close($ch);

    // Вывод ответа
    echo $url.'</br>';

    // Возвращаем ответ для дальнейшей обработки, если нужно
    return json_decode($response, true);
}

function convertToGuid($billid) {
    // Преобразуем число в 16-ричную строку
    $hex = base_convert($billid, 10, 16);
    
    // Приводим строку к нужной длине (если требуется дополнение)
    $hex = str_pad($hex, 32, '0', STR_PAD_LEFT);
    
    // Форматируем строку как UUID
    $uuid = substr($hex, 0, 8) . '-' .
            substr($hex, 8, 4) . '-' .
            substr($hex, 12, 4) . '-' .
            substr($hex, 16, 4) . '-' .
            substr($hex, 20);
    
    return $uuid;
}

function new_hutki_invoice($order_number,$sum,$cart)		// сформировать новый счет по заказу и вернуть его id
{
	
	$invId = 'FTKRM-' . $order_number . '-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
	$billData = json_encode([
    'number' => $invId,  
    'currency' => 'BYN', 
    'serviceProviderInfo' => [
        'serviceProviderCode' => 10020002  
    ],
    'paymentChannels' => [  // Указываем каналы оплаты
        'ERIP', 'WEBPAY'
		// Канал оплаты, как в примере разработчика
    ],
    'payerInfo' => [  // Структурированная информация о плательщике
        'contact' => [
            'firstName' => $cart['client_name'],  // Имя клиента
            'lastName' => '',  // Можно добавить поле, если нужно
            'middleName' => ''  // Можно добавить поле, если нужно
        ],
        'phone' => [
            'type' => 'string',
            'countryCode' => '375',  // Код страны
            'fullNumber' => $cart['client_phone'],  // Полный номер телефона
            'isMain' => true  // Основной телефон
        ],
        'address' => [
            'country' => 'Беларусь',  // Страна
            'line1' => $cart['client_address'],  // Основной адрес
            'line2' => '',  // Можно добавить поле, если нужно
            'city' => '',  // Город, если доступен
            'postalCode' => ''  // Индекс, если доступен
        ],
        'email' => $cart['client_email'],  // Электронная почта клиента
        'note' => '',  // Дополнительные заметки, если есть
        'notifyParams' => [  // Параметры уведомлений
            'eventTypes' => [
                'INVOICE_CREATED'  // Уведомление при создании счета
            ],
            'notifyByEMail' => false,  // Уведомление по email
            'notifyBySMS' => false  // Уведомление по SMS
        ]
    ],
    'items' => [  // Продукты в счете
        [
            'code' => 'Артикул 123',  // Код товара
            'name' => 'Товар',  // Наименование товара
            'description' => 'Описание товара',  // Описание товара
            'quantity' => 1,  // Количество товаров
            'measure' => 'шт.',  // Единица измерения (например, штуки)
            'unitPrice' => $sum,  // Цена за единицу товара
            'discount' => [  // Скидка
                'percent' => 0,  // Процент скидки
                'label' => ''  // Описание скидки
            ]
        ]
    ],
    'discount' => [  // Общая скидка
        'percent' => 0,  // Процент скидки
        'amount' => 0,  // Сумма скидки
        'label' => ''  // Описание скидки
    ],
    'penalty' => [  // Штрафы
        'percent' => 0,  // Процент штрафа
        'amount' => 0,  // Сумма штрафа
        'label' => ''  // Описание штрафа
    ],
    'note' => 'Et vitae praesentium eius temporibus nemo est molestiae beatae.',  // Примечание к счету
    'amount' => $sum,  // Общая сумма счета
    'transactions' => []  // Транзакции (может быть пустым)
	]);


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
	$paydata = '{
	"billId":'.$billid.',
	"returnUrl":		"https://fitokrama.by/payment_received.php/hutki_incoming_ok",
	"cancelReturnUrl":	"https://fitokrama.by/payment_received.php/hutki_incoming_no",
	"submitValue":"Оплатить картой",
	}';
	
	$payresponse = hutkigroshPOST('Pay/WebPay', $paydata, $cookies);
	$html = json_decode($payresponse['body'], true)['form'];

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
}



if ($method == 'check') 
{
	$invoiceid = '00000000-0000-0000-6d47-cd108ee1dc08';
	//$invoiceid = '6bece491-12ff-43ca-b0fe-5b99a1b48217';
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid?api-version=2.0");
	echo json_encode($response).PHP_EOL.PHP_EOL;
	die;
}
if ($method == 'bills') 
{
	$response = hutkigrosh_new_GET("invoicing/invoice?api-version=2.0&beginDate=2024-09-28");
	echo json_encode($response).PHP_EOL.PHP_EOL;
	die;
}

if ($method == 'newqr') 
{
	$invoiceid = '00000000-0000-0000-6d47-cd108ee1dc08';
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid/qr?channelType=ERIP&width=256&height=256&api-version=2.0");
	$base64Image = $response['image']; // Извлечение base64-строки из ответа
// Выводим HTML для отображения картинки
echo '<img src="data:image/png;base64,' . $base64Image . '" alt="QR Code" />';

	die;
}
if ($method == 'newlink') 
{
	$invoiceid = '00000000-0000-0000-6d47-cd108ee1dc08';
	$response = hutkigrosh_new_GET("invoicing/invoice/$invoiceid/link?paymentChannel=ERIP&api-version=2.0");
	$url = $response['url']; // Извлечение base64-строки из ответа
	echo json_encode($url);
	die;
}
