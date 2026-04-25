<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
}

$email = trim($input["email"] ?? "");
if ($email === "") {
    echo json_encode(["success" => false, "message" => "email required"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, email_address FROM users WHERE email_address = :email LIMIT 1");
$stmt->execute([":email" => $email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["success" => true, "message" => "If email exists, reset link was generated"]);
    exit;
}

$token = bin2hex(random_bytes(32));
$expiresAt = date("Y-m-d H:i:s", time() + 1800); 

$insert = $pdo->prepare("
    INSERT INTO password_resets (user_id, reset_token, expires_at)
    VALUES (:user_id, :reset_token, :expires_at)
");
$insert->execute([
    ":user_id" => $user["id"],
    ":reset_token" => $token,
    ":expires_at" => $expiresAt
]);

echo json_encode([
    "success" => true,
    "message" => "Reset token generated",
    "reset_token_demo" => $token
]);