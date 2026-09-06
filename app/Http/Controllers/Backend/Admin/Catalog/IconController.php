<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Icon;
use App\Models\IconLibrary;
use Illuminate\Http\Request;

class IconController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $icons = Icon::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('icon_value', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('library_id'), function ($q) use ($request) {
                $q->where('library_id', $request->integer('library_id'));
            })
            ->when($request->filled('icon_type'), function ($q) use ($request) {
                $q->where('icon_type', $request->string('icon_type'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->string('status')->toString();
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->with('library')
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $libraries = IconLibrary::orderBy('name')->get(['id', 'name']);

        return view('backend.admin.catalog.icons.index', [
            'icons'      => $icons,
            'libraries'  => $libraries,
            'search'     => $request->string('search')->toString(),
            'libraryId'  => $request->input('library_id', ''),
            'iconType'   => $request->string('icon_type')->toString(),
            'status'     => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        $libraries = IconLibrary::active()->orderBy('name')->get();

        return view('backend.admin.catalog.icons.create', [
            'icon'      => new Icon(['is_active' => true, 'icon_type' => 'fontawesome']),
            'libraries' => $libraries,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $validated = $request->validate([
            'library_id' => 'required|exists:icon_libraries,id',
            'name'       => 'required|string|max:255',
            'icon_type'  => 'required|string|in:fontawesome,svg,image_url',
            'icon_value' => 'required|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $icon = Icon::create($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($icon)->log('Icon created');

        return redirect()->route('admin.catalog.icons.index')->with('success', "Icon '{$icon->name}' created successfully.");
    }

    public function edit(Icon $icon)
    {
        $this->authorize('platform.attributes.manage');

        $libraries = IconLibrary::orderBy('name')->get();

        return view('backend.admin.catalog.icons.edit', [
            'icon'      => $icon,
            'libraries' => $libraries,
        ]);
    }

    public function update(Request $request, Icon $icon)
    {
        $this->authorize('platform.attributes.manage');

        $validated = $request->validate([
            'library_id' => 'required|exists:icon_libraries,id',
            'name'       => 'required|string|max:255',
            'icon_type'  => 'required|string|in:fontawesome,svg,image_url',
            'icon_value' => 'required|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $icon->update($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($icon)->log('Icon updated');

        return redirect()->route('admin.catalog.icons.index')->with('success', "Icon '{$icon->name}' updated successfully.");
    }

    public function destroy(Icon $icon)
    {
        $this->authorize('platform.attributes.manage');

        $name = $icon->name;
        $icon->delete();

        activity('catalog')->causedBy($this->admin())->log("Icon '{$name}' deleted");

        return redirect()->route('admin.catalog.icons.index')->with('success', "Icon '{$name}' deleted successfully.");
    }

    public function toggleActive(Icon $icon)
    {
        $this->authorize('platform.attributes.manage');

        $icon->update(['is_active' => ! $icon->is_active]);

        $state = $icon->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Icon '{$icon->name}' {$state}.");
    }

    /**
     * Search API for icon pickers in backend/supplier panels.
     */
    public function apiSearch(Request $request)
    {
        $search = $request->string('q')->toString();
        $libraryId = $request->input('library_id');

        $icons = Icon::query()
            ->active()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('icon_value', 'like', "%{$search}%");
                });
            })
            ->when($libraryId, fn ($q) => $q->where('library_id', $libraryId))
            ->with('library:id,name')
            ->orderBy('name')
            ->limit(60)
            ->get()
            ->map(function (Icon $icon) {
                return [
                    'id'           => $icon->id,
                    'name'         => $icon->name,
                    'icon_type'    => $icon->icon_type,
                    'icon_value'   => $icon->icon_value,
                    'library_name' => $icon->library?->name ?? '',
                    'html'         => (string) $icon->render('text-lg inline-block align-middle'),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $icons,
        ]);
    }
}
