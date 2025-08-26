<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Data Load';
    protected string $view = 'filament.app.pages.data-load';

    public array $data = [];
    public array $formData = [];

    public ?string $storedAttachmentPath = null;

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Data attachment')
                    ->schema([
                        FileUpload::make('data.attachment')
                            ->multiple(false)
                            ->directory('formattachments')
                            ->moveFiles(), // default livewire physical move on upload
                    ])
                    ->afterValidation(function () {
                        $raw = $this->data['attachment'] ?? null;

                        if (is_array($raw)) {
                            $raw = reset($raw);
                        }

                        if ($raw instanceof TemporaryUploadedFile) {
                            // Clean up any previously stored file
                            if ($this->storedAttachmentPath && Storage::disk('local')->exists($this->storedAttachmentPath)) {
                                Storage::disk('local')->delete($this->storedAttachmentPath);
                            }

                            $finalFilename = $raw->getClientOriginalName();
                            $finalPath = 'formattachments/'.$finalFilename;

                            // Ensure overwrite if already exists
                            if (Storage::disk('local')->exists($finalPath)) {
                                Storage::disk('local')->delete($finalPath);
                            }

                            // Move file
                            $storedPath = $raw->storeAs('formattachments', $finalFilename);
                            $this->storedAttachmentPath = $storedPath;

                            $this->data['summary'] = $this->generateSummary($storedPath);
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
            ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Submit')
                        ->action('submit')
                ),
        ];
    }


    public function updated($propertyName, $value): void
    {
        if ($propertyName === 'data.attachment') {
            \Log::info('Intercepted update to data.attachment', ['value' => $value]);

            // If user removed the file
            if (empty($value)) {
                if (!empty($this->storedAttachmentPath)) {
                    \Log::info('Deleting stored file due to removal');
                    Storage::disk('local')->delete($this->storedAttachmentPath);
                    $this->storedAttachmentPath = null;
                }

                $this->data['summary'] = null;
                return;
            }

            // Handle re-uploaded file
            $raw = is_array($value) ? reset($value) : $value;

            if ($raw instanceof TemporaryUploadedFile) {
                $finalPath = 'formattachments/'.$raw->getClientOriginalName();

                if (Storage::disk('local')->exists($finalPath)) {
                    \Log::info('Deleting duplicate file before re-upload');
                    Storage::disk('local')->delete($finalPath);
                }
            }
        }
    }


    public function submit(): void
    {
        Notification::make()
            ->title('Form submitted!')
            ->success()
            ->send();
    }

    protected function generateSummary(?string $filename): string
    {
        if (!$filename) {
            return 'No file uploaded.';
        }

        $path = storage_path('app/'.$filename);
        return "Simulated AI summary of file: ".basename($path);
    }
}
