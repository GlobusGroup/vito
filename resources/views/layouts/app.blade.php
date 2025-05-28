<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Secret Sharing')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff8ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#006FB9',
                            600: '#005a9a',
                            700: '#004a7c',
                            800: '#003a5e',
                            900: '#002a40',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="icon" type="image/png" href="{{ asset('fav.png') }}">
</head>
<body class="bg-gray-50 min-h-screen">
    {{-- Navigation Bar --}}
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('secrets.create') }}" class="flex items-center space-x-2">
                            <img class="h-8 w-auto" src="{{ asset('pass.png') }}" alt="Pass Logo">
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('secrets.create') }}" class="text-gray-600 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150">
                        New Secret
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html> 