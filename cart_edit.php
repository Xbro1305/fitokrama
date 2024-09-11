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

	
	$cond = 'datetime_last=CURRENT_TIMESTAMP()';
	
	foreach (array('client_name', 'client_telegram', 'client_phone', 'client_address', 'delievery_method', 'delievery_price', 'lat', 'lng' ) as $param)
		if (isset($json_in["$param"])) 
				$cond = $cond . ", $param='".$json_in["$param"]."' ";
			
		if (isset($json_in['address'])) 
			$cond = $cond . ", client_address='".$json_in["$address"]."' ";
		
// надо сделать проверку: если изменился адрес, то стереть метод доставки
	if (isset($json_in['address']) or isset($json_in['client_address']))
	{ 
		$cond = $cond . ", delivery_method=NULL,   delivery_submethod=NULL,  delivery_price=0 ";
	}

		
	
	//if (isset($json_in['client_email']))  
	//	if ($json_in['client_email']!=$cart_before['client_email']) $cond = $cond . ', datetime_email_confirmed=NULL ';
	if (isset($json_in['client_telegram']))  
		if ($json_in['client_telegram']!=$cart_before['client_telegram']) $cond = $cond . ', datetime_telegram_confirmed=NULL ';	
	if (isset($json_in['client_phone']))  
		if ($json_in['client_phone']!=$cart_before['client_phone']) $cond = $cond . ', datetime_phone_confirmed=NULL ';
	
	
	
	
	
	$que = "UPDATE clients SET $cond WHERE id=$client_id";
	ExecSQL($link,$que);
	
	$cart = cart_by_session_id_and_username($session_id,$username);
	
	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	