<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$staticFile = __DIR__ . $path;

if (PHP_SAPI === 'cli-server' && is_file($staticFile)) {
    return false;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use StoreProducts\ProductController;
use StoreProducts\Response;

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$segments = array_values(array_filter(explode('/', trim($path, '/'))));

if (($segments[0] ?? '') === 'api') {
    if (($segments[1] ?? '') === 'products') {
        (new ProductController())->handle($method, array_slice($segments, 2));
        return;
    }

    if (($segments[1] ?? '') === 'health') {
        Response::ok(['status' => 'ok', 'app' => 'store-products-php']);
        return;
    }

    Response::error('API endpoint was not found.', 404);
    return;
}

if ($method !== 'GET') {
    Response::error('Page not found.', 404);
    return;
}

require dirname(__DIR__) . '/src/views/app.php';
