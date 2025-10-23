<div
    x-data="{
        selected: {},
        options: @js($availableSpreadsheetHeaders),

        normalize(str) {
            return str?.toLowerCase().replace(/[^a-z0-9]/g, '') ?? '';
        },

        init() {
            const fields = @js($structuredEntities);
            const headers = this.options;

            Object.entries(fields).forEach(([entityName, fieldDefs]) => {
                Object.entries(fieldDefs).forEach(([fieldKey, fieldMeta]) => {
                    const labelNorm = this.normalize(fieldMeta.label);
                    const match = headers.find(h => this.normalize(h) === labelNorm);

                    if (match) {
                        this.selected[fieldKey] = match;
                        $wire.set(`structuredFieldMappings.${fieldKey}`, match);
                    }
                });
            });
        },

        isDisabled(option, currentKey) {
            return Object.entries(this.selected)
                .filter(([key]) => key !== currentKey)
                .some(([, val]) => val === option);
        }
    }"
    x-init="init()"
    class="space-y-6"
>
    @foreach ($structuredEntities ?? [] as $entityName => $fields)
        <x-filament::section>
            <x-slot name="heading">
                {{ $entityName === 'FLEX FIELDS' ? 'Previously labelled flex fields' : ucfirst(strtolower($entityName)) }}
            </x-slot>

            <table class="w-full text-sm text-gray-700">
                <tbody>
                @foreach ($fields as $fieldKey => $fieldMeta)
                    <tr class="border-t border-gray-200">
                        <td class="py-2 pr-4 font-medium w-1/3">
                            {{ $fieldMeta['label'] }}
                        </td>
                        <td class="py-2">
                            <select
                                x-model="selected['{{ $fieldKey }}']"
                                x-on:change="$wire.set('structuredFieldMappings.{{ $fieldKey }}', selected['{{ $fieldKey }}'])"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 text-sm"
                            >
                                <option value="">-- Select column --</option>
                                @foreach ($availableSpreadsheetHeaders ?? [] as $header)
                                    <option
                                        value="{{ $header }}"
                                        x-bind:disabled="isDisabled('{{ $header }}', '{{ $fieldKey }}')"
                                    >
                                        {{ $header }}
                                    </option>
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
