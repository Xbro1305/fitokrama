<?php
	include 'mnn.php';


	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	
	
	$doc = file_get_contents('ftkrm_sample.html');
	
	$doc = actual_by_auth($username,$reddottext,$doc,$cart['sum_goods']);
	
	 
	$doc = cut_fragment($doc, '<!-- GOOD_1_BEGIN -->', '<!-- GOOD_1_END -->','[similargoods]',$tmpts_similargoods);
	$doc = cut_fragment($doc, '<!-- CART_BEGIN -->', '<!-- CART_END -->','');
	$doc = cut_fragment($doc, '<!--ORDER_PAGE_BEGIN -->','<!--ORDER_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PROFILE_PAGE_BEGIN -->','<!--PROFILE_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!--PAYMENT_PAGE_BEGIN -->','<!--PAYMENT_PAGE_END -->','');
	$doc = cut_fragment($doc, '<!-- BANNERS_PAGE_BEGIN -->','<!-- BANNERS_PAGE_END -->','');
	
	$art = $_GET['art'];
	$que = "SELECT g.*,
    COALESCE(
        (
            (SELECT r.qty FROM register_qty r WHERE r.art = g.art) 
            + (SELECT COALESCE(SUM(d.qty), 0) FROM goods_deliveries d WHERE d.art = g.art AND d.datetime > (SELECT r.datetime FROM register_qty r WHERE r.art = g.art))
            - (SELECT COALESCE(SUM(og.qty), 0) FROM orders_goods og LEFT JOIN orders o ON og.order_id = o.id WHERE og.good_art = g.art AND o.datetime_assembly > (SELECT r.datetime FROM register_qty r WHERE r.art = g.art))
        ), 0
    ) AS qty,
    COALESCE(
        (
            SELECT COALESCE(SUM(og.qty), 0)
            FROM orders_goods og
            LEFT JOIN orders o ON og.order_id = o.id
            WHERE og.good_art = g.art AND o.datetime_assembly IS NULL
        ), 0
    ) AS qty_fr
	FROM goods g
	WHERE g.goods_groups_id IS NOT NULL AND price>0
	AND art=?";
	$good = Exec_PR_SQL($link,$que,[$art])[0];
	
	if (($good==NULL) or count($good)==0)
	{
		$doc = cut_fragment($doc, '<!-- GOOD_PAGE_BEGIN -->', '<!-- GOOD_PAGE_END -->','oops... Товар отсутствует... ');
		$doc = cut_fragment($doc, '<!-- SIMILAR_GOODS_BEGIN -->', '<!-- SIMILAR_GOODS_END -->','');
		die($doc);
	}

	$cart_count = $cart ['cart_count'];
	if ($cart_count>0) $doc = str_replace('[cart_count]', $cart_count, $doc); else $doc = cut_fragment($doc, '<!-- CART_COUNT_START -->','<!-- CART_COUNT_END -->','');


	$doc = str_replace('[pagename]', 'Фитокрама - '.$good['name'], $doc);
	$doc = str_replace('[goodart]', $good['art'], $doc);
	$doc = str_replace('[goodname]', $good['name'], $doc);
	$doc = str_replace('[gooddef1]', $good['description_short'], $doc);
	$doc = str_replace('[gooddef2]', $good['description_full'], $doc);
	$doc = str_replace('[goodspics]', $good['pic_name'], $doc);
	$doc = str_replace('[goodoldprice]', $good['price_old'], $doc);
	$doc = str_replace('[goodactprice]', $good['price'], $doc);
	
	$doc = str_replace('[good_old_price_rub]', f2_rub($good['price_old']), $doc);
	$doc = str_replace('[good_old_price_kop]', f2_kop($good['price_old']), $doc);
	$doc = str_replace('[good_price_rub]', f2_rub($good['price']), $doc);
	$doc = str_replace('[good_price_kop]', f2_kop($good['price']), $doc);
	
	$doc = str_replace('[timedelievery]', 'Завтра в 19:15', $doc); 		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! исправить логику
	$doc = str_replace('[goodbarcode]', $good['barcode'], $doc);
	$doc = str_replace('[goodcat]', $good['cat'], $doc);
	$doc = str_replace('[goodsubcat]',$good['subcat'], $doc);
	$doc = str_replace('[goodtegs]','', $doc);						// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! исправить логику
	if ($good['qty']-$good['qty_fr']<3) $doc = str_replace('[low_qty]', '', $doc); 		
								  else  $doc = str_replace('[low_qty]', '', $doc); 		
	
	
	$nolek = '';
	if ($good['goods_groups_id']!=99) 	$nolek.='Не является лекарственным средством. ';
	if ($good['goods_groups_id']!=3) 	$nolek.='Не является биологически активной добавкой к пище.';
	
	
	$doc = str_replace('[nolek]',$nolek, $doc);						// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! исправить логику
	$doc = str_replace('[goodprod]', $good['producer'], $doc);
	$doc = str_replace('[good1kg]', $good['koef_ed_ism']*$good['price'], $doc);
	
	
	$similargood_1 ='';
	$que = "SELECT g.*,
    COALESCE(
        (
            (SELECT r.qty FROM register_qty r WHERE r.art = g.art) 
            + (SELECT COALESCE(SUM(d.qty), 0) FROM goods_deliveries d WHERE d.art = g.art AND d.datetime > (SELECT r.datetime FROM register_qty r WHERE r.art = g.art))
            - (SELECT COALESCE(SUM(og.qty), 0) FROM orders_goods og LEFT JOIN orders o ON og.order_id = o.id WHERE og.good_art = g.art AND o.datetime_assembly > (SELECT r.datetime FROM register_qty r WHERE r.art = g.art))
        ), 0
    ) AS qty,
    COALESCE(
        (
            SELECT COALESCE(SUM(og.qty), 0)
            FROM orders_goods og
            LEFT JOIN orders o ON og.order_id = o.id
            WHERE og.good_art = g.art AND o.datetime_assembly IS NULL
        ), 0
    ) AS qty_fr
	FROM goods g
	WHERE g.goods_groups_id IS NOT NULL
	ORDER BY RAND () LIMIT 3 ";
	$similar_goods = Exec_PR_SQL($link,$que,[]);

	foreach ($similar_goods as $sgood)
	{
		$similargood_1 = $similargood_1.$tmpts_similargoods;
		$similargood_1 = str_replace('[goodart]', $sgood['art'], $similargood_1);
		$similargood_1 = str_replace('[goodname]', $sgood['name'], $similargood_1);
		$similargood_1 = str_replace('[gooddef1]', $sgood['description_short'], $similargood_1);
		$similargood_1 = str_replace('[goodspics]', $sgood['pic_name'], $similargood_1);
		$similargood_1 = str_replace('[goodoldprice]', $sgood['price_old'], $similargood_1);
		$similargood_1 = str_replace('[goodactprice]', $sgood['price'], $similargood_1);
		
		$similargood_1 = str_replace('[good_old_price_rub]', f2_rub($sgood['price_old']), $similargood_1);
		$similargood_1 = str_replace('[good_old_price_kop]', f2_kop($sgood['price_old']), $similargood_1);
		$similargood_1 = str_replace('[good_price_rub]', f2_rub($sgood['price']), $similargood_1);
		$similargood_1 = str_replace('[good_price_kop]', f2_kop($sgood['price']), $similargood_1);
		
		if ($sgood['qty']-$sgood['qty_fr']<3) $similargood_1 = str_replace('[low_qty]', '', $similargood_1); 		
										else  $similargood_1 = str_replace('[low_qty]', '', $similargood_1); 		
	}
	
	$doc = str_replace('[similargoods]', $similargood_1, $doc);			// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! исправить логику
	exit ($doc);