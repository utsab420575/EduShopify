<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\GitDeploymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GitDeploymentController extends Controller
{
    public function index(GitDeploymentService $git): View
    {
        $this->authorize('platform.system.deploy');

        return view('backend.admin.system.deploy.index', [
            'status' => $git->status(),
            'commits' => $git->recentCommits(10),
            'branches' => $git->branches(),
            'githubRepoUrl' => $git->githubRepoUrl(),
        ]);
    }

    public function pull(Request $request, GitDeploymentService $git): RedirectResponse
    {
        $this->authorize('platform.system.deploy');

        $validated = $request->validate([
            // The service re-validates this against the real branch list
            // before it's ever used in the git process — this rule only
            // rejects an obviously malformed request early.
            'branch' => ['required', 'string', 'max:255'],
        ]);

        $result = $git->pull($validated['branch']);

        activity('rbac')
            ->causedBy($request->user())
            ->withProperties([
                'action' => 'git_pull',
                'branch' => $validated['branch'],
                'success' => $result['ok'],
                'ip_address' => $request->ip(),
            ])
            ->log($result['ok']
                ? "Pulled branch '{$validated['branch']}' from GitHub"
                : "Failed to pull branch '{$validated['branch']}' from GitHub");

        return back()->with($result['ok'] ? 'success' : 'error', $result['ok']
            ? 'Pulled the latest changes from GitHub.'
            : 'Git pull failed — see details below.')
            ->with('pullOutput', $result['output'])
            ->with('pullError', $result['error']);
    }
}
