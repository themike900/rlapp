<!-- resources/views/pages/home.blade.php -->
@extends('layouts.app')

@section('header', 'Import Mitglieder aus MeinVerein')

@section('content')
    <div class="flex p-4 space-x-2">

            @if(session('success'))
                <div>{{ session('success') }}</div>
            @endif

            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input id="file-upload" type="file" name="file" class="cursor-pointer space-x-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <button type="submit" class="px-4 py-2 border text-white bg-blue-600 rounded-md">Import Excel</button>
            </form>


    </div>
@endsection
