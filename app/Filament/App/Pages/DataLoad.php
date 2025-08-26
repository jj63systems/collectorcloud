<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\FileUpload;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Data Load';

    protected string $view = 'filament.app.pages.data-load';


//    public $attachment;

    public array $data = [];
    public array $formData = [];

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Data attachment')
                    ->schema([
                        FileUpload::make('data.attachment')
                            ->multiple(false)
                            ->directory('formattachments')
                            ->moveFiles()

                        ,
                    ])
                    ->afterValidation(function () {
                        $raw = $this->data['attachment'] ?? null;

                        if (is_array($raw)) {
                            $raw = reset($raw); // grab the first item
                        }

                        if ($raw instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                            // Generate a destination path
                            $finalPath = 'formattachments/'.$raw->getClientOriginalName();

                            // Move the file manually
                            $storedPath = $raw->storeAs('formattachments', $raw->getClientOriginalName());

                            $this->data['attachment'] = $storedPath;

                            $summary = $this->generateSummary($storedPath);
                            $this->data['summary'] = $summary;

                            return;
                        }

                        $this->data['summary'] = 'No valid file uploaded.';
                    }),

                Step::make('Review')
                    ->schema([
                        Textarea::make('data.summary')
                            ->label('Summary of Uploaded Data')
                            ->disabled(),
                    ]),
//                Step::make('Billing')
//                    ->schema([
//                        TextInput::make('formData.billing_email')
//                            ->label('Billing Email')
//                            ->email()
//                            ->required(),
//                    ]),
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


    public function updatedDataAttachment($value)
    {
        // Called when a file is uploaded in step 1
        $this->processAttachmentForOpenAI($value);
    }

    protected function generateSummary(?string $filename): string
    {
        if (!$filename) {
            return 'No file uploaded.';
        }

        $path = storage_path('app/'.$filename); // <-- uses full path now

        return "Simulated AI summary of file: ".basename($path);
    }


}
