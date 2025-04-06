<div>
    @switch($page)
        @case('rl-action-list')
            @livewire('pages.rl-actions-list')
            @break

        @case('rl-action-new')
            @livewire('pages.rl-action-new')
            @break

        @case('rl-mem-list')
            @livewire('pages.rl-members-list')
            @break

        @case('rl-mem-import')
            @livewire('pages.rl-members-import')
            @break

        @default
            <div>Seite nicht gefunden</div>
    @endswitch
</div>
