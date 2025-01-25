<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            Neue Fahrt anlegen
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto p-2 mb-2 border bg-blue-200 rounded-md">
    <form action="{{ route('actions.create') }}" method="post">
        @csrf
        <div class="flex gap-4 p-2 border">
            <x-dropdown2 name="type" :options="['gf' => 'Gästefahrt', 'vf' => 'Vereinsfahrt']" :selected="old('type',$def_vals['selected'])"/>
            <button type="submit" class="bg-blue-500 text-white font-semibold py-1 px-3 rounded-md shadow hover:bg-blue-600 focus:ring-2">Aktualisieren</button>
        </div>
    </form>
        <script>

        </script>
    </div>


    <div class="max-w-2xl mx-auto p-2 border rounded-md">
        <div class="grid grid-cols-1">
        <form method="POST" action="{{ route('actions.store') }}">
            @csrf
            <label class="block p-1 w-80 border">Bezeichnung: <input name="action_type"
				type="text" maxlength="30"
                class="max-w-36 p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                value="{{ $def_vals['action_type'] }}"
                ></label>
            <label class="block p-1">Datum der Fahrt: <input name="action_date"
				type="text" min="10" max="10"
                placeholder="yyyy-mm-dd"
                class="form-control"
                ></label>
            <label class="block">Crew Startzeit: <input name="crew_start_at"
				type="text" maxlength="10"
                placeholder="hh:mm"
                class="w-20 p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                ></label>
            <label class="block">Crew Endezeit: <input name="crew_end_at"
				type="text" maxlength="10"
                placeholder="hh:mm"
                class="w-20 p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                ></label>
			<label class="block">Fahrt Startzeit: <input name="action_start_at"
				type="text" maxlength="10"
                placeholder="hh:mm"
                class="w-20 p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                ></label>
            <label class="block">Fahrt Endezeit:
                <input name="action_end_at"
                    type="text" maxlength="10"
                    placeholder="hh:mm"
                    class="w-20 p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
            </label>
            <label class="block p-1 border">max Gäste
                <input name="guests_max"
                     type="number" min="0" max="25"
                     class="w-12 p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                     value="{{ old('guests_max', $def_vals['guests_max']) }}">
            </label>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <x-primary-button class="mt-4">Speichern</x-primary-button>
        </form>
        </div>
    </div>

    <script>
        document.addEventListener('DomContentLoaded', function () {
            $('datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>

</x-app-layout>
