<nav class="bg-white rounded-lg p-4 shadow-lg min-w-52">
    Aktivitäten
    <ul>
        <li class="mb-2"><a href="{{ route('rl-action-list') }}" class="text-blue-600 hover:underline">Liste</a></li>
        <li class="mb-2"><a href="{{ route('rl-action-new') }}" class="text-blue-600 hover:underline">Neu</a></li>
        <li class="mb-2"><a href="/{{ route('rl-action-edit') }}" class="text-blue-600 hover:underline">Bearbeiten</a></li>
    </ul>
    Mitglieder
    <ul>
        <li class="mb-2"><a href="{{ route('rl-action-list') }}" class="text-blue-600 hover:underline">Liste</a></li>
        <li class="mb-2"><a href="{{ route('rl-action-list') }}" class="text-blue-600 hover:underline">Bearbeiten</a></li>
    </ul>
    Administration
    <ul>
        <li class="mb-2"><a href="{{ route('rl-action-list') }}" class="text-blue-600 hover:underline">Listen</a></li>
        <li class="mb-2"><a href="{{ route('rl-action-list') }}" class="text-blue-600 hover:underline">Anmeldungen</a></li>
    </ul>

</nav>
