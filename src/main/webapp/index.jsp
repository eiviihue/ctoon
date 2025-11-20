<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CToon - Comic Reader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="${pageContext.request.contextPath}/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="${pageContext.request.contextPath}/">
                <i class="fas fa-book-reader"></i> CToon
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="${pageContext.request.contextPath}/">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="${pageContext.request.contextPath}/comics.jsp">
                            <i class="fas fa-book"></i> Comics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="${pageContext.request.contextPath}/bookmarks.jsp">
                            <i class="fas fa-bookmark"></i> Bookmarks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="${pageContext.request.contextPath}/profile.jsp">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to CToon</h1>
        <p>Discover and read amazing comics from around the world</p>
        <a href="${pageContext.request.contextPath}/comics.jsp" class="btn btn-outline-light btn-lg">
            <i class="fas fa-search"></i> Explore Comics
        </a>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Latest Comics Section -->
        <div class="mb-5">
            <h2 class="mb-4">
                <i class="fas fa-star" style="color: #ffc107;"></i> Latest Comics
            </h2>
            <div class="row" id="latestComics">
                <div class="col-md-12 text-center">
                    <div class="spinner"></div>
                    <p class="mt-3">Loading comics...</p>
                </div>
            </div>
        </div>

        <!-- Popular Comics Section -->
        <div class="mb-5">
            <h2 class="mb-4">
                <i class="fas fa-fire" style="color: #ff7675;"></i> Popular This Week
            </h2>
            <div class="row" id="popularComics">
                <div class="col-md-12 text-center">
                    <div class="spinner"></div>
                    <p class="mt-3">Loading comics...</p>
                </div>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="mb-5">
            <h2 class="mb-4">
                <i class="fas fa-th"></i> Categories
            </h2>
            <div class="row" id="categories">
                <div class="col-md-12 text-center">
                    <div class="spinner"></div>
                    <p class="mt-3">Loading categories...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-2">&copy; 2025 CToon - Comic Reader Platform</p>
            <p class="mb-0">
                <a href="#" style="color: white; text-decoration: none;">About</a> |
                <a href="#" style="color: white; text-decoration: none;">Contact</a> |
                <a href="#" style="color: white; text-decoration: none;">Privacy</a> |
                <a href="#" style="color: white; text-decoration: none;">Terms</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check if user is logged in
        function checkAuth() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '${pageContext.request.contextPath}/login.jsp';
            }
        }

        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '${pageContext.request.contextPath}/login.jsp';
        }

        // Load comics on page load
        document.addEventListener('DOMContentLoaded', function() {
            // For now, show placeholder content
            // In a real app, you would fetch data from API
            loadLatestComics();
            loadPopularComics();
            loadCategories();
        });

        function loadLatestComics() {
            const container = document.getElementById('latestComics');
            const comics = [
                { title: 'Adventure Quest', author: 'Artist Name', image: 'https://via.placeholder.com/200x300?text=Adventure+Quest', rating: 4.5 },
                { title: 'Mystery Island', author: 'Comic Creator', image: 'https://via.placeholder.com/200x300?text=Mystery+Island', rating: 4.8 },
                { title: 'Sky Warriors', author: 'Story Master', image: 'https://via.placeholder.com/200x300?text=Sky+Warriors', rating: 4.2 },
                { title: 'Lost Realm', author: 'Fantasy Writer', image: 'https://via.placeholder.com/200x300?text=Lost+Realm', rating: 4.7 }
            ];

            container.innerHTML = comics.map((comic, idx) => `
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card">
                        <img src="${comic.image}" class="card-img-top" alt="${comic.title}">
                        <div class="card-body">
                            <h5 class="card-title">${comic.title}</h5>
                            <p class="card-text">${comic.author}</p>
                            <div class="rating">
                                <i class="fas fa-star"></i> ${comic.rating}
                            </div>
                            <button class="btn btn-primary btn-sm mt-3 w-100">
                                <i class="fas fa-book-open"></i> Read Now
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function loadPopularComics() {
            const container = document.getElementById('popularComics');
            const comics = [
                { title: 'Epic Battle', author: 'Battle Master', image: 'https://via.placeholder.com/200x300?text=Epic+Battle', views: '2.5M' },
                { title: 'Love Story', author: 'Romance Writer', image: 'https://via.placeholder.com/200x300?text=Love+Story', views: '3.1M' },
                { title: 'Sci-Fi Future', author: 'Tech Visionary', image: 'https://via.placeholder.com/200x300?text=Sci-Fi+Future', views: '1.8M' },
                { title: 'Dark Mystery', author: 'Thriller Author', image: 'https://via.placeholder.com/200x300?text=Dark+Mystery', views: '2.9M' }
            ];

            container.innerHTML = comics.map((comic, idx) => `
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card">
                        <img src="${comic.image}" class="card-img-top" alt="${comic.title}">
                        <div class="card-body">
                            <h5 class="card-title">${comic.title}</h5>
                            <p class="card-text">${comic.author}</p>
                            <p class="text-muted"><i class="fas fa-eye"></i> ${comic.views} views</p>
                            <button class="btn btn-primary btn-sm mt-3 w-100">
                                <i class="fas fa-book-open"></i> Read Now
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function loadCategories() {
            const container = document.getElementById('categories');
            const categories = [
                { name: 'Action', icon: 'fas-bolt', count: 245 },
                { name: 'Romance', icon: 'fas-heart', count: 178 },
                { name: 'Sci-Fi', icon: 'fas-rocket', count: 156 },
                { name: 'Fantasy', icon: 'fas-wand-magic-sparkles', count: 234 },
                { name: 'Mystery', icon: 'fas-mask', count: 98 },
                { name: 'Horror', icon: 'fas-skull', count: 87 }
            ];

            container.innerHTML = categories.map((cat, idx) => `
                <div class="col-md-2 col-sm-4 col-6 mb-4">
                    <div class="card text-center" style="cursor: pointer; transition: all 0.3s;">
                        <div class="card-body">
                            <i class="fas fa-${cat.icon.split('-').slice(1).join('-')}" style="font-size: 2.5rem; color: #6c5ce7; margin-bottom: 1rem;"></i>
                            <h5 class="card-title">${cat.name}</h5>
                            <p class="text-muted">${cat.count} comics</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    </script>
</body>
</html>
