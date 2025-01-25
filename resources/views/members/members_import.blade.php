<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            Mitglieder-Import aus Excel-Tabelle
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto p-2 mb-2 border bg-blue-200 rounded-md">
        @if(session('success'))
            <div>{{ session('success') }}</div>
        @endif
        <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file">
            <button type="submit">Import Excel</button>
        </form>

    </div>

</x-app-layout>
