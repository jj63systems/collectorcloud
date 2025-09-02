<div class="space-y-6">

    {{-- Summary Block --}}
    @if (!empty($summaryText))
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
            <div class="text-lg font-semibold mb-2">Summary</div>
            <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ is_string($summaryText) ? $summaryText : implode("\n", $summaryText) }}
            </p>
        </div>
    @endif

    {{-- Columns Block --}}
    @if (!empty($columns))
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
            <div class="text-lg font-semibold mb-4">Columns and their likely meanings</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>

                    </thead>
                    <tbody>
                    @foreach ($columns as $pgName => $col)
                        <tr class="border-b last:border-none">
                            <td class="py-2 px-3 font-semibold text-gray-900">
                                <div class="text-xs text-gray-500 italic">{{ $pgName }}</div>
                            </td>
                            <td class="py-2 px-3 text-gray-700">
                                {{ is_array($col['meaning']) ? implode(', ', $col['meaning']) : $col['meaning'] }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Validation Issues Block --}}
    @if (!empty($validationIssues))
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
            <div class="text-lg font-semibold mb-4">Validation Issues</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">

                    <tbody>
                    @foreach ($validationIssues as $pgName => $issue)
                        <tr class="border-b last:border-none">
                            <td class="py-2 px-3 font-semibold text-gray-900">
                                {{ $pgName }}

                            </td>
                            <td class="py-2 px-3 text-gray-700">
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
        </div>
    @endif

    {{-- Entities Block --}}
    @if (!empty($entities))
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
            <div class="text-lg font-semibold mb-2">Entities</div>
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
        </div>
    @endif

</div>
