<?php

namespace App\Filament\App\Resources\CcLocations\Pages;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use App\Filament\App\Resources\CcLocations\Schemas\CcLocationForm;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewCcLocation extends ViewRecord
{
    protected static string $resource = CcLocationResource::class;

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
                    CcLocationForm::configure(
                        Schema::make()
                    )->getComponents()
                )
                ->extraModalFooterActions([
                    \Filament\Actions\Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->visible(fn($record) => static::getResource()::canDelete($record))
                        ->action(function ($record) {
                            if (!$record->canDelete()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('This location has child locations and cannot be deleted.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->delete();

                            \Filament\Notifications\Notification::make()
                                ->title('Location deleted')
                                ->success()
                                ->send();

                            $this->redirect(CcLocationResource::getUrl('index'));
                        }),
                ])
                ->visible(fn($record) => static::getResource()::canEdit($record))
                ->action(function (array $data, $record): void {
                    $record->fill($data);
                    $record->save();   // cascadePathUpdate will fire via model event
                })
                ->after(fn($record) => $this->redirect(
                    CcLocationResource::getUrl('view', ['record' => $record])
                )),

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
