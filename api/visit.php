<?php

declare(strict_types=1);

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    if (!is_local_host()) {
        json_response(['error' => 'not found'], 404);
        return;
    }
    json_response(['visits' => Store::read('visits')]);
    return;
}

if ($method !== 'POST') {
    header('Allow: GET, POST');
    json_response(['error' => 'GET or POST'], 405);
    return;
}

$body = json_body();
$page = clean($body['path'] ?? '', 180);
if ($page === '' || !str_starts_with($page, '/') || str_starts_with($page, '//')) {
    json_response(['error' => 'path required'], 400);
    return;
}

Store::append('visits', [[
    'at' => gmdate('c'),
    'path' => $page,
    'ip' => client_ip(),
    'ua' => clean($_SERVER['HTTP_USER_AGENT'] ?? '', 240),
    'referrer' => clean($body['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? ''), 240),
    'landing' => clean($body['landing'] ?? '', 180),
    'device' => clean($body['device'] ?? '', 40),
    'sessionId' => clean($body['sessionId'] ?? '', 80),
    'visitorId' => clean($body['visitorId'] ?? '', 80),
    'region' => '',
]]);

json_response(['ok' => true]);
