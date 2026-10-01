{{-- Teacher: New Assignment --}}
@extends('layouts.teacher')
@section('title', 'New Assignment')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to assignments
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">New Assignment</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Create an assignment for one of your classes.</p>
    </div>

    @if ($schedules->count() === 0)
        <div class="{{ $card }} p-6">
            <p class="text-sm text-ink/70 dark:text-slate-300">You don't have any classes yet. Assignments are posted per class, so a schedule is required.</p>
        </div>
    @else
        <form method="POST" action="{{ route('teacher.assignments.store') }}" class="{{ $card }} overflow-hidden"
              data-confirm="Post this assignment?" data-confirm-text="Students in the class will see it right away." data-confirm-button="Post assignment">
            @csrf

            <div class="flex items-start gap-3 border-b border-blue-100 px-6 py-4 dark:border-slate-700">
                <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/></svg></span>
                <div>
                    <h2 class="font-display text-base font-bold text-ink dark:text-white">Assignment details</h2>
                    <p class="mt-0.5 text-xs text-ink/60 dark:text-slate-400">Students submit one file before the due date.</p>
                </div>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div>
                    <label for="schedule_id" class="{{ $label }}">Class <span class="text-red-500">*</span></label>
                    <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                        <option value="">Select a class…</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->schedule_id }}" @selected((string) old('schedule_id', request('schedule_id')) === (string) $schedule->schedule_id)>{{ $classLabel($schedule) }}</option>
                        @endforeach
                    </select>
                    @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                           value="{{ old('title') }}" placeholder="e.g. Research Paper — Chapter 1">
                    @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="due_date" class="{{ $label }}">Due date &amp; time <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="due_date" id="due_date" required class="{{ $input }}" value="{{ old('due_date') }}">
                        @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="max_score" class="{{ $label }}">Maximum score <span class="text-red-500">*</span></label>
                        <input type="number" name="max_score" id="max_score" required min="0.01" max="9999.99" step="0.01"
                               class="{{ $input }}" value="{{ old('max_score', '100') }}">
                        @error('max_score') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="instructions" class="{{ $label }}">Instructions <span class="text-xs font-normal text-ink/45">(optional)</span></label>
                    <textarea name="instructions" id="instructions" rows="5" class="{{ $input }}" placeholder="What students need to do…">{{ old('instructions') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-white/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <a href="{{ route('teacher.assignments.index') }}" class="st-btn-outline">Cancel</a>
                <button type="submit" class="btn-navy">Create Assignment</button>
            </div>
        </form>
    @endif
</div>
@endsection
