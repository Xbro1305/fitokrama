<?php
	include_once 'mnn.php';

function autocomplete_dadata($address)	
{
	GLOBAL $dadata_key;
    $apiUrl = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";
	
    $data = array(
        "query" => $address,
        "count" => 10,
        "locations" => array(
            array("country_iso_code" => "BY")
        )
    );

    $options = array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Token " . $dadata_key
        ),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    );

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}

function autocomplete_ApiDQ($address)	
{
	$token_ApiDQ='YTg1YmZiZDI1MDA0MmQ4YjNjZDZlNzNmMDk1YzgwMGM=';
	//GLOBAL $token_ApiDQ
	;
    $apiUrl = "https://api.apidq.io/v1/suggest/address";
	$data['query'] = $address;
	$data['countryCode'] = 'BY';
	
    
    $options = array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Accept: application/json",
			"Authorization: YTg1YmZiZDI1MDA0MmQ4YjNjZDZlNzNmMDk1YzgwMGM="   // Добавляем заголовок Authorization
            
        ),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    );

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}

function city_by_address_dadata($address)		// вернуть город и координату по адресу
{
	$res = json_decode(autocomplete_dadata($address),TRUE);
	//echo (json_encode($res).PHP_EOL);
	if (isset($res['suggestions'][0]['data']['settlement'])) 
		$city = $res['suggestions'][0]['data']['settlement'];
	else
		if (isset($res['suggestions'][0]['data']['city'])) 
		$city = $res['suggestions'][0]['data']['city'];
	if (isset($res['suggestions'][0]['data']['geo_lat'])) $lat = $res['suggestions'][0]['data']['geo_lat'];
	if (isset($res['suggestions'][0]['data']['geo_lon'])) $lng = $res['suggestions'][0]['data']['geo_lon'];
	if (isset($res['suggestions'][0]['data']['postal_code'])) $index = $res['suggestions'][0]['data']['postal_code'];

	//die(json_encode(['city'=>$city,'lat'=>$lat,'lng'=>$lng, 'index'=>$index]));

	return ['city'=>$city,'lat'=>$lat,'lng'=>$lng, 'index'=>$index];		
}

if (isset($_GET['query'])) 
{
    $query = ($_GET['query']);

    $suggestions = array();
    $response = autocomplete_dadata($query);
        $result = json_decode($response, true);
        if (isset($result['suggestions'])) 
		{
            foreach ($result['suggestions'] as $suggestion) 
			$res[] = [ 'address' => str_replace('Беларусь,','',$suggestion['value']),
				'lat' => $suggestion['data']['geo_lat'],
				'lng' => $suggestion['data']['geo_lon'],
				'postal_code' => $suggestion['data']['postal_code']				];
        }
    
	exit( json_encode($res));
}

?>

