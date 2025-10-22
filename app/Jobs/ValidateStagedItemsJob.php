<?php

namespace App\Jobs;

use App\Models\Tenant\CcDataLoad;
use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcItemStage;
use App\Services\FieldStructureHelper;
use App\Services\StagedItemValidatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ValidateStagedItemsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public function __construct(public int $dataLoadId)
    {
    }

    public function handle(): void
    {
        Log::info('ValidateStagedItemsJob starting', ['data_load_id' => $this->dataLoadId]);

        $dataLoad = CcDataLoad::findOrFail($this->dataLoadId);
        $stagedRows = CcItemStage::where('data_load_id', $this->dataLoadId)->get();
        $fieldMappings = CcFieldMapping::where('team_id', $dataLoad->team_id)->get()->keyBy('field_name');

        // Build core field type map from FieldStructureHelper (flattened)
        $structured = FieldStructureHelper::getStructuredFieldsByEntity();
        $coreFieldTypes = collect([
            ...$structured['ITEMS'],
            ...$structured['DONORS'],
            ...$structured['DONATIONS'],
            ...$structured['LOCATIONS'],
        ])->mapWithKeys(fn($def, $key) => [Str::after($key, '.') => $def])->toArray();

        // Instantiate validator with mappings and core types
        $validator = new StagedItemValidatorService($fieldMappings, $coreFieldTypes);

        // Run validation
        $result = $validator->validateCollection($stagedRows);

        Log::info('about to foreach');
        foreach ($result['invalid'] as $invalidRow) {
            Log::info('invalid row', ['id' => $invalidRow['id']]);

            CcItemStage::where('id', $invalidRow['id'])->update([
                'has_data_error' => true,
                'data_error_summary' => json_encode($invalidRow['errors']),
            ]);
        }

        $dataLoad->update([
            'validation_status' => 'complete',
            'validation_progress' => 100,
        ]);

        Log::info('ValidateStagedItemsJob complete', ['data_load_id' => $this->dataLoadId]);
    }
}
