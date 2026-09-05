<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;

class ResourceController extends Controller
{
    public function index()
    {
        $articles = collect(config('frontend_new_demo.articles', []));

        return view('frontend_new.resources.index', [
            'featured' => $articles->firstWhere('featured', true),
            'articles' => $articles->reject(fn ($a) => $a['featured'] ?? false)->values(),
            'events' => collect(config('frontend_new_demo.events', []))->take(3),
        ]);
    }
}
