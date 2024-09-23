<?php
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode
	include 'mnn.php';



	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	$doc = file_get_contents('ftkrm_sample.html');
	$doc = actual_by_auth($username,$reddottext,$doc,$cart['sum_goods']);
	
	
	$doc = cut_fragment($doc, '<!-- SIMILAR_GOODS_BEGIN -->', '<!-- SIMILAR_GOODS_END -->','');
	$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','');
	$doc = cut_fragment($doc, '<!--PROFILE_PAGE_BEGIN -->','<!--PROFILE_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PAYMENT_PAGE_BEGIN -->','<!--PAYMENT_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- BANNERS_PAGE_BEGIN -->','<!-- BANNERS_PAGE_END -->','');
	
	
	
	$order_number = $_GET['order'];
	$doc = str_replace('[pagename]', 'Подробности заказа '.$order_number, $doc);
	
	$order = all_about_order($order_number);
	if ($order['client_id']!=$client_id) die(json_encode(['status'=>'error', 'message'=>'Access error' ]));
	
	
	$cart_count = $cart['cart_count'];
	if ($cart_count>0) $doc = str_replace('[cart_count]', $cart_count, $doc); else $doc = cut_fragment($doc, '<!-- CART_COUNT_START -->','<!-- CART_COUNT_END -->','');
	$doc = str_replace('[order_number]', $order['number'], $doc);
	$doc = str_replace('[order_detailed]', json_encode($order), $doc);
	$doc = str_replace('[order_date]', (new DateTime($order['datetime_create']))->format('d.m.Y H:i') , $doc);

	if ($order['status']=='waiting_for_receive') 
	{
		$insturction_receive = 'Инструкция по получению </br></br></br></br> Инструкция окончена'; ///////!!!!!!!!!!!!!!!!!!!!!! инструкция по получению
		$doc = cut_fragment($doc, '<!-- BUTTON_PAY_BEGIN -->','<!-- BUTTON_PAY_END -->',$insturction_receive);
	}

	if ($order['status']!='need_to_pay') 
		$doc = cut_fragment($doc, '<!-- BUTTON_PAY_BEGIN -->','<!-- BUTTON_PAY_END -->','');
		


	$doc = str_replace('[delivery_logo]' ,  $order['delivery_logo'], $doc);
	$doc = str_replace('[delivery_text]', $order['delivery_text'], $doc);
	
	$doc = str_replace('[sum_rub]', f2_rub($order['sum']), $doc);
	$doc = str_replace('[sum_kop]', f2_kop($order['sum']), $doc);
	$doc = str_replace('[delivery_method]', $order['delivery_text'], $doc);
	
	$doc = str_replace('[order_status]', $order['status_text'], $doc);
	$doc = str_replace('[order_color]', $order['status_color'], $doc);
	
	
	$paylink = $order['epos_link'];
	$paycode = $epos_client_number.$order['number'];
	ob_start();
	QRcode::png($paylink, null, QR_ECLEVEL_Q, 4);
	$imageString = base64_encode(ob_get_clean());
	
	$doc = str_replace('[payqrpicture]', $imageString , $doc);
	$doc = str_replace('[paylink]', $paylink, $doc);
	$doc = str_replace('[paycode]', $paycode, $doc);
	
	$doc = cut_fragment($doc,'<!-- ORDER_GOOD_1_BEGIN -->','<!-- ORDER_GOOD_1_END -->','[goods_table]',$html_good_1);
	
	$html_goods = '';
	foreach ($order['goods'] as $good_1)
	{
		$html_goods = $html_goods . $html_good_1;
		
		$html_goods = str_replace('[good_pic]'			, './'.$good_1['pic_name'], $html_goods);
		$html_goods = str_replace('[good_name]'			, $good_1['name'], $html_goods);
		$html_goods = str_replace('[good_price_rub]'	, f2_rub($good_1['price']), $html_goods);
		$html_goods = str_replace('[good_price_kop]'	, f2_kop($good_1['price']), $html_goods);
		$html_goods = str_replace('[good_art]'			, $good_1['good_art'], $html_goods);
		$html_goods = str_replace('[goodart]'			, $good_1['good_art'], $html_goods);
		$html_goods = str_replace('[good_price]'		, ($good_1['price']), $html_goods);
		$html_goods = str_replace('[good_sum_rub]'		, f2_rub($good_1['price']*$good_1['qty']), $html_goods);
		$html_goods = str_replace('[good_sum_kop]'		, f2_kop($good_1['price']*$good_1['qty']), $html_goods);
		$html_goods = str_replace('[good_qty]'			, $good_1['qty'], $html_goods);
		$html_goods = str_replace('[good_link]'			, $good_1['qty'], $html_goods);
	}	
	$html_goods = $html_goods . $html_good_1;
	$html_goods = str_replace('/goods_pics/[good_pic]'			, '/logos/'.$order['delivery_logo'], $html_goods);
	$html_goods = str_replace('[good_name]'			, 'Доставка: '.$order['delivery_text'], $html_goods);
	$html_goods = str_replace('[good_sum_rub]'		, f2_rub($order['delivery_price']), $html_goods);
	$html_goods = str_replace('[good_sum_kop]'		, f2_kop($order['delivery_price']), $html_goods);
	$html_goods = str_replace('[good_qty]'			, '', $html_goods);
	$html_goods = str_replace('[good_price_rub]'	,  f2_rub($order['delivery_price']), $html_goods);
	$html_goods = str_replace('[good_price_kop]'	,  f2_kop($order['delivery_price']), $html_goods);
	$html_goods = str_replace('шт.'	, '', $html_goods);
		

	$doc = str_replace('[goods_table]', $html_goods, $doc);
	//$doc = str_replace('Оплата:',json_encode($order),$doc);
	
	
	
	
	exit ($doc);