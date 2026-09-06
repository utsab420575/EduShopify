<?php

namespace App\Services;

use App\Models\AttributeGroup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Bulk-creates attribute groups from a flat CSV (one group per row), the
 * same shape as CategoryImportService but without any path/tree parsing
 * since attribute groups are a flat list, not a hierarchy.
 *
 * The CSV only carries the two fields an admin actually has an opinion
 * about — name and description. Everything else (is_active, sort_order,
 * created_by, timestamps) is set automatically, the same as it would be
 * for any other row in the table: is_active always starts true, sort_order
 * is generated (appended after whatever already exists), and created_by is
 * whoever ran the import.
 *
 * Both preview() and import() share process() — the only difference is
 * whether missing groups are actually written to the database ($write). A
 * $seenNames set tracks names already resolved earlier in the same batch so
 * a file that lists the same group twice doesn't try to create it twice.
 */
class AttributeGroupImportService
{
    /**
     * Parse an uploaded CSV into normalized rows. Expects a header row with
     * both a "name" and a "description" column — both are required per row.
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
        $descriptionCol = array_search('description', $header, true);
        if ($descriptionCol === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV must have a "description" column.']);
        }

        $rows = [];
        $lineNumber = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $name = trim((string) ($line[$nameCol] ?? ''));
            $description = trim((string) ($line[$descriptionCol] ?? ''));

            if ($name === '' && $description === '') {
                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'name' => $name,
                'description' => $description,
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
        return $this->process($rows, write: false, actorUserId: null);
    }

    /**
     * Actually creates the missing groups (get-or-create per name), reusing
     * the same slug-generation approach as manual creation
     * (AttributeGroupController::store()) so imported rows behave
     * identically to hand-created ones.
     */
    public function import(array $rows, int $actorUserId): array
    {
        return DB::transaction(fn () => $this->process($rows, write: true, actorUserId: $actorUserId));
    }

    private function process(array $rows, bool $write, ?int $actorUserId): array
    {
        $seenNames = [];
        $nextSortOrder = ((int) AttributeGroup::max('sort_order')) + 10;
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

            if ($row['description'] === '') {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'error' => 'Description is required.'];
                $summary['error_count']++;
                continue;
            }

            $key = mb_strtolower($name);

            if (isset($seenNames[$key])) {
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'existing'];
                $summary['existing_count']++;
                continue;
            }

            $existing = AttributeGroup::whereRaw('LOWER(name) = ?', [$key])->first();
            if ($existing) {
                $seenNames[$key] = true;
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'existing', 'id' => $existing->id];
                $summary['existing_count']++;
                continue;
            }

            if ($write) {
                $group = AttributeGroup::create([
                    'name' => $name,
                    'slug' => $this->generateUniqueSlug($name),
                    'description' => $row['description'],
                    'sort_order' => $nextSortOrder,
                    'is_active' => true,
                    'created_by_user_id' => $actorUserId,
                ]);
                $seenNames[$key] = true;
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'created', 'id' => $group->id];
                $summary['created_count']++;
            } else {
                $seenNames[$key] = true;
                $summary['rows'][] = ['line' => $row['line'], 'name' => $name, 'status' => 'would_create'];
                $summary['created_count']++;
            }

            $nextSortOrder += 10;
        }

        return $summary;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (AttributeGroup::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
