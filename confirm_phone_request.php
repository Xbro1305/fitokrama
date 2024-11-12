<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	
	$que = "SELECT * FROM phone_confirm WHERE client_id=? AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 58 second)";
	$last_record = Exec_PR_SQL($link,$que,[$client_id]);
	if (count($last_record)>0) 
		die (json_encode(['status'=>'error', 'message'=> 'too_fast']));	
	
	
	$phone = $cart['client_phone'];
	
	if ($cart['datetime_phone_confirm']!=NULL) die (json_encode(['status'=>'error', 'message'=> 'yet confirmed']));	

	
	$code = random_int(10001,99999);
	
	
	if (substr($phone,0,4)=='+375' || substr($phone,0,3)=='375') 
		{
			$text = '🌿 Ваш код [code] 🌿';	
			$text = str_replace('[code]', $code, $text);
			$rep = send_sms_smstrafficby ($phone, $text);
		}
		else 
		{
			$text = 'Ваш код [code]';	
			$text = str_replace('[code]', $code, $text);
			$rep = send_sms_mysim ($phone, $text);
		}
	$que = "INSERT INTO phone_confirm (client_id,phone,code,datetime,report) VALUES (?,?,?,CURRENT_TIMESTAMP(),?);";
	Exec_PR_SQL($link,$que,[$client_id,$phone,$code,$rep]);




	exit (json_encode(['status'=>'ok', 'message'=> 'Код выслан на телефон.']));	
