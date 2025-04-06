<!-- resources/views/pages/home.blade.php -->
@extends('layouts.app')

@section('header', 'Mitgliederliste')

@section('content')
    <div class="flex p-4 space-x-2">

        <main class="bg-white rounded-lg p-4 shadow-lg flex-1">
            <div class="flex p-4 space-x-4">
                @livewire('members-table')
            </div>
        </main>
    </div>
@endsection
