{{-- Teacher: Quizzes --}}
@extends('layouts.teacher')
@section('title', 'Quizzes')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';

    $dueDisplay = fn ($quiz) => $quiz->due_date
        ? trim($quiz->due_date->format('M d, Y') . ' · ' . $quiz->due_date->format('h:i A'))
        : 'No due date';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Quizzes</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create quizzes per class, then review attempts.</p>
        </div>
        <a href="{{ route('teacher.quizzes.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">
            New Quiz
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="{{ $card }}">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['total'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total quizzes</p>
        </div>
        <div class="{{ $card }}">
            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['awaiting_review'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Awaiting review</p>
        </div>
        <div class="{{ $card }}">
            <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Overdue</p>
        </div>
    </div>

    <form method="GET" action="{{ route('teacher.quizzes.index') }}" class="flex flex-wrap items-center gap-3">
        <label for="schedule_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">Class</label>
        <select name="schedule_id" id="schedule_id" onchange="this.form.submit()"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
            <option value="">All classes</option>
            @foreach ($schedules as $schedule)
                <option value="{{ $schedule->schedule_id }}" {{ $selectedScheduleId === (string) $schedule->schedule_id ? 'selected' : '' }}>
                    {{ $classLabel($schedule) }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                    <th class="px-4 py-3">Quiz</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Time limit</th>
                    <th class="px-4 py-3">Attempts</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($quizzes as $quiz)
                    <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $quiz->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $quiz->questions_count }} question{{ $quiz->questions_count === 1 ? '' : 's' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $classLabel($quiz->schedule) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $dueDisplay($quiz) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $quiz->attempts_count }}</span>
                            @if ($quiz->awaiting_review_count > 0)
                                <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                    {{ $quiz->awaiting_review_count }} to review
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('teacher.quizzes.attempts', $quiz->quiz_id) }}"
                                   class="rounded-lg border border-blue-100 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-500 hover:text-white dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">Review</a>
                                <a href="{{ route('teacher.quizzes.edit', $quiz->quiz_id) }}"
                                   class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">Edit</a>
                                <form method="POST" action="{{ route('teacher.quizzes.destroy', $quiz->quiz_id) }}"
                                      onsubmit="return confirm('Delete this quiz and all of its attempts?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No quizzes yet — create your first quiz.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
