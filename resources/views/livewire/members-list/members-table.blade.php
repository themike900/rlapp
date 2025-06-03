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
                <option value="cr">Decks-Crew</option>
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
            <th class="w-60 border p-2 cursor-pointer" wire:click="sortBy('firstname')">
                Vorname
                @if($orderFirstname != '')
                    {!! $orderFirstname == 'asc' ? '&#9650;' : '&#9660;' !!}
                @endif
            </th>
            <th class="w-60 border p-2 cursor-pointer" wire:click="sortBy('lastname')">
                Name
                @if($orderLastname != '')
                    {!! $orderLastname == 'asc' ? '&#9650;' : '&#9660;' !!}
                @endif
            </th>
            <th class="border p-2">Rolle</th>
            <th class="border p-2 cursor-pointer" wire:click="sortBy('fahrten')">
                Teilnahmen<br/>
                @if($orderFahrten == 'countCr')
                    <u>cr</u>/sv/sf/tn
                @elseif ($orderFahrten == 'countSv')
                    cr/<u>sv</u>/sf/tn
                @elseif($orderFahrten == 'countSf')
                    cr/sv/<u>sf</u>/tn
                @elseif($orderFahrten == 'countTn')
                    cr/sv/sf/<u>tn</u>
                @else
                    cr/sv/sf/tn
                @endif
                @if($orderFahrten != '')
                    &#9660;
                @endif
            </th>
            <th class="border p-2 cursor-pointer" wire:click="sortBy('lastAccess')">
                letzter Zugriff
                @if($orderLastAccess != '')
                    {!! $orderLastAccess == 'asc' ? '&#9650;' : '&#9660;'  !!}
                @endif
            </th>
            <th class="w-35 border p-2 cursor-pointer" wire:click="sortBy('ids')">
                IDs<br/>
                @if($orderIds == 'id')
                    <u>app</u>/web/mv
                @elseif ($orderIds == 'webid')
                    app/<u>web</u>/mv
                @elseif($orderIds == 'mv_id')
                    app/web/<u>mv</u>
                @else
                    app/web/mv
                @endif
                @if($orderIds != '')
                    &#9650;
                @endif
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($members as $member)
            <tr class="border">
                <td class="p-2">{{ $member->firstname }}</td>
                <td class="p-2">{{ $member->name }}</td>
                <td class="p-2">{{ $member->groups }}</td>
                <td class="p-2">{{ $member->countCr }} - {{ $member->countSv }} - {{ $member->countSf }} - {{ $member->countTn }}</td>
                <td class="p-2">{{ $member->last_access }}</td>
                <td class="p-2">{{ $member->id }}-{{ $member->webid }}-{{ $member->mv_id }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
