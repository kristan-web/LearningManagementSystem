@if ($sections->isNotEmpty())
    <form method="GET" action="{{ route('announcements.index') }}" class="flex items-center gap-2">
        <label for="section_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">Audience:</label>
        <select name="section_id" id="section_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            <option value="">All Viewable</option>
            <option value="school_wide" {{ request('section_id') === 'school_wide' ? 'selected' : '' }}>School-wide</option>
            @foreach ($sections as $sec)
                <option value="{{ $sec->section_id }}" {{ request('section_id') == $sec->section_id ? 'selected' : '' }}>{{ $sec->section_name }}</option>
            @endforeach
        </select>
    </form>
@endif

<div class="space-y-4">
    @forelse ($announcements as $announcement)
        <article id="announcement-{{ $announcement->announcement_id }}" class="scroll-mt-20 rounded-2xl border border-blue-100 bg-white p-6 shadow-sm target:ring-2 target:ring-blue-400 dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="rounded bg-blue-50 px-2 py-0.5 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $announcement->section ? $announcement->section->section_name : 'School-wide' }}
                        </span>
                        <span class="text-slate-400">•</span>
                        <time class="text-slate-500 dark:text-slate-400">{{ $announcement->posted_at?->format('M d, Y · g:i A') ?? 'Recently' }}</time>
                    </div>
                    <h2 class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $announcement->title }}</h2>
                </div>
                @if (auth()->user()?->role === 'Admin' || (auth()->user()?->role === 'Teacher' && auth()->id() === (int)$announcement->posted_by))
                    <button type="button" @click="announcementTitle = @js($announcement->title); deleteAction = @js(route('announcements.destroy', $announcement)); deleteOpen = true;" class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400">
                        Delete
                    </button>
                @endif
            </div>
            @if ($announcement->thumbnail_path)
                <img src="{{ route('announcements.thumbnail', $announcement) }}" alt="" class="mt-4 max-h-96 w-full rounded-xl object-cover">
            @endif
            <p class="mt-4 text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $announcement->body }}</p>
            @if ($announcement->attachments->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($announcement->attachments as $attachment)
                        <a href="{{ route('announcements.attachments.download', $attachment) }}" class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-slate-700/50">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-9a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="truncate">{{ $attachment->file_name }}</span>
                            <span class="ml-auto shrink-0 text-xs font-normal text-slate-400">{{ number_format($attachment->file_size / 1024, 1) }} KB</span>
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400 dark:border-slate-800">
                Posted by {{ $announcement->postedBy?->first_name }} {{ $announcement->postedBy?->last_name }} ({{ $announcement->postedBy?->role ?? 'Staff' }})
            </div>
            @include('partials.announcement-discussion', ['announcement' => $announcement])
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-200 p-12 text-center dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-900 dark:text-white">No announcements found</p>
        </div>
    @endforelse

    @if ($announcements->hasPages())
        <div class="pt-4">{{ $announcements->links() }}</div>
    @endif
</div>
