<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

		<link rel="shortcut icon" href="favicon.png" />

        <!-- Fonts -->
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-200">
			<div>
				<h2 class="text-3xl font-bold text-gray-500 py-6">ROYAL-LOUISE Aktivitätenplanung</h2>
			</div>
            <div>
                <a href="/">
					<img class="w-32" src="{{ asset('/images/rl-icon.jpg') }}" alt="">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
