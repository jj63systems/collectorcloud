<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Illuminate\Support\Str;

class SpreadsheetPreprocessor
{
    public function extractData(string $path): array
    {
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        $rows = [];

        if ($extension === 'csv') {
            $reader = IOFactory::createReader('Csv');
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            $reader = IOFactory::createReaderForFile($path);
        } else {
            throw new \RuntimeException('Unsupported file format: '.$extension);
        }

        $spreadsheet = $reader->load($path);
        $sheetNames = $spreadsheet->getSheetNames();
        $sheet = $spreadsheet->getSheet(0);
        $worksheetName = $sheet->getTitle();

        $rows = $sheet->toArray(null, true, true, true);

        return [
            'worksheet' => $worksheetName,
            'sheet_count' => count($sheetNames),
            'columns' => array_keys($rows[0] ?? []),
            'rows' => array_slice($rows, 0, 10), // first 10 rows
        ];
    }
}
