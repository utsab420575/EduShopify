<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\IconLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class IconLibraryController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $libraries = IconLibrary::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->string('type'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->string('status')->toString();
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->withCount('icons')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.catalog.icon-libraries.index', [
            'libraries' => $libraries,
            'search'    => $request->string('search')->toString(),
            'type'      => $request->string('type')->toString(),
            'status'    => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.icon-libraries.create', [
            'library' => new IconLibrary(['is_active' => true, 'type' => 'CDN']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255|unique:icon_libraries,slug',
            'type'      => 'required|string|in:CDN,Local,Custom',
            'cdn_url'   => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $count = 1;
        while (IconLibrary::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        $library = IconLibrary::create($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($library)->log('Icon library created');

        return redirect()->route('admin.catalog.icon-libraries.index')->with('success', "Icon library '{$library->name}' created successfully.");
    }

    public function edit(IconLibrary $iconLibrary)
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.icon-libraries.edit', [
            'library' => $iconLibrary->loadCount('icons'),
        ]);
    }

    public function update(Request $request, IconLibrary $iconLibrary)
    {
        $this->authorize('platform.attributes.manage');

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => ['nullable', 'string', 'max:255', Rule::unique('icon_libraries', 'slug')->ignore($iconLibrary->id)],
            'type'      => 'required|string|in:CDN,Local,Custom',
            'cdn_url'   => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $iconLibrary->update($validated);

        activity('catalog')->causedBy($this->admin())->performedOn($iconLibrary)->log('Icon library updated');

        return redirect()->route('admin.catalog.icon-libraries.index')->with('success', "Icon library '{$iconLibrary->name}' updated successfully.");
    }

    public function destroy(IconLibrary $iconLibrary)
    {
        $this->authorize('platform.attributes.manage');

        $name = $iconLibrary->name;
        $iconLibrary->delete();

        activity('catalog')->causedBy($this->admin())->log("Icon library '{$name}' deleted");

        return redirect()->route('admin.catalog.icon-libraries.index')->with('success', "Icon library '{$name}' deleted successfully.");
    }

    public function toggleActive(IconLibrary $iconLibrary)
    {
        $this->authorize('platform.attributes.manage');

        $iconLibrary->update(['is_active' => ! $iconLibrary->is_active]);

        $state = $iconLibrary->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Icon library '{$iconLibrary->name}' {$state}.");
    }
}
