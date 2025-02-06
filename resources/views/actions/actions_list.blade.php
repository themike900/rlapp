<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ 'Liste der Vereinsaktivitäten' }}
        </h2>
    </x-slot>


    <form action="{{ route('actions.index') }}" method="get">
        @csrf
        <div class="flex flex-wrap gap-4 mb-4 border">
                <label class="block font-semibold py-1">Aktivitäten:</label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="sel_actions[]" value="ausfahrten"
                           @if(in_array('ausfahrten',$sel_actions)) checked @endif
                           class="w-4 h-4">
                    <span>Ausfahrten</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="sel_actions[]" value="veranstaltungen"
                           @if(in_array('veranstaltungen',$sel_actions)) checked @endif
                           class="w-4 h-4">
                    <span>Veranstaltungen</span>
                </label>
                <input type="hidden" name="sel_actions[]" value="web_select">
                <button type="submit" class="bg-blue-500 text-white font-semibold py-1 px-3 rounded-md shadow hover:bg-blue-600 focus:ring-2">Aktualisieren</button>
        </div>
    </form>

    <form action="{{ route('actions.index') }}" method="get">
        @csrf
        <div class="flex flex-wrap gap-4 mb-4 border">
            <label class="block font-semibold py-1">Status:</label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="sel_states[]" value="sichtbar"
                       @if(in_array('sichtbar',$sel_states)) checked @endif
                       class="w-4 h-4">
                <span>sichtbar</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="sel_states[]" value="geplant"
                       @if(in_array('geplant',$sel_states)) checked @endif
                       class="w-4 h-4">
                <span>geplant</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="sel_states[]" value="abgeschlossen"
                       @if(in_array('abgeschlossen',$sel_states)) checked @endif
                       class="w-4 h-4">
                <span>abgeschlossen</span>
            </label>
            <input type="hidden" name="sel_states[]" value="web_select">
            <button type="submit" class="bg-blue-500 text-white font-semibold py-1 px-3 rounded-md shadow hover:bg-blue-600 focus:ring-2">Aktualisieren</button>
        </div>
    </form>

    <table class="w-full table-auto border border-collapse">
       <thead>
       <tr class="bg-gray-300">
           <th class="min-w-24" colspan="2">Datum</th>
           <th class="min-w-32">Bezeichnung</th>
           <th class="min-w-28">Crew-Zeiten</th>
           <th class="min-w-28">Termin-Zeiten</th>
           <th class="min-w-20">Status</th>
           <th class="min-w-20">Kapitän</th>
           <th class="min-w-10">Crew</th>
           <th class="min-w-10">Service</th>
           <th class="min-w-10">Teilnehmer</th>
           <th class="min-w-10">Gäste</th>
       </tr>
       </thead>
       <tbody>
       @foreach($actions as $ac)
           <tr onclick="window.location.href='{{ route('actions.create') }}'" class="hover:bg-gray-200 border cursor-pointer">
               <td class="p-1">{{ __(date_format(date_create($ac->action_date), 'D')) }}</td>
               <td class="border-r p-1">{{ date_format(date_create($ac->action_date), 'j. M') }}</td>
               <td class="border-r p-1">{{ $ac->action_name }}</td>
               <td class="text-center border-r">{{ $ac->crew_start_at }} - {{ $ac->crew_end_at }}</td>
               <td class="text-center border-r">{{ $ac->action_start_at }}-{{ $ac->action_end_at }}</td>
               <td class="text-center border-r">{{ $ac->as_name }}</td>
               <td class="text-center border-r">{{ $ac->am_kp }}</td>
               <td class="text-center border-r">{{ $ac->am_cr }}</td>
               <td class="text-center border-r">{{ $ac->am_sv }}</td>
               <td class="text-center border-r">{{ $ac->am_tn + $ac->am_mf }}</td>
               <td class="text-center border-r">{{ $ac->am_gs }}</td>
           </tr>

       @endforeach
       </tbody>
   </table>

</x-app-layout>
