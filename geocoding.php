<?php
//	include 'mnn.php';
	header('Content-Type: application/json');
	include '../varsse.php';

	

function transformAddress($address) {
    // Разделим адрес на части
    $parts = explode(',', $address);

    // Инициализируем переменные
    $location = '';
    $street = '';
    $house = '';
    $apartment = '';
    $locality_types = ['г. ', 'р-н ', 'деревня ', 'агрогородок ', 'поселок '];
    $street_replacements = [
        'ул. ' => 'улица ',
        'ул ' => 'улица ',
        'улица ' => 'улица ',
        'пер. ' => 'переулок ',
        'пер.' => 'переулок ',
        'пр-кт' => 'проспект ',
        'проспект' => 'проспект ',        
        'б-р ' => 'бульвар ',
        'пл. ' => 'площадь ',
        'пр. ' => 'проспект ',
        'пр-т ' => 'проспект ',
    ];

    // Найти и удалить местоположение из частей адреса
    foreach ($parts as $key => $part) {
        $part = trim($part);
        foreach ($locality_types as $locality) {
            if (strpos($part, $locality) !== false) {
                $location = trim(str_replace($locality, '', $part));
                unset($parts[$key]);
                break 2;
            }
        }
    }

    // Найти и удалить улицу из частей адреса
    foreach ($parts as $key => $part) {
        $part = trim($part);
        foreach ($street_replacements as $short => $full) {
            if (strpos($part, $short) !== false) {
                $street = $full . trim(str_replace($short, '', $part));
                unset($parts[$key]);
                break 2;
            }
        }
    }

    // Осталось только части с домом и корпусом
    foreach ($parts as $part) {
        $part = trim($part);
        if (strpos($part, 'д. ') !== false) {
            $house_part = trim(str_replace('д. ', '', $part));
            if (strpos($house_part, '/') !== false) {
                list($house, $apartment) = explode('/', $house_part);
            } else {
                $house = $house_part;
            }
        } elseif (is_numeric($part[0]) || preg_match('/^\d+[А-Яа-я]?/', $part)) {
            $house_part = trim($part);
            if (strpos($house_part, '/') !== false) {
                list($house, $apartment) = explode('/', $house_part);
            } else {
                $house = $house_part;
            }
        }
    }

    // Сформируем новый адрес в формате "дом/корпус, улица, город"
    if (!empty($apartment)) {
        return trim("$house к $apartment, $street, $location");
    } else {
        return trim("$house, $street, $location");
    }
}

	
function geocoding_by_osm($address): array {
    $lang = 'RU';
	
	// подготовить адрес для nominatim
	
	$cleanedAddress = transformAddress($address);//prepareAddressForGeocoding($address);
	
	//send_warning_telegram($address.' -> '.$cleanedAddress);
	file_put_contents('debug_geocoding.txt', date('[Y-m-d H:i:s] ') . 'address='.$address.' cleanedAddress='.$cleanedAddress. PHP_EOL, FILE_APPEND | LOCK_EX);	
	$address = $cleanedAddress;
	
    // Формируем базовый URL и параметры для запроса
    $base_url = 'https://nominatim.openstreetmap.org/search';
	
    $parameters = [
        'format' => 'json',
        'addressdetails' => 1,
        'accept-language' => $lang,
        'q' => $address
    ];
    $query = http_build_query($parameters);
    $full_url = $base_url . '?' . $query;
	//send_warning_telegram($full_url);

    // Создаем контекст с необходимым заголовком User-Agent
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: YourAppName/1.0 (your_email@example.com)\r\n"
        ]
    ];
    $context = stream_context_create($opts);

    // Выполняем запрос к API Nominatim
    $json = file_get_contents($full_url, false, $context);
    $data = json_decode($json, true);

    $lon = 0; // Долгота по умолчанию
    $lat = 0; // Широта по умолчанию
    $city = ''; // Город
    $precision = 'low'; // Уровень точности

    if (!empty($data)) {
        // Берем первый результат из массива
        $firstResult = $data[0];
        $lon = $firstResult['lon'];
        $lat = $firstResult['lat'];
        // Определяем населенный пункт
        if (isset($firstResult['address'])) {
            if (isset($firstResult['address']['city'])) {
                $city = $firstResult['address']['city'];
            } elseif (isset($firstResult['address']['town'])) {
                $city = $firstResult['address']['town'];
            } elseif (isset($firstResult['address']['village'])) {
                $city = $firstResult['address']['village'];
            }
        }
        $precision = 'hi'; // Устанавливаем высокий уровень точности, если получен результат
    }

    return [$lat, $lon, $city, $precision,$cleanedAddress];
}

function geocode_by_locationiq($address) {
    GLOBAL $apiKey_locationiq;
	$url = 'https://us1.locationiq.com/v1/search.php?key=' . $apiKey_locationiq . '&q=' . urlencode($address) . '&format=json&addressdetails=1&countrycodes=by';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
	die ($response);
	curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data[0])) {
        $lat = $data[0]['lat'];
        $lng = $data[0]['lon'];
        return [$lat, $lng];
    } else {
        return [null, null]; 
    }
}
	
	
function geocoding_by_yandex ($address): array	// геокодинг с помощью яндекса
{
	
		GLOBAL $yandex_key;
		$lang='RU';
		$url = "https://geocode-maps.yandex.ru/1.x/?geocode=".urlencode($address)."&lang=$lang&apikey=".urlencode($yandex_key);
		$ans = file_get_contents($url);
		
		$res = json_decode(json_encode(simplexml_load_string($ans, "SimpleXMLElement", LIBXML_NOCDATA)),TRUE);
		
		$lon = 0; // на случай отсутствия данных геокодинга
		$lat = 0;
		if (isset($res['GeoObjectCollection']['featureMember'])) 
		{
			if (isset($res['GeoObjectCollection']['featureMember'][0])) 	
				{
					$koor = $res['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
					$city = $res['GeoObjectCollection']['featureMember'][0]['GeoObject']['description'];
					$precision='low';
				}
				else 
				{
					$koor = $res['GeoObjectCollection']['featureMember']['GeoObject']['Point']['pos'];
					$city = $res['GeoObjectCollection']['featureMember']['GeoObject']['description'];
					$precision='hi';
				}
			$k = explode(' ',$koor);
			$lon = $k[0];
			$lat = $k[1];
		}	
		
		//rego(json_encode($res));
		
		return [$lat,$lon];
		
}	