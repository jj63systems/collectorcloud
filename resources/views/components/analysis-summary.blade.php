<div class="space-y-6">

    {{-- Summary Block --}}
    @if (!empty($summaryText))
        <x-filament::section>
            <x-slot name="heading">
                Summary
            </x-slot>
            <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ is_string($summaryText) ? $summaryText : implode("\n", $summaryText) }}
            </p>
        </x-filament::section>
    @endif

    {{-- Columns Block --}}
    @if (!empty($columns))
        <x-filament::section>
            <x-slot name="heading">
                Columns and their likely meanings
            </x-slot>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <tbody>
                    @foreach ($columns as $pgName => $col)
                        <tr class="last:border-none">
                            <td>
                                <div class="p-2">{{ $pgName }}</div>
                            </td>
                            <td>
                                &nbsp;-&nbsp;{{ is_array($col['meaning']) ? implode(', ', $col['meaning']) : $col['meaning'] }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif


    {{-- Validation Issues Block --}}
    @if ($rowsAnalysed > 0)
        <x-filament::section>
            <x-slot name="heading">
                Validation Issues
            </x-slot>

            <div class="text-sm text-gray-700 mb-2">
                We processed {{ $rowsAnalysed }} record{{ $rowsAnalysed === 1 ? '' : 's' }} from the spreadsheet
                @if (empty($validationIssues))
                    and found no obvious data issues.
                @else
                    and found the following potential issues:
                @endif
            </div>

            @if (!empty($validationIssues))
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <tbody>
                        @foreach ($validationIssues as $pgName => $issue)
                            <tr class="last:border-none">
                                <td>
                                    <div class="p-2">
                                        {{ $pgName }}
                                    </div>
                                </td>
                                <td class="align-left">
                                    &nbsp;-&nbsp;
                                    @if (is_array($issue))
                                        {!! implode('<br>', array_map('e', $issue)) !!}
                                    @else
                                        {{ $issue }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif

    {{-- Entities Block --}}
    @if (!empty($entities))
        <x-filament::section>
            <x-slot name="heading">
                Entities
            </x-slot>

            <p class="text-sm text-gray-700 whitespace-pre-line">
                Based on the analysis of the column headings and likely content, we think the following system entities
                are represented or referenced within your upload. Please select those which you would like to import or
                use.
            </p>

            @php
                $checkboxFieldName = 'selectedEntities';
            @endphp

            <div class="mt-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($entities as $entity => $example)
                        @php
                            $inputId = 'entity-' . $loop->index;
                        @endphp
                        <label for="{{ $inputId }}"
                               class="flex items-center gap-6 rounded-lg p-4 transition focus-within:ring-2 focus-within:ring-primary-500">
                            <input
                                id="{{ $inputId }}"
                                type="checkbox"
                                wire:model.defer="{{ $checkboxFieldName }}"
                                value="{{ $entity }}"
                                class="h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="font-semibold uppercase text-gray-900">{{ $entity }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

        </x-filament::section>
    @endif

</div>
