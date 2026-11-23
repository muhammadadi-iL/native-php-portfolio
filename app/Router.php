<?php

declare(strict_types=1);

final class Router
{
    public static function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/') . '/';
        }

        if (str_starts_with($path, '/api/')) {
            self::api($path, strtoupper($method));

            return;
        }

        // Static CV page keeps its own print stylesheet.
        if ($path === '/cv/') {
            $file = PUBLIC_PATH . '/cv/index.html';
            if (is_readable($file)) {
                header('Content-Type: text/html; charset=utf-8');
                readfile($file);

                return;
            }
        }

        $map = [
            '/' => 'home',
            '/about/' => 'about',
            '/work/' => 'work',
            '/how-i-ship/' => 'how-i-ship',
            '/privacy/' => 'privacy',
            '/javascript-required/' => 'javascript-required',
        ];

        if (isset($map[$path])) {
            render($map[$path]);

            return;
        }

        if (preg_match('#^/work/([a-z0-9-]+)/$#', $path, $m)) {
            $slug = $m[1];
            $view = 'case:' . $slug;
            if (is_readable(view_path($view))) {
                render($view);

                return;
            }
        }

        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "404 Not Found\n";
    }

    private static function api(string $path, string $method): void
    {
        $file = match ($path) {
            '/api/visit/', '/api/visit' => BASE_PATH . '/api/visit.php',
            '/api/chat/', '/api/chat' => BASE_PATH . '/api/chat.php',
            '/api/captcha/', '/api/captcha' => BASE_PATH . '/api/captcha.php',
            default => null,
        };

        if ($file === null || !is_readable($file)) {
            json_response(['error' => 'not found'], 404);

            return;
        }

        require $file;
    }
}
