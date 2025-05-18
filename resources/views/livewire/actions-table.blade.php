<div class="w-full">

    <!-- Filter & Suche -->
    <div class="flex gap-4 pb-4">
         <div class="flex flex-col">
            <label for="field2" class="text-sm font-medium">Status-Filter:</label>
            <select wire:model.live="filter" class="border p-2 rounded min-w-36" title="Filter nach Rolle">
                <option value="of,gs">veröffentlicht (offen, geschlossen)</option>
                <option value="br,of,gs">bereit und veröffentlicht</option>
                <option value="iv">in Vorbereitung (noch unfertig)</option>
                <option value="iv,br">in Vorbereitung und bereit</option>
                <option value="br">bereit (kann veröffentlicht werden)</option>
                <option value="of">offen (sichtbar, Anmeldung möglich)</option>
                <option value="gs">geschlossen (sichtbar, keine Anmeldungen mehr)</option>
                <option value="df,as,ag">erledigt (nicht mehr sichtbar)</option>
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
            <th class="text-center border p-2 w-20">Teilnehmer</th>
            <th class="text-center border p-2 w-20">Warteliste</th>
            <th class="text-center border p-2 w-16">Crew</th>
            <th class="text-center border p-2 w-16">Service</th>
            <th class="text-center border p-2 w-16">Gäste</th>
            <th class="text-center border p-2 w-40">Status</th>
            <th class="text-center w-40"> </th>
            <th class="text-center border p-2 w-10">id</th>
        </tr>
        </thead>
        <tbody>
        @foreach($actions as $action)
            <tr class="border hover:bg-indigo-50" wire:key="{{ $action->id }}">
                <td class="p-2">{{ $action->action_date }}</td>
                <td class="border-x p-2">{{ $action->action_name }}</td>
                <td class="p-2">{{ $action->applicant_name }}</td>
                <td class="text-center p-2">{{ $action->cnt['ac_tn_ang'] }}</td>
                <td class="text-center p-2">{{ $action->cnt['ac_tn_wl'] }}</td>
                <td class="text-center p-2">{{ $action->cnt['ac_reg_cr'] }}</td>
                <td class="text-center p-2">{{ $action->cnt['ac_reg_sv'] }}</td>
                <td class="text-center p-2">{{ $action->cnt['ac_guests'] }}</td>
                <td class="text-center p-2">{{ $action->action_state_name }}</td>
                <td class="text-center">
                    <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                        <button @click="open = !open" class="px-3 py-1 bg-blue-500 text-white hover:bg-blue-700 rounded">Aktionen</button>
                        <div x-show="open" @click.away="open = false" class="absolute p-1 mt-2 w-52 bg-white border rounded shadow-lg z-50">
                            <button wire:click="openViewModal({{ $action->id }})" class="block w-full px-3 py-1 text-gray-700 hover:bg-gray-200">Details anzeigen</button>
                            <button wire:click="openEditModal({{ $action->id }})" class="block w-full px-3 py-1 text-gray-700 hover:bg-gray-200">Daten ändern</button>
                            @if (in_array($action->action_type_sc,['vf','gfx','af','uf','bf']))
                                <button wire:click="openCrewPage({{ $action->id }})" class="block w-full px-3 py-1 text-gray-700 hover:bg-gray-200">Crew-Planung</button>
                            @endif
                            @if (in_array($action->action_type_sc,['vf','af','bf','vt','sc','mv','vr','afr','abr','wa']))
                                <button wire:click="openTeilnehmerPage({{ $action->id }})" class="block w-full px-3 py-1 text-gray-700 hover:bg-gray-200">Teilnehmer/Gäste</button>
                            @endif
                            <button wire:click="openStatusModal({{ $action->id }})" class="block w-full px-3 py-1 text-gray-700 hover:bg-gray-200">Status ändern</button>
                            <button @click="open = false; window.open('{{ route('rl-fahrtenblatt', ['actionId' => $action->id]) }}', '_blank')"
                                    class="block w-full px-3 py-1 text-gray-700 hover:bg-gray-200">
                                Fahrtenblatt
                            </button>
                        </div>
                    </div>
                </td>
                <td class="text-center p-2">{{ $action->id }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>


