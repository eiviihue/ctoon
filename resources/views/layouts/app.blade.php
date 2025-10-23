<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CToon') }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/rating.css') }}">
    <!-- Custom Styles -->
    <style>
        .lazy {
            opacity: 0;
            transition: opacity 0.3s ease-in;
        }
        .lazy.loaded {
            opacity: 1;
        }
        .card-img-top {
            height: 300px;
            object-fit: cover;
            background-color: #f8f9fa;
        }
        [data-bs-theme="dark"] .card-img-top {
            background-color: #343a40;
        }
    </style>
    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="{{ asset('js/bookmark.js') }}" defer></script>
    <script src="{{ asset('js/lazy-load.js') }}" defer></script>
    <script src="{{ asset('js/dark-mode.js') }}" defer></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">CToon</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <form class="d-none d-md-flex mx-3 flex-grow-1" action="{{ url('/comics') }}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" placeholder="Search comics..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <button class="btn btn-outline-secondary" type="button" data-theme-toggle aria-label="Toggle theme">
                            <i class="fas fa-sun"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/comics') }}">Comics</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/bookmarks') }}">Bookmarks</a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <footer class="container-fluid py-4 text-center text-muted mt-5">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'CToon') }}</p>
        </div>
    </footer>
</body>
</html>