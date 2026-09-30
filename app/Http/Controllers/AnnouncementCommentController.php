<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementCommentController extends Controller
{
    /** Any role that can view the announcement may post/reply the same way — role-agnostic. */
    public function store(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->isViewableBy($request->user()), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_comment_id' => ['nullable', 'integer', 'exists:announcement_comments,comment_id'],
        ]);

        // A reply must target a top-level comment on this same announcement —
        // keeps threads one level deep and prevents cross-announcement replies.
        if (! empty($data['parent_comment_id'])) {
            $parentValid = AnnouncementComment::where('comment_id', $data['parent_comment_id'])
                ->where('announcement_id', $announcement->announcement_id)
                ->whereNull('parent_comment_id')
                ->exists();
            abort_unless($parentValid, 422);
        }

        AnnouncementComment::create([
            'announcement_id' => $announcement->announcement_id,
            'user_id' => $request->user()->user_id,
            'parent_comment_id' => $data['parent_comment_id'] ?? null,
            'body' => $data['body'],
        ]);

        return redirect()->back()->with('success', 'Comment posted.');
    }

    public function destroy(Request $request, Announcement $announcement, AnnouncementComment $comment): RedirectResponse
    {
        abort_unless($comment->announcement_id === $announcement->announcement_id, 404);
        abort_unless($announcement->isViewableBy($request->user()), 403);

        // A comment's author may remove it; Admin or the posting teacher may moderate any comment.
        $user = $request->user();
        $isAuthor = $comment->user_id === $user->user_id;
        $isAdmin = $user->role === 'Admin';
        $isPostingTeacher = $user->role === 'Teacher' && (int) $announcement->posted_by === (int) $user->user_id;
        abort_unless($isAuthor || $isAdmin || $isPostingTeacher, 403);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted.');
    }
}
