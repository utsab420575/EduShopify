<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.activity_logs.view');

        $activities = Activity::query()
            ->when($request->filled('log'), fn ($q) => $q->where('log_name', $request->string('log')))
            ->when($request->filled('causer'), fn ($q) => $q->where('causer_id', $request->integer('causer')))
            ->when($request->filled('search'), fn ($q) => $q->where('description', 'like', '%'.$request->string('search').'%'))
            ->with('causer')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('backend.admin.system.audit.index', [
            'activities' => $activities,
            'log' => $request->string('log')->toString(),
            'search' => $request->string('search')->toString(),
            'logNames' => Activity::query()->distinct()->pluck('log_name')->filter()->values(),
        ]);
    }

    public function show(Activity $activity)
    {
        $this->authorize('platform.activity_logs.view');

        $activity->load('causer', 'subject');

        return view('backend.admin.system.audit.show', ['activity' => $activity]);
    }
}
