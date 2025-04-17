<div>
    <!-- Header -->
    <div class="bg-white p-3 shadow rounded-lg">
        <p class="text-2xl font-bold mb-0">Neuerfassung einer Aktivität</p>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow-lg p-4 max-w-6xl mx-auto mt-3 space-y-6">
            <div>
                <label for="formularTyp" class="block text-sm font-medium text-gray-700">Aktivitätentyp auswählen</label>
                <select wire:model.live="selectedForm" id="formularTyp"
                        class="border px-2 py-1 mt-1 block w-auto rounded-md shadow-sm hover:bg-gray-200 focus:ring-indigo-500 focus:border-indigo-800">
                    <option disabled value="">Bitte einen Aktivitätentyp wählen…</option>
                    <option value="gfx">Gästefahrt</option>
                    <option value="vf">Vereinsfahrt</option>
                    <option value="af">Ausbildungsfahrt</option>
                    <option value="uf">Übungsfahrt</option>
                    <option value="bf">Betriebsfahrt</option>
                    <option disabled>─────────────</option>
                    <option value="vt">Vereinstreffen</option>
                    <option value="sc">Shanty-Chor</option>
                    <option value="mv">Mitgliederversammlung</option>
                    <option value="vr">Vereinsreise</option>
                    <option disabled>─────────────</option>
                    <option value="afr">Aufriggen</option>
                    <option value="abr">Abriggen</option>
                    <option value="wa">Winterarbeit</option>
                </select>

            </div>

            <div>
                @if (in_array($selectedForm, ['gfx','gfm']))
                    @livewire('new-gf', ['selectedForm' => $selectedForm], key($selectedForm))
                @elseif (in_array($selectedForm, ['vf','bf']))
                    @livewire('new-vf', ['selectedForm' => $selectedForm], key($selectedForm))
                @elseif (in_array($selectedForm, ['af','uf']))
                    @livewire('new-af', ['selectedForm' => $selectedForm], key($selectedForm))
                @elseif (in_array($selectedForm, ['vt','vr','mv','sc','abr','afr','wa']))
                    @livewire('new-va', ['selectedForm' => $selectedForm], key($selectedForm))
                @endif
            </div>
        </div>
    </div>


</div>
