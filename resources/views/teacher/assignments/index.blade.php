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

    {{-- Assignments table --}}
    <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                    <th class="px-4 py-3">Assignment</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Max</th>
                    <th class="px-4 py-3">Submissions</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($assignments as $assignment)
                    <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3">
                            <a href="{{ route('teacher.assignments.show', $assignment->assignment_id) }}" class="font-medium text-slate-800 hover:underline dark:text-slate-100">{{ $assignment->title }}</a>
                            @if ($assignment->instructions)
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $assignment->instructions }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $classLabel($assignment->schedule) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                            {{ $dueDisplay($assignment) }}
                            @if ($assignment->isPastDue())
                                <span class="ml-1 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-500/15 dark:text-red-300">Overdue</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $assignment->max_score }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $assignment->submissions_count }}</span>
                            @if ($assignment->awaiting_grading_count > 0)
                                <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                    {{ $assignment->awaiting_grading_count }} to grade
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="relative inline-block" x-data="{ extending: false }">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('teacher.assignments.submissions', $assignment->assignment_id) }}"
                                       class="rounded-lg border border-blue-100 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-500 hover:text-white dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">Grade</a>
                                    <button type="button" @click="extending = !extending"
                                            class="rounded-lg border border-amber-200 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-500 hover:text-white dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">Extend</button>
                                    <a href="{{ route('teacher.assignments.edit', $assignment->assignment_id) }}"
                                       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Edit</a>
                                    <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment->assignment_id) }}"
                                          onsubmit="return confirm('Delete this assignment and all of its submissions?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                                <form x-show="extending" x-cloak x-transition @click.outside="extending = false"
                                      method="POST" action="{{ route('teacher.assignments.extend', $assignment->assignment_id) }}"
                                      class="absolute right-0 z-10 mt-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-white p-3 text-left shadow-lg dark:border-slate-600 dark:bg-slate-800">
                                    @csrf
                                    @method('PUT')
                                    <input type="datetime-local" name="due_date" required
                                           class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                    <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:shadow-md">Save</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No assignments yet — create your first assignment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
