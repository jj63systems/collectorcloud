<?php

namespace App\Filament\App\Resources\CcLocations\Pages;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use App\Models\Tenant\CcLocation;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Log;

class EditCcLocation extends EditRecord
{
    protected static string $resource = CcLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->slideOver()
                ->before(function (CcLocation $record) {
                    if (!$record->canDelete()) {
                        Notification::make()
                            ->title('This location has child locations and cannot be deleted.')
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {

        Log::info('Location updated: '.$this->record->name);
        // Trigger ripple-down path/depth update after editing
        $this->record->cascadePathUpdate();

        // Redirect to the view page
        $this->redirect(CcLocationResource::getUrl('view', ['record' => $this->record]));


    }
}
