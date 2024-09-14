<?php
	include 'mnn.php';
	header('Content-Type: application/json');
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

function mail_sender($email, $subject, $text)
{
    require '../vendor/autoload.php';
	
    $mail = new PHPMailer(true);

    try {
        // Настройка сервера
        $mail->isSMTP();
        $mail->Host = 'mailbe06.hoster.by'; // Замени на нужный SMTP-сервер
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@fitokrama.by'; // Твой email
        $mail->Password = 'uVAO5g0{(z'; // Твой пароль
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Отправитель
        $mail->setFrom('noreply@fitokrama.by', 'Fitokrama');
        
        // Получатель
        $mail->addAddress($email);

        // Настройка содержания письма
        $mail->isHTML(true); 
        $mail->Subject = $subject; // Тема письма из параметра
        $mail->Body    = $text;

		
        // Отправка письма
        $mail->send();
		
        return 'Письмо отправлено mail_sender';
    } catch (Exception $e) {
        send_warning_telegram("Ошибка при отправке e-mail: {$mail->ErrorInfo}");
		return null;
    }
}


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
	
	$text = 'Введите код [code] или пройдите по ссылке https://fitokrama.by/confirm_email.php?longcode=[longcode] !';					// !!!!!!!!!!!!!!! взять из шаблона
	
	$text = str_replace('[code]', $code, $text);
	$text = str_replace('[longcode]', $longcode, $text);
		
	$rep = mail_sender($email, 'Код подтверждения Fitokrama - noreply', $text);
	
	$que = "INSERT INTO email_confirm (client_id,email,code,longcode,datetime,report) VALUES ($client_id,'$email',$code,'$longcode',CURRENT_TIMESTAMP(),'$rep');";
	
	ExecSQL($link,$que);
	
	


	if (!is_null($rep))
	exit (json_encode(['status'=>'ok', 'message'=> 'Код выслан на почту.']));	
	else exit;
