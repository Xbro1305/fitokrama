<?php

function get_content_with_curl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("HTTP Code $http_code при запросе: $url");
        return null;
    }

    return $data;
}

include_once  'mnn.php'; 
header('Content-Type: application/json');
$link = firstconnect();

$current_datetime = new DateTime();
$yesterday_datetime = (clone $current_datetime)->sub(new DateInterval('P1D'));

$goods = Exec_PR_SQL($link, "
    SELECT * 
    FROM goods 
    LEFT JOIN goods_ftb ON goods.art_f = goods_ftb.good_art_ftb 
    WHERE datetime_update < NOW() - INTERVAL 1 DAY 
       OR datetime_update IS NULL 
       OR goods_ftb.good_art_ftb IS NULL 
    LIMIT 15;
", [],false,true);

foreach ($goods as $good) {
    $art = $good['art'];
    $art_f = $good['art_f'];

    $goods_ftb_record = Exec_PR_SQL($link, "SELECT * FROM goods_ftb WHERE good_art = ?", [$art]);
    if ($goods_ftb_record == NULL) {
        Exec_PR_SQL($link, "INSERT INTO goods_ftb (good_art, good_art_ftb) VALUES (?, ?)", [$art, $art_f]);
    }

    $goods_ftb_record1 = Exec_PR_SQL($link, "SELECT * FROM goods_ftb WHERE good_art = ?", [$art])[0];
    $goods_ftb_id = $goods_ftb_record1['id'];
    $goods_ftb_datetime = $goods_ftb_record1['datetime_update'];

    if ($goods_ftb_datetime != NULL) {
        $goods_ftb_datetime = new DateTime($goods_ftb_datetime);
    }

    if ($goods_ftb_record != NULL && $goods_ftb_datetime != NULL && $goods_ftb_datetime > $yesterday_datetime && $goods_ftb_record1['price_ftb'] > 0) {
        continue;
    }

    $site_ftb = null;
    if ($goods_ftb_record != NULL) {
        $site_ftb = get_content_with_curl($goods_ftb_record1['link_ftb']);
    }

    if ($goods_ftb_record == NULL || $site_ftb == NULL || strlen($site_ftb) < 100) {
        $url_for_search = "https://fito.by/wp-admin/admin-ajax.php?action=flatsome_ajax_search_products&query=" . $art_f;
        $res_search_string = get_content_with_curl($url_for_search);
        $res_search_string = preg_replace('/^\xEF\xBB\xBF/', '', $res_search_string);
        $res_search = json_decode($res_search_string, true);

        $leftstr = "https://fito.by/product/$art_f-";
        foreach ($res_search['suggestions'] as $res1) {
            if (substr($res1['url'], 0, strlen($leftstr)) === $leftstr) {
                $link_ftb = $res1['url'];
                $site_ftb = get_content_with_curl($link_ftb);
                Exec_PR_SQL($link, "UPDATE goods_ftb SET datetime_update = CURRENT_TIMESTAMP(), link_ftb = ? WHERE id = ?", [$link_ftb, $goods_ftb_id]);
                break;
            }
        }
    }

    if ($site_ftb == NULL || strlen($site_ftb) < 100) {
        error_log("Не удалось получить контент для товара: $art");
        continue;
    }

    $name = $img = $price = $producer = $cat = $subcat = $good_def1 = $good_def2 = '';

    if (preg_match('/<meta name="twitter:description" content="([^"]*)"/i', $site_ftb, $matches)) {
        $good_def1 = str_replace("'", "", $matches[1]);
    }

    if (preg_match('/<img[^>]+src="([^"]+-600x600\.jpg)"[^>]*>/i', $site_ftb, $matches)) {
        $img = $matches[1];
    }

    if (preg_match('/<span class="woocommerce-Price-amount amount">([0-9,.]+)&nbsp;<span class="woocommerce-Price-currencySymbol">/i', $site_ftb, $matches)) {
        $price = $matches[1];
    }

    if (preg_match('/<span>Производитель:\s*([^<]+)<\/span>/i', $site_ftb, $matches)) {
        $producer = str_replace("'", "", trim($matches[1]));
    }

    if (preg_match('/<nav class="woocommerce-breadcrumb breadcrumbs">.*?<a[^>]+href="[^"]*">([^<]+)<\/a>.*?<span class="divider">[^<]*<\/span>.*?<a[^>]+href="[^"]*">([^<]+)<\/a>/is', $site_ftb, $matches)) {
        $cat = trim($matches[1]);
        $subcat = trim($matches[2]);
    }

    if (preg_match('/<div class="panel entry-content active"[^>]*>(.*?)<\/div>/is', $site_ftb, $matches)) {
        $good_def2 = preg_replace('/<br\s*\/?>/i', "\n", $matches[1]);
        $good_def2 = strip_tags($good_def2);
        $good_def2 = preg_replace("/\n+/", "\n", $good_def2);
        $good_def2 = trim($good_def2);
    }

    if (preg_match('/<h1[^>]*class="product-title product_title entry-title"[^>]*>([^<]+)<\/h1>/i', $site_ftb, $matches)) {
        $name = str_replace("'", "", trim($matches[1]));
    }

    $que = "
        UPDATE goods_ftb 
        SET 
            datetime_update = CURRENT_TIMESTAMP(), 
            ftb_name = ?, 
            img = ?, 
            price_ftb = ?, 
            producer = ?, 
            cat = ?, 
            subcat = ?, 
            good_def1 = ?, 
            good_def2 = ? 
        WHERE id = ?
    ";

    $params = [$name, $img, $price, $producer, $cat, $subcat, $good_def1, $good_def2, $goods_ftb_id];
    Exec_PR_SQL($link, $que, $params);

    $fullpath = "goods_pics/$art.png";
    $imageData = get_content_with_curl($img);

    if ($imageData !== false) {
        $image = @imagecreatefromstring($imageData);
        if ($image !== false) {
            if (!imagepng($image, $fullpath)) {
                error_log("Не удалось сохранить изображение: $fullpath");
            }
            imagedestroy($image);
        } else {
            error_log("Ошибка создания изображения из данных: $img");
        }
    } else {
        error_log("Не удалось загрузить изображение: $img");
    }
}

$que = "
    UPDATE goods g
    JOIN goods_ftb f ON g.art = f.good_art
    SET 
        #g.art_f = f.good_art_ftb,
        #g.name = f.ftb_name,
        #g.description_short = f.good_def1,
        #g.description_full = f.good_def2,
        #g.pic_name = CONCAT(g.art, '.png'),
        g.price_old = f.price_ftb
        #g.cat = f.cat,
        #g.subcat = f.subcat,
        #g.producer = f.producer;
";
Exec_PR_SQL($link, $que, [],false,true);

