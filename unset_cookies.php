<?php
	include 'mnn.php'; 
	header("Access-Control-Allow-Origin: $http_origin");

	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	


	foreach ($_COOKIE as $key => $value) 
	{
    // Генерация команд для удаления куки на фронте
		setcookie($key, '', time() - 3600, '/', '', false, true);

		
		echo "<script>document.cookie = '{$key}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'</script>";
	}


	setcookie('session_id', '6c72d83a5c78af745bacfeadc00c8720', time() + (365 * 24 * 60 * 60), '/');

	header("Access-Control-Allow-Origin: $http_origin");
	

	exit (json_encode(['status'=>'ok']));	

?>