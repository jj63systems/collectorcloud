<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\CcItemStages\CcItemStageResource;
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
        $teamId = auth()->user()->current_team_id;

        $this->dataLoadOptions = CcDataLoad::where('team_id', $teamId)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function ($d) {
                $uploadedAt = \Carbon\Carbon::parse($d->uploaded_at)->format('Y-m-d H:i');
                $label = "{$d->id} / {$d->filename} / {$d->worksheet_name} / {$uploadedAt}";
                return [$d->id => $label];
            })
            ->toArray();

        if (empty($this->dataLoadOptions)) {
            $this->dataLoad = null;
            $this->selectedDataLoadId = null;
            return;
        }

        $recordId = request()->query('record');
        $this->dataLoad = $recordId
            ? CcDataLoad::findOrFail($recordId)
            : CcDataLoad::where('team_id', $teamId)->orderByDesc('id')->first();

        $this->selectedDataLoadId = $this->dataLoad->id;
        $this->startTime = now();
    }

    public function getShouldPollProperty(): bool
    {
        if (!$this->dataLoad) {
            return false;
        }

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
        if (!$this->dataLoad) {
            return;
        }

        $this->dataLoad->update([
            'validation_status' => 'validating',
            'validation_progress' => 0,
        ]);

        ValidateStagedItemsJob::dispatch($this->dataLoad->id);
    }

    public function pollStatus(): void
    {
        if (!$this->dataLoad) {
            return;
        }

        $this->dataLoad->refresh();

        if (
            $this->dataLoad->status === 'completed' &&
            (
                $this->dataLoad->validation_status === 'completed' ||
                (int) $this->dataLoad->validation_progress >= 100
            )
        ) {
            \Log::info('Polling naturally stopped: status complete or progress >= 100');
        }
    }

    public function discardUpload(): void
    {
        if (!$this->dataLoad) {
            return;
        }

        CcItemStage::where('data_load_id', $this->dataLoad->id)->delete();
        $this->dataLoad->delete();

        $this->redirect(route('filament.app.pages.data-load'));
    }

    public function commitUpload(): void
    {
        if (!$this->dataLoad) {
            return;
        }

        CommitStagedItemsJob::dispatch($this->dataLoad->id);

        \Filament\Notifications\Notification::make()
            ->title('Commit job queued')
            ->success()
            ->send();
    }

    public function viewData(): void
    {
        if (!$this->dataLoad) {
            return;
        }

        $baseUrl = CcItemStageResource::getUrl();

        $queryString = http_build_query([
            'filters' => [
                'data_load_id' => ['value' => $this->dataLoad->id],
                'has_data_error' => ['value' => false],
            ],
        ]);

        $this->redirect($baseUrl.'?'.$queryString);
    }
}
