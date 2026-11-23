<?php

declare(strict_types=1);

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$siteKey = env('RECAPTCHA_SITE_KEY', '');
$secret = env('RECAPTCHA_SECRET_KEY', '');
$ready = $siteKey !== '' && $secret !== '' && !is_local_host();

if ($method === 'GET') {
    json_response([
        'enabled' => $ready,
        'siteKey' => $ready ? $siteKey : '',
    ]);
    return;
}

if ($method !== 'POST') {
    header('Allow: GET, POST');
    json_response(['error' => 'GET or POST'], 405);
    return;
}

if (!$ready) {
    json_response(['ok' => true, 'skipped' => true]);
    return;
}

$body = json_body();
$token = clean($body['token'] ?? '', 2000);
if ($token === '') {
    json_response(['ok' => false], 400);
    return;
}

$query = http_build_query([
    'secret' => $secret,
    'response' => $token,
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $query,
        'timeout' => 8,
    ],
]);

$raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
$data = is_string($raw) ? json_decode($raw, true) : null;
$ok = is_array($data) && !empty($data['success']);

json_response(['ok' => $ok], $ok ? 200 : 400);
