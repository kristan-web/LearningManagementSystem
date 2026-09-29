{{--
    Partial: Single assignment comment row
    Expects: $comment (with user loaded), $assignment
--}}
<div class="flex items-start justify-between gap-3">
    <div>
        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
            {{ $comment->user?->first_name }} {{ $comment->user?->last_name }}
            <span class="ml-1.5 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                {{ $comment->user?->role }}
            </span>
        </p>
        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">{{ $comment->body }}</p>
        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">{{ $comment->created_at?->diffForHumans() }}</p>
    </div>

    @if ($comment->user_id === auth()->id() || auth()->user()?->role === 'Teacher')
        <form method="POST" action="{{ route('assignments.comments.destroy', [$assignment->assignment_id, $comment->comment_id]) }}"
              onsubmit="return confirm('Delete this comment?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs font-medium text-red-500 hover:underline dark:text-red-400">Delete</button>
        </form>
    @endif
</div>
