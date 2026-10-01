{{-- Teacher: Quizzes --}}
@extends('layouts.teacher')
@section('title', 'Quizzes')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
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
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Quizzes</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Create CSV-based quizzes per class and review attempts.</p>
        </div>
        <a href="{{ route('teacher.quizzes.create', array_filter(['schedule_id' => $selectedScheduleId])) }}" class="btn-navy">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            New Quiz
        </a>
    </div>

    <div class="bento-grid">
        <article class="bento-card bento-card--full">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                    <div>
                        <div class="bento-card__label">Assessments</div>
                        <h2 class="bento-card__title">Quiz List</h2>
                        <p class="bento-card__description">{{ $quizzes->count() }} {{ Str::plural('quiz', $quizzes->count()) }}</p>
                    </div>
                </div>
                {{-- Class filter --}}
                <form method="GET" action="{{ route('teacher.quizzes.index') }}" class="sm:w-80">
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
                            @if ($quiz->isPastDue())
                                <span class="ml-1 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-500/15 dark:text-red-300">Overdue</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $quiz->attempts_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="relative inline-block" x-data="{ extending: false }">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="extending = !extending"
                                            class="rounded-lg border border-amber-200 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-500 hover:text-white dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">Extend</button>
                                    <form method="POST" action="{{ route('teacher.quizzes.destroy', $quiz->quiz_id) }}"
                                          onsubmit="return confirm('Delete this quiz and all of its questions and attempts?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                                <form x-show="extending" x-cloak x-transition @click.outside="extending = false"
                                      method="POST" action="{{ route('teacher.quizzes.extend', $quiz->quiz_id) }}"
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
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No quizzes yet — create your first CSV-based quiz.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
            <div class="st-table-wrap mt-3">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Class</th>
                            <th class="text-center">Questions</th>
                            <th>Timer</th>
                            <th class="text-center">Attempts</th>
                            <th>Due</th>
                            <th class="text-center">Submitted</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quizzes as $quiz)
                            <tr>
                                <td class="font-semibold text-ink dark:text-white">{{ $quiz->title }}</td>
                                <td>{{ $classLabel($quiz->schedule) }}</td>
                                <td class="text-center">{{ $quiz->questions_count }}</td>
                                <td class="whitespace-nowrap">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No limit' }}</td>
                                <td class="text-center">{{ $quiz->attempts_allowed }}</td>
                                <td class="whitespace-nowrap">{{ $quiz->due_date?->format('M d, Y h:i A') ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="inline-flex min-w-7 justify-center rounded-full bg-[#eef3fd] px-2 py-0.5 text-xs font-semibold text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">{{ $quiz->attempts_count }}</span>
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('teacher.quizzes.results', $quiz->quiz_id) }}" class="st-btn-outline btn-sm">Results</a>
                                        <a href="{{ route('teacher.quizzes.edit', $quiz->quiz_id) }}" class="st-btn-outline btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('teacher.quizzes.destroy', $quiz->quiz_id) }}"
                                              onsubmit="return confirm('Delete this quiz and all of its questions and attempts?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-8 items-center rounded-[0.625rem] bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 ring-1 ring-red-500/20 transition hover:bg-red-500 hover:text-white dark:text-red-400">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="st-table__empty">No quizzes yet. Click "New Quiz" to upload your first CSV quiz.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</div>
@endsection
