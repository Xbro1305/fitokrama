<?php
GLOBAL $data;
	include_once 'mnn.php';
	include_once 'delivery_methods.php'; 

	header('Content-Type: application/json');
	$json_in = json_decode(file_get_contents("php://input"),TRUE);
	
	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	
	$submethod_id = $json_in['submethod_id'];
	$delivery_methods = delivery_methods();
	
	if (($submethod_id==NULL) /*OR ($method_price==NULL) OR ($partner_id==NULL) OR ($lat==NULL) OR ($lng==NULL)*/)
		die (json_encode(['status'=>'error', 'message'=> 'no_data']));	
	
	$meth = NULL;
	foreach ($delivery_methods['methods'] as $methodIndex => $method) 
		{
			$pointIndex = array_search($submethod_id, array_column($method['points'], 'point_id'));
			if ($pointIndex !== false) $meth = $method;
		}
    
	if ($meth == NULL)
		die (json_encode(['error'=>'К сожалению, выбранный вами способ доставки сейчас недоступен. Выберите метод еще раз!'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

	$delivery_method = $meth['method_id'];
	$delivery_price = $meth['price'];

	$que = "UPDATE clients SET datetime_last=CURRENT_TIMESTAMP(), delivery_method=?, delivery_submethod=?, delivery_price=? WHERE id=?";
	Exec_PR_SQL($link,$que,[$delivery_method,$submethod_id,$delivery_price,$client_id]);

	$cart = cart_by_session_id_and_username ($session_id,$username);
	
	[$cart['delivery_logo'], $cart['delivery_text']] = info_about_delivery_by_id ($cart['delivery_method'],$cart['delivery_submethod']);
	
	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


	