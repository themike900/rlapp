<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    @keydown.escape.window="show = false"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6">
        <p class="text-xl font-semibold mb-4">{{ $action["action_name"] ?? '' }}</p>

        <div>
            <p class="p-2 border rounded-md">
                Datum: <b>{{ $action["action_date"] ?? '' }}</b><br/>
                Ab/Anlegen: <b>{{ $action["action_start_at"] ?? '' }}-{{ $action["action_end_at"] ?? '' }}</b><br/>
                An/VonBoard: <b>{{ $action["crew_start_at"] ?? '' }}-{{ $action["crew_end_at"] ?? '' }}</b><br/>
                Zusatzinformationen: <b>{{ $action["additional_info"] ?? '' }}</b><br/>
                Max-TN: <b>{{ $action["ac_max_pers"] ?? '' }}</b>, Max-Gäste: <b>{{ $action["ac_max_guests"] ?? '' }}</b><br/>
            </p>
            <p class="p-2 border rounded-md">
                Anlass: <b>{{ $action["reason"] ?? '-' }}</b><br/>
                Besteller: <b>{{ $action["applicant_name"] ?? '-' }}, {{ $action["applicant_email"] ?? '-' }}, {{ $action["applicant_phone"] ?? '-' }}</b><br>
                Kontakt:<b>{{ $action["contact_name"] ?? '-' }}, {{ $action["contact_email"] ?? '-' }}, {{ $action["contact_phone"] ?? '-' }}</b><br/>
                Rechnung: <b>{{ $action["invoice_address"] ?? '-' }}, {{ $action["invoice_amount"] ?? '-' }}€</b><br/>
                Gäste-Anzahl: <b>{{ $action["guest_count"] ?? '-' }}</b><br/>
                Catering: <b>{{ $action["catering_info"] ?? '-' }}</b><br/>
                Eis: <b>{{ $action["ice_info"] ?? '-' }}</b><br/>
                Crew-Versorgung: <b>{{ $action["crew_supply"] ?? '-' }}</b><br/>
            </p>
            <p class="p-2 border rounded-md">
                Crew: <b>{{ html_entity_decode($members['crew'] ?? '-') }}</b><br/>
                Service: <b>{{ html_entity_decode($members['service'] ?? '-') }}</b><br/>
                TN: <b>{{ html_entity_decode($members['participants'] ?? '-') }}</b><br/>
                Warteliste: <b>{{ html_entity_decode($members['participants_wl'] ?? '-') }}</b><br/>
            </p>
            <p class="p-2 border rounded-md">
                ID: {{ $actionId }} ( {{ $action["action_type_sc"] ?? '' }} )<br/>
                Reg-Status: Crew: <b>{{ $action["ac_reg_state_cr"] ?? '' }}</b>, Service: <b>{{ $action["ac_reg_state_sv"] ?? '' }}</b>, Teilnehmer: <b>{{ $action["ac_reg_state_tn"] ?? '' }}</b>
            </p>
        </div>

        <button wire:click="$set('show', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Zurück
        </button>

    </div>
</div>
