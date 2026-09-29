<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentCommentController extends Controller
{
    /** Both teachers and students post/reply the same way — role-agnostic. */
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($assignment->isAccessibleBy($request->user()), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_comment_id' => ['nullable', 'integer', 'exists:assignment_comments,comment_id'],
        ]);

        // A reply must target a top-level comment on this same assignment —
        // keeps threads one level deep and prevents cross-assignment replies.
        if (! empty($data['parent_comment_id'])) {
            $parentValid = AssignmentComment::where('comment_id', $data['parent_comment_id'])
                ->where('assignment_id', $assignment->assignment_id)
                ->whereNull('parent_comment_id')
                ->exists();
            abort_unless($parentValid, 422);
        }

        AssignmentComment::create([
            'assignment_id' => $assignment->assignment_id,
            'user_id' => $request->user()->user_id,
            'parent_comment_id' => $data['parent_comment_id'] ?? null,
            'body' => $data['body'],
        ]);

        return redirect()->back()->with('success', 'Comment posted.');
    }

    public function destroy(Request $request, Assignment $assignment, AssignmentComment $comment): RedirectResponse
    {
        abort_unless($comment->assignment_id === $assignment->assignment_id, 404);
        abort_unless($assignment->isAccessibleBy($request->user()), 403);

        // A comment's author may remove it; the assignment's owning teacher may moderate any comment.
        $isAuthor = $comment->user_id === $request->user()->user_id;
        $isOwningTeacher = $request->user()->role === 'Teacher'
            && $assignment->schedule?->teacher_id === \App\Models\Teacher::where('user_id', $request->user()->user_id)->value('teacher_id');
        abort_unless($isAuthor || $isOwningTeacher, 403);

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted.');
    }
}
