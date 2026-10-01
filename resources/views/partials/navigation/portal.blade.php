{{-- Partial: Student/Teacher Top Navbar (same bar as the admin layout: page title, shortcuts with unread count, profile) --}}
@php
    $navUser = auth()->user();
    $navName = trim(($navUser->first_name ?? '') . ' ' . ($navUser->last_name ?? '')) ?: $navUser->role;
    $navInitials = mb_strtoupper(mb_substr($navUser->first_name ?? '', 0, 1) . mb_substr($navUser->last_name ?? '', 0, 1));
    $isTeacher = $navUser->role === 'Teacher';
    $inboxRoute = $isTeacher ? 'teacher.communication.index' : 'student.communication.index';
    $inboxPath = $isTeacher ? 'teacher/communication*' : 'student/communication*';
    $unreadMessages = \App\Models\Message::where('receiver_id', $navUser->user_id)->whereNull('read_at')->count();

    $iconBtn = 'relative flex h-10 w-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink/70 shadow-sm transition hover:text-brand hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white';
    $iconBtnActive = '!border-brand/20 !bg-brand/10 !text-brand dark:!bg-blue-500/20 dark:!text-blue-300';
@endphp

<nav class="sticky top-0 z-20 -mx-4 -mt-4 mb-4 border-b border-ink/10 bg-white/80 py-3 pl-16 pr-4 backdrop-blur dark:border-slate-700 dark:bg-slate-900/80">
    <div class="flex items-center justify-between gap-4">
        <h2 class="truncate font-display text-lg font-bold tracking-[-.3px] text-ink dark:text-white">@yield('title', $navUser->role)</h2>

        <div class="flex items-center gap-3">
            <a href="{{ route('announcements.index') }}" class="{{ $iconBtn }} {{ request()->is('announcements*') ? $iconBtnActive : '' }}" aria-label="Announcements" title="Announcements">
                <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </a>
            <a href="{{ route($inboxRoute) }}" class="{{ $iconBtn }} {{ request()->is($inboxPath) ? $iconBtnActive : '' }}" aria-label="Inbox{{ $unreadMessages ? ", $unreadMessages unread" : '' }}" title="Inbox">
                <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                @if ($unreadMessages)
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
                @endif
            </a>

            {{-- Profile: the user's own profile page --}}
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40" title="My profile">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-linear-150 from-[#3a52a0] to-ink text-sm font-semibold text-white shadow-sm">{{ $navInitials ?: mb_substr($navUser->role, 0, 1) }}</span>
                <span class="hidden text-sm font-semibold text-ink/80 md:block dark:text-slate-200">{{ $navName }}</span>
            </a>
        </div>
    </div>
</nav>
