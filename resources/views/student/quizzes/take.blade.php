{{-- Student: Take Quiz --}}
@extends('layouts.student')
@section('title', $quiz->title)

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 p-6 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $choice = 'flex cursor-pointer items-center gap-2.5 rounded-xl border border-[#d5dcec] bg-white px-3.5 py-2.5 text-sm text-ink/80 transition hover:border-[#c9d6f5] hover:bg-[#f5f7fd] has-[:checked]:border-brand has-[:checked]:bg-[#eef3fd] has-[:checked]:font-semibold has-[:checked]:text-ink dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-700/50 dark:has-[:checked]:border-blue-400 dark:has-[:checked]:bg-blue-500/15 dark:has-[:checked]:text-white';
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

    <a href="{{ route('student.quizzes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to quizzes
    </a>

    <div class="bg-navy flex items-center justify-between gap-4 rounded-2xl px-6 py-5 text-white shadow-[0_14px_32px_-14px_rgb(22_36_79/0.55)]">
        <div>
            <p class="text-xs font-bold uppercase tracking-[1.6px] text-gold">{{ $quiz->schedule?->subject?->subject_name }}</p>
            <h1 class="mt-1 font-display text-2xl font-bold tracking-[-.4px]">{{ $quiz->title }}</h1>
            <p class="mt-1 text-sm text-white/70">{{ count($questions) }} question(s)</p>
        </div>
        <template x-if="deadline">
            <div class="rounded-xl bg-white/10 px-4 py-2 text-center ring-1 ring-white/20">
                <p class="text-xs font-semibold uppercase tracking-wide text-gold">Time left</p>
                <p class="font-display text-xl font-bold" x-text="display"></p>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ route('student.quizzes.submit', [$quiz->quiz_id, $attempt->attempt_id]) }}" x-ref="quizForm" class="space-y-4"
          data-confirm="Submit your answers?" data-confirm-text="You can't change your answers after you submit." data-confirm-button="Submit quiz">
        @csrf

        @foreach ($questions as $index => $question)
            <div class="{{ $card }}">
                <div class="flex items-start gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-bold text-white dark:bg-blue-500">{{ $index + 1 }}</span>
                    <p class="pt-0.5 text-base font-semibold text-ink dark:text-white">{{ $question->question_text }}</p>
                </div>

                @if ($question->question_type === 'multiple_choice')
                    <div class="mt-4 space-y-2">
                        @foreach ($question->shuffledOptions($attempt->attempt_id) as $option)
                            <label class="{{ $choice }}">
                                <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option }}" required class="accent-brand">
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>
                @elseif ($question->question_type === 'true_false')
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <label class="{{ $choice }}">
                            <input type="radio" name="answers[{{ $question->question_id }}]" value="true" required class="accent-brand"> True
                        </label>
                        <label class="{{ $choice }}">
                            <input type="radio" name="answers[{{ $question->question_id }}]" value="false" required class="accent-brand"> False
                        </label>
                    </div>
                @else
                    <input type="text" name="answers[{{ $question->question_id }}]" required
                           class="mt-4 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400"
                           placeholder="Your answer">
                @endif
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="btn-navy">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Submit Quiz
            </button>
        </div>
    </form>
</div>
@endsection
