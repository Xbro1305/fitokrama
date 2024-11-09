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
	$que = "SELECT name, CONCAT('https://fitokrama.by/art_page.php?art=',art) as art, pic_name, price,price_old FROM goods WHERE (name like '%$search%' or art='$search' or barcode='$search') AND goods_groups_id IS NOT NULL AND price>0 ORDER BY prod_30 DESC LIMIT 20;";
	$res = ExecSQL($link,$que);
	//$res[]['query']=$que;
	exit( json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
