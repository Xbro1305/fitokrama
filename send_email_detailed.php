<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");
	$link = firstconnect ();



function insert_base64_encoded_image($img)
{
	$imageSize = getimagesize($img);
	$imageData = base64_encode(file_get_contents($img));
	$imageHTML = "<img src='data:{$imageSize['mime']};base64,{$imageData}' {$imageSize[3]} />";
	return $imageHTML;
}


	$email = $_GET['email'];
	$stage = $_GET['stage'];
	$order_number = $_GET['order_number'];
	
	$order = all_about_order($_GET['order_number']);
	$client_name = $order['client_name'];
	$order_address_delivery = $order['order_address_delivery'];
	
	/*
	$client_name = 'Иванов Иван Иванович' ;
	$order_address_delivery  = 'Адрес доставки';
	*/
	$img_ok = insert_base64_encoded_image('./logos/ok_green.png');
	$img_not_ok = insert_base64_encoded_image('./logos/not_ok_green.png');
	$img_finger = insert_base64_encoded_image('./logos/finger.png');

	


	
	$doc = file_get_contents("./pages/for_mail_stages.html");
	if (intval($stage)>1) 	$doc = str_replace('[img_1]', $img_ok, $doc);
							$doc = str_replace('[img_1]', $img_not_ok, $doc);
	if (intval($stage)>2) 	$doc = str_replace('[img_2]', $img_ok, $doc);
							$doc = str_replace('[img_2]', $img_not_ok, $doc);
	if (intval($stage)>3) 	$doc = str_replace('[img_3]', $img_ok, $doc);
							$doc = str_replace('[img_3]', $img_not_ok, $doc);
	if (intval($stage)>4) 	$doc = str_replace('[img_4]', $img_ok, $doc);
							$doc = str_replace('[img_4]', $img_not_ok, $doc);
	if (intval($stage)>5) 	$doc = str_replace('[img_5]', $img_ok, $doc);
							$doc = str_replace('[img_5]', $img_not_ok, $doc);
	if (intval($stage)>6) 	$doc = str_replace('[img_6]', $img_ok, $doc);
							$doc = str_replace('[img_6]', $img_not_ok, $doc);
	$doc = str_replace('[img_finger]', $img_finger, $doc);
							
	
						
	$doc = str_replace('[text_detailed]', 'Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. Детальное описание этапов заказа. ', $doc);
	$doc = str_replace('[order_number]', $order_number, $doc);
	$doc = str_replace('[client_name]', $client_name, $doc);
	$doc = str_replace('[order_address_delivery]', $order_address_delivery, $doc);
	
	$subject = '🌿 [client_name]! Сообщаем вам информацию о продвижении заказа № [order_number]';
	$subject = str_replace('[order_number]', $order_number, $subject);
	$subject = str_replace('[client_name]', $client_name, $subject);
	
	
	echo $doc;
	//mail_sender($email, $subject, $doc);					
	
	
	exit ;