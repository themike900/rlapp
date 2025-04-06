    <ul class="space-y-2">
        @foreach ($menuItems as $item)
            @php $active = request()->routeIs($item['route']) @endphp
            <li>
                <a href="#"
                   wire:click="setActivePage('{{ $item['route'] }}}}')"
                   class="block px-2 py-2 rounded-md hover:bg-indigo-800  {{ request()->routeIs($item['route']) ? 'bg-white text-indigo-800' : 'bg-indigo-600 text-white' }}">
                    {{ $item['name'] }}
                </a>
            </li>
        @endforeach
    </ul>

