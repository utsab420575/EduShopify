<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Bulk-creates predefined attribute values (the options for
 * select/multi_select/color attributes) from a flat CSV — one value per
 * row, referencing its attribute by name rather than id, so an admin can
 * author the file without knowing internal ids. Attributes must already
 * exist (see AttributeImportService) — this never creates one.
 *
 * Admin only ever supplies the option's name (e.g. Color: Red, Black) —
 * never a color_hex. The exact shade a supplier means by "Red" is a
 * supplier-side detail set elsewhere, not something bulk-imported here, so
 * every value this creates leaves color_hex null.
 *
 * Both preview() and import() share process() — the only difference is
 * whether missing values are actually written to the database ($write).
 * Sort order is generated per attribute (continuing after whatever values
 * that attribute already has), and duplicate value text for the same
 * attribute — case- and slug-insensitive, matching the table's own
 * (attribute_id, slug) uniqueness — is reused rather than duplicated,
 * whether it was already in the database or just appeared earlier in the
 * same file.
 */
class AttributeValueImportService
{
    private const OPTION_INPUT_TYPES = ['select', 'multi_select', 'color'];

    /**
     * Parse an uploaded CSV into normalized rows. Expects a header row with
     * "attribute_name" and "value" columns.
     */
    public function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            throw ValidationException::withMessages(['file' => 'Could not read the uploaded file.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The file is empty.']);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $attributeCol = array_search('attribute_name', $header, true);
        if ($attributeCol === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV must have an "attribute_name" column.']);
        }
        $valueCol = array_search('value', $header, true);
        if ($valueCol === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV must have a "value" column.']);
        }

        $rows = [];
        $lineNumber = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $attributeName = trim((string) ($line[$attributeCol] ?? ''));
            $value = trim((string) ($line[$valueCol] ?? ''));

            if ($attributeName === '' && $value === '') {
                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'attribute_name' => $attributeName,
                'value' => $value,
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            throw ValidationException::withMessages(['file' => 'No usable rows found — every row was empty.']);
        }

        return $rows;
    }

    /**
     * Non-destructive: reports which rows already exist and which would be
     * newly created. Writes nothing to the database.
     */
    public function preview(array $rows): array
    {
        return $this->process($rows, write: false);
    }

    /**
     * Actually creates the missing values (get-or-create per attribute +
     * value).
     */
    public function import(array $rows): array
    {
        return DB::transaction(fn () => $this->process($rows, write: true));
    }

    private function process(array $rows, bool $write): array
    {
        $attributeCache = [];
        $nextSortOrderByAttribute = [];
        $seenValueKeys = [];
        $summary = ['rows' => [], 'created_count' => 0, 'existing_count' => 0, 'error_count' => 0];

        foreach ($rows as $row) {
            $attributeName = $row['attribute_name'];
            $value = $row['value'];

            if ($attributeName === '') {
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'error' => 'attribute_name is required.'];
                $summary['error_count']++;
                continue;
            }

            if ($value === '') {
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'error' => 'value is required.'];
                $summary['error_count']++;
                continue;
            }

            $attributeKey = mb_strtolower($attributeName);
            if (! array_key_exists($attributeKey, $attributeCache)) {
                $attributeCache[$attributeKey] = Attribute::whereRaw('LOWER(name) = ?', [$attributeKey])->first();
            }
            $attribute = $attributeCache[$attributeKey];

            if (! $attribute) {
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'error' => "Attribute '{$attributeName}' not found — import attributes first."];
                $summary['error_count']++;
                continue;
            }

            if (! in_array($attribute->input_type, self::OPTION_INPUT_TYPES, true)) {
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'error' => "'{$attributeName}' is a {$attribute->input_type} attribute — predefined values aren't applicable."];
                $summary['error_count']++;
                continue;
            }

            $valueSlug = Str::slug($value);
            $dedupeKey = $attribute->id . '|' . $valueSlug;

            if (isset($seenValueKeys[$dedupeKey])) {
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'status' => 'existing'];
                $summary['existing_count']++;
                continue;
            }

            $existing = AttributeValue::where('attribute_id', $attribute->id)->where('slug', $valueSlug)->first();
            if ($existing) {
                $seenValueKeys[$dedupeKey] = true;
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'status' => 'existing', 'id' => $existing->id];
                $summary['existing_count']++;
                continue;
            }

            if (! array_key_exists($attribute->id, $nextSortOrderByAttribute)) {
                $nextSortOrderByAttribute[$attribute->id] = ((int) AttributeValue::where('attribute_id', $attribute->id)->max('sort_order')) + 10;
            }

            if ($write) {
                $attributeValue = AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                    'slug' => $valueSlug,
                    'color_hex' => null,
                    'sort_order' => $nextSortOrderByAttribute[$attribute->id],
                    'is_active' => true,
                ]);
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'status' => 'created', 'id' => $attributeValue->id];
                $summary['created_count']++;
            } else {
                $summary['rows'][] = ['line' => $row['line'], 'attribute_name' => $attributeName, 'value' => $value, 'status' => 'would_create'];
                $summary['created_count']++;
            }

            $seenValueKeys[$dedupeKey] = true;
            $nextSortOrderByAttribute[$attribute->id] += 10;
        }

        return $summary;
    }
}
