{{-- Student: Assignment post --}}
@extends('layouts.student')
@section('title', $assignment->title)

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $statusBadge = [
        'Submitted' => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Graded' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $meta = 'inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-ink/70 ring-1 ring-ink/10 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10';
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <a href="{{ route('student.assignments.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to assignments
    </a>

    {{-- Post card --}}
    <article class="{{ $card }} overflow-hidden">
        <div class="bg-navy px-6 py-5 text-white">
            <p class="text-xs font-bold uppercase tracking-[1.6px] text-gold">
                {{ $assignment->schedule?->subject?->subject_name }}
            </p>
            <h1 class="mt-1 font-display text-2xl font-bold tracking-[-.4px]">{{ $assignment->title }}</h1>
        </div>

        <div class="px-6 py-5">
            <div class="flex flex-wrap gap-2">
                <span class="{{ $meta }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    Due {{ $assignment->due_date?->format('M d, Y') }} at {{ $assignment->due_date?->format('h:i A') }}
                </span>
                <span class="{{ $meta }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                    Max {{ $assignment->max_score }} points
                </span>
            </div>

            @if ($assignment->instructions)
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-ink/80 dark:text-slate-300">{{ $assignment->instructions }}</p>
            @endif
        </div>

        <div class="border-t border-blue-100 bg-white/60 px-6 py-4 dark:border-slate-700 dark:bg-slate-900/40">
            @if ($submission)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$submission->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                        {{ $submission->status }}
                    </span>
                    <span class="text-xs text-ink/50 dark:text-slate-500">Submitted {{ $submission->submitted_at?->format('M d, Y') }}</span>
                    @if ($submission->score !== null)
                        <span class="ml-auto font-display text-lg font-bold text-ink dark:text-white">{{ $submission->score }} <span class="text-sm font-medium text-ink/50 dark:text-slate-400">/ {{ $assignment->max_score }}</span></span>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ route('student.assignments.submit', $assignment->assignment_id) }}"
                      enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                      data-confirm="Submit this assignment?" data-confirm-text="You can only submit once, so make sure you picked the right file." data-confirm-button="Yes, submit">
                    @csrf
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-white">Your work</p>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip" class="st-file mt-1.5">
                    </div>
                    <button type="submit" class="btn-navy">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M17 8l-5-5-5 5M12 3v12"/></svg>
                        Submit
                    </button>
                </form>
            @endif
        </div>
    </article>

    @include('partials.assignment-discussion', ['assignment' => $assignment])
</div>
@endsection
