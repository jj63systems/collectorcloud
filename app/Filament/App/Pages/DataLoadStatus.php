<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\CcItemStages\CcItemStageResource;
use App\Models\Tenant\CcDataLoad;
use Carbon\Carbon;
use Filament\Pages\Page;


class DataLoadStatus extends Page
{
    public ?CcDataLoad $dataLoad = null;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.app.pages.data-load-status';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Data Load Status';

    public ?Carbon $startTime = null;


    public function mount(): void
    {
        $record = request()->query('record');
        $this->dataLoad = CcDataLoad::findOrFail($record);
        $this->startTime = now();
    }

    public function pollStatus(): void
    {
        $this->dataLoad->refresh();

        if ($this->dataLoad->status === 'completed') {
            $this->redirect(
                CcItemStageResource::getUrl().'?filters[data_load_id][value]='.$this->dataLoad->id
            );
        }
    }
}
