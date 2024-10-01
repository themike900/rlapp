<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Royal-Louise') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-300">
		
            <!-- Page Heading -->
            <header class="bg-blue-600 shadow">
                <div class="max-w-7xl mx-auto py-4 px-5 sm:px-6 lg:px-8">
					<h2 class="font-semibold text-xl text-gray-200 leading-tight">
						{{ __('Royal-Louise Aktivitätenplanung') }}
					</h2>
                </div>
            </header>
			
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-blue-100 shadow">
                    <div class="max-w-7xl mx-auto py-4 px-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
