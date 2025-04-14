<div class="w-full">

    <!-- Filter & Suche -->
    <div class="flex gap-4 pb-4">
        <div class="flex flex-col">
            <label for="field1" class="text-sm font-medium">Suche:</label>
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="Vorname..."
                   class="border p-2 rounded min-w-36">
        </div>
        <div class="flex flex-col">
            <label for="field2" class="text-sm font-medium">Rollen-Filter:</label>
            <select wire:model.live="filter" class="border p-2 rounded min-w-36" title="Filter nach Rolle">
                <option value="">Alle Rollen</option>
                <option value="cr">Deckscrew</option>
                <option value="sv">Service-Crew</option>
                <option value="sh">Shanty</option>
                <option value="wa">Winterarbeit</option>
                <option value="sf">Schiffsführer</option>
                <option value="vs">Vorstand</option>
            </select>
        </div>
    </div>

    <!-- Tabelle -->
    <table class="w-full border-collapse border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="border p-2">Vorname</th>
            <th class="border p-2">Name</th>
            <th class="border p-2">Rolle</th>
            <th class="border p-2">letzter Zugriff</th>
            <th class="border p-2">web_id</th>
            <th class="border p-2">mv_id</th>
        </tr>
        </thead>
        <tbody>
        @foreach($members as $member)
            <tr class="border">
                <td class="p-2">{{ $member->firstname }}</td>
                <td class="p-2">{{ $member->name }}</td>
                <td class="p-2">{{ $member->groups }}</td>
                <td class="p-2">{{ $member->last_access }}</td>
                <td class="p-2">{{ $member->webid }}</td>
                <td class="p-2">{{ $member->mv_id }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
