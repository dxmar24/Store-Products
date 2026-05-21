<?php

declare(strict_types=1);

namespace StoreProducts;

use MongoDB\Client;
use MongoDB\Collection;
use Throwable;

final class Database
{
    private static ?Client $client = null;
    private static bool $indexesReady = false;

    private function __construct()
    {
    }

    public static function products(): Collection
    {
        $collection = self::client()->selectCollection(Config::databaseName(), 'products');
        self::ensureIndexes($collection);

        return $collection;
    }

    private static function client(): Client
    {
        if (self::$client === null) {
            self::$client = new Client(Config::mongoUri());
        }

        return self::$client;
    }

    private static function ensureIndexes(Collection $collection): void
    {
        if (self::$indexesReady) {
            return;
        }

        try {
            $collection->createIndex(['sku' => 1], ['unique' => true, 'name' => 'uniq_products_sku']);
            $collection->createIndex(['createdAt' => -1], ['name' => 'idx_products_created_at']);
        } catch (Throwable $exception) {
            error_log('Unable to ensure MongoDB indexes: ' . $exception->getMessage());
        }

        self::$indexesReady = true;
    }
}
