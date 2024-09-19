<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	
	$email = $_GET['email'];
	$stage = $_GET['stage'];
	$order_number = $_GET['order_number'];
	
	$order = all_about_order($_GET['order_number']);
	$client_name = $order['client_name'];
	$order_address_delivery = $order['order_address_delivery'];
	
	$doc = file_get_contents("./pages/for_mail_stages.html");
	if (intval($stage)>1) $doc = str_replace('[img_1]', './logos/ok_green.png', $doc);
						$doc = str_replace('[img_1]', './logos/not_ok_green.png', $doc);
	if (intval($stage)>2) $doc = str_replace('[img_2]', './logos/ok_green.png', $doc);
						$doc = str_replace('[img_2]', './logos/not_ok_green.png', $doc);
	if (intval($stage)>3) $doc = str_replace('[img_3]', './logos/ok_green.png', $doc);
						$doc = str_replace('[img_3]', './logos/not_ok_green.png', $doc);
	if (intval($stage)>4) $doc = str_replace('[img_4]', './logos/ok_green.png', $doc);
						$doc = str_replace('[img_4]', './logos/not_ok_green.png', $doc);
	if (intval($stage)>5) $doc = str_replace('[img_5]', './logos/ok_green.png', $doc);
						$doc = str_replace('[img_5]', './logos/not_ok_green.png', $doc);
	if (intval($stage)>6) $doc = str_replace('[img_6]', './logos/ok_green.png', $doc);
						$doc = str_replace('[img_6]', './logos/not_ok_green.png', $doc);
						
	$doc = str_replace('[text_detailed]', 'Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. ', $doc);
	$doc = str_replace('[order_number]', $order_number, $doc);
	$doc = str_replace('[client_name]', $client_name, $doc);
	$doc = str_replace('[order_address_delivery]', $order_address_delivery, $doc);
	
	$subject = '🌿 [client_name]! Сообщаем вам информацию о продвижении заказа № [order_number]';
	$subject = str_replace('[order_number]', $order_number, $subject);
	$subject = str_replace('[client_name]', $client_name, $subject);
	
	send_warning_telegram(jeon_encode([$email, $subject, $doc]));
	mail_sender($email, $subject, $doc);					
	
	
	exit ($doc);