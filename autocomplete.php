<?php
	include 'mnn.php';
	global $dadata_key;
	$query = $_GET['query'];
	$url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Token ' . $dadata_key
    ];

    $data = [
        'query' => $query,
        'count' => 5,
        'locations' => [
            'country_iso_code' => 'BY'
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    die($response);
