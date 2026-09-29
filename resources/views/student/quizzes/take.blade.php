{{-- Student: Take Quiz --}}
@extends('layouts.student')
@section('title', $quiz->title)

@php
    $card = 'rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800';
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8"
     x-data="{
        deadline: @js($deadline?->toIso8601String()),
        remaining: null,
        expired: false,
        tick() {
            if (!this.deadline) return;
            const diff = Math.floor((new Date(this.deadline) - new Date()) / 1000);
            if (diff <= 0) { this.remaining = 0; this.expired = true; this.$refs.quizForm.submit(); return; }
            this.remaining = diff;
        },
        get display() {
            if (this.remaining === null) return '';
            const m = Math.floor(this.remaining / 60).toString().padStart(2, '0');
            const s = (this.remaining % 60).toString().padStart(2, '0');
            return m + ':' + s;
        }
     }"
     x-init="deadline && (tick(), setInterval(() => tick(), 1000))">

    <a href="{{ route('student.quizzes.index') }}" class="text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&larr; Back to quizzes</a>

    <div class="{{ $card }} flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $quiz->title }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $quiz->schedule?->subject?->subject_name }} &middot; {{ count($questions) }} question(s)</p>
        </div>
        <template x-if="deadline">
            <div class="rounded-xl bg-amber-100 px-4 py-2 text-center dark:bg-amber-500/15">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">Time left</p>
                <p class="text-xl font-bold text-amber-800 dark:text-amber-200" x-text="display"></p>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ route('student.quizzes.submit', [$quiz->quiz_id, $attempt->attempt_id]) }}" x-ref="quizForm" class="space-y-4">
        @csrf

        @foreach ($questions as $index => $question)
            <div class="{{ $card }}">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Question {{ $index + 1 }}</p>
                <p class="mt-1 text-base font-medium text-slate-900 dark:text-white">{{ $question->question_text }}</p>

                @if ($question->question_type === 'multiple_choice')
                    <div class="mt-3 space-y-2">
                        @foreach ($question->shuffledOptions($attempt->attempt_id) as $option)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700/50">
                                <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option }}" required class="text-blue-600 focus:ring-blue-500">
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>
                @elseif ($question->question_type === 'true_false')
                    <div class="mt-3 flex gap-4">
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-600 dark:text-slate-200">
                            <input type="radio" name="answers[{{ $question->question_id }}]" value="true" required class="text-blue-600 focus:ring-blue-500"> True
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-600 dark:text-slate-200">
                            <input type="radio" name="answers[{{ $question->question_id }}]" value="false" required class="text-blue-600 focus:ring-blue-500"> False
                        </label>
                    </div>
                @else
                    <input type="text" name="answers[{{ $question->question_id }}]" required
                           class="mt-3 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                           placeholder="Your answer">
                @endif
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">Submit Quiz</button>
        </div>
    </form>
</div>
@endsection
