<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Royal-Louise') }}</title>

		<link rel="shortcut icon" href="{{ config('app.url') }}/favicon.png" />

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
					<h2 class="font-semibold text-2xl text-gray-200 leading-tight">
						{{ 'Royal-Louise Aktivitätenplanung' }}
					</h2>
                </div>
            </header>
			<div class="max-w-4xl mx-auto p-4 ">

				<div class="mt-6 bg-white shadow- rounded-lg">
						<div class="grid-cols-1 gap-4 p-6 divide-y">
							<div class="p-4">
								<h2 class="text-xl font-semibold">Einstieg</h2>
							</div>
							@if (Route::has('login'))

							@auth
							<div class="p-4">
								<span>Du bist noch angemeldet</span>
								<a
									href="{{ route('actions.index') }}"
									class="rounded-md px-3 py-2 text-black ring-1 transition hover:bg-blue-100 focus:outline-none focus-visible:ring-[#FF2D20]"
								>
									Zur Fahrtenliste
								</a>
							</div>
							@else
							<div class="p-4">
								<a
									href="{{ route('login') }}"
									class="rounded-md px-3 py-2 text-black ring-1 transition hover:bg-blue-100 focus:outline-none focus-visible:ring-[#FF2D20]"
								>
									Anmeldung (Login)
								</a>
							</div>
							<div class="p-4">
								<a
									href="{{ route('register') }}"
									class="rounded-md px-3 py-2 text-black ring-1 transition hover:bg-blue-100 focus:outline-none focus-visible:ring-[#FF2D20]"
								>
									Registrierung als neuer Nutzer
								</a>
							</div>
							@endauth
							@endif
						</div>
				</div>
			</div>
        </div>
    </body>
</html>
