<form wire:submit="submit" class="space-y-2">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Linker Block -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md border">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Daten der Gästefahrt</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bezeichnung</label>
                    <input type="text" value="Gästefahrt" wire:model.defer="action_name"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 active:ring-indigo-500 active:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Datum der Fahrt</label>
                    <input type="date" wire:model.defer="action_date"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Crew an Bord</label>
                        <input type="text" placeholder="00:00" wire:model.defer="crew_start_at"
                               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Crew von Bord</label>
                        <input type="text" placeholder="00:00" wire:model.defer="crew_end_at"
                               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ablegen</label>
                        <input type="text" placeholder="00:00" wire:model.defer="action_start_at"
                               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Anlegen</label>
                        <input type="text" placeholder="00:00" wire:model.defer="action_end_at"
                               class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Zusatzinformationen</label>
                    <input type="text" wire:model.defer="additional_info"
                           class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-base font-medium text-gray-500 mb-1 pt-2">Crew-Info</label>
                    <div class="border border-gray-300 rounded-lg shadow-sm p-3">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catering</label>
                                <input type="text" placeholder="... wer beschafft das Essen?" wire:model.defer="catering_info"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Eis</label>
                                <input type="text" placeholder="... wo kommt das Eis her?" wire:model.defer="ice_info"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Crew-Versorgung</label>
                                <input type="text" placeholder="... bekommt die Crew was ab?" wire:model.defer="crew_supply"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechter Block -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Auftraggeber</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700">Termin bestätigt am</label>
                <input type="date" wire:model.defer="confirm_date"
                       class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Anlass</label>
                <input type="text" wire:model.defer="reason"
                       class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm hover:bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Anzahl erwarteten Gäste</label>
                <input type="text" required wire:model.defer="guest_count"
                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sektion 1 -->
                <div>
                    <label class="block text-base font-medium text-gray-500 mb-1">Antragsteller</label>
                    <div class="border border-gray-300 rounded-lg shadow-sm p-3">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" wire:model.defer="applicant_name"
                                       class="mt-1 w-full border border-gray-800 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1 focus:ring-blue-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email-Adresse</label>
                                <input type="email" wire:model.defer="applicant_email"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="tel" wire:model.defer="applicant_phone"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1 focus:ring-blue-700">
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Sektion 2 -->
                <div>
                    <label class="block text-base font-medium text-gray-500 font-medium mb-1">Kontaktperson</label>
                    <div class="border border-gray-300 rounded-lg shadow-sm p-3">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" wire:model.defer="contact_name"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email-Adresse</label>
                                <input type="text" wire:model.defer="contact_email"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="text" wire:model.defer="contact_phone"
                                       class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-base font-medium text-gray-500 mb-1 pt-2">Rechnung</label>
                <div class="border border-gray-300 rounded-lg shadow-sm p-3">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rechnungsadresse</label>
                            <input type="text" wire:model.defer="invoice_address"
                                   class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kostenbeitrag</label>
                            <input type="text" wire:model.defer="invoice_amount"
                                   class="mt-1 w-full border border-gray-300 rounded px-2 py-1 shadow-sm hover:bg-gray-100 focus:ring-1">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <input type="hidden" wire:model.defer="action_type_sc" value="gf">

    <div class="pt-4">
        <button wire:click="save" type="button"
                class="justify-center px-6 py-2 rounded shadow-lg text-white bg-indigo-600 hover:bg-indigo-800">
            Neue Gästefahrt anlegen
        </button>
    </div>

</form>
