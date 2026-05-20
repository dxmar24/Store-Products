package com.example.storeproducts.model;

import dev.morphia.annotations.Entity;
import dev.morphia.annotations.Id;
import dev.morphia.annotations.Indexed;
import org.bson.types.ObjectId;

import java.math.BigDecimal;
import java.time.Instant;

@Entity(value = "products", useDiscriminator = false)
public class Product {
    @Id
    private ObjectId id;

    @Indexed
    private String sku;

    private String name;
    private String category;
    private BigDecimal price;
    private int stock;
    private boolean active;
    private Instant createdAt;

    public Product() {
        this.active = true;
        this.createdAt = Instant.now();
    }

    public String getId() {
        return id == null ? null : id.toHexString();
    }

    public ObjectId getObjectId() {
        return id;
    }

    public void setObjectId(ObjectId id) {
        this.id = id;
    }

    public String getSku() {
        return sku;
    }

    public void setSku(String sku) {
        this.sku = sku;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getCategory() {
        return category;
    }

    public void setCategory(String category) {
        this.category = category;
    }

    public BigDecimal getPrice() {
        return price;
    }

    public void setPrice(BigDecimal price) {
        this.price = price;
    }

    public int getStock() {
        return stock;
    }

    public void setStock(int stock) {
        this.stock = stock;
    }

    public boolean isActive() {
        return active;
    }

    public void setActive(boolean active) {
        this.active = active;
    }

    public Instant getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(Instant createdAt) {
        this.createdAt = createdAt;
    }
}
