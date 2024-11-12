<?php
	include 'mnn.php';
	include 'geocoding.php';
	header('Content-Type: application/json');
	

	
	$link = firstconnect ();
	$dps = Exec_PR_SQL($link,"SELECT * FROM delivery_points WHERE lat=0 OR lon=0",[]);
	foreach ($dps as $dp)
	{	
		$address = $dp['address'];
		
		[$lat,$lon] = geocoding_by_yandex($address);
		echo "      # $address   $lat,$lon".PHP_EOL;
		
		$que = "UPDATE delivery_points SET lat=?, lon=? WHERE id=?";
		echo $que.PHP_EOL;
	
	// Exec_PR_SQL($link,$que,[$lat,$lon,$dp['id']]);
		
		
		
	}
	
	


