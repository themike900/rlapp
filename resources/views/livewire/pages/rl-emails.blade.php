<div>

    <!-- Header -->
    <div class="bg-white p-4 shadow rounded-lg">
        <p class="text-2xl font-bold mb-0">Email-Textvorlagen und Email-Versandliste</p>
    </div>


    <div class="py-4">

        <div class="flex border-b-2">
            <button wire:click="$set('activeTab', 'sentEmails')"
                    class="px-2 py-1 {{ $activeTab == 'sentEmails' ? 'bg-blue-600 text-white' : 'bg-blue-100'}}">
                Email-Versandliste
            </button>
            <button wire:click="$set('activeTab', 'emailTexts')"
                    class="px-2 py-1 {{ $activeTab == 'emailTexts' ? 'bg-blue-600 text-white' : 'bg-blue-100'}}">
                Email-Textvorlagen
            </button>
        </div>

        <main class="bg-white p-4 rounded-lg shadow-lg">


            @if ($activeTab == 'emailTexts')
                @livewire('emails.email-texts')
            @elseif ($activeTab == 'sentEmails')
                @livewire('emails.sent-emails')
            @endif

        </main>
    </div>
</div>
