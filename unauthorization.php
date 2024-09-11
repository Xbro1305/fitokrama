<?php
	include 'mnn.php'; 
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	


	setcookie('jwt', '', time() - 3600, '/', '', false, true);
	header('Content-Type: application/json');
	
	setcookie('jwt_staff', '', time() - 3600, '/', '', false, true);
	header('Content-Type: application/json');
	
	$que = "UPDATE clients SET client_email='', datetime_email_confirmed=NULL,email_confirm_detailed='unauth ' WHERE id=$client_id";
	ExecSQL($link,$que);
	
	$cart = cart_by_session_id_and_username($session_id,$username);	
	
  exit (json_encode(['status'=>'ok', 'JWT'=> $jwt, 'cart' => $cart, 'message'=>'Мы вышли из профиля']));	

?>