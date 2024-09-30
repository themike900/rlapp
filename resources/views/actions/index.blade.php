<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
		<span class="text-gray-800">Liste der Aktivitäten</span>
		
        <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @foreach ($actions as $action)
                <div class="p-6 flex space-x-2">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-gray-800">{{ $action->action_date }}</span>
                                <small class="ml-2 text-sm text-gray-500">Crew: {{ $action->crew_start_at }} - {{ $action->crew_end_at }}, Gäste: {{ $action->action_start_at }} - {{ $action->action_end_at }}</small>
                            </div>
                        </div>
                        <p class="mt-3 text-lg text-gray-900">{{ $action->action_type }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
