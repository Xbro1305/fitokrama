<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	$code = $_GET['code'];
	$longcode = $_GET['longcode'];

	if (empty($code) && (empty($longcode) || strlen($longcode) <= 5)) 
		die(json_encode(['status' => 'error', 'message' => 'Отсутствуют параметры для подтверждения!']));


	if (isset($longcode) && !is_null($longcode) && strlen($longcode) > 5)
		$record = Exec_PR_SQL($link, "SELECT * FROM email_confirm WHERE longcode=? AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)"
									,[$longcode]);
	else
		$record = Exec_PR_SQL($link, "SELECT * FROM email_confirm WHERE session_id=? AND code=? AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)"
									,[$session_id,$code]);
	
	
	if (count($record)==0) 
		die (json_encode(['status'=>'error', 'message'=> 'Неверный код!']));	
	
	
	
	$que = "UPDATE carts SET datetime_email_confirmed=CURRENT_TIMESTAMP() WHERE session_id=?;";
	Exec_PR_SQL($link,$que,[$session_id]);
	
	$cart = cart_by_session_id_and_username($session_id,$username);
	$cart['status']='ok';
	$cart['message']='Код подтвежден!';

	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
