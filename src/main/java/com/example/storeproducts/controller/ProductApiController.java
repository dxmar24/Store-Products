package com.example.storeproducts.controller;

import com.example.storeproducts.model.Product;
import com.example.storeproducts.service.ProductService;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.SerializationFeature;
import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.util.Map;
import java.util.Optional;

public class ProductApiController extends HttpServlet {
    private final ObjectMapper mapper = new ObjectMapper()
            .registerModule(new JavaTimeModule())
            .disable(SerializationFeature.WRITE_DATES_AS_TIMESTAMPS);

    private ProductService productService;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws IOException {
        try {
            writeJson(response, HttpServletResponse.SC_OK, service().listProducts());
        } catch (Throwable exception) {
            writeException(response, HttpServletResponse.SC_SERVICE_UNAVAILABLE, exception);
        }
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws IOException {
        try {
            Product product = mapper.readValue(request.getInputStream(), Product.class);
            writeJson(response, HttpServletResponse.SC_CREATED, service().createProduct(product));
        } catch (IllegalArgumentException exception) {
            writeError(response, HttpServletResponse.SC_BAD_REQUEST, exception.getMessage());
        } catch (Throwable exception) {
            writeException(response, HttpServletResponse.SC_SERVICE_UNAVAILABLE, exception);
        }
    }

    @Override
    protected void doPut(HttpServletRequest request, HttpServletResponse response) throws IOException {
        String id = productId(request);
        try {
            Product changes = mapper.readValue(request.getInputStream(), Product.class);
            Optional<Product> updated = service().updateProduct(id, changes);
            if (updated.isPresent()) {
                writeJson(response, HttpServletResponse.SC_OK, updated.get());
            } else {
                writeError(response, HttpServletResponse.SC_NOT_FOUND, "Product was not found.");
            }
        } catch (IllegalArgumentException exception) {
            writeError(response, HttpServletResponse.SC_BAD_REQUEST, exception.getMessage());
        } catch (Throwable exception) {
            writeException(response, HttpServletResponse.SC_SERVICE_UNAVAILABLE, exception);
        }
    }

    @Override
    protected void doDelete(HttpServletRequest request, HttpServletResponse response) throws IOException {
        try {
            boolean deleted = service().deleteProduct(productId(request));
            if (deleted) {
                response.setStatus(HttpServletResponse.SC_NO_CONTENT);
            } else {
                writeError(response, HttpServletResponse.SC_NOT_FOUND, "Product was not found.");
            }
        } catch (Throwable exception) {
            writeException(response, HttpServletResponse.SC_SERVICE_UNAVAILABLE, exception);
        }
    }

    private String productId(HttpServletRequest request) {
        String pathInfo = request.getPathInfo();
        if (pathInfo == null || pathInfo.length() <= 1) {
            return "";
        }
        return pathInfo.substring(1);
    }

    private ProductService service() {
        if (productService == null) {
            productService = new ProductService();
        }
        return productService;
    }

    private void writeJson(HttpServletResponse response, int status, Object body) throws IOException {
        response.setStatus(status);
        response.setContentType("application/json");
        response.setCharacterEncoding("UTF-8");
        mapper.writeValue(response.getWriter(), body);
    }

    private void writeError(HttpServletResponse response, int status, String message) throws IOException {
        writeJson(response, status, Map.of("message", message == null ? "Unexpected server error." : message));
    }

    private void writeException(HttpServletResponse response, int status, Throwable exception) throws IOException {
        String message = exception.getMessage();
        if (message == null || message.isBlank()) {
            message = exception.getClass().getSimpleName();
        }
        writeError(response, status, message);
    }
}
