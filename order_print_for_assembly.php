<?php
	require_once '../phpqrcode/qrlib.php'; // Подключение библиотеки phpqrcode
	include 'mnn.php';
	
	$next_delay_sec = 10;

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

	if (file_exists('test_order_print_page.html')) // висит задание на печать тестовой страницы
	{
		//send_warning_telegram('1');
		$html = file_get_contents('test_order_print_page.html'); 
		unlink('test_order_print_page.html');
		exit(json_encode(['html_print'=>$html, 'next_delay_sec'=>$next_delay_sec ]));
	}
	


	$orders = Exec_PR_SQL($link,"SELECT * FROM `orders` WHERE datetime_paid IS NOT NULL AND datetime_assembly IS NULL AND datetime_cancel IS NULL AND datetime_order_print IS NULL ORDER BY datetime_assembly_order,datetime_create LIMIT 1",[]);
	if (count($orders)==0) exit (json_encode(['massage'=>'no_for_print',  'next_delay_sec'=>$next_delay_sec]));
	Exec_PR_SQL($link,"UPDATE `orders` SET datetime_order_print=CURRENT_TIMESTAMP() WHERE id=?",[$orders[0]['id']]);	
	$order = all_about_order($orders[0]['number']);
	$order['qrcode']='002-/'.$order['number'];
	$qrTempDir = sys_get_temp_dir(); // Используем временную директорию для хранения QR-кода
	$qrFileName = $qrTempDir . '/qrcode_' . $order['number'] . '.png';
	QRcode::png($order['qrcode'], $qrFileName, QR_ECLEVEL_L, 10);

	// Начинаем формирование HTML
	$html = file_get_contents('pages/order_print_page.html'); // берем шаблон листа на распечатку
	$html = str_replace("[order_number]",$order['number'],$html);
	$html = str_replace("[qr_data]",base64_encode(file_get_contents($qrFileName)),$html);

	$html_goods = '';
	$html = cut_fragment($html, '<!-- GOOD_BEGIN -->', '<!-- GOOD_END -->','[goods_table]',$html_good_1);
	foreach ($order['goods'] as $item) 
	{
			$html_goods .= $html_good_1;
			$html_goods = str_replace("[good_name]",$item['name'],$html_goods);
			$html_goods = str_replace("[good_qty]",$item['qty'],$html_goods);		
	}
	
	$html = str_replace("[goods_table]",$html_goods,$html);

    exit(json_encode(['html_print'=>$html, 'next_delay_sec'=>$next_delay_sec ]));
