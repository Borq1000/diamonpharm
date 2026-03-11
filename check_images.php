<?php
/**
 * Скрипт для диагностики проблемных изображений в OpenCart
 * Проверяет наличие файлов изображений, указанных в базе данных
 */


// Подключаем конфиг
require_once('config.php');

// Подключаемся к БД
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_error) {
    die("Ошибка подключения к БД: " . $db->connect_error);
}

$db->set_charset("utf8");

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Проверка изображений</title>";
echo "<style>body{font-family:Arial;padding:20px;} .missing{color:red;} .ok{color:green;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style></head><body>";

echo "<h1>Диагностика изображений в OpenCart</h1>";

// 1. Проверка изображений брендов
echo "<h2>1. Изображения брендов (Manufacturers)</h2>";
echo "<table><tr><th>ID</th><th>Название</th><th>Путь к изображению</th><th>Статус</th></tr>";

$result = $db->query("SELECT manufacturer_id, name, image FROM " . DB_PREFIX . "manufacturer WHERE image != '' AND image IS NOT NULL");
$missing_manufacturers = 0;
$total_manufacturers = 0;

while($row = $result->fetch_assoc()) {
    $total_manufacturers++;
    $file_path = DIR_IMAGE . $row['image'];
    $exists = file_exists($file_path);

    if (!$exists) {
        $missing_manufacturers++;
        echo "<tr class='missing'>";
    } else {
        echo "<tr class='ok'>";
    }

    echo "<td>{$row['manufacturer_id']}</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['image']) . "</td>";
    echo "<td>" . ($exists ? "✓ OK" : "✗ ОТСУТСТВУЕТ") . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Итого брендов: $total_manufacturers, отсутствует файлов: $missing_manufacturers</strong></p>";

// 2. Проверка изображений категорий
echo "<h2>2. Изображения категорий (Categories)</h2>";
echo "<table><tr><th>ID</th><th>Название</th><th>Путь к изображению</th><th>Статус</th></tr>";

$result = $db->query("SELECT c.category_id, cd.name, c.image
                       FROM " . DB_PREFIX . "category c
                       LEFT JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
                       WHERE c.image != '' AND c.image IS NOT NULL AND cd.language_id = 1
                       LIMIT 20");
$missing_categories = 0;
$total_categories = 0;

while($row = $result->fetch_assoc()) {
    $total_categories++;
    $file_path = DIR_IMAGE . $row['image'];
    $exists = file_exists($file_path);

    if (!$exists) {
        $missing_categories++;
        echo "<tr class='missing'>";
    } else {
        echo "<tr class='ok'>";
    }

    echo "<td>{$row['category_id']}</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['image']) . "</td>";
    echo "<td>" . ($exists ? "✓ OK" : "✗ ОТСУТСТВУЕТ") . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Проверено категорий: $total_categories, отсутствует файлов: $missing_categories</strong></p>";

// 3. Проверка изображений товаров (первые 50)
echo "<h2>3. Изображения товаров (Products) - первые 50</h2>";
echo "<table><tr><th>ID</th><th>Название</th><th>Путь к изображению</th><th>Статус</th></tr>";

$result = $db->query("SELECT p.product_id, pd.name, p.image
                       FROM " . DB_PREFIX . "product p
                       LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id
                       WHERE p.image != '' AND p.image IS NOT NULL AND pd.language_id = 1
                       LIMIT 50");
$missing_products = 0;
$total_products = 0;

while($row = $result->fetch_assoc()) {
    $total_products++;
    $file_path = DIR_IMAGE . $row['image'];
    $exists = file_exists($file_path);

    if (!$exists) {
        $missing_products++;
        echo "<tr class='missing'>";
    } else {
        echo "<tr class='ok'>";
    }

    echo "<td>{$row['product_id']}</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['image']) . "</td>";
    echo "<td>" . ($exists ? "✓ OK" : "✗ ОТСУТСТВУЕТ") . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Проверено товаров: $total_products, отсутствует файлов: $missing_products</strong></p>";

// 4. Проверка дополнительных изображений товаров
echo "<h2>4. Дополнительные изображения товаров (Product Images)</h2>";
echo "<table><tr><th>ID товара</th><th>Путь к изображению</th><th>Статус</th></tr>";

$result = $db->query("SELECT product_id, image FROM " . DB_PREFIX . "product_image WHERE image != '' AND image IS NOT NULL LIMIT 50");
$missing_product_images = 0;
$total_product_images = 0;

while($row = $result->fetch_assoc()) {
    $total_product_images++;
    $file_path = DIR_IMAGE . $row['image'];
    $exists = file_exists($file_path);

    if (!$exists) {
        $missing_product_images++;
        echo "<tr class='missing'>";
    } else {
        echo "<tr class='ok'>";
    }

    echo "<td>{$row['product_id']}</td>";
    echo "<td>" . htmlspecialchars($row['image']) . "</td>";
    echo "<td>" . ($exists ? "✓ OK" : "✗ ОТСУТСТВУЕТ") . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Проверено доп. изображений: $total_product_images, отсутствует файлов: $missing_product_images</strong></p>";

// Общая статистика
echo "<hr><h2>Общая статистика</h2>";
$total_missing = $missing_manufacturers + $missing_categories + $missing_products + $missing_product_images;
echo "<p style='font-size:18px;'><strong>ВСЕГО ОТСУТСТВУЕТ ФАЙЛОВ: <span style='color:red;font-size:24px;'>$total_missing</span></strong></p>";

if ($total_missing > 0) {
    echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;border-radius:5px;'>";
    echo "<h3>Рекомендации по исправлению:</h3>";
    echo "<ol>";
    echo "<li><strong>Удалить ссылки на отсутствующие изображения из БД</strong> - используйте скрипт <code>fix_images.php</code></li>";
    echo "<li><strong>Загрузить заглушку</strong> - создать изображение-заглушку (placeholder.png) и заменить пути в БД</li>";
    echo "<li><strong>Восстановить из бэкапа</strong> - если есть резервные копии изображений</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";

$db->close();
