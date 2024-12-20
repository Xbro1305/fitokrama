<?php
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode
	include_once 'mnn.php';
	include_once  'yandex_methods.php';
	include_once  'dpd_methods.php';
	include_once  'europost_methods.php';
	include_once  'send_email_detailed.php';


    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        }

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }

        exit(0);
    }

	$test = $_GET['test']=='test';

	header('Content-Type: application/json');
	//header('Content-Type: text/html; charset=UTF-8');
	//header("Access-Control-Allow-Origin: $http_origin");
	$link = firstconnect ();

	$json_in = json_decode(file_get_contents("php://input"),TRUE);


	if ($test)
			[$staff_id,$staff_name,$staff_role] = [1,'Emil Kenherli','main']; 								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! временная заглушка для
	else 	[$staff_id,$staff_name,$staff_role] = staff_auth($json_in['staff_login'],$json_in['staff_password']);

	if ($staff_role!='store' && $staff_role!='main') die (json_encode(['error'=>'No rights']));


	$order_number = $json_in['order_number'];
	$goods = $json_in['goods'];

	if ($test) $order_number = $_GET['order_number']; //$json_in['order_number'];		// !!!!!!!!!!!!!!!!!!!!!!
	if ($test) $goods = json_decode('[{"good_art":"76544","qty_as":"1"}]',TRUE); //$json_in['goods']; // !!!!!!!!!!!!!!!!!!!!!!

	$order = all_about_order($order_number,'all_info');
	$order_id=$order['id'];

	//die (json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


	if ($order['status']!='in_process_assembly') die (json_encode(['error'=>'The order is not in assembling state']));

	$que = "UPDATE `orders_goods` SET `qty_as`=0 WHERE `order_id`=?;";
	Exec_PR_SQL($link,$que,[$order_id]);
	$diff_text = '';


	foreach ($goods as $good_1)
	{
		$que = "SELECT * FROM `orders_goods` WHERE `order_id`=? AND `good_art`=?;";
		$ress = Exec_PR_SQL($link,$que,[$order_id,$good_1['good_art']]);
		if (count($ress)==0) $diff_text .= "Арт. {$good_1['good_art']} - Δ {$good_1['qty_as']} (надо 0, собрано {$good_1['qty_as']})!; ";
		$que = "UPDATE `orders_goods` SET `qty_as`=`qty_as`+? WHERE `order_id`=? AND `good_art`=?;";
		Exec_PR_SQL($link,$que,[$good_1['qty_as'],$order_id,$good_1['good_art']]);
	}
	$que = "SELECT good_art,`qty_as`-`qty` as delta,`qty_as`,`qty` FROM `orders_goods`  WHERE `order_id`=? AND `qty_as`<>`qty`;";
	$diff = Exec_PR_SQL($link,$que,[$order_id]);

	foreach ($diff as $diff_1)
		$diff_text .= "Арт. {$diff_1['good_art']} - Δ {$diff_1['delta']} (надо {$diff_1['qty']}, собрано {$diff_1['qty_as']}); ";
	if (count($diff)!=0 || $diff_text!='') die (json_encode(['error'=>'Неверная сборка заказа. '.$diff_text], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

	$message = 'Заказ $order_number собран! Распечатайте, наклейте наклейку и передайте на отправку'; // !!!!!!!!!!!!!!!!!!!!!!

	$delivery_method = $order['delivery_method'];
	$delivery_partners = Exec_PR_SQL($link,"SELECT * FROM delivery_partners WHERE id=?",[$delivery_method]);

	if (!count($delivery_partners)>0) die (json_encode(['error'=>'Не удается отметить заказ как собранный из-за проблем с формированием почтовой квитанции. '], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	$delivery_partner = $delivery_partners[0];

	// меняем статус заказа!

	$que = "UPDATE `orders` SET datetime_assembly=CURRENT_TIMESTAMP() WHERE id=?";
	//echo $que.PHP_EOL;
	// Exec_PR_SQL($link,$que,[$order_id]); //////// !!!!!!!!!!!!!!!!!!!!!!!! пока заглушка
	$que = "INSERT INTO `orders_steps` (`order_id`,`datetime`,`status`,`report`) VALUES (?,CURRENT_TIMESTAMP(),'assembled',?);";
	//echo $que.PHP_EOL;
	// Exec_PR_SQL($link,$que,[$order_id,$staff_name]); //////// !!!!!!!!!!!!!!!!!!!!!!!! пока заглушка

	if (!$test) send_telegram_info_group("🫡 Заказ $order_number собран. ВРЕМЕННО СТАТУС не меняется.");

	$address = $order['order_point_address'];
	$sending_point_address = $delivery_partner['sending_point_address'];

	$sum = $order['sum'];
	[$qty, $weight, $volume] = qty_weight_volume_by_goods($order['goods']);


	if ($delivery_method==1)	// яндекс
		[$track_number,$post_code,$label_filename,$internal_postcode] = yandex_send ($order);

	if ($delivery_method==2)	// DPD-почтомат
		[$track_number,$post_code,$label_filename,$internal_postcode] = dpd_send ($order,'PUP','ТТ');

	if ($delivery_method==3)	// Евроопт, пункт выдачи
		[$track_number,$post_code,$label_filename,$internal_postcode] = europost_send ($order,true); // selfdelivery=true, т.е. клиент заберет сам

	if ($delivery_method==4)	// DPD-доставка до двери
		[$track_number,$post_code,$label_filename,$internal_postcode] = dpd_send ($order,'NDY','ТД');

	if ($delivery_method==5)	// DPD-пункт выдачи
		[$track_number,$post_code,$label_filename,$internal_postcode] = dpd_send ($order,'NDY','ТТ');

	if ($delivery_method==6)	// Белпочта-пункт выдачи
		[$track_number,$post_code,$label_filename] = belpost_send ($address, $qty, $weight, $order_number);

	if ($delivery_method==7)	// Евроопт, курьер до двери
		[$track_number,$post_code,$label_filename,$internal_postcode] = europost_send ($order,false); // selfdelivery=false, т.е. доставка до двери

	if (is_null($internal_postcode)) $internal_postcode='FTKRM...';

	$que = "UPDATE `orders` SET track_number=?, post_code=?, internal_postcode= WHERE id=?";
	Exec_PR_SQL($link,$que,[$track_number,$post_code,$internal_postcode,$order_id]);

	$message = "Сформировано отправление $track_number. Распечатайте наклейку!";

	if (file_exists($label_filename)) $label_content = file_get_contents($label_filename);
	send_email_detailed($order['number']);	// выслать письмо 


    exit(json_encode(['message'=>$message,'label_filename'=>$label_filename,'html_for_print'=>$label_content,'post_code'=>$post_code, 'track_number'=>$track_number], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
