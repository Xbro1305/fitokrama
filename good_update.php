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

$goods = ExecSQL($link, "SELECT * FROM `goods` WHERE `art`='{$json_in['product']['art']}' LIMIT 1");

if (count($goods) === 0) {
    die (json_encode([
        'error' => 'Товар не найден',
    ]));
}

$name = mysqli_real_escape_string($link, $json_in['product']['name']);
$description_short = mysqli_real_escape_string($link, $json_in['product']['description_short']);
$description_full = mysqli_real_escape_string($link, $json_in['product']['description_full']);

$sql = "UPDATE `goods` SET
                `name`='$name'
                `description_short`='$description_short'
                `description_full`='$description_full'
                `price`='{$json_in['product']['price']}'
                `price_old`='{$json_in['product']['price_old']}'
                `qty`='{$json_in['product']['qty']}'
                `barcode`='{$json_in['product']['barcode']}'
                `producer`='{$json_in['product']['producer']}'
                `producer_country`='{$json_in['product']['producer_country']}'
                `cat`='{$json_in['product']['cat']}'
                `subcat`='{$json_in['product']['subcat']}'
                `koef_ed_izm`='{$json_in['product']['koef_ed_izm']}'
                `ed_izm_name`='{$json_in['product']['ed_izm_name']}'
                WHERE `art`='{$json_in['product']['art']}' LIMIT 1";

ExecSQL($link, $sql);

exit(json_encode([
    'message' => 'Товар успешно обновлён',
]));
