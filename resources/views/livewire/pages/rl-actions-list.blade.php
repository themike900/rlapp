<div>
    <!-- Header -->
    <div class="bg-white p-3 shadow rounded-lg">
        <p class="text-2xl font-bold mb-0">Aktivitätenliste</p>
    </div>

    <!-- Content -->
    <div class="flex py-4 space-x-2">
        <main class="bg-white p-3 rounded-lg shadow-lg flex-1">
            <div class="flex p-4 space-x-4">
                @livewire('actions-table')
                @livewire('edit-modal')
                @livewire('ac-view-modal')
                @livewire('ac-crew-modal')
                @livewire('ac-status-modal')
                @livewire('ac-members-modal')
            </div>
        </main>
    </div>

</div>
