<div>

    <!-- Header -->
    <div class="bg-white p-4 shadow rounded-lg">
        <p class="text-2xl font-bold mb-0">Crew-/Service-Planung</p>
    </div>

    <div class="flex py-4 space-x-4">
        <main class="rounded-lg shadow-lg p-6 w-full mx-auto mt-3 space-y-6">
            <div>
                <label for="field2" class="text-sm font-medium text-gray-700">Aktivität auswählen:</label>
                <select wire:model.live="actionId" class="font-bold block border p-2 rounded min-w-36" title="Aktivität">
                    @foreach($selectActions as $sel)
                        <option value="{{ $sel->id }}" key="{{ $sel->id }}">{{ \Carbon\Carbon::parse($sel->action_date)->format('d.m') }} - {{ $sel->action_name }} - {{ $sel->action_start_at }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="border rounded-lg p-4 shadow-md">
                    <h3 class="font-medium mb-2">Crew-Planung</h3>
                    @if( count($crew) == 0 )
                        <p>keine Crew-Bereitschaften</p>
                    @else
                        <table class="w-full rounded-md">
                            @foreach($crew as $cr)
                                <tr >

                                    <td class="px-2 py-2 border ">{{ $cr->display_name }}</td>
                                    <td class="px-1 py-2 border">

                                        <select wire:model="newCrewSelections.{{ $cr->web_id }}" class="px-2 py-1 border rounded-md">
                                            <option value="br">&#x2753; gemeldet</option>
                                            <option value="gpl">&#x2705; geplant</option>
                                            <option value="abgl">&#x274C; abgelehnt</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    <button wire:click="saveCrew" class="px-4 py-2 mt-2 bg-blue-500 text-white hover:bg-blue-700 rounded">Testmodus</button>
                    @endif
                </div>
                <div class="border rounded-lg p-4 shadow-md">
                    <h3 class="font-medium mb-2">Service-Planung</h3>
                    @if( count($service) == 0 )
                        <p>keine Service-Bereitschaften</p>
                    @else
                        <table class="w-full border border-grey-00 rounded-md border-separate shadow-sm">
                            @foreach($service as $sv)
                                <tr >
                                    <td class="px-2 py-2 border ">{{ $sv->display_name }}</td>
                                    <td class="px-1 py-2 border">
                                        <select class="px-2 py-1 border rounded-md">
                                            <option value="br">&#x2753; gemeldet</option>
                                            <option value="gpl">&#x2705; geplant</option>
                                            <option value="abgl">&#x274C; abgelehnt</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <button class="px-4 py-2 mt-2 bg-blue-500 text-white hover:bg-blue-700 rounded">Testmodus</button>
                    @endif
                </div>
                <div class="border rounded-lg p-4 shadow-md">
                    <h3 class="font-medium mb-2">Schiffsführer</h3>
                    <label class="text-sm font-medium text-gray-700">ausgewählt:</label>
                    <select wire:model="newCaptain" class="block font-bold border px-2 py-1 rounded min-w-36 hover:bg-gray-100 shadow-sm" title="Aktivität">
                        <option value="0">bisher kein Schiffsführer</option>
                        @foreach($captains as $cp)
                            <option value="{{ $cp->webid }}" key="{{ $cp->webid }}">{{ $cp->display_name }}</option>
                        @endforeach
                    </select>
                    <label class="text-sm font-medium text-gray-700">gespeichert:</label>
                    <input readonly type="text" wire:model="captainName" class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm">
                    <button wire:click="saveCaptain" class="px-4 py-2 mt-2 bg-blue-500 text-white hover:bg-blue-700 rounded">Speichern</button>
                </div>
            </div>
        </main>
    </div>
</div>
