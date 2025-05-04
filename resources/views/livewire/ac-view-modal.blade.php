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
                Max-TN: <b>{{ $action["ac_max_pers"] ?? '' }}</b>, Max-Gäste: <b>{{ $action["ac_max_guests"] ?? '' }}</b>
            </p>
            @if( ($action["action_type_sc"] ?? '') == 'gfx' )
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
            @endif
            @if( in_array(($action["action_type_sc"] ?? ''), ['vf','af']) )
                <p class="p-2 border rounded-md">
                    <b>Belegte Plätze: {{ 1 + $cnt["ac_crew"] + $cnt["ac_tn_ang"] + $cnt["ac_guests_res"] }}</b>
                        (incl. Reservierungen: {{ $cnt["ac_crew"] }} Crew/Service,
                        {{ (($cnt["ac_guests"] > $cnt["ac_guests_res"]) ? 0 : $cnt["ac_guests_res"] - $cnt["ac_guests"]) }} Gäste<br/>
                    plus: {{ $cnt["ac_tn_wl"] }} Warteliste, {{ $cnt["ac_guests_angf"] }} angefragte Gäste
                </p>
            @endif
            <p class="p-2 border rounded-md">
                @if(in_array(($action["action_type_sc"] ?? ''), ['vf','af','bf','uf','gfx']) )
                    Schiffsführer: <b>{!! $members['captain'] ?? '-' !!}</b><br/>
                    Crew: <b>{!! $members['crew'] ?? '-' !!}</b><br/>
                    Service: <b>{!! $members['service'] ?? '-' !!}</b><br/>
                @endif
                @if(in_array(($action["action_type_sc"] ?? ''), ['vf','af','bf','vt','sc','mv','vr','afr','abr','wa']) )
                    Teilnehmer: <b>{!! $members['participants'] ?? '-' !!}</b><br/>
                @endif
                @if($action["ac_with_wl"] ?? 0)
                    Warteliste: <b>{!! $members['participants_wl'] ?? '-' !!}</b><br/>
                @endif
                @if(in_array(($action["action_type_sc"] ?? ''), ['vf','vr']) )
                    Gäste: <b>{{ $cnt["ac_guests_angf"] ?? 0 }} angefragt, {{ $cnt["ac_guests_angn"] ?? 0 }} angenommen</b>
                @endif
            </p>
            <p class="p-2 border rounded-md">
                ID: {{ $actionId }} ( {{ $action["action_type_sc"] ?? '' }} )<br/>
                Anmelde-Status: Crew: <b>{{ $action["ac_reg_state_cr"] ?? '' }}</b>, Service: <b>{{ $action["ac_reg_state_sv"] ?? '' }}</b>, Teilnehmer: <b>{{ $action["ac_reg_state_tn"] ?? '' }}</b>
            </p>
        </div>

        <button wire:click="$set('show', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Zurück
        </button>

    </div>
</div>
