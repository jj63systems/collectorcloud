<?php

namespace App\Filament\App\Pages;

use App\Filament\Traits\HasNavigationPermission;
use App\Filament\Traits\HasResourcePermissions;
use App\Jobs\ProcessDataLoad;
use App\Models\Tenant\CcDataLoad;
use App\Services\FieldStructureHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    use HasNavigationPermission;

    use HasResourcePermissions;


    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
//    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings

    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'data_load.view';
    }

    // ✅ Appears in sidebar navigation
    protected static ?string $navigationLabel = 'Data load';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Text';         // Singular
    protected static ?string $pluralLabel = 'Texts';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 50;

    // END TITLES AND NAV SETTINGS ----------------------------

    // --- variables ------------------------------
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


    public array $availableSpreadsheetHeaders = []; // e.g. ['Donor Name', 'Donor ID', 'Location']
    public array $structuredFieldMappings = [];     // e.g. ['donors.donor_name' => 'Donor Name']
    public array $unmappedFieldActions = [];        // e.g. ['Condition' => 'legacy', 'Notes' => 'discard']


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

                // STEP 1 — Upload spreadsheet
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
                                    ->options(fn() => collect($this->sheetNames)
                                        ->mapWithKeys(fn($name) => [$name => $name])
                                        ->toArray())
                                    ->required(),
                            ])
                            ->visible(fn() => count($this->sheetNames) > 1)
                            ->extraAttributes([
                                'class' => 'bg-gray-50 rounded-md p-4 border border-gray-200',
                            ]),
                    ])
                    ->afterValidation(function (LivewireComponent $livewire, Get $get) {
                        $livewire->data = [
                            'status' => 'analysing',
                            'analysis_summary' => '',
                            'analysis_columns' => '',
                            'analysis_issues' => '',
                        ];

                        // 👇 HARD-CODE SELECTED ENTITY
                        $livewire->selectedEntities = ['ITEMS'];

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

                // STEP 2 — Review OpenAI analysis
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
                            ->visible(fn() => $this->data['status'] === 'completed'),

                        TextEntry::make('data.analysis_loader')
                            ->state('⏳ Analysing file, please wait...')
                            ->visible(fn() => $this->data['status'] !== 'completed')
                            ->extraAttributes([
                                'class' => 'animate-pulse bg-gray-100 rounded-md p-2 mb-4 text-sm',
                            ])
                            ->html(),
                    ]),

                // STEP 3 — Map known fields
                Step::make('Map known fields')
                    ->schema([
                        ViewField::make('mapping_known')
                            ->view('filament.app.components.step-three-mapping', [
                                'availableSpreadsheetHeaders' => $this->availableSpreadsheetHeaders,
                                'structuredEntities' => $this->getStructuredFieldsForSelectedEntities(),
                            ])
                            ->visible(fn() => filled($this->selectedEntities)),

                    ]),

                // STEP 4 — Assign remaining columns to fxxx or ignore
                Step::make('Assign remaining columns')
                    ->schema([
                        ViewField::make('step_four_fxxx')
                            ->view('filament.app.components.step-four-fxxx-mapping', [
                                'unmappedSpreadsheetHeaders' => $this->getUnmappedSpreadsheetHeaders(),
                                'mappedSpreadsheetHeaders' => $this->getMappedSpreadsheetHeaders(),
                                'mappedSpreadsheetHeadersByEntity' => $this->getConfirmedMappingsByEntity(),
                                'fieldLabels' => $this->getStructuredFieldsForSelectedEntities(),
                            ])
                            ->visible(fn() => filled($this->availableSpreadsheetHeaders)),

                        Textarea::make('formData.notes')
                            ->label('Notes about this data load')
                            ->rows(3)
                            ->placeholder('Enter any comments or context about this upload...')
                            ->columnSpanFull(),
                    ]),
            ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Submit')
                        ->action('submit')
                ),
        ];
    }


    public function submit()
    {
        $teamId = auth()->user()->current_team_id;
        $userId = auth()->id();

        $parsedRows = $this->formData['parsedRows'] ?? [];
        $rowCount = count($parsedRows);

        $this->structuredFieldMappings['fxxx'] = $this->getFxxxFieldAssignments();

        Log::info('DataLoad::submit starting', [
            'user_id' => $userId,
            'team_id' => $teamId,
            'rows' => $rowCount,
            'filename' => $this->formData['upload_filename'] ?? 'Unknown',
        ]);

        // Build payload for insert/update
        $payload = [
            'team_id' => $teamId,
            'user_id' => $userId,
            'filename' => $this->formData['upload_filename'] ?? 'Unknown file',
            'worksheet_name' => $this->formData['sheetName'] ?? null,
            'row_count' => $rowCount,
            'status' => 'queued',
            'uploaded_at' => now(),
            'sample_rows' => json_encode($parsedRows),
            'notes' => $this->formData['notes'] ?? null,
            'confirmed_field_mappings' => json_encode($this->structuredFieldMappings),
        ];

        // Upsert by id if provided
        $dataLoad = CcDataLoad::updateOrCreate(
            ['id' => $this->formData['data_load_id'] ?? null],
            $payload
        );


        Log::info('CcDataLoad upserted', [
            'data_load_id' => $dataLoad->id,
            'row_count' => $dataLoad->row_count,
            'status' => $dataLoad->status,
        ]);

        $dataLoad->update([
            'confirmed_field_mappings' => json_encode($this->structuredFieldMappings),
            'sample_rows' => json_encode($this->formData['parsedRows'] ?? []),
        ]);

        Notification::make()
            ->title('Data load queued for processing - please be patient')
            ->info()
            ->send();
        // Dispatch background job
        ProcessDataLoad::dispatch($dataLoad->id);

        Log::info('ProcessDataLoad job dispatched', [
            'job' => ProcessDataLoad::class,
            'data_load_id' => $dataLoad->id,
        ]);


        return redirect()->to(
            route('filament.app.pages.data-load-status').'?record='.$dataLoad->id
        );
    }

    public function runAnalysis(): void
    {
        $set = fn(string $key, $value) => $this->data[str_replace('data.', '', $key)] = $value;

        if (!$this->storedAttachmentPath) {
            $set('data.analysis_summary', 'Error: No uploaded file to process.');
            $this->data['status'] = 'completed';
            return;
        }

        $result = $this->extractTextFromFile($this->storedAttachmentPath);
        $plainText = $result['text'] ?? '';
        $this->rowsAnalysed = $result['rowsAnalysed'] ?? 0;

        $analysis = $this->callOpenAiAnalysis($plainText);

        if (isset($analysis['error'])) {
            \Log::info('OpenAI analysis error', ['error' => $analysis['error']]);
            $set('data.analysis_summary', 'OpenAI Error: '.$analysis['error']);
            $this->data['status'] = 'completed';
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

        $this->data['status'] = 'completed';
        $this->availableSpreadsheetHeaders = array_values($this->headerMap ?? []);

        // 👇 Add this to populate unmappedFieldActions
        $this->unmappedFieldActions = collect($this->getUnmappedSpreadsheetHeaders())
            ->mapWithKeys(fn($header) => [$header => 'legacy'])
            ->toArray();

        \Log::info('Analysis complete', ['rowsAnalysed' => $this->rowsAnalysed]);
    }

    protected function extractTextFromFile(string $path): array
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $fullPath = Storage::disk('local')->path($path);

        // --- CSV HANDLING --------------------------------------------------
        if ($ext === 'csv') {
            $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $headerLine = array_shift($lines);

            $header = array_map('trim', str_getcsv($headerLine));

            // ❗ Ignore blank headers
            $validHeaderIndexes = collect($header)
                ->map(fn($h, $i) => strlen(trim($h)) > 0 ? $i : null)
                ->filter(fn($i) => !is_null($i))
                ->values()
                ->all();

            $header = array_values(array_filter($header, fn($h) => strlen(trim($h)) > 0));

            $rows = collect($lines)
                ->map(fn($line) => str_getcsv($line))
                ->map(fn($row) => array_intersect_key($row, array_flip($validHeaderIndexes))) // keep only valid columns
                ->map(fn($row) => array_combine($header, $row))
                ->filter(fn($r) => array_filter($r)) // skip blank rows
                ->values()
                ->all();

            $this->formData['parsedRows'] = $rows;

            $this->headerMap = collect($header)
                ->mapWithKeys(fn($h) => [$this->normalizeHeader((string) $h) => (string) $h])
                ->toArray();

            $sample = array_slice($rows, 0, 50);
            $text = collect([$header, ...$sample])
                ->map(fn($r) => implode("\t", array_map('strval', $r)))
                ->implode("\n");

            return [
                'text' => Str::limit($text, 4000, '...'),
                'rowsAnalysed' => count($rows),
            ];
        }

        // --- XLS / XLSX HANDLING ------------------------------------------
        if (in_array($ext, ['xls', 'xlsx'])) {
            try {
                $reader = IOFactory::createReaderForFile($fullPath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($fullPath);

                $sheetName = $this->formData['sheetName'] ?? $spreadsheet->getSheetNames()[0];
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray();

                $header = array_shift($rows);
                $header = array_map('trim', $header);

                // ❗ Filter out empty header columns
                $validHeaderIndexes = collect($header)
                    ->map(fn($h, $i) => strlen(trim($h)) > 0 ? $i : null)
                    ->filter(fn($i) => !is_null($i))
                    ->values()
                    ->all();

                $header = array_values(array_filter($header, fn($h) => strlen(trim($h)) > 0));

                $parsedRows = collect($rows)
                    ->map(fn($row) => array_intersect_key($row, array_flip($validHeaderIndexes)))
                    ->map(fn($row) => array_combine($header, $row))
                    ->filter(fn($r) => array_filter($r)) // skip blank rows
                    ->values()
                    ->all();

                $this->formData['parsedRows'] = $parsedRows;
                $this->headerMap = collect($header)
                    ->mapWithKeys(fn($h) => [$this->normalizeHeader((string) $h) => (string) $h])
                    ->toArray();

                $sample = array_slice($parsedRows, 0, 50);
                $text = collect([$header, ...$sample])
                    ->map(fn($r) => implode("\t", array_map('strval', $r)))
                    ->implode("\n");

                Log::info('Parsed full rows', [
                    'total' => count($parsedRows),
                    'blankSkipped' => count($rows) - count($parsedRows),
                    'ignoredEmptyHeaders' => count(array_diff_key($rows[0] ?? [], array_flip($validHeaderIndexes))),
                    'fromFile' => $ext,
                ]);

                return [
                    'text' => Str::limit($text, 4000, '...'),
                    'rowsAnalysed' => count($parsedRows),
                ];
            } catch (\Throwable $e) {
                return [
                    'text' => 'Error reading spreadsheet: '.$e->getMessage(),
                    'rowsAnalysed' => 0,
                ];
            }
        }

        // --- DEFAULT -------------------------------------------------------
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
You are a data analysis assistant working with archival item records. You are given a partial extract from a spreadsheet as tab-separated raw text.

NOTE:
- Only a sample of the uploaded data is shown.
- Your task is to help interpret the likely meaning of each column.

---
{$plainText}
---

Tasks:
1) Write a short high-level summary describing the type of data you see. Be clear this is based on a partial sample.
2) List each column found and explain what it likely represents.

Respond EXACTLY as JSON:
{
  "summary": "...",
  "columns": [
    { "name": "...", "meaning": "..." }
  ],
  "validation_issues": {
  }
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

            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? '';

            // Remove Markdown-style ```json code fences
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

        $this->formData['upload_filename'] = $upload->getClientOriginalName();

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
                $this->sheetNames = array_filter($spreadsheet->getSheetNames(), fn($n) => !empty(trim($n)));

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

    protected function getStructuredFieldsForSelectedEntities(): array
    {
        $all = FieldStructureHelper::getStructuredFieldsByEntity();

        return collect($this->selectedEntities)
            ->filter(fn($entity) => isset($all[$entity]))
            ->mapWithKeys(fn($entity) => [$entity => $all[$entity]])
            ->toArray();
    }


    protected function getUnmappedSpreadsheetHeaders(): array
    {
        $allHeaders = $this->availableSpreadsheetHeaders ?? [];

        // Flatten all used values from structured mappings (e.g. 'Title', 'Description', etc.)
        $usedHeaders = collect($this->structuredFieldMappings ?? [])
            ->flatMap(fn($mappings) => array_values($mappings))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return array_values(array_filter(
            $allHeaders,
            fn($header) => !in_array($header, $usedHeaders, true)
        ));
    }

    protected function getMappedSpreadsheetHeaders(): array
    {
        return array_values(array_filter($this->structuredFieldMappings ?? []));
    }


    protected function getConfirmedMappingsByEntity(): array
        //
        // THIS ONE NEARLY WORKS
        //
    {

        Log::info('selected entities', ['selectedEntities' => $this->selectedEntities]);
        $structuredFields = FieldStructureHelper::getStructuredFieldsByEntity();

        Log::info('Structured fields by entity', $structuredFields);

        // Flatten the structure: 'cc_items.name' => 'Item Name'
        $flatLabels = collect($structuredFields)
            ->flatMap(fn($fields) => collect($fields)->mapWithKeys(
                fn($data, $fieldKey) => [$fieldKey => $data['label'] ?? '[Unknown field]']
            ))
            ->toArray();

        $allowedKeys = array_keys($flatLabels);
        Log::info('Allowed keys', ['allowedKeys' => $allowedKeys]);

        $selectedEntityPrefixes = collect($this->selectedEntities)
            ->map(fn($entity) => match ($entity) {
                'ITEMS' => 'cc_items',
                'DONORS' => 'cc_donors',
                'LOCATIONS' => 'cc_locations',
                'DONATIONS' => 'cc_donations',
                default => null,
            })
            ->filter()
            ->values()
            ->all();

        $flatMappings = collect($this->structuredFieldMappings ?? [])
            ->only($selectedEntityPrefixes)
            ->flatMap(fn($fields, $entity) => collect($fields)->mapWithKeys(
                fn($header, $field) => ["{$entity}.{$field}" => $header]
            ))
            ->toArray();

        Log::info('Flat mappings', $flatMappings);

        // Filter to only allowed keys and convert to grouped structure
        $grouped = collect($flatMappings)
            ->filter(fn($_header, $fieldKey) => in_array($fieldKey, $allowedKeys, true))
            ->map(function ($header, $fieldKey) use ($flatLabels) {
                return [
                    'header' => $header,
                    'fieldLabel' => $flatLabels[$fieldKey] ?? '[Unknown field]',
                ];
            })
            ->groupBy(fn($_row, $fieldKey) => explode('.', $fieldKey)[0])
            ->toArray();

//        Log::info('Final mapped columns by entity', ['grouped' => $grouped]);

        return $grouped;
    }

    protected function getFxxxFieldAssignments(): array
    {
        // Deduplicate and sort unmapped spreadsheet headers
        $headers = collect($this->unmappedFieldActions ?? [])
            ->keys()
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Map deterministically to f001, f002, ...
        return collect($headers)
            ->mapWithKeys(function ($header, $index) {
                $fieldName = sprintf('f%03d', $index + 1);
                return [$header => $fieldName];
            })
            ->toArray();
    }
}
