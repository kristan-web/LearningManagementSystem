{{-- Dashboard: Student --}}
@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $panel = 'rounded-2xl border border-[#e3e8f4] bg-linear-to-b from-white to-[#f7f9fe] shadow-[0_1px_2px_rgb(22_36_79/0.04),0_8px_24px_-12px_rgb(22_36_79/0.16)] dark:border-[#24386f] dark:from-[#0f1a38] dark:to-[#0f1a38] dark:shadow-none';
    $panelHead = 'flex items-center justify-between gap-3 border-b border-[#eef1f8] px-5 py-4 dark:border-[#24386f]';
    $panelLink = 'inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-deep transition hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-500/10';

    $stats = [
        ['label' => 'Academics', 'title' => 'Pending Assignments', 'description' => 'Assignments awaiting submission', 'value' => $pendingAssignmentsCount, 'href' => route('student.assignments.index'),
         'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4'],
        ['label' => 'Academics', 'title' => 'Pending Quizzes', 'description' => 'Quizzes not yet attempted', 'value' => $pendingQuizzesCount, 'href' => route('student.quizzes.index'),
         'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['label' => 'Schedule', 'title' => 'Upcoming Events', 'description' => 'Events in the next 14 days', 'value' => $upcomingEvents->count(), 'href' => route('calendar.index'),
         'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z'],
    ];
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header">
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Your assignments, quizzes, and upcoming events at a glance.</p>
    </div>

    {{-- Welcome banner --}}
    <div class="mx-auto w-full max-w-6xl px-3 pt-3">
        <section class="bg-navy relative overflow-hidden rounded-2xl px-6 py-6 text-white shadow-[0_14px_32px_-14px_rgb(22_36_79/0.55)]">
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[1.6px] text-gold">{{ now()->format('l, F j') }}</p>
                    <h2 class="mt-1 font-display text-xl font-bold tracking-[-.3px]">Welcome back, {{ auth()->user()->first_name ?: 'Student' }}!</h2>
                    <p class="mt-1 text-sm text-white/70">Keep up with your classes, deadlines and school news.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('student.schedule.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        My Schedule
                    </a>
                    <a href="{{ route('student.materials.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        Materials
                    </a>
                </div>
            </div>
            <div class="pointer-events-none absolute -right-10 -top-12 h-44 w-44 rounded-full bg-white/5"></div>
            <div class="pointer-events-none absolute -bottom-16 right-24 h-36 w-36 rounded-full bg-gold/10"></div>
        </section>
    </div>

    {{-- Stat cards: pending work at a glance --}}
    <div class="bento-grid">
        @foreach ($stats as $stat)
            <a href="{{ $stat['href'] }}" class="bento-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="bento-card__label">{{ $stat['label'] }}</div>
                        <h2 class="bento-card__title">{{ $stat['title'] }}</h2>
                        <p class="bento-card__description">{{ $stat['description'] }}</p>
                    </div>
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg></span>
                </div>
                <div class="bento-card__value">{{ $stat['value'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="mx-auto grid max-w-6xl grid-cols-1 items-start gap-4 px-3 lg:grid-cols-3">
        {{-- Announcements newsfeed: center column, scrollable like a social feed --}}
        <section class="{{ $panel }} lg:col-span-2">
            <div class="{{ $panelHead }}">
                <div class="flex items-center gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6M5.436 13.683A4.001 4.001 0 0 1 7 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 0 1-1.564-.317Z"/></svg></span>
                    <div>
                        <h2 class="font-display text-base font-bold text-ink dark:text-white">Announcements</h2>
                        <p class="text-xs text-ink/60 dark:text-slate-400">Latest posts from your school and teachers</p>
                    </div>
                </div>
                <a href="{{ route('announcements.index') }}" class="{{ $panelLink }}">View all <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg></a>
            </div>
            @if ($announcements->isNotEmpty())
                <ul class="max-h-[32rem] space-y-1 overflow-y-auto p-3">
                    @foreach ($announcements as $announcement)
                        <li>
                            <a href="{{ route('announcements.index') }}#announcement-{{ $announcement->announcement_id }}" class="flex items-start gap-3 rounded-xl p-3 transition hover:bg-white hover:shadow-sm dark:hover:bg-white/5">
                                @if ($announcement->thumbnail_path)
                                    <img src="{{ route('announcements.thumbnail', $announcement) }}" alt="" class="h-14 w-14 shrink-0 rounded-lg object-cover">
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-ink dark:text-white">{{ $announcement->title }}</p>
                                    <p class="mt-1 text-sm text-ink/70 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($announcement->body, 160) }}</p>
                                    <p class="mt-1 text-xs text-ink/50 dark:text-slate-500">
                                        {{ $announcement->postedBy?->first_name }} {{ $announcement->postedBy?->last_name }}
                                        &middot; {{ $announcement->posted_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="flex flex-col items-center px-6 py-10 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-ink/5 text-ink/40 dark:bg-white/5 dark:text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-ink dark:text-white">No announcements yet</p>
                    <p class="mt-0.5 text-xs text-ink/60 dark:text-slate-400">New posts from your school will show up here.</p>
                </div>
            @endif
        </section>

        {{-- Upcoming events: right sidebar, sticky so it stays visible while the feed scrolls --}}
        <section class="{{ $panel }} lg:sticky lg:top-20">
            <div class="{{ $panelHead }}">
                <div class="flex items-center gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg></span>
                    <h2 class="font-display text-base font-bold text-ink dark:text-white">Upcoming Events</h2>
                </div>
                <a href="{{ route('calendar.index') }}" class="{{ $panelLink }}">Calendar <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg></a>
            </div>
            @if ($upcomingEvents->isNotEmpty())
                <ul class="space-y-1 p-3">
                    @foreach ($upcomingEvents as $event)
                        <li class="flex items-start gap-3 rounded-xl p-2.5 transition hover:bg-white hover:shadow-sm dark:hover:bg-white/5">
                            <span class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-[#eef3fd] leading-none text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">
                                <span class="text-[10px] font-bold uppercase">{{ $event->start_datetime->format('M') }}</span>
                                <span class="font-display text-base font-bold">{{ $event->start_datetime->format('j') }}</span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ $event->title }}</p>
                                <p class="text-xs text-ink/60 dark:text-slate-400">{{ $event->start_datetime->format('D, g:i A') }}</p>
                            </div>
                            @if ($event->event_type === 'Meeting' && $event->meeting_link)
                                <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener" class="shrink-0 rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-emerald-700">Join</a>
                            @else
                                <span class="shrink-0 rounded-full bg-gold/15 px-2 py-0.5 text-[11px] font-semibold text-gold-deep dark:bg-gold/10 dark:text-gold">{{ $event->event_type }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="flex flex-col items-center px-6 py-10 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-ink/5 text-ink/40 dark:bg-white/5 dark:text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-ink dark:text-white">Nothing scheduled</p>
                    <p class="mt-0.5 text-xs text-ink/60 dark:text-slate-400">No upcoming events in the next 14 days.</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
