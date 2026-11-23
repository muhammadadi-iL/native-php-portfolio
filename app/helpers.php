<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function clean(mixed $value, int $max): string
{
    return mb_substr(trim(preg_replace('/[\r\n\t]+/', ' ', (string) $value) ?? ''), 0, $max);
}

function json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function is_local_host(): bool
{
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]);
    $host = trim($host, '[]');

    return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}

function client_ip(): string
{
    $forwarded = explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0];
    $forwarded = trim($forwarded);
    if ($forwarded !== '') {
        return $forwarded;
    }

    return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
}

function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $pages = require BASE_PATH . '/app/data/pages.php';
    $meta = $pages[$view] ?? [
        'title' => 'Muhammad Adil',
        'description' => '',
        'body_class' => '',
    ];

    $title = $meta['title'];
    $description = $meta['description'];
    $bodyClass = $meta['body_class'];
    $contentView = $view;

    require VIEWS_PATH . '/layout.php';
}

function view_path(string $view): string
{
    if (str_starts_with($view, 'case:')) {
        $slug = substr($view, 5);

        return VIEWS_PATH . '/cases/' . $slug . '.php';
    }

    return VIEWS_PATH . '/pages/' . $view . '.php';
}
