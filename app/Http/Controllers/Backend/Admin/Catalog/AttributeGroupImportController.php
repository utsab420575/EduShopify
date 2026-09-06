<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Services\AttributeGroupImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/**
 * Bulk attribute group creation from a flat CSV (one group per row), the
 * same two-step preview-then-confirm flow as CategoryImportController —
 * parsed rows are held in the session between the two requests so nothing
 * needs temporary file storage.
 */
class AttributeGroupImportController extends Controller
{
    use InteractsWithAdmin;

    private const SESSION_KEY = 'attribute_group_import_rows';

    public function template(): Response
    {
        $this->authorize('platform.attributes.manage');

        $csv = "name,description\n"
            ."Technical Specification,Core specifications suppliers must fill in for this category.\n"
            ."Physical Dimensions,Size and weight of the product.\n"
            ."Warranty & Support,Warranty coverage and after-sales support details.\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attribute-group-import-template.csv"',
        ]);
    }

    public function preview(Request $request, AttributeGroupImportService $service)
    {
        $this->authorize('platform.attributes.manage');

        try {
            $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
            $rows = $service->parseCsv($request->file('file'));
            $summary = $service->preview($rows);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('open_group_import', true);
        }

        Session::put(self::SESSION_KEY, $rows);

        return redirect()->route('admin.catalog.builder.attribute-groups')
            ->with('group_import_preview', $summary)
            ->with('open_group_import_preview', true);
    }

    public function store(Request $request, AttributeGroupImportService $service)
    {
        $this->authorize('platform.attributes.manage');

        $rows = Session::get(self::SESSION_KEY);

        if (! $rows) {
            return redirect()->route('admin.catalog.builder.attribute-groups')
                ->with('error', 'Your import preview expired — please upload the file again.');
        }

        Session::forget(self::SESSION_KEY);

        $summary = $service->import($rows, $this->admin()->id);

        return redirect()->route('admin.catalog.builder.attribute-groups')
            ->with('success', "Import complete — {$summary['created_count']} group".($summary['created_count'] === 1 ? '' : 's')." created, {$summary['existing_count']} already existed."
                .($summary['error_count'] > 0 ? " {$summary['error_count']} row(s) had errors and were skipped." : ''));
    }
}
