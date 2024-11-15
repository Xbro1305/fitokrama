<?php
	include 'mnn.php'; 
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	


	// Удаление cookie JWT
	setcookie('jwt', '', [
		'expires' => time() - 3600,
		'path' => '/',
		'secure' => true,
		'httponly' => true,
		'samesite' => 'Strict'
	]);

	// Удаление cookie JWT для сотрудника
	setcookie('jwt_staff', '', [
		'expires' => time() - 3600,
		'path' => '/',
		'secure' => true,
		'httponly' => true,
		'samesite' => 'Strict'
	]);

	header('Content-Type: application/json');
	
	$que = "UPDATE clients SET client_email='', datetime_email_confirmed=NULL,email_confirm_detailed='unauth ' WHERE id=?";
	Exec_PR_SQL($link,$que,[$client_id]);
	
	$cart = cart_by_session_id_and_username($session_id,$username);	
	
  exit (json_encode(['status'=>'ok', 'JWT'=> $jwt, 'cart' => $cart, 'message'=>'Мы вышли из профиля']));	

?>