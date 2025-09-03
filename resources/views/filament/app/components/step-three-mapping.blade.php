<div class="space-y-6">

    @foreach ($structuredEntities ?? [] as $entityName => $fields)
        <x-filament::section>
            <x-slot name="heading">
                {{ ucfirst(strtolower($entityName)) }}
            </x-slot>

            <table class="w-full text-sm text-gray-700">
                <tbody>
                @foreach ($fields as $fieldKey => $fieldMeta)
                    <tr class="border-gray-200">
                        <td class="py-2 pr-4 font-medium w-1/3">
                            {{ $fieldMeta['label'] }} &nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                        <td class="py-2">
                            <select
                                wire:model.defer="structuredFieldMappings.{{ $fieldKey }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 text-sm"
                            >
                                <option value="">-- Select column --</option>
                                @foreach ($availableSpreadsheetHeaders ?? [] as $header)
                                    <option value="{{ $header }}">{{ $header }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @endforeach

</div>
