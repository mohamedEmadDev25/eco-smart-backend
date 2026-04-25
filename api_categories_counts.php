<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$home_id = isset($_GET["home_id"]) ? (int)$_GET["home_id"] : 0;
if ($home_id <= 0) {
    echo json_encode(["success" => false, "message" => "home_id is required"]);
    exit;
}

$sql = "
SELECT
    c.id,
    c.category_name,
    COUNT(d.id) AS devices_count
FROM device_categories c
LEFT JOIN devices d
    ON d.category_id = c.id
   AND d.home_id = :home_id
GROUP BY c.id, c.category_name
ORDER BY c.category_name ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":home_id" => $home_id]);
$rows = $stmt->fetchAll();

$totalStmt = $pdo->prepare("SELECT COUNT(*) AS total_devices FROM devices WHERE home_id = :home_id");
$totalStmt->execute([":home_id" => $home_id]);
$total = $totalStmt->fetch();

echo json_encode([
    "success" => true,
    "all_devices_count" => (int)$total["total_devices"],
    "categories" => $rows
]);