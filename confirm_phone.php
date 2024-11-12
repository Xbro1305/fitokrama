<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	$code = $_GET['code'];
	

	$que = "SELECT * FROM phone_confirm WHERE client_id=? AND code=? AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)";
	
	$record = Exec_PR_SQL($link,$que,[$client_id,$code]);
	
	
	if (count($record)==0) 
		die (json_encode(['status'=>'error', 'message'=> 'Неверный код!']));	
	
	
	$que = "UPDATE clients SET datetime_phone_confirmed=CURRENT_TIMESTAMP() WHERE id=?";
	Exec_PR_SQL($link,$que,[$client_id]);
	
	$cart = cart_by_session_id_and_username($session_id,$username);
	$cart['status']='ok';
	$cart['message']='Код подтвежден!';

	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
