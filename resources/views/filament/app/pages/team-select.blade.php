<x-filament::section>
    <x-slot name="heading">
        Select a Team
    </x-slot>

    <x-slot name="description">
        You are a member of multiple teams. Please choose which one you’d like to work with:
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        @foreach($teams as $id => $name)
            <div
                wire:click="selectTeam({{ $id }})"
                class="cursor-pointer rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md hover:border-primary-500"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-semibold">
                        {{ strtoupper(substr($name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-lg font-bold">{{ $name }}</div>
                        <div class="text-sm text-gray-500">Click to switch</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament::section>
