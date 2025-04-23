<div class="flex h-fit">
    <!-- Sidebar -->
    <div class="w-64 bg-indigo-600 h-screen p-4">
        <p class="pb-1 mb-0 text-white text-3xl">ROYAL-LOUISE</p>
        <p class="pb-10 text-white text-xl">Aktivitätenplanung</p>

        <ul class="space-y-2">
            @foreach ([
                ['name' => 'Aktivitätenliste', 'route' => 'rl-action-list'],
                ['name' => 'Neue Aktivität', 'route' => 'rl-action-new'],
                ['name' => 'Crew-Planung', 'route' => 'rl-crew-edit'],
                ['name' => 'Mitglieder', 'route' => 'rl-mem-list'],
                ['name' => 'Mitglieder-Import', 'route' => 'rl-mem-import'],
            ] as $item)
                <li>
                    <a href="#"
                       wire:click="$set('currentPage', '{{ $item['route'] }}')"
                       class="block px-2 py-2 rounded-md hover:bg-indigo-800 {{ $currentPage === $item['route'] ? 'bg-white text-indigo-800' : 'bg-indigo-600 text-white' }}">
                        {{ $item['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="space-y-2 pt-36 pl-8">
            <div class="block px-2 py-2 rounded-md hover:bg-indigo-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button wire:click="logout" class="underline text-white">
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
