<?php

require_once __DIR__ . '/mnn.php';

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
$json_in = json_decode(file_get_contents("php://input"),TRUE);
[$staff_id,$staff_name,$staff_role] = staff_auth($json_in['staff_login'], $json_in['staff_password']);

if ($staff_role !== 'store' && $staff_role !== 'main') {
    die (json_encode([
        'error' => 'No rights',
    ]));
}

if (!isset($json_in['number']) || empty($json_in['number'])) {
    die(json_encode(['error' => 'Order number is missing']));
}


$que = "SELECT * FROM `orders` WHERE `number`=? AND `datetime_paid` IS NOT NULL AND `datetime_assembly` IS NULL AND `datetime_cancel` IS NULL LIMIT 1";
$orders = Exec_PR_SQL($link,$que,[$json_in['number']]);

if (count($orders) === 0) {
    die (json_encode([
        'error' => 'Заказ не найден',
    ]));
}

$order = all_about_order($orders[0]['number'], 'all_info');

exit(json_encode([
    'order' => $order,
]));
