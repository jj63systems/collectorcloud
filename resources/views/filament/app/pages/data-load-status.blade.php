<x-filament::page wire:poll.1000ms="pollStatus">
    <div class="space-y-4">
        <h2 class="text-xl font-bold">Import in Progress</h2>

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
                <p class="text-green-700 font-semibold">Import complete. Redirecting shortly...</p>
            </div>
        @endif
    </div>
</x-filament::page>
