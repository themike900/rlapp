<!-- resources/views/pages/home.blade.php -->
@extends('layouts.rl-app')

@section('content')
    <!-- Zweiter Header mit dynamischer Überschrift -->
    <header class="bg-gray-100 p-4">
        <h2 class="text-xl font-semibold">Mitglied bearbeiten</h2>
    </header>

    <div class="flex p-4 space-x-4">
        <!-- Menü auf der linken Seite -->
        @include('rl-views.rl-menu')

        <!-- Content auf der rechten Seite -->
        <main class="bg-white rounded-lg p-4 shadow-lg flex-1">
            <p>Willkommen auf der Startseite! Hier findest du alle wichtigen Informationen.</p>
        </main>
    </div>
@endsection
