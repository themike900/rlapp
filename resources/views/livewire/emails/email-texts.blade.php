<div class="w-3xl">

    <!-- Filter & Suche -->
    <div class="flex gap-4 pb-4">
        <div class="flex flex-col">
            <label for="field2" class="text-sm font-medium text-gray-700">Vorlage auswählen:</label>
            <select wire:model.live="templateId" class="block border p-2 rounded min-w-36" title="Aktivität">
                @foreach($templates as $temp)
                    <option value="{{ $temp->id }}" key="{{ $temp->id }}">{{ $temp->template }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Betreff</label>
        <input type="text" value="Gästefahrt" wire:model="subject"
               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 active:ring-indigo-500 active:border-indigo-500">
    </div>

    <div class="mt-2">
        <label class="block text-sm font-medium text-gray-700">Text</label>
        <textarea wire:model="text"
               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500"
                  rows="15"></textarea>
    </div>

    <div class="mt-4 flex flex-row">
        <button wire:click="saveTemplate" class="rl-btn">
            Änderungen speichern
        </button>
        @if($saved)
            <div class="px-4 py-2 font-bold text-blue-500">Änderungen gespeichert</div>
        @endif
    </div>


</div>
