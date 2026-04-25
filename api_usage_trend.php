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

$stmt = $pdo->prepare("
    SELECT point_time, usage_kwh
    FROM energy_usage_points
    WHERE home_id = :home_id
      AND DATE(point_time) = CURDATE()
    ORDER BY point_time ASC
");
$stmt->execute([":home_id" => $home_id]);
$points = $stmt->fetchAll();

echo json_encode(["success" => true, "data" => $points]);