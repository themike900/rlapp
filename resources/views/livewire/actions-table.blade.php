<div class="w-full">

    <!-- Filter & Suche -->
    <div class="flex gap-4 pb-4">
         <div class="flex flex-col">
            <label for="field2" class="text-sm font-medium">Status-Filter:</label>
            <select wire:model.live="filter" class="border p-2 rounded min-w-36" title="Filter nach Rolle">
                <option value="of,gs">veröffentlicht</option>
                <option value="br,of,gs">bereit und veröffentlicht</option>
                <option value="iv">in Vorbereitung</option>
                <option value="iv,br">in Vorbereitung und bereit</option>
                <option value="br">bereit</option>
                <option value="of">offen</option>
                <option value="gs">geschlossen</option>
                <option value="df,as,ag">erledigt</option>
                <option value="iv,br,of,gs,df,as,ag">alle</option>
            </select>
        </div>
    </div>

    <!-- Tabelle -->
    <table class="w-full border-collapse border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="border p-2">Datum</th>
            <th class="border p-2">Name</th>
            <th class="border p-2">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($actions as $action)
            <tr class="border">
                <td class="p-2">{{ $action->action_date }}</td>
                <td class="p-2">{{ $action->action_name }}</td>
                <td class="p-2">{{ $action->action_state_sc }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
