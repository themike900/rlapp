<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Liste der Fahrten') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto p-4">

        <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @foreach ($actions as $action)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-rows-2 md:grid-flow-col p-2 space-x-1 justify-between items-center hover:bg-gray-100 hover:rounded-lg">
                    <div class="mx-2 w-30 text-xl text-gray-800">
                        {{ __(date_format(date_create($action->action_date), 'D')) }} {{ date_format(date_create($action->action_date), 'j. M') }}
                    </div>
                    <div class="mt-2 font-bold text-xl text-gray-900">
                        {{ $action->action_type }}
                    </div>
                    <div class="mx-2 p-1 bg-gray-200 text-sm text-gray-800 rounded-md">
                        Crew: {{ $action->crew_start_at }}-{{ $action->crew_end_at }}
                    </div>
                    <div class="mx-2 p-1 bg-gray-200 text-sm text-gray-400 rounded-md">
                        Fahrt: {{ $action->action_start_at }}-{{ $action->action_end_at }}
                    </div>
                    <div class="mx-2 p-1 w-40 bg-green-100 rounded-md">
                        Anmeldung offen
                    </div>
                    <div class="mx-2 p-1 w-40 text-red-400 rounded-md">
                        ich bin angemeldet
                    </div>
                    <div class="mx-2 p-1 mx-2 w-30 text-white bg-blue-800 hover:bg-blue-600 rounded-md">
                        <a href="{{ route('actions.show', $action) }}">Details</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
