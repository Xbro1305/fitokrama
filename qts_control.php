<?php
	include 'mnn.php';
	header('Content-Type: application/json; charset=UTF-8');

	$link = firstconnect ();

	$method = explode("/", $_SERVER["SCRIPT_URL"])[2];

if ($method == 'qty_by_art') 
{
	$art = $_GET['art'];
	[$qty,$qty_fr] = qty_by_art ($art);
	die("qty=$qty   qty_fr=$qty_fr");
}


if ($method == 'update_qty_control') 
{
	$que = "UPDATE goods g SET g.qty_control = (
        (
            COALESCE(
                (
                    -- qty: на основе последней записи в register_qty + поставки - сборки
                    (
                        SELECT r.qty 
                        FROM register_qty r
                        WHERE r.art = g.art
                        ORDER BY r.datetime DESC
                        LIMIT 1
                    ) 
                    + (
                        SELECT COALESCE(SUM(d.qty), 0)
                        FROM goods_deliveries d
                        WHERE d.art = g.art
                        AND d.datetime > (
                            SELECT COALESCE(MAX(r.datetime), '1970-01-01 00:00:00')
                            FROM register_qty r
                            WHERE r.art = g.art
                        )
                    )
                    - (
                        SELECT COALESCE(SUM(og.qty), 0)
                        FROM orders_goods og
                        LEFT JOIN orders o ON og.order_id = o.id
                        WHERE og.good_art = g.art
                        AND o.datetime_assembly > (
                            SELECT COALESCE(MAX(r.datetime), '1970-01-01 00:00:00')
                            FROM register_qty r
                            WHERE r.art = g.art
                        )
                    )
                ), 0
            ) < COALESCE(
                GREATEST(
                    5, 
                    (
                        SELECT COALESCE(SUM(og.qty), 0)
                        FROM orders_goods og
                        JOIN orders o ON og.order_id = o.id
                        WHERE og.good_art = g.art
                        AND o.datetime_create > NOW() - INTERVAL 5 DAY
                    )
                ), 0
            )
        )
    ),
    g.prod_30 = (
        SELECT COALESCE(SUM(og.qty), 0)
        FROM orders_goods og
        JOIN orders o ON og.order_id = o.id
        WHERE og.good_art = g.art
        AND o.datetime_create > NOW() - INTERVAL 30 DAY
    );
	";
	Exec_PR_SQL($link,$que,[]);
	exit('qty_control updated');
}

if ($method == 'update_qty_register') 
{
$link->begin_transaction();

try {
    // Вставка или обновление актуальных данных с обновлением datetime
    $query = "
        INSERT INTO register_qty (art, datetime, qty)
        SELECT 
            g.art,
            NOW() AS datetime,
            COALESCE(
                (
                    -- Общее количество: поставки минус сборки
                    (
                        SELECT COALESCE(SUM(d.qty), 0)
                        FROM goods_deliveries d
                        WHERE d.art = g.art
                    )
                    - (
                        SELECT COALESCE(SUM(og.qty), 0)
                        FROM orders_goods og
                        LEFT JOIN orders o ON og.order_id = o.id
                        WHERE og.good_art = g.art
                        AND o.datetime_assembly IS NOT NULL
                    )
                ), 0
            ) AS qty
        FROM goods g
        ON DUPLICATE KEY UPDATE 
            datetime = NOW(),          -- Обновляем datetime на текущее время
            qty = VALUES(qty);          -- Обновляем qty с пересчитанным значением
    ";
    $link->query($query);

    // Удаление старых записей, если есть лишние записи
    $deleteQuery = "
        DELETE r1 
        FROM register_qty r1
        INNER JOIN register_qty r2 
            ON r1.art = r2.art AND r1.datetime < r2.datetime;
    ";
    $link->query($deleteQuery);

    // Завершение транзакции
    $link->commit();

} catch (Exception $e) {
    // В случае ошибки — откат транзакции
    $link->rollback();
    echo "Ошибка: " . $e->getMessage();
}

	exit('register_qty updated');
}







	exit( json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
