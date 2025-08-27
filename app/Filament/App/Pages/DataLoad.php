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
    public array $attachment = []; // ✅ Livewire expects array here
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
                            ->moveFiles()
                            ->afterStateUpdated(fn($state, $set) => $this->handleAttachmentUpload($state, $set)),
                    ]),
                Step::make('Review')
                    ->schema([
                        Textarea::make('data.analysis_summary')
                            ->label('What the data appears to represent')
                            ->disabled(),
                        Textarea::make('data.analysis_columns')
                            ->label('Columns and their likely meanings')
                            ->disabled(),
                        Textarea::make('data.analysis_issues')
                            ->label('Validation issues')
                            ->disabled(),
                    ]),
            ])->submitAction(
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

    private function handleAttachmentUpload(mixed $state, callable $set): void
    {
        // Log that the method was entered (debugging aid).
        \Log::info('Entered handleAttachmentUpload');

        // Ensure the uploaded "state" is an instance of TemporaryUploadedFile (what FileUpload provides).
        if (!$state instanceof TemporaryUploadedFile) {
            \Log::warning('afterStateUpdated: State is not TemporaryUploadedFile', [
                'actual' => is_object($state) ? get_class($state) : gettype($state),
            ]);
            return; // Exit early if it’s not a valid file upload.
        }

        // Log the original filename of the uploaded file.
        \Log::info('afterStateUpdated: New file uploaded', [
            'filename' => $state->getClientOriginalName(),
        ]);

        // Determine the final filename and storage path under "formattachments".
        $finalFilename = $state->getClientOriginalName();
        $finalPath = 'formattachments/'.$finalFilename;

        // If a file already exists at this path, delete it to avoid duplicates/conflicts.
        if (Storage::disk('local')->exists($finalPath)) {
            \Log::info('Deleting existing file at final path');
            Storage::disk('local')->delete($finalPath);
        }

        // Store the uploaded file at the final path using the original filename.
        $storedPath = $state->storeAs('formattachments', $finalFilename);
        $this->storedAttachmentPath = $storedPath; // Keep track of stored file path.

        \Log::info('start text extraction');

        // Extract text from the uploaded file (e.g., from CSV/XLSX/PDF etc.).
        $text = $this->extractTextFromFile($storedPath);
        \Log::info('end text extraction');

        // Send the extracted text to OpenAI for analysis.
        $analysis = $this->callOpenAiAnalysis($text);

        // If OpenAI returned an error, log it and set default error messages into form state.
        if (isset($analysis['error'])) {
            \Log::error('OpenAI returned error', ['error' => $analysis['error']]);

            // Update form fields with error details.
            $set('data.analysis_summary', 'OpenAI Error: '.$analysis['error']);
            $set('data.analysis_columns', null);
            $set('data.analysis_issues', null);

        } else {
            // Analysis succeeded — log success.
            \Log::info('OpenAI analysis successful');

            // Update form fields with structured analysis output.
            $set('data.analysis_summary', $analysis['summary'] ?? 'No summary.');

            // Convert list of columns into "name: meaning" strings separated by newlines.
            $set('data.analysis_columns', collect($analysis['columns'] ?? [])
                ->map(fn($col) => "{$col['name']}: {$col['meaning']}")
                ->implode("\n"));

            // Convert validation issues into "column: issue1, issue2" format separated by newlines.
            $set('data.analysis_issues', collect($analysis['validation_issues'] ?? [])
                ->map(fn($problems, $col) => "$col: ".implode(', ', $problems))
                ->implode("\n"));
        }
    }

    protected function extractTextFromFile(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $fullPath = Storage::disk('local')->path($path);

        \Log::info('Extracting text from file', [
            'path' => $path,
            'fullPath' => $fullPath,
            'extension' => $extension,
        ]);

        if ($extension === 'csv') {
            \Log::info('File is CSV — returning raw contents');
            return file_get_contents($fullPath);
        }

        if (in_array($extension, ['xls', 'xlsx'])) {
            \Log::info('File is XLS/XLSX — attempting to load spreadsheet');

            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
                $spreadsheet = $reader->load($fullPath);

                foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
                    $sheetName = $sheet->getTitle();
                    \Log::info("Checking worksheet", ['index' => $index, 'title' => $sheetName]);

                    $rows = $sheet->toArray();
                    $rowCount = count($rows);
                    $header = $rows[0] ?? [];

                    \Log::info("Row count: {$rowCount}");
                    \Log::info("Header row", ['header' => $header]);

                    $nonEmptyHeaderCells = array_filter($header, fn($cell) => trim((string) $cell) !== '');
                    $nonEmptyCount = count($nonEmptyHeaderCells);

                    \Log::info("Non-empty header cells: {$nonEmptyCount}");

                    if ($nonEmptyCount >= 2 && $rowCount >= 2) {
                        \Log::info("Tabular worksheet detected — extracting", ['sheet' => $sheetName]);

                        $header = array_shift($rows);
                        $previewRows = array_slice($rows, 0, 50);
                        $allRows = array_merge([$header], $previewRows);

                        $text = collect($allRows)->map(function ($row) {
                            return implode("\t", array_map(fn($cell) => (string) $cell, $row));
                        })->implode("\n");

                        \Log::info("Extracted text sample prepared", [
                            'row_count' => count($allRows),
                            'char_count' => strlen($text),
                        ]);

                        return $text;
                    } else {
                        \Log::info("Skipping non-tabular worksheet", ['sheet' => $sheetName]);
                    }
                }

                \Log::info("No suitable tabular worksheet found");
                return 'No tabular worksheet found.';
            } catch (\Exception $e) {
                \Log::error('Error reading spreadsheet', ['exception' => $e->getMessage()]);
                return 'Error reading spreadsheet: '.$e->getMessage();
            }
        }

        \Log::warning('Unsupported file type');
        return 'Unsupported file type.';
    }


    /**
     * Send (truncated) plain text to OpenAI and return a parsed JSON array.
     * - Forces JSON output via response_format when supported.
     * - Falls back to stripping code fences or extracting the first JSON object.
     */
    protected function callOpenAiAnalysis(string $plainText): array
    {
        // Log the true size and a safe excerpt
        \Log::info('PlainText content + length', [
            'length_bytes' => strlen($plainText),
            'excerpt' => substr($plainText, 0, 1000),
        ]);

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            return ['error' => 'OpenAI API key is not set in .env (OPENAI_API_KEY)'];
        }

        // Limit payload sent to the model
        $truncatedPlainText = substr($plainText, 0, 2000);

        $prompt = <<<PROMPT
You are an expert in interpreting data tables.

The following is the raw content of a spreadsheet (tab-separated).
NOTE: Only the first 2000 characters are included:

---
{$truncatedPlainText}
---

Tasks:
1) Give a short high-level summary of the entities represented.
2) List the columns and what each likely represents.
3) Identify columns that look like dates or numbers, and list any invalid values.

Respond EXACTLY as JSON with this schema (no commentary, no code fences):

{
  "summary": "High-level summary of the data",
  "columns": [
    {"name": "Column A", "meaning": "Likely a date of donation"},
    {"name": "Column B", "meaning": "Donor name"}
  ],
  "validation_issues": {
    "Column A": ["13/32/2022", "not-a-date"],
    "Column C": ["abc", "£??"]
  }
}
PROMPT;

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-2024-08-06', // pin the dated model you saw in the response
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a data analysis assistant. Output valid JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.0,
                    // Enforce JSON output (models that support JSON mode will comply)
                    'response_format' => ['type' => 'json_object'],
                ]);

            // HTTP-level errors
            if ($response->failed()) {
                return ['error' => 'OpenAI HTTP error: '.$response->status(), 'details' => $response->json()];
            }

            $json = $response->json();

            // API-level errors
            if (isset($json['error'])) {
                return ['error' => $json['error']['message'] ?? 'Unknown API error', 'details' => $json['error']];
            }

            $text = $json['choices'][0]['message']['content'] ?? null;
            if (!$text) {
                return ['error' => 'No response from OpenAI.'];
            }

            // Try direct decode first (should succeed when response_format works)
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            // Fallback 1: strip ```json ... ``` or ``` ... ```
            $stripped = preg_replace('/^```json\s*|^```\s*|```$/m', '', $text);
            $decoded = json_decode($stripped, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            // Fallback 2: extract the first JSON object heuristically
            if (preg_match('/\{.*\}/s', $text, $m)) {
                $candidate = $m[0];
                $decoded = json_decode($candidate, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            // If we got here, parsing failed—log a small excerpt for diagnosis
            \Log::warning('Failed to parse OpenAI response text', [
                'excerpt' => substr($text, 0, 500),
            ]);

            return ['error' => 'Failed to parse OpenAI response.'];

        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
