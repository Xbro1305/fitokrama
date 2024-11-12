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

$goods = Exec_PR_SQL($link, "SELECT * FROM `goods` WHERE `art`=? LIMIT 1",[$json_in['product']['art']]);

if (count($goods) === 0) {
    die (json_encode([
        'error' => 'Товар не найден',
    ]));
}

$sql = "UPDATE `goods` SET
    `name` = ?,
    `description_short` = ?,
    `description_full` = ?,
    `price` = ?,
    `price_old` = ?,
    `qty` = ?,
    `barcode` = ?,
    `producer` = ?,
    `producer_country` = ?,
    `cat` = ?,
    `subcat` = ?,
    `koef_ed_izm` = ?,
    `ed_izm_name` = ?
    WHERE `art` = ? LIMIT 1";

$params = [
    $json_in['product']['name'],
    $json_in['product']['description_short'],
    $json_in['product']['description_full'],
    $json_in['product']['price'],
    $json_in['product']['price_old'],
    $json_in['product']['qty'],
    $json_in['product']['barcode'],
    $json_in['product']['producer'],
    $json_in['product']['producer_country'],
    $json_in['product']['cat'],
    $json_in['product']['subcat'],
    $json_in['product']['koef_ed_izm'],
    $json_in['product']['ed_izm_name'],
    $json_in['product']['art']
];

Exec_PR_SQL($link, $sql, $params);

exit(json_encode([
    'message' => 'Товар успешно обновлён',
]));
