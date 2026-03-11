<?php
/**
 * Скрипт для автоматического исправления проблемных изображений в OpenCart
 * Очищает ссылки на несуществующие изображения в базе данных
 */

// Подключаем конфиг
require_once('config.php');







// Параметры
$DRY_RUN = isset($_GET['dryrun']) ? true : false; // Если ?dryrun=1 - только показать что будет сделано
$CONFIRM = isset($_GET['confirm']) ? true : false; // Если ?confirm=1 - выполнить изменения

// Подключаемся к БД
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_error) {
    die("Ошибка подключения к БД: " . $db->connect_error);
}

$db->set_charset("utf8");

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Исправление изображений</title>";
echo "<style>body{font-family:Arial;padding:20px;} .warning{background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;} .success{background:#d4edda;border:1px solid #28a745;padding:15px;margin:10px 0;} .danger{background:#f8d7da;border:1px solid #dc3545;padding:15px;margin:10px 0;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .btn{display:inline-block;padding:10px 20px;margin:10px 5px;background:#007bff;color:white;text-decoration:none;border-radius:5px;} .btn:hover{background:#0056b3;} .btn-danger{background:#dc3545;} .btn-danger:hover{background:#c82333;}</style></head><body>";

echo "<h1>Исправление отсутствующих изображений в OpenCart</h1>";

if (!$CONFIRM && !$DRY_RUN) {
    echo "<div class='warning'>";
    echo "<h2>⚠️ Внимание!</h2>";
    echo "<p>Этот скрипт очистит ссылки на отсутствующие изображения в базе данных.</p>";
    echo "<p><strong>Рекомендуется сначала создать резервную копию базы данных!</strong></p>";
    echo "<a href='?dryrun=1' class='btn'>Показать что будет изменено (безопасный режим)</a>";
    echo "<a href='?confirm=1' class='btn btn-danger'>Выполнить исправления (изменит БД)</a>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

$mode = $DRY_RUN ? "ТЕСТОВЫЙ РЕЖИМ (изменения НЕ будут сохранены)" : "РЕЖИМ ИСПРАВЛЕНИЯ (изменения БУДУТ сохранены в БД)";
echo "<div class='" . ($DRY_RUN ? "warning" : "danger") . "'><h2>$mode</h2></div>";

$total_fixed = 0;

// 1. Исправление изображений брендов
echo "<h2>1. Бренды (Manufacturers)</h2>";
$result = $db->query("SELECT manufacturer_id, name, image FROM " . DB_PREFIX . "manufacturer WHERE image != '' AND image IS NOT NULL");
$fixed_manufacturers = [];

while($row = $result->fetch_assoc()) {
    $file_path = DIR_IMAGE . $row['image'];
    if (!file_exists($file_path)) {
        $fixed_manufacturers[] = $row;
    }
}

if (count($fixed_manufacturers) > 0) {
    echo "<p>Найдено брендов с отсутствующими изображениями: <strong>" . count($fixed_manufacturers) . "</strong></p>";
    echo "<table><tr><th>ID</th><th>Название</th><th>Отсутствующее изображение</th></tr>";

    foreach($fixed_manufacturers as $row) {
        echo "<tr><td>{$row['manufacturer_id']}</td><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['image']) . "</td></tr>";

        if (!$DRY_RUN) {
            $db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '' WHERE manufacturer_id = " . (int)$row['manufacturer_id']);
            $total_fixed++;
        }
    }
    echo "</table>";
} else {
    echo "<p style='color:green;'>✓ Все изображения брендов в порядке</p>";
}

// 2. Исправление изображений категорий
echo "<h2>2. Категории (Categories)</h2>";
$result = $db->query("SELECT category_id, image FROM " . DB_PREFIX . "category WHERE image != '' AND image IS NOT NULL");
$fixed_categories = [];

while($row = $result->fetch_assoc()) {
    $file_path = DIR_IMAGE . $row['image'];
    if (!file_exists($file_path)) {
        $fixed_categories[] = $row;
    }
}

if (count($fixed_categories) > 0) {
    echo "<p>Найдено категорий с отсутствующими изображениями: <strong>" . count($fixed_categories) . "</strong></p>";
    echo "<table><tr><th>ID</th><th>Отсутствующее изображение</th></tr>";

    foreach($fixed_categories as $row) {
        echo "<tr><td>{$row['category_id']}</td><td>" . htmlspecialchars($row['image']) . "</td></tr>";

        if (!$DRY_RUN) {
            $db->query("UPDATE " . DB_PREFIX . "category SET image = '' WHERE category_id = " . (int)$row['category_id']);
            $total_fixed++;
        }
    }
    echo "</table>";
} else {
    echo "<p style='color:green;'>✓ Все изображения категорий в порядке</p>";
}

// 3. Исправление изображений товаров
echo "<h2>3. Товары (Products)</h2>";
$result = $db->query("SELECT product_id, image FROM " . DB_PREFIX . "product WHERE image != '' AND image IS NOT NULL");
$fixed_products = [];

while($row = $result->fetch_assoc()) {
    $file_path = DIR_IMAGE . $row['image'];
    if (!file_exists($file_path)) {
        $fixed_products[] = $row;
    }
}

if (count($fixed_products) > 0) {
    echo "<p>Найдено товаров с отсутствующими изображениями: <strong>" . count($fixed_products) . "</strong></p>";
    echo "<table><tr><th>ID</th><th>Отсутствующее изображение</th></tr>";

    foreach($fixed_products as $row) {
        echo "<tr><td>{$row['product_id']}</td><td>" . htmlspecialchars($row['image']) . "</td></tr>";

        if (!$DRY_RUN) {
            $db->query("UPDATE " . DB_PREFIX . "product SET image = '' WHERE product_id = " . (int)$row['product_id']);
            $total_fixed++;
        }
    }
    echo "</table>";
} else {
    echo "<p style='color:green;'>✓ Все изображения товаров в порядке</p>";
}

// 4. Исправление дополнительных изображений товаров
echo "<h2>4. Дополнительные изображения товаров (Product Images)</h2>";
$result = $db->query("SELECT product_image_id, product_id, image FROM " . DB_PREFIX . "product_image WHERE image != '' AND image IS NOT NULL");
$fixed_product_images = [];

while($row = $result->fetch_assoc()) {
    $file_path = DIR_IMAGE . $row['image'];
    if (!file_exists($file_path)) {
        $fixed_product_images[] = $row;
    }
}

if (count($fixed_product_images) > 0) {
    echo "<p>Найдено дополнительных изображений с отсутствующими файлами: <strong>" . count($fixed_product_images) . "</strong></p>";
    echo "<table><tr><th>ID записи</th><th>ID товара</th><th>Отсутствующее изображение</th></tr>";

    foreach($fixed_product_images as $row) {
        echo "<tr><td>{$row['product_image_id']}</td><td>{$row['product_id']}</td><td>" . htmlspecialchars($row['image']) . "</td></tr>";

        if (!$DRY_RUN) {
            $db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_image_id = " . (int)$row['product_image_id']);
            $total_fixed++;
        }
    }
    echo "</table>";
} else {
    echo "<p style='color:green;'>✓ Все дополнительные изображения в порядке</p>";
}

// Итоги
echo "<hr><h2>Итоги</h2>";

if ($DRY_RUN) {
    $total_issues = count($fixed_manufacturers) + count($fixed_categories) + count($fixed_products) + count($fixed_product_images);
    echo "<div class='warning'>";
    echo "<p><strong>Найдено проблем: $total_issues</strong></p>";
    echo "<p>Это тестовый режим. Изменения НЕ были сохранены в базу данных.</p>";
    echo "<a href='?confirm=1' class='btn btn-danger'>Выполнить исправления</a>";
    echo "<a href='?' class='btn'>Назад</a>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<p><strong>✓ Исправлено записей: $total_fixed</strong></p>";
    echo "<p>Теперь рекомендуется:</p>";
    echo "<ol>";
    echo "<li>Очистить кэш OpenCart (Админка → Dashboard → Developer Settings → Refresh)</li>";
    echo "<li>Обновить модификации (Админка → Extensions → Modifications → Refresh)</li>";
    echo "<li>Проверить сайт на наличие предупреждений</li>";
    echo "</ol>";
    echo "<a href='check_images.php' class='btn'>Запустить проверку заново</a>";
    echo "</div>";
}

echo "</body></html>";

$db->close();
