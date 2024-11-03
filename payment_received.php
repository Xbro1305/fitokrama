<?php
	include_once  'mnn.php';
	include_once  'alfa_methods.php';
	include_once  'epos_methods.php';
	include_once  'grosh_methods.php';
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode

	
	header('Content-Type: application/json');

	$data = json_decode(file_get_contents("php://input"),TRUE);
	$link = firstconnect ();




$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='epos_incoming') // вызванный webhook при совершенной оплате
	{
		file_put_contents('epos_log.txt', json_encode($data, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );
		
		if (!isset($data['claimId'])) exit(json_encode(['status'=>'ok', 'message'=>'No_data']));	

		$invoice_number = $data['claimId'];
		$payment_id = $data['id'];
		$sum = $data['amount']['amt'];
		$eripid = $data['memorialSlip']['tranEripId'];
		$eposid = $data['memorialSlip']['transEposId'];
		$invoice_id = $data['parentId'];
		
		check_payment_by_order (NULL,$invoice_id);
		
		exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	}

if ($method=='erip_incoming'||$method=='erip_incoming~kenherli@gmail.com' ) // вызванный webhook 
{
	GLOBAL $data;	
	$invoiceid = $data['id'];
	if (is_null($invoiceid) || ($invoiceid=='')) exit;

	check_payment_by_order (NULL,$invoiceid);
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	

}

if ($method=='alfa_incoming_ok' || $method=='alfa_incoming_no') // вызванный webhook при совершенной или сломавшейся оплате
{
	if (!isset($_GET['orderId'])) exit(json_encode(['status'=>'ok', 'message'=>'No_data']));	
	$alfa_orderId = $_GET['orderId'];

	check_payment_by_order (NULL,$alfa_orderId);
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
}

function check_payment_by_order ($order,$payid)	// проверяет статус заказа
{
	GLOBAL $link;
	$sum = 0;
	
	if (!is_null($payid))
	{
		$orders = ExecSQL($link,"SELECT * FROM orders WHERE epos_id='$payid' OR hutki_billId='$payid' OR alfa_orderId='$payid'");
		if (count($orders)==0) exit(json_encode(['status'=>'error', 'error'=>'Incorrect order_number']));	
		$order = $orders[0];
		if ($order['epos_id']==$payid) 		[$payment_method,$payment_report,$sum] = epos_check($order['epos_id']);
		if ($order['hutki_billId']==$payid) [$payment_method,$payment_report,$sum] = erip_check($order['hutki_billId']);
		if ($order['alfa_orderId']==$payid) [$payment_method,$payment_report,$sum] = alfa_check($order['alfa_orderId']);
	}
	
	if (!is_null($order))
	{
		if ($sum==0) [$payment_method,$payment_report,$sum] = epos_check($order['epos_id']);
		if ($sum==0) [$payment_method,$payment_report,$sum] = erip_check($order['hutki_billId']);
		if ($sum==0) [$payment_method,$payment_report,$sum] = alfa_check($order['alfa_orderId']); 
	}
	
	if ($sum==0) return (NULL);	// оплата не зафиксирована
	
	if ($payment_method!='epos') epos_kill($order['epos_id']);
	if ($payment_method!='erip') erip_kill($order['hutki_billId']);
	//if ($payment_method!='alfa') alfa_kill($order['alfa_orderId']);
	
	$payment_records = ExecSQL($link,"SELECT * FROM payments WHERE order_id={$order['id']} AND payment_method='$payment_method'");
	
	if (count($payment_records)==0) // нет записи об оплате
	{
		$que = "INSERT INTO payments (order_id,sum,datetime,payment_method,payment_report)
				VALUES ({$order['id']},$sum,CURRENT_TIMESTAMP,'$payment_method','$payment_report')";
		ExecSQL($link,$que);
	}
	if ($order['datetime_paid']==NULL)
	{
		$paid_amount = ExecSQL($link,"SELECT SUM(`sum`) AS paid FROM `payments` WHERE order_id={$order['id']}")[0]['paid'];
		if ($paid_amount>=$order['sum']) 
		{
			$que = "UPDATE orders SET datetime_paid = CURRENT_TIMESTAMP WHERE id={$order['id']}";		
			ExecSQL($link,$que);
			// !!!!!!!!!!!!!!!!!! Заказ оплачен. Какие-то еще действия?
		}
	send_warning_telegram("check_payment_by_order Видим оплату по заказу {$order['number']} в сумме $sum методом $payment_method. Отмечаем заказ как оплаченный.");	
		
	}
		
	return ($sum);
	
}

if ($method=='check_payment_by_order') // проверить оплату по соотв. заказу
{
	$order_number = $_GET['order_number'];
	if ($order_number=='' or is_null($order_number)) exit(json_encode(['status'=>'error', 'error'=>'Incorrect order_number']));	
	$orders = ExecSQL($link,"SELECT * FROM orders WHERE number=$order_number");
	if (count($orders)==0) exit(json_encode(['status'=>'error', 'error'=>'Incorrect order_number']));	
		
	$sum = check_payment_by_order ($orders[0],NULL);
	$order = all_about_order($order_number);
	exit(json_encode(['status'=>'ok', 'paid_amount'=>$sum, 'order'=>$order]));	
}

if ($method=='check_payment_by_id') // проверить оплату по соотв. заказу
{
	$payid = $_GET['payid'];
	
	$sum = check_payment_by_order (NULL,$payid);
	
	exit(json_encode(['status'=>'ok', 'paid_amount'=>$sum]));	
}


if ($method=='check_orders_not_paid') // вызываемый webhook по CRON для действий с неоплаченными заказами
{
	// взять неоплаченные >30 минут заказы с отсутствующим NOT_PAID_EMAIL и отправить письма
	$que = "SELECT * FROM orders o WHERE 
    o.datetime_cancel IS NULL 
    AND o.datetime_paid IS NULL
	AND o.datetime_create < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)
	AND o.datetime_create > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 120 MINUTE)
    AND NOT EXISTS ( SELECT 1 
        FROM messages m 
        WHERE m.order_number = o.number 
          AND m.type = 'NOT_PAID_EMAIL' ) ";
		  
	$orders_30 = ExecSQL($link,$que);
	
	foreach ($orders_30 as $order)
	{
		
		$order_number = $order['number'];
		$doc = file_get_contents("./pages/for_mail_payment_push.html");
		
		$finaldatetime = (new DateTime($order['datetime_create']))->modify('+2 hours')->format('H:i d.m.Y');
							
		$doc = str_replace('[order_number]', $order_number, $doc);
		$doc = str_replace('[finaldatetime]', $finaldatetime, $doc);
		
		$doc = str_replace('[client_name]', $client_name, $doc);
		$doc = str_replace('[order_address_delivery]', $order_address_delivery, $doc);
		
		$paylink = $order['epos_link'];
		
		$paycode = "В дереве ЕРИП выберите услугу «E-POS - оплата товаров и услуг» и введите код: $epos_client_number".$order['number'];
		
		//$payalfalink = $order['alfa_url'];
		
		ob_start();
		$hutki_url = $order['hutki_url'];
		$qrFilePath = "qrcodes/$order_number.png";
		QRcode::png($hutki_url, $qrFilePath, QR_ECLEVEL_Q, 4);
		$imageUrl = "https://fitokrama.by/qrcodes/$order_number.png";
		$doc = str_replace('[payqrpicture]', $imageUrl, $doc);
		$doc = str_replace('[hutki_link]', $hutki_url, $doc);

		$doc = str_replace('[paylink]', $paylink, $doc);
		$doc = str_replace('[epos_full_text]', $paycode, $doc);
		
		
		$doc = str_replace('[link]', "https://fitokrama.by/order_page.php?order=$order_number", $doc);
		$doc_sl = addslashes($doc);
	
		$que = "INSERT INTO messages (
			order_number, 
			client_id, 
			datetime, 
			type, 
			email, 
			text 
		) VALUES (
			'{$order['number']}',
			'{$order['client_id']}',
			CURRENT_TIMESTAMP,
			'NOT_PAID_EMAIL',
			'{$order['client_email']}',
			'$doc_sl' );
		";
		
		$ins_id = ExecSQL($link,$que);
		$rep = mail_sender($order['client_email'], "⚡️ Заказ не оплачен! ☘", $doc);		
		$que = "UPDATE messages SET datetime_sent=CURRENT_TIMESTAMP, report='$rep' WHERE id=$ins_id;";
		ExecSQL($link,$que);		

	}
	
		
	// взять неоплаченные >90 минут заказы с отсутствующим NOT_PAID_PHONE и отправить СМС
	
	$que = "SELECT * FROM orders o WHERE 
    o.datetime_cancel IS NULL 
    AND o.datetime_paid IS NULL
	AND o.datetime_create < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 90 MINUTE)
	AND o.datetime_create > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 120 MINUTE)
    AND NOT EXISTS ( SELECT 1 
        FROM messages m 
        WHERE m.order_number = o.number 
          AND m.type = 'NOT_PAID_PHONE' ) ";
		  
	$orders_90 = ExecSQL($link,$que);
	
	foreach ($orders_90 as $order)
	{
		
	$order_number = $order['number'];
	$finaldatetime = (new DateTime($order['datetime_create']))->modify('+2 hours')->format('H:i');

	$doc = "⚡️Заказ $order_number не оплачен! Оплатите, иначе в $finaldatetime будет расформирован. {$order['epos_link']}";
		$doc_sl = addslashes($doc);
		$que = "INSERT INTO messages (
			order_number, 
			client_id, 
			datetime, 
			type, 
			phone, 
			text 
		) VALUES (
			'{$order['number']}',
			'{$order['client_id']}',
			CURRENT_TIMESTAMP,
			'NOT_PAID_PHONE',
			'{$order['client_phone']}',
			'$doc_sl' );
		";
		
		$ins_id = ExecSQL($link,$que);
		$rep = send_sms_smstrafficby ($order['client_phone'], $doc);
		$que = "UPDATE messages SET datetime_sent=CURRENT_TIMESTAMP, report='$rep' WHERE id=$ins_id;";
		ExecSQL($link,$que);
	}	
	
	
	// взять неоплаченные >120 минут заказы, расформировать обратно в корзину, и если отсутствует NOT_PAID_ORDER_CANCEL, выслать письмо
	$que = "SELECT * FROM orders o WHERE 
    o.datetime_cancel IS NULL 
    AND o.datetime_paid IS NULL
	AND o.datetime_create < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 120 MINUTE) ";
		  
	$orders_120 = ExecSQL($link,$que);
	
	foreach ($orders_120 as $order)
	{
		$order_number = $order['number'];
		$doc = file_get_contents("./pages/for_mail_payment_deadline_missed.html");
		
		$doc = str_replace('[order_number]', $order_number, $doc);
		$doc = str_replace('[link]', "https://fitokrama.by/cart_page.php", $doc);
		$doc_sl = addslashes($doc);
	
		$que = "INSERT INTO messages (
			order_number, 
			client_id, 
			datetime, 
			type, 
			email, 
			text 
		) VALUES (
			'{$order['number']}',
			'{$order['client_id']}',
			CURRENT_TIMESTAMP,
			'NOT_PAID_ORDER_CANCEL',
			'{$order['client_email']}',
			'$doc_sl' );
		";
		
		$ins_id = ExecSQL($link,$que);
		$rep = mail_sender($order['client_email'], "⚡️ Заказ не оплачен! ☘", $doc);		
		$que = "UPDATE messages SET datetime_sent=CURRENT_TIMESTAMP, report='$rep' WHERE id=$ins_id;";
		ExecSQL($link,$que);

		// а теперь расформировать заказ!
		$client_id = $order['client_id'];
		$order = all_about_order($order_number);
		
		
		$goods_client =  ExecSQL($link,"SELECT * FROM `carts_goods` WHERE `client_id`=$client_id");
		foreach ($order['goods'] as $good_1)
		{
			$this_good_now = ExecSQL($link,"SELECT art,price,price_old FROM goods WHERE art={$good_1['good_art']};");
			if (count($this_good_now)==0) continue;
			$price 		= $this_good_now[0]['price'];
			$price_old 	= $this_good_now[0]['price_old'];
		
		
			$goods_client_index = array_search($good_1['good_art'],array_column($goods_client,'good_art'));
			if ($goods_client_index !== false)	 
			{
				$qty_from_cart = $goods_client[$goods_client_index]['qty'];
				$qty = MAX ($good_1['qty'],$qty_from_cart);
				$que = "UPDATE `carts_goods` SET `price`=$price, `old_price`=$price_old, `qty`=$qty
				WHERE `client_id`=$client_id AND `good_art`={$good_1['good_art']};";
			}
			else
			{
				$qty = $good_1['qty'];
				$que = "INSERT INTO `carts_goods` (`client_id`, `good_art`, `price`, `old_price`, `qty`) 
					VALUES ($client_id,{$good_1['good_art']},$price,$price_old,$qty);";
			}				
			
			ExecSQL($link,$que);
			
		}

		$que = "UPDATE orders SET datetime_cancel=CURRENT_TIMESTAMP WHERE number=$order_number ";
		ExecSQL($link,$que);

	}
	
	
	
	
	// сократить срок платежных ссылок до 2 часов
	
	
	
	
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
}
