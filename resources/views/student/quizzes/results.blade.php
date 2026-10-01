{{-- Student: Quiz Results --}}
@extends('layouts.student')
@section('title', $quiz->title . ' — Results')

@php
    $card = 'rounded-2xl border bg-linear-to-b from-white to-sky-50/60 p-6 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <a href="{{ route('student.quizzes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to quizzes
    </a>

    <div class="bg-navy relative overflow-hidden rounded-2xl px-6 py-8 text-center text-white shadow-[0_14px_32px_-14px_rgb(22_36_79/0.55)]">
        <p class="text-xs font-bold uppercase tracking-[1.6px] text-gold">{{ $quiz->title }}</p>
        <p class="mt-2 font-display text-5xl font-bold tracking-[-.04em]">{{ $attempt->score }}%</p>
        <p class="mt-2 text-sm text-white/70">Submitted {{ $attempt->submitted_at?->format('M d, Y h:i A') }}</p>
        <div class="pointer-events-none absolute -right-10 -top-12 h-40 w-40 rounded-full bg-white/5"></div>
    </div>

    <div class="space-y-4">
        @foreach ($questions as $index => $question)
            @php
                $given = $answers[$question->question_id] ?? '';
                $isCorrect = $given !== '' && strcasecmp(trim($given), trim($question->correct_answer)) === 0;
            @endphp
            <div class="{{ $card }} {{ $isCorrect ? 'border-emerald-200 dark:border-emerald-500/40' : 'border-red-200 dark:border-red-500/40' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-bold text-white dark:bg-blue-500">{{ $index + 1 }}</span>
                        <p class="pt-0.5 text-base font-semibold text-ink dark:text-white">{{ $question->question_text }}</p>
                    </div>
                    <span class="inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isCorrect ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' }}">
                        {{ $isCorrect ? 'Correct' : 'Incorrect' }}
                    </span>
                </div>

                <div class="mt-3 space-y-1 pl-10 text-sm">
                    <p class="text-ink/70 dark:text-slate-300">Your answer: <span class="font-semibold {{ $isCorrect ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $given !== '' ? $given : '—' }}</span></p>
                    @unless ($isCorrect)
                        <p class="text-ink/70 dark:text-slate-300">Correct answer: <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $question->correct_answer }}</span></p>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
