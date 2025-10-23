<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'CToon') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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