<?php

declare(strict_types=1);

namespace StoreProducts;

final class ProductValidator
{
    private const MAX_SKU_LENGTH = 30;
    private const MAX_NAME_LENGTH = 80;
    private const MAX_CATEGORY_LENGTH = 50;
    private const MAX_NUMERIC_VALUE = 999999;

    private function __construct()
    {
    }

    public static function validate(array $input): array
    {
        $errors = [];

        $sku = strtoupper(trim((string)($input['sku'] ?? '')));
        if ($sku === '') {
            $errors['sku'] = 'SKU is required.';
        } elseif (!preg_match('/^[A-Z0-9-]+$/', $sku)) {
            $errors['sku'] = 'SKU can only contain uppercase letters, numbers, and hyphens.';
        } elseif (strlen($sku) > self::MAX_SKU_LENGTH) {
            $errors['sku'] = 'SKU must be 30 characters or fewer.';
        }

        $name = trim((string)($input['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'Product name is required.';
        } elseif (strlen($name) > self::MAX_NAME_LENGTH) {
            $errors['name'] = 'Product name must be 80 characters or fewer.';
        }

        $category = trim((string)($input['category'] ?? ''));
        if ($category === '') {
            $errors['category'] = 'Category is required.';
        } elseif (strlen($category) > self::MAX_CATEGORY_LENGTH) {
            $errors['category'] = 'Category must be 50 characters or fewer.';
        }

        $price = filter_var($input['price'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        if ($price === null || $price <= 0 || $price > self::MAX_NUMERIC_VALUE) {
            $errors['price'] = 'Price must be greater than zero and no more than 999999.';
        }

        $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if ($stock === null || $stock <= 0 || $stock > self::MAX_NUMERIC_VALUE) {
            $errors['stock'] = 'Quantity must be a whole number greater than zero and no more than 999999.';
        }

        $active = filter_var($input['active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($active === null) {
            $errors['active'] = 'Active must be true or false.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'sku' => $sku,
            'name' => $name,
            'category' => $category,
            'price' => round((float)$price, 2),
            'stock' => (int)$stock,
            'active' => (bool)$active,
        ];
    }
}
