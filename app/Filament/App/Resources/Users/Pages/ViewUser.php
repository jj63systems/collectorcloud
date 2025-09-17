<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\Schemas\UserForm;
use App\Filament\App\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [


            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->slideOver()
                ->modalSubmitActionLabel('Save changes')
                ->fillForm(fn($record): array => $record->toArray())
                ->schema(
                    UserForm::configure(
                        Schema::make()
                    )->getComponents()
                )
                ->action(function (array $data, $record): void {
                    $record->fill($data);
                    $record->save();
                })
                ->after(fn($record) => $this->redirect(
                    UserResource::getUrl('view', ['record' => $record])
                )),

            Action::make('auditLog')
                ->label('Audit Log')
                ->icon('heroicon-o-clock')
                ->url(fn($record) => UserResource::getUrl('activities', ['record' => $record]))
                ->color('gray')
                ->tooltip('View record change history'),


            Action::make('close')
                ->icon('heroicon-o-x-mark')
                ->label('') // no label, icon only
                ->url(fn() => UserResource::getUrl())
                ->color('gray')
                ->tooltip('Close'),

        ];
    }
}
