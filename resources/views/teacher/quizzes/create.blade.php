{{-- Teacher: New Quiz --}}
@extends('layouts.teacher')
@section('title', 'New Quiz')

@php
    $card = 'rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800';
    $label = 'mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">New Quiz</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload a CSV of questions to create a quiz for one of your classes.</p>
    </div>

    @if ($schedules->count() === 0)
        <div class="{{ $card }} p-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">You don't have any classes yet. Quizzes are posted per class, so a schedule is required.</p>
        </div>
    @else
        <div class="{{ $card }} p-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Need the CSV format? <a href="{{ asset('templates/quiz-import-template.csv') }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">Download the template</a>.
                Columns: <code class="rounded bg-slate-100 px-1 py-0.5 text-xs dark:bg-slate-700">question_text, question_type, option_a, option_b, option_c, option_d, correct_answer</code>.
                <code class="rounded bg-slate-100 px-1 py-0.5 text-xs dark:bg-slate-700">question_type</code> must be
                <code class="rounded bg-slate-100 px-1 py-0.5 text-xs dark:bg-slate-700">multiple_choice</code>,
                <code class="rounded bg-slate-100 px-1 py-0.5 text-xs dark:bg-slate-700">true_false</code>, or
                <code class="rounded bg-slate-100 px-1 py-0.5 text-xs dark:bg-slate-700">short_answer</code>.
            </p>
        </div>

        <form method="POST" action="{{ route('teacher.quizzes.store') }}" enctype="multipart/form-data" class="{{ $card }} p-6">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="schedule_id" class="{{ $label }}">Class</label>
                    <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                        <option value="">Select a class…</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->schedule_id }}" {{ (string) old('schedule_id') === (string) $schedule->schedule_id ? 'selected' : '' }}>
                                {{ $classLabel($schedule) }}
                            </option>
                        @endforeach
                    </select>
                    @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="{{ $label }}">Title</label>
                    <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                           value="{{ old('title') }}" placeholder="e.g. Quiz 1: Vocabulary Check">
                    @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="time_limit_minutes" class="{{ $label }}">Time limit (minutes) <span class="text-xs text-slate-400">(optional)</span></label>
                        <input type="number" name="time_limit_minutes" id="time_limit_minutes" min="1" max="600"
                               class="{{ $input }}" value="{{ old('time_limit_minutes') }}" placeholder="e.g. 20">
                        @error('time_limit_minutes') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="attempts_allowed" class="{{ $label }}">Attempts allowed</label>
                        <select name="attempts_allowed" id="attempts_allowed" required class="{{ $input }}">
                            <option value="1" {{ old('attempts_allowed', '1') === '1' ? 'selected' : '' }}>1 attempt</option>
                            <option value="2" {{ old('attempts_allowed') === '2' ? 'selected' : '' }}>2 attempts</option>
                        </select>
                        @error('attempts_allowed') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="due_date" class="{{ $label }}">Due date &amp; time <span class="text-xs text-slate-400">(optional)</span></label>
                    <input type="datetime-local" name="due_date" id="due_date" class="{{ $input }}" value="{{ old('due_date') }}">
                    @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="csv_file" class="{{ $label }}">Questions CSV</label>
                    <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt" class="{{ $input }}">
                    @error('csv_file') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('teacher.quizzes.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
                <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">Create Quiz</button>
            </div>
        </form>
    @endif
</div>
@endsection


