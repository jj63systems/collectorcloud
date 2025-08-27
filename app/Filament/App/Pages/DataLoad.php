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
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DataLoad extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Data Load';
    protected string $view = 'filament.app.pages.data-load';

    public array $data = [];
    public array $formData = [];
    public array $attachment = [];
    protected ?string $storedAttachmentPath = null;

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
                            ->multiple(false)
                            ->required(),
                    ])
                    ->afterValidation(function (LivewireComponent $livewire, Get $get) {
                        $state = $get('attachment');
                        $livewire->processUploadOnNext($state);
                    }),

                Step::make('Review')
                    ->schema([
                        Textarea::make('data.analysis_summary')->label('What the data appears to represent')->disabled(),
                        Textarea::make('data.analysis_columns')->label('Columns and their likely meanings')->disabled(),
                        Textarea::make('data.analysis_issues')->label('Validation issues')->disabled(),
                    ]),
            ])->submitAction(
                Action::make('submit')->label('Submit')->action('submit')
            ),
        ];
    }

    public function submit(): void
    {
        Notification::make()->title('Form submitted!')->success()->send();
    }

    public function processUploadOnNext(mixed $state): void
    {
        $set = fn(string $key, $value) => str_starts_with($key, 'data.') ? $this->data[substr($key, 5)] = $value : null;

        if (is_array($state) && count($state) === 1) {
            $firstKey = array_key_first($state);
            $firstVal = $state[$firstKey];

            if (is_string($firstKey)) {
                $state = $firstKey;
            } elseif (is_string($firstVal)) {
                $state = $firstVal;
            } elseif (is_array($firstVal) && isset($firstVal['id'])) {
                $state = $firstVal['id'];
            } elseif (is_array($firstVal) && isset($firstVal['path'])) {
                $state = $firstVal['path'];
            }
        }

        \Log::info('processUploadOnNext: normalized candidate', [
            'type' => gettype($state),
            'preview' => is_string($state) ? substr($state, 0, 120) : null,
        ]);

        $normalized = $this->normalizeUploadState($state);
        $this->handleAttachmentUpload($normalized, $set);
    }

    protected function normalizeUploadState(mixed $state): mixed
    {
        if ($state instanceof TemporaryUploadedFile) {
            return $state;
        }

        if (is_string($state)) {
            try {
                $tmp = TemporaryUploadedFile::createFromLivewire($state);
                if (file_exists($tmp->getRealPath())) {
                    return $tmp;
                }
                \Log::warning('normalizeUploadState: token resolved to non-existent temp file',
                    ['token' => $state, 'path' => $tmp->getRealPath()]);
            } catch (\Throwable) {
                return $state;
            }
        }

        if (is_array($state)) {
            if (isset($state['id'])) {
                return $this->normalizeUploadState($state['id']);
            }
            if (isset($state['path'])) {
                return $state['path'];
            }
            if (isset($state[0])) {
                return $this->normalizeUploadState($state[0]);
            }
        }

        return $state;
    }

    private function handleAttachmentUpload(mixed $state, callable $set): void
    {
        \Log::info('Entered handleAttachmentUpload', ['type' => gettype($state)]);

        $state = $this->normalizeUploadState($state);

        if (!$state instanceof TemporaryUploadedFile && !is_string($state)) {
            \Log::warning('State is neither TemporaryUploadedFile nor path string', ['actual' => gettype($state)]);
            $set('data.analysis_summary', 'Upload error: unexpected file state.');
            return;
        }

        $finalFilename = $state instanceof TemporaryUploadedFile ? $state->getClientOriginalName() : basename($state);
        $finalPath = 'formattachments/'.$finalFilename;

        if ($state instanceof TemporaryUploadedFile) {
            Storage::disk('local')->delete($finalPath);
            $storedPath = $state->storeAs('formattachments', $finalFilename);
        } else {
            $storedPath = $state;
        }

        $this->storedAttachmentPath = $storedPath;

        \Log::info('start text extraction');
        $text = $this->extractTextFromFile($storedPath);
        \Log::info('end text extraction');

        $analysis = $this->callOpenAiAnalysis($text);

        if (isset($analysis['error'])) {
            $set('data.analysis_summary', 'OpenAI Error: '.$analysis['error']);
            return;
        }

        \Log::info('OpenAI analysis successful');

        $set('data.analysis_summary', $analysis['summary'] ?? 'No summary.');
        $set('data.analysis_columns',
            collect($analysis['columns'] ?? [])->map(fn($c) => "$c[name]: $c[meaning]")->implode("\n"));
        $set('data.analysis_issues',
            collect($analysis['validation_issues'] ?? [])->map(fn($v, $k) => "$k: ".implode(', ', $v))->implode("\n"));
    }

    protected function extractTextFromFile(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $fullPath = Storage::disk('local')->path($path);
        \Log::info('Extracting text from file', ['path' => $path, 'fullPath' => $fullPath, 'extension' => $ext]);

        if ($ext === 'csv') {
            return file_get_contents($fullPath);
        }

        if (in_array($ext, ['xls', 'xlsx'])) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
                $spreadsheet = $reader->load($fullPath);
                foreach ($spreadsheet->getAllSheets() as $sheet) {
                    $rows = $sheet->toArray();
                    if (count($rows) < 2 || count(array_filter($rows[0])) < 2) {
                        continue;
                    }
                    $header = array_shift($rows);
                    $text = collect(array_merge([$header], array_slice($rows, 0, 50)))->map(fn($r) => implode("\t",
                        array_map('strval', $r)))->implode("\n");
                    return $text;
                }
            } catch (\Throwable $e) {
                return 'Error reading spreadsheet: '.$e->getMessage();
            }
        }

        return 'Unsupported file type.';
    }

    protected function callOpenAiAnalysis(string $plainText): array
    {
        \Log::info('PlainText content + length', [
            'length_bytes' => strlen($plainText),
            'excerpt' => substr($plainText, 0, 1000),
        ]);

        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return ['error' => 'Missing API key.'];
        }

        $truncated = substr($plainText, 0, 2000);
        $prompt = <<<PROMPT
You are an expert in interpreting data tables.

The following is the raw content of a spreadsheet (tab-separated).
NOTE: Only the first 2000 characters are included:

---
{$truncated}
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
            if (isset($json['error'])) {
                return ['error' => $json['error']['message'] ?? 'Unknown error'];
            }

            $text = $json['choices'][0]['message']['content'] ?? '';
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            $fallback = preg_replace('/^```(?:json)?|```$/m', '', $text);
            $decoded = json_decode($fallback, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            if (preg_match('/\{.*?\}/s', $text, $m)) {
                $decoded = json_decode($m[0], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            return ['error' => 'Unable to parse response'];

        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
