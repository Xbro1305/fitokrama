<?php
	include 'mnn.php';
	header("Access-Control-Allow-Origin: $http_origin");
	
	$link = firstconnect ();


	$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	
	if ($method=='test_printing') // надо проверить, работает ли автопечать?
	{
		$dayOfWeek = date('N'); // номер дня недели (1 для понедельника, 7 для воскресенья)
		$currentTime = date('H:i'); // время в формате ЧЧ:ММ

		if ($dayOfWeek < 1 || $dayOfWeek > 5 || $currentTime < '09:15' || $currentTime > '17:00') 
			exit (json_encode(['status'=>'ok', 'message'=> 'ok']));			//	в эти моменты контроль распечатки лишен смысла
    
		$que = "SELECT * 
					FROM `orders` 
					WHERE datetime_paid IS NOT NULL 
					  AND datetime_paid < DATE_SUB(NOW(), INTERVAL 10 MINUTE) 
					  AND datetime_assembly IS NULL 
					  AND datetime_cancel IS NULL 
					  AND datetime_order_print IS NULL;	";
		$orders = ExecSQL($link,$que);
		if (count($orders)>0) 
			{	
				send_telegram_info_group('☠️ Внимание! Количество нераспечатанных заданий на сборку сроком более 10 минут: '.count($orders),$ids);
			}	
		exit (json_encode(['status'=>'ok', 'message'=> 'ok']));
	}
	

