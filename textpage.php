<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	
	$page = $_GET['page'];
	$pagetext = file_get_contents("./pages/$page.html");

	
	
	

	$cart_count = $cart ['cart_count'];
	
	$doc = file_get_contents('ftkrm_sample.html');
	$doc = actual_by_auth($username,$reddottext,$doc,$cart['sum_goods']);

	

	
	$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','[pagetext]');
	$doc = cut_fragment($doc, '<!-- SIMILAR_GOODS_BEGIN -->', '<!-- SIMILAR_GOODS_END -->','');
	$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','');
	$doc = cut_fragment($doc, '<!--ORDER_PAGE_BEGIN -->','<!--ORDER_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PROFILE_PAGE_BEGIN -->','<!--PROFILE_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- BANNERS_PAGE_BEGIN -->','<!-- BANNERS_PAGE_END -->','');
	$doc = str_replace('[cart_count]', $cart_count, $doc);

	if ($pagetext!=NULL) $doc = str_replace('[pagetext]', $pagetext, $doc);
					else $doc = str_replace('[pagetext]', 'Упс.. такой страницы не существует...', $doc);

	
	
	exit ($doc);