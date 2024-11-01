<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	$doc = file_get_contents('ftkrm_sample.html');
	$doc = actual_by_auth($username,$reddottext,$doc,$cart['sum_goods']);
	
	
	$doc = cut_fragment($doc, '<!-- SIMILAR_GOODS_BEGIN -->', '<!-- SIMILAR_GOODS_END -->','');
	$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','');
	$doc = cut_fragment($doc, '<!--ORDER_PAGE_BEGIN -->','<!--ORDER_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PAYMENT_PAGE_BEGIN -->','<!--PAYMENT_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- BANNERS_PAGE_BEGIN -->','<!-- BANNERS_PAGE_END -->','');
	
	$doc = str_replace('[client_email]', $cart['client_email'], $doc);
	$doc = cut_fragment($doc,'<!-- ORDER_1_BEGIN -->','<!-- ORDER_1_END -->','[orders_table]',$html_order_1);
	
	$cart_count = $cart['cart_count'];
	if ($cart_count>0) $doc = str_replace('[cart_count]', $cart_count, $doc); else $doc = cut_fragment($doc, '<!-- CART_COUNT_START -->','<!-- CART_COUNT_END -->','');
	
	
	$orders = ExecSQL($link,"SELECT * FROM `orders` WHERE client_id=$client_id ORDER BY datetime_create ");
	
	$html_orders = '';
	if (count($orders)>0) foreach ($orders as $order)
	{
		$order = all_about_order($order['number']);
		$html_orders = $html_orders . $html_order_1;
		
		$html_orders = str_replace('[order_number]', '№ ' . $order['number'] . ' от ' . (new DateTime($order['datetime_create']))->format('d.m.Y') . ' ' . $order['status_text'] . ' ', $html_orders);
		$html_orders = str_replace('[order_link]'		, 'https://fitokrama.by/order_page.php?order='.$order['number'], $html_orders);
		if ($order['status']!='need_to_pay') 
			{ 
				$html_orders = cut_fragment($html_orders, '<!-- BUTTON_PAY_BEGIN -->','<!-- BUTTON_PAY_END -->','');
			}
			else
			{ 
				$html_orders = str_replace('<!-- BUTTON_PAY_BEGIN -->','',$html_orders );
				$html_orders = str_replace('<!-- BUTTON_PAY_END -->','',$html_orders );
			}
		if ($order['status']!='waiting_for_receive') 
			{ 
				$html_orders = cut_fragment($html_orders, '<!-- BUTTON_GET_BEGIN -->','<!-- BUTTON_GET_END -->','');
			}
			else
			{ 
				$html_orders = str_replace('<!-- BUTTON_GET_BEGIN -->','',$html_orders );
				$html_orders = str_replace('<!-- BUTTON_GET_END -->','',$html_orders );
			}
		
		
		$html_orders = str_replace('[order_sum_rub]'		, f2_rub($order['sum']), $html_orders);
		$html_orders = str_replace('[order_sum_kop]'		, f2_kop($order['sum']), $html_orders);
		
		
		
	}	
		$doc = str_replace('[orders_table]', $html_orders, $doc);

	
	
	exit ($doc);
	