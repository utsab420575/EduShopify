<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bulk-creates a category tree from a CSV of full paths
 * ("Electronics > Computer > Laptop", one per row), so an admin doesn't
 * have to hand-pick a parent from a dropdown for every single node.
 *
 * Both preview() and import() share resolveRow() — the only difference is
 * whether missing segments are actually written to the database ($write).
 * A $virtual map tracks segments that would exist after earlier rows in
 * the same batch are processed, so overlapping paths ("Electronics" then
 * "Electronics > Mobile") never produce duplicates, and a preview
 * accurately reflects what import() will actually do.
 */
class CategoryImportService
{
    public const PATH_SEPARATOR = '>';

    /**
     * Parse an uploaded CSV into normalized rows. Expects a header row with
     * at least a "path" column; "type" (product/service/both) and
     * "description" columns are optional and apply only to the leaf
     * segment of each row.
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
        $pathCol = array_search('path', $header, true);
        if ($pathCol === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV must have a "path" column, e.g. "Electronics > Computer > Laptop".']);
        }
        $typeCol = array_search('type', $header, true);
        $descriptionCol = array_search('description', $header, true);

        $rows = [];
        $lineNumber = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $path = trim((string) ($line[$pathCol] ?? ''));
            if ($path === '') {
                continue;
            }

            $type = $typeCol !== false ? strtolower(trim((string) ($line[$typeCol] ?? ''))) : '';
            if (! in_array($type, ['product', 'service', 'both'], true)) {
                $type = 'both';
            }

            $rows[] = [
                'line' => $lineNumber,
                'path' => $path,
                'type' => $type,
                'description' => $descriptionCol !== false ? trim((string) ($line[$descriptionCol] ?? '')) ?: null : null,
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            throw ValidationException::withMessages(['file' => 'No usable rows found — every "path" cell was empty.']);
        }

        return $rows;
    }

    /**
     * Non-destructive: reports, per row, which path segments already exist
     * and which would be newly created. Writes nothing to the database.
     */
    public function preview(array $rows): array
    {
        return $this->process($rows, write: false, actorUserId: null);
    }

    /**
     * Actually creates the missing categories (get-or-create per segment),
     * reusing the same slug and approval defaults as manual creation
     * (CategoryController::store()) so imported rows behave identically to
     * hand-created ones.
     */
    public function import(array $rows, int $actorUserId): array
    {
        return DB::transaction(fn () => $this->process($rows, write: true, actorUserId: $actorUserId));
    }

    private function process(array $rows, bool $write, ?int $actorUserId): array
    {
        $virtual = [];
        $summary = ['rows' => [], 'created_count' => 0, 'existing_count' => 0, 'error_count' => 0];

        foreach ($rows as $row) {
            $segments = array_values(array_filter(array_map('trim', explode(self::PATH_SEPARATOR, $row['path']))));

            if (empty($segments)) {
                $summary['rows'][] = ['line' => $row['line'], 'path' => $row['path'], 'error' => 'Empty path.'];
                $summary['error_count']++;
                continue;
            }

            try {
                $segmentResults = $this->resolveSegments($segments, $virtual, $write, $actorUserId, $row);
            } catch (ValidationException $e) {
                $summary['rows'][] = ['line' => $row['line'], 'path' => $row['path'], 'error' => $e->getMessage()];
                $summary['error_count']++;
                continue;
            }

            foreach ($segmentResults as $seg) {
                $seg['status'] === 'existing' ? $summary['existing_count']++ : $summary['created_count']++;
            }

            $summary['rows'][] = [
                'line' => $row['line'],
                'path' => $row['path'],
                'segments' => $segmentResults,
            ];
        }

        return $summary;
    }

    /**
     * Walks a path's segments left to right under a shared, mutable
     * $virtual map (keyed "parentId|lowercased name" => id) so segments
     * shared across multiple rows in the same batch — real or, in preview
     * mode, not-yet-real — are only resolved/created once.
     */
    private function resolveSegments(array $segments, array &$virtual, bool $write, ?int $actorUserId, array $row): array
    {
        $parentId = null;
        $results = [];

        foreach ($segments as $level => $segment) {
            if (mb_strlen($segment) > 150) {
                throw ValidationException::withMessages(['path' => "\"{$segment}\" is longer than 150 characters."]);
            }

            $key = ($parentId ?? 0).'|'.mb_strtolower($segment);

            if (isset($virtual[$key])) {
                $id = $virtual[$key];
                $results[] = ['name' => $segment, 'status' => 'existing', 'id' => $id];
                $parentId = $id;
                continue;
            }

            $existing = Category::where('parent_id', $parentId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($segment)])
                ->first();

            if ($existing) {
                $virtual[$key] = $existing->id;
                $results[] = ['name' => $segment, 'status' => 'existing', 'id' => $existing->id];
                $parentId = $existing->id;
                continue;
            }

            $isLeaf = $segment === end($segments);

            if ($write) {
                $category = Category::create([
                    'parent_id' => $parentId,
                    'name' => $segment,
                    'slug' => Category::generateUniqueSlug($segment),
                    'level' => $level,
                    'type' => $isLeaf ? ($row['type'] ?: 'both') : 'both',
                    'description' => $isLeaf ? $row['description'] : null,
                    'approval_status' => 'approved',
                    'created_by_user_id' => $actorUserId,
                    'reviewed_by_user_id' => $actorUserId,
                    'reviewed_at' => now(),
                    'is_active' => true,
                ]);
                $virtual[$key] = $category->id;
                $results[] = ['name' => $segment, 'status' => 'created', 'id' => $category->id];
                $parentId = $category->id;
            } else {
                // Preview mode: a negative placeholder so later segments in
                // this same branch (this row or a later one) still resolve
                // as "would be a child of the thing we're about to create",
                // without ever colliding with a real category id.
                $placeholderId = -(count($virtual) + 1);
                $virtual[$key] = $placeholderId;
                $results[] = ['name' => $segment, 'status' => 'would_create', 'id' => $placeholderId];
                $parentId = $placeholderId;
            }
        }

        return $results;
    }
}
