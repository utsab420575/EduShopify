<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\CategoryRequest;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Category;
use App\Models\CategorySuggestion;
use App\Services\CategorySuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.categories.manage');

        $categories = Category::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->with('parent')
            ->withCount(['children', 'attributes'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.catalog.categories.index', [
            'categories' => $categories,
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.categories.manage');

        $categories = Category::with('parent')->orderBy('name')->get();

        return view('backend.admin.catalog.categories.create', [
            'category' => new Category(),
            'parents' => $categories,
            'existingCategories' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'parent_id' => $c->parent_id,
                'parent_name' => $c->parent?->name,
            ]),
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;

        $category = Category::create($request->validated() + [
            'slug' => Category::generateUniqueSlug($request->string('name')),
            'level' => Category::levelForParent($parentId),
            'approval_status' => 'approved',
            'created_by_user_id' => $this->admin()->id,
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('success', "'{$category->name}' created.");
        }

        return redirect()->route('admin.catalog.categories.attributes.index', $category)
            ->with('success', "'{$category->name}' created. Now assign the specification attributes suppliers should fill in for this category.");
    }

    public function edit(Category $category)
    {
        $this->authorize('platform.categories.manage');

        $categories = Category::with('parent')->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('backend.admin.catalog.categories.edit', [
            'category' => $category,
            'parents' => $categories,
            'existingCategories' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'parent_id' => $c->parent_id,
                'parent_name' => $c->parent?->name,
            ]),
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $newParentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;
        $parentChanged = $newParentId !== $category->parent_id;

        $category->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
            'level' => $parentChanged ? Category::levelForParent($newParentId) : $category->level,
        ]);

        // A category's level is only ever set once, at creation — moving it
        // to a different parent here is the one path that can make it (and
        // everything nested under it) stale, so bring the whole subtree
        // back in sync straight away rather than leaving it wrong.
        if ($parentChanged) {
            $category->realignDescendantLevels();
        }

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('success', "'{$category->name}' updated.");
        }

        return redirect()->route('admin.catalog.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('platform.categories.manage');

        abort_if($category->children()->exists(), 422, 'Remove or reassign child categories first.');
        abort_if($category->mainCategoryListings()->exists(), 422, 'This category is in use by listings.');

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function approveSuggestion(CategorySuggestion $suggestion, CategorySuggestionService $service)
    {
        $this->authorize('platform.categories.manage');

        try {
            $service->approve($suggestion, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($suggestion)->log('Category suggestion approved');

        return back()->with('success', 'Category suggestion approved.');
    }

    public function rejectSuggestion(ReasonRequest $request, CategorySuggestion $suggestion, CategorySuggestionService $service)
    {
        $this->authorize('platform.categories.manage');

        try {
            $service->reject($suggestion, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($suggestion)
            ->withProperties(['reason' => $request->string('reason')])->log('Category suggestion rejected');

        return back()->with('success', 'Category suggestion rejected.');
    }

}
