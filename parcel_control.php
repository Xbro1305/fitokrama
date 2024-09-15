<?php
	include_once 'mnn.php'; 
	include_once  'yandex_methods.php';
	include_once  'dpd_methods.php';
	include_once  'europost_methods.php';
	include_once  'belpost_methods.php';
	
	header('Content-Type: application/json');

	

function problem_notifications ()				//	сгенерировать предупреждения о проблемах и уведомить клиентов о поступивших посылках
{
	GLOBAL $link;
	
	// отсев незавершенных заказов для проверки и нотификаций
	$que = "SELECT 
				`orders`.`client_phone`, 
				clients.client_email, 
				orders.client_name, 
				order_point_address, 
				orders.delivery_method, 
				orders.delivery_submethod, 
				orders.id AS id, number, 
				datetime_assembly_order, 
				datetime_order_print, 
				orders.datetime_wait, 
				datetime_paid, 
				datetime_assembly, 
				datetime_sent, 
				datetime_delivery, 
				datetime_finish, 
				datetime_cancel, 
				assembly_staff_id, 
				track_number, 
				post_code 
			FROM 
				`orders` 
			JOIN 
				`clients` 
			ON 
				`clients`.`id` = `orders`.`client_id` 
			WHERE 
				`datetime_finish` IS NULL 
				AND `datetime_cancel` IS NULL 
				AND `datetime_paid` IS NOT NULL";	
	$parcels = ExecSQL($link,$que);
	$problem_text_for_staff_number = 0;
	$problem_text_for_client_email_number = 0;
	$problem_text_for_client_phone_number = 0;
	
	foreach ($parcels as $parcel)
	{
		
		$order_number 	= $parcel['number'];
		$order_id 		= $parcel['id'];
		$client_phone	= $parcel['client_phone'];	
		$client_email	= $parcel['client_email'];	
		$client_name	= $parcel['client_name'];	
		
		$problem_type = NULL;
		$problem_text_for_staff = NULL;
		$problem_text_for_client_email = NULL;
		$problem_text_for_client_sms = NULL;
		
		if ($parcel['datetime_order_print']==NULL && 
			$parcel['datetime_assembly']==NULL && 
			strtotime($parcel['datetime_assembly_order']) < strtotime('+30 minutes') && 
			strtotime($parcel['datetime_paid']) > strtotime('-10 minutes'))
			{
				// --- НЕ РАСПЕЧАТАНО ЗАДАНИЕ
				$problem_type = 'non-printed';
				$problem_text_for_staff = "🖶 Не распечатано задание на сборку заказа $order_number!";
			}
		
		if ($parcel['datetime_assembly']==NULL && 
			strtotime($parcel['datetime_assembly_order']) < strtotime('+0 minutes'))
			{
				// --- НЕ СОБРАН ЗАКАЗ
				$problem_type = 'non-assembled';
				$problem_text_for_staff = "🤺 Не собран заказ $order_number, а нормативный момент сборки истёк!";
			}
		
		if ($parcel['datetime_assembly']!=NULL && 
			$parcel['track_number']==NULL)
			{
				// --- ЗАКАЗ СОБРАН, НО НЕ ПОЛУЧИЛ ТРЕК-НОМЕР
				$problem_type = 'non-tracked';
				$problem_text_for_staff = "🆘 Заказ $order_number собран, но не имеет трек-номера для отправки!";
			}
		
		if ($parcel['datetime_assembly']!=NULL && 
			$parcel['track_number']!=NULL &&
			$parcel['datetime_sent']==NULL &&
			strtotime($parcel['datetime_assembly']) < strtotime('-480 minutes')) // !!!!!!!!!!  временное упрощение
			{
				// --- ЗАКАЗ СОБРАН, НО НЕ ОТПРАВЛЕН					// разделить на на “--- ЗАКАЗ СОБРАН, НО НЕ ПЕРЕДАН ВОДИТЕЛЮ” и “--- ЗАКАЗ ПЕРЕДАН ВОДИТЕЛЮ, НО НЕ ОТПРАВЛЕН”
				$problem_type = 'non-sent';
				$problem_text_for_staff = "⚡️ Заказ $order_number собран, но не отправлен!";
			}
				
		if ($parcel['datetime_sent']!=NULL && 
			$parcel['datetime_delivery']==NULL &&
			strtotime($parcel['datetime_wait']) < strtotime('-0 minutes'))
			{
				// --- ЗАКАЗ НАРУШАЕТ СРОКИ ПРИБЫТИЯ
				$problem_type = 'non-delivered';
				$problem_text_for_staff = "☃️ Заказ $order_number уже должен был прибыть для вручения, но не прибыл!";
			}
		
		if ($parcel['datetime_delivery']!=NULL && 
			($parcel['datetime_finish']==NULL))
			{
				// --- КЛИЕНТ ДОЛЖЕН ЗАБРАТЬ
				$problem_type = 'client-time-to-pickup';	//	тот случай, когда это не проблема, а оповещение
				$problem_text_for_client_email = "🌿 Поздравляем! Заказ $order_number прибыл и ждет вас! 🌿"; // добавить html-форму и инструкцию!
				$problem_text_for_client_sms   = "🌿 Заказ $order_number прибыл и ждет вас! 🌿";
			}
		
		if ($parcel['datetime_delivery']!=NULL && 
			($parcel['datetime_finish']==NULL) &&
			strtotime($parcel['datetime_delivery']) < strtotime('-2880 minutes') &&
			strtotime($parcel['datetime_delivery']) > strtotime('-8640 minutes'))		//	!!!!!!!     через 6 дней снимаем с контроля, сомнительно
			{
				// --- КЛИЕНТ НАРУШАЕТ СРОК ПОЛУЧЕНИЯ
				$problem_type = 'non-pickup';	
				$problem_text_for_client_email = "⭐ Поздравляем! Заказ $order_number прибыл и ждет вас! ⭐"; // добавить html-форму и инструкцию!
				$problem_text_for_client_sms   = "⭐ Заказ $order_number прибыл и ждет вас! ⭐";
			}
		
		if (is_null($problem_type)) continue;	// нет проблемы - идем дальше
		$que = "SELECT * FROM `problem_reports` WHERE order_id=$order_id AND problem_type='$problem_type' AND datetime>DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)"; // есть ли уже такое действие моложе 1 дня?
		//die($que);
		$reports = ExecSQL($link,$que);
		if (count($reports)>0)  continue;	// есть свежая отметка по этой проблеме - идем дальше
		
		$email_subject = '🌿 '.$client_name.'! Требуется ваше внимание 🌿';
		$report_sender = '';
		if (!is_null($problem_text_for_staff)) 			{ $problem_text_for_staff_number++; 		$report_sender .= '1 '; } //send_telegram_info_group ($problem_text_for_staff); } //!!!!!!!!!!!!!!! не замусориваем чат
		if (!is_null($problem_text_for_client_email)) 	{ $problem_text_for_client_email_number++; 	$report_sender .= mail_sender ($client_email, $email_subject, $problem_text_for_client_email); }
		if (!is_null($problem_text_for_client_sms)) 	{ $problem_text_for_client_phone_number++; 	$report_sender .= send_sms_smstrafficby ($client_phone,$problem_text_for_client_sms); }
		
		// делаем запись в журнале
		$que = "INSERT INTO `problem_reports` (`problem_type`,`datetime`,`problem_text_for_staff`,`problem_text_for_client_email`,`problem_text_for_client_sms`,`order_id`, `report_sender`) 
				VALUES ('$problem_type',CURRENT_TIMESTAMP(),'$problem_text_for_staff','$problem_text_for_client_email','$problem_text_for_client_sms',$order_id, '$report_sender')";
		ExecSQL($link,$que);
		
	}
	return (['status'=>'ok', 'message'=>"Сформировано $problem_text_for_staff_number сообщений сотрудникам, $problem_text_for_client_email_number писем клиентам, $problem_text_for_client_phone_number СМС клиентам."]);
}

function parcel_update ()
{
	$parcel_updated = 0;
	$parcel_checked = 0;
	GLOBAL $link;
	
	// отсев посылок, которые надо проконтролировать у почтовых провайдеров
	$que = "SELECT 
				`orders`.`client_phone`, 
				clients.client_email, 
				orders.client_name, 
				order_point_address, 
				orders.delivery_method, 
				orders.delivery_submethod, 
				orders.id AS id, number, 
				datetime_assembly_order, 
				datetime_order_print, 
				orders.datetime_wait, 
				datetime_paid, 
				datetime_assembly, 
				datetime_sent, 
				datetime_delivery, 
				datetime_finish, 
				datetime_cancel, 
				assembly_staff_id, 
				track_number, 
				post_code 
			FROM 
				`orders` 
			JOIN 
				`clients` 
			ON 
				`clients`.`id` = `orders`.`client_id` 
			WHERE 
				`datetime_finish` IS NULL 
				AND `datetime_cancel` IS NULL 
				AND `datetime_paid` IS NOT NULL
				AND `track_number` IS NOT NULL";	
	$parcels = ExecSQL($link,$que);	
	foreach ($parcels as $parcel)
	{
		$order_number 	= $parcel['number'];
		$order_id 		= $parcel['id'];
		$client_phone	= $parcel['client_phone'];	
		$client_email	= $parcel['client_email'];	
		$client_name	= $parcel['client_name'];	
		$track_number		= $parcel['track_number'];	
		$post_code			= $parcel['post_code'];	
		$delivery_method	= $parcel['delivery_method'];	
		$delivery_submethod	= $parcel['delivery_submethod'];	
		
		if ($delivery_method==1)
			$parcel_status = yandex_tracker($track_number,$post_code);
		
		if ($delivery_method==2 || $delivery_method==4 || $delivery_method==5)
			$parcel_status = dpd_tracker($track_number,$post_code);
		
		if ($delivery_method==3)
			$parcel_status = europost_tracker($track_number,$post_code);
		
		if ($delivery_method==6)
			$parcel_status = belpost_tracker($track_number,$post_code);
		
		echo "post_code=$post_code, track_number=$track_number => parcel_status=$parcel_status".PHP_EOL;
		//выполнить какие-то действия
		
	}
	return (['status'=>'ok', 'message'=>"Запрошено $parcel_checked статусов. Сформировано $parcel_updated информаций о посылках."]);	
}

	$link = firstconnect ();
	$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='problem_notifications') // вызванный webhook при совершенной оплате
{
	$res = problem_notifications();
	exit (json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	
}
	
if ($method=='parcel_update') // обновление данных о посылках
{
	$res = parcel_update();
	exit (json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	
}
