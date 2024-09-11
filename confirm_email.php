<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	$code = $_GET['code'];
	$longcode = $_GET['longcode'];
	
	if (isset($longcode) AND ($longcode!=NULL) AND (strlen($longcode)>5))
		$que = "SELECT * FROM email_confirm WHERE longcode='$longcode' AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)";
	else
		$que = "SELECT * FROM email_confirm WHERE session_id=('$session_id') AND code='$code' AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)";
	
	$record = ExecSQL($link,$que);
	
	if (count($record)==0) 
		die (json_encode(['status'=>'error', 'message'=> 'Неверный код!']));	
	
	
	
	$que = "UPDATE carts SET datetime_email_confirmed=CURRENT_TIMESTAMP() WHERE session_id=('$session_id');";
	ExecSQL($link,$que);
	
	$cart = cart_by_session_id_and_username($session_id,$username);
	$cart['status']='ok';
	$cart['message']='Код подтвежден!';

	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
