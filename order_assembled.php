<?php
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode
	include_once 'mnn.php'; 
	include_once  'yandex_methods.php';
	include_once  'dpd_methods.php';
	include_once  'europost_methods.php';

	$test = $_GET['test']=='test';
	
	header('Content-Type: application/json');
	//header('Content-Type: text/html; charset=UTF-8');
	//header("Access-Control-Allow-Origin: $http_origin");
	$link = firstconnect ();
	
	$json_in = json_decode(file_get_contents("php://input"),TRUE);
	[$staff_id,$staff_name,$staff_role] = staff_auth($json_in['staff_login'],$json_in['staff_password']);
	
	if ($staff_role!='store' && $staff_role!='main') die (json_encode(['error'=>'No rights']));
	
	
	$order_number = $json_in['order_number'];
	$goods = $json_in['goods']; 

	if ($test) $order_number = $_GET['order_number']; //$json_in['order_number'];		// !!!!!!!!!!!!!!!!!!!!!!
	if ($test) $goods = json_decode('[{"good_art":"54985","qty_as":"3"}]',TRUE); //$json_in['goods']; // !!!!!!!!!!!!!!!!!!!!!!
	
	$order = all_about_order($order_number,'all_info');
	$order_id=$order['id'];
	
	//die (json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	
	if ($order['status']!='in_process_assembly') die (json_encode(['error'=>'The order is not in assembling state']));
	
	$que = "UPDATE `orders_goods` SET `qty_as`=0 WHERE `order_id`=$order_id;";
	ExecSQL($link,$que);
	$diff_text = '';
	
	
	foreach ($goods as $good_1)
	{
		$que = "SELECT * FROM `orders_goods` WHERE `order_id`=$order_id AND `good_art`={$good_1['good_art']};";
		$ress = ExecSQL($link,$que);
		if (count($ress)==0) $diff_text .= "Арт. {$good_1['good_art']} - Δ {$good_1['qty_as']} (надо 0, собрано {$good_1['qty_as']})!; ";
		$que = "UPDATE `orders_goods` SET `qty_as`=`qty_as`+{$good_1['qty_as']} WHERE `order_id`=$order_id AND `good_art`={$good_1['good_art']};";
		ExecSQL($link,$que);
	}
	$que = "SELECT good_art,`qty_as`-`qty` as delta,`qty_as`,`qty` FROM `orders_goods`  WHERE `order_id`=$order_id AND `qty_as`<>`qty`;";
	$diff = ExecSQL($link,$que);
	
	foreach ($diff as $diff_1)
		$diff_text .= "Арт. {$diff_1['good_art']} - Δ {$diff_1['delta']} (надо {$diff_1['qty']}, собрано {$diff_1['qty_as']}); ";
	if (count($diff)!=0 || $diff_text!='') die (json_encode(['error'=>'Неверная сборка заказа. '.$diff_text], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	$message = 'Заказ $order_number собран! Распечатайте, наклейте наклейку и передайте на отправку'; // !!!!!!!!!!!!!!!!!!!!!!
	
	$delivery_method = $order['delivery_method'];
	$delivery_partners = ExecSQL($link,"SELECT * FROM delivery_partners WHERE id=$delivery_method");
	
	if (!count($delivery_partners)>0) die (json_encode(['error'=>'Не удается отметить заказ как собранный из-за проблем с формированием почтовой квитанции. '], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
	$delivery_partner = $delivery_partners[0];
	
	// меняем статус заказа!
	
	$que = "UPDATE `orders` SET datetime_assembly=CURRENT_TIMESTAMP() WHERE id=$order_id";	
	//echo $que.PHP_EOL;
	// ExecSQL($link,$que); //////// !!!!!!!!!!!!!!!!!!!!!!!! пока заглушка
	$que = "INSERT INTO `orders_steps` (`order_id`,`datetime`,`status`,`report`) VALUES ($order_id,CURRENT_TIMESTAMP(),'assembled','$staff_name');";	
	//echo $que.PHP_EOL; 
	// ExecSQL($link,$que); //////// !!!!!!!!!!!!!!!!!!!!!!!! пока заглушка
	
	if (!$test) send_telegram_info_group("🫡 Заказ $order_number собран. ВРЕМЕННО СТАТУС не меняется.");
	
	$address = $order['order_point_address'];
	$sending_point_address = $delivery_partner['sending_point_address'];

	$sum = $order['sum'];
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);

	
	if ($delivery_method==1)	// яндекс
		[$track_number,$post_code,$label_filename] = yandex_send ($address, $qty, $weight, $order_number);
	
	if ($delivery_method==2)	// DPD-почтомат
		[$track_number,$post_code,$label_filename] = dpd_send ($order,'PUP','ТТ');
	
	if ($delivery_method==3)	// Евроопт, пункт выдачи
		[$track_number,$post_code,$label_filename] = europost_send ($order);
	
	if ($delivery_method==4)	// DPD-доставка до двери
		[$track_number,$post_code,$label_filename] = dpd_send ($order,'NDY','ТД');
		
	if ($delivery_method==5)	// DPD-пункт выдачи
		[$track_number,$post_code,$label_filename] = dpd_send ($order,'NDY','ТТ');

	if ($delivery_method==6)	// Белпочта-пункт выдачи
		[$track_number,$post_code,$label_filename] = belpost_send ($address, $qty, $weight, $order_number);		
	
	$que = "UPDATE `orders` SET track_number='$track_number', post_code='$post_code' WHERE id=$order_id";	
	//send_warning_telegram($que);
	ExecSQL($link,$que);
	$message = "Сформировано отправление $track_number. Распечатайте наклейку!";

	
    exit(json_encode(['message'=>$message,'html_for_print'=>$label_filename,'post_code'=>$post_code, 'track_number'=>$track_number], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
