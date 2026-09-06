<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\InputType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Bulk-creates attributes from a flat CSV (one attribute per row) — the
 * same shape as AttributeGroupImportService. Predefined values for
 * select/multi_select/color attributes are NOT part of this file; they're
 * imported separately by AttributeValueImportService, referencing the
 * attribute by name, so an admin runs this import first and the values
 * import second.
 *
 * Both preview() and import() share process() — the only difference is
 * whether missing attributes are actually written to the database
 * ($write). A $seenNames set tracks names already resolved earlier in the
 * same batch so a file that lists the same attribute twice doesn't try to
 * create it twice.
 */
class AttributeImportService
{
    private const VALID_INPUT_TYPES = ['text', 'textarea', 'number', 'select', 'multi_select', 'boolean', 'date', 'color'];

    /**
     * Parse an uploaded CSV into normalized rows. Expects a header row with
     * "name" and "input_type" columns; "attribute_group", "is_required",
     * "is_filterable" and "is_variant" columns are optional.
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
        $nameCol = array_search('name', $header, true);
        if ($nameCol === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV must have a "name" column.']);
        }
        $inputTypeCol = array_search('input_type', $header, true);
        if ($inputTypeCol === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV must have an "input_type" column.']);
        }
        $groupCol = array_search('attribute_group', $header, true);
        $requiredCol = array_search('is_required', $header, true);
        $filterableCol = array_search('is_filterable', $header, true);
        $variantCol = array_search('is_variant', $header, true);

        $rows = [];
        $lineNumber = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $name = trim((string) ($line[$nameCol] ?? ''));
            $inputType = strtolower(trim((string) ($line[$inputTypeCol] ?? '')));
            $group = $groupCol !== false ? trim((string) ($line[$groupCol] ?? '')) : '';

            if ($name === '' && $inputType === '' && $group === '') {
                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'name' => $name,
                'input_type' => $inputType,
                'attribute_group' => $group !== '' ? $group : null,
                'is_required' => $this->parseBool($requiredCol !== false ? ($line[$requiredCol] ?? '') : ''),
                'is_filterable' => $this->parseBool($filterableCol !== false ? ($line[$filterableCol] ?? '') : ''),
                'is_variant' => $this->parseBool($variantCol !== false ? ($line[$variantCol] ?? '') : ''),
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
     * Actually creates the missing attributes (get-or-create per name).
     */
    public function import(array $rows): array
    {
        return DB::transaction(fn () => $this->process($rows, write: true));
    }

    private function process(array $rows, bool $write): array
    {
        $seenNames = [];
        $groupCache = [];
        $inputTypeIds = InputType::pluck('id', 'code');
        $nextSortOrder = ((int) Attribute::max('sort_order')) + 10;
        $summary = ['rows' => [], 'created_count' => 0, 'existing_count' => 0, 'error_count' => 0];

        foreach ($rows as $row) {
            $name = $row['name'];

            if ($name === '') {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'error' => 'Name is required.'];
                $summary['error_count']++;
                continue;
            }

            if (mb_strlen($name) > 150) {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'error' => 'Name is longer than 150 characters.'];
                $summary['error_count']++;
                continue;
            }

            if ($row['input_type'] === '') {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'error' => 'input_type is required.'];
                $summary['error_count']++;
                continue;
            }

            if (! in_array($row['input_type'], self::VALID_INPUT_TYPES, true)) {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'error' => "Unknown input_type '{$row['input_type']}' — must be one of: " . implode(', ', self::VALID_INPUT_TYPES) . '.'];
                $summary['error_count']++;
                continue;
            }

            $groupId = null;
            if ($row['attribute_group']) {
                $groupKey = mb_strtolower($row['attribute_group']);
                if (! array_key_exists($groupKey, $groupCache)) {
                    $groupCache[$groupKey] = AttributeGroup::whereRaw('LOWER(name) = ?', [$groupKey])->value('id');
                }
                $groupId = $groupCache[$groupKey];

                if (! $groupId) {
                    $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'error' => "Attribute group '{$row['attribute_group']}' not found — create it first on the Attribute Groups tab."];
                    $summary['error_count']++;
                    continue;
                }
            }

            $key = mb_strtolower($name);

            if (isset($seenNames[$key])) {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'existing'];
                $summary['existing_count']++;
                continue;
            }

            $existing = Attribute::whereRaw('LOWER(name) = ?', [$key])->first();
            if ($existing) {
                $seenNames[$key] = true;
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'existing', 'id' => $existing->id];
                $summary['existing_count']++;
                continue;
            }

            if ($write) {
                $attribute = Attribute::create([
                    'attribute_group_id' => $groupId,
                    'name' => $name,
                    'slug' => $this->generateUniqueSlug($name),
                    'input_type' => $row['input_type'],
                    'input_type_id' => $inputTypeIds[$row['input_type']] ?? null,
                    'is_required' => $row['is_required'],
                    'is_filterable' => $row['is_filterable'],
                    'is_variant' => $row['is_variant'],
                    'is_active' => true,
                    'sort_order' => $nextSortOrder,
                ]);
                $seenNames[$key] = true;
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'created', 'id' => $attribute->id, 'input_type' => $row['input_type']];
                $summary['created_count']++;
            } else {
                $seenNames[$key] = true;
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'would_create', 'input_type' => $row['input_type']];
                $summary['created_count']++;
            }

            $nextSortOrder += 10;
        }

        return $summary;
    }

    private function parseBool(mixed $raw): bool
    {
        return in_array(strtolower(trim((string) $raw)), ['1', 'true', 'yes'], true);
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Attribute::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
