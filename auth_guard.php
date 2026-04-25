<?php
require_once "jwt_helper.php";
require "auth_guard.php";
$user = requireAuth();
function base64UrlDecodeJWT(string $data): string {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function verifyJWT(string $token, string $secret): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$h, $p, $s] = $parts;
    $check = base64UrlEncode(hash_hmac('sha256', $h . "." . $p, $secret, true));
    if (!hash_equals($check, $s)) return null;

    $payload = json_decode(base64UrlDecodeJWT($p), true);
    if (!$payload) return null;
    if (isset($payload["exp"]) && time() > (int)$payload["exp"]) return null;

    return $payload;
}

function requireAuth(): array {
    $JWT_SECRET = "super_secret_key_change_me_2026";
    $headers = getallheaders();
    $auth = $headers["Authorization"] ?? $headers["authorization"] ?? "";

    if (!str_starts_with($auth, "Bearer ")) {
        http_response_code(401);
        echo json_encode(["success"=>false,"message"=>"Missing Bearer token"]);
        exit;
    }

    $token = trim(substr($auth, 7));
    $payload = verifyJWT($token, $JWT_SECRET);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(["success"=>false,"message"=>"Invalid or expired token"]);
        exit;
    }

    return $payload;
}