{{-- Teacher: Student Grade Detail --}}
@extends('layouts.teacher')
@section('title', 'Student Grades')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';

    $studentName = $student->user
        ? trim($student->user->last_name . ', ' . $student->user->first_name)
        : $student->student_number;

    $sectionLabel = $enrollment->section
        ? trim('Grade ' . $enrollment->section->grade_level . ' - ' . $enrollment->section->section_name)
        : '—';

    $fmt = fn (?float $value) => $value === null ? '—' : number_format($value, 1) . '%';

    $statusBadge = [
        'Submitted' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Graded' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Missing' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300',
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-2">
        <a href="{{ route('teacher.grades.index') }}" class="text-sm text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">&larr; Back to Grade Book</a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $studentName }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            {{ $sectionLabel }} &middot; LRN {{ $student->lrn }} &middot; {{ $student->student_number }}
        </p>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $fmt($assignmentAvg) }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Assignments Average</p>
        </div>
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $fmt($quizAvg) }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Quizzes Average</p>
        </div>
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $fmt($overall) }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Overall Grade</p>
        </div>
    </div>

    {{-- Assignments breakdown --}}
    <section class="{{ $card }}">
        <div class="border-b border-blue-100 px-5 py-4 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Assignments</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                        <th class="px-5 py-3">Subject</th>
                        <th class="px-5 py-3">Assignment</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($assignments as $assignment)
                        <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $assignment['subject'] }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-100">{{ $assignment['title'] }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge[$assignment['status']] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $assignment['status'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm text-slate-600 dark:text-slate-300">
                                {{ $assignment['score'] !== null ? $assignment['score'] . ' / ' . $assignment['max_score'] : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No assignments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Quizzes breakdown --}}
    <section class="{{ $card }}">
        <div class="border-b border-blue-100 px-5 py-4 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Quizzes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                        <th class="px-5 py-3">Subject</th>
                        <th class="px-5 py-3">Quiz</th>
                        <th class="px-5 py-3">Attempts</th>
                        <th class="px-5 py-3 text-right">Best Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($quizzes as $quiz)
                        <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $quiz['subject'] }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-100">{{ $quiz['title'] }}</td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $quiz['attempts'] }}</td>
                            <td class="px-5 py-3.5 text-right text-sm text-slate-600 dark:text-slate-300">
                                {{ $quiz['best_score'] !== null ? $quiz['best_score'] : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No quizzes yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
