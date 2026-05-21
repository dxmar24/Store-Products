<?php

declare(strict_types=1);

namespace StoreProducts;

use JsonException;
use Throwable;

final class ProductController
{
    private ?ProductRepository $products;

    public function __construct(?ProductRepository $products = null)
    {
        $this->products = $products;
    }

    public function handle(string $method, array $segments): void
    {
        try {
            match ($method) {
                'GET' => $this->index(),
                'POST' => $this->store(),
                'PUT', 'PATCH' => $this->update($segments[0] ?? ''),
                'DELETE' => $this->destroy($segments[0] ?? ''),
                default => Response::error('Method not allowed.', 405),
            };
        } catch (ValidationException $exception) {
            Response::error('Please check the form fields.', 422, $exception->errors());
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Response::error('Server error. Check MongoDB configuration and logs.', 503);
        }
    }

    private function index(): void
    {
        Response::ok($this->products()->all());
    }

    private function store(): void
    {
        $product = ProductValidator::validate($this->readJsonBody());
        if ($this->products()->findBySku($product['sku']) !== null) {
            throw new ValidationException(['sku' => 'SKU already exists. Use a different SKU.']);
        }

        Response::ok($this->products()->create($product), 201);
    }

    private function update(string $id): void
    {
        if ($id === '') {
            Response::error('Product ID is required.', 400);
            return;
        }

        $existing = $this->products()->findById($id);
        if ($existing === null) {
            Response::error('Product was not found.', 404);
            return;
        }

        $product = ProductValidator::validate($this->readJsonBody());
        $sameSku = $this->products()->findBySku($product['sku']);
        if ($sameSku !== null && $sameSku['id'] !== $id) {
            throw new ValidationException(['sku' => 'SKU already exists. Use a different SKU.']);
        }

        Response::ok($this->products()->update($id, $product));
    }

    private function destroy(string $id): void
    {
        if ($id === '') {
            Response::error('Product ID is required.', 400);
            return;
        }

        if (!$this->products()->delete($id)) {
            Response::error('Product was not found.', 404);
            return;
        }

        Response::ok(['deleted' => true]);
    }

    private function products(): ProductRepository
    {
        if ($this->products === null) {
            $this->products = new ProductRepository();
        }

        return $this->products;
    }

    private function readJsonBody(): array
    {
        $body = file_get_contents('php://input');
        if ($body === false || trim($body) === '') {
            throw new ValidationException(['body' => 'Request body is required.']);
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ValidationException(['body' => 'Request body must be valid JSON.']);
        }

        if (!is_array($data)) {
            throw new ValidationException(['body' => 'Request body must be a JSON object.']);
        }

        return $data;
    }
}
