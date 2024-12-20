<?php
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

	header('Content-Type: application/json; charset=UTF-8');
	$link = firstconnect ();
	//[$session_id, $username] = enterregistration ();

	//$search = mb_substr($_GET['search'], 0, 7);

	$search = $_GET['search'];
	if (preg_match('/(--|#|\/\*|\*\/|;|UNION|SELECT|INSERT|UPDATE|DELETE|DROP|TRUNCATE|EXEC|XP_|SLEEP|BENCHMARK|WAITFOR|INTO OUTFILE|LOAD_FILE|OR 1=1|AND 1=1)/i', $search)) 
	{
		file_put_contents('suspicious_queries.log', date("Y-m-d H:i:s") . " HTTP_COOKIE : {$_SERVER['HTTP_COOKIE']}  HTTP_X_CLIENT_IP : {$_SERVER['HTTP_X_CLIENT_IP']} Подозрительный запрос: $search" . PHP_EOL, FILE_APPEND);
		send_warning_telegram('search с IP '.$_SERVER['HTTP_X_CLIENT_IP'].': '.$search);
		$search = '';
	}
		
	$search = trim($search); // Удаляем лишние пробелы
	$search = htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // Экранируем HTML-специальные символы
	if (mb_strlen($search) > 100) $search = mb_substr($search, 0, 100);



	$que = "SELECT name, CONCAT('https://fitokrama.by/art_page.php/', `name_human`) as art, pic_name, price, price_old 
			FROM goods 
			WHERE (name LIKE ? OR art = ? OR barcode = ?) 
			AND goods_groups_id IS NOT NULL 
			AND price > 0 
			ORDER BY prod_30 DESC 
			LIMIT 20";

	// Подготавливаем параметры для запроса
	
	$res = Exec_PR_SQL($link, $que, ['%'.$search .'%', $search, $search]);

	//$res[]['query']=$que;
	exit( json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
