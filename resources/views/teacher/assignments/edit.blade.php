{{-- Teacher: Edit Assignment --}}
@extends('layouts.teacher')
@section('title', 'Edit Assignment')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );

    $dueValue = $assignment->due_date?->format('Y-m-d') . 'T' . $assignment->due_date?->format('H:i');
    $oldDue = old('due_date', $dueValue);
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to assignments
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Edit Assignment</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Update this assignment for your class.</p>
    </div>

    <form method="POST" action="{{ route('teacher.assignments.update', $assignment->assignment_id) }}" class="{{ $card }} overflow-hidden"
          data-confirm="Save changes?" data-confirm-text="Students will see the updated details." data-confirm-button="Save changes">
        @csrf
        @method('PUT')

        <div class="space-y-5 px-6 py-5">
            <div>
                <label for="schedule_id" class="{{ $label }}">Class <span class="text-red-500">*</span></label>
                <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                    <option value="">Select a class…</option>
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->schedule_id }}" @selected((string) old('schedule_id', $assignment->schedule_id) === (string) $schedule->schedule_id)>{{ $classLabel($schedule) }}</option>
                    @endforeach
                </select>
                @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                       value="{{ old('title', $assignment->title) }}">
                @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="due_date" class="{{ $label }}">Due date &amp; time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="due_date" id="due_date" required class="{{ $input }}" value="{{ $oldDue }}">
                    @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_score" class="{{ $label }}">Maximum score <span class="text-red-500">*</span></label>
                    <input type="number" name="max_score" id="max_score" required min="0.01" max="9999.99" step="0.01"
                           class="{{ $input }}" value="{{ old('max_score', $assignment->max_score) }}">
                    @error('max_score') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="instructions" class="{{ $label }}">Instructions <span class="text-xs font-normal text-ink/45">(optional)</span></label>
                <textarea name="instructions" id="instructions" rows="5" class="{{ $input }}">{{ old('instructions', $assignment->instructions) }}</textarea>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-white/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
            <a href="{{ route('teacher.assignments.index') }}" class="st-btn-outline">Cancel</a>
            <button type="submit" class="btn-navy">Save Changes</button>
        </div>
    </form>
</div>
@endsection
