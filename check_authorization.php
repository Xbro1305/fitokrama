<?php
	include 'mnn.php';
	header('Content-Type: application/json');
	$link = firstconnect ();
	[$session_id, $username, $cart, $client_id] = enterregistration ();	

	  
function checkTelegramAuthorization($auth_data) 
{
  Global $telegram_mainbot_token;
  $check_hash = $auth_data['hash'];
  unset($auth_data['hash']);
  $data_check_arr = [];
  foreach ($auth_data as $key => $value) {
    $data_check_arr[] = $key . '=' . $value;
  }
  sort($data_check_arr);
  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash('sha256', $telegram_mainbot_token, true);
  $hash = hash_hmac('sha256', $data_check_string, $secret_key);
  if (strcmp($hash, $check_hash) !== 0)
    die (json_encode(['status'=>'error', 'message'=> 'No or error telegram DATA']));	
  
  if ((time() - $auth_data['auth_date']) > 86400) 
    die (json_encode(['status'=>'error', 'message'=> 'old DATA']));	
  
  $username = 'https://t.me/'.$auth_data['username'];
  $email_confirm_detailed = 'by telegram '.json_encode($auth_data);
  return [$username,$email_confirm_detailed];
}

function checkEmailAuthorization($getdata) 
{
	GLOBAL $link;
	GLOBAL $client_id;
	$code = $getdata['code'];
	$longcode = $getdata['longcode'];
	
	if (isset($longcode) AND ($longcode!=NULL) AND (strlen($longcode)>5))
		$record = Exec_PR_SQL($link, "SELECT * FROM email_confirm WHERE longcode=? AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)"
					,[$longcode]);
	else
		$record = Exec_PR_SQL($link, "SELECT * FROM email_confirm WHERE client_id=? AND code=? AND datetime>DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 24 hour)"
					,[$client_id,$code]);
	
	
	
	if (count($record)==0) 
		die (json_encode(['status'=>'error', 'message'=> 'Неверный код!']));	
	
	$username = $record[0]['email'];
	$email_confirm_detailed = 'by_code '.str_replace('"','',str_replace("'","",$que));
  
    return [$username,$email_confirm_detailed];
}



function jwkToPem($jwk) {
    $modulus = base64UrlDecode($jwk['n']);
    $exponent = base64UrlDecode($jwk['e']);

    $modulus = bin2hex($modulus);
    $exponent = bin2hex($exponent);

    $modulus = pack('H*', '02' . dechex(strlen($modulus) / 2) . $modulus);
    $exponent = pack('H*', '02' . dechex(strlen($exponent) / 2) . $exponent);

    $publicKey = pack('H*', '30' . dechex(strlen($modulus . $exponent) + 4) . '02' . dechex(strlen($modulus)) . $modulus . '02' . dechex(strlen($exponent)) . $exponent);

    $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n" .
        chunk_split(base64_encode($publicKey), 64, "\n") .
        "-----END PUBLIC KEY-----\n";
    return $publicKeyPem;
}

function checkGoogleAuthorization($postparams) 
{
    GLOBAL $google_auth_client_id;

    $credential = $postparams['credential'];
        
    if (count(explode('.', $credential)) != 3) return null;
    list($headerB64, $payloadB64, $signatureB64) = explode('.', $credential);

    $header = json_decode(base64UrlDecode($headerB64), true);
    $payload = json_decode(base64UrlDecode($payloadB64), true);
    $signature = base64UrlDecode($signatureB64);

    $publicKeys = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v3/certs'), true);
    
    $publicKeyPem = null;
    foreach ($publicKeys['keys'] as $key) {
        if ($key['kid'] == $header['kid']) {
            $publicKeyPem = jwkToPemForApple($key);
            break;
        }
    }
    
    if ($publicKeyPem === null) {
        die ('error 4'); // Публичный ключ не найден
    }

    // Проверяем подпись
    $data = "$headerB64.$payloadB64";
    
	$publicKey = openssl_pkey_get_public($publicKeyPem);
		
    $verified = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    
    if ($verified === 1) {
        $username = $payload['email'];
        $email_confirm_detailed = 'by google '.json_encode($payload);
        return [$username, $email_confirm_detailed];
    } else {
        die ('error 5'); // Подпись неверна
    }
}

function base64UrlDecodeAlternative($input) {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $input) . str_repeat('=', 3 - (3 + strlen($input)) % 4));
}

function base64UrlDecode2($input) {
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $addlen = 4 - $remainder;
        $input .= str_repeat('=', $addlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

function jwkToPemForApple($jwk) {
    $n = base64UrlDecode2($jwk['n']);
    $e = base64UrlDecode2($jwk['e']);

    // Create ASN.1 structures for modulus and exponent
    $modulus = "\x02" . encodeLength(strlen($n)) . $n;
    $exponent = "\x02" . encodeLength(strlen($e)) . $e;

    // Create the public key sequence
    $publicKeySeq = "\x30" . encodeLength(strlen($modulus) + strlen($exponent)) . $modulus . $exponent;

    // Create the bit string
    $bitString = "\x03" . encodeLength(strlen($publicKeySeq) + 1) . "\x00" . $publicKeySeq;

    // Create the final sequence
    $seq = "\x30" . encodeLength(strlen($bitString) + 15) . "\x30\x0D\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01\x05\x00" . $bitString;

    $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($seq), 64, "\n") . "-----END PUBLIC KEY-----\n";

    return $pem;
}

function encodeLength($length) {
    if ($length <= 0x7F) {
        return chr($length);
    } else {
        $temp = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($temp)) . $temp;
    }
}

function checkAppleAuthorization($postparams) {
    GLOBAL $apple_auth_client_id;

    $code = $postparams['code'];
    $credential = $postparams['id_token'];
    
    if (count(explode('.', $credential)) != 3) return null;
    list($headerB64, $payloadB64, $signatureB64) = explode('.', $credential);

    $header = json_decode(base64UrlDecode2($headerB64), true);
    $payload = json_decode(base64UrlDecode2($payloadB64), true);
	
    $signature = base64UrlDecode2($signatureB64);
    
    // Отладочная информация
 
    $publicKeys = json_decode(file_get_contents('https://appleid.apple.com/auth/keys'), true);
    
    $publicKeyPem = null;
    foreach ($publicKeys['keys'] as $key) {
        if ($key['kid'] == $header['kid']) {
            $publicKeyPem = jwkToPemForApple($key);
            break;
        }
    }

    if ($publicKeyPem === null) {
        die('Error 1');
    }

    // Убедимся, что OpenSSL может прочитать ключ
    $publicKey = openssl_pkey_get_public($publicKeyPem);
    if ($publicKey === false) {
        die('Error 2');
    }

    $data = "$headerB64.$payloadB64";
    $verified = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    openssl_free_key($publicKey);


    if ($verified !== 1) {
        die('Error 3');
    }

    // Проверка полей токена
    if ($payload['iss'] !== 'https://appleid.apple.com') {
        die('Error 4');
    }

    if ($payload['aud'] !== $apple_auth_client_id) {
        die('Error 5');
    }

    if ($payload['exp'] < time()) {
        die('Error 6');
    }

    $username = $payload['email'];

    
    $email_confirm_detailed = 'by apple ' . json_encode($payload);
    return [$username, $email_confirm_detailed];
}



  $postparams = json_decode(file_get_contents("php://input"),TRUE);
	if (isset($_GET['hash'])) [$username,$email_confirm_detailed] = checkTelegramAuthorization($_GET);								// telegram авторизация
	else
	  
	if (isset($_GET['code']) OR isset($_GET['longcode'])) [$username,$email_confirm_detailed] = checkEmailAuthorization($_GET);		// e-mail авторизация
	else
	
	if (isset($postparams['credential'])) [$username,$email_confirm_detailed] = checkGoogleAuthorization($postparams);				// google авторизация
	else
	
	if ($postparams['state']=='initial_state') [$username,$email_confirm_detailed] = checkAppleAuthorization($postparams);			// apple авторизация
	else     
	
		die (json_encode(['status'=>'error', 'message'=> 'Error DATA']));						
								
	$message = 'Отлично, мы вас запомнили!';
	$anothers = Exec_PR_SQL($link,"SELECT * from clients WHERE client_email=?",[$username]);
	if (count($anothers)>0)			// есть другие корзины этого пользователя
	{
		foreach ($anothers as $another_1)	// перебираем все такие корзины
		{
			$que = "SELECT * FROM carts_goods WHERE client_id=?";
			$res = Exec_PR_SQL($link,$que,[$another_1['id']]);
			if (count($res)>0) $message = $message.' Обратите внимание: корзина дополнена ранее сохраненными товарами!';
						
			$que = "UPDATE carts_goods SET client_id=? WHERE client_id=?";
			Exec_PR_SQL($link,$que,[$client_id,$another_1['id']]);
			$que = "UPDATE clients SET client_email=? перенесен WHERE id=?";
			Exec_PR_SQL($link,$que,[$username,$another_1['id']]);
		}
	}
  
  $jwt = jwt_create($username);
  $que = "UPDATE clients SET client_email=?, datetime_email_confirmed=CURRENT_TIMESTAMP(), email_confirm_detailed = ? WHERE id=?";
  Exec_PR_SQL($link,$que,[$username,$email_confirm_detailed,$client_id]);
  
  $que = "SELECT * FROM staff WHERE staff_email=? AND role IS NOT NULL LIMIT 1";
  $staffs = Exec_PR_SQL($link,$que,[$username]);
  if (count($staffs)>0)	// видим сотрудника компании
  {
	$que = "UPDATE staff SET datetime_last=CURRENT_TIMESTAMP() WHERE id=?";
	Exec_PR_SQL($link,$que,[$staffs[0]['id']]);
	$staff_level = $staffs[0]['role'];
    $jwt_staff = jwt_create_staff($username,$staff_level);
	setcookie('jwt_staff', $jwt_staff, [
    'expires' => time() + (30 * 24 * 60 * 60),
    'path' => '/',
    'secure' => true,       // Передавать только по HTTPS
    'httponly' => true,     // Запретить доступ через JavaScript
    'samesite' => 'Strict'  // Защита от CSRF
]);

  } 
  
  
  
  setcookie('jwt', $jwt, time() + (365 * 24 * 60 * 60), '/');

  exit (json_encode(['status'=>'ok', 'JWT'=> $jwt, 'session_id'=>$session_id, 'message'=> $message]));	

?>