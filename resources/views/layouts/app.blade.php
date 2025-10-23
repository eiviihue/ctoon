<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'CToon') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--gray-800);
            background: var(--gray-100);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        .header {
            background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .nav-brand {
            font-size: 1.75rem;
            font-weight: 700;
            text-decoration: none;
            color: var(--primary);
            letter-spacing: -0.025em;
        }
        .search-form {
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 9999px;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s;
        }
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .nav-link {
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 0.875rem;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: var(--primary);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .comic-detail {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="{{ url('/') }}" class="nav-brand">CToon</a>
            <form action="{{ url('/comics') }}" method="GET" class="search-form">
                <input type="search" name="search" placeholder="Search comics..." class="search-input" value="{{ request('search') }}">
            </form>
            <div class="nav-links">
                <a href="{{ url('/comics') }}" class="nav-link">Comics</a>
                @auth
                    <a href="{{ url('/bookmarks') }}" class="nav-link">Bookmarks</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="nav-link" style="border:0;background:none;cursor:pointer">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn">Login</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>

    <footer class="container" style="text-align: center; padding-top: 3rem; color: var(--gray-600);">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'CToon') }}</p>
    </footer>
</body>
</html>