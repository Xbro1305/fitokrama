<?php
	include_once  'mnn.php';
	header('Content-Type: application/json');

function yandex_post($method, $data, $test = false)
{
    GLOBAL $yandex_delivery_token;
    $url = 'https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/' . $method;
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
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $yandex_delivery_token,
            'Accept-Language: ru-RU',
        ),
        CURLOPT_HEADER => true, // Включаем заголовки в ответ
    ));

    $response = curl_exec($curl);
    
    // Получаем размер заголовков
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size); // Заголовки
    $body = substr($response, $header_size); // Тело ответа
    
    // Ищем заголовок X-YaTraceId
    if (preg_match('/^X-YaTraceId:\s*(.+)$/mi', $headers, $matches)) {
        $trace_id = trim($matches[1]);
    } else {
        $trace_id = 'X-YaTraceId не найден в заголовках';
    }

    if ($test) {
        echo '----- PAYLOAD -----' . PHP_EOL . PHP_EOL . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
        echo '----- URL -----' . PHP_EOL . PHP_EOL . $url . PHP_EOL . PHP_EOL;
        echo '----- RESPONSE -----' . PHP_EOL . PHP_EOL . $body . PHP_EOL . PHP_EOL;
        echo '----- X-YaTraceId -----' . PHP_EOL . PHP_EOL . $trace_id . PHP_EOL . PHP_EOL;
    }

    curl_close($curl);
    return $body;
}


function yandex_check_price($address, $qty, $weight) {

	$dayOfWeek = date('N'); // номер дня недели (1 для понедельника, 7 для воскресенья)
	$currentTime = date('H:i'); // время в формате ЧЧ:ММ

	if ($dayOfWeek < 1 || $dayOfWeek > 5 || $currentTime < '09:00' || $currentTime > '16:30') 
		return NULL;			//	в эти дни и это время доставка не осуществляется
    
	$data = array();
	$data['route_points'][] = ['coordinates'=>[27.588279,53.930548], 'fullname'=>'улица Леонида Беды, 2Б, Минск', 'id'=> 1];
	$data['route_points'][] = [/*'coordinates'=>[27.484441,53.914833],*/ 'fullname'=>$address, 'id'=> 2];
	$data['items'][] = ['quantity'=>$qty, 'dropoff_point'=>1, 'pickup_point'=>2, 'size'=> array('length' => 0.1, 'width' => 0.1, 'height' => 0.1), 'weight'=>$weight];
	$data['Requirements'] = ['taxi_class'=>'courier', 'pro_courier'=>false];
	//echo 'data: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
	
	file_put_contents('yandex_delivery_log.txt', json_encode($data).PHP_EOL , FILE_APPEND | LOCK_EX);
	$response = yandex_post ('check-price',$data);
	file_put_contents('yandex_delivery_log.txt', json_encode($response).PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);
    
    return json_decode($response,TRUE);
}

function yandex_get_lable($order_number,$track_number)	// сформировать наклейку
{				
	$sticker_data = ExecSQL($link,"SELECT * FROM orders WHERE number='$order_number'")[0];
	$sticker_data['senderdata'] = 'Отправитель: Офис-склад Фитокрама. Улица Леонида Беды, 2Б, Минск, офис 316. Тел. +375445975005';
	
	$doc = file_get_contents('post_stickers/yandex_sticker.html'); // берем шаблон стикера
	
	foreach (array(
		'number', 
		'client_phone', 
		'client_name', 
		'order_point_address', 
		'datetime_assembly', 
		'track_number', 
		'post_code', 
		'internal_postcode'
	) as $param)
		if (isset($sticker_data[$param])) 
		$doc = str_replace("[$param]",$sticker_data[$param],$doc);
	
	
	
	$post_code = $sticker_data['post_code'];
	$doc = str_replace("[parcelNum]",$post_code,$doc);
	
	$sticker_filename = "stickers/$order_number.html";	
	file_put_contents($sticker_filename,$doc);
    return [$sticker_filename,$post_code];
}


function yandex_send($order) {

	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);

	$order_number = $order['number'];

	$request_id = 'FTKR_'.$order_number.'_'.strtoupper(substr(md5(rand(1,1000)), 0, 4));
	$data = array();
	
	$data['request_id'] = $request_id;
	
	$point_from['coordinates']=[27.588279,53.930548];
	$point_from['fullname']='Офис-склад Фитокрама';
	$point_from['address']['fullname']='улица Леонида Беды, 2Б, офис 316, Минск';
	$point_from['point_id']= 1;
	$point_from['visit_order']=1;
	$point_from['type']='source';
	$point_from["contact"] = 
                ["name"=> "Офис Фитокрама (316)",
                "phone"=> "+375445975005",
                "phone_additional_code"=> "",
                "email"=> "info@fitokrama.by"];
	
	//die(json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	
	$point_to['coordinates']=[$order['lng'],$order['lat']];
	$point_to['fullname']=$order['client_name'];
	$point_to['address']['fullname']=$order['order_point_address'];
	$point_to['point_id']= 2;
	$point_to['visit_order']=2 ;
	$point_to['type']='destination';
	$point_to["contact"] = 
                ["name"=> $order['client_name'],
                "phone"=> $order['client_phone'],
                "phone_additional_code"=> ""];
	
	
	
	
	
	$data['route_points'][] = $point_from; 
	$data['route_points'][] = $point_to; 
	
	$data['items'][] = ['title'=>'Покупка. Заказ №'.$order_number, 'cost_value'=>$order['sum'], 'cost_currency'=>'BYN','quantity'=>$qty, 'dropoff_point'=>2, 'pickup_point'=>1, 'size'=> array('length' => 0.4, 'width' => 0.2, 'height' => 0.1), 'weight'=>$weight];
	
	
	
	
	
	$data['auto_accept'] = true;			// можно сделать автоподтверждение по согласованию менеджера
	$data['skip_confirmation'] = true;
	
	$data['callback_properties']['callback_url'] = "https://fitokrama.by/post_checker.php/imcoming?order_number=$order_number&";
	$data['client_requirements'] = ['taxi_class'=>'courier', 'pro_courier'=>false];
	$data['comment'] = 'Необходимо забрать товар по склада Фитокрама и доставить клиенту.';
	$data['emergency_contact'] = ['name'=>'Аварийный телефон Фитокрама', 'phone'=> '+375296562441'];
	//$data['offer_payload'] = $offer_payload;			// непонятно. метод offers/calculate недоступен в Беларуси !!!!!!!!!!!!!!!!!!
	$data['optional_return'] = false;
	$data['referral_source'] = 'fitokrama.by';
	//$data['same_day_data'] = ['delivery_interval'=> ['from'=> (new DateTime('now', new DateTimeZone('Europe/Minsk')))->format('Y-m-d\TH:i:sP'), 'to'=> (new DateTime('now', new DateTimeZone('Europe/Minsk')))->modify('+1 hour')->format('Y-m-d\TH:i:sP')]];
	//$data['features_context'] = NULL;
	$data['referral_source'] = 'fitokrama.by';
	
	
	$response = yandex_post ("claims/create?request_id=$request_id",$data,false);
	$response_ = json_decode($response,TRUE);
	
	if (!isset($response_['id']))
	{
		send_warning_telegram('Yandex_send 143 ERROR: '.$response);
		die;
	}
    
	$id_code = $response_['id'];
	$track_number = 'yandex_'.$order_number;
	$label_filename = 'no_label';
	
	return [$track_number,$id_code,$label_filename,$request_id];
}

function tariffs($address) {
    $data = array(
        'fullname' => $address
        );
    
	
	$response = yandex_post ('tariffs',$data);
    return $response;
}

/*function delivery_methods($address) {
    $data = array(
        'fullname' => $address,
		'start_point' => [27.588279,53.930548]
        );
    
	
	$response = yandex_post ('delivery-methods',$data);
    return $response;
}*/

$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='yandex_incoming') // вызванный webhook при изменении статуса доставки
	{
		$link = firstconnect ();
		$payload = file_get_contents("php://input");
		if ($payload==NULL) exit ('no data');
		$data = json_decode($payload,TRUE);
		file_put_contents('yandex_incoming.txt', $payload.PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);
		file_put_contents('yandex_incoming.txt', json_encode($_GET).PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);
		send_warning_telegram('from Yandex: '.$payload);
		exit;
	}

if ($method=='test') // тестирование функций
	{

	$address = 'Минск, Шаранговича, 22';
	//echo 'tariffs: '. PHP_EOL. tariffs ($address).PHP_EOL.PHP_EOL;
	//echo 'delivery-methods: '. PHP_EOL. delivery_methods ($address).PHP_EOL.PHP_EOL;
	
	
	$qty = 22;
	$weight = 0.2;
	
	echo 'check_price: '. json_decode(yandex_check_price ($address,$qty,$weight)).PHP_EOL.PHP_EOL;
	
	
	//echo 'create: '.create($address, $qty, $weight, $order_number).PHP_EOL.PHP_EOL;

	exit;

	}

if ($method=='check_price') // информация о стоимости доставки
	{

	$address = $_GET['address'];
	$qty	 = round($_GET['qty']);
	$weight  = floatval($_GET['weight']);
	
	$res = yandex_check_price ($address,$qty,$weight);
	
	//die (json_encode($res));
	
	if (!isset($res['price']))
		die(json_encode(['status'=>'error', 'error'=>'Yandex calculation error']));
	$price = $res['price'];
	$distance_meters = round($res['distance_meters']);
	$eta = round($res['eta']);
	exit(json_encode(['status'=>'ok', 'price'=>$price, 'distance_meters'=>$distance_meters, 'waiting_min'=>$eta]));
	
	
	
	

	exit;

	}


//die ('incorrect method');


