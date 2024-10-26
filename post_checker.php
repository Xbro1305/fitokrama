<?php
	include_once 'mnn.php';
	include_once 'yandex_methods.php';
	include_once 'dpd_methods.php';
	include_once 'europost_methods.php';


function yandex_checkstate($postcode) 
{
	$response = json_decode(yandex_post ("claims/info?claim_id=$postcode",array(),false),TRUE);
	//die(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	
	
	
	if (!isset($response['status'])) return([null,'no_info']);
	$updated_time = date('Y-m-d H:i:s', strtotime($response['updated_ts']));
	$status = $response['status'];
	return([$updated_time,$status]);
}

function dpd_checkstate($postcode) 
{
	
	$data['clientParcelNr']=$postcode;
	$response = dpd_request('tracing?wsdl','getStatesByClientParcel',$data,'request',false);	// или getStatesByClientOrder ?
	$response = json_decode(json_encode($response),TRUE);
	// расшифровка вроде тут https://docs.google.com/document/d/1TQpP1Ad8P9xfgI1LSJwCLHca3g2hIuXusL0e6vU4MbU/edit?tab=t.0#heading=h.356xmb2 
	if ($response['detail']['WSFault']['code']=='date-before-start') return([null,'waiting_for_delivery']);
	if (!isset($response['states'])) return([null,'no_info']);
	$last_state = array_reduce($response['states'], function ($carry, $item) {  return (isset($carry['transitionTime']) && $carry['transitionTime'] > $item['transitionTime']) ? $carry : $item; });
	$updated_time = date('Y-m-d H:i:s', strtotime($last_state['transitionTime']));
	$status = $last_state['newState'];
	return([$updated_time,$status]);
}

function eur_checkstate($postcode) 
{
	
	$data['PostalItemExternalId']=$postcode;
	$response = europochta_post('Postal.Tracking', $data, false);
	$last_record = $response['Table'][0];
	if (isset($last_record['Error'])) return([null,'no_info']);
	
	$updated_time = date('Y-m-d H:i:s', strtotime($last_record['Timex']));
	$status = $last_record['InfoTrack'];
	if ($status=='Заявка на почтовое отправление зарегистрирована') $status='waiting_for_delivery';
	return([$updated_time,$status]);
}

function belpost_checkstate($postcode) 
{
	
	
	return([NULL,NULL]);
}


$method = explode("/", $_SERVER ["SCRIPT_URL"])[2];	

if ($method=='checkstate') // информация о состоянии посылки
{
	$order_number = $_GET['order_number'];
	$order = all_about_order($order_number);
	if (is_null($order)) die ('Incorrect order_number');
	$delivery_method = $order['delivery_method'];
	
	$post_code = $order['post_code'];
	$track_number = $order['track_number'];
	$internal_postcode = $order['internal_postcode'];
	
	if (in_array($delivery_method, [1]))     	$status = yandex_checkstate	($post_code);
	if (in_array($delivery_method, [2, 4, 5]))  $status = dpd_checkstate	($internal_postcode);
	if (in_array($delivery_method, [3, 7]))     $status = eur_checkstate	($internal_postcode);
	if (in_array($delivery_method, [6]))     	$status = belpost_checkstate($post_code);

	die(json_encode($status));
	
}

if ($method=='imcoming') // входящий вебхук
{
		send_warning_telegram('post_checker 78 incoming    GET '.json_encode($_GET).'   POST   '.json_encode($_POST).'    SERVER  '.json_encode($_SERVER) );
		die;
	
	
}



//die ('incorrect method');


