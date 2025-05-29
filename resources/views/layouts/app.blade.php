<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vito')</title>
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
    <link rel="icon" type="image/svg+xml" href="{{ asset('fav.svg') }}">
</head>
<body class="bg-gray-50 min-h-screen">
    {{-- Navigation Bar --}}
    @if(!Route::is('secrets.create'))
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20 lg:h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="{{ route('secrets.create') }}" class="flex items-center">
                            <img class="h-16 lg:h-8 w-auto transition-all duration-200 hover:scale-105" 
                                 src="{{ asset('vito_text_2.png') }}" 
                                 alt="Vito Logo"
                                 style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('secrets.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-150 shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Secret
                    </a>
                </div>
            </div>
        </div>
    </nav>
    @endif

    {{-- Main Content --}}
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html> 