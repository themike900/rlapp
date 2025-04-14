<!-- resources/views/livewire/edit-modal.blade.php -->

<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <p class="text-xl font-semibold mb-4">Bearbeiten:</p>

        <p>Platzhalter für Bearbeitung einer Aktivität</p>


        <button wire:click="close" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Abbrechen
        </button>
        <button wire:click="save" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Änderungen speichern
        </button>

    </div>
</div>
