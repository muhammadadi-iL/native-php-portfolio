<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

Router::dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
