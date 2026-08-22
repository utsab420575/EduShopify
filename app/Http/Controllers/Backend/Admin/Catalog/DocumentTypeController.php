<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\DocumentTypeRequest;
use App\Models\DocumentType;
use Illuminate\Support\Str;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.document-types.index', [
            'documentTypes' => DocumentType::orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.document-types.create', ['documentType' => new DocumentType()]);
    }

    public function store(DocumentTypeRequest $request)
    {
        DocumentType::create($request->safe()->except('accepted_formats') + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'accepted_formats' => $this->parseFormats($request->string('accepted_formats')->toString()),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.document-types.index')->with('success', 'Document type created.');
    }

    public function edit(DocumentType $documentType)
    {
        $this->authorize('platform.categories.manage');

        return view('backend.admin.catalog.document-types.edit', ['documentType' => $documentType]);
    }

    public function update(DocumentTypeRequest $request, DocumentType $documentType)
    {
        $documentType->update($request->safe()->except('accepted_formats') + [
            'accepted_formats' => $this->parseFormats($request->string('accepted_formats')->toString()),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.document-types.index')->with('success', 'Document type updated.');
    }

    public function destroy(DocumentType $documentType)
    {
        $this->authorize('platform.categories.manage');

        abort_if($documentType->supplierDocuments()->exists(), 422, 'This document type is in use.');

        $documentType->delete();

        return back()->with('success', 'Document type deleted.');
    }

    private function parseFormats(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($v) => ltrim(trim($v), '.'))
            ->filter()
            ->values()
            ->all();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (DocumentType::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
