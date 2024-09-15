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
		else send_warning_telegram('Europost. Ошибка запроса. '.json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
			
	
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
	
	$patner_id = 3;
	$patner_prefix = 'EUR';
	$datetieme_refresh_start = date('Y-m-d H:i:s');	//	запомнить момент начала обновления
	
	foreach ($europochta_points as $europochta_point)
	{
		$unique_id = $patner_prefix.'-'.$europochta_point['WarehouseId'];
		$address = $europochta_point['Address7Name'].','.$europochta_point['Address6Name'].','.$europochta_point['Address4NamePrefix'].' '.$europochta_point['Address4Name'].', '.$europochta_point['Address3Name'];
		$descript = $europochta_point['WarehouseName'];
		$lat = $europochta_point['Latitude'];
		$lng = $europochta_point['Longitude'];
		$shed = $europochta_point['Info1'];
		
		//$comment = $europochta_point['']['operation'];
		$que = "INSERT INTO `delivery_points` (unique_id, datetime_updated, actual_until_datetime,partner_id,address,name,comment,lat,lng)
				VALUES ('$unique_id', CURRENT_TIMESTAMP,DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR),$patner_id,'$address','$descript', '$shed', $lat, $lng)
				ON DUPLICATE KEY UPDATE
					datetime_updated = CURRENT_TIMESTAMP,
					actual_until_datetime = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR),
					partner_id = $patner_id,
					address = '$address',
					name = '$descript',
					comment  = '$shed',
					lat = $lat,
					lng = $lng ";
					
		ExecSQL($link,$que);
	}
	$que = "SELECT * FROM `delivery_points` WHERE datetime_updated<'$datetieme_refresh_start' AND actual_until_datetime>CURRENT_TIMESTAMP AND partner_id=$patner_id";	
	$deactivated = count(ExecSQL($link,$que));
	$que = "SELECT * FROM `delivery_points` WHERE datetime_updated>='$datetieme_refresh_start' AND actual_until_datetime>CURRENT_TIMESTAMP AND partner_id=$patner_id";	
	$activated = count(ExecSQL($link,$que));
	$que = "UPDATE `delivery_points` SET actual_until_datetime=CURRENT_TIMESTAMP WHERE datetime_updated<'$datetieme_refresh_start' AND partner_id=$patner_id";	// под конец деактивировать необновленные
	ExecSQL($link,$que);
	if ($deactivated>0) send_warning_telegram('europochta: деактивировано '.$deactivated.' пунктов.');
	exit (json_encode(['status'=>'ok', 'message'=> "Activated $activated points, Deactivated $deactivated points"])); 
}

function eur_calculator($delivery_city,$weight,$volume,$selfDelivery,$index=NULL) 		//	произвести расчет стоимости доставки
{
	GLOBAL $link;
	$PostalWeightId = europost_weight_id($weight);
	
	
	$que = "SELECT * FROM `delivery_points` WHERE partner_id=3 AND address LIKE '%$delivery_city%' LIMIT 1;";
	$eur_points = ExecSQL($link,$que);
	if (count($eur_points)>0) $WarehouseIdFinish = preg_replace('/\D/', '', $eur_points [0]['unique_id']);
						 else $WarehouseIdFinish = 72130020; // непонятный город - Узда 
		
	$data=array();
	$data['GoodsId'] = 836884;						// код отправления - посылка
	$data['PostDeliveryTypeId'] = 1; 				// от отделения до отделения
	$data['PostalWeightId'] = $PostalWeightId;		// дип диапазона веса
	$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
	//$data['Adress1IdReciever'] = ; 				//  ID адреса выдачи
	$data['IsJuristic'] = 1; 				// 1 - юрлицо
	$data['isOversize'] = 0; 				// 
	$data['IsRelabeling'] = 0; 				// 
	$data['IsRecieverShipping'] = 0; 				// оплата за счет отправителя
	
	
	
	//die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	
	$res = europochta_post('Postal.CalculationTariff', $data,false);
	if (!isset($res['Table'][0]['PriceWithTax']))
	{
		send_warning_telegram('Ошибка тарификации Евроопт');
		return NULL;
	}
	$calc ['price']=$res['Table'][0]['PriceWithTax'];
	$calc ['days']=3;
	//send_warning_telegram($weight.' - '.$volume.' - '.$calc ['price']);
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
	

function europost_send($order) 
{
	
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);
	
	$PostalWeightId = europost_weight_id($weight);
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
	$data['InfoSender'] = "fitokrama.by. Заказ № $order_number.";	// текстовый комментарий
	$data['PostalItemExternalId'] = $request_id;
	$data['IsRecieverShipping'] = 1;
	
	$res = europochta_post('Postal.PutOrder', $data);
	
	if (!isset($res['Table'][0]['Number']))
	{
		send_warning_telegram('Европочта. Ошибка формирования заказа на отправку.'.$order_number.'  - '.json_encode($res));
		return (json_encode(['status'=>'error', 'message'=> 'EUR refresh calcelled']));
	}
	$track_number = $res['Table'][0]['Number'];
	
	$post_code = $res['Table'][0]['PostalItemId'];	// пока непонятно, что с ним делать
	
	[$label_filename,$post_code] = europost_get_lable($order_number,$track_number);
	return [$track_number,$track_number,$label_filename];
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
		$data['Text'] = $address;
		$res = europochta_post('Addresses.Search4', $data,false);
		if (!isset($res['Table'][0]['Address4Id']))	die ('Ошибка определения адреса');
		$Address4Id = $res['Table'][0]['Address4Id'];

		$addr = json_decode(autocomplete_dadata($address),TRUE);
		if (isset($addr['suggestions'][0]['data']['house'])) $house = $addr['suggestions'][0]['data']['house'];
														else $house = 0;
		//echo ($house);

		$data=array();
		$data['Address4Id'] = $Address4Id;
		$data['Address3Name'] = $house;
		$res = europochta_post('Addresses.GetAddressId', $data,false);
		
		if (!isset($res['Table'][0]['Address1Id'])) return NULL;
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
		$order_number = $_GET['order_number'];
		$order = all_about_order($order_number);
		[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);
		
		$PostalWeightId = europost_weight_id($weight);
		$Adress1IdReciever	= europost_address_to_id($_GET['address']);
		
		$order_number = $order['number'];
		$sum = $order['sum'];
		$request_id = 'FTKR_'.$order_number.'_'.strtoupper(substr(md5(rand(1,1000)), 0, 4));
		
		$data = array();
		$data['GoodsId'] = 836884;						// код отправления - посылка
		$data['PostDeliveryTypeId'] = 2; 				// от отделения до двери!!!!!!!!!!!!!!!!
		$data['PostalWeightId'] = $PostalWeightId;		// дип диапазона веса
		$data['WarehouseIdStart'] = 70130012 ;			// ID пункта выдачи - Жебрака
		//$data['WarehouseIdFinish'] = $WarehouseIdFinish;	// ID пункта выдачи
		$data['Adress1IdReciever'] = $Adress1IdReciever; 				//  ID адреса выдачи (именно адреса, а не пункта!)
		
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
		$data['InfoSender'] = "fitokrama.by. Заказ № $order_number.";	// текстовый комментарий
		$data['PostalItemExternalId'] = $request_id;
		$data['IsRecieverShipping'] = 1;
		
		$res = europochta_post('Postal.PutOrder', $data);
		echo(json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
		
		if (!isset($res['Table'][0]['Number']))
		{
			send_warning_telegram('Европочта. Ошибка формирования заказа на отправку.'.$order_number.'  - '.json_encode($res));
			return (json_encode(['status'=>'error', 'message'=> 'DPD refresh calcelled']));
		}
		$track_number = $res['Table'][0]['Number'];
		$post_code = $res['Table'][0]['PostalItemId'];	// пока непонятно, что с ним делать
		
		[$label_filename,$post_code] = europost_get_lable($order_number,$track_number);
	echo $label_filename.' '.$post_code;
	die;
		exit(json_encode($res,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL);
	}


	
if ($method=='sticker') // тестирование функций
	{
		$order_number = $_GET['order_number'];
		$order = all_about_order($order_number);
		$track_number = $_GET['track_number'];
		
		$res = europost_get_lable($order_number,$track_number);
		header('Content-Type: text/html; charset=UTF-8');
		header("Access-Control-Allow-Origin: $http_origin");
		if (isset($res[0])) header('Location: https://fitokrama.by/'.$res[0]);
		else echo json_encode($res);
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




