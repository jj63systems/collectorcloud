<x-filament::page :wire:poll.1000ms="$this->shouldPoll ? 'pollStatus' : false">
    <div class="space-y-6 max-w-md">


        {{-- Data Load Selector --}}
        <div class="bg-white shadow rounded-lg p-6 border border-gray-200 space-y-2">
            <label for="dataLoadSelect" class="block text-sm font-medium text-gray-700">
                Select Data Load Run:
            </label>
            <select
                id="dataLoadSelect"
                wire:model.live="selectedDataLoadId"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
                @foreach ($dataLoadOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Import Status --}}
        <div class="bg-white shadow rounded-lg p-6 border border-gray-200 space-y-2">
            <h3 class="text-lg font-semibold text-gray-800">Import Summary</h3>

            <p class="text-sm text-gray-700"><strong>File:</strong> {{ $dataLoad->filename }}</p>
            <p class="text-sm text-gray-700"><strong>Worksheet:</strong> {{ $dataLoad->worksheet_name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ $dataLoad->status }}</p>

            @if (!empty($dataLoad->notes))
                <p class="text-sm text-gray-700"><strong>Notes:</strong> {{ $dataLoad->notes }}</p>
            @endif

            @if ($dataLoad->row_count)
                <p class="text-sm text-gray-700"><strong>Total Rows:</strong> {{ number_format($dataLoad->row_count) }}
                </p>
            @endif

            {{-- Import in Progress --}}
            @if (!in_array($dataLoad->status, ['completed', 'failed']))
                <div class="pt-4">
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div
                            class="bg-primary-600 h-4 rounded-full transition-all duration-500"
                            style="width: {{ intval(($dataLoad->rows_processed / max(1, $dataLoad->row_count)) * 100) }}%;">
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $dataLoad->rows_processed }} / {{ $dataLoad->row_count }} rows processed
                    </p>
                    <p class="text-sm text-gray-600">
                        Elapsed time: {{ round(abs($startTime->diffInSeconds(now())), 0) }}s
                    </p>
                </div>
            @elseif ($dataLoad->status === 'completed')
                <p class="text-sm font-medium text-green-700 mt-4">✔ Import complete.</p>

                @if (is_null($dataLoad->validation_status))
                    <x-filament::button wire:click="startValidation" class="mt-4">
                        Start Data Validation
                    </x-filament::button>
                @endif
            @endif
        </div>

        {{-- Validation Progress --}}
        @if ($dataLoad->status === 'completed' && $dataLoad->validation_status === 'validating')
            <div class="bg-white shadow rounded-lg p-6 border border-gray-200 space-y-2">
                <h3 class="text-lg font-semibold text-gray-800">Validation in Progress</h3>
                <p class="text-sm text-gray-600">Validation Progress: {{ $dataLoad->validation_progress }}%</p>
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div
                        class="bg-primary-600 h-4 rounded-full transition-all duration-500"
                        style="width: {{ $dataLoad->validation_progress }}%;">
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation Complete Summary --}}
        @if ($dataLoad->validation_status === 'completed')
            <div class="bg-white shadow rounded-lg p-6 border border-gray-200 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Validation Summary</h3>

                <p class="text-sm text-gray-700">
                    {{ \App\Models\Tenant\CcItemStage::where('data_load_id', $dataLoad->id)->where('has_data_error', true)->count() }}
                    rows have validation errors.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
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
