<?php

require_once __DIR__ . '/../varsse.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function ExecSQL($link,$query)
{
	$dataset = $link->query($query);
    if ($dataset === false)
        file_put_contents('debug_query_errors', date("Y-m-d H:i:s").'  '.$link->error.'   '.$query.PHP_EOL, FILE_APPEND);

	$answer = array();
    if (is_object($dataset)) { while (($row = $dataset->fetch_assoc()) != false) { $answer[] = $row;}}
     else { $answer = $link->insert_id; }
    return $answer;
}

function firstconnect ()
{
	GLOBAL $db_host;
	GLOBAL $db_user;
	GLOBAL $db_password;
	GLOBAL $db_name;
	//die (json_encode([$db_host,$db_user,$db_password,$db_name]));
	$link = new mysqli($db_host, $db_user, $db_password,$db_name);
	$link->set_charset('utf8mb4');
	if ($link->connect_error) {   die(json_encode(['status'=>'error', 'message'=>'Connect error ' . $link->connect_error])); }
	return $link;
}

function enterregistration ()
{
	GLOBAL $link;
	//$backtrace = debug_backtrace();
    //$callerFile = basename($backtrace[0]['file']);

	$username = jwt_check($_COOKIE['jwt']);
	$session_id =  ($_COOKIE['session_id']); // если нет JWT-токена, то надо брать session_id




	//$que = "INSERT INTO `enters` (`session_id`, `method`,`get_params`, `post_params`, `datetime`) VALUES (('$session_id'), '".$callerFile."','".json_encode($_GET)."', '".json_encode($_POST)."', CURRENT_TIMESTAMP() );";
	//ExecSQL($link,$que);
	$cart = cart_by_session_id_and_username ($session_id,$username);
	$client_id = $cart['client_id'];
	$reddottext = orders_short_info ($client_id);


    return [$session_id, $username, $cart, $client_id, $reddottext];
}

function enterregistration_admin ()
{
	[$username,$staff_level] = jwt_check_staff($_COOKIE['jwt_staff']);
	return [$username, $staff_level];
}

function cart_by_session_id_and_username ($session_id,$username)
{
	GLOBAL $link;
	$que = "SELECT
			id AS client_id,
			datetime_last,
			client_name,
			client_email,
			datetime_email_confirmed,
			client_phone,
			datetime_phone_confirmed,
			client_address,
			lat as client_lat,
			lng as client_lng,
			delivery_method,
			delivery_submethod,
			delivery_price,
			datetime_wait,
			lat, 
			lng,
			FLOOR(delivery_price) AS delivery_price_rub,
			LPAD(ROUND((delivery_price - FLOOR(delivery_price)) * 100), 2, '0') AS delivery_price_kop
		FROM
			clients
		WHERE
			client_email = '$username' OR session_id = '$session_id';
		";
	$cart = ExecSQL($link,$que)[0];

	if ($cart==NULL)
	{
		$query_add = "INSERT INTO clients (session_id, datetime_last) VALUES ('$session_id', CURRENT_TIMESTAMP());";
		ExecSQL($link, $query_add);
		$cart = ExecSQL($link,$que)[0];
	}

	$client_id = $cart['client_id'];
	if ($cart ['datetime_email_confirmed']!=NULL)
		$cart ['client_email_nochange_text'] = 'E-mail подтвержден через авторизацию google. Изменить e-mail можно только после выхода из профиля.';

	$que = "
			SELECT
			carts_goods.id,
			good_art,
			name,
			carts_goods.price,
			FLOOR(carts_goods.price) AS price_rub,
			LPAD(ROUND((carts_goods.price - FLOOR(carts_goods.price)) * 100), 2, '0') AS price_kop,
			carts_goods.old_price,
			FLOOR(carts_goods.old_price) AS old_price_rub,
			LPAD(ROUND((carts_goods.old_price - FLOOR(carts_goods.old_price)) * 100), 2, '0') AS old_price_kop,
			carts_goods.qty,
			ROUND(carts_goods.price * carts_goods.qty, 2) AS good_sum,
			FLOOR(ROUND(carts_goods.price * carts_goods.qty, 2)) AS good_sum_rub,
			LPAD(ROUND((ROUND(carts_goods.price * carts_goods.qty, 2) - FLOOR(ROUND(carts_goods.price * carts_goods.qty, 2))) * 100), 2, '0') AS good_sum_kop,
			goods.pic_name
		FROM
			carts_goods
		JOIN
			goods ON goods.art = carts_goods.good_art
		WHERE
		    client_id = $client_id
		    AND carts_goods.qty > 0
		ORDER BY
			carts_goods.id;
	";
	$cart['goods'] = ExecSQL($link,$que);
	$cart['cart_count'] = count($cart['goods']);




	$sum_goods = round(array_reduce($cart['goods'], function($carry, $item) {    return $carry + floatval($item['good_sum']);}, 0),2);
	$sum = round($sum_goods + $cart['delivery_price'],2);

	$cart['sum_goods'] = $sum_goods;
	$cart['sum'] = $sum;
	$cart['sum_rub'] = f2_rub($sum);
	$cart['sum_kop'] = f2_kop($sum);

	[ $cart['delivery_logo'], $cart['delivery_text'] ]= info_about_delivery_by_id ($cart['delivery_method'],$cart['delivery_submethod']);



	return $cart;
}

function base64UrlDecode($input)
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $addlen = 4 - $remainder;
        $input .= str_repeat('=', $addlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

function f2z($input) {
    $number = floatval($input);
    if ($number == 0)
        return '0,00';
    return number_format($number, 2, ',', '');
}

function f2_rub($input) {
    return (string) intval(floatval($input));
}


function f2_kop($input) {
    $number = floatval($input);
    $kopecks = round(($number - intval($number)) * 100);
    return str_pad($kopecks, 2, '0', STR_PAD_LEFT);
}

function send_warning_telegram($text)	//	отправить сообщение на telegram
{
		GLOBAL $config;
		GLOBAL $telegram_warning_token;
		GLOBAL $telegram_mainadmin_chat_id;
		$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://api.telegram.org/bot".$telegram_warning_token."/sendMessage?chat_id=".$telegram_mainadmin_chat_id."&text=".urlencode($text),
				CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 4,CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array( "Content-Type: application/JSON"	),
			));
			$response = curl_exec($curl);
			return $response;
}
function send_telegram_info_group ($text)	//	отправить сообщение в инфо группу telegram
{
		GLOBAL $config;
		GLOBAL $telegram_warning_token;
		GLOBAL $telegram_warning_group_chat_id;
		$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://api.telegram.org/bot".$telegram_warning_token."/sendMessage?chat_id=".$telegram_warning_group_chat_id."&text=".urlencode($text),
				CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 4,CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array( "Content-Type: application/JSON"	),
			));
			$response = curl_exec($curl);
			return $response;
}



function cut_fragment($text, $begin, $end, $replacement, &$cut_fragment = null) {
    // Найти позиции начала и конца
    $start_pos = strpos($text, $begin);
    if ($start_pos === false) {
		return $text; // Начальная метка не найдена, возвращаем оригинальный текст
    }

    $end_pos = strpos($text, $end, $start_pos + strlen($begin));
    if ($end_pos === false) {
		return $text; // Конечная метка не найдена, возвращаем оригинальный текст
    }

    // Конец фрагмента включает длину метки $end
    $end_pos += strlen($end);

    $cut_fragment = substr($text, $start_pos, $end_pos - $start_pos);

    // Возвращаем текст с заменой фрагмента
	$cutt = substr_replace($text, $replacement, $start_pos, $end_pos - $start_pos);
    return $cutt;
}


function send_sms_mysim ($phone_number, $message)	//	отправить SMS на шлюз
{
	global $sim_gateway_ip;
	global $sim_gateway_username;
	global $sim_gateway_password;
	global $sim_gateway_port;
	
	$message_encoded = urlencode($message);
	$phone_number_encoded = urlencode($phone_number);

	$url = "http://$sim_gateway_ip/cgi/WebCGI?1500101=account=$sim_gateway_username&password=$sim_gateway_password&port=$sim_gateway_port&destination=$phone_number_encoded&content=$message_encoded";

	
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);

	if (strpos($response, 'successfully') === false) 
		send_warning_telegram('Ошибка отправки SMS с собственного шлюза. '.$url.'   -  '.$response);
	
	return $response;
}

function send_sms_smstrafficby ($phone, $text)	//	отправить SMS на smstrafficby
{

	global $smstrafficby_login;
	global $smstrafficby_pass;
	global $smstrafficby_originator;

	if ($text=='') return ('');
    $ekr_text=urlencode($text);
	$urrl = "https://api.smstraffic.by/multi.php?login=$smstrafficby_login&password=$smstrafficby_pass&originator=$smstrafficby_originator&phones=".$phone."&message=".$ekr_text."&rus=5&route=sms&want_sms_ids=1";

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $urrl,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 1,
			CURLOPT_CUSTOMREQUEST => "POST",

			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded",
				/*"Content-Length: 78",*/
				"Connection: close",
			),
		));

	$response = curl_exec($curl);
	curl_close($curl);

	$response_arr = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
	if ($response_arr->result=='OK') $response_arr->success ='true';

	return json_encode($response_arr);
}

function jwt_check($token)	// проверить jwt-токен
{
	GLOBAL $jwtkey;
	if (count(explode('.', $token))!=3) return;
	$jwtArr = array_combine(['header', 'payload', 'signature'], explode('.', $token));

	$calculatedHash = hash_hmac('sha256',$jwtArr['header'] . '.' . $jwtArr['payload'], $jwtkey, true);

	if (str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($calculatedHash))!=$jwtArr['signature'])
			return;

	$ret_payload = json_decode(base64_decode( $jwtArr['payload'] ));
	$username =  $ret_payload->user_id;
	$timeexpire = $ret_payload->expire;
	if ($timeexpire<microtime(true))  return;
	return ($username);
}

function jwt_check_staff($token)	// проверить jwt-токен
{
	GLOBAL $jwtkey_staff;
	if (count(explode('.', $token))!=3) die(json_encode(['status'=>'error', 'error'=>'authorization_required']));
	$jwtArr = array_combine(['header', 'payload', 'signature'], explode('.', $token));

	$calculatedHash = hash_hmac('sha256',$jwtArr['header'] . '.' . $jwtArr['payload'], $jwtkey_staff, true);

	if (str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($calculatedHash))!=$jwtArr['signature'])
			die(json_encode(['status'=>'error', 'error'=>'authorization_required']));

	$ret_payload = json_decode(base64_decode( $jwtArr['payload'] ));
	$username =  $ret_payload->user_id;
	$timeexpire = $ret_payload->expire;
	$staff_level = $ret_payload->staff_level;
	if ($timeexpire<microtime(true))  	die(json_encode(['status'=>'error', 'error'=>'authorization_required']));
	if ($username==NULL) 				die(json_encode(['status'=>'error', 'error'=>'authorization_required']));
	if ($staff_level==NULL) 			die(json_encode(['status'=>'error', 'error'=>'authorization_required']));
	return [$username,$staff_level];
}

function jwt_create($user_id)	// выпустить jwt-токен
{
	GLOBAL $jwtkey;
	$lifetime_sec = 31536000; // время жизни 1 год
	$expire     = microtime(true)+$lifetime_sec;

	$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
	$payload = json_encode(['user_id' => $user_id, 'expire'=> $expire]);

	$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
	$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtkey, true);
	$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
	return ($base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature);

}

function jwt_create_staff($user_id,$staff_level)	// выпустить jwt-токен сотрудника
{
	GLOBAL $jwtkey_staff;
	$lifetime_sec = 604800; // время жизни 1 неделя
	$expire     = microtime(true)+$lifetime_sec;

	$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
	$payload = json_encode(['user_id' => $user_id, 'expire'=> $expire, 'staff_level'=>$staff_level]);

	$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
	$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtkey_staff, true);
	$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
	return ($base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature);

}


function actual_by_auth ($username,$reddottext,$doc,$sum_goods=0)	// доработка html-шаблона doc в зависимости от наличия авторизации $username
{
	GLOBAL $min_sum_gratis_delivery;
	if (strpos($username, 'https://t.me/') === 0 || strpos($username, '@') !== false) 		// пользователь авторизован
	{
		$doc = str_replace('account_logo.png', 'account_logo_auth.png', $doc);
		$doc = str_replace('[account_action]', 'showAccountActions()', $doc);
		$doc = cut_fragment($doc, '<!-- NONAUTHORIZED_START -->', '<!-- NONAUTHORIZED_END -->','');
	}
	else
	{
		$doc = str_replace('[account_action]', 'showAuthMetods()', $doc);
		$doc = cut_fragment($doc, '<!-- AUTHORIZED_START -->', '<!-- AUTHORIZED_END -->','');
	}

	if ($reddottext=='') $doc = cut_fragment($doc, '<!-- RED_DOT_START -->','<!-- RED_DOT_END -->','');	// красная точка на профиле
	$doc = str_replace('[reddottext]'	, $reddottext, $doc);

if ($sum_goods==0 || is_null($sum_goods))
		$doc = str_replace('[main_text]', "БЕСПЛАТНАЯ ДОСТАВКА до пункта выдачи ОТ $min_sum_gratis_delivery руб.", $doc);

		if ($sum_goods>=$min_sum_gratis_delivery) $doc = str_replace('[main_text]', "✓ БЕСПЛАТНАЯ ДОСТАВКА до пункта выдачи!", $doc);
		else $doc = str_replace('[main_text]', "Добавь товар на ".($min_sum_gratis_delivery-$sum_goods)." руб. для бесплатной доставки!", $doc);


	return $doc;

}

function info_about_delivery_by_id ($delivery_method,$delivery_submethod)
{
	GLOBAL $link;
	$delivery_partner = ExecSQL($link,"SELECT * FROM `delivery_partners` WHERE `id`='$delivery_method'");

	if (is_null($delivery_partner)) return NULL;

	$delivery_logo = $delivery_partner[0]['logo'];
	$delivery_prefix = $delivery_partner[0]['prefix'];
	$delivery_text = $delivery_partner[0]['name'];

	$que = "SELECT * FROM `delivery_points` WHERE CONCAT('$delivery_prefix','-',`unique_id`)='$delivery_submethod'";
	$sub_delivery = ExecSQL($link,$que);


	if (count($sub_delivery)>0)	$delivery_text .= ' '.$sub_delivery[0]['address'].' '.$sub_delivery[0]['name']. ' '.$sub_delivery[0]['comment'];
						else	$delivery_text .= ' Доставка до дверей';

	$delivery_text = htmlspecialchars($delivery_text, ENT_QUOTES, 'UTF-8');
	return [$delivery_logo, $delivery_text];
}

function orders_short_info($client_id = null, $selection = null) // кратко требуются ли действия клиентов по ранее сделанным заказам
{
	GLOBAL $link;
	$reddottext = '';


	if (count(ExecSQL($link,"SELECT * FROM `orders` WHERE client_id=$client_id AND datetime_delivery IS NOT NULL AND datetime_finish IS NULL AND datetime_cancel IS NOT NULL "))>0) $reddottext = 'Посылка ждёт вас!';
	if (count(ExecSQL($link,"SELECT * FROM `orders` WHERE client_id=$client_id AND datetime_paid IS NULL AND  datetime_cancel IS NOT NULL "))>0) $reddottext = 'Необходимо оплатить!';

	return $reddottext;
}



function all_about_order($order_number,$type=NULL) // подробности по заказу
{
	GLOBAL $link;

	$que = "SELECT * FROM `orders` WHERE number='$order_number' LIMIT 1";
	$orders = ExecSQL($link,$que);
	if (count($orders)==0) return NULL;
	$order = $orders[0];
		list($order['delivery_logo'], $order['delivery_text']) = info_about_delivery_by_id($order['delivery_method'],$order['delivery_submethod']);

		if ($type=='all_info')
			$que = "SELECT good_art,name,pic_name,barcode,orders_goods.price,orders_goods.qty,0 AS qty_as FROM `orders_goods`
				JOIN goods ON goods.art=orders_goods.good_art
				WHERE order_id={$order['id']};";
			else $que = "SELECT good_art,name,pic_name,orders_goods.price,orders_goods.qty FROM `orders_goods`
				JOIN goods ON goods.art=orders_goods.good_art
				WHERE order_id={$order['id']};";

		$order['goods'] = ExecSQL($link,$que);

		$order['paid'] = ExecSQL($link,"SELECT SUM(`sum`) AS paid FROM `payments` WHERE order_id={$order['id']};")[0]['paid'];
		$order['steps'] = ExecSQL($link,"SELECT datetime,status FROM `orders_steps` WHERE order_id={$order['id']} ORDER BY datetime;");
		if ($order['paid'] == NULL) $order['paid'] = 0;

		if ($order['datetime_cancel']!=NULL)
		{
			$order['status']='cancelled';
			$order['status_text_admin']='❌️ Заказ отменен.';
			$order['status_text']='❌️ Заказ отменен.';
			$order['status_color']='#bda6ae';
		}

		elseif ($order['datetime_paid']==NULL)
		{
			$order['status']='need_to_pay';
			$order['status_text_admin']='⏰ Ждем оплаты клиента';
			$order['status_text']='⚠️ Необходимо оплатить!';
			$order['status_color']='#ff0000';
		}

		elseif ($order['datetime_assembly']==NULL)
		{
			$order['status']='in_process_assembly';
			$order['status_text_admin']='🤺 на сборку!';
			$order['status_text']='⌛ Оплата принята! В процессе...';
			$order['status_color']='#bfb524';
		}

		elseif ($order['datetime_sent']==NULL)
		{
			$order['status']='waiting_for_delivery';
			$order['status_text_admin']='🚀 Необходимо отправить';
			$order['status_text']='⌛ Оплата принята! В процессе...';
			$order['status_color']='#bfb524';
		}

		elseif ($order['datetime_delivery']==NULL)
		{
			$order['status']='in_process_sending';
			$order['status_text_admin']='🚛 На стороне почты';
			$order['status_text']='🚛 В доставке';
			$order['status_color']='#bf8424';
		}

		elseif ($order['datetime_finish']==NULL)
		{
			$order['status']='waiting_for_receive';
			$order['status_text_admin']='🫡 Клиент должен забрать!';
			$order['status_text']='🫡 Посылка ждет вас!';
			$order['status_color']='#bf8424';
		}

		else
		{
			$order['status']='finished';
			$order['status_text_admin']='✅ Заказ завершен!';
			$order['status_text']='✅ Заказ завершен!';
			$order['status_color']='#b0aaa0';
		}

	return $order;
};



function generateTable($headers, $data) {
    $html = "<table border='1' style='border-collapse: collapse;'>";

    // Создание заголовков таблицы
    $html .= "<tr>";
    foreach ($headers as $header) {
        $html .= "<th style='border: 1px solid black; text-align: center; padding: 5px; min-width: 100px;'>$header</th>";
    }
    $html .= "</tr>";

    // Создание строк таблицы
    foreach ($data as $row) {
        $html .= "<tr>";
        foreach ($row as $cell) {
            // Обработка тегов для выравнивания и цветового оформления
            $style = "border: 1px solid black; text-align: center; padding: 5px; min-width: 100px;";

            // Выравнивание по левому краю
            if (strpos($cell, '{left}') !== false) {
                $style = str_replace('{left}', '', $style);
                $style .= " text-align: left;";
            }
            // Выравнивание по правому краю
            if (strpos($cell, '{right}') !== false) {
                $style = str_replace('{right}', '', $style);
                $style .= " text-align: right;";
            }
            // Красный фон
            if (strpos($cell, '{red}') !== false) {
                $style = str_replace('{red}', '', $style);
                $style .= " background-color: red; color: white;";
            }

            // Очистка кастомных тегов из значения
            $cleanCell = str_replace(['{left}', '{right}', '{red}' ], '', $cell);

            $html .= "<td style='$style'>$cleanCell</td>";
        }
        $html .= "</tr>";
    }

    $html .= "</table>";

    return $html;
}

function staff_auth($login, $password)
{
    GLOBAL $link;
    $que = "SELECT * FROM `staff` WHERE `staff_email`='$login' LIMIT 1;";
    $staffs = ExecSQL($link, $que);

    if (count($staffs) === 0) {
        die (json_encode(['error' => 'Authorization error']));
    }

    if (!password_verify($password, $staffs[0]['password_hash'])) {
        die (json_encode(['error' => 'Authorization error']));
    }

    $staff_role = $staffs[0]['role'];
    $staff_name = $staffs[0]['staff_name'];
    $staff_id = $staffs[0]['id'];
    $que = "UPDATE staff set datetime_last=CURRENT_TIMESTAMP() WHERE id=$staff_id";
    ExecSQL($link, $que);

    return [$staff_id, $staff_name, $staff_role];
}

function generateRow($tab1)
{
    $rowHtml = "<tr>";

    foreach ($tab1 as $cell) {
        $style = '';

        // Выравнивание по левому краю
        if (strpos($cell, '{left}') !== false) {
            $cell = str_replace('{left}', '', $cell);
            $style .= "text-align: left;";
        }
        // Выравнивание по правому краю
        if (strpos($cell, '{right}') !== false) {
            $cell = str_replace('{right}', '', $cell);
            $style .= "text-align: right;";
        }
        // Красный фон
        if (strpos($cell, '{red}') !== false) {
            $cell = str_replace('{red}', '', $cell);
            $style .= "background-color: red; color: white;";
        }

        // Очистка кастомных тегов из значения
        $cleanCell = str_replace(['{left}', '{right}', '{red}'], '', $cell);

        // Генерация HTML для ячейки
        $rowHtml .= "<td style='{$style}'>" . htmlspecialchars($cleanCell, ENT_QUOTES, 'UTF-8') . "</td>";
    }

    $rowHtml .= "</tr>";
    return $rowHtml;
}

function haversineGreatCircleDistance($lat1, $lng1, $lat2, $lng2, $earthRadius = 6371000) {
    // Преобразуем градусы в радианы
    $lat1 = deg2rad($lat1);
    $lng1 = deg2rad($lng1);
    $lat2 = deg2rad($lat2);
    $lng2 = deg2rad($lng2);

    // Разница координат
    $latDelta = $lat2 - $lat1;
    $lngDelta = $lng2 - $lng1;

    // Формула Хаверсина
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($lat1) * cos($lat2) *
         sin($lngDelta / 2) * sin($lngDelta / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Расстояние в метрах
    $distance = $earthRadius * $c;
	$distance = $distance /1000;
    return $distance;
}

function qty_weight_volume_by_goods($goods)			// количество, вес, объем по набору товаров
{

	$qty = round(array_reduce($goods, function($qtt, $item) {    return $qtt + floatval($item['qty']);}, 0),0);
	$weight = $qty*0.15; 					// !!!!!!!!!!!!!!!!!!!! подсчет веса примитивный!
	$volume = round($qty*0.2*0.2*0.2,2); 			// !!!!!!!!!!!!!!!!!!!! подсчет объема примитивный!

	return [$qty, $weight, $volume];

}

function mail_sender($email, $subject, $text)
{
	GLOBAL $noreply_email;
	GLOBAL $noreply_host;
	GLOBAL $noreply_password;
    require '../vendor/autoload.php';

    $mail = new PHPMailer(true);

    try {
        // Настройка сервера
        $mail->isSMTP();
        $mail->Host = $noreply_host;
        $mail->SMTPAuth = true;
        $mail->Username = $noreply_email;
        $mail->Password = $noreply_password;
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($noreply_email, 'Fitokrama.by');
        $mail->addAddress($email);


        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $text;
        $mail->AltBody = strip_tags($text);


		$mail->send();

        return 'Письмо отправлено mail_sender';
    } catch (Exception $e) {
        send_warning_telegram("Ошибка при отправке e-mail: {$mail->ErrorInfo}");
		return null;
    }
}


