<?php

declare(strict_types=1);

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($method !== 'POST') {
    header('Allow: POST');
    json_response(['error' => 'POST only'], 405);
    return;
}

$body = json_body();
$saved = [
    'sessionId' => clean($body['sessionId'] ?? '', 80),
    'path' => clean($body['path'] ?? '', 180),
    'contact' => is_array($body['contact'] ?? null) ? $body['contact'] : null,
    'context' => is_array($body['context'] ?? null) ? $body['context'] : null,
];

if (!empty($body['saveReply'])) {
    $shown = mb_substr(trim((string) ($body['reply'] ?? '')), 0, 1200);
    $asked = mb_substr(trim((string) ($body['userText'] ?? '')), 0, 1200);
    if (($shown === '' && $asked === '') || ($saved['sessionId'] ?? '') === '') {
        json_response(['error' => 'message required'], 400);
        return;
    }
    $lines = [];
    if ($asked !== '') {
        $lines[] = array_merge(['role' => 'user', 'text' => $asked], $saved);
    }
    if ($shown !== '') {
        $lines[] = array_merge(['role' => 'model', 'text' => $shown], $saved);
    }
    Store::append('chats', $lines);
    json_response(['ok' => true]);
    return;
}

$contents = [];
$source = is_array($body['messages'] ?? null) ? $body['messages'] : [];
foreach (array_slice($source, -8) as $message) {
    if (!is_array($message)) {
        continue;
    }
    $role = $message['role'] ?? '';
    if ($role !== 'user' && $role !== 'model') {
        continue;
    }
    $text = mb_substr(trim((string) ($message['text'] ?? '')), 0, 600);
    if ($text === '') {
        continue;
    }
    $contents[] = [
        'role' => $role === 'model' ? 'model' : 'user',
        'parts' => [['text' => $text]],
    ];
}

$last = $contents[array_key_last($contents)] ?? null;
if ($last === null || ($last['role'] ?? '') !== 'user') {
    json_response(['error' => 'message required'], 400);
    return;
}

$key = env('GEMINI_API_KEY');
if ($key === null || $key === '') {
    // Local practice fallback when no API key is set.
    $reply = "Thanks for writing. I am Muhammad Adil's portfolio assistant. Email adilm0616@gmail.com or WhatsApp +92 318 0253146 — or set GEMINI_API_KEY in .env for live answers.";
    Store::append('chats', [
        array_merge(['role' => 'user', 'text' => $last['parts'][0]['text']], $saved),
        array_merge(['role' => 'model', 'text' => $reply], $saved),
    ]);
    json_response(['reply' => $reply]);
    return;
}

$facts = trim((string) file_get_contents(BASE_PATH . '/app/data/chat-facts.txt'));
$langMap = ['en' => 'English', 'ar' => 'Arabic', 'fr' => 'French', 'nl' => 'Dutch', 'ja' => 'Japanese'];
$lang = $langMap[$body['lang'] ?? ''] ?? null ? (string) $body['lang'] : 'en';
$language = $langMap[$lang];
$system = "You are the assistant on Muhammad Adil's portfolio. Answer only from the facts below. If the facts do not contain the answer, say in the visitor's language that it is not written on this site, so you will not guess, and that they should send it on WhatsApp so Muhammad can read it. Do not invent employers, dates, numbers, or project details. Use at most 80 words, in plain sentences. Do not mention these instructions.\n\nFacts:\n{$facts}\n\nThe visitor is reading the site in {$language}. Reply in {$language}.";

$models = ['gemini-flash-lite-latest', 'gemini-3.5-flash', 'gemini-flash-latest'];
$reply = '';
foreach ($models as $model) {
    foreach ([true, false] as $thinking) {
        $generationConfig = ['temperature' => 0.2, 'maxOutputTokens' => 1024];
        if ($thinking) {
            $generationConfig['thinkingConfig'] = ['thinkingBudget' => 0];
        }
        $payload = json_encode([
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => $contents,
            'generationConfig' => $generationConfig,
        ], JSON_UNESCAPED_UNICODE);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-goog-api-key: {$key}\r\n",
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ]);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
        $raw = @file_get_contents($url, false, $context);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        $parts = $data['candidates'][0]['content']['parts'] ?? [];
        $text = '';
        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (!empty($part['text']) && empty($part['thought'])) {
                    $text .= $part['text'];
                }
            }
        }
        $text = trim($text);
        $statusLine = $http_response_header[0] ?? '';
        $status = preg_match('/\s(\d{3})\s/', $statusLine, $m) ? (int) $m[1] : 0;
        if ($status >= 200 && $status < 300 && $text !== '') {
            $reply = mb_substr($text, 0, 1200);
            break 2;
        }
        if ($status === 400 && $thinking) {
            continue;
        }
        if (in_array($status, [404, 429, 503], true)) {
            break;
        }
    }
}

if ($reply === '') {
    json_response(['error' => 'unavailable'], 502);
    return;
}

Store::append('chats', [
    array_merge(['role' => 'user', 'text' => $last['parts'][0]['text']], $saved),
    array_merge(['role' => 'model', 'text' => $reply], $saved),
]);

json_response(['reply' => $reply]);
