<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	
	$que = "SELECT * FROM phone_confirm WHERE client_id=$client_id AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 58 second)";
	$last_record = ExecSQL($link,$que);
	if (count($last_record)>0) 
		die (json_encode(['status'=>'error', 'message'=> 'too_fast']));	
	
	
	$phone = $cart['client_phone'];
	
	if ($cart['datetime_phone_confirm']!=NULL) die (json_encode(['status'=>'error', 'message'=> 'yet confirmed']));	

	
	$code = random_int(10001,99999);
	
	$text = 'Ваш код [code]';					// !!!!!!!!!!!!!!! взять из шаблона
	
	$text = str_replace('[code]', $code, $text);
	
	$rep = send_sms_smstrafficby ($phone, $text);
	
	$que = "INSERT INTO phone_confirm (client_id,phone,code,datetime,report) VALUES ($client_id,'$phone',$code,CURRENT_TIMESTAMP(),'$rep');";
	ExecSQL($link,$que);




	exit (json_encode(['status'=>'ok', 'message'=> 'Код выслан на телефон.']));	
