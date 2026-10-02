{{-- Teacher: Edit Quiz details (TeacherQuizReviewController@edit/update). Questions stay locked. --}}
@extends('layouts.teacher')
@section('title', 'Edit Quiz')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $dueValue = $quiz->due_date ? $quiz->due_date->format('Y-m-d\TH:i') : '';
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <a href="{{ route('teacher.quizzes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to quizzes
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Edit Quiz</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">{{ $quiz->schedule?->subject?->subject_name }} — {{ $quiz->schedule?->section?->section_name }}</p>
    </div>

    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z"/></svg>
        <p><span class="font-semibold">Questions are locked</span> ({{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}, {{ $quiz->attempts_count }} {{ Str::plural('attempt', $quiz->attempts_count) }} taken) so existing answers and scores stay valid. To change questions, create a new quiz.</p>
    </div>

    <form method="POST" action="{{ route('teacher.quizzes.update', $quiz->quiz_id) }}" class="{{ $card }} overflow-hidden"
          data-confirm="Save quiz details?" data-confirm-text="Students will see the new title, timer, attempts and due date." data-confirm-button="Save changes">
        @csrf
        @method('PUT')

        <div class="space-y-5 px-6 py-5">
            <div>
                <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}" value="{{ old('title', $quiz->title) }}">
                @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="time_limit_minutes" class="{{ $label }}">Time limit (minutes) <span class="text-xs font-normal text-ink/45">(optional)</span></label>
                    <input type="number" name="time_limit_minutes" id="time_limit_minutes" min="1" max="600" class="{{ $input }}"
                           value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}" placeholder="No limit">
                    @error('time_limit_minutes') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="attempts_allowed" class="{{ $label }}">Attempts allowed <span class="text-red-500">*</span></label>
                    <select name="attempts_allowed" id="attempts_allowed" required class="{{ $input }}">
                        @foreach ([1, 2] as $n)
                            <option value="{{ $n }}" @selected((int) old('attempts_allowed', $quiz->attempts_allowed) === $n)>{{ $n }} {{ Str::plural('attempt', $n) }}</option>
                        @endforeach
                    </select>
                    @error('attempts_allowed') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="due_date" class="{{ $label }}">Due date &amp; time <span class="text-xs font-normal text-ink/45">(optional)</span></label>
                <input type="datetime-local" name="due_date" id="due_date" class="{{ $input }}" value="{{ old('due_date', $dueValue) }}">
                @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-white/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
            <a href="{{ route('teacher.quizzes.index') }}" class="st-btn-outline">Cancel</a>
            <button type="submit" class="btn-navy">Save Changes</button>
        </div>
    </form>
</div>
@endsection
