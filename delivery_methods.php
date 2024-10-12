<?php
	include_once 'mnn.php'; 
	include_once 'geocoding.php'; 
	include_once  'yandex_methods.php';
	include_once  'dpd_methods.php';
	include_once  'europost_methods.php';
	include_once  'belpost_methods.php';
	
	header('Content-Type: application/json');

	

function delivery_methods ($address=NULL)				//	выдать методы доставки
{
	GLOBAL $cart;
	GLOBAL $link;
	GLOBAL $session_id, $username, $cart, $client_id, $reddottext;
	GLOBAL $min_sum_gratis_delivery;
	
	if (is_null($address)) $address = $cart['client_address'];
	
	$que = "SELECT `methods` FROM `delivery_methods_saved` WHERE `address`='$address' AND `datetime_until`>CURRENT_TIMESTAMP AND sum_goods=".$cart['sum_goods'];
	$delivery_methods_saved = ExecSQL($link,$que);
	if (count($delivery_methods_saved)>0)
	{
		$methods_json = $delivery_methods_saved[0]['methods'];
		$methods_json = preg_replace('/[[:cntrl:]]/', '', $methods_json);
		$methods = json_decode($methods_json, TRUE);
		//die (json_encode($methods));
		return $methods;
	}
	
	
	//$address = 'Брест, Машерова, 2';

	//$lat = $cart['client_lat'];
	//$lng = $cart['client_lng'];
	[$city, $lat, $lng, $postindex] = array_values(city_by_address_dadata($address));

	[$qty, $weight, $volume] = qty_weight_volume_by_goods($cart['goods']);
	if ($weight==0) $weight = 0.1;
	if ($volume==0) $volume = 0.008;
	
	if ($lat==NULL OR $lng==NULL) return NULL;
	
	$methods ['address'] = $address;
	
	$methods ['lat'] = $lat;
	$methods ['lng'] = $lng;
	$methods ['methods'] = ExecSQL($link,'SELECT id AS method_id, name, logo, prefix, gratis_delivery_by_sum FROM `delivery_partners` WHERE `available`=TRUE ORDER by `priority`;');

	foreach ($methods['methods'] as &$method)
	{

			$method_id = $method['method_id'];

			
			if ($method_id==1)	//	это яндекс-доставка курьером 
			{
				$yandex_res = yandex_check_price ($address,$qty,$weight);
				
				if (isset($yandex_res['price']))
				if ($yandex_res['distance_meters']<30000)	//	более 50 км не выдаем
				{
					$method['price'] = $yandex_res['price'];
					$method['price_rub'] = f2_rub($yandex_res['price']);
					$method['price_kop'] = f2_kop($yandex_res['price']);
					$point['point_id'] = $method['prefix'].'-'.str_pad(base_convert(crc32($client_id), 10, 36), 9, '0', STR_PAD_LEFT); // уникальный хэш от $client_id
					$point['address'] = $address;
					$point['name'] = 'Немедленная доставка курьером Яндекс';
					$point['comment'] = 'Доставка '.round($yandex_res['distance_meters']/1000,1).' км';
					$point['lat'] = $lat;
					$point['lng'] = $lng;
					$point['distance'] = 0;
					$point['walking_time'] = 0;
					
					$method['points'][] = $point;
					
					$method['duration_text'] = "Через ".round(10+$yandex_res['eta'])." мин после заказа";
					$method['note'] = 'Время прибытия указано ориентировочно';
					
				}	
			}
			
			if ($method_id==2)	//	это DPD-почтомат
			{
					$dpd_postomat_res = dpd_calculator(/*$city*/'Минск',$weight,$volume,true);	//	подставляем Минск, т.к. нет разницы в тарифах; чтобы не потерять маленькие пункты возле больших городоа
					
					
					if (is_null($dpd_postomat_res)) continue;
					$index = array_search('PUP', array_column($dpd_postomat_res, 'serviceCode')); // отбираем по сервису PUP	
					
					if ($index===false) continue;
					if (!isset($dpd_postomat_res[$index]['cost'])) continue;
					$method['price'] = $dpd_postomat_res[$index]['cost'];
					$method['price_rub'] = f2_rub($method['price']);
					$method['price_kop'] = f2_kop($method['price']);
					$method['duration_text'] = "Через ".$dpd_postomat_res[$index]['days']." дн. после заказа";
					
					$method['note'] = 'Дата доставки указана ориентировочно';

				$que = "
					WITH distances AS (
						SELECT 
							CONCAT('{$method['prefix']}-', unique_id) AS point_id,
							address,
							name,
							comment,
							lat,
							lng,
							ROUND(6371000 * ACOS(COS(RADIANS($lat)) * COS(RADIANS(dp.lat)) * COS(RADIANS(dp.lng) - RADIANS($lng)) + SIN(RADIANS($lat)) * SIN(RADIANS(dp.lat)))) AS distance,
							specific_json
						FROM 
							delivery_points dp
						WHERE
							partner_id = $method_id
							AND lat BETWEEN -90 AND 90 
							AND lng BETWEEN -180 AND 180
							AND actual_until_datetime > CURRENT_TIMESTAMP
							AND JSON_CONTAINS(specific_json, '\"PUP\"')
					)
					SELECT 
						point_id,
						address,
						name,
						comment,
						lat,
						lng,
						distance,
						ROUND(distance / 5000 * 60) AS walking_time
					FROM 
						distances
					WHERE 
						distance < 30000
					ORDER BY 
						distance
					LIMIT 3";
				
				$method['points'] = ExecSQL($link,$que);
				$count_DPD_postomat = count($method['points']);
			}
			if ($method_id==3)	//	это Европочта - пункт выдачи
			{
					$eur_res = eur_calculator($city,$weight,$volume,true,$address);
					$method['price'] = $eur_res['price'];			//	тут заложена логика, что цена не зависит от пункта доставки
					$method['price_rub'] = f2_rub($method['price']);
					$method['price_kop'] = f2_kop($method['price']);
					$method['duration_text'] = "Через ".$eur_res['days']." дн. после заказа";
					
					$method['note'] = 'Время доставки указано ориентировочно';

				$que = "
					WITH distances AS (
						SELECT 
							CONCAT('{$method['prefix']}-', unique_id) AS point_id,
							address,
							name,
							comment,
							lat,
							lng,
							ROUND(6371000 * ACOS(COS(RADIANS($lat)) * COS(RADIANS(dp.lat)) * COS(RADIANS(dp.lng) - RADIANS($lng)) + SIN(RADIANS($lat)) * SIN(RADIANS(dp.lat)))) AS distance,
							specific_json
						FROM 
							delivery_points dp
						WHERE
							partner_id = $method_id
							AND lat BETWEEN -90 AND 90 
							AND lng BETWEEN -180 AND 180
							AND actual_until_datetime > CURRENT_TIMESTAMP
					)
					SELECT 
						point_id,
						address,
						name,
						comment,
						lat,
						lng,
						distance,
						ROUND(distance / 5000 * 60) AS walking_time
					FROM 
						distances
					WHERE 
						distance < 30000
					ORDER BY 
						distance
					LIMIT 3";
				
				$method['points'] = ExecSQL($link,$que);
			}
			if ($method_id==7)	//	это Европочта - доставка до дверей!
			{
					$eur_res = eur_calculator($city,$weight,$volume,false,$address); // false - это не самовзятие, а доставка до дверей
					if (!isset($eur_res['price'])) continue;	//	нет смысла продолжать метод, если нет тарификации
					$method['price'] = $eur_res['price'];			//	тут заложена логика, что цена не зависит от пункта доставки
					$method['price_rub'] = f2_rub($method['price']);
					$method['price_kop'] = f2_kop($method['price']);
					$method['duration_text'] = "Через ".$eur_res['days']." дн. после заказа";
					
					$method['note'] = 'Время доставки указано ориентировочно';

					$point = array();
					$point['point_id'] = $method['prefix'].'-'.str_pad(base_convert(crc32($client_id), 10, 36), 9, '0', STR_PAD_LEFT); // уникальный хэш от $client_id
					$point['address'] = $address;
					$point['name'] = 'Доставка - курьер Европочты';
					$point['comment'] = '';
					$point['lat'] = $lat;
					$point['lng'] = $lng;
					$point['distance'] = 0;
					$point['walking_time'] = 0;
					
					$method['points'][] = $point;
					
					$method['note'] = 'Дата прибытия указана ориентировочно';
			}
			if ($method_id==4)	//	это DPD-доставка до дверей
			{
				$dpd_res = dpd_calculator($city,0.5,0.4*0.2*0.1,false,$postindex);
				if (!is_null($dpd_res))
				if (!is_null(array_column($dpd_res, 'serviceCode')))
					$index = array_search('NDY', array_column($dpd_res, 'serviceCode')); // отбираем по сервису NDY	
				if (is_null($index)) continue;
				
				if (isset($dpd_res[$index]['cost']))
				{
					$method['price'] = $dpd_res[$index]['cost'];
					$method['price_rub'] = f2_rub($method['price']);
					$method['price_kop'] = f2_kop($method['price']);
					$point['point_id'] = $method['prefix'].'-'.str_pad(base_convert(crc32($client_id), 10, 36), 9, '0', STR_PAD_LEFT); // уникальный хэш от $client_id
					$point['address'] = $address;
					$point['name'] = 'Доставка - курьер DPD';
					$point['comment'] = '';
					$point['lat'] = $lat;
					$point['lng'] = $lng;
					$point['distance'] = 0;
					$point['walking_time'] = 0;
					
					$method['points'][] = $point;
					
					$method['duration_text'] = "Через ".round(1+$dpd_res[$index]['days'])." дн. после заказа";
					$method['note'] = 'Дата прибытия указана ориентировочно';
					
				}	
			}
			if ($method_id==5)	//	это DPD - пункт выдачи
			{
					if ($count_DPD_postomat>0) continue;			 // не выбираем пункты выдачи DPD, если есть почтоматы DPD
					$dpd_postomat_res = dpd_calculator(/*$city*/'Минск',$weight,$volume,true/*,$postindex*/);
					if (is_null($dpd_postomat_res)) continue;
					$index = array_search('NDY', array_column($dpd_postomat_res, 'serviceCode')); // отбираем по сервису NDY
					if (is_null($index)) continue;
					$method['price'] = $dpd_postomat_res[$index]['cost'];
					$method['price_rub'] = f2_rub($method['price']);
					$method['price_kop'] = f2_kop($method['price']);
					$method['duration_text'] = "Через ".$dpd_postomat_res[$index]['days']." дн. после заказа";
					
					$method['note'] = 'Дата доставки указана ориентировочно';

				$que = "
					WITH distances AS (
						SELECT 
							CONCAT('{$method['prefix']}-', unique_id) AS point_id,
							address,
							name,
							comment,
							lat,
							lng,
							ROUND(6371000 * ACOS(COS(RADIANS($lat)) * COS(RADIANS(dp.lat)) * COS(RADIANS(dp.lng) - RADIANS($lng)) + SIN(RADIANS($lat)) * SIN(RADIANS(dp.lat)))) AS distance,
							specific_json
						FROM 
							delivery_points dp
						WHERE
							partner_id = 2
							AND lat BETWEEN -90 AND 90 
							AND lng BETWEEN -180 AND 180
							AND actual_until_datetime > CURRENT_TIMESTAMP
							AND JSON_CONTAINS(specific_json, '\"NDY\"')
							AND NOT JSON_CONTAINS(specific_json, '\"PUP\"')
					)
					SELECT 
						point_id,
						address,
						name,
						comment,
						lat,
						lng,
						distance,
						ROUND(distance / 5000 * 60) AS walking_time
					FROM 
						distances
					WHERE 
						distance < 30000
					ORDER BY 
						distance
					LIMIT 3";
				$method['points'] = ExecSQL($link,$que);	

			
			}
			if ($method_id==6)	//	это Белпочта - пункт выдачи
			{
					$belpost_res = belpost_calculator($city,$weight,$volume,true);
					$method['price'] = $belpost_res['price'];			//	тут заложена логика, что цена не зависит от пункта доставки
					$method['price_rub'] = f2_rub($method['price']);
					$method['price_kop'] = f2_kop($method['price']);
					$method['duration_text'] = "Через ".$belpost_res['days']." дн. после заказа";
					
					$method['note'] = 'Время доставки указано ориентировочно';

				$que = "
					WITH distances AS (
						SELECT 
							CONCAT('{$method['prefix']}-', unique_id) AS point_id,
							address,
							name,
							comment,
							lat,
							lng,
							ROUND(6371000 * ACOS(COS(RADIANS($lat)) * COS(RADIANS(dp.lat)) * COS(RADIANS(dp.lng) - RADIANS($lng)) + SIN(RADIANS($lat)) * SIN(RADIANS(dp.lat)))) AS distance,
							specific_json
						FROM 
							delivery_points dp
						WHERE
							partner_id = $method_id
							AND lat BETWEEN -90 AND 90 
							AND lng BETWEEN -180 AND 180
							AND actual_until_datetime > CURRENT_TIMESTAMP
					)
					SELECT 
						point_id,
						address,
						name,
						comment,
						lat,
						lng,
						distance,
						ROUND(distance / 5000 * 60) AS walking_time
					FROM 
						distances
					WHERE 
						distance < 30000
					ORDER BY 
						distance
					LIMIT 3";
				
				$method['points'] = ExecSQL($link,$que);
			}
			
		if ($method['gratis_delivery_by_sum']==1 && $cart['sum_goods']>=$min_sum_gratis_delivery )
				$method['price']=0; // бесплатная доставка
		
	}


	foreach ($methods['methods'] as $key => &$method) 
	{
		if (is_null($method['points']) OR count($method['points']) == 0) 
			unset($methods['methods'][$key]); // Удаляем элемент массива
	}
	
	$methods_json = json_encode($methods, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);

	//$methods_json = preg_replace('/[[:cntrl:]]/', '', $methods_json);

	$que = "SELECT `id` FROM `delivery_methods_saved` WHERE `address`='$address' AND sum_goods=".$cart['sum_goods'];
	$delivery_methods_saved = ExecSQL($link,$que);
	if (count($delivery_methods_saved)>0)
		$que = "UPDATE `delivery_methods_saved` SET 
					`datetime_until` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE),
					`methods` = '$methods_json' 
				WHERE `address`='$address' AND sum_goods={$cart['sum_goods']}; ";
		else
		$que = "INSERT INTO `delivery_methods_saved` 
				(`address`,`datetime_until`,`methods`, `sum_goods`)
				VALUES 
				('$address',
				DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE),
				'$methods_json',
				{$cart['sum_goods']});";		

	ExecSQL($link,$que);
		
	return $methods;
	
}
		
	if (isset ($_GET['address']))
{
	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id, $reddottext] = enterregistration ();	

	$methods = delivery_methods($_GET['address']);
	
	exit (json_encode($methods, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	
}
	
