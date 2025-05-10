<form wire:submit="submit" class="space-y-2">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Linker Block -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md border">
            <h3 class="text-base font-semibold text-gray-700 mb-4">{{ $action_name }}</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bezeichnung</label>
                    <input type="text" wire:model="action_name"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 active:ring-indigo-500 active:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Datum der Fahrt</label>
                    <input type="date" wire:model="action_date"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ablegen</label>
                        <input type="text" placeholder="00:00" wire:model="action_start_at"
                               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Anlegen</label>
                        <input type="text" placeholder="00:00" wire:model="action_end_at"
                               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

            </div>
        </div>

        <!-- Rechter Block -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Details</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Zusatzinformationen</label>
                    <input type="text" wire:model="additional_info"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Personen gesamt</label>
                    <input type="text" wire:model="ac_max_pers"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

            </div>

        </div>
    </div>

    <!--input type="hidden" wire:model.defer="action_type_sc" value=""-->

    <div class="pt-4">
        <button wire:click="save" type="button" wire:confirm="Änderung speichern?"
                class="justify-center px-6 py-2 rounded shadow-lg text-white bg-indigo-600 hover:bg-indigo-800">
            Änderungen speichern
        </button>
    </div>

</form>
