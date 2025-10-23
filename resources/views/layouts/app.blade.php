<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'CToon') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        .header {
            background: var(--card-background);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.9);
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
            font-family: var(--font-heading);
            font-size: 1.875rem;
            font-weight: 700;
            text-decoration: none;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.025em;
            transition: all 0.3s ease;
        }
        
        .nav-brand:hover {
            transform: scale(1.05);
        }
        
        .search-form {
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem 1.25rem;
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-radius: 9999px;
            font-size: 0.875rem;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1);
            background-color: white;
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(109, 40, 217, 0.1);
        }
        
        .btn-nav {
            background-color: var(--primary-color);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(109, 40, 217, 0.2);
        }
        
        .btn-nav:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(109, 40, 217, 0.3);
        }
        
        .comic-detail {
            background: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            margin-top: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .comic-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
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
                    <a href="{{ route('login') }}" class="btn-nav">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="width: 1.25rem; height: 1.25rem;">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Login
                    </a>
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