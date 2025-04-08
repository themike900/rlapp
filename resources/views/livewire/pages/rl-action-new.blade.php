<div>
    <!-- Header -->
    <div class="bg-white p-3 shadow rounded-lg">
        <p class="text-2xl font-bold text-amber-800 mb-0">Neuerfassung einer Aktivität</p>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow-lg p-4 max-w-6xl mx-auto mt-5 space-y-6">
            <div>
                <label for="formularTyp" class="block text-sm font-medium text-gray-700">Aktivitätentyp auswählen</label>
                <select wire:model.live="selectedForm" id="formularTyp"
                        class="border px-2 py-1 mt-1 block w-auto rounded-md shadow-sm hover:bg-gray-200 focus:ring-indigo-500 focus:border-indigo-800">
                    <option value="">Bitte einen Aktivitätentyp wählen…</option>
                    <option value="gfx">Gästefahrt extern</option>
                    <option value="gfm">Gästefahrt Mitglied</option>
                    <option value="vf">Vereinsfahrt</option>
                    <option value="tf">Trainingsfahrt</option>
                    <option value="uf">Übungsfahrt</option>
                    <option value="bf">Betriebsfahrt</option>
                    <option disabled>─────────────</option>
                    <option value="va">Vereinstreffen</option>
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
                @elseif ($selectedForm === 'vf')
                    @livewire('new-vf', ['selectedForm' => $selectedForm])
                @elseif ($selectedForm === 'tf')
                    @livewire('new-tf', ['selectedForm' => $selectedForm])
                @elseif ($selectedForm === 'va')
                    @livewire('new-va', ['selectedForm' => $selectedForm])
                @endif
            </div>
        </div>
    </div>


</div>
