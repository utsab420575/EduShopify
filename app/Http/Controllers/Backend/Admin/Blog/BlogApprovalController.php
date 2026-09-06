<?php

namespace App\Http\Controllers\Backend\Admin\Blog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogApprovalController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.blog.review');

        $status = $request->string('status')->toString();

        $posts = BlogPost::query()
            ->where('status', '!=', 'draft')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->with(['account', 'category', 'authorUser', 'approvedBy'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.blog.approval.index', [
            'posts' => $posts,
            'status' => $status,
            'search' => $request->string('search')->toString(),
            'counts' => [
                'pending' => BlogPost::where('status', 'pending')->count(),
                'approved' => BlogPost::where('status', 'approved')->count(),
                'rejected' => BlogPost::where('status', 'rejected')->count(),
                'all' => BlogPost::where('status', '!=', 'draft')->count(),
            ],
        ]);
    }

    public function approve(BlogPost $post)
    {
        $this->authorize('platform.blog.review');

        abort_unless($post->status === 'pending', 422, 'Only a pending post can be approved.');

        $post->update([
            'status' => 'approved',
            'approved_by_user_id' => $this->admin()->id,
            'approved_at' => now(),
            'published_at' => $post->published_at ?? now(),
            'rejection_reason' => null,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($post)->log('Blog post approved');

        return back()->with('success', 'Blog post approved.');
    }

    public function reject(ReasonRequest $request, BlogPost $post)
    {
        $this->authorize('platform.blog.review');

        abort_unless($post->status === 'pending', 422, 'Only a pending post can be rejected.');

        $post->update([
            'status' => 'rejected',
            'approved_by_user_id' => $this->admin()->id,
            'approved_at' => now(),
            'rejection_reason' => $request->string('reason'),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($post)
            ->withProperties(['reason' => $request->string('reason')])->log('Blog post rejected');

        return back()->with('success', 'Blog post rejected.');
    }

    /**
     * Revert an approved or rejected post back to pending so admin has full
     * authority over the decision at any time, not just once — matches the
     * same Undo convention as achievement/certification review.
     */
    public function undo(BlogPost $post)
    {
        $this->authorize('platform.blog.review');

        abort_unless(in_array($post->status, ['approved', 'rejected'], true), 422, 'Only an approved or rejected post can be undone.');

        $post->update([
            'status' => 'pending',
            'approved_by_user_id' => null,
            'approved_at' => null,
            'published_at' => null,
            'rejection_reason' => null,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($post)->log('Blog post decision undone');

        return back()->with('success', 'Blog post decision undone — back to pending.');
    }
}
