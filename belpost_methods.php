<?php
	include_once  'mnn.php';
	header('Content-Type: application/json');

function belpochta_post($method, $data, $getpost, $test = false) 
{
    global $belpost_token;
    $url = 'https://api.belpost.by/api/v1/business/' . $method; 
    $curl = curl_init();
    
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $getpost, // GET или POST
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json', // Добавлен accept заголовок для получения JSON
            'Authorization: Bearer ' . $belpost_token,
        ),
    );

    if ($getpost === 'POST') {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    curl_setopt_array($curl, $options);

    if ($test) echo '---   ' . $url . ' --- ' . json_encode($data) . '   ---' . PHP_EOL . PHP_EOL;
    
    $response = curl_exec($curl);
    
    if ($response === false) {
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);
        send_warning_telegram('belpochta request error: '. "cURL Error ({$errorNumber}): {$errorMessage}");
        error_log("belpochta cURL Error ({$errorNumber}): {$errorMessage}");
        die( "belpochta cURL Error ({$errorNumber}): {$errorMessage}");
    }

    curl_close($curl);
    
    // Декодируем ответ
    $res = json_decode($response, TRUE);
    
    // Проверка на ошибки при декодировании JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        $jsonError = json_last_error_msg();
        send_warning_telegram('belpochta JSON decode error: ' . $jsonError);
        error_log("belpochta JSON decode error: " . $jsonError);
        die("belpochta JSON decode error: " . $jsonError);
    }

    return $res;
}





function refresh_belpochta_data() 			//обновление базы пунктов выдачи в Беларуси
{ 
	GLOBAL $link;

	$page = 0;
	$belpochta_points = array ();
	while (true)
	{
		$page++; 
		$res = belpochta_post('geo-directory/ops/list?page='.$page, [], 'GET', false);
		$belpochta_points = array_merge($belpochta_points,$res['data']); 
		if ($res['meta']['current_page']==$res['meta']['last_page']) break;
	}

	if (count($belpochta_points)<5)
	{
		send_warning_telegram('Внимание! Количество полученных пунктов belpochta <5. Обновление аварийно завершено!');
		die (json_encode(['status'=>'error', 'message'=> 'belpochta refresh calcelled']));
	}
	
	$partner_id = 6;
	$partner_prefix = 'BPS';
	$datetime_refresh_start = date('Y-m-d H:i:s');	//	запомнить момент начала обновления
	
	foreach ($belpochta_points as $belpochta_point)
	{
		$unique_id = $partner_prefix.'-'.$belpochta_point['postcode'];
		$address = $belpochta_point['address'];
		$descript = $belpochta_point['name'].' Белпочта';
		$lat = $belpochta_point['latitude'];
		$lng = $belpochta_point['longitude'];
		$shed = '';// $belpochta_point['Info1'];  !!!!!!!!!!!!!!!! расписание не анализируем
		
		//$comment = $belpochta_point['']['operation'];
		// Обновляем или вставляем данные в таблицу `delivery_points`
		$que = "
			INSERT INTO `delivery_points` 
			(unique_id, datetime_updated, actual_until_datetime, partner_id, address, name, comment, lat, lng)
			VALUES (?, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR), ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
				datetime_updated = CURRENT_TIMESTAMP,
				actual_until_datetime = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR),
				partner_id = ?,
				address = ?,
				name = ?,
				comment = ?,
				lat = ?,
				lng = ?
		";

		$params = [
			$unique_id, $partner_id, $address, $descript, $shed, $lat, $lng,
			$partner_id, $address, $descript, $shed, $lat, $lng
		];

		Exec_PR_SQL($link, $que, $params);

		// Подсчёт количества деактивированных пунктов
		$que = "
			SELECT COUNT(*) 
			FROM `delivery_points` 
			WHERE datetime_updated < ? 
			AND actual_until_datetime > CURRENT_TIMESTAMP 
			AND partner_id = ?
		";
		$params = [$datetime_refresh_start, $partner_id];
		$deactivated = Exec_PR_SQL($link, $que, $params)[0]['COUNT(*)'];

		// Подсчёт количества активированных пунктов
		$que = "
			SELECT COUNT(*) 
			FROM `delivery_points` 
			WHERE datetime_updated >= ? 
			AND actual_until_datetime > CURRENT_TIMESTAMP 
			AND partner_id = ?
		";
		$params = [$datetime_refresh_start, $partner_id];
		$activated = Exec_PR_SQL($link, $que, $params)[0]['COUNT(*)'];

		// Деактивируем устаревшие пункты
		$que = "
			UPDATE `delivery_points` 
			SET actual_until_datetime = CURRENT_TIMESTAMP 
			WHERE datetime_updated < ? 
			AND partner_id = ?
		";
		$params = [$datetime_refresh_start, $partner_id];
		Exec_PR_SQL($link, $que, $params);

	if ($deactivated>0) send_warning_telegram('belpochta: деактивировано '.$deactivated.' пунктов.');
	exit (json_encode(['status'=>'ok', 'message'=> "Activated $activated points, Deactivated $deactivated points"])); 
}

function belpost_calculator($delivery_city,$weight,$volume,$selfDelivery,$index=NULL) 		//	произвести расчет стоимости доставки
{
	// определить используемый способ доставки
	
	$calc ['price']=4;
	$calc ['days']=3;
	//send_warning_telegram($weight.' - '.$volume.' - '.$calc ['price']);
	return($calc);


}

function belpost_get_jwt () {
		GLOBAL $belpost_loginname;
		GLOBAL $belpost_password;
		$data['LoginName'] = $belpost_loginname;
		$data['Password'] = $belpost_password;
		$data['LoginNameTypeId'] = "1";
			
		$re = belpochta_post('GetJWT', $data);
		
		if (isset($re['Table'][0]['JWT']))
			file_put_contents('belpost_jwt.json',$re['Table'][0]['JWT']);
		else send_warning_telegram('belpost. Ошибка получения токена. '.json_encode($re, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		exit (json_encode(['result'=>'ok']));
}

function belpost_get_lable($order_number,$track_number)	// получить наклейку
{				
	$data = array ();
	$data['SerialNumber'][]['SerialNumber'] = $track_number;
	$sticker_data = belpochta_post('Postal.GetPDFContent', $data);
	if (!isset($sticker_data['Table'][0]['PostalItemId']))
	{
		send_warning_telegram('Европочта получение стикера '.json_encode($sticker_data));
		return NULL;
	}
	
	$sticker_data = $sticker_data['Table'][0];
	
	$doc = file_get_contents('post_stickers/eur_sticker.html'); // берем шаблон стикера
	
	foreach (array(
		'PostalItemId', 
		'FromWhomFIO', 
		'FromWhomAddress', 
		'FromWhomPhone', 
		'WhereFIO', 
		'OPS', 
		'WhereAddress', 
		'PhoneNumberReciever',
		'Number',
		'Name1Reciever',
		'FromAddress',
		'StringValueStart_152',
		'StringValueStart_153',
		'StringValueStart_154',
		'StringValueFinish_152',
		'StringValueFinish_153',
		'StringValueFinish_154',
		'WarehouseIdFinish',
		'AddressRecieverName',
		'AddressSender',
		'PostalItemExternalId',
		'DateTimeOPS',
		'DateTimeBegin',
		'CashOnDeliverySum_1',
		'CashOnDeliveryDeclaredValueSum_1'
	) as $param)
		if (isset($sticker_data[$param])) 
		$doc = str_replace("[$param]",$sticker_data[$param],$doc);
	
	$post_code = $sticker_data['label']['parcelNum'];
	
	
	$doc = str_replace("[parcelNum]",$post_code,$doc);
	
	$sticker_filename = "stickers/$order_number.html";	
	
	
	
	file_put_contents($sticker_filename,$doc);
    return [$sticker_filename,$post_code];
}
	

function belpost_send($order) 
{
	
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);
	$PostalWeights = file_get_contents('belpochta_PostalWeights.json');
	foreach (json_decode($PostalWeights,TRUE) as $weights)
		if ($weight>=$weights['PostalWeightMin'] && $weight<=$weights['PostalWeightMax'])
		{
			$PostalWeightId = $weights['PostalWeightTypeId'];
			break;
		}
	if (!isset($PostalWeightId)) 
	{
		send_warning_telegram("Европочта. Не выбран идентификатор веса! для веса $weight в $delivery_city");
		die ();
	}
	
	$WarehouseIdFinish = preg_replace('/\D/', '', $order['delivery_submethod']);
	
	$order_number = $order['number'];
	$sum = $order['sum'];
	$request_id = 'FTKR_'.$order_number.'_'.strtoupper(substr(md5(rand(1,1000)), 0, 4));
	
	$data = array();
	$data['GoodsId'] = 836884;						// код отправления - посылка
	$data['PostDeliveryTypeId'] = 1; 				// от отделения до отделения
	$data['PostalWeightId'] = $PostalWeightId;		// дип диапазона веса
	$data['WarehouseIdStart'] = 70130012 ;			// ID пункта выдачи - Жебрака
	$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
	//$data['Adress1IdReciever'] = ; 				//  ID адреса выдачи
	
	$data['CashOnDeliveryDeclareValueSum'] = $sum;
	
	$data['PhoneNumberReciever'] = $order['client_phone'];	// телефон клиента
	
	
	$name_parts = explode(' ', $order['client_name'], 3);
	
	$client_surname = $name_parts[0]; // Все, что до первого пробела
	$client_name_1 = isset($name_parts[1]) ? $name_parts[1] : ' '; // 2-я часть
	$client_name_2 = isset($name_parts[2]) ? $name_parts[2] : ' '; // 3-я часть
	
	$data['Name1Reciever'] = $client_surname;		// имя клиента 1
	$data['Name2Reciever'] = $client_name_1;		// имя клиента 1
	$data['Name2Reciever'] = $client_name_2;		// имя клиента 2
	$data['CashOnDeliveryMoneyBackId'] = 1;			// оплачивает отправитель
	$data['InfoSender'] = "fitokrama.by. Заказ № order_number.";	// текстовый комментарий
	$data['PostalItemExternalId'] = $request_id;
	$data['IsRecieverShipping'] = 1;
	
	$res = belpochta_post('Postal.PutOrder', $data);
	
	if (!isset($res['Table'][0]['Number']))
	{
		send_warning_telegram('Европочта. Ошибка формирования заказа на отправку.'.$order_number.'  - '.json_encode($res));
		return (json_encode(['status'=>'error', 'message'=> 'DPD refresh calcelled']));
	}
	$track_number = $res['Table'][0]['Number'];
	
	$post_code = $res['Table'][0]['PostalItemId'];	// пока непонятно, что с ним делать
	
	[$label_filename,$post_code] = belpost_get_lable($order_number,$track_number);
	
	
	return [$track_number,$track_number,$label_filename];
}
		

$link = firstconnect ();
$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='home') // вариант определения цены доставки до дома
		{
			
		$data=array();
		$data['Text'] = $_GET['address'];						
		$res = belpochta_post('Addresses.Search4', $data,false);
		if (!isset($res['Table'][0]['Address4Id']))	die ('Ошибка определения адреса');
		$Address4Id = $res['Table'][0]['Address4Id'];

		$addr = json_decode(autocomplete_dadata($_GET['address']),TRUE);
		if (isset($addr['suggestions'][0]['data']['house'])) $house = $addr['suggestions'][0]['data']['house'];
														else $house = 0;
		echo ($house);

		$data=array();
		$data['Address4Id'] = $Address4Id;
		$data['Address3Name'] = $house;
		$res = belpochta_post('Addresses.GetAddressId', $data,false);
		
		if (!isset($res['Table'][0]['Address1Id'])) return NULL;
			
		$Adress1IdReciever	=$res['Table'][0]['Address1Id'];

		//$WarehouseIdFinish = 72130020; 
		$PostalWeightId = 20;
			
		$data=array();
		$data['GoodsId'] = 836884;						// код отправления - посылка
		$data['PostDeliveryTypeId'] = 2; 				// от отделения до отделения
		$data['PostalWeightId'] = $PostalWeightId;		// дип диапазона веса
		//$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
		$data['Adress1IdReciever'] = $Adress1IdReciever; 				//  ID адреса выдачи
		$data['IsJuristic'] = 1; 				// 1 - юрлицо
		$data['isOversize'] = 0; 				// 
		$data['IsRelabeling'] = 0; 				// 
		$data['IsRecieverShipping'] = 0; 				// оплата за счет отправителя
		
		
		
		$res = belpochta_post('Postal.CalculationTariff', $data);
		//exit($city.'  '.json_encode($re,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	}
	
if ($method=='test') // тестирование функций
	{
		$order_number = 883440;
		$order = all_about_order($order_number);
		
		$res = belpost_send($order) ;
		exit(json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	}
	
if ($method=='sticker') // тестирование функций
	{
		$order_number = $_GET['order_number'];
		$order = all_about_order($order_number);
		$track_number = $_GET['track_number'];
		
		
		
		$res = belpost_get_lable($order_number,$track_number);
		header('Content-Type: text/html; charset=UTF-8');
		header("Access-Control-Allow-Origin: $http_origin");
		if (isset($res[0])) header('Location: https://fitokrama.by/'.$res[0]);
		else echo json_encode($res);
	
		
		
		exit;
		
		
	}
	
if ($method=='refresh_belpochta_data') // тестирование функций
	{
		refresh_belpochta_data(); 
		exit;
	}





