<?php

namespace App\Filament\App\Pages;

use App\Jobs\CommitStagedItemsJob;
use App\Jobs\ValidateStagedItemsJob;
use App\Models\Tenant\CcDataLoad;
use App\Models\Tenant\CcItemStage;
use Carbon\Carbon;
use Filament\Pages\Page;

class DataLoadStatus extends Page
{
    public ?CcDataLoad $dataLoad = null;

    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.app.pages.data-load-status';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Data Load Status';

    public ?Carbon $startTime = null;


    public ?int $selectedDataLoadId = null;
    public array $dataLoadOptions = [];
    public bool $shouldPoll = true;

    public function mount(): void
    {
        $recordId = request()->query('record');

        if ($recordId) {
            $this->dataLoad = \App\Models\Tenant\CcDataLoad::findOrFail($recordId);
        } else {
            $this->dataLoad = \App\Models\Tenant\CcDataLoad::where('team_id', auth()->user()->current_team_id)
                ->orderByDesc('id')
                ->firstOrFail();

            $this->shouldPoll =
                $this->dataLoad->status !== 'completed' ||
                $this->dataLoad->validation_status === 'validating';
        }

        $this->startTime = now();

        $this->dataLoadOptions = \App\Models\Tenant\CcDataLoad::where('team_id', auth()->user()->current_team_id)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function ($d) {
                $uploadedAt = \Carbon\Carbon::parse($d->uploaded_at)->format('Y-m-d H:i');
                $label = "{$d->id} / {$d->filename} / {$d->worksheet_name} / {$uploadedAt}";
                return [$d->id => $label];
            })
            ->toArray();

        $this->selectedDataLoadId = $this->dataLoad->id;
    }

    public function startValidation(): void
    {
        $this->dataLoad->update([
            'validation_status' => 'validating',
            'validation_progress' => 0,
        ]);

        ValidateStagedItemsJob::dispatch($this->dataLoad->id);
    }

    public function pollStatus(): void
    {
        $this->dataLoad->refresh();

//        if ($this->dataLoad->status === 'completed') {
//            $this->redirect(
//                CcItemStageResource::getUrl().'?filters[data_load_id][value]='.$this->dataLoad->id
//            );
//        }
    }

    public function getShouldPollProperty(): bool
    {
        return $this->dataLoad->status !== 'completed'
            || $this->dataLoad->validation_status === 'validating';
    }


    public function discardUpload(): void
    {
        // Delete staged items and the data load record
        CcItemStage::where('data_load_id', $this->dataLoad->id)->delete();
        $this->dataLoad->delete();

        // Redirect back to the data load wizard
        $this->redirect(route('filament.app.pages.data-load'));
    }


    public function commitUpload(): void
    {
        CommitStagedItemsJob::dispatch($this->dataLoad->id);

        \Filament\Notifications\Notification::make()
            ->title('Commit job queued')
            ->success()
            ->send();
    }

    public function reviewErrors(): void
    {
        $baseUrl = \App\Filament\App\Resources\CcItemStages\CcItemStageResource::getUrl();

        $queryString = http_build_query([
            'filters' => [
                'data_load_id' => ['value' => $this->dataLoad->id],
                'has_data_error' => ['value' => true],
            ],
        ]);

        $this->redirect($baseUrl.'?'.$queryString);
    }

    public function updatedSelectedDataLoadId(): void
    {
        \Log::info('Dropdown changed to: '.$this->selectedDataLoadId);

        $this->dataLoad = CcDataLoad::findOrFail($this->selectedDataLoadId);
        $this->startTime = now();

        $this->shouldPoll =
            $this->dataLoad->status !== 'completed' ||
            $this->dataLoad->validation_status === 'validating';
    }
}
