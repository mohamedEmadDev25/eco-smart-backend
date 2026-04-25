<?php
// انسخ الملف ده وسميه config_db.php وحط بياناتك
$host    = "127.0.0.1";
$db      = "smart_home_energy";
$user    = "root";
$pass    = "YOUR_DB_PASSWORD_HERE";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}
