<?php
	include 'mnn.php';
	header('Content-Type: application/json; charset=UTF-8');

	$link = firstconnect ();

	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];
	
function transliterate($text) {
    $translit_map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
        'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ' ' => '-', '_' => '-', '/' => '-', '&' => 'and', '\'' => ''
    ];
    return strtr($text, $translit_map);
}

if ($method == 'human_name_update') 
{

	$que = "SELECT id, name FROM goods WHERE CHAR_LENGTH(name_human) < 5 OR name_human IS NULL";
	$goods = Exec_PR_SQL($link, $que, []);

	foreach ($goods as $good) 
	{
		$id = $good['id'];
		$name = mb_substr(trim($good['name']), 0, 50);

		// Транслитерация имени
		$name_human = transliterate($name);

		// Проверяем на уникальность
		$unique_name = $name_human;
		$suffix = 1;
		while (true) {
			$check_query = "SELECT COUNT(*) AS cnt FROM goods WHERE name_human = ?";
			$result = Exec_PR_SQL($link, $check_query, [$unique_name]);
			if ($result[0]['cnt'] == 0) {
				break;
			}
			// Добавляем суффикс для уникальности
			$unique_name = mb_substr($name_human, 0, 47) . '-' . $suffix;
			$suffix++;
		}

		// Обновляем запись
		$update_query = "UPDATE goods SET name_human = ? WHERE id = ?";
		Exec_PR_SQL($link, $update_query, [$unique_name, $id]);
	}

	echo "Заполнение поля name_human завершено.";

	
	
	
	
	exit( json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	
}






