<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\InputTypeRequest;
use App\Models\InputType;
use Illuminate\Http\Request;

class InputTypeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $inputTypes = InputType::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('has_options'), function ($q) use ($request) {
                $q->where('has_options', $request->boolean('has_options'));
            })
            ->when($request->filled('is_multiple'), function ($q) use ($request) {
                $q->where('is_multiple', $request->boolean('is_multiple'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->string('status')->toString();
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->with(['attributes' => fn ($q) => $q->with('attributeGroup')->orderBy('name')])
            ->withCount('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.input-types.index', [
            'inputTypes'  => $inputTypes,
            'search'      => $request->string('search')->toString(),
            'hasOptions'  => $request->input('has_options', ''),
            'isMultiple'  => $request->input('is_multiple', ''),
            'status'      => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.input-types.create', [
            'inputType' => new InputType(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(InputTypeRequest $request)
    {
        $validated = $request->validated();
        $validated['has_options'] = $request->boolean('has_options');
        $validated['is_multiple'] = $request->boolean('is_multiple');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $inputType = InputType::create($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($inputType)->log('Input type created');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Input type created successfully.']);
        }

        return redirect()->route('admin.catalog.input-types.index')->with('success', "Input type '{$inputType->name}' created.");
    }

    public function edit(InputType $inputType)
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.input-types.edit', [
            'inputType' => $inputType->loadCount('attributes'),
        ]);
    }

    public function update(InputTypeRequest $request, InputType $inputType)
    {
        $validated = $request->validated();
        $validated['has_options'] = $request->boolean('has_options');
        $validated['is_multiple'] = $request->boolean('is_multiple');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $inputType->update($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($inputType)->log('Input type updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Input type updated successfully.']);
        }

        return redirect()->route('admin.catalog.input-types.index')->with('success', "Input type '{$inputType->name}' updated.");
    }

    public function destroy(Request $request, InputType $inputType)
    {
        $this->authorize('platform.attributes.manage');

        abort_if(
            $inputType->attributes()->exists(),
            422,
            "Cannot delete '{$inputType->name}' — it is currently assigned to {$inputType->attributes()->count()} attribute(s)."
        );

        $inputType->delete();

        activity('catalog')->causedBy($this->admin())->performedOn($inputType)->log('Input type deleted');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Input type deleted successfully.']);
        }

        return back()->with('success', "Input type '{$inputType->name}' deleted.");
    }
}
