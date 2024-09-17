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
	$isKioskPrintingEnabled = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'adminpage') !== false;

	// if (!$isKioskPrintingEnabled) die (json_encode(['error'=>'Launch your browser with permissions for auto-printing']));

	$orders = ExecSQL($link,"SELECT * FROM `orders` WHERE datetime_paid IS NOT NULL AND datetime_assembly IS NULL AND datetime_cancel IS NULL AND datetime_order_print IS NULL ORDER BY datetime_assembly_order,datetime_create LIMIT 1");
	if (count($orders)==0) exit ();
	//ExecSQL($link,"UPDATE `orders` SET datetime_order_print=CURRENT_TIMESTAMP() WHERE id=".$orders[0]['id']);	/// !!!!!!!!!!!
	$order = all_about_order($orders[0]['number']);
	$order['qrcode']='002-/'.$order['number'];
	$qrTempDir = sys_get_temp_dir(); // Используем временную директорию для хранения QR-кода
	$qrFileName = $qrTempDir . '/qrcode_' . $order['number'] . '.png';
	QRcode::png($order['qrcode'], $qrFileName, QR_ECLEVEL_L, 10);

	// Начинаем формирование HTML
	$html = '<div style="display: flex; align-items: flex-start;">';

	// Слева - номер заказа и QR-код
	$html .= '<div style="margin-right: 20px; text-align: center;">';
	$html .= '<div style="font-size: 48px; font-weight: bold;">' . $order['number'] . '</div>';
	$html .= '<img src="data:image/png;base64,' . base64_encode(file_get_contents($qrFileName)) . '" alt="QR Code">';
	$html .= '</div>';

	// Справа - таблица товаров
	$html .= '<div>';
	$html .= '<table border="1" cellspacing="0" cellpadding="10" style="border-collapse: collapse;">';
	foreach ($order['goods'] as $item) {
		$html .= '<tr>';
		$html .= '<td style="font-size: 18px; font-weight: bold;">' . htmlspecialchars($item['name']) . '</td>';
		$html .= '<td style="font-size: 18px; text-align: center;">' . htmlspecialchars($item['qty']) . '</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	$html .= '</div>';

	$html .= '</div>';
	//die($html);
    exit(json_encode(['html_print'=>$html]));
