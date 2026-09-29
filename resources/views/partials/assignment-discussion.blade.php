{{--
    Partial: Assignment discussion thread
    Expects: $assignment (with comments.user and comments.replies.user eager-loaded)
--}}
<div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Discussion</h2>
    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Ask questions or leave a comment about this assignment.</p>

    {{-- New top-level comment --}}
    <form method="POST" action="{{ route('assignments.comments.store', $assignment->assignment_id) }}" class="mt-4">
        @csrf
        <textarea name="body" rows="2" required maxlength="2000" placeholder="Write a comment..."
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
        <div class="mt-2 flex justify-end">
            <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:shadow-md">Post</button>
        </div>
    </form>

    {{-- Thread --}}
    <div class="mt-5 space-y-4 divide-y divide-slate-100 dark:divide-slate-700">
        @forelse ($assignment->comments as $comment)
            <div class="pt-4 first:pt-0">
                @include('partials.assignment-comment', ['comment' => $comment, 'assignment' => $assignment])

                {{-- Replies --}}
                @if ($comment->replies->isNotEmpty())
                    <div class="mt-3 ml-6 space-y-3 border-l-2 border-slate-100 pl-4 dark:border-slate-700">
                        @foreach ($comment->replies as $reply)
                            @include('partials.assignment-comment', ['comment' => $reply, 'assignment' => $assignment])
                        @endforeach
                    </div>
                @endif

                {{-- Reply toggle + form --}}
                <div class="mt-2 ml-6" x-data="{ replying: false }">
                    <button type="button" @click="replying = !replying" class="text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400">
                        Reply
                    </button>
                    <form x-show="replying" x-cloak method="POST" action="{{ route('assignments.comments.store', $assignment->assignment_id) }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="parent_comment_id" value="{{ $comment->comment_id }}">
                        <textarea name="body" rows="2" required maxlength="2000" placeholder="Write a reply..."
                                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="rounded-lg border border-blue-100 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-500 hover:text-white dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">Reply</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <p class="pt-2 text-sm text-slate-500 dark:text-slate-400">No comments yet — be the first to ask a question.</p>
        @endforelse
    </div>
</div>
