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

        $capInput = $request->input('capability_type_id');

        $validated = $request->validate([
            'document_type_id'   => ['required', 'exists:document_types,id'],
            'capability_type_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'both' && ! CapabilityType::where('id', $value)->exists()) {
                        $fail('The selected capability is invalid.');
                    }
                },
            ],
            'is_required'        => ['required', 'in:0,1'],
        ]);

        $documentType = DocumentType::findOrFail($validated['document_type_id']);
        $isRequired = (bool) $validated['is_required'];

        if ($capInput === 'both') {
            $targetCaps = CapabilityType::whereIn('code', [CapabilityType::BUYER, CapabilityType::SUPPLIER])->get();
            if ($targetCaps->isEmpty()) {
                $targetCaps = CapabilityType::all();
            }

            $created = [];
            $alreadyExisting = [];

            foreach ($targetCaps as $cap) {
                $exists = DocumentTypeEnable::where('document_type_id', $documentType->id)
                    ->where('capability_type_id', $cap->id)
                    ->exists();

                if ($exists) {
                    $alreadyExisting[] = $cap->name;
                } else {
                    DocumentTypeEnable::create([
                        'document_type_id'   => $documentType->id,
                        'capability_type_id' => $cap->id,
                        'is_required'        => $isRequired,
                    ]);
                    $created[] = $cap->name;
                }
            }

            if (count($created) === 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Document '{$documentType->name}' is already enabled for both Buyer and Supplier.");
            }

            if (count($alreadyExisting) > 0) {
                return redirect()->route('admin.catalog.document-type-enables.index')
                    ->with('success', "Document '{$documentType->name}' enabled for " . implode(' and ', $created) . ". (Already enabled for " . implode(' and ', $alreadyExisting) . ").");
            }

            return redirect()->route('admin.catalog.document-type-enables.index')
                ->with('success', "Document '{$documentType->name}' enabled for both Buyer and Supplier successfully.");
        }

        // Single capability selected
        $capability = CapabilityType::findOrFail($capInput);
        $exists = DocumentTypeEnable::where('document_type_id', $documentType->id)
            ->where('capability_type_id', $capability->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Document '{$documentType->name}' is already enabled for {$capability->name}.");
        }

        DocumentTypeEnable::create([
            'document_type_id'   => $documentType->id,
            'capability_type_id' => $capability->id,
            'is_required'        => $isRequired,
        ]);

        return redirect()->route('admin.catalog.document-type-enables.index')
            ->with('success', "Document '{$documentType->name}' enabled for {$capability->name} successfully.");
    }

    public function update(Request $request, DocumentTypeEnable $documentTypeEnable)
    {
        $this->authorize('platform.categories.manage');

        $capInput = $request->input('capability_type_id');

        $validated = $request->validate([
            'document_type_id'   => ['required', 'exists:document_types,id'],
            'capability_type_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'both' && ! CapabilityType::where('id', $value)->exists()) {
                        $fail('The selected capability is invalid.');
                    }
                },
            ],
            'is_required'        => ['required', 'in:0,1'],
        ]);

        $documentType = DocumentType::findOrFail($validated['document_type_id']);
        $isRequired = (bool) $validated['is_required'];

        if ($capInput === 'both') {
            $targetCaps = CapabilityType::whereIn('code', [CapabilityType::BUYER, CapabilityType::SUPPLIER])->get();
            if ($targetCaps->isEmpty()) {
                $targetCaps = CapabilityType::all();
            }

            // 1. Update current record
            $currentCapId = $documentTypeEnable->capability_type_id;
            $documentTypeEnable->update([
                'document_type_id' => $documentType->id,
                'is_required'      => $isRequired,
            ]);

            // 2. Ensure all target capabilities (Buyer & Supplier) have a record
            foreach ($targetCaps as $cap) {
                if ($cap->id !== $currentCapId) {
                    DocumentTypeEnable::updateOrCreate(
                        [
                            'document_type_id'   => $documentType->id,
                            'capability_type_id' => $cap->id,
                        ],
                        [
                            'is_required' => $isRequired,
                        ]
                    );
                }
            }

            return redirect()->route('admin.catalog.document-type-enables.index')
                ->with('success', "Document '{$documentType->name}' enabled for both Buyer and Supplier successfully.");
        }

        // Single capability selected
        $capability = CapabilityType::findOrFail($capInput);
        $exists = DocumentTypeEnable::where('document_type_id', $documentType->id)
            ->where('capability_type_id', $capability->id)
            ->where('id', '!=', $documentTypeEnable->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Document '{$documentType->name}' is already enabled for {$capability->name}.");
        }

        $documentTypeEnable->update([
            'document_type_id'   => $documentType->id,
            'capability_type_id' => $capability->id,
            'is_required'        => $isRequired,
        ]);

        return redirect()->route('admin.catalog.document-type-enables.index')
            ->with('success', "Document enable configuration updated for {$capability->name} successfully.");
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
