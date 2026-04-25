<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$home_id = isset($_GET["home_id"]) ? (int)$_GET["home_id"] : 0;
$search = trim($_GET["search"] ?? "");
$category_id = isset($_GET["category_id"]) ? (int)$_GET["category_id"] : 0;
$status_filter = trim($_GET["status_filter"] ?? "");
$sort_by = trim($_GET["sort_by"] ?? "last_seen_desc");

if ($home_id <= 0) {
    echo json_encode(["success" => false, "message" => "home_id is required"]);
    exit;
}

$devicesSql = "
SELECT
    d.id, d.device_name, d.location_text, d.status, d.metric_label, d.metric_value,
    d.last_seen_at, d.health_score, d.warning_level, c.category_name
FROM devices d
JOIN device_categories c ON c.id = d.category_id
WHERE d.home_id = :home_id
  AND (
    :search = '' OR
    d.device_name LIKE CONCAT('%', :search, '%') OR
    d.location_text LIKE CONCAT('%', :search, '%') OR
    c.category_name LIKE CONCAT('%', :search, '%')
  )
  AND (:category_id = 0 OR d.category_id = :category_id)
  AND (:status_filter = '' OR d.status = :status_filter)
ORDER BY
  CASE WHEN :sort_by = 'name_asc' THEN d.device_name END ASC,
  CASE WHEN :sort_by = 'name_desc' THEN d.device_name END DESC,
  CASE WHEN :sort_by = 'last_seen_desc' THEN d.last_seen_at END DESC,
  CASE WHEN :sort_by = 'health_desc' THEN d.health_score END DESC,
  d.id DESC
";
$devicesStmt = $pdo->prepare($devicesSql);
$devicesStmt->execute([
    ":home_id" => $home_id,
    ":search" => $search,
    ":category_id" => $category_id,
    ":status_filter" => $status_filter,
    ":sort_by" => $sort_by
]);
$devices = $devicesStmt->fetchAll();

$catSql = "
SELECT c.id, c.category_name, COUNT(d.id) AS devices_count
FROM device_categories c
LEFT JOIN devices d ON d.category_id = c.id AND d.home_id = :home_id
GROUP BY c.id, c.category_name
ORDER BY c.id
";
$catStmt = $pdo->prepare($catSql);
$catStmt->execute([":home_id" => $home_id]);
$categories = $catStmt->fetchAll();

$allStmt = $pdo->prepare("SELECT COUNT(*) AS all_devices_count FROM devices WHERE home_id = :home_id");
$allStmt->execute([":home_id" => $home_id]);
$allDevices = $allStmt->fetch();

$healthSql = "
SELECT
    COALESCE(ROUND(AVG(d.health_score)), 0) AS system_health_percent,
    SUM(CASE WHEN d.status = 'online' THEN 1 ELSE 0 END) AS online_devices,
    SUM(CASE WHEN d.warning_level IN ('medium', 'high') THEN 1 ELSE 0 END) AS warnings_count
FROM devices d
WHERE d.home_id = :home_id
";
$healthStmt = $pdo->prepare($healthSql);
$healthStmt->execute([":home_id" => $home_id]);
$health = $healthStmt->fetch();

$syncStmt = $pdo->prepare("
SELECT id, sync_status, devices_scanned, devices_updated, created_at
FROM sync_logs
WHERE home_id = :home_id
ORDER BY id DESC
LIMIT 1
");
$syncStmt->execute([":home_id" => $home_id]);
$latestSync = $syncStmt->fetch();

echo json_encode([
    "success" => true,
    "filters" => [
        "home_id" => $home_id,
        "search" => $search,
        "category_id" => $category_id,
        "status_filter" => $status_filter,
        "sort_by" => $sort_by
    ],
    "system_health" => [
        "percent" => (int)($health["system_health_percent"] ?? 0),
        "online_devices" => (int)($health["online_devices"] ?? 0),
        "warnings_count" => (int)($health["warnings_count"] ?? 0)
    ],
    "all_devices_count" => (int)($allDevices["all_devices_count"] ?? 0),
    "latest_sync" => $latestSync ?: null,
    "categories" => $categories,
    "devices" => $devices
]);