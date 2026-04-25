<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$home_id = (int)($_GET["home_id"] ?? 0);
$tab = trim($_GET["tab"] ?? "all");
$search = trim($_GET["search"] ?? "");
$status = trim($_GET["status"] ?? "");

if ($home_id <= 0) {
  echo json_encode(["success" => false, "message" => "home_id is required"]);
  exit;
}

$sql = "SELECT id, title, message, alert_type, severity, status, source_component, service_name, created_at
        FROM system_alerts
        WHERE home_id = :home_id";
$params = [":home_id" => $home_id];

if ($tab === "critical" || $tab === "high" || $tab === "medium") {
  $sql .= " AND severity = :tabSeverity AND status != 'resolved'";
  $params[":tabSeverity"] = $tab;
} elseif ($tab === "resolved") {
  $sql .= " AND status = 'resolved'";
}

if ($status !== "") {
  $sql .= " AND status = :status";
  $params[":status"] = $status;
}

if ($search !== "") {
  $sql .= " AND (title LIKE :q OR message LIKE :q OR source_component LIKE :q OR service_name LIKE :q)";
  $params[":q"] = "%" . $search . "%";
}

$sql .= " ORDER BY created_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
