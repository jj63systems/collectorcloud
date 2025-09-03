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


    // --- variables ------------------------------
    protected static ?string $navigationLabel = 'Data Load';
    protected string $view = 'filament.app.pages.data-load';

    public $data = [];
    public $formData = [];
    public $attachment = [];
    public $sheetNames = [];
    protected $storedAttachmentPath = null;

    protected $listeners = ['runAnalysis'];

    public string $summaryText = '';
    public array $columns = [];
    public array $validationIssues = [];
    public array $entities = [];

    public array $selectedEntities = [];

    public int $rowsAnalysed = 0; // Number of rows analysed (excluding header)

    protected array $headerMap = []; // normalized_name => original_header


    // --- end of variables ------------------------------


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

                        ViewField::make('data.analysis_combined')
                            ->view('filament.app.components.analysis-blocks', [
                                'summaryText' => $this->summaryText,
                                'columns' => $this->columns,
                                'validationIssues' => $this->validationIssues,
                                'entities' => $this->entities,
                                'rowsAnalysed' => $this->rowsAnalysed,

                            ])
                            ->visible(fn() => $this->data['status'] === 'complete'),


                        TextEntry::make('data.analysis_loader')
                            ->state('⏳ Analysing file, please wait...')
                            ->visible(fn() => $this->data['status'] !== 'complete')
                            ->extraAttributes(['class' => 'animate-pulse bg-gray-100 rounded-md p-2 mb-4 text-sm'])
                            ->html(),
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

        $result = $this->extractTextFromFile($this->storedAttachmentPath);
        $plainText = $result['text'] ?? '';
        $this->rowsAnalysed = $result['rowsAnalysed'] ?? 0;

        $analysis = $this->callOpenAiAnalysis($plainText);

        if (isset($analysis['error'])) {
            \Log::info('OpenAI analysis error', ['error' => $analysis['error']]);
            $set('data.analysis_summary', 'OpenAI Error: '.$analysis['error']);
            $this->data['status'] = 'complete';
            return;
        }

        // ✅ Assign structured outputs
        $this->summaryText = $analysis['summary'] ?? '';

        $this->columns = collect($analysis['columns'] ?? [])
            ->mapWithKeys(function ($col) {
                $original = $col['name'] ?? '[Unnamed]';
                $key = $this->toPostgresColumnName($original);
                return [
                    $key => [
                        'original' => $original,
                        'meaning' => $col['meaning'] ?? '[No description]',
                    ],
                ];
            })
            ->toArray();

        $this->validationIssues = collect($analysis['validation_issues'] ?? [])
            ->mapWithKeys(function ($issues, $maybeNormalized) {
                $normalized = $this->normalizeHeader($maybeNormalized);

                $originalHeader = $this->headerMap[$normalized] ?? $maybeNormalized;

                return [$originalHeader => $issues];
            })
            ->toArray();

        \Log::info('Validation issue keys received', [
            'raw_keys' => array_keys($analysis['validation_issues'] ?? []),
            'mapped_keys' => array_keys($this->validationIssues),
        ]);

        $this->entities = collect($analysis['entities'] ?? [])
            ->mapWithKeys(function ($entity) {
                $key = strtoupper($entity['name'] ?? 'UNKNOWN');
                $examples = $entity['examples'] ?? [];
                return [$key => $entity['detected'] ? ($examples[0] ?? '—') : null];
            })
            ->toArray();

        $this->selectedEntities = array_keys(array_filter($this->entities, fn($v) => !is_null($v)));

        $this->data['status'] = 'complete';

        \Log::info('Analysis complete', ['rowsAnalysed' => $this->rowsAnalysed]);
    }

    protected function extractTextFromFile(string $path): array
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $fullPath = Storage::disk('local')->path($path);

        if ($ext === 'csv') {
            $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $header = array_shift($lines);
            $rows = array_slice($lines, 0, 50); // Limit to 50 rows max
            $text = implode("\n", [$header, ...$rows]);

            return [
                'text' => Str::limit($text, 4000, '...'),
                'rowsAnalysed' => count($rows),
            ];
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
                $sample = array_slice($rows, 0, 50); // Limit to 50 rows max
                $this->headerMap = collect($header)
                    ->mapWithKeys(fn($h) => [$this->normalizeHeader((string) $h) => (string) $h])
                    ->toArray();

                $text = collect([$header, ...$sample])
                    ->map(fn($r) => implode("\t", array_map('strval', $r)))
                    ->implode("\n");

                return [
                    'text' => Str::limit($text, 4000, '...'),
                    'rowsAnalysed' => count($sample),
                ];
            } catch (\Throwable $e) {
                return [
                    'text' => 'Error reading spreadsheet: '.$e->getMessage(),
                    'rowsAnalysed' => 0,
                ];
            }
        }

        return [
            'text' => 'Unsupported file type.',
            'rowsAnalysed' => 0,
        ];
    }

    protected function callOpenAiAnalysis(string $plainText): array
    {
        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return ['error' => 'Missing API key.'];
        }

        $prompt = <<<PROMPT
You work in the archive team of a museum or similar organisation. You are given a spreadsheet of data - it is provided as raw content (tab-separated).
NOTE: Only a subset of the data is included here.
NOTE 2: This data is related to collections or archives — so the context is likely to involve entities such as donors, donations, items, and locations. Use this information to help identify the most likely purpose of each column.
If a column appears to be a location (i.e., a place where an item may be found), look at the data content to see if there is a logical structure and suggest what it might represent. Apply this to the other columns found.
---
{$plainText}
---

Tasks:
1) Give a short high-level summary of the content in general terms - be clear that the analysis is based on only a small number of records sampled.
2) List the columns and what each likely represents.
3) Identify any columns that appear to contain dates or numbers, and list only the values that are invalid or ambiguous.
   - A valid date can be in any commonly used format (e.g. dd/mm/yyyy, yyyy-mm-dd, 1-Jul-2019, Excel-style serial numbers like 43633, etc.).
   - If the format is valid but ambiguous (e.g. 03/04/21), assume UK date order (dd/mm/yyyy) unless obviously inconsistent with surrounding data.
   - Do not report valid values as invalid just because the format varies.
   - When listing validation issues, ensure that the data value that appears incorrect is listed under its corresponding column name.
4) From the following list of possible entities, assess whether each is effectively referenced in the data:
   LOCATIONS, DONORS, DONATIONS, ITEMS.
   For each, return:
   - The entity name
   - Whether it is detected in the data (true/false)
   - One or two example values (if applicable)

Respond EXACTLY as JSON:
{
  "summary": "...",
  "columns": [
    { "name": "...", "meaning": "..." }
  ],
  "validation_issues": {
    "Column A": ["invalid date", "not a number"],
    "Column B": ["..."]
  },
  "entities": [
    { "name": "LOCATIONS", "detected": true, "examples": ["Room 1", "Hangar A"] },
    { "name": "DONORS", "detected": false, "examples": [] },
    { "name": "DONATIONS", "detected": true, "examples": ["Loan", "Gift"] },
    { "name": "ITEMS", "detected": true, "examples": ["Radio", "Engine part"] }
  ]
}
PROMPT;

        try {
            $response = \Http::withToken($apiKey)->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-2024-08-06',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a data analysis assistant. Output valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.0,
                'response_format' => ['type' => 'json_object'],
            ]);

//            \Log::info('Raw OpenAI response body: '.$response->body());

            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? '';

            // Clean markdown-style code fences (e.g., ```json ... ```)
            $content = trim($content);
            $content = preg_replace('/^```(?:json)?|```$/m', '', $content);

            $decoded = json_decode($content, true);

            if (!is_array($decoded)) {
                \Log::error('Unable to decode OpenAI content as JSON', ['content' => $content]);
                return ['error' => 'Invalid JSON response from OpenAI.'];
            }

            return $decoded;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function processUploadAndAnalyse(): void
    {


        \Log::info('Starting processUploadAndAnalyse');

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

    protected function toPostgresColumnName(string $input): string
    {
        // 1. Lowercase
        $name = mb_strtolower($input);

        // 2. Replace invalid characters with underscore
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);

        // 3. Collapse multiple underscores
        $name = preg_replace('/_+/', '_', $name);

        // 4. Trim leading/trailing underscores
        $name = trim($name, '_');

        // 5. Ensure it doesn't start with a number
        if (preg_match('/^[0-9]/', $name)) {
            $name = 'col_'.$name;
        }

        // 6. Fallback if empty
        if ($name === '') {
            $name = 'col_unnamed';
        }

        // 7. Truncate to 63 characters (PostgreSQL max identifier length)
        return substr($name, 0, 63);
    }

    protected function normalizeHeader(string $header): string
    {
        $normalized = mb_strtolower($header);
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized); // remove non-alphanum
        return $normalized;
    }
}
