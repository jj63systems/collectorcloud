<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;

class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Data Load';

    protected string $view = 'filament.app.pages.data-load';


    public array $formData = [];

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Order')
                    ->schema([
                        TextInput::make('formData.order_number')
                            ->label('Order Number')
                            ->required(),
                    ]),
                Step::make('Delivery')
                    ->schema([
                        TextInput::make('formData.delivery_address')
                            ->label('Delivery Address')
                            ->required(),
                    ]),
                Step::make('Billing')
                    ->schema([
                        TextInput::make('formData.billing_email')
                            ->label('Billing Email')
                            ->email()
                            ->required(),
                    ]),
            ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Submit')
                        ->action('submit')
                ),
        ];
    }

    public function submit(): void
    {
        Notification::make()
            ->title('Form submitted!')
            ->success()
            ->send();
    }


}
