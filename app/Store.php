<?php

declare(strict_types=1);

final class Store
{
    private const CAP = 500;

    public static function path(string $kind): string
    {
        return STORAGE_PATH . '/' . $kind . '.jsonl';
    }

    public static function read(string $kind): array
    {
        $file = self::path($kind);
        if (!is_readable($file)) {
            return [];
        }

        $rows = [];
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $row = json_decode($line, true);
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return array_slice($rows, -self::CAP);
    }

    public static function append(string $kind, array $records): void
    {
        if ($records === []) {
            return;
        }

        $rows = array_slice(array_merge(self::read($kind), $records), -self::CAP);
        $body = '';
        foreach ($rows as $row) {
            $body .= json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        }

        $file = self::path($kind);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($file, $body, LOCK_EX);
    }
}
