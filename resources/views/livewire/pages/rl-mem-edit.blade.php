<div>

    <!-- Header -->
    <div class="bg-white p-4 shadow rounded-lg">
        <p class="text-2xl font-bold mb-0">Teilnehmer und Gäste-Planung</p>
    </div>

    <div class="flex py-4 space-x-4">
        <main class="rounded-lg shadow-lg p-6 min-w-3xl max-w-7xl mx-auto mt-3 space-y-6">
            <div>
                <label for="field2" class="text-sm font-medium text-gray-700">Aktivität auswählen:</label>
                <select wire:model.live="actionId" class="font-bold block border p-2 rounded min-w-36" title="Aktivität">
                    @foreach($selectActions as $sel)
                        <option value="{{ $sel->id }}" key="{{ $sel->id }}">{{ \Carbon\Carbon::parse($sel->action_date)->format('d.m.') }} - {{ $sel->action_name }} - {{ $sel->action_start_at }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 mt-4">
                <div class="border rounded-lg p-4 shadow-md">
                    <h3 class="font-medium mb-2">Teilnehmer</h3>
                        <div class="mb-2 flex justify-between items-start w-full">
                            @if( count($teilnehmer) == 0 )
                                <p>keine Teilnehmer-Meldungen</p>
                            @else
                                <p class="m-0 font-bold">Status: {{ $action->ac_reg_state_tn_name }}<br/>Belegte Plätze gesamt: x, Teilnehmer frei: x</p>
                            @endif
                            <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                                <button @click="open = !open" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded">hinzufügen</button>
                                <div x-show="open" @click.away="open = false" class="absolute p-1 mt-2 w-60 bg-white border rounded shadow-lg z-50">
                                    <p class="text-xl font-semibold mb-2">Teilnehmer hinzufügen</p>

                                    <div class="flex flex-col mb-2 space-x-2">
                                        <label for="field1" class="text-sm font-medium">Mitglied suchen</label>
                                        <div x-data="{ open: false, search: @entangle('search') }">
                                            <input
                                                type="text"
                                                x-model="search"
                                                wire:model.live.debounce.500ms="search"
                                                placeholder="Vorname..."
                                                @focus="open = true"
                                                @keydown.escape="open = false"
                                                class="border rounded-sm p-1">

                                            <ul
                                                x-show="open"
                                                @mousedown.outside="open = false"
                                                class="absolute py-2 pe-2 bg-white border rounded w-80 mt-1 shadow-lg">
                                                @foreach($suchErgebnisse as $person)
                                                    <li wire:click="addMember({{ $person->id }},'tn','ang'); open = false; search = ''"
                                                        wire:confirm="{{ $person->firstname }} {{ $person->name }} als Teilnehmer hinzufügen?"
                                                        class="p-0 hover:bg-gray-200 cursor-pointer">
                                                        {{ $person->firstname }} {{ $person->name }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @if(count($teilnehmer) > 0)
                        <table class="max-w-max rounded-md">
                            <thead>
                            <tr class="font-medium bg-gray-200">
                                <td class="max-w-80 min-w-60 px-2 py-1 border">Mitglied</td>
                                <td class="min-w-40 px-3 py-1 border">Status</td>
                                <td class="min-w-20 px-2 py-1 border">Fahrten</td>
                            </tr>
                            </thead>
                            @foreach($teilnehmer as $tn)
                                <tr class="hover:bg-blue-100" wire:key="{{ $tn->web_id }}">

                                    <td class="px-2 py-1 border ">{{ $tn->display_name }}</td>
                                    <td class="text-center px-3 py-1 border">

                                        <select wire:model="newTeilnehmerSelections.{{ $tn->web_id }}" class="px-2 py-1 border rounded-md">
                                            <option value="tn">&#x2705; Teilnehmer</option>
                                            @if(in_array($action->action_type_sc,['vf','af']))
                                                <option value="cr">&#x2B06; zur Crew</option>
                                            @endif
                                            @if($action->action_type_sc == 'vf')
                                                <option value="sv">&#x2B06; zum Service</option>
                                            @endif
                                            @if($action->ac_with_wl == 1)
                                                <option value="wl">&#x2B07; zur Warteliste</option>
                                            @endif
                                            <option value="del">&#x274C; abmelden</option>
                                        </select>
                                    </td>
                                    <td class="text-center px-2 py-1 border">{{ $tn->count }}</td>
                                </tr>
                            @endforeach
                        </table>
                    <button wire:click="saveTeilnehmer" class="px-4 py-2 mt-2 bg-blue-500 text-white hover:bg-blue-700 rounded">Änderungen speichern</button>
                    @endif
                </div>

                @if($action->ac_with_wl)
                    <div class="border rounded-lg p-4 shadow-md">
                        <h3 class="font-medium mb-2">Teilnehmer-Warteliste</h3>
                            <div class="mb-2 flex justify-between items-start w-full">
                                @if( count($wlist) == 0 )
                                    <p>keine Warteliste</p>
                                @else
                                    <p class="font-bold">Status: {{ $action->ac_reg_state_tn_name }}</p>
                                @endif
                                <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                                    <button @click="open = !open" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded">hinzufügen</button>
                                    <div x-show="open" @click.away="open = false" class="absolute p-1 mt-2 w-60 bg-white border rounded shadow-lg z-50">
                                        <p class="text-xl font-semibold mb-2">Teilnehmer hinzufügen</p>

                                        <div class="flex flex-col mb-2 space-x-2">
                                            <label for="field1" class="text-sm font-medium">Mitglied suchen</label>
                                            <div x-data="{ open: false, search: @entangle('search') }">
                                                <input
                                                    type="text"
                                                    x-model="search"
                                                    wire:model.live.debounce.500ms="search"
                                                    placeholder="Vorname..."
                                                    @focus="open = true"
                                                    @keydown.escape="open = false"
                                                    class="border rounded-sm p-1">

                                                <ul
                                                    x-show="open"
                                                    @mousedown.outside="open = false"
                                                    class="absolute py-2 pe-2 bg-white border rounded w-80 mt-1 shadow-lg">
                                                    @foreach($suchErgebnisse as $person)
                                                        <li wire:click="addMember({{ $person->id }},'tn','wl'); open = false; search = ''"
                                                            wire:confirm="{{ $person->firstname }} {{ $person->name }} zu Warteliste hinzufügen?"
                                                            class="p-0 hover:bg-gray-200 cursor-pointer">
                                                            {{ $person->firstname }} {{ $person->name }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @if( count($wlist) > 0 )
                            <table class="max-w-max rounded-md">
                                <thead>
                                <tr class="font-medium bg-gray-200">
                                    <td class="max-w-80 min-w-60 px-2 py-1 border">Mitglied</td>
                                    <td class="min-w-40 px-3 py-1 border">Status</td>
                                    <td class="min-w-20 px-2 py-1 border">Fahrten</td>
                                </tr>
                                </thead>
                                @foreach($wlist as $wl)
                                    <tr class="hover:bg-blue-100" wire:key="{{ $wl->web_id }}">

                                        <td class="px-2 py-1 border ">{{ $wl->display_name }}</td>
                                        <td class="text-center px-3 py-1 border">

                                            <select wire:model="newWlistSelections.{{ $wl->web_id }}" class="px-2 py-1 border rounded-md">
                                                <option value="tn">&#x2B06; zu Teilnehmern</option>
                                                <option value="cr">&#x2B06; zu Crew</option>
                                                <option value="del">&#x274C; abmelden</option>
                                            </select>
                                        </td>
                                        <td class="text-center px-2 py-1 border">0</td>
                                    </tr>
                                @endforeach
                            </table>
                            <button wire:click="saveWarteliste" class="px-4 py-2 mt-2 bg-blue-500 text-white hover:bg-blue-700 rounded">Änderungen speichern</button>
                        @endif
                    </div>
                @endif

                @if($action->ac_max_guests > 0)
                    <div class="border rounded-lg p-4 shadow-md">
                        <h3 class="font-medium mb-2">Gäste-Planung</h3>
                        @if( count($guests) == 0 )
                            <p>keine Gäste-Anmeldungen</p>
                        @else
                            <div class="mb-2 flex justify-between items-start w-full">
                                <p class="font-medium">Status: {{ $action->ac_reg_state_tn_name }}</p>
                                <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded">hinzufügen</button>
                            </div>
                            <table class="border border-grey-00 rounded-md border-separate shadow-sm">
                                <thead>
                                <tr class="font-medium bg-gray-200">
                                    <td class="min-w-60 px-2 py-1 border">Mitglied</td>
                                    <td class="min-w-20 px-2 py-1 border">Gast</td>
                                    <td class="min-w-40 px-3 py-1 border">Status</td>
                                    <td class="min-w-20 px-2 py-1 border"></td>
                                </tr>
                                </thead>
                                @foreach($guests as $gst)
                                    <tr class="hover:bg-blue-100" wire:key="{{ $gst->id }}">
                                        <td class="px-2 py-1 border ">{{ $gst->firstname }} {{ $gst->name }}</td>
                                        <td class="px-2 py-1 border ">{{ $gst->gst_name }}</td>
                                        <td class="px-3 py-1 border">
                                            <select wire:model="newGuestSelections.{{ $gst->id }}" class="px-2 py-1 border rounded-md">
                                                <option value="angefragt">&#x2753; angefragt</option>
                                                <option value="angenommen">&#x2705; angenommen</option>
                                                <option value="del">&#x274C; abgelehnt</option>
                                            </select>
                                        </td>
                                        <td class="text-center px-2 py-1 border"></td>
                                    </tr>
                                @endforeach
                            </table>
                            <button wire:click="saveGuests" class="px-4 py-2 mt-2 bg-blue-500 text-white hover:bg-blue-700 rounded">Änderungen speichern</button>
                        @endif
                    </div>
                @endif

            </div>
        </main>
    </div>
</div>
