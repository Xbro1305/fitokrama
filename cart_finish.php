<?php
	include_once  'mnn.php'; 
	include_once  'epos_methods.php';
	include_once  'grosh_methods.php';
	include_once  'alfa_methods.php';
	
	header('Content-Type: application/json');

function qty_by_art ($art)			// вычисление текущего количества товара и замороженного количества товара
{
	GLOBAL $link;
	if (!is_numeric($art)) die (json_encode(['error' => 'art error']));
	$que = "SELECT g.*,
    COALESCE(
        (
            (
                SELECT r.qty 
                FROM register_qty r
                WHERE r.art = g.art
            ) 
            + (
                SELECT COALESCE(SUM(d.qty), 0)
                FROM goods_deliveries d
                WHERE d.art = g.art
                AND d.datetime > (
                    SELECT r.datetime FROM register_qty r WHERE r.art = g.art
                )
            )
            - (
                SELECT COALESCE(SUM(og.qty), 0)
                FROM orders_goods og
                LEFT JOIN orders o ON og.order_id = o.id
                WHERE og.good_art = g.art
                AND o.datetime_assembly > (
                    SELECT r.datetime FROM register_qty r WHERE r.art = g.art
                )
            )
        ), 0
    ) AS qty,
    COALESCE(
        (
            SELECT COALESCE(SUM(og.qty), 0)
            FROM orders_goods og
            LEFT JOIN orders o ON og.order_id = o.id
            WHERE og.good_art = g.art
            AND o.datetime_assembly IS NULL
        ), 0
    ) AS qty_fr
	FROM goods g WHERE g.art = ?;  "	;

	$goods = Exec_PR_SQL($link,$que,[$art]);
	if (count($goods)==0) die (json_encode(['error' => 'art error']));
	$qty = $goods[0]['qty']; 
	$qty_fr = $goods[0]['qty_fr']; 
	return [$qty,$qty_fr];
}


	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	$cart = cart_by_session_id_and_username($session_id,$username);
	


	
	if (!$cart['sum']>0 OR !$cart['cart_count']>0 OR $cart['datetime_phone_confirmed']==NULL OR $cart['datetime_email_confirmed']==NULL)
		exit (json_encode(['icon'=>'./logos/problem_red.png', 'error_text'=>'Ошибка оформления заказа. Не указаные некоторые необходимые данные.', 'data'=>json_encode([$cart['sum'],$cart['cart_count'],$cart['datetime_phone_confirmed'],$cart['datetime_email_confirmed']])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	// контроль, нет ли необходимости скорректировать количества
	$good_shortage = false;		// флаг нехватки товара
	foreach ($cart['goods'] as $good_1)
	{
		[$qty,$qty_fr] = qty_by_art ($good_1['good_art']);
		if ($good_1['qty']>$qty-$qty_fr) 
		{
			$qty_limited = MAX(0,MIN($good_1['qty'],$qty-$qty_fr));
			$que = "UPDATE carts_goods SET qty=? WHERE client_id=? AND good_art=?";
			Exec_PR_SQL($link,$que,[$qty_limited,$client_id,$good_1['good_art']]);
			$good_shortage = true;					 // корзина скорректрована
		}
	}
	if ($good_shortage) exit (json_encode(['icon'=>'./logos/problem_red.png', 'error_text'=>'Из-за большого спроса мы были вынуждены скорректировать корзину. Нажмите кнопку "КУПИТЬ" еще раз!']));
	
	
	// переносим корзину в orders
	
	while (true)
	{
		$order_number = random_int(100001,999999);
		$test_order_number_unique = Exec_PR_SQL($link,"SELECT * FROM `orders` WHERE `number`=?",[$order_number]);
		if ($test_order_number_unique==NULL) break;
	}
	$sum = $cart['sum'];
	$sum = 0.15;			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	
	[$epos_link,$epos_id] 	= new_epos_invoice($order_number,$sum,$cart);
	$hutki_billId 			= new_hutki_invoice($order_number,$sum,$cart);
	$hutki_url 				= hutkigrosh_new_GET("invoicing/invoice/$hutki_billId/link?paymentChannel=ERIP&api-version=2.0")['url'];
	
	
	
	
	//[$alfa_orderId,$alfa_url] = new_alfa_invoice($order_number,$sum,$cart);

	
	$order_point_address = NULL;
	$delivery_partners = Exec_PR_SQL($link,"SELECT * FROM delivery_partners WHERE id=?",[$cart['delivery_method']]);
	if (count($delivery_partners)>0) 
	{
		$delivery_partner = $delivery_partners[0];
		if ($delivery_partner['order_point_address']=='client_address')
		{
			$order_point_address = $cart['client_address'];
		}
		else 
		{
			$que = "SELECT * FROM `delivery_points` WHERE CONCAT(?,'-',`unique_id`)=?";
			$delivery_points = Exec_PR_SQL($link,$que,[$delivery_partner['prefix'],$cart['delivery_submethod']]);
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
	`client_email`, 
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
	`hutki_url`,
	`alfa_orderId`,
	`alfa_url`
	
		) VALUES (
	?,?,
	NOW(), 
	?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
	);";
	
	
	$order_id = Exec_PR_SQL($link,$que,
	[$order_number, 	
	$cart['client_id'],
	$cart['client_phone'], 
	$cart['client_email'], 	
	$cart['client_name'],
	$order_point_address,
	$cart['lat'],
	$cart['lng'],
	$cart['delivery_method'], 
	$cart['delivery_submethod'], 
	$cart['delivery_price'],
	$cart['datetime_wait'],
	$sum, 
	$epos_link, 
	$epos_id,
	$hutki_billId,
	$hutki_url,
	$alfa_orderId,
	$alfa_url ]);
	
	$goods =  Exec_PR_SQL($link,"SELECT * FROM `carts_goods` WHERE `client_id`=?",[$client_id]);
	$que = "
	INSERT INTO `orders_goods` (`order_id`, `good_art`, `price`, `qty`, `qty_as`) 
	SELECT $order_id, `good_art`, `price`, `qty`, 0  
		FROM `carts_goods`
		WHERE `client_id` = ?";
	Exec_PR_SQL($link,$que,[$client_id]);
	$que = "DELETE FROM `carts_goods` WHERE `client_id` = ?";
	Exec_PR_SQL($link,$que,[$client_id]);
	
	$res = ['status' => 'success', 'redirect_url' => 'https://fitokrama.by/order_page.php?order=' . $order_number ];
	echo json_encode($res);


	exit ();
