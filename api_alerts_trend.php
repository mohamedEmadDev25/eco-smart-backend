<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$home_id = (int)($_GET["home_id"] ?? 0);
if ($home_id <= 0) { echo json_encode(["success"=>false,"message"=>"home_id is required"]); exit; }

$stmt = $pdo->prepare("
SELECT DATE(created_at) AS day_date, COUNT(*) AS total_alerts
FROM system_alerts
WHERE home_id = :home_id
  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
GROUP BY DATE(created_at)
ORDER BY day_date ASC
");
$stmt->execute([":home_id"=>$home_id]);
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll()]);