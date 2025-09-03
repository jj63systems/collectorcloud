<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FieldMappingService
{
    public function suggestFieldMappingsFromAI(
        array $structuredFields,
        array $spreadsheetHeaders,
        array $sampleRows = []
    ): array {
        $internalFieldDescriptions = [];

        foreach ($structuredFields as $entity => $fields) {
            foreach ($fields as $key => $meta) {
                $internalFieldDescriptions[$key] = [
                    'label' => $meta['label'],
                    'description' => $meta['description'] ?? '',
                    'entity' => $entity,
                ];
            }
        }

        $prompt = [
            [
                "role" => "system",
                "content" => "You are a data analyst who helps map spreadsheet columns to known system fields."
            ],
            [
                "role" => "user",
                "content" => $this->buildPrompt($internalFieldDescriptions, $spreadsheetHeaders, $sampleRows)
            ],
        ];

        $response = Http::withToken(config('services.openai.api_key'))
            ->baseUrl('https://api.openai.com/v1')
            ->post('/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => $prompt,
                'temperature' => 0.3,
            ]);

        $content = $response->json('choices.0.message.content');

        try {
            return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('Failed to decode suggested field mappings from OpenAI', [
                'raw' => $content,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    protected function buildPrompt(array $internalFields, array $headers, array $rows): string
    {
        $fieldDescriptions = collect($internalFields)->map(function ($meta, $key) {
            return "$key: {$meta['label']} – {$meta['description']} (Entity: {$meta['entity']})";
        })->implode("\n");

        // Normalize headers if associative
        if (array_is_list($headers)) {
            $headerLines = implode("\n- ", $headers);
        } else {
            $headerLines = implode("\n", array_map(fn($k, $v) => "{$k} ({$v})", array_keys($headers), $headers));
        }

        $prompt = <<<EOT
We are importing spreadsheet data into a system with the following known internal fields:

{$fieldDescriptions}

Here are the spreadsheet column headers:
- {$headerLines}
EOT;

        if (!empty($rows)) {
            $prompt .= "\n\nExample rows:\n";
            foreach ($rows as $i => $row) {
                $prompt .= "Row ".($i + 1).": [".implode(", ", $row)."]\n";
            }
        }

        $prompt .= <<<EOT

Please return a JSON object where the keys are spreadsheet column headers and the values are the best-matching internal field keys (e.g., 'cc_donors.name').
Return only the JSON object. If unsure, return null for that header.
EOT;

        return $prompt;
    }
}
