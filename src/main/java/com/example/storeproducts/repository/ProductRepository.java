package com.example.storeproducts.repository;

import com.example.storeproducts.config.AppConfig;
import com.example.storeproducts.model.Product;
import dev.morphia.Datastore;
import dev.morphia.query.filters.Filters;
import org.bson.types.ObjectId;

import java.util.List;
import java.util.Optional;

public class ProductRepository {
    private final Datastore datastore;

    public ProductRepository() {
        this.datastore = AppConfig.datastore();
    }

    public List<Product> findAll() {
        return datastore.find(Product.class).iterator().toList();
    }

    public Optional<Product> findById(String id) {
        if (id == null || !ObjectId.isValid(id)) {
            return Optional.empty();
        }
        return Optional.ofNullable(datastore.find(Product.class)
                .filter(Filters.eq("_id", new ObjectId(id)))
                .first());
    }

    public Product save(Product product) {
        datastore.save(product);
        return product;
    }

    public boolean delete(String id) {
        Optional<Product> product = findById(id);
        product.ifPresent(datastore::delete);
        return product.isPresent();
    }
}
