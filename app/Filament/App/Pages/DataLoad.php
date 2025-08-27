<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Data Load';
    protected string $view = 'filament.app.pages.data-load';

    public $data = [];
    public $formData = [];
    public $attachment = [];
    protected $storedAttachmentPath = null;

    public $isAnalysing = false;

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
                            ->required(),
                    ])
                    ->afterValidation(function (LivewireComponent $livewire, Get $get) {
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
                        TextEntry::make('data.analysis_summary')
                            ->label('What the data appears to represent')
                            ->state(fn(
                            ) => $this->data['status'] === 'complete' ? $this->data['analysis_summary'] : 'Analysing...')
                            ->html(),

                        TextEntry::make('data.analysis_columns')
                            ->label('Columns and their likely meanings')
                            ->state(fn(
                            ) => $this->data['status'] === 'complete' ? nl2br(e($this->data['analysis_columns'])) : '')
                            ->html(),

                        TextEntry::make('data.analysis_issues')
                            ->label('Validation issues')
                            ->state(fn(
                            ) => $this->data['status'] === 'complete' ? nl2br(e($this->data['analysis_issues'])) : '')
                            ->html(),
                    ]),
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
            return file_get_contents($fullPath);
        }

        if (in_array($ext, ['xls', 'xlsx'])) {
            try {
                $spreadsheet = IOFactory::createReaderForFile($fullPath)->load($fullPath);
                $sheet = $spreadsheet->getSheet(0);
                $rows = $sheet->toArray();
                $header = array_shift($rows);
                return collect([$header, ...array_slice($rows, 0, 50)])
                    ->map(fn($r) => implode("\t", array_map('strval', $r)))
                    ->implode("\n");
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
NOTE: Only the first 2000 characters are included:

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
}
