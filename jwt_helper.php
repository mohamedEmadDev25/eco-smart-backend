<?php

function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function createJWT(array $payload, string $secret): string {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));

    $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $secret, true);
    $signatureEncoded = base64UrlEncode($signature);

    return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
}