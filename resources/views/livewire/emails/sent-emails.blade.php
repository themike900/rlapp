<div class="w-full flex flex-row">
    @if($sentEmails)
        <div class="basis-1/2">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Sende-Datum</th>
                    <th class="border p-2">Empfänger</th>
                    <th class="border p-2">Betreff</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sentEmails as $sm)
                    <tr wire:click="showEmail({{ $sm->id }})" class="border cursor-pointer hover:bg-blue-50">
                        <td class="p-2">{{ $sm->created_at }}</td>
                        <td class="p-2">{{ $sm->receiver }}</td>
                        <td class="p-2">{{ $sm->subject }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="basis-1/2 ml-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Datum/Zeit</label>
                <input type="text" readonly wire:model="sent_at"
                       class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Absender</label>
                <input type="text" readonly value="Royal-Louise Planung (planung@royal-louise.de)"
                       class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Empfänger</label>
                <input type="text" readonly wire:model="receiver"
                       class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Betreff</label>
                <input type="text" readonly  wire:model="subject"
                       class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm">
            </div>

            <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700">Text</label>
                <textarea readonly wire:model="text"
                          class="mt-1 px-2 py-1 block w-full rounded-md border shadow-sm"
                          rows="15"></textarea>
            </div>
           <p>Anhang</p>
        </div>
    @else
        Kein versendeten Emails in der Liste
    @endif

</div>
