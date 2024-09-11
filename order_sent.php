<?php
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode
	include 'mnn.php';

	header('Content-Type: application/json');
	//header('Content-Type: text/html; charset=UTF-8');
	//header("Access-Control-Allow-Origin: $http_origin");
	$link = firstconnect ();
	
	$json_in = json_decode(file_get_contents("php://input"),TRUE);
	[$staff_id,$staff_name,$staff_role] = staff_auth($json_in['staff_login'],$json_in['staff_password']);
	
	if ($staff_role!='postman' && $staff_role!='main') die (json_encode(['error'=>'No rights']));
	
	$qrcode = $json_in['qrcode'];
	
	$lat = $json_in['lat'];
	$lng = $json_in['lng'];
	
	//$qrcode = '31644323431';
	//$lat = 50;
	//$lng = 20;
	
	if (is_null($lng) OR $lng==0 OR $lng=='' OR is_null($lat) OR $lat==0 OR $lat=='' )	die (json_encode(['error'=>'Необходимы координаты!'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	$que = "SELECT * FROM orders WHERE post_code='$qrcode'";
	 
	$orders = ExecSQL($link,$que);
	if (strlen($qrcode)<5 OR count($orders)==0) die (json_encode(['error'=>'Это не почтовый QR-код!'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	if (count($orders)>1) die (json_encode(['error'=>'Код не укальный!'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	$order = all_about_order($orders[0]['number']);
	$order_id = $order['id'];
	 
	if ($order['status']!='waiting_for_delivery') die (json_encode(['error'=>'Заказ не в том состоянии, чтобы быть отправленным'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	$maplink = "https://yandex.ru/maps/?whatshere[point]=$lng,$lat&whatshere[zoom]=17";
	
	$delivery_partners = ExecSQL($link,"SELECT * FROM delivery_partners WHERE id={$order['delivery_method']}");
	if (!count($delivery_partners)>0) die (json_encode(['error'=>'Не удается установить способ почтового отправления. '], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 

	$delivery_partner = $delivery_partners[0];
	

	$sending_point_lat = $delivery_partner['sending_point_lat'];
	$sending_point_lng = $delivery_partner['sending_point_lng'];
	
	$dist = round(haversineGreatCircleDistance($lat, $lng, $sending_point_lat, $sending_point_lng));
	


	// меняем статус заказа!
	
	$que = "UPDATE `orders` SET datetime_sent=CURRENT_TIMESTAMP() WHERE id=$order_id";	
	//echo $que.PHP_EOL;
	// ExecSQL($link,$que); //////// !!!!!!!!!!!!!!!!!!!!!!!! пока заглушка
	$que = "INSERT INTO `orders_steps` (`order_id`,`datetime`,`status`,`report`) VALUES ($order_id,CURRENT_TIMESTAMP(),'sent','$staff_name: $maplink');";	
	// ExecSQL($link,$que); //////// !!!!!!!!!!!!!!!!!!!!!!!! пока заглушка
	
	send_telegram_info_group("🫡 Заказ $order_number отправлен с расстояния $dist м. ВРЕМЕННО СТАТУС не меняется. Точка отправки: $maplink");	
	
	
	if ($dist>1) 
		 $message = "Отправлено. Расстояние от пункта отправки $dist км, это странно.";
	else $message = 'Отправлено!';

    exit(json_encode(['message'=>$message], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
