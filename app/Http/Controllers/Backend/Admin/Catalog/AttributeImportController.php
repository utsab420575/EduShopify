<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Services\AttributeImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/**
 * Bulk attribute creation from a flat CSV (one attribute per row), the same
 * two-step preview-then-confirm flow as CategoryImportController and
 * AttributeGroupImportController. Predefined values aren't part of this
 * file — see AttributeValueImportController, which is run afterward.
 */
class AttributeImportController extends Controller
{
    use InteractsWithAdmin;

    private const SESSION_KEY = 'attribute_import_rows';

    public function template(): Response
    {
        $this->authorize('platform.attributes.manage');

        $csv = "name,input_type,attribute_group,is_required,is_filterable,is_variant\n"
            ."Voltage,number,Technical Specification,0,1,0\n"
            ."Color,select,Physical Dimensions,0,1,1\n"
            ."Certifications,multi_select,Warranty & Support,0,0,0\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attribute-import-template.csv"',
        ]);
    }

    public function preview(Request $request, AttributeImportService $service)
    {
        $this->authorize('platform.attributes.manage');

        try {
            $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
            $rows = $service->parseCsv($request->file('file'));
            $summary = $service->preview($rows);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('open_attribute_import', true);
        }

        Session::put(self::SESSION_KEY, $rows);

        return redirect()->route('admin.catalog.builder.attributes')
            ->with('attribute_import_preview', $summary)
            ->with('open_attribute_import_preview', true);
    }

    public function store(Request $request, AttributeImportService $service)
    {
        $this->authorize('platform.attributes.manage');

        $rows = Session::get(self::SESSION_KEY);

        if (! $rows) {
            return redirect()->route('admin.catalog.builder.attributes')
                ->with('error', 'Your import preview expired — please upload the file again.');
        }

        Session::forget(self::SESSION_KEY);

        $summary = $service->import($rows);

        return redirect()->route('admin.catalog.builder.attributes')
            ->with('success', "Import complete — {$summary['created_count']} attribute".($summary['created_count'] === 1 ? '' : 's')." created, {$summary['existing_count']} already existed."
                .($summary['error_count'] > 0 ? " {$summary['error_count']} row(s) had errors and were skipped." : ''));
    }
}
