<?php
	include_once  'mnn.php';
	include_once  'alfa_methods.php';
	include_once  'epos_methods.php';
	
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
		
		$pay_check = epos_pay_check($payment_id);
		if (!$pay_check)
		{
			send_warning_telegram('EPOS. Странная ситуация с неоплаченным заказом и вебхуком об оплате. Заказ '.$invoice_number);
			exit(json_encode(['status'=>'ok', 'message'=>'No_payment']));
		}

		
		$orders = ExecSQL($link,"SELECT * FROM orders WHERE epos_id='$invoice_id'");
		
		if (count($orders)==0)
		{
			send_warning_telegram("EPOS Не найдена оплата в журнале invoice_number=$invoice_number payment_id=$payment_id sum=$sum");
		}
		else
		{
			$order_id = $orders[0]['id'];
			$order_number = $orders[0]['number'];
			
		
			
			$que = "INSERT INTO payments (order_id,sum,datetime,payment_method,payment_report)
					VALUES ('$order_id',$sum,CURRENT_TIMESTAMP,'epos','EPOS $invoice_number - $eposid - $eripid')";
			ExecSQL($link,$que);
			send_warning_telegram("EPOS Зарегистрирована оплата по заказу $order_number в сумме $sum руб.");
		}
		$paid = ExecSQL($link,"SELECT SUM(`sum`) AS paid FROM `payments` WHERE order_id=$order_id")[0]['paid'];
		if ($paid>=$orders[0]['sum']) 
		{
			$que = "UPDATE orders SET datetime_paid = CURRENT_TIMESTAMP WHERE id=$order_id";		
			ExecSQL($link,$que);
			// !!!!!!!!!!!!!!!!!! Заказ оплачен; требуются дополнительные действия!
		}
		
		exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	}

if ($method=='hutki_incoming_ok') // вызванный webhook при совершенной оплате
{
	$wsb_order_num = $_GET['wsb_order_num'];
	$wsb_tid = $_GET['wsb_tid'];
	
	echo 'yes GET: '.json_encode($_GET).PHP_EOL;
	echo 'yes POST: '.json_encode($_POST).PHP_EOL;
	
	
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	
}
if ($method=='hutki_incoming_no') // вызванный webhook при несовершенной оплате
{
	$wsb_order_num = $_GET['wsb_order_num'];
	$wsb_tid = $_GET['wsb_tid'];
	
	echo 'yes GET: '.json_encode($_GET).PHP_EOL;
	echo 'yes POST: '.json_encode($_POST).PHP_EOL;

	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	
}

if ($method=='alfa_incoming_ok' || $method=='alfa_incoming_no') // вызванный webhook при совершенной или сломавшейся оплате
{
	if (!isset($_GET['orderId'])) exit(json_encode(['status'=>'ok', 'message'=>'No_data']));	
	$alfa_orderId = $_GET['orderId'];
	send_warning_telegram('ALFA '.json_encode($_GET));
	$pay_check = alfa_pay_check($alfa_orderId);
	$payment_id = '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!'; // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$sum = '1'; // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	if ($pay_check)
	{
		$orders = ExecSQL($link,"SELECT * FROM orders WHERE alfa_orderId='$alfa_orderId'");
		if (count($orders)==0)
		{
			send_warning_telegram("ALFA Не найдена оплата в журнале alfa_orderId=$alfa_orderId payment_id=$payment_id sum=$sum");
		}
		else
		{
			$order_id = $orders[0]['id'];
			$order_number = $orders[0]['number'];
		
			$que = "INSERT INTO payments (order_id,sum,datetime,payment_method,payment_report)
					VALUES ('$order_id',$sum,CURRENT_TIMESTAMP,'alfa','ALFA $alfa_orderId - $payment_id ')";
			ExecSQL($link,$que);
			send_warning_telegram("ALFA Зарегистрирована оплата по заказу $order_number в сумме $sum руб.");
		}
	}
	
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
}
