<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Filament\Schemas\Components\Section;

class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Data Load';
    protected string $view = 'filament.app.pages.data-load';

    public $data = [];
    public $formData = [];
    public $attachment = [];
    public $sheetNames = [];
    protected $storedAttachmentPath = null;

    protected $listeners = ['runAnalysis'];

    public function mount(): void
    {
        $this->data = [
            'status' => 'idle',
            'analysis_summary' => '',
            'analysis_columns' => '',
            'analysis_issues' => '',
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Data attachment')
                    ->schema([
                        FileUpload::make('attachment')
                            ->label('Upload Spreadsheet')
                            ->disk('local')
                            ->directory('formattachments')
                            ->preserveFilenames()
                            ->multiple(false)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($state) => $this->processUploadedFileInfo($state)),

                        ViewField::make('sheet_message')
                            ->view('filament.app.components.sheet-message')
                            ->visible(fn() => count($this->sheetNames) === 1),


                        Section::make('Worksheet Selection')
                            ->schema([
                                Radio::make('formData.sheetName')
                                    ->label('Select worksheet')
                                    ->options(fn() => collect($this->sheetNames)->mapWithKeys(fn($name
                                    ) => [$name => $name])->toArray())
                                    ->required(),
                            ])
                            ->visible(fn() => count($this->sheetNames) > 1)
                            ->extraAttributes([
                                'class' => 'bg-gray-50 rounded-md p-4 border border-gray-200',
                            ])


                    ])
                    ->afterValidation(function (LivewireComponent $livewire, Get $get) {
                        $livewire->data = [
                            'status' => 'analysing',
                            'analysis_summary' => '',
                            'analysis_columns' => '',
                            'analysis_issues' => '',
                        ];

                        Notification::make()
                            ->title('Analysing file...')
                            ->info()
                            ->send();

                        $livewire->js(<<<'JS'
        setTimeout(() => {
            const wizard = document.querySelector('[data-id^="wizard-"]');
            if (wizard) {
                const nextBtn = wizard.querySelector('button[title="Next step"]');
                if (nextBtn) nextBtn.click();
            }
            window.Livewire.find($wire.__instance.id).call('processUploadAndAnalyse');
        }, 500);
    JS
                        );
                    }),
                Step::make('Review')
                    ->schema([
                        TextEntry::make('selectedSheet')
                            ->label('Worksheet being processed')
                            ->state(fn() => $this->formData['sheetName'] ?? '[Unknown]')
                            ->visible(fn() => filled(data_get($this->formData, 'sheetName')))
                            ->extraAttributes([
                                'class' => 'bg-gray-100 rounded-md p-2 mb-4 text-sm text-gray-700',
                            ]),

                        TextEntry::make('data.analysis_summary')
                            ->label('What the data appears to represent')
                            ->state(fn() => $this->data['analysis_summary'])
                            ->hidden(fn() => $this->data['status'] !== 'complete')
                            ->html(),

                        TextEntry::make('data.analysis_columns')
                            ->label('Columns and their likely meanings')
                            ->state(fn() => nl2br(e($this->data['analysis_columns'])))
                            ->hidden(fn() => $this->data['status'] !== 'complete')
                            ->html(),

                        TextEntry::make('data.analysis_issues')
                            ->label('Validation issues')
                            ->state(fn() => nl2br(e($this->data['analysis_issues'])))
                            ->hidden(fn() => $this->data['status'] !== 'complete')
                            ->html(),

                        TextEntry::make('data.analysis_loader')
                            ->state('⏳ Analysing file, please wait...')
                            ->visible(fn() => $this->data['status'] !== 'complete')
                            ->extraAttributes(['class' => 'animate-pulse text-gray-500']),
                    ])
            ])->submitAction(Action::make('submit')->label('Submit')->action('submit')),
        ];
    }

    public function submit(): void
    {
        Notification::make()->title('Form submitted!')->success()->send();
    }

    public function runAnalysis(): void
    {
        $set = fn(string $key, $value) => $this->data[str_replace('data.', '', $key)] = $value;

        if (!$this->storedAttachmentPath) {
            $set('data.analysis_summary', 'Error: No uploaded file to process.');
            $this->data['status'] = 'complete';
            return;
        }

        $text = $this->extractTextFromFile($this->storedAttachmentPath);
        $analysis = $this->callOpenAiAnalysis($text);

        if (isset($analysis['error'])) {
            $set('data.analysis_summary', 'OpenAI Error: '.$analysis['error']);
            $this->data['status'] = 'complete';
            return;
        }

        $set('data.analysis_summary', $analysis['summary'] ?? 'No summary.');
        $set('data.analysis_columns',
            collect($analysis['columns'] ?? [])->map(fn($c) => "$c[name]: $c[meaning]")->implode("\n"));
        $set('data.analysis_issues',
            collect($analysis['validation_issues'] ?? [])->map(fn($v, $k) => "$k: ".implode(', ', $v))->implode("\n"));

        $this->data['status'] = 'complete';
    }

    protected function extractTextFromFile(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $fullPath = Storage::disk('local')->path($path);

        if ($ext === 'csv') {
            return Str::limit(file_get_contents($fullPath), 4000, '...'); // ✅ cap CSV too
        }

        if (in_array($ext, ['xls', 'xlsx'])) {
            try {
                $reader = IOFactory::createReaderForFile($fullPath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($fullPath);

                $sheetName = $this->formData['sheetName'] ?? $spreadsheet->getSheetNames()[0];
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray();
                $header = array_shift($rows);

                $text = collect([$header, ...array_slice($rows, 0, 50)])
                    ->map(fn($r) => implode("\t", array_map('strval', $r)))
                    ->implode("\n");

                return Str::limit($text, 4000, '...'); // ✅ cap text length
            } catch (\Throwable $e) {
                return 'Error reading spreadsheet: '.$e->getMessage();
            }
        }

        return 'Unsupported file type.';
    }

    protected function callOpenAiAnalysis(string $plainText): array
    {
        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return ['error' => 'Missing API key.'];
        }

        $prompt = <<<PROMPT
You are an expert in interpreting data tables.

The following is the raw content of a spreadsheet (tab-separated).
NOTE: Only the first 4000 characters are included:
NOTE2: Bear in mind that this is data related to collections / archives so the context is likely to be linked to that -
it is likely to include data about one or more entities such as donors, donations, items, and locations - use this information to help identify the most likely purpose of each column.
If a column appears to be a location (i.e. a place where an item may be found) then please look at the data content to see if there is a logical structure and suggest what it might mean.
---
{$plainText}
---

Tasks:
1) Give a short high-level summary of the entities represented.
2) List the columns and what each likely represents.
3) Identify columns that look like dates or numbers, and list any invalid values.

Respond EXACTLY as JSON:
{
  "summary": "...",
  "columns": [{"name": "...", "meaning": "..."}],
  "validation_issues": {"Column A": ["...", "..."]}
}
PROMPT;

        try {
            $response = \Http::withToken($apiKey)->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-2024-08-06',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a data analysis assistant. Output valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.0,
                'response_format' => ['type' => 'json_object'],
            ]);

            $json = $response->json();
            $text = $json['choices'][0]['message']['content'] ?? '';
            $decoded = json_decode(preg_replace('/^```(?:json)?|```$/m', '', $text), true);

            return is_array($decoded) ? $decoded : ['error' => 'Unable to parse response'];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function processUploadAndAnalyse(): void
    {


        \Log::info('Starting processUploadAndAnalyse', ['rawAttachment' => $this->attachment]);

        $this->resetAnalysisFields();

        $upload = is_array($this->attachment) ? reset($this->attachment) : $this->attachment;

        if (!$upload instanceof TemporaryUploadedFile) {
            \Log::warning('Attachment is not a TemporaryUploadedFile', ['type' => gettype($upload)]);
            return;
        }

        $storedPath = $upload->store('formattachments');

        if (!$storedPath) {
            \Log::error('Failed to store uploaded file');
            return;
        }

        $this->storedAttachmentPath = $storedPath;

        \Log::info('File stored to formattachments', [
            'originalName' => $upload->getClientOriginalName(),
            'storedPath' => $storedPath,
        ]);

        $this->dispatch('nextWizardStep');
        $this->analyseUploadedFile($storedPath);
    }

    public function analyseUploadedFile(string $storedPath): void
    {
        $this->storedAttachmentPath = $storedPath;
        $this->runAnalysis();
    }

    protected function resetAnalysisFields(): void
    {
        $this->data = [
            'status' => 'analysing',
            'analysis_summary' => '⏳ Analysing...',
            'analysis_columns' => '',
            'analysis_issues' => '',
        ];
    }

    public function processUploadedFileInfo($upload): void
    {

//        ini_set('memory_limit', '512M');

        $this->sheetNames = [];

        if (!$upload instanceof TemporaryUploadedFile) {
            return;
        }

        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        $fullPath = $upload->getRealPath();

        if (in_array(strtolower($ext), ['xls', 'xlsx'])) {
            try {
                $reader = IOFactory::createReaderForFile($fullPath);
                $spreadsheet = $reader->load($fullPath);
                $this->sheetNames = $spreadsheet->getSheetNames();

                if (count($this->sheetNames) === 1) {
                    $this->formData['sheetName'] = $this->sheetNames[0];
                }
            } catch (\Throwable $e) {
                $this->sheetNames = [];
                \Log::error('Error reading spreadsheet for sheet names: '.$e->getMessage());
            }
        }
    }
}
