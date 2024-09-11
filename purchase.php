<?php
	include 'mnn.php';

	
	header("Access-Control-Allow-Origin: $http_origin");
		
	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	
	
	$cart_count = $cart ['cart_count'];
	$doc = actual_by_auth($username,$doc);
	
	$doc = file_get_contents('ftkrm_sample.html');
	$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- SIMILAR_GOODS_BEGIN -->', '<!-- SIMILAR_GOODS_END -->','');
	$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','Страница оформления покупки');
	
	
	
	
		
		$doc = str_replace('[cart_count]', $cart_count, $doc);
	
	
	exit ($doc);