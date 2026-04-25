<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "jwt_helper.php";
require "auth_guard.php";
$user = requireAuth();
$JWT_SECRET = "super_secret_key_change_me_2026"; // غيّرها لقيمة طويلة وصعبة

$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
}

$email_or_username = trim($input["email_or_username"] ?? "");
$password = $input["password"] ?? "";

if ($email_or_username === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "email_or_username and password required"
    ]);
    exit;
}

$sql = "SELECT id, full_name, username, email_address, password_hash
        FROM users
        WHERE email_address = :value OR username = :value
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":value" => $email_or_username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
    exit;
}

$payload = [
    "sub" => (int)$user["id"],
    "email" => $user["email_address"],
    "iat" => time(),
    "exp" => time() + (60 * 60 * 24) // 24 ساعة
];

$token = createJWT($payload, $JWT_SECRET);

unset($user["password_hash"]);

echo json_encode([
    "success" => true,
    "message" => "Login success",
    "token" => $token,
    "user" => $user
]);