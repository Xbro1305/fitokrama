<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	//die (json_encode($_SERVER).PHP_EOL);
	$doc = file_get_contents('ftkrm_sample.html');
	$doc = actual_by_auth($username,$reddottext,$doc,$cart['sum_goods']);
	
	 
	$doc = cut_fragment($doc, '<!-- GOOD_1_BEGIN -->', '<!-- GOOD_1_END -->','[similargoods]',$tmpts_similargoods);
	$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','');
	$doc = cut_fragment($doc, '<!--ORDER_PAGE_BEGIN -->','<!--ORDER_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PROFILE_PAGE_BEGIN -->','<!--PROFILE_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PAYMENT_PAGE_BEGIN -->','<!--PAYMENT_PAGE_END -->','');


	//	подкорректировать настройки баннеров

	
	$que = "SELECT * FROM goods WHERE goods_groups_id IS NOT NULL LIMIT 12;";
	$goods = ExecSQL($link,$que);
	$list_goods = array_column($goods, 'art');

	$doc = 		 preg_replace('/<!\s*--\s*\[CHANGE_FROM\]\s*--\s*>.*?<!\s*--\s*\[CHANGE_TO\]\s*--\s*>/s', '' , $doc);
	$cart_count = $cart['cart_count'];
	if ($cart_count>0) $doc = str_replace('[cart_count]', $cart_count, $doc); else $doc = cut_fragment($doc, '<!-- CART_COUNT_START -->','<!-- CART_COUNT_END -->','');

	
	if ($cat!=NULL) $doc = str_replace('ПОХОЖИЕ ТОВАРЫ', $cat, $doc);
	 else if ($subcat!=NULL) $doc = str_replace('ПОХОЖИЕ ТОВАРЫ', $subcat, $doc);
		else $doc = str_replace('ПОХОЖИЕ ТОВАРЫ', 'КАТАЛОГ', $doc);
	
	
	foreach ($list_goods as $similar_good_art_1)
	{
		$sgood = ExecSQL($link,"SELECT * FROM goods WHERE art=$similar_good_art_1")[0];
		$similargood_1 = $similargood_1.$tmpts_similargoods;
		$similargood_1 = str_replace('[goodart]', $sgood['art'], $similargood_1);
		$similargood_1 = str_replace('[goodname]', $sgood['name'], $similargood_1);
		$similargood_1 = str_replace('[gooddef1]', $sgood['description_short'], $similargood_1);
		$similargood_1 = str_replace('[goodspics]', $sgood['pic_name'], $similargood_1);
		$similargood_1 = str_replace('[goodoldprice]', $sgood['price_old'], $similargood_1);
		$similargood_1 = str_replace('[goodactprice]', $sgood['price'], $similargood_1);
	}
	
	
	$doc = str_replace('[similargoods]', $similargood_1, $doc);
	exit ($doc);