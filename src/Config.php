<?php

declare(strict_types=1);

namespace StoreProducts;

use RuntimeException;

final class Config
{
    private static ?array $dotenv = null;

    private function __construct()
    {
    }

    public static function mongoUri(): string
    {
        $uri = self::get('MONGODB_URI');

        if ($uri === null || str_contains($uri, 'USER:PASSWORD') || str_contains($uri, 'CLUSTER.mongodb.net')) {
            throw new RuntimeException('MongoDB Atlas URI is not configured. Set MONGODB_URI in Render or in .env.');
        }

        return self::withDefaultTimeouts($uri);
    }

    public static function databaseName(): string
    {
        return self::get('MONGODB_DATABASE', 'store_products_db') ?? 'store_products_db';
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        self::loadDotenv();

        $value = self::$dotenv[$key] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return $default;
    }

    private static function loadDotenv(): void
    {
        if (self::$dotenv !== null) {
            return;
        }

        self::$dotenv = [];
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            self::$dotenv[trim($key)] = self::stripQuotes(trim($value));
        }
    }

    private static function stripQuotes(string $value): string
    {
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    private static function withDefaultTimeouts(string $uri): string
    {
        foreach (['serverSelectionTimeoutMS=5000', 'connectTimeoutMS=10000'] as $parameter) {
            [$name] = explode('=', $parameter, 2);
            if (!str_contains($uri, $name . '=')) {
                $separator = str_contains($uri, '?') ? '&' : '?';
                if (str_ends_with($uri, '?') || str_ends_with($uri, '&')) {
                    $separator = '';
                }
                $uri .= $separator . $parameter;
            }
        }

        return $uri;
    }
}
