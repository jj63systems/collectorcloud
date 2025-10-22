<x-filament::page :wire:poll.1000ms="$this->shouldPoll ? 'pollStatus' : false">
    <div class="space-y-4">
        <h2 class="text-xl font-bold">Import Status</h2>

        <p class="text-gray-600">
            <strong>File:</strong> {{ $dataLoad->filename }}
        </p>

        <p class="text-gray-600">
            <strong>Worksheet:</strong> {{ $dataLoad->worksheet_name ?? 'N/A' }}
        </p>

        <p class="text-gray-600">
            <strong>Status:</strong> {{ $dataLoad->status }}
        </p>

        @if ($dataLoad->status !== 'completed')
            <div class="mt-6">
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div
                        class="bg-primary-600 h-4 rounded-full transition-all duration-500"
                        style="width: {{ intval(($dataLoad->rows_processed / max(1, $dataLoad->row_count)) * 100) }}%;"
                    ></div>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $dataLoad->rows_processed }} / {{ $dataLoad->row_count }} rows processed
                </p>
                <p class="text-sm text-gray-600">
                    Elapsed time: {{ round(abs($startTime->diffInSeconds(now())), 0) }}s
                </p>
            </div>
        @else
            <div class="mt-6">
                <p class="text-green-700 font-semibold">Import complete.</p>

                @if ($dataLoad->validation_status === null)
                    <x-filament::button wire:click="startValidation" class="mt-4">
                        Start Data Validation
                    </x-filament::button>
                @endif
            </div>
        @endif

        {{-- Validation Progress Bar --}}
        @if ($dataLoad->status === 'completed' && $dataLoad->validation_status !== null && $dataLoad->validation_status !== 'complete')
            <div class="mt-6">
                <p class="text-sm text-gray-600">Validation Progress: {{ $dataLoad->validation_progress }}%</p>
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div class="bg-primary-600 h-4 rounded-full transition-all duration-500"
                         style="width: {{ $dataLoad->validation_progress }}%;">
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation Summary + Actions --}}
        @if ($dataLoad->validation_status === 'complete')
            <div class="mt-6 space-y-2">
                <p class="text-sm text-gray-600">
                    {{ \App\Models\Tenant\CcItemStage::where('data_load_id', $dataLoad->id)->where('has_data_error', true)->count() }}
                    rows have validation errors.
                </p>

                <div class="mt-4 space-x-2">
                    <x-filament::button color="danger" wire:click="discardUpload">
                        Discard Upload
                    </x-filament::button>

                    <x-filament::button color="success" wire:click="commitUpload">
                        Commit Upload
                    </x-filament::button>

                    <x-filament::button wire:click="reviewErrors">
                        Review Errors
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
