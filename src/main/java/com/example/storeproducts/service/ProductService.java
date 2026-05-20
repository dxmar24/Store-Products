package com.example.storeproducts.service;

import com.example.storeproducts.model.Product;
import com.example.storeproducts.repository.ProductRepository;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;
import java.util.Optional;

public class ProductService {
    private final ProductRepository productRepository;

    public ProductService() {
        this.productRepository = new ProductRepository();
    }

    public List<Product> listProducts() {
        return productRepository.findAll();
    }

    public Product createProduct(Product product) {
        validate(product);
        product.setCreatedAt(Instant.now());
        return productRepository.save(product);
    }

    public Optional<Product> updateProduct(String id, Product changes) {
        validate(changes);
        return productRepository.findById(id).map(existing -> {
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
        if (isBlank(product.getName())) {
            throw new IllegalArgumentException("Product name is required.");
        }
        if (isBlank(product.getCategory())) {
            throw new IllegalArgumentException("Category is required.");
        }
        if (product.getPrice() == null || product.getPrice().compareTo(BigDecimal.ZERO) < 0) {
            throw new IllegalArgumentException("Price must be zero or greater.");
        }
        if (product.getStock() < 0) {
            throw new IllegalArgumentException("Stock must be zero or greater.");
        }
    }

    private boolean isBlank(String value) {
        return value == null || value.isBlank();
    }
}
