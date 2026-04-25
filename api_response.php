<?php
function ok($data = null, string $message = "OK"): void {
    echo json_encode(["success" => true, "message" => $message, "data" => $data]);
    exit;
}
function fail(string $message = "Error", int $status = 400): void {
    http_response_code($status);
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}