<?php

cors();

$method = $_GET['method'] ?? '';

switch ($method) {
    case 'login':
        login();
        break;
    case 'user':
        user();
        break;
    case 'order_print_for_assembly':
        order_print_for_assembly();
        break;
    default:
        header('HTTP/1.0 404 not found');
        exit();
}

function login()
{
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../mnn.php';
    $link = firstconnect ();
    $json_in = json_decode(file_get_contents('php://input'),TRUE);
    [$staff_id, $staff_name, $staff_role] = staff_auth($json_in['email'], $json_in['password']);

    echo json_encode([
        'name' => $staff_name,
        'role' => $staff_role,
    ]);
}

function user()
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['user' => ['email' => 'qwerty@qwert.ru']]);
}

function cors()
{
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
}

function order_print_for_assembly()
{
    require_once __DIR__ . '/../../order_print_for_assembly.php';
}
