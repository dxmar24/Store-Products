package com.example.storeproducts.controller;

import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;

public class HomeController extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        if ("/table".equals(request.getServletPath())) {
            request.setAttribute("pageTitle", "Store Products Table");
            request.getRequestDispatcher("/WEB-INF/views/table.jsp").forward(request, response);
            return;
        }

        request.setAttribute("pageTitle", "Store Products");
        request.getRequestDispatcher("/WEB-INF/views/index.jsp").forward(request, response);
    }
}
