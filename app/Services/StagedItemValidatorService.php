<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StagedItemValidatorService
{
    protected Collection $fieldMappings;
    protected array $coreFieldTypes;


    public function __construct(Collection $fieldMappings, array $coreFieldTypes = [])
    {
        $this->fieldMappings = $fieldMappings->keyBy('field_name');
        $this->coreFieldTypes = $coreFieldTypes; // e.g. from getStructuredFieldsByEntity()['ITEMS']
    }

    public function validateCollection(Collection $rows): array
    {
        $valid = [];
        $invalid = [];
        $i = 0;
        foreach ($rows as $row) {
            $errors = [];

            foreach ($row->toArray() as $field => $value) {
                if (is_null($value) || $value === '') {
                    continue;
                }

                // Prefer fxxx mapping
                $mapping = $this->fieldMappings[$field] ?? null;
                $type = null;

                if ($mapping) {
                    $type = strtoupper($mapping->data_type);
                } elseif (isset($this->coreFieldTypes[$field])) {
                    $type = strtoupper($this->coreFieldTypes[$field]['type']);
                }

                if (!$type) {
//                    Log::debug("Skipping unknown field [$field] in validation.");
                    $i = $i + 1;
                    continue;
                }

//                Log::info("Validating field [$field] with value [$value] as [$type]");
                switch ($type) {
                    case 'DATE':
                        if (!$this->isValidDate($value)) {
                            $errors[$field] = 'Invalid date ('.$value.')';
                        }
                        break;

                    case 'NUMBER':
                        if (!is_numeric($value)) {
                            $errors[$field] = 'Not a number ('.$value.')';
                        }
                        break;

                    case 'BOOLEAN':
                        if (!$this->isBooleanLike($value)) {
                            $errors[$field] = 'Not a valid boolean ('.$value.')';
                        }
                        break;

                    case 'TEXT':
                    case 'STRING':
                    case 'LOOKUP':
                    case 'FOREIGN':
                    default:
                        // Skip — handled elsewhere or no validation needed
                        break;
                }
            }

            if (empty($errors)) {
                $valid[] = $row->toArray();
            } else {
                $invalid[] = [
                    'id' => $row->id,
                    'errors' => $errors,
                ];
            }
        }


        Log::info('Validation complete - i='.$i);
        return compact('valid', 'invalid');
    }

    protected function isValidDate($value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        return $dt && $dt->format('Y-m-d') === $value;
    }

    protected function isBooleanLike($value): bool
    {
        return in_array(strtolower(trim($value)), ['yes', 'no', 'true', 'false', '1', '0'], true);
    }
}
