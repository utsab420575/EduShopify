<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\DocumentTypeRequest;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.categories.manage');

        $documentTypes = DocumentType::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sq) use ($request) {
                $sq->where('name', 'like', '%'.$request->string('search').'%')
                   ->orWhere('description', 'like', '%'.$request->string('search').'%');
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.document-types.index', [
            'documentTypes' => $documentTypes,
            'search'        => $request->string('search')->toString(),
            'status'        => $request->string('status')->toString(),
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
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return redirect()->route('admin.catalog.document-types.index')->with('success', 'Document type created successfully.');
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
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return redirect()->route('admin.catalog.document-types.index')->with('success', 'Document type updated successfully.');
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
