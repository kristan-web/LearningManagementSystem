{{-- Teacher: Quizzes --}}
@extends('layouts.teacher')
@section('title', 'Quizzes')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Quizzes</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create CSV-based quizzes per class and review attempts.</p>
        </div>
        <a href="{{ route('teacher.quizzes.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">
            New Quiz
        </a>
    </div>

    {{-- Class filter --}}
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

    {{-- Quizzes table --}}
    <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                    <th class="px-4 py-3">Quiz</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Questions</th>
                    <th class="px-4 py-3">Timer</th>
                    <th class="px-4 py-3">Attempts</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Submitted</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($quizzes as $quiz)
                    <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $quiz->title }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $classLabel($quiz->schedule) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $quiz->questions_count }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No limit' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $quiz->attempts_allowed }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                            {{ $quiz->due_date?->format('M d, Y h:i A') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $quiz->attempts_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <form method="POST" action="{{ route('teacher.quizzes.destroy', $quiz->quiz_id) }}"
                                  onsubmit="return confirm('Delete this quiz and all of its questions and attempts?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No quizzes yet — create your first CSV-based quiz.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
