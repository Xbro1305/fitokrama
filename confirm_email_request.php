<?php
	include 'mnn.php';
	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	
	$que = "SELECT * FROM email_confirm WHERE client_id=$client_id AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 58 second)";
	$last_record = ExecSQL($link,$que);
	if (count($last_record)>0) 
		die (json_encode(['status'=>'error', 'message'=> 'too_fast']));	
	
	
	
	$email = $_GET['email'];
	
	if ($cart['datetime_email_confirm']!=NULL) die (json_encode(['status'=>'error', 'message'=> 'yet confirmed']));	

	
	$code = random_int(10001,99999);
	$longcode = bin2hex(random_bytes(32));
	$longlink = "https://fitokrama.by/confirm_email.php?longcode=$longcode";	
	
	$text = file_get_contents("./pages/confirm_email.html");

	
	
	$text = str_replace('[code]', $code, $text);
	$text = str_replace('[link]', $longlink, $text);
		
	$rep = mail_sender($email, '🌿 Код подтверждения Fitokrama - noreply', $text);
	
	$que = "INSERT INTO email_confirm (client_id,email,code,longcode,datetime,report) VALUES ($client_id,'$email',$code,'$longcode',CURRENT_TIMESTAMP(),'$rep');";
	
	ExecSQL($link,$que);
	
	


	if (!is_null($rep))
	exit (json_encode(['status'=>'ok', 'message'=> 'Код выслан на почту.']));	
	else exit;
