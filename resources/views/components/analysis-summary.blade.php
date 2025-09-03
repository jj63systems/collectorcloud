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

    <p>&nbsp;</p>
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
                        <tr class="border-b last:border-none">
                            <td>
                                <div class="text-xs  p-2">{{ $pgName }}</div>
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

    <p>&nbsp;</p>

    {{-- Validation Issues Block --}}
    @if (!empty($validationIssues))
        <x-filament::section>
            <x-slot name="heading">
                Validation Issues
            </x-slot>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <tbody>
                    @foreach ($validationIssues as $pgName => $issue)
                        <tr class="border-b last:border-none">
                            <td>
                                <div class="text-xs  p-2">
                                    {{ $pgName }}
                                </div>
                            </td>
                            <td>
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
        </x-filament::section>
    @endif

    <p>&nbsp;</p>

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

            <div class="mt-4 space-y-2 bg-gray-100 rounded-md">
                @foreach ($entities as $entity => $example)
                    <label class="flex items-center space-x-2 text-sm text-gray-800">
                        <input
                            type="checkbox"
                            wire:model.defer="{{ $checkboxFieldName }}"
                            value="{{ $entity }}"
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                        />

                        <span class="font-semibold uppercase p-4 mt-4">{{ $entity }}</span>
                    </label>
                @endforeach
            </div>

        </x-filament::section>
    @endif

</div>
