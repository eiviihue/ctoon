package com.ctoon.services;

import com.ctoon.dto.AuthRequest;
import com.ctoon.dto.AuthResponse;
import com.ctoon.dto.UserDTO;
import com.ctoon.entities.Profile;
import com.ctoon.entities.User;
import com.ctoon.security.JwtTokenProvider;
import com.ctoon.util.PasswordUtil;
import jakarta.persistence.EntityManager;
import jakarta.persistence.PersistenceContext;
import jakarta.persistence.Query;
import jakarta.transaction.Transactional;
import java.time.LocalDateTime;

public class AuthService {
    @PersistenceContext
    private EntityManager entityManager;

    @Transactional
    public AuthResponse register(AuthRequest request) {
        // Validate input
        if (request.getEmail() == null || request.getEmail().isEmpty()) {
            return new AuthResponse(false, "Email is required");
        }
        if (request.getPassword() == null || request.getPassword().length() < 8) {
            return new AuthResponse(false, "Password must be at least 8 characters");
        }
        if (!request.getPassword().equals(request.getPasswordConfirmation())) {
            return new AuthResponse(false, "Passwords do not match");
        }

        // Check if email already exists
        Query query = entityManager.createQuery("SELECT COUNT(u) FROM User u WHERE u.email = :email");
        query.setParameter("email", request.getEmail());
        Long count = (Long) query.getSingleResult();
        if (count > 0) {
            return new AuthResponse(false, "Email already registered");
        }

        // Create new user
        User user = new User();
        user.setName(request.getName());
        user.setEmail(request.getEmail());
        user.setPassword(PasswordUtil.hashPassword(request.getPassword()));

        entityManager.persist(user);
        entityManager.flush();

        // Create user profile
        Profile profile = new Profile();
        profile.setUser(user);
        entityManager.persist(profile);
        entityManager.flush();

        // Generate JWT token
        String token = JwtTokenProvider.generateToken(user.getId(), user.getEmail());
        UserDTO userDTO = new UserDTO(user.getId(), user.getName(), user.getEmail(), user.getCreatedAt());

        return new AuthResponse(true, "Registration successful", token, userDTO);
    }

    @Transactional
    public AuthResponse login(AuthRequest request) {
        // Validate input
        if (request.getEmail() == null || request.getEmail().isEmpty()) {
            return new AuthResponse(false, "Email is required");
        }
        if (request.getPassword() == null || request.getPassword().isEmpty()) {
            return new AuthResponse(false, "Password is required");
        }

        // Find user by email
        Query query = entityManager.createQuery("SELECT u FROM User u WHERE u.email = :email");
        query.setParameter("email", request.getEmail());

        try {
            User user = (User) query.getSingleResult();

            // Verify password
            if (!PasswordUtil.verifyPassword(request.getPassword(), user.getPassword())) {
                return new AuthResponse(false, "Invalid email or password");
            }

            // Generate JWT token
            String token = JwtTokenProvider.generateToken(user.getId(), user.getEmail());
            UserDTO userDTO = new UserDTO(user.getId(), user.getName(), user.getEmail(), user.getCreatedAt());

            return new AuthResponse(true, "Login successful", token, userDTO);
        } catch (Exception e) {
            return new AuthResponse(false, "Invalid email or password");
        }
    }

    public User getUserById(Long userId) {
        return entityManager.find(User.class, userId);
    }

    public User getUserByEmail(String email) {
        Query query = entityManager.createQuery("SELECT u FROM User u WHERE u.email = :email");
        query.setParameter("email", email);
        try {
            return (User) query.getSingleResult();
        } catch (Exception e) {
            return null;
        }
    }
}
