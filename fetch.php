<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data) {
        // Запис у таблицю logs
        $stmt = $pdo->prepare("INSERT INTO logs (filter_type, query_params, browser_info, latitude, longitude, request_time)
                               VALUES (:filter, :params, :browser, :lat, :lon, :time)");
        $stmt->execute([
            'filter' => $data['filterType'],
            'params' => $data['queryParams'],
            'browser' => $data['browserInfo'],
            'lat' => $data['latitude'],
            'lon' => $data['longitude'],
            'time' => $data['requestTime']
        ]);

        // Розпаковуємо параметри для вибірки
        $_GET = json_decode($data['queryParams'], true) + $_GET;
    }
}

$filter = $_GET['filter'] ?? null;
$products = [];

if ($filter === "vendor" && isset($_GET['vendor'])) {
    $stmt = $pdo->prepare("SELECT items.name, items.price, vendors.v_name 
        FROM items JOIN vendors ON items.FID_Vendor = vendors.ID_Vendors 
        WHERE vendors.ID_Vendors = :vendorId");
    $stmt->execute(['vendorId' => $_GET['vendor']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($filter === "category" && isset($_GET['category'])) {
    $stmt = $pdo->prepare("SELECT items.name, items.price, category.c_name 
        FROM items JOIN category ON items.FID_Category = category.ID_Category 
        WHERE category.ID_Category = :categoryId");
    $stmt->execute(['categoryId' => $_GET['category']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($filter === "price" && isset($_GET['min_price'], $_GET['max_price'])) {
    $stmt = $pdo->prepare("SELECT name, price FROM items WHERE price BETWEEN :min AND :max");
    $stmt->execute([
        'min' => $_GET['min_price'],
        'max' => $_GET['max_price']
    ]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!empty($products)) {
    echo "<div class='product-container'>";
    foreach ($products as $p) {
        echo "<div class='product-card'>";
        echo "<h4>" . htmlspecialchars($p['name']) . "</h4>";
        echo "<p class='price'>Ціна: <strong>" . htmlspecialchars($p['price']) . " грн</strong></p>";
        if (isset($p['v_name'])) echo "<p class='vendor'>Виробник: <strong>" . htmlspecialchars($p['v_name']) . "</strong></p>";
        if (isset($p['c_name'])) echo "<p class='category'>Категорія: <strong>" . htmlspecialchars($p['c_name']) . "</strong></p>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='no-products'>Немає товарів за заданими критеріями.</p>";
}
?>

