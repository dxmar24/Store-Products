package com.example.storeproducts.config;

import com.example.storeproducts.model.Product;
import com.mongodb.ConnectionString;
import com.mongodb.client.MongoClient;
import com.mongodb.client.MongoClients;
import dev.morphia.Datastore;
import dev.morphia.Morphia;

import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.Properties;

public final class AppConfig {
    private static final Properties PROPERTIES = loadProperties();
    private static final Properties DOTENV = loadDotenv();
    private static MongoClient mongoClient;
    private static Datastore datastore;

    private AppConfig() {
    }

    public static synchronized Datastore datastore() {
        if (datastore == null) {
            String uri = property("mongodb.uri");
            String database = property("mongodb.database");

            if (uri == null || uri.isBlank() || uri.contains("${MONGODB_URI}")) {
                throw new IllegalStateException("MongoDB Atlas URI is not configured. Set the MONGODB_URI environment variable.");
            }

            mongoClient = MongoClients.create(new ConnectionString(uri));
            datastore = Morphia.createDatastore(mongoClient, database);
            datastore.getMapper().map(Product.class);
            datastore.ensureIndexes();
        }
        return datastore;
    }

    public static String property(String key) {
        String envName = toEnvName(key);
        String value = System.getenv(envName);
        if (value == null || value.isBlank()) {
            value = DOTENV.getProperty(envName);
        }
        if (value == null || value.isBlank()) {
            value = PROPERTIES.getProperty(key);
        }
        return resolveDefault(value);
    }

    private static String toEnvName(String key) {
        return key.toUpperCase().replace('.', '_');
    }

    private static Properties loadDotenv() {
        Properties properties = new Properties();
        Path dotenv = Path.of(".env");
        if (!Files.exists(dotenv)) {
            return properties;
        }

        try {
            for (String line : Files.readAllLines(dotenv)) {
                String trimmed = line.trim();
                if (trimmed.isEmpty() || trimmed.startsWith("#") || !trimmed.contains("=")) {
                    continue;
                }

                String key = trimmed.substring(0, trimmed.indexOf('=')).trim();
                String value = trimmed.substring(trimmed.indexOf('=') + 1).trim();
                properties.setProperty(key, stripQuotes(value));
            }
        } catch (IOException exception) {
            throw new IllegalStateException("Unable to load .env file.", exception);
        }
        return properties;
    }

    private static String stripQuotes(String value) {
        if ((value.startsWith("\"") && value.endsWith("\"")) || (value.startsWith("'") && value.endsWith("'"))) {
            return value.substring(1, value.length() - 1);
        }
        return value;
    }

    private static String resolveDefault(String value) {
        if (value == null) {
            return null;
        }
        if (value.startsWith("${") && value.endsWith("}") && value.contains(":")) {
            String expression = value.substring(2, value.length() - 1);
            return expression.substring(expression.indexOf(':') + 1);
        }
        return value;
    }

    private static Properties loadProperties() {
        Properties properties = new Properties();
        try (InputStream input = AppConfig.class.getClassLoader().getResourceAsStream("application.properties")) {
            if (input != null) {
                properties.load(input);
            }
        } catch (IOException exception) {
            throw new IllegalStateException("Unable to load application properties.", exception);
        }
        return properties;
    }

    public static synchronized void close() {
        if (mongoClient != null) {
            mongoClient.close();
        }
    }
}
