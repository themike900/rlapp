<!-- resources/views/livewire/edit-modal.blade.php -->

<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
        <h3 class="text-xl font-semibold mb-4">Bearbeiten</h3>

        <div class="mb-2">
            @if (in_array($ac_type, ['gfx','gfm']))
                @livewire('new-gf', ['selectedForm' => $ac_type], key($ac_type))
            @elseif (in_array($ac_type, ['vf','bf']))
                @livewire('edit-vf', ['action_id' => $actionId], key($actionId))
            @elseif (in_array($ac_type, ['af','uf']))
                @livewire('new-af', ['selectedForm' => $ac_type], key($ac_type))
            @elseif (in_array($ac_type, ['vt','vr','mv','sc','abr','afr','wa']))
                @livewire('edit-va', ['action_id' => $actionId], key($actionId))
            @endif
        </div>

        <button wire:click="close" class="px-4 p-2 bg-gray-200 rounded hover:bg-gray-300">
            Abbrechen
        </button>

    </div>
</div>
