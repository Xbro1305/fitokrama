<?php
	include_once  'mnn.php';
	include_once  'autocomplete_by_yandex.php';
	header('Content-Type: application/json');

function dpd_request($method, $operation, $data,$tag,$test=false) {
	//send_warning_telegram(json_encode([$method, $operation, $data,$tag,$test]));	
	GLOBAL $dpd_clientNumber;	
	GLOBAL $dpd_clientKey;	
	$url = 'https://ws.dpd.ru/services/' . $method; 

    $client = new SoapClient($url);
    $data['auth'] = array(
        'clientNumber' => $dpd_clientNumber,
        'clientKey' => $dpd_clientKey
    );
    $request[$tag] = $data;
	if ($test) send_warning_telegram('dpd18 '.json_encode($request));
	
	try 
	{
		$ret = $client->__soapCall($operation, array($request));	
		$ret = json_decode(json_encode($ret),TRUE);
		if (isset($ret['return'])) return  $ret['return'];
		// send_warning_telegram('dpd25   '.json_encode($ret));
		return $ret;
	}
	catch (SoapFault $fault) 
	{
		send_warning_telegram('dpd30  error '.json_encode($request).'    '.$fault);
		file_put_contents('dpd_errors_log.txt', json_encode($request).PHP_EOL , FILE_APPEND | LOCK_EX);
		file_put_contents('dpd_errors_log.txt', ($fault).PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);
		return $fault;
	}
	
	
	
	/*
    try {
			$ret = $client->__soapCall($operation, array($request));	
			$base64File = $ret->return->file;
			if (!empty($base64File)) {					// на случай, если сформирована этикетка (файл)
				$fileContents = ($base64File);
				$filename = "stickers/{$data['order_number']}.{$data['fileFormat']}";
				file_put_contents($filename, $fileContents);
				return ['filename'=>$filename];
			}

			$result = json_decode(json_encode($ret), true);					// если ответ - не файл
			if ($test) 
			{
				echo ('REQUEST -----'.PHP_EOL.json_encode(($request), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
				echo ('----- METHOD -----'.PHP_EOL.$url.'    '.$operation.PHP_EOL);
				echo ('RESPONSE -----'.PHP_EOL.json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
			}
			if (isset($result['return'])) return  $result['return'];
			else return $result;
		} 
		catch (SoapFault $fault) 
		{
			file_put_contents('dpd_errors_log.txt', json_encode($request).PHP_EOL , FILE_APPEND | LOCK_EX);
			file_put_contents('dpd_errors_log.txt', ($fault).PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);
			//send_warning_telegram('DPD_request error. '.json_encode($fault));
			if ($test) 
			{
				echo ('----- REQUEST -----'.PHP_EOL.json_encode(($request), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
				echo ('----- METHOD -----'.PHP_EOL.$url.'    '.$operation.PHP_EOL);
				echo ('----- RESPONSE -----'.PHP_EOL.json_encode($fault, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
			}			//echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
			return null;
		}*/
}




function refresh_dpd_data() { //обновление базы пунктов выдачи в Беларуси
	GLOBAL $link;
	$data['countryCode']='BY';
	$all_dpd_points = dpd_request('geography2?wsdl','getParcelShops',$data,'request');	//	получить список объектов
	if (count($all_dpd_points['parcelShop'])<5)
	{
		send_warning_telegram('dpd83   '.'Внимание! Количество полученных пунктов DPD <5. Обновление аварийно завершено!');
		echo (json_encode(['status'=>'error', 'message'=> 'DPD refresh calcelled']));
	}
	
	$partner_id = 2;
	$partner_prefix = 'DPD';
	$datetime_refresh_start = date('Y-m-d H:i:s');	//	запомнить момент начала обновления
	//die (json_encode($all_dpd_points['parcelShop']));
	foreach ($all_dpd_points['parcelShop'] as $dpd_point)
	{
		$parcelShopType = $dpd_point['parcelShopType']; // П или ПВП
		$unique_id = $partner_prefix.'-'.$dpd_point['code'];
		$address = $dpd_point['address']['cityName'].','.$dpd_point['address']['streetAbbr'].' '.$dpd_point['address']['street'].', '.$dpd_point['address']['houseNo'];
		$descript = $dpd_point['address']['descript'];
		$lat = $dpd_point['geoCoordinates']['latitude'];
		$lng = $dpd_point['geoCoordinates']['longitude'];
		$specific_json = json_encode($dpd_point['services']['serviceCode']);
		
		$shed = '';
		if (!isset($dpd_point['schedule'][0])) $dpd_point['schedule'][]=$dpd_point['schedule'];
		foreach ($dpd_point['schedule'] as $sh1)
		  if (isset($sh1['operation']))
			if ($sh1['operation']=='SelfDelivery')
			{
				if (!isset($sh1['timetable'][0])) $sh1['timetable'][0]=$sh1['timetable'];
				
				foreach ($sh1['timetable'] as $tt1)
				if (isset($tt1['weekDays']))
				{
					if ($tt1['weekDays']!='Пн,Вт,Ср,Чт,Пт,Сб,Вс')
						 $shed.=$tt1['weekDays'].': '.$tt1['workTime'].' ';
					else $shed.=$tt1['workTime'].' ';
				}
			}
		//$comment = $dpd_point['']['operation'];
		$que = "INSERT INTO `delivery_points` (
			unique_id, datetime_updated, actual_until_datetime, partner_id, address, name, comment, lat, lng, specific_json
		) VALUES (
			?, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR), ?, ?, ?, ?, ?, ?, ?
		) ON DUPLICATE KEY UPDATE
			datetime_updated = CURRENT_TIMESTAMP,
			actual_until_datetime = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 25 HOUR),
			partner_id = ?,
			address = ?,
			name = ?,
			comment = ?,
			lat = ?,
			lng = ?,
			specific_json = ?";

		$params = [
			$unique_id,
			$partner_id,
			$address,
			$descript,
			$shed,
			$lat,
			$lng,
			$specific_json,
			$partner_id,
			$address,
			$descript,
			$shed,
			$lat,
			$lng,
			$specific_json
		];

		Exec_PR_SQL($link, $que, $params);	
	}
	
	$que = "SELECT COUNT(*) as deactivated_count FROM `delivery_points` WHERE datetime_updated < ? AND actual_until_datetime > CURRENT_TIMESTAMP AND partner_id = ?";
	$deactivated = Exec_PR_SQL($link, $que, [$datetime_refresh_start, $partner_id])[0]['deactivated_count'];

	$que = "SELECT COUNT(*) as activated_count FROM `delivery_points` WHERE datetime_updated >= ? AND actual_until_datetime > CURRENT_TIMESTAMP AND partner_id = ?";
	$activated = Exec_PR_SQL($link, $que, [$datetime_refresh_start, $partner_id])[0]['activated_count'];

	$que = "UPDATE `delivery_points` SET actual_until_datetime=CURRENT_TIMESTAMP WHERE datetime_updated<? AND partner_id=?";	// под конец деактивировать необновленные
	Exec_PR_SQL($link,$que,[$datetime_refresh_start,$partner_id]);
	if ($deactivated>0) send_warning_telegram('DPD: деактивировано '.$deactivated.' пунктов.');
	exit (json_encode(['status'=>'ok', 'message'=> "Activated $activated points, Deactivated $deactivated points"])); 
}

function dpd_calculator($delivery_city,$weight,$volume,$selfDelivery,$index=NULL,$test=NULL) 		//	произвести расчет стоимости доставки
{ 	
	//send_warning_telegram(json_encode($index));
	//$data['pickup']['cityId']		='196058326';
	//$data['pickup']['index']		='200400';
	$data['pickup']['cityName']		='Минск';
	//$data['pickup']['regionCode']	='06';
	$data['pickup']['countryCode']	='BY';
	
	//$data['delivery']['cityId']		='543742523';
	if (!is_null($index)) 
	{
		if ($index=='211792') $index='211793';	// ошибка dadata
		if ($index=='231891') $index='231893';	// ошибка DPD
		if ($index=='231892') $index='231893';	// ошибка DPD
		$data['delivery']['index']		=$index;
		
		
	}
	
	//send_warning_telegram($index);
	
	
	
	
	$data['delivery']['cityName']	=$delivery_city;
	//$data['delivery']['regionCode']	='01';
	$data['delivery']['countryCode']='BY';
	
	$data['selfPickup']=true;
	$data['selfDelivery']=$selfDelivery;
	
	$data['weight']=$weight;
	$data['volume']=$volume;
	
	
	//$data['serviceCode']=;
	//$data['pickupDate']=;
	//echo 'Data'.PHP_EOL.json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	$calc = dpd_request('calculator2?wsdl','getServiceCost2',$data,'request',$test);	
	//echo 'Result'.PHP_EOL.json_encode($calc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	//die(json_encode($calc));
	//if (isset($calc[0]['cost']))
		
	//send_warning_telegram(json_encode($calc));
	
	if (isset($calc['serviceCode'])) return $calc[]=$calc; // если метод один, то он не массив
	if (is_array($calc))
		if (isset($calc[0]['cost']))
			return $calc;
	
	
	//die(json_encode($calc));
	
	
	
	return NULL;
}

function dpd_get_lable_createLabelFile($order_number,$dpd_number,$fileformat) {

	
	$data = array();
	$data['fileFormat'] = $fileformat;
	$data['pageSize'] = 'A5';
	$data['order'] = ['orderNum'=>$dpd_number,'parcelsNumber'=>1];
	$data['order_number'] = $order_number;
	
	$response = dpd_request ('label-print?wsdl','createLabelFile',$data,'getLabelFile',false);
	//echo json_encode($response).PHP_EOL;
	
    return $response;
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!! что-то не так ^)))))))))))))))))))
	
}

function dpd_get_lable_createParcelLabel($order_number,$track_number,$internal_postcode) {
	$data = array();
	$data['parcel'] = ['orderNum'=>$track_number,'parcelNum'=>$internal_postcode];
	$data['order_number'] = $order_number;
	$sticker_data = dpd_request ('label-print?wsdl','createParcelLabel',$data,'getLabel',false);
	if (!isset($sticker_data['order']['category']))
	{
		send_warning_telegram('DPD_getlabel '.json_encode($sticker_data));
		return NULL;
	}
	
	$sticker_data = $sticker_data['order'];
	//echo json_encode($response).PHP_EOL;
	//die(json_encode([$sticker_data['label']['parcelNum'],$sticker_data,$post_code]));
	
	
	$doc = file_get_contents('post_stickers/dpd_sticker.html'); // берем шаблон стикера
	
	foreach (array(
		'orderNum', 
		'clientOrderNum', 
		'datePickup', 
		'serviceCode', 
		'category', 
		'senderName', 
		'senderAddress', 
		'pickipTerminalCode',
		'receiverName',
		'receiverAddress',
		'deliveryTerminalCode',
		'deliveryTerminalName',
		'deliveryServiceArea',
		'isAviadepo',
		'isTerm',
		'isCashPayment'
	) as $param)
		if (isset($sticker_data[$param])) 
		$doc = str_replace("[$param]",$sticker_data[$param],$doc);
	
	$post_code = $sticker_data['label']['parcelNum'];
	
	
	$doc = str_replace("[parcelNum]",$post_code,$doc);
	
	$sticker_filename = "stickers/$order_number.html";	
	
	
	
	file_put_contents($sticker_filename,$doc);
    return [$sticker_filename,$post_code];
}

function dpd_send($order,$service_code,$service_variant) {
	
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);
	
	$sending_point_address_detailed = ['name'=>'ООО Фитокрама', 'terminalCode'=>'MSQ', 'countryName'=> 'Беларусь', 'contactFio'=>'Шиханцова Людмила Ивановна', 'contactPhone'=>'+375445975005', 'contactEmail'=>'info@fitokrama.by'];
	$client_address = $order['order_point_address'];
	/*
	$from_dadata = json_decode(autocomplete_dadata($client_address),TRUE);
	if (isset($from_dadata['suggestions'][0]['data']))
		$address_detailed_dadata = $from_dadata['suggestions'][0]['data'];
	else
	{
		die(json_encode($from_dadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		send_warning_telegram("DPD. {$order['number']} Ошибка расшифровки адреса $client_address. dpd_send не выполнен");
		die("DPD. {$order['number']} Ошибка расшифровки адреса $client_address. dpd_send не выполнен");
	}
	
	//$address_detailed['aaa'] = $address_detailed_dadata;
	$address_detailed['name'] = $order['client_name'];
	$address_detailed['countryName'] = $address_detailed_dadata['country'];
	$address_detailed['index'] = $address_detailed_dadata['postal_code'];
	
	$address_detailed['city'] = $address_detailed_dadata['city_with_type'].', '.$address_detailed_dadata['settlement_with_type'];
	$address_detailed['city'] = 'Минск' ;
	$address_detailed['street'] = $address_detailed_dadata['street'];
	$address_detailed['streetAbbr'] = $address_detailed_dadata['street_type'];
	$address_detailed['house'] = $address_detailed_dadata['house'];
	$address_detailed['houseKorpus'] = $address_detailed_dadata['block_type_full'].' '.$address_detailed_dadata['block'];
	$address_detailed['instructions'] = '!!! ПРОБНЫЙ ЗАКАЗ !!! '.$client_address;
	$address_detailed['flat'] = $address_detailed_dadata['flat'];
	
	$address_detailed['contactFio']=$order['client_name'];
	$address_detailed['contactPhone']=$order['client_phone'];
	$address_detailed['contactEmail']='';
	*/
	
	if ($service_code=='PUP' || $service_code=='NDY') 	// метод доставки требует указания кода пункта на стороне клиента
	{
		$submethod_parts = explode('-', $order['delivery_submethod']);
		$address_detailed['terminalCode'] = $submethod_parts[2];
	}

		
	
	$address_detailed['name'] = $order['client_name'];
	$address_detailed['countryName'] = 'Беларусь';
	$address_detailed['contactFio']=$order['client_name'];
	$address_detailed['contactPhone']=$order['client_phone'];
	$address_detailed['street']=$client_address;

	$address_detailed['contactEmail']='';
	
	
	
	$return_address_detailed = ['name'=>'ООО Фитокрама', 'countryName'=> 'Беларусь', 'index' => '220040', 'city' => 'Минск', 'street' => 'Беды Леонида', 'streetAbbr' => 'ул', 'house' => '2Б', 'office' => '316', 
	'instructions' => 'Склад/офис ООО Фитокрама', 	'contactFio'=>'Шиханцова Людмила Ивановна', 'contactPhone'=>'+375445975005', 'contactEmail'=>'info@fitokrama.by'];
	
	//die(json_encode($address_detailed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

	
	//$request_id = strtoupper(substr(md5($order_number), 0, 16));
	$order_number = $order['number'];
	$sum = $order['sum'];
	$request_id = 'FTKR_'.$order_number.'_'.strtoupper(substr(md5(rand(1,1000)), 0, 4));
	
	
	$data = array();
	$data['header']['datePickup'] = date('Y-m-d');
	$data['header']['senderAddress'] = $sending_point_address_detailed;
	$data['header']['pickupTimePeriod'] = '9-18';
	$data['order']['orderNumberInternal'] = $request_id;
	$data['order']['serviceCode'] = $service_code;			// PUP (доставка до почтомата или доставка до дверей) / NDY (передать в пункт выдачи)
	$data['order']['serviceVariant'] = $service_variant;	// ТД (до двери)/ ТТ (до терминала)
	$data['order']['cargoNumPack'] = 1;
	$data['order']['cargoWeight'] = $weight;
	$data['order']['cargoVolume'] = $volume;
	$data['order']['cargoValue'] = $sum;
	$data['order']['cargoCategory'] = 'косметические средства';
	$data['order']['receiverAddress'] = $address_detailed;
	$data['order']['returnAddress'] = $return_address_detailed;
	$data['order']['cargoRegistered'] = false;
	$data['order']['parcel'] = [['number'=>$request_id]];
	
	
//	echo 'create data: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
	$response = dpd_request ('order2?wsdl','createOrder',$data,'orders',false);
	if (!isset($response['orderNum']))
	{
		send_warning_telegram('DPD_send error. '.json_encode($data).json_encode($response));
		exit(json_encode(['status'=>'error', 'error'=>'Ошибка формирования отправления']));
	}
	
	$track_number = $response['orderNum'];
	//$label_filename = dpd_get_lable_createLabelFile($order_number,$track_number,'PDF')['filename'];
	
	[$label_filename,$post_code] = dpd_get_lable_createParcelLabel($order_number,$track_number,$request_id);
	
	
	return [$track_number,$post_code,$label_filename,$request_id];
}
	

$link = firstconnect ();
$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='test_label') // тестирование функций
	{
		
	$res = dpd_get_lable_createParcelLabel($_GET['order_number'],$_GET['tracknumber'],$_GET['internal_postcode']);
	
		/*header('Content-Type: text/html; charset=UTF-8');
		header("Access-Control-Allow-Origin: $http_origin");
		if (isset($res[0])) header('Location: https://fitokrama.by/'.$res[0]);
		else echo json_encode($res);*/
	echo "https://fitokrama.by/$res[0]";
		
		
	exit;
		
		
	}

if ($method=='test') // тестирование функций
	{
	$res = dpd_calculator('Минск',0.5,round(0.4*0.2*0.1,4),true,null,false);
	echo json_encode($res);
	die;
	
	
	$address = $_GET['address'];
	//$adr_apidq = autocomplete_ApiDQ($address);
	
	//echo $address.PHP_EOL;
	//echo json_encode(json_decode($adr_apidq,TRUE), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	//die;
	
	
	[$city, $lat, $lng, $index] = array_values(city_by_address_dadata($address));
	echo "address = $address".PHP_EOL;
	//echo "city = $city".PHP_EOL;
	//echo "lat,lng = $lat,$lng".PHP_EOL;
	//echo "index = $index".PHP_EOL;
	$res = dpd_calculator($city,0.5,round(0.4*0.2*0.1,4),true,$index/*,null*/,true);
	echo 'dpd_calculator'.PHP_EOL.json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	
	
	
	
	
	
	

	exit;

	}
	
	
if ($method=='test_send') // тестирование функций
	{
	$order = all_about_order(807120);
	
	$service_code = 'NDY';
	$service_variant = 'ТД';
	
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);
	
	$sending_point_address_detailed = ['name'=>'ООО Фитокрама', 'terminalCode'=>'MSQ', 'countryName'=> 'Беларусь', 'contactFio'=>'Шиханцова Людмила Ивановна', 'contactPhone'=>'+375445975005', 'contactEmail'=>'info@fitokrama.by'];
	/*
	$from_dadata = json_decode(autocomplete_dadata($client_address),TRUE);
	if (isset($from_dadata['suggestions'][0]['data']))
		$address_detailed_dadata = $from_dadata['suggestions'][0]['data'];
	else
	{
		die(json_encode($from_dadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		send_warning_telegram("DPD. {$order['number']} Ошибка расшифровки адреса $client_address. dpd_send не выполнен");
		die("DPD. {$order['number']} Ошибка расшифровки адреса $client_address. dpd_send не выполнен");
	}
	
	//$address_detailed['aaa'] = $address_detailed_dadata;
	$address_detailed['name'] = $order['client_name'];
	$address_detailed['countryName'] = $address_detailed_dadata['country'];
	$address_detailed['index'] = $address_detailed_dadata['postal_code'];
	
	$address_detailed['city'] = $address_detailed_dadata['city_with_type'].', '.$address_detailed_dadata['settlement_with_type'];
	$address_detailed['city'] = 'Минск' ;
	$address_detailed['street'] = $address_detailed_dadata['street'];
	$address_detailed['streetAbbr'] = $address_detailed_dadata['street_type'];
	$address_detailed['house'] = $address_detailed_dadata['house'];
	$address_detailed['houseKorpus'] = $address_detailed_dadata['block_type_full'].' '.$address_detailed_dadata['block'];
	$address_detailed['instructions'] = '!!! ПРОБНЫЙ ЗАКАЗ !!! '.$client_address;
	$address_detailed['flat'] = $address_detailed_dadata['flat'];
	
	$address_detailed['contactFio']=$order['client_name'];
	$address_detailed['contactPhone']=$order['client_phone'];
	$address_detailed['contactEmail']='';
	*/
	

	
	$return_address_detailed = ['name'=>'ООО Фитокрама', 'countryName'=> 'Беларусь', 'index' => '220040', 'city' => 'Минск', 'street' => 'Беды Леонида', 'streetAbbr' => 'ул', 'house' => '2Б', 'office' => '316', 
	'instructions' => 'Склад/офис ООО Фитокрама', 	'contactFio'=>'Шиханцова Людмила Ивановна', 'contactPhone'=>'+375445975005', 'contactEmail'=>'info@fitokrama.by'];
	

	
	$order_number = $order['number'];
	$sum = $order['sum'];
	$request_id = 'FTKR_'.$order_number.'_'.strtoupper(substr(md5(rand(1,1000)), 0, 4));
	
	
	$data = array();
	$data['header']['datePickup'] = date('Y-m-d');
	$data['header']['senderAddress'] = $sending_point_address_detailed;
	$data['header']['pickupTimePeriod'] = '9-18';
	$data['order']['orderNumberInternal'] = $request_id;
	$data['order']['serviceCode'] = $service_code;			// PUP (доставка до почтомата или доставка до дверей) / NDY (передать в пункт выдачи)
	$data['order']['serviceVariant'] = $service_variant;	// ТД (до двери)/ ТТ (до терминала)
	$data['order']['cargoNumPack'] = 1;
	$data['order']['cargoWeight'] = $weight;
	$data['order']['cargoVolume'] = $volume;
	$data['order']['cargoValue'] = $sum;
	$data['order']['cargoCategory'] = 'косметические средства';
	$data['order']['returnAddress'] = $return_address_detailed;
	$data['order']['cargoRegistered'] = false;
	$data['order']['parcel'] = [['number'=>$request_id]];
	
	
	//$client_address = $order['order_point_address'];
	$client_address = $_GET['client_address'];
	$address_detailed['name'] = $order['client_name'];
	$address_detailed['countryName'] = 'Беларусь';
	$address_detailed['contactFio']=$order['client_name'];
	$address_detailed['contactPhone']=$order['client_phone'];
	$address_detailed['contactEmail']='';



	
	$from_dadata = json_decode(autocomplete_dadata($client_address),TRUE);
	if (!isset($from_dadata['suggestions'][0]['data']))
		die(json_encode($from_dadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	$address_detailed_dadata = $from_dadata['suggestions'][0]['data'];
	
	$address_detailed['street']=$client_address;
	$address_detailed['cityName']=$address_detailed_dadata['settlement_with_type'];
	$address_detailed['index']=$address_detailed_dadata['postal_code'];
	
	
	
	$data['order']['receiverAddress'] = $address_detailed;
	
	
	$response = dpd_request ('order2?wsdl','createOrder',$data,'orders',false);
	echo 'order[receiverAddress]   '.json_encode($data['order']['receiverAddress'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	echo 'dpd_send'.PHP_EOL.json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
	
	
	
	
	
	exit;

	}	
if ($method=='refresh_dpd_data') // тестирование функций
	{
		refresh_dpd_data(); //так мы запишем номер города из DPD в нашу переменную.
		exit;
	}



