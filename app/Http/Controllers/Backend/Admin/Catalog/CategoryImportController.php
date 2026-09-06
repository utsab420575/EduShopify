<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Services\CategoryImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/**
 * Bulk category creation from a path-based CSV
 * ("Electronics > Computer > Laptop" per row), so an admin building out a
 * large taxonomy doesn't have to hand-pick a parent from a dropdown for
 * every single node. Two-step preview-then-confirm, same redirect-with-
 * flash convention as the rest of this admin panel (CategoryController)
 * rather than an AJAX round trip — the parsed rows are held in the session
 * between the two requests so nothing needs temporary file storage.
 */
class CategoryImportController extends Controller
{
    use InteractsWithAdmin;

    private const SESSION_KEY = 'category_import_rows';

    public function template(): Response
    {
        $this->authorize('platform.categories.manage');

        $csv = "path,type,description\n"
            ."Electronics,both,\n"
            ."Electronics > Computer,both,\n"
            ."Electronics > Computer > Laptop,product,\n"
            ."Electronics > Mobile,product,\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="category-import-template.csv"',
        ]);
    }

    public function preview(Request $request, CategoryImportService $service)
    {
        $this->authorize('platform.categories.manage');

        try {
            $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
            $rows = $service->parseCsv($request->file('file'));
            $summary = $service->preview($rows);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('open_import', true);
        }

        Session::put(self::SESSION_KEY, $rows);

        return redirect()->route('admin.catalog.builder.categories')
            ->with('import_preview', $summary)
            ->with('open_import_preview', true);
    }

    public function store(Request $request, CategoryImportService $service)
    {
        $this->authorize('platform.categories.manage');

        $rows = Session::get(self::SESSION_KEY);

        if (! $rows) {
            return redirect()->route('admin.catalog.builder.categories')
                ->with('error', 'Your import preview expired — please upload the file again.');
        }

        Session::forget(self::SESSION_KEY);

        $summary = $service->import($rows, $this->admin()->id);

        return redirect()->route('admin.catalog.builder.categories')
            ->with('success', "Import complete — {$summary['created_count']} categor".($summary['created_count'] === 1 ? 'y' : 'ies')." created, {$summary['existing_count']} already existed."
                .($summary['error_count'] > 0 ? " {$summary['error_count']} row(s) had errors and were skipped." : ''));
    }
}
