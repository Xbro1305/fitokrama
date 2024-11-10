<?php
	require_once __DIR__ . '/../mnn.php';

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

    $link = firstconnect();
	tst();

    function tst()
    {
        $link = firstconnect();
		$json_in = json_decode(file_get_contents('php://input'),TRUE);
        [$staff_id, $staff_name, $staff_role] = staff_auth($json_in['email'], $json_in['password']);

        if (!$staff_role) {
            die (json_encode([
                'error' => 'No rights',
            ]));
        }

        $orders = array();
		$from = $json_in['date_from'];
        $to = $json_in['date_to'];
		
		
        $que = "SELECT * FROM `orders` WHERE datetime_create>='$from' AND datetime_create<='$to'";
		$records = ExecSQL($link, $que);
		foreach ($records as $order1)
			$orders[] = all_about_order($order1['number']);

        exit(json_encode([
            'orders' => $orders,
        ]));
    }
