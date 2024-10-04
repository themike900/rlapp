<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Detail-Ansicht einer Fahrt') }}
        </h2>
    </x-slot>

    <div class="max-w-xl mx-auto p-4 sm:p-6 lg:p-8 mt-6 bg-white shadow-sm rounded-lg">
        <div class="grid grid-cols-2 gap-2">
            <div>Type der Fahrt:</div> <div>{{ $action->action_type }}</div>
            <div>Datum der Fahrt:</div> <div>{{ __(date_format(date_create($action->action_date), 'D')) }} {{ date_format(date_create($action->action_date), 'j. M') }}</div>
            <div>Crew-Zeit:t:</div> <div>{{ $action->crew_start_at }}-{{ $action->crew_end_at }}</div>
            <div>Fahrt-Zeit:</div> <div>{{ $action->action_start_at }}-{{ $action->action_start_at }}</div>

            <form method="POST" action="{{ route('actions.store') }}">
                <x-primary-button class="mt-4 bg-blue-800 hover:bg-blue-500">{{ __('Bearbeiten') }}</x-primary-button>
            </form>

        </div>
    </div>

</x-app-layout>
