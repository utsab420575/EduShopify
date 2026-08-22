<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\System\LanguageRequest;
use App\Models\Language;

class LanguageController extends Controller
{
    public function index()
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.languages.index', [
            'languages' => Language::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.languages.create', ['language' => new Language()]);
    }

    public function store(LanguageRequest $request)
    {
        Language::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.system.languages.index')->with('success', 'Language created.');
    }

    public function edit(Language $language)
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.languages.edit', ['language' => $language]);
    }

    public function update(LanguageRequest $request, Language $language)
    {
        $language->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.system.languages.index')->with('success', 'Language updated.');
    }

    public function destroy(Language $language)
    {
        $this->authorize('platform.settings.manage');

        abort_if($language->is_default, 422, 'Cannot delete the default language.');

        $language->delete();

        return back()->with('success', 'Language deleted.');
    }
}
