<div>
    <!-- Button zum Öffnen des Modals -->
    <button wire:click="open" class="px-4 py-2 bg-blue-500 text-white rounded">Modal öffnen</button>

    <!-- Modal-Overlay -->
    @if($isOpen)
        <div class="fixed inset-0 flex items-center justify-center g-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                <h2 class="text-xl font-bold mb-4">Livewire Modal</h2>
                <p>Dies ist ein einfaches Modal mit Livewire.</p>

                <button wire:click="close" class="mt-4 px-4 py-2 bg-red-500 text-white rounded py">
                    Schließen
                </button>
            </div>
        </div>
    @endif
</div>
