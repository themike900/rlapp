<!-- resources/views/pages/home.blade.php -->
@extends('layouts.rl-app')

@section('content')
    <!-- Zweiter Header mit dynamischer Überschrift -->
    <header class="bg-gray-100 p-2">
        <h2 class="text-xl font-semibold">Liste der Aktivitäten</h2>
    </header>

    <div class="flex p-4 space-x-4">
        <!-- Menü auf der linken Seite -->
        @include('rl-views.rl-menu')

        <!-- Content auf der rechten Seite -->
        <main class="bg-white rounded-lg p-4 shadow-lg flex-1">
            <p>Hier kommt die Liste hin.</p>
        </main>
    </div>
@endsection
