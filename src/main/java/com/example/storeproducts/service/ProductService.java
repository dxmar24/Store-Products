package com.example.storeproducts.service;

import com.example.storeproducts.model.Product;
import com.example.storeproducts.repository.ProductRepository;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;
import java.util.Optional;

public class ProductService {
    private static final int MAX_SKU_LENGTH = 30;
    private static final int MAX_NAME_LENGTH = 80;
    private static final int MAX_CATEGORY_LENGTH = 50;

    private final ProductRepository productRepository;

    public ProductService() {
        this.productRepository = new ProductRepository();
    }

    public List<Product> listProducts() {
        return productRepository.findAll();
    }

    public Product createProduct(Product product) {
        normalize(product);
        validate(product);
        ensureSkuIsAvailable(product.getSku(), null);
        product.setCreatedAt(Instant.now());
        return productRepository.save(product);
    }

    public Optional<Product> updateProduct(String id, Product changes) {
        normalize(changes);
        validate(changes);
        return productRepository.findById(id).map(existing -> {
            ensureSkuIsAvailable(changes.getSku(), existing.getId());
            existing.setSku(changes.getSku());
            existing.setName(changes.getName());
            existing.setCategory(changes.getCategory());
            existing.setPrice(changes.getPrice());
            existing.setStock(changes.getStock());
            existing.setActive(changes.isActive());
            return productRepository.save(existing);
        });
    }

    public boolean deleteProduct(String id) {
        return productRepository.delete(id);
    }

    private void validate(Product product) {
        if (isBlank(product.getSku())) {
            throw new IllegalArgumentException("SKU is required.");
        }
        if (!product.getSku().matches("[A-Z0-9-]+")) {
            throw new IllegalArgumentException("SKU can only contain uppercase letters, numbers, and hyphens.");
        }
        if (product.getSku().length() > MAX_SKU_LENGTH) {
            throw new IllegalArgumentException("SKU must be 30 characters or fewer.");
        }
        if (isBlank(product.getName())) {
            throw new IllegalArgumentException("Product name is required.");
        }
        if (product.getName().length() > MAX_NAME_LENGTH) {
            throw new IllegalArgumentException("Product name must be 80 characters or fewer.");
        }
        if (isBlank(product.getCategory())) {
            throw new IllegalArgumentException("Category is required.");
        }
        if (product.getCategory().length() > MAX_CATEGORY_LENGTH) {
            throw new IllegalArgumentException("Category must be 50 characters or fewer.");
        }
        if (product.getPrice() == null || product.getPrice().compareTo(BigDecimal.ZERO) <= 0) {
            throw new IllegalArgumentException("Price must be greater than zero.");
        }
        if (product.getStock() <= 0) {
            throw new IllegalArgumentException("Quantity must be greater than zero.");
        }
    }

    private void normalize(Product product) {
        product.setSku(normalizeSku(product.getSku()));
        product.setName(trim(product.getName()));
        product.setCategory(trim(product.getCategory()));
    }

    private void ensureSkuIsAvailable(String sku, String currentId) {
        productRepository.findBySku(sku).ifPresent(existing -> {
            if (currentId == null || !currentId.equals(existing.getId())) {
                throw new IllegalArgumentException("SKU already exists. Use a different SKU.");
            }
        });
    }

    private String normalizeSku(String value) {
        String trimmed = trim(value);
        return trimmed == null ? null : trimmed.toUpperCase();
    }

    private String trim(String value) {
        return value == null ? null : value.trim();
    }

    private boolean isBlank(String value) {
        return value == null || value.isBlank();
    }
}
