<?php
	include 'mnn.php';


if (!isset($_GET['query'])) die;
    $query = ($_GET['query']);

    GLOBAL $dadata_key;
    $apiUrl = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";
	
    $data = array(
        "query" => $query,
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
	$res = array();
	
    $suggestions = array();
    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['suggestions'])) 
		{
            foreach ($result['suggestions'] as $suggestion) 
			$res[] = [ 'address' => $suggestion['value'],
				'lat' => $suggestion['data']['geo_lat'],
				'lng' => $suggestion['data']['geo_lon'] ];
        }
    }
	echo json_encode($res);


?>
