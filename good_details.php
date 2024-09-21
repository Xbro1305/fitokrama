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

if ($staff_role !== 'buyer' && $staff_role !== 'main' && $staff_role !== 'manager') {
    die (json_encode([
        'error' => 'No rights',
    ]));
}

$goods = ExecSQL($link, "SELECT * FROM `goods` WHERE `art`='{$json_in['art']}' LIMIT 1");

if (count($goods) === 0) {
    die (json_encode([
        'error' => 'Товар не найден',
    ]));
}

exit(json_encode([
    'good' => $goods[0],
]));
