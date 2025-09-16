<?php

namespace App\Filament\App\Resources\CcLocations\Pages;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;


class ViewCcLocation extends ViewRecord
{
    protected static string $resource = CcLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [


            EditAction::make()->slideOver(),

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
