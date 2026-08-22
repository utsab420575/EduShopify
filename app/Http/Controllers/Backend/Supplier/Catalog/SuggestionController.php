<?php

namespace App\Http\Controllers\Backend\Supplier\Catalog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\AttributeSuggestion;
use App\Models\Category;
use App\Models\CategorySuggestion;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();

        $categorySuggestions = CategorySuggestion::where('supplier_account_id', $account->id)
            ->with('parentCategory')
            ->latest()
            ->paginate(10, ['*'], 'cat_page');

        $attributeSuggestions = AttributeSuggestion::where('supplier_account_id', $account->id)
            ->with('category')
            ->latest()
            ->paginate(10, ['*'], 'attr_page');

        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('backend.supplier.catalog.suggestions.index', [
            'categorySuggestions'  => $categorySuggestions,
            'attributeSuggestions' => $attributeSuggestions,
            'parentCategories'     => $parentCategories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'proposed_name'      => ['required', 'string', 'max:200'],
            'proposed_type'      => ['required', 'string', 'in:product,service,both'],
            'parent_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description'        => ['nullable', 'string', 'max:1000'],
        ]);

        CategorySuggestion::create([
            'supplier_account_id'  => $account->id,
            'suggested_by_user_id' => $this->currentUser()->id,
            'proposed_name'        => $validated['proposed_name'],
            'proposed_type'        => $validated['proposed_type'],
            'parent_category_id'   => $validated['parent_category_id'] ?? null,
            'description'          => $validated['description'] ?? null,
            'status'               => 'pending',
        ]);

        return redirect()
            ->route('supplier.catalog.suggestions.index')
            ->with('success', 'Category suggestion submitted. Our team will review it shortly.');
    }

    public function storeAttribute(Request $request)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'proposed_name'       => ['required', 'string', 'max:150'],
            'proposed_input_type' => ['required', 'string', 'in:text,textarea,number,select,multi_select,boolean,date,color'],
            'category_id'         => ['nullable', 'exists:categories,id'],
            'proposed_unit_name'  => ['nullable', 'string', 'max:50'],
            'reason'              => ['nullable', 'string', 'max:1000'],
        ]);

        AttributeSuggestion::create([
            'supplier_account_id'  => $account->id,
            'suggested_by_user_id' => $this->currentUser()->id,
            'proposed_name'        => $validated['proposed_name'],
            'proposed_input_type'  => $validated['proposed_input_type'],
            'category_id'          => $validated['category_id'] ?? null,
            'proposed_unit_name'   => $validated['proposed_unit_name'] ?? null,
            'reason'               => $validated['reason'] ?? null,
            'status'               => 'pending',
        ]);

        return redirect()
            ->route('supplier.catalog.suggestions.index')
            ->with('success', 'Attribute suggestion submitted. Our team will review it shortly.');
    }
}
