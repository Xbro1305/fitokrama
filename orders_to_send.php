<?php
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode
	include 'mnn.php';

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        }

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }

        exit(0);
    }

	header('Content-Type: application/json');
	//header('Content-Type: text/html; charset=UTF-8');
	//header("Access-Control-Allow-Origin: $http_origin");
	$link = firstconnect ();

	$json_in = json_decode(file_get_contents("php://input"),TRUE);
	[$staff_id,$staff_name,$staff_role] = staff_auth($json_in['staff_login'],$json_in['staff_password']);

	if ($staff_role!='store' && $staff_role!='main') die (json_encode(['error'=>'No rights']));

	$que = "SELECT id,name,prefix,logo,sending_point_address,sending_point_lat,sending_point_lng FROM `delivery_partners`";
	$delivery_partners = Exec_PR_SQL($link,$que,[]);

	foreach ($delivery_partners as $key=>&$delivery_partner)
	{
		$que = "SELECT number FROM `orders` WHERE delivery_method=? AND datetime_paid IS NOT NULL AND datetime_assembly IS NOT NULL AND datetime_cancel IS NULL AND datetime_sent IS NULL ORDER BY datetime_assembly_order,datetime_create";
		$orders = Exec_PR_SQL($link,$que,[$delivery_partner['id']]);
		$delivery_partner['orders'] = $orders;
		if (count($orders)==0) unset($delivery_partners[$key]);
	}

	exit (json_encode(array_values($delivery_partners), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
