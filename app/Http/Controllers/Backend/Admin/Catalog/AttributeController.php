<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\AttributeRequest;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Attribute;
use App\Models\AttributeSuggestion;
use App\Models\AttributeValue;
use App\Models\Unit;
use App\Services\AttributeSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttributeController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.attributes.manage');

        $attributes = Attribute::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->with('unit')
            ->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.attributes.index', [
            'attributes' => $attributes,
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.attributes.create', [
            'attribute' => new Attribute(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(AttributeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $attribute = Attribute::create($request->safe()->except('values') + [
                'slug' => $this->uniqueSlug($request->string('name')),
                'is_filterable' => $request->boolean('is_filterable'),
                'is_variant' => $request->boolean('is_variant'),
                'is_required' => $request->boolean('is_required'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncValues($attribute, $request->string('values')->toString());
        });

        return redirect()->route('admin.catalog.attributes.index')->with('success', 'Attribute created.');
    }

    public function edit(Attribute $attribute)
    {
        $this->authorize('platform.attributes.manage');

        return view('backend.admin.catalog.attributes.edit', [
            'attribute' => $attribute->load('values'),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(AttributeRequest $request, Attribute $attribute)
    {
        DB::transaction(function () use ($request, $attribute) {
            $attribute->update($request->safe()->except('values') + [
                'is_filterable' => $request->boolean('is_filterable'),
                'is_variant' => $request->boolean('is_variant'),
                'is_required' => $request->boolean('is_required'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncValues($attribute, $request->string('values')->toString());
        });

        return redirect()->route('admin.catalog.attributes.index')->with('success', 'Attribute updated.');
    }

    public function destroy(Attribute $attribute)
    {
        $this->authorize('platform.attributes.manage');

        abort_if($attribute->listingAttributeValues()->exists(), 422, 'This attribute is in use by listings.');

        $attribute->values()->delete();
        $attribute->delete();

        return back()->with('success', 'Attribute deleted.');
    }

    public function approveSuggestion(AttributeSuggestion $suggestion, AttributeSuggestionService $service)
    {
        $this->authorize('platform.attributes.manage');

        try {
            $service->approve($suggestion, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($suggestion)->log('Attribute suggestion approved');

        return back()->with('success', 'Attribute suggestion approved.');
    }

    public function rejectSuggestion(ReasonRequest $request, AttributeSuggestion $suggestion, AttributeSuggestionService $service)
    {
        $this->authorize('platform.attributes.manage');

        try {
            $service->reject($suggestion, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($suggestion)
            ->withProperties(['reason' => $request->string('reason')])->log('Attribute suggestion rejected');

        return back()->with('success', 'Attribute suggestion rejected.');
    }

    private function syncValues(Attribute $attribute, string $raw): void
    {
        $submitted = collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values();

        if ($submitted->isEmpty()) {
            return;
        }

        $existing = $attribute->values()->pluck('value');

        $attribute->values()->whereNotIn('value', $submitted)->delete();

        foreach ($submitted as $index => $value) {
            if ($existing->contains($value)) {
                continue;
            }

            AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $value,
                'slug' => Str::slug($value),
                'sort_order' => $index,
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Attribute::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
