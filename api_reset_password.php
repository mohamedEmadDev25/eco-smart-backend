<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
}

$token = trim($input["reset_token"] ?? "");
$newPassword = $input["new_password"] ?? "";

if ($token === "" || $newPassword === "") {
    echo json_encode(["success" => false, "message" => "reset_token and new_password required"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, user_id, expires_at, used_at
    FROM password_resets
    WHERE reset_token = :token
    LIMIT 1
");
$stmt->execute([":token" => $token]);
$resetRow = $stmt->fetch();

if (!$resetRow) {
    echo json_encode(["success" => false, "message" => "Invalid token"]);
    exit;
}

if (!empty($resetRow["used_at"])) {
    echo json_encode(["success" => false, "message" => "Token already used"]);
    exit;
}

if (strtotime($resetRow["expires_at"]) < time()) {
    echo json_encode(["success" => false, "message" => "Token expired"]);
    exit;
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

$pdo->beginTransaction();
try {
    $updateUser = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :user_id");
    $updateUser->execute([
        ":hash" => $newHash,
        ":user_id" => $resetRow["user_id"]
    ]);

    $markUsed = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
    $markUsed->execute([":id" => $resetRow["id"]]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Password updated successfully"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Database error"]);
}