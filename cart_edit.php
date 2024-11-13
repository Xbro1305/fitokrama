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
	
	$userdata = $json_in[$param];

	if (preg_match('/(--|\/\*|\*\/|;|UNION|SELECT|INSERT|UPDATE|DELETE|DROP|TRUNCATE|EXEC|XP_|SLEEP|BENCHMARK|WAITFOR|INTO OUTFILE|LOAD_FILE|OR 1=1|AND 1=1)/i', $userdata))
	{
		file_put_contents('suspicious_queries.log', date("Y-m-d H:i:s") . " HTTP_COOKIE : {$_SERVER['HTTP_COOKIE']}  HTTP_X_CLIENT_IP : {$_SERVER['HTTP_X_CLIENT_IP']} Подозрительные данные клиента: $userdata" . PHP_EOL, FILE_APPEND);
		send_warning_telegram('данные клиента с IP '.$_SERVER['HTTP_X_CLIENT_IP'].': '.$userdata);
		$userdata = '';
	}
		
	$userdata = trim($userdata); // Удаляем лишние пробелы
	$userdata = htmlspecialchars($userdata, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // Экранируем HTML-специальные символы
	if (mb_strlen($userdata) > 100) $userdata = mb_substr($userdata, 0, 100);
	
	$params[] = $userdata;


			
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
	