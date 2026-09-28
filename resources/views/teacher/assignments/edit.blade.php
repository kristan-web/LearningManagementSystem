{{-- Teacher: Edit Assignment --}}
@extends('layouts.teacher')
@section('title', 'Edit Assignment')

@php
    $label = 'mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );

    $dueValue = $assignment->due_date?->format('Y-m-d') . 'T' . $assignment->due_date?->format('H:i');
    $oldDue = old('due_date', $dueValue);
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Assignment</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update this assignment for your class.</p>
    </div>

    <form method="POST" action="{{ route('teacher.assignments.update', $assignment->assignment_id) }}"
          class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <div>
                <label for="schedule_id" class="{{ $label }}">Class</label>
                <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                    <option value="">Select a class…</option>
                    @foreach ($schedules as $schedule)
                        @if ($schedule->schedule_id === $assignment->schedule_id)
                            <option value="{{ $schedule->schedule_id }}" selected>{{ $classLabel($schedule) }}</option>
                        @else
                            <option value="{{ $schedule->schedule_id }}">{{ $classLabel($schedule) }}</option>
                        @endif
                    @endforeach
                </select>
                @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="title" class="{{ $label }}">Title</label>
                <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                       value="{{ old('title', $assignment->title) }}">
                @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="due_date" class="{{ $label }}">Due date &amp; time</label>
                    <input type="datetime-local" name="due_date" id="due_date" required class="{{ $input }}" value="{{ $oldDue }}">
                    @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_score" class="{{ $label }}">Maximum score</label>
                    <input type="number" name="max_score" id="max_score" required min="0.01" max="9999.99" step="0.01"
                           class="{{ $input }}" value="{{ old('max_score', $assignment->max_score) }}">
                    @error('max_score') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="instructions" class="{{ $label }}">Instructions <span class="text-xs text-slate-400">(optional)</span></label>
                <textarea name="instructions" id="instructions" rows="5" class="{{ $input }}">{{ old('instructions', $assignment->instructions) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('teacher.assignments.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
            <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">Save Changes</button>
        </div>
    </form>
</div>
@endsection