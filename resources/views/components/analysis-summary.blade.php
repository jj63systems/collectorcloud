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
                are represented or referenced within your upload.
            <ul class="list-none space-y-1 text-sm text-gray-800">
                @foreach ($entities as $entity => $example)
                    <li class="flex items-start">
                        <span class="mr-2">
                            {{ $example ? '✅' : '❌' }}
                        </span>
                        <span class="font-semibold uppercase">{{ $entity }}</span>
                        <span class="ml-2 text-gray-600">
                            (e.g. {{ is_string($example) ? e($example) : '—' }})
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif

</div>
