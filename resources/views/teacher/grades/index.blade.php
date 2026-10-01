{{-- Teacher: Grade Book --}}
@extends('layouts.teacher')
@section('title', 'Grade Book')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';

    $studentName = fn ($student) => $student?->user
        ? trim($student->user->last_name . ', ' . $student->user->first_name)
        : ($student?->student_number ?? 'Unknown student');

    $sectionLabel = fn ($section) => $section
        ? trim('Grade ' . $section->grade_level . ' - ' . $section->section_name)
        : '—';

    $fmt = fn (?float $value) => $value === null ? '—' : number_format($value, 1) . '%';
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Grade Book</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Assignment, quiz, and overall grades for students across your classes.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['sections'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sections</p>
        </div>
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['students'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Students</p>
        </div>
    </div>

    <section class="{{ $card }}">
        {{-- Filters --}}
        <form method="GET" action="{{ route('teacher.grades.index') }}" class="grid grid-cols-1 gap-3 border-b border-blue-100 p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-700">
            <div class="relative sm:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, student no. or LRN..." class="{{ $input }} pl-9">
            </div>
            <select name="section_id" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All sections</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->section_id }}" @selected((string) $selectedSectionId === (string) $section->section_id)>
                        {{ $sectionLabel($section) }}
                    </option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-navy w-full justify-center">Filter</button>
                @if (request()->hasAny(['search', 'section_id']))
                    <a href="{{ route('teacher.grades.index') }}" title="Clear filters"
                       class="flex shrink-0 items-center justify-center rounded-lg border border-slate-300 px-3 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>

        {{-- Grades table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                        <th class="px-5 py-3">Student</th>
                        <th class="px-5 py-3">LRN</th>
                        <th class="px-5 py-3">Section</th>
                        <th class="px-5 py-3">Assignments Avg</th>
                        <th class="px-5 py-3">Quizzes Avg</th>
                        <th class="px-5 py-3">Overall Grade</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($enrollments as $enrollment)
                        @php
                            $student = $enrollment->student;
                            $summary = $grades[$student->student_id] ?? ['assignment' => null, 'quiz' => null, 'overall' => null];
                        @endphp
                        <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-emerald-500 to-emerald-700 text-[11px] font-bold text-white">
                                        {{ mb_strtoupper(mb_substr($student?->user?->first_name ?? 'S', 0, 1) . mb_substr($student?->user?->last_name ?? '', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-slate-100">{{ $studentName($student) }}</p>
                                        <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $student?->student_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $student?->lrn }}</td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $sectionLabel($enrollment->section) }}</td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $fmt($summary['assignment']) }}</td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $fmt($summary['quiz']) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                    {{ $fmt($summary['overall']) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('teacher.grades.show', $student->student_id) }}"
                                   class="text-xs font-semibold text-blue-600 transition hover:underline dark:text-blue-400">View Grades</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No students found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="border-t border-blue-100 px-5 py-4 dark:border-slate-700">
                {{ $enrollments->links() }}
            </div>
        @endif
    </section>
</div>
@endsection

