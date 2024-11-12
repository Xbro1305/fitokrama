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

	}

	$methods = $json_in['methods'];
	$methods = json_decode('[3]', TRUE); // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	$html = '<div style="display: flex; align-items: flex-start;">';

	$html .= '<div style="margin-right: 20px; text-align: center;">';
	$html .= '<div style="font-size: 48px; font-weight: bold;">' . 'Список на отправку на '.date('d.m.Y H:i'). '</div>';
	$html .= '</div>';
$html .= '</div>';

	$html .= '<div>';
	$html .= '<table border="1" cellspacing="0" cellpadding="10" style="border-collapse: collapse;">';

	foreach ($methods as $method)
	{
		$index = array_search($method, array_column($delivery_partners, 'id'));
		/*echo (json_encode(array_column($delivery_partners, 'id')).PHP_EOL);
		echo $method.PHP_EOL;
		echo (json_encode(array_search($method, array_column($delivery_partners, 'id')).PHP_EOL));

			echo (($index).PHP_EOL);
		echo ($delivery_partners [$index]['name'].PHP_EOL);
		echo ($delivery_partners [$index]['sending_point'].PHP_EOL);
		die;*/
		$delivery_partner = $delivery_partners [$index] ;
		$html .= '<tr>';
		$html .= '<td style="font-size: 18px; font-weight: bold;">' . htmlspecialchars($delivery_partner['name']) . '</td>';
		$html .= '<td style="font-size: 18px; text-align: center;">' . htmlspecialchars($delivery_partner['sending_point_address']) . '</td>';
		$html .= '</tr>';
		foreach ($delivery_partner['orders'] as $order_number)
		{
			$order = all_about_order($order_number['number']);
			$html .= '<tr>';
			$html .= '<td style="font-size: 8px; font-weight: bold;">' . ' ' . '</td>';
			$html .= '<td style="font-size: 28px; font-weight: bold;">' . htmlspecialchars($order['number']) . '</td>';
			$html .= '<td style="font-size: 38px; text-align: center;">' . htmlspecialchars($order['client_name']) . '</td>';
			$html .= '</tr>';
		}
	}
	$html .= '</table>';
	$html .= '</div>';

	$message = 'Список на отправку сформирован';

    exit(json_encode(['message'=>$message,'for_print'=>$html], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
