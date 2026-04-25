<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$home_id = (int)($_GET["home_id"] ?? 0);
if ($home_id <= 0) { echo json_encode(["success"=>false,"message"=>"home_id is required"]); exit; }

$stmt = $pdo->prepare("
SELECT
  SUM(CASE WHEN severity='critical' AND status!='resolved' THEN 1 ELSE 0 END) AS critical_count,
  SUM(CASE WHEN severity='high'     AND status!='resolved' THEN 1 ELSE 0 END) AS high_count,
  SUM(CASE WHEN severity='medium'   AND status!='resolved' THEN 1 ELSE 0 END) AS medium_count,
  SUM(CASE WHEN severity='low'      AND status!='resolved' THEN 1 ELSE 0 END) AS low_count,
  SUM(CASE WHEN severity='info'     AND status!='resolved' THEN 1 ELSE 0 END) AS info_count
FROM system_alerts
WHERE home_id = :home_id
");
$stmt->execute([":home_id"=>$home_id]);
$data = $stmt->fetch();

echo json_encode(["success"=>true,"data"=>$data]);