<div
    x-data="{ show: $wire.entangle('show') }"
    x-show="show"
    @keydown.escape.window="show = false"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6">
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

                <!-- Gruppen-Select -->
                <select x-model="selectedGroup" wire:model="selectedGroup" class="border rounded-sm p-1 ml-2">
                    <option value="tn">Teilnehmer</option>
                    <option value="cr">Crew</option>
                    <option value="sv">Service</option>
                </select>

                <ul
                    x-show="open"
                    @mousedown.outside="open = false"
                    class="absolute py-2 pe-2 bg-white border rounded w-80 mt-1 shadow-lg">
                    @foreach($suchErgebnisse as $person)
                        <li wire:click="addMember({{ $person->id }}); open = false; search = ''" wire:confirm="{{ $person->firstname }} {{ $person->name }} hinzufügen als {{ $groups[$selectedGroup] }}?"
                            class="p-0 hover:bg-gray-200 cursor-pointer">
                            {{ $person->firstname }} {{ $person->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="max-h-80 mb-3 overflow-y-auto border rounded">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border py-1 px-2 ">Name</th>
                    <th class="border py-1 px-2 ">Gruppe</th>
                    <th class="border py-1 px-2 ">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($action_members as $a_member)
                    <tr class="border hover:bg-indigo-50" wire:key="{{ $a_member->id }}">
                        <td class="border py-1 px-2">{{ $a_member->firstname }} {{ $a_member->name }} ({{ $a_member->id }})</td>
                        <td class="border py-1 px-2">{{ $a_member->group }}</td>
                        <td class="border py-1 px-2">{{ $a_member->reg_state }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <button wire:click="$set('show', false)" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
            Schließen
        </button>

    </div>
</div>
