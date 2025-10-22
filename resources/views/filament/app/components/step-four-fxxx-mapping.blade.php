<div class="space-y-6">

    {{-- Mapped headers (grouped by entity, read-only) --}}
    @if (!empty($mappedSpreadsheetHeadersByEntity))
        <x-filament::section>
            <x-slot name="heading">Mapped Columns</x-slot>
            <p class="text-sm text-gray-700 mb-2">
                These spreadsheet columns have already been assigned to known system fields:
            </p>

            <div class="space-y-6">
                @foreach ($mappedSpreadsheetHeadersByEntity as $entity => $rows)
                    @if (!empty($rows))
                        <div>
                            <div class="text-sm font-semibold text-gray-800 mb-1">{{ $entity }}</div>
                            <table class="w-full text-sm text-left border-collapse">
                                <thead>
                                <tr>
                                    <th class="pr-4 pb-1 text-gray-500 font-medium">Your Column</th>
                                    <th class="pb-1 text-gray-500 font-medium">Mapped To</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        <td class="pr-4 text-gray-900">{{ $row['header'] }}</td>
                                        <td class="text-gray-700">{{ $row['fieldLabel'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- Unmapped headers (select to assign to fxxx) --}}
    @if (!empty($unmappedSpreadsheetHeaders))
        <x-filament::section>
            <x-slot name="heading">Assign Remaining Columns</x-slot>
            <p class="text-sm text-gray-700">
                The following spreadsheet columns have not yet been mapped. Select the ones you’d like to include;
                they’ll be automatically assigned to system-defined extension fields (f001–fxxx):
            </p>

            <div class="mt-4 space-y-3">
                @foreach ($unmappedSpreadsheetHeaders as $header)
                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            wire:model.defer="fxxxFieldSelections.{{ $header }}"
                            class="h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            @checked(!isset($this->fxxxFieldSelections[$header]) || $this->fxxxFieldSelections[$header])
                        />
                        <span class="text-sm text-gray-900">{{ $header }}</span>
                    </label>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    <div
        wire:loading.delay
        wire:target="submit"
        class="fixed inset-0 bg-white bg-opacity-75 z-50 flex items-center justify-center"
    >
        <div class="text-gray-700 text-lg font-semibold">
            Please wait while we prepare the request for background processing...
        </div>
    </div>
</div>
