<?php
header("Content-Type: application/json; charset=UTF-8");
require "config_db.php";
require "auth_guard.php";
$user = requireAuth();
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
}

$full_name = trim($input["full_name"] ?? "");
$username = trim($input["username"] ?? "");
$email = trim($input["email"] ?? "");
$password = $input["password"] ?? "";
$accepted_terms = isset($input["accepted_terms"]) ? (int)$input["accepted_terms"] : 0;

if ($full_name === "" || $email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "full_name, email, password required"
    ]);
    exit;
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO users (full_name, username, email_address, password_hash, accepted_terms)
        VALUES (:full_name, :username, :email, :password_hash, :accepted_terms)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ":full_name" => $full_name,
        ":username" => ($username !== "" ? $username : null),
        ":email" => $email,
        ":password_hash" => $password_hash,
        ":accepted_terms" => $accepted_terms
    ]);

    echo json_encode([
        "success" => true,
        "message" => "User registered successfully"
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "Email or username already exists"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error"
        ]);
    }
}