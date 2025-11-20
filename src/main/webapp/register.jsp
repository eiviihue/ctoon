<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CToon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="${pageContext.request.contextPath}/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2><i class="fas fa-book-reader"></i> Create Account</h2>
            <p class="text-muted">Join CToon and discover amazing comics</p>
            
            <form id="registerForm">
                <div class="form-group mb-3">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group mb-3">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    <small class="form-text text-muted">We'll never share your email.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min 8 characters" required>
                    <small class="form-text text-muted">Must be at least 8 characters long.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="passwordConfirmation">Confirm Password</label>
                    <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Confirm your password" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" style="color: #6c5ce7;">Terms & Conditions</a>
                    </label>
                </div>

                <div id="alertContainer"></div>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

                <button type="button" class="btn btn-outline-primary w-100">
                    <i class="fab fa-google"></i> Sign up with Google
                </button>
            </form>

            <div class="auth-link">
                Already have an account? <a href="${pageContext.request.contextPath}/login.jsp">Sign in here</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('passwordConfirmation').value;

            // Validate passwords match
            if (password !== passwordConfirmation) {
                showAlert('Passwords do not match', 'danger');
                return;
            }

            try {
                const response = await fetch('${pageContext.request.contextPath}/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ name, email, password, passwordConfirmation })
                });

                const data = await response.json();

                if (data.success) {
                    // Store token and user info
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    
                    // Show success message
                    showAlert('Registration successful! Redirecting...', 'success');
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '${pageContext.request.contextPath}/index.jsp';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Registration failed', 'danger');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            }
        });

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
    </script>
</body>
</html>
