<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Neue Fahrt anlegen') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('actions.store') }}">
            @csrf
			Type der Fahrt: <input name="action_type"
				type="text"
                placeholder="{{ __('Fahrtentyp') }}"
                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
			Datum der Fahrt: <input name="action_date"
				type="text"
                placeholder="{{ __('yyyy-mm-dd') }}"
                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
			Crew Startzeit: <input name="crew_start_at"
				type="text"
                placeholder="{{ __('hh:mm') }}"
                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
			Crew Endezeit: <input name="crew_end_at"
				type="text"
                placeholder="{{ __('hh:mm') }}"
                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
			Fahrt Startzeit: <input name="action_start_at"
				type="text"
                placeholder="{{ __('hh:mm') }}"
                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
			Fahrt Endezeit: <input name="action_end_at"
				type="text"
                placeholder="{{ __('hh:mm') }}"
                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <x-primary-button class="mt-4">{{ __('Speichern') }}</x-primary-button>
        </form>
    </div>
	
</x-app-layout>
