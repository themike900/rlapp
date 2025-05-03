<div class="flex h-fit">
    <!-- Sidebar -->
    <div class="w-56 bg-indigo-600 h-screen px-2 py-4">
        <p class="pb-1 mb-0 text-white text-3xl text-center">ROYAL-LOUISE</p>
        <p class="text-white text-xl text-center">Aktivitätenplanung</p>

        <ul class=" pt-4 space-y-2">
            @foreach ([
                ['name' => 'Aktivitätenliste', 'route' => 'rl-action-list'],
                ['name' => 'Neue Aktivität', 'route' => 'rl-action-new'],
                ['name' => 'Crew-Planung', 'route' => 'rl-crew-edit'],
                ['name' => 'Teilnehmer/Gäste', 'route' => 'rl-mem-edit'],
                ['name' => 'Mitgliederliste', 'route' => 'rl-mem-list'],
                ['name' => 'Mitglieder-Import', 'route' => 'rl-mem-import'],
            ] as $item)
                <li>
                    <a href="#"
                       wire:click="$set('currentPage', '{{ $item['route'] }}')"
                       class="block px-1 rounded-md hover:bg-indigo-800 no-underline {{ $currentPage === $item['route'] ? 'bg-white text-indigo-800' : 'bg-indigo-600 text-white' }}"
                        style="text-decoration: none !important;">
                        {{ $item['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="space-y-2 pt-3 pl-8">
            <div class="block px-1 rounded-md hover:bg-indigo-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button wire:click="logout" class="no-underline text-white">
                        Abmelden
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col p-4 space-y-4 mb-0 shadow-md bg-white">
        @livewire('dynamic-content', ['page' => $currentPage], key($currentPage))
    </div>
</div>
