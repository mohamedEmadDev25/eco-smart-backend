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

$healthStmt = $pdo->prepare("
    SELECT COALESCE(ROUND(AVG(health_score)), 0) AS health_percent
    FROM devices
    WHERE home_id = :home_id
");
$healthStmt->execute([":home_id" => $home_id]);
$health = $healthStmt->fetch();

$onlineStmt = $pdo->prepare("
    SELECT COUNT(*) AS online_count
    FROM devices
    WHERE home_id = :home_id AND is_online = 1
");
$onlineStmt->execute([":home_id" => $home_id]);
$online = $onlineStmt->fetch();

$warningStmt = $pdo->prepare("
    SELECT COUNT(*) AS warning_count
    FROM system_alerts
    WHERE home_id = :home_id AND is_resolved = 0
");
$warningStmt->execute([":home_id" => $home_id]);
$warning = $warningStmt->fetch();

echo json_encode([
    "success" => true,
    "data" => [
        "health_percent" => (int)$health["health_percent"],
        "online_devices" => (int)$online["online_count"],
        "active_warnings" => (int)$warning["warning_count"]
    ]
]);