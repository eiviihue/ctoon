package com.ctoon.rest;

import com.ctoon.dto.AuthRequest;
import com.ctoon.dto.AuthResponse;
import com.ctoon.services.AuthService;
import com.google.gson.Gson;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;

@WebServlet("/api/auth/*")
public class AuthServlet extends HttpServlet {
    private AuthService authService;
    private Gson gson = new Gson();

    @Override
    public void init() {
        authService = new AuthService();
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws IOException {
        response.setContentType("application/json");
        response.setCharacterEncoding("UTF-8");

        String pathInfo = request.getPathInfo();

        try {
            if ("/register".equals(pathInfo)) {
                handleRegister(request, response);
            } else if ("/login".equals(pathInfo)) {
                handleLogin(request, response);
            } else {
                response.setStatus(HttpServletResponse.SC_NOT_FOUND);
                response.getWriter().write("{\"success\": false, \"message\": \"Endpoint not found\"}");
            }
        } catch (Exception e) {
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write(gson.toJson(new AuthResponse(false, "Server error: " + e.getMessage())));
        }
    }

    private void handleRegister(HttpServletRequest request, HttpServletResponse response) throws IOException {
        StringBuilder sb = new StringBuilder();
        String line;
        try (var reader = request.getReader()) {
            while ((line = reader.readLine()) != null) {
                sb.append(line);
            }
        }

        AuthRequest authRequest = gson.fromJson(sb.toString(), AuthRequest.class);
        AuthResponse authResponse = authService.register(authRequest);

        response.setStatus(
                authResponse.isSuccess() ? HttpServletResponse.SC_CREATED : HttpServletResponse.SC_BAD_REQUEST);
        response.getWriter().write(gson.toJson(authResponse));
    }

    private void handleLogin(HttpServletRequest request, HttpServletResponse response) throws IOException {
        StringBuilder sb = new StringBuilder();
        String line;
        try (var reader = request.getReader()) {
            while ((line = reader.readLine()) != null) {
                sb.append(line);
            }
        }

        AuthRequest authRequest = gson.fromJson(sb.toString(), AuthRequest.class);
        AuthResponse authResponse = authService.login(authRequest);

        response.setStatus(authResponse.isSuccess() ? HttpServletResponse.SC_OK : HttpServletResponse.SC_UNAUTHORIZED);
        response.getWriter().write(gson.toJson(authResponse));
    }
}
