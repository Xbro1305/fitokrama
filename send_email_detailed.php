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
	if (int($stage)>1) $doc = str_replace('[1_ok]', '<img src="./logos/ok_green.png" alt="" />', $doc);
						else $doc = str_replace('[1_ok]', '<img src="./logos/not_ok_green.png" alt="" />', $doc);
	if (int($stage)>2) $doc = str_replace('[2_ok]', '<img src="./logos/ok_green.png" alt="" />', $doc);
						else $doc = str_replace('[2_ok]', '<img src="./logos/not_ok_green.png" alt="" />', $doc);
	if (int($stage)>3) $doc = str_replace('[3_ok]', '<img src="./logos/ok_green.png" alt="" />', $doc);
						else $doc = str_replace('[3_ok]', '<img src="./logos/not_ok_green.png" alt="" />', $doc);
	if (int($stage)>4) $doc = str_replace('[4_ok]', '<img src="./logos/ok_green.png" alt="" />', $doc);
						else $doc = str_replace('[4_ok]', '<img src="./logos/not_ok_green.png" alt="" />', $doc);
	if (int($stage)>5) $doc = str_replace('[5_ok]', '<img src="./logos/ok_green.png" alt="" />', $doc);
						else $doc = str_replace('[5_ok]', '<img src="./logos/not_ok_green.png" alt="" />', $doc);
	if (int($stage)>6) $doc = str_replace('[6_ok]', '<img src="./logos/ok_green.png" alt="" />', $doc);
						else $doc = str_replace('[6_ok]', '<img src="./logos/not_ok_green.png" alt="" />', $doc);
						
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