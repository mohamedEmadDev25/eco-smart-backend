<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$input = $_POST;
if (empty($input)) $input = json_decode(file_get_contents("php://input"), true) ?? [];
$home_id = (int)($input["home_id"] ?? 0);
$user_id = (int)($input["user_id"] ?? 0);
if ($home_id <= 0 || $user_id <= 0) {
  echo json_encode(["success"=>false,"message"=>"home_id and user_id are required"]); exit;
}
$stmt = $pdo->prepare("
UPDATE system_alerts
SET status='acknowledged',
    acknowledged_by_user_id=:user_id,
    acknowledged_at=NOW()
WHERE home_id=:home_id
  AND status='active'
");
$stmt->execute([":user_id"=>$user_id, ":home_id"=>$home_id]);
echo json_encode(["success"=>true,"message"=>"All active alerts acknowledged","affected_rows"=>$stmt->rowCount()]);