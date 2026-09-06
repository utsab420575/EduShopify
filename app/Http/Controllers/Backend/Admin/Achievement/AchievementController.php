<?php

namespace App\Http\Controllers\Backend\Admin\Achievement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Achievement\AchievementRequest;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.achievements.manage');

        $achievements = Achievement::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->withCount('accountAchievements')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.achievements.index', [
            'achievements' => $achievements,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function store(AchievementRequest $request)
    {
        Achievement::create($request->safe()->except('is_active') + [
            'slug' => $this->uniqueSlug($request->string('name')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Achievement created.');
    }

    public function update(AchievementRequest $request, Achievement $achievement)
    {
        $achievement->update($request->safe()->except('is_active') + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Achievement updated.');
    }

    public function destroy(Achievement $achievement)
    {
        $this->authorize('platform.achievements.manage');

        abort_if($achievement->accountAchievements()->exists(), 422, 'This achievement has already been claimed by one or more accounts and cannot be deleted.');

        $achievement->delete();

        return back()->with('success', 'Achievement deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Achievement::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
