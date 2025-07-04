<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    @keydown.escape.window="show = false"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <p class="text-xl font-semibold mb-4">Absage {{ $action['action_name'] ?? '' }} am {{ $action['action_date'] ?? '' }}</p>

        <div class="flex flex-col mb-4 space-y-2">

            <label class="block text-sm font-medium text-gray-700">Grund der Absage</label>
            <input type="text" placeholder="Unwetter" wire:model="cancel_reason"
                   class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">

         </div>

        <button wire:click="save" wire:confirm="Änderung speichern?"
                class="px-4 py-2 bg-indigo-300 rounded hover:bg-indigo-400">
            Änderung speichern
        </button>

        <button wire:click="close" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Abbrechen
        </button>

    </div>
</div>
