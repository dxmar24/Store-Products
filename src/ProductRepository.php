<?php

declare(strict_types=1);

namespace StoreProducts;

use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use Throwable;

final class ProductRepository
{
    private Collection $collection;

    public function __construct(?Collection $collection = null)
    {
        $this->collection = $collection ?? Database::products();
    }

    public function all(): array
    {
        $cursor = $this->collection->find([], ['sort' => ['createdAt' => -1, 'name' => 1]]);
        $products = [];

        foreach ($cursor as $document) {
            $products[] = $this->normalizeDocument((array)$document);
        }

        return $products;
    }

    public function findById(string $id): ?array
    {
        $objectId = $this->objectId($id);
        if ($objectId === null) {
            return null;
        }

        $document = $this->collection->findOne(['_id' => $objectId]);

        return $document === null ? null : $this->normalizeDocument((array)$document);
    }

    public function findBySku(string $sku): ?array
    {
        $document = $this->collection->findOne(['sku' => $sku]);

        return $document === null ? null : $this->normalizeDocument((array)$document);
    }

    public function create(array $product): array
    {
        $now = new UTCDateTime();
        $document = [
            'sku' => $product['sku'],
            'name' => $product['name'],
            'category' => $product['category'],
            'price' => new Decimal128(number_format($product['price'], 2, '.', '')),
            'stock' => $product['stock'],
            'active' => $product['active'],
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        try {
            $result = $this->collection->insertOne($document);
        } catch (Throwable $exception) {
            $this->throwDuplicateSkuIfNeeded($exception);
            throw $exception;
        }

        return $this->findById((string)$result->getInsertedId()) ?? [];
    }

    public function update(string $id, array $product): ?array
    {
        $objectId = $this->objectId($id);
        if ($objectId === null) {
            return null;
        }

        try {
            $result = $this->collection->updateOne(
                ['_id' => $objectId],
                [
                    '$set' => [
                        'sku' => $product['sku'],
                        'name' => $product['name'],
                        'category' => $product['category'],
                        'price' => new Decimal128(number_format($product['price'], 2, '.', '')),
                        'stock' => $product['stock'],
                        'active' => $product['active'],
                        'updatedAt' => new UTCDateTime(),
                    ],
                ]
            );
        } catch (Throwable $exception) {
            $this->throwDuplicateSkuIfNeeded($exception);
            throw $exception;
        }

        if ($result->getMatchedCount() === 0) {
            return null;
        }

        return $this->findById($id);
    }

    public function delete(string $id): bool
    {
        $objectId = $this->objectId($id);
        if ($objectId === null) {
            return false;
        }

        $result = $this->collection->deleteOne(['_id' => $objectId]);

        return $result->getDeletedCount() > 0;
    }

    private function normalizeDocument(array $document): array
    {
        return [
            'id' => isset($document['_id']) ? (string)$document['_id'] : null,
            'sku' => (string)($document['sku'] ?? ''),
            'name' => (string)($document['name'] ?? ''),
            'category' => (string)($document['category'] ?? ''),
            'price' => $this->priceToFloat($document['price'] ?? 0),
            'stock' => (int)($document['stock'] ?? 0),
            'active' => (bool)($document['active'] ?? true),
            'createdAt' => $this->dateToString($document['createdAt'] ?? null),
            'updatedAt' => $this->dateToString($document['updatedAt'] ?? null),
        ];
    }

    private function objectId(string $id): ?ObjectId
    {
        try {
            return new ObjectId($id);
        } catch (Throwable) {
            return null;
        }
    }

    private function priceToFloat(mixed $value): float
    {
        if ($value instanceof Decimal128) {
            return (float)(string)$value;
        }

        return is_numeric($value) ? (float)$value : 0.0;
    }

    private function dateToString(mixed $value): ?string
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->format(DATE_ATOM);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function throwDuplicateSkuIfNeeded(Throwable $exception): void
    {
        if ((int)$exception->getCode() === 11000 || str_contains($exception->getMessage(), 'E11000')) {
            throw new ValidationException(['sku' => 'SKU already exists. Use a different SKU.']);
        }
    }
}
