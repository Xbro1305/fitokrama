<?php
	include 'mnn.php';


	header('Content-Type: application/json; charset=UTF-8');
	$link = firstconnect ();
	//[$session_id, $username] = enterregistration ();	
	
	$search = mb_substr($_GET['search'], 0, 7);
	$res = ExecSQL($link,"SELECT name, CONCAT('https://fitokrama.by/art_page.php?art=',art) as art FROM goods WHERE name like '%$search%' or art='$search' or barcode='$search' LIMIT 20;");
	
	exit( json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
