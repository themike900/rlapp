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
            <th class="border p-2 w-24">Datum</th>
            <th class="border p-2 w-48">Aktivität</th>
            <th class="border p-2">Auftraggeber</th>
            <th class="border p-2 w-20">Teilnehmer</th>
            <th class="border p-2 w-20">Crew</th>
            <th class="border p-2 w-20">Service</th>
            <th class="border p-2 w-36">Status</th>
            <th class="w-48"> </th>
            <th class="border p-2 w-10">id</th>
        </tr>
        </thead>
        <tbody>
        @foreach($actions as $action)
            <tr class="border hover:bg-indigo-50">
                <td class="p-2">{{ $action->action_date }}</td>
                <td class="border-x p-2">{{ $action->action_name }}</td>
                <td class="p-2">{{ $action->applicant_name }}</td>
                <td class="p-2">0</td>
                <td class="p-2">0</td>
                <td class="tet-center p-2">0</td>
                <td class="tet-center p-2">{{ $action->action_state_name }}</td>
                <td class="tet-center">
                    <button wire:click="openViewModal({{ $action->id }})"
                        class="border rounded px-1 py-1 bg-indigo-300 hover:bg-indigo-400">
                        Info
                    </button>
                    <button wire:click="openEditModal({{ $action->id }})"
                            class="border rounded px-1 py-1 bg-indigo-300 hover:bg-indigo-400">
                        Edit
                    </button>
                    <button wire:click="openCrewModal({{ $action->id }})"
                        class="border rounded px-1 py-1 bg-indigo-300 hover:bg-indigo-400">
                        Crew
                    </button>
                    <button wire:click="openStatusModal({{ $action->id }})"
                        class="border rounded px-1 py-1 bg-indigo-300 hover:bg-indigo-400">
                        Status
                    </button>
                </td>
                <td class="p-2">{{ $action->id }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>


