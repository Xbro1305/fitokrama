<?php
	include 'mnn.php';
	header('Content-Type: application/json; charset=UTF-8');

	$link = firstconnect ();

	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];
	
function mb_strtr($str, $from, $to = null) {
    if (is_array($from)) {
        $keys = array_keys($from);
        foreach ($keys as $key) {
            $from[mb_strtolower($key, 'UTF-8')] = $from[$key];
            unset($from[$key]);
        }
        return strtr(mb_strtolower($str, 'UTF-8'), $from);
    } else {
        $from = mb_strtolower($from, 'UTF-8');
        $to = mb_strtolower($to, 'UTF-8');
        return strtr(mb_strtolower($str, 'UTF-8'), $from, $to);
    }
}
	
function transliterate($text) {
    $translit_map = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
        'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ' ' => '-', '_' => '-', '/' => '-', '&' => 'and', '\'' => '', '@' => 'at',
        '%' => '', '$' => '', ',' => '', '.' => '', '(' => '', ')' => '', '№' => '', '!' => '', '?' => ''
    ];

    // Приведение к нижнему регистру
    $text = mb_strtolower($text, 'UTF-8');
    // Транслитерация
    $text = strtr($text, $translit_map);
    // Удаление недопустимых символов
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    // Замена множественных дефисов на один
    $text = preg_replace('/\-+/', '-', $text);
    // Удаление дефисов в начале и конце строки
    $text = trim($text, '-');
    return $text;
}



function generateMetaKeywords($name) {
    $stop_words = ['по', 'в', 'и', 'среде', 'активаторе', 'г', 'мг', 'капсулы'];
    $words = preg_split('/\s+/', $name);

    // Фильтруем слова длиннее 4 символов и не являющиеся стоп-словами
    $filtered_words = array_filter($words, function($word) use ($stop_words) {
        $word = trim($word, ",.№"); // Убираем лишние символы
        return mb_strlen($word) > 4 && !in_array(mb_strtolower($word), $stop_words);
    });

    // Ограничиваем количество слов до 7
    $filtered_words = array_slice($filtered_words, 0, 7);
    $meta_keywords_content = implode(", ", $filtered_words);
	
    return $meta_keywords_content;
}

if ($method == 'meta_keywords_content') 
{
	$que = "SELECT id, name, meta_keywords_content FROM goods WHERE CHAR_LENGTH(meta_keywords_content) < 5 OR meta_keywords_content IS NULL";
	$goods = Exec_PR_SQL($link, $que, []);

	foreach ($goods as $good) 
	{
		$id = $good['id'];
		$name = mb_substr(trim($good['name']), 0, 50);

		echo $name.PHP_EOL;
		$meta_keywords_content = generateMetaKeywords($good['name']);
		echo $meta_keywords_content.PHP_EOL;

		$update_query = "UPDATE goods SET meta_keywords_content = ? WHERE id = ?";
		Exec_PR_SQL($link, $update_query, [$meta_keywords_content, $id]);

	}
	exit( json_encode(['result'=>'ok'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	
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
	echo $name.PHP_EOL;
	echo $name_human.PHP_EOL;
	
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

	exit( json_encode(['result'=>'ok'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));	
}

if ($method == 'site_map_create') 
{
    $site_map = [];

    // Запрос товаров
    $que = "SELECT name, name_human 
            FROM goods 
            WHERE goods_groups_id IS NOT NULL 
            AND price > 0";
    $goods = Exec_PR_SQL($link, $que, []);

    // Добавляем товары в карту сайта
    foreach ($goods as $good) 
    {
		$link = 'https://fitokrama.by/art_page.php/' . urlencode($good['name_human']);

        $site_map[] = [
            'loc' => $link,
            'changefreq' => 'daily',
            'priority' => '0.8'
        ];
    }

    // Добавляем статические страницы
    $static_pages = [
        ['loc' => 'https://fitokrama.by/index_page.php', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => 'https://fitokrama.by/textpage.php?page=contacts', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['loc' => 'https://fitokrama.by/textpage.php?page=payments_and_delivery', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['loc' => 'https://fitokrama.by/catalog_page.php', 'changefreq' => 'weekly', 'priority' => '0.8']
    ];

    $site_map = array_merge($site_map, $static_pages);

    // Генерация XML-карты сайта
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

    foreach ($site_map as $url) 
    {
        $url_element = $xml->addChild('url');
        $url_element->addChild('loc', htmlspecialchars($url['loc'], ENT_QUOTES | ENT_XML1, 'UTF-8'));
        $url_element->addChild('changefreq', $url['changefreq']);
        $url_element->addChild('priority', $url['priority']);
    }

    // Сохранение XML-файла
    $xml_file_path = __DIR__ . '/sitemap.xml';
    $xml->asXML($xml_file_path);

    exit(json_encode(['result' => 'ok', 'message' => 'Sitemap создан успешно', 'file' => $xml_file_path], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}







