<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class FrontendLayoutComposer
{
    public function compose(View $view): void
    {
        $view->with('headerCategories', Category::active()->approved()->roots()->orderBy('sort_order')->limit(8)->get());
    }
}
