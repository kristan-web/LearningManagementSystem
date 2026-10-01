{{-- Teacher: Assignments --}}
@extends('layouts.teacher')
@section('title', 'Assignments')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $dueDisplay = fn ($assignment) => trim(
        ($assignment->due_date?->format('M d, Y') ?? '') . ' · ' . ($assignment->due_date?->format('h:i A') ?? '')
    );

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:focus:border-blue-400';
@endphp

@section('content')
<div class="bento-content">
    {{-- Page header --}}
    <div class="bento-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Assignments</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Create and manage assignments per class, then grade submissions.</p>
        </div>
        <a href="{{ route('teacher.assignments.create') }}" class="btn-navy">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            New Assignment
        </a>
    </div>

    {{-- Stats --}}
    <div class="bento-grid">
        @foreach ([
            ['Total', $stats['total'], 'Assignments you posted', 'text-ink dark:text-white'],
            ['Awaiting Grading', $stats['awaiting_grading'], 'Submissions without a score', 'text-amber-600 dark:text-amber-400'],
            ['Overdue', $stats['overdue'], 'Past their due date', 'text-red-600 dark:text-red-400'],
        ] as [$label, $value, $hint, $tone])
            <article class="bento-card !min-h-0">
                <div class="bento-card__label">{{ $label }}</div>
                <p class="bento-card__description mt-1">{{ $hint }}</p>
                <div class="bento-card__value {{ $tone }}">{{ $value }}</div>
            </article>
        @endforeach
    </div>

    <div class="bento-grid">
        <article class="bento-card bento-card--full">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/></svg></span>
                    <div>
                        <div class="bento-card__label">Academics</div>
                        <h2 class="bento-card__title">Assignment List</h2>
                    </div>
                </div>
                {{-- Class filter --}}
                <form method="GET" action="{{ route('teacher.assignments.index') }}" class="sm:w-80">
                    <label for="schedule_id" class="sr-only">Class</label>
                    <select name="schedule_id" id="schedule_id" onchange="this.form.submit()" class="{{ $input }}">
                        <option value="">All classes</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->schedule_id }}" {{ $selectedScheduleId === (string) $schedule->schedule_id ? 'selected' : '' }}>
                                {{ $classLabel($schedule) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="st-table-wrap mt-3">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Class</th>
                            <th>Due</th>
                            <th>Max</th>
                            <th>Submissions</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            <tr>
                                <td>
                                    <a href="{{ route('teacher.assignments.show', $assignment->assignment_id) }}" class="font-semibold text-ink hover:text-brand hover:underline dark:text-white">{{ $assignment->title }}</a>
                                    @if ($assignment->instructions)
                                        <p class="mt-0.5 line-clamp-2 text-xs text-ink/55 dark:text-slate-400">{{ $assignment->instructions }}</p>
                                    @endif
                                </td>
                                <td>{{ $classLabel($assignment->schedule) }}</td>
                                <td class="whitespace-nowrap">{{ $dueDisplay($assignment) }}</td>
                                <td>{{ $assignment->max_score }}</td>
                                <td class="whitespace-nowrap">
                                    <span class="font-semibold text-ink dark:text-white">{{ $assignment->submissions_count }}</span>
                                    @if ($assignment->awaiting_grading_count > 0)
                                        <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                            {{ $assignment->awaiting_grading_count }} to grade
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('teacher.assignments.submissions', $assignment->assignment_id) }}" class="btn-navy btn-sm">Grade</a>
                                        <a href="{{ route('teacher.assignments.edit', $assignment->assignment_id) }}" class="st-btn-outline btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment->assignment_id) }}"
                                              onsubmit="return confirm('Delete this assignment and all of its submissions?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-8 items-center rounded-[0.625rem] bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 ring-1 ring-red-500/20 transition hover:bg-red-500 hover:text-white dark:text-red-400">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="st-table__empty">
                                    No assignments yet. Click "New Assignment" to post your first one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</div>
@endsection
