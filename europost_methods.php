<?php
	include_once  'mnn.php';
	include_once  'autocomplete_by_yandex.php';
	header('Content-Type: application/json');

function europochta_post($method, $data, $test = false) 
{
	
	Global $europost_servicenumber;
    
	$url = 'https://api.eurotorg.by:10352/Json'; 

	
	if (file_exists('europost_jwt.json')) $europost_jwt = file_get_contents('europost_jwt.json'); //!!!!!!!! контролировать тут срок?

	$dat = array();
	$dat['CRC'] = '';
	if ($method=='GetJWT') $dat['Packet']['JWT'] = 'null';	
		else $dat['Packet']['JWT'] = $europost_jwt;
	

	$dat['Packet']['MethodName'] = $method;
	$dat['Packet']['ServiceNumber'] = $europost_servicenumber;
	$dat['Packet']['Data'] = $data;
	
	if ($test) echo ''.PHP_EOL.json_encode($dat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	
	
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
        CURLOPT_POSTFIELDS => json_encode($dat),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept-Language: ru-RU'
        ),
    ));

	if ($test) echo '---   '.($url).'   ---'.PHP_EOL.PHP_EOL;
	
	$response = curl_exec($curl);
	if ($test) echo ''.PHP_EOL.json_encode(json_decode($response,TRUE), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;

		
	
	if ($response === false) 
	{
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);
		send_warning_telegram('Europochta request error: '. "cURL Error ({$errorNumber}): {$errorMessage}");
		error_log("Europochta cURL Error ({$errorNumber}): {$errorMessage}");
		die( "Europochta cURL Error ({$errorNumber}): {$errorMessage}");
	}

	curl_close($curl);
	$res = json_decode($response,TRUE);
	if (isset($res['Table'][0]['Error']))
		if (($res['Table'][0]['Error']==50101 || $res['Table'][0]['Error']==50003) && ($method!='GetJWT'))
		{
			europost_get_jwt();			//	 надо получить заново токен
			europochta_post($method, $data);	// и повторить
		}
		else return NULL; //send_warning_telegram('Europost. Ошибка запроса. '.json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			
	
	if (json_last_error() !== JSON_ERROR_NONE) {
		$jsonError = json_last_error_msg();
		send_warning_telegram('Europochta JSON decode error: ' . $jsonError);
		error_log("Europochta JSON decode error: " . $jsonError);
		die("Europochta JSON decode error: " . $jsonError);
	}	
	
	return $res;
}




function refresh_europochta_data() 			//обновление базы пунктов выдачи в Беларуси
{ 
	GLOBAL $link;

	$DeliveryTypeDir = europochta_post('Postal.DeliveryTypeDir', ['IsEnable'=>1]); // список вариантов доставки
		echo PHP_EOL.json_encode($DeliveryTypeDir, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
		
	$TypesDir = europochta_post('Postal.TypesDir', new stdClass());	// список типов отправки (только один)
		echo PHP_EOL.json_encode($TypesDir, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
		
	$WeightTypeDir = europochta_post('Postal.WeightTypeDir', ['IsEnable'=>1]); 	// список типов веса
		//echo PHP_EOL.json_encode($WeightTypeDir, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	file_put_contents('europochta_PostalWeights.json',json_encode($WeightTypeDir['Table']));	
		
	$OfficesIn = europochta_post('Postal.OfficesIn', ['TypeSender'=>2]); // список входящих ОПС 
		echo PHP_EOL.json_encode($OfficesIn, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
		
	$OfficesOut = europochta_post('Postal.OfficesOut', new stdClass()); // список исходящих офисов
		echo PHP_EOL.json_encode($OfficesOut, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	
	$europochta_points = $OfficesOut['Table'];
	if (count($europochta_points)<5)
	{
		send_warning_telegram('Внимание! Количество полученных пунктов europochta <5. Обновление аварийно завершено!');
		die (json_encode(['status'=>'error', 'message'=> 'europochta refresh calcelled']));
	}
	
	$partner_id = 3;
	$partner_prefix = 'EUR';
	$datetime_refresh_start = date('Y-m-d H:i:s');	//	запомнить момент начала обновления
	
	foreach ($europochta_points as $europochta_point)
	{
		$unique_id = $partner_prefix.'-'.$europochta_point['WarehouseId'];
		$address = $europochta_point['Address7Name'].','.$europochta_point['Address6Name'].','.$europochta_point['Address4NamePrefix'].' '.$europochta_point['Address4Name'].', '.$europochta_point['Address3Name'];
		$descript = $europochta_point['WarehouseName'];
		$lat = $europochta_point['Latitude'];
		$lng = $europochta_point['Longitude'];
		$shed = $europochta_point['Info1'];
		
		//$comment = $europochta_point['']['operation'];
		$que = "INSERT INTO `delivery_points` (
					unique_id, datetime_updated, actual_until_datetime, 
					partner_id, address, name, comment, lat, lng, coordinates
				)
				VALUES (
					?, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR), 
					?, ?, ?, ?, ?, ?, ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')'))
				)
				ON DUPLICATE KEY UPDATE
					datetime_updated = CURRENT_TIMESTAMP,
					actual_until_datetime = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR),
					partner_id = ?,
					address = ?,
					name = ?,
					comment = ?,
					lat = ?,
					lng = ?,
					lat_radians = RADIANS(?),
					lng_radians = RADIANS(?),
					coordinates = ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')'))";
					
		Exec_PR_SQL($link, $que, [
			$unique_id, $partner_id, $address, $descript, $shed, $lat, $lng, $lat, $lng, // INSERT
			$partner_id, $address, $descript, $shed, $lat, $lng, $lat, $lng, $lng, $lat  // UPDATE
		],false,true);

	}
		Exec_PR_SQL($link, "UPDATE delivery_points SET 
		lat_radians = RADIANS(lat), 
		lng_radians = RADIANS(lng), 
		coordinates = ST_GeomFromText(CONCAT('POINT(', lng, ' ', lat, ')'));", []);

	// Подсчет деактивированных точек

	$que = "SELECT COUNT(*) AS deactivated_count 
			FROM `delivery_points` 
			WHERE datetime_updated < ? 
			AND actual_until_datetime > CURRENT_TIMESTAMP 
			AND partner_id = ?";
	$deactivated = Exec_PR_SQL($link, $que, [$datetime_refresh_start, $partner_id])[0]['deactivated_count'];

	// Подсчет активированных точек
	$que = "SELECT COUNT(*) AS activated_count 
			FROM `delivery_points` 
			WHERE datetime_updated >= ? 
			AND actual_until_datetime > CURRENT_TIMESTAMP 
			AND partner_id = ?";
	$activated = Exec_PR_SQL($link, $que, [$datetime_refresh_start, $partner_id])[0]['activated_count'];
	
	$que = "UPDATE `delivery_points` SET actual_until_datetime=CURRENT_TIMESTAMP WHERE datetime_updated<? AND partner_id=?";	// под конец деактивировать необновленные
	Exec_PR_SQL($link,$que,[$datetime_refresh_start,$partner_id]);
	if ($deactivated>0) send_warning_telegram('europochta: деактивировано '.$deactivated.' пунктов.');
	exit (json_encode(['status'=>'ok', 'message'=> "Activated $activated points, Deactivated $deactivated points"])); 
}

function eur_calculator($delivery_city,$weight,$volume,$selfDelivery,$client_address) 		//	произвести расчет стоимости доставки
{
	GLOBAL $link;
	//echo (json_encode([$delivery_city,$weight,$volume,$selfDelivery,$client_address])).PHP_EOL;
	
	
	$que = "SELECT * FROM `delivery_points` WHERE partner_id=3 AND address LIKE ? LIMIT 1;";
	$eur_points = Exec_PR_SQL($link, $que, ['%' . $delivery_city . '%']);
	
	if (count($eur_points)>0) $WarehouseIdFinish = preg_replace('/\D/', '', $eur_points [0]['unique_id']);
						 else $WarehouseIdFinish = 72130020; // непонятный город - Узда 
		
	$data=array();
	$data['GoodsId'] = 836884;						// код отправления - посылка
	if ($selfDelivery)
	{
		$data['PostDeliveryTypeId'] = 1; 				// от отделения до отделения
		$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
	}
	else 
	{
		$data['PostDeliveryTypeId'] = 2; 				// от отделения до дверей
		$data['Adress1IdReciever'] = europost_address_to_id($client_address); 				//  ID адреса выдачи (именно адреса, а не пункта!)
	}
	$data['PostalWeightId'] = europost_weight_id($weight);		// дип диапазона веса
	$data['IsJuristic'] = 1; 				// 1 - юрлицо
	$data['isOversize'] = 0; 				// 
	$data['IsRelabeling'] = 0; 				// 
	$data['IsRecieverShipping'] = 0; 				// оплата за счет отправителя
	
	
	
	//if (!$selfDelivery)  die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	
	$res = europochta_post('Postal.CalculationTariff', $data,false);
	if (!isset($res['Table'][0]['PriceWithTax']))
	{
		//send_warning_telegram('Ошибка тарификации Евроопт');
		return NULL;
	}
	$calc ['price']=$res['Table'][0]['PriceWithTax'];
	$calc ['days']=3;
	//if (!$selfDelivery) die(json_encode($calc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	return($calc);


}

function europost_get_jwt () {
		GLOBAL $europost_loginname;
		GLOBAL $europost_password;
		$data['LoginName'] = $europost_loginname;
		$data['Password'] = $europost_password;
		$data['LoginNameTypeId'] = "1";
			
		$re = europochta_post('GetJWT', $data);
		
		if (isset($re['Table'][0]['JWT']))
			file_put_contents('europost_jwt.json',$re['Table'][0]['JWT']);
		else send_warning_telegram('Europost. Ошибка получения токена. '.json_encode($re, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		exit (json_encode(['result'=>'ok']));
}

function europost_get_lable($order_number,$track_number)	// получить наклейку
{				
	$data = array ();
	$data['SerialNumber'][]['SerialNumber'] = $track_number;
	$sticker_data = europochta_post('Postal.GetPDFContent', $data);
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
	

function europost_send($order,$selfdelivery) 	//	selfpickup=true - до отделения, selfpickup=false - до дверей
{
	
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);
	
	$PostalWeightId = europost_weight_id($weight);				//	взять ID веса
	$WarehouseIdFinish = $WarehouseIdFinish = (int) preg_replace('/\D/', '', $order['delivery_submethod']);
	
	
	$order_number = $order['number'];
	$client_address = $order['order_point_address'];
	$sum = $order['sum'];
	$request_id = 'FTKR_'.$order_number.'_'.strtoupper(substr(md5(rand(1,1000)), 0, 4));
	
	$data = array();
	$data['GoodsId'] = 836884;						// код отправления - посылка
	$data['PostalWeightId'] = $PostalWeightId;		// дип диапазона веса
	$data['WarehouseIdStart'] = 70130012 ;			// ID пункта выдачи - Жебрака
	if ($selfdelivery)			// вариант выдачи - до отделения
	{
		$data['PostDeliveryTypeId'] = 1;		// от отделения до отделения (1) или до дверей (2)
		$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
	}
	else			// вариант до дверей
	{
		$data['PostDeliveryTypeId'] = 2;		// от отделения до отделения (1) или до дверей (2)
		$data['Adress1IdReciever'] = europost_address_to_id($client_address); 				//  ID адреса выдачи (именно адреса, а не пункта!)
	}
	
	$data['CashOnDeliveryDeclareValueSum'] = $sum;
	$data['PhoneNumberReciever'] = str_replace('+', '', $order['client_phone']);	// телефон клиента без плюса
	
	$name_parts = explode(' ', $order['client_name'], 3);
	
	$client_surname = $name_parts[0]; // Все, что до первого пробела
	$client_name_1 = isset($name_parts[1]) ? $name_parts[1] : ' '; // 2-я часть
	$client_name_2 = isset($name_parts[2]) ? $name_parts[2] : ' '; // 3-я часть
	
	
	$data['Name1Reciever'] = $client_surname;		// имя клиента 1
	$data['Name2Reciever'] = $client_name_1;		// имя клиента 1
	$data['Name3Reciever'] = $client_name_2;		// имя клиента 2
	$data['CashOnDeliveryMoneyBackId'] = 1;			// оплачивает отправитель
	$data['InfoSender'] = "fitokrama.by. Заказ № $order_number.";	// текстовый комментарий
	$data['PostalItemExternalId'] = $request_id;
	$data['IsRecieverShipping'] = 0;
	
	
	$res = europochta_post('Postal.PutOrder', $data, false);
	
	if (!isset($res['Table'][0]['Number']))
	{
		send_warning_telegram('Европочта. Ошибка формирования заказа на отправку.'.$order_number.'  - '.json_encode($res));
		return (json_encode(['status'=>'error', 'message'=> 'EUR refresh calcelled']));
	}
	$track_number = $res['Table'][0]['Number'];
	
	$post_code = $res['Table'][0]['PostalItemId'];	// пока непонятно, что с ним делать
	
	[$label_filename,$post_code] = europost_get_lable($order_number,$track_number);
	return [$track_number,$track_number,$label_filename,$request_id];
}

function europost_tracker($track_number,$post_code)
{
	$data = array();

	$data['Number'] = $track_number;
	$res = europochta_post('Postal.Tracking', $data);
	file_put_contents('europost_tracker.txt', date('[Y-m-d H:i:s] ') . $track_number. PHP_EOL .' '.json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
	/*if (!isset($res['Table'][0]['Number']))
	{
		send_warning_telegram('Европочта. Ошибка формирования заказа на отправку.'.$order_number.'  - '.json_encode($res));
		return (json_encode(['status'=>'error', 'message'=> 'DPD refresh calcelled']));
	}
	*/
	//die (json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	$parcel_status = 'не выяснен; сохранен в _tracker.txt';
	return $parcel_status;
}

function europost_weight_id($weight)		// преобразование веса в ID
{
		$PostalWeights = file_get_contents('europochta_PostalWeights.json');
		
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
		return $PostalWeightId;
}
		
		
function europost_address_to_id ($address)	// преобразование адреса в Adress1IdReciever методами Евроопта
{
		$data=array();
//echo $address.PHP_EOL;
		$data['Text'] = $address;
//echo ('data_1   '.json_encode($data, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );
		$res = europochta_post('Addresses.Search4', $data,false);				// Получение адреса до улицы по строке
		if (!isset($res['Table'][0]['Address4Id']))	die ('Ошибка определения адреса');
		$Address4Id = $res['Table'][0]['Address4Id'];
//echo ('res_1   '.json_encode($res, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );

		$addr = json_decode(autocomplete_dadata($address),TRUE);
//echo ('addr   '.json_encode($addr, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );		

		$data=array();
		$data['Address4Id'] = $Address4Id;
		$data['Address3Name'] = $addr['suggestions'][0]['data']['house'] ?? '';
		$data['Address2Name'] = $addr['suggestions'][0]['data']['block'] ?? '';
		$data['Address1Name'] = $addr['suggestions'][0]['data']['flat'] ?? '';
		
//echo ('data_2   '.json_encode($data, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );
		$res = europochta_post('Addresses.GetAddressId', $data,false);			// Получение адреса дома (Address1Id)
//echo ('res_2   '.json_encode($res, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );		
		
		
		if (!isset($res['Table'][0]['Address1Id'])) return NULL;
		/*return NULL;
		
		die ('Ошибка определения адреса');*/
		
		return $res['Table'][0]['Address1Id'];

}	

$link = firstconnect ();
$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='home') // вариант определения цены доставки до дома
		{
			
		
		$Adress1IdReciever	= europost_address_to_id($_GET['address']);
		
		//$WarehouseIdFinish = 72130020; 
		$PostalWeightId = 20;
		
		
		$data=array();
		$data['GoodsId'] = 836884;						// код отправления - посылка
		$data['PostDeliveryTypeId'] = 2; 				// от отделения до двери!
		$data['PostalWeightId'] = $PostalWeightId;		// дип диапазона веса
		//$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
		$data['Adress1IdReciever'] = $Adress1IdReciever; 				//  ID адреса выдачи
		$data['IsJuristic'] = 1; 				// 1 - юрлицо
		$data['isOversize'] = 0; 				// 
		$data['IsRelabeling'] = 0; 				// 
		$data['IsRecieverShipping'] = 0; 				// оплата за счет отправителя
		
		echo(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);

		
		$res = europochta_post('Postal.CalculationTariff', $data);
		echo(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);

	}
	
if ($method=='test_send_home') // тестирование функций
	{
		$order = all_about_order('317782');
				[$track_number,$post_code,$label_filename,$internal_postcode] = europost_send ($order,false,true); // selfdelivery=false, т.е. доставка до двери

	}


	
if ($method=='europost_address_to_id') // тестирование функций
	{
		$address = $_GET['address'];
		$res = europost_address_to_id($address);
		echo ($res);
		exit;
		
	}
	
if ($method=='refresh_europochta_data') // тестирование функций
	{
		refresh_europochta_data(); 
		exit;
	}

if ($method=='europost_tracker') // тестирование функций
	{
		echo europost_tracker('BY080038634561',''); 
		exit;
	}




