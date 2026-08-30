<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class RbacAuditLogController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $activities = Activity::where('log_name', 'rbac')
            ->with('causer')
            ->latest()
            ->paginate(20);

        return view('backend.admin.access-control.audit-logs.index', [
            'activities' => $activities,
        ]);
    }
}
