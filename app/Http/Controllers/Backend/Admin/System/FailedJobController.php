<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FailedJobController extends Controller
{
    public function index()
    {
        $this->authorize('platform.settings.manage');

        $jobs = DB::table('failed_jobs')->orderByDesc('failed_at')->paginate(30);

        return view('backend.admin.system.jobs.index', ['jobs' => $jobs]);
    }

    public function retry(string $id)
    {
        $this->authorize('platform.settings.manage');

        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', 'Job queued for retry.');
    }

    public function destroy(string $id)
    {
        $this->authorize('platform.settings.manage');

        Artisan::call('queue:forget', ['id' => $id]);

        return back()->with('success', 'Failed job removed.');
    }
}
