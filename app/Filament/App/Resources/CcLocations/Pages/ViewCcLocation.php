<?php

namespace App\Filament\App\Resources\CcLocations\Pages;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use App\Models\tenant\CcLocation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Exceptions\Halt;


class ViewCcLocation extends ViewRecord
{
    protected static string $resource = CcLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [


            EditAction::make()->slideOver(),


            DeleteAction::make()->slideOver()
//                ->visible(fn(CcLocation $record) => $record->canDelete())
                ->before(function (CcLocation $record) {
                    if (!$record->canDelete()) {
                        Notification::make()
                            ->title('This location has child locations and cannot be deleted.')
                            ->danger()
                            ->send();
                        throw new Halt();
                    }
                }),

//            Action::make('activities')->url(fn($record) => CcLocationResource::getUrl('activities',
//                ['record' => $record])),
            Action::make('auditLog')
                ->label('Audit Log')
                ->icon('heroicon-o-clock')
                ->url(fn($record) => CcLocationResource::getUrl('activities', ['record' => $record]))
                ->color('gray')
                ->tooltip('View record change history'),


            Action::make('close')
                ->icon('heroicon-o-x-mark')
                ->label('') // no label, icon only
                ->url(fn() => CcLocationResource::getUrl())
                ->color('gray')
                ->tooltip('Close'),

        ];
    }


}
