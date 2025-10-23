<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CToon') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Static assets served from public/ (Vite removed) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rating.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <header class="site-header bg-white dark:bg-gray-900/60 sticky top-0 z-50 border-b border-gray-200 dark:border-gray-800 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="brand text-2xl font-bold">CToon</a>
                </div>

                <div class="hidden md:block md:flex-1 md:px-6">
                    <form action="{{ url('/comics') }}" method="GET" class="w-full">
                        <input type="search" name="search" placeholder="Search comics..." value="{{ request('search') }}" class="input" />
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" data-theme-toggle aria-label="Toggle theme" class="p-2 rounded-md border border-transparent hover:border-gray-200 dark:hover:border-gray-700 transition">
                        <svg class="h-6 w-6 text-gray-700 dark:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-9H21m-18 0H2.34M18.36 5.64l-.7.7M6.34 17.66l-.7.7M18.36 18.36l-.7-.7M6.34 6.34l-.7-.7M12 7a5 5 0 100 10 5 5 0 000-10z" />
                        </svg>
                    </button>
                    <a href="{{ url('/comics') }}" class="nav-link">Comics</a>
                    @auth
                        <a href="{{ url('/bookmarks') }}" class="nav-link">Bookmarks</a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="nav-link">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="container-fluid mt-8">
        @yield('content')
    </main>

    <footer class="mt-12 py-8 text-center text-sm text-gray-600 dark:text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'CToon') }}</p>
        </div>
    </footer>
</body>
</html>