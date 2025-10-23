<div class="space-y-6">

    {{-- Mapped Columns Block --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-md p-6">
        <h2 class="text-md font-semibold mb-2">Mapped Columns</h2>

        @php
            $validMappings = collect($mappedSpreadsheetHeadersByEntity ?? [])
                ->filter(fn($header) => is_string($header) || (is_array($header) && !empty($header['header'])));
        @endphp

        @if ($validMappings->isNotEmpty())
            <p class="text-sm text-gray-600 mb-4">
                These spreadsheet columns have already been assigned to known system fields:
            </p>

            <table class="w-full text-sm text-left">
                <thead>
                <tr>
                    <th class="py-2 pr-4 font-medium text-gray-700">Database field</th>
                    <th class="py-2 font-medium text-gray-700">Your spreadsheet column</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($validMappings as $fieldLabel => $header)
                    <tr class="border-t border-gray-100">
                        <td class="py-2 pr-4">{{ $fieldLabel }}</td>
                        <td class="py-2">
                            {{ is_array($header) ? $header['header'] ?? '[Missing header]' : $header }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-gray-600">
                None of your spreadsheet columns were matched to known system fields.
            </p>
        @endif
    </div>

    {{-- Flex Field Mappings Block --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-md p-6">
        <h2 class="text-md font-semibold mb-2">Flex Field Mappings</h2>
        <p class="text-sm text-gray-600 mb-4">
            These spreadsheet columns will be imported as flexible fields:
        </p>

        <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
            @foreach ($fxxxMappings ?? [] as $row)
                <li>{{ $row['column'] }}</li>
            @endforeach
        </ul>
    </div>


</div>
