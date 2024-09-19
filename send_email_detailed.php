<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");
	$link = firstconnect ();





	$email = $_GET['email'];
	$stage = $_GET['stage'];
	$order_number = $_GET['order_number'];
	
	$order = all_about_order($_GET['order_number']);
	$client_name = $order['client_name'];
	$order_address_delivery = $order['order_point_address'];
	
	
	$doc = file_get_contents("./pages/for_mail_stages.html");

	$doc = str_replace('[opacity_1]', (intval($stage) >= 1 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_2]', (intval($stage) >= 2 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_3]', (intval($stage) >= 3 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_4]', (intval($stage) >= 4 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_5]', (intval($stage) >= 5 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_6]', (intval($stage) >= 6 ? 1 : 0.2), $doc);
						
	$doc = str_replace('[text_detailed]', 'Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. ', $doc);
	$doc = str_replace('[order_number]', $order_number, $doc);
	$doc = str_replace('[client_name]', $client_name, $doc);
	$doc = str_replace('[order_address_delivery]', $order_address_delivery, $doc);
	
	$subject = '🌿 [client_name]! Сообщаем вам информацию о продвижении заказа № [order_number]';
	$subject = str_replace('[order_number]', $order_number, $subject);
	$subject = str_replace('[client_name]', $client_name, $subject);
	
	//echo $doc;
	mail_sender($email, $subject, $doc);					
	
	
	exit ('sent to '.$email) ;