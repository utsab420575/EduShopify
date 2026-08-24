<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\CapabilityType;
use App\Models\DocumentType;
use App\Models\DocumentTypeEnable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentTypeEnableController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.categories.manage');

        $query = DocumentTypeEnable::query()->with(['documentType', 'capabilityType']);

        if ($request->filled('capability_id')) {
            $query->where('capability_type_id', $request->integer('capability_id'));
        }

        if ($request->filled('requirement')) {
            $query->where('is_required', $request->string('requirement')->toString() === 'required');
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('documentType', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $enables = $query->join('document_types', 'document_type_enables.document_type_id', '=', 'document_types.id')
            ->select('document_type_enables.*')
            ->orderBy('document_type_enables.capability_type_id')
            ->orderBy('document_types.sort_order')
            ->orderBy('document_types.name')
            ->paginate(20)
            ->withQueryString();

        $documentTypes = DocumentType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $capabilityTypes = CapabilityType::orderBy('name')->get();

        return view('backend.admin.catalog.document-type-enables.index', [
            'enables'         => $enables,
            'documentTypes'   => $documentTypes,
            'capabilityTypes' => $capabilityTypes,
            'capabilityId'    => $request->input('capability_id'),
            'requirement'     => $request->string('requirement')->toString(),
            'search'          => $request->string('search')->toString(),
            'totalCount'      => DocumentTypeEnable::count(),
            'requiredCount'   => DocumentTypeEnable::where('is_required', true)->count(),
            'optionalCount'   => DocumentTypeEnable::where('is_required', false)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('platform.categories.manage');

        $validated = $request->validate([
            'document_type_id'   => ['required', 'exists:document_types,id'],
            'capability_type_id' => [
                'required',
                'exists:capability_types,id',
                Rule::unique('document_type_enables')->where(function ($query) use ($request) {
                    return $query->where('document_type_id', $request->input('document_type_id'))
                                 ->where('capability_type_id', $request->input('capability_type_id'));
                }),
            ],
            'is_required'        => ['required', 'in:0,1'],
        ], [
            'capability_type_id.unique' => 'This document type is already enabled for the selected capability.',
        ]);

        DocumentTypeEnable::create([
            'document_type_id'   => $validated['document_type_id'],
            'capability_type_id' => $validated['capability_type_id'],
            'is_required'        => (bool) $validated['is_required'],
        ]);

        return redirect()->route('admin.catalog.document-type-enables.index')
            ->with('success', 'Document type enabled for capability successfully.');
    }

    public function update(Request $request, DocumentTypeEnable $documentTypeEnable)
    {
        $this->authorize('platform.categories.manage');

        $validated = $request->validate([
            'document_type_id'   => ['required', 'exists:document_types,id'],
            'capability_type_id' => [
                'required',
                'exists:capability_types,id',
                Rule::unique('document_type_enables')
                    ->ignore($documentTypeEnable->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('document_type_id', $request->input('document_type_id'))
                                     ->where('capability_type_id', $request->input('capability_type_id'));
                    }),
            ],
            'is_required'        => ['required', 'in:0,1'],
        ], [
            'capability_type_id.unique' => 'This document type is already enabled for the selected capability.',
        ]);

        $documentTypeEnable->update([
            'document_type_id'   => $validated['document_type_id'],
            'capability_type_id' => $validated['capability_type_id'],
            'is_required'        => (bool) $validated['is_required'],
        ]);

        return redirect()->route('admin.catalog.document-type-enables.index')
            ->with('success', 'Document enable configuration updated successfully.');
    }

    public function toggleRequirement(DocumentTypeEnable $documentTypeEnable)
    {
        $this->authorize('platform.categories.manage');

        $newRequirement = !$documentTypeEnable->is_required;
        $documentTypeEnable->update(['is_required' => $newRequirement]);

        $label = $newRequirement ? 'Required (Mandatory)' : 'Optional';
        $docName = $documentTypeEnable->documentType?->name ?? 'Document';

        return back()->with('success', "{$docName} is now {$label}.");
    }

    public function destroy(DocumentTypeEnable $documentTypeEnable)
    {
        $this->authorize('platform.categories.manage');

        $docName = $documentTypeEnable->documentType?->name ?? 'Document';
        $capName = $documentTypeEnable->capabilityType?->name ?? 'Capability';

        $documentTypeEnable->delete();

        return back()->with('success', "Removed {$docName} from {$capName} capability.");
    }
}
