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
		$html = file_get_contents('pages/order_print_page.html'); // берем шаблон листа на распечатку
		$html = str_replace("[order_number]",'000000',$html);
		
		$html_goods = '';
		$html = cut_fragment($html, '<!-- GOOD_BEGIN -->', '<!-- GOOD_END -->','[goods_table]',$html_good_1);
		$html_goods .= $html_good_1;
			$html_goods = str_replace("[good_name]",'Товар № 1',$html_goods);
			$html_goods = str_replace("[good_qty]",'1',$html_goods);		
		$html_goods .= $html_good_1;
			$html_goods = str_replace("[good_name]",'Товар № 2',$html_goods);
			$html_goods = str_replace("[good_qty]",'2',$html_goods);		
		$html = str_replace("[goods_table]",$html_goods,$html);
		file_put_contents('test_order_print_page.html',$html);	
		send_message_telegram_infobot_by_to_id("Тестовая страница печати сформирована!",$ids);		
		exit (json_encode(['status'=>'ok', 'message'=> 'ok']));
	}
	
	$text = "обратное сообщение на сообщение ".$tx;
			
			
	send_message_telegram_infobot_by_to_id($text,$ids);
		
			
			
			
	exit (json_encode(['status'=>'ok', 'message'=> 'ok']));
	
}

