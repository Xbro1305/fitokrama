<?php
	include 'mnn.php'; 


	header('Content-Type: application/json');

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	

	$json_in = json_decode(file_get_contents("php://input"),TRUE);

	

	if (isset($json_in['goodart'])) $good_art = $json_in['goodart'];;
	if (isset($json_in['qty']))  	$qty = 		$json_in['qty'];
	if (isset($json_in['price'])) 	$price = $json_in['price'];

	if (is_null($good_art) or $good_art=='') die (json_encode(['error'=>'Incorrect goodart 1']));

	if (!is_numeric($good_art)) die (json_encode(['error'=>'Incorrect goodart 2']));
	if (!is_numeric($qty)) die (json_encode(['error'=>'Incorrect qty ']));
		
	
    if (!is_numeric($price)) die (json_encode(['error'=>'Incorrect price ']));
        
	
	
	$this_good = Exec_PR_SQL($link,"SELECT * FROM goods WHERE art=?",[$good_art])[0];
	if (is_null($this_good)) die (json_encode(['error'=>'Incorrect goodart 3']));
	$old_price = $this_good['price_old'];
	
	$page_name_called = basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));

	$que = "SELECT id FROM carts_goods WHERE good_art=? AND client_id=?";
	$carts_goods_id = Exec_PR_SQL($link,$que,[$good_art,$client_id])[0]['id'];

	if ($carts_goods_id==NULL)
		Exec_PR_SQL($link,"INSERT INTO carts_goods (client_id,good_art,price,old_price,qty) VALUES (?,?,?,?,?)",[$client_id,$good_art,$price,$old_price,$qty]);
	else
		if ($page_name_called=='art_page.php')
				Exec_PR_SQL($link,"UPDATE carts_goods SET qty=qty+?, price=?, old_price=? WHERE id=?",[$qty,$price,$old_price,$carts_goods_id]);
		else 	Exec_PR_SQL($link,"UPDATE carts_goods SET qty=?, price=?, old_price=? WHERE id=?",[$qty,$price,$old_price,$carts_goods_id]);
	
	
	Exec_PR_SQL($link,"UPDATE clients SET datetime_last=CURRENT_TIMESTAMP() WHERE id=?",[$client_id]);
	
	
	$cart = cart_by_session_id_and_username($session_id,$username);
	

	
	exit (json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
