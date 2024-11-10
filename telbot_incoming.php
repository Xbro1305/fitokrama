<?php
	include 'mnn.php';
	header("Access-Control-Allow-Origin: $http_origin");

function send_message_telegram_infobot_by_to_id($text,$id) // отправить сообщение от инфобота на указанный id
{
	GLOBAL $telegram_warning_token;
	$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.telegram.org/bot".$telegram_warning_token."/sendMessage?chat_id=".$id."&text=".urlencode($text),
			CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 4,CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array( "Content-Type: application/JSON"	),
		));
		
	$response = curl_exec($curl);
	return $response;

}

	$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	
	if ($method=='incoming') // входящее сообщение бота
{
//	$link = firstconnect ();
	$data = json_decode(file_get_contents('php://input'),TRUE);
	//send_warning_telegram(json_encode($data));
	
	$tx = $data['message']['text'];
	$ids = $data['message']['from']['id'];
	
	if ($tx=='/testprint')	// необходимо сделать тестовую отправку на браузер автопечати
	{
		
		
		
	}
	
	$text = "обратное сообщение на сообщение ".$tx;
			
			
	send_message_telegram_infobot_by_to_id($text,$ids);
		
			
			
			
	exit (json_encode(['status'=>'ok', 'message'=> 'ok']));
	
}

