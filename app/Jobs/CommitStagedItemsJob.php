<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CommitStagedItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $dataLoadId)
    {
    }

    public function handle(): void
    {
        // TODO: Copy valid rows from cc_items_stage to cc_items
        // E.g. CcItemStage::where('data_load_id', $this->dataLoadId)->where('has_data_error', false)...
    }
}
