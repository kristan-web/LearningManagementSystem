{{-- Teacher: New Quiz --}}
@extends('layouts.teacher')
@section('title', 'New Quiz')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $code = 'rounded bg-ink/5 px-1 py-0.5 text-xs text-ink dark:bg-white/10 dark:text-slate-200';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <a href="{{ route('teacher.quizzes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to quizzes
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">New Quiz</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Upload a CSV of questions to create a quiz for one of your classes.</p>
    </div>

    @if ($schedules->count() === 0)
        <div class="{{ $card }} p-6">
            <p class="text-sm text-ink/70 dark:text-slate-300">You don't have any classes yet. Quizzes are posted per class, so a schedule is required.</p>
        </div>
    @else
        <div class="flex items-start gap-3 rounded-2xl border border-blue-100 bg-blue-50/70 p-5 dark:border-blue-500/20 dark:bg-blue-500/10">
            <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg></span>
            <p class="text-sm leading-relaxed text-ink/75 dark:text-slate-300">
                Need the CSV format? <a href="{{ asset('templates/quiz-import-template.csv') }}" class="font-semibold text-brand-deep hover:underline dark:text-blue-300">Download the template</a>.
                Columns: <code class="{{ $code }}">question_text, question_type, option_a, option_b, option_c, option_d, correct_answer</code>.
                <code class="{{ $code }}">question_type</code> must be
                <code class="{{ $code }}">multiple_choice</code>,
                <code class="{{ $code }}">true_false</code>, or
                <code class="{{ $code }}">short_answer</code>.
            </p>
        </div>

        <form method="POST" action="{{ route('teacher.quizzes.store') }}" enctype="multipart/form-data" class="{{ $card }} overflow-hidden"
              data-confirm="Create this quiz?" data-confirm-text="Students in the class can take it as soon as it is created." data-confirm-button="Create quiz">
            @csrf

            <div class="space-y-5 px-6 py-5">
                <div>
                    <label for="schedule_id" class="{{ $label }}">Class <span class="text-red-500">*</span></label>
                    <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                        <option value="">Select a class…</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->schedule_id }}" @selected((string) old('schedule_id', request('schedule_id')) === (string) $schedule->schedule_id)>
                                {{ $classLabel($schedule) }}
                            </option>
                        @endforeach
                    </select>
                    @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                           value="{{ old('title') }}" placeholder="e.g. Quiz 1: Vocabulary Check">
                    @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="time_limit_minutes" class="{{ $label }}">Time limit (minutes) <span class="text-xs font-normal text-ink/45">(optional)</span></label>
                        <input type="number" name="time_limit_minutes" id="time_limit_minutes" min="1" max="600"
                               class="{{ $input }}" value="{{ old('time_limit_minutes') }}" placeholder="e.g. 20">
                        @error('time_limit_minutes') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="attempts_allowed" class="{{ $label }}">Attempts allowed <span class="text-red-500">*</span></label>
                        <select name="attempts_allowed" id="attempts_allowed" required class="{{ $input }}">
                            <option value="1" {{ old('attempts_allowed', '1') === '1' ? 'selected' : '' }}>1 attempt</option>
                            <option value="2" {{ old('attempts_allowed') === '2' ? 'selected' : '' }}>2 attempts</option>
                        </select>
                        @error('attempts_allowed') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="due_date" class="{{ $label }}">Due date &amp; time <span class="text-xs font-normal text-ink/45">(optional)</span></label>
                    <input type="datetime-local" name="due_date" id="due_date" class="{{ $input }}" value="{{ old('due_date') }}">
                    @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="csv_file" class="{{ $label }}">Questions CSV <span class="text-red-500">*</span></label>
                    <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt" class="st-file">
                    @error('csv_file') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-white/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <a href="{{ route('teacher.quizzes.index') }}" class="st-btn-outline">Cancel</a>
                <button type="submit" class="btn-navy">Create Quiz</button>
            </div>
        </form>
    @endif
</div>
@endsection
