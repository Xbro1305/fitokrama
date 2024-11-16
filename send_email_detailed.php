<?php
	include_once 'mnn.php';

function send_email_detailed($order_number)
{
	GLOBAL $link;
	
	$order = all_about_order($order_number);
	if (is_null($order)) return ('Incorrect order number');
	
	$email = $order['client_email'];
	
	$stage = 1;															// заказ принят
	$stage = $order['paid'] 				!== null ? 2 : $stage;		// заказ оплачен
	$stage = $order['datetime_assembly'] 	!== null ? 3 : $stage;		// заказ собран
	$stage = $order['sent'] 				!== null ? 4 : $stage;		// заказ выслан
	$stage = $order['datetime_delivery'] 	!== null ? 5 : $stage;		// заказ готов к выдаче
	$stage = $order['datetime_finish'] 		!== null ? 6 : $stage;		// заказ завершен
	$stage = $order['datetime_cancel'] 		!== null ? 0 : $stage;		// заказ отменен
		
	$client_name = $order['client_name'];
	$order_address_delivery = $order['order_point_address'];
	
	
	$doc = file_get_contents("./pages/for_mail_stages.html");

	if ($stage==0) 
	{
		$doc = str_replace('[opacity_1]', 1 , $doc);	
		$doc = str_replace('Заказ принят', '❌ Заказ отменен' , $doc);	
	}
	
	$doc = str_replace('[opacity_1]', (intval($stage) >= 1 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_2]', (intval($stage) >= 2 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_3]', (intval($stage) >= 3 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_4]', (intval($stage) >= 4 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_5]', (intval($stage) >= 5 ? 1 : 0.2), $doc);
	$doc = str_replace('[opacity_6]', (intval($stage) >= 6 ? 1 : 0.2), $doc);
						
	$doc = str_replace('[text_detailed]', '', $doc);
	$doc = str_replace('[order_number]', $order_number, $doc);
	$doc = str_replace('[client_name]', $client_name, $doc);
	$doc = str_replace('[order_address_delivery]', $order_address_delivery, $doc);
	
	$subject = '🌿 [client_name]! Сообщаем вам информацию о продвижении заказа № [order_number]';
	$subject = str_replace('[order_number]', $order_number, $subject);
	$subject = str_replace('[client_name]', $client_name, $subject);
	
	mail_sender($email, $subject, $doc);					
	
	return ('sent to '.$email) ;
}