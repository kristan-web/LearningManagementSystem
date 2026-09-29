{{-- Student: Quiz Results --}}
@extends('layouts.student')
@section('title', $quiz->title . ' — Results')

@php
    $card = 'rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800';
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <a href="{{ route('student.quizzes.index') }}" class="text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&larr; Back to quizzes</a>

    <div class="{{ $card }} text-center">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ $quiz->title }}</p>
        <p class="mt-2 text-4xl font-bold text-slate-900 dark:text-white">{{ $attempt->score }}%</p>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Submitted {{ $attempt->submitted_at?->format('M d, Y h:i A') }}</p>
    </div>

    <div class="space-y-4">
        @foreach ($questions as $index => $question)
            @php
                $given = $answers[$question->question_id] ?? '';
                $isCorrect = $given !== '' && strcasecmp(trim($given), trim($question->correct_answer)) === 0;
            @endphp
            <div class="{{ $card }} {{ $isCorrect ? 'border-emerald-200 dark:border-emerald-500/40' : 'border-red-200 dark:border-red-500/40' }}">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Question {{ $index + 1 }}</p>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $isCorrect ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' }}">
                        {{ $isCorrect ? 'Correct' : 'Incorrect' }}
                    </span>
                </div>
                <p class="mt-1 text-base font-medium text-slate-900 dark:text-white">{{ $question->question_text }}</p>

                <div class="mt-3 space-y-1 text-sm">
                    <p class="text-slate-600 dark:text-slate-300">Your answer: <span class="font-medium {{ $isCorrect ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $given !== '' ? $given : '—' }}</span></p>
                    @unless ($isCorrect)
                        <p class="text-slate-600 dark:text-slate-300">Correct answer: <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $question->correct_answer }}</span></p>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
