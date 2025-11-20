<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CToon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="${pageContext.request.contextPath}/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2><i class="fas fa-book-reader"></i> CToon</h2>
            <p class="text-muted">Welcome back! Sign in to your account</p>
            
            <form id="loginForm">
                <div class="form-group mb-3">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    <small class="form-text text-muted">We'll never share your email.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <div id="alertContainer"></div>

                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <button type="button" class="btn btn-outline-primary w-100">
                    <i class="fab fa-google"></i> Sign in with Google
                </button>
            </form>

            <div class="auth-link">
                Don't have an account? <a href="${pageContext.request.contextPath}/register.jsp">Sign up here</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('${pageContext.request.contextPath}/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success) {
                    // Store token and user info
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    
                    // Show success message
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '${pageContext.request.contextPath}/index.jsp';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Login failed', 'danger');
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
