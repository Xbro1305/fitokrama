<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	$cart_before = $cart;
	
	$json_in = json_decode(file_get_contents("php://input"),TRUE);
	if (isset($_GET['address'])) $json_in['client_address']=$_GET['address'];
	if (isset($_GET['lat'])) $json_in['lat']=$_GET['lat'];
	if (isset($_GET['lng'])) $json_in['lng']=$_GET['lng'];

	
	$cond = 'datetime_last = CURRENT_TIMESTAMP()';
	$params = [];

	foreach (['client_name', 'client_telegram', 'client_phone', 'client_address', 'delivery_method', 'delivery_price', 'lat', 'lng'] as $param) {
		if (isset($json_in[$param])) {
			$cond .= ", $param = ?";
			$params[] = $json_in[$param];
		}
	}

	// Проверка изменения адреса и сброс метода доставки
	if (isset($json_in['address']) || isset($json_in['client_address'])) {
		$cond .= ", delivery_method = NULL, delivery_submethod = NULL, delivery_price = 0";
	}

	// Проверка изменения Telegram
	if (isset($json_in['client_telegram']) && $json_in['client_telegram'] !== $cart_before['client_telegram']) {
		$cond .= ", datetime_telegram_confirmed = NULL";
	}

	// Проверка изменения телефона
	if (isset($json_in['client_phone']) && $json_in['client_phone'] !== $cart_before['client_phone']) {
		$cond .= ", datetime_phone_confirmed = NULL";
	}

	// Добавляем условие WHERE
	$cond .= " WHERE id = ?";
	$params[] = $client_id;

	// Формируем финальный запрос
	$que = "UPDATE clients SET $cond";
	Exec_PR_SQL($link, $que, $params);

	
	$cart = cart_by_session_id_and_username($session_id,$username);
	
	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	