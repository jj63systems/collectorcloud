<?php

namespace App\Jobs;

use App\Models\Tenant\CcDataLoad;
use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcItemStage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDataLoad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $dataLoadId)
    {
    }

    public function handle(): void
    {
        Log::info('ProcessDataLoad starting', ['data_load_id' => $this->dataLoadId]);

        try {
            $dataLoad = CcDataLoad::findOrFail($this->dataLoadId);
            Log::info('DataLoad found', ['id' => $dataLoad->id]);

            $teamId = $dataLoad->team_id;

            // --- Load and validate mappings ---
            $confirmedMappings = json_decode($dataLoad->confirmed_field_mappings ?? '{}', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Invalid confirmed_field_mappings JSON: ".json_last_error_msg());
            }

            $step3Mappings = $confirmedMappings['cc_items'] ?? [];
            $fxxxMappings = $confirmedMappings['fxxx'] ?? [];  // format: 'Original Header' => 'f001'

            Log::info('cc_items mappings', $step3Mappings);
            Log::info('fxxx mappings', $fxxxMappings);

            // --- Load and validate sample rows ---
            $rows = json_decode($dataLoad->sample_rows ?? '[]', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Invalid sample_rows JSON: ".json_last_error_msg());
            }

            $rowCount = is_array($rows) ? count($rows) : 0;
            Log::info('rowCount', ['rowCount' => $rowCount]);

            if ($rowCount === 0) {
                Log::warning("No rows to process", ['data_load_id' => $dataLoad->id]);
                $dataLoad->update([
                    'status' => 'completed',
                    'rows_processed' => 0,
                ]);
                return;
            }

            $processed = 0;
            foreach ($rows as $row) {
                $fields = [
                    'team_id' => $teamId,
                    'data_load_id' => $dataLoad->id,
                ];

                // Step 3 — known field mappings
                foreach ($step3Mappings as $dbField => $header) {
                    if (isset($row[$header])) {
                        $fields[$dbField] = $row[$header];
                    }
                }

                // Step 4 — unmapped headers assigned to fxxx
                foreach ($fxxxMappings as $header => $fxxxField) {
                    if (isset($row[$header])) {
                        $fields[$fxxxField] = $row[$header];

                        // Upsert into cc_field_mappings
                        CcFieldMapping::updateOrCreate(
                            [
                                'team_id' => $teamId,
                                'field_name' => $fxxxField,
                            ],
                            [
                                'label' => $header,
                                'data_type' => 'TEXT', // defaulting to TEXT, adjust if needed
                            ]
                        );
                    }
                }

                CcItemStage::create($fields);
                $processed++;

                if ($processed % 50 === 0) {
                    $dataLoad->update(['rows_processed' => $processed]);
                }
            }

            // Final update
            $dataLoad->update([
                'status' => 'completed',
                'rows_processed' => $processed,
            ]);

            Log::info("ProcessDataLoad completed", [
                'data_load_id' => $dataLoad->id,
                'total_rows' => $processed,
            ]);

        } catch (\Throwable $e) {
            Log::error('ProcessDataLoad failed', [
                'data_load_id' => $this->dataLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            CcDataLoad::where('id', $this->dataLoadId)->update([
                'status' => 'failed',
            ]);
        }
    }
}
