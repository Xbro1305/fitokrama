<?php
	include 'mnn.php';
	include_once 'delivery_methods.php';

	
	header('Content-Type: text/html; charset=UTF-8');
	header("Access-Control-Allow-Origin: $http_origin");
		
	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	$delivery_methods = delivery_methods();
	
	$doc = file_get_contents('ftkrm_sample.html');
	$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- SIMILAR_GOODS_BEGIN -->', '<!-- SIMILAR_GOODS_END -->','');
	$doc = cut_fragment($doc, '<!--ORDER_PAGE_BEGIN -->','<!--ORDER_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PROFILE_PAGE_BEGIN -->','<!--PROFILE_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PAYMENT_PAGE_BEGIN -->','<!--PAYMENT_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- BANNERS_PAGE_BEGIN -->','<!-- BANNERS_PAGE_END -->','');
	

	$doc = actual_by_auth($username,$reddottext,$doc,$cart['sum_goods']);
	
	
		
	if ($cart['goods']==NULL) 
	{
		
		$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','oops Корзина пуста');
		
		
		exit ($doc);
	}
	
	
	$doc = str_replace('[delivery_address]', $delivery_address, $doc);
	
	
	
	$doc = str_replace('[delivery_logo]'	, $cart['delivery_logo'], $doc);
	$doc = str_replace('[delivery_text]'	, $cart['delivery_text'], $doc);
	
	
	if ($cart['delivery_price']!=NULL)
		$doc = str_replace('[delivery_price]'	, $cart['delivery_price'], $doc);
	else
	{
		$doc = str_replace('[delivery_price]'	, '?', $doc);
		$doc = str_replace('[sum_rub]'			, '?', $doc);
		$doc = str_replace('[sum_kop]'			, '?', $doc);
		$doc = str_replace('[sum]'				, '?', $doc);
	}
	$doc = str_replace('[sum_rub]'			, ($cart['sum_rub']), $doc);
	$doc = str_replace('[sum_kop]'			, ($cart['sum_kop']), $doc);
	$doc = str_replace('[sum]'				, $cart['sum'], $doc);
	$doc = str_replace('[client_name]'		, $cart['client_name'], $doc);
	$doc = str_replace('[client_email]'		, $cart['client_email'], $doc);	
	$doc = str_replace('[client_telegram]'		, $cart['client_telegram'], $doc);	
	$doc = str_replace('[client_phone]'		, $cart['client_phone'], $doc);
	$doc = str_replace('[client_address]'	, $cart['client_address'], $doc);
	
	$doc = str_replace('[delivery_price]', 	$cart['delivery_price'], $doc);
	$doc = str_replace('[delivery_price_rub]', 	$cart['delivery_price_rub'], $doc);
	$doc = str_replace('[delivery_price_kop]', 	$cart['delivery_price_kop'], $doc);
	$cart_count = $cart ['cart_count'];
	if ($cart_count>0) $doc = str_replace('[cart_count]', $cart_count, $doc); else $doc = cut_fragment($doc, '<!-- CART_COUNT_START -->','<!-- CART_COUNT_END -->','');
	
	if ($cart['datetime_email_confirmed']==NULL) 
				$doc = cut_fragment($doc,'<!-- EMAIL_CONFIRMED_START -->','<!-- EMAIL_CONFIRMED_END -->','');
		else 	$doc = cut_fragment($doc,'<!-- EMAIL_NOT_CONFIRMED_START -->','<!-- EMAIL_NOT_CONFIRMED_END -->','');


	if ($cart['datetime_telegram_confirmed']==NULL) 
				$doc = cut_fragment($doc,'<!-- TELEGRAM_CONFIRMED_START -->','<!-- TELEGRAM_CONFIRMED_END -->','');
		else 	$doc = cut_fragment($doc,'<!-- TELEGRAM_NOT_CONFIRMED_START -->','<!-- TELEGRAM_NOT_CONFIRMED_END -->','');

	if ($cart['datetime_phone_confirmed']==NULL) 
				$doc = cut_fragment($doc,'<!-- PHONE_CONFIRMED_START -->','<!-- PHONE_CONFIRMED_END -->','');
		else 	$doc = cut_fragment($doc,'<!-- PHONE_NOT_CONFIRMED_START -->','<!-- PHONE_NOT_CONFIRMED_END -->','');
	
	
	$doc = cut_fragment($doc,'<!-- CART_GOOD_1_BEGIN -->','<!-- CART_GOOD_1_END -->','[goods_table]',$html_good_1);

	$html_goods = '';
	$cou = 0;
	
	foreach ($cart['goods'] as $good_1)
	{
		$cou = $cou + 1;
		$html_goods = $html_goods . $html_good_1;
		
		$html_goods = str_replace('[good_pic]'			, './'.$good_1['pic_name'], $html_goods);
		$html_goods = str_replace('[good_name]'			, $good_1['name'], $html_goods);
		if ($good_1['old_price']!=NULL)
		{
			$html_goods = str_replace('[good_old_price_rub]', f2_rub($good_1['old_price']), $html_goods);
			$html_goods = str_replace('[good_old_price_kop]', f2_kop($good_1['old_price']), $html_goods);
				}
		else
		{
			$html_goods = str_replace('[good_old_price_rub],<sup>[good_old_price_kop]</sup>', '', $html_goods);
			
		}
		
		$html_goods = str_replace('[good_price_rub]'	, f2_rub($good_1['price']), $html_goods);
		$html_goods = str_replace('[good_price_kop]'	, f2_kop($good_1['price']), $html_goods);
		$html_goods = str_replace('[good_count]'		, $cou, $html_goods);
		$html_goods = str_replace('[good_art]'			, $good_1['good_art'], $html_goods);
		$html_goods = str_replace('[goodart]'			, $good_1['good_art'], $html_goods);
		$html_goods = str_replace('[good_price]'		, ($good_1['price']), $html_goods);
		$html_goods = str_replace('[good_sum_rub]'		, f2_rub($good_1['price']*$good_1['qty']), $html_goods);
		$html_goods = str_replace('[good_sum_kop]'		, f2_kop($good_1['price']*$good_1['qty']), $html_goods);
		$html_goods = str_replace('[good_qty]'			, $good_1['qty'], $html_goods);
		$html_goods = str_replace('[good_link]'			, $good_1['qty'], $html_goods);
		
	}
	$doc = str_replace('[goods_table]', $html_goods, $doc);
	
	
	$doc = cut_fragment($doc,'<!-- METHOD_1_BEGIN -->','<!-- METHOD_1_END -->','[methods_table]',$html_methods_1);


	$html_methods = '';
	$cou = 0;
	$method_found=false;
	
	if (isset($delivery_methods['methods']))
	foreach ($delivery_methods['methods'] as $method_1)
	{
		foreach ($method_1['points'] as $point_1)
			{
				$cou = $cou + 1;
				$html_methods = $html_methods . $html_methods_1;
				
				$html_methods = str_replace('[method_pic]'			, ''.$method_1['logo'], $html_methods);
				$html_methods = str_replace('[method_name]'			, $method_1['name'], $html_methods);
				$html_methods = str_replace('[method_duration_text]', $method_1['duration_text'], $html_methods);
				$html_methods = str_replace('[method_note]', $method_1['method_note'], $html_methods);
				
				$html_methods = str_replace('[method_price_rub]'	, f2_rub($method_1['price']), $html_methods);
				$html_methods = str_replace('[method_price_kop]'	, f2_kop($method_1['price']), $html_methods);
				$html_methods = str_replace('[method_price]'		, ($method_1['price']), $html_methods);
				
				$html_methods = str_replace('[method_id]', ''.$point_1['point_id'], $html_methods);
				$html_methods = str_replace('[method_address]', ''.$point_1['address'], $html_methods);
				$html_methods = str_replace('[method_name]', ''.$point_1['name'], $html_methods);
				$html_methods = str_replace('[method_comment]', ''.$point_1['comment'], $html_methods);
				if ($point_1['distance']==0) 
								$html_methods = str_replace('[method_distance]', '🏠 к вашим дверям', $html_methods);
						else 	$html_methods = str_replace('[method_distance]', $point_1['distance'].'м🚶 '.$point_1['walking_time'].'мин'.'🕕', $html_methods);
				
				
				if ($cart['delivery_submethod']==$point_1['point_id'])									// поставить точку на выбранный вариант
					{	
						$html_methods=str_replace('[button_pic]', 'green_ok.png', $html_methods); 
						$html_methods=str_replace('[option_class]', 'delivery_option', $html_methods); 
						$method_found=true;
					}
					else 
					{
						$html_methods=str_replace('[button_pic]', 'notenabled.png', $html_methods); 
						$html_methods=str_replace('[option_class]', 'delivery_option hidden_option', $html_methods); 
					}
					
				if ($method_1['price']==0)
				{
					$html_methods=str_replace('[plate_color]', '#B1D1B2 ', $html_methods); 
					$html_methods=str_replace('[plate_text]', 'бесплатная доставка', $html_methods); 
				}
				
				

				if ($point_1['distance']==0)
				{
					$html_methods=str_replace('[plate_color]', '#ffaaa6', $html_methods); 
					$html_methods=str_replace('[plate_text]', 'на дом!', $html_methods); 
				}

				$html_methods=str_replace('[plate_color]', '', $html_methods); 
				$html_methods=str_replace('[plate_text]', '', $html_methods); 

				$html_methods = str_replace('<!-- METHOD_1_BEGIN -->', '', $html_methods);
				$html_methods = str_replace('<!-- METHOD_1_END -->', '', $html_methods);
				//$html_methods = str_replace('<!-- checked-->', '', $html_methods); 
				
				
		}
	}
	
	if (!$method_found) 
		$html_methods=str_replace('delivery_option hidden_option', 'delivery_option', $html_methods); 
	
	
	
	$doc = str_replace('[methods_table]', $html_methods, $doc);
	if (!$method_found || $cou==1) 
	$doc=str_replace('Больше вариантов ⋁', '', $doc); 

	
	exit ($doc);
	
	
	
/*
	<button id="toggleOptionsBtn" onclick="toggleOptions()">
	Больше вариантов ⋁
	</button>
*/