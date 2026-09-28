{{-- Teacher: New Assignment --}}
@extends('layouts.teacher')
@section('title', 'New Assignment')

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
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">New Assignment</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create an assignment for one of your classes.</p>
    </div>

    @if ($schedules->count() === 0)
        <div class="{{ $card }} p-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">You don't have any classes yet. Assignments are posted per class, so a schedule is required.</p>
        </div>
    @else
        <form method="POST" action="{{ route('teacher.assignments.store') }}" class="{{ $card }} p-6">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="schedule_id" class="{{ $label }}">Class</label>
                    <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                        <option value="">Select a class…</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->schedule_id }}">{{ $classLabel($schedule) }}</option>
                        @endforeach
                    </select>
                    @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="{{ $label }}">Title</label>
                    <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                           value="{{ old('title') }}" placeholder="e.g. Research Paper — Chapter 1">
                    @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="due_date" class="{{ $label }}">Due date &amp; time</label>
                        <input type="datetime-local" name="due_date" id="due_date" required class="{{ $input }}" value="{{ old('due_date') }}">
                        @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="max_score" class="{{ $label }}">Maximum score</label>
                        <input type="number" name="max_score" id="max_score" required min="0.01" max="9999.99" step="0.01"
                               class="{{ $input }}" value="{{ old('max_score', '100') }}">
                        @error('max_score') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="instructions" class="{{ $label }}">Instructions <span class="text-xs text-slate-400">(optional)</span></label>
                    <textarea name="instructions" id="instructions" rows="5" class="{{ $input }}" placeholder="What students need to do…">{{ old('instructions') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('teacher.assignments.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
                <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">Create Assignment</button>
            </div>
        </form>
    @endif
</div>
@endsection