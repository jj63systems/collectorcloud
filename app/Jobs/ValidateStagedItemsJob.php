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
use Throwable;

class ValidateStagedItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Allow long-running validation for large datasets.
     */
    public int $timeout = 900; // 15 minutes
    public int $tries = 3;     // Retry up to 3 times if it fails

    public function __construct(public int $dataLoadId)
    {
    }

    public function handle(): void
    {
        Log::info('ValidateStagedItemsJob starting', ['data_load_id' => $this->dataLoadId]);

        try {
            $dataLoad = CcDataLoad::findOrFail($this->dataLoadId);

            // Step 1: Retrieve mappings and field definitions
            $fieldMappings = CcFieldMapping::where('team_id', $dataLoad->team_id)
                ->get()
                ->keyBy('field_name');

            $structured = FieldStructureHelper::getStructuredFieldsByEntity();
            $coreFieldTypes = collect([
                ...$structured['ITEMS'],
                ...$structured['DONORS'],
                ...$structured['DONATIONS'],
                ...$structured['LOCATIONS'],
            ])->mapWithKeys(fn($def, $key) => [Str::after($key, '.') => $def])->toArray();

            $validator = new StagedItemValidatorService($fieldMappings, $coreFieldTypes);

            // Step 2: Process staged rows in manageable chunks
            $totalRows = CcItemStage::where('data_load_id', $this->dataLoadId)->count();
            $processed = 0;
            $chunkSize = 500;

            CcItemStage::where('data_load_id', $this->dataLoadId)
                ->orderBy('id')
                ->chunkById($chunkSize, function ($rows) use (&$processed, $totalRows, $validator, $dataLoad) {
                    $result = $validator->validateCollection($rows);

                    foreach ($result['invalid'] as $invalidRow) {
                        CcItemStage::where('id', $invalidRow['id'])->update([
                            'has_data_error' => true,
                            'data_error_summary' => json_encode($invalidRow['errors']),
                        ]);
                    }

                    $processed += count($rows);
                    $progress = intval(($processed / max(1, $totalRows)) * 100);

                    $dataLoad->update([
                        'validation_status' => 'validating',
                        'validation_progress' => $progress,
                    ]);

                    Log::info('Validation progress update', [
                        'data_load_id' => $this->dataLoadId,
                        'processed' => $processed,
                        'total' => $totalRows,
                        'progress' => $progress,
                    ]);
                });

            // Step 3: Final completion update
            $dataLoad->update([
                'validation_status' => 'completed',
                'validation_progress' => 100,
            ]);

            Log::info('ValidateStagedItemsJob complete', ['data_load_id' => $this->dataLoadId]);

        } catch (Throwable $e) {
            Log::error('ValidateStagedItemsJob failed', [
                'data_load_id' => $this->dataLoadId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw so Laravel marks the job as failed
            throw $e;
        }
    }
}
