<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    @keydown.escape.window="show = false"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <p class="text-xl font-semibold mb-4">Status ändern</p>

        <div class="flex flex-col mb-4 space-y-2">
            <label class="flex items-center space-x-3">
                <input type="radio" name="state" value="iv" wire:model="action_state_sc"
                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-700">in Vorbereitung/Bearbeitung</span>
            </label>

            <label class="flex items-center space-x-3">
                <input type="radio" name="state" value="br" wire:model="action_state_sc"
                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-700">bereit zum Veröffentlichen</span>
            </label>

            <label class="flex items-center space-x-3">
                <input type="radio" name="state" value="of" wire:model="action_state_sc"
                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-700">veröffentlicht, offen für Anmeldungen</span>
            </label>

            <label class="flex items-center space-x-3">
                <input type="radio" name="state" value="gs" wire:model="action_state_sc"
                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-700">veröffentlicht, geschlossen, keine Anmeldungen mehr</span>
            </label>

            <label class="flex items-center space-x-3">
                <input type="radio" name="state" value="df" wire:model="action_state_sc"
                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-700">durchgeführt, nicht mehr sichtbar</span>
            </label>

            <label class="flex items-center space-x-3">
                <input type="radio" name="state" value="as" wire:model="action_state_sc"
                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-700">abgeschlossen, nicht mehr sichtbar</span>
            </label>

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
