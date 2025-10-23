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
    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.app.pages.data-load-status';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Data Load Status';

    public ?CcDataLoad $dataLoad = null;

    public ?Carbon $startTime = null;

    public ?int $selectedDataLoadId = null;

    public array $dataLoadOptions = [];

    public function mount(): void
    {
        $recordId = request()->query('record');

        $this->dataLoad = $recordId
            ? CcDataLoad::findOrFail($recordId)
            : CcDataLoad::where('team_id', auth()->user()->current_team_id)
                ->orderByDesc('id')
                ->firstOrFail();

        $this->selectedDataLoadId = $this->dataLoad->id;
        $this->startTime = now();

        $this->dataLoadOptions = CcDataLoad::where('team_id', auth()->user()->current_team_id)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function ($d) {
                $uploadedAt = \Carbon\Carbon::parse($d->uploaded_at)->format('Y-m-d H:i');
                $label = "{$d->id} / {$d->filename} / {$d->worksheet_name} / {$uploadedAt}";
                return [$d->id => $label];
            })
            ->toArray();
    }

    public function getShouldPollProperty(): bool
    {
        return $this->dataLoad->status !== 'completed'
            || $this->dataLoad->validation_status === 'validating';
    }

    public function updatedSelectedDataLoadId(): void
    {
        \Log::info('Dropdown changed to: '.$this->selectedDataLoadId);

        $this->dataLoad = CcDataLoad::findOrFail($this->selectedDataLoadId);
        $this->startTime = now();
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

        // Logging optional; not necessary anymore for polling control
        if (
            $this->dataLoad->status === 'completed' &&
            (
                $this->dataLoad->validation_status === 'complete' ||
                (int) $this->dataLoad->validation_progress >= 100
            )
        ) {
            \Log::info('Polling naturally stopped: status complete or progress >= 100');
        }
    }

    public function discardUpload(): void
    {
        CcItemStage::where('data_load_id', $this->dataLoad->id)->delete();
        $this->dataLoad->delete();

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
}
