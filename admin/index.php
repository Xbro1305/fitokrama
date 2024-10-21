<?php

require_once __DIR__ . '/../../../varsse.php';

cors();

$link = firstconnect();

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

function ExecSQL($link, $query)
{
    $dataset = $link->query($query);

    if ($dataset === false) {
        file_put_contents('debug_query_errors', date("Y-m-d H:i:s").'  '.$link->error.'   '.$query.PHP_EOL, FILE_APPEND);
    }

    $answer = [];

    if (is_object($dataset)) {
        while (($row = $dataset->fetch_assoc()) != false)
        {
            $answer[] = $row;
        }
    } else {
        $answer = $link->insert_id;
    }

    return $answer;
}

function firstconnect()
{
    GLOBAL $db_host;
    GLOBAL $db_user;
    GLOBAL $db_password;
    GLOBAL $db_name;

    $link = new mysqli($db_host, $db_user, $db_password, $db_name);
    $link->set_charset('utf8mb4');
    if ($link->connect_error) {
        die(json_encode(['status'=>'error', 'message'=>'Connect error ' . $link->connect_error]));
    }

    return $link;
}

function login()
{
    header('Content-Type: application/json; charset=utf-8');

    // require_once __DIR__ . '/../../mnn.php';

    $json_in = json_decode(file_get_contents('php://input'),TRUE);
    [$staff_id, $staff_name, $staff_role] = staff_auth($json_in['email'], $json_in['password']);

    echo json_encode([
        'id' => $staff_id,
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


function staff_auth($login, $password)
{
    GLOBAL $link;

    $que = "SELECT * FROM `staff` WHERE `staff_email`='$login' LIMIT 1;";

    $staffs = ExecSQL($link, $que);

    if (count($staffs) === 0) {
        die (json_encode(['error' => 'Authorization error']));
    }

    if (!password_verify($password, $staffs[0]['password_hash'])) {
        die (json_encode(['error' => 'Authorization error']));
    }

    $staff_role = $staffs[0]['role'];
    $staff_name = $staffs[0]['staff_name'];
    $staff_id = $staffs[0]['id'];
    $que = "UPDATE staff set datetime_last=CURRENT_TIMESTAMP() WHERE id=$staff_id";
    ExecSQL($link, $que);

    return [$staff_id, $staff_name, $staff_role];
}
