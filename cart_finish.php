<?php
	include_once  'mnn.php'; 
	include_once  'epos_methods.php';
	include_once  'grosh_methods.php';
	include_once  'alfa_methods.php';
	
	

	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	$cart = cart_by_session_id_and_username($session_id,$username);
	
	
	if (!$cart['sum']>0 OR !$cart['cart_count']>0 OR $cart['datetime_phone_confirmed']==NULL OR $cart['datetime_email_confirmed']==NULL)
		exit (json_encode(['icon'=>'./logos/problerm_red.png', 'error_text'=>'Ошибка оформления заказа. Не указаные некоторые необходимые данные.', 'data'=>json_encode([$cart['sum'],$cart['cart_count'],$cart['datetime_phone_confirmed'],$cart['datetime_email_confirmed']])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	// переносим корзину в orders
	
	while (true)
	{
		$order_number = random_int(100001,999999);
		$test_order_number_unique = ExecSQL($link,"SELECT * FROM `orders` WHERE `number`='$order_number'");
		if ($test_order_number_unique==NULL) break;
	}
	$sum = $cart['sum'];
	$sum = 1.11;			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	
	[$epos_link,$epos_id] = new_epos_invoice($order_number,$sum,$cart);
	$hutki_billId = new_hutki_invoice($order_number,$sum,$cart);
	[$alfa_orderId,$alfa_url] = new_alfa_invoice($order_number,$sum,$cart);

	
	$order_point_address = NULL;
	$delivery_partners = ExecSQL($link,"SELECT * FROM delivery_partners WHERE id={$cart['delivery_method']}");
	if (count($delivery_partners)>0) 
	{
		$delivery_partner = $delivery_partners[0];
		if ($delivery_partner['order_point_address']=='client_address')
		{
			$order_point_address = $cart['client_address'];
		}
		else 
		{
			$que = "SELECT * FROM `delivery_points` WHERE CONCAT('{$delivery_partner['prefix']}','-',`unique_id`)='{$cart['delivery_submethod']}'";
			$delivery_points = ExecSQL($link,$que);
			if (count($delivery_points)>0) 
			{
				$delivery_point = $delivery_points[0];
				$order_point_address = $delivery_point['unique_id'].' '.$delivery_point['address'].' '.$delivery_point['name'];
			}
		}
	}


	if ($order_point_address==NULL)
	{
		$order_point_address = $delivery_point['unique_id'].' '.$delivery_point['address'].' '.$delivery_point['name'];
		send_warning_telegram('50 Ошибка определения order_point_address');
		
	}
	
	
	
	$que = "INSERT INTO `orders` (
	`number`, 
	`client_id`, 
	`datetime_create`, 
	`client_phone`, 
	`client_name`,
	`order_point_address`,
	`lat`,
	`lng`,
	`delivery_method`,
	`delivery_submethod`, 
	`delivery_price`,
	`datetime_wait`,
	`sum`,
	`epos_link`, 
	`epos_id`,
	`hutki_billId`,
	`alfa_orderId`,
	`alfa_url`
	
		) VALUES (
	'$order_number', 
	'{$cart['client_id']}', 
	NOW(), 
	'{$cart['client_phone']}', 
	'{$cart['client_name']}',
	'$order_point_address',
	'{$cart['lat']}',
	'{$cart['lng']}',
	'{$cart['delivery_method']}', 
	'{$cart['delivery_submethod']}', 
	{$cart['delivery_price']},
	'{$cart['datetime_wait']}',
	$sum, 
	'$epos_link', 
	'$epos_id',
	'$hutki_billId',
	'$alfa_orderId',
	'$alfa_url'
	);";
	
	$order_id = ExecSQL($link,$que);
	
	$goods =  ExecSQL($link,"SELECT * FROM `carts_goods` WHERE `client_id`=$client_id");
	$que = "
	INSERT INTO `orders_goods` (`order_id`, `good_art`, `price`, `qty`, `qty_as`) 
	SELECT $order_id, `good_art`, `price`, `qty`, 0  
		FROM `carts_goods`
		WHERE `client_id` = $client_id;";
	ExecSQL($link,$que);
	$que = "DELETE FROM `carts_goods` WHERE `client_id` = $client_id;";
	ExecSQL($link,$que);
	
	$res = ['status' => 'success', 'redirect_url' => 'https://fitokrama.by/order_page.php?order=' . $order_number ];
	echo json_encode($res);


	exit ();
