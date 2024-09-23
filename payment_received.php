<?php
	include_once  'mnn.php';
	header('Content-Type: application/json');

	$data = json_decode(file_get_contents("php://input"),TRUE);
	$link = firstconnect ();




$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='epos_incoming') // вызванный webhook при совершенной оплате
	{
		file_put_contents('epos_log.txt', json_encode($data, FILE_APPEND | LOCK_EX).PHP_EOL.PHP_EOL );
		
		$invoice_number = $data['claimId'];
		$payment_id = $data['id'];
		$sum = $data['amount']['amt'];
		$eripid = $data['memorialSlip']['tranEripId'];
		$eposid = $data['memorialSlip']['transEposId'];
		$invoice_id = $data['parentId'];
		
		$orders = ExecSQL($link,"SELECT * FROM orders WHERE epos_id='$invoice_id'");
		
		if (count($orders)==0)
		{
			send_warning_telegram("Не найдена оплата в журнале invoice_number=$invoice_number payment_id=$payment_id sum=$sum");
		}
		else
		{
			$order_id = $orders[0]['id'];
			$order_number = $orders[0]['number'];
			
		
			
			$que = "INSERT INTO payments (order_id,sum,datetime,payment_method,payment_report)
					VALUES ('$order_id',$sum,CURRENT_TIMESTAMP,'epos','EPOS $invoice_number - $eposid - $eripid')";
			ExecSQL($link,$que);
			send_warning_telegram("Зарегистрирована оплата по заказу $order_number в сумме $sum руб.");
			
		
		}
		$paid = ExecSQL($link,"SELECT SUM(`sum`) AS paid FROM `payments` WHERE order_id=$order_id")[0]['paid'];
		if ($paid>=$orders[0]['sum']) 
		{
			$que = "UPDATE orders SET datetime_paid = CURRENT_TIMESTAMP WHERE id=$order_id";
			ExecSQL($link,$que);
		}
		
		exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	}

if ($method=='hutki_incoming_ok') // вызванный webhook при совершенной оплате
{
	send_warning_telegram('yes: '.json_encode($data));
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	
}
if ($method=='hutki_incoming_ok') // вызванный webhook при несовершенной оплате
{
	send_warning_telegram('no: '.json_encode($data));
	exit(json_encode(['status'=>'ok', 'message'=>'ok']));	
	
}