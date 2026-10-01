{{-- Teacher: Assignment post --}}
@extends('layouts.teacher')
@section('title', $assignment->title)

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $meta = 'inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-ink/70 ring-1 ring-ink/10 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10';
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to assignments
    </a>

    {{-- Post card --}}
    <article class="{{ $card }} overflow-hidden">
        <div class="bg-navy px-6 py-5 text-white">
            <p class="text-xs font-bold uppercase tracking-[1.6px] text-gold">
                {{ $assignment->schedule?->subject?->subject_name }} &middot; {{ $assignment->schedule?->section?->section_name }}
            </p>
            <h1 class="mt-1 font-display text-2xl font-bold tracking-[-.4px]">{{ $assignment->title }}</h1>
        </div>

        <div class="px-6 py-5">
            <div class="flex flex-wrap gap-2">
                <span class="{{ $meta }}">Due {{ $assignment->due_date?->format('M d, Y') }} at {{ $assignment->due_date?->format('h:i A') }}</span>
                <span class="{{ $meta }}">Max {{ $assignment->max_score }} points</span>
            </div>
            @if ($assignment->instructions)
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-ink/80 dark:text-slate-300">{{ $assignment->instructions }}</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-blue-100 bg-white/60 px-6 py-4 dark:border-slate-700 dark:bg-slate-900/40">
            <span class="text-sm font-semibold text-ink dark:text-white">{{ $assignment->submissions_count }} submitted</span>
            @if ($assignment->awaiting_grading_count > 0)
                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                    {{ $assignment->awaiting_grading_count }} to grade
                </span>
            @endif
            <div class="ml-auto flex gap-2">
                <a href="{{ route('teacher.assignments.edit', $assignment->assignment_id) }}" class="st-btn-outline btn-sm">Edit</a>
                <a href="{{ route('teacher.assignments.submissions', $assignment->assignment_id) }}" class="btn-navy btn-sm">View submissions</a>
            </div>
        </div>
    </article>

    @include('partials.assignment-discussion', ['assignment' => $assignment])
</div>
@endsection
