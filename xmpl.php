<?php
header('Content-Type: application/json');

$json_in = json_decode(file_get_contents("php://input"), TRUE) ?? []; // взять пришедший payload (POST-параметры) или пустой массив
$input_data = array_merge($json_in, $_GET); // добавить пришедшие query (GET-параметры)

$result = array();	// место для результата

foreach ($input_data as $parameter) {			// перебираем все параметры
    if (is_numeric($parameter)) {				
        $result[] = $parameter * 2;				// если число, то умножаем на 2
    } else {
        $result[] = $parameter;					// иначе оставляем как есть
    }
}

exit(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	// красиво выводим весь JSON -результат
?>
