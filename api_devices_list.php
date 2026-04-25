<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$home_id = isset($_GET["home_id"]) ? (int)$_GET["home_id"] : 0;
$search = trim($_GET["search"] ?? "");
$category_id = isset($_GET["category_id"]) ? (int)$_GET["category_id"] : 0;
$status = trim($_GET["status"] ?? "");
$sort_by = trim($_GET["sort_by"] ?? "last_seen_at");
$sort_dir = strtoupper(trim($_GET["sort_dir"] ?? "DESC"));

if ($home_id <= 0) {
    echo json_encode(["success" => false, "message" => "home_id is required"]);
    exit;
}

$allowedSort = ["last_seen_at", "device_name", "health_score"];
if (!in_array($sort_by, $allowedSort, true)) {
    $sort_by = "last_seen_at";
}

if ($sort_dir !== "ASC" && $sort_dir !== "DESC") {
    $sort_dir = "DESC";
}

$sql = "
SELECT
    d.id,
    d.device_name,
    d.location_text,
    d.status,
    d.metric_label,
    d.metric_value,
    d.last_seen_at,
    d.health_score,
    d.warning_level,
    d.is_online,
    c.category_name
FROM devices d
JOIN device_categories c ON c.id = d.category_id
WHERE d.home_id = :home_id
";

$params = [":home_id" => $home_id];

if ($search !== "") {
    $sql .= " AND (d.device_name LIKE :search OR d.location_text LIKE :search) ";
    $params[":search"] = "%" . $search . "%";
}

if ($category_id > 0) {
    $sql .= " AND d.category_id = :category_id ";
    $params[":category_id"] = $category_id;
}

if ($status !== "") {
    $sql .= " AND d.status = :status ";
    $params[":status"] = $status;
}

$sql .= " ORDER BY {$sort_by} {$sort_dir} ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

echo json_encode(["success" => true, "data" => $data]);
