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
    SELECT period_type, usage_kwh, baseline_kwh, target_kwh, achievement_percent, period_start, period_end
    FROM energy_usage_summary
    WHERE home_id = :home_id
      AND (
        (period_type = 'daily'   AND period_start = CURDATE()) OR
        (period_type = 'weekly'  AND period_start = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)) OR
        (period_type = 'monthly' AND period_start = DATE_FORMAT(CURDATE(), '%Y-%m-01'))
      )
");
$stmt->execute([":home_id" => $home_id]);
$rows = $stmt->fetchAll();

$out = ["daily" => null, "weekly" => null, "monthly" => null];
foreach ($rows as $r) {
    $out[$r["period_type"]] = $r;
}

echo json_encode(["success" => true, "data" => $out]);